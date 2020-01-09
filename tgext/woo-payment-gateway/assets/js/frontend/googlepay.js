(function($, wc_braintree) {

    if (typeof wc_braintree_googlepay_params === 'undefined') {
        return;
    }

    /**
     * @constructor
     */
    function GooglePay() {
        wc_braintree.GooglePay.call(this);
    }

    /**
     * 
     */
    GooglePay.prototype = $.extend({}, wc_braintree.GooglePay.prototype,
        wc_braintree.CheckoutGateway.prototype, {
            params: wc_braintree_googlepay_params,
            cannot_pay: false
        });

    /**
     * 
     */
    GooglePay.prototype.initialize = function() {
        wc_braintree.CheckoutGateway.call(this);
        this.maybe_show_gateway();
    }

    /**
     * 
     */
    GooglePay.prototype.create_instance = function(e, client, client_token) {
        wc_braintree.GooglePay.prototype.create_instance.apply(this, arguments).then(function() {
            $(this.container).show();
            if (this.banner_enabled) {
                var $button = $(this.paymentsClient.createButton($.extend({ onClick: this.banner_checkout.bind(this) }, this.button_options)));
                $button.addClass('wc-braintree-googlepay-top-container');
                $(this.banner_container).prepend($button)
            }
            $(document.body).on('wc_braintree_payment_method_selected', this.payment_gateway_changed.bind(this));
            $(document.body).on('wc_braintree_display_saved_methods', this.display_saved_methods.bind(this));
            $(document.body).on('wc_braintree_display_new_payment_method', this.display_new_payment_method_container.bind(this));
            setInterval(this.maybe_show_gateway.bind(this), 2000);
        }.bind(this)).catch(function() {
            $(this.container).hide();
        }.bind(this))
    }

    /**
     * 
     */
    GooglePay.prototype.create_button = function() {
        wc_braintree.GooglePay.prototype.create_button.call(this).then(function() {
            this.$button.hide();
            $("#place_order").parent().append(this.$button);
            this.payment_gateway_changed(null, this.get_selected_gateway());
        }.bind(this))
    }

    /**
     * Update the gateway when WC calls the updated_checkout trigger.
     */
    GooglePay.prototype.updated_checkout = function(e) {
        wc_braintree.CheckoutGateway.prototype.updated_checkout.apply(this, arguments);
        this.maybe_show_gateway();
    }

    /**
     * 
     */
    GooglePay.prototype.populate_address_fields = function(paymentData) {
        wc_braintree.GooglePay.prototype.populate_address_fields.call(this, paymentData);
        if ($('[name="ship_to_different_address"]').length) {
            $('[name="ship_to_different_address"]').prop(
                'checked',
                this.get_address_hash('#billing') !== this
                .get_address_hash('#shipping')).trigger(
                'change');
        }
        $('[name*="_country"]').trigger("change");
    }

    /**
     * 
     */
    GooglePay.prototype.banner_checkout = function(e) {
        this.tokenize();
        this.set_payment_method(this.gateway_id);
        this.set_use_nonce_option(true);
    }

    /**
     * 
     */
    GooglePay.prototype.maybe_show_gateway = function() {
        if (!this.cannot_pay) {
            $(this.container).show();
        }
    }

    /**
     * Return the total price of the cart.
     */
    GooglePay.prototype.get_total_price = function() {
        return $('#wc_braintree_cart_total').val();
    }

    new GooglePay();

}(jQuery, wc_braintree));