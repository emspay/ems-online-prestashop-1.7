<?php

use Lib\banktwins\GingerBankGateway;
use Lib\components\GingerInstallTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(\_PS_MODULE_DIR_ . 'emspay/ginger/vendor/autoload.php');

class emspayideal extends GingerBankGateway
{
    use GingerInstallTrait;

    public function __construct()
    {
        $this->name = 'emspayideal';
	    $this->method_id = 'ideal';
        parent::__construct();
    }

}
