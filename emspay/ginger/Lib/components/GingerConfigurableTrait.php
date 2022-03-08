<?php

namespace Lib\components;

use Lib\interfaces\GingerCountryValidation;
use Lib\interfaces\GingerIPValidation;

trait GingerConfigurableTrait
{
    public $_postErrors;

    public function getContent()
    {

        if (\Tools::isSubmit('btnSubmit')) {

            $this->postValidation();

            if (isset($this->_postErrors))
            {
                foreach ($this->_postErrors as $error) $this->_html .= $this->displayError($error);
            }

            $this->postProcess();

        } else {
            $this->_html .= '<br />';
        }

        $this->_html .= $this->displayginger();
        $this->_html .= $this->renderForm();

        return $this->_html;

    }

    private function postProcess()
    {

        $templateForVariable =  'GINGER_'.strtoupper(str_replace('-','',$this->method_id));
        if (\Tools::isSubmit('btnSubmit'))
        {
            if ($this instanceof GingerIPValidation)
            {
                \Configuration::updateValue($templateForVariable.'_SHOW_FOR_IP', trim(\Tools::getValue($templateForVariable.'_SHOW_FOR_IP')));
            }

            if ($this instanceof GingerCountryValidation)
            {
                \Configuration::updateValue($templateForVariable.'_COUNTRY_ACCESS', trim(\Tools::getValue($templateForVariable.'_COUNTRY_ACCESS')));
            }

            if ($this->method_id == GingerBankConfig::BANK_PREFIX)
            {
                \Configuration::updateValue('GINGER_API_KEY', trim(\Tools::getValue('GINGER_API_KEY')));
                \Configuration::updateValue('GINGER_KLARNAPAYLATER_TEST_API_KEY', trim(\Tools::getValue('GINGER_KLARNAPAYLATER_TEST_API_KEY')));
                \Configuration::updateValue('GINGER_AFTERPAY_TEST_API_KEY', trim(\Tools::getValue('GINGER_AFTERPAY_TEST_API_KEY')));
                \Configuration::updateValue('GINGER_BUNDLE_CA', \Tools::getValue('GINGER_BUNDLE_CA'));

                $this->cacheCurrencyList();
            }
        }
        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    protected function displayginger()
    {
        return $this->display(__PS_BASE_URI__.'modules/' .$this->name.'/'.$this->name.'.php', 'infos.tpl');
    }

    protected function postValidation()
    {
        if (\Tools::isSubmit('btnSubmit'))
        {
            if (!\Configuration::get('GINGER_API_KEY') && $this->name != GingerBankConfig::BANK_PREFIX)
            {
                $this->_postErrors[] = $this->l('API key should be set.');
            }
        }
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l(GingerBankConfig::BANK_LABEL.' Settings'),
                    'icon' => 'icon-envelope'
                ),
                'input' => ($this->method_id == GingerBankConfig::BANK_PREFIX) ? $this->getLibraryFields() : $this->getPaymentMethodsFields(),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new \HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new \Language((int) \Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int) \Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = \Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => ($this->method_id == GingerBankConfig::BANK_PREFIX) ? $this->getLibraryFieldsValue() : $this->getPaymentMethodsFieldsValue(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));

    }

    public function getLibraryFields()
    {
        return [
            [
                'type' => 'checkbox',
                'name' => 'GINGER',
                'desc' => $this->l('Resolves issue when curl.cacert path is not set in PHP.ini'),
                'values' => array(
                    'query' => array(
                        array(
                            'id' => 'BUNDLE_CA',
                            'name' => $this->l('Use cURL CA bundle'),
                            'val' => '1'
                        ),
                    ),
                    'id' => 'id',
                    'name' => 'name'
                )
            ],
            [
                'type' => 'text',
                'label' => $this->l('API Key'),
                'name' => 'GINGER_API_KEY',
                'required' => true
            ],
            [
                'type' => 'text',
                'label' => $this->l('Klarna Test API Key'),
                'name' => 'GINGER_KLARNAPAYLATER_TEST_API_KEY',
                'required' => false,
                'desc' => $this->l('The Test API Key is Applicable only for Klarna. Remove when not used.')
            ],
            [
                'type' => 'text',
                'label' => $this->l('Afterpay Test API Key'),
                'name' => 'GINGER_AFTERPAY_TEST_API_KEY',
                'required' => false,
                'desc' => $this->l('The Test API Key is Applicable only for Afterpay. Remove when not used.')
            ],
        ];
    }

    public function getPaymentMethodsFields()
    {
        $countryAccessValidationVar = 'GINGER_'.strtoupper(str_replace('-','',$this->method_id)).'_COUNTRY_ACCESS';
        $ipValidationVar = 'GINGER_'.strtoupper(str_replace('-','',$this->method_id)).'_SHOW_FOR_IP';

        return [
            ($this instanceof GingerIPValidation) ? [
                'type' => 'text',
                'label' => $this->l('IP address(es) for testing.'),
                'name' => $ipValidationVar,
                'required' => true,
                'desc' => $this->l('You can specify specific IP addresses for which '.GingerBankConfig::GINGER_BANK_LABELS[$this->method_id].' is visible, for example if you want to test '.GingerBankConfig::GINGER_BANK_LABELS[$this->method_id].' you can type IP addresses as 128.0.0.1, 255.255.255.255. If you fill in nothing, then, '.GingerBankConfig::GINGER_BANK_LABELS[$this->method_id].' is visible to all IP addresses.'),

            ] : null,
            ($this instanceof GingerCountryValidation) ? [
                'type' => 'text',
                'label' => $this->l('Countries available for '.GingerBankConfig::GINGER_BANK_LABELS[$this->method_id].'.'),
                'name' => $countryAccessValidationVar,
                'required' => true,
                'desc' => $this->l('To allow '.GingerBankConfig::GINGER_BANK_LABELS[$this->method_id].' to be used for any other country just add its country code (in ISO 2 standard) to the "Countries available for '.GingerBankConfig::GINGER_BANK_LABELS[$this->method_id].'" field. Example: BE, NL, FR If field is empty then '.GingerBankConfig::GINGER_BANK_LABELS[$this->method_id].' will be available for all countries.'),
            ] : null,
        ];
    }

    public function getLibraryFieldsValue()
    {
        return [
            'GINGER_API_KEY' => \Tools::getValue('GINGER_API_KEY', \Configuration::get('GINGER_API_KEY')),
            'GINGER_KLARNAPAYLATER_TEST_API_KEY' => \Tools::getValue('GINGER_KLARNAPAYLATER_TEST_API_KEY', \Configuration::get('GINGER_KLARNAPAYLATER_TEST_API_KEY')),
            'GINGER_AFTERPAY_TEST_API_KEY' => \Tools::getValue('GINGER_AFTERPAY_TEST_API_KEY', \Configuration::get('GINGER_AFTERPAY_TEST_API_KEY')),
            'GINGER_BUNDLE_CA' => \Tools::getValue('GINGER_BUNDLE_CA', \Configuration::get('GINGER_BUNDLE_CA')),
        ];
    }
    public function getPaymentMethodsFieldsValue()
    {
        $countryAccessValidationVar = 'GINGER_'.strtoupper(str_replace('-','',$this->method_id)).'_COUNTRY_ACCESS';
        $ipValidationVar = 'GINGER_'.strtoupper(str_replace('-','',$this->method_id)).'_SHOW_FOR_IP';

        return [
            $ipValidationVar => ($this instanceof GingerIPValidation) ? \Tools::getValue($ipValidationVar, \Configuration::get($ipValidationVar)) : null,
            $countryAccessValidationVar => ($this instanceof GingerCountryValidation) ? \Tools::getValue($countryAccessValidationVar, \Configuration::get($countryAccessValidationVar)) : null
        ];
    }

}
