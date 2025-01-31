<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: upgrade.php
| Author: Core Development Team
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
pageaccess('U');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/upgrade.php');
$settings = fusion_get_settings();

add_to_jquery('$("#updatechecker_result").hide();');
add_to_footer('<script>let locale = '.json_encode($locale).'</script>');

add_breadcrumb(['link' => ADMIN.'upgrade.php'.fusion_get_aidlink(), 'title' => $locale['U_000']]);

opentable($locale['U_000']);

$update = new PHPFusion\Update();

echo '<div class="m-b-20">';
echo sprintf($locale['U_002'], showdate('longdate', $settings['update_last_checked']));
echo '<a href="#" id="forceupdate" class="m-l-10 btn btn-default"><i class="fas fa-sync fa-spin" style="display:none;"></i> '.$locale['U_003'].'</a>';

if (!check_get('updatelocales') && is_array($update->getEnabledLanguages())) {
    echo '<a class="btn btn-primary m-l-10" href="#" id="updatelocales"><i class="fas fa-sync fa-spin" style="display:none;"></i> '.$locale['U_016'].'</a>';
}
echo '</div>';

echo '<h3 class="strong m-b-20">'.sprintf($locale['U_019'], $settings['version']).'</h3>';

$update_result = $update->checkUpdate();

if ($update_result === FALSE) {
    echo '<h5 class="strong m-t-20">'.$locale['U_005'].'</h5>';
} else {
    if ($update->newVersionAvailable()) {
        echo '<div id="new_update_box">';
        echo alert($locale['U_001'], ['class' => 'alert-info']);
        echo '<h4 class="strong m-t-20">'.$locale['U_004'].'</h4>';
        echo '<p>'.sprintf($locale['U_007'], $update->getLatestVersion()).'</p>';
        echo '<a class="btn btn-primary" href="#" id="updatecore">'.$locale['update_now'].'</a>';
        echo '</div>';
    } else {
        echo '<p class="m-t-10">'.$locale['U_008'].'</p>';
    }
}

echo '<div class="m-t-20" id="update-results"></div>';

closetable();
require_once THEMES.'templates/footer.php';
