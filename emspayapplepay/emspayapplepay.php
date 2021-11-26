<?php

use Lib\banktwins\GingerBankGateway;
use Lib\components\GingerInstallTrait;
use Lib\interfaces\GingerCustomFieldsOnCheckout;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(\_PS_MODULE_DIR_ . 'bank_title/ginger/vendor/autoload.php');

class emspayapplepay extends GingerBankGateway implements GingerCustomFieldsOnCheckout
{
    use GingerInstallTrait;
    public function __construct()
    {
        $this->name = 'emspayapplepay';
	    $this->method_id = 'apple-pay';
        parent::__construct();
    }
}
