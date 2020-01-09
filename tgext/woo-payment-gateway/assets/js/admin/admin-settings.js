jQuery(function($) {
	settings = {
		prefix: '#' + $('#wc_braintree_prefix').val(),
		params: wc_braintree_setting_params,
		init: function() {
			$('[name^="woocommerce_braintree"]').on('change', this.display_children);

			$('select.braintree-accepted-cards').on('select2:select', this.reorder_multiselect);

			$('.domain-association').on('click', this.create_domain_association.bind(this));

			this.display_children();

			$.each(this.params.templates, function(i, template) {
				$('body').append(template);
			})
		},
		display_children: function(e) {
			$('[data-show-if]').each(function(el) {
				var $this = $(this);
				var values = $this.data('show-if');
				var hidden = [];
				$.each(values, function(k, v) {
					$key = $(settings.prefix + k);
					if (hidden.indexOf($this.attr('id')) == -1) {
						if ($key.is(':checkbox')) {
							if ($key.is(':checked') == v) {
								$this.closest('tr').show();
							} else {
								$this.closest('tr').hide();
								hidden.push($this.attr('id'));
							}
						} else {
							if ($key.val() == v) {
								$this.closest('tr').show();
							} else {
								$this.closest('tr').hide();
								hidden.push($this.attr('id'));
							}
						}
					} else {
						$this.closest('tr').hide();
						hidden.push($this.attr('id'));
					}
				});
			});
		},
		reorder_multiselect: function(e) {
			var element = e.params.data.element;
			var $element = $(element);
			$element.detach();
			$(this).append($element);
			$(this).trigger('change');
		},
		create_domain_association: function(e) {
			e.preventDefault();
			this.block();
			$.ajax({
				url: wc_braintree_applepay_params.routes.domain_association,
				dataType: 'json',
				method: 'POST',
				data: { _wpnonce: this.params.rest_nonce }
			}).done(function(response) {
				this.unblock();
				$(e.target).WCBackboneModal({
					template: 'wc-braintree-message',
					variable: response.data
				});
				//window.alert(response.message);
			}.bind(this)).fail(function(xhr, textStatus, errorThrown) {
				this.unblock();
				window.alert(errorThrown);
			}.bind(this))
		},
		block: function() {
			$('.wc-braintree-settings-container').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
		},
		unblock: function() {
			$('.wc-braintree-settings-container').unblock();
		}
	};
	settings.init();
});