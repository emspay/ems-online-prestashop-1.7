<?php

require_once(_PS_MODULE_DIR_ . '/emspay/vendor/autoload.php');

class emspayCreditcardPaymentModuleFrontController extends ModuleFrontController
{

    public $ssl = true;
    public $display_column_left = false;

    use Lib\EmsPayPaymentModuleFrontControllerTrait;
}
