<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: available_languages.php
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
function ajax_available_languages() {
    $update = new PHPFusion\Update();
    $list = $update->getAvailableLanguages();

    $langs_temp = [];
    foreach ($list as $lang) {
        $langs_temp[$lang] = $lang;
    }
    $langs_temp = array_diff_key($langs_temp, array_flip(makefilelist(LOCALE, '|.|..', TRUE, 'folders')));

    $langs = [];
    foreach ($langs_temp as $lang) {
        $langs[] = ['id' => $lang, 'text' => str_replace('_', ' ', $lang)];
    }

    header('Content-Type: application/json');
    echo json_encode($langs);
}

/**
 * @uses ajax_available_languages()
 */
fusion_add_hook('fusion_admin_hooks', 'ajax_available_languages');
