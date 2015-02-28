<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_info_panel.php
| Author: Nick Jones (Digitanium)
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

if (iMEMBER) {
	$msg_count = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'");

	openside($userdata['user_name']);
	echo THEME_BULLET." <a href='".BASEDIR."edit_profile.php' class='side'>".$locale['global_120']."</a><br />\n";
	echo THEME_BULLET." <a href='".BASEDIR."messages.php' class='side'>".$locale['global_121']."</a><br />\n";
	echo THEME_BULLET." <a href='".BASEDIR."members.php' class='side'>".$locale['global_122']."</a><br />\n";

	if (iADMIN && (iUSER_RIGHTS != "" || iUSER_RIGHTS != "C")) {
		echo THEME_BULLET." <a href='".ADMIN."index.php".$aidlink."' class='side'>".$locale['global_123']."</a><br />\n";
	}

	echo THEME_BULLET." <a href='".BASEDIR."index.php?logout=yes' class='side'>".$locale['global_124']."</a>\n";

	if ($msg_count) {
		echo "<div style='text-align:center;margin-top:15px;'>\n";
		echo "<strong><a href='".BASEDIR."messages.php' class='side'>".sprintf($locale['global_125'], $msg_count);
		echo ($msg_count == 1 ? $locale['global_126'] : $locale['global_127'])."</a></strong>\n";
		echo "</div>\n";
	}

	if (iADMIN && checkrights("SU")) {
		$subm_count = dbcount("(submit_id)", DB_SUBMISSIONS);

		if ($subm_count) {
			echo "<div style='text-align:center;margin-top:15px;'>\n";
			echo "<strong><a href='".ADMIN."submissions.php".$aidlink."' class='side'>".sprintf($locale['global_125'], $subm_count);
			echo ($subm_count == 1 ? $locale['global_128'] : $locale['global_129'])."</a></strong>\n";
			echo "</div>\n";
		}
	}
	closeside();
} else {
	if (!preg_match('/login.php/i',FUSION_SELF)) {
		$action_url = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
		if (isset($_GET['redirect']) && strstr($_GET['redirect'], "/")) {
			$action_url = cleanurl(urldecode($_GET['redirect']));
		}

		openside($locale['global_100']);
		echo "<div style='text-align:center'>\n";
		echo "<form name='loginform' method='post' action='".$action_url."'>\n";
		echo $locale['global_101']."<br />\n<input type='text' name='user_name' class='textbox' style='width:100px' /><br />\n";
		echo $locale['global_102']."<br />\n<input type='password' name='user_pass' class='textbox' style='width:100px' /><br />\n";
		echo "<label><input type='checkbox' name='remember_me' value='y' title='".$locale['global_103']."' style='vertical-align:middle;' /></label>\n";
		echo "<input type='submit' name='login' value='".$locale['global_104']."' class='button' /><br />\n";
		echo "</form>\n<br />\n";

		if ($settings['enable_registration']) {
			echo $locale['global_105']."<br /><br />\n";
		}
		echo $locale['global_106']."\n</div>\n";
		closeside();
	}
}
?>