<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: alerts.php
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

class Alerts {

    public static function renderNotices($notices) {
        $messages = "";
        // need to migrate to boiler support since this has HTML
        foreach ($notices as $status => $notice) {
            if (count($notice) > 0) {
                foreach($notice as $toast) {
                    if (isset($toast["toast"])) {
                        $template = "toast.twig";
                        $info = $toast;
                    } else {
                        $template = "alert.twig";
                        $info["message"] = $toast;
                    }
                    $messages .= fusion_render(__DIR__."/html/", $template, $info, true);
                    //print_P($messages);
                }
            } else {
                $template = (isset($notice["toast"]) ? "toast.twig" : "alert.twig");
                $messages .= fusion_render(__DIR__."/html/", $template, $notice, true);
            }
        }

        return (string)$messages;
    }

}
