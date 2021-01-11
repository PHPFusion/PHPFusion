<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: chart_include.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
(defined("IN_FUSION")||exit);

// Charts Loader
//spl_autoload_register(function ($className) {
//    if (stristr($className, 'PHPFusion\\Charts')) {
//        //print_p($className);
//        $className = str_replace('PHPFusion\\Charts\\', '', $className);
//        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
//        $fullPath = INCLUDES.'charts/'.$className.'.php';
//        $baseName = basename($fullPath);
//        $path_dir = strtolower(str_replace($baseName, "", $fullPath)).$baseName;
//        /** All folders must be lowercase and only the filename is camelcase */
//        if (is_file($path_dir)) {
//            $class_loaded[$className] = TRUE;
//            require_once $path_dir;
//        }
//    }
//});
