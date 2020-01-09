<?php
return array( 
		'title' => array( 'type' => 'description', 
				'class' => 'wc-braintree-googlepay-desc', 
				'description' => '<p><a target="_blank" href="https://services.google.com/fb/forms/googlepayAPIenable/">' . __ ( 'Google Pay Request', 'woo-payment-gateway' ) . '</a>. ' . __ ( 'When you submit your request for Google Pay, request to be whitelisted for callbackintents. This ensures that the order items are displayed on the Google Payment sheet.', 'woo-payment-gateway' ) . '</p>' . __ ( 'To have the Google API team approve your integration you can enable sandbox mode and Google Pay. When sandbox mode is enabled, Google Pay will work, allowing you to capture the necessary screenshots the Google API team needs to approve your Merchant ID request.', 'woo-payment-gateway' ) 
		), 
		'enabled' => array( 
				'title' => __ ( 'Enabled', 'woo-payment-gateway' ), 
				'type' => 'checkbox', 'default' => 'no', 
				'value' => 'yes', 'desc_tip' => true, 
				'description' => __ ( 'If enabled, your site can accept Google Pay.', 'woo-payment-gateway' ) 
		), 
		'general_settings' => array( 'type' => 'title', 
				'title' => __ ( 'General Settings', 'woo-payment-gateway' ), 
				'description' => __ ( 'General Settings for the Google Pay gateway.', 'woo-payment-gateway' ) 
		), 
		'dynamic_price' => array( 
				'title' => 'Dynamic Price', 
				'type' => 'checkbox', 'default' => 'yes', 
				'description' => __ ( 'With dynamic pricing, order totals will be present on the Google payment sheet and your customers can select shipping address and shipping methods. We highly recommend you leave this option enabled for user experience and conversion rate. You will need to ask the Google API team to whitelist you for this functionality.', 'woo-payment-gateway' ) 
		), 
		'merchant_id' => array( 
				'title' => __ ( 'Google Merchant ID', 'woo-payment-gateway' ), 
				'type' => 'text', 'default' => '', 
				'description' => __ ( 'Once you have been approved by Google to accept payments you will be issued a merchant ID. Enter that merchant ID in this field. A merchant ID is not needed when Sandbox mode is enabled. This will allow you to go through the Google Pay approval process.', 'woo-payment-gateway' ) 
		), 
		'title_text' => array( 'type' => 'text', 
				'title' => __ ( 'Title Text', 'woo-payment-gateway' ), 
				'value' => '', 
				'default' => __ ( 'Google Pay', 'woo-payment-gateway' ), 
				'class' => '', 'desc_tip' => true, 
				'description' => __ ( 'The title text is the text that will be displayed next to the gateway.', 'woo-payment-gateway' ) 
		),
		'description' => array( 'type' => 'text',
				'title' => __ ( 'Description', 'woo-payment-gateway' ),
				'default' => '', 'desc_tip' => true,
				'description' => __ ( 'Description that appears on your checkout page when the gateway is selected. Leave blank if you don\'t want any text to show.', 'woo-payment-gateway' )
		),
		'sections' => array( 'type' => 'multiselect', 
				'title' => __ ( 'Pages Enabled On' ), 
				'default' => array( 'cart' 
				), 'class' => 'wc-enhanced-select', 
				'options' => array( 
						'cart' => __ ( 'Cart page', 'woo-payment-gateway' ), 
						'product' => __ ( 'Product page', 'woo-payment-gateway' ), 
						'checkout_banner' => __ ( 'Top of checkout page', 'woo-payment-gateway' ) 
				), 
				'description' => __ ( 'These are the sections that Google Pay can be enabled on. The checkout page is enabled by default if the gateway is enabled. Increase your conversion rate by including Google Pay on product pages and the cart page.', 'woo-payment-gateway' ) 
		), 
		'icon' => array( 'type' => 'select', 
				'title' => __ ( 'Gateway Icon', 'woo-payment-gateway' ), 
				'class' => 'wc-enhanced-select', 
				'options' => array( 
						'google_pay_standard' => __ ( 'Standard', 'woo-payment-gateway' ), 
						'google_pay_outline' => __ ( 'Black Outline', 'woo-payment-gateway' ) 
				), 'default' => 'google_pay_outline', 
				'description' => __ ( 'Google Pay icon that appears on the checkout page.', 'woo-payment-gateway' ) 
		), 
		'method_format' => array( 
				'title' => __ ( 'Credit Card Display', 'woo-payment-gateway' ), 
				'type' => 'select', 
				'class' => 'wc-enhanced-select', 
				'options' => wp_list_pluck ( $this->get_payment_method_formats (), 'example' ), 
				'value' => '', 'default' => 'type_ending_in', 
				'desc_tip' => true, 
				'description' => __ ( 'This option allows you to customize how the credit card will display for your customers on orders, subscriptions, etc.' ) 
		), 
		'order_prefix' => array( 'type' => 'text', 
				'title' => __ ( 'Order Prefix', 'woo-payment-gateway' ), 
				'value' => '', 'default' => '', 
				'class' => '', 'desc_tip' => true, 
				'description' => __ ( 'The order prefix is prepended to the WooCommerce order id and will appear within Braintree as the Order ID. This settings can be helpful if you want to distinguish
						orders that came from this particular site, plugin, or gateway.', 'woo-payment-gateway' ) 
		), 
		'order_suffix' => array( 'type' => 'text', 
				'title' => __ ( 'Order Suffix', 'woo-payment-gateway' ), 
				'value' => '', 'default' => '', 
				'class' => '', 'desc_tip' => true, 
				'description' => __ ( 'The order suffix is appended to the WooCommerce order id and will appear within Braintree as the Order ID. This settings can be helpful if you want to distinguish
						orders that came from this particular site, plugin, or gateway.', 'woo-payment-gateway' ) 
		), 
		'order_status' => array( 'type' => 'select', 
				'title' => __ ( 'Order Status', 'woo-payment-gateway' ), 
				'default' => 'default', 
				'class' => 'wc-enhanced-select', 
				'options' => array_merge ( array( 
						'default' => __ ( 'Default', 'woo-payment-gateway' ) 
				), wc_get_order_statuses () ), 
				'tool_tip' => true, 
				'description' => __ ( 'This is the status of the order once payment is complete. If <b>Default</b> is selected, then WooCommerce will set the order status automatically based on internal logic which states if a product is virtual and downloadable then status is set to complete. Products that require shipping are set to Processing. Default is the recommended setting as it allows standard WooCommerce code to process the order status.', 'woo-payment-gateway' ) 
		), 
		'charge_type' => array( 'type' => 'select', 
				'title' => __ ( 'Transaction Type', 'woo-payment-gateway' ), 
				'default' => 'capture', 
				'class' => 'wc-enhanced-select', 
				'options' => array( 
						'capture' => __ ( 'Capture', 'woo-payment-gateway' ), 
						'authorize' => __ ( 'Authorize', 'woo-payment-gateway' ) 
				), 
				'description' => __ ( 'If set to capture, funds will be captured immediately during checkout. Authorized transactions put a hold on the customer\'s funds but
						no payment is taken until the charge is captured. Authorized charges can be captured on the Admin Order page.', 'woo-payment-gateway' ) 
		), 
		'button' => array( 'type' => 'title', 
				'title' => __ ( 'Button Design', 'woo-payment-gateway' ) 
		), 
		'button_color' => array( 
				'title' => __ ( 'Button Color', 'woo-payment-gateway' ), 
				'type' => 'select', 'default' => 'default', 
				'class' => 'wc-braintree-button-option wc-braintree-button-color wc-enhanced-select', 
				'options' => array( 
						'default' => __ ( 'Default', 'woo-payment-gateway' ), 
						'black' => __ ( 'Black', 'woo-payment-gateway' ), 
						'white' => __ ( 'White', 'woo-payment-gateway' ) 
				), 'desc_tip' => true, 
				'description' => __ ( 'The color of the Google Pay button on the checkout page.', 'woo-payment-gateway' ) 
		), 
		'button_type' => array( 
				'title' => __ ( 'Button Type', 'woo-payment-gateway' ), 
				'type' => 'select', 'default' => 'long', 
				'class' => 'wc-braintree-button-option wc-braintree-button-type wc-enhanced-select', 
				'options' => array( 
						'long' => __ ( 'Long', 'woo-payment-gateway' ), 
						'short' => __ ( 'Short', 'woo-payment-gateway' ) 
				), 'tool_tip' => true, 
				'description' => __ ( 'The type of Google Pay button on the checkout page.', 'woo-payment-gateway' ) 
		), 
		'button_demo' => array( 
				'title' => __ ( 'Button Demo', 'woo-payment-gateway' ), 
				'type' => 'button_demo', 
				'description' => __ ( 'If no button demo appears then the device you are using does not support Google Pay. Try viewing your settings on a different device.', 'woo-payment-gateway' ), 
				'id' => 'wc-braintree-button-demo' 
		) 
);
