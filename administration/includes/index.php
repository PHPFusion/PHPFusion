<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: index.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__."/../../maincore.php";

if (iADMIN) {
    $endpoints = [
        "sitelinks"       => "sitelinks/sitelinks.php",
        "sitelinks-order" => "sitelinks/sitelinks-order.php",
    ];
    if ($api = get("api")) {
        if (isset($endpoints[$api])) {

            require __DIR__.DIRECTORY_SEPARATOR.$endpoints[$api];

            fusion_apply_hook("fusion_admin_hooks");

        } else {
            throw new Exception("End point is faulty");
        }
    } else {
        throw new Exception("API is not specified");
    }
} else {
    throw new Exception("You are not authorized to view the data");
}
