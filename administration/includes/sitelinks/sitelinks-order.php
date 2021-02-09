<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: sitelinks-order.php
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
(defined("IN_FUSION") || exit);

function update_sitelinks_order() {
    if (iADMIN && checkrights("SL")) {
        if (fusion_safe()) {
            if ($link_order = post("order")) {
                $link_order = explode(",", $link_order);
                $order = 1;
                foreach ($link_order as $link_id) {
                    dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=:order ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id=:linkid", [':order' => $order, ':linkid' => $link_id]);
                    $order++;
                }

                echo json_encode(["status" => 200]);
            }
        } else {
            echo json_encode(["status" => 400]);
        }
    }
}

/**
 * @uses update_sitelinks_order()
 */
fusion_add_hook("fusion_admin_hooks", "update_sitelinks_order");

