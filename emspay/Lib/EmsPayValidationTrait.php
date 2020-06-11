<?php

namespace Lib;

use Ginger\Ginger;

/**
 * Description of TraitValidation
 *
 * @author bojan
 */
trait EmsPayValidationTrait
{
    public function postProcess()
    {
        $cart_id = \Tools::getValue('id_cart');

        try {
            $orderStatus = $this->getOrderStatus(\Tools::getValue('order_id'));
        } catch (\Exception $e) {
            $this->context->smarty->assign(
                array(
                'template' => _PS_THEME_DIR_ . 'templates/page.tpl',
                'checkout_url' => $this->context->link->getPagelink('order'),
                'error_message' => $e->getMessage()
                    )
            );
            $this->setTemplate('module:emspay/views/templates/front/error.tpl');
            return;
        }
            
        if (\Tools::getValue('processing')) {
            $this->checkStatusAjax();
        }
            
        switch ($orderStatus) {
            case 'completed':
            case 'accepted':
                
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
                \Tools::redirect(
                        __PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' . $cart_id
                    . '&id_module=' . $this->module->id . '&id_order=' . \Order::getOrderByCartId(intval($cart_id))
                            . '&key=' . $this->context->customer->secure_key
                );
                break;
            case 'processing':
                if (isset($cart_id)) {
                    \Tools::redirect($this->context->link->getModuleLink(
                        'emspay',
                        'processing',
                        [
                            'order_id' => \Tools::getValue('order_id'),
                            'id_cart' => $cart_id,
                        ])
                    );
                };
                break;
            case 'new':
            case 'cancelled':
            case 'expired':
            case 'error':

                $this->context->smarty->assign(
                    array(
                    'template' => _PS_THEME_DIR_ . 'templates/page.tpl',
                    'checkout_url' => $this->context->link->getPagelink('order'),
                    'shop_name' => \Configuration::get('PS_SHOP_NAME')
                        )
                );
                $this->setTemplate('module:emspay/views/templates/front/error.tpl');
                break;
            default:
                die("Should not happen");
        }
    }

    /**
     * @param string $orderId
     * @return null|string
     */
    public function getOrderStatus($orderId)
    {
        $ginger = Ginger::createClient(
		      Helper::GINGER_ENDPOINT,
		      \Configuration::get('EMS_PAY_APIKEY'),
		      (null !== \Configuration::get('EMS_PAY_BUNDLE_CA')) ?
			    [
				  CURLOPT_CAINFO => Helper::getCaCertPath()
			    ] : []
	  	      );
	  $ginger_order = $ginger->getOrder($orderId);

        return $ginger_order['status'];
    }
            
}
