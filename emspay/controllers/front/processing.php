<?php

use Ginger\Ginger;
use Lib\Helper;

class emspayProcessingModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        
        if (Tools::getValue('processing')) {
            $this->checkStatusAjax();
        }
        
        $this->context->smarty->assign(
            [
                'fallback_url' => $this->context->link->getModuleLink('emspay', 'pending'),
                'template' => _PS_THEME_DIR_ . 'templates/page.tpl',
                'modules_dir' => _MODULE_DIR_,
                'checkout_url' => $this->context->link->getPagelink('order'),
                'validation_url' => $this->getValidationUrl()
            ]
        );
        $this->setTemplate('module:emspay/views/templates/front/processing.tpl');
    }
    
    protected function getValidationUrl()
    {
        return $this->context->link->getModuleLink(
            'emspayideal',
            'validation',
            [
                'id_cart' => \Tools::getValue('id_cart'),
                'id_module' => $this->module->id,
                'order_id' => \Tools::getValue('order_id')
            ]
        );
    }
    
    /**
      * @param string $orderId
      * @return null|string
      */
    public function checkOrderStatus()
    {
        $ginger = Ginger::createClient(
		  	Helper::GINGER_ENDPOINT,
		  	\Configuration::get('EMS_PAY_APIKEY'),
		  	(null !== \Configuration::get('EMS_PAY_BUNDLE_CA')) ?
			    [
				  CURLOPT_CAINFO => Helper::getCaCertPath()
			    ] : []
	  		);

        return $ginger->getOrder(\Tools::getValue('order_id'))->getStatus();
    }

    /**
     * Method prepares Ajax response for processing page
     */
    public function checkStatusAjax()
    {
        $orderStatus = $this->checkOrderStatus();

        if ($orderStatus == 'processing') {
            $response = [
                'status' => $orderStatus,
                'redirect' => false
            ];
        } else {
            $response = [
                'status' => $orderStatus,
                'redirect' => true
            ];
        }

        die(json_encode($response));
    }
}
