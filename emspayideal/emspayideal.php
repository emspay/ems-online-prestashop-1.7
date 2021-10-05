<?php

use Lib\banktwins\GingerBankGateway;
use Lib\interfaces\GingerCustomFieldsOnCheckout;
use Lib\interfaces\GingerIssuers;
use Lib\components\GingerInstallTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . '/ginger/vendor/autoload.php');

class emspayideal extends GingerBankGateway implements GingerIssuers, GingerCustomFieldsOnCheckout
{
    use GingerInstallTrait;

    public function __construct()
    {
        $this->name = 'emspayideal';
	    $this->method_id = 'ideal';
        parent::__construct();
    }

    /**
     * get a list of IDEAL issuers
     *
     * @return array
     */
    public function _getIssuers()
    {
        try {
            return $this->gingerClient->getIdealIssuers();
        } catch (\Exception $e) {
            $this->context->controller->errors[] = $this->l($e->getMessage());
            return [];
        }
    }

}
