<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: post.php
| Author: Nick Jones (Digitanium)
| Co-author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
/**
 * Now just post new thread
 */
//require_once ($settings['site_seo'] == "1" ? "" : "../../")."maincore.php";
require_once __DIR__."/../../maincore.php";
if (!db_exists(DB_FORUMS)) {
	$_GET['code'] = 404;
	require_once BASEDIR.'error.php';
	exit;
}
require_once THEMES."templates/header.php";
include INFUSIONS."forum/locale/".LOCALESET."forum.php";
add_to_title($locale['global_204']);
require_once INCLUDES."mimetypes_include.php";
require_once INCLUDES."bbcode_include.php";
require_once INFUSIONS."forum/classes/functions.php";
require_once INFUSIONS."forum/forum_include.php";
require_once INFUSIONS."forum/templates/forum_input.php";

if (iMEMBER && PHPFusion\Forums\Functions::verify_forum($_GET['forum_id'])) {
	// yield forum_id and forum_id before that
	$info = dbarray(dbquery("SELECT f.*, f2.forum_name AS forum_cat_name
						FROM ".DB_FORUMS." f
						LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
						WHERE f.forum_id='".intval($_GET['forum_id'])."'
						AND ".groupaccess('f.forum_access')."
					")
			);
	if ($info['forum_type'] == 1) redirect("index.php");

	define_forum_mods($info);

	$info['lock_edit'] = $settings['forum_edit_lock'] == 1 ? TRUE : FALSE;
	add_breadcrumb(array('link'=>FORUM.'index.php', 'title'=>$locale['forum_0000']));
	add_breadcrumb(array('link'=>FORUM.'index.php?viewforum&amp;forum_id='.$info['forum_id'].'&amp;parent_id='.$info['forum_cat'], 'title'=>$info['forum_name']));
	add_breadcrumb(array('link'=>FORUM.'index.php?viewforum&amp;forum_id='.$info['forum_id'].'&amp;parent_id='.$info['forum_cat'], 'title'=>'New Thread'));
	$data['new'] = 1;
	if (isset($_POST['postnewthread']) ||
		isset($_POST['previewpost']) ||
		isset($_POST['add_poll_option']) ||
		isset($_POST['delete_poll_option']) ||
		isset($_POST['delete_poll']) ||
		isset($_POST['update_poll_option']) ||
		isset($_POST['update_poll_title'])
	) {
		include "post_actions.php";
	}
	postform($data, $info);
} else {
	redirect('index.php');
}

require_once THEMES."templates/footer.php";