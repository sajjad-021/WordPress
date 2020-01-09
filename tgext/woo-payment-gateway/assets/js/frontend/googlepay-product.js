(function($, wc_braintree) {

    var GooglePay = function() {
        wc_braintree.GooglePay.call(this);
    };

    GooglePay.prototype = $.extend({}, wc_braintree.GooglePay.prototype, wc_braintree.ProductGateway.prototype, {
        params: wc_braintree_googlepay_product_params,
    });

    /**
     * 
     */
    GooglePay.prototype.initialize = function() {
        wc_braintree.ProductGateway.call(this);
    }

    /**
     * 
     */
    GooglePay.prototype.create_instance = function(e, client, client_token) {
        wc_braintree.GooglePay.prototype.create_instance.apply(this, arguments).then(function() {
            $(this.container).show();
        }.bind(this)).catch(function() {
            $(this.container).hide();
        })
    }

    /**
     * 
     */
    GooglePay.prototype.create_button = function() {
        wc_braintree.GooglePay.prototype.create_button.call(this).then(function() {
            this.$button.addClass('wc-braintree-googlepay-button-container');
            $(this.container).append(this.$button);
        }.bind(this));
    }

    /**
     * 
     */
    GooglePay.prototype.get_total_price = function() {
        return this.get_cart_total();
    }

    /**
     * 
     */
    GooglePay.prototype.tokenize = function(e) {
        this.add_to_cart(e).then(function(response) {
            this.set_display_items(response.data.displayItems);
            this.set_cart_total(response.data.total);
            wc_braintree.GooglePay.prototype.tokenize.call(this);
        }.bind(this));
    }

    /**
     * 
     */
    GooglePay.prototype.get_button = function() {
        return this.$button.find('button');
    }

    GooglePay.prototype.get_process_order_data = function() {
        return {
            order_review: !this.dynamic_price_enabled()
        }
    }

    new GooglePay();
}(jQuery, wc_braintree))