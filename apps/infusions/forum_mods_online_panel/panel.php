<?php

/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: infusion.php
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

$locale = fusion_get_locale('', [ INFUSIONS . 'forum_mods_online_panel/locale/' . LANGUAGE . '.php' ]);

// Infusion general information
$inf_title = $locale['fmp_0001'];
$inf_description = $locale['fmp_0002'];
$inf_version = '1.0.0';
$inf_developer = 'PHP Fusion Development Team';
$inf_email = 'info@phpfusion.com';
$inf_weburl = 'https://phpfusion.com';
$inf_folder = 'forum';
$inf_image = 'forum.png';
