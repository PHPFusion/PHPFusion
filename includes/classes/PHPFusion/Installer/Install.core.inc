<?php
/*
<Files ~ "\.inc$">
Order allow,deny
Deny from all
</Files>
*/
namespace PHPFusion\Installer;

use PHPFusion\Database\DatabaseFactory;
use PHPFusion\Locale;

const STEP_INTRO = 1;
const STEP_PERMISSIONS = 2;
const STEP_DB_SETTINGS_FORM = 3;
const STEP_DB_SETTINGS_SAVE = 4;
const STEP_PRIMARY_ADMIN_FORM = '5';   //must be between quotation marks because of implicit type conversion
const STEP_PRIMARY_ADMIN_SAVE = '5/2';
const STEP_INFUSIONS = 6;
const STEP_SETUP_COMPLETE = 7;
const STEP_EXIT = 8;

class Install_Core {

    protected static $locale = array();
    protected static $localeset = 'English';
    protected static $step = array(
        'next' => FALSE,
        'previous' => FALSE,
    );
    private static $setup_instance = NULL;
    private static $locale_files = array();
    private static $pdo_enabled = FALSE;
    private static $db_prefix = '';

    /*
     * next can be STEP_PERMISSIONS;
     * back can be STEP_INTRODUCTION;
     * @var array
     */
    private static $connection = array(
        'db_host' => 'localhost',
        'db_user' => NULL,
        'db_pass' => NULL,
        'db_name' => NULL,
        'db_prefix' => NULL
    );

    protected function __construct() {
    }

    public static function getInstance() {

        if (self::$setup_instance == NULL) {

            self::$setup_instance = new static();

            self::verify_requirements();

            ini_set('display_errors', 1);

            define('iMEMBER', FALSE);

            define("FUSION_QUERY", isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "");

            define("FUSION_SELF", basename($_SERVER['PHP_SELF']));

            define("FUSION_REQUEST", isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != "" ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME']);


            self::$localeset = filter_input(INPUT_GET, 'localeset') ?: (isset($settings['locale']) ? $settings['locale'] : 'English');
            define('LANGUAGE', is_dir(LOCALE.self::$localeset) ? self::$localeset : 'English');
            define("LOCALESET", LANGUAGE."/");

            self::$pdo_enabled = filter_input(INPUT_POST, 'pdo_enabled', FILTER_VALIDATE_BOOLEAN);

            // ALWAYS reset config to config_temp.php
            if (file_exists(BASEDIR.'config.php')) {
                @rename(BASEDIR.'config.php', BASEDIR.'config_temp.php');
                @chmod(BASEDIR.'config_temp.php', 0755);
            }
            if (file_exists(BASEDIR.'config_temp.php')) {
                include BASEDIR.'config_temp.php';
                if (!empty($db_prefix)) {
                    self::$db_prefix = $db_prefix;
                    if (!empty($db_host) && !empty($db_user) && !empty($db_pass) && !empty($db_name) && isset($pdo_enabled)) {
                        self::$pdo_enabled = $pdo_enabled;
                        self::$connection = [
                            'db_host' => $db_host,
                            'db_user' => $db_user,
                            'db_pass' => $db_pass,
                            'db_name' => $db_name
                        ];
                        DatabaseFactory::setDefaultDriver(intval($pdo_enabled) === 1 ? DatabaseFactory::DRIVER_PDO_MYSQL : DatabaseFactory::DRIVER_MYSQL);
                        require_once INCLUDES."db_handlers/all_functions_include.php";
                        dbconnect(self::$connection['db_host'], self::$connection['db_user'], self::$connection['db_pass'],
                                  self::$connection['db_name'], FALSE);
                    }
                }

            } else {

                if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
                    self::$pdo_enabled = (bool)intval(self::$pdo_enabled);
                    self::$connection = array(
                        'db_host' => (string)(stripinput(trim(filter_input(INPUT_POST, 'db_host'))) ?: self::$connection['db_host']),
                        'db_user' => (string)(stripinput(trim(filter_input(INPUT_POST, 'db_user'))) ?: self::$connection['db_user']),
                        'db_pass' => (string)(stripinput(filter_input(INPUT_POST, 'db_pass')) ?: self::$connection['db_pass']),
                        'db_name' => (string)(stripinput(trim(filter_input(INPUT_POST, 'db_name'))) ?: self::$connection['db_name']),
                        'db_prefix' => (string)(stripinput(trim(filter_input(INPUT_POST, 'db_prefix'))) ?: self::$connection['db_prefix'])
                    );
                }

            }

            if (!defined('SECRET_KEY')) {
                define('SECRET_KEY', self::createRandomPrefix(32));
            }

            if (!defined('SECRET_KEY_SALT')) {
                define('SECRET_KEY_SALT', self::createRandomPrefix(32));
            }

            self::$locale_files = fusion_get_detected_language();

            self::$locale = Locale::getLocale('', LOCALE.LOCALESET."setup.php");

            new \dynamics();

            self::define_step();

            session_start();
        }

        return self::$setup_instance;
    }

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

    private static function createRandomPrefix($length = 5) {
        $chars = array("abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ", "123456789");
        $count = array((strlen($chars[0]) - 1), (strlen($chars[1]) - 1));
        $prefix = "";
        for ($i = 0; $i < $length; $i++) {
            $type = mt_rand(0, 1);
            $prefix .= substr($chars[$type], mt_rand(0, $count[$type]), 1);
        }

        return $prefix;
    }

    /*
     * First Step - Introduction
     */

    private static function define_step() {

        if (!defined('INSTALLATION_STEP') && \defender::safe()) {
            $step = (isset($_SESSION['step']) ? $_SESSION['step'] : STEP_INTRO);
            if (isset($_POST['infuse']) || isset($_POST['defuse'])) {
                $step = STEP_INFUSIONS;
            } elseif (isset($_POST['step'])) {
                $step = $_POST['step'];
            }
            define('INSTALLATION_STEP', $step);
        }
    }

    private static function setConnection($db_host, $db_user, $db_pass, $db_name) {
        self::$connection = [
            'db_host' => $db_host,
            'db_user' => $db_user,
            'db_pass' => $db_pass,
            'db_name' => $db_name
        ];
    }

    public function install_phpfusion() {
        $content = Console_Core::getInstance()->getView($this->get_InstallerContent());
        echo strtr(Console_Core::getInstance()->getLayout(), array("{%content%}" => $content));
    }

    private function get_InstallerContent() {
        $content = "";
        switch (INSTALLATION_STEP) {
            // Introduction
            case STEP_INTRO:
            default:
                return $this->step_introduction();
                break;
            // Step 2 - File and Folder Permissions
            case STEP_PERMISSIONS:
                return $this->step_permissions();
                break;
            // Step 3 - Database Settings
            case STEP_DB_SETTINGS_FORM:
                if (!$db_prefix) {
                    $db_prefix = "fusion".createRandomPrefix()."_";
                }
                $cookie_prefix = "fusion".createRandomPrefix()."_";
                $email = (isset($_POST['email']) ? stripinput(trim($_POST['email'])) : "");
                $username = (isset($_POST['username']) ? stripinput(trim($_POST['username'])) : "");
                $db_error = (isset($_POST['db_error']) && isnum($_POST['db_error']) ? $_POST['db_error'] : "0");
                $field_class = array("", "", "", "", "");
                if ($db_error > "0") {
                    $field_class[2] = " tbl-error";
                    if ($db_error == 1) {
                        $field_class[1] = " tbl-error";
                        $field_class[2] = " tbl-error";
                    } elseif ($db_error == 2) {
                        $field_class[3] = " tbl-error";
                    } elseif ($db_error == 3) {
                        $field_class[4] = " tbl-error";
                    } elseif ($db_error == 7) {
                        if ($db_host == "") {
                            $field_class[0] = " tbl-error";
                        }
                        if ($db_user == "") {
                            $field_class[1] = " tbl-error";
                        }
                        if ($db_name == "") {
                            $field_class[3] = " tbl-error";
                        }
                        if ($db_prefix == "") {
                            $field_class[4] = " tbl-error";
                        }
                    }
                }
                $content .= "<div class='m-b-20'><h4>".$locale['setup_1200']."</h4> ".$locale['setup_1201']."</div>\n";
                $content .= "<table class='table table-responsive'>\n<tr>\n";
                $content .= "<td class='tbl1' style='text-align:left'>".$locale['setup_1202']."</td>\n";
                $content .= "<td class='tbl1'><input type='text' value='".$db_host."' name='db_host' class='form-control input-sm textbox".$field_class[0]."' style='width:200px' /></td>\n</tr>\n";
                $content .= "<tr>\n<td class='tbl1' style='text-align:left'>".$locale['setup_1203']."</td>\n";
                $content .= "<td class='tbl1'><input type='text' value='".$db_user."' name='db_user' class='form-control input-sm textbox".$field_class[1]."' style='width:200px' /></td>\n</tr>\n";
                $content .= "<tr>\n<td class='tbl1' style='text-align:left'>".$locale['setup_1204']."</td>\n";
                $content .= "<td class='tbl1'><input type='password' value='' name='db_pass' class='form-control input-sm textbox".$field_class[2]."' style='width:200px' /></td>\n</tr>\n";
                $content .= "<tr>\n<td class='tbl1'>".$locale['setup_1205']."</td>\n";
                $content .= "<td class='tbl1'><input type='text' value='".$db_name."' name='db_name' class='form-control input-sm textbox".$field_class[3]."' style='width:200px' /></td>\n</tr>\n";
                $content .= "<tr>\n<td class='tbl1' style='text-align:left'>".$locale['setup_1208']."</td>\n";
                // enable PDO
                $content .= "<td class='tbl1'>\n";
                if (!defined('PDO::ATTR_DRIVER_NAME')) {
                    $content .= $locale['setup_1209'];
                } else {
                    $content .= "<select name='pdo_enabled' class='form-control input-sm textbox' style='width:200px'>\n";
                    $content .= "<option value='0'>".$locale['setup_1210']."</option>\n";
                    $content .= "<option value='1' selected='selected'>".$locale['setup_1211']."</option>\n";
                    $content .= "</select>\n";
                }
                $content .= "</td>\n</tr>\n";
                $content .= "<tr>\n<td class='tbl1'>".$locale['setup_1213']."</td>\n";
                $content .= "<td class='tbl1' style='text-align:right'><input type='text' name='username' value='".$username."' maxlength='255' class='form-control input-sm textbox' style='width:200px' /></td></tr>\n";
                $content .= "<tr>\n<td class='tbl1'>".$locale['setup_1509']."</td>\n";
                $content .= "<td class='tbl1' style='text-align:right'><input type='text' name='email' value='".$email."' maxlength='100' class='form-control input-sm textbox' style='width:200px' /></td></tr>\n";
                $content .= "<tr><td class='tbl1'>".$locale['setup_1212']."</td>\n";
                $content .= "<td class='tbl1'>\n";

                for ($i = 0; $i < count($locale_files); $i++) {
                    if (file_exists(BASEDIR.'locale/'.$locale_files[$i].'/setup.php')) {

                        if ($_POST['localeset'] == $locale_files[$i]) {
                            $content .= form_hidden("enabled_languages[]", "", $locale_files[$i]);
                        }

                        $content .= form_checkbox("enabled_languages[]", translate_lang_names($locale_files[$i]), $_POST['localeset'],
                                                  array(
                                                      "input_id" => "locale_".$i,
                                                      "reverse_label" => TRUE,
                                                      "class" => "m-b-0",
                                                      "value" => $locale_files[$i],
                                                      "deactivate" => ($_POST['localeset'] == $locale_files[$i] ? TRUE : FALSE)
                                                  )
                        );
                    }
                }
                $content .= "</td></tr>\n";
                $content .= "<tr>\n<td class='tbl1' style='text-align:left'>".$locale['setup_1206']."</td>\n";
                $content .= "<td class='tbl1'><input type='text' value='".$db_prefix."' name='db_prefix' class='form-control input-sm textbox".$field_class[4]."' style='width:200px' /></td>\n</tr>\n";
                $content .= "<tr>\n<td class='tbl1' style='text-align:left'>".$locale['setup_1207']."</td>\n";
                $content .= "<td class='tbl1'><input type='text' value='".$cookie_prefix."' name='cookie_prefix' class='form-control input-sm textbox' style='width:200px' /></td>\n</tr>\n";
                $content .= "</table>\n";
                $nextStep = STEP_DB_SETTINGS_SAVE;
                $buttonMode = 'next';
                break;
            // Step 4 - Config / Database Setup
            case STEP_DB_SETTINGS_SAVE:
                // Generate All Core Tables - this includes settings and all its injections
                $db_host = (isset($_POST['db_host']) ? stripinput(trim($_POST['db_host'])) : "");
                $db_user = (isset($_POST['db_user']) ? stripinput(trim($_POST['db_user'])) : "");
                $db_pass = (isset($_POST['db_pass']) ? stripinput(trim($_POST['db_pass'])) : "");
                $db_name = (isset($_POST['db_name']) ? stripinput(trim($_POST['db_name'])) : "");
                $db_prefix = (isset($_POST['db_prefix']) ? stripinput(trim($_POST['db_prefix'])) : "fusion_");
                $cookie_prefix = (isset($_POST['cookie_prefix']) ? stripinput(trim($_POST['cookie_prefix'])) : "fusion_");
                $email = (isset($_POST['email']) ? stripinput(trim($_POST['email'])) : "");
                $username = (isset($_POST['username']) ? stripinput(trim($_POST['username'])) : "");
                $enabled_languages = '';
                if (!empty($_POST['enabled_languages'])) {
                    for ($i = 0; $i < count($_POST['enabled_languages']); $i++) {
                        $enabled_languages .= $_POST['enabled_languages'][$i].".";
                    }
                    $enabled_languages = substr($enabled_languages, 0, (strlen($enabled_languages) - 1));
                } else {
                    $enabled_languages = stripinput($_POST['localeset']);
                }
                if ($db_prefix != "") {
                    $db_prefix_last = $db_prefix[strlen($db_prefix) - 1];
                    if ($db_prefix_last != "_") {
                        $db_prefix = $db_prefix."_";
                    }
                }
                if ($cookie_prefix != "") {
                    $cookie_prefix_last = $cookie_prefix[strlen($cookie_prefix) - 1];
                    if ($cookie_prefix_last != "_") {
                        $cookie_prefix = $cookie_prefix."_";
                    }
                }
                $selected_langs = '';
                $secret_key = createRandomPrefix(32);
                $secret_key_salt = createRandomPrefix(32);
                if ($db_host != "" && $db_user != "" && $db_name != "" && $db_prefix != "") {
                    $connection_info = dbconnect($db_host, $db_user, $db_pass, $db_name, FALSE);
                    $db_connect = $connection_info['connection_success'];
                    $db_select = $connection_info['dbselection_success'];
                    if ($db_connect) {
                        if ($db_select) {
                            $countRows = dbrows(dbquery("SHOW TABLES LIKE '".str_replace("_", "\_", $db_prefix)."%'"));
                            if (!$countRows) {
                                $table_name = uniqid($db_prefix, FALSE);
                                $can_write = TRUE;
                                $result = dbquery("CREATE TABLE ".$table_name." (test_field VARCHAR(10) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
                                if (!$result) {
                                    $can_write = FALSE;
                                }
                                $result = dbquery("DROP TABLE ".$table_name);
                                if (!$result) {
                                    $can_write = FALSE;
                                }
                                if ($can_write) {
                                    // Write a Temporary Config File.
                                    $config = "<?php\n";
                                    $config .= "// database settings\n";
                                    $config .= "\$db_host = '".$db_host."';\n";
                                    $config .= "\$db_user = '".$db_user."';\n";
                                    $config .= "\$db_pass = '".$db_pass."';\n";
                                    $config .= "\$db_name = '".$db_name."';\n";
                                    $config .= "\$db_prefix = '".$db_prefix."';\n";
                                    $config .= "\$pdo_enabled = ".intval($pdo_enabled).";\n";
                                    $config .= "define(\"DB_PREFIX\", \"".$db_prefix."\");\n";
                                    $config .= "define(\"COOKIE_PREFIX\", \"".$cookie_prefix."\");\n";
                                    $config .= "define(\"SECRET_KEY\", \"".$secret_key."\");\n";
                                    $config .= "define(\"SECRET_KEY_SALT\", \"".$secret_key_salt."\");\n";
                                    $config .= "?>";
                                    if (fusion_file_put_contents(BASEDIR.'config_temp.php', $config)) {
                                        /*
                                         * We need to include them to create DB_SETTINGS
                                         * for fusion_get_settings() in write_htaccess()
                                         *
                                         * TODO: Find better way
                                         */
                                        require BASEDIR.'config_temp.php';
                                        require_once INCLUDES.'multisite_include.php';
                                        $fail = FALSE;
                                        if (!$result) {
                                            $fail = TRUE;
                                        }
                                        // install core tables fully injected.
                                        include 'includes/core_setup.php';
                                        $content = '';
                                        if (!$fail) {
                                            $content .= "<i class='entypo check'></i> ".$locale['setup_1300']."<br /><br />\n<i class='entypo check'></i> ";
                                            $content .= $locale['setup_1301']."<br /><br />\n<i class='entypo check'></i> ";
                                            $content .= $locale['setup_1302']."<br /><br />\n";
                                            $success = TRUE;
                                            $db_error = 6;
                                        } else {
                                            $content .= "<br />\n<i class='entypo check'></i> ".$locale['setup_1300']."<br /><br />\n<i class='entypo check'></i> ";
                                            $content .= $locale['setup_1301']."<br /><br />\n<i class='entypo icancel'></i> ";
                                            $content .= "<strong>".$locale['setup_1303']."</strong> ".$locale['setup_1308']."<br /><br />\n";
                                            $success = FALSE;
                                            $db_error = 0;
                                        }
                                    } else {
                                        $content .= "<br />\n".$locale['setup_1300']."<br /><br />\n";
                                        $content .= "<strong>".$locale['setup_1303']."</strong> ".$locale['setup_1306']."<br />\n";
                                        $content .= "<span class='small'>".$locale['setup_1307']."</span><br /><br />\n";
                                        $success = FALSE;
                                        $db_error = 5;
                                    }
                                    write_htaccess(fusion_get_settings('site_path'));
                                } else {
                                    $content .= "<div class='alert alert-danger'>\n";
                                    $content .= $locale['setup_1300']."<br /><br />\n";
                                    $content .= "<strong>".$locale['setup_1303']."</strong> ".$locale['setup_1314']."<br />\n";
                                    $content .= "<span class='small'>".$locale['setup_1315']."</span><br /><br />\n";
                                    $content .= "</div>\n";
                                    $success = FALSE;
                                    $db_error = 4;
                                }
                            } else {

                                // has tables -- should direct to next step instead.
                                $content .= "<div class='alert alert-warning'>\n";
                                $content .= "<strong>".$locale['setup_1312']."</strong><br />\n";
                                $content .= "<span class='small'>".$locale['setup_1313']."</span><br /><br />\n";
                                $content .= "</div>\n";
                                $success = TRUE;
                                $db_error = 0;
                            }
                        } else {
                            $content .= "<div class='alert alert-danger'>\n";
                            $content .= "<br />\n<strong>".$locale['setup_1303']."<strong> ".$locale['setup_1310']."<br />\n";
                            $content .= "<span class='small'>".$locale['setup_1311']."</span><br /><br />\n";
                            $content .= "</div>\n";
                            $success = FALSE;
                            $db_error = 2;
                        }
                    } else {
                        $content .= "<div class='alert alert-danger'>\n";
                        $content .= "<strong>".$locale['setup_1303']."<strong> ".$locale['setup_1304']."<br />\n";
                        $content .= "<span class='small'>".$locale['setup_1305']."</span><br /><br />\n";
                        $content .= "</div>\n";
                        $success = FALSE;
                        $db_error = 1;
                    }
                } else {
                    $content .= "<div class='alert alert-danger'>\n";
                    $content .= "<strong>".$locale['setup_1303']."<strong> ".$locale['setup_1316']."<br />\n";
                    $content .= "".$locale['setup_1317']."<br /><br />\n";
                    $content .= "</div>\n";
                    $success = FALSE;
                    $db_error = 7;
                }
                $content .= "<input type='hidden' name='enabled_languages' value='".$selected_langs."' />\n";
                if ($success) {
                    $nextStep = STEP_PRIMARY_ADMIN_FORM;
                    $buttonMode = 'next';
                } else {
                    $content .= "<input type='hidden' name='db_host' value='".$db_host."' />\n";
                    $content .= "<input type='hidden' name='db_user' value='".$db_user."' />\n";
                    $content .= "<input type='hidden' name='db_name' value='".$db_name."' />\n";
                    $content .= "<input type='hidden' name='db_prefix' value='".$db_prefix."' />\n";
                    $content .= "<input type='hidden' name='db_error' value='".$db_error."' />\n";
                    $nextStep = STEP_DB_SETTINGS_FORM;
                    $buttonMode = 'tryagain';
                }
                break;

            // Step 5 - Primary Admin Details
            case STEP_PRIMARY_ADMIN_FORM:
                $error_pass = (isset($_POST['error_pass']) && isnum($_POST['error_pass']) ? $_POST['error_pass'] : "0");
                $error_name = (isset($_POST['error_name']) && isnum($_POST['error_name']) ? $_POST['error_name'] : "0");
                $error_mail = (isset($_POST['error_mail']) && isnum($_POST['error_mail']) ? $_POST['error_mail'] : "0");
                $field_class = array("", "", "", "", "", "");
                if ($error_pass == "1" || $error_name == "1" || $error_mail == "1") {
                    $field_class = array("", " tbl-error", " tbl-error", " tbl-error", " tbl-error", "");
                    if ($error_name == 1) {
                        $field_class[0] = " tbl-error";
                    }
                    if ($error_mail == 1) {
                        $field_class[5] = " tbl-error";
                    }
                }
                // to scan whether User Acccount exists.
                if (!file_exists(BASEDIR.'config.php') && !file_exists(BASEDIR.'config_temp.php')) {
                    redirect(FUSION_SELF);
                }
                /*
                 * We need to include it to create DB_SETTINGS
                 * for fusion_get_settings()
                 *
                 * TODO: Find better way
                 */
                require_once INCLUDES.'multisite_include.php';
                dbconnect($db_host, $db_user, $db_pass, $db_name, FALSE);

                $owner = dbarray(dbquery("SELECT user_name, user_email FROM ".$db_prefix."users WHERE user_id = 1")) ?: array();
                if ($owner) {
                    $content .= "<div class='m-b-20'><h4>".$locale['setup_1502']."</h4> ".$locale['setup_1503']."</div>\n";
                    $content .= "<input type='hidden' name='transfer' value='1'>\n";
                    // load authentication during post.
                    // in development.
                } else {
                    $content .= "<div class='m-b-20'><h4>".$locale['setup_1500']."</h4> ".$locale['setup_1501']."</div>\n";
                }
                $owner += array(
                    'user_name' => '',
                    'user_email' => ''
                );
                $username = (isset($_POST['username']) ? stripinput(trim($_POST['username'])) : $owner['user_name']);
                $email = (isset($_POST['email']) ? stripinput(trim($_POST['email'])) : $owner['user_email']);

                $content .= "<table class='table table-responsive'>\n<tr>\n";
                $content .= "<td class='tbl1'>".$locale['setup_1504']."</td>\n";
                $content .= "<td class='tbl1' style='text-align:right'><input type='text' name='username' value='".$username."' maxlength='30' class='form-control input-sm textbox".$field_class[0]."' style='width:200px' /></td></tr>\n";
                $content .= "<tr>\n<td class='tbl1'>".$locale['setup_1509']."</td>\n";
                $content .= "<td class='tbl1' style='text-align:right'><input type='text' name='email' value='".$email."' maxlength='100' class='form-control input-sm textbox' style='width:200px' /></td></tr>\n";
                $content .= "<tr>\n<td class='tbl1'>".$locale['setup_1505']."</td>\n";
                $content .= "<td class='tbl1' style='text-align:right'><input type='password' name='password1' maxlength='64' class='form-control input-sm textbox".$field_class[1]."' style='width:200px' /></td></tr>\n";
                $content .= "<tr>\n<td class='tbl1'>".$locale['setup_1506']."</td>\n";
                $content .= "<td class='tbl1' style='text-align:right'><input type='password' name='password2' maxlength='64' class='form-control input-sm textbox".$field_class[2]."' style='width:200px' /></td></tr>\n";
                $content .= "<tr>\n<td class='tbl1'>".$locale['setup_1507']."</td>\n";
                $content .= "<td class='tbl1' style='text-align:right'><input type='password' name='admin_password1' maxlength='64' class='form-control input-sm textbox".$field_class[3]."' style='width:200px' /></td></tr>\n";
                $content .= "<tr>\n<td class='tbl1'>".$locale['setup_1508']."</td>\n";
                $content .= "<td class='tbl1' style='text-align:right'><input type='password' name='admin_password2' maxlength='64' class='form-control input-sm textbox".$field_class[4]."' style='width:200px' /></td></tr>\n";
                $content .= "</table>\n";
                $content .= "<input type='hidden' name='enabled_languages' value='".fusion_get_settings('enabled_languages')."' />\n";
                $nextStep = STEP_PRIMARY_ADMIN_SAVE;
                $buttonMode = 'next';
                break;
            // Step 5/2 - Primary Admin Details
            case STEP_PRIMARY_ADMIN_SAVE:
                if (!file_exists(BASEDIR.'config_temp.php')) {
                    redirect(FUSION_SELF);
                }
                dbconnect($db_host, $db_user, $db_pass, $db_name, FALSE);
                $error = "";
                $error_pass = "0";
                $error_name = "0";
                $error_mail = "0";
                $password_algorithm = "sha256";
                $username = (isset($_POST['username']) ? stripinput(trim($_POST['username'])) : "");
                if ($username == "") {
                    $error .= $locale['setup_5011']."<br /><br />\n";
                    $error_name = "1";
                } elseif (!preg_match("/^[-0-9A-Z_@\s]+$/i", $username)) {
                    $error .= $locale['setup_5010']."<br /><br />\n";
                    $error_name = "1";
                }
                $userPassword = "";
                $adminPassword = "";
                $userPass = new \PHPFusion\PasswordAuth($password_algorithm);
                $userPass->inputNewPassword = (isset($_POST['password1']) ? stripinput(trim($_POST['password1'])) : "");
                $userPass->inputNewPassword2 = (isset($_POST['password2']) ? stripinput(trim($_POST['password2'])) : "");
                $returnValue = $userPass->isValidNewPassword();
                if ($returnValue == 0) {
                    $userPassword = $userPass->getNewHash();
                    $userSalt = $userPass->getNewSalt();
                } elseif ($returnValue == 2) {
                    $error .= $locale['setup_5012']."<br /><br />\n";
                    $error_pass = "1";
                } elseif ($returnValue == 3) {
                    $error .= $locale['setup_5013']."<br /><br />\n";
                }

                $adminPass = new \PHPFusion\PasswordAuth($password_algorithm);
                $adminPass->inputNewPassword = (isset($_POST['admin_password1']) ? stripinput(trim($_POST['admin_password1'])) : "");
                $adminPass->inputNewPassword2 = (isset($_POST['admin_password2']) ? stripinput(trim($_POST['admin_password2'])) : "");
                $returnValue = $adminPass->isValidNewPassword();

                if ($returnValue == 0) {
                    $adminPassword = $adminPass->getNewHash();
                    $adminSalt = $adminPass->getNewSalt();
                } elseif ($returnValue == 2) {
                    $error .= $locale['setup_5015']."<br /><br />\n";
                    $error_pass = "1";
                } elseif ($returnValue == 3) {
                    $error .= $locale['setup_5017']."<br /><br />\n";
                }
                if ($userPass->inputNewPassword == $adminPass->inputNewPassword) {
                    $error .= $locale['setup_5016']."<br /><br />\n";
                    $error_pass = "1";
                }
                $email = (isset($_POST['email']) ? stripinput(trim($_POST['email'])) : "");
                if ($email == "") {
                    $error .= $locale['setup_5020']."<br /><br />\n";
                    $error_mail = "1";
                } elseif (!preg_match("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $email)) {
                    $error .= $locale['setup_5019']."<br /><br />\n";
                    $error_mail = "1";
                }
                $rows = dbrows(dbquery("SELECT user_id FROM ".$db_prefix."users WHERE user_id = 1"));
                if ($error == "") {
                    if ($rows) {
                        // Create Super Admin with Full Modular Rights - We don't need to update Super Admin later.
                        dbquery("UPDATE ".$db_prefix."users SET user_name='".$username."', user_salt='".$userSalt."', user_password='".$userPassword."', user_admin_salt='".$adminSalt."', user_admin_password='".$adminPassword."',
				user_email='".$email."' WHERE user_id='1'");
                        $_SESSION['step'] = STEP_INTRO;
                    } else {
                        dbquery("INSERT INTO ".$db_prefix."users (
					user_name, user_algo, user_salt, user_password, user_admin_algo, user_admin_salt, user_admin_password, user_email, user_hide_email, user_timezone,
					user_avatar, user_posts, user_threads, user_joined, user_lastvisit, user_ip, user_rights,
					user_groups, user_level, user_status, user_theme, user_location, user_birthdate, user_aim,
					user_icq, user_yahoo, user_web, user_sig
					) VALUES (
					'".$username."', 'sha256', '".$userSalt."', '".$userPassword."', 'sha256', '".$adminSalt."', '".$adminPassword."',
					'".$email."', '1', 'Europe/London', '',  '0', '0', '".time()."', '0', '0.0.0.0',
					'A.AD.APWR.B.BB.BLOG.C.CP.DB.ERRO.FQ.F.FR.IM.I.IP.M.MI.MAIL.P.PH.PI.PL.PO.ROB.SL.S1.S2.S3.S4.S5.S6.S7.S8.S9.S10.S11.S12.S13.SB.SM.SU.UF.UFC.UG.UL.U.TS.W.LANG.ESHP',
					'', '-103', '0', 'Default', '', '1900-01-01', '', '',  '', '', ''
					)");
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
                break;
            // Step 6 - Configure Core System - $settings accessible - Requires Config_temp.php (Shut down site when upgrading).
            /**
             * This is where the errors is showing.
             */
            case STEP_INFUSIONS:
                include LOCALE.LANGUAGE."/admin/infusions.php";
                // Load Config and SQL handler.
                if (file_exists(BASEDIR.'config_temp.php')) {
                    require_once INCLUDES.'multisite_include.php';
                    dbconnect($db_host, $db_user, $db_pass, $db_name, FALSE);
                    if (!fusion_get_settings()) {
                        $_SESSION['step'] = STEP_INTRO;
                        redirect(FUSION_SELF);
                    }
                } else {
                    $_SESSION['step'] = STEP_INTRO;
                    redirect(FUSION_SELF); // start all over again if you tampered config_temp here.
                }
                $fail = FALSE;
                $message = "";

                if (($folder = filter_input(INPUT_POST, 'infuse'))) {
                    $error = "";
                    if (($inf = fusion_load_infusion($folder))) {
                        $result = dbquery("SELECT inf_id, inf_version FROM ".DB_INFUSIONS." WHERE inf_folder=:folder", array(':folder' => $folder));
                        if (dbrows($result)) {
                            $data = dbarray($result);
                            if ($inf['version'] > $data['inf_version']) {
                                if ($inf['altertable'] && is_array($inf['altertable'])) {
                                    foreach ($inf['altertable'] as $alter) {
                                        $result = dbquery("ALTER TABLE ".$alter);
                                    }
                                }
                                dbquery("UPDATE ".DB_INFUSIONS." SET inf_version=:
                        WHERE inf_id=:id", array(
                                    ':version' => $inf['version'],
                                    ':id' => $inf['id'],
                                ));
                            }
                        } else {
                            if ($inf['adminpanel'] && is_array($inf['adminpanel'])) {
                                $error = 0;
                                foreach ($inf['adminpanel'] as $adminpanel) {
                                    // auto recovery
                                    if (!empty($adminpanel['rights'])) {
                                        dbquery("DELETE FROM ".DB_ADMIN." WHERE admin_rights='".$adminpanel['rights']."'");
                                    }

                                    $inf_admin_image = ($adminpanel['image'] ?: "infusion_panel.png");

                                    if (empty($adminpanel['page'])) {
                                        $item_page = 5;
                                    } else {
                                        $item_page = isnum($adminpanel['page']) ? $adminpanel['page'] : 5;
                                    }

                                    if (!dbcount("(admin_id)", DB_ADMIN, "admin_rights='".$adminpanel['rights']."'")) {
                                        $adminpanel += array(
                                            "rights" => "",
                                            "title" => "",
                                            "panel" => "",
                                        );
                                        dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('".$adminpanel['rights']."', '".$inf_admin_image."', '".$adminpanel['title']."', '".INFUSIONS.$inf['folder']."/".$adminpanel['panel']."', '".$item_page."')");
                                        $result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level=".USER_LEVEL_SUPER_ADMIN);
                                        while ($data = dbarray($result)) {
                                            dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".".$adminpanel['rights']."' WHERE user_id='".$data['user_id']."'");
                                        }
                                    } else {
                                        $error = 1;
                                    }
                                }
                            }
                            if (!$error) {
                                if ($inf['sitelink'] && is_array($inf['sitelink'])) {
                                    $last_id = 0;
                                    foreach ($inf['sitelink'] as $sitelink) {
                                        $link_order = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS), 0) + 1;
                                        $sitelink += array(
                                            "title" => "",
                                            "cat" => 0,
                                            "url" => "",
                                            "icon" => "",
                                            "visibility" => 0,
                                            "position" => 3,
                                        );
                                        if (!empty($sitelink['cat']) && $sitelink['cat'] == "{last_id}" && !empty($last_id)) {
                                            $sitelink['cat'] = $last_id;
                                            dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_cat, link_url, link_icon, link_visibility, link_position, link_window,link_language, link_order) VALUES ('".$sitelink['title']."', '".$sitelink['cat']."', '".str_replace("../",
                                                                                                                                                                                                                                                                              "",
                                                                                                                                                                                                                                                                              INFUSIONS).$inf_folder."/".$sitelink['url']."', '".$sitelink['icon']."', '".$sitelink['visibility']."', '".$sitelink['position']."', '0', '".LANGUAGE."', '".$link_order."')");
                                        } else {
                                            dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_cat, link_url, link_icon, link_visibility, link_position, link_window,link_language, link_order) VALUES ('".$sitelink['title']."', '".$sitelink['cat']."', '".str_replace("../",
                                                                                                                                                                                                                                                                              "",
                                                                                                                                                                                                                                                                              INFUSIONS).$inf_folder."/".$sitelink['url']."', '".$sitelink['icon']."', '".$sitelink['visibility']."', '".$sitelink['position']."', '0', '".LANGUAGE."', '".$link_order."')");
                                            $last_id = dblastid();
                                        }
                                    }
                                }
                                //Multilang rights
                                if ($inf['mlt'] && is_array($inf['mlt'])) {
                                    foreach ($inf['mlt'] as $mlt) {
                                        dbquery("INSERT INTO ".DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('".$mlt['rights']."', '".$mlt['title']."', '1')");
                                    }
                                }
                                if ($inf['newtable'] && is_array($inf['newtable'])) {
                                    foreach ($inf['newtable'] as $newtable) {
                                        dbquery("CREATE TABLE ".$newtable);
                                    }
                                }
                                if (isset($inf['newcol']) && is_array($inf['newcol'])) {
                                    foreach ($inf['newcol'] as $newCol) {
                                        if (is_array($newCol) && !empty($newCol['table']) && !empty($newCol['column']) && !empty($newCol['column_type'])) {
                                            $columns = fieldgenerator($newCol['table']);
                                            $count = count($columns);
                                            if (!in_array($newCol['column'], $columns)) {
                                                dbquery("ALTER TABLE ".$newCol['table']." ADD ".$newCol['column']." ".$newCol['column_type']." AFTER ".$columns[$count - 1]);
                                            }
                                        }
                                    }
                                }
                                if ($inf['insertdbrow'] && is_array($inf['insertdbrow'])) {
                                    $last_id = 0;
                                    foreach ($inf['insertdbrow'] as $insertdbrow) {
                                        if (stristr($insertdbrow, "{last_id}") && !empty($last_id)) {
                                            dbquery("INSERT INTO ".str_replace("{last_id}", $last_id, $insertdbrow));
                                        } else {
                                            dbquery("INSERT INTO ".$insertdbrow);
                                            $last_id = dblastid();
                                        }
                                    }
                                }

                                if ($inf['mlt_insertdbrow'] && is_array($inf['mlt_insertdbrow'])) {
                                    foreach (fusion_get_enabled_languages() as $current_language => $language_translations) {
                                        if (isset($mlt_insertdbrow[$current_language])) {
                                            $last_id = 0;
                                            foreach ($mlt_insertdbrow[$current_language] as $insertdbrow) {
                                                if (stristr($insertdbrow, "{last_id}") && !empty($last_id)) {
                                                    dbquery("INSERT INTO ".str_replace("{last_id}", $last_id, $insertdbrow));
                                                } else {
                                                    dbquery("INSERT INTO ".$insertdbrow);
                                                    $last_id = dblastid();
                                                }
                                            }
                                        }
                                    }
                                }
                                dbquery("INSERT INTO ".DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$inf['title']."', '".$inf['folder']."', '".$inf['version']."')");
                            }
                        }
                    }
                    //redirect(FUSION_SELF);
                }

                if (($folder = filter_input(INPUT_POST, 'defuse'))) {
                    $result = dbquery("SELECT inf_folder FROM ".DB_INFUSIONS." WHERE inf_folder=:folder", array(':folder' => $folder));
                    $data = dbarray($result);
                    $inf = fusion_load_infusion($folder);
                    if ($inf['adminpanel'] && is_array($inf['adminpanel'])) {
                        foreach ($inf['adminpanel'] as $adminpanel) {
                            dbquery("DELETE FROM ".DB_ADMIN." WHERE admin_rights='".($adminpanel['rights'] ?: "IP")."' AND admin_link='".INFUSIONS.$inf['folder']."/".$adminpanel['panel']."' AND admin_page='5'");
                            $result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level<=".USER_LEVEL_ADMIN);
                            while ($data = dbarray($result)) {
                                $user_rights = explode(".", $data['user_rights']);
                                if (in_array($adminpanel['rights'], $user_rights)) {
                                    $key = array_search($adminpanel['rights'], $user_rights);
                                    unset($user_rights[$key]);
                                }
                                dbquery("UPDATE ".DB_USERS." SET user_rights='".implode(".", $user_rights)."' WHERE user_id='".$data['user_id']."'");
                            }
                        }
                    }
                    if ($inf['mlt'] && is_array($inf['mlt'])) {
                        foreach ($inf['mlt'] as $mlt) {
                            dbquery("DELETE FROM ".DB_LANGUAGE_TABLES." WHERE mlt_rights='".$mlt['rights']."'");
                        }
                    }
                    if ($inf['sitelink'] && is_array($inf['sitelink'])) {
                        foreach ($inf['sitelink'] as $sitelink) {
                            $result2 = dbquery("SELECT link_id, link_order FROM ".DB_SITE_LINKS." WHERE link_url='".str_replace("../", "",
                                                                                                                                INFUSIONS).$inf['folder']."/".$sitelink['url']."'");
                            if (dbrows($result2)) {
                                $data2 = dbarray($result2);
                                dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 WHERE link_order>'".$data2['link_order']."'");
                                dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_id='".$data2['link_id']."'");
                            }
                        }
                    }

                    if (isset($inf['deldbrow']) && is_array($inf['deldbrow'])) {
                        foreach ($inf['deldbrow'] as $deldbrow) {
                            dbquery("DELETE FROM ".$deldbrow);
                        }
                    }

                    if ($inf['mlt_deldbrow'] && is_array($inf['mlt_deldbrow'])) {
                        foreach (fusion_get_enabled_languages() as $current_language) {
                            if (isset($inf['mlt_deldbrow'][$current_language])) {
                                foreach ($inf['mlt_deldbrow'][$current_language] as $mlt_deldbrow) {
                                    dbquery("DELETE FROM ".$mlt_deldbrow);
                                }
                            }
                        }
                    }

                    if (!empty($inf['delfiles']) && is_array($inf['delfiles'])) {
                        foreach ($inf['delfiles'] as $folder) {
                            $files = makefilelist($folder, ".|..|index.php", TRUE);
                            if (!empty($files)) {
                                foreach ($files as $filename) {
                                    unlink($folder.$filename);
                                }
                            }
                        }
                    }

                    if (isset($inf['dropcol']) && is_array($inf['dropcol'])) {
                        foreach ($inf['dropcol'] as $dropCol) {
                            if (is_array($dropCol) && !empty($dropCol['table']) && !empty($dropCol['column'])) {
                                $columns = fieldgenerator($dropCol['table']);
                                if (in_array($dropCol['column'], $columns)) {
                                    dbquery("ALTER TABLE ".$dropCol['table']." DROP COLUMN ".$dropCol['column']);
                                }
                            }
                        }
                    }

                    if ($inf['droptable'] && is_array($inf['droptable'])) {
                        foreach ($inf['droptable'] as $droptable) {
                            dbquery("DROP TABLE IF EXISTS ".$droptable);
                        }
                    }
                    dbquery("DELETE FROM ".DB_INFUSIONS." WHERE inf_folder=:folder", array(
                        ':folder' => $folder
                    ));
                    //redirect(FUSION_SELF);
                }

                add_to_jquery("
        $('.defuse').bind('click', function() {return confirm('".$locale['412']."');});
        ");

                $temp = opendir(INFUSIONS);
                $infs = array();
                while ($folder = readdir($temp)) {
                    if (!in_array($folder, array("..", ".")) && ($inf = fusion_load_infusion($folder))) {
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
                $nextStep = STEP_SETUP_COMPLETE;
                $buttonMode = 'next';
                break;
            // Step 7 - Final Settings
            case STEP_SETUP_COMPLETE:
                $content .= "<div class='m-b-20'><h4>".$locale['setup_1600']."</h4> ".$locale['setup_1601']."</div>\n";
                $content .= "<div class='m-b-10'>".$locale['setup_1602']."</div>\n";
                $content .= "<div class='m-b-10'>".$locale['setup_1603']."</div>\n";
                $nextStep = STEP_EXIT;
                $buttonMode = 'finish';
                break;
            case STEP_EXIT:
                if (file_exists(BASEDIR.'config_temp.php')) {
                    @rename(BASEDIR.'config_temp.php', BASEDIR.'config.php');
                    @chmod(BASEDIR.'config.php', 0644);
                }
                unset($_SESSION['step']);
                redirect(BASEDIR.'index.php');
                break;
        }
    }

    // fetch the step content and executions

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
        if (!empty(self::$db_prefix)) {

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

            $content .= "<h4 class='strong'>".self::$locale['setup_1002']."</h4>\n";
            $content .= "<span class='display-block m-t-20 m-b-10'>".self::$locale['setup_1003']."</span>\n";
            $content .= "<div class='well'>\n";
            $content .= "<span class='strong display-inline-block m-b-10'>".self::$locale['setup_1017']."</span><br/><p>".self::$locale['setup_1018']."</p>";
            $content .= form_button('step', self::$locale['setup_1019'], STEP_EXIT, array('class' => 'btn-success btn-sm m-t-10'));
            $content .= "</div>\n";
            $content .= "<div class='well'>\n";
            $content .= "<span class='strong display-inline-block m-b-10'>".self::$locale['setup_1004']."</span><br/><p>".self::$locale['setup_1005']." <span class='strong'>".self::$locale['setup_1006']."</span></p>";
            $content .= form_button('uninstall', self::$locale['setup_1007'], 'uninstall', array('class' => 'btn-danger btn-sm m-t-10'));
            $content .= "</div>\n";
            $content .= "<div class='well'>\n";
            $content .= "<span class='strong display-inline-block m-b-10'>".self::$locale['setup_1008']."</span>\n<br/><p>".self::$locale['setup_1009']."</p>";
            $content .= form_button('step', self::$locale['setup_1010'], STEP_INFUSIONS, array('class' => 'btn-primary btn-sm m-r-10'));
            $content .= "</div>\n";
            $content .= "<div class='well'>\n";
            $content .= "<span class='strong display-inline-block m-b-10'>".self::$locale['setup_1011']."</span>\n<br/><p>".self::$locale['setup_1012']."</p>";
            $content .= form_button('step', self::$locale['setup_1013'], STEP_PRIMARY_ADMIN_FORM, array('class' => 'btn-primary btn-sm m-r-10'));
            $content .= "</div>\n";
            $content .= "<input type='hidden' name='localeset' value='".stripinput(LANGUAGE)."' />\n";
            if (isset($db_prefix)) {
                $content .= "<div class='well'>\n";
                $content .= "<span class='strong display-inline-block m-b-10'>".self::$locale['setup_1014']."</span>\n<br/><p>".self::$locale['setup_1015']."</p>";
                $content .= form_button('htaccess', self::$locale['setup_1016'], 'htaccess', array('class' => 'btn-primary btn-sm m-r-10'));
                $content .= "</div>\n";
            }

        } elseif (!isset($_POST['uninstall'])) {

            if (isset($_GET['error']) && $_GET['error'] == 'license') {
                $content .= "<div class='alert alert-danger'>".self::$locale['setup_5000']."</div>\n";
            }
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
                'value' => STEP_PERMISSIONS
            )
        );

        return $content;
    }

    private function step_permissions() {
        $content = '';
        include __DIR__.'/Requirements.core.inc';
        // Create a blank config temp by now if not exist.
        if (!file_exists(BASEDIR."config_temp.php")) {
            if (file_exists(BASEDIR."_config.php") && function_exists("rename")) {
                @rename(BASEDIR."_config.php", BASEDIR."config_temp.php");
            } else {
                touch(BASEDIR."config_temp.php");
            }
        }
        $content .= "<div class='m-b-20'><h4>".self::$locale['setup_1106']."</h4></div>\n";
        $content .= "<table class='table table-responsive table-hover'>\n";
        // Check Environmental requirements

        $system_health = 10;
        foreach (get_system_requirements() as $test) {
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
                $content .= "<div class='m-t-20'>\n";
                $content .= "<table class='table'><tr><th>Files</th><th>Status</th></tr>";
                foreach ($test['sub'] as $key => $value) {
                    $content .= "<tr><td>$key</td><td>$value</td></tr>\n";
                }
                $content .= "</table>";
                $content .= "</div>";
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
                    'value' => STEP_DB_SETTINGS_FORM
                )
            );
        } else {
            self::$step = array(
                1 => array(
                    'name' => 'step',
                    'type' => 'tryagain',
                    'label' => self::$locale['setup_0122'],
                    'value' => STEP_PERMISSIONS
                )
            );
            $content .= form_hidden('license', '', '1');
        }

        return $content;
    }

    // Generate a random string

    /**
     * Render button with custom name and label
     *
     * @param string $name
     * @param string $label
     */
    private function renderButton($name, $label, $mode = 'next') {
        $icon = 'right-dir';
        $btnType = 'btn-primary';
        if ($mode === 'refresh') {
            $icon = 'cw';
        } elseif ($mode === 'tryagain') {
            $btnType = 'btn-warning';
            $icon = 'cw';
        }
        echo "<div class='text-right'>\n";
        echo "<button type='submit' name='".$name."' value='$label' class='btn $btnType m-t-20'><i class='entypo $icon'></i> $label</button>\n";
        echo "</div>\n";
    }

    // Get Current URL

    private function getCurrentURL() {
        $s = empty($_SERVER["HTTPS"]) ? "" : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
        $port = ($_SERVER["SERVER_PORT"] == "80" || ($_SERVER['SERVER_PORT'] == "443" && $s == "s")) ? "" : (":".$_SERVER["SERVER_PORT"]);

        return $protocol."://".$_SERVER['SERVER_NAME'].$port.(str_replace(basename(cleanurl($_SERVER['PHP_SELF'])), "", $_SERVER['REQUEST_URI']));
    }

    private function strleft($s1, $s2) {
        return substr($s1, 0, strpos($s1, $s2));
    }

    // Generate a standard .htaccess file
    private function write_htaccess($site_path) {
        if (!file_exists(BASEDIR.'.htaccess')) {
            if (file_exists(BASEDIR."_htaccess") && function_exists("rename")) {
                @rename(BASEDIR."_htaccess", ".htaccess");
            } else {
                touch(BASEDIR.".htaccess");
            }
        }
        $htc = "# Force utf-8 charset".PHP_EOL;
        $htc .= "AddDefaultCharset utf-8".PHP_EOL.PHP_EOL;
        $htc .= "# Security".PHP_EOL;
        $htc .= "ServerSignature Off".PHP_EOL.PHP_EOL;
        $htc .= "# Secure htaccess file".PHP_EOL;
        $htc .= "<Files .htaccess>".PHP_EOL;
        $htc .= "order allow,deny".PHP_EOL;
        $htc .= "deny from all".PHP_EOL;
        $htc .= "</Files>".PHP_EOL.PHP_EOL;
        $htc .= "# Protect config.php".PHP_EOL;
        $htc .= "<Files config.php>".PHP_EOL;
        $htc .= "order allow,deny".PHP_EOL;
        $htc .= "deny from all".PHP_EOL;
        $htc .= "</Files>".PHP_EOL.PHP_EOL;
        $htc .= "# Block Nasty Bots".PHP_EOL;
        $htc .= "<IfModule mod_setenvifno.c>".PHP_EOL;
        $htc .= "	SetEnvIfNoCase ^User-Agent$ .*(craftbot|download|extract|stripper|sucker|ninja|clshttp|webspider|leacher|collector|grabber|webpictures) HTTP_SAFE_BADBOT".PHP_EOL;
        $htc .= "	SetEnvIfNoCase ^User-Agent$ .*(libwww-perl|aesop_com_spiderman) HTTP_SAFE_BADBOT".PHP_EOL;
        $htc .= "	Deny from env=HTTP_SAFE_BADBOT".PHP_EOL;
        $htc .= "</IfModule>".PHP_EOL.PHP_EOL;
        $htc .= "# Disable directory listing".PHP_EOL;
        $htc .= "Options -Indexes".PHP_EOL.PHP_EOL;
        $htc .= "ErrorDocument 400 ".$site_path."error.php?code=400".PHP_EOL;
        $htc .= "ErrorDocument 401 ".$site_path."error.php?code=401".PHP_EOL;
        $htc .= "ErrorDocument 403 ".$site_path."error.php?code=403".PHP_EOL;
        $htc .= "ErrorDocument 404 ".$site_path."error.php?code=404".PHP_EOL;
        $htc .= "ErrorDocument 500 ".$site_path."error.php?code=500".PHP_EOL;
        file_put_contents(BASEDIR.".htaccess", $htc);
    }

    /**
     * A wrapper function for file_put_contents with cache invalidation
     *
     * If opcache is enabled on the server, this function will write the file
     * as the original file_put_contents and invalidate the cache of the file.
     *
     * It is needed when you create a file dynamically and want to include it
     * before the cache is invalidated. Redirection does not matter.
     *
     * @todo Find a better place and/or name for this function
     *
     * @param string          $file file path
     * @param string|string[] $data
     * @param int             $flags
     * @return int Number of written bytes
     */
    private function fusion_file_put_contents($file, $data, $flags = NULL) {
        $bytes = NULL;
        if ($flags === NULL) {
            $bytes = \file_put_contents($file, $data);
        } else {
            $bytes = \file_put_contents($file, $data, $flags);
        }
        if (function_exists('opcache_invalidate')) {
            \opcache_invalidate($file, TRUE);
        }

        return $bytes;
    }

}