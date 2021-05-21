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
pageAccess("U");

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/upgrade.php');
$settings = fusion_get_settings();

add_breadcrumb(['link' => ADMIN.'upgrade.php'.fusion_get_aidlink(), 'title' => $locale['U_0000']]);

opentable($locale['U_0000']);

//$update = new PHPFusion\AutoUpdate(BASEDIR.'temp/', BASEDIR, 60); // production
$update = new PHPFusion\AutoUpdate(BASEDIR.'temp/', BASEDIR.'test/', 60);
$update->setCurrentVersion($settings['version']);

//$update->setUpdateUrl('http://localhost/update/');

// Check for a new update
if ($update->checkUpdate() === FALSE) {
    echo 'Could not check for updates!';
}

if ($update->newVersionAvailable()) {
    // Install new update
    echo 'New Version: '.$update->getLatestVersion().'<br>';

    $result = $update->update();

    if ($result === TRUE) {
        if (isset($_GET['proceed'])) {
            $update->doUpdate();
        } else {
            echo '<a class="btn btn-primary" href="'.ADMIN.'upgrade.php'.fusion_get_aidlink().'&proceed">Proceed</a>';
        }
    }
} else {
    echo 'Current Version is up to date<br>';
}

echo '<br><br>Log:<br>';
echo $update->getMessage();

closetable();
require_once THEMES.'templates/footer.php';
