# Changelog Prestashop 1.7

** 1.0.0 **

* Initial Version

** 1.0.1 **

* Add Apple Pay
* Fix in the beberlei/assert library
* Fixed bug with field ip address

** 1.0.2 **

* Renamed Sofort to Klarna Pay Now
* Renamed Klarna to Klarna Pay Later

** 1.2.0 **

* Updated SDK
* Add American Express
* Add Tikkie Payment Request
* Add WeChat

** 1.3.0 **

* Added the ability for AfterPay to be available in the selected countries.
* Replaced locally stored ginger-php library on composer library installer.

** 1.3.1 **

* Fixed displaying "Thank you page" after success payment

** 1.3.2 **

* Removed WebHook option from all payments.
* Update plugin description.

** 1.3.3 **

* Edited status updates of orders.

** 1.3.4 **

* Added refund functionality.

** 1.4.0 **

* Refactored code to handle GPE solution.
* Unified bank labels to handle GPE solution.
* Added Bank Config class.
* Added Bank Twins for handling custom bank functionality requests.
* Implemented GitHubActions.
* Added AfterMerge & CreateOrder PHPUnit tests.
* Added Sofort, Klarna Direct Debit, Google Pay payment methods
* Implemented multi-currency
* Fixed bugs in refund&capture functionality
* Updated the extra field in an order

** 1.4.1 **

* Added possibility to install the plugin through admin panel
* Added Apple Pay detection
* Fixed bug: Error message appears when user enters API key for the first time
* Added default list of currencies with EUR
* Added OrderLines in each ginger order
* Updated current ‘can be captured’ check using capturable field from gingerOrder array
* Unified titles

** 1.4.2 **

* Added possibility to skip the intermediate page with terms of condition in AfterPay
* Removed unavailable payment methods
* Added caching the array of currency

** 1.4.3 **

* Added Swish, MobilePay, GiroPay

** 1.4.4 **

* Fixed bug: User gets an error while installing plugin

** 1.4.5 **

* Added ViaCash

** 1.4.6 **

* Updated payment method icons

** 1.4.7 **

* Removed select with ideal issuers

** 1.4.8 **

* Fixed Issue: User can’t proceed payment with Ideal Payment Method