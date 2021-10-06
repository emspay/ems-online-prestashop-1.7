<?php
namespace Lib\components;

use Lib\interfaces\GingerIssuers;
use Lib\interfaces\GingerOrderLines;

class GingerOrderBuilder
{

    private $paymentMethod;
    private $cart;

    private $prestashopCustomer;
    private $prestashopAddress;
    private $prestashopCountry;

    private $locale;

    const PHYSICAL = 'physical';
    const SHIPPING_FEE = 'shipping_fee';

    public function __construct($paymentMethod, $cart, $locale = '')
    {
        $this->paymentMethod = $paymentMethod;
        $this->cart = $cart;

        $this->prestashopCustomer = new \Customer((int)$this->cart->id_customer);
        $this->prestashopAddress = new \Address((int)$this->cart->id_address_invoice);
        $this->prestashopCountry = new \Country((int)$this->prestashopAddress->id_country);

        $this->locale = $locale;

    }

    public function getBuiltOrder()
    {
        $order = [];

        $order['amount'] = $this->getAmountInCents();
        $order['currency'] = $this->getOrderCurrency();
        $order['description'] = $this->getOrderDescription();
        $order['merchant_order_id'] = $this->getMerchantOrderID();
        $order['return_url'] = $this->getReturnURL();
        $order['customer'] = $this->getCustomerInformation();
        $order['extra'] = $this->getExtra();
        $order['webhook_url'] = $this->getWebhookURL();
        $transactions = $this->getOrderTransactions();

        if ($this->paymentMethod instanceof GingerIssuers)
        {
            $transactions = array_merge(
                $transactions,
                array_filter(['payment_method_details' => ['issuer_id' => $this->getSelectedIssuer()]])
            );
        }

        $order['transactions'][] = $transactions;

        if ($this->paymentMethod instanceof GingerOrderLines)
        {
            $order['order_lines'] = $this->paymentMethod->getOrderLines($this->cart);
        }


        return $order;
    }

    public function getSelectedIssuer(): string
    {
        return filter_input(INPUT_POST, 'issuerid');
    }

    /**
     * @return array
     */
    public function getCustomerInformation(): array
    {
        return array_filter([
            'address_type' => $this->getAddressType(),
            'country' => $this->getCountry(),
            'email_address' => $this->getEmailAddress(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'merchant_customer_id' => $this->getMerchantCustomerID(),
            'phone_numbers' => $this->getPhoneNumbers(),
            'address' => $this->getAddress(),
            'locale' => $this->getLocale(),
            'ip_address' => $this->getIPAddress(),
            'gender' => $this->getGender(),
            'birthdate' => $this->getBirthday(),
            'additional_addresses' => $this->getAdditionalAddress()
        ]);
    }

    public function getFirstName()
    {
        return $this->prestashopCustomer->firstname;
    }


    public function getLastName()
    {
        return $this->prestashopCustomer->lastname;
    }


    public function getMerchantCustomerID()
    {
        return $this->cart->id_customer;
    }


    public function getEmailAddress()
    {
        return $this->prestashopCustomer->email;
    }

    public function getCountry()
    {
        return $this->prestashopCountry->iso_code;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getGender()
    {
        if ($this->prestashopCustomer->id_gender)
        {
            return $this->prestashopCustomer->id_gender == '1' ? 'male' : 'female';
        }
    }

    public function getBirthday()
    {
        if ($this->prestashopCustomer->birthday)
        {
            return $this->isBirthdateValid($this->prestashopCustomer->birthday) ? $this->prestashopCustomer->birthday : null;
        }
    }

    public function getIPAddress()
    {
        return \Tools::getRemoteAddr();
    }

    public function getPhoneNumbers()
    {

        $phone_numbers = [];

        if($this->prestashopAddress->phone)
        {
            $phone_numbers[] = $this->prestashopAddress->phone;
        }
        if($this->prestashopAddress->phone_mobile)
        {
            $phone_numbers[] = $this->prestashopAddress->phone_mobile;
        }

        return $phone_numbers;
    }

    public function getAddressType()
    {
        return 'customer';
    }

    public function getPostCode()
    {
        return $this->prestashopAddress->postcode;
    }

    public function getCity()
    {
        return $this->prestashopAddress->city;
    }

    public function getFirstAddress()
    {
        return $this->prestashopAddress->address1;
    }

    public function getSecondAddress()
    {
        return $this->prestashopAddress->address2;
    }

    public function getAddress()
    {
        return implode("\n", array_filter(array(
            $this->getFirstAddress(),
            $this->getSecondAddress(),
            $this->getPostCode()." ".$this->getCity()
        )));
    }

    public function getAdditionalAddress()
    {
        return [
            array_filter(array(
            'address' => $this->getAddress(),
            'address_type' => 'billing',
            'country' => $this->getCountry()
        ))];
    }
    /**
     * @return string
     */
    public function getReturnURL($orderId = null)
    {
        $options = [
            'id_cart' => $this->cart->id,
            'id_module' => $this->paymentMethod->id
        ];

        if (isset($orderId)) $options['order_id'] = $orderId;

        return \Context::getContext()->link->getModuleLink(
            $this->paymentMethod->name, 'validation', $options
        );
    }

    /**
     * @return string
     */
    public function getOrderDescription()
    {
        return sprintf($this->paymentMethod->l('Your order at')." %s", \Configuration::get('PS_SHOP_NAME'));
    }

    /**
     * @return string
     */
    public function getOrderCurrency()
    {
        $currencyOrder = new \Currency($this->cart->id_currency);
        return $currencyOrder->iso_code;
    }

    /**
     * @return string
     */
    public function getWebhookUrl()
    {
        return \_PS_BASE_URL_.\__PS_BASE_URI__.'modules/ginger/webhook.php';
    }

    /**
     * @return string
     */
    public function getPluginVersion()
    {
        return $this->paymentMethod->version;
    }

    public function getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    public function getPluginName()
    {
        return GingerBankConfig::PLUGIN_NAME;
    }
    public function getPlatformName()
    {
        return 'PrestaShop1.7';
    }

    public function getPlatformVersion()
    {
        return _PS_VERSION_;
    }

    public function getMerchantOrderID()
    {
        return $this->paymentMethod->currentOrder;
    }

    public function getExtra()
    {
        return [
            'user_agent' => $this->getUserAgent(),
            'platform_name' => $this->getPlatformName(),
            'platform_version' => $this->getPlatformVersion(),
            'plugin_name' => $this->getPluginName(),
            'plugin_version' => $this->getPluginVersion()
        ];
    }

    public function getOrderTransactions()
    {
        return [
            'payment_method' => $this->getPaymentMethod()
        ];
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod->method_id;
    }

    /**
     * @return int
     */
    public function getAmountInCents($amount = ''): int
    {
        return $amount ? round($amount * 100) : round($this->cart->getOrderTotal(true) * 100);
    }

    /**
     * checks is brithdate format valid
     * && is not 0000-00-00
     *
     * @param string $birthdate
     * @return boolean
     */
    public function isBirthdateValid($birthdate)
    {
        if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/", $birthdate, $matches))
        {
            return (bool) ($matches[2] != '00' && $matches[3] != '00');
        }
        return false;
    }


}