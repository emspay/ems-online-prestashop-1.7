<?php

use Lib\banktwins\GingerBankGateway;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ginger/vendor/autoload.php');

class emspaygooglepay extends GingerBankGateway
{
    public function __construct()
    {
        $this->name = 'emspaygooglepay';
	    $this->method_id = 'google-pay';
        parent::__construct();
    }
}
