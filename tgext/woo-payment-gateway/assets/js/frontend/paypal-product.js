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
        wc_braintree.ProductGateway.prototype, {
            params: wc_braintree_paypal_product_params
        });

    /**
     * 
     */
    PayPal.prototype.initialize = function() {
        wc_braintree.ProductGateway.call(this);
    }

    /**
     * 
     */
    PayPal.prototype.render_options = function() {
        var options = wc_braintree.PayPal.prototype.render_options.call(this);
        var onAuthorize = options.onAuthorize,
            validate = options.validate,
            payment = options.payment;
        options.onAuthorize = function() {
            if (this.needs_shipping()) {
                this.block();
            }
            onAuthorize.apply(this, arguments);
        }.bind(this)
        options.validate = function(actions) {
            validate.apply(this, arguments);
            if ($('[name="variation_id"]').length) {
                var variation_id = $('[name="variation_id"]').val();
                if (variation_id == "0" || variation_id == "") {
                    this.actions.disable();
                }
            }
        }.bind(this)
        options.payment = function(resolve, reject) {
            this.add_to_cart().then(function() {
                payment.call(this, resolve, reject);
            }.bind(this))
        }.bind(this)
        return options;
    }

    /**
     * 
     */
    PayPal.prototype.create_button = function() {
        wc_braintree.PayPal.prototype.create_button.call(this).then(function() {
            $('ul.wc_braintree_product_gateways').addClass('paypal-active');
        }.bind(this))
    }

    /**
     * 
     */
    PayPal.prototype.on_payment_method_received = function(response) {
        if (this.needs_shipping()) {
            var address = this.map_shipping_address(response.details.shippingAddress);
            address = $.extend({
                first_name: $('#shipping_first_name').val(),
                last_name: $('#shipping_last_name').val()
            }, address);
            this.open_shipping_modal(address);
        } else {
            this.process_order();
        }
    }

    /**
     * Returns the jQuery element that the PayPal button should be appended to.
     * 
     * @returns {jQuery}
     */
    PayPal.prototype.get_button_container = function() {
        return $('.wc-braintree-paypal-button');
    }

    /**
     * 
     */
    PayPal.prototype.get_amount = function() {
        return wc_braintree.ProductGateway.prototype.get_product_amount.call(this);
    }

    /**
     * Wrapper for ProductGateway.prototype.found_variation. Enables the PayPal
     * button since a variation has been selected.
     */
    PayPal.prototype.found_variation = function() {
        wc_braintree.ProductGateway.prototype.found_variation.apply(this,
            arguments);
        if (this.actions) {
            this.actions.enable();
        }
    }

    /**
     * Wrapper for ProductGateway.prototype.reset_variation_data. Disables the
     * PayPal button since no variation is selected.
     */
    PayPal.prototype.reset_variation_data = function() {
        wc_braintree.ProductGateway.prototype.reset_variation_data.apply(this, arguments);
        if (this.actions) {
            this.actions.disable();
        }
    }

    new PayPal();

}(jQuery, wc_braintree))