<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-header.php';
?>
    <div class="wcdg-main-left-section res-cl">
        <div class="product_header_title">
            <h2><?php 
esc_html_e( 'Setting', WCDG_TEXT_DOMAIN );
?></h2>
        </div>
        <?php 
global  $woocommerce ;
if ( isset( $_POST['submit_setting'] ) ) {
    // verify nonce
    
    if ( !isset( $_POST['woo_checkout_digital_goods'] ) || !wp_verify_nonce( sanitize_text_field( $_POST['woo_checkout_digital_goods'] ), basename( __FILE__ ) ) ) {
        die( 'Failed security check' );
    } else {
        $data_post = $_POST;
        
        if ( $data_post ) {
            $general_setting_data = maybe_serialize( $data_post );
            update_option( 'wcdg_checkout_setting', $general_setting_data );
        }
    
    }

}
$wcdg_general_setting = maybe_unserialize( get_option( 'wcdg_checkout_setting' ) );
$wcdg_status = ( isset( $wcdg_general_setting['wcdg_status'] ) && !empty($wcdg_general_setting['wcdg_status']) ? 'checked' : '' );
$wcdg_ch_field = ( isset( $wcdg_general_setting['wcdg_chk_field'] ) && !empty($wcdg_general_setting['wcdg_chk_field']) ? $wcdg_general_setting['wcdg_chk_field'] : '' );
$wcdg_chk_order_note = ( isset( $wcdg_general_setting['wcdg_chk_order_note'] ) && !empty($wcdg_general_setting['wcdg_chk_order_note']) ? 'checked' : '' );
$wcdg_chk_prod = ( isset( $wcdg_general_setting['wcdg_chk_prod'] ) && !empty($wcdg_general_setting['wcdg_chk_prod']) ? 'checked' : '' );
$wcdg_chk_details = ( isset( $wcdg_general_setting['wcdg_chk_details'] ) && !empty($wcdg_general_setting['wcdg_chk_details']) ? 'checked' : '' );
$wcdg_chk_on = ( isset( $wcdg_general_setting['wcdg_chk_on'] ) && !empty($wcdg_general_setting['wcdg_chk_on']) ? $wcdg_general_setting['wcdg_chk_on'] : 'wcdg_down_virtual' );
$wcdg_user_role_field = ( isset( $wcdg_general_setting['wcdg_user_role_field'] ) && !empty($wcdg_general_setting['wcdg_user_role_field']) ? $wcdg_general_setting['wcdg_user_role_field'] : '' );
?>
        <form method="POST" name="" action="">
            <?php 
wp_nonce_field( basename( __FILE__ ), 'woo_checkout_digital_goods' );
?>
            <table class="form-table wcdg-table-outer product-fee-table">
                <tbody>
                <tr valign="top">
                    <th class="titledesc" scope="row">
                        <label for="perfect_match_title"><?php 
esc_html_e( 'Enable / Disable', WCDG_TEXT_DOMAIN );
?></label></th>
                    <td class="forminp mdtooltip">
                        <label class="switch">
                            <input type="checkbox" name="wcdg_status" value="on" <?php 
echo  esc_attr( $wcdg_status ) ;
?>>
                            <div class="slider round"></div>
                        </label>
                        <span class="wcdg_tooltip_icon"></span>
                        <p class="wcdg_tooltip_desc description">
                            <?php 
esc_html_e( 'Enable or Disable Digital Goods for WooCommerce Checkout Plugin', WCDG_TEXT_DOMAIN );
?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th class="titledesc" scope="row">
                        <label for="perfect_match_title"><?php 
esc_html_e( 'Select Exclude Field on Checkout', WCDG_TEXT_DOMAIN );
?></label></th>
                    <td class="forminp mdtooltip">
                        <?php 
$wcdg_fields = array(
    "billing_first_name" => "First Name",
    "billing_last_name"  => "Last Name",
    "billing_company"    => "Company Name",
    "billing_address_1"  => "Billing Address",
    "billing_address_2"  => "Billing Address two",
    "billing_city"       => "Billing City",
    "billing_postcode"   => "Postal Code",
    "billing_country"    => "Billing Country",
    "billing_state"      => "Billing State",
    "billing_phone"      => "Billing Phone",
);
echo  '<select name = "wcdg_chk_field[]" multiple="multiple" class="multiselect2">' ;
foreach ( $wcdg_fields as $wcdg_field_key => $wcdg_field ) {
    $selectedVal = ( is_array( $wcdg_ch_field ) && !empty($wcdg_ch_field) && in_array( $wcdg_field_key, $wcdg_ch_field, true ) ? 'selected=selected' : '' );
    echo  '<option value="' . esc_attr( $wcdg_field_key ) . '" ' . esc_attr( $selectedVal ) . '>' . esc_html__( $wcdg_field, WCDG_TEXT_DOMAIN ) . '</option>' ;
}
echo  '</select>' ;
?>
                        <span class="wcdg_tooltip_icon"></span>
                        <p class="wcdg_tooltip_desc description">
                            <?php 
esc_html_e( 'Select fields which you want to exclude on checkout page.', WCDG_TEXT_DOMAIN );
?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th class="titledesc" scope="row">
                        <label for="perfect_match_title"><?php 
esc_html_e( 'Exclude Order Note', WCDG_TEXT_DOMAIN );
?></label></th>
                    <td class="forminp mdtooltip">
                        <label>
                            <input type="checkbox" name="wcdg_chk_order_note" value="on" <?php 
echo  esc_attr( $wcdg_chk_order_note ) ;
?>>
                        </label>
                        <span class="wcdg_tooltip_icon"></span>
                        <p class="wcdg_tooltip_desc description">
                            <?php 
esc_html_e( 'Enable Order Note which you want to exclude on checkout page.', WCDG_TEXT_DOMAIN );
?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th class="titledesc" scope="row">
                        <label for="perfect_match_title"><?php 
esc_html_e( 'Quick Checkout Button display on Shop page', WCDG_TEXT_DOMAIN );
?></label></th>
                    <td class="forminp mdtooltip">
                        <label >
                            <input type="checkbox" name="wcdg_chk_prod" value="on" <?php 
echo  esc_attr( $wcdg_chk_prod ) ;
?>>
                        </label>
                        <span class="wcdg_tooltip_icon"></span>
                        <p class="wcdg_tooltip_desc description">
                            <?php 
esc_html_e( 'Display Quick Checkout Button on Shop Page for Digital Product', WCDG_TEXT_DOMAIN );
?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th class="titledesc" scope="row">
                        <label for="perfect_match_title"><?php 
esc_html_e( 'Quick Checkout Button display on Product Details page', WCDG_TEXT_DOMAIN );
?></label></th>
                    <td class="forminp mdtooltip">
                        <label>
                            <input type="checkbox" name="wcdg_chk_details" value="on" <?php 
echo  esc_attr( $wcdg_chk_details ) ;
?>>
                        </label>
                        <span class="wcdg_tooltip_icon"></span>
                        <p class="wcdg_tooltip_desc description">
                            <?php 
esc_html_e( 'Display Quick Checkout Button on Product Details Page for Digital Product', WCDG_TEXT_DOMAIN );
?>
                        </p>
                    </td>
                </tr>
                <?php 
?>
                    <tr valign="top">
                        <th class="titledesc" scope="row">
                            <label for="perfect_match_title"><?php 
esc_html_e( 'Quick Checkout On', WCDG_TEXT_DOMAIN );
?></label></th>
                        <td class="forminp mdtooltip">
                            <input type="radio" name="wcdg_chk_on" value="wcdg_down_virtual" <?php 
checked( $wcdg_chk_on, 'wcdg_down_virtual' );
?>> <?php 
esc_html_e( 'Quick Checkout for all downloadable and/or virtual products', WCDG_TEXT_DOMAIN );
?><br>
                            <input type="radio" name="" value="" class="wcdg_read_only"> <?php 
esc_html_e( 'Manually Quick Checkout List for Product/Category/Tag ', WCDG_TEXT_DOMAIN );
?> <label class="pro-version"><?php 
esc_html_e( 'In Pro Version', WCDG_TEXT_DOMAIN );
?></label><br>
                        </td>
                    </tr>
                <?php 
?>
                </tbody>
            </table>
            <p class="submit"><input type="submit" name="submit_setting" class="button button-primary button-large" value="<?php 
esc_html_e( 'Save', WCDG_TEXT_DOMAIN );
?>"></p>
        </form>
    </div>

<?php 
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-sidebar.php';