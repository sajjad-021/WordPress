<?php
/**
 * custom hosting manager
 * singleton class
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\DB
 * @link http://www.php-fig.org/psr/psr-2/
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

require_once ($GLOBALS['DUPX_INIT'].'/classes/host/interface.host.php');
require_once ($GLOBALS['DUPX_INIT'].'/classes/host/class.godaddy.host.php');
require_once ($GLOBALS['DUPX_INIT'].'/classes/host/class.wpengine.host.php');
require_once ($GLOBALS['DUPX_INIT'].'/classes/host/class.wordpresscom.host.php');
require_once ($GLOBALS['DUPX_INIT'].'/classes/host/class.liquidweb.host.php');

class DUPX_Custom_Host_Manager
{

    const HOST_GODADDY      = 'godaddy';
    const HOST_WPENGINE     = 'wpengine';
    const HOST_WORDPRESSCOM = 'wordpresscom';
    const HOST_LIQUIDWEB    = 'liquidweb';

    /**
     *
     * @var self
     */
    protected static $instance = null;

    /**
     * this var prevent multiple params inizialization. 
     * it's useful on development to prevent an infinite loop in class constructor
     * 
     * @var bool
     */
    private $inizialized = false;

    /**
     * custom hostings list 
     * 
     * @var DUPX_Host_interface[]
     */
    private $customHostings = array();

    /**
     * active custom hosting in current server
     * 
     * @var string[]
     */
    private $activeHostings = array();

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * init custom histings
     */
    private function __construct()
    {
        $this->customHostings[DUPX_WPEngine_Host::getIdentifier()]     = new DUPX_WPEngine_Host();
        $this->customHostings[DUPX_GoDaddy_Host::getIdentifier()]      = new DUPX_GoDaddy_Host();
        $this->customHostings[DUPX_WordpressCom_Host::getIdentifier()] = new DUPX_WordpressCom_Host();
        $this->customHostings[DUPX_Liquidweb_Host::getIdentifier()]    = new DUPX_Liquidweb_Host();
    }

    /**
     * execute the active custom hostings inizialization only one time.
     * 
     * @return boolean
     * @throws Exception
     */
    public function init()
    {
        if ($this->inizialized) {
            return true;
        }
        foreach ($this->customHostings as $cHost) {
            if (!($cHost instanceof DUPX_Host_interface)) {
                throw new Exception('Host must implemnete DUPX_Host_interface');
            }
            if ($cHost->isHosting()) {
                $this->activeHostings[] = $cHost->getIdentifier();
                $cHost->init();
            }
        }
        $this->inizialized = true;
        return true;
    }

    /**
     * return the lisst of current custom active hostings
     * 
     * @return DUPX_Host_interface[]
     */
    public function getActiveHostings()
    {
        return $this->activeHostings;
    }

    /**
     * return true if current identifier hostoing is active
     * 
     * @param string $identifier
     * @return bool
     */
    public function isHosting($identifier)
    {
        return in_array($identifier, $this->activeHostings);
    }

    /**
     * 
     * @return boolean|string return false if isn't managed manage hosting of manager hosting 
     */
    public function isManaged()
    {
        // can't a manager hosting if isn't a overwrite install
        if ($GLOBALS['DUPX_STATE']->mode !== DUPX_InstallerMode::OverwriteInstall) {
            return false;
        }

        if ($this->isHosting(self::HOST_LIQUIDWEB)) {
            return self::HOST_LIQUIDWEB;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $identifier
     * @return boolean|DUPX_Host_interface
     */
    public function getHosting($identifier)
    {
        if ($this->isHosting($identifier)) {
            return $this->customHostings[$identifier];
        } else {
            return false;
        }
    }
}