<?php
/*
 * Get System Requirements for PHP-Fusion 9
 * @return mixed
 */
function get_system_requirements() {
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
        "config_temp.php" => FALSE,
        "robots.txt" => FALSE,
    );

    foreach ($check_arr as $key => $value) {

        $check_arr[$key] = (file_exists(BASEDIR.$key) && is_writable(BASEDIR.$key)) or (file_exists(BASEDIR.$key) && function_exists("chmod") && @chmod(BASEDIR.$key,
                                                                                                                                                        0777) && is_writable(BASEDIR.$key));
        if (!$check_arr[$key]) {
            $requirements['files_check']['sub'][$key] = 'Not Writable (Failed)';
            $requirements['files_check']['severability'] = -10;
        } else {
            $requirements['files_check']['sub'][$key] = 'Writable (Pass)';
        }
    }
    $requirements['files_check']['title'] = 'Files Check Requirements';
    $requirements['files_check']['description'] = 'In order for setup to continue, the following files and folders should be writable. Please chmod the files to 755 o 777 to continue';

    return $requirements;
}