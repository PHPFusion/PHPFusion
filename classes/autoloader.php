<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/classes/autoloader.php
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
        "PHPFusion\\FAQ\\FaqServer"           => FAQ_CLASS."server.php",
        "PHPFusion\\FAQ\\FaqAdminModel"       => FAQ_CLASS."admin/faq_admin_model.php",
        "PHPFusion\\FAQ\\FaqAdminView"        => FAQ_CLASS."admin/faq_admin_view.php",
        "PHPFusion\\FAQ\\FaqSettingsAdmin"    => FAQ_CLASS."admin/controllers/faq_settings.php",
        "PHPFusion\\FAQ\\FaqSubmissionsAdmin" => FAQ_CLASS."admin/controllers/faq_submissions.php",
        "PHPFusion\\FAQ\\FaqSubmissions"      => FAQ_CLASS."faq/faq_submissions.php",
        "PHPFusion\\FAQ\\FaqAdmin"            => FAQ_CLASS."admin/controllers/faq.php",
        "PHPFusion\\FAQ\\FaqView"             => FAQ_CLASS."faq/faq_view.php",
        "PHPFusion\\FAQ\\Faq"                 => FAQ_CLASS."faq/faq.php"
    ];

    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (is_file($fullPath)) {
            require $fullPath;
        }
    }
});
