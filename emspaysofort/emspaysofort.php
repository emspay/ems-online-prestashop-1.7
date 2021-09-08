<?php

use Lib\banktwins\GingerBankGateway;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ginger/vendor/autoload.php');

class emspaysofort extends GingerBankGateway
{
    public function __construct()
    {
        $this->name = 'emspaysofort';
	    $this->method_id = 'sofort';
        parent::__construct();
    }
}
