<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.multidots.com
 * @since      1.0.0
 *
 * @package    Woo_Checkout_For_Digital_Goods
 * @subpackage Woo_Checkout_For_Digital_Goods/public
 */
class Woo_Checkout_For_Digital_Goods_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/woo-checkout-for-digital-goods-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woo-checkout-for-digital-goods-public.js', array('jquery'), $this->version, false);
    }

    /**
     * Function for remove checkout fields.
     */
    public function custom_override_checkout_fields($fields) {

	    global $woocommerce,$product;
	    $woo_checkout_field_array = get_option('wcdg_checkout_fields');

	    $temp_product = array();

	    $temp_product_flag = 1;
	    // basic checks

	    foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
		    $_product = $values['data'];
		    if ( ! $_product->is_virtual() && ! $_product->is_downloadable()) {
			    $temp_product_flag = 0; break;
		    }
	    }
	    if (0 === $temp_product_flag ) {
            return $fields;
        } else {
            if (isset($woo_checkout_field_array) && !empty($woo_checkout_field_array)) {
                $woo_checkout_field_array_serilize = maybe_unserialize($woo_checkout_field_array);
                if(isset($woo_checkout_field_array_serilize) && !empty($woo_checkout_field_array_serilize)) {
	                foreach ( $woo_checkout_field_array_serilize as $key => $values ) {
		                if ( $values == 'order_comments' ) {
			                unset( $fields['order']['order_comments'] );
		                } else {
			                unset( $fields['billing'][ $values ] );
		                }
	                }
                }
            } else {

                unset($fields['billing']['billing_company']);
                unset($fields['billing']['billing_address_1']);
                unset($fields['billing']['billing_address_2']);
                unset($fields['billing']['billing_city']);
                unset($fields['billing']['billing_postcode']);
                unset($fields['billing']['billing_country']);
                unset($fields['billing']['billing_state']);
                unset($fields['billing']['billing_phone']);
                unset($fields['order']['order_comments']);
                unset($fields['billing']['billing_address_2']);
                unset($fields['billing']['billing_postcode']);
                unset($fields['billing']['billing_company']);
                unset($fields['billing']['billing_city']);
                return $fields;
            }
        }
        return $fields;
    }

    /**
     * BN code added
     */
    function paypal_bn_code_filter_woo_checkout_field($paypal_args) {
        $paypal_args['bn'] = 'Multidots_SP';
        return $paypal_args;
    }
}