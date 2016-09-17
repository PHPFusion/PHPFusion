<?php
/*
<Files ~ "\.inc$">
Order allow,deny
Deny from all
</Files>
*/
namespace PHPFusion\Installer;

use PHPFusion\Authenticate;
use PHPFusion\Database\DatabaseFactory;
use PHPFusion\Infusion;
use PHPFusion\Locale;

class Install_Core {

    const STEP_INTRO = 1;
    const STEP_PERMISSIONS = 2;
    const STEP_DB_SETTINGS_FORM = 3;
    const STEP_DB_SETTINGS_SAVE = 4;
    const STEP_PRIMARY_ADMIN_FORM = '5';   //must be between quotation marks because of implicit type conversion
    const STEP_PRIMARY_ADMIN_SAVE = '5/2';
    const STEP_INFUSIONS = 6;
    const STEP_SETUP_COMPLETE = 7;
    const STEP_EXIT = 8;
    const BUILD_VERSION = 9.02;
    const INSTALLER_ALGO = 'sha256';
    const USER_RIGHTS_SA = 'A.AD.APWR.B.BB.BLOG.C.CP.DB.ERRO.FQ.F.FR.IM.I.IP.M.MI.MAIL.P.PH.PI.PL.PO.ROB.SL.S1.S2.S3.S4.S5.S6.S7.S8.S9.S10.S11.S12.S13.SB.SM.SU.UF.UFC.UG.UL.U.TS.W.LANG';
    protected static $locale = array();
    protected static $localeset = 'English';
    /*
     * next can be STEP_PERMISSIONS;
     * back can be STEP_INTRODUCTION;
     * @var array
     */
    protected static $step = array(
        'next' => FALSE,
        'previous' => FALSE,
    );

    protected static $connection = [
        'db_host' => 'localhost',
        'db_user' => NULL,
        'db_pass' => NULL,
        'db_name' => NULL,
        'db_prefix' => NULL,
        'cookie_prefix' => NULL,
        'secret_key_salt' => NULL,
        'secret_key' => NULL,
        'pdo_enabled' => NULL
    ];

    private static $siteData = [
        'sitename' => '',
        'siteemail' => '',
        'enabled_languages' => ''
    ];

    private static $userData = [
        'user_name' => '',
        'user_email' => '',
        'user_timezone' => '',
    ];

    /*
     * Verify the requirements that allows you to run the installer before boot up.
     * Due to the support for PHP-Fusion 9 in many uses of empty() as a condition
     * and being counter productive in fixing low end php version deprecated codes /e,
     * no oPCache, and other problems, using PHP-Fusion 9 is not going to be allowed
     * entirely.
     */
    private static $setup_instance = NULL;
    private static $locale_files = array();

    /*
     * Defining the steps and ensure that there are no field left blank
     */

    protected function __construct() {
        require_once __DIR__.'/Requirements.core.inc';
    }

    /*
     * Accessors and Mutators method implementation of the base of
     * installer and subsequently to replace on output.
     */

    public static function getInstance() {

        if (self::$setup_instance == NULL) {

            self::$setup_instance = new static();

            session_start();

            self::define_step();

            self::verify_requirements();

            ini_set('display_errors', 1);

            define('iMEMBER', FALSE);

            define("FUSION_QUERY", isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "");

            define("FUSION_SELF", basename($_SERVER['PHP_SELF']));

            define("FUSION_REQUEST", isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != "" ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME']);

            // this go for session as well.
            self::$localeset = filter_input(INPUT_GET, 'localeset') ?: (isset($settings['locale']) ? $settings['locale'] : 'English');

            define('LANGUAGE', is_dir(LOCALE.self::$localeset) ? self::$localeset : 'English');
            define("LOCALESET", LANGUAGE."/");

            // ALWAYS reset config to config_temp.php
            if (file_exists(BASEDIR.'config.php')) {
                @rename(BASEDIR.'config.php', BASEDIR.'config_temp.php');
                @chmod(BASEDIR.'config_temp.php', 0755);
            }

            if (file_exists(BASEDIR.'config_temp.php')) { // config_temp might be blank

                include BASEDIR.'config_temp.php';

                if (!empty($db_host) && !empty($db_user) && !empty($db_pass) && !empty($db_name) && isset($pdo_enabled)) {

                    require_once INCLUDES."db_handlers/all_functions_include.php";

                    self::$connection = [
                        'db_host' => $db_host,
                        'db_user' => $db_user,
                        'db_pass' => $db_pass,
                        'db_name' => $db_name,
                        'pdo_enabled' => $pdo_enabled,
                        'db_prefix' => DB_PREFIX,
                        'secret_key' => SECRET_KEY,
                        'secret_key_salt' => SECRET_KEY_SALT,
                        'cookie_prefix' => COOKIE_PREFIX
                    ];

                    // cannot declare, will go to uninstaller.
                    //self::$db_prefix = DB_PREFIX;

                    DatabaseFactory::setDefaultDriver(intval(self::$connection['pdo_enabled']) === 1 ? DatabaseFactory::DRIVER_PDO_MYSQL : DatabaseFactory::DRIVER_MYSQL);
                    $connection = dbconnect(self::$connection['db_host'], self::$connection['db_user'], self::$connection['db_pass'],
                                            self::$connection['db_name'], FALSE);

                    if (INSTALLATION_STEP > self::STEP_PERMISSIONS) {
                        if ($connection['dbselection_success'] === FALSE) {
                            $_SESSION['step'] = STEP_INTRO;
                            redirect(filter_input(INPUT_SERVER, 'REQUEST_URI'));
                        }
                    }
                } else {
                    @unlink(BASEDIR."config_temp.php");
                    $_SESSION['step'] = self::STEP_INTRO;
                    redirect(filter_input(INPUT_SERVER, 'REQUEST_URI'));
                }

            } else {

                // For the form in DB Settings Form
                if (empty(self::$connection['db_prefix'])) {
                    self::$connection['db_prefix'] = 'fusion'.self::createRandomPrefix().'_';
                }

                if (empty(self::$connection['cookie_prefix'])) {
                    self::$connection['cookie_prefix'] = 'fusion'.self::createRandomPrefix().'_';
                }
                if (empty(self::$connection['secret_key'])) {
                    self::$connection['secret_key'] = self::createRandomPrefix(32);
                }
                if (empty(self::$connection['secret_key_salt'])) {
                    self::$connection['secret_key_salt'] = self::createRandomPrefix(32);
                }

            }

            self::$locale_files = fusion_get_detected_language();

            self::$locale = Locale::getLocale('', LOCALE.LOCALESET."setup.php");

            new \dynamics();
        }

        return self::$setup_instance;
    }

    /*
     * Fetches Installer Functions
     */

    private static function define_step() {

        if (!defined('INSTALLATION_STEP') && \defender::safe()) {

            $_SESSION['step'] = (!isset($_SESSION['step']) ? self::STEP_INTRO : $_SESSION['step']);
            // current session

            if (isset($_POST['infuse']) || isset($_POST['defuse'])) {
                $_SESSION['step'] = self::STEP_INFUSIONS;
            } elseif (isset($_POST['step'])) {
                $_SESSION['step'] = $_POST['step'];
            }
            define('INSTALLATION_STEP', $_SESSION['step']);
            //$_SESSION['step'] = self::STEP_INFUSIONS;
            //define('INSTALLATION_STEP', self::STEP_INTRO);
        }

    }

    // Installation Step 1
    private static function verify_requirements() {
        if (version_compare(PHP_VERSION, '5.5.9') < 0) {
            print 'PHP-Fusion 9 requires at least PHP 5.5.9. See the <a href="https://www.php-fusion.co.uk/requirements">system requirements</a> page for more information.';
            exit;
        }
        if (function_exists('opcache_get_status') && opcache_get_status()['opcache_enabled'] && !ini_get('opcache.save_comments')) {
            print 'Systems with OPcache installed must have <a href="http://php.net/manual/en/opcache.configuration.php#ini.opcache.save-comments">opcache.save_comments</a> enabled.';
            exit();
        }
    }

    // Installation Step 2
    public static function createRandomPrefix($length = 5) {
        $chars = array("abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ", "123456789");
        $count = array((strlen($chars[0]) - 1), (strlen($chars[1]) - 1));
        $prefix = "";
        for ($i = 0; $i < $length; $i++) {
            $type = mt_rand(0, 1);
            $prefix .= substr($chars[$type], mt_rand(0, $count[$type]), 1);
        }

        return $prefix;
    }

    // Installation Step 3

    public function install_phpfusion() {
        $content = Console_Core::getInstance()->getView($this->get_InstallerContent());
        echo strtr(Console_Core::getInstance()->getLayout(), array("{%content%}" => $content));
    }

    private function get_InstallerContent() {

        switch (INSTALLATION_STEP) {
            // Introduction
            case self::STEP_INTRO:
            default:
                return $this->step_introduction();
                break;
            // Step 2 - File and Folder Permissions
            case self::STEP_PERMISSIONS:
                return $this->step_permissions();
                break;
            // Step 3 - Database Settings
            case self::STEP_DB_SETTINGS_FORM:
                return $this->step_dbSettings();
                break;
            // Step 4 - Config / Database Setup
            case self::STEP_DB_SETTINGS_SAVE:
                return $this->step_dbCreate();
                break;
            // Step 5 - Primary Admin Details
            case self::STEP_PRIMARY_ADMIN_FORM:
                return $this->step_AdminSettings();
                break;
            // Step 6 - Configure Core System - $settings accessible - Requires Config_temp.php (Shut down site when upgrading).
            case self::STEP_INFUSIONS:
                return $this->step_Infusions();
                break;
            // Step 7 - Final Settings
            case self::STEP_SETUP_COMPLETE:
                return $this->stepComplete();
                break;
            case self::STEP_EXIT:
                return $this->stepExit();
                break;
        }
    }

    private function step_introduction() {

        $content = "";

        // create htaccess file.
        if (isset($_POST['htaccess'])) {

            dbconnect(self::$connection['db_host'], self::$connection['db_user'], self::$connection['db_pass'], self::$connection['db_name'], FALSE);

            /*
             * We need to include it to create DB_SETTINGS
             * for fusion_get_settings()
             *
             * TODO: Find better way
             */
            require_once INCLUDES.'multisite_include.php';
            $site_path = fusion_get_settings('site_path');
            write_htaccess($site_path);
            redirect(FUSION_SELF."?localeset=".LANGUAGE);
        }

        // Must always include a temp file.
        /* 1. To enter Recovery. CONFIG TEMP file must have dbprefix and have value in dbprefix. */
        if (!empty(self::$db_prefix) && !empty(self::$connection['db_name'])) {

            $this->step_Uninstall();

        } elseif (!isset($_POST['uninstall'])) {

            /* Without click uninstall this is the opening page of installer - just for safety. if not, an else suffices */
            // no db_prefix
            $content .= "<h4 class='strong'>".self::$locale['setup_0002']."</h4>\n";
            $content .= "<p>".self::$locale['setup_0003']."</p>\n";
            $content .= "<p>".self::$locale['setup_1001']."</p>\n";
            $content .= "<hr/>";
            $content .= "<h3 class='display-block m-t-20 m-b-10 strong'>".self::$locale['setup_1000']."</h3>\n";
            $content .= form_select('localeset', '', LANGUAGE,
                                    array(
                                        'options' => self::$locale_files,
                                    )
            );
            if (isset($_GET['error']) && $_GET['error'] == 'license') {
                $content .= "<div class='alert alert-danger'>".self::$locale['setup_5000']."</div>\n";
            }
            $content .= form_checkbox('license', self::$locale['setup_0005'], '',
                                      array(
                                          'reverse_label' => TRUE,
                                          'required' => TRUE,
                                          'error_text' => self::$locale['setup_5000']
                                      )
            );
            add_to_jquery("
            $('#localeset').bind('change', function() {
            var value = $(this).val();
            document.location.href='".FUSION_SELF."?localeset='+value;
            });
            ");
        }

        self::$step = array(
            1 => array(
                'name' => 'step',
                'label' => self::$locale['setup_0121'],
                'value' => self::STEP_PERMISSIONS
            )
        );

        return $content;
    }

    /*
     * Step 2 creates a blank config_temp.php
     */

    private function step_Uninstall() {

        if (isset($_POST['uninstall'])) {
            // sandbox this with an un-installer instead of progressive execution
            include_once 'includes/core_setup.php'; // See below previous comment
            /*
             * Q: why does it still produce flash of error message?, Locale ?
             * A: Don't know...  things happens when you execute from all the way from China to London.
             * Solution: catch_exception. Halt the installer with a single mistake.
             */
            @unlink(BASEDIR.'config_temp.php');
            @unlink(BASEDIR.'config.php');
            @unlink(BASEDIR.'.htaccess');
            redirect(BASEDIR."install/index.php", 3);
        }

        $content = "<h4 class='strong'>".self::$locale['setup_1002']."</h4>\n";
        $content .= "<span class='display-block m-t-20 m-b-10'>".self::$locale['setup_1003']."</span>\n";
        $content .= "<div class='well'>\n";
        $content .= "<span class='strong display-inline-block m-b-10'>".self::$locale['setup_1017']."</span><br/><p>".self::$locale['setup_1018']."</p>";
        $content .= form_button('step', self::$locale['setup_1019'], self::STEP_EXIT, array('class' => 'btn-success btn-sm m-t-10'));
        $content .= "</div>\n";
        $content .= "<div class='well'>\n";
        $content .= "<span class='strong display-inline-block m-b-10'>".self::$locale['setup_1004']."</span><br/><p>".self::$locale['setup_1005']." <span class='strong'>".self::$locale['setup_1006']."</span></p>";
        $content .= form_button('uninstall', self::$locale['setup_1007'], 'uninstall', array('class' => 'btn-danger btn-sm m-t-10'));
        $content .= "</div>\n";
        $content .= "<div class='well'>\n";
        $content .= "<span class='strong display-inline-block m-b-10'>".self::$locale['setup_1008']."</span>\n<br/><p>".self::$locale['setup_1009']."</p>";
        $content .= form_button('step', self::$locale['setup_1010'], self::STEP_INFUSIONS, array('class' => 'btn-primary btn-sm m-r-10'));
        $content .= "</div>\n";
        $content .= "<div class='well'>\n";
        $content .= "<span class='strong display-inline-block m-b-10'>".self::$locale['setup_1011']."</span>\n<br/><p>".self::$locale['setup_1012']."</p>";
        $content .= form_button('step', self::$locale['setup_1013'], self::STEP_PRIMARY_ADMIN_FORM,
                                array('class' => 'btn-primary btn-sm m-r-10'));
        $content .= "</div>\n";
        $content .= "<input type='hidden' name='localeset' value='".stripinput(LANGUAGE)."' />\n";
        if (isset($db_prefix)) {
            $content .= "<div class='well'>\n";
            $content .= "<span class='strong display-inline-block m-b-10'>".self::$locale['setup_1014']."</span>\n<br/><p>".self::$locale['setup_1015']."</p>";
            $content .= form_button('htaccess', self::$locale['setup_1016'], 'htaccess', array('class' => 'btn-primary btn-sm m-r-10'));
            $content .= "</div>\n";
        }
    }

    // @todo:revisit for update

    private function step_permissions() {

        if (!isset($_POST['license'])) {
            $_SESSION['step'] = self::STEP_INTRO;
            redirect(FUSION_SELF."?error=license&localeset=".LANGUAGE);
        }

        $content = '';
        $content .= "<div class='m-b-20'><h4>".self::$locale['setup_1106']."</h4></div>\n";
        $content .= "<table class='table table-responsive table-hover'>\n";
        // Check Environmental requirements

        $system_health = 10;
        foreach (Requirements::get_system_requirements() as $test) {
            $class = '';
            if (isset($test['severability'])) {
                $system_health = $system_health - intval($test['severability']);
                if ($test['severability'] > 5) {
                    $class = "alert";
                } else {
                    $class = "warning";
                }
            }
            $content .= "<tr ".($class ? "class='$class'" : '').">\n";
            $content .= "<td>\n".$test['title']."</td>\n";
            $content .= "<td>\n";
            $content .= (isset($test['value']) ? $test['value'].'<br />' : '').(isset($test['description']) ? $test['description'] : '');
            if (isset($test['sub'])) {
                $warned_content = '';
                $show_sub = FALSE;
                foreach ($test['sub'] as $key => $value) {
                    if (isset($value['severability'])) {
                        $show_sub = TRUE;
                        $warned_content .= "<tr><td>$key</td><td>$value</td></tr>\n";
                    }
                }

                if ($show_sub === TRUE) {
                    $content .= "<div class='m-t-20'>\n";
                    $content .= "<table class='table'><tr><th>Files</th><th>Status</th></tr>";
                    $content .= $warned_content;
                    $content .= "</table>";
                    $content .= "</div>";
                }
            }
            $content .= "</td>\n";
            $content .= "</tr>\n";
        }
        $content .= "</table><br /><br />\n";
        // can proceed
        if ($system_health > 6) {
            self::$step = array(
                1 => array(
                    'name' => 'step',
                    'label' => self::$locale['setup_0121'],
                    'value' => self::STEP_DB_SETTINGS_FORM
                )
            );
        } else {
            self::$step = array(
                1 => array(
                    'name' => 'step',
                    'type' => 'tryagain',
                    'label' => self::$locale['setup_0122'],
                    'value' => self::STEP_PERMISSIONS
                )
            );
            $content .= form_hidden('license', '', '1');
        }

        return $content;
    }

    /*
     * Batching process begins
     */

    private function step_dbSettings() {

        $content = "<div class='m-b-20'><h4>".self::$locale['setup_1200']."</h4> ".self::$locale['setup_1201']."</div>\n";

        $content .= form_text('db_host', self::$locale['setup_1202'], self::$connection['db_host'], array('inline' => TRUE, 'required' => TRUE));
        $content .= form_text('db_user', self::$locale['setup_1203'], self::$connection['db_user'], array('inline' => TRUE, 'required' => TRUE));
        $content .= form_text('db_pass', self::$locale['setup_1204'], self::$connection['db_pass'],
                              array('type' => 'password', 'inline' => TRUE, 'required' => TRUE));
        $content .= form_text('db_name', self::$locale['setup_1205'], self::$connection['db_name'], array('inline' => TRUE, 'required' => TRUE));

        $content .= "<div class='m-b-20'>\n<h4>Database Configurations and Driver</h4>\n</div>\n";
        $content .= form_text('db_prefix', self::$locale['setup_1206'], self::$connection['db_prefix'], array('inline' => TRUE, 'required' => TRUE));
        $content .= form_text('cookie_prefix', self::$locale['setup_1207'], self::$connection['cookie_prefix'],
                              array('inline' => TRUE, 'required' => TRUE));
        $options[0] = 'MySQL Driver';
        if (defined('PDO::ATTR_DRIVER_NAME')) {
            $options[1] = 'PDO Driver';
        }
        $content .= form_select('pdo_enabled', self::$locale['setup_1208'], 1,
                                array(
                                    'options' => $options,
                                    'inline' => TRUE,
                                )
        );

        self::$step = array(
            1 => array(
                'name' => 'step',
                'label' => self::$locale['setup_0121'],
                'value' => self::STEP_DB_SETTINGS_SAVE
            )
        );

        return $content;
    }

    private function step_dbCreate() {

        // Handle insertions of core settings table
        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            self::$connection = [
                'db_host' => (string)\defender::getInstance()->form_sanitizer($_POST['db_host'], self::$connection['db_host'], 'db_host'),
                'db_user' => (string)\defender::getInstance()->form_sanitizer($_POST['db_user'], self::$connection['db_user'], 'db_user'),
                'db_pass' => (string)\defender::getInstance()->form_sanitizer($_POST['db_pass'], self::$connection['db_pass'], 'db_pass'),
                'db_name' => (string)\defender::getInstance()->form_sanitizer($_POST['db_name'], self::$connection['db_name'], 'db_name'),
                'db_prefix' => (string)\defender::getInstance()->form_sanitizer($_POST['db_prefix'], 'fusion_', 'db_prefix'),
                'cookie_prefix' => (string)\defender::getInstance()->form_sanitizer($_POST['cookie_prefix'], 'fusion_', 'cookie_prefix'),
                'pdo_enabled' => (bool)\defender::getInstance()->form_sanitizer($_POST['pdo_enabled'], '', 'pdo_enabled'),
                'secret_key_salt' => self::createRandomPrefix(32),
                'secret_key' => self::createRandomPrefix(32)
            ];
        }

        // This will write a blank config_temp.php if does not exist.
        if ($validation_result = Requirements::get_system_validation()) { // need to halt.

            // include the new config_temp.php
            require_once BASEDIR."config_temp.php";

            $to_create = Batch_Core::getInstance()->batch_runtime('create'); // this should just run once no matter how many times queried.

            // Load profiles - core table records
            // @attention: please delete config_temp.php
            $to_insert = Batch_Core::getInstance()->batch_runtime('insert'); // must return array to insert with table.

            // Get total test phase count
            $total_tests = count($validation_result) + count($to_create) + count($to_insert) + 1; // add an extra step

            foreach ($validation_result as $current_count => $system_result) {
                Batch_Core::getInstance()->Progress($current_count, $total_tests, $system_result['elapsed'], $system_result['description'],
                                                    $system_result['result']);
            }

            $final_message = 'PHP-Fusion installation errors. Please restart installer.';
            // Go for point system differentiation
            if (Batch_Core::getInstance()->ProgressHasError() === FALSE) {
                $final_message = 'New PHP-Fusion installation completed. Please proceed to the next step.';

                // Run Update - alterations.


                // Run Creations - new installation.
                if (!empty($to_create)) {
                    foreach ($to_create as $table_name => $table_process) {
                        $current_count = $current_count + 1;
                        $microtime = microtime(TRUE);
                        if ($result = dbquery($table_process)) {
                            $microtime = microtime(TRUE) - $microtime;
                        }
                        Batch_Core::getInstance()->Progress($current_count, $total_tests, $microtime, 'Installing '.$table_name.'...', $result);
                    }
                }
                // insert rows
                if (!empty($to_insert)) {
                    foreach ($to_insert as $table_name => $row_inserts) {
                        $current_count = $current_count + 1;
                        $microtime = microtime(TRUE);
                        if ($result = dbquery($row_inserts)) {
                            $microtime = microtime(TRUE) - $microtime;
                            Batch_Core::getInstance()->Progress($current_count, $total_tests, $microtime, 'Populating '.$table_name.'...', $result);
                        }
                    }
                }

            }

            /*
             * Generate final message
             */
            $errors = Batch_Core::getInstance()->ProgressHasError();
            if ($errors == FALSE) {
                Batch_Core::getInstance()->Progress($total_tests, $total_tests, 15, $final_message, 1);
            }

            // Displays Output
            $content = "<div class='m-b-20'><h4>".self::$locale['setup_0104a']."</h4> ".self::$locale['setup_1209']."</div>\n";
            $content .= "<div class='progress'>\n";
            $content .= "<div id='progress-bar' style='height:80px' class='progress-bar progress-bar-striped active' role='progressbar'>\n";
            $content .= "<span id='pcg'>0</span>\n";
            $content .= "</div>\n</div>\n";
            $content .= "<div id='content' class='text-center m-b-20'>\n</div>\n";

            self::$step = array(
                1 => array(
                    'name' => 'step',
                    'label' => self::$locale['setup_0121'],
                    'value' => ($errors) ? self::STEP_DB_SETTINGS_FORM : self::STEP_PRIMARY_ADMIN_FORM
                )
            );
            $_batchedJs = Batch_Core::getInstance()->getProgress();
            $hide_button_js = "$('#step').hide();";
            $show_button_js = "setTimeout(function() { $('#step').show(); }, 100);";
            $_batchedJs = str_replace('{%next%}', $show_button_js, $_batchedJs);
            add_to_jquery($hide_button_js.$_batchedJs);
        }

        return $content;
    }

    private function step_adminSettings() {

        if ($this->InstallSecured()) {

            $this->step_dbUpdate();

            $content = "<div class='m-b-20'>\n<h4>Site and Super Administrator Configurations</h4>\nSite Information Details</div>\n";
            $content .= form_text('sitename', 'Site Name', self::$siteData['sitename'],
                                  array(
                                      'inline' => TRUE, 'required' => TRUE, 'placeholder' => 'PHP-Fusion Powered Website', 'class' => 'form-group-lg'
                                  ));
            $content .= form_text('siteemail', 'Website Email', self::$siteData['siteemail'],
                                  array('inline' => TRUE, 'required' => TRUE, 'type' => 'email'));

            $timezones = timezone_abbreviations_list();
            $timezoneArray = array();
            foreach ($timezones as $zones) {
                foreach ($zones as $zone) {
                    if (preg_match('/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $zone['timezone_id'])) {
                        if (!in_array($zone['timezone_id'], $timezoneArray)) {
                            $timezoneArray[$zone['timezone_id']] = $zone['timezone_id'];
                        }
                    }
                }
            }
            unset($timezones);
            $content .= form_select('user_timezone', 'Select Site Region', self::$userData['user_timezone'],
                                    array('options' => $timezoneArray, 'required' => TRUE, 'inline' => TRUE));
            $content .= "<hr />\n";
            $content .= "<div class='m-b-20'><h4>".self::$locale['setup_1500']."</h4> ".self::$locale['setup_1501']."</div>\n";
            $content .= form_text('user_name', self::$locale['setup_1504'], self::$userData['user_name'],
                                  array(
                                      'required' => TRUE,
                                      'inline' => TRUE,
                                      'maxlength' => 30,
                                      'error_text' => self::$locale['setup_5010'],
                                      'callback_check' => 'username_check'
                                  )
            );
            $content .= form_text('user_email', self::$locale['setup_1509'], self::$userData['user_email'],
                                  array('required' => TRUE, 'inline' => TRUE, 'type' => 'email', 'error_text' => self::$locale['setup_5020']));
            $content .= form_text('password1', self::$locale['setup_1505'], '',
                                  array('required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'));
            $content .= form_text('password2', self::$locale['setup_1506'], '',
                                  array('required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'));
            $content .= form_text('admin_password1', self::$locale['setup_1507'], '',
                                  array('required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'));
            $content .= form_text('admin_password2', self::$locale['setup_1508'], '',
                                  array('required' => TRUE, 'inline' => TRUE, 'maxlength' => 64, 'type' => 'password'));

            $content .= "<div class='m-b-20'>\n<h4>Site Language Installations</h4>\n".self::$locale['setup_1001']."</div>\n";

            $checkbox_options = [];
            foreach (self::$locale_files as $languageKey => $languageName) {
                $localeFlagPath = BASEDIR."locale/".$languageKey."/$languageKey-s.png";
                $checkbox_options[$languageKey] = "<img src='".$localeFlagPath."' class='m-l-15' alt='$languageName'/> $languageName";
            }

            $content .= form_checkbox('enabled_languages[]', '',
                                      !empty(self::$siteData['enabled_languages']) ? self::$siteData['enabled_languages'] : self::$localeset, array(
                'required' => TRUE,
                'reverse_label' => TRUE,
                'class' => 'm-0 p-0 input-md',
                'options' => $checkbox_options,
                'deactivate_key' => self::$localeset
            ));
            $content .= "<hr />\n";

            self::$step = array(
                1 => array(
                    'name' => 'step',
                    'label' => self::$locale['setup_0121'],
                    'value' => self::STEP_PRIMARY_ADMIN_FORM
                )
            );

            return $content;
        }

    }

    private function InstallSecured() {
        /*
         * Site configuration and account settings must have a valid config file.
         */
        if (!file_exists(BASEDIR.'config_temp.php')) {
            redirect(filter_input(INPUT_SERVER, 'REQUEST_URI'));
        }

        /*
         * Do not allow site update without settings field.
         */
        if (!db_exists(DB_PREFIX."settings")) {
            $_SESSION['step'] = self::STEP_DB_SETTINGS_FORM;
            redirect(filter_input(INPUT_SERVER, 'REQUEST_URI'));
        }

        /*
         * If there is already a super admin, go to infusions
         */
        if (dbcount("(user_id)", DB_PREFIX."users")) {
            $_SESSION['step'] = self::STEP_INFUSIONS;
            redirect(filter_input(INPUT_SERVER, 'REQUEST_URI'));
        }

        return TRUE;
    }

    private function step_dbUpdate() {

        if (isset($_POST['sitename']) && isset($_POST['siteemail']) && isset($_POST['user_name'])) {

            function user_name_check($username) {
                return !preg_match("/^[-0-9A-Z_@\s]+$/i", $username);
            }

            self::$siteData = [
                'sitename' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'sitename'), '', 'sitename'),
                'siteemail' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'siteemail'), '', 'siteemail'),
                'enabled_languages' => \defender::getInstance()->form_sanitizer((isset($_POST['enabled_languages']) ? $_POST['enabled_languages'] : ''),
                    '', 'enabled_languages'),
            ];

            self::$userData = [
                'user_name' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'user_name'), '', 'user_name'),
                'user_email' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'user_email'), '', 'user_email'),
                'user_algo' => self::INSTALLER_ALGO,
                'user_salt' => '',
                'user_password' => '',
                'user_admin_algo' => self::INSTALLER_ALGO,
                'user_admin_salt' => '',
                'user_admin_password' => '',
                'password1' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'password1'), '', 'password1'),
                'password2' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'password2'), '', 'password2'),
                'admin_password1' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'admin_password1'), '', 'admin_password1'),
                'admin_password2' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'admin_password2'), '', 'admin_password2'),
                'user_rights' => self::USER_RIGHTS_SA,
                'user_hide_email' => 1,
                'user_timezone' => \defender::getInstance()->form_sanitizer(filter_input(INPUT_POST, 'user_timezone'), '', 'user_timezone'),
                'user_joined' => TIME,
                'user_lastvisit' => TIME,
                'user_ip' => USER_IP,
                'user_level' => USER_LEVEL_SUPER_ADMIN,
                'user_status' => '0',
                'user_theme' => 'Default',
                'user_birthdate' => '1900-01-01',
            ];

            if (self::$userData['password1'] == self::$userData['admin_password1']) {
                \defender::getInstance()->setErrorText('password1', self::$locale['setup_5016']);
                \defender::getInstance()->setErrorText('admin_password1', self::$locale['setup_5016']);
                \defender::stop();
            }

            if (\defender::safe()) {

                $user_auth = new \PasswordAuth(self::INSTALLER_ALGO);

                $user_auth->inputNewPassword = self::$userData['password1'];
                $user_auth->inputNewPassword2 = self::$userData['password2'];

                switch ($user_auth->isValidNewPassword()) {
                    default:
                        self::$userData['user_password'] = $user_auth->getNewHash();
                        self::$userData['user_salt'] = $user_auth->getNewSalt();
                        break;
                    case 2:
                        \defender::getInstance()->setErrorText('password1', self::$locale['setup_5012']);
                        break;
                    case 3:
                        \defender::getInstance()->setErrorText('password1', self::$locale['setup_5013']);
                        break;
                }

                $admin_auth = new \PasswordAuth(self::INSTALLER_ALGO);
                $admin_auth->inputNewPassword = self::$userData['admin_password1'];
                $admin_auth->inputNewPassword2 = self::$userData['admin_password2'];
                switch ($admin_auth->isValidNewPassword()) {
                    default:
                        self::$userData['user_admin_password'] = $admin_auth->getNewHash();
                        self::$userData['user_admin_salt'] = $admin_auth->getNewSalt();
                        break;
                    case 2:
                        \defender::getInstance()->setErrorText('admin_password1', self::$locale['setup_5015']);
                        break;
                    case 3:
                        \defender::getInstance()->setErrorText('admin_password2', self::$locale['setup_5017']);
                        break;
                }

                if (\defender::safe()) {

                    // Create Super Admin
                    dbquery_insert(DB_PREFIX."users", self::$userData, 'save');

                    // Update Site Settings
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['sitename']."' WHERE settings_name='sitename'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['siteemail']."' WHERE settings_name='siteemail'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$siteData['enabled_languages']."' WHERE settings_name='enabled_languages'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$userData['user_timezone']."' WHERE settings_name='default_timezone'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$userData['user_timezone']."' WHERE settings_name='timeoffset'");
                    dbquery("UPDATE ".DB_PREFIX."settings SET settings_value='".self::$userData['user_timezone']."' WHERE settings_name='serveroffset'");

                    if (strpos(self::$siteData['enabled_languages'], ',')) {
                        $result = dbquery("SELECT link_language FROM ".DB_PREFIX."site_links GROUP by link_language ASC");
                        if (dbrows($result) > 0) {
                            while ($data = dbarray($result)) {
                                $installed_languages[] = $data['link_language'];
                            }
                        }
                        $langDiff = array_diff(explode(',', self::$siteData['enabled_languages']), $installed_languages);
                        if (!empty($langDiff)) {
                            foreach ($langDiff as $language) {
                                // Insert rows of other languages for site links
                                $sql_inserts = Batch_Core::getInstance()->batch_insert_rows('site_links', $language);
                                if ($result = dbquery($sql_inserts)) {
                                    continue;
                                }
                                // Insert rows of other languages for email templates
                                $sql_inserts = Batch_Core::getInstance()->batch_insert_rows('email_templates', $language);
                                if ($result = dbquery($sql_inserts)) {
                                    continue;
                                }
                            }
                        }
                    }

                    new Authenticate(self::$userData['user_name'], self::$userData['user_password'], FALSE);

                    $_SESSION['step'] = self::STEP_INFUSIONS;
                    redirect(filter_input(INPUT_SERVER, 'REQUEST_URI'));

                }
            }
        }
    }

    private function step_Infusions() {

        $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/infusions.php");

        add_to_jquery("$('.defuse').bind('click', function() {return confirm('".$locale['412']."');});");

        require_once INCLUDES."multisite_include.php";

        if (($folder = filter_input(INPUT_POST, 'infuse'))) {
            Infusion::getInstance()->infuse($folder);
        } elseif ($folder = filter_input(INPUT_POST, 'defuse')) {
            Infusion::getInstance()->defuse($folder);
        }

        $content = "";
        $temp = opendir(INFUSIONS);
        $infs = array();
        while ($folder = readdir($temp)) {
            if (!in_array($folder, array("..", ".")) && ($inf = Infusion::load_infusion($folder))) {
                $infs[] = $inf;
            }
        }
        closedir($temp);
        $content .= "<div>\n";
        if ($infs) {

            $content .= "<div class='list-group'>\n";
            $content .= "<div class='list-group-item hidden-xs'>\n";
            $content .= "<div class='row'>\n";
            $content .= "<div class='col-xs-2 col-sm-2 col-md-2 col-lg-2'>\n<strong>".$locale['419']."</strong></div>\n";
            $content .= "<div class='col-xs-5 col-sm-5 col-md-4 col-lg-4'>\n<strong>".$locale['400']."</strong></div>\n";
            $content .= "<div class='col-xs-2 col-sm-2 col-md-2 col-lg-2'>\n<strong>".$locale['418']."</strong></div>\n";
            $content .= "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>\n<strong>".$locale['420']."</strong></div>\n";
            $content .= "<div class='hidden-xs hidden-sm hidden-md col-lg-3 col-lg-offset-0'>\n<strong>".$locale['421']."</strong></div>\n";
            $content .= "</div>\n</div>\n";
            $formaction = FUSION_SELF;
            foreach ($infs as $i => $inf) {
                $content .= "<div class='list-group-item'>\n";
                $content .= "<div class='row'>\n";
                $content .= "<div class='col-xs-2 col-sm-2 col-md-2 col-lg-2'>\n";
                if ($inf['status'] > 0) {
                    if ($inf['status'] > 1) {
                        $content .= form_button('infuse', $locale['401'], $inf['folder'],
                                                array('class' => 'btn-info m-t-5 infuse btn-xs', 'icon' => 'entypo magnet'));
                    } else {
                        $content .= form_button('defuse', $locale['411'], $inf['folder'],
                                                array('class' => 'btn-default btn-sm m-t-5 btn-xs defuse', 'icon' => 'entypo trash'));
                    }
                } else {
                    $content .= form_button('infuse', $locale['401'], $inf['folder'],
                                            array('class' => 'btn-primary btn-sm m-t-5 infuse btn-xs', 'icon' => 'entypo install'));
                }
                $content .= "</div>\n";
                $content .= "<div class='col-xs-6 col-sm-6 col-md-4 col-lg-4'><strong>".$inf['name']."</strong><br/>".trimlink($inf['description'],
                                                                                                                               30)."</div>\n";
                $content .= "<div class='col-xs-2 col-sm-2 col-md-2 col-lg-2'>".($inf['status'] > 0 ? "<h5 class='m-0'><label class='label label-success'>".$locale['415']."</label></h5>" : "<h5 class='m-0'><label class='label label-default'>".$locale['414']."</label></h5>")."</div>\n";
                $content .= "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>".($inf['version'] ? $inf['version'] : '')."</div>\n";
                $content .= "<div class='col-xs-10 col-xs-offset-2 col-sm-10 col-sm-offset-2 col-md-10 col-md-offset-1 col-lg-3 col-lg-offset-0'>".($inf['url'] ? "<a href='".$inf['url']."' target='_blank'>" : "")." ".($inf['developer'] ? $inf['developer'] : $locale['410'])." ".($inf['url'] ? "</a>" : "")." <br/>".($inf['email'] ? "<a href='mailto:".$inf['email']."'>".$locale['409']."</a>" : '')."</div>\n";
                $content .= "</div>\n</div>\n";
            }
        } else {
            $content .= "<br /><p class='text-center'>".$locale['417']."</p>\n";
        }
        $content .= "</div>\n</div>\n";
        $content .= "<div class='well text-center m-t-10'>\n";
        $content .= "<a class='btn btn-block btn-primary' href='https://www.php-fusion.co.uk/infusions/addondb/directory.php' title='".$locale['422']."' target='_blank'>".$locale['422']."</a>\n";
        $content .= "</div>\n";

        self::$step = array(
            1 => array(
                'name' => 'step',
                'label' => self::$locale['setup_0121'],
                'value' => self::STEP_SETUP_COMPLETE
            )
        );

        return $content;

    }

    private function stepComplete() {

        $content = "<div class='m-b-20'><h4>".self::$locale['setup_1600']."</h4> ".self::$locale['setup_1601']."</div>\n";
        $content .= "<div class='well'>".self::$locale['setup_1602']."</div>\n";
        $content .= "<div class='m-b-20'><h4>".self::$locale['setup_1603']."</h4></div>\n";
        $content .= "<hr />\n";
        self::$step = array(
            1 => array(
                'name' => 'step',
                'label' => self::$locale['setup_0121'],
                'value' => self::STEP_EXIT
            )
        );

        return $content;
    }

    private function stepExit() {
        if (file_exists(BASEDIR.'config_temp.php')) {
            @rename(BASEDIR.'config_temp.php', BASEDIR.'config.php');
            @chmod(BASEDIR.'config.php', 0644);
        }
        unset($_SESSION['step']);
        redirect(BASEDIR.'index.php');
    }

    private function step_transferOwnership() {


        // Create the site settings, update config, and create the user.
        // we need to check the settings table

        //$locale['setup_5011']


        $error = "";
        $error_pass = "0";
        $error_name = "0";
        $error_mail = "0";


        $rows = dbrows(dbquery("SELECT user_id FROM ".$db_prefix."users WHERE user_id = 1"));
        if ($error == "") {
            if ($rows) {
                // Create Super Admin with Full Modular Rights - We don't need to update Super Admin later.
                dbquery("UPDATE ".$db_prefix."users SET user_name='".$username."', user_salt='".$userSalt."', user_password='".$userPassword."', user_admin_salt='".$adminSalt."', user_admin_password='".$adminPassword."',
				user_email='".$email."' WHERE user_id='1'");
                $_SESSION['step'] = STEP_INTRO;

            } else {

                $_SESSION['step'] = STEP_INFUSIONS;
            }

            new Authenticate(filter_input(INPUT_POST, 'username'), $userPass->inputNewPassword, FALSE);
            redirect(filter_input(INPUT_SERVER, 'REQUEST_URI'));

        } else {
            $content .= "<br />\n".$locale['setup_5021']."<br /><br />\n".$error;
            $content .= "<input type='hidden' name='error_pass' value='".$error_pass."' />\n";
            $content .= "<input type='hidden' name='error_name' value='".$error_name."' />\n";
            $content .= "<input type='hidden' name='error_mail' value='".$error_mail."' />\n";
            $content .= "<input type='hidden' name='username' value='".$username."' />\n";
            $content .= "<input type='hidden' name='email' value='".$email."' />\n";
            $nextStep = STEP_PRIMARY_ADMIN_FORM;
            $buttonMode = 'back';
        }
    }

    private function step_writeHtaccess() {

    }

}