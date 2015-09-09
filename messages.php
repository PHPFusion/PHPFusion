<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: messages.php
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
require_once "maincore.php";
if (!iMEMBER) {	redirect("index.php"); }
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."messages.php";
include THEMES."templates/global/messages.php";

if (!isset($userdata['user_inbox'])) {
	if (isset($_POST['upgrade'])) {
		include_once "administration/upgrade/upgrade-7.02-9.00.php";
		upgrade_private_message();
		echo "<div class='jumbotron'>\n";
		echo "<h1>Thanks, have fun. I will keep this in for 7 days.</h1>";
		echo "<p><strong>Now your render time should be about a twilight faster.</strong></p>";
		echo "</div>\n";
	} else {
		echo "<div class='jumbotron'>\n";
		echo "<h1>Dear beta testers, upgrade is available for Private Message.</h1>";
		echo "<em>If you don't, user info panel will flood you with beautifully done SQL/PDO errors.</em>";
		echo "<p><strong>Press `the upgrade button` to upgrade</strong></p>";
		echo openform("upgrade", "post", FUSION_SELF);
		echo form_button("upgrade", "The upgrade button", "upgrade", array("class"=>"btn-success btn-lg"));
		echo closeform();
		echo "</div>\n";
	}
} else {
	// New Private Message
	$message = new \PHPFusion\PrivateMessages();
	$message->display_inbox();
	display_inbox($message->getInfo());
}
require_once THEMES."templates/footer.php";