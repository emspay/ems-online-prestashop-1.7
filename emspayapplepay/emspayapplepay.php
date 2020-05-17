<?php

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Lib\EmsPayPaymentModule;
use Lib\Helper;
use Model\Customer\Customer as EmsCustomer;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/emspay/emspay_module_bootstrap.php');

class emspayApplePay extends EmsPayPaymentModule
{
    public function __construct()
    {
        $this->name = 'emspayapplepay';
	  $this->method_id = 'apple-pay';
        parent::__construct();
        $this->displayName = $this->l('EMS Online Apple Pay');
        $this->description = $this->l('Accept payments for your products using Apple Pay.');
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
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));

        $paymentOption = new PaymentOption;
        $paymentOption->setCallToActionText($this->l('Pay by Apple Pay'));
        $paymentOption->setLogo(Media::getMediaPath(dirname(__FILE__) . '/'.$this->name.'.png'));
        $paymentOption->setAction($this->context->link->getModuleLink($this->name, 'payment'));
        $paymentOption->setModuleName($this->name);
        return [$paymentOption];
    }
    public function execPayment($cart, $locale = '')
    {
        $customer = $this->_createCustomer($cart, $locale);
        try {
		$response = $this->ginger->createOrder([
		    'amount' => Helper::getAmountInCents($cart->getOrderTotal(true)),   // Amount in cents
		    'currency' => $this->getPaymentCurrency(),                          // Currency
		    'transactions' => [
		        [
		            'payment_method' => $this->method_id                        // Payment method
		        ]
		    ],
		    'description' => $this->getPaymentDescription(),                    // Description
		    'merchant_order_id' => $this->currentOrder,                         // Merchant Order Id
		    'return_url' => $this->getReturnURL($cart->id, $this->name),        // Return URL
		    'customer' => $customer->toArray(),                                 // Customer information
		    'extra' => ['plugin' => $this->getPluginVersion()],                 // Extra information
		    'webhook_url' => $this->getWebhookUrl(),                            // Webhook URL
		]);
        } catch (\Exception $exception) {
            return Tools::displayError($exception->getMessage());
        }

        if ($response['status'] == 'error') {
            return $response->transactions()->current()->reason()->toString();
        }

        if (!$response->getId()) {
            return Tools::displayError($response['transactions'][0]['reason']);
        }

	  $pay_url = array_key_exists(0, $response['transactions'])
		  ? $response['transactions'][0]['payment_url']
		  : null;

	  if (!$pay_url) {
		return Tools::displayError("Error: Response did not include payment url!");
	  }

        $this->saveEMSOrderId($response, $cart->id, $this->context->customer->secure_key, $this->name);

        Tools::redirect($pay_url);
    }

    private function _createCustomer($cart, $locale)
    {
        $presta_customer = new Customer((int) $cart->id_customer);
        $presta_address = new Address((int) $cart->id_address_invoice);
        $presta_country = new Country((int) $presta_address->id_country);

        return EmsCustomer::createFromPrestaData(
                    $presta_customer,
                    $presta_address,
                    $presta_country,
                    $cart->id_customer,
                    $locale
                );
    }
     

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }
        
        $emspay = $this->getOrderFromDB($params['order']->id_cart);
        $this->updateGingerOrder($emspay->getGingerOrderId(), $params['order']->id);

        return $this->fetch('module:'.$this->name.'/views/templates/hook/payment_return.tpl');
    }
}
