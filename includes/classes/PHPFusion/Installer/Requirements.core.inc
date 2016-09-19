<?php
namespace PHPFusion\Installer;

class Requirements extends Install_Core {

    /*
    * Get System Requirements for PHP-Fusion 9
    * @return mixed
    */
    public static function get_system_requirements() {
        // Web server information.
        $software = $_SERVER['SERVER_SOFTWARE'];

        $requirements['webserver'] = array(
            'title' => 'Web Server',
            'value' => $software,
        );

        // Tests clean URL support.
        if (!extension_loaded('rewrite') && strpos($software, 'Apache') !== FALSE) {
            $apache_version_string = 'Apache';
            // Determine the Apache version number: major, minor and revision.
            if (preg_match('/Apache\/(\d+)\.?(\d+)?\.?(\d+)?/', $software, $matches)) {
                $apache_version_string = $matches[0];
                // Major version number
                if ($matches[1] < 2) {
                    $requirements['apache_version'] = array(
                        'version' => $apache_version_string,
                        'description' => 'The minimum version of Apache needed to run PHP-Fusion without mod_rewrite enabled is 2.2.16.',
                        'severability' => -10
                    );
                } elseif ($matches[1] == 2) {
                    if (!isset($matches[2])) {
                        $requirements['apache_version'] = array(
                            'version' => $apache_version_string,
                            'description' => 'Due to the settings for Servertokens in httpd.confg, it is impossible to determine the version of Apache without mod_rewrite, a minimum version of 2.2.16 is needed',
                            'severability' => -10
                        );
                    } elseif ($matches[2] < 2) {
                        $requirements['apache_version'] = array(
                            'version' => $apache_version_string,
                            'description' => 'The minimum version of Apache needed to run Drupal without mod_rewrite enabled is 2.2.16.',
                            'severability' => -10
                        );
                    } elseif ($matches[2] == 2) {
                        if (!isset($matches[3])) {
                            $requirements['apache_version'] = array(
                                'version' => $apache_version_string,
                                'description' => 'Due to the settings for Servertokens in httpd.confg, it is impossible to determine the version of Apache without mod_rewrite, a minimum version of 2.2.16 is needed',
                                'severability' => -10
                            );
                        } elseif ($matches[3] < 16) {
                            $requirements['apache_version'] = array(
                                'version' => $apache_version_string,
                                'description' => 'The minimum version of Apache needed to run Drupal without mod_rewrite enabled is 2.2.16.',
                                'severability' => -10
                            );
                        }
                    }
                }
            } else {
                $requirements['apache_version'] = array(
                    'version' => $apache_version_string,
                    'description' => 'Due to the settings for Servertokens in httpd.confg, it is impossible to determine the version of Apache without mod_rewrite, a minimum version of 2.2.16 is needed',
                    'severability' => -10
                );
            }
        }

        // Test PHP version and show link to phpinfo() if it's available
        $phpversion = $phpversion_label = phpversion();
        if (function_exists('phpinfo')) {
            $requirements['php'] = array(
                'title' => 'PHP',
                'value' => $phpversion_label,
            );
        } else {
            $requirements['php'] = array(
                'title' => 'PHP',
                'value' => $phpversion_label,
                'description' => 'The phpinfo() function has been disabled for security reasons. To see your server\'s phpinfo() information, change your PHP settings or contact your server administrator.',
                'severability' => -5
            );
        }

        if (version_compare($phpversion, '5.5.21') < 0) {
            $requirements['php']['description'] = 'Your PHP installation is too old. PHP-Fusion requires at least a minimum of 5.5.21. PHP versions higher than 5.6.5 or 5.5.21 provide built-in SQL injection protection for mysql databases. It is recommended to update.';
            $requirements['php']['severability'] = -10;
        }

        // Suggest to update to at least 5.5.21 or 5.6.5 for disabling multiple
        // statements.
        $requirements['php_extensions']['title'] = 'PHP Extensions';
        $missing_extensions = array();
        $required_extensions = array(
            'date',
            'dom',
            'filter',
            'gd',
            'hash',
            'json',
            'pcre',
            'pdo',
            'session',
            'SimpleXML',
            'SPL',
            'tokenizer',
            'xml',
        );
        foreach ($required_extensions as $extension) {
            if (!extension_loaded($extension)) {
                $missing_extensions[] = $extension;
            }
        }

        if (!empty($missing_extensions)) {
            $requirements['php_extensions'] = array(
                'description' => 'PHP-Fusion requires you to enable the PHP extension in the following list',
                'value' => implode(', ', $missing_extensions),
                'severability' => -10,
            );
        } else {
            $requirements['php_extensions']['description'] = 'Enabled';
        }

        // Check to see if OPcache is installed.
        $opcache_enabled = (function_exists('opcache_get_status') && opcache_get_status()['opcache_enabled']);
        if (!$opcache_enabled) {
            $requirements['php_opcache'] = array(
                'value' => 'Not enabled',
                'description' => 'PHP OPcode caching can improve your site\'s performance considerably. It is <strong>highly recommended</strong> to have <a href="http://php.net/manual/opcache.installation.php" target="_blank">OPcache</a> installed on your server.',
                'severability' => -1,
            );
        } else {
            $requirements['php_opcache']['value'] = 'Enabled';
        }
        $requirements['php_opcache']['title'] = 'OPCache support';

        // Test for PDO (database).
        // Make sure PDO is available.
        $database_ok = extension_loaded('pdo');
        if (!$database_ok) {
            $requirements['database_extensions'] = array(
                'value' => 'Not enabled',
                'description' => 'Your web server does not appear to support PDO (PHP Data Objects). Ask your hosting provider if they support the native PDO extension.',
                'severability' => -1,
            );
        } else {
            $requirements['database_extensions']['value'] = 'Enabled';
        }
        $requirements['database_extensions']['title'] = 'PDO Database support';

        // Test PHP memory_limit
        $memory_limit = ini_get('memory_limit');
        $requirements['php_memory_limit'] = [
            'title' => 'PHP memory limit',
            'value' => $memory_limit == -1 ? '-1 (Unlimited)' : $memory_limit,
        ];
        if (strpos($memory_limit, 'M')) {
            $memory_limit_value = intval(rtrim($memory_limit, 'M'));
        }
        if (!$memory_limit_value || $memory_limit_value < 64) {
            $requirements['memory'] = [
                'description' => 'Consider increasing your PHP memory limit to %memory_minimum_limit to help prevent errors in the installation process.',
                'severability' => -5,
            ];
            if (php_ini_loaded_file() == get_cfg_var('cfg_file_path')) {
                $requirements['value'] = 'Increase the memory limit by editing the memory_limit parameter in the file '.get_cfg_var('cfg_file_path').' and then restart your web server (or contact your system administrator or hosting provider for assistance).';
                $requirements['severability'] = -5;
            } else {
                $requirements['value'] = 'Contact your system administrator or hosting provider for assistance with increasing your PHP memory limit.';
                $requirements['severability'] = -5;
            }
        }

        // Xdebug max nesting level.
        if (extension_loaded('xdebug')) {
            // Setting this value to 256 was considered adequate on Xdebug 2.3
            // (see http://bugs.xdebug.org/bug_view_page.php?bug_id=00001100)
            $minimum_nesting_level = 256;
            $current_nesting_level = ini_get('xdebug.max_nesting_level');
            if ($current_nesting_level < $minimum_nesting_level) {
                $requirements['xdebug_max_nesting_level'] = [
                    'title' => 'Xdebug settings',
                    'value' => 'xdebug.max_nesting_level is set to '.$current_nesting_level,
                    'description' => 'Set <code>xdebug.max_nesting_level='.$minimum_nesting_level.'</code> in your PHP configuration as some pages in your Drupal site will not work when this setting is too low.'
                ];
            }
        }

        // Check system core files and folders
        $check_arr = array(
            "administration/db_backups" => FALSE,
            "ftp_upload" => FALSE,
            "images" => FALSE,
            "images/imagelist.js" => FALSE,
            "images/avatars" => FALSE,
            //"config_temp.php" => FALSE,
            "robots.txt" => FALSE,
        );

        foreach ($check_arr as $key => $value) {

            $check_arr[$key] = (file_exists(BASEDIR.$key) && is_writable(BASEDIR.$key)) or (file_exists(BASEDIR.$key) && function_exists("chmod") && @chmod(BASEDIR.$key,
                                                                                                                                                            0777) && is_writable(BASEDIR.$key));
            if (!$check_arr[$key]) {
                $requirements['files_check']['sub'][$key] = 'Not Writable (Failed)';
                $requirements['files_check']['sub'][$key]['severability'] = -10;
            } else {
                $requirements['files_check']['sub'][$key] = 'Writable (Pass)';
            }
        }
        $requirements['files_check']['title'] = 'Files Check Requirements';
        $requirements['files_check']['description'] = 'All required files passed the file writable requirements.';
        if (isset($requirements['files_check']['severability'])) {
            $requirements['files_check']['description'] = 'In order for setup to continue, the following files and folders should be writable. Please chmod the files to 755 o 777 to continue';
        }

        return $requirements;
    }

    /*
     * Validate the system for consistency
     */
    public static function get_system_validation() {

        $microtime = microtime(TRUE);
        $system = self::$connection;
        $locale = self::$locale;

        if (!file_exists(BASEDIR."config_temp.php")) {

            if (file_exists(BASEDIR."_config.php") && function_exists("rename")) {
                @rename(BASEDIR."_config.php", BASEDIR."config_temp.php");
            } else {
                touch(BASEDIR."config_temp.php");
            }

        }

        if (!empty($system['db_host']) && !empty($system['db_user']) && !empty($system['db_name']) && !empty($system['db_pass'])) {

            $validation[1] = [
                'result' => 1,
                'description' => 'Database Diagnostics Completed',
                'elapsed' => microtime(TRUE) - $microtime
            ];

            $connection_info = dbconnect($system['db_host'], $system['db_user'], $system['db_pass'], $system['db_name'], FALSE);
            $db_connect = $connection_info['connection_success'];
            $db_select = $connection_info['dbselection_success'];

            if ($db_connect) {

                $validation[2] = [
                    'result' => 1,
                    'description' => 'Database connection established',
                    'elapsed' => microtime(TRUE) - $microtime
                ];

                if ($db_select) {

                    $validation[3] = [
                        'result' => 1,
                        'description' => 'Database column selection established',
                        'elapsed' => microtime(TRUE) - $microtime
                    ];


                    if (!check_table($system['db_prefix'])) {
                        $validation[4] = [
                            'result' => 1,
                            'description' => 'Database is available and ready for installation',
                            'elapsed' => microtime(TRUE) - $microtime
                        ];
                        if (test_table($system['db_prefix'])) {
                            $validation[5] = [
                                'result' => 1,
                                'description' => 'Database permissions and access verified',
                                'elapsed' => microtime(TRUE) - $microtime
                            ];

                            if (write_config($system)) {
                                $validation[6] = [
                                    'result' => 1,
                                    'description' => 'config.php file created',
                                    'elapsed' => microtime(TRUE) - $microtime
                                ];
                            } else {

                                //Please ensure config.php is writable
                                $validation[6] = [
                                    'result' => 0,
                                    'description' => $locale['setup_1307'],
                                    'elapsed' => microtime(TRUE) - $microtime
                                ];
                            }

                        } else {

                            //"Please make sure your MySQL user has read, write and delete permission for the selected database.";
                            $validation[5] = [
                                'result' => 1,
                                'description' => $locale['setup_1315'],
                                'elapsed' => microtime(TRUE) - $microtime
                            ];

                        }
                    } else {

                        //The specified table prefix is already in use and is running. No tables will be installed. Please start over or proceed to the next step
                        /*
                         * We will not stop the installer and let it proceed with schema scans
                         */
                        $validation[4] = [
                            'result' => 1,
                            'description' => 'The specified table prefix is already in use and is running. The installer will proceed with updating differences as required',
                            //$locale['setup_1313'],
                            'elapsed' => microtime(TRUE) - $microtime
                        ];

                    }


                } else {
                    //The specified MySQL database does not exist.
                    $validation[3] = [
                        'result' => 0,
                        'description' => $locale['setup_1311'],
                        'elapsed' => microtime(TRUE) - $microtime
                    ];

                }
            } else {
                //Unable to connect with MySQL database.
                $validation[2] = [
                    'result' => 0,
                    'description' => $locale['setup_1310'],
                    'elapsed' => microtime(TRUE) - $microtime
                ];
            }
        } else {
            //Please make sure you have filled out all the MySQL connection fields
            $validation[1] = [
                'result' => 0,
                'description' => $locale['setup_1317'],
                'elapsed' => microtime(TRUE) - $microtime
            ];
        }

        return $validation;
    }

}

function test_table($db_prefix) {
    $table_name = uniqid($db_prefix, FALSE);
    $result = dbquery("CREATE TABLE ".$table_name." (test_field VARCHAR(10) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
    if (!$result) {
        return FALSE;
    }
    $result = dbquery("DROP TABLE ".$table_name);
    if (!$result) {
        return FALSE;
    }

    return TRUE;
    }

function check_table($db_prefix) {
    return dbrows(dbquery("SHOW TABLES LIKE '".str_replace("_", "\_", $db_prefix)."%'"));
    }

function write_config(array $system = array()) {

    if (!file_exists(BASEDIR.'config.php')) {
        //touch(BASEDIR.'config_temp.php');
        }

    // Force underscore between prefix and cookie name
    if (!empty($system['cookie_prefix'])) {
        $cookie_prefix_last = $system['cookie_prefix'][strlen($system['cookie_prefix']) - 1];
        if ($cookie_prefix_last != "_") {
            $system['cookie_prefix'] = $system['cookie_prefix']."_";
        }
    }


    if (!empty($system['db_prefix'])) {
        $cookie_prefix_last = $system['db_prefix'][strlen($system['db_prefix']) - 1];
        if ($cookie_prefix_last != "_") {
            $system['db_prefix'] = $system['db_prefix']."_";
        }
        }

    $config = "<?php\n";
    $config .= "// database settings\n";
    $config .= "\$db_host = '".$system['db_host']."';\n";
    $config .= "\$db_user = '".$system['db_user']."';\n";
    $config .= "\$db_pass = '".$system['db_pass']."';\n";
    $config .= "\$db_name = '".$system['db_name']."';\n";
    $config .= "\$db_prefix = '".$system['db_prefix']."';\n";
    $config .= "\$pdo_enabled = ".intval($system['pdo_enabled']).";\n";
    $config .= "define(\"DB_PREFIX\", \"".$system['db_prefix']."\");\n";
    $config .= "define(\"COOKIE_PREFIX\", \"".$system['cookie_prefix']."\");\n";
    $config .= "define(\"SECRET_KEY\", \"".$system['secret_key']."\");\n";
    $config .= "define(\"SECRET_KEY_SALT\", \"".$system['secret_key_salt']."\");\n";
    if (write_file(BASEDIR.'config_temp.php', $config)) {
        return TRUE;
    }

    return FALSE;
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
function write_file($file, $data, $flags = NULL) {
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