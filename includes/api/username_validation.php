<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: username_validation.php
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
defined('IN_FUSION') || exit;
/**
 * Username validation
 */
function xusername_validation() {
    $username = (string)filter_input(INPUT_GET, 'name', FILTER_UNSAFE_RAW);
    $result = [];
    if (!empty($username)) {
        $locale = fusion_get_locale('', LOCALE.LOCALESET.'user_fields.php');

        $available = dbcount("(user_id)", DB_USERS, "user_name=:name", [':name' => $username]);
        $is_used = dbcount("(user_code)", DB_NEW_USERS, "user_name=:name", [':name' => $username]);

        if (!preg_match('/^[-a-z\p{L}\p{N}_]*$/ui', $username)) { // Check for invalid characters
            $result['result'] = 'invalid';
            $result['response'] = $locale['u120'];
        } else if (in_array($username, explode(',', fusion_get_settings('username_ban')))) { // Check for prohibited usernames
            $result['result'] = 'invalid';
            $result['response'] = $locale['u119'];
        } else {
            if ($available == 0 && $is_used == 0) {
                $result['result'] = 'valid';
            } else {
                $result['result'] = 'invalid';
                $result['response'] = $locale['u121'];
            }
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }
    die();
}

/**
 * @uses xusername_validation()
 */
fusion_add_hook('fusion_filters', 'xusername_validation');
