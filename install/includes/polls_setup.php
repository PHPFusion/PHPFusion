<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: includes/polls_setup.php
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
if (isset($_POST['uninstall'])) {
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."poll_votes");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."polls");
	$result = dbquery("DELETE FROM ".$db_prefix."admin WHERE admin_rights='PO'");
	$result = dbquery("DELETE FROM ".$db_prefix."panels WHERE panel_filename='member_poll_panel'");
} else {
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."poll_votes");
	$result = dbquery("DROP TABLE IF EXISTS ".$db_prefix."polls");
	$result = dbquery("CREATE TABLE ".$db_prefix."poll_votes (
			vote_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			vote_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			vote_opt SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0',
			poll_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (vote_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	$result = dbquery("CREATE TABLE ".$db_prefix."polls (
			poll_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
			poll_title VARCHAR(200) NOT NULL DEFAULT '',
			poll_opt_0 VARCHAR(200) NOT NULL DEFAULT '',
			poll_opt_1 VARCHAR(200) NOT NULL DEFAULT '',
			poll_opt_2 VARCHAR(200) NOT NULL DEFAULT '',
			poll_opt_3 VARCHAR(200) NOT NULL DEFAULT '',
			poll_opt_4 VARCHAR(200) NOT NULL DEFAULT '',
			poll_opt_5 VARCHAR(200) NOT NULL DEFAULT '',
			poll_opt_6 VARCHAR(200) NOT NULL DEFAULT '',
			poll_opt_7 VARCHAR(200) NOT NULL DEFAULT '',
			poll_opt_8 VARCHAR(200) NOT NULL DEFAULT '',
			poll_opt_9 VARCHAR(200) NOT NULL DEFAULT '',
			poll_started INT(10) UNSIGNED NOT NULL DEFAULT '0',
			poll_ended INT(10) UNSIGNED NOT NULL DEFAULT '0',
			poll_language VARCHAR(50) NOT NULL DEFAULT '".$_POST['localeset']."',
			PRIMARY KEY (poll_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
	if (!$result) {
		$fail = TRUE;
	}
	$result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('PO', 'polls.gif', '".$locale['102']."', 'polls.php', '1')");
	// panel
	$result = dbquery("INSERT INTO ".$db_prefix."panels (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list) VALUES ('".$locale['167']."', 'member_poll_panel', '', '4', '2', 'file', '0', '0', '0', '')");
}
?>