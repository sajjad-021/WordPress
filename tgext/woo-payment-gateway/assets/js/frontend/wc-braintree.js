(function($) {
    window.wc_braintree = {
        utils: {},
    };

    /**
     * BaseGateway class that all gateways should extend.
     * 
     * @constructor
     */
    wc_braintree.BaseGateway = function() {

        this.gateway_id = this.params.gateway;
        this.container = 'li.payment_method_' + this.gateway_id;
        this.nonce_selector = '[name="' + this.params.nonce_selector + '"]';
        this.device_data_selector = '[name="' + this.params.device_data_selector + '"]';
        this.tokenized_response_selector = '[name="' + this.params.tokenized_response_selector + '"]';

        // list for refresh token from ajax calls.
        $(document.body).on('wc_braintree_refreshed_token', this.set_refresh_token.bind(this));

        // listen for client object creation.
        $(document.body).on('wc_braintree_client_created', this.create_instance.bind(this));

        this.initialize();
    }

    /**
     * Function that should be overridden by gateways so they can create
     * instances of the Braintree objects used for tokenization.
     * 
     * @param {event}
     * @param {client}
     * @param {String}
     * @return {Promise}
     */
    wc_braintree.BaseGateway.prototype.create_instance = function(e, client, client_token) {

    }

    wc_braintree.BaseGateway.prototype.getBrowserLocales = function() {
        var nav = window.navigator,
            locales = nav.languages ? Array.prototype.slice.apply(nav.languages) : [];
        nav.language && locales.push(nav.language);
        nav.userLanguage && locales.push(nav.userLanguage);
        return locales.map(function(locale) {
            if (locale && locale.match(/^[a-z]{2}[-_][A-Z]{2}$/)) {
                var _locale$split = locale.split(/[-_]/),
                    _lang = _locale$split[0];
                return {
                    country: _locale$split[1],
                    lang: _lang
                };
            }
            return locale && locale.match(/^[a-z]{2}$/) ? {
                lang: locale
            } : null;
        }).filter(Boolean);
    }

    /**
     * Wrapper for the jQuery blockUI plugin. This function blocks the view.
     */
    wc_braintree.BaseGateway.prototype.block = function() {
        $.blockUI({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
    }

    /**
     * Wrapper for the jQuery blockUI plugin. This function unblocks the view.
     */
    wc_braintree.BaseGateway.prototype.unblock = function() {
        $.unblockUI();
    }

    /**
     * Set a refreshed token used by WP for rest calls.
     */
    wc_braintree.BaseGateway.prototype.set_refresh_token = function(e, token) {
        this.params._wp_rest_nonce = token;
    }

    wc_braintree.BaseGateway.prototype.get_cart_total = function() {
        return $('#wc_braintree_cart_total').val()
    }

    wc_braintree.BaseGateway.prototype.set_cart_total = function(total) {
        return $('#wc_braintree_cart_total').val(total)
    }

    /**
     * Return the recurring cart total that's based on subscriptions total.
     * @return {[type]} [description]
     */
    wc_braintree.BaseGateway.prototype.get_recurring_cart_total = function() {
        return $('#wc_braintree_recurring_cart_total').val();
    }

    wc_braintree.BaseGateway.prototype.has_recurring_total = function() {
        return $('#wc_braintree_recurring_cart_total').length > 0;
    }

    wc_braintree.BaseGateway.prototype.get_currency = function() {
        return $('#wc_braintree_cart_currency').val();
    }

    /**
     * Return the gateway's button if it exists.
     */
    wc_braintree.BaseGateway.prototype.get_button = function() {
        return this.$button;
    }

    /**
     * Return the merchant account for the store.
     * 
     * @returns {String}
     */
    wc_braintree.BaseGateway.prototype.get_merchant_account = function() {
        return $('#wc_braintree_merchant_account').val()
    }

    /**
     * Sets the device data for the gateway.
     */
    wc_braintree.BaseGateway.prototype.set_device_data = function() {
        if (this.dataCollectorInstance) {
            $(this.device_data_selector).val(
                this.dataCollectorInstance.deviceData);
        }
    }

    /**
     * Sets the nonce value to the hidden input field identified by the gateway.
     * 
     * @param {String}
     *            value the nonce value which represents a tokenized payment
     *            method.
     */
    wc_braintree.BaseGateway.prototype.set_nonce = function(value) {
        $(this.nonce_selector).val(value);
    }

    /**
     * Function that returns a jQuery element containing a form.
     * 
     * @return {Element}
     */
    wc_braintree.BaseGateway.prototype.get_form = function() {
        return $(this.container).closest('form');
    }

    /**
     * Returns true if the gateway needs shipping. Shipping is based on the
     * products in the cart.
     * 
     * @return {bool}
     */
    wc_braintree.BaseGateway.prototype.needs_shipping = function() {
        return this.params.needs_shipping === "1";
    }

    /**
     * Initialize any additional functionality.
     */
    wc_braintree.BaseGateway.prototype.initialize = function() {

    }

    /**
     * @return {bool}
     */
    wc_braintree.BaseGateway.prototype.is_valid_checkout = function() {
        return true;
    }

    /**
     * Convert serialized form data to an object.
     * 
     * @param {Array}
     */
    wc_braintree.BaseGateway.prototype.form_to_data = function($form) {
        var formData = $form.find('input').filter(function(i, e) {
                if ($(e).is('[name^="add-to-cart"]')) {
                    return false;
                }
                return true;
            }.bind(this)).serializeArray(),
            data = {};

        for (var i in formData) {
            var obj = formData[i];
            data[obj.name] = obj.value;
        }
        return data;
    }

    /**
     * Add a product to the WC cart via Ajax.
     * 
     * @param {event}
     */
    wc_braintree.BaseGateway.prototype.add_to_cart = function(e) {
        if (e) {
            e.preventDefault();
        }
        return new Promise(function(resolve, reject) {
            this.block();
            var data = {
                product_id: this.get_product_data().post_id,
                variation_id: $('[name="variation_id"]').val(),
                qty: this.get_product_quantity(),
                payment_method: this.gateway_id
            }
            $.ajax({
                url: this.params.routes.add_to_cart,
                method: 'POST',
                dataType: 'json',
                data: data,
                beforeSend: function(xhr, settings) {
                    xhr.setRequestHeader('X-WP-Nonce',
                        this.params._wp_rest_nonce);
                }.bind(this),
                success: function(response, status, xhr) {
                    this.unblock();
                    $(document.body).triggerHandler('wc_braintree_refreshed_token', xhr
                        .getResponseHeader('X-WP-Nonce'));
                    if (!response.success) {
                        this.submit_error(response.messages);
                        return;
                    }
                    resolve(response);
                }.bind(this),
                error: function(jqXHR, textStatus, errorThrown) {
                    this.unblock();
                    this.submit_error(errorThrown);
                    reject(errorThrown);
                }.bind(this)
            });
        }.bind(this));

    }

    /**
     * Processes the WC order using Ajax.
     */
    wc_braintree.BaseGateway.prototype.process_order = function() {
        if (!this.is_valid_checkout()) {
            this.submit_error(this.params.messages.terms);
            return;
        }
        // create the order using ajax;
        var data = $.extend({}, this.form_to_data(this.get_form()), { payment_method: this.gateway_id }, this.get_process_order_data());
        this.block();
        $.ajax({
            method: 'POST',
            url: this.params.routes.checkout,
            data: data,
            dataType: 'json',
            beforeSend: function(xhr, settings) {
                xhr.setRequestHeader('X-WP-Nonce', this.params._wp_rest_nonce);
            }.bind(this),
            success: function(result, status, jqXHR) {
                if (result.reload) {
                    window.location.reload();
                    return;
                }
                if (result.result === 'success') {
                    window.location = result.redirect;
                } else {
                    if (result.messages) {
                        this.submit_error(result.messages);
                    }
                    this.set_nonce("");
                    this.unblock();
                }
            }.bind(this),
            error: function(jqXHR, textStatus, errorThrown) {
                this.set_nonce("");
                this.submit_error(errorThrown);
                this.unblock();
            }.bind(this)
        });
    }

    /**
     * Updates the customer's shipping address.
     * 
     * @param {Object}
     */
    wc_braintree.BaseGateway.prototype.update_shipping_address = function(
        address) {
        return new Promise(function(resolve, reject) {
            $.ajax({
                method: 'POST',
                url: this.params.routes.shipping_address,
                data: {
                    payment_method: this.gateway_id,
                    address: address
                },
                dataType: 'json',
                beforeSend: function(xhr, settings) {
                    xhr.setRequestHeader('X-WP-Nonce',
                        this.params._wp_rest_nonce);
                }.bind(this),
                success: function(result, status, jqXHR) {
                    if (result.code) {
                        reject(result);
                    } else {
                        resolve(result);
                    }
                }.bind(this),
                error: function(jqXHR, textStatus, errorThrown) {
                    reject(jqXHR, textStatus, errorThrown);
                }.bind(this)
            })
        }.bind(this))
    }

    /**
     * Update the shipping method for the customer.
     * 
     * @param {String}
     */
    wc_braintree.BaseGateway.prototype.update_shipping_method = function(method) {
        return new Promise(function(resolve, reject) {
            $.ajax({
                method: 'POST',
                url: this.params.routes.shipping_method,
                data: {
                    payment_method: this.gateway_id,
                    shipping_methods: method
                },
                dataType: 'json',
                beforeSend: function(xhr, settings) {
                    xhr.setRequestHeader('X-WP-Nonce', this.params._wp_rest_nonce);
                }.bind(this),
                success: function(result, status, jqXHR) {
                    if (result.code) {
                        reject(result);
                    } else {
                        this.set_selected_shipping_method(result.data.chosen_shipping_methods);
                        resolve(result);
                    }
                }.bind(this),
                error: function(jqXHR, textStatus, errorThrown) {
                    reject();
                }.bind(this)
            })
        }.bind(this))
    }

    wc_braintree.BaseGateway.prototype.get_process_order_data = function() {
        return {};
    }

    /**
     * Map a shipping address from the format gateway format to the generic
     * plugin format.
     * 
     * @returns {Object}
     */
    wc_braintree.BaseGateway.prototype.map_shipping_address = function(address) {

    }

    /**
     * [set_selected_shipping_method description]
     */
    wc_braintree.BaseGateway.prototype.set_selected_shipping_method = function(shipping_methods) {
        if (shipping_methods && $('[name^="shipping_method"]').length) {
            for (var i in shipping_methods) {
                $('[name="shipping_method[' + i + ']"][value="' + shipping_methods[i] + '"]').prop('checked', true).trigger('change');
            }
        }
    }

    /**
     * 
     */
    wc_braintree.BaseGateway.prototype.initialize_fraud_tools = function() {
        if (this.params.advanced_fraud.enabled) {
            braintree.dataCollector.create(this.get_fraud_tool_options(),
                function(err, dataCollectorInstance) {
                    if (err) {
                        if (err.code === 'DATA_COLLECTOR_KOUNT_NOT_ENABLED') {
                            return;
                        }
                        this.submit_error(err);
                        return;
                    }
                    this.dataCollectorInstance = dataCollectorInstance;
                }.bind(this))
        }
    }

    /**
     * Returns an object of fraud tool options.
     * 
     * @returns {Object}
     */
    wc_braintree.BaseGateway.prototype.get_fraud_tool_options = function() {
        return {
            client: this.clientInstance,
            kount: true
        }
    }

    /**
     * Function that is called after successful tokenization. This method should
     * be extended by other classes.
     * 
     * @param {object}
     *            response the response from Braintree when tokenizing a payment
     *            method.
     */
    wc_braintree.BaseGateway.prototype.on_payment_method_received = function(response) {}

    wc_braintree.BaseGateway.prototype.submit_error = function(error) {
        this.clear_error();
        $(document.body).triggerHandler('wc_braintree_submit_error', {
            error: error,
            element: this.message_container
        });
    }

    wc_braintree.BaseGateway.prototype.clear_error = function() {
        $('#wc_braintree_checkout_error').remove();
    }

    /**
     * 
     */
    wc_braintree.BaseGateway.prototype.terms_updated = function() {

    }

    /**
     * [get_first_name description]
     * @return {[type]} [description]
     */
    wc_braintree.BaseGateway.prototype.get_first_name = function(prefix) {
        return $(prefix + '_first_name').length ? $(prefix + '_first_name').val() : '';
    }

    /**
     * [get_first_name description]
     * @return {[type]} [description]
     */
    wc_braintree.BaseGateway.prototype.get_last_name = function(prefix) {
        return $(prefix + '_last_name').length ? $(prefix + '_last_name').val() : '';
    }

    wc_braintree.BaseGateway.prototype.get_shipping_prefix = function() {
        return '#billing';
    }

    /**
     * Cart gateway that contains overridden functions specific to the cart
     * page.
     */
    wc_braintree.CartGateway = function() {
        this.container = 'li.wc_braintree_cart_gateway_' + this.gateway_id;
        this.message_container = '.shop_table';
        this.form_id = '#wc-braintree-cart-fields-form';
        $(document.body).on('change', '[name="terms"]', this.terms_updated.bind(this));
    }

    wc_braintree.CartGateway.prototype.needs_shipping = function() {
        return $("#wc_braintree_needs_shipping").data('value') === 1;
    }

    wc_braintree.CartGateway.prototype.on_payment_method_received = function(response) {
        this.process_order();
    }

    /**
     * Return the form that contains all the cart payment data.
     */
    wc_braintree.CartGateway.prototype.get_form = function() {
        return $(this.form_id);
    }

    /**
     * Validate the cart page.
     * 
     * @return {bool}
     */
    wc_braintree.CartGateway.prototype.is_valid_checkout = function() {
        if ($('[name="terms"]').length) {
            if (!$('[name="terms"]').is(':checked')) {
                return false;
            }
        }
        return true;
    }

    wc_braintree.CartGateway.prototype.get_cart_total = function() {
        return $('#wc_braintree_cart_total').val()
    }

    /**
     * Class that inherits from wc_braintree.BaseGateway. Provides functionality
     * for product page gateways.
     */
    wc_braintree.ProductGateway = function() {
        this.container = 'li.wc_braintree_product_gateway_' + this.gateway_id;
        this.message_container = 'div.product';
        this.buttonWidth = $('div.quantity').outerWidth(true) + $('.single_add_to_cart_button').outerWidth();

        // for variation products when they are selected.
        this.get_form().on('found_variation', this.found_variation.bind(this));
        this.get_form().on('reset_data', this.reset_variation_data.bind(this));

        $(this.container).css('max-width', this.buttonWidth + 'px');
    }

    /**
     * 
     */
    wc_braintree.ProductGateway.prototype.on_payment_method_received = function() {
        this.process_order();
    }

    wc_braintree.ProductGateway.prototype.get_product_data = function() {
        return $('#wc_braintree_product_data').data('product');
    }

    wc_braintree.ProductGateway.prototype.set_product_data = function(data) {
        return $('#wc_braintree_product_data').data('product', data);
    }

    wc_braintree.ProductGateway.prototype.get_product_quantity = function() {
        return parseInt($('[name="quantity"]').val());
    }

    /**
     * Returns the shopping cart amount for the product. Calculation is based on
     * product price and cart qty.
     * 
     * @returns {Number}
     */
    wc_braintree.ProductGateway.prototype.get_product_amount = function() {
        return this.get_product_data().price * this.get_product_quantity();
    }

    /**
     * Called when the "found_variation" event is fired. The product data is
     * updated to use the variation's price.
     * 
     * @param {Event}
     * @param {Object}
     */
    wc_braintree.ProductGateway.prototype.found_variation = function(e, variation) {
        // update the product attributes with the variation.
        var data = this.get_product_data();
        data.price = variation.display_price;
        data.needs_shipping = !variation.is_virtual;
        this.set_product_data(data);
        if (this.$button) {
            this.get_button().prop('disabled', false).removeClass('disabled');
        }
    }

    wc_braintree.ProductGateway.prototype.reset_variation_data = function() {
        if (this.$button) {
            this.get_button().prop('disabled', true).addClass('disabled');
        }
    }

    /**
     * Returns true if the gateway needs shipping. Shipping is based on the
     * products in the cart.
     * 
     * @return {bool}
     */
    wc_braintree.ProductGateway.prototype.needs_shipping = function() {
        return this.get_product_data().needs_shipping;
    }

    wc_braintree.CheckoutGateway = function() {
        this.token_selector = '[name="' + this.params.token_selector + '"]';
        this.payment_type_selector = '[name="' + this.params.payment_type_selector + '"]';
        this.container = this.message_container = 'li.payment_method_' + this.gateway_id;
        this.banner_container = '.wc_braintree_banner_gateway_' + this.gateway_id;
        this.list_container = 'li.payment_method_' + this.gateway_id;
        this.banner_enabled = this.params.banner_enabled === "1";

        $(this.container).closest('form').on('checkout_place_order_' + this.gateway_id, this.pre_submit_validations.bind(this));
        $(this.container).closest('form').on('checkout_place_order_' + this.gateway_id, this.woocommerce_form_submit.bind(this));

        $(document.body).on('wc_braintree_pre_form_submit_' + this.gateway_id, this.set_device_data.bind(this));

        $(document.body).on('updated_checkout', this.updated_checkout.bind(this));
        $(document.body).on('checkout_error', this.checkout_error.bind(this));
        $(document.body).on('change', '[name="terms"]', this.terms_updated.bind(this));

        if (this.banner_enabled) {
            if ($('.woocommerce-billing-fields').length) {
                $(this.banner_container).css('max-width', $('.woocommerce-billing-fields').outerWidth(true));
            }
        }

        this.order_review();
    }

    /**
     * Function that is called when the client receives the tokenized payment
     * method from Braintree
     * 
     * @param {Object}
     * 
     */
    wc_braintree.CheckoutGateway.prototype.on_payment_method_received = function(response) {
        this.tokenize_response = response;
        this.payment_method_received = true;
        this.set_nonce(response.nonce);
        this.hide_payment_button();
        this.show_place_order();
    }

    wc_braintree.CheckoutGateway.prototype.order_review = function() {
        var url = window.location.href;
        var matches = url.match(/order_review.+payment_method=([\w]+).+payment_nonce=(.+)/);
        if (matches && matches.length > 1) {
            var payment_method = matches[1],
                nonce = matches[2];
            if (this.gateway_id === payment_method) {
                this.payment_method_received = true;
                this.set_nonce(nonce);
                this.set_use_nonce_option();
            }
        }
    }

    /**
     * Function that can be implemented by gateways. It is called when the
     * payment nonce is received from Braintree.
     */
    wc_braintree.CheckoutGateway.prototype.hide_payment_button = function() {
        if (this.$button) {
            this.$button.hide();
        }
    }

    wc_braintree.CheckoutGateway.prototype.hide_place_order = function() {
        $('#place_order').addClass('wc-braintree-hide');
    }

    wc_braintree.CheckoutGateway.prototype.show_place_order = function() {
        $('#place_order').removeClass('wc-braintree-hide');
    }

    /**
     * Customer may have chosen to use billing address for shipping or to use unique shipping. Return
     * the prefix for the address type customer has chosen.
     * @return {[type]} [description]
     */
    wc_braintree.CheckoutGateway.prototype.get_shipping_prefix = function() {
        if (this.needs_shipping() && $('[name="ship_to_different_address"]').is(':checked')) {
            return "#shipping";
        } else {
            return "#billing";
        }
    }

    wc_braintree.CheckoutGateway.prototype.woocommerce_form_submit = function() {
        if (this.is_payment_method_selected()) {
            return true;
        } else {
            return this.payment_method_received;
        }
    }

    /**
     * Validate the cart page.
     * 
     * @return {bool}
     */
    wc_braintree.CheckoutGateway.prototype.is_valid_checkout = function() {
        if ($('[name="terms"]').length) {
            if (!$('[name="terms"]').is(':checked')) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate the checkout page before the form is submitted.
     */
    wc_braintree.CheckoutGateway.prototype.pre_submit_validations = function(e) {
        if (!this.is_valid_checkout()) {
            e.stopImmediatePropagation();
            this.submit_error(this.params.messages.terms);
            return false;
        }
        return true;
    }

    /**
     * Function that is called when the [name="payment_method"] radio button is
     * clicked.
     * 
     * @param {Event}
     * @param {String}
     */
    wc_braintree.CheckoutGateway.prototype.payment_gateway_changed = function(e, payment_gateway) {
        if (payment_gateway === this.gateway_id) {
            if (this.is_payment_method_selected() ||
                this.payment_method_received) {
                this.$button.hide();
                this.show_place_order();
            } else {
                this.$button.show();
                this.hide_place_order();
            }
        } else {
            this.$button.hide();
        }
    }

    /**
     * Gateways should use this function to add all button creation code.
     */
    wc_braintree.CheckoutGateway.prototype.create_button = function() {}

    /**
     * Function that is called on the WC updated_checkout event. Gateway's
     * should use this function to perform actions like rendering button html
     * that's been refreshed by WC.
     * 
     * @param {event}
     */
    wc_braintree.CheckoutGateway.prototype.updated_checkout = function(e) {
        this.create_button();
        if (this.payment_method_received) {
            $('#' + this.gateway_id + '_use_nonce').trigger('click');
        }
    }

    /**
     * @param {event}
     */
    wc_braintree.CheckoutGateway.prototype.checkout_error = function(e) {
        if (this.is_gateway_selected() && this.has_checkout_error()) {
            this.payment_method_received = false;
            this.tokenize_response = null;
            this.payment_gateway_changed(null, this.gateway_id);
        }
    }

    /**
     * Returns true if the gateway is currently selected on the checkout page.
     * 
     * @returns {bool}
     */
    wc_braintree.CheckoutGateway.prototype.is_gateway_selected = function() {
        return this.get_selected_gateway() === this.gateway_id;
    }

    /**
     * Return the selected payment gateway identified by [name="payment_method"]
     * 
     * @returns {String}
     */
    wc_braintree.CheckoutGateway.prototype.get_selected_gateway = function() {
        return $('input[name="payment_method"]:checked').val();
    }

    /**
     * Returns true if there are errors being shown on the checkout page.
     * 
     * @returns {bool}
     */
    wc_braintree.CheckoutGateway.prototype.has_checkout_error = function() {
        return $('#wc_braintree_checkout_error').length > 0;
    }

    /**
     * Returns true if the customer has selected a saved payment method.
     * 
     * @returns {bool}
     */
    wc_braintree.CheckoutGateway.prototype.is_payment_method_selected = function() {
        if ($(this.token_selector).length) {
            if ($(this.payment_type_selector + ':checked').val() === 'token') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Function that is called when teh display saved methods event is
     * triggered.
     */
    wc_braintree.CheckoutGateway.prototype.display_saved_methods = function(e,
        gateway_id) {
        if (this.gateway_id == gateway_id && this.$button) {
            this.$button.hide();
            this.show_place_order();
        }
    }

    /**
     * Function that is triggered when the display new payment method event is
     * fired.
     */
    wc_braintree.CheckoutGateway.prototype.display_new_payment_method_container = function(
        e, gateway_id) {
        if (this.gateway_id == gateway_id && this.$button) {
            if (!this.payment_method_received) {
                this.$button.show();
                this.hide_place_order();
            }
        }
    }

    /**
     * [needs_shipping description]
     * @return {[type]} [description]
     */
    wc_braintree.CheckoutGateway.prototype.needs_shipping = function() {
        return this.params.needs_shipping === "1";
    }

    /**
     * 
     */
    wc_braintree.CheckoutGateway.prototype.get_payment_token = function() {
        return $(this.token_selector).val();
    }

    wc_braintree.blockUI = {
        message: null,
        overlayCSS: {
            background: '#fff',
            opacity: 0.6
        }
    }

    /**
     * [set_use_nonce_option description]
     * @param {[type]} bool [description]
     */
    wc_braintree.CheckoutGateway.prototype.set_use_nonce_option = function(bool) {
        $('#' + this.gateway_id + '_use_nonce').prop("checked", bool).trigger('click');
    }

    /**
     * [set_payment_method description]
     * @param {[type]} id [description]
     */
    wc_braintree.CheckoutGateway.prototype.set_payment_method = function(payment_method) {
        $('[name="payment_method"][value="' + payment_method + '"]').prop("checked", true).trigger('click');
    }

    /** ********** PayPal ****************** */

    /**
     * @constructor
     */
    wc_braintree.PayPal = function() {
        wc_braintree.BaseGateway.call(this);
        if (typeof this.get_amount === 'undefined') {
            throw 'Instance must have get_amount function';
        }
        this.create_button();
    }

    wc_braintree.PayPal.prototype = Object.create(wc_braintree.BaseGateway.prototype);

    /**
     * @param {Event}
     * @param {Object}
     * @param {String}
     */
    wc_braintree.PayPal.prototype.create_instance = function(e, client, client_token) {
        return new Promise(function(resolve, reject) {
            this.clientInstance = client;
            this.client_token = client_token;
            this.initialize_fraud_tools();

            braintree.paypalCheckout.create({
                client: client,
                authorization: client_token,
                merchantAccountId: this.get_merchant_account()
            }, function(err, paypalInstance) {
                if (err) {
                    this.submit_error(err);
                    reject();
                    return;
                }
                this.paypalInstance = paypalInstance;
                resolve();
            }.bind(this));
        }.bind(this))
    }

    /**
     * Return fraud options specific to PayPal.
     */
    wc_braintree.PayPal.prototype.get_fraud_tool_options = function() {
        return $.extend({}, wc_braintree.BaseGateway.prototype.get_fraud_tool_options.call(this), {
            paypal: true
        })
    }

    /**
     * Creates the PayPal SmartButton then returns a Promise.
     * 
     * @returns {Promise}
     */
    wc_braintree.PayPal.prototype.create_button = function() {
        return new Promise(function(resolve, reject) {
            // remove any existing PayPal buttons.
            this.get_button_container().find('.paypal-button').remove();
            paypal.Button.render(this.render_options(), this.get_button_container()[0]);
            this.$button = this.get_button_container().find('.paypal-button');
            resolve();
        }.bind(this))
    }

    /**
     * @returns {Object}
     */
    wc_braintree.PayPal.prototype.render_options = function() {
        var options = {
            locale: this.params.locale,
            style: this.params.button_style,
            funding: this.get_funding(),
            env: this.params.environment,
            validate: function(actions) {
                this.actions = actions;
            }.bind(this),
            onClick: function(data) {

            }.bind(this),
            payment: function(resolve, reject) {
                var options = this.get_options();
                this.paypalInstance.createPayment(options, function(err, id) {
                    if (err) {
                        if (this.get_merchant_account() == "") {
                            err.code = "PAYPAL_MERCHANT_ACCOUNT_EMPTY";
                        } else if (options.shippingAddressOverride) {
                            err.code = "PAYPAL_INVALID_ADDRESS";
                        }
                        this.submit_error(err);
                        reject(err);
                    }
                    resolve(id);
                }.bind(this));
            }.bind(this),
            onAuthorize: function(data, actions) {
                return this.paypalInstance.tokenizePayment(data, function(err,
                    response) {
                    this.tokenize_response = response;
                    this.set_nonce(response.nonce);
                    this.set_device_data();
                    this.payment_method_received = true;
                    this.update_addresses(response.details);
                    $('[name="payment_method"]').val(this.gateway_id);
                    this.on_payment_method_received(response, data, actions);
                }.bind(this));
            }.bind(this),
            onCancel: function(data) {

            },
            onError: function(err) {
                if (err.message.indexOf('props.locale') > -1) {
                    this.submit_error(this.params.messages.invalid_locale);
                } else {
                    this.submit_error(err);
                }
            }.bind(this)
        }
        if (this.needs_shipping() && 'checkout' === this.params.options.flow) {
            options.onShippingChange = function(data, actions) {
                var address = data.shipping_address;
                this.update_shipping_address({
                    city: address.city,
                    state: address.state,
                    postcode: address.postal_code,
                    country: address.country_code
                }).then(function() {
                    actions.resolve();
                }).catch(function() {
                    actions.reject();
                });
            }.bind(this)
        }
        var match = null,
            locales = this.getBrowserLocales(),
            locale = locales.length > 0 ? locales[0].lang + '_' + locales[0].country : false;
        if (locale && (match = locale.match(/([\w]{2})_undefined/))) {
            locale = match[1] + '_' + match[1].toUpperCase();
        }
        if (locale && this.params.locales.indexOf(locale) > -1) {
            options.locale = locale;
        }
        return options;
    }

    wc_braintree.PayPal.prototype.get_user_locale = function() {
        var match = null,
            locales = this.getBrowserLocales(),
            locale = locales.length > 0 ? locales[0].lang + '_' + locales[0].country : false;
        if (locale && (match = locale.match(/([\w]{2})_undefined/))) {
            locale = match[1] + '_' + match[1].toUpperCase();
        }
        if (locale && this.params.locales.indexOf(locale) > -1) {
            return locale;
        }
        return this.params.locale;
    }

    /**
     * 
     */
    wc_braintree.PayPal.prototype.get_options = function() {
        var options = {
            amount: this.get_amount(),
            enableShippingAddress: this.needs_shipping(),
            shippingAddressEditable: this.needs_shipping()
        };
        if (this.needs_shipping()) {
            var prefix = this.get_shipping_prefix();
            if (this.valid_address(this.get_address_object(prefix), prefix)) {
                options.shippingAddressOverride = this.get_address_object(prefix);
            }
        }
        var options = $.extend({}, this.params.options, options);
        options.locale = this.get_user_locale();
        return options;
    }

    /**
     * @returns {Object}
     */
    wc_braintree.PayPal.prototype.get_funding = function() {
        return {
            allowed: [],
            disallowed: [paypal.FUNDING.CREDIT, paypal.FUNDING.CARD,
                paypal.FUNDING.VENMO, paypal.FUNDING.ELV, paypal.FUNDING.IDEAL, paypal.FUNDING.BANCONTACT,
                paypal.FUNDING.GIROPAY, paypal.FUNDING.SOFORT, paypal.FUNDING.EPS, paypal.FUNDING.MYBANK,
                paypal.FUNDING.MYBANK, paypal.FUNDING.P24, paypal.FUNDING.ZIMPLER
            ]
        }
    }

    /**
     * Returns a jQuery element that the PayPal button should be appended to.
     * 
     * @returns {jQuery}
     */
    wc_braintree.PayPal.prototype.get_button_container = function() {
        return $("#place_order").parent();
    }

    /**
     * Returns an object of all PayPal address properties
     * 
     * @returns {Object}
     */
    wc_braintree.PayPal.prototype.get_address_mappings = function() {
        return {
            recipientName: {
                set: function(val, prefix) {
                    var name = val.split(' ', 2);
                    if (name.length >= 1) {
                        $(prefix + '_first_name').val(name[0]);
                    }
                    if (name.length >= 2) {
                        $(prefix + '_last_name').val(name[1]);
                    }
                },
                get: function(prefix) {
                    return $(prefix + '_first_name').val() + " " +
                        $(prefix + '_last_name').val();
                },
                required: false
            },
            line1: {
                set: function(val, prefix) {
                    $(prefix + '_address_1').val(val);
                },
                get: function(prefix) {
                    return $(prefix + '_address_1').val();
                },
                required: true
            },
            line2: {
                set: function(val, prefix) {
                    return $(prefix + '_address_2').val(val);
                },
                get: function(prefix) {
                    return $(prefix + '_address_2').val();
                },
                required: false
            },
            city: {
                set: function(val, prefix) {
                    $(prefix + '_city').val(val);
                },
                get: function(prefix) {
                    return $(prefix + '_city').val();
                },
                required: true
            },
            countryCode: {
                set: function(val, prefix) {
                    $(prefix + '_country').val(val);
                },
                get: function(prefix) {
                    return $(prefix + '_country').val();
                },
                required: true
            },
            postalCode: {
                set: function(val, prefix) {
                    $(prefix + '_postcode').val(val);
                },
                get: function(prefix) {
                    return $(prefix + '_postcode').val();
                },
                required: true
            },
            state: {
                set: function(val, prefix) {
                    $(prefix + '_state').val(val);
                },
                get: function(prefix) {
                    return $(prefix + '_state').val();
                },
                required: function(prefix) {
                    if (typeof wc_address_i18n_params != 'undefined') {
                        var locales = JSON.parse(wc_address_i18n_params.locale),
                            country = $(prefix + '_country').val();
                        if (locales && locales[country]) {
                            if (locales[country].state) {
                                return typeof locales[country].state.required != 'undefined' ? locales[country].state.required : true;
                            }
                        }
                    }
                    return $(prefix + '_state').length > 0 && $(prefix + '_state').is(':visible');
                }
            },
            phone: {
                set: function(val, prefix) {
                    $('#billing_phone').val(val);
                },
                get: function(prefix) {
                    return $('#billing_phone').val();
                },
                required: false
            },

        }
    }

    /**
     * Update the WC billing and shipping address fields if required.
     * 
     * @param {Object{
     */
    wc_braintree.PayPal.prototype.update_addresses = function(details) {
        // only update billing address if it was empty when PayPal was initiated
        // or the billingAddress is returned from PayPal:
        if (!this.valid_address(this.get_address_object('#billing'), '#billing') || details.billingAddress) {
            if (details.billingAddress || (details.shippingAddress && !$('[name="ship_to_different_address"]').is(':checked'))) {
                var billingAddress = details.billingAddress ? details.billingAddress : details.shippingAddress;
                if (billingAddress && !$.isEmptyObject(billingAddress)) {
                    this.populate_address_fields(billingAddress, '#billing');
                }
            }
        }
        this.populate_detail_fields(details);
        if (details.shippingAddress && !$.isEmptyObject(details.shippingAddress)) {
            // if shipping enabled, then update shipping fields;
            if ($('[name^="shipping_"]').length > 0) {
                this.populate_address_fields(details.shippingAddress, '#shipping');
            }
            $('[name*="_state"], [name*="_country"]').trigger("change");
        }
    }

    /**
     * 
     */
    wc_braintree.PayPal.prototype.populate_address_fields = function(address, prefix) {
        $.each(this.get_address_mappings(), function(key, object) {
            if (address[key]) {
                object.set(address[key], prefix);
            }
        })
    }

    wc_braintree.PayPal.prototype.get_address_object = function(prefix) {
        var address = {};
        $.each(this.get_address_mappings(), function(key, object) {
            address[key] = object.get(prefix);
        })
        return address;
    }

    wc_braintree.PayPal.prototype.valid_address = function(address, prefix) {
        var valid = true;
        if ($.isEmptyObject(address)) {
            return false;
        }
        mappings = this.get_address_mappings();
        $.each(mappings, function(k, object) {
            var required = typeof object.required == 'function' ? object.required(prefix) : object.required;
            if (required) {
                if (!address[k] || typeof address[k] === 'undefined' ||
                    address[k].length == 0) {
                    valid = false;
                }
            }
        });
        return valid;
    }

    wc_braintree.PayPal.prototype.populate_detail_fields = function(details) {
        if (details.firstName) {
            $('[name="billing_first_name"]').val(details.firstName);
        }
        if (details.lastName) {
            $('[name="billing_last_name"]').val(details.lastName);
        }
        if (details.email) {
            $('[name="billing_email"]').val(details.email);
        }
        if (details.phone) {
            $('[name="billing_phone"]').val(details.phone);
        }
    }

    wc_braintree.PayPal.prototype.get_address_hash = function(prefix) {
        var fields = ['recipientName', 'line1', 'line2', 'city', 'state',
            'postalCode', 'countryCode'
        ];
        var mappings = this.get_address_mappings();
        var hash = '';
        for (var i = 0; i < fields.length; i++) {
            if (mappings[fields[i]]) {
                hash += mappings[fields[i]].get.call(this, prefix);
            }
        }
        return hash;
    }

    /**
     * Open the shipping method modal.
     * 
     * @param {Object}
     */
    wc_braintree.PayPal.prototype.open_shipping_modal = function(params, options) {
        var url = this.params.shipping_window_url,
            options = options ? options : {};
        if (params) {
            url += (url.indexOf('?') != -1 ? '&' : '?') + $.param(params);
        }
        $.ajax({
            method: 'GET',
            dataType: 'html',
            url: url,
            success: function(html) {
                this.unblock();
                openModal.call(this, html)
            }.bind(this),
            error: function() {
                this.unblock();
            }.bind(this)
        })

        function openModal(html) {
            var $overlay = $('<div>', { 'class': 'wc-braintree-shipping-modal-overlay' }),
                $modal = $('<div>', { 'class': 'wc-braintree-shipping-modal' });
            $modal.append(html).show();
            $('body').addClass('shipping-modal-active').append($overlay).append($modal);

            $('[name^="wc_shipping_method"]').on('click', update_shipping_method.bind(this));
            $('.wc-braintree-shipping-modal #place_order').on('click', process_order.bind(this));
            $('.wc-braintree-shipping-modal #close').on('click', closeModal);
        }

        function closeModal(e) {
            e.preventDefault();
            $('.wc-braintree-shipping-modal-overlay').remove();
            $('.wc-braintree-shipping-modal').remove();
            $('body').removeClass('shipping-modal-active');
        }

        function process_order(e) {
            closeModal(e);
            this.process_order();
        }

        function block() {
            $('#preloaderSpinner').height($('.paypal-shipping-methods').outerHeight() + 20).show();
            var height = $('.wc-braintree-shipping-modal').height();
            var scrollTop = $('.wc-braintree-shipping-modal').scrollTop();
            var top = (height / 2 + scrollTop);
            $('.spinWrap').css('top', top);
        }

        function unblock() {
            $('#preloaderSpinner').hide();
        }

        function update_shipping_method(e) {
            block();
            var method = {};
            method[$(e.target).data('index')] = $(e.target).val();
            this.update_shipping_method(method).then(function(response) {
                unblock();
                // update the cart totals html
                $('.cart-totals').replaceWith(response.data.cart_totals);
                if (options.onShippingMethodSelected) {
                    options.onShippingMethodSelected.call(this, data);
                }
            }.bind(this)).catch(function() {
                unblock();
            }.bind(this))
        }
    }

    /**
     * @param {Object}
     */
    wc_braintree.PayPal.prototype.map_shipping_address = function(address) {
        return {
            address_1: address.line1,
            address_2: address.line2,
            city: address.city,
            postcode: address.postalCode,
            state: address.state,
            country: address.countryCode
        }
    }

    /** ************** Google Pay *************************** */

    /**
     * 
     */
    wc_braintree.GooglePay = function() {
        wc_braintree.BaseGateway.call(this);
        this.create_payments_client();
        this.create_button();
    }

    wc_braintree.GooglePay.prototype = Object.create(wc_braintree.BaseGateway.prototype);

    wc_braintree.GooglePay.prototype.dynamic_price_enabled = function() {
        return this.params.dynamic_price === "1";
    }

    wc_braintree.GooglePay.prototype.get_environment = function() {
        return $('#wc_braintree_googlepay_environment').val();
    }

    wc_braintree.GooglePay.prototype.get_merchant_id = function() {
        return $('#wc_braintree_googlepay_merchant_id').val();
    }

    wc_braintree.GooglePay.prototype.get_total_price = function() {

    }

    /**
     * Called after the payment data in the Google payment sheet is updated.
     */
    wc_braintree.GooglePay.prototype.after_payment_data_callback = function() {

    }

    wc_braintree.GooglePay.prototype.create_payments_client = function() {
        var args = {
            environment: this.get_environment()
        }
        if (this.dynamic_price_enabled()) {
            args.paymentDataCallbacks = {
                onPaymentAuthorized: function(paymentData) {
                    return new Promise(function(resolve, reject) {
                        resolve({ transactionState: "SUCCESS" })
                    }.bind(this))
                }.bind(this)
            }
            if (this.needs_shipping()) {
                args.paymentDataCallbacks.onPaymentDataChanged = function(address) {
                    return new Promise(function(resolve, reject) {
                        this.payment_data_callback(address).then(function(response) {
                            resolve(response.data.requestUpdate);
                            this.after_payment_data_callback(response.data);
                        }.bind(this)).catch(function(response) {
                            resolve(response.data);
                        }.bind(this));
                    }.bind(this));
                }.bind(this)
            }
        }
        this.paymentsClient = new google.payments.api.PaymentsClient(args);
    }

    wc_braintree.GooglePay.prototype.create_instance = function(e, client, client_token) {
        return new Promise(function(resolve, reject) {
            this.clientInstance = client;
            braintree.googlePayment.create({
                client: client,
                googlePayVersion: 2,
                googleMerchantId: this.get_merchant_id()
            }, function(err, googlePayInstance) {
                if (err) {
                    this.submit_error(err);
                    return;
                }
                this.googlePayInstance = googlePayInstance;
                this.initialize_fraud_tools();
                this.paymentsClient.isReadyToPay({
                    apiVersion: 2,
                    apiVersionMinor: 0,
                    allowedPaymentMethods: googlePayInstance.createPaymentDataRequest().allowedPaymentMethods
                }).then(function(response) {
                    if (!response.result) {
                        this.cannot_pay = true;
                        reject(response);
                    } else {
                        this.cannot_pay = false;
                        resolve(response);
                    }
                }.bind(this)).catch(function(err) {
                    this.submit_error({
                        message: err.statusMessage
                    });
                    return;
                }.bind(this));
            }.bind(this))
        }.bind(this));
    }

    /**
     * 
     */
    wc_braintree.GooglePay.prototype.create_button = function() {
        return new Promise(function(resolve, reject) {
            if (this.$button) {
                this.$button.remove();
            }
            if (this.paymentsClient) {
                this.$button = $(this.paymentsClient.createButton($.extend({
                    onClick: this.tokenize.bind(this)
                }, this.params.button_options)));
                resolve();
            }
        }.bind(this))
    }

    /**
     * 
     */
    wc_braintree.GooglePay.prototype.tokenize = function() {
        var paymentDataRequest = this.googlePayInstance.createPaymentDataRequest(this.get_payment_data_request());
        var cardPaymentMethodDetails = paymentDataRequest.allowedPaymentMethods[0];
        cardPaymentMethodDetails.parameters.billingAddressRequired = true;
        cardPaymentMethodDetails.parameters.billingAddressParameters = {
            format: 'FULL',
            phoneNumberRequired: $('#billing_phone').length > 0
        }
        try {
            this.paymentsClient.loadPaymentData(paymentDataRequest).then(function(paymentData) {
                this.paymentData = paymentData;
                this.populate_address_fields(paymentData);
                return this.googlePayInstance.parseResponse(paymentData);
            }.bind(this)).then(function(response) {
                $(this.tokenized_response_selector).val(JSON.stringify(response));
                this.set_nonce(response.nonce);
                this.set_device_data();
                $('[name="payment_method"]').val(this.gateway_id);
                this.on_payment_method_received(response);
            }.bind(this)).catch(function(err) {
                if (err.statusCode === 'CANCELED') {
                    return;
                }
                var error = {
                    code: err.statusMessage.indexOf('whitelisted') > -1 ? "DEVELOPER_ERROR_WHITELIST" : null,
                    message: err.statusMessage
                };
                this.submit_error(error);
                return;
            }.bind(this));
        } catch (err) {
            this.submit_error({
                message: err
            });
        }
    }

    wc_braintree.GooglePay.prototype.get_payment_data_request = function() {
        var request = {
            transactionInfo: {
                currencyCode: $('#wc_braintree_cart_currency').val(),
                totalPriceStatus: 'FINAL',
                totalPrice: this.get_total_price(),
                totalPriceLabel: this.params.price_label,
                displayItems: this.get_display_items()
            },
            emailRequired: true,
        }
        if (this.dynamic_price_enabled()) {
            if (this.needs_shipping()) {
                request.shippingAddressParameters = {};
                request.shippingAddressRequired = true;
                request.callbackIntents = ['PAYMENT_AUTHORIZATION', 'SHIPPING_ADDRESS', 'SHIPPING_OPTION'];
                request.shippingOptionRequired = true;
            } else {
                request.callbackIntents = ['PAYMENT_AUTHORIZATION'];
            }
        }
        return request;
    }

    /**
     * [get_display_items description]
     * @return {[type]} [description]
     */
    wc_braintree.GooglePay.prototype.get_display_items = function() {
        return $('#wc_braintree_googlepay_display_items').data('items');
    }

    /**
     * [set_display_items description]
     * @param {[type]} items [description]
     */
    wc_braintree.GooglePay.prototype.set_display_items = function(items) {
        $('#wc_braintree_googlepay_display_items').data('items', items);
    }

    wc_braintree.GooglePay.prototype.populate_address_fields = function(paymentData) {
        if (paymentData.paymentMethodData.info.billingAddress) {
            this.populate_address(
                paymentData.paymentMethodData.info.billingAddress,
                '#billing');
            if (paymentData.email) {
                $('#billing_email').val(paymentData.email);
            }
        }
        // populate the shipping address fields
        if (paymentData.shippingAddress) {
            this.populate_address(paymentData.shippingAddress, '#shipping');
        }
    }

    wc_braintree.GooglePay.prototype.populate_address = function(address,
        prefix) {
        $.each(this.get_address_mappings(), function(key, object) {
            if (address[key]) {
                object.set(address[key], prefix);
            }
        })
    }

    wc_braintree.GooglePay.prototype.payment_data_callback = function(data) {
        return new Promise(function(resolve, reject) {
            $.when(this.api_update_address(data)).done(function(response) {
                if (response.code) {
                    reject(response.data);
                } else {
                    resolve(response);
                    this.set_selected_shipping_method(response.data.chosen_shipping_methods);
                }
            }.bind(this))
        }.bind(this))
    }

    wc_braintree.GooglePay.prototype.api_update_address = function(data) {
        return $.ajax({
            url: this.params.routes.shipping_data,
            method: 'POST',
            dataType: 'json',
            data: {
                address: data.shippingAddress,
                shippingOptions: data.shippingOptionData
            },
            beforeSend: function(xhr, settings) {
                xhr.setRequestHeader('X-WP-Nonce', this.params._wp_rest_nonce);
            }.bind(this),
        });
    }

    wc_braintree.GooglePay.prototype.valid_address = function(address) {
        var valid = true;
        if ($.isEmptyObject(address)) {
            return false;
        }
        mappings = this.get_address_mappings();
        $.each(mappings, function(k, object) {
            if (object.required) {
                if (!address[k] || typeof address[k] === 'undefined' ||
                    address[k].length == 0) {
                    valid = false;
                }
            }
        });
        return valid;
    }

    wc_braintree.GooglePay.prototype.get_address_object = function(prefix) {
        var address = {};
        $.each(this.get_address_mappings(), function(key, object) {
            address[key] = object.get(prefix);
        })
        return address;
    }

    wc_braintree.GooglePay.prototype.get_address_hash = function(prefix) {
        var fields = ['name', 'postalCode', 'countryCode', 'address1',
            'address2', 'locality', 'administrativeArea'
        ];
        var mappings = this.get_address_mappings();
        var hash = '';
        $.each(fields, function(i, k) {
            hash += mappings[k].get(prefix);
        });
        return hash;
    }

    wc_braintree.GooglePay.prototype.block = function() {
        $.blockUI({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
    }

    wc_braintree.GooglePay.prototype.unblock = function() {
        $.unblockUI();
    }

    wc_braintree.GooglePay.prototype.clear_error = function() {
        this.paymentData = null;
        $('#wc_braintree_checkout_error').remove();
    }

    wc_braintree.GooglePay.prototype.get_address_mappings = function() {
        return {
            name: {
                set: function(val, prefix) {
                    val = val.split(" ");
                    if (val.length >= 1) {
                        $(prefix + '_first_name').val(val[0]);
                    }
                    if (val.length >= 2) {
                        $(prefix + '_last_name').val(val[1]);
                    }
                },
                get: function(prefix) {
                    var name = "";
                    if ($(prefix + '_first_name').length) {
                        name = $(prefix + '_first_name').val();
                    }
                    if ($(prefix + '_last_name').length) {
                        name = name !== "" ? " " +
                            $(prefix + '_last_name').val() : $(
                                prefix + '_last_name').val();
                    }
                    return name;
                },
                required: true
            },
            postalCode: {
                set: function(val, prefix) {
                    $(prefix + '_postcode').val(val);
                },
                get: function(prefix) {
                    return $(prefix + '_postcode').val();
                },
                required: true
            },
            countryCode: {
                set: function(val, prefix) {
                    $(prefix + '_country').val(val);
                },
                get: function(prefix) {
                    return $(prefix + '_country').val();
                },
                required: true
            },
            phoneNumber: {
                set: function(val, prefix) {
                    $(prefix + '_phone').val(val);
                },
                get: function(prefix) {
                    return $(prefix + '_phone').val();
                }
            },
            address1: {
                set: function(val, prefix) {
                    $(prefix + '_address_1').val(val);
                },
                get: function(prefix) {
                    return $(prefix + '_address_1').val();
                },
                required: true
            },
            address2: {
                set: function(val, prefix) {
                    $(prefix + '_address_2').val(val);
                },
                get: function(prefix) {
                    return $(prefix + '_address_2').val();
                }
            },
            locality: {
                set: function(val, prefix) {
                    $(prefix + '_city').val(val);
                },
                get: function(prefix) {
                    return $(prefix + '_city').val();
                },
                required: true
            },
            administrativeArea: {
                set: function(val, prefix) {
                    $(prefix + '_state').val(val);
                },
                get: function(prefix) {
                    return $(prefix + '_state').val();
                },
                required: true
            }
        }
    }

    /** ***************** Credit Cards ************************** */

    wc_braintree.CreditCard = function() {
        wc_braintree.BaseGateway.call(this);
        this._3ds_vaulted_nonce_selector = '[name="' + this.params._3ds_vaulted_nonce_selector + '"]';
        this.config_selector = '[name="' + this.params.config_selector + '"]';
    }

    wc_braintree.CreditCard.prototype = Object.create(wc_braintree.BaseGateway.prototype);

    /**
     * @param {Event}
     * @param {Object}
     * @param {String}
     */
    wc_braintree.CreditCard.prototype.create_instance = function(e, client, client_token) {
        this.clientInstance = client;
        this.client_token = client_token;
        this.config = client.getConfiguration().gatewayConfiguration;
        this.initialize_fraud_tools();
        this.initialize_3d_secure();
    }

    /**
     * 
     */
    wc_braintree.CreditCard.prototype.initialize_3d_secure = function() {
        if (this._3ds_enabled()) {
            braintree.threeDSecure.create({
                version: 2,
                client: this.clientInstance
            }, function(err, threeDSecureInstance) {
                if (err) {
                    this.threeds_error = err;
                    this.submit_error(err);
                    return;
                }
                this.threeDSecureInstance = threeDSecureInstance;
            }.bind(this))
        }
    }

    /**
     * 
     */
    wc_braintree.CreditCard.prototype.set_config_data = function() {
        $(this.config_selector).val(JSON.stringify(this.config));
    }

    /**
     * 
     */
    wc_braintree.CreditCard.prototype.get_hosted_fields = function() {
        this.hosted_fields = {};
        var self = this;
        $.each(this.params.custom_fields, function(key, field) {
            if ($('#' + field.id).length && field.type) {
                self.hosted_fields[field.type] = {
                    selector: '#' + field.id,
                    placeholder: field.placeholder
                };
            }
        });
        $.each(this.hosted_fields, function(key, value) {
            var remove = false;
            switch (key) {
                case 'cvv':
                    if (self.config.challenges.indexOf('cvv') === -1) {
                        remove = true;
                    }
                    break;
                case 'postalCode':
                    if (self.config.challenges.indexOf('postal_code') === -1) {
                        remove = true;
                    }
                    break;
            }
            if (remove) {
                // remove field from html
                $(value.selector).closest('.' + key + '-container').remove();
                $(document.body).triggerHandler(
                    'wc_braintree_hosted_field_removed', key);
                delete self.hosted_fields[key];
            }
        });
        return this.hosted_fields;
    }

    /**
     * @returns {Boolean}
     */
    wc_braintree.CreditCard.prototype._3ds_enabled = function() {
        return $('#wc_braintree_3ds_enabled').val() === "1";
    }

    /**
     * @returns {Boolean}
     */
    wc_braintree.CreditCard.prototype._3ds_active = function() {
        return $('#wc_braintree_3ds_active').val() === "1";
    }

    /**
     * Request a payment nonce for use in 3DS vaulted requests.
     */
    wc_braintree.CreditCard.prototype.payment_nonce_request = function(token) {
        return $.ajax({
            type: 'POST',
            url: this.params.urls._3ds.vaulted_nonce,
            data: {
                _wpnonce: this.params._wp_rest_nonce,
                token: token,
                version: 2
            },
        });
    }

    /**
     * Return options specific to the tokenization process.
     * 
     * @returns {Object}
     */
    wc_braintree.CreditCard.prototype.get_tokenization_options = function() {
        var options = {
            billingAddress: {}
        }
        if ($('#billing_address_1').length) {
            options.billingAddress.streetAddress = $('#billing_address_1').val();
        }
        if ($('#billing_postcode').length) {
            options.billingAddress.postalCode = $('#billing_postcode').val();
        }
        return options;
    }

    /**
     * 
     */
    wc_braintree.CreditCard.prototype.process_3dsecure = function(response, isVaulted) {
        if (this.threeDSecureInstance) {
            var prefix = this.get_shipping_prefix();
            this.threeDSecureInstance.verifyCard({
                amount: this.has_recurring_total() ? this.get_recurring_cart_total() : this.get_cart_total(),
                nonce: response.nonce,
                bin: response.details.bin,
                email: $('#billing_email').val(),
                billingAddress: {
                    givenName: this.get_first_name('#billing').replace(/[^\x00-\x7f]/g, ""),
                    surname: this.get_last_name('#billing').replace(/[^\x00-\x7f]/g, ""),
                    phoneNumber: $('#billing_phone').val(),
                    streetAddress: $('#billing_address_1').val(),
                    extendedAddress: $('#billing_address_2').val(), // available
                    locality: $('#billing_city').val(),
                    region: $('#billing_state').val(),
                    postalCode: $('#billing_postcode').val(),
                    countryCodeAlpha2: $('#billing_country').val()
                },
                additionalInformation: this.needs_shipping() ? {
                    shippingGivenName: this.get_first_name(prefix).replace(/[^\x00-\x7f]/g, ""),
                    shippingSurname: this.get_last_name(prefix).replace(/[^\x00-\x7f]/g, ""),
                    shippingAddress: {
                        streetAddress: $(prefix + '_address_1').val(),
                        extendedAddress: $(prefix + '_address_2').val(),
                        locality: $(prefix + '_city').val(),
                        region: $(prefix + '_state').val(),
                        postalCode: $(prefix + '_postcode').val(),
                        countryCodeAlpha2: $(prefix + '_country').val()
                    }
                } : {},
                onLookupComplete: function(data, next) {
                    this.remove_loader();
                    next();
                }.bind(this)
            }, function(err, payload) {
                if (err) {
                    this.submit_error(err);
                    this.remove_loader();
                    return;
                }
                if (isVaulted) {
                    $(this._3ds_vaulted_nonce_selector).val('true');
                }
                this.on_payment_method_received(payload);
            }.bind(this));
            if (isVaulted) {
                this.unblock_form();
            }
        }
    }

    /**
     * 
     */
    wc_braintree.CreditCard.prototype.process_3dsecure_vaulted = function() {
        this.block_form();
        $.when(this.payment_nonce_request(this.get_payment_token())).done(
            function(response) {
                if (response.success) {
                    this.process_3dsecure(response.data, true);
                } else {
                    this.submit_error(response.message);
                    this.unblock_form();
                }
            }.bind(this)).fail(function(jqXHR, textStatus, errorThrown) {
            this.submit_error({
                message: errorThrown
            });
            this.unblock_form();
        }.bind(this));
    }

    wc_braintree.CreditCard.prototype.add_icon_class = function(e, data, container) {
        $(container).addClass(this.params.icon_style);
    }

    wc_braintree.CreditCard.prototype.add_icon_type = function() {
        $(this.container).find('.wc-braintree-payment-methods-container').addClass(this.params.icon_style + '-icons');
    }

    /**
     * 
     */
    wc_braintree.CreditCard.prototype.block_form = function() {
        $(this.container).closest('form').block(wc_braintree.blockUI);
    }

    /**
     * 
     */
    wc_braintree.CreditCard.prototype.unblock_form = function() {
        $(this.container).closest('form').unblock();
    }

    /**
     * 
     */
    wc_braintree.CreditCard.prototype.display_loader = function() {
        if (this.params.loader.enabled) {
            $('.wc-braintree-payment-loader').fadeIn(200);
        }
    }

    /**
     * 
     */
    wc_braintree.CreditCard.prototype.remove_loader = function() {
        $('.wc-braintree-payment-loader').fadeOut(200);
    }

    /** ********************************************************* */

    /** **************** Apple Pay ***************************** */

    wc_braintree.ApplePay = function() {
        wc_braintree.BaseGateway.call(this);
        if (typeof this.get_total_price === 'undefined') {
            throw 'get_total_price function must be implemented.'
        }
    }

    wc_braintree.ApplePay.prototype = Object.create(wc_braintree.BaseGateway.prototype);

    /**
     * 
     */
    wc_braintree.ApplePay.prototype.create_instance = function(e, client, client_token) {
        this.clientInstance = client;
        this.initialize_fraud_tools();
        return new Promise(function(resolve, reject) {
            if (this.can_initialize_applepay()) {
                braintree.applePay.create({
                    client: this.clientInstance
                }, function(err, applePayInstance) {
                    if (err) {
                        reject();
                        this.submit_error(err);
                        return;
                    }
                    this.applePayInstance = applePayInstance;
                    var promise = ApplePaySession.canMakePaymentsWithActiveCard(applePayInstance.merchantIdentifier);
                    promise.then(function(bool) {
                        if (bool) {
                            resolve();
                        } else {
                            reject();
                        }
                    });
                }.bind(this));
            } else {
                reject();
            }
        }.bind(this))
    }

    /**
     * 
     */
    wc_braintree.ApplePay.prototype.open_wallet = function() {
        this.applePaySession.begin();
    }

    /**
     * 
     */
    wc_braintree.ApplePay.prototype.init_wallet = function() {
        var paymentRequest = this.applePayInstance.createPaymentRequest(this.get_payment_request());
        try {
            var applePaySession = new ApplePaySession(this.get_applepay_version(), paymentRequest);
            this.applePaySession = applePaySession;
            applePaySession.onvalidatemerchant = this.onvalidatemerchant.bind(this);
            if (!this.needs_shipping() && !$(document.body).hasClass('woocommerce-change-payment-method') && !$(document.body).hasClass('woocommerce-order-pay')) {
                applePaySession.onpaymentmethodselected = this.onpaymentmethodselected.bind(this);
            }
            if (this.needs_shipping()) {
                applePaySession.onshippingcontactselected = this.onshippingcontactselected.bind(this);
                applePaySession.onshippingmethodselected = this.onshippingmethodselected.bind(this);
            }
            applePaySession.onpaymentauthorized = this.onpaymentauthorized.bind(this);
        } catch (err) {
            this.submit_error(err);
        }
    }

    /**
     * Returns the ApplePay version that the iOS device supports.
     * @return {[type]} [description]
     */
    wc_braintree.ApplePay.prototype.get_applepay_version = function() {
        if (ApplePaySession.supportsVersion(4)) {
            return 4;
        } else if (ApplePaySession.supportsVersion(3)) {
            return 3;
        }
    }

    /**
     * Function that returns a payment request object used in establishing an
     * ApplePaySession.
     * 
     * @returns {Object}
     */
    wc_braintree.ApplePay.prototype.get_payment_request = function() {
        return {
            total: {
                label: this.params.store_name,
                amount: this.get_total_price(),
                type: 'final'
            },
            lineItems: this.get_line_items(),
            currencyCode: this.get_currency(),
            requiredBillingContactFields: this.get_billing_fields_array(),
            requiredShippingContactFields: this.get_shipping_fields_array(),
        }
    }

    wc_braintree.ApplePay.prototype.get_line_items = function() {
        return $('#wc_braintree_applepay_line_items').data('items');
    }

    wc_braintree.ApplePay.prototype.set_line_items = function(items) {
        $('#wc_braintree_applepay_line_items').data('items', items);
    }

    wc_braintree.ApplePay.prototype.get_address_mappings = function() {
        return {
            "givenName": {
                get: function(prefix) {
                    return $(prefix + "_first_name").val();
                },
                set: function(v, prefix) {
                    $(prefix + "_first_name").val(v);
                },
                contactField: 'name',
                isValid: function(v) {
                    return typeof v !== "undefined" &&
                        (typeof v === "string" && v.trim() !== "");
                }
            },
            "familyName": {
                get: function(prefix) {
                    return $(prefix + "_last_name").val();
                },
                set: function(v, prefix) {
                    $(prefix + "_last_name").val(v);
                },
                contactField: 'name',
                isValid: function(v) {
                    return typeof v !== "undefined" &&
                        (typeof v === "string" && v.trim() !== "");
                }
            },
            "addressLines": {
                get: function(prefix) {
                    var addressLines = [];
                    if ($(prefix + "_address_1").val() != '') {
                        addressLines.push($(prefix + "_address_1").val());
                    }
                    if ($(prefix + "_address_2").val() != '') {
                        addressLines.push($(prefix + "_address_2").val());
                    }
                    return addressLines;
                },
                set: function(v, prefix) {
                    $(prefix + "_address_1").val(v[0]);
                    if (v.length == 2) {
                        $(prefix + "_address_2").val(v[1]);
                    }
                },
                contactField: 'addressLines',
                isValid: function(v) {
                    var street1 = v[0];
                    if (typeof street1 === "undefined" ||
                        (typeof street1 === "string" && street1.trim() === "")) {
                        return false;
                    }
                    return true;
                }
            },
            "administrativeArea": {
                get: function(prefix) {
                    return $(prefix + "_state").val();
                },
                set: function(v, prefix) {
                    if (v.length == 2) {
                        $(prefix + "_state").val(v.toUpperCase());
                    } else {
                        $(prefix + '_state option').each(function() {
                            var $this = $(this);
                            var text = $this.text().toLowerCase();
                            v = v.toLowerCase();
                            if (text.indexOf(v) >= 0) {
                                $(prefix + "_state").val($this.val());
                            }
                        });
                    }
                },
                contactField: 'administrativeArea',
                isValid: function(v, address) {
                    // validate state value
                    var states = this.params.states[address.countryCode];
                    if (states && !$.isEmptyObject(states)) {
                        if (typeof v !== "undefined" && (typeof v === "string" && v.trim() !== "")) {
                            return typeof states[v] !== 'undefined';
                        }
                        return false;
                    } else {
                        return true;
                    }
                }
            },
            "locality": {
                get: function(prefix) {
                    return $(prefix + "_city").val();
                },
                set: function(v, prefix) {
                    $(prefix + "_city").val(v);
                },
                contactField: 'locality',
                isValid: function(v) {
                    return typeof v !== "undefined" &&
                        (typeof v === "string" && v.trim() !== "");
                }
            },
            "postalCode": {
                get: function(prefix) {
                    return $(prefix + "_postcode").val();
                },
                set: function(v, prefix) {
                    $(prefix + "_postcode").val(v);
                },
                contactField: 'postalCode',
                isValid: function(v) {
                    return typeof v !== "undefined" &&
                        (typeof v === "string" && v.trim() !== "");
                }
            },
            "phoneNumber": {
                get: function(prefix) {
                    return $("#billing_phone").val();
                },
                set: function(v, prefix) {
                    $("#billing_phone").val(v);
                },
                contactField: 'phoneNumber',
                isValid: function(v) {
                    return typeof v !== "undefined" &&
                        (typeof v === "string" && v.trim() !== "");
                }
            },
            "emailAddress": {
                get: function(prefix) {
                    return $("#billing_email").val();
                },
                set: function(v, prefix) {
                    $("#billing_email").val(v);
                },
                contactField: 'emailAddress',
                isValid: function(v) {
                    return typeof v !== "undefined" &&
                        (typeof v === "string" && v.trim() !== "");
                }
            },
            "countryCode": {
                get: function(prefix) {
                    return $(prefix + "_country").val();
                },
                set: function(v, prefix) {
                    $(prefix + "_country").val(v.toUpperCase());
                },
                contactField: 'countryCode',
                isValid: function(v) {
                    return typeof v !== "undefined" &&
                        (typeof v === "string" && v.trim() !== "");
                }
            }
        }
    }

    wc_braintree.ApplePay.prototype.populate_address_fields = function(address,
        prefix) {
        var mappings = this.get_address_mappings();
        for (var k in address) {
            if (mappings[k]) {
                var v = address[k];
                mappings[k].set.call(this, v, prefix);
            }
        }
    }

    wc_braintree.ApplePay.prototype.get_address_hash = function(prefix) {
        var fields = ['givenName', 'familyName', 'addressLines',
            'administrativeArea', 'locality', 'postalCode', 'countryCode'
        ];
        var mappings = this.get_address_mappings();
        var hash = '';
        $.each(fields, function(i, k) {
            hash += mappings[k].get(prefix);
        });
        return hash;
    }

    wc_braintree.ApplePay.prototype.validate_response = function(response) {
        var errors = [],
            mappings = this.get_address_mappings();
        var messages = this.params.messages.errors;
        if (response.shippingContact) {
            var shippingMappings = this.needs_shipping() ? mappings : {
                'emailAddress': mappings['emailAddress'],
                'phoneNumber': mappings['phoneNumber']
            };
            for (var k in response.shippingContact) {
                if (shippingMappings[k]) {
                    var v = response.shippingContact[k];
                    if (!shippingMappings[k].isValid.call(this, v, response.shippingContact)) {
                        errors.push(new ApplePayError('shippingContactInvalid',
                            shippingMappings[k].contactField));
                    }
                }
            }
        }
        if (response.billingContact) {
            for (var k in response.billingContact) {
                if (mappings[k]) {
                    var v = response.billingContact[k];
                    if (!mappings[k].isValid.call(this, v, response.billingContact)) {
                        var message = messages['invalid_' +
                            mappings[k].contactField] ? messages['invalid_' + mappings[k].contactField] : "";
                        errors.push(new ApplePayError('billingContactInvalid',
                            mappings[k].contactField, message));
                    }
                }
            }
        }
        return errors;
    }

    wc_braintree.ApplePay.prototype.get_billing_fields_array = function() {
        return ['postalAddress'];
    }

    wc_braintree.ApplePay.prototype.get_shipping_fields_array = function() {
        var fields = [];
        if (this.needs_shipping()) {
            fields.push("name", "postalAddress");
        }
        if ($("#billing_email").length) {
            fields.push("email");
        }
        if ($("#billing_phone").length) {
            fields.push("phone");
        }
        return fields;
    }

    wc_braintree.ApplePay.prototype.populate_address_data = function(response) {
        return new Promise(function(resolve, reject) {
            if (response.shippingContact) {
                // populate the shipping fields
                this.populate_address_fields(response.shippingContact,
                    '#shipping');
            }
            if (response.billingContact) {
                // populate the billing fields
                this.populate_address_fields(response.billingContact,
                    '#billing');
            }
            resolve(response);
        }.bind(this))
    }

    wc_braintree.ApplePay.prototype.can_initialize_applepay = function() {
        return window.ApplePaySession && ApplePaySession.canMakePayments();
    }

    wc_braintree.ApplePay.prototype.has_nonce = function() {
        return $(this.nonce_selector).val().length > 0;
    }

    wc_braintree.ApplePay.prototype.onvalidatemerchant = function(event) {
        this.applePayInstance.performValidation({
            validationURL: event.validationURL,
            displayName: this.params.store_name
        }, function(err, merchantSession) {
            if (err) {
                this.submit_error(err);
                this.applePaySession.abort();
                return;
            }
            this.applePaySession.completeMerchantValidation(merchantSession);
        }.bind(this))
    }

    /**
     * 
     */
    wc_braintree.ApplePay.prototype.onshippingcontactselected = function(event) {
        this.update_shipping_address({
            country: event.shippingContact.countryCode,
            state: event.shippingContact.administrativeArea,
            postcode: event.shippingContact.postalCode,
            city: event.shippingContact.locality
        }).then(function(response) {
            this.can_ship = true;
            this.applePaySession.completeShippingContactSelection(response.data.shippingContactUpdate);
        }.bind(this)).catch(function(response) {
            if (response.code === 'addressUnserviceable') {
                this.can_ship = false;
                this.applePaySession.completeShippingContactSelection(response.data.shippingContactUpdate);
            } else {
                this.applePaySession.completeShippingContactSelection({
                    errors: [new ApplePayError(
                        response.code,
                        response.data.contactField,
                        response.message)],
                    newTotal: response.data.newTotal,
                    newShippingMethods: response.data.newShippingMethods
                });
            }
        }.bind(this))
    }

    /**
     * 
     */
    wc_braintree.ApplePay.prototype.onshippingmethodselected = function(event) {
        var identifier = event.shippingMethod.identifier,
            method = {},
            matches = identifier.match(/([\w]+)\:(.+)/);
        method[matches[1]] = matches[2];
        this.update_shipping_method(method).then(function(response) {
            if (response.code) {
                this.applePaySession.abort();
                this.submit_error(response.messages);
            } else {
                this.applePaySession.completeShippingMethodSelection(response.data.shippingMethodUpdate);
            }
        }.bind(this)).catch(function() {
            this.applePaySession.abort();
            this.submit_error(errorThrown);
        }.bind(this))
    }

    /**
     * 
     */
    wc_braintree.ApplePay.prototype.onpaymentmethodselected = function(event) {
        $.ajax({
            url: this.params.routes.applepay_payment_method,
            method: 'POST',
            dataType: 'json',
            beforeSend: function(xhr, settings) {
                xhr.setRequestHeader('X-WP-Nonce', this.params._wp_rest_nonce);
            }.bind(this),
            success: function(response, status, jqXHR) {
                if (response.success) {
                    this.applePaySession.completePaymentMethodSelection(response.data);
                } else {
                    this.applePaySession.abort();
                    this.submit_error(response.messages);
                    return;
                }
            }.bind(this),
            error: function(jqXHR, textStatus, errorThrown) {
                this.applePaySession.abort();
                this.submit_error(errorThrown);
            }.bind(this)
        })
    }

    /**
     * 
     */
    wc_braintree.ApplePay.prototype.onpaymentauthorized = function(event) {
        return new Promise(function(resolve, reject) {
            this.applePayInstance.tokenize({
                token: event.payment.token
            }, function(err, response) {
                if (err) {
                    this.submit_error(err);
                    this.applePaySession.completePayment(ApplePaySession.STATUS_FAILURE);
                    return;
                }
                var errors = this.validate_response(event.payment);
                if (errors.length > 0) {
                    this.applePaySession.completePayment({
                        status: ApplePaySession.STATUS_FAILURE,
                        errors: errors
                    });
                    reject(response);
                } else if (this.needs_shipping() && !this.can_ship) {
                    this.applePaySession.completePayment({
                        status: ApplePaySession.STATUS_FAILURE,
                        errors: [new ApplePayError('addressUnserviceable')]
                    });
                    reject(response);
                } else {
                    this.applePaySession.completePayment(ApplePaySession.STATUS_SUCCESS);
                    this.set_nonce(response.nonce);
                    this.set_device_data();
                    this.populate_address_data(event.payment);
                    $('[name="payment_method"]').val(this.gateway_id);
                    this.on_payment_method_received(response);
                    resolve(response);
                }
            }.bind(this))
        }.bind(this))
    }

}(jQuery))