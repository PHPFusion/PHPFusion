<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
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
defined("IN_FUSION") || exit;

/**
 * Error logs updater
 */
function error_logs_updater() {
    $id = get('error_id', FILTER_SANITIZE_NUMBER_INT);
    $type = get('error_type', FILTER_SANITIZE_NUMBER_INT);

    if (checkrights("ERRO") && fusion_safe()) {
        $this_response = ['fusion_error_id' => $id, 'from' => 0, 'status' => 'Not Updated'];

        $result = dbquery("SELECT error_status FROM ".DB_ERRORS." WHERE error_id='".intval($id)."'");

        if (dbrows($result) > 0) {
            $data = dbarray($result);
            if ($type == 999) {
                // Delete Error
                $result = dbquery("DELETE FROM ".DB_ERRORS." WHERE error_id='".intval($id)."'");
                if ($result) {
                    $this_response = ['fusion_error_id' => $id, 'from' => $data['error_status'], 'to' => $type, 'status' => 'RMD'];
                }
            } else {
                // Update Error Status
                $result = dbquery("UPDATE ".DB_ERRORS." SET error_status='".intval($type)."' WHERE error_id='".intval($id)."'");
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
}

/**
 * @uses error_logs_updater()
 */
fusion_add_hook('fusion_admin_hooks', 'error_logs_updater');
