<?php
/**
 * @version 3.0.0
 * @package Braintree/Templates
 */
?>
<div class="wc-braintree-cart-gateways-container">
	<form id="wc-braintree-cart-fields-form" method="post">
		<?php do_action('wc_braintree_cart_form_fields')?>
	<?php wc_get_template( 'checkout/terms.php' ); ?>
		<ul class="wc_braintree_cart_gateways">
		<?php foreach($gateways as $gateway){?>
			<li class="wc_braintree_cart_gateway wc_braintree_cart_gateway_<?php echo esc_attr($gateway->id)?>">
				<?php $gateway->cart_fields();?>
			</li>
		<?php }?>
			<li class="wc-braintree-cart-text">&mdash;&nbsp;<?php _e('or', 'woo-payment-gateway')?>&nbsp;&mdash;</li>
		</ul>
	</form>
</div>