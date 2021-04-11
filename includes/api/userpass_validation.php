<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: username_validation.php
| Author: PHPFusion Developers Team
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

function xhttp_userpasscheck() {

    $userpass = (string)filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
    $result = [];
    if (!empty($userpass)) {
        // Check length
        $regex = \PHPFusion\PasswordAuth::_passwordStrengthOpts(8, FALSE, FALSE, FALSE);
        if (preg_match('/'.$regex.'/', $userpass)) {
            // Check contains number
            $regex = \PHPFusion\PasswordAuth::_passwordStrengthOpts(8, TRUE, FALSE, FALSE);
            if (preg_match('/'.$regex.'/', $userpass)) {
                // Check contains at least 1 upper and 1 lowercase
                $regex = \PHPFusion\PasswordAuth::_passwordStrengthOpts(8, TRUE, TRUE, FALSE);
                if (preg_match('/'.$regex.'/', $userpass)) {
                    // Check contains special char
                    $regex = \PHPFusion\PasswordAuth::_passwordStrengthOpts(8, TRUE, TRUE, TRUE);
                    if (preg_match('/'.$regex.'/', $userpass)) {
                        $result['result'] = 'valid';

                    } else {
                        $result['result'] = 'invalid';
                        $result['response'] = 'Password should contain at least 1 special character';
                    }
                } else {
                    $result['result'] = 'invalid';
                    $result['response'] = 'Password should contain at least 1 uppercase and 1 lowercase character';
                }
            } else {
                // no number
                $result['result'] = 'invalid';
                $result['response'] = 'Password should contain at least 1 number character';
            }
        } else {
            // password too short
            $result['result'] = 'invalid';
            $result['response'] = 'Password should be at least 8 characters long';
        }

        $result['regex'] = $regex;

        header('Content-Type: application/json');
        echo json_encode($result);
    }
    die();
}

fusion_add_hook("fusion_filters", "xhttp_userpasscheck");
