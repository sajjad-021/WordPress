<?php
/**
 * Class used to control values about the package meta data
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\ArchiveConfig
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

abstract class DUPX_LicenseType
{

    const Unlicensed   = 0;
    const Personal     = 1;
    const Freelancer   = 2;
    const BusinessGold = 3;

}

/**
 * singleton class
 */
class DUPX_ArchiveConfig
{

    //READ-ONLY: COMPARE VALUES
    public $created;
    public $version_dup;
    public $version_wp;
    public $version_db;
    public $version_php;
    public $version_os;
    public $dbInfo;
    public $wpInfo;
    //GENERAL
    public $secure_on;
    public $secure_pass;
    public $skipscan;
    public $package_name;
    public $package_hash;
    public $package_notes;
    public $wp_tableprefix;
    public $blogname;
    public $wplogin_url;
    public $relative_content_dir;
    public $blogNameSafe;
    public $exportOnlyDB;
    //BASIC DB
    public $dbhost;
    public $dbname;
    public $dbuser;
    public $dbpass;
    //CPANEL: Login
    public $cpnl_host;
    public $cpnl_user;
    public $cpnl_pass;
    public $cpnl_enable;
    public $cpnl_connect;
    //CPANEL: DB
    public $cpnl_dbaction;
    public $cpnl_dbhost;
    public $cpnl_dbname;
    public $cpnl_dbuser;
    //ADV OPTS
    public $wproot;
    public $url_old;
    public $opts_delete;
    //MULTISITE
    public $mu_mode;
    public $mu_generation;
    public $subsites;
    public $mu_is_filtered;
    //LICENSING
    public $license_limit;
    //PARAMS
    public $overwriteInstallerParams = array();

    /**
     *
     * @var self 
     */
    private static $instance = null;

    /**
     * Loads a usable object from the archive.txt file found in the dup-installer root
     *
     * @param string $path	// The root path to the location of the server config files
     * 
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected function __construct()
    {
        $config_filepath = DUPX_Package::getPackageArchivePath();
        if (file_exists($config_filepath)) {
            $file_contents = file_get_contents($config_filepath);
            $ac_data       = json_decode($file_contents);

            foreach ($ac_data as $key => $value) {
                $this->{$key} = $value;
            }
        } else {
            echo "$config_filepath doesn't exist<br/>";
        }

        //Instance Updates:
        $this->blogNameSafe = preg_replace("/[^A-Za-z0-9?!]/", '', $this->blogname);
        $this->dbhost       = empty($this->dbhost) ? 'localhost' : $this->dbhost;
        $this->cpnl_host    = empty($this->cpnl_host) ? "https://{$GLOBALS['HOST_NAME']}:2083" : $this->cpnl_host;
        $this->cpnl_dbhost  = empty($this->cpnl_dbhost) ? 'localhost' : $this->cpnl_dbhost;
        $this->cpnl_dbname  = strlen($this->cpnl_dbname) ? $this->cpnl_dbname : '';
    }

    /**
     * Returns the license type this installer file is made of.
     *
     * @return obj	Returns an enum type of DUPX_LicenseType
     */
    public function getLicenseType()
    {
        $license_type = DUPX_LicenseType::Personal;

        if ($this->license_limit < 0) {
            $license_type = DUPX_LicenseType::Unlicensed;
        } else if ($this->license_limit < 15) {
            $license_type = DUPX_LicenseType::Personal;
        } else if ($this->license_limit < 500) {
            $license_type = DUPX_LicenseType::Freelancer;
        } else if ($this->license_limit >= 500) {
            $license_type = DUPX_LicenseType::BusinessGold;
        }

        return $license_type;
    }

    /**
     * 
     * @return bool
     */
    public function isZipArchive()
    {
        //$extension = strtolower(pathinfo($this->package_name)['extension']);
        $extension = strtolower(pathinfo($this->package_name, PATHINFO_EXTENSION));

        return ($extension == 'zip');
    }

    /**
     * 
     * @param string $define
     * @return bool             // return true if define value exists
     */
    public function defineValueExists($define)
    {
        return isset($this->wpInfo->configs->defines->{$define});
    }

    /**
     * return define value from archive or default value if don't exists
     * 
     * @param string $define
     * @param mixed $default
     * @return mixed
     */
    public function getDefineValue($define, $default = false)
    {
        $defines = $this->wpInfo->configs->defines;
        if (isset($defines->{$define})) {
            return $defines->{$define}->value;
        } else {
            return $default;
        }
    }

    /**
     * return define value from archive or default value if don't exists in wp-config
     * 
     * @param string $define
     * @param mixed $default
     * @return mixed
     */
    public function getWpConfigDefineValue($define, $default = false)
    {
        $defines = $this->wpInfo->configs->defines;
        if (isset($defines->{$define}) && $defines->{$define}->inWpConfig) {
            return $defines->{$define}->value;
        } else {
            return $default;
        }
    }

    /**
     * 
     * @param string $key
     * @return boolean
     */
    public function realValueExists($key)
    {
        return isset($this->wpInfo->configs->realValues->{$key});
    }

    /**
     * return read value from archive if exists of default if don't exists
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getRealValue($key, $default = false)
    {
        $values = $this->wpInfo->configs->realValues;
        if (isset($values->{$key})) {
            return $values->{$key};
        } else {
            return $default;
        }
    }

    /**
     * 
     * @return string
     */
    public function getBlognameFromSelectedSubsiteId()
    {
        $subsiteId = DUPX_Paramas_Manager::getInstance()->getValue(DUPX_Paramas_Manager::PARAM_SUBSITE_ID);
        $blogname  = $this->blogname;
        if ($subsiteId > 0) {
            foreach ($this->subsites as $subsite) {
                if ($subsiteId == $subsite->id) {
                    $blogname = $subsite->blogname;
                    break;
                }
            }
        }
        return $blogname;
    }

    /**
     * 
     * @return bool
     */
    public function isNetworkInstall()
    {
        $subsiteId = DUPX_Paramas_Manager::getInstance()->getValue(DUPX_Paramas_Manager::PARAM_SUBSITE_ID);
        return $subsiteId < 1 && $this->mu_mode > 0;
    }
}