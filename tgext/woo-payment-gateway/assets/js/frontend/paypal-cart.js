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
        wc_braintree.CartGateway.prototype, {
            params: wc_braintree_paypal_cart_params
        });

    /**
     * 
     */
    PayPal.prototype.initialize = function() {
        wc_braintree.CartGateway.call(this);
        $(document.body).on('updated_wc_div, updated_cart_totals', this.dom_refresh.bind(this));
        $('.wc-braintree-cart-gateways-container').addClass('active');
    }

    /**
     * 
     */
    PayPal.prototype.dom_refresh = function() {
        this.create_button();
        if (this.tokenize_response) {
            $('.wc-braintree-cart-gateways-container').addClass('active');
            this.set_nonce(this.tokenize_response.nonce);
            this.set_device_data();
            this.update_addresses(this.tokenize_response.details);
            $('[name="payment_method"]').val(this.gateway_id);
        }
    }

    /**
     * 
     */
    PayPal.prototype.render_options = function() {
        var options = wc_braintree.PayPal.prototype.render_options.apply(this,
            arguments);
        var onAuthorize = options.onAuthorize,
            validate = options.validate;
        options.onAuthorize = function() {
            if (this.needs_shipping()) {
                this.block();
            }
            onAuthorize.apply(this, arguments);
        }.bind(this);
        options.validate = function(actions) {
            validate.call(this, actions);
            if (!this.is_valid_checkout()) {
                this.actions.disable();
            }
        }.bind(this);
        options.onClick = function() {
            if (!this.is_valid_checkout()) {
                this.actions.disable();
                this.submit_error(this.params.messages.terms);
            } else {
                this.actions.enable();
            }
        }.bind(this)
        return options;
    }

    /**
     * 
     */
    PayPal.prototype.create_button = function() {
        wc_braintree.PayPal.prototype.create_button.call(this).then(function() {
            $('ul.wc_braintree_cart_gateways').addClass('paypal-active');
        }.bind(this))
    }

    /**
     * @param {Object}
     */
    PayPal.prototype.on_payment_method_received = function(response) {
        if (this.needs_shipping()) {
            var address = this.map_shipping_address(response.details.shippingAddress);
            address = $.extend({
                first_name: $('#shipping_first_name').val(),
                last_name: $('#shipping_last_name').val()
            }, address);
            this.open_shipping_modal(address, {
                onShippingMethodSelected: function() {
                    $(document.body).trigger('wc_update_cart');
                }
            });
        } else {
            this.process_order();
        }
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
     * Wrapper for wc_braintree.CartGateway.prototype.get_cart_total
     */
    PayPal.prototype.get_amount = function() {
        return wc_braintree.CartGateway.prototype.get_cart_total.call(this);
    }

    /**
     * 
     */
    PayPal.prototype.get_button_container = function() {
        return $('.paypal-button-container');
    }

    new PayPal()

}(jQuery, wc_braintree));