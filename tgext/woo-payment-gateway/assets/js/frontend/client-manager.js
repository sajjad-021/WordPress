// wrap in jQuery so manager is called when DOM loads. This ensures
// all scripts listening for client creation receive it.
jQuery(function($){
	manager = {
			params: wc_braintree_client_manager_params,
			upated_checkout_count: 0,
			init: function(){
				this.container = this.get_container_class();
				this.currency = this.get_currency();
				$(document.body).on('update_checkout', this.update_currency);
				$(document.body).on('updated_checkout', this.updated_checkout);
				this.create_client();
			},
			create_client: function(){
				if(typeof wc_braintree_client_token === 'undefined'){
					manager.submit_error({
						code: 'INVALID_CLIENT_TOKEN'
					});
					return;
				}
				braintree.client.create({
					authorization: wc_braintree_client_token
				}, function(err, clientInstance){
					if(err){
						manager.submit_error(err);
						return;
					}
					manager.client = clientInstance;
					$(document.body).triggerHandler('wc_braintree_client_created', [clientInstance, wc_braintree_client_token]);
				});
			},
			submit_error: function(error){
				$(document.body).triggerHandler('wc_braintree_submit_error', {error: error, element: manager.container});
			},
			get_container_class: function(){
				if($('body').hasClass('woocommerce-cart')){
					return 'div.woocommerce';
				}
				if($('body').hasClass('woocommerce-checkout')){
					return 'ul.payment_methods';
				}
				if($('body').hasClass('woocommerce-add-payment-method')){
					return 'div.woocommerce';
				}
				if($('body').hasClass('single-product')){
					return 'div.woocommerce-notices-wrapper';
				}
			},
			update_currency: function(){
				manager.currency = manager.get_currency();
			},
			get_currency: function(){
				return $('#wc_braintree_cart_currency').val();
			},
			updated_checkout: function(){
				manager.upated_checkout_count++;
				if(manager.currency !== manager.get_currency()){
					manager.get_client_token();
					manager.currency = manager.get_currency();
					manager.upated_checkout_count = 0;
				}
			},
			get_client_token: function(){
				$.when($.ajax({
					method: 'POST',
					url: manager.params.url,
					data: {_wpnonce: manager.params._wpnonce}
				})).done(function(response){
					wc_braintree_client_token = response;
					manager.create_client();
				}).fail(function(jqXHR, errorThrown, textStatus){
					//fail gracefully.
				});
			}
	};
	manager.init();
})