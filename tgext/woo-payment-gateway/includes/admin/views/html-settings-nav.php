<?php
global $current_section;
$tabs = apply_filters ( 'wc_braintree_admin_settings_tabs', array() );
?>
<div class="wc-braintree-settings-logo">
	<img src="<?php echo braintree()->assets_path() . 'img/braintree-logo-black-2.svg'?>"/>
</div>
<div class="braintree-settings-nav">
	<?php foreach($tabs as $id => $tab):?>
		<a
		class="wc-braintree-nav-tab<?php if($current_section === $id){echo ' nav-tab-active';}?>"
		href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $id)?>"><?php echo esc_attr($tab)?></a>
	<?php endforeach;?>
</div>
<div class="wc-braintree-docs">
	<a target="_blank" class="button button-secondary" href="https://docs.paymentplugins.com/wc-braintree/config/#/<?php echo $current_section?>"><?php _e('Documentation', 'woo-payment-gateway')?></a>
</div>