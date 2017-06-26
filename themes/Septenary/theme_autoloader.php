<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Septenary Theme
| Filename: theme_autoloader.php
| Version: 1.00
| Author: PHP-Fusion Mods UK
| Developer & Designer:
| Craig (http://www.phpfusionmods.co.uk),
| Chan (Lead developer of PHP-Fusion)
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

    $autoload_register_paths = array(
        "PHPFusion\\SeptenaryTheme"      => THEME."includes/septenary.php",
        "PHPFusion\\SeptenaryComponents" => THEME."includes/components.php",
    );

    $fullPath = isset($autoload_register_paths[$className]) ? $autoload_register_paths[$className] : "";

    if (is_file($fullPath)) {
        require $fullPath;
    }

});