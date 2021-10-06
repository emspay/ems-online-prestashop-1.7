<?php
namespace Lib\components;

trait GingerInstallTrait
{
    public function install()
    {
        if (!\Module::isInstalled(GingerBankConfig::BANK_PREFIX))
        {
            throw new \PrestaShopException('The '.GingerBankConfig::BANK_PREFIX.' extension is not installed, please install the '.GingerBankConfig::BANK_PREFIX.' extension first and then the current extension.');
        }

        if (!\Configuration::get('GINGER_API_KEY'))
        {
            throw new \PrestaShopException('The webshop API key is missing in the '.GingerBankConfig::BANK_PREFIX.' extension. Please add the API Key in the '.GingerBankConfig::BANK_PREFIX.' extension, save it & then re-install this extension.');
        }
        return parent::install();
    }
}