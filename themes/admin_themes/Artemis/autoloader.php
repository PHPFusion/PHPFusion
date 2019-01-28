<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: autoloader.php
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
require_once INCLUDES.'theme_functions_include.php';
spl_autoload_register(function ($className) {
    $autoload_register_paths = [
        "Artemis\\Viewer\\adminPanel"      => THEMES."admin_themes/Artemis/Drivers/Viewer/adminPanel.php",
        "Artemis\\Viewer\\adminDashboard"  => THEMES."admin_themes/Artemis/Drivers/Viewer/adminDashboard.php",
        "Artemis\\Viewer\\loginPanel"      => THEMES."admin_themes/Artemis/Drivers/Viewer/loginPanel.php",
        "Artemis\\Model\\resource"         => THEMES."admin_themes/Artemis/Drivers/Model/resource.php",
        "Artemis\\Controller"              => THEMES."admin_themes/Artemis/Drivers/controller.php",
    ];

    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (file_exists($fullPath)) {
            require $fullPath;
        }
    }
});
