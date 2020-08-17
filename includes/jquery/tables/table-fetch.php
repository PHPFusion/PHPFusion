<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: table-fetch.php
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

//define('FUSION_ALLOW_REMOTE', true);
require_once __DIR__.'/../../../maincore.php';
require_once INCLUDES.'ajax_include.php';

$response = [
    'status' => 'error',
    'data' => $_POST,
    'error_message' => '',
];

if (isset($_GET['table_db']) && isset($_GET['table_key']) && isset($_GET['table_col']) && isnum($_GET['table_col'])) {
    $table = stripinput($_GET['table_db']);
    $primary_key = stripinput($_GET['table_key']);
    $primary_value = stripinput($_GET['table_col']);


    $result = dbquery("SELECT * FROM `$table` WHERE `$primary_key`=:val", [':val' => intval($primary_value) ]);
    if (dbrows($result)) {

        $response['data'] = dbarray($result);

        $response['status'] = 'success';
    }
}

echo json_encode($response);
