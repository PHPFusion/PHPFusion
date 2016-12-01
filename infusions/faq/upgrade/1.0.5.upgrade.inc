<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /faq/upgrade/1.0.5.upgrade.inc
| Upgrade FAQ to Version 9.0.2 (RC5)
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
$inf_version = '1.0.5';
require_once (INCLUDES.'infusions_include.php');
// Add multilingual support
if (!column_exists(DB_FAQ_CATS, 'faq_cat_language', FALSE)) {
    $inf_altertable[] = DB_FAQ_CATS." ADD faq_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER faq_cat_description";
}
$inf_updatedbrow[] = DB_ADMIN." SET admin_link='../infusions/faq/faq_admin.php' WHERE admin_link='faq.php'";