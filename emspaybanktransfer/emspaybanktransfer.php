<?php

use Lib\banktwins\GingerBankGateway;
use Lib\interfaces\GingerIdentificationPay;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ginger/vendor/autoload.php');

class emspaybanktransfer extends GingerBankGateway implements GingerIdentificationPay
{
    public function __construct()
    {
        $this->name = 'emspaybanktransfer';
	    $this->method_id = 'bank-transfer';
        parent::__construct();
    }
}
