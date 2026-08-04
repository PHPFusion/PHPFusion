<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: index.php
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

use PHPFusion\AdminDashboard\DashboardManager;

require_once __DIR__ . '/../maincore.php';

if (!iADMIN || fusion_get_userdata('user_rights') == '' || !defined('iAUTH')) {
    redirect('../index.php');
}

$requestAid = check_get('aid') ? (string)get('aid') : '';
if ($requestAid === '' || !hash_equals(iAUTH, $requestAid)) {
    redirect(fusion_get_current_aid_url(), FALSE, FALSE, 302);
}

require_once THEMES . 'templates/admin_header.php';

echo DashboardManager::create()->render();

require_once THEMES . 'templates/footer.php';
