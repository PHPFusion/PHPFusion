<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: autoloader.php
| Author: Takács Ákos (Rimelek)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
/*
 * Loads classes from ClassName.php
 */
spl_autoload_register(function ($className) {
    $baseDir = __DIR__.'/classes/';
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    $fullPath = $baseDir.$path.'.php';
    if (is_file($fullPath)) {
        require_once $fullPath;
    }

    $baseDir = __DIR__.'/';
    $fullPath = $baseDir.$path.'.php';
    if (is_file($fullPath)) {
        require_once $fullPath;
    }
});

spl_autoload_register(function ($className) {
    if (stristr($className, '_')) {
        $className = explode('_', $className);
        $className = $className[0].'.'.strtolower($className[1]);
        $baseDir = __DIR__.'/classes/';
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $className);
        $fullPath = $baseDir.$path.'.php';
        if (is_file($fullPath)) {
            require_once $fullPath;
        }
    }
});

/*
 * Autoloader for compatibility reason
 *
 * It loads only classes from ClassName.class.php in global namespace
 */
spl_autoload_register(function ($className) {
    if (strpos($className, '\\') !== FALSE) {
        return;
    }
    $baseDir = __DIR__.'/classes/';
    $fullPath = $baseDir.$className.'.class.php';
    if (is_file($fullPath)) {
        require_once $fullPath;
    }
});

/**
 * Infusions Autoloading
 * All class files must be lowercase and end with .class.php in infusions global namespace
 */
spl_autoload_register(function ($className) {
    if (stristr($className, 'PHPFusion\\Infusions')) {

        $className = str_replace('PHPFusion\\Infusions\\', '', $className);
        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);

        $fullPath = BASEDIR.'infusions/'.$className.'.class.php';
        if (is_file($fullPath)) {
            require_once $fullPath;
        } else {
            // Files with all lowercase accepted
            $className = strtolower($className);
            $fullPath = BASEDIR.'infusions/'.$className.'.class.php';
            if (is_file($fullPath)) {
                require_once $fullPath;
            }
        }
    }
});

/**
 * Get path of config.php
 *
 * @param int $max_level
 *
 * @return string|null The relative path of the base directory
 * or empty string if config.php was not found
 */
function fusion_get_config($max_level = 7) {
    static $config_path = '';
    if ($config_path === '') {
        $basedir = "";
        $i = 0;
        while ($i <= $max_level and !file_exists($basedir."config.php")) {
            $basedir .= "../";
            $i++;
        }
        $config_path = file_exists($basedir."config.php") ? $basedir."config.php" : '';
    }

    return $config_path;
}

if (!defined('BASEDIR')) {
    define("BASEDIR", strpos(fusion_get_config(), '/') === FALSE ? '' : dirname(fusion_get_config()).'/');
}

/*
 * Include core files that is required in working order
 */
require_once __DIR__.'/core_functions_include.php';
require_once __DIR__.'/deprecated.php';
require_once __DIR__.'/core_constants_include.php';
require_once __DIR__."/sqlhandler.inc.php";
require_once __DIR__."/translate_include.php";
require_once __DIR__."/output_handling_include.php";
require_once __DIR__."/notify.php";
require_once __DIR__."/hooks_include.php";

if (is_file(__DIR__."/vendor/autoload.php")) {
    require_once __DIR__."/vendor/autoload.php";
}

if (is_file(__DIR__."/custom_includes.php")) {
    require_once __DIR__."/custom_includes.php";
}
//require_once __DIR__.'/db_handlers/all_functions_include.php';

// Generate config file
if (!is_file(__DIR__.'/config.inc.php')) {
    $text = "<?php".PHP_EOL;
    $text .= "/**".PHP_EOL;
    $text .= " * Here you can configure additional system settings".PHP_EOL;
    $text .= " */".PHP_EOL;
    $text .= "\$config_inc = [".PHP_EOL;
    $text .= "    'cache' => [".PHP_EOL;
    $text .= "        'storage'        => 'file', // file|redis|memcache".PHP_EOL;
    $text .= "        'memcache_hosts' => ['localhost:11211'], // e.g. ['localhost:11211', '192.168.1.100:11211', 'unix:///var/tmp/memcached.sock']".PHP_EOL;
    $text .= "        'redis_hosts'    => ['localhost:6379'], // e.g. ['localhost:6379', '192.168.1.100:6379:1:passwd']".PHP_EOL;
    $text .= "        'path'           => BASEDIR.'cache/system/' // for FileCache".PHP_EOL;
    $text .= "    ]".PHP_EOL;
    $text .= "];".PHP_EOL;

    write_file(__DIR__.'/config.inc.php', $text);
}
