<?php


use Lib\banktwins\GingerBankGateway;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ginger/vendor/autoload.php');


class emspayPayconiq extends GingerBankGateway
{
    public function __construct()
    {
        $this->name = 'emspaypayconiq';
        $this->method_id = 'payconiq';
        parent::__construct();
    }
}
