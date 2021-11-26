<?php

use Lib\banktwins\GingerBankGateway;
use Lib\components\GingerInstallTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(\_PS_MODULE_DIR_ . 'bank_title/ginger/vendor/autoload.php');

class emspaycreditcard extends GingerBankGateway
{
    use GingerInstallTrait;

    public function __construct()
    {
        $this->name = 'emspaycreditcard';
	    $this->method_id = 'credit-card';
        parent::__construct();
    }
}
