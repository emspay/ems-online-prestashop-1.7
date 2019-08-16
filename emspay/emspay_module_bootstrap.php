<?php

$composer = require_once(_PS_MODULE_DIR_ . '/emspay/ems-php/vendor/autoload.php');

if (is_object($composer)) {
    $composer->addPsr4("Lib\\", _PS_MODULE_DIR_ . '/emspay/Lib/');
    $composer->addPsr4("Model\\", _PS_MODULE_DIR_ . '/emspay/model/');
    $composer->loadClass(_PS_MODULE_DIR_ . '/emspay/emspay.php');
} else {
    require_once(_PS_MODULE_DIR_ . '/emspay/emspay.php');
    spl_autoload_register(function ($class) {
        $file = _PS_MODULE_DIR_.'emspay/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });
}