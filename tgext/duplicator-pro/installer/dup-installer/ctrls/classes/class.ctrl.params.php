<?php
/**
 * Controller params manager
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * singleton class
 */
final class DUPX_Ctrl_Params
{

    public static function setParamsBase()
    {
        DUPX_LOG::info('CTRL PARAMS BASE', DUPX_Log::LV_DETAILED);
        $paramsManager = DUPX_Paramas_Manager::getInstance();
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CTRL_ACTION, DUPX_Param_item_form::INPUT_REQUEST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_VIEW, DUPX_Param_item_form::INPUT_REQUEST);

        $paramsManager->save();
    }

    public static function setParamsStep0()
    {
        DUPX_LOG::info('CTRL PARAMS S0', DUPX_Log::LV_DETAILED);
        $paramsManager = DUPX_Paramas_Manager::getInstance();

        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_SECURE_PASS, DUPX_Param_item_form::INPUT_REQUEST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_SECURE_TRY, DUPX_Param_item_form::INPUT_REQUEST);

        $paramsManager->save();
    }

    public static function setParamsStep1()
    {
        DUPX_LOG::info('CTRL PARAMS S1', DUPX_Log::LV_DETAILED);
        $paramsManager = DUPX_Paramas_Manager::getInstance();
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_LOGGING, DUPX_Param_item_form::INPUT_POST);
        DUPX_Log::setLogLevel();

        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_MULTISITE_INST_TYPE, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_SUBSITE_ID, DUPX_Param_item_form::INPUT_POST);

        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_ARCHIVE_ENGINE, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_SET_FILE_PERMS, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_SET_DIR_PERMS, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_FILE_PERMS_VALUE, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DIR_PERMS_VALUE, DUPX_Param_item_form::INPUT_POST);

        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_SAFE_MODE, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_WP_CONFIG, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_HTACCESS_CONFIG, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_OTHER_CONFIG, DUPX_Param_item_form::INPUT_POST);

        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_FILE_TIME, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_REMOVE_RENDUNDANT, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CLIENT_KICKOFF, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_ACCEPT_TERM_COND, DUPX_Param_item_form::INPUT_POST);

        // UPDATE ACTIVE PLUGINS BY SUBSITE ID
        $subsiteId     = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_SUBSITE_ID);
        $activePlugins = DUPX_Plugins_Manager::getInstance()->getDefaultActivePluginsList($subsiteId);
        $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_PLUGINS, $activePlugins);
        
        // IF SAFE MODE DISABLE ALL PLUGINS
        if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_SAFE_MODE) > 0) {
            $forceDisable = DUPX_Plugins_Manager::getInstance()->getAllPluginsSlugs();
            
            // EXCLUDE DUPLICATOR PRO
            if (($key          = array_search(DUPX_Plugins_Manager::SLUG_DUPLICATOR_PRO, $forceDisable)) !== false) {
                unset($forceDisable[$key]);
            }
            $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_FORCE_DIABLE_PLUGINS, $forceDisable);
        }

        $paramsManager->save();
    }

    public static function setParamsStep2()
    {
        DUPX_LOG::info('CTRL PARAMS S2', DUPX_Log::LV_DETAILED);
        $paramsManager = DUPX_Paramas_Manager::getInstance();

        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_CHUNK, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_SPACING, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_VIEW_CEATION, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_PROC_CREATION, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_CHARSET, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_COLLATE, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE_OPTS, DUPX_Param_item_form::INPUT_POST);

        $paramsManager->save();
    }

    public static function setParamsStep3()
    {
        DUPX_LOG::info('CTRL PARAMS S3', DUPX_Log::LV_DETAILED);
        $paramsManager = DUPX_Paramas_Manager::getInstance();

        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_URL_NEW, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_PATH_NEW, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_BLOGNAME, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_REPLACE_MODE, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_REPLACE_ENGINE, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_EMPTY_SCHEDULE_STORAGE, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_SITE_URL, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_URL_OLD, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_PATH_OLD, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_PLUGINS, DUPX_Param_item_form::INPUT_POST);

        $paramsManager->save();
    }
}