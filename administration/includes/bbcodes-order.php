<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: bbcodes-order.php
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
defined("IN_FUSION") || exit;

/**
 * Update sitelinks order
 */
function update_bbcodes_order() {
    require_once INCLUDES.'ajax_include.php';
    header_content_type('json');

    if (iADMIN && checkrights("BB")) {

        if (fusion_safe()) {
            if ($bbcodes_order = post("order")) {
                $bbcodes_order = explode(",", $bbcodes_order);
                $order = 1;
                foreach ($bbcodes_order as $bbcode_id) {
                    dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=:order WHERE bbcode_id=:bbcodeid", [':order' => $order, ':bbcodeid' => $bbcode_id]);
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
 * @uses update_bbcodes_order()
 */
fusion_add_hook("fusion_admin_hooks", "update_bbcodes_order");
