<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------*
| Filename: sldata.php
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

$token = filter_input(INPUT_GET, 'token', FILTER_DEFAULT);
$aid = !empty($token) ? explode('=', $token) : '';

if (!empty($aid)) {
    $aid = $aid[1];
}

$inputq = filter_input(INPUT_GET, 'q', FILTER_VALIDATE_INT);
$q = !empty($inputq) ? $inputq : 0;

if (checkrights("SL") && defined("iAUTH") && $aid == iAUTH) {
    $result = dbquery("SELECT * FROM ".DB_SITE_LINKS." WHERE link_id = :linkid", [':linkid' => (int)$q]);
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
