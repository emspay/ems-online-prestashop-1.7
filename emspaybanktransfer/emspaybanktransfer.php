<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Lib\EmsPayPaymentModule;
use Lib\Helper;
use Model\Customer\Customer as EmsCustomer;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/emspay/emspay_module_bootstrap.php');

class emspayBanktransfer extends EmsPayPaymentModule
{
    public function __construct()
    {
        $this->name = 'emspaybanktransfer';
        parent::__construct();
        $this->displayName = $this->l('EMS PAY Banktransfer');
        $this->description = $this->l('Accept payments for your products using EMS PAY Banktransfer');
    }

    public function install()
    {
        if (!Module::isInstalled('emspay')) {
            throw new PrestaShopException('The emspay extension is not installed, please install the emspay extension first and then the current extension.');
        }
        if (!Configuration::get('EMS_PAY_APIKEY')) {
            throw new PrestaShopException('The webshop API key is missing in the emspay extension. Please add the API Key in the emspay extension, save it & then re-install this extension.');
        }
        if (!parent::install()
                || !$this->registerHook('paymentOptions')
                || !$this->registerHook('paymentReturn')
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $this->context->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_bw' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
        ));

        $paymentOption = new PaymentOption;
        $paymentOption->setCallToActionText($this->l('Pay by bank transfer'));
        $paymentOption->setLogo(Media::getMediaPath(dirname(__FILE__) . '/' . $this->name . '.png'));
        $paymentOption->setAction($this->context->link->getModuleLink($this->name, 'payment'));
        $paymentOption->setModuleName($this->name);
        return [$paymentOption];
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $state = $params['order']->getCurrentState();
        if (in_array($state, array(
                    Configuration::get('PS_OS_BANKWIRE'),
                    Configuration::get('PS_OS_OUTOFSTOCK'),
                    Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')
                ))) {
            $emspay = $this->getOrderFromDB($params['order']->id_cart);
            $this->context->smarty->assign(array(
                'total_to_pay' => Tools::displayPrice($params['order']->getOrdersTotalPaid(), new Currency($params['order']->id_currency), false),
                'gingerbanktransferIBAN' => 'NL13INGB0005300060',
                'gingerbanktransferAddress' => '',
                'gingerbanktransferOwner' => 'EMS PAY',
                'status' => 'ok',
                'reference' => $emspay->getReference(),
                'shop_name' => strval(Configuration::get('PS_SHOP_NAME'))
            ));
        } else {
            $this->context->smarty->assign('status', 'failed');
        }
        return $this->fetch('module:' . $this->name . '/views/templates/hook/payment_return.tpl');
    }

    public function execPayment($cart, $locale = '')
    {
        $presta_customer = new Customer((int) $cart->id_customer);
        $presta_address = new Address((int) $cart->id_address_invoice);
        $presta_country = new Country((int) $presta_address->id_country);

        $customer = EmsCustomer::createFromPrestaData(
                    $presta_customer,
                    $presta_address,
                    $presta_country,
                    $cart->id_customer,
                    $locale
                );
        
        $paymentMethodDetails = [
            'consumer_name' => implode(" ", [$customer->getFirstname(), $customer->getLastname()]),
            'consumer_address' => $customer->getAddress(),
            'consumer_city' => $presta_address->city,
            'consumer_country' => $customer->getCountry()
        ];
       
        try {
            $response = $this->ginger->createSepaOrder(
                    Helper::getAmountInCents($cart->getOrderTotal(true)),   // Amount in cents
                    $this->getPaymentCurrency(),                            // Currency
                    $paymentMethodDetails,                                  // Payment method details
                    $this->getPaymentDescription(),                         // Description
                    $this->currentOrder,                                    // Merchant Order Id
                    null,                                                   // Return URL
                    null,                                                   // Expiration Period
                    $customer->toArray(),                                   // Customer information
                    ['plugin' => $this->getPluginVersion()],                // Extra information
                    $this->getWebhookUrl()                                  // Webhook URL
            );
        } catch (\Exception $exception) {
            return Tools::displayError($exception->getMessage());
        }

        if ($response->status()->isError()) {
            return $response->transactions()->current()->reason()->toString();
        }

        if (!$response->getId()) {
            return Tools::displayError("Error: Response did not include id!");
        }

        $bankReference = $response->transactions()->current()->paymentMethodDetails()->reference()->toString();

        $extra_vars = array(
            '{bankwire_owner}' => "EMS PAY",
            '{bankwire_details}' => "NL79ABNA0842577610",
            '{bankwire_address}' => $this->l('Use the following reference when paying for your order:') . " " . $bankReference,
        );

        $this->validateOrder(
                $cart->id,
            Configuration::get('PS_OS_BANKWIRE'),
            $cart->getOrderTotal(true),
            $this->displayName,
            null,
            $extra_vars,
            null,
            false,
            $this->context->customer->secure_key
        );

        $this->saveEMSOrderId($response, $cart->id, $this->context->customer->secure_key, $this->name, $this->currentOrder, $response->transactions()->current()->paymentMethodDetails()->reference()->toString());
        $this->_updateOrder($response->getId());
        $this->sendPrivateMessage($bankReference);

        Tools::redirect($this->getReturnURL($cart->id, $this->name, $response->getId()));
    }

    private function _updateOrder($orderId)
    {
        $orderData = $this->ginger->getOrder($orderId);
        $orderData->merchantOrderId($this->currentOrder);
        $this->ginger->updateOrder($orderData);
    }

    /**
     * @param $bankReference
     */
    public function sendPrivateMessage($bankReference)
    {
        $new_message = new Message();
        $new_message->message = $this->l('EMS PAY Bank Transfer Reference: ') . $bankReference;
        $new_message->id_order = $this->currentOrder;
        $new_message->private = 1;
        $new_message->add();
    }
}
