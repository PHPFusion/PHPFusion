<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: autoloader.php
| Author: Frederick Chan (deviance)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once INCLUDES.'theme_functions_include.php';

spl_autoload_register(function ($className) {
    $autoload_register_paths = [
        "Genesis\\Viewer\\adminPanel"      => THEMES."admin_themes/Genesis/Drivers/Viewer/adminPanel.php",
        "Genesis\\Viewer\\adminDashboard"  => THEMES."admin_themes/Genesis/Drivers/Viewer/adminDashboard.php",
        "Genesis\\Viewer\\loginPanel"      => THEMES."admin_themes/Genesis/Drivers/Viewer/loginPanel.php",
        "Genesis\\Viewer\\adminApps"       => THEMES."admin_themes/Genesis/Drivers/Viewer/adminApps.php",
        "Genesis\\Model\\resource"         => THEMES."admin_themes/Genesis/Drivers/Model/resource.php",
        "Genesis\\Controller"              => THEMES."admin_themes/Genesis/Drivers/controller.php",
        "Genesis\\Subcontroller\\get_apps" => THEMES."admin_themes/Genesis/Drivers/Subcontroller/get_apps.php"
    ];

    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (file_exists($fullPath)) {
            require $fullPath;
        }
    }
});
