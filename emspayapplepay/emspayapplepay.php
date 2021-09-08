<?php

use Lib\banktwins\GingerBankGateway;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ginger/vendor/autoload.php');

class emspayapplepay extends GingerBankGateway
{
    public function __construct()
    {
        $this->name = 'emspayapplepay';
	    $this->method_id = 'apple-pay';
        parent::__construct();
    }
}
