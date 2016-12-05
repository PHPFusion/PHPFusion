<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Requirments.core.inc
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
namespace PHPFusion\Installer;

use PHPFusion\Database\DatabaseFactory;

class Requirements extends Install_Core {

    /*
    * Get System Requirements for PHP-Fusion 9
    * @return mixed
    */
    public static function get_system_requirements() {
        // Web server information.
        $software = $_SERVER['SERVER_SOFTWARE'];

        $requirements['webserver'] = array(
            'title' => self::$locale['setup_0050'],
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
                        'title' => 'Apache',
                        'version' => $apache_version_string,
                        'description' => self::$locale['setup_0109'],
                        'severability' => -10
                    );
                } elseif ($matches[1] == 2) {
                    if (!isset($matches[2])) {
                        $requirements['apache_version'] = array(
                            'title' => 'Apache',
                            'version' => $apache_version_string,
                            'description' => self::$locale['setup_0120'],
                            'severability' => -10
                        );
                    } elseif ($matches[2] < 2) {
                        $requirements['apache_version'] = array(
                            'title' => 'Apache',
                            'version' => $apache_version_string,
                            'description' => self::$locale['setup_0109'],
                            'severability' => -10
                        );
                    } elseif ($matches[2] == 2) {
                        if (!isset($matches[3])) {
                            $requirements['apache_version'] = array(
                                'title' => 'Apache',
                                'version' => $apache_version_string,
                                'description' => self::$locale['setup_0110'],
                                'severability' => -10
                            );
                        } elseif ($matches[3] < 16) {
                            $requirements['apache_version'] = array(
                                'title' => 'Apache',
                                'version' => $apache_version_string,
                                'description' => self::$locale['setup_0109'],
                                'severability' => -10
                            );
                        }
                    }
                }
            } else {
                $requirements['apache_version'] = array(
                    'title' => 'Apache',
                    'version' => $apache_version_string,
                    'description' => self::$locale['setup_0110'],
                    'severability' => -10
                );
            }
        }

        // Test PHP version and show link to phpinfo() if it's available
        $phpversion = $phpversion_label = phpversion();
        if (function_exists('phpinfo')) {
            $requirements['php'] = array(
                'title' => self::$locale['setup_0051'],
                'value' => $phpversion_label,
            );
        } else {
            $requirements['php'] = array(
                'title' => self::$locale['setup_0051'],
                'value' => $phpversion_label,
                'description' => self::$locale['setup_0112'],
                'severability' => -5
            );
        }

        if (version_compare($phpversion, '5.5.21') < 0) {
            $requirements['php']['title'] = 'PHP';
            $requirements['php']['description'] = self::$locale['setup_0113'];
            $requirements['php']['severability'] = -10;
        }

        // Suggest to update to at least 5.5.21 or 5.6.5 for disabling multiple
        // statements.
        $requirements['php_extensions']['title'] = self::$locale['setup_0052'];
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
                'description' => self::$locale['setup_0114'],
                'value' => implode(', ', $missing_extensions),
                'severability' => -10,
            );
        } else {
            $requirements['php_extensions']['description'] = self::$locale['setup_0115'];
        }

        // Check to see if OPcache is installed.
        $opcache_enabled = (function_exists('opcache_get_status') && opcache_get_status()['opcache_enabled']);
        if (!$opcache_enabled) {
            $requirements['php_opcache'] = array(
                'value' => self::$locale['setup_0115a'],
                'description' => self::$locale['setup_0116'],
                'severability' => -1,
            );
        } else {
            $requirements['php_opcache']['value'] = self::$locale['setup_0115'];
        }
        $requirements['php_opcache']['title'] = self::$locale['setup_0053'];

        // Test for PDO (database).
        // Make sure PDO is available.
        $database_ok = extension_loaded('pdo');
        if (!$database_ok) {
            $requirements['database_extensions'] = array(
                'value' => self::$locale['setup_0115a'],
                'description' => self::$locale['setup_0118'],
                'severability' => -1,
            );
        } else {
            $requirements['database_extensions']['value'] = self::$locale['setup_0115'];
        }
        $requirements['database_extensions']['title'] = self::$locale['setup_0054'];

        // Test PHP memory_limit
        $memory_limit = ini_get('memory_limit');
        $requirements['php_memory_limit'] = [
            'title' => self::$locale['setup_0055'],
            'value' => $memory_limit == -1 ? '-1 (Unlimited)' : $memory_limit,
        ];
        if (strpos($memory_limit, 'M')) {
            $memory_limit_value = intval(rtrim($memory_limit, 'M'));
        }
        if (!$memory_limit_value || $memory_limit_value < 64) {
            $requirements['php_memory_limit'] = [
                'description' => self::$locale['setup_0119a'],
                'severability' => -5,
            ];
            if (php_ini_loaded_file() == get_cfg_var('cfg_file_path')) {
                $requirements['php_memory_limit']['value'] = self::$locale['setup_0119b'];
                $requirements['php_memory_limit']['severability'] = -5;
            } else {
                $requirements['php_memory_limit']['value'] = self::$locale['setup_0119c'];
                $requirements['php_memory_limit']['severability'] = -5;
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
                    'title' => self::$locale['setup_0130'],
                    'value' => self::$locale['setup_0131'].' '.$current_nesting_level,
                    'description' => strtr(self::$locale['setup_0132'], [
                        '{%code%}' => "<code>xdebug.max_nesting_level=".$minimum_nesting_level."</code>"
                    ])
                ];
            }
        }

        // Check system core files and folders
        $check_arr = array(
            "administration/db_backups" => FALSE,
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
                $requirements['files_check']['sub'][$key] = self::$locale['setup_0136'];
                $requirements['files_check']['sub'][$key]['severability'] = -10;
            } else {
                $requirements['files_check']['sub'][$key] = self::$locale['setup_0137'];
            }
        }
        $requirements['files_check']['title'] = self::$locale['setup_0056'];
        $requirements['files_check']['description'] = self::$locale['setup_0134'];
        if (isset($requirements['files_check']['severability'])) {
            $requirements['files_check']['description'] = self::$locale['setup_0135'];
        }

        return $requirements;
    }

    /*
     * Validate the system for consistency
     */
    public static function get_system_validation() {

        $microtime = microtime(TRUE);
        $system = self::$connection;

        if (!isset($system['db_prefix']) && empty($system['db_prefix']) && defined('DB_PREFIX')) {
            $system['db_prefix'] = DB_PREFIX;
        }

        if (!isset($system['cookie_prefix']) && empty($system['cookie_prefix']) && defined('COOKIE_PREFIX')) {
            $system['cookie_prefix'] = COOKIE_PREFIX;
        }

        if (!isset($system['secret_key']) && empty($system['secret_key']) && defined('SECRET_KEY')) {
            $system['secret_key'] = SECRET_KEY;
        }

        if (!isset($system['secret_key_salt']) && empty($system['secret_key_salt']) && defined('SECRET_KEY_SALT')) {
            $system['secret_key_salt'] = SECRET_KEY_SALT;
        }

        $locale = self::$locale;

        if (!empty($system['db_host']) && !empty($system['db_user']) && !empty($system['db_name']) && !empty($system['db_pass'])) {

            require_once INCLUDES."db_handlers/all_functions_include.php";

            $validation[1] = [
                'result' => 1,
                'description' => self::$locale['setup_0144'],
                'elapsed' => microtime(TRUE) - $microtime
            ];

            DatabaseFactory::setDefaultDriver(intval(self::$connection['pdo_enabled']) === 1 ? DatabaseFactory::DRIVER_PDO_MYSQL : DatabaseFactory::DRIVER_MYSQLi);

            // There will be a connection issue present.
            $connection_info = $connection = dbconnect(self::$connection['db_host'], self::$connection['db_user'], self::$connection['db_pass'],
                                                       self::$connection['db_name'], FALSE);
            $db_connect = $connection_info['connection_success'];
            $db_select = $connection_info['dbselection_success'];

            if ($db_connect) {

                $validation[2] = [
                    'result' => 1,
                    'description' => self::$locale['setup_0138'],
                    'elapsed' => microtime(TRUE) - $microtime
                ];

                if ($db_select) {

                    $validation[3] = [
                        'result' => 1,
                        'description' => self::$locale['setup_0139'],
                        'elapsed' => microtime(TRUE) - $microtime
                    ];


                    if (!check_table($system['db_prefix'])) {
                        $validation[4] = [
                            'result' => 1,
                            'description' => self::$locale['setup_0140'],
                            'elapsed' => microtime(TRUE) - $microtime
                        ];
                        if (test_table($system['db_prefix'])) {
                            $validation[5] = [
                                'result' => 1,
                                'description' => self::$locale['setup_0141'],
                                'elapsed' => microtime(TRUE) - $microtime
                            ];

                            if (write_config($system)) {
                                $validation[6] = [
                                    'result' => 1,
                                    'description' => self::$locale['setup_0142'],
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
                                'description' => self::$locale['setup_1315'],
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
                            'description' => self::$locale['setup_0143'],
                            //$locale['setup_1313'],
                            'elapsed' => microtime(TRUE) - $microtime
                        ];

                    }


                } else {
                    //The specified MySQL database does not exist.
                    $validation[3] = [
                        'result' => 0,
                        'description' => self::$locale['setup_1311'],
                        'elapsed' => microtime(TRUE) - $microtime
                    ];

                }
            } else {
                //Unable to connect with MySQL database.
                $validation[2] = [
                    'result' => 0,
                    'description' => self::$locale['setup_1310'],
                    'elapsed' => microtime(TRUE) - $microtime
                ];

            }
        } else {
            //Please make sure you have filled out all the MySQL connection fields
            $validation[1] = [
                'result' => 0,
                'description' => self::$locale['setup_1317'],
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

function write_htaccess() {

    $site_path = fusion_get_settings('site_path');

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
    $htc .= "   order allow,deny".PHP_EOL;
    $htc .= "   deny from all".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;
    $htc .= "# Secure inc file".PHP_EOL;
    $htc .= "<Files *.inc>".PHP_EOL;
    $htc .= "   Order allow,deny".PHP_EOL;
    $htc .= "   deny from all".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;
    $htc .= "# Protect config.php".PHP_EOL;
    $htc .= "<Files config.php>".PHP_EOL;
    $htc .= "   order allow,deny".PHP_EOL;
    $htc .= "   deny from all".PHP_EOL;
    $htc .= "</Files>".PHP_EOL.PHP_EOL;
    $htc .= "#Cache images for 7 days to soften network load".PHP_EOL;
    $htc .= "<IfModule mod_headers.c>".PHP_EOL;
    $htc .= '<FilesMatch "\.(ico|pdf|flv|jpg|jpeg|png|gif|js|css|swf)$">'.PHP_EOL;
    $htc .= '   Header set Cache-Control "max-age=290304000, public"'.PHP_EOL;
    $htc .= "</FilesMatch>".PHP_EOL;
    $htc .= "</IfModule>".PHP_EOL.PHP_EOL;
    $htc .= "# Block Nasty Bots".PHP_EOL;
    $htc .= "<IfModule mod_setenvifno.c>".PHP_EOL;
    $htc .= "	SetEnvIfNoCase ^User-Agent$ .*(craftbot|download|extract|stripper|sucker|ninja|clshttp|webspider|leacher|collector|grabber|webpictures) HTTP_SAFE_BADBOT".PHP_EOL;
    $htc .= "	SetEnvIfNoCase ^User-Agent$ .*(libwww-perl|aesop_com_spiderman) HTTP_SAFE_BADBOT".PHP_EOL;
    $htc .= "   SetEnvIfNoCase ^User-Agent$ .*(almaden|Anarchie|ASPSeek|attach|autoemailspider|BackWeb|Bandit|BatchFTP|BlackWidow|Bot\ mailto:craftbot@yahoo.com|Buddy|bumblebee|CherryPicker|ChinaClaw|CICC|Collector|Copier|Crescent|Custo|DA|DIIbot|DISCo|DISCo\ Pump|Download\ Demon|Download\ Wonder|Downloader|Drip|DSurf15a|eCatch|EasyDL/2.99|EirGrabber|EmailCollector|EmailSiphon|EmailWolf|Express\ WebPictures|ExtractorPro|EyeNetIE|FileHound|FlashGet|GetRight|GetSmart|GetWeb!|gigabaz|Go\!Zilla|Go!Zilla|Go-Ahead-Got-It|gotit|Grabber|GrabNet|Grafula|grub-client|HMView|HTTrack|httpdown|ia_archiver|Image\ Stripper|Image\ Sucker|Indy*Library|InterGET|InternetLinkagent|Internet\ Ninja|InternetSeer.com|Iria|JBH*agent|JetCar|JOC\ Web\ Spider|JustView|larbin|LeechFTP|LexiBot|lftp|Link*Sleuth|likse|Link|LinkWalker|Mag-Net|Magnet|Mass\ Downloader|Memo|Microsoft.URL|MIDown\ tool|Mirror|Mister\ PiX|Mozilla.*Indy|Mozilla.*NEWT|Mozilla*MSIECrawler|MS\ FrontPage*|MSFrontPage|MSIECrawler|MSProxy|Navroad|NearSite|NetAnts|NetMechanic|NetSpider|Net\ Vampire|NetZIP|NICErsPRO|Ninja|Octopus|Offline\ Explorer|Offline\ Navigator|Openfind|PageGrabber|Papa\ Foto|pavuk|pcBrowser|Ping|PingALink|Pockey|psbot|Pump|QRVA|RealDownload|Reaper|Recorder|ReGet|Scooter|Seeker|Siphon|sitecheck.internetseer.com|SiteSnagger|SlySearch|SmartDownload|Snake|SpaceBison|sproose|Stripper|Sucker|SuperBot|SuperHTTP|Surfbot|Szukacz|tAkeOut|Teleport\ Pro|URLSpiderPro|Vacuum|VoidEYE|Web\ Image\ Collector|Web\ Sucker|WebAuto|[Ww]eb[Bb]andit|webcollage|WebCopier|Web\ Downloader|WebEMailExtrac.*|WebFetch|WebGo\ IS|WebHook|WebLeacher|WebMiner|WebMirror|WebReaper|WebSauger|Website|Website\ eXtractor|Website\ Quester|Webster|WebStripper|ebWhacker|WebZIP|Wget|Whacker|Widow|WWWOFFLE|x-Tractor|Xaldon\ WebSpider|Xenu|Zeus.*Webster|Zeus) HTTP_SAFE_BADBOT".PHP_EOL;
    $htc .= "	Deny from env=HTTP_SAFE_BADBOT".PHP_EOL;
    $htc .= "</IfModule>".PHP_EOL.PHP_EOL;
    $htc .= "# Disable directory listing".PHP_EOL;
    $htc .= "Options -Indexes".PHP_EOL.PHP_EOL;
    // This force image to not be able to be used as other matter
    // See the harmless_kitten image in image directory
    $htc .= "<FilesMatch \"(?i).jpe?g$\">".PHP_EOL;
    $htc .= "   ForceType image/jpeg".PHP_EOL;
    $htc .= "</FilesMatch>".PHP_EOL;
    $htc .= "<FilesMatch \"(?i).gif$\">".PHP_EOL;
    $htc .= "   ForceType image/gif".PHP_EOL;
    $htc .= "</FilesMatch>".PHP_EOL;
    $htc .= "<FilesMatch \"(?i).png$\">".PHP_EOL;
    $htc .= "   ForceType image/png".PHP_EOL;
    $htc .= "</FilesMatch>".PHP_EOL.PHP_EOL;
    $htc .= "ErrorDocument 400 ".$site_path."error.php?code=400".PHP_EOL;
    $htc .= "ErrorDocument 401 ".$site_path."error.php?code=401".PHP_EOL;
    $htc .= "ErrorDocument 403 ".$site_path."error.php?code=403".PHP_EOL;
    $htc .= "ErrorDocument 404 ".$site_path."error.php?code=404".PHP_EOL;
    $htc .= "ErrorDocument 500 ".$site_path."error.php?code=500".PHP_EOL;
    write_file(BASEDIR.".htaccess", $htc);
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
