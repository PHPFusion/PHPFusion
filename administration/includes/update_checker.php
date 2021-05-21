<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: update_checker.php
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
defined('IN_FUSION') || exit;

$settings = fusion_get_settings();
$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/main.php');

if ($settings['update_checker'] == 1) {
    $update = new PHPFusion\AutoUpdate();
    $url = $update->getUpdateUrl().$update->getUpdateFile();
    $versions = $update->checkUpdate(10, TRUE);
    $updates = [];

    if (!empty($versions)) {
        foreach ($versions as $data) {
            if (version_compare($data['version'], $settings['version'], '>')) {
                $result = str_replace(
                    ['[LINK]', '[/LINK]', '[VERSION]'],
                    ['<a href="'.$data['url'].'" target="_blank">', '</a>', $data['version']],
                    $locale['new_update_avalaible']
                );

                $result .= '<a class="btn btn-primary" href="'.ADMIN.'upgrade.php'.fusion_get_aidlink().'">'.$locale['upgrade_now'].'</a>';

                header('Content-Type: application/json');
                echo json_encode(['result' => $result]);
            }
        }
    }
}
