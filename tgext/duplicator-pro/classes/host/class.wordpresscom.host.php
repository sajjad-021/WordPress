<?php
/**
 * godaddy custom hosting class
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\HOST
 * @link http://www.php-fig.org/psr/psr-2/
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

class DUP_PRO_WordpressCom_Host implements DUP_PRO_Host_interface
{

    public static function getIdentifier()
    {
        return DUP_PRO_Custom_Host_Manager::HOST_WORDPRESSCOM;
    }

    public function isHosting()
    {
        if (!function_exists('gethostname')) {
            return false;
        }
        // ex. xxxx.dca.atomicsites.net
        $hostName = gethostname();
        return apply_filters('duplicator_pro_wpcom_host_check', DUP_PRO_STR::endsWith($hostName, '.atomicsites.net'));
    }

    public function init()
    {
        add_filter('duplicator_pro_is_shellzip_available', '__return_false');
    }
}
