<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: table-update.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This file is for updating the table with Quick Fields
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
    'status'        => 'error',
    'data-post'          => $_POST,
    'error_message' => '',
];
$table_fields = $_POST['table_fields'];
$table_db = post('table_db');
$table_key = post('table_key');
$table_order = post('table_ordering');
$table_cat = post('table_cat');
$table_multilang = post('table_multilang');

if ($table_db && $table_key) {

    if (!empty($table_fields)) {

        foreach ($table_fields as $input_name => $input_value) {
            $data[$input_name] = stripinput($input_value);
        }

        if (!empty($table_db)) {

            dbquery_insert($table_db, $data, 'update', ['keep_session'=>TRUE]);

            if ($table_order) {
                // find all results of all applicable
                $columns[] = $table_key;

                $columns[] = $table_order;

                if ($table_cat) {
                    $columns[] = $table_cat;
                }
                if ($table_multilang) {
                    $columns[] = $table_multilang;
                }

                $conditions = [];
                if ($table_cat && isset($data[$table_cat])) {
                    $conditions[] = "$table_cat='".$data[$table_cat]."'";
                }
                if ($table_multilang && isset($data[$table_multilang])) {
                    $conditions[] = "$table_multilang='".$data[$table_multilang]."'";
                }

                $result_sql = "SELECT ".implode(',', $columns)." FROM $table_db ".(!empty($conditions) ? "WHERE ".implode(' AND ', $conditions) : '')."  ORDER BY $table_order ASC";
                $order_result = dbquery($result_sql);
                if (dbrows($order_result)) {
                    $position = 0;
                    while ($order_data = dbarray($order_result)) {
                        if (isset($order_data[$table_key])) {
                            dbquery("UPDATE $table_db SET $table_order=:order WHERE $table_key=:id", [
                                ':order' => ($position + 1),
                                ':id' => intval($order_data[$table_key])]);

                            $position++;
                        }
                    }
                }
            }

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