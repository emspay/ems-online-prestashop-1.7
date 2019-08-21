<?php

require_once(_PS_MODULE_DIR_ . '/emspay/emspay_module_bootstrap.php');

class emspayidealValidationModuleFrontController extends ModuleFrontController
{
    use Lib\EmsPayValidationTrait;
}
