<?php
require_once(_PS_MODULE_DIR_ . '/emspay/vendor/autoload.php');

class emspayWeChatValidationModuleFrontController extends ModuleFrontController
{
     use Lib\EmsPayValidationTrait;
}
