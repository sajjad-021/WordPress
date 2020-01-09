=== PickPlugins Invoice for WooCommerce ===
	Contributors: pickplugins
	Donate link: http://pickplugins.com
	Tags:  WooCommerce Invoices, PDF invoices, WooCommerce PDF Invoices
	Requires at least: 4.1
	Tested up to: 5.2
	Stable tag: 1.0.2
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html

	Send invoices to your WooCommerce customers

== Description ==

No more tense with Invoice. This plugin will send invoices directly to your customer's email as an attachment automatically when ever you like. Besides the invoices can be preview and download any time.

### PickPlugins Invoice for WooCommerce by [http://pickplugins.com](http://pickplugins.com)


* [Support !&raquo; ](https://www.pickplugins.com/support/?ref=wordpress.org)
* [Documentation! &raquo; ](https://www.pickplugins.com/documentation/woocommerce-invoice/?ref=wordpress.org)


# Plugin Features

* Shop info with Logo
* Send invoice automatically as email attachment of completed order and others order status.
* Custom invoice template by filter hook, you can generate your own invoice template.
* User can download invoice from order list under my account page.
* User can preview invoice from order list under my account page.
* Admin can download invoice from order list page.


# Shortcodes

<strong>Order Invoice</strong>

Create a page first and the paste the following shortcode to display the invoice for specific order. You just need to pass the `order_id="1232"` in the shortcode. You can also pass any specific template for the output, like `template="flat"`

`[order-invoice order_id="X" template="XXXXX"]`


# Filter Hooks
<strong>wooin_invoice_templates_internal</strong>

With this filter hook, you can easily add new Templates. But you just need to place it inside `wp-content/plugins/woo-invoice/templates/invoice-templates/my-template` Each template should have minimum 3 files.

`* my-template.png
 * style.css
 * template.php`



`    // Add New template
     function add_my_template( $templates ){

         $templates['my-template'] = __( 'My Template', 'text-domain' );

         return $templates;
     }
     add_filter( 'wooin_invoice_templates_internal', 'add_my_template' );`


<strong>wooin_invoice_templates</strong>

Now imagine that you want to add a custom template from external source, not from that specific directory, then you can do that easily with this filter hook.

   `	// Add New template from other directory
        function add_custom_template( $templates ){

            $templates[ 'custom-template' ] = array(
            	'label' 		=> __( 'Custom Template', 'text-domain' ),
                'thumb' 		=> 'URL of the thumbnail source',
                'stylesheet' 	=> 'Stylesheet of the template',
                'template' 		=> 'Directory of the template file',
            );
        	return $templates;
        }
        add_filter( 'wooin_invoice_templates', 'add_custom_template' );`

# Translation

Please find the 'en.po' for default translation file under 'languages' folder and add your own translation. you can also contribute in translation, please contact us http://www.pickplugins.com/contact/




== Installation ==

1. Install as regular WordPress plugin.<br />
2. Go your plugin setting via WordPress Dashboard and find "<strong>PickPlugins Invoice for WooCommerce</strong>" activate it.<br />


== Screenshots ==

1. Screenshot 1
2. Screenshot 2
3. Screenshot 3



== Changelog ==



= 1.0.2 =
* 17/04/2019 add - translation added for Lithuanian

= 1.0.1 =
* 13/04/2019 add - added two new template
* 13/04/2019 add - compatible for translation


= 1.0.0 =
* 22/05/2018 Initial release
