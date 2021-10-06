<?php
namespace Lib\components;

use Lib\interfaces\GingerCapturable;
use Lib\interfaces\GingerCountryValidation;
use Lib\interfaces\GingerCustomFieldsOnCheckout;
use Lib\interfaces\GingerIdentificationPay;
use Lib\interfaces\GingerIPValidation;
use Lib\interfaces\GingerIssuers;
use Lib\banktwins\GingerBankOrderBuilder;
use Lib\banktwins\GingerBankClientBuilder;
use Model\Ginger;
use Model\GingerGateway;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ginger/vendor/autoload.php');


class GingerPlugin extends \PaymentModule
{

    protected $orderBuilder;
    protected $gingerClient;
    public $method_id;
    protected $extra_mail_vars;
    protected $useDemoApiKey = false;
    protected $_html = '';

    protected $capturableMethods = ['klarnadirectdebit','afterpay','klarnapaylater'];


    use GingerOrderLinesTrait;

    public function __construct()
    {
        $this->displayName = $this->l(GingerBankConfig::BANK_LABEL . ' ' . GingerBankConfig::GINGER_BANK_LABELS[$this->method_id]);
        $this->description = $this->l('Accept payments for your products using '. GingerBankConfig::GINGER_BANK_LABELS[$this->method_id]);
        $this->tab = 'payments_gateways';
        $this->version = "1.4.0";
        $this->author = 'Ginger Payments';
        $this->controllers = array('payment', 'validation');
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;

        parent::__construct();

        $this->confirmUninstall = $this->l('Are you sure about removing these details?');

        try {
            $this->gingerClient = GingerBankClientBuilder::gingerBuildClient($this->method_id);
        }catch (\Exception $exception) {
            $this->warning = $exception->getMessage();
        }

        if (!count(\Currency::checkPaymentCurrencies($this->id)))
        {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    public function install()
    {
        if (!parent::install()) return false;

        if ($this->name == GingerBankConfig::BANK_PREFIX)
        {
            if (!$this->createTables()) return false; //Create table in db

            if (!$this->createOrderState()) return false;

            /**
             * Hook for partial refund
             * TODO: PLUG-856: the hook doesn't work on prestashop version 1.7.6.8, but works on version 1.7.7.6 (tested in docker)
             */
            if (!$this->registerHook('OrderSlip')) return false;

            return true;
        }

        if (!$this->registerHook('paymentOptions') || !$this->registerHook('paymentReturn'))
        {
            return false;
        }

        if($this instanceof GingerCustomFieldsOnCheckout)
        {
            if (!$this->registerHook('header')) return false;
        }

        if ($this instanceof GingerCapturable)
        {
            if (!$this->registerHook('actionOrderStatusUpdate')) return false;
        }

        if($this instanceof GingerCountryValidation)
        {
            \Configuration::updateValue('GINGER_'.strtoupper(str_replace('-','',$this->method_id)).'_COUNTRY_ACCESS', trim('NL, BE'));
        }

        return true;
    }

    public function uninstall()
    {

        if (!parent::uninstall())
        {
            return false;
        }

        if ($this->name == GingerBankConfig::BANK_PREFIX)
        {
            if (!\Configuration::deleteByName('GINGER_API_KEY')) return false;

            return true;

        }

        $templateForVariable =  'GINGER_'.strtoupper(str_replace('-','',$this->method_id));

        if($this instanceof GingerIPValidation)
        {
            \Configuration::deleteByName($templateForVariable.'_SHOW_FOR_IP');
        }

        if($this instanceof GingerCountryValidation)
        {
            \Configuration::deleteByName($templateForVariable.'_COUNTRY_ACCESS');
        }

        return true;
    }


    /**
     * @param \Cart $cart
     * @return bool
     */
    protected function checkCurrency(\Cart $cart)
    {
        $currencyOrder = new \Currency($cart->id_currency);
        $currenciesModule = $this->getCurrency($cart->id_currency);

        if (is_array($currenciesModule))
        {
            foreach ($currenciesModule as $currencyModule)
            {
                if ($currencyOrder->id == $currencyModule['id_currency'])
                {
                    return $this->validateCurrency($currencyOrder->iso_code);
                }
            }
        }

        return false;
    }

    /**
     * update order with presta order id
     *
     * @param $GingerOrderId
     * @param $PSOrderId
     * @param $amount
     */
    public function updateGingerOrder($GingerOrderId, $PSOrderId, $amount)
    {
        $orderData = [
            'amount' => $this->orderBuilder->getAmountInCents($amount),
            'currency' => $this->orderBuilder->getOrderCurrency(),
            'merchant_order_id' => (string) $PSOrderId
        ];
        $this->gingerClient->updateOrder($GingerOrderId, $orderData);
    }


    public function hookPaymentOptions($params)
    {
        if (!$this->active)
        {
            return;
        }

        if (!$this->checkCurrency($params['cart']))
        {
            return;
        }


        if($this instanceof GingerIPValidation)
        {
            if (!$this->validateIP()) return;
        }

        $this->context->smarty->assign(
            array_filter([
                'this_path' => $this->_path,
                'this_path_bw' => $this->_path,
                'this_path_ssl' => \Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
                'issuers' => ($this instanceof GingerIssuers) ? $this->_getIssuers() : null,
            ])
        );

        $paymentOption = new PaymentOption;
        $paymentOption->setCallToActionText($this->l('Pay by ' . GingerBankConfig::BANK_LABEL . ' ' . GingerBankConfig::GINGER_BANK_LABELS[$this->method_id]));
        $paymentOption->setLogo(\Media::getMediaPath(__PS_BASE_URI__.'modules/' .$this->name. '/'.$this->name.'.png'));
        $paymentOption->setAction($this->context->link->getModuleLink($this->name, 'payment'));
        $paymentOption->setModuleName($this->name);

        if($this instanceof GingerCountryValidation)
        {
            if (!$this->validateCountry($params['cart']->id_address_invoice))
            {
                return;
            }

            $userCountry = $this->getUserCountryFromAddressId($params['cart']->id_address_invoice);
            $this->context->smarty->assign(
                'terms_and_condition_url',
                (strtoupper($userCountry) === static::BE_ISO_CODE) ? static::TERMS_CONDITION_URL_BE : static::TERMS_CONDITION_URL_NL
            );
        }

        if($this instanceof GingerCustomFieldsOnCheckout)
        {
            $paymentOption->setForm($this->context->smarty->fetch('module:' . $this->name . '/views/templates/hook/payment.tpl'));
        }

        return [$paymentOption];

    }

    public function execPayment($cart, $locale = '')
    {
        try {
            $this->orderBuilder = new GingerBankOrderBuilder($this, $cart, $locale);
            $gingerOrder = $this->gingerClient->createOrder($this->orderBuilder->getBuiltOrder());
        } catch (\Exception $exception) {
            return \Tools::displayError($exception->getMessage());
        }

        if ($gingerOrder['status'] == 'error')
        {
            return \Tools::displayError(current($gingerOrder['transactions'])['customer_message']);
        }

        if (!$gingerOrder['id'])
        {
            return \Tools::displayError("Error: Response did not include id!");
        }


        if ($this instanceof GingerIdentificationPay)
        {
            $bankReference = current($gingerOrder['transactions']) ? current($gingerOrder['transactions'])['payment_method_details']['reference'] : null;

            $this->saveGingerOrderId($gingerOrder, $cart->id, $this->context->customer->secure_key, $this->name, $this->currentOrder, $bankReference);
            $this->sendPrivateMessage($bankReference);

            \Tools::redirect($this->orderBuilder->getReturnURL($gingerOrder['id']));

        }

        $this->saveGingerOrderId($gingerOrder, $cart->id, $this->context->customer->secure_key, $this->name);

        $pay_url = array_key_exists(0, $gingerOrder['transactions'])
            ? current($gingerOrder['transactions'])['payment_url']
            : null;

        if (!$pay_url)
        {
            return \Tools::displayError("Error: Response did not include payment url!");
        }

        \Tools::redirect($pay_url);
    }

    /**
     *
     * @param type $response
     * @param int $cartId
     * @param type $customerSecureKey
     * @param string $type
     */
    protected function saveGingerOrderId($response, $cartId, $customerSecureKey, $type, $currentOrder = null, $reference = null)
    {
        $ginger = new Ginger();
        $ginger->setGingerOrderId($response['id'])
            ->setIdCart($cartId)
            ->setKey($customerSecureKey)
            ->setPaymentMethod($type)
            ->setIdOrder($currentOrder)
            ->setReference($reference);
        (new GingerGateway(\Db::getInstance()))
            ->save($ginger);
    }

    /**
     * fetch order from db
     *
     * @param int $cartId
     * @return array
     */
    protected function getOrderFromDB($cartId)
    {
        return (new GingerGateway(\Db::getInstance()))->getByCartId($cartId);
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
        return (new GingerGateway(\Db::getInstance()))->update($cartId, $orderId);
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active)
        {
            return;
        }

        $this->orderBuilder = new GingerBankOrderBuilder($this, $params['order']);

        $ginger = $this->getOrderFromDB($params['order']->id_cart);
        $this->updateGingerOrder($ginger->getGingerOrderId(), $params['order']->id, $params['order']->total_paid);

        if($this instanceof GingerIdentificationPay)
        {
            $gingerOrder = $this->gingerClient->getOrder($ginger->getGingerOrderId());

            $gingerOrderIBAN = current($gingerOrder['transactions'])['payment_method_details']['creditor_iban'];
            $gingerOrderBIC = current($gingerOrder['transactions'])['payment_method_details']['creditor_bic'];
            $gingerOrderHolderName = current($gingerOrder['transactions'])['payment_method_details']['creditor_account_holder_name'];
            $gingerOrderHolderCity = current($gingerOrder['transactions'])['payment_method_details']['creditor_account_holder_city'];

            $this->context->smarty->assign(array(
                'total_to_pay' => \Tools::displayPrice($params['order']->getOrdersTotalPaid(), new \Currency($params['order']->id_currency), false),
                'gingerbanktransferIBAN' => $gingerOrderIBAN,
                'gingerbanktransferAddress' => $gingerOrderHolderCity,
                'gingerbanktransferOwner' => $gingerOrderHolderName,
                'gingerbanktransferBIC' => $gingerOrderBIC,
                'status' => 'ok',
                'reference' => $ginger->getReference(),
                'shop_name' => strval(\Configuration::get('PS_SHOP_NAME'))
            ));
        }

        return $this->fetch('module:'.$this->name.'/views/templates/hook/payment_return.tpl');
    }

    public function hookHeader()
    {
        $this->context->controller->addCss($this->_path . 'views/css/'.$this->method_id.'_form.css');
        $this->context->controller->addJS($this->_path . 'views/js/'.$this->method_id.'_form.js');
    }

    public function gingerClient()
    {
        return $this->gingerClient;
    }

    public function validateIP()
    {
        $ipFromConfiguration = \Configuration::get('GINGER_'.strtoupper(str_replace('-','',$this->method_id)).'_SHOW_FOR_IP'); //ex. klarna-pay-later GINGER_KLARNAPAYLATER_SHOW_FOR_IP
        if (strlen($ipFromConfiguration))
        {
            $ipWhiteList = array_map('trim', explode(",", $ipFromConfiguration));

            if (!in_array(filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP), $ipWhiteList))
            {
                return false;
            }
        }
        return true;
    }


    public function hookActionOrderStatusUpdate($params)
    {

        $ginger = (new GingerGateway(\Db::getInstance()))->getByOrderId($params['id_order']);
        $paymentMethod =  str_replace(GingerBankConfig::BANK_PREFIX,'',$ginger->getPaymentMethod());

        if (!in_array($paymentMethod, $this->capturableMethods)) return true;

        if (intval($params['newOrderStatus']->id) === intval(\Configuration::get('PS_OS_SHIPPING')))
        {
            try {
                $gingerOrderID = $ginger->getGingerOrderId();
                $gingerOrder = $this->gingerClient->getOrder($gingerOrderID);

                if (in_array('has-captures',$gingerOrder['flags'])) return true; //order is already captured
                $transactionID = current($gingerOrder['transactions']) ? current($gingerOrder['transactions'])['id'] : null;
                $this->gingerClient->captureOrderTransaction($gingerOrderID,$transactionID);
            } catch (\Exception $exception) {
                \Tools::displayError($exception->getMessage());
                return false;
            }
        }
        return true;
    }

    public function getUserCountryFromAddressID($addressID)
    {
        $prestaShopAddress = new \Address((int) $addressID);
        $country = new \Country(intval($prestaShopAddress->id_country));
        return strtoupper($country->iso_code);
    }

    public function validateCountry($addressID)
    {
        $userCountry = $this->getUserCountryFromAddressID($addressID);

        $countriesFromConfiguration = \Configuration::get('GINGER_'.strtoupper(str_replace('-','',$this->method_id)).'_COUNTRY_ACCESS');
        if (!$countriesFromConfiguration)
        {
            return true;
        }

        $countryList = array_map('trim', (explode(",", $countriesFromConfiguration)));
        if (!in_array($userCountry, $countryList))
        {
            return false;
        }

        if (!in_array($userCountry, $this->allowedLocales))
        {
            return false;
        }

        return true;
    }

    /**
     * Refund function
     */
    public function productRefund($orderId,$partialRefund, $paymentMethod, $cartId, $moduleName, $orderDetails = null)
    {
        $query = \Db::getInstance()->getRow("SELECT ginger_order_id FROM `" . _DB_PREFIX_ . GingerBankConfig::BANK_PREFIX."` WHERE `id_cart` = " . $cartId);

        $gingerOrderID = $query['ginger_order_id'];

        $gingerOrder = $this->gingerClient->getOrder($gingerOrderID);
        if (in_array('has-refunds',$gingerOrder['flags'])) return true; //order is already refunded

        if ($gingerOrder['status'] != 'completed')
        {
            throw new \Exception($paymentMethod . ': ' . $this->l('Only completed orders can be refunded.'));
        }

        $order = new \Order((int) $orderId);

        $this->orderBuilder = new GingerBankOrderBuilder($this,$order);

        $refund_data = [
            'amount' => $this->orderBuilder->getAmountInCents((float) str_replace(',', '.', $partialRefund)),
            'description' => 'OrderID: #' . $orderId
        ];

        $paymentMethodName =  str_replace(GingerBankConfig::BANK_PREFIX,'',$moduleName);

        if (in_array($paymentMethodName, $this->capturableMethods))
        {
            if(!in_array('has-captures',$gingerOrder['flags']))
            {
                throw new \Exception($paymentMethod . ': ' . $this->l('Refunds only possible when captured.'));
            }

            $refund_data['order_lines'] = $this->getOrderLinesForRefunds($order);

        }
        $gingerRefundOrder = $this->gingerClient->refundOrder($gingerOrder['id'], $refund_data);

        if (in_array($gingerRefundOrder['status'], ['error', 'cancelled', 'expired']))
        {
            if (isset(current($gingerRefundOrder['transactions'])['customer_message']))
            {
                throw new \Exception($paymentMethod . ': ' . current($gingerRefundOrder['transactions'])['customer_message']);
            }

            throw new \Exception($paymentMethod . ': ' . $this->l('Refund order is not completed.'));
        }
    }

    /**
     * @param $bankReference
     */
    public function sendPrivateMessage($bankReference)
    {
        $new_message = new \Message();
        $new_message->message = $this->l(GingerBankConfig::BANK_LABEL.' '.GingerBankConfig::GINGER_BANK_LABELS[$this->method_id].' Reference: ') . $bankReference;
        $new_message->id_order = $this->currentOrder;
        $new_message->private = 1;
        $new_message->add();
    }

    public function createTables()
    {
        $db = \Db::getInstance();

        if ( !$db->Execute( '
		DROP TABLE IF EXISTS `'._DB_PREFIX_.GingerBankConfig::BANK_PREFIX.'`;
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.GingerBankConfig::BANK_PREFIX.'` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`id_cart` int(11) DEFAULT NULL,
			`id_order` int(11) DEFAULT NULL,
			`key` varchar(64) NOT NULL,
			`ginger_order_id` varchar(36) NOT NULL,
			`payment_method` text,
			`reference` varchar(32) DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `id_order` (`id_cart`),
			KEY `ginger_order_id` (`ginger_order_id`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1' ) )
            return false;

        return true;
    }

    public function createOrderState()
    {
        if (!\Configuration::get('GINGER_PENDING'))
        {
            $orderState = new OrderState();
            $orderState->name = array();

            foreach (\Language::getLanguages() as $language)
            {
                if (\Tools::strtolower( $language['iso_code'] ) == 'nl')
                    $orderState->name[$language['id_lang']] = 'Wachten op betaling';
                else
                    $orderState->name[$language['id_lang']] = 'Waiting for payment';
            }

            $orderState->send_email = false;
            $orderState->color = '#9f00a7';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->paid = false;

            if (!$orderState->add()) return false;

            \Configuration::updateValue('GINGER_PENDING', (int)$orderState->id);
        }

        if (!\Configuration::get('GINGER_ERROR'))
        {
            $orderState = new OrderState();
            $orderState->name = array();

            foreach (\Language::getLanguages() as $language)
            {
                if (\Tools::strtolower($language['iso_code']) == 'nl')
                    $orderState->name[$language['id_lang']] = 'Betaling mislukt';
                else
                    $orderState->name[$language['id_lang']] = 'Payment Failed';
            }

            $orderState->send_email = false;
            $orderState->color = '#FF0000';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->paid = false;

            if (!$orderState->add()) return false;

            \Configuration::updateValue('GINGER_ERROR', (int)$orderState->id);
        }

        return true;
    }


    /**
     * Hook for partial refund
     */
    public function hookOrderSlip($params)
    {

        try {

            $partialRefund = current($params['productList'])['total_refunded_tax_incl'];
            $this->productRefund(
                $params['order']->id,
                $partialRefund,
                $params['order']->payment,
                $params['order']->id_cart,
                $params['order']->module
            );

        } catch (\Exception $e) {
            \Tools::displayError($e->getMessage());
            return false;
        }
    }

    private function validateCurrency($selectedCurrency)
    {

        $gingerCurrencies = $this->gingerClient->getCurrencyList();

        if (!isset($gingerCurrencies['payment_methods'][$this->method_id]['currencies']))
        {
            return false;
        }

        $supportedCurrencies = $gingerCurrencies['payment_methods'][$this->method_id]['currencies'];
        return true ? in_array($selectedCurrency,$supportedCurrencies) : false;
    }
}