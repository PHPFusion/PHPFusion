<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: site_links_updater.php
| Author: Hans Kristian Flaatten {Starefossen}
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../../maincore.php";
pageAccess("SL");

$result = '';

if (isset($_GET['listItem']) && is_array($_GET['listItem'])) {
    foreach ($_GET['listItem'] as $position => $item) {
        if (isnum($position) && isnum($item)) {
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order='".($position + 1)."' WHERE link_id='".$item."'");
        }
    }
}

if ($result) {
    header('Content-Type: application/json');

    echo json_encode(['status' => 200]);
}
