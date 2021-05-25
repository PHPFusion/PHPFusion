<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: upgrade.php
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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageAccess('U');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/upgrade.php');
$settings = fusion_get_settings();

add_breadcrumb(['link' => ADMIN.'upgrade.php'.fusion_get_aidlink(), 'title' => $locale['U_000']]);

opentable($locale['U_000']);

echo '<div class="m-b-20">';
echo sprintf($locale['U_002'], showdate('longdate', $settings['update_last_checked']));
echo '<a href="'.ADMIN.'upgrade.php'.fusion_get_aidlink().'&force=true" class="m-l-10 btn btn-default">'.$locale['U_003'].'</a>';
echo '</div>';

$update = new PHPFusion\AutoUpdate();

$update_result = $update->checkUpdate();

if ($update_result === FALSE) {
    echo '<h5 class="strong m-t-20">'.$locale['U_005'].'</h5>';
}

if (check_get('force')) {
    dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:time WHERE settings_name=:name", [':time' => time(), ':name' => 'update_last_checked']);

    if ($update_result === NULL) {
        echo '<h5 class="strong m-t-20">'.$locale['U_006'].'</h5>';
    }
}

if ($update->newVersionAvailable()) {
    if (check_get('proceed')) {
        $update->upgradeCms();
    } else {
        echo alert($locale['U_001'], ['class' => 'alert-info']);
        echo '<h4 class="strong m-t-20">'.$locale['U_004'].'</h4>';
        echo '<p>'.sprintf($locale['U_007'], $update->getLatestVersion()).'</p>';
        echo '<a class="btn btn-primary" href="'.ADMIN.'upgrade.php'.fusion_get_aidlink().'&proceed">'.$locale['update_now'].'</a>';
    }
} else {
    echo '<p class="m-t-10">'.$locale['U_008'].'</p>';
}

if (!empty($update->getMessages())) {
    echo '<div>';
    foreach ($update->getMessages() as $message) {
        echo $message.'<br>';
    }
    echo '</div>';
}

closetable();
require_once THEMES.'templates/footer.php';
