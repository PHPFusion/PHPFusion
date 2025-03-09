<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: infusion_db.php
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
defined('IN_FUSION') || exit;

// Locales
define('SHOUTBOX_LOCALE', fusion_get_inf_locale_path('shoutbox.php', INFUSIONS.'shoutbox_panel/locale/'));

// Paths
const SHOUTBOX = INFUSIONS.'shoutbox_panel/';

// Database
const DB_SHOUTBOX = DB_PREFIX."shoutbox";

// Admin Settings
\PHPFusion\Admins::getInstance()->setAdminPageIcons("S", "<i class='admin-ico fa fa-fw fa-commenting'></i>");
