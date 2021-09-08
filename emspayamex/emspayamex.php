<?php

use Lib\banktwins\GingerBankGateway;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ginger/vendor/autoload.php');

class emspayAmex extends GingerBankGateway
{
    public function __construct()
    {
        $this->name = 'emspayamex';
        $this->method_id = 'amex';
        parent::__construct();
    }
}
