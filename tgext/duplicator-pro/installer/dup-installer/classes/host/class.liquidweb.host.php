<?php
/**
 * liquidweb custom hosting class
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\DB
 * @link http://www.php-fig.org/psr/psr-2/
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

class DUPX_Liquidweb_Host implements DUPX_Host_interface
{

    /**
     * return the current host itentifier
     *
     * @return string
     */
    public static function getIdentifier()
    {
        return DUPX_Custom_Host_Manager::HOST_LIQUIDWEB;
    }

    /**
     * @return bool true if is current host
     */
    public function isHosting()
    {        
        // can't a manager hosting if isn't a overwrite install
        if ($GLOBALS['DUPX_STATE']->mode !== DUPX_InstallerMode::OverwriteInstall) {
            return false;
        }
        $liquidWebFile = $GLOBALS['CURRENT_ROOT_PATH'].'/wp-content/mu-plugins/liquid-web.php';

        return file_exists($liquidWebFile);
    }

    /**
     * the init function.
     * is called only if isHosting is true
     *
     * @return void
     */
    public function init()
    {
        
    }

    /**
     * return the label of current hosting
     * 
     * @return string
     */
    public function getLabel()
    {
        return 'Liquid Web';
    }

    /**
     * this function is called if current hosting is this
     */
    public function setCustomParams()
    {
        DUPX_Paramas_Manager::getInstance()->setValue(DUPX_Paramas_Manager::PARAM_IGNORE_PLUGINS, array(
            'liquidweb_mwp.php',
            '000-liquidweb-config.php',
            'liquid-web.php',
            'lw_disable_nags.php'
        ));
    }
}