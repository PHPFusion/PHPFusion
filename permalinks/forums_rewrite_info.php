<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: forums_rewrite_info.php
| Author: Chan (Frederick MC Chan)
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

$permalink_name = $locale['pl_forums_title'];
$permalink_desc = $locale['pl_forums_desc'];
$permalink_tags_desc = ["%forum_id%" => $locale['pl_tags_001'], "%forum_title%" => $locale['pl_tags_002']];
$permalink_author = "PHP-Fusion Dev Team";
$permalink_version = "1.0";
