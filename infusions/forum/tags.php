<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: forum/tags.php
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

use PHPFusion\Infusions\Forum\Classes\ForumServer;

require_once __DIR__.'/../../maincore.php';
if (!defined('FORUM_EXIST')) {
    redirect(BASEDIR."error.php?code=404");
}
require_once THEMES."templates/header.php";
require_once INFUSIONS."forum/forum_include.php";
require_once INCLUDES."infusions_include.php";
require_once INFUSIONS."forum/templates.php";
fusion_get_locale("", [FORUM_LOCALE, FORUM_TAGS_LOCALE]);
$info = ForumServer::Tag(TRUE, TRUE)->get_TagInfo();
echo display_forum_tags($info);
require_once THEMES."templates/footer.php";
