<?php
require_once(_PS_MODULE_DIR_ . '/ingpsp/ingpsp_module_bootstrap.php');

class ingpspbancontactValidationModuleFrontController extends ModuleFrontController 
{
    
     use Lib\IngPspValidationTrait;
    
}
