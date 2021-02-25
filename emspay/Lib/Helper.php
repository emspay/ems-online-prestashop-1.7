<?php

namespace Lib;

/**
 * Description of Helper
 *
 * @author bojan
 */
class Helper
{
    /**
     * GINGER_ENDPOINT used for create Ginger client
     */
    const GINGER_ENDPOINT = 'https://api.online.emspay.eu';

    const PHYSICAL = 'physical';
    const SHIPPING_FEE = 'shipping_fee';

    /**
     * @var array
     */
    public static $afterPayCountries = ['NL', 'BE'];

    public static function getAmountInCents($amount)
    {
        return (int) round($amount * 100);
    }
    
    /**
     * @param array $array
     * @return array
     */
    public static function getArrayWithoutNullValues($array)
    {
	  static $fn = __FUNCTION__;

	  foreach (array_unique($array) as $key => $value) {
		if (is_array($value)) {
		    $array[$key] = self::$fn($array[$key]);
		}

		if (empty($array[$key]) && $array[$key] !== '0' && $array[$key] !== 0) {
		    unset($array[$key]);
		}
	  }

	  return $array;
    }
    
    /**
     * checks is brithdate format valid
     * && is not 0000-00-00
     *
     * @param string $birthdate
     * @return boolean
     */
    public static function isBirthdateValid($birthdate)
    {
        if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/", $birthdate, $matches)) {
            return (bool) ($matches[2] != '00' && $matches[3] != '00');
        }
        return false;
    }

    /**
     * Get CA certificate path
     *
     * @return bool|string
     */
    public static function getCaCertPath(){
	  return realpath(_PS_MODULE_DIR_ . '/emspay/assets/cacert.pem');
    }

    /**
     * Convert int data in order to string
     * @param array $orderData
     * @return array
     */
    public static function orderDataToString($orderData)
    {
	  $orderData["order_lines"][0]["vat_percentage"]=(int)$orderData["order_lines"][0]["vat_percentage"];
	  $orderData["order_lines"][0]["amount"]=(int)$orderData["order_lines"][0]["amount"];
	  $orderData["order_lines"][0]["quantity"]=(int)$orderData["order_lines"][0]["quantity"];

	  return $orderData;
    }
    /**
     * @return string
     */
    public static function getPaymentCurrency()
    {
        return 'EUR';
    }
}
