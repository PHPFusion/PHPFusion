<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: upgrade.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
pageAccess("U");

require_once THEMES."templates/admin_header.php";

$settings = fusion_get_settings();

include LOCALE.LOCALESET."admin/upgrade.php";

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'upgrade.php'.fusion_get_aidlink(), 'title' => $locale['U_0000']]);

opentable($locale['U_0000']);

echo '<div class="well text-center">'.$locale['U_0001'].'</div>';
$check_array = [
    'UserName_ban'                  => '',
];
foreach ($check_array as $name => $val) {
    if (!dbcount("(settings_name)", DB_SETTINGS, "settings_name=:col_name", [':col_name' => $name])) {
		dbquery("INSERT INTO ".DB_SETTINGS." (settings_name, settings_value) VALUES ('UserName_ban', '')");
    }
}

closetable();
require_once THEMES."templates/footer.php";
