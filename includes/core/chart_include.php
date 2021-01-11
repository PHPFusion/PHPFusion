<?php
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
