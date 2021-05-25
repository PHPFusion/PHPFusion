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

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/main.php');
$settings = fusion_get_settings();

if ($settings['update_checker'] == 1 && ($settings['update_last_checked'] < (time() - 21600))) { // check every 6 hours
    dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:time WHERE settings_name=:name", [':time' => time(), ':name' => 'update_last_checked']);

    $update = new PHPFusion\AutoUpdate();
    $url = $update->getUpdateUrl();
    $version = $update->checkUpdate(TRUE);

    if (!empty($version)) {
        if (version_compare($version, $settings['version'], '>')) {
            $result = sprintf($locale['new_update_avalaible'], $version);
            $result .= '<a class="btn btn-primary btn-sm m-l-10" href="'.ADMIN.'upgrade.php'.fusion_get_aidlink().'">'.$locale['update_now'].'</a>';

            header('Content-Type: application/json');
            echo json_encode(['result' => $result]);
        }
    }
}
