<?php

use Lib\banktwins\GingerBankGateway;
use Lib\interfaces\GingerCapturable;
use Lib\components\GingerInstallTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(\_PS_MODULE_DIR_ . 'bank_title/ginger/vendor/autoload.php');


class emspayKlarnaDirectDebit extends GingerBankGateway implements GingerCapturable
{
    use GingerInstallTrait;
    public function __construct()
    {
        $this->name = 'emspayklarnadirectdebit';
	    $this->method_id = 'klarna-direct-debit';
        $this->useDemoApiKey = true;
        parent::__construct();
    }
}
