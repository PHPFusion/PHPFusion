<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: username_validation.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

function xhttp_usernamecheck() {
    $username = (string)filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
    $result = [];
    if (!empty($username)) {
        $available = dbcount("(user_id)", DB_USERS, "user_name=:name", [':name' => $username]);
        $is_used = dbcount("(user_code)", DB_NEW_USERS, "user_name=:name", [':name' => $username]);
        $check_string = preg_match("/^[\p{Latin}\p{Arabic}\p{Cyrillic}\p{Han}\p{Hebrew}a-zA-Z\p{N}]+\h?[\p{N}\p{Latin}\p{Arabic}\p{Cyrillic}\p{Han}\p{Hebrew}a-zA-Z]*$/um", $username);
        $username_ban = explode(',', fusion_get_settings('username_ban'));

        if ($available == 0 && $is_used == 0 && $check_string && !in_array($username, $username_ban)) {
            $result['result'] = 'valid';
        } else {
            $result['result'] = 'invalid';
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }
    die();
}

fusion_add_hook("fusion_filters", "xhttp_usernamecheck");
