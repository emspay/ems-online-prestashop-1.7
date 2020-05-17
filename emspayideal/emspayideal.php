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

class emspayIdeal extends EmsPayPaymentModule
{
    public function __construct()
    {
        $this->name = 'emspayideal';
	  $this->method_id = 'ideal';
        parent::__construct();
        $this->displayName = $this->l('EMS Online iDEAL');
        $this->description = $this->l('Accept payments for your products using EMS Online iDEAL.');
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
                || !$this->registerHook('header')
        ) {
            return false;
        }
        return true;
    }

    public function hookHeader()
    {
        $this->context->controller->addCss($this->_path . 'views/css/ideal_form.css');
        $this->context->controller->addJS($this->_path . 'views/js/ideal_form.js');
    }

    public function hookPaymentOptions()
    {
        $this->context->smarty->assign(array(
            'issuers' => $this->_getIssuers(),
            'this_path' => $this->_path,
            'this_path_bw' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
        ));

        $paymentOption = new PaymentOption;
        $paymentOption->setCallToActionText($this->l('Pay by iDEAL'));
        $paymentOption->setLogo(Media::getMediaPath(dirname(__FILE__) . '/' . $this->name . '.png'));
        $paymentOption->setAction($this->context->link->getModuleLink($this->name, 'payment', array(), true));
        $paymentOption->setModuleName($this->name);
        $paymentOption->setForm($this->context->smarty->fetch('module:' . $this->name . '/views/templates/hook/payment.tpl'));
        return [$paymentOption];
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }
        
        $emspay = $this->getOrderFromDB($params['order']->id_cart);
        $this->updateGingerOrder($emspay->getGingerOrderId(), $params['order']->id);
        
        return $this->fetch('module:' . $this->name . '/views/templates/hook/payment_return.tpl');
    }
    
    /**
     * get a list of IDEAL issuers
     *
     * @return array
     */
    private function _getIssuers()
    {
        try {
            return $this->ginger->getIdealIssuers();
        } catch (\Exception $e) {
            $this->context->controller->errors[] = $this->l($e->getMessage());
            return [];
        }
    }

    public function execPayment($cart, $locale = '')
    {
	  $customer = $this->createCustomer($cart, $locale);
	  try {
		$response = $this->ginger->createOrder([
		    'amount' => Helper::getAmountInCents($cart->getOrderTotal(true)),   // Amount in cents
		    'currency' => $this->getPaymentCurrency(),                          // Currency
		    'transactions' => [
		        [
		            'payment_method' => $this->method_id,                       // Payment method
				'payment_method_details' => ['issuer_id' => (string) filter_input(INPUT_POST, 'issuerid')]

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
            return Tools::displayError($response['transactions'][0]['reason']);
        }

        if (!$response['id']) {
            return Tools::displayError("Error: Response did not include id!");
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

    /**
     * create a Gigner request customer object
     *
     * @param type $cart
     * @param type $locale
     * @return Model\Customer\Customer
     */
    private function createCustomer($cart, $locale = '')
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
}
