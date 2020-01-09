<p class="form-field form-field-wide">
	<button class="button button-secondary wc-braintree-pay-order"><?php _e('Pay for Order', 'woo-payment-gateway')?></button>
			<?php echo wc_help_tip(__('Admins can process customer orders over the phone using this functionality.', 'woo-payment-gateway'))?>
</p>
<script type="text/template" id="tmpl-wc-braintree-modal-pay-order">
<div class="wc-backbone-modal">
	<div class="wc-backbone-modal-content">
		<section class="wc-backbone-modal-main" role="main">
			<header class="wc-backbone-modal-header">
				<h1><?php esc_html_e( 'Pay for Order', 'woocommerce' ); ?></h1>
				<button
					class="modal-close modal-close-link dashicons dashicons-no-alt">
					<span class="screen-reader-text">Close modal panel</span>
				</button>
			</header>
			<article>
				<form id="wc-braintree-pay-order-form">
					<input type="hidden" name="wc_braintree_payment_method" value="braintree_cc"/>
					<p class="form-field form-field-wide">
						<label><?php _e('Charge Type', 'woo-payment-gateway')?></label>
						<select name="wc_braintree_charge_type" class="wc-select2">
							<option value="capture"><?php _e('Capture', 'woo-payment-gateway')?></value>
							<option value="authorize"><?php _e('Authorize', 'woo-payment-gateway')?></value>
						</select>
					</p>
					<#if(data.payment_methods.length){#>					
						<div class="row form-field form-field-wid">
							<input type="radio" value="token" name="payment_type" checked/>
							<label class=""><?php _e('Saved Cards', 'woo-payment-gateway')?></label>
							<div class="token-container show_if_token hide_if_nonce">
								<select name="payment_token_id" class="wc-select2">
								<#_.each(data.payment_methods, function(method){#>
									<option value="{{{method.id}}}">{{{method.title}}}</option>
								<#})#>
								</select>
							</div>
						</div>
					<#}#>
					<div class="row form-field form-field-wid">
						<input type="radio" value="nonce" name="payment_type" class="" <#if(!data.payment_methods.length){#>checked<#}#>/>
						<label class=""><?php _e('New Card', 'woo-payment-gateway')?></label>
						<input type="hidden" name="payment_nonce"/>
						<div class="wc-braintree-card-container show_if_nonce hide_if_token"></div>
					<div>
				</form>
			</article>
			<footer>
				<div class="inner">
					<button id="pay-order" class="button button-primary button-large"><?php esc_html_e( 'Pay', 'woo-payment-gateway' ); ?></button>
				</div>
			</footer>
		</section>
	</div>
</div>
<div class="wc-backbone-modal-backdrop modal-close"></div>			
</script>