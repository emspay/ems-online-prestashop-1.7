<?php
require_once(_PS_MODULE_DIR_ . '/emspay/vendor/autoload.php');

class emspayCreditcardValidationModuleFrontController extends ModuleFrontController
{
     use Lib\EmsPayValidationTrait;
}
