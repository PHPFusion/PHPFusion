<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://www.phpfusion.com/
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
    $input_string = (string)filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
    $result = [];
    if (!empty($input_string)) {
        $name_active = dbcount("(user_id)", DB_USERS, "user_name='".$input_string."'");
        $name_inactive = dbcount("(user_code)", DB_NEW_USERS, "user_name='".$input_string."'");
        $check_string = preg_match("/^[\p{Latin}\p{Arabic}\p{Cyrillic}\p{Han}\p{Hebrew}a-zA-Z\p{N}]+\h?[\p{N}\p{Latin}\p{Arabic}\p{Cyrillic}\p{Han}\p{Hebrew}a-zA-Z]*$/um", $input_string);

        if ($name_active == 0 && $name_inactive == 0 && $check_string) {
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
