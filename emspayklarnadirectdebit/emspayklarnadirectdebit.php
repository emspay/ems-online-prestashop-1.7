<?php

use Lib\banktwins\GingerBankGateway;
use Lib\components\GingerOrderLinesTrait;
use Lib\interfaces\GingerCapturable;
use Lib\interfaces\GingerOrderLines;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ginger/vendor/autoload.php');


class emspayKlarnaDirectDebit extends GingerBankGateway implements GingerCapturable, GingerOrderLines
{
    use GingerOrderLinesTrait;
    public function __construct()
    {
        $this->name = 'emspayklarnadirectdebit';
	    $this->method_id = 'klarna-direct-debit';
        $this->useDemoApiKey = true;
        parent::__construct();
    }
}
