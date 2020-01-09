(function($, wc_braintree) {

    /**
     * @constructor
     */
    function PayPal() {
        wc_braintree.PayPal.call(this);
    }

    /**
     * 
     */
    PayPal.prototype = $.extend({}, wc_braintree.PayPal.prototype,
        wc_braintree.CheckoutGateway.prototype, {
            params: wc_braintree_paypal_params
        });

    /**
     * 
     */
    PayPal.prototype.initialize = function() {
        wc_braintree.CheckoutGateway.call(this);
    }

    /**
     * 
     */
    PayPal.prototype.create_instance = function(e, client, client_token) {
        wc_braintree.PayPal.prototype.create_instance.apply(this, arguments).then(function() {
            if (this.banner_enabled && $(this.banner_container).length) {
                $(this.banner_container)
                    .prepend(
                        '<div class="wc-braintree-paypal-top-container"></div>');
                var render_options = this.render_options();
                paypal.Button.render($.extend({}, this.render_options(), {
                    funding: {
                        allowed: [],
                        disallowed: [paypal.FUNDING.CARD,
                            paypal.FUNDING.CREDIT,
                            paypal.FUNDING.VENMO
                        ]
                    },
                    onClick: function(data, actions) {
                        this.set_payment_method(this.gateway_id);
                        this.set_use_nonce_option(true);
                        render_options.onClick.apply(this, arguments);
                    }.bind(this)
                }), '.wc-braintree-paypal-top-container');
            }
            $('.wc_braintree_banner_gateways').addClass(
                'paypal-active');
            $(document.body).on('wc_braintree_payment_method_selected', this.payment_gateway_changed.bind(this));
            $(document.body).on('wc_braintree_display_saved_methods', this.display_saved_methods.bind(this));
            $(document.body).on('wc_braintree_display_new_payment_method', this.display_new_payment_method_container.bind(this));
        }.bind(this))
    }

    /**
     * 
     */
    PayPal.prototype.create_button = function() {
        wc_braintree.PayPal.prototype.create_button.call(this).then(function() {
            this.payment_gateway_changed(null, this.get_selected_gateway());
        }.bind(this))
    }

    /**
     * 
     */
    PayPal.prototype.render_options = function() {
        return $.extend({}, wc_braintree.PayPal.prototype.render_options
            .call(this), {
                validate: function(actions) {
                    this.actions = actions;
                }.bind(this),
                onClick: function() {}.bind(this)
            });
    }

    /**
     * 
     */
    PayPal.prototype.terms_updated = function(e) {
        if ($(e.target).is(':checked')) {
            this.actions.enable();
        } else {
            this.actions.disable();
        }
    }

    /**
     * 
     */
    PayPal.prototype.update_addresses = function(details) {
        wc_braintree.PayPal.prototype.update_addresses.call(this, details);
        // compare billing and shipping address to see if different
        if ($('[name="ship_to_different_address"]').length) {
            $('[name="ship_to_different_address"]').prop(
                'checked',
                this.get_address_hash('#billing') !== this.get_address_hash('#shipping')).trigger('change')
        }
    }

    /**
     * Return allowed and disallowed payment methods for the PayPal button.
     */
    PayPal.prototype.get_funding = function() {
        var funding = {
            allowed: [],
            disallowed: [paypal.FUNDING.VENMO]
        };
        if (this.params.card_icons === "1") {
            funding.allowed.push(paypal.FUNDING.CARD);
        } else {
            funding.disallowed.push(paypal.FUNDING.CARD);
        }
        if (this.params.options.offerCredit) {
            funding.allowed.push(paypal.FUNDING.CREDIT);
        } else {
            funding.disallowed.push(paypal.FUNDING.CREDIT);
        }
        if (this.params.funding) {
            funding.allowed.push(this.params.funding);
        }
        return funding;
    }

    /**
     * Called by the get_options() functions. Must return the amount of the
     * shopping cart.
     */
    PayPal.prototype.get_amount = function() {
        return wc_braintree.BaseGateway.prototype.get_cart_total.call(this);
    }

    new PayPal();

}(jQuery, wc_braintree))