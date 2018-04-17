<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: FusionTheme/autoloader.php
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
require_once INCLUDES."theme_functions_include.php";

spl_autoload_register(function ($className) {
    $baseDir = __DIR__.DIRECTORY_SEPARATOR;
    $path = strtolower(str_replace('\\', DIRECTORY_SEPARATOR, $className));
    $fullPath = $baseDir.$path.'.php';
    //print_p($fullPath);
    if (is_file($fullPath)) {
        require $fullPath;
    }
});
