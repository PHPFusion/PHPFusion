<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: InstallCore.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\Installer;

use Dynamics;
use PHPFusion\Installer\Steps\Introduction;
use PHPFusion\Installer\Steps\Permissions;
use PHPFusion\OutputHandler;
use PHPFusion\Installer\Steps\AdminSetup;
use PHPFusion\Installer\Steps\DatabaseSetup;
use PHPFusion\Installer\Steps\InfusionsSetup;

ini_set( 'display_errors', 1 );

/**
 * Class InstallCore
 *
 * @package PHPFusion\Installer
 */
class InstallCore extends Infusions {

    const STEP_INTRO = 1;
    const STEP_PERMISSIONS = 2;
    const STEP_DB_SETTINGS_FORM = 3;
    const STEP_DB_SETTINGS_SAVE = 4;
    const STEP_PRIMARY_ADMIN_FORM = '5';   //must be between quotation marks because of implicit type conversion
    const STEP_PRIMARY_ADMIN_SAVE = '5';
    const STEP_INFUSIONS = 6;
    const STEP_SETUP_COMPLETE = 7;
    const STEP_EXIT = 8;
    const STEP_TRANSFER = 9;
    const BUILD_VERSION = '9.20.00';
    const INSTALLER_ALGO = 'sha256';
    const USER_RIGHTS_SA = 'AD.APWR.B.BB.C.CP.DB.ERRO.FM.I.IM.IP.LANG.M.MAIL.MI.P.PI.PL.ROB.S1.S2.S4.S6.S7.S9.S12.SB.SL.SM.TS.U.UF.UG.UL';
    protected static $locale = [];
    protected static $localeset = 'English';
    protected static $allow_delete = FALSE;
    /*
     * next can be STEP_PERMISSIONS;
     * back can be STEP_INTRODUCTION;
     * @var array
     */
    protected static $step = [
        //  'next' => FALSE,
        //  'previous' => FALSE,
    ];

    protected static $connection = [
        'db_host'         => '',
        'db_port'         => '',
        'db_user'         => NULL,
        'db_pass'         => NULL,
        'db_name'         => NULL,
        'db_prefix'       => NULL,
        'cookie_prefix'   => NULL,
        'secret_key_salt' => NULL,
        'secret_key'      => NULL,
        'db_driver'       => NULL
    ];

    protected static $site_data = [
        'sitename'          => '',
        'siteemail'         => '',
        'enabled_languages' => '',
        'siteusername'      => ''
    ];

    protected static $user_data = [
        'user_name'       => '',
        'user_email'      => '',
        'user_timezone'   => '',
        'password1'       => '',
        'password2'       => '',
        'admin_password1' => '',
        'admin_password2' => '',
    ];

    /*
     * Verify the requirements that allows you to run the installer before boot up.
     * Due to the support for PHPFusion 9 in many uses of empty() as a condition
     * and being counterproductive in fixing low end php version deprecated codes /e,
     * no oPCache, and other problems, using PHPFusion 9 is not going to be allowed
     * entirely.
     */
    protected static $locale_files = [];
    protected static $document;

    /*
     * Defining the steps and ensure that there are no field left blank
     */
    private static $setup_instance = NULL;

    /*
     * Accessors and Mutators method implementation of the base of
     * installer and subsequently to replace on output.
     */
    private static $config = [];

    protected function __construct() {
    }

    /**
     * @return null|static
     */
    public static function getInstallInstance() {
        $settings = fusion_get_settings();

        if (self::$setup_instance == NULL) {

            self::$setup_instance = new static();

            // ALWAYS reset config to config_temp.php
            if (file_exists( BASEDIR . 'config.php' )) {
                @rename( BASEDIR . 'config.php', BASEDIR . 'config_temp.php' );
                @chmod( BASEDIR . 'config_temp.php', 0755 );
                maintenance_mode();
            }

            session_start();

            //require_once BASEDIR.'includes/autoloader.php';
            require_once DB_HANDLERS . "all_functions_include.php";
            require_once BASEDIR . "includes/defender.php";
            require_once BASEDIR . 'includes/dynamics.php';

            Dynamics::getInstance();

            self::installerStep();
            self::verifyRequirements();

            define( 'iMEMBER', FALSE );
            define( "FUSION_QUERY", $_SERVER['QUERY_STRING'] ?? "" );
            define( "FUSION_SELF", basename( $_SERVER['PHP_SELF'] ) );
            define( "FUSION_ROOT", '' );
            define( "FUSION_REQUEST", isset( $_SERVER['REQUEST_URI'] ) && $_SERVER['REQUEST_URI'] != "" ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'] );

            self::$localeset = filter_input( INPUT_GET, 'localeset' ) ?: ($settings['locale'] ?? 'English');
            define( 'LANGUAGE', is_dir( LOCALE . self::$localeset ) ? self::$localeset : 'English' );
            define( 'LOCALESET', LANGUAGE . "/" );
            self::$locale = fusion_get_locale( '', [LOCALE . LOCALESET . "global.php", LOCALE . LOCALESET . "setup.php"] );
            self::$locale_files = fusion_get_detected_languages();

            self::detectSystemUpgrade();

            // set timezone for PDO
            date_default_timezone_set( 'Europe/London' );

        }

        return self::$setup_instance;
    }

    /**
     * Set the current installer steps.
     *
     * @param string $step
     */
    protected static function installerStep( $step = 'auto' ) {
        if (isset( $_GET['session'] )) {
            $_SESSION['step'] = $_GET['session'];
        }
        if ($step == 'auto') {

            if (!defined( 'INSTALLATION_STEP' )) {
                $_SESSION['step'] = (!isset( $_SESSION['step'] ) ? self::STEP_INTRO : $_SESSION['step']);
                // current session
                if (isset( $_POST['infuse'] ) || isset( $_POST['defuse'] )) {
                    $_SESSION['step'] = self::STEP_INFUSIONS;
                } else if (isset( $_POST['step'] )) {
                    $_SESSION['step'] = $_POST['step'];
                }
                define( 'INSTALLATION_STEP', $_SESSION['step'] );
            }
        } else {
            $_SESSION['step'] = $step;
        }
    }

    /**
     * Check the server minimum requirements
     */
    private static function verifyRequirements() {
        if (version_compare( PHP_VERSION, '5.5.9' ) < 0) {
            print self::$locale['setup_0006'];
            exit;
        }
        if (function_exists( 'opcache_get_status' ) && opcache_get_status()['opcache_enabled'] && !ini_get( 'opcache.save_comments' )) {
            print self::$locale['setup_0007'];
            exit();
        }
    }

    /**
     * Detect system upgrade
     */
    private static function detectSystemUpgrade() {

        // Read the config_temp.php
        self::setEmptyPrefix();

        if (self::$connection = self::fusionGetConfig( BASEDIR . 'config_temp.php' )) {

            if (empty( self::$connection['db_driver'] )) {
                self::$connection['db_driver'] = FALSE;
            }

            require_once(INCLUDES . 'multisite_include.php');

            $validation = Requirements::getSystemValidation();

            $version = fusion_get_settings( 'version' );

            if (!empty( $version )) {

                if (isset( $validation[3] )) {

                    if (version_compare( self::BUILD_VERSION, $version, ">" )) {

                        $_GET['upgrade'] = TRUE;

                        if (!defined( 'COOKIE_PREFIX' ) && !empty( self::$connection['COOKIE_PREFIX'] )) {

                            define( 'COOKIE_PREFIX', self::$connection['COOKIE_PREFIX'] );
                        }

                        if (!defined( 'DB_PREFIX' ) && !empty( self::$connection['DB_PREFIX'] )) {

                            define( 'DB_PREFIX', self::$connection['DB_PREFIX'] );
                        }
                        //self::set_empty_prefix();
                    }
                }
            }
        }
    }

    /**
     * Set empty prefix
     */
    protected static function setEmptyPrefix() {

        $default_init = [
            'db_host'       => '',
            'db_port'       => '',
            'db_name'       => '',
            'db_user'       => '',
            'db_pass'       => '',
            'db_prefix'     => '',
            'cookie_prefix' => '',
        ];


        if (is_file( BASEDIR . 'config_temp.php' ) && filesize( BASEDIR . 'config_temp.php' ) > 0) { // config_temp might be blank
            self::$connection = self::fusionGetConfig( BASEDIR . "config_temp.php" );                // All fields must be not empty
        }

        self::$connection = self::$connection + $default_init;

        if (empty( self::$connection['db_prefix'] )) {
            self::$connection['db_prefix'] = 'fusion' . self::createRandomPrefix() . '_';
        }

        if (empty( self::$connection['cookie_prefix'] )) {
            self::$connection['cookie_prefix'] = 'fusion' . self::createRandomPrefix() . '_';
        }

        if (empty( self::$connection['secret_key'] )) {
            self::$connection['secret_key'] = self::createRandomPrefix( 32 );
        }

        if (empty( self::$connection['secret_key_salt'] ) && !defined( 'SECRET_KEY_SALT' )) {
            self::$connection['secret_key_salt'] = self::createRandomPrefix( 32 );
        }


    }

    /**
     * @param string $config_path
     *
     * @return array
     */
    public static function fusionGetConfig( $config_path ) {
        if (empty( self::$config ) && is_file( $config_path ) && filesize( $config_path ) > 0) {
            include $config_path;
            $default_path = [];
            if (isset( $db_host )) {
                $default_path['db_host'] = $db_host;
            }
            if (isset( $db_port )) {
                $default_path['db_port'] = $db_port;
            }
            if (isset( $db_user )) {
                $default_path['db_user'] = $db_user;
            }
            if (isset( $db_pass )) {
                $default_path['db_pass'] = $db_pass;
            }
            if (isset( $db_name )) {
                $default_path['db_name'] = $db_name;
            }
            if (isset( $db_prefix )) {
                $default_path['db_prefix'] = $db_prefix;
            }
            if (isset( $db_driver )) {
                $default_path['db_driver'] = $db_driver;
            }
            if (defined( 'DB_PREFIX' )) {
                $default_path['DB_PREFIX'] = DB_PREFIX;
            }
            if (defined( 'COOKIE_PREFIX' )) {
                $default_path['COOKIE_PREFIX'] = COOKIE_PREFIX;
            }
            if (defined( 'SECRET_KEY' )) {
                $default_path['SECRET_KEY'] = SECRET_KEY;
            }
            if (defined( 'SECRET_KEY_SALT' )) {
                $default_path['SECRET_KEY_SALT'] = SECRET_KEY_SALT;
            }
            self::$config = $default_path;
        }

        return self::$config;
    }

    /**
     * @param int $length
     *
     * @return string
     */
    public static function createRandomPrefix( $length = 5 ) {
        $chars = ["abcdefghijklmnpqrstuvwxyz", "123456789"];
        $count = [(strlen( $chars[0] ) - 1), (strlen( $chars[1] ) - 1)];
        $prefix = "";
        for ($i = 0; $i < $length; $i++) {
            $type = mt_rand( 0, 1 );
            $prefix .= substr( $chars[$type], mt_rand( 0, $count[$type] ), 1 );
        }

        return $prefix;
    }

    /**
     * Install PHPFusion
     */
    public function installPhpfusion() {

        $current_content = $this->getInstallerContent();

        $content = Console::getConsoleInstance()->getView( $current_content );

        echo strtr( Console::getConsoleInstance()->getLayout(), ['{%content%}' => $content] );
    }

    /**
     * @return false|string|null
     */
    private function getInstallerContent() {

        OutputHandler::addToJQuery( "
            $('form').change(function() {
                window.onbeforeunload = function() {
                    return true;
                }
                $(':button').bind('click', function() {
                    window.onbeforeunload = null;
                });
            });
        " );
        // Instead of using INSTALLATION STEP, we let each file control
        switch (INSTALLATION_STEP) {
            case self::STEP_INTRO:
            default:
                defined( 'COOKIE_PREFIX' ) || define( 'COOKIE_PREFIX', 'installer_' );
                return Introduction::servePage()->view();
                break;
            case self::STEP_PERMISSIONS:
                defined( 'COOKIE_PREFIX' ) || define( 'COOKIE_PREFIX', 'installer_' );
                return Permissions::servePage()->view();
                break;
            case self::STEP_DB_SETTINGS_SAVE:
            case self::STEP_DB_SETTINGS_FORM:
                defined( 'COOKIE_PREFIX' ) || define( 'COOKIE_PREFIX', 'installer_' );
                return DatabaseSetup::servePage()->view();
                break;
            case self::STEP_TRANSFER:
            case self::STEP_PRIMARY_ADMIN_SAVE:
            case self::STEP_PRIMARY_ADMIN_FORM:
                defined( 'COOKIE_PREFIX' ) || define( 'COOKIE_PREFIX', 'installer_' );

                return AdminSetup::servePage()->view();
                break;
            case self::STEP_INFUSIONS:
                return InfusionsSetup::servePage()->view();
                break;
            case self::STEP_SETUP_COMPLETE:
            case self::STEP_EXIT:
                if (file_exists( BASEDIR . 'config_temp.php' )) {
                    @rename( BASEDIR . 'config_temp.php', BASEDIR . 'config.php' );
                    @chmod( BASEDIR . 'config.php', 0644 );
                }
                maintenance_mode( FALSE );
                unset( $_SESSION['step'] );
                redirect( BASEDIR . 'index.php' );
                break;
        }

        return FALSE;
    }

    /**
     * @return static
     */
    protected static function servePage() {
        if (empty( self::$document )) {
            self::$document = new static();
        }

        return self::$document;
    }

    /**
     * Installer system checks
     * Redirect to step 1 if the database has been intentionally dropped during the installation.
     */
    protected function tableCheck() {
        if (!empty( self::$connection['db_name'] )) {
            $result = dbquery( "SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema='" . self::$connection['db_name'] . "' AND TABLE_NAME='" . DB_USERS . "'" );
            if (dbrows( $result )) {
                return TRUE;
            } else {
                $_SESSION['step'] = 1;
                redirect( FUSION_REQUEST );
            }
        }
        return FALSE;
    }

    private function __clone() {
    }
}
