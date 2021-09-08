<?php


use Lib\banktwins\GingerBankGateway;
use Lib\components\GingerConfigurableTrait;
use Lib\components\GingerOrderLinesTrait;
use Lib\interfaces\GingerCapturable;
use Lib\interfaces\GingerCountryValidation;
use Lib\interfaces\GingerCustomFieldsOnCheckout;
use Lib\interfaces\GingerIPValidation;
use Lib\interfaces\GingerOrderLines;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ginger/vendor/autoload.php');


class emspayafterpay extends GingerBankGateway implements
    GingerCountryValidation,
    GingerOrderLines,
    GingerIPValidation,
    GingerCapturable,
    GingerCustomFieldsOnCheckout
{
    const TERMS_CONDITION_URL_NL = 'https://www.afterpay.nl/nl/algemeen/betalen-met-afterpay/betalingsvoorwaarden';
    const TERMS_CONDITION_URL_BE = 'https://www.afterpay.be/be/footer/betalen-met-afterpay/betalingsvoorwaarden';
    const BE_ISO_CODE = 'BE';
    
    protected $allowedLocales = ['NL', 'BE'];

    use GingerConfigurableTrait, GingerOrderLinesTrait;

    public function __construct()
    {
        $this->name = 'emspayafterpay';
	    $this->method_id = 'afterpay';
        $this->useDemoApiKey = true;
        parent::__construct();
    }

}
