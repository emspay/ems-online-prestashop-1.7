<?php
namespace Lib\components;


class GingerBankConfig
{

    const GINGER_BANK_LABELS = [
        'emspay' => 'Library',
        'klarna-pay-later' => 'Klarna Pay Later',
        'klarna-pay-now' => 'Klarna Pay Now',
        'paynow' => 'Pay Now',
        'apple-pay' => 'Apple Pay',
        'ideal' => 'iDEAL',
        'afterpay' => 'Afterpay',
        'amex' => 'American Express',
        'bancontact' => 'Bancontact',
        'bank-transfer' => 'Bank Transfer',
        'credit-card' => 'Credit Card',
        'paypal' => 'PayPal',
        'payconiq' => 'Payconiq',
        'sofort' => 'Sofort',
        'klarna-direct-debit' => 'Klarna Direct Debit',
        'google-pay' => 'Google Pay',
        'swish' => 'Swish',
        'mobilepay' => 'MobilePay',
        'giropay' => 'GiroPay',
        'viacash' => 'ViaCash'
    ];

    const PLUGIN_NAME = 'emspay-online-prestashop-1.7';
    const BANK_LABEL = 'EMS Online';
    const BANK_PREFIX = 'emspay';
    const GINGER_BANK_ENDPOINT = 'https://api.online.emspay.eu';

}