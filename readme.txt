=== Facturare.Online ===
Contributors: itcreative
Tags: ecommerce, invoice, facturare.online
Requires at least: 4.1
Tested up to: 6.4
Stable tag: 1.2.5
Requires PHP: 5.5.0
WC requires at least: 6.9.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html


== Description ==

= Overview =

Generate invoices from your Woocommerce shop using Facturare.Online API (https://facturare.online)

= Summary of services =

For now there are 3 system components:
*Generate a fiscal or proforma invoice from admin interface, on the order page
*Cancel an invoice
*SNIF (Invoice Instant Status Notification)

After generating an invoice from your eshop, when you cancel, charge or reverse an invoice using Facturare.Online website, we will send an API notification to a predefined URL notifying your system regarding the new invoice status. This API notification includes the invoice ID and the invoice in PDF format. This way you will find the PDF invoice in your system without having to enter our online platform to download it.

= Summary of features =
*Generate an invoice (fiscal or proforma)
*Cancel an invoice
*SNIF
*Allow the client to download the invoice from his account
*Generate invoice in foreign currency, please use https://wordpress.org/plugins/woocommerce-product-price-based-on-countries for exchage rates
*more to come

== Installation ==

= Minimum Requirements =

* PHP version 5.5 or greater (PHP 7.4 or greater is recommended)
* MySQL version 5.0 or greater (MySQL 5.6 or greater is recommended)
* cURL version 7.29.0 or greater
* Woocommerce 6.9.0 or greater
* SOAP extension enabled
* cURL extension enabled
* OpenSSL version 1.0.1 or greater

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of Facturare.Online, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type Facturare.Online and click Search Plugins. Once you’ve found our payment plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading our payment plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Upgrade Notice ==
Please read the release notes for each version

== Frequently Asked Questions ==
= Where can I get support for installing and configuring the plugin? =

For help, please send an email to contact[at]itcreative[dot]ro

== Changelog ==

= 1.2.5 =
* added quick edit Facturare.Online alias for product

= 1.2.4 =
* fix custom cui and regcom fields not being sent

= 1.2.3 =
* change view invoice in admin from embed to iframe to fix safari issues

= 1.2.2 =
* Woocommerce HPOS support

= 1.2.0 =
* Woocommerce HPOS support

= 1.1.3 =
* fix meta box in admin

= 1.1.2 =
* rebranding to Facturare.Online
* fix number of decimals for product

= 1.1.1 =
* don't allow to generate receipt or charge via bank if invoice is proforma

= 1.1.0 =
* rebranding to Facturare.Online

= 1.0.18 =
* API added is invoice paid

= 1.0.17 =
* fix vat for products and shipping

= 1.0.16 =
* charge via bank added datepicker

= 1.0.15 =
* charge via bank
* fixes for invoice and bank charge final invoice status

= 1.0.14 =
* fix VAT division by 0

= 1.0.13 =
* added payment method in invoice remarks

= 1.0.12 =
* fix some VAT calculations
* added fees to invoices

= 1.0.11 =
* added setting for displaying company fields in checkout

= 1.0.10 =
* added receipt generation
* general fixes

= 1.0.9 =
* fix json schema validation

= 1.0.8 =
* fix logo, added svg

= 1.0.7 =
*added company_cui and company_regcom to user meta

= 1.0.6 =
*added alias for product name

= 1.0.5 =
*fixed shipping vat

= 1.0.4 =
*fixed translations

= 1.0.3 =
*fixed translations
*WP 5 compatible

= 1.0.2 =
*fixed translations

= 1.0.1 =
*send product sku instead of id
*updated translations

= 1.0.0 =
Initial version


== Screenshots ==
1. Facturare.Online admin setup interface
2. Facturare.Online generate invoice interface
3. Download PDF invoice from customer account
