<?php

use Lib\banktwins\GingerBankGateway as GingerBankGatewayAlias;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ginger/vendor/autoload.php');

class emspayTikkiePaymentRequest extends GingerBankGatewayAlias
{
    public function __construct()
    {
        $this->name = 'emspaytikkiepaymentrequest';
        $this->method_id = 'tikkie-payment-request';
        parent::__construct();
    }
}