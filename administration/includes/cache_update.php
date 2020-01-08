<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: cache_update.php
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
// cache all entries of a form to session
require_once(__DIR__.'/../../maincore.php');
header("Content-Type: text/html; charset=".fusion_get_locale('charset'));

$status['response'] = 400;

if (isset($_REQUEST['form_id'])
    && isset($_REQUEST['fusion_token'])
    && isset($_REQUEST['form_type'])
    && isset($_REQUEST['fields'])
    && isset($_REQUEST['aidlink'])
    && isset($_REQUEST['callback'])
    && isset($_REQUEST['item_id']) && isnum($_REQUEST['item_id'])
) {

    $form_id = stripinput($_REQUEST['form_id']);
    $form_type = stripinput($_REQUEST['form_type']);
    $item_id = stripinput($_REQUEST['item_id']);

    if (iADMIN && fusion_get_aidlink() == $_REQUEST['aidlink']) {
        if ($_REQUEST['callback'] == 'set_cache') {
            $status['response'] = 200;
            $_SESSION['form_cache'][$form_id][$form_type][$item_id] = form_sanitizer($_REQUEST['fields']);
            /*if ($item_id) {
                if (\defender::safe()) {
                    $status['response'] = 201;
                    parse_str(urldecode($_SESSION['form_cache'][$form_id][$form_type][$item_id]), $data);
                    // you need an unobstrusive method. do not update the table, but recall only. Show that there are some version, and whether they want to output.
                    //dbquery_insert($table, $data, 'update', ['keep_session'=>TRUE]);
                    unset($_SESSION['form_cache'][$form_id][$form_type][$item_id]);
                }
            }*/
        } else if ($_REQUEST['callback'] == 'read_cache') {
            $status['response'] = 600;
            if (!empty($_SESSION['form_cache'][$form_id][$form_type])) {
                parse_str(html_entity_decode($_SESSION['form_cache'][$form_id][$form_type][$item_id]), $status['data']);
                unset($status['data']['fusion_token']);
                $status['response'] = 200;
            }
        } else if ($_REQUEST['callback'] == 'cancel') {
            unset($_SESSION['form_cache'][$form_id][$form_type][$item_id]);
        }
    } else {
        $status['response'] = 501;
    }
}

header('Content-Type: application/json');

echo json_encode($status);
