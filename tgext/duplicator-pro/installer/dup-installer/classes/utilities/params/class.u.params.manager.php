<?php
/**
 * Installer params manager
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

require_once($GLOBALS['DUPX_INIT'].'/classes/utilities/params/class.u.params.descriptors.php');
require_once($GLOBALS['DUPX_INIT'].'/classes/utilities/params/class.u.param.item.php');
require_once($GLOBALS['DUPX_INIT'].'/classes/utilities/params/class.u.param.item.form.php');
require_once($GLOBALS['DUPX_INIT'].'/classes/utilities/params/class.u.param.item.form.pass.php');
require_once($GLOBALS['DUPX_INIT'].'/classes/utilities/params/class.u.param.item.form.plugins.php');

/**
 * singleton class
 * 
 * this class takes care of initializing the parameters and managing their updating with persistence.
 * It also provides parameter values ​​accessible from all the instlaler.
 */
final class DUPX_Paramas_Manager
{

    const ENV_PARAMS_KEY = 'DUPLICATOR_PRO_PARAMS';

    /**
     * overwrite file content example
      <?php
      $json = <<<JSON
      {
      "debug": {
      "value": false
      },
      "debug_params": {
      "value": true
      },
      "logging": {
      "value": 2
      }
      }
      JSON;
      // OVERWRITE FILE END
     */
    const LOCAL_OVERWRITE_PARAMS       = 'duplicator_pro_params_overwrite.php';
    // actions
    const PARAM_CTRL_ACTION            = 'ctrl_action';
    const PARAM_VIEW                   = 'view';
    const PARAM_ROUTER_ACTION          = 'router-action';
    const PARAM_SECURE_PASS            = 'secure-pass';
    const PARAM_SECURE_TRY             = 'secure-try';
    // input params
    const PARAM_DEBUG                  = 'debug';
    const PARAM_DEBUG_PARAMS           = 'debug_params';
    const PARAM_ARCHIVE_ENGINE         = 'archive_engine';
    const PARAM_LOGGING                = 'logging';
    const PARAM_REMOVE_RENDUNDANT      = 'remove-redundant';
    const PARAM_FILE_TIME              = 'zip_filetime';
    const PARAM_HTACCESS_CONFIG        = 'ht_config';
    const PARAM_OTHER_CONFIG           = 'other_config';
    const PARAM_WP_CONFIG              = 'wp_config';
    const PARAM_CLIENT_KICKOFF         = 'clientside_kickoff';
    const PARAM_SAFE_MODE              = 'exe_safe_mode';
    const PARAM_SET_FILE_PERMS         = 'set_file_perms';
    const PARAM_FILE_PERMS_VALUE       = 'file_perms_value';
    const PARAM_SET_DIR_PERMS          = 'set_dir_perms';
    const PARAM_DIR_PERMS_VALUE        = 'dir_perms_value';
    const PARAM_MULTISITE_INST_TYPE    = 'multisite-install-type';
    const PARAM_SUBSITE_ID             = 'subsite_id';
    const PARAM_ACCEPT_TERM_COND       = 'accpet-warnings';
    const PARAM_DB_TEST_OK             = 'dbtest_ok';
    const PARAM_DB_VIEW_MODE           = 'view_mode';
    const PARAM_DB_ACTION              = 'dbaction';
    const PARAM_DB_HOST                = 'dbhost';
    const PARAM_DB_NAME                = 'dbname';
    const PARAM_DB_USER                = 'dbuser';
    const PARAM_DB_PASS                = 'dbpass';
    const PARAM_DB_CHARSET             = 'dbcharset';
    const PARAM_DB_COLLATE             = 'dbcollate';
    const PARAM_DB_COLLATE_FB          = 'dbcollatefb';
    const PARAM_DB_CHUNK               = 'dbchunk';
    const PARAM_DB_SPACING             = 'dbnbsp';
    const PARAM_DB_VIEW_CEATION        = 'dbobj_views';
    const PARAM_DB_PROC_CREATION       = 'dbobj_procs';
    const PARAM_DB_MYSQL_MODE          = 'dbmysqlmode';
    const PARAM_DB_MYSQL_MODE_OPTS     = 'dbmysqlmode_opts';
    const PARAM_CPNL_HOST              = 'cpnl-host';
    const PARAM_CPNL_USER              = 'cpnl-user';
    const PARAM_CPNL_PASS              = 'cpnl-pass';
    const PARAM_CPNL_IGNORE_PREFIX     = 'cpnl_ignore_prefix';
    const PARAM_CPNL_DB_ACTION         = 'cpnl-dbaction';
    const PARAM_CPNL_DB_HOST           = 'cpnl-dbhost';
    const PARAM_CPNL_PREFIX            = 'cpnl-prefix';
    const PARAM_CPNL_DB_NAME_SEL       = 'cpnl-dbname-select';
    const PARAM_CPNL_DB_NAME_TXT       = 'cpnl-dbname-txt';
    const PARAM_CPNL_DB_USER_SEL       = 'cpnl-dbuser-select';
    const PARAM_CPNL_DB_USER_TXT       = 'cpnl-dbuser-txt';
    const PARAM_CPNL_DB_USER_CHK       = 'cpnl-dbuser-chk';
    const PARAM_CPNL_DB_PASS           = 'cpnl-dbpass';
    const PARAM_URL_OLD                = 'url_old';
    const PARAM_URL_NEW                = 'url_new';
    const PARAM_SITE_URL               = 'siteurl';
    const PARAM_PATH_OLD               = 'path_old';
    const PARAM_PATH_NEW               = 'path_new';
    const PARAM_BLOGNAME               = 'blogname';
    const PARAM_REPLACE_MODE           = 'replace_mode';
    const PARAM_REPLACE_ENGINE         = 'mode_chunking';
    const PARAM_EMPTY_SCHEDULE_STORAGE = 'empty_schedule_storage';
    const PARAM_PLUGINS                = 'plugins';
    const PARAM_IGNORE_PLUGINS         = 'ignore_plugins';
    const PARAM_FORCE_DIABLE_PLUGINS   = 'fd_plugins';

    /**
     *
     * @var self
     */
    private static $instance = null;

    /**
     *
     * @var bool 
     */
    private static $inizialized = false;

    /**
     *
     * @var DUPX_Param_item_form[] 
     */
    private $params = array();

    /**
     *
     * @var array 
     */
    private $paramsHtmlInfo = array();

    /**
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

    /**
     * init params and load
     */
    private function __construct()
    {
        $this->initParams();
        $this->load();
    }

    /**
     * 
     * @return boolean
     * @throws Exception
     */
    private function initParams()
    {
        if (self::$inizialized) {
            // prevent multiple inizialization
            throw new Exception('multiple init param calls');
        }
        self::$inizialized = true;
        $this->params      = array();

        $this->paramsHtmlInfo[] = '***** INIT PARAMS WITH STD VALUlES';

        DUPX_Paramas_Descriptors::initGenericParams($this->params);
        DUPX_Paramas_Descriptors::initDatabaseParams($this->params);
        DUPX_Paramas_Descriptors::initCpanelParams($this->params);
        DUPX_Paramas_Descriptors::initScanParams($this->params);

        return true;
    }

    /**
     * get value of param key.
     * thorw execption if key don't exists
     * 
     * 
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function getValue($key)
    {
        if (!isset($this->params[$key])) {
            throw new Exception('Param key '.DUPX_Log::varToString($key).'don\' exists');
        }
        return $this->params[$key]->getValue();
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @return boolean // return false if params isn't valid
     * @throws Exception // if key don't exists
     */
    public function setValue($key, $value)
    {
        if (!isset($this->params[$key])) {
            throw new Exception('Param key '.DUPX_Log::varToString($key).'don\' exists');
        }
        return $this->params[$key]->setValue($value);
    }

    /**
     * this cungion set value get from input method.
     * 
     * @param string $key
     * @param string $method
     * @param bool $thowException   // if true throw exception if  value isn't valid.
     * @return type
     * @throws Exception
     */
    public function setValueFromInput($key, $method = DUPX_Param_item_form::INPUT_POST, $thowException = true)
    {
        if (!isset($this->params[$key])) {
            throw new Exception('Param key '.DUPX_Log::varToString($key).'don\' exists');
        }

        if (($result = $this->params[$key]->setValueFromInput($method)) === false) {
            if ($thowException) {
                throw new Exception('PARAM FROM INPUT ERROR: Can\'t set param '.$key);
            }
        }
        return $result;
    }

    /**
     * return the form param wrapper id 
     * @param string $key
     * @return boolean|string   // return false if the item key isn't a instance of DUPX_Param_item_form
     * @throws Exception
     */
    public function getFormWrapperId($key)
    {
        if (!isset($this->params[$key])) {
            throw new Exception('Param key '.DUPX_Log::varToString($key).'don\' exists');
        }

        if (method_exists($this->params[$key], 'getFormWrapperId')) {
            return $this->params[$key]->getFormWrapperId();
        } else {
            return false;
        }
    }

    /**
     * 
     * @param string $key
     * @return boolean|string   // return false if the item key isn't a instance of DUPX_Param_item_form
     * @throws Exception
     */
    public function getFormItemId($key)
    {
        if (!isset($this->params[$key])) {
            throw new Exception('Param key '.DUPX_Log::varToString($key).'don\' exists');
        }

        if (method_exists($this->params[$key], 'getFormItemId')) {
            return $this->params[$key]->getFormItemId();
        } else {
            return false;
        }
    }

    /**
     * 
     * @param string $key
     * @return boolean|string   // return false if the item key isn't a instance of DUPX_Param_item_form
     * @throws Exception
     */
    public function getFormStatus($key)
    {
        if (!isset($this->params[$key])) {
            throw new Exception('Param key '.DUPX_Log::varToString($key).'don\' exists');
        }

        if (method_exists($this->params[$key], 'getFormStatus')) {
            return $this->params[$key]->getFormStatus();
        } else {
            return false;
        }
    }

    /**
     * return true if the input exists in html form
     * false if isn't DUPX_Param_item_form object or status is STATUS_INFO_ONLY or STATUS_SKIP
     * 
     * @param string $key
     * @return boolean
     * @throws Exception
     */
    public function isHtmlInput($key)
    {
        $status = $this->getFormStatus($key);
        switch ($status) {
            case DUPX_Param_item_form::STATUS_ENABLED:
            case DUPX_Param_item_form::STATUS_READONLY:
            case DUPX_Param_item_form::STATUS_DISABLED:
                return true;
            case DUPX_Param_item_form::STATUS_INFO_ONLY:
            case DUPX_Param_item_form::STATUS_SKIP:
            default:
                return false;
        }
    }

    /**
     * get the input form html
     * 
     * @param strin $key                // the param identifier
     * @param mixed $overwriteValue     // if not null set overwriteValue begore get html (IMPORTANT: the stored param value don't change. To change it use setValue.)
     * @param bool $echo                // true echo else return string
     * 
     * @return bool|string              // return false if the item kay isn't a instance of DUPX_Param_item_form
     * 
     * @throws Exception
     */
    public function getHtmlFormParam($key, $overwriteValue = null, $echo = true)
    {
        if (!isset($this->params[$key])) {
            throw new Exception('Param key '.DUPX_Log::varToString($key).'don\' exists');
        }

        if (!($this->params[$key] instanceof DUPX_Param_item_form)) {
            return false;
        }

        if (is_null($overwriteValue)) {
            return $this->params[$key]->getHtml($echo);
        } else {
            $tmpParam = clone $this->params[$key];
            if ($tmpParam->setValue($overwriteValue) === false) {
                throw new Exception('Can\'t set overwriteValue '.DUPX_Log::varToString($overwriteValue).' in param:'.$tmpParam->getName());
            }

            return $tmpParam->getHtml($echo);
        }
    }

    /**
     * load params from persistance files
     * 
     * @return boolean
     */
    protected function load()
    {
        if (!file_exists(self::getPersistanceFilePath())) {
            return false;
        }
        $this->paramsHtmlInfo[] = '***** LOAD PARAMS FROM PERSISTANCE FILE';

        if (($json = file_get_contents(self::getPersistanceFilePath())) === false) {
            throw new Exception('Can\'t read param persistance file '.DUPX_Log::varToString(self::getPersistanceFilePath()));
        }

        $arrayData = json_decode($json, true);
        $this->setParamsValues($arrayData);
        return true;
    }

    /**
     * remove persistance file and all params and reinit all
     */
    public function resetParams()
    {
        $this->paramsHtmlInfo[] = '***** RESET PARAMS';
        DupProSnapLibIOU::rm(self::getPersistanceFilePath());
        $this->params           = array();
        self::$inizialized      = false;
        $this->initParams();
        return $this->load();
    }

    /**
     * ovrewrite params from sources
     * 
     * @return boolean
     * @throws Exception
     */
    public function initParamsOverwrite()
    {
        $this->paramsHtmlInfo[] = '***** LOAD OVERWRITE INFO';
        /**
         * @todo temp disabled require major study
         * if (isset($_ENV[self::ENV_PARAMS_KEY])) {
         *  $this->paramsHtmlInfo[] = 'LOAD FROM ENV VARS';
          $arrayData = json_decode($_ENV[self::ENV_PARAMS_KEY]);
          $this->setParamsValues($arrayData);
          } */
        // LOAD PARAMS FROM PACKAGE OVERWRITE
        $arrayData              = (array) DUPX_ArchiveConfig::getInstance()->overwriteInstallerParams;
        if (!empty($arrayData)) {
            $this->paramsHtmlInfo[] = '***** LOAD FROM PACKAGE OVERWRITE';
            $this->setParamsValues($arrayData);
        }

        if (DUPX_Custom_Host_Manager::getInstance()->isManaged()) {
            DUPX_Paramas_Descriptors::setManagedHostParams($this->params);
        }

        // LOAD PARAMS FROM LOCAL OVERWRITE
        $localOverwritePath = $GLOBALS['DUPX_ROOT'].'/'.self::LOCAL_OVERWRITE_PARAMS;
        if (is_readable($localOverwritePath)) {
            // json file is set in $localOverwritePath php file
            $json = null;
            include($localOverwritePath);
            if (empty($json)) {
                DUPX_Log::info('LOCAL OVERWRITE PARAMS FILE ISN\'T WELL FORMED');
            } else {
                $arrayData = json_decode($json, true);
                if (!empty($arrayData)) {
                    $this->paramsHtmlInfo[] = '***** LOAD FROM LOCAL OVERWRITE';
                    $this->setParamsValues($arrayData);
                }
            }
        }
        return true;
    }

    /**
     * update params velues from arrayData 
     * 
     * @param array $arrayData
     * @throws Exception
     */
    protected function setParamsValues($arrayData)
    {

        if (!is_array($arrayData)) {
            throw new Exception('Invalid data params ');
        }
        foreach ($arrayData as $key => $arrayValues) {
            if (isset($this->params[$key])) {
                $arrayValues            = (array) $arrayValues;
                $this->paramsHtmlInfo[] = 'SET PARAM <b>'.$key.'</b> ARRAY DATA: '.DupProSnapLibStringU::implodeKeyVals(', ', $arrayValues, '[<b>%s</b> = %s]');
                $this->params[$key]->fromArrayData($arrayValues);
            }
        }
    }

    /**
     * update persistance file
     * 
     * @return bool\int // This function returns the number of bytes that were written to the file, or FALSE on failure.
     */
    public function save()
    {
        DUPX_LOG::info('SAVE PARAMS', DUPX_Log::LV_DETAILED);

        $arrayData = array();
        foreach ($this->params as $param) {
            if ($param->isPersistent()) {
                $arrayData[$param->getName()] = $param->toArrayData();
            }
        }
        $json = DupProSnapJsonU::wp_json_encode_pprint($arrayData);
        return file_put_contents(self::getPersistanceFilePath(), $json);
    }

    /**
     * 
     * @staticvar string $path
     * @return string
     */
    protected static function getPersistanceFilePath()
    {
        static $path = null;

        if (is_null($path)) {
            $path = $GLOBALS['DUPX_INIT'].'/'.'dup-params__'.DUPX_Package::getPackageHash().'.json';
        }

        return $path;
    }

    /**
     * html params info for debug params
     * 
     * @return void
     */
    public function getParamsHtmlInfo()
    {
        if (!$this->getValue(self::PARAM_DEBUG_PARAMS)) {
            return;
        }
        ?>
        <div id="params-html-info" >
            <h3>CURRENT VALUES</h3>
            <ul class="values" >
                <?php foreach ($this->params as $param) { ?>
                    <li> 
                        PARAM <b><?php echo $param->getName(); ?></b> VALUE: <b><?php echo htmlentities(DUPX_Log::varToString($param->getValue())); ?></b>
                    </li>
                <?php } ?>
            </ul>
            <h3>LOAD SEQUENCE</h3>
            <ul class="load-sequence" >
                <?php foreach ($this->paramsHtmlInfo as $info) { ?>
                    <li>
                        <?php echo $info; ?>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <?php
    }

    /**
     * get params value list for log
     * @return string
     */
    public function getParamsToText()
    {
        $result = array();

        foreach ($this->params as $param) {

            if (method_exists($param, 'getFormStatus')) {
                $line = 'PARAM FORM '.$param->getName().' VALUE: '.DUPX_Log::varToString($param->getValue()).' STATUS: '.$param->getFormStatus();
            } else {
                $line = 'PARAM ITEM '.$param->getName().' VALUE: '.DUPX_Log::varToString($param->getValue());
            }

            $result[] = $line;
        }

        return implode("\n", $result);
    }

    private function __clone()
    {
        
    }

    private function __wakeup()
    {
        
    }
}