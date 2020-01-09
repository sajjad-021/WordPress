<?php
return array( 
		'title' => array( 'type' => 'description', 
				'description' => '<div class="wc-braintree-paypal-instructions"><p><a target="_blank" href="https://docs.paymentplugins.com/wc-braintree/config/#/paypal_config?id=setup">' . __ ( 'How To Setup & Test PayPal', 'woo-payment-gateway' ) . '</a>' . '<p><a target="_blank" href="https://docs.paymentplugins.com/wc-braintree/config/#/paypal_config?id=require-billing-phone">' . __ ( 'Require Billing Phone', 'woo-payment-gateway' ) . '</a>' . '<p><a target="_blank" href="https://docs.paymentplugins.com/wc-braintree/config/#/paypal_config?id=enable-billing-address">' . __ ( 'Enable PayPal Billing Address', 'woo-payment-gateway' ) . '</a></div>' 
		), 
		'enabled' => array( 
				'title' => __ ( 'Enabled', 'woo-payment-gateway' ), 
				'type' => 'checkbox', 'default' => 'no', 
				'value' => 'yes', 'desc_tip' => true, 
				'description' => __ ( 'If enabled, your site can accept PayPal through Braintree.', 'woo-payment-gateway' ) 
		), 
		'general_settings' => array( 'type' => 'title', 
				'title' => __ ( 'General Settings', 'woo-payment-gateway' ), 
				'description' => __ ( 'General Settings for the credit card gateway.', 'woo-payment-gateway' ) 
		), 
		'title_text' => array( 'type' => 'text', 
				'title' => __ ( 'Title Text', 'woo-payment-gateway' ), 
				'value' => '', 
				'default' => __ ( 'PayPal', 'woo-payment-gateway' ), 
				'class' => '', 'desc_tip' => true, 
				'description' => __ ( 'The title text is the text that will be displayed next to the gateway.', 'woo-payment-gateway' ) 
		),
		'description' => array( 'type' => 'text',
				'title' => __ ( 'Description', 'woo-payment-gateway' ),
				'default' => '', 'desc_tip' => true,
				'description' => __ ( 'Description that appears on your checkout page when the gateway is selected. Leave blank if you don\'t want any text to show.', 'woo-payment-gateway' )
		),
		'sections' => array( 'type' => 'multiselect', 
				'title' => __ ( 'Sections', 'woo-payment-gateway' ), 
				'class' => 'wc-enhanced-select', 
				'default' => array( 'cart' 
				), 
				'options' => array( 
						'cart' => __ ( 'Cart page', 'woo-payment-gateway' ), 
						'product' => __ ( 'Product Page', 'woo-payment-gateway' ), 
						'checkout_banner' => __ ( 'Top of checkout page', 'woo-payment-gateway' ) 
				), 
				'description' => __ ( 'PayPal can be enabled on the cart page, product pages, or the top of the checkout page. This functionality is possible because PayPal data can be used to auto populate WooCommerce billing and shipping fields. <a href="https://support.paymentplugins.com/hc/en-us/articles/360015278633-PayPal-Billing-Address" target="_blank">Enable PayPal Billing Address</a>', 'woo-payment-gateway' ) 
		), 
		'method_format' => array( 
				'title' => __ ( 'PayPal Display', 'woo-payment-gateway' ), 
				'type' => 'select', 
				'class' => 'wc-enhanced-select', 
				'options' => wp_list_pluck ( $this->get_payment_method_formats (), 'example' ), 
				'value' => '', 'default' => 'email', 
				'desc_tip' => true, 
				'description' => __ ( 'This option allows you to customize how PayPal accounts display for your customers on orders, subscriptions, etc.' ) 
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
				'class' => 'wc-enhanced-select', 
				'title' => __ ( 'Transaction Type', 'woo-payment-gateway' ), 
				'default' => 'capture', 
				'options' => array( 
						'capture' => __ ( 'Capture', 'woo-payment-gateway' ), 
						'authorize' => __ ( 'Authorize', 'woo-payment-gateway' ) 
				), 
				'description' => __ ( 'If set to capture, funds will be captured immediately during checkout. Authorized transactions put a hold on the customer\'s funds but
						no payment is taken until the charge is captured. Authorized charges can be captured on the Admin Order page.', 'woo-payment-gateway' ) 
		), 
		'billing_agreement' => array( 'type' => 'textarea', 
				'title' => __ ( 'Billing Agreement Description', 'woo-payment-gateway' ), 
				'default' => sprintf ( __ ( 'Purchase agreement from %s.', 'woo-payment-gateway' ), get_bloginfo ( 'name' ) ), 
				'value' => '', 
				'description' => __ ( 'The billing agreement description appears on your customer\'s PayPal account and gives information about the company they have granted authorization to. This is a good way to prevent
								customers from cancelling recurring billing authorizations because they are unsure who they granted access to.', 'woo-payment-gateway' ), 
				'desc_tip' => true 
		), 
		'display_name' => array( 
				'title' => __ ( 'Display Name', 'woo-payment-gateway' ), 
				'type' => 'text', 
				'default' => get_option ( 'blogname' ), 
				'desc_tip' => true, 
				'description' => __ ( 'This is the business name that is displayed on the PayPal popup.', 'woo-payment-gateway' ) 
		), 
		/* 'submit_form' => array( 'type' => 'checkbox', 
				'title' => __ ( 'Submit Form Automatically', 'woo-payment-gateway' ), 
				'default' => 'no', 'value' => 'yes', 
				'desc_tip' => true, 
				'description' => __ ( 'If enabled, the checkout form will be submitted once the customer finishes the PayPal flow and the customer won\'t need to click the Place Order button.', 'woo-payment-gateway' ) 
		), */ 
		'smartbutton' => array( 'type' => 'title', 
				'title' => __ ( 'Smartbutton Checkout Page Design', 'woo-payment-gateway' ), 
				'description' => __ ( 'Smartbuttons are designed to improve customer conversion on eCommerce platforms. You can control the look and feel of the Checkout page buttons using these settings. Pages like the Cart have a specific design for conversion purposes
						and can only be changed using filters.', 'woo-payment-gateway' ) 
		), 
		'locale' => array( 
				'title' => __ ( 'Default Locale', 'woo-payment-gateway' ), 
				'type' => 'select', 
				'class' => 'wc-enhanced-select', 
				'options' => $this->get_supported_locales (), 
				'default' => 'en_US', 
				'description' => __ ( 'This is the default locale of the PayPal button and this option shows all 34 supported locales. PayPal uses this along with other parameters like shipping address and browser locale to determine which language to show the user.', 'woo-payment-gateway' ), 
				'desc_tip' => true 
		), 
		'smartbutton_size' => array( 'type' => 'select', 
				'title' => __ ( 'Button Size', 'woo-payment-gateway' ), 
				'class' => 'wc-braintree-smartbutton-size wc-enhanced-select', 
				'default' => 'responsive', 
				'options' => array( 
						'responsive' => __ ( 'Responsive', 'woo-payment-gateway' ), 
						'small' => __ ( 'Small', 'woo-payment-gateway' ), 
						'medium' => __ ( 'Medium', 'woo-payment-gateway' ), 
						'large' => __ ( 'Large', 'woo-payment-gateway' ) 
				) 
		), 
		'smartbutton_color' => array( 'type' => 'select', 
				'title' => __ ( 'Button Color', 'woo-payment-gateway' ), 
				'class' => 'wc-braintree-smartbutton-color wc-enhanced-select', 
				'default' => 'gold', 
				'options' => array( 
						'gold' => __ ( 'Gold', 'woo-payment-gateway' ), 
						'blue' => __ ( 'Blue', 'woo-payment-gateway' ), 
						'silver' => __ ( 'Silver', 'woo-payment-gateway' ), 
						'black' => __ ( 'Black', 'woo-payment-gateway' ) 
				) 
		), 
		'smartbutton_shape' => array( 'type' => 'select', 
				'title' => __ ( 'Button Shape', 'woo-payment-gateway' ), 
				'class' => 'wc-braintree-smartbutton-shape wc-enhanced-select', 
				'default' => 'pill', 
				'options' => array( 
						'pill' => __ ( 'Pill', 'woo-payment-gateway' ), 
						'rect' => __ ( 'Rectangle', 'woo-payment-gateway' ) 
				) 
		), 
		'smartbutton_layout' => array( 'type' => 'select', 
				'title' => __ ( 'Button Layout', 'woo-payment-gateway' ), 
				'class' => 'wc-braintree-smartbutton-layout wc-enhanced-select', 
				'default' => 'vertical', 
				'options' => array( 
						'horizontal' => __ ( 'Horizontal', 'woo-payment-gateway' ), 
						'vertical' => __ ( 'Vertical', 'woo-payment-gateway' ) 
				) 
		), 
		'smartbutton_cards' => array( 
				'title' => __ ( 'Card Icons', 'woo-payment-gateway' ), 
				'class' => 'wc-braintree-smartbutton ', 
				'type' => 'checkbox', 'default' => 'yes' 
		), 
		'smart_button_demo' => array( 
				'type' => 'button_demo', 
				'title' => __ ( 'Demo', 'woo-payment-gateway' ), 
				'id' => 'wc-braintree-button-demo' 
		), 
		'paypal_credit' => array( 'type' => 'title', 
				'title' => __ ( 'PayPal Credit', 'woo-payment-gateway' ) 
		), 
		'credit_enabled' => array( 'type' => 'checkbox', 
				'class' => 'wc-braintree-smartbutton', 
				'value' => 'yes', 'default' => 'no', 
				'title' => __ ( 'Enable', 'woo-payment-gateway' ), 
				'tool_tip' => true, 
				'description' => __ ( 'PayPal credit allows your customers to pay for their order over time. You receive all the funds up front and the customer makes payments on their end.', 'woo-payment-gateway' ) 
		), 
		'credit_conditions' => array( 
				'title' => __ ( 'PayPal Credit Conditions', 'woo-payment-gateway' ), 
				'type' => 'text', 'value' => '', 
				'default' => '', 
				'custom_attributes' => array( 
						'data-show-if' => array( 
								'credit_enabled' => true 
						) 
				), 'tool_tip' => true, 
				'description' => __ ( 'Enter a condition that must be met before PayPal Credit is offered as an option. E.g. {amount} > 400 AND {amount} < 1000 if the cart amount must be greater than 400 but less than 1,000. If left blank, credit will be enabled by default. <a href="https://support.paymentplugins.com/hc/en-us/articles/115002805388" target="_blank"> Conditional statement examples</a>', 'woo-payment-gateway' ) 
		) 
);
