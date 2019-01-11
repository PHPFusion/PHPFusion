<?php

/**
 * Autoloader
 *
 * @package Less
 * @subpackage autoload
 */
class Less_Autoloader {

    /**
     * Registered flag
     *
     * @var boolean
     */
    protected static $registered = FALSE;

    /**
     * Library directory
     *
     * @var string
     */
    protected static $libDir;

    /**
     * Register the autoloader in the spl autoloader
     *
     * @return void
     * @throws Exception If there was an error in registration
     */
    public static function register() {
        if (self::$registered) {
            return;
        }

        self::$libDir = __DIR__;

        if (FALSE === spl_autoload_register(['Less_Autoloader', 'loadClass'])) {
            throw new Exception('Unable to register Less_Autoloader::loadClass as an autoloading method.');
        }

        self::$registered = TRUE;
    }

    /**
     * Unregisters the autoloader
     *
     * @return void
     */
    public static function unregister() {
        spl_autoload_unregister(['Less_Autoloader', 'loadClass']);
        self::$registered = FALSE;
    }

    /**
     * Loads the class
     *
     * @param string $className The class to load
     */
    public static function loadClass($className) {


        // handle only package classes
        if (strpos($className, 'Less_') !== 0) {
            return;
        }

        $className = substr($className, 5);
        $fileName = self::$libDir.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $className).'.php';

        if (file_exists($fileName)) {
            require $fileName;

            return TRUE;
        } else {
            throw new Exception('file not loadable '.$fileName);
        }
    }

}
