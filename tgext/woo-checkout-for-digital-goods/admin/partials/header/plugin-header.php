<?php

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
global  $wcfdg_fs ;
$plugin_name = WCDG_PLUGIN_NAME;
$plugin_version = WCDG_PLUGIN_VERSION;
?>
<div id="dotsstoremain">
    <div class="all-pad">
        <header class="dots-header">
            <div class="dots-logo-main">
                <img src="<?php 
echo  esc_url( WCDG_PLUGIN_URL . 'admin/images/wc-digital-goods.png' ) ;
?>">
            </div>
            <div class="dots-header-right">
                <div class="logo-detail">
                    <strong><?php 
esc_html_e( $plugin_name, WCDG_TEXT_DOMAIN );
?></strong>
                    <span><?php 
esc_html_e( WCDG_VERSION_TEXT, WCDG_TEXT_DOMAIN );
?> <?php 
esc_html_e( $plugin_version, WCDG_TEXT_DOMAIN );
?></span>
                </div>

                <div class="button-group">
                    <?php 
?>
                        <div class="button-dots-left">
                            <span class="support_dotstore_image"><a target="_blank" href="<?php 
echo  esc_url( $wcfdg_fs->get_upgrade_url() ) ;
?>">
                                <img src="<?php 
echo  esc_url( WCDG_PLUGIN_URL . 'admin/images/upgrade_new.png' ) ;
?>"></a>
                            </span>
                        </div>
                        <?php 
?>
                    <div class="button-dots">
                        <span class="support_dotstore_image"><a target="_blank" href="<?php 
echo  esc_url( 'http://www.thedotstore.com/support/' ) ;
?>">
                                <img src="<?php 
echo  esc_url( WCDG_PLUGIN_URL . 'admin/images/support_new.png' ) ;
?>"></a>
                        </span>
                    </div>
                </div>
            </div>

            <?php 
$current_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
$general_setting = ( isset( $current_page ) && 'wcdg-general-setting' === $current_page ? 'active' : '' );
$quick_checkout = ( isset( $current_page ) && 'wcdg-quick-checkout' === $current_page ? 'active' : '' );
$wcdg_getting_started = ( isset( $current_page ) && 'wcdg-get-started' === $current_page ? 'active' : '' );
$wcdg_information = ( isset( $current_page ) && 'wcdg-information' === $current_page ? 'active' : '' );

if ( isset( $current_page ) && 'wcdg-get-started' === $current_page || isset( $current_page ) && 'wcdg-information' === $current_page ) {
    $fee_about = 'active';
} else {
    $fee_about = '';
}

?>
            <div class="dots-menu-main">
                <nav>
                    <ul>
                        <li>
                            <a class="dotstore_plugin <?php 
echo  esc_attr( $general_setting ) ;
?>" href="<?php 
echo  esc_url( add_query_arg( array(
    'page' => 'wcdg-general-setting',
), admin_url( 'admin.php' ) ) ) ;
?>"><?php 
esc_html_e( 'General Setting', WCDG_TEXT_DOMAIN );
?></a>
                        </li>
                        <?php 
?>
                        <li>
                            <a class="dotstore_plugin <?php 
echo  esc_attr( $fee_about ) ;
?>" href="<?php 
echo  esc_url( add_query_arg( array(
    'page' => 'wcdg-get-started',
), admin_url( 'admin.php' ) ) ) ;
?>"><?php 
esc_html_e( 'About Plugin', WCDG_TEXT_DOMAIN );
?></a>
                            <ul class="sub-menu">
                                <li><a class="dotstore_plugin <?php 
echo  esc_attr( $wcdg_getting_started ) ;
?>" href="<?php 
echo  esc_url( add_query_arg( array(
    'page' => 'wcdg-get-started',
), admin_url( 'admin.php' ) ) ) ;
?>"><?php 
esc_html_e( 'Getting Started', WCDG_TEXT_DOMAIN );
?></a></li>
                                <li><a class="dotstore_plugin <?php 
echo  esc_attr( $wcdg_information ) ;
?>" href="<?php 
echo  esc_url( add_query_arg( array(
    'page' => 'wcdg-information',
), admin_url( 'admin.php' ) ) ) ;
?>"><?php 
esc_html_e( 'Quick info', WCDG_TEXT_DOMAIN );
?></a></li>
                            </ul>
                        </li>
                        <li>
                            <a class="dotstore_plugin"><?php 
esc_html_e( 'Dotstore', 'advanced-flat-rate-shipping-for-woocommerce' );
?></a>
                            <ul class="sub-menu">
                                <li><a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/woocommerce-plugins/' ) ;
?>"><?php 
esc_html_e( 'WooCommerce Plugins', WCDG_TEXT_DOMAIN );
?></a></li>
                                <li><a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/wordpress-plugins/' ) ;
?>"><?php 
esc_html_e( 'Wordpress Plugins', WCDG_TEXT_DOMAIN );
?></a></li><br>
                                <li><a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/support/' ) ;
?>"><?php 
esc_html_e( 'Contact Support', WCDG_TEXT_DOMAIN );
?></a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </header>