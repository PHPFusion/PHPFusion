<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_input.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

function postform($info) {
	global $locale;
	add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."forum/templates/css/forum.css'>");
	echo render_breadcrumbs();
	opentable($info['title']);
	// New template
	echo "<!--pre_form-->\n";
	echo "<h4 class='m-b-20'>".$info['description']."</h4>\n";
	echo $info['openform'];
	echo $info['subject_field'];
	echo "<hr/>\n";
	echo $info['message_field'];
	echo $info['edit_reason_field'];
	echo $info['forum_id_field'];
	echo $info['thread_id_field'];

	echo $info['poll_form'];

	$tab_title['title'][0] = $locale['forum_0602'];
	$tab_title['id'][0] = 'postopts';
	$tab_title['icon'][0] = '';

	$tab_active = tab_active($tab_title, 0);

	$tab_content = opentabbody($tab_title['title'][0], 'postopts', $tab_active); // first one is guaranteed to be available
	$tab_content .= "<div class='well m-t-20'>\n";
	$tab_content .= $info['delete_field'];
	$tab_content .= $info['sticky_field'];
	$tab_content .= $info['notify_field'];
	$tab_content .= $info['lock_field'];
	$tab_content .= $info['hide_edit_field'];
	$tab_content .= $info['smileys_field'];
	$tab_content .= $info['signature_field'];
	$tab_content .= "</div>\n";
	$tab_content .= closetabbody();

	if (!empty($info['attachment_field'])) {
		$tab_title['title'][1] = $locale['forum_0557'];
		$tab_title['id'][1] = 'attach_tab';
		$tab_title['icon'][1] = '';
		$tab_content .= opentabbody($tab_title['title'][1], 'attach_tab', $tab_active);
		$tab_content .= "<div class='well m-t-20'>\n".$info['attachment_field']."</div>\n";
		$tab_content .= closetabbody();
	}

	echo opentab($tab_title, $tab_active, 'newthreadopts');
	echo $tab_content;
	echo closetab();

	echo $info['post_buttons'];
	echo $info['closeform'];

	echo "<!--end_form-->\n";
	closetable();
	if (!empty($info['last_posts_reply'])) {
		echo "<div class='well m-t-20'>\n";
		echo $info['last_posts_reply'];
		echo "</div>\n";
	}
}

function pollform($info) {
	echo render_breadcrumbs();
	opentable($info['title']);
	echo "<h4 class='m-b-20'>".$info['description']."</h4>\n";
	echo "<!--pre_form-->\n";
	echo $info['field']['openform'];
	echo $info['field']['poll_field'];
	echo $info['field']['poll_button'];
	echo $info['field']['closeform'];
	closetable();
}