<?php
/**
 * @version 3.0.0
 * @package Braintree/Templates
 */
?>
<div class="wc-braintree-clear"></div>
<div class="wc-braintree-product-gateways-container">
	<ul class="wc-braintree-product-gateways wc_braintree_product_gateways">
	<?php foreach($gateways as $gateway):?>
	<li class="wc-braintree-product-gateway wc_braintree_product_gateway_<?php echo $gateway->id?>">
		<?php $gateway->product_fields()?>
	</li>
	<?php endforeach;?>
	</ul>
</div>