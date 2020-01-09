<?php
/**
 * @version 3.0.0
 * @package Braintree/Templates
 */
$gateway->output_line_items();
?>
<div class="wc-braintree-applepay-product-checkout-container">
	<?php
	wc_braintree_get_template ( 'applepay-button.php', array( 
			'gateway' => $gateway, 
			'button' => $gateway->get_option ( 'button' ), 
			'type' => $gateway->get_option ( 'button_type_product' ) 
	) )?>
</div>