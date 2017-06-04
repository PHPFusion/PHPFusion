<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/index.php
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
require_once INCLUDES."infusions_include.php";
require_once INFUSIONS.'forum/infusion_db.php';
require_once FORUM_CLASS."autoloader.php";
require_once INFUSIONS."forum/forum_include.php";
include INFUSIONS."forum/templates/forum_main.php";
// Base theme is 0.06s TOP
\PHPFusion\Locale::setLocale(FORUM_LOCALE);
$info = \PHPFusion\Forums\ForumServer::forum()->getForumInfo();
render_forum($info);
//showBenchmark(TRUE); //0.374
require_once THEMES."templates/footer.php";