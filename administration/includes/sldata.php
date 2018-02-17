<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: administration/includes/sldata.php
| Author: Frederick MC Chan (Chan)
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

$aid = isset($_GET['token']) ? explode('=', $_GET['token']) : '';

if (!empty($aid)) {
    $aid = $aid[1];
}

$q = isset($_GET['q']) && isnum($_GET['q']) ? $_GET['q'] : 0;

if (checkrights("SL") && defined("iAUTH") && $aid == iAUTH) {
    $sql = "SELECT * FROM ".DB_SITE_LINKS." WHERE link_id = '".intval($_GET['q'])."' ";
    $result = dbquery($sql);
    if (dbrows($result) > 0) {
        $data = dbarray($result);
        // parse for custom navigational ID
        if ($data['link_position'] > 3) {
            $data['link_position_id'] = $data['link_position'];
            $data['link_position'] = 4;
        }

        header('Content-Type: application/json');

        echo json_encode($data);
    }
}
