=== Braintree For WooCommerce ===
Contributors: Payment Plugins
Donate link: 
Tags: braintree, braintree gateway, braintree plugin, braintree payments, payment processing, woocommerce, payment gateway, 3DS, 3D-Secure, 3D Secure, threeDSecure, woocommerce subscriptions, payment gateways, paypal, subscriptions, braintree subscriptions, payment forms, wordpress payments, v.zero, saq a
Requires at least: 3.0.1
Tested up to: 5.3
Stable tag: 3.1.1
Copyright: Payment Plugins
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Accept Credit Cards, PayPal, PayPal Credit, Google Pay, ApplePay, Venmo, and Local Payments like iDEAL all in one plugin for free!

= Official Partner Of Braintree =
Payment Plugins is an official partner of Braintree & PayPal and has worked closely with them to develop this solution.

= Boost conversion by offering product and cart page checkout =
Braintree for WooCommerce is made to supercharge your conversion rate by decreasing payment friction for your customer.
Offer PayPal, Google Pay, Apple Pay on product pages, cart pages, and at the top of your checkout page.

= Features =
- Google Pay
- Apple Pay
- PayPal & PayPal Credit
- Venmo
- Credit Cards
- 3D Secure 2.0 & 1.0
- iDEAL, P24, SEPA, WeChat, Giropay, & more
- SAQ A PCI Compliant
- Beautifully designed forms
- Create your own custom form
- Integrates with Woocommerce
- Integrates with Woocommerce Subscriptions 2.0.0+
- Offer subscriptions without the WooCommerce Subscription plugin
- Integrations with WooCommerce currency switchers
- Add Custom fees
- Void transactions
- Automatic settlement or authorize transactions
- Issue refunds
- Dynamic descriptors

= Support =
For more information or questions, please email <a href=”mailto:support@paymentplugins.com”>support@paymentplugins.com</a> or read through our detailed <a target="_blank" href="https://docs.paymentplugins.com/wc-braintree/config/#/">documentation</a>.

= Developer Docs = 
Need to customize the plugin? It's easy using our documentation. [Code Examples](https://docs.paymentplugins.com/wc-braintree/config/#/code_examples) && [Developer Docs](https://docs.paymentplugins.com/wc-braintree/api/)

== Frequently Asked Questions ==

= Do you have an documentation? =
Yes, we have [Configuration docs](https://docs.paymentplugins.com/wc-braintree/config/#/) and [Developer docs](https://docs.paymentplugins.com/wc-braintree/api/)

= How do I test the plugin? = 
To test the plugin, all you have to do is create a [Braintree Sandbox](https://www.braintreepayments.com/sandbox) account and [Configure the plugin](https://docs.paymentplugins.com/wc-braintree/config/#/braintree_api).

= Does your plugin support mulit-currency shops? = 
Yes, our plugin supports shops that sell in multiple currencies. It's easy to setup! [Read more here](https://docs.paymentplugins.com/wc-braintree/config/#/braintree_advanced?id=merchant-accounts)

= Does your plugin support 3DS 2.0? = 
Yes, this plugin supports 3DS 2.0.

= Why is my card processing as 3DS 1.0? = 
Some card providers have not switched over to 3DS 2.0 yet so Braintree processes the transaction as 3DS 1.0. This isn't anything to be alarmed
about and is expected behavior.

== Screenshots ==
1. Product page showing one click checkout with PayPal, Google Pay, and Apple Pay
2. Cart page showing one click checkout
3. Checkout page showing Google Pay selected
4. Credit card form which has been filled out
5. PayPal popup for selecting shipping method
6. Settings page

== Changelog ==
= 3.1.1 = 
* Updated  - Braintree JS to 3.56.0
* Fixed - Place order button not re-appearing when PayPal clicked then local method.
= 3.1.0 = 
* Added - WC 3.8.1
* Added - Pop-ups message for local payments when browser blocks pop-up.
* Added - Hook added after subscription payment method update.
= 3.0.9 =
* Updated - Braintree JS to 3.55.0
* Added - Gateway description option
* Added - Merchants can now add the Apple domain association file using the plugin
* Fixed - Used for variation option not showing in WC 3.7+ when Braintree variable subscription selected
= 3.0.8 =
* Updated - Braintree JS to 3.54.2
* Updated - Improved local payments logic.
* Updated - Google Pay paymentDatacallbacks updated
* Added - Kount status logic
= 3.0.7 = 
* Updated - Always return instance of token in WC_Braintree_Payment_Gateway::get_token() even if token doesn't exist. This prevents exceptions when data doesn't exist in tokens table.
* Added - WC Pre-Order check to see if order contains a pre-order. Previously only checked if a pre-order required tokenization.
* Added - Polyfill for old browsers (IE11 etc) that don't support Promises.
= 3.0.6 = 
* Updated - Braintree JS to 3.53.0
* Added - action in add_payment_method function so plugins can alter behavior before payment method save.
* Fixed - Dropin form message "please fill out payment form" that happens occasionally on checkout page load.
* Fixed - Truncate item description to less than 127 characters when adding line items to transaction.
* Fixed - Error that appears when 3DS enabled and cart total is zero due to subscription with a trial period.
* Added - PayPal addressOverride logic so returning customers will see their address in PayPal popup.
* Added - Pre Order support for payments on a product that occur in the future.
= 3.0.5 = 
* Updated - Braintree JS 3.52.1
* Updated - update-3.0.4.php file directory list check added. Some merchant sites don't have permissions setup properly so check for directory before update.
* Updated - Check for existance of shipping fields when verifying 3DS so undefined values aren't returned.
* Updated - Shop manager permission added to order actions like void, capture, view transaction popup.
* Fixed - Place Order button not re-appearing if credit card gateway not selected first.
= 3.0.4 = 
* Updated - Braintree JS 3.52.0
* Updated - Braintree vault ID check added to add_payment_method function.
* Updated - Address null check added to add_payment_method function
* Updated - Plugin text domain changed to woo-payment-gateway. Update attemps to change all translations that use braintree-payments to woo-payment-gateway.
* Added - Browser locale detection added for PayPal smartbutton
* Updated - PayPal will show customer shipping address in pop-up if already entered.
= 3.0.3 = 
* Added - Order pay line items in Google and Apple Pay payment sheets.
* Fixed - Spelling errors corrected
* Fixed - PayPal popup error on order pay page
* Fixed - Apple Pay invalid state validation for countries without states
* Updated - Updated subscription functionality so billing frequency always returns a number to prevent DateInterval exceptions when subscriptions haven't been configured 100%.
= 3.0.2 = 
* Fixed - Error caused by fees script when fees are enabled.
* Updated - If dynamic pricing is not enabled for Google Pay, the customer is directed to the checkout page and prompted to review their order. All billing and shipping fields are pre-populated.
* Updated - JS script version to 3.51.0.
* Added - EPS, Bancontact, Giropay, SEPA, WeChat, MyBank, Sofort
= 3.0.1 = 
* Added - PayPal to product page
* Added - Google Pay to product and cart page
* Added - Google Pay dynamic price (shows line items in payment sheet)
* Added - iDEAL, P24
* Updated - Subscription functionality
* Updated - All JS scripts
* Updates - Client token API for improved speed
* Removed - Donation functionality removed. To continue to use donations, download version 2.6.65 and do not upgrade.
= 2.6.65 = 
* Updated - Script version to 3.50.0
* Updated - Non ASCII chars replaced for 3DS 2.0
= 2.6.64 = 
* Fixed - Apple Pay variable product conflict resolved.
* Updated - Script version to 3.48.0
* Added - 3.0.0 Release candidate notice
* Added - PayPal locale in button render function