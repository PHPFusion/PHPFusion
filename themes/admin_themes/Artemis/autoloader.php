<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Artemis Interface
| The Artemis Project - 2014 - 2016 (c)
| Network Data Model Development
| Filename: Artemis_ACP/acp_request.php
| Author: Guidlsquare , enVision Sdn Bhd
| Copyright patent 0517721 IPO
| Author's all rights reserved.
+--------------------------------------------------------+
| Released under PHP-Fusion EPAL
+--------------------------------------------------------*/

spl_autoload_register(function ($className) {

    $autoload_register_paths = array(
        "Artemis\\Viewer\\adminPanel" => THEMES."admin_themes/Artemis/Drivers/Viewer/adminPanel.php",
        "Artemis\\Viewer\\adminDashboard" => THEMES."admin_themes/Artemis/Drivers/Viewer/adminDashboard.php",
        "Artemis\\Viewer\\adminPanel" => THEMES."admin_themes/Artemis/Drivers/Viewer/adminPanel.php",
        "Artemis\\Viewer\\loginPanel" => THEMES."admin_themes/Artemis/Drivers/Viewer/loginPanel.php",
        "Artemis\\Viewer\\adminApps" => THEMES."admin_themes/Artemis/Drivers/Viewer/adminApps.php",
        "Artemis\\Model\\resource" => THEMES."admin_themes/Artemis/Drivers/Model/resource.php",
        "Artemis\\Controller" => THEMES."admin_themes/Artemis/Drivers/controller.php",
        "Artemis\\Subcontroller\\get_apps" => THEMES."admin_themes/Artemis/Drivers/Subcontroller/get_apps.php"
    );

    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (file_exists($fullPath)) {
            require $fullPath;
        }
    }
});
