<?php

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Lib\EmsPayPaymentModule;
use Lib\Helper;
use Model\Customer\Customer as EmsCustomer;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/emspay/vendor/autoload.php');

class emspayBanktransfer extends EmsPayPaymentModule
{
    public function __construct()
    {
        $this->name = 'emspaybanktransfer';
	  $this->method_id = 'bank-transfer';
        parent::__construct();
        $this->displayName = $this->l('EMS Online Banktransfer');
        $this->description = $this->l('Accept payments for your products using EMS Online Banktransfer');
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
                'gingerbanktransferIBAN' => 'NL79ABNA0842577610',
                'gingerbanktransferAddress' => '',
                'gingerbanktransferOwner' => 'THIRD PARTY FUNDS EMS',
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
        try {
		$response = $this->ginger->createOrder(array_filter([
			  'amount' => Helper::getAmountInCents($cart->getOrderTotal(true)),   // Amount in cents
			  'currency' => $this->getPaymentCurrency(),                          // Currency
			  'transactions' => [
				  [
					'payment_method' => $this->method_id                      // Payment method
				  ]
			  ],
			  'description' => $this->getPaymentDescription(),                    // Description
			  'merchant_order_id' => $this->currentOrder,                         // Merchant Order Id
			  'return_url' => $this->getReturnURL($cart->id, $this->name),        // Return URL
			  'customer' => $customer->toArray(),                                 // Customer information
			  'extra' => ['plugin' => $this->getPluginVersion()],                 // Extra information
			  'webhook_url' => $this->getWebhookUrl(),                            // Webhook URL
		]));
        } catch (\Exception $exception) {
            return Tools::displayError($exception->getMessage());
        }

        if ($response['status'] == 'error') {
            return Tools::displayError($response['transactions'][0]['reason']);
        }

        if (!$response['id']) {
            return Tools::displayError("Error: Response did not include id!");
        }

        $bankReference = !empty(current($response['transactions'])) ? current($response['transactions'])['payment_method_details']['reference'] : null;

        $extra_vars = array(
            '{bankwire_owner}' => "THIRD PARTY FUNDS EMS",
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

        $this->saveEMSOrderId($response, $cart->id, $this->context->customer->secure_key, $this->name, $this->currentOrder, $bankReference);
        $this->_updateOrder($response['id']);
        $this->sendPrivateMessage($bankReference);

        Tools::redirect($this->getReturnURL($cart->id, $this->name, $response['id']));
    }

    private function _updateOrder($orderId)
    {
        $orderData = $this->ginger->getOrder($orderId);
        $this->ginger->updateOrder($orderId, $orderData);
    }

    /**
     * @param $bankReference
     */
    public function sendPrivateMessage($bankReference)
    {
        $new_message = new Message();
        $new_message->message = $this->l('EMS Online Bank Transfer Reference: ') . $bankReference;
        $new_message->id_order = $this->currentOrder;
        $new_message->private = 1;
        $new_message->add();
    }
}
