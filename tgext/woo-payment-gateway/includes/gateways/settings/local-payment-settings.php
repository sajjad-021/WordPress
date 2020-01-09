<?php
return array( 
		'title' => array( 'type' => 'description', 
				'description' => $this->get_gateway_supports_description () 
		), 
		'enabled' => array( 
				'title' => __ ( 'Enabled', 'woo-payment-gateway' ), 
				'type' => 'checkbox', 'default' => 'no', 
				'value' => 'yes', 'desc_tip' => false, 
				'description' => sprintf ( __ ( 'If enabled, your site can accept %s through Braintree.', 'woo-payment-gateway' ), $this->default_title ) 
		), 
		'general_settings' => array( 'type' => 'title', 
				'title' => __ ( 'General Settings', 'woo-payment-gateway' ), 
				'description' => sprintf ( __ ( 'General Settings for the %s gateway.', 'woo-payment-gateway' ), $this->get_title () ) 
		), 
		'title_text' => array( 'type' => 'text', 
				'title' => __ ( 'Title Text', 'woo-payment-gateway' ), 
				'value' => '', 
				'default' => $this->default_title, 
				'class' => '', 'desc_tip' => true, 
				'description' => __ ( 'The title text is the text that will be displayed next to the gateway.', 'woo-payment-gateway' ) 
		),
		'description' => array( 'type' => 'text',
				'title' => __ ( 'Description', 'woo-payment-gateway' ),
				'default' => '', 'desc_tip' => true,
				'description' => __ ( 'Description that appears on your checkout page when the gateway is selected. Leave blank if you don\'t want any text to show.', 'woo-payment-gateway' )
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
		) 
);