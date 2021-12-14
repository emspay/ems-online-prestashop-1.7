<?php

use Lib\banktwins\GingerBankGateway;
use Lib\components\GingerBankConfig;
use Lib\components\GingerConfigurableTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(\_PS_MODULE_DIR_ . 'emspay/ginger/vendor/autoload.php');

class emspay extends GingerBankGateway
{

    use GingerConfigurableTrait;
    public $extra_mail_vars;

    public function __construct()
    {
        $this->name = GingerBankConfig::BANK_PREFIX;
        $this->method_id = GingerBankConfig::BANK_PREFIX;
        parent::__construct();
    }

}