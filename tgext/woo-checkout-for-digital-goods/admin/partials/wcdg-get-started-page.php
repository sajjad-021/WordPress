<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-header.php';
?>
<div class="wcdg-main-left-section res-cl">
    <div class="wcdg-main-table res-cl">
        <h2><?php 
esc_html_e( WCDG_PLUGIN_NAME, WCDG_TEXT_DOMAIN );
?></h2>
        <table class="table-outer">
            <tbody>
                <tr>
                    <td class="fr-2">
                        <p class="block gettingstarted"><strong><?php 
esc_html_e( 'Getting Started', WCDG_TEXT_DOMAIN );
?></strong></p>					
                        <p class="block gettingstarted textgetting">
                            <strong><?php 
esc_html_e( 'Step 1:', WCDG_TEXT_DOMAIN );
?></strong> <?php 
esc_html_e( 'Quick Checkout Enable / Disable Backend ', WCDG_TEXT_DOMAIN );
?>
                            <span class="gettingstarted">
                                <?php 
?>
                                    <img src="<?php 
echo  esc_url( WCDG_PLUGIN_URL . 'admin/images/Getting_Started_01_free.png' ) ;
?>">
                                <?php 
?>
                                
                            </span>
                        </p>
                        <p class="block gettingstarted textgetting">
                            <strong><?php 
esc_html_e( 'Step 2:', WCDG_TEXT_DOMAIN );
?></strong> <?php 
esc_html_e( 'Select fields which you want to exclude on checkout page.', WCDG_TEXT_DOMAIN );
?>
                        </p>
                        <p class="block gettingstarted textgetting">
                            <strong><?php 
esc_html_e( 'Step 3:', WCDG_TEXT_DOMAIN );
?></strong> <?php 
esc_html_e( 'Enable Order Note which you want to exclude on checkout page.', WCDG_TEXT_DOMAIN );
?>
                        </p>
                        <p class="block gettingstarted textgetting">
                            <strong><?php 
esc_html_e( 'Step 4:', WCDG_TEXT_DOMAIN );
?></strong> <?php 
esc_html_e( 'Display Quick Checkout Button on Shop Page or Product Details Page for Digital Product.', WCDG_TEXT_DOMAIN );
?>
                            <span class="gettingstarted">
                                <img src="<?php 
echo  esc_url( WCDG_PLUGIN_URL . 'admin/images/Getting_Started_02.png' ) ;
?>">
                            </span>
                        </p>
                        <p class="block gettingstarted textgetting">
                            <strong><?php 
esc_html_e( 'Step 5:', WCDG_TEXT_DOMAIN );
?></strong> <?php 
esc_html_e( 'Quick Checkout for all downloadable and/or virtual products.', WCDG_TEXT_DOMAIN );
?>
                        </p>
                        <p class="block gettingstarted textgetting">
                            <strong><?php 
esc_html_e( 'Step 6:', WCDG_TEXT_DOMAIN );
?></strong> <?php 
esc_html_e( 'Selected Exclude field on checkout page.', WCDG_TEXT_DOMAIN );
?>
                            <span class="gettingstarted">
                                <img src="<?php 
echo  esc_url( WCDG_PLUGIN_URL . 'admin/images/Getting_Started_03.png' ) ;
?>">
                            </span>
                        </p>
                        <p class="block gettingstarted textgetting"><strong><?php 
esc_html_e( 'Important Note: ', WCDG_TEXT_DOMAIN );
?></strong><?php 
esc_html_e( 'This plugin is only compatible with WooCommerce version 2.4.0 and more', WCDG_TEXT_DOMAIN );
?></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php 
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-sidebar.php';