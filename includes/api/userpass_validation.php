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
 * Validate password strength
 */
function xuserpass_validation() {
    $userpass = (string)filter_input(INPUT_GET, 'pass', FILTER_UNSAFE_RAW);
    $result = [];
    if (!empty($userpass)) {
        $locale = fusion_get_locale('', LOCALE.LOCALESET.'user_fields.php');

        // Check length
        $regex = \PHPFusion\PasswordAuth::passwordStrengthOpts(8, FALSE, FALSE, FALSE);
        if (preg_match('/'.$regex.'/', $userpass)) {
            // Check contains number
            $regex = \PHPFusion\PasswordAuth::passwordStrengthOpts(8, TRUE, FALSE, FALSE);
            if (preg_match('/'.$regex.'/', $userpass)) {
                // Check contains at least 1 upper and 1 lowercase
                $regex = \PHPFusion\PasswordAuth::passwordStrengthOpts(8, TRUE, TRUE, FALSE);
                if (preg_match('/'.$regex.'/', $userpass)) {
                    // Check contains special char
                    $regex = \PHPFusion\PasswordAuth::passwordStrengthOpts(8, TRUE, TRUE, TRUE);
                    if (preg_match('/'.$regex.'/', $userpass)) {
                        $result['result'] = 'valid';

                    } else {
                        $result['result'] = 'invalid';
                        $result['response'] = $locale['u300'];
                    }
                } else {
                    $result['result'] = 'invalid';
                    $result['response'] = $locale['u301'];
                }
            } else {
                // no number
                $result['result'] = 'invalid';
                $result['response'] = $locale['u302'];
            }
        } else {
            // password too short
            $result['result'] = 'invalid';
            $result['response'] = $locale['u303'];
        }

        $result['regex'] = $regex;

        header('Content-Type: application/json');
        echo json_encode($result);
    }
    die();
}

/**
 * @uses xuserpass_validation()
 */
fusion_add_hook('fusion_filters', 'xuserpass_validation');
