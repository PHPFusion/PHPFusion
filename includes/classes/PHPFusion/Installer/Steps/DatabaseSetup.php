<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: DatabaseSetup.php
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
namespace PHPFusion\Installer\Steps;

use PHPFusion\Installer;
use PHPFusion\Installer\Batch;
use PHPFusion\Installer\InstallCore;
use PHPFusion\Installer\Requirements;

/**
 * Class InstallerDbSetup
 *
 * @package PHPFusion\Steps
 */
class DatabaseSetup extends InstallCore {

    /**
     * @return false|string
     */
    public function view() {
        return INSTALLATION_STEP == self::STEP_DB_SETTINGS_SAVE ? $this->dispatchTables() : $this->stepForm();
    }

    /**
     * Handle insertions of core settings table
     *
     * @return string
     */
    private function dispatchTables() {
        $debug_process = FALSE;
        $debug_batching = FALSE;
        $log_file = FALSE; // set true for debugging
        $content = '';

        if (check_post("step")) {
            self::$connection = [
                'db_host'         => post("db_host"),
                'db_port'         => post("db_port", FILTER_VALIDATE_INT),
                'db_user'         => post("db_user"),
                'db_pass'         => post("db_pass"),
                'db_name'         => post("db_name"),
                'db_prefix'       => post("db_prefix"),
                'cookie_prefix'   => post("cookie_prefix"),
                //'db_driver'       => post("db_driver"),
                'db_driver'       => extension_loaded('pdo_mysql') ? 'pdo' : 'mysqli',
                'localeset'       => post("localeset"),
                'secret_key_salt' => self::createRandomPrefix(32),
                'secret_key'      => self::createRandomPrefix(32)
            ];

            // Force underscores for these two values
            self::$connection['db_prefix'] = rtrim(self::$connection['db_prefix'], '_').'_';
            self::$connection['cookie_prefix'] = rtrim(self::$connection['cookie_prefix'], '_').'_';

            if (!defined('DB_PREFIX')) {
                define('DB_PREFIX', self::$connection['db_prefix']);
            }

            if (!defined('COOKIE_PREFIX')) {
                define('COOKIE_PREFIX', self::$connection['cookie_prefix']);
            }

            if (!defined('SECRET_KEY_SALT')) {
                define('SECRET_KEY_SALT', self::createRandomPrefix(32));
            }

            if (!defined('SECRET_KEY')) {
                define('SECRET_KEY', self::createRandomPrefix(32));
            }


        } else {
            $db_host = '';
            $db_port = '';
            $db_user = '';
            $db_pass = '';
            $db_name = '';
            $db_driver = '';
            include BASEDIR."config_temp.php";
            self::$connection = [
                'db_host'       => $db_host,
                'db_port'       => $db_port,
                'db_user'       => $db_user,
                'db_pass'       => $db_pass,
                'db_name'       => $db_name,
                'db_prefix'     => DB_PREFIX,
                'cookie_prefix' => COOKIE_PREFIX,
                'db_driver'     => $db_driver,
                'localeset'     => LOCALESET
            ];
        }

        $_SESSION['db_config_connection'] = self::$connection;

        if (fusion_safe()) {

            $validate = Requirements::getSystemValidation();

            if (isset($validate[4])) {

                require_once(INCLUDES.'multisite_include.php');

                $to_create = Batch::getInstance()->batchRuntime('create'); // this should just run once no matter how many times queried.

                $to_alter_column = Batch::getInstance()->batchRuntime('alter_column');

                $to_add_column = Batch::getInstance()->batchRuntime('add_column');

                $to_insert_rows = Batch::getInstance()->batchRuntime('insert'); // must return array to insert with table.

                $to_upgrade = Batch::getInstance()->batchRuntime("upgrade");

                $query_count = 0;

                // Create missing new tables
                if (!empty($to_create) && $debug_batching === FALSE) {
                    if (!$debug_process) {
                        foreach ($to_create as $table_create) {

                            $query_count = $query_count + 1;

                            $this->doCoreBatch($table_create);
                        }
                    }
                }

                // Alterations of inconsistent columns - varchar(200) to text
                if (!empty($to_alter_column) && $debug_batching === FALSE) {
                    //$message = "<strong>".self::$locale['setup_1600']."...</strong>\n";
                    if (!$debug_process) {
                        foreach ($to_alter_column as $table_processes) {

                            if (!empty($table_processes)) {

                                foreach ($table_processes as $table_alter) {

                                    $query_count = $query_count + 1;

                                    $this->doCoreBatch($table_alter);

                                }
                            }
                        }
                    }
                }

                // Adding missing columns on a specific table
                if (!empty($to_add_column) && $debug_batching === FALSE) {
                    //$message = "<strong>".self::$locale['setup_1602']."...</strong>\n";
                    if (!$debug_process) {
                        foreach ($to_add_column as $table_processes) {

                            if (!empty($table_processes)) {
                                foreach ($table_processes as $table_add) {

                                    $query_count = $query_count + 1;

                                    $this->doCoreBatch($table_add);

                                }
                            }
                        }
                    }
                }

                // Insert default rows on all required tables
                if (!empty($to_insert_rows) && $debug_batching === FALSE) {
                    // $message = "<strong>".self::$locale['setup_1603']."...</strong>\n";
                    if (!$debug_process) {
                        foreach ($to_insert_rows as $row_inserts) {

                            $query_count = $query_count + 1;

                            $this->doCoreBatch($row_inserts);
                        }
                        //addNotice("info", $message);
                    }
                }

                //Checking for upgrade
                if ($debug_batching === FALSE) {
                    $to_upgrade = Batch::getInstance()->checkUpgrades(); // get upgrade queries
                    if (!empty($to_upgrade)) {
                        $error = FALSE;
                        //$message = "<strong>Building version upgrades...</strong>\n";
                        if (!$debug_process) {
                            $filename = '';
                            foreach ($to_upgrade as $filename => $file_upgrades) {

                                //$microtime = microtime(TRUE);
                                if (!empty($file_upgrades)) {
                                    foreach ($file_upgrades as $callback_method => $upgrades) {
                                        if (!empty($upgrades)) {

                                            self::$allow_delete = TRUE;

                                            $method = $callback_method."_infuse";
                                            if (method_exists($this, $method)) {
                                                //dynamically select object pairing dynamic assigned function on dynamic callback.
                                                $query_count = $query_count + 1;

                                                //$error = $this->$method([$callback_method => $upgrades]);
                                                $this->doUpgradeBatch($callback_method, [$callback_method => $upgrades]);

                                            }
                                        }
                                    }
                                }

                                //$microtime = microtime(TRUE) - $microtime;
                                //$message .= "Building version upgrades -".$filename;
                                //Batch_Core::getInstance()->Progress($current_count, $total_tests, $microtime, "Building version upgrades ".$filename.'...', (!$error ? 1 : 0));
                            }

                            /*if (!$error) {
                                dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:version_value WHERE settings_name=:version_col", [
                                    ':version_value' => $filename,
                                    ':version_col'   => 'version'
                                ]);
                            }*/
                        }
                    }
                }

                // Generate Log File
                if ($log_file === TRUE) {
                    if (!empty($to_create)) {
                        $sql[] = $this->makeSqlImportLog("Creates Table", $to_create);
                    }
                    if (!empty($to_alter_column)) {
                        $sql[] = $this->makeSqlImportLog("Modifies Table", $to_alter_column);
                    }
                    if (!empty($to_add_column)) {
                        $sql[] = $this->makeSqlImportLog("Adds Column Table on", $to_add_column);
                    }
                    if (!empty($to_insert_rows)) {
                        $sql[] = $this->makeSqlImportLog("Insert rows into", $to_insert_rows);
                    }
                    if (!empty($to_upgrade)) {
                        foreach ($to_upgrade as $instructions) {
                            $sql[] = $this->makeSqlImportLog("Upgrading..", $instructions, TRUE);
                        }
                    }
                    if (!empty($sql)) {
                        if (!is_file(BASEDIR."installer_".date("d-M-Y").".process.log")) {
                            touch(BASEDIR."installer_".date("d-M-Y").".process.log");
                        }
                        write_file(BASEDIR."installer_".date("d-M-Y").".process.log", implode("", $sql));
                    }
                }


                /*
                 * Generate final message
                 */
                if ($debug_process === FALSE) {
                    require_once(INCLUDES.'htaccess_include.php');
                    Installer\write_config(self::$connection);

                    if (!isset($_GET['upgrade']) || !is_file(BASEDIR.'.htaccess')) {
                        write_htaccess();
                    }

                    if (!empty($to_upgrade)) {
                        self::installerStep(self::STEP_INFUSIONS);
                    } else {
                        self::installerStep(self::STEP_PRIMARY_ADMIN_FORM);
                    }
                    redirect(FUSION_REQUEST);
                } else {
                    print_p('Debug print end.');
                }
            } else {
                foreach ($validate as $validate_result) {
                    if (!$validate_result['result']) {
                        addnotice('danger', $validate_result['description']);
                    }
                }
                self::installerStep(self::STEP_DB_SETTINGS_FORM);
                redirect(FUSION_REQUEST);
            }
            return $content;
        } else {
            self::installerStep(self::STEP_DB_SETTINGS_FORM);
            redirect(FUSION_REQUEST);
        }

        return FALSE;
    }

    /**
     * @param string $query
     *
     * @return bool
     */
    protected function doCoreBatch($query) {
        try {
            dbquery($query);
            return FALSE;
        } catch (\Exception $e) {
            if (!is_file(BASEDIR."installer_".date("d-M-Y").".errors.log")) {
                touch(BASEDIR."installer_".date("d-M-Y").".errors.log");
            }
            write_file(BASEDIR."installer_".date("d-M-Y").".log.txt", $e->getMessage().$query, FILE_APPEND);
            return TRUE;
        }
    }

    /**
     * @param string $method
     * @param array  $code_array
     *
     * @return bool
     */
    protected function doUpgradeBatch($method, $code_array) {
        try {
            $method = $method."_infuse";
            return $this->$method($code_array);

        } catch (\Exception $e) {
            if (!is_file(BASEDIR."installer_".date("d-M-Y").".errors.log")) {
                touch(BASEDIR."installer_".date("d-M-Y").".errors.log");
            }
            write_file(BASEDIR."installer_".date("d-M-Y").".log.txt", $e->getMessage(), FILE_APPEND);
            return TRUE;
        }

    }

    /**
     * @param string $comment_message
     * @param array  $array
     * @param bool   $_SDK
     *
     * @return string
     */
    protected function makeSqlImportLog($comment_message, $array, $_SDK = FALSE) {
        $sql = "";
        if (!empty($array)) {
            foreach ($array as $table => $syntax) { // table is method
                if ($comment_message) {
                    $sql_head = "### ".$comment_message." ".DB_PREFIX.$table.PHP_EOL;
                    if ($_SDK) {
                        $sql_head = "### ".$comment_message." ON $table method".PHP_EOL;
                    }
                    $sql .= $sql_head;
                    if (!empty($syntax)) {

                        if (is_array($syntax)) {
                            foreach ($syntax as $code) {
                                if (is_array($code)) {
                                    $code = implode("\n\r", $code);
                                }
                                $sql_code = $code.PHP_EOL.PHP_EOL;
                                if ($_SDK) {
                                    $sql_code .= "### Skipping\n".$code.PHP_EOL;
                                }
                                $sql .= $sql_code;
                            }
                        } else {
                            // new installation requires this
                            $sql .= $syntax.PHP_EOL.PHP_EOL;
                        }

                    }
                }
            }
        }
        return $sql;
    }

    /**
     * @return string
     */
    private function stepForm() {
        // Back button prevention
        if (!empty(self::$connection)) {
            if (version_compare(self::BUILD_VERSION, fusion_get_settings('version'), "==")) {
                self::installerStep(self::STEP_INTRO);
                redirect(FUSION_REQUEST);
            }
        }

        self::setEmptyPrefix();

        if (!empty(session_get("db_config_connection"))) {
            self::$connection = $_SESSION['db_config_connection'];
        }

        $content = "<h4 class='title'>".self::$locale['setup_1200']."</h4><p>".self::$locale['setup_1201']."</p>\n";
        $content .= "<hr/>\n";

        $content .= rendernotices(getnotices());
        $content .= form_text('db_host', self::$locale['setup_1202'], !empty(self::$connection['db_host']) ? self::$connection['db_host'] : 'localhost', [
            'inline'      => TRUE,
            'required'    => TRUE,
            'placeholder' => self::$locale['setup_1225']
        ]);
        $content .= form_text('db_port', self::$locale['setup_1202a'].'<br/><small>'.self::$locale['setup_1202b'].'</small>', self::$connection['db_port'], [
            'inline'      => TRUE,
            'placeholder' => 3306
        ]);
        $content .= form_text('db_name', self::$locale['setup_1205'], self::$connection['db_name'], [
            'inline'      => TRUE,
            'required'    => TRUE,
            'placeholder' => self::$locale['setup_1220']
        ]);
        $content .= form_text('db_user', self::$locale['setup_1203'], self::$connection['db_user'], [
            'inline'      => TRUE,
            'required'    => TRUE,
            'placeholder' => self::$locale['setup_1221']
        ]);
        $content .= form_text('db_pass', self::$locale['setup_1204'], self::$connection['db_pass'], [
            'type'             => 'text',
            'inline'           => TRUE,
            'required'         => FALSE,
            'placeholder'      => self::$locale['setup_1222'],
            'autocomplete_off' => !isset($_GET['upgrade'])
        ]);
        $content .= "<h4 class='title'>".self::$locale['setup_1092']."</h4>";
        $content .= form_text('db_prefix', self::$locale['setup_1206'], self::$connection['db_prefix'], [
            'inline'      => TRUE,
            'required'    => TRUE,
            'placeholder' => self::$locale['setup_1223']
        ]);
        $content .= form_text('cookie_prefix', self::$locale['setup_1207'], self::$connection['cookie_prefix'], [
            'inline'      => TRUE,
            'required'    => TRUE,
            'placeholder' => self::$locale['setup_1224']
        ]);
        /*$options['mysqli'] = 'MySQLi';
        $value = 'mysqli';
        if (extension_loaded('pdo_mysql')) {
            $options['pdo'] = 'PDO MySQL';
            $value = 'pdo';
        }
        $content .= form_select('db_driver', self::$locale['setup_1208'], $value, [
            'options' => $options,
            'inline'  => TRUE
        ]);*/

        self::$step = [
            1 => [
                'name'  => 'step',
                'label' => self::$locale['setup_0121'],
                'value' => self::STEP_DB_SETTINGS_SAVE
            ]
        ];

        return $content;
    }
}
