<?php

use Lib\banktwins\GingerBankGateway;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ginger/vendor/autoload.php');

class emspaycreditcard extends GingerBankGateway
{
    public function __construct()
    {
        $this->name = 'emspaycreditcard';
	    $this->method_id = 'credit-card';
        parent::__construct();
    }
}
