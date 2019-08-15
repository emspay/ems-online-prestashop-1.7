<?php

require_once(_PS_MODULE_DIR_ . '/ingpsp/ingpsp_module_bootstrap.php');

class ingpspafterpayPaymentModuleFrontController extends ModuleFrontController 
{

    public $ssl = true;
    public $display_column_left = false;

    use Lib\IngPspPaymentModuleFrontControllerTrait;
 
}
