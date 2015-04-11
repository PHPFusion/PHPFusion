<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/index.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

require_once __DIR__."/../maincore.php";
if (!db_exists(DB_FORUMS)) {
	$_GET['code'] = 404;
	require_once __DIR__.'/../error.php';
	exit;
}
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."forum.php";
require_once INCLUDES."forum_include.php";
include THEMES."templates/global/forum.index.php";

$forum = new PHPFusion\Forums\Forum();
$forum->set_ForumInfo();
$info = $forum->getForumInfo();
render_forum($info);
// temporary
require_once THEMES."templates/footer.php";