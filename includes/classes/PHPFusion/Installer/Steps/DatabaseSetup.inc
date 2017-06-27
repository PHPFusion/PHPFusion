<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: DatabaseSetup.inc
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Steps;

use PHPFusion\Installer\Batch_Core;
use PHPFusion\Installer\Install_Core;
use PHPFusion\Installer\Lib\HomeInstall;
use PHPFusion\Installer\Requirements;

class InstallerDbSetup extends Install_Core {

    public function __view() {
        switch (INSTALLATION_STEP) {
            case self::STEP_DB_SETTINGS_FORM:
                return $this->step_form();
                break;
            case self::STEP_DB_SETTINGS_SAVE:
                return $this->dispatch_tables();
        }
    }

    private function step_form() {
        // Back button prevention
        if (!empty(self::$connection)) {
            if (version_compare(self::BUILD_VERSION, fusion_get_settings('version'), "==")) {
                $this->installer_step(self::STEP_INTRO);
                redirect(FUSION_REQUEST);
            }
        }
        self::set_empty_prefix();
        $content = "<div class='m-b-20'><h4>".self::$locale['setup_1200']."</h4> ".self::$locale['setup_1201']."</div>\n";
        $content .= renderNotices(getNotices());
        $content .= form_text('db_host', self::$locale['setup_1202'], self::$connection['db_host'],
                              array(
                                  'inline'           => TRUE,
                                  'required'         => TRUE,
                                  'placeholder'      => self::$locale['setup_1225'],
                                  'autocomplete_off' => isset($_GET['upgrade']) ? FALSE : TRUE,
                              ));
        $content .= form_text('db_name', self::$locale['setup_1205'], self::$connection['db_name'],
                              array(
                                  'inline'           => TRUE,
                                  'required'         => TRUE,
                                  'placeholder'      => self::$locale['setup_1220'],
                                  'autocomplete_off' => isset($_GET['upgrade']) ? FALSE : TRUE,
                              ));
        $content .= form_text('db_user', self::$locale['setup_1203'], self::$connection['db_user'],
                              array(
                                  'inline'           => TRUE,
                                  'required'         => TRUE,
                                  'placeholder'      => self::$locale['setup_1221'],
                                  'autocomplete_off' => isset($_GET['upgrade']) ? FALSE : TRUE,
                              ));
        $content .= form_text('db_pass', self::$locale['setup_1204'], self::$connection['db_pass'],
                              array(
                                  'type'             => 'password',
                                  'inline'           => TRUE,
                                  'required'         => FALSE,
                                  'placeholder'      => self::$locale['setup_1222'],
                                  'autocomplete_off' => isset($_GET['upgrade']) ? FALSE : TRUE,
                              ));
        $content .= "<div class='m-b-20'>\n<h4>".self::$locale['setup_1092']."</h4>\n</div>\n";
        $content .= form_text('db_prefix', self::$locale['setup_1206'], self::$connection['db_prefix'],
                              array(
                                  'inline' => TRUE,
                                  'required' => TRUE,
                                  'placeholder' => self::$locale['setup_1223']
                              ));
        $content .= form_text('cookie_prefix', self::$locale['setup_1207'], self::$connection['cookie_prefix'],
                              array(
                                  'inline' => TRUE,
                                  'required' => TRUE,
                                  'placeholder' => self::$locale['setup_1224']
                              ));
        $options[0] = 'MySQLi';
        if (defined('PDO::ATTR_DRIVER_NAME')) {
            $options[1] = 'PDO';
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

    /**
     * Handle insertions of core settings table
     * @return string
     * @throws \Exception
     */
    private function dispatch_tables() {
        $debug_process = FALSE;
        $content = '';
        if (isset($_POST['step'])) {

            self::$connection = [
                'db_host' => \defender::getInstance()->form_sanitizer($_POST['db_host'], '', 'db_host'),
                'db_user' => \defender::getInstance()->form_sanitizer($_POST['db_user'], '', 'db_user'),
                'db_pass' => \defender::getInstance()->form_sanitizer($_POST['db_pass'], '', 'db_pass'),
                'db_name' => \defender::getInstance()->form_sanitizer($_POST['db_name'], '', 'db_name'),
                'db_prefix' => \defender::getInstance()->form_sanitizer($_POST['db_prefix'], '', 'db_prefix'),
                'cookie_prefix' => \defender::getInstance()->form_sanitizer($_POST['cookie_prefix'], '', 'cookie_prefix'),
                'pdo_enabled' => \defender::getInstance()->form_sanitizer($_POST['pdo_enabled'], '', 'pdo_enabled'),
                'localeset' => \defender::getInstance()->form_sanitizer($_POST['localeset'], '', 'localeset'),
                'secret_key_salt' => self::createRandomPrefix(32),
                'secret_key' => self::createRandomPrefix(32),
            ];

            if (!defined('DB_PREFIX')) define('DB_PREFIX', self::$connection['db_prefix']);
            if (!defined('COOKIE_PREFIX')) define('COOKIE_PREFIX', self::$connection['cookie_prefix']);
            if (!defined('SECRET_KEY_SALT')) define('SECRET_KEY_SALT', self::createRandomPrefix(32));
            if (!defined('SECRET_KEY')) define('SECRET_KEY', self::createRandomPrefix(32));

        } else {
            $db_host = '';
            $db_user = '';
            $db_pass = '';
            $db_name = '';
            $pdo_enabled = '';
            include BASEDIR."config_temp.php";
            self::$connection = [
                'db_host' => $db_host,
                'db_user' => $db_user,
                'db_pass' => $db_pass,
                'db_name' => $db_name,
                'db_prefix' => DB_PREFIX,
                'cookie_prefix' => COOKIE_PREFIX,
                'pdo_enabled' => $pdo_enabled,
                'localeset' => LOCALESET,
            ];
        }

        if (\defender::safe()) {

            $validate = Requirements::get_system_validation();

            if (isset($validate[4])) {

                require_once(INCLUDES.'multisite_include.php');

                $to_create = Batch_Core::getInstance()->batch_runtime('create'); // this should just run once no matter how many times queried.

                $to_alter_column = Batch_Core::getInstance()->batch_runtime('alter_column');

                $to_add_column = Batch_Core::getInstance()->batch_runtime('add_column');

                $to_insert_rows = Batch_Core::getInstance()->batch_runtime('insert'); // must return array to insert with table.

                $final_message = self::$locale['setup_1210'];

                // Go for point system differentiation
                $current_count = 0;
                if (Batch_Core::getInstance()->ProgressHasError() === FALSE) {

                    $final_message = self::$locale['setup_1211'];

                    // Create missing new tables
                    if (!empty($to_create)) {
                        $message = "<strong>".self::$locale['setup_1600']."...</strong>\n";
                        if (!$debug_process) {
                            foreach ($to_create as $table_name => $table_process) {
                                $current_count = $current_count + 1;
                                $microtime = microtime(TRUE);
                                if ($result = dbquery($table_process)) {
                                    $microtime = microtime(TRUE) - $microtime;
                                }
                                $message .= $table_name." created<br/>\n";
                                //Batch_Core::getInstance()->Progress($current_count, $total_tests, $microtime, self::$locale['setup_1600'].$table_name.'...', $result);
                            }
                            //addNotice("success", $message);
                        } else {
                            print_p('Create:');
                            print_p($to_create);
                        }
                    }

                    // Alterations of inconsistent columns - varchar(200) to text
                    if (!empty($to_alter_column)) {
                        $message = "<strong>".self::$locale['setup_1600']."...</strong>\n";
                        if (!$debug_process) {
                            foreach ($to_alter_column as $table_name => $table_processes) {
                                $current_count = $current_count + 1;
                                if (!empty($table_processes)) {
                                    foreach ($table_processes as $table_alter) {
                                        $microtime = microtime(TRUE);
                                        if ($result = dbquery($table_alter)) {
                                            $microtime = microtime(TRUE) - $microtime;
                                        }
                                        $message .= $table_name." altered<br/>\n";
                                    }
                                }
                                //addNotice("info", $message);
                            }
                        } else {
                            print_p('Alter Column:');
                            print_p($to_alter_column);
                        }
                    }
                    // Adding missing columns on a specific table
                    if (!empty($to_add_column)) {
                        $message = "<strong>".self::$locale['setup_1602']."...</strong>\n";
                        if (!$debug_process) {
                            foreach ($to_add_column as $table_name => $table_processes) {
                                $current_count = $current_count + 1;
                                if (!empty($table_processes)) {
                                    foreach ($table_processes as $table_add) {
                                        $microtime = microtime(TRUE);
                                        if ($result = dbquery($table_add)) {
                                            $microtime = microtime(TRUE) - $microtime;
                                            $message .= $table_name." altered<br/>\n";
                                        }
                                        //Batch_Core::getInstance()->Progress($current_count, $total_tests, $microtime, self::$locale['setup_1602'].$table_name.'...', $result);
                                    }
                                }
                            }
                            //addNotice("info", $message);
                        } else {
                            print_p('Add Column:');
                            print_p($to_add_column);
                        }
                    }
                    // Insert default rows on all required tables
                    if (!empty($to_insert_rows)) {
                        $message = "<strong>".self::$locale['setup_1603']."...</strong>\n";
                        if (!$debug_process) {
                            foreach ($to_insert_rows as $table_name => $row_inserts) {
                                $current_count = $current_count + 1;
                                $microtime = microtime(TRUE);
                                if ($result = dbquery($row_inserts)) {
                                    $microtime = microtime(TRUE) - $microtime;
                                    $message .= $table_name." inserted<br/>\n";
                                    //Batch_Core::getInstance()->Progress($current_count, $total_tests, $microtime, self::$locale['setup_1603'].$table_name.'...', $result);
                                }
                            }
                            //addNotice("info", $message);
                        } else {
                            print_p('Insert Rows:');
                            print_p($to_insert_rows);
                        }
                    }
                    //Checking for upgrade
                    $to_upgrade = Batch_Core::getInstance()->check_upgrades(); // get upgrade queries
                    if (!empty($to_upgrade)) {
                        $error = FALSE;
                        $message = "<strong>Building version upgrades...</strong>\n";
                        if (!$debug_process) {
                            foreach ($to_upgrade as $filename => $file_upgrades) {
                                $current_count = $current_count + 1;
                                $microtime = microtime(TRUE);
                                if (!empty($file_upgrades)) {
                                    foreach ($file_upgrades as $callback_method => $upgrades) {
                                        if (!empty($upgrades)) {
                                            self::$allow_delete = TRUE;
                                            $method = $callback_method."_infuse";
                                            if (method_exists($this, $method)) {
                                                //dynamically select object pairing dynamic assigned function on dynamic callback.
                                                $sql = [
                                                    $callback_method => $upgrades
                                                ];
                                                $error = $this->$method($sql);
                                            }
                                        }
                                    }
                                }
                                $microtime = microtime(TRUE) - $microtime;
                                //$message .= "Building version upgrades -".$filename;
                                //Batch_Core::getInstance()->Progress($current_count, $total_tests, $microtime, "Building version upgrades ".$filename.'...', (!$error ? 1 : 0));
                            }
                            if (!$error) {
                                $message = "<strong>Updating your OS to latest version.</strong>\n";
                                dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:version_value WHERE settings_name=:version_col", array(
                                    ':version_value' => $filename,
                                    ':version_col'   => 'version'
                                ));
                                //addNotice('warning', $message);
                            }
                        } else {
                            print_p('To Upgrade:');
                            print_p($to_upgrade);
                        }
                    }
                }
                /*
                 * Generate final message
                 */
                $errors = Batch_Core::getInstance()->ProgressHasError();
                if (!$errors) {
                    if (!$debug_process) {
                        \PHPFusion\Installer\write_config(self::$connection);
                        require_once(INCLUDES.'htaccess_include.php');
                        write_htaccess();
                        if (!empty($to_upgrade)) {
                            self::installer_step(self::STEP_INFUSIONS);
                        } else {
                            self::installer_step(self::STEP_PRIMARY_ADMIN_FORM);
                        }
                        redirect(FUSION_REQUEST);
                    } else {
                        print_p('Debug print end.');
                    }
                }
            } else {
                foreach ($validate as $validate_result) {
                    if (!$validate_result['result']) {
                        addNotice('danger', $validate_result['description']);
                    }
                }
                $this->installer_step(self::STEP_DB_SETTINGS_FORM);
                redirect(FUSION_REQUEST);
            }
            return $content;
        } else {
            $this->installer_step(self::STEP_DB_SETTINGS_FORM);
            redirect(FUSION_REQUEST);
        }
    }

}