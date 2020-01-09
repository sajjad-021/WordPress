<?php
/**
 * @version 3.0.0
 * @package Braintree/Templates
 */
?>
<div class="simple-form">
	<div class="form-group">
		<label><?php _e('Card Number', 'woo-payment-gateway')?></label>
		<div id="wc-braintree-card-number"
			data-placeholder="<?php _e('Card Number', 'woo-payment-gateway')?>"
			class="hosted-field">
			<span class="wc-braintree-card-type"></span>
		</div>
	</div>
	<div class="form-group">
		<label><?php _e('Exp Date', 'woo-payment-gateway')?></label>
		<div id="wc-braintree-expiration-date"
			data-placeholder="<?php _e('MM / YY', 'woo-payment-gateway')?>"
			class="hosted-field"></div>
	</div>
	<div class="form-group cvv-container">
		<label><?php _e('CVV', 'woo-payment-gateway')?></label>
		<div id="wc-braintree-cvv"
			data-placeholder="<?php _e('CVV', 'woo-payment-gateway')?>"
			class="hosted-field"></div>
	</div>
	<?php if($gateway->is_postal_code_enabled()):?>
	<div class="form-group postalCode-container">
		<label><?php _e('Postal Code', 'woo-payment-gateway')?></label>
		<div id="wc-braintree-postal-code"
			data-placeholder="<?php _e('Postal Code', 'woo-payment-gateway')?>"
			class="hosted-field"></div>
	</div>
	<?php endif?>
	<?php if(wc_braintree_save_cc_enabled()):?>
	<div class="form-group">
		<label><?php _e('Save', 'woo-payment-gateway')?></label>
		<div class="hosted-field save-card-field">
			<input type="checkbox" id="<?php echo $gateway->save_method_key?>"
				name="<?php echo $gateway->save_method_key?>"> <label class="wc-braintree-save-label"
				for="<?php echo $gateway->save_method_key?>"></label>
		</div>
	</div>
	<?php endif;?>
	<?php
	
if ($gateway->should_display_street()) :
		$checkout = WC()->checkout();
		?>
	<div class="form-group streetAddress">
		<label><?php _e('Street Address', 'woocommerce')?></label>
		<div class="hosted-field">
			<input type="text" id="billing_address_1" name="billing_address_1"
				placeholder="<?php _e('Street Address', 'woocommerce')?>"
				value="<?php echo $checkout->get_value('billing_address_1')?>" />
		</div>
	</div>
	<?php endif;?>
</div>