<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: upgrade.php
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
require_once "../maincore.php";

if (!checkrights("U") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
if (file_exists(LOCALE.LOCALESET."admin/upgrade.php")) {
	include LOCALE.LOCALESET."admin/upgrade.php";
} else {
	include LOCALE."English/admin/upgrade.php";
}

opentable($locale['400']);
echo "<div style='text-align:center'><br />\n";
echo "<form name='upgradeform' method='post' action='".FUSION_SELF.$aidlink."'>\n";

if (str_replace(".", "", $settings['version']) < "70207") {
	if (!isset($_POST['stage'])) {
		echo sprintf($locale['500'], $locale['504'])."<br />\n".$locale['501']."<br /><br />\n";
		echo "<input type='hidden' name='stage' value='2'>\n";
		echo "<input type='submit' name='upgrade' value='".$locale['400']."' class='button'><br /><br />\n";
	} elseif (isset($_POST['upgrade']) && isset($_POST['stage']) && $_POST['stage'] == 2) {
//Check files from earlier installations
	echo "<div style='width:550px; margin:15px auto;' class='tbl'>\n";
	echo "File check, please save and remove according to list.<br />\n";
	echo "<div class='tbl-border' style='margin-top:10px; padding: 5px; text-align:left;'>";

	if (file_exists(ADMIN."settings_links.php")) { echo "<span style='color:red;'>administration/settings_links.php </span> need to be deleted<br />"; }
	if (file_exists(ADMIN."shoutbox.php")) { echo "<span style='color:red;'>administration/shoutbox.php </span> need to be deleted<br />"; }
	if (file_exists(ADMIN."updateuser.php")) { echo "<span style='color:red;'>administration/updateuser.php </span> need to be deleted<br />"; }
	if (file_exists(IMAGES."edit.gif")) { echo "<span style='color:red;'>images/edit.gif </span> need to be deleted<br />"; }
	if (file_exists(IMAGES."star.gif")) { echo "<span style='color:red;'>images/star.gif </span> need to be deleted<br />"; }
	if (file_exists(IMAGES."tick.gif")) { echo "<span style='color:red;'>images/tick.gif </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."jscripts/tiny_mce/langs/hu.js")) { echo "<span style='color:red;'>includes/jscripts/tiny_mce/langs/hu.js </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/compat2x/editor_plugin.js")) { echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/compat2x/editor_plugin.js </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/compat2x/editor_plugin_src.js")) { echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/compat2x/editor_plugin_src.js </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/paste/css/blank.css")) { echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/paste/css/blank.css </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/paste/css/pasteword.css")) { echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/paste/css/pasteword.css </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/paste/blank.htm")) { echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/paste/blank.htm </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/safari/blank.htm")) { echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/safari/blank.htm </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/safari/editor_plugin.js")) { echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/safari/editor_plugin.js </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/safari/editor_plugin_src.js")) { echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/safari/editor_plugin_src.js </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."jscripts/tiny_mce/plugins/xhtmlxtras/css/xhtmlxtras.css")) { echo "<span style='color:red;'>includes/jscripts/tiny_mce/plugins/xhtmlxtras/css/xhtmlxtras.css </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."jscripts/tiny_mce/utils/mclayer.js")) { echo "<span style='color:red;'>includes/jscripts/tiny_mce/utils/mclayer.js </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."securimage/index.php")) { echo "<span style='color:red;'>The folder includes/securimage and it´s content </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."captcha_include.php")) { echo "<span style='color:red;'>includes/captcha_include.php </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."jquery.js")) { echo "<span style='color:red;'>includes/jquery.js </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."phpmailer_include.php")) { echo "<span style='color:red;'>includes/phpmailer_include.php </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."smtp_include.php")) { echo "<span style='color:red;'>includes/smtp_include.php </span> need to be deleted<br />"; }
	if (file_exists(INCLUDES."update_profile_include.php")) { echo "<span style='color:red;'>includes/update_profile_include.php </span> need to be deleted<br />"; }
	if (file_exists(INFUSIONS."navigation_panel/index.php")) { echo "<span style='color:red;'>The folder infusions/navigation_panel </span> need to be deleted <br /> (<STRONG>Check so you use the new one before!</STRONG>)<br />"; }
	if (file_exists(LOCALE."English/admin/shoutbox.php")) { echo "<span style='color:red;'>locale/English/admin/shoutbox.php </span> need to be deleted<br />"; }
	if (file_exists(LOCALE."English/edit_profile.php")) { echo "<span style='color:red;'>locale/English/edit_profile.php </span> need to be deleted<br />"; }
	if (file_exists(LOCALE."English/register.php")) { echo "<span style='color:red;'>locale/English/register.php </span> need to be deleted<br />"; }
	if (file_exists(LOCALE."English/view_profile.php")) { echo "<span style='color:red;'>locale/English/view_profile.php </span> need to be deleted<br />"; }

	echo "</div></div>";

		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='7.02.07' WHERE settings_name='version'");
		echo $locale['502']."<br /><br />\n";
	}
} else {
	echo $locale['401']."<br /><br />\n";
}

echo "</form>\n</div>\n";
closetable();

require_once THEMES."templates/footer.php";
?>