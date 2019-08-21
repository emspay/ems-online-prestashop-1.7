<?php
use Lib\GingerClientFactory;
use Lib\GingerClientFactoryParams;

require_once(_PS_MODULE_DIR_ . '/emspay/emspay_module_bootstrap.php');

class emspayBanktransferValidationModuleFrontController extends ModuleFrontController
{

    public function postProcess() {
        
        $ginger = GingerClientFactory::create(
                new GingerClientFactoryParams(
                        'emspay',
                        \Configuration::get('EMS_PAY_APIKEY'),
                        \Configuration::get('EMS_PAY_BUNDLE_CA')
                        )
                );

        $ginger_order_status = $ginger->getOrder(Tools::getValue('order_id'))->getStatus();
        $cart_id = Tools::getValue('id_cart');

        switch ($ginger_order_status) {
            case 'completed':
                if (isset($cart_id)) {
                    Tools::redirect(__PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' . $cart_id . '&id_module=' . $this->module->id . '&id_order=' . Order::getOrderByCartId(intval($cart_id)) . '&key=' . $this->context->customer->secure_key);
                }
                break;
            case 'processing':
                if (isset($cart_id)) {
                    Tools::redirect(__PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' . $cart_id . '&id_module=' . $this->module->id . '&id_order=' . Order::getOrderByCartId(intval($cart_id)) . '&key=' . $this->context->customer->secure_key);
                }
                break;
            case 'cancelled':
            case 'expired':
            case 'error':
                Tools::redirect('order');
                break;
            default:
                die("Should not happen");
        }

        Tools::redirect('index.php?controller=order-confirmation&id_cart=' . Tools::getValue('id_cart') . '&id_module=' . Tools::getValue('id_module'));
    }

}