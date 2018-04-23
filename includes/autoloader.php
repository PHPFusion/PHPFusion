<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: includes/autoloader.php
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
    $fullPath = $baseDir.$path.'.inc';
    if (is_file($fullPath)) {
        require_once $fullPath;
    }
    $baseDir = __DIR__.'/';
    $fullPath = $baseDir.$path.'.inc';
    if (is_file($fullPath)) {
        require_once $fullPath;
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
    $fullPath = $baseDir.$className.'.class.inc';
    if (is_file($fullPath)) {
        require $fullPath;
    }
});

/*
 * New convention to rename core files as .inc instead of.php
 */
spl_autoload_register(function ($className) {
    if (stristr($className, '_')) {
        $className = explode('_', $className);
        $className = $className[0].'.'.strtolower($className[1]);
        $baseDir = __DIR__.'/classes/';
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $className);
        $fullPath = $baseDir.$path.'.inc';
        if (is_file($fullPath)) {
            require $fullPath;
        }
    }
});

// Load infusions autoloader.
// @todo 9.04 --
// @todo: Rename all infusions namespace with "PHPFusion\Infusions\{Infusion_name};
// @todo: then remove all infusions autoloader and complete check the file structure.
spl_autoload_register(function ($className) {
    if (stristr($className, 'PHPFusion\\Infusions')) {
        //print_p($className);
        $className = str_replace('PHPFusion\\Infusions\\', '', $className);
        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
        $className = strtolower($className);
        //print_P($className);
        $fullPath = BASEDIR.'infusions/'.$className.'.php';
        //print_p($fullPath);
        if (is_file($fullPath)) {
            require $fullPath;
        }
    }
});

/**
 * Get path of config.php
 *
 * @param int $max_level
 *
 * @return string|null The relative path of the base directory
 * or NULL if config.php was not found
 */
function fusion_get_config($max_level = 7) {
    static $config_path = NULL;
    if ($config_path === NULL) {
        $basedir = "";
        $i = 0;
        while ($i <= $max_level and !file_exists($basedir."config.php")) {
            $basedir .= "../";
            $i++;
        }
        $config_path = file_exists($basedir."config.php") ? $basedir."config.php" : NULL;
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
require_once __DIR__.'/core_constants_include.php';
require_once __DIR__."/sqlhandler.inc.php";
require_once __DIR__."/translate_include.php";
require_once __DIR__."/output_handling_include.php";
require_once __DIR__."/notify.inc";
//require_once __DIR__.'/db_handlers/all_functions_include.php';
