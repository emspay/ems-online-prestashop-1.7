# EMS Online plugin for Prestashop 1.7.x
This is the offical EMS Online plugin.
 
EMS Online Payment Solution
 
* The ideal online payment page for your webshop.
* Entirely in the style of your website, making transactions less likely to be terminated.
* Working in the cloud, you won’t be bothered by updates.
* Download your reports in the formats CAMT.053, MT940, MT940S & CODA.
* Receive your VAT invoices via the 24/7 available online reporting tool.
* See : https://emspay.nl/en/payment-solutions/online-payments

## About

EMS helps entrepreneurs with the best, smartest and most efficient payment systems. Both 
in your physical store and online in your webshop. With a wide range of payment methods 
you can serve every customer.

Why EMS?

Via the EMS website you can create a free test account online 24/7 and try out the online 
payment solution. EMS's online solution also offers the option of sending payment links and 
accepting QR payments.

The ideal online payment page for your webshop:
- Free test account - available online 24/7
- Wide range of payment methods
- Easy integration via a plug-in or API
- Free shopping cart plug-ins
- Payment page in the look & feel of your webshop
- Reports in the formats CAMT.053, MT940S, MT940 & CODA
- One clear dashboard for all your payment, turnover data and administration functions

Promotion promotion extended!

Choose the EMS Online Payment Solution now
and pay no subscription costs at € 9.95 throughout 2020!

Start immediately with your test account
Request it https://portal.emspay.eu/create-test-account?language=NL_NL 

Satisfied after testing?
Click on the yellow button [Begin→]
 in the test portal and
simply request your live account.

## Version number
Version 1.4.6


## Pre-requisites to install the plug-ins: 
- PHP v5.4 and above
- MySQL v5.4 and above

## Installation
Manual installation of the PrestaShop 1.7 plugin using (s)FTP

1. Upload all of the folders in the ZIP file into the Modules folder of your PrestaShop installation (no files are transferred).
You can use an sFTP or SCP program, for example, to upload the files. There are various sFTP clients that you can download free of charge from the internet, such as WinSCP or Filezilla.
2. Go to your PrestaShop admin environment. Click ‘Improve' > 'Modules' > 'Module Catalog’ and search for EMS Online.
3. You will see several modules to be installed. Start with ‘EMS Online’. Click Install / Proceed with the installation.
After installation, the module will move to Improve' > 'Modules' > 'Module Manager’
4. Configure the EMS Online module
- Enable the cURL CA bundle option.
This fixes a cURL SSL Certificate issue that appears in some web-hosting environments where you do not have access to the PHP.ini file and therefore are not able to update server certificates.
- Copy the API key
- Are you offering Afterpay on your pay page? In that case copy the API Key of your test webshop in the Afterpay Test API Key.
When your Afterpay application was approved an extra test webshop was created for you to use in your test with Afterpay. The name of this webshop starts with ‘TEST Afterpay’.
- Are you offering Klarna on your pay page? In that case copy the API Key of your test webshop in the Klarna Test API Key field.
When your Klarna application was approved an extra test webshop was created for you to use in your test with Klarna. The name of this webshop starts with ‘TEST Klarna’.

5. After you have installed the ‘EMS Online´ module, you can install the other modules you would like to offer in your webshop.
Enable only those payment methods that you applied for and for which you have received a confirmation from us.

Note that if a payment method has no specific configuration to be done apart from the ones in the generic configuration, the only option shown on the panel will be “Disable”/”Enable”.
The “configure” option is only shown in case the payment method has further configuration e.g. Klarna with IP Filtering.

6. Afterpay / Klarna specific configuration
For the payment method Afterpay / Klarna you can choose to offer it only to a limited set of whitelisted IP addresses. You can use this for instance when you are in the testing phase and want to make sure that Afterpay / Klarna is not available yet for your customers.
To do this click on the “Configure” button of EMS Online Afterpay or EMS Online Klarna in the payment method overview.

Enter the IP addresses that you want to whitelist, separate the addresses by a comma (“,”). The payment method Afterpay / Klarna will only be presented to customers who use a whitelisted IP address.
If you want to offer Afterpay / Klarna to all your customers, you can leave the field empty.

Only for AfterPay payment: To allow AfterPay to be used for any other country just add its country code (in ISO 2 standard) to the "Countries available for AfterPay" field. Example: BE, NL, FR

7. Once the modules are installed you can offer the payment methods in your webshop.
8. Compatibility: PrestaShop 1.7

Manual installation of the PrestaShop 1.7 plugin using Admin Panel

1.  In the admin panel go IMPROVE->Module Manager.
2.  Push "Upload a module".
3.  Drag and drop the "emspay.zip" or push the "select file" button and choose it. Wait for the installation process will be completed.
4.  Find the "EMS Online" at the bottom of the page in the "Other" section and push the "Configure" button in it.
5.  Fill the "API Key" field and push the "Save" button.
6.  Push the "Back" button in the top or go IMPROVE->Module Manager.
7.  Push "Upload a module".
8.  Drag and drop one by one all other archives with payment methods that you need.
