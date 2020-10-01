<?php

require_once(_PS_MODULE_DIR_ . '/emspay/vendor/autoload.php');

class emspayTikkiePaymentRequestValidationModuleFrontController extends ModuleFrontController
{
    use Lib\EmsPayValidationTrait;
}
