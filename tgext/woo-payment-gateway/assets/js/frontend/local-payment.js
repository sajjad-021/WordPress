(function($, wc_braintree) {

    /**
     * @constructor
     */
    function LocalPayment(params) {
        this.params = params;
        this.button_text = params.button_text;
        this.payment_type = params.payment_type;
        wc_braintree.BaseGateway.call(this);
        if (this.is_active()) {
            $(this.container).show();
        } else {
            $(this.container).hide();
        }
    }

    LocalPayment.prototype = Object.create(wc_braintree.BaseGateway.prototype);

    LocalPayment.prototype = $.extend({}, LocalPayment.prototype, wc_braintree.PayPal.prototype, wc_braintree.CheckoutGateway.prototype)

    LocalPayment.prototype.needs_shipping = function() {
        return this.params.needs_shipping;
    }

    /**
     * 
     */
    LocalPayment.prototype.initialize = function() {
        wc_braintree.CheckoutGateway.call(this);
        this.payment_key = '[name="' + this.params.payment_key + '"]';
        $(document.body).on('click', '#place_order', this.start.bind(this));
        $(document.body).on('wc_braintree_payment_method_selected', this.payment_gateway_changed.bind(this));
        window.addEventListener('hashchange', this.hashchange.bind(this));
    }

    LocalPayment.prototype.is_active = function() {
        return $('#' + this.gateway_id + '_active').data('active') === 1;
    }

    LocalPayment.prototype.hashchange = function(e) {
        if (this.is_gateway_selected()) {
            var matches = e.newURL.match(/local_payment=([\w]+)&order_id=([\d]+)/);
            if (matches) {
                this.order_id = matches[2];
                this.tokenize();
                this.get_form().unblock().removeClass('processing');
            }
        }
    }

    LocalPayment.prototype.updated_checkout = function() {
        wc_braintree.CheckoutGateway.prototype.updated_checkout.apply(this, arguments);
        if (this.is_active()) {
            $(this.container).show();
        } else {
            $(this.container).hide();
        }
    }

    /**
     * 
     */
    LocalPayment.prototype.create_instance = function(e, client, client_token) {
        this.clientInstance = client;
        braintree.localPayment.create({
            client: client,
            authorization: client_token,
            merchantAccountId: this.get_merchant_account()
        }, function(err, localPaymentInstance) {
            if (err) {
                this.submit_error(err);
                return;
            }
            this.localPaymentInstance = localPaymentInstance;

            if (this.localPaymentInstance.hasTokenizationParams() && this.is_gateway_selected()) {
                this.tokenize();
            }
        }.bind(this));
    }

    LocalPayment.prototype.start = function(e) {
        if (this.is_gateway_selected()) {
            this.payment_method_received = true;
        }
    }

    /**
     * Tokenize the payment method for the local payment.
     */
    LocalPayment.prototype.tokenize = function() {
        if (this.get_merchant_account() == "") {
            this.submit_error({
                code: 'LOCAL_GATEWAY_INVALID_MERCHANT_ACCOUNT'
            });
            return;
        }
        this.localPaymentInstance.startPayment(this.get_payment_args(), function(err, payload) {
            if (err) {
                if (err.code === "LOCAL_PAYMENT_WINDOW_CLOSED") {
                    return;
                }
                this.submit_error(err);
                return;
            }
            this.on_payment_method_received(payload);
        }.bind(this));
    }

    LocalPayment.prototype.get_payment_args = function() {
        var args = {
            paymentType: this.payment_type,
            amount: this.get_cart_total(),
            email: $('#billing_email').val(),
            givenName: $('#billing_first_name').val(),
            surname: $('#billing_last_name').val(),
            phone: $('#billing_phone').val(),
            fallback: {
                url: this.params.return_url,
                buttonText: this.button_text
            },
            currencyCode: this.get_currency(),
            shippingAddressRequired: this.params.needs_shipping,
            onPaymentStart: function(data, start) {
                // save the payment data to server
                // in case customer does not return to page.
                $(this.payment_key).val(data.paymentId);
                this.store_payment_data(data, this.order_id);
                start();
            }.bind(this)
        };
        var prefix = this.get_shipping_prefix();
        if (this.valid_address(this.get_address_object(prefix), prefix)) {
            args.address = {
                streetAddress: $(prefix + '_address_1').val(),
                locality: $(prefix + '_city').val(),
                region: $(prefix + '_state').val(),
                postalCode: $(prefix + '_postcode').val(),
                countryCode: $(prefix + '_country').val()
            }
        } else {
            args.address = {
                countryCode: this.get_billing_country()
            }
        }
        return args;
    }

    /**
     * [store_payment_data description]
     * @param  {[type]} data [description]
     * @return {[type]}      [description]
     */
    LocalPayment.prototype.store_payment_data = function(data, order_id) {
        $.ajax({
            url: this.params.routes.payment_data,
            dataType: 'json',
            method: 'POST',
            data: { payment_id: data.paymentId, order_id: order_id }
        }).done(function(response) {
            if (response.code) {
                this.submit_error(response.message);
            }
        }.bind(this)).fail(function(xhr, textStatus, errorThrown) {
            this.submit_error(textStatus);
        }.bind(this))
    }

    /**
     * 
     */
    LocalPayment.prototype.payment_gateway_changed = function(e, payment_gateway) {
        if (payment_gateway == this.gateway_id) {
            this.show_place_order();
            if (this.payment_method_received) {
                $('#place_order').text($('#place_order').data('value'));
            }
        }
    }

    /**
     * 
     */
    LocalPayment.prototype.on_payment_method_received = function(response) {
        wc_braintree.CheckoutGateway.prototype.on_payment_method_received.apply(this, arguments);
        wc_braintree.PayPal.prototype.update_addresses.call(this, response.details);
        $(this.container).closest('form').removeClass('processing');
        $(this.container).closest('form').submit();
        $('#place_order').text($('#place_order').data('value'));

    }

    /**
     * @returns {String}
     */
    LocalPayment.prototype.get_billing_country = function() {
        return $('#billing_country').val();
    }

    /**
     * Creates all the local payment instances.
     */
    function Init_Gateways() {
        for (var id in wc_braintree_local_payment_params.gateways) {
            new LocalPayment(wc_braintree_local_payment_params.gateways[id]);
        }
    }

    Init_Gateways();

}(jQuery, wc_braintree))