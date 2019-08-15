<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Lib\IngPspPaymentModule;
use Lib\Helper;
use Model\Customer\Customer as IngCustomer;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ingpsp/ingpsp_module_bootstrap.php');

class ingpspCashondelivery extends IngPspPaymentModule
{
    public function __construct()
    {
        $this->name = 'ingpspcashondelivery';
        parent::__construct();
        $this->displayName = $this->l('ING PSP Cash On Delivery');
        $this->description = $this->l('Accept payments for your products using ING PSP Cash On Delivery');
    }

    public function install()
    {
        if (!Module::isInstalled('ingpsp')) {
            throw new PrestaShopException('The ingpsp extension is not installed, please install the ingpsp extension first and then the current extension.');
        }
        if (!Configuration::get('ING_PSP_APIKEY')) {
            throw new PrestaShopException('The webshop API key is missing in the ingpsp extension. Please add the API Key in the ingpsp extension, save it & then re-install this extension.');
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
        $paymentOption->setCallToActionText($this->l('Pay by Cash On Delivery'));
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
                    Configuration::get('PS_OS_PREPARATION'),
                    Configuration::get('PS_OS_OUTOFSTOCK'),
                    Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')
                ))) {
            $this->context->smarty->assign(array(
                'total_to_pay' => Tools::displayPrice($params['order']->getOrdersTotalPaid(), new Currency($params['order']->id_currency), false),
                'status' => 'ok',
                'shop_name' => strval(Configuration::get('PS_SHOP_NAME'))
            ));
        } else {
            $this->context->smarty->assign('status', 'failed');
        }
        
        $ingpsp = $this->getOrderFromDB($params['order']->id_cart);
        $this->updateGingerOrder($ingpsp->getGingerOrderId(), $params['order']->id);
        
        return $this->fetch('module:' . $this->name . '/views/templates/hook/payment_return.tpl');
    }

    public function execPayment($cart, $locale = '')
    {
        $presta_customer = new Customer((int) $cart->id_customer);
        $presta_address = new Address((int) $cart->id_address_invoice);
        $presta_country = new Country((int) $presta_address->id_country);

        $customer = IngCustomer::createFromPrestaData(
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
            $response = $this->ginger->createCashOnDeliveryOrder(
                    Helper::getAmountInCents($cart->getOrderTotal(true)),     // Amount in cents
                    $this->getPaymentCurrency(),                              // Currency
                    $paymentMethodDetails,                                    // Payment method details
                    $this->getPaymentDescription(),                           // Description
                    $this->currentOrder,                                      // Merchant Order Id
                    null,                                                     // Return URL
                    null,                                                     // Expiration Period
                    $customer->toArray(),                                     // Customer information
                    ['plugin' => $this->getPluginVersion()],                  // Extra information
                    $this->getWebhookUrl()                                    // Webhook URL
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

        $this->validateOrder(
            $cart->id,
            Configuration::get('PS_OS_PREPARATION'),
            $cart->getOrderTotal(true),
            $this->displayName,
            null,
            array(),
            null,
            false,
            $this->context->customer->secure_key
        );

        $this->saveINGOrderId($response, $cart->id, $this->context->customer->secure_key, $this->name, $this->currentOrder);

        $orderData = $this->ginger->getOrder($response->getId());
        
        $orderData->merchantOrderId($this->currentOrder);
        $this->ginger->updateOrder($orderData);
        
        Tools::redirect($this->getReturnURL($cart->id, $this->name, $response->getId()));
    }
}
