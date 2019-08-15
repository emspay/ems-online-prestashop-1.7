<?php

use Lib\GingerClientFactory;
use Lib\GingerClientFactoryParams;

class ingpspProcessingModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        
        if (Tools::getValue('processing')) {
            $this->checkStatusAjax();
        }
        
        $this->context->smarty->assign(
            [
                'fallback_url' => $this->context->link->getModuleLink('ingpsp', 'pending'),
                'template' => _PS_THEME_DIR_ . 'templates/page.tpl',
                'modules_dir' => _MODULE_DIR_,
                'checkout_url' => $this->context->link->getPagelink('order'),
                'validation_url' => $this->getValidationUrl()
            ]
        );
        $this->setTemplate('module:ingpsp/views/templates/front/processing.tpl');
    }
    
    protected function getValidationUrl()
    {
        return $this->context->link->getModuleLink(
            'ingpspideal',
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
        $ginger = GingerClientFactory::create(
                    new GingerClientFactoryParams(
                            'ingpsp', 
                            \Configuration::get('ING_PSP_APIKEY'), 
                            \Configuration::get('ING_PSP_PRODUCT'), 
                            \Configuration::get('ING_PSP_BUNDLE_CA')
                    )
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
