<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: auth.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
// Build the rules here for the api to read and output it's data

$api['auth']['token'] = [
    'name' => 'Fetches a token that can be used in the current site',
    'method' => 'get',
    'params' => [
        'form_id' => 'string',
        'max_tokens' => 'int',
        'file' => 'string',
        'token_time' => 'int',
    ],
    'callback' => ['\Defender\Token', 'generate_token'] // bind this to ReflectionClass for output?
];
$api['auth']['login'] = [
    'name' => 'Login to the current system',
    'method' => 'post',
    'construct' => [
        'user_id' => 'string',
        'user_email' => 'string',
        'user_pass' => 'string',
    ],
    'callback' => ['\PHPFusion\Authenticate', 'getUserData'] // bind this to ReflectionClass for output?
];
$api['auth']['logout'] = [
    'name' => 'Logout from the current system',
    'method' => 'post',
    'callback' => ['\PHPFusion\Authenticate', 'logOut'] // bind this to ReflectionClass for output?
];
