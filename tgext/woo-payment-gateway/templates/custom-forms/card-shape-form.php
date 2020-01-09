<?php
/**
 * @version 3.0.0
 * @package Braintree/Templates
 */
?>
<div class="wc-braintree-card-form">
	<!-- <h3 class="wc-braintree-card-title">
		<?php _e('Payment Details', 'woo-payment-gateway' )?>
	</h3>-->
	<div class="wc-braintree-form-wrapper">
		<div class="wc-braintree-field-container card-field-container">
			<label><?php  echo $fields['number']['label']?></label>
			<div id="wc-braintree-card-number" class="hosted-field">
				<span class="wc-braintree-card-type"></span>
			</div>
		</div>
	</div>
	<div class="wc-braintree-form-wrapper">
		<div class="wc-braintree-field-container field-exp-month">
			<label><?php echo $fields['exp_date']['label']?></label>
			<div id="wc-braintree-expiration-month" class="hosted-field"></div>
		</div>
		<div class="wc-braintree-field-container field-year">
			<div id="wc-braintree-expiration-year" class="hosted-field"></div>
		</div>
		<div class="wc-braintree-field-container field-cvv cvv-container">
			<label><?php echo $fields['cvv']['label']?></label>
			<div id="wc-braintree-cvv" class="hosted-field"></div>
		</div>
	</div>
	<?php if($gateway->is_postal_code_enabled() || wc_braintree_save_cc_enabled()):?>
		<div class="wc-braintree-form-wrapper">
			<?php if($gateway->is_postal_code_enabled()):?>
			<div class="wc-braintree-field-container field-postal postalCode-container">
				<label><?php echo $fields['postal_code']['label']?></label>
				<div id="wc-braintree-postal-code" class="hosted-field"></div>
			</div>
			<?php endif;?>
			<?php if(wc_braintree_save_cc_enabled()):?>
				<div class="wc-braintree-field-container field-save">
			<label><?php echo $fields['save']['label']?></label>
			<input type="checkbox" id="<?php echo $gateway->save_method_key?>"
				name="<?php echo $gateway->save_method_key?>"> <label class="wc-braintree-save-label"
				for="<?php echo $gateway->save_method_key?>"></label>
		</div>
		<?php endif;?>
		</div>
	<?php endif;?>
	<?php
	
if ($gateway->should_display_street()) :
		$checkout = WC()->checkout();
		?>
	<div class="wc-braintree-form-wrapper">
		<div class="wc-braintree-field-container">
			<label><?php echo $fields['street']['label']?></label>
			<div class="hosted-field streetAddress">
				<input type="text" placeholder="<?php _e('Street Address', 'woocommerce')?>"
					value="<?php echo $checkout->get_value('billing_address_1')?>">
			</div>
		</div>
	</div>
	<?php endif;?>
</div>