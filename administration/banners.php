<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_banners.php
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
require_once __DIR__.'/../maincore.php';

if (!checkrights("SB") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
require_once INCLUDES."html_buttons_include.php";
include LOCALE.LOCALESET."admin/settings.php";

add_breadcrumb(array('link' => ADMIN.'banners.php'.$aidlink, 'title' => $locale['850']));

$message = '';
if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
	if ($_GET['error'] == 0) {
		$message = $locale['900'];
	} elseif ($_GET['error'] == 1) {
		$message = $locale['901'];
	} elseif ($_GET['error'] == 2) {
		$message = $locale['global_182'];
	}
	if (isset($message)) {
		echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n";
	}
}

if (isset($_POST['save_banners'])) {
    $error = 0;
    $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslash($_POST['sitebanner1'])."' WHERE settings_name='sitebanner1'");
    if (!$result) {
        $error = 1;
    }
    $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslash($_POST['sitebanner2'])."' WHERE settings_name='sitebanner2'");
    if (!$result) {
        $error = 1;
    }
    redirect(FUSION_SELF.$aidlink."&error=".$error, TRUE);
	
} else {

	if (isset($_POST['preview_banners'])) {
		$sitebanner1 = "";
		$sitebanner2 = "";
		$sitebanner1 = stripslash($_POST['sitebanner1']);
		$sitebanner2 = stripslash($_POST['sitebanner2']);
	} else {
		$sitebanner1 = stripslashes($settings['sitebanner1']);
		$sitebanner2 = stripslashes($settings['sitebanner2']);
	}

	opentable($locale['850']);
	echo "<div class='panel panel-default box-shadow' style='border:none;'>\n";
	echo "<div class='panel-body'>\n";
	echo "<h3 class='m-b-20'>".$locale['851']."</h3>\n";

	if (isset($_POST['preview_banners']) && $sitebanner1) {
		eval("?><div class='list-group-item m-b-10'>".$sitebanner1."</div><?php ");
	}

	echo "<form name='settingsform' method='post' action='".FUSION_REQUEST."'>\n";
	echo "<textarea name='sitebanner1' cols='50' rows='5' class='textbox' style='width:450px'>".phpentities($sitebanner1)."</textarea>\n";
	echo "<div class='list-group-item'><input type='button' value='<?php?>' class='button' style='width:60px;' onclick=\"addText('sitebanner1', '<?php\\n', '\\n?>', 'settingsform');\" />\n";
	echo display_html("settingsform", "sitebanner1", true)."\n";
	echo "</div>";
	echo "</div>";
	echo "</div>";

	echo "<div class='panel panel-default box-shadow' style='border:none;'>\n";
	echo "<div class='panel-body'>\n";
	echo "<h3 class='m-b-20'>".$locale['852']."</h3>\n";

	if (isset($_POST['preview_banners']) && $sitebanner2) {
		eval("?><div class='list-group-item  m-b-10'>".$sitebanner2."</div><?php ");
	}

	echo "<textarea name='sitebanner2' cols='50' rows='5' class='textbox' style='width:450px'>".phpentities($sitebanner2)."</textarea>\n";
	echo "<div class='list-group-item  m-b-10'><input type='button' value='<?php?>' class='button' style='width:60px;' onclick=\"addText('sitebanner2', '<?php\\n', '\\n?>', 'settingsform');\" />\n";
	echo display_html("settingsform", "sitebanner2", true)."\n";
	echo "</div>\n";

	echo "<div class='list-group-item'><input type='submit' name='save_banners' value='".$locale['854']."' class='btn-success m-r-10' />\n";
	echo "<input type='submit' name='preview_banners' value='".$locale['855']."' class='btn-success m-r-10' />\n";

	echo "</div>\n";
	echo "</div>\n";
	echo "</form>\n";
	closetable();

}
require_once THEMES."templates/footer.php";