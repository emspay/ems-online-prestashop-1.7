<?php

$composer = require_once(_PS_MODULE_DIR_ . '/ingpsp/ing-php/vendor/autoload.php');

if (is_object($composer)) {
    $composer->addPsr4("Lib\\", _PS_MODULE_DIR_ . '/ingpsp/Lib/');
    $composer->addPsr4("Model\\", _PS_MODULE_DIR_ . '/ingpsp/model/');
    $composer->loadClass(_PS_MODULE_DIR_ . '/ingpsp/ingpsp.php');
} else {
    require_once(_PS_MODULE_DIR_ . '/ingpsp/ingpsp.php');
    spl_autoload_register(function ($class) {
        $file = _PS_MODULE_DIR_.'ingpsp/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });
}