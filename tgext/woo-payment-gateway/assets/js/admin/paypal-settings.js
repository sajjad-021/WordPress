jQuery(function($){
	var settings = {
			init: function(){
				this.render_button();
				$('[class*="wc-braintree-smartbutton"]').on('change', this.render_button);
			},
			render_button: function(){
				$('#wc-braintree-button-demo').empty();
				paypal.Button.render({
					env: 'sandbox',
					client: {
						sandbox: 'AZDxjDScFpQtjWTOUtWKbyN_bDt4OgqaF4eYXlewfBP4-8aqX3PiV8e1GWU6liB2CUXlkA59kJXE7M6R'
					},
					style: {
						layout: $('.wc-braintree-smartbutton-layout').val(),
						size: $('.wc-braintree-smartbutton-size').val(),
						shape: $('.wc-braintree-smartbutton-shape').val(),
						color: $('.wc-braintree-smartbutton-color').val()
					},
					funding: settings.get_funding(),
					validate: function(actions){
						actions.disable();
					},
					payment: function(data, actions){
						
					},
					onAuthorize: function(data, actions){
						
					},
					onError: function(error){
						window.alert(error);
					}
				}, '#wc-braintree-button-demo');
			},
			get_funding: function(){
				var funding = {
						allowed: [],
						disallowed: []
				};
				if($('#woocommerce_braintree_paypal_credit_enabled').is(':checked')){
					funding.allowed.push(paypal.FUNDING.CREDIT);
				}else{
					funding.disallowed.push(paypal.FUNDING.CREDIT);
				}
				if($('#woocommerce_braintree_paypal_smartbutton_cards').is(':checked')){
					funding.allowed.push(paypal.FUNDING.CARD);
				}else{
					funding.disallowed.push(paypal.FUNDING.CARD);
				}
				return funding;
			}
	};
	settings.init();
});