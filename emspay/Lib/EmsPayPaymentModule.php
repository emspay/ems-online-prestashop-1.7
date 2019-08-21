<?php

namespace Lib;

use Model\Emspay\Emspay;
use Model\Emspay\EmspayGateway;

/**
 * Abstract EmsPay Payment Module
 *
 * @author GingerPayments
 */
abstract class EmsPayPaymentModule extends \PaymentModule
{
    protected $extra_mail_vars;
    protected $ginger;
    protected $useDemoApiKey = false;

    const PLUGIN_TYPE = 'emspay';
    const EMSPAY_KLARNA_PLUGIN_NAME   = 'emspayklarna';
    const EMSPAY_AFTERPAY_PLUGIN_NAME = 'emspayafterpay';

    public function __construct()
    {
        $this->tab = 'payments_gateways';
        $this->version = '1.1.1';
        $this->author = 'Ginger Payments';
        $this->controllers = array('payment', 'validation');
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;
        
        parent::__construct();

        $this->createGingerClient();

        $this->confirmUninstall = $this->l('Are you sure about removing these details?');

        if (!count(\Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    protected function createGingerClient()
    {
        try {
            $apiKey = $this->apiKeyResolver();
            $this->ginger = GingerClientFactory::create(
                    new GingerClientFactoryParams(
                            self::PLUGIN_TYPE,
                            $apiKey,
                            \Configuration::get('EMS_PAY_BUNDLE_CA')
                            )
                    );
        } catch (\Exception $exception) {
            $this->warning = $exception->getMessage();
        }
    }
    
    protected function apiKeyResolver()
    {
        if ($this->name === static::EMSPAY_KLARNA_PLUGIN_NAME && !empty(\Configuration::get('EMS_PAY_APIKEY_TEST'))) {
            return \Configuration::get('EMS_PAY_APIKEY_TEST');
        }
        
        if ($this->name === static::EMSPAY_AFTERPAY_PLUGIN_NAME && !empty(\Configuration::get('EMS_PAY_AFTERPAY_APIKEY_TEST'))) {
            return \Configuration::get('EMS_PAY_AFTERPAY_APIKEY_TEST');
        }
        return \Configuration::get('EMS_PAY_APIKEY');
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    protected function checkCurrency(\Cart $cart)
    {
        $currency_order = new \Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * update order with presta order id
     *
     * @param type $orderId
     */
    public function updateGingerOrder($GingerOrderId, $PSOrderId)
    {
        $orderData = $this->ginger->getOrder($GingerOrderId);
        $orderData->merchantOrderId($PSOrderId);
        $this->ginger->updateOrder($orderData);
    }

    /**
     *
     * @param type $response
     * @param int $cartId
     * @param type $customerSecureKey
     * @param string $type
     */
    protected function saveEMSOrderId($response, $cartId, $customerSecureKey, $type, $currentOrder = null, $reference = null)
    {
        $emspay = new Emspay();
        $emspay->setGingerOrderId($response->id()->toString())
                ->setIdCart($cartId)
                ->setKey($customerSecureKey)
                ->setPaymentMethod($type)
                ->setIdOrder($currentOrder)
                ->setReference($reference);
        (new EmspayGateway(\Db::getInstance()))
                    ->save($emspay);
    }
    
    /**
     * fetch order from db
     *
     * @param int $cartId
     * @return array
     */
    protected function getOrderFromDB($cartId)
    {
        return (new EmspayGateway(\Db::getInstance()))->getByCartId($cartId);
    }
    
    /**
     * update order id
     *
     * @param type $cartId
     * @param type $orderId
     * @return type
     */
    public function updateOrderId($cartId, $orderId)
    {
        return (new EmspayGateway(\Db::getInstance()))->update($cartId, $orderId);
    }

    /**
     * @param $cart
     * @return string
     */
    protected function getReturnURL($cartId, $pluginame, $orderId = null)
    {
        $options = [
            'id_cart' => $cartId,
            'id_module' => $this->id
        ];
        if (!is_null($orderId)) {
            $options['order_id'] = $orderId;
        }
 
        return \Context::getContext()->link->getModuleLink(
                        $pluginame, 'validation', $options
        );
    }

    /**
     * @return string
     */
    protected function getPaymentDescription()
    {
        return sprintf($this->l('Your order at')." %s", \Configuration::get('PS_SHOP_NAME'));
    }
    
    /**
     * @return string
     */
    protected function getPaymentCurrency()
    {
        return \GingerPayments\Payment\Currency::EUR;
    }
    
    
    /**
     * @return string
     */
    protected function getWebhookUrl()
    {
        return \Configuration::get('EMS_PAY_USE_WEBHOOK')
            ? \_PS_BASE_URL_.\__PS_BASE_URI__.'modules/emspay/webhook.php'
            : null;
    }
    
    /**
     * @return string
     */
    public function getPluginVersion()
    {
        return sprintf('Prestashop 1.7 v%s', $this->version);
    }

    /**
     * @return bool
     */
    public function getUseDemoApiKey()
    {
        return $this->useDemoApiKey;
    }
    
    
    /**
     * @return  GingerPayments\Payment\Client
     */
    public function ginger() {
        return $this->ginger;
    }
}
