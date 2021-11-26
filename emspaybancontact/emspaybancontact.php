<?php

use Lib\banktwins\GingerBankGateway;
use Lib\components\GingerInstallTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(\_PS_MODULE_DIR_ . 'bank_title/ginger/vendor/autoload.php');

class emspaybancontact extends GingerBankGateway
{
    use GingerInstallTrait;
    public function __construct()
    {
        $this->name = 'emspaybancontact';
	    $this->method_id = 'bancontact';

        parent::__construct();
    }
}
