<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

require_once(plugin_dir_path( __FILE__ ).'header/plugin-header.php');
?>

<div class="wcdg-main-left-section">
    <div class="wcdg-main-table res-cl">
        <h2><?php esc_html_e('Quick info', WCDG_TEXT_DOMAIN); ?></h2>
        <table class="table-outer">
            <tbody>
                <tr>
                    <td class="fr-1"><?php esc_html_e('Product Type', WCDG_TEXT_DOMAIN); ?></td>
                    <td class="fr-2"><?php esc_html_e('WooCommerce Plugin', WCDG_TEXT_DOMAIN); ?></td>
                </tr>
                <tr>
                    <td class="fr-1"><?php esc_html_e('Product Name', WCDG_TEXT_DOMAIN); ?></td>
                    <td class="fr-2"><?php esc_html_e($plugin_name, WCDG_TEXT_DOMAIN); ?></td>
                </tr>
                <tr>
                    <td class="fr-1"><?php esc_html_e('Installed Version', WCDG_TEXT_DOMAIN); ?></td>
                    <td class="fr-2"><?php esc_html_e(WCDG_VERSION_TEXT, WCDG_TEXT_DOMAIN); ?> <?php esc_html_e($plugin_version, WCDG_TEXT_DOMAIN); ?></td>
                </tr>
                <tr>
                    <td class="fr-1"><?php esc_html_e('License & Terms of use', WCDG_TEXT_DOMAIN); ?></td>
                    <td class="fr-2"><a target="_blank"  href="<?php echo esc_url('https://www.thedotstore.com/terms-and-conditions/'); ?>"><?php esc_html_e('Click here', WCDG_TEXT_DOMAIN); ?></a><?php esc_html_e(' to view license and terms of use.', WCDG_TEXT_DOMAIN); ?></td>
                </tr>
                <tr>
                    <td class="fr-1"><?php esc_html_e('Help & Support', WCDG_TEXT_DOMAIN); ?></td>
                    <td class="fr-2">
                        <ul>
                            <li><a href="<?php echo esc_url(admin_url('/admin.php?page=wcdg-get-started')); ?>"><?php esc_html_e('Quick Start', WCDG_TEXT_DOMAIN); ?></a></li>
                            <li><a target="_blank" href="<?php echo esc_url('https://thedotstore.helpscoutdocs.com/collection/165-digital-goods-for-woocommerce-checkout'); ?>"><?php esc_html_e('Guide Documentation', WCDG_TEXT_DOMAIN); ?></a></li> 
                            <li><a target="_blank" href="<?php echo esc_url('https://www.thedotstore.com/support/'); ?>"><?php esc_html_e('Support Forum', WCDG_TEXT_DOMAIN); ?></a></li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td class="fr-1"><?php esc_html_e('Localization', WCDG_TEXT_DOMAIN); ?></td>
                    <td class="fr-2"><?php esc_html_e('English, German', WCDG_TEXT_DOMAIN); ?></td>
                </tr>

            </tbody>
        </table>
    </div>
</div>
<?php require_once(plugin_dir_path( __FILE__ ).'header/plugin-sidebar.php');