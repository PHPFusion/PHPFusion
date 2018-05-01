<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusions/weblinks/classes/autoloader.php
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
require_once INCLUDES."infusions_include.php";

spl_autoload_register(function ($className) {
    $autoload_register_paths = [
        "PHPFusion\\Weblinks\\WeblinksServer"           => WEBLINKS_CLASS."server.php",
        "PHPFusion\\Weblinks\\WeblinksAdminModel"       => WEBLINKS_CLASS."admin/weblinks_admin_model.php",
        "PHPFusion\\Weblinks\\WeblinksAdminView"        => WEBLINKS_CLASS."admin/weblinks_admin_view.php",
        "PHPFusion\\Weblinks\\WeblinksSettingsAdmin"    => WEBLINKS_CLASS."admin/controllers/weblinks_settings.php",
        "PHPFusion\\Weblinks\\WeblinksSubmissionsAdmin" => WEBLINKS_CLASS."admin/controllers/weblinks_submissions.php",
        "PHPFusion\\Weblinks\\WeblinksCategoryAdmin"    => WEBLINKS_CLASS."admin/controllers/weblinks_cat.php",
        "PHPFusion\\Weblinks\\WeblinksAdmin"            => WEBLINKS_CLASS."admin/controllers/weblinks.php",
        "PHPFusion\\Weblinks\\WeblinksSubmissions"      => WEBLINKS_CLASS."weblinks/weblinks_submissions.php",
        "PHPFusion\\Weblinks\\WeblinksView"             => WEBLINKS_CLASS."weblinks/weblinks_view.php",
        "PHPFusion\\Weblinks\\Weblinks"                 => WEBLINKS_CLASS."weblinks/weblinks.php"
    ];

    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (is_file($fullPath)) {
            require $fullPath;
        }
    }
});
