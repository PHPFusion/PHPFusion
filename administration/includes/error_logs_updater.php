<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: error_logs_updater.php
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
require_once __DIR__.'/../../maincore.php';
require_once INCLUDES.'ajax_include.php';

$aidlink = filter_input(INPUT_GET, 'aidlink', FILTER_DEFAULT);
$aid = !empty($aidlink) ? explode('=', $aidlink) : '';

if (!empty($aid)) {
    $aid = $aid[1];
}
$error_id = filter_input(INPUT_GET, 'error_id', FILTER_VALIDATE_INT);
$id = !empty($error_id) ? $error_id : 0;

$error_type = filter_input(INPUT_GET, 'error_type', FILTER_VALIDATE_INT);
$type = !empty($error_type) ? $error_type : 0;

if (checkrights("ERRO") && defined("iAUTH") && $aid == iAUTH && fusion_safe()()) {

    $this_response = ['fusion_error_id' => $id, 'from' => 0, 'status' => 'Not Updated'];

    $result = dbquery("SELECT error_status	FROM ".DB_ERRORS." WHERE error_id=:errorid", [':errorid' => (int)$id]);

    if (dbrows($result) > 0) {
        $data = dbarray($result);
        if ($type == 999) {
            // Delete Error
            $result = dbquery("DELETE FROM ".DB_ERRORS." WHERE error_id=:errorid", [':errorid' => (int)$id]);
            if ($result) {
                $this_response = ['fusion_error_id' => $id, 'from' => $data['error_status'], 'to' => $type, 'status' => 'RMD'];
            }
        } else {
            // Update Error Status
            $result = dbquery("UPDATE ".DB_ERRORS." SET error_status=:status WHERE error_id=:errorid", [':status' => (int)$type, ':errorid' => (int)$id]);
            if ($result) {
                $this_response = ['fusion_error_id' => $id, 'from' => $data['error_status'], 'to' => $type, 'status' => 'OK'];
            }
        }
    } else {
        // Invalid error ID
        $this_response = ['fusion_error_id' => $id, 'from' => 0, 'status' => 'Invalid ID'];
    }
} else {
    $this_response = ['fusion_error_id' => $id, 'from' => 0, 'status' => 'Invalid Token or Insufficient Rights'];
}

header('Content-Type: application/json');

echo json_encode($this_response);
