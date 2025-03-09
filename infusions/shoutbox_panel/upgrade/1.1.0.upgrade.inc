<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 1.1.0.upgrade.inc
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
$inf_version = '1.1.0';

if (!column_exists(DB_SHOUTBOX, 'shout_language')) {
    $column_add = DB_SHOUTBOX." ADD shout_language VARCHAR(50) NOT NULL DEFAULT '".fusion_get_settings('locale')."' AFTER shout_ip_type";
    dbquery("ALTER TABLE ".$column_add);
}

$inf_updatedbrow[] = DB_ADMIN." SET admin_image='../infusions/shoutbox_panel/shouts.svg', admin_page='5' WHERE admin_link='../infusions/shoutbox_panel/shoutbox_admin.php'";
