<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: update_core.php
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

/**
 * Ajax update checker
 */
function ajax_update_core() {
    $update = new PHPFusion\Update();
    $result = [];

    if (get('step')) {
        switch (get('step')) {
            case 'update_langs':
                $update->updateLanguages();
                break;
            case 'update_core':
                $update->checkUpdate();
                $update->upgradeCms();
                break;
            default:
                break;
        }
    }

    if (!empty($update->getMessages())) {
        $messages = '';
        foreach ($update->getMessages() as $message) {
            $messages .= $message.'<br>';
        }

        $result = ['result' => $messages];
    }

    header('Content-Type: application/json');
    echo json_encode($result);
}

/**
 * @uses ajax_update_core()
 */
fusion_add_hook('fusion_admin_hooks', 'ajax_update_core');
