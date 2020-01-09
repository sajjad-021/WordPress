<?php
/**
 * @version 3.0.0
 * @package Braintree/Templates
 */
?>
<div class="wc-braintree-card-icons-container">
<?php foreach($payment_methods as $method):?>
	<?php printf('<img class="wc-braintree-card-icon ' . $type . ' ' . $method . '" src="%1$s%2$s%3$s%4$s"/>', braintree()->assets_path(), 'img/payment-methods/' . $type . '/', $method, '.svg')?>
<?php endforeach;?>
</div>