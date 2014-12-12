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
			vote_user MEDIUMINT(8) UNSIGNED NOT NUL