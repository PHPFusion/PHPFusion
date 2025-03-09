<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
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
defined("IN_FUSION") || exit;

/**
 * Cache all entries of a form to session
 */
function ajax_cache_update() {
    $status['response'] = 400;

    if (isset($_REQUEST['form_id'])
        && isset($_REQUEST['fusion_token'])
        && isset($_REQUEST['form_type'])
        && isset($_REQUEST['fields'])
        && isset($_REQUEST['aidlink'])
        && isset($_REQUEST['callback'])
        && isset($_REQUEST['item_id']) && isnum($_REQUEST['item_id'])
    ) {

        $form_id = filter_var($_REQUEST['form_id'], FILTER_UNSAFE_RAW);
        $form_type = filter_var($_REQUEST['form_type'], FILTER_UNSAFE_RAW);
        $item_id = filter_var($_REQUEST['item_id'], FILTER_UNSAFE_RAW);

        if (iADMIN && fusion_get_aidlink() == $_REQUEST['aidlink']) {
            if ($_REQUEST['callback'] == 'set_cache') {
                $status['response'] = 200;
                $_SESSION['form_cache'][$form_id][$form_type][$item_id] = form_sanitizer($_REQUEST['fields']);
                /*if ($item_id) {
                    if (fusion_safe()) {
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
}

/**
 * @uses ajax_cache_update()
 */
fusion_add_hook('fusion_admin_hooks', 'ajax_cache_update');
