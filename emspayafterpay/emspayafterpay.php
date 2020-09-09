<?php

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Lib\EmsPayPaymentModule;
use Lib\Helper;
use Model\Emspay\EmspayGateway;
use Model\Emspay\Emspay;
use Model\Customer\Customer as EmsCustomer;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/emspay/emspay_module_bootstrap.php');


class emspayAfterpay extends EmsPayPaymentModule
{
    const TERMS_CONDITION_URL_NL = 'https://www.afterpay.nl/nl/algemeen/betalen-met-afterpay/betalingsvoorwaarden';
    const TERMS_CONDITION_URL_BE = 'https://www.afterpay.be/be/footer/betalen-met-afterpay/betalingsvoorwaarden';
    const BE_ISO_CODE = 'BE';
    
    protected $allowedLocales = ['NL', 'BE'];
   
    public function __construct()
    {
        $this->name = 'emspayafterpay';
	    $this->method_id = 'afterpay';
        $this->useDemoApiKey = true;
        parent::__construct();
        $this->displayName = $this->l('EMS Online AfterPay');
        $this->description = $this->l('Accept payments for your products using EMS Online AfterPay');
    }

    public function install()
    {
        if (!Module::isInstalled('emspay')) {
            throw new PrestaShopException('The emspay extension is not installed, please install the emspay extension first and then the current extension.');
        }
        if (!Configuration::get('EMS_PAY_APIKEY')) {
            throw new PrestaShopException('The webshop API key is missing in the emspay extension. Please add the API Key in the emspay extension, save it & then re-install this extension.');
        }
        Configuration::updateValue('EMS_AFTERPAY_COUNTRY_ACCESS', trim('NL, BE'));

        if (!parent::install()
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('actionOrderStatusUpdate')
            || !$this->registerHook('header')
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName('EMS_AFTERPAY_SHOW_FOR_IP');
        Configuration::deleteByName('EMS_AFTERPAY_COUNTRY_ACCESS');

        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }
    
    public function hookHeader()
    {
        $this->context->controller->addCss($this->_path . 'views/css/afterpay_form.css');
        $this->context->controller->addJS($this->_path . 'views/js/afterpay_form.js');
    }

    public function getContent()
    {
        $html = '<br />';
        if (Tools::isSubmit('btnSubmit')) {
            $html = $this->postProcess();
        }

        $html .= $this->displayemspay();
        $html .= $this->renderForm();

        return $html;
    }

    private function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('EMS_AFTERPAY_SHOW_FOR_IP', trim(Tools::getValue('EMS_AFTERPAY_SHOW_FOR_IP')));
            Configuration::updateValue('EMS_AFTERPAY_COUNTRY_ACCESS', trim(Tools::getValue('EMS_AFTERPAY_COUNTRY_ACCESS')));
        }
        return $this->displayConfirmation($this->l('Settings updated'));
    }
    
    private function displayemspay()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('EMS Online Settings'),
                    'icon' => 'icon-envelope'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('IP address(es) for testing.'),
                        'name' => 'EMS_AFTERPAY_SHOW_FOR_IP',
                        'required' => true,
                        'desc' => $this->l('You can specify specific IP addresses for which AfterPay is visible, for example if you want to test AfterPay you can type IP addresses as 128.0.0.1, 255.255.255.255. If you fill in nothing, then, AfterPay is visible to all IP addresses.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Countries available for AfterPay.'),
                        'name' => 'EMS_AFTERPAY_COUNTRY_ACCESS',
                        'required' => true,
                        'desc' => $this->l('To allow AfterPay to be used for any other country just add its country code (in ISO 2 standard) to the "Countries available for AfterPay" field. Example: BE, NL, FR If field is empty then AfterPay will be available for all countries.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink(
            'AdminModules',
                false
        ).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }
    
    public function getConfigFieldsValues()
    {
        return array(
            'EMS_AFTERPAY_SHOW_FOR_IP' => Tools::getValue(
                'EMS_AFTERPAY_SHOW_FOR_IP',
                Configuration::get('EMS_AFTERPAY_SHOW_FOR_IP')
            ),
            'EMS_AFTERPAY_COUNTRY_ACCESS' => Tools::getValue(
                'EMS_AFTERPAY_COUNTRY_ACCESS',
                Configuration::get('EMS_AFTERPAY_COUNTRY_ACCESS')
            )
        );
    }
    
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }
        if ($this->isSetShowForIpFilter()) {
            return;
        }

        $this->context->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_bw' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));

        $paymentOption = new PaymentOption;
        $paymentOption->setCallToActionText($this->l('Pay by AfterPay'));
        $paymentOption->setLogo(Media::getMediaPath(dirname(__FILE__) . '/logo_bestelling.png'));
        $paymentOption->setAction($this->context->link->getModuleLink($this->name, 'payment'));
        $paymentOption->setModuleName($this->name);
        $userCountry = $this->getUserCountryFromAddressId($params['cart']->id_address_invoice);
        if (!$this->CountryAccess($params['cart']->id_address_invoice)){
            return;
        }
        if ($this->isValidCountry($userCountry)) {
            $this->context->smarty->assign('terms_and_condition_url', $this->getTermsAndConditionUrlByCountryIsoLocale($userCountry));
            $paymentOption->setForm($this->context->smarty->fetch('module:' . $this->name . '/views/templates/hook/payment.tpl'));
        } else {
            $paymentOption->setAdditionalInformation('<p>'.$this->l('Afterpay is intended for use in the Netherlands and Belgium. Please choose another payment method.').'</p>');
        }
        return [$paymentOption];
    }

    /**
     * check if the EMS_AFTERPAY_SHOW_FOR_IP is set,
     * if so, only display if user is from that IP
     *
     * @return boolean
     */
    protected function isSetShowForIpFilter()
    {
        $ems_afterpay_show_for_ip = Configuration::get('EMS_AFTERPAY_SHOW_FOR_IP');
        if (strlen($ems_afterpay_show_for_ip)) {
            $ip_whitelist = array_map('trim', explode(",", $ems_afterpay_show_for_ip));
            if (!in_array(filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP), $ip_whitelist)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     *
     * @param string $idAddress
     * @return string
     */
    protected function getUserCountryFromAddressId($idAddress)
    {
        $presta_address = new Address((int) $idAddress);
        $country = new Country(intval($presta_address->id_country));
        return strtoupper($country->iso_code);
    }

    /**
     * Method checks is afterpay pm available for the user country
     *
     * @param type $countryIsoCode
     * @return type
     */
    protected function isValidCountry($countryIsoCode)
    {
        return in_array($countryIsoCode, $this->allowedLocales);
    }

    /**
     * Get Terms&Condition url based on the country iso locale code
     * If the customer is from BE use the BE url, otherwise use default NL url
     *
     * @param string $isoLocaleCode
     * @return string
     */
    protected function getTermsAndConditionUrlByCountryIsoLocale($isoLocaleCode)
    {
        if (strtoupper($isoLocaleCode) === self::BE_ISO_CODE) {
            return self::TERMS_CONDITION_URL_BE;
        }
        return self::TERMS_CONDITION_URL_NL;
    }

    /**
     * check if the EMS_AFTERPAY_COUNTY_ACCESS is set,
     * if so, only display if user is from that counties
     *
     * @return boolean
     */
    protected function CountryAccess($idusercountry)
    {
        $ems_afterpay_country_access = Configuration::get('EMS_AFTERPAY_COUNTRY_ACCESS');
            if (empty($ems_afterpay_country_access)) {
                return true;
            } else {
                $countrylist = array_map('trim', (explode(",", $ems_afterpay_country_access)));
                return in_array($this->getUserCountryFromAddressId($idusercountry), $countrylist);
            }
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
            $this->smarty->smarty->assign(array(
                'total_to_pay' => Tools::displayPrice($params['order']->getOrdersTotalPaid(), new Currency($params['order']->id_currency), false),
                'status' => 'ok',
            ));
        } else {
            $this->smarty->smarty->assign('status', 'failed');
        }
        
        $emspay = $this->getOrderFromDB($params['order']->id_cart);
        $this->updateGingerOrder($emspay->getGingerOrderId(), $params['order']->id, $params['order']->total_paid);

        return $this->fetch('module:'.$this->name.'/views/templates/hook/payment_return.tpl');
    }
    
    public function hookActionOrderStatusUpdate($params)
    {
        $emspay = (new EmspayGateway(Db::getInstance()))->getByCartId($params['cart']->id);
        if ($this->isNewOrderStatusIsShipping($params, $emspay)) {
            try {
                $this->ginger->setOrderCapturedStatus(
                             $this->ginger->getOrder($emspay->getGingerOrderId())
                             );
            } catch (\Exception $exception) {
                Tools::displayError($exception->getMessage());
                return false;
            }
        }
        return true;
    }
    
    /**
     * @param array $params
     * @return boolean
     */
    protected function isNewOrderStatusIsShipping($params, $emspay)
    {
        return (bool)  (
            $emspay !== null
            && $emspay->isAfterPayPaymentMethod()
            && isset($params['newOrderStatus'])
            && isset($params['newOrderStatus']->id)
            && intval($params['newOrderStatus']->id) === intval(Configuration::get('PS_OS_SHIPPING'))
        );
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
			      'payment_method' => $this->method_id                        // Payment method
			  ]
		    ],
		    'description' => $this->getPaymentDescription(),                    // Description
		    'merchant_order_id' => $this->currentOrder,                         // Merchant Order Id
		    'return_url' => $this->getReturnURL($cart->id, $this->name),        // Return URL
		    'customer' => $customer->toArray(),                                 // Customer information
		    'extra' => ['plugin' => $this->getPluginVersion()],                 // Extra information
		    'webhook_url' => $this->getWebhookUrl(),                            // Webhook URL
		    'order_lines' => $this->getOrderLines($cart)                        // Order lines
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
        
        $emspay = new Emspay();
        $emspay->setGingerOrderId($response['id'])
                ->setIdCart($cart->id)
                ->setKey($this->context->customer->secure_key)
                ->setIdOrder($this->currentOrder)
                ->setAfterPayPaymentMethod();
        (new EmspayGateway(\Db::getInstance()))
                    ->save($emspay);
        
        $orderData = $this->ginger->getOrder($response['id']);
	  Helper::orderDataToString($orderData);
        $this->ginger->updateOrder($response['id'], $orderData);

        Tools::redirect($this->getReturnURL($cart->id, $this->name, $response['id']));
    }
     
    /**
     * create Gigner request customer object
     *
     * @param type $cart
     * @param type $locale
     * @return Model\Customer\Customer
     */
    private function createCustomer($cart, $locale)
    {
        $presta_customer = new Customer((int) $cart->id_customer);
        $presta_address = new Address((int) $cart->id_address_invoice);
        $presta_billing_address = new Address((int) $cart->id_address_invoice);
        $presta_country = new Country((int) $presta_address->id_country);
         
        return EmsCustomer::createFromPrestaData(
                    $presta_customer,
                    $presta_address,
                    $presta_billing_address,
                    $presta_country,
                    $cart->id_customer,
                    $locale,
                    Tools::getRemoteAddr()
                );
    }
 
    /**
     * @param $cart
     * @return array
     */
    public function getOrderLines($cart)
    {
        $orderLines = [];

        foreach ($cart->getProducts() as $key => $product) {
            $orderLines[] = array_filter([
                'ean' => $this->getProductEAN($product),
                'url' => $this->getProductURL($product),
                'name' => $product['name'],
                'type' => Helper::PHYSICAL,
                'amount' => Helper::getAmountInCents(Tools::ps_round($product['price_wt'], 2)),
                'currency' => $this->getPaymentCurrency(),
                'quantity' => (int)$product['cart_quantity'],
                'image_url' => $this->getProductCoverImage($product),
                'vat_percentage' => ((int) $product['rate'] * 100),
                'merchant_order_line_id' => $product['unique_id']
            ], function ($var) {
                return !is_null($var);
            });
        }

        $shippingFee = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);

        if ($shippingFee > 0) {
            $orderLines[] = $this->getShippingOrderLine($cart, $shippingFee);
        }

        return count($orderLines) > 0 ? $orderLines : null;
    }
    
    /**
     * @param $cart
     * @param $shippingFee
     * @return array
     */
    public function getShippingOrderLine($cart, $shippingFee)
    {
        return [
            'name' => $this->l("Shipping Fee"),
            'type' => Helper::SHIPPING_FEE,
            'amount' => Helper::getAmountInCents($shippingFee),
            'currency' => $this->getPaymentCurrency(),
            'vat_percentage' => Helper::getAmountInCents($this->getShippingTaxRate($cart)),
            'quantity' => 1,
            'merchant_order_line_id' => count($cart->getProducts()) + 1
        ];
    }
    
    /**
     * @param $product
     * @return string|null
     */
    public function getProductEAN($product)
    {
        return (key_exists('ean13', $product) && strlen($product['ean13']) > 0) ? $product['ean13'] : null;
    }

    /**
     * @param $product
     * @return string|null
     */
    public function getProductURL($product)
    {
        $productURL = $this->context->link->getProductLink($product);

        return strlen($productURL) > 0 ? $productURL : null;
    }
    
    /**
     * @param $product
     * @return mixed
     */
    public function getProductCoverImage($product)
    {
        $productCover = Product::getCover($product['id_product']);

        if ($productCover) {
            return $this->context->link->getImageLink($product['link_rewrite'], $productCover['id_image']);
        }
    }

    /**
     * @param $cart
     * @return mixed
     */
    public function getShippingTaxRate($cart)
    {
        $carrier = new Carrier((int) $cart->id_carrier, (int) $this->context->cart->id_lang);

        return $carrier->getTaxesRate(
            new Address((int) $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')})
        );
    }
}
