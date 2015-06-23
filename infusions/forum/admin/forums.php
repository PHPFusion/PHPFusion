<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forums.php
| Author: PHP-Fusion Inc.
| Co-Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__."/../../../maincore.php";
if (!db_exists(DB_FORUMS)) {
	$_GET['code'] = 404;
	require_once BASEDIR.'error.php';
	exit;
}
pageAccess('F');

require_once THEMES."templates/admin_header.php";
include INFUSIONS."forum/locale/".LOCALESET."forum_admin.php";
include LOCALE.LOCALESET."admin/settings.php";
include INFUSIONS."forum/locale/".LOCALESET."forum_ranks.php";
require_once INFUSIONS."forum/classes/Admin.php";
require_once INFUSIONS."forum/classes/Functions.php";
require_once INCLUDES.'infusions_include.php';
$forum_settings = get_settings('forum');
$forum_admin = new PHPFusion\Forums\Admin;
// want to tab , do it here.
$tab_title['title'][] = $locale['forum_admin_000'];
$tab_title['id'][] = 'fm';
$tab_title['icon'][] = '';
$tab_title['title'][] = $locale['forum_admin_001'];
$tab_title['id'][] = 'fr';
$tab_title['icon'][] = '';
$tab_title['title'][] = $locale['forum_admin_002'];
$tab_title['id'][] = 'fs';
$tab_title['icon'][] = '';
$tab_active = tab_active($tab_title, isset($_GET['section']) ? $_GET['section'] : 'fm', true);
echo opentab($tab_title, $tab_active, 'fmm', true);
if (isset($_GET['section'])) {
	switch($_GET['section']) {
		case 'fr':
			pageAccess('FR');
			add_breadcrumb(array('link'=>ADMIN.'administrators.php'.$aidlink, 'title'=>$locale['404']));
			echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active, true, 'section');
			include INFUSIONS.'forum/admin/forum_ranks.php';
			echo closetabbody();
			break;
		case 'fs':
			pageAccess('F');
			echo opentabbody($tab_title['title'][2], $tab_title['id'][2], $tab_active, true, 'section');
			include INFUSIONS.'forum/admin/settings_forum.php';
			echo closetabbody();
			break;
		default :
			redirect(INFUSIONS.'forum/admin/forums.php'.$aidlink);
	}
} else {
	pageAccess('F');
	add_breadcrumb(array('link'=>INFUSIONS.'forum/admin/forums.php'.$aidlink, 'title'=>$locale['forum_admin_000']));
	echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active, true, 'section');
	$forum_admin->display_forum_admin();
	echo closetabbody();
}
echo closetab();
require_once THEMES."templates/footer.php";