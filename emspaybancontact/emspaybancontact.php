<?php

use Lib\banktwins\GingerBankGateway;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ginger/vendor/autoload.php');

class emspaybancontact extends GingerBankGateway
{
    public function __construct()
    {
        $this->name = 'emspaybancontact';
	    $this->method_id = 'bancontact';

        parent::__construct();
    }
}
