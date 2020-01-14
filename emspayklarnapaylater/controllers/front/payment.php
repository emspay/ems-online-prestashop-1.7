<?php

require_once(_PS_MODULE_DIR_ . '/emspay/emspay_module_bootstrap.php');

class emspayKlarnaPayLaterPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    public function initContent() 
    {
       parent::initContent();

        $errorMessage = $this->module->execPayment(
                $this->context->cart, $this->_getWebshopLocale()
        );

        if ($errorMessage) {
            $this->context->smarty->assign('checkout_url', 
                    $this->context->link->getPagelink('order')
            );
            $this->context->smarty->assign('error_message', $errorMessage);
            $this->context->smarty->assign('template', _PS_THEME_DIR_ . 'templates/page.tpl');
            $this->setTemplate('module:emspay/views/templates/front/error.tpl');
        }
     
    }

    /**
     * @return string
     */
    protected function _getWebshopLocale() 
    {
        if ($this->context->language) {
            // Current language
            $language = $this->context->language->iso_code;
        } else {
            // Default locale language
            $language = \Configuration::get('PS_LOCALE_LANGUAGE');
        }
        return strtolower($language) . '_' . strtoupper(\Configuration::get('PS_LOCALE_COUNTRY'));
    }
}
