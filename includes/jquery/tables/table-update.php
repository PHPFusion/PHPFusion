<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: table-update.php
| Author: PHP-Fusion Development Team
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
// token already being used.

$response = [
    'status' => 'error',
    'data' => $_POST,
    'error_message' => '',
];

if (isset($_POST['table_fields']) && isset($_POST['table_db']) && isset($_POST['table_key'])) {

    $table_db = stripinput($_POST['table_db']); // table db

    $table_fields = Defender::sanitize_array($_POST['table_fields']);

    $table_key = stripinput($_POST['table_key']);

    if (!empty($table_fields)) {

        foreach($table_fields as $input_name => $input_value) {

            $data[$input_name] = stripinput($input_value);

        }

        if (!empty($table_db)) {

            dbquery_insert($table_db, $data, 'update');

            $response['status'] = 'success';

            $response['data'] = $data;

            $response['data-row'] = $table_key;

        } else {

            $reponse['error_message'] = 'Fail to obtain primary table';

        }

    } else {

        $response['error_message'] = 'Fail to obtain field structure';
    }

} else {
    $response['error_message'] = 'Incorrect parameters being used.';
}

echo json_encode($response);