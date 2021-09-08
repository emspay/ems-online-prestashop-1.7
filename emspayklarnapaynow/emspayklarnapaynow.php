<?php

use Lib\banktwins\GingerBankGateway;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ginger/vendor/autoload.php');

class emspayklarnapaynow extends GingerBankGateway
{
    public function __construct()
    {
        $this->name = 'emspayklarnapaynow';
	    $this->method_id = 'klarna-pay-now';
        parent::__construct();
    }

}
