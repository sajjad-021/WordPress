<script type="text/template" id="tmpl-wc-braintree-message">
<div class="wc-backbone-modal">
<div class="wc-backbone-modal-content wc-transaction-data">
<section class="wc-backbone-modal-main" role="main">
<header class="wc-backbone-modal-header">
<h1><?php _e('Message', 'woo-payment-gateway')?></h1>
<button
class="modal-close modal-close-link dashicons dashicons-no-alt">
<span class="screen-reader-text">Close modal panel</span>
</button>
</header>
<article class="wc-braintree-modal-message">
{{{ data.message }}}
</article>
<footer>
<div class="inner">
	
</div>
</footer>
</section>
</div>
</div>
<div class="wc-backbone-modal-backdrop modal-close"></div>
</script>