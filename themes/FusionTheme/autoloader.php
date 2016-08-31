<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Genesis Interface
| The Genesis Project - 2014 - 2016 (c)
| Network Data Model Development
| Filename: Genesis_ACP/acp_request.php
| Author: Guidlsquare , enVision Sdn Bhd
| Copyright patent 0517721 IPO
| Author's all rights reserved.
+--------------------------------------------------------+
| Released under PHP-Fusion EPAL
+--------------------------------------------------------*/
require_once INCLUDES."theme_functions_include.php";

spl_autoload_register(function ($className) {
    $baseDir = __DIR__.DIRECTORY_SEPARATOR;
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    $fullPath = $baseDir.$path.'.php';
    if (is_file($fullPath)) {
        require $fullPath;
    }
});
