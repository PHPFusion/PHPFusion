<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: autoloader.php
| Author: Core Development Team
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
        "PHPFusion\\Weblinks\\WeblinksServer"           => WEBLINKS_CLASSES."server.php",
        "PHPFusion\\Weblinks\\WeblinksAdminModel"       => WEBLINKS_CLASSES."admin/weblinks_admin_model.php",
        "PHPFusion\\Weblinks\\WeblinksAdminView"        => WEBLINKS_CLASSES."admin/weblinks_admin_view.php",
        "PHPFusion\\Weblinks\\WeblinksSettingsAdmin"    => WEBLINKS_CLASSES."admin/controllers/weblinks_settings.php",
        "PHPFusion\\Weblinks\\WeblinksSubmissionsAdmin" => WEBLINKS_CLASSES."admin/controllers/weblinks_submissions.php",
        "PHPFusion\\Weblinks\\WeblinksCategoryAdmin"    => WEBLINKS_CLASSES."admin/controllers/weblinks_cat.php",
        "PHPFusion\\Weblinks\\WeblinksAdmin"            => WEBLINKS_CLASSES."admin/controllers/weblinks.php",
        "PHPFusion\\Weblinks\\WeblinksSubmissions"      => WEBLINKS_CLASSES."weblinks/weblinks_submissions.php",
        "PHPFusion\\Weblinks\\WeblinksView"             => WEBLINKS_CLASSES."weblinks/weblinks_view.php",
        "PHPFusion\\Weblinks\\Weblinks"                 => WEBLINKS_CLASSES."weblinks/weblinks.php"
    ];

    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (is_file($fullPath)) {
            require $fullPath;
        }
    }
});
