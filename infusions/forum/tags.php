<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/tags.php
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
require_once dirname(__FILE__).'/../../maincore.php';
if (!infusion_exists('forum')) {
    redirect(BASEDIR."error.php?code=404");
}
require_once THEMES."templates/header.php";
require_once "infusion_db.php";
require_once FORUM_CLASS."autoloader.php";
require_once INFUSIONS."forum/forum_include.php";
require_once INCLUDES."infusions_include.php";
require_once INFUSIONS."forum/templates.php";

$info = \PHPFusion\Forums\ForumServer::Tag(TRUE, TRUE)->get_TagInfo();
display_forum_tags($info);

require_once THEMES."templates/footer.php";