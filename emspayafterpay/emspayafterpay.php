<?php


use Lib\banktwins\GingerBankGateway;
use Lib\components\GingerConfigurableTrait;
use Lib\components\GingerInstallTrait;
use Lib\interfaces\GingerCapturable;
use Lib\interfaces\GingerCountryValidation;
use Lib\interfaces\GingerCustomFieldsOnCheckout;
use Lib\interfaces\GingerIPValidation;
use Lib\interfaces\GingerTermsAndConditions;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(\_PS_MODULE_DIR_ . 'bank_title/ginger/vendor/autoload.php');


class emspayafterpay extends GingerBankGateway implements
    GingerCountryValidation,
    GingerIPValidation,
    GingerCapturable,
    GingerCustomFieldsOnCheckout,
    GingerTermsAndConditions
{
    const BE_ISO_CODE = 'BE';

    protected $allowedLocales = ['NL', 'BE'];

    use GingerConfigurableTrait, GingerInstallTrait;

    public function __construct()
    {
        $this->name = 'emspayafterpay';
	    $this->method_id = 'afterpay';
        $this->useDemoApiKey = true;
        parent::__construct();
    }

}
