<?php
/**
 * Class used to update and edit web server configuration files
 * for .htaccess, web.config and user.ini
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Crypt
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Package related functions
 *
 */
final class DUPX_Package
{
    /**
     * 
     * @staticvar bool|string $packageHash
     * @return bool|string false if fail
     */
    public static function getPackageHash()
    {
        static $packageHash = null;
        if (is_null($packageHash)) {
            if (($packageHash = DUPX_Boot::getPackageHash()) === false) {
                throw new Exception('PACKAGE ERROR: can\'t find package hash');
            }
        }
        return $packageHash;
    }

    /**
     * 
     * @staticvar string $archivePath
     * @return bool|string false if fail
     */
    public static function getPackageArchivePath()
    {
        static $archivePath = null;
        if (is_null($archivePath)) {
            $path = $GLOBALS['DUPX_INIT'].'/'.DUPX_Boot::ARCHIVE_PREFIX.self::getPackageHash().DUPX_Boot::ARCHIVE_EXTENSION;
            if (!file_exists($path)) {
                throw new Exception('PACKAGE ERROR: can\'t read package path: '.$path);
            } else {
                $archivePath = $path;
            }
        }
        return $archivePath;
    }

    /**
     *
     * @staticvar string $path
     * @return string
     */
    public static function getWpconfigArkPath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = $GLOBALS['DUPX_ROOT'].'/dup-wp-config-arc__'.self::getPackageHash().'.txt';
        }
        return $path;
    }

    /**
     *
     * @staticvar string $path
     * @return string
     */
    public static function getHtaccessArkPath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = $GLOBALS['DUPX_ROOT'].'/htaccess.orig';
        }
        return $path;
    }

    /**
     *
     * @staticvar string $path
     * @return string
     */
    public static function getOrigWpConfigPath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = $GLOBALS['DUPX_INIT'].'/dup-orig-wp-config__'.self::getPackageHash().'.txt';
        }
        return $path;
    }

    /**
     *
     * @staticvar string $path
     * @return string
     */
    public static function getOrigHtaccessPath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = $GLOBALS['DUPX_INIT'].'/dup-orig-wp-config__'.self::getPackageHash().'.txt';
        }
        return $path;
    }
}