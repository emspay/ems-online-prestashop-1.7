<?php

require_once(_PS_MODULE_DIR_ . '/ingpsp/ingpsp_module_bootstrap.php');

class ingpspPayconiqValidationModuleFrontController extends ModuleFrontController 
{

    use Lib\IngPspValidationTrait;

}
