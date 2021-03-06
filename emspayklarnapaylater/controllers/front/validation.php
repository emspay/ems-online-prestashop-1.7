<?php

use Ginger\Ginger;
use Lib\Helper;

require_once(_PS_MODULE_DIR_ . '/emspay/vendor/autoload.php');

class emspayKlarnaPayLaterValidationModuleFrontController extends ModuleFrontController
{

    public function postProcess() 
    {
        $apiKey = Configuration::get('EMS_PAY_APIKEY_TEST') ?: Configuration::get('EMS_PAY_APIKEY');
        $ginger = Ginger::createClient(
		  	Helper::GINGER_ENDPOINT,
		  	$apiKey,
		  	(null !== \Configuration::get('EMS_PAY_BUNDLE_CA')) ?
			    [
				  CURLOPT_CAINFO => Helper::getCaCertPath()
			    ] : []
	  		);
	  $ginger_order = $ginger->getOrder(Tools::getValue('order_id'));
        $ginger_order_status = $ginger_order['status'];
        $cart_id = Tools::getValue('id_cart');

        switch ($ginger_order_status) {
            case 'processing':
            case 'new':
            case 'completed':
                if (isset($cart_id)) {
                    Tools::redirect(
                            __PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' . $cart_id
                            . '&id_module=' . $this->module->id
                            . '&id_order=' . Order::getOrderByCartId(intval($cart_id))
                            . '&key=' . $this->context->customer->secure_key
                    );
                }
                break;
            case 'cancelled':
            case 'expired':
            case 'error':
                $this->context->smarty->assign(array(
                    'template' => _PS_THEME_DIR_ . 'templates/page.tpl',
                    'checkout_url' => $this->context->link->getPagelink('order')
                        )
                );
                $this->setTemplate('module:emspay/views/templates/front/error.tpl');
                break;
            default:
                die("Should not happen");
        }
    }

}
