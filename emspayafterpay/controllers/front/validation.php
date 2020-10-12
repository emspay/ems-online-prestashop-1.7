<?php

use Ginger\Ginger;
use Lib\Helper;

require_once(_PS_MODULE_DIR_ . '/emspay/vendor/autoload.php');

class emspayafterpayValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        try {
            switch ($this->getOrderStatus()) {
                case 'completed':
                case 'accepted':
                    $this->processCompletedStatus();
                    break;
                case 'cancelled':
                    $this->processCancelledStatus();
                    break;
                case 'processing':
                case 'new':
                case 'expired':
                case 'error':
                    $this->processErrorStatus();
                    break;
                default:
                    die("Should not happen");
            }
        } catch (\Exception $e) {
            $this->handlePostProcessException();
            return;
        }
    }

    private function handlePostProcessException()
    {
        $this->context->smarty->assign(
            [
                'template' => _PS_THEME_DIR_ . 'templates/page.tpl',
                'checkout_url' => $this->context->link->getPagelink('order'),
                'error_message' => $e->getMessage()
            ]
        );
        $this->setTemplate('module:emspay/views/templates/front/error.tpl');
    }
    
    private function processCompletedStatus()
    {
        $this->validateOrder();
        $this->doRedirectToConfirmationPage();
    }
    
    /**
     * Method validates Presta order
     *
     * @param int $cart_id
     */
    private function validateOrder()
    {
        $cart_id = (int) \Tools::getValue('id_cart');
        $order = \Order::getOrderByCartId((int)($cart_id));
                
        if (isset($cart_id) && empty($order)) { // order has not been created yet (by webhook)
            $cart = $this->context->cart;
            $customer = new \Customer($cart->id_customer);
            $total = (float) $cart->getOrderTotal(true, \Cart::BOTH);
            $currency = $this->context->currency;
            $this->module->validateOrder(
                    $cart_id,
                    \Configuration::get('PS_OS_PAYMENT'),
                    $total,
                    $this->module->displayName,
                    null,
                    [],
                    (int) $currency->id,
                    false,
                    $customer->secure_key
            );
            $order = \Order::getOrderByCartId((int)($cart_id));
            if (isset($order) && is_numeric($order)) {
                $this->module->updateOrderId($cart_id, $order);
            }
        }
    }
    
    private function doRedirectToConfirmationPage()
    {
        $cart_id = (int) \Tools::getValue('id_cart');
        \Tools::redirect(
                __PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' . $cart_id
            . '&id_module=' . $this->module->id . '&id_order=' . \Order::getOrderByCartId(intval($cart_id))
                    . '&key=' . $this->context->customer->secure_key
        );
    }
    
    
    private function processCancelledStatus()
    {
        $this->context->smarty->assign(
            [
                'checkout_url' => $this->context->link->getPagelink('order'),
                'template' => _PS_THEME_DIR_ . 'templates/page.tpl'
            ]
        );
        $this->setTemplate('module:'.$this->module->name.'/views/templates/front/cancelled.tpl');
    }
    
    private function processErrorStatus()
    {
        $this->context->smarty->assign(
            [
                'template' => _PS_THEME_DIR_ . 'templates/page.tpl',
                'checkout_url' => $this->context->link->getPagelink('order'),
                'shop_name' => \Configuration::get('PS_SHOP_NAME')
            ]
        );
        $this->setTemplate('module:emspay/views/templates/front/error.tpl');
    }
    
    /**
     * @param string $orderId
     * @return null|string
     */
    public function getOrderStatus()
    {
        $apiKey = \Configuration::get('EMS_PAY_AFTERPAY_APIKEY_TEST') ?: \Configuration::get('EMS_PAY_APIKEY');
        $ginger = Ginger::createClient(
		  	Helper::GINGER_ENDPOINT,
		  	$apiKey,
		  	(null !== \Configuration::get('EMS_PAY_BUNDLE_CA')) ?
			    [
				  CURLOPT_CAINFO => Helper::getCaCertPath()
			    ] : []
	  		);
	  $ginger_order = $ginger->getOrder(\Tools::getValue('order_id'));

        return $ginger_order['status'];
    }
}
