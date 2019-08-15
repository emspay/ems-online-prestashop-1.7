<?php

namespace Lib;

/**
 * Description of Helper
 *
 * @author bojan
 */
class Helper
{
    public static function getAmountInCents($amount)
    {
        return (int) round($amount * 100);
    }
    
    /**
     * @param type $array
     * @return array
     */
    public static function getArrayWithoutNullValues($array)
    {
        return array_values(
                \GingerPayments\Payment\Common\ArrayFunctions::withoutNullValues(
                    array_unique($array)
                )
            );
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
}
