(function($, wc_braintree) {

    if (typeof wc_braintree_applepay_params === 'undefined') {
        return;
    }

    /**
     * @constructor
     */
    function ApplePay() {
        wc_braintree.ApplePay.call(this);
    }

    ApplePay.prototype = $.extend({}, wc_braintree.ApplePay.prototype,
        wc_braintree.CheckoutGateway.prototype, {
            params: wc_braintree_applepay_params,
            applepay_button: '.apple-pay-button'
        });

    /**
     * 
     */
    ApplePay.prototype.initialize = function() {
        wc_braintree.CheckoutGateway.call(this);
    }

    /**
     * 
     */
    ApplePay.prototype.create_instance = function() {
        wc_braintree.ApplePay.prototype.create_instance.apply(this, arguments).then(function() {
            this.create_button();
            $(document.body).on('click', this.applepay_button, this.start.bind(this));
            $(this.container).show();
            $(document.body).on('wc_braintree_payment_method_selected', this.payment_gateway_changed.bind(this));
            $(document.body).on('wc_braintree_display_saved_methods', this.display_saved_methods.bind(this));
            $(document.body).on('wc_braintree_display_new_payment_method', this.display_new_payment_method_container.bind(this));
            if (this.banner_enabled) {
                $(this.banner_container).prepend(
                    '<div class="applepay-top-container">' +
                    this.params.button_html +
                    '</div>');
            }
        }.bind(this)).catch(function() {
            $(this.container).hide();
        }.bind(this))
    }

    /**
     * Start the Apple Pay session.
     */
    ApplePay.prototype.start = function(e) {
        e.preventDefault();
        // make sure New Card option is selected since this could be a banner click
        this.set_use_nonce_option(true);
        this.set_payment_method(this.gateway_id);
        this.init_wallet();
        this.open_wallet();
    }

    /**
     * Create the Apple Pay button.
     */
    ApplePay.prototype.create_button = function() {
        var $container = $('#place_order').parent();
        // remove existing button just in case it wasn't refreshed.
        $container.find('.apple-pay-button').remove();
        this.$button = $(this.params.button_html);
        $container.append(this.$button);
        this.payment_gateway_changed(null, this.get_selected_gateway());
    }

    /**
     * Wrapper for wc_braintree.CheckoutGateway.prototype.updated_checkout.
     * Displays the Apple Pay gateway if Apple Pay is supported.
     */
    ApplePay.prototype.updated_checkout = function() {
        if (this.can_initialize_applepay()) {
            $(this.container).show();
            wc_braintree.CheckoutGateway.prototype.updated_checkout.apply(this, arguments);
        }
    }

    /**
     * Wrapper for the wc_braintree.ApplePay.prototype.populate_address_data
     * function.
     */
    ApplePay.prototype.populate_address_data = function(response) {
        wc_braintree.ApplePay.prototype.populate_address_data.apply(this, arguments);
        this.address_compare(response);
        $('[name="billing_country"]').trigger("change");
    }

    /**
     * Compare the shipping and billing address if they are in the response. If
     * they are not the same, the ship to different address checkbox is
     * selected.
     * 
     * @param {Object}
     * 
     */
    ApplePay.prototype.address_compare = function(response) {
        if (response.shippingContact && response.billingContact) {
            if ($('[name="ship_to_different_address"]').length) {
                $('[name="ship_to_different_address"]').prop('checked', this.get_address_hash('#billing') !== this.get_address_hash('#shipping')).trigger('change');
                if ($('[name="ship_to_different_address"]').is(':checked')) {
                    $('[name="shipping_country"]').trigger('change');
                }
            }
        }
    }

    /**
     * Returns the total price of the shopping cart.
     * 
     * @returns {Float}
     */
    ApplePay.prototype.get_total_price = function() {
        return wc_braintree.BaseGateway.prototype.get_cart_total.call(this);
    }

    new ApplePay();

}(jQuery, wc_braintree))