<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Lib\IngPspPaymentModule;
use Lib\Helper;
use Model\Customer\Customer as IngCustomer;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ingpsp/ingpsp_module_bootstrap.php');

class ingpspCreditcard extends IngPspPaymentModule
{
    public function __construct()
    {
        $this->name = 'ingpspcreditcard';
        parent::__construct();
        $this->displayName = $this->l('ING PSP Creditcard');
        $this->description = $this->l('Accept payments for your products using creditcard.');
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
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));

        $paymentOption = new PaymentOption;
        $paymentOption->setCallToActionText($this->l('Pay by Mastercard, VISA, Maestro or V PAY'));
        $paymentOption->setLogo(Media::getMediaPath(dirname(__FILE__) . '/'.$this->name.'.png'));
        $paymentOption->setAction($this->context->link->getModuleLink($this->name, 'payment'));
        $paymentOption->setModuleName($this->name);
        return [$paymentOption];
    }

    public function execPayment($cart, $locale = '')
    {
        $customer = $this->_createCustomer($cart, $locale);
        try {
            $response = $this->ginger->createCreditCardOrder(
                Helper::getAmountInCents($cart->getOrderTotal(true)),     // Amount in cents
                $this->getPaymentCurrency(),                              // Currency
                $this->getPaymentDescription(),                           // Description
                $this->currentOrder,                                      // Merchant Order Id
                $this->getReturnURL($cart->id, $this->name),              // Return URL
                null,                                                     // Expiration Period
                $customer->toArray(),                                     // Customer Information
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

        if (!$response->firstTransactionPaymentUrl()) {
            return Tools::displayError("Error: Response did not include payment url!");
        }
         
        $this->saveINGOrderId($response, $cart->id, $this->context->customer->secure_key, $this->name);

        Tools::redirect($response->firstTransactionPaymentUrl()->toString());
    }

    private function _createCustomer($cart, $locale)
    {
        $presta_customer = new Customer((int) $cart->id_customer);
        $presta_address = new Address((int) $cart->id_address_invoice);
        $presta_country = new Country((int) $presta_address->id_country);

        return IngCustomer::createFromPrestaData(
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
        
        $ingpsp = $this->getOrderFromDB($params['order']->id_cart);
        $this->updateGingerOrder($ingpsp->getGingerOrderId(), $params['order']->id);

        return $this->fetch('module:'.$this->name.'/views/templates/hook/payment_return.tpl');
    }
}
