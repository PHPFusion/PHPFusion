<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_main.php
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

if (!checkrights("S1") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";

if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
	if ($_GET['error'] == 0) {
		$message = $locale['900'];
	} elseif ($_GET['error'] == 1) {
		$message = $locale['901'];
	} elseif ($_GET['error'] == 2) {
		$message = $locale['902'];
	}
	if (isset($message)) {
		echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n";
	}
}

if (isset($_POST['savesettings'])) {
	$error = 0;
	$siteintro = descript(stripslash($_POST['intro']));
	$sitefooter = descript(stripslash($_POST['footer']));
	$localeset = stripinput($_POST['localeset']);
	$old_localeset = stripinput($_POST['old_localeset']);
	$site_host = ""; $site_path = "/"; $site_protocol = "http"; $site_port = "";

	if (in_array($_POST['site_protocol'], array("http", "https"))) {
		$site_protocol = $_POST['site_protocol'];
	}

	if ($_POST['site_host'] && $_POST['site_host'] != "/") {
		$site_host = stripinput($_POST['site_host']);
		if (strpos($site_host, "/") !== false) {
			$site_host = explode("/", $site_host, 2);
			if ($site_host[1] != "") {
				$site_path = "/".$site_host[1];
			}
			$site_host = $site_host[0];
		}
	} else {
		redirect(FUSION_SELF.$aidlink."&error=2");
	}

	if (($_POST['site_path'] && $_POST['site_path'] != "/") || $site_path != "/") {
		if ($site_path == "/") { $site_path = stripinput($_POST['site_path']); }
		$site_path = (substr($site_path, 0, 1) != "/" ? "/" : "").$site_path.(strrchr($site_path,"/") != "/" ? "/" : "");
	}

	if ((isnum($_POST['site_port']) || $_POST['site_port'] == "") && !in_array($_POST['site_port'], array(0, 80, 443))) {
		$site_port = $_POST['site_port'];
	}

	$siteurl = $site_protocol."://".$site_host.($site_port ? ":".$site_port : "").$site_path;

	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['sitename'])."' WHERE settings_name='sitename'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['sitebanner'])."' WHERE settings_name='sitebanner'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['siteemail'])."' WHERE settings_name='siteemail'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['username'])."' WHERE settings_name='siteusername'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$site_protocol."' WHERE settings_name='site_protocol'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$site_host."' WHERE settings_name='site_host'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$site_path."' WHERE settings_name='site_path'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$site_port."' WHERE settings_name='site_port'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$siteurl."' WHERE settings_name='siteurl'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslashes(addslashes($siteintro))."' WHERE settings_name='siteintro'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['description'])."' WHERE settings_name='description'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['keywords'])."' WHERE settings_name='keywords'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslashes(addslashes($sitefooter))."' WHERE settings_name='footer'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['opening_page'])."' WHERE settings_name='opening_page'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$localeset' WHERE settings_name='locale'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['theme'])."' WHERE settings_name='theme'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['default_search'])."' WHERE settings_name='default_search'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['exclude_left'])."' WHERE settings_name='exclude_left'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['exclude_upper'])."' WHERE settings_name='exclude_upper'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['exclude_lower'])."' WHERE settings_name='exclude_lower'");
	if (!$result) { $error = 1; }
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['exclude_right'])."' WHERE settings_name='exclude_right'");
	if (!$result) { $error = 1; }

	if (($localeset != $old_localeset) && !$error) {
		include LOCALE.$localeset."/admin/main.php";
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['201']."' WHERE admin_link='administrators.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['248']."' WHERE admin_link='admin_reset.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['202']."' WHERE admin_link='article_cats.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['203']."' WHERE admin_link='articles.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['236']."' WHERE admin_link='bbcodes.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['204']."' WHERE admin_link='blacklist.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['245']."' WHERE admin_link='banners.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['206']."' WHERE admin_link='custom_pages.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['249']."' WHERE admin_link='errors.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['207']."' WHERE admin_link='db_backup.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['208']."' WHERE admin_link='download_cats.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['209']."' WHERE admin_link='downloads.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['210']."' WHERE admin_link='faq.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['211']."' WHERE admin_link='forums.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['212']."' WHERE admin_link='images.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['213']."' WHERE admin_link='infusions.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['215']."' WHERE admin_link='members.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['216']."' WHERE admin_link='news.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['235']."' WHERE admin_link='news_cats.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['217']."' WHERE admin_link='panels.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['218']."' WHERE admin_link='photoalbums.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['219']."' WHERE admin_link='phpinfo.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['220']."' WHERE admin_link='polls.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['222']."' WHERE admin_link='site_links.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['223']."' WHERE admin_link='submissions.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['224']."' WHERE admin_link='upgrade.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['225']."' WHERE admin_link='user_groups.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['226']."' WHERE admin_link='weblink_cats.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['227']."' WHERE admin_link='weblinks.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['228']."' WHERE admin_link='settings_main.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['229']."' WHERE admin_link='settings_time.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['230']."' WHERE admin_link='settings_forum.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['231']."' WHERE admin_link='settings_registration.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['232']."' WHERE admin_link='settings_photo.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['233']."' WHERE admin_link='settings_misc.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['234']."' WHERE admin_link='settings_messages.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['237']."' WHERE admin_link='smileys.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['238']."' WHERE admin_link='user_fields.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['239']."' WHERE admin_link='forum_ranks.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['240']."' WHERE admin_link='user_field_cats.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['241']."' WHERE admin_link='settings_news.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['243']."' WHERE admin_link='settings_dl.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['244']."' WHERE admin_link='settings_ipp.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['246']."' WHERE admin_link='settings_security.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['247']."' WHERE admin_link='settings_users.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['266']."' WHERE admin_link='user_log.php'");
		if (!$result) { $error = 1; }
		$result = dbquery("UPDATE ".DB_ADMIN." SET admin_title='".$locale['267']."' WHERE admin_link='robots.php'");
		if (!$result) { $error = 1; }
	}
	redirect(FUSION_SELF.$aidlink."&error=".$error);
}

$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}
$theme_files = makefilelist(THEMES, ".|..|templates", true, "folders");
$locale_files = makefilelist(LOCALE, ".|..", true, "folders");

ob_start();
opentable($locale['400']);
echo "<form name='settingsform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
echo "<table cellpadding='0' cellspacing='0' width='600' class='center'>\n<tr>\n";
echo "<td width='30%' class='tbl'>".$locale['402']."</td>\n";
echo "<td width='70%' class='tbl'><input type='text' name='sitename' value='".$settings2['sitename']."' maxlength='255' class='textbox' style='width:230px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='30%' class='tbl'>".$locale['404']."</td>\n";
echo "<td width='70%' class='tbl'><input type='text' name='sitebanner' value='".$settings2['sitebanner']."' maxlength='255' class='textbox' style='width:230px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='30%' class='tbl'>".$locale['405']."</td>\n";
echo "<td width='70%' class='tbl'><input type='text' name='siteemail' value='".$settings2['siteemail']."' maxlength='128' class='textbox' style='width:230px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='30%' class='tbl'>".$locale['406']."</td>\n";
echo "<td width='70%' class='tbl'><input type='text' name='username' value='".$settings2['siteusername']."' maxlength='32' class='textbox' style='width:230px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'><strong>".$locale['425']."</strong></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='30%' class='tbl'>".$locale['426']."</td>\n";
echo "<td width='70%' class='tbl'><select id='site_protocol' name='site_protocol' class='textbox' style='width:100px;'>\n";
echo "<option".($settings2['site_protocol'] == "http" ? " selected='selected'" : "").">http</option>\n";
echo "<option".($settings2['site_protocol'] == "https" ? " selected='selected'" : "").">https</option>\n";
echo "</select>\n</td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='30%' class='tbl' valign='top'>".$locale['427']."<br /><span class='small2'>".$locale['428']." ".$locale['433']."</span></td>\n";
echo "<td width='70%' class='tbl' valign='top'><input type='text' id='site_host' name='site_host' value='".$settings2['site_host']."' maxlength='255' class='textbox' style='width:230px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='30%' class='tbl' valign='top'>".$locale['429']."<br /><span class='small2'>".$locale['428']." /".$locale['434']."/</span></td>\n";
echo "<td width='70%' class='tbl' valign='top'><input type='text' id='site_path' name='site_path' value='".$settings2['site_path']."' maxlength='255' class='textbox' style='width:230px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='30%' class='tbl' valign='top'>".$locale['430']."<br /><span class='small2'>".$locale['408']."</span></td>\n";
echo "<td width='70%' class='tbl' valign='top'><input type='text' id='site_port' name='site_port' value='".$settings2['site_port']."' maxlength='4' class='textbox' style='width:100px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='30%' class='tbl'>".$locale['431']."</td>\n";
echo "<td width='70%' class='tbl'>";
echo "<span id='display_protocol'>".$settings2['site_protocol']."</span>://";
echo "<span id='display_host'>".$settings2['site_host']."</span>";
echo "<span id='display_port'>".($settings2['site_port'] ? ":".$settings2['site_port'] : "")."</span>";
echo "<span id='display_path'>".$settings2['site_path']."</span>";
echo "</td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'><strong>".$locale['432']."</strong></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='30%' class='tbl' valign='top'>".$locale['407']."<br /><span class='small2'>".$locale['408']."</span></td>\n";
echo "<td width='70%' class='tbl' valign='top'><textarea name='intro' cols='50' rows='6' class='textbox' style='width:230px;'>".phpentities(stripslashes($settings2['siteintro']))."</textarea></td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='30%' class='tbl'>".$locale['409']."</td>\n";
echo "<td width='70%' class='tbl'><textarea name='description' cols='50' rows='6' class='textbox' style='width:230px;'>".$settings2['description']."</textarea></td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='30%' class='tbl'>".$locale['410']."<br /><span class='small2'>".$locale['411']."</span></td>\n";
echo "<td width='70%' class='tbl'><textarea name='keywords' cols='50' rows='6' class='textbox' style='width:230px;'>".$settings2['keywords']."</textarea></td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='30%' class='tbl'>".$locale['412']."</td>\n";
echo "<td width='70%' class='tbl'><textarea name='footer' cols='50' rows='6' class='textbox' style='width:230px;'>".phpentities(stripslashes($settings2['footer']))."</textarea></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='30%' valign='top' class='tbl'>".$locale['413']."</td>\n";
echo "<td width='70%' class='tbl'><input type='text' name='opening_page' value='".$settings2['opening_page']."' maxlength='100' class='textbox' style='width:200px;' /></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='30%' class='tbl'>".$locale['417']."</td>\n";
echo "<td width='70%' class='tbl'><select name='localeset' class='textbox'>\n";
echo makefileopts($locale_files, $settings2['locale'])."\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='30%' class='tbl'>".$locale['418'];
if ($userdata['user_theme'] == "Default") {
  if ($settings2['theme'] != str_replace(THEMES, "", substr(THEME, 0, strlen(THEME)-1))) {
  	echo "<div id='close-message'><div class='admin-message'>".$locale['global_302']."</div></div>\n";
  }
}
echo "</td>\n";
echo "<td width='70%' class='tbl'><select name='theme' class='textbox'>\n";
echo makefileopts($theme_files, $settings2['theme'])."\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='30%' class='tbl'>".$locale['419']."</td>\n";
echo "<td width='70%' class='tbl'><select name='default_search' class='textbox'>\n[DEFAULT_SEARCH]</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='30%' class='tbl'>".$locale['420']."<br /><span class='small2'>".$locale['424']."</span></td>\n";
echo "<td width='70%' class='tbl'><textarea name='exclude_left' cols='50' rows='5' class='textbox' style='width:230px;'>".$settings2['exclude_left']."</textarea></td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='30%' class='tbl'>".$locale['421']."<br /><span class='small2'>".$locale['424']."</span></td>\n";
echo "<td width='70%' class='tbl'><textarea name='exclude_upper' cols='50' rows='5' class='textbox' style='width:230px;'>".$settings2['exclude_upper']."</textarea></td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='30%' class='tbl'>".$locale['422']."<br /><span class='small2'>".$locale['424']."</span></td>\n";
echo "<td width='70%' class='tbl'><textarea name='exclude_lower' cols='50' rows='5' class='textbox' style='width:230px;'>".$settings2['exclude_lower']."</textarea></td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='30%' class='tbl'>".$locale['423']."<br /><span class='small2'>".$locale['424']."</span></td>\n";
echo "<td width='70%' class='tbl'><textarea name='exclude_right' cols='50' rows='5' class='textbox' style='width:230px;'>".$settings2['exclude_right']."</textarea></td>\n";
echo "</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />";
echo "<input type='hidden' name='old_localeset' value='".$settings2['locale']."' />\n";
echo "<input type='submit' name='savesettings' value='".$locale['750']."' class='button' /></td>\n";
echo "</tr>\n</table>\n</form>\n";
closetable();

$cache = ob_get_contents();

unset($locale);
$default_search = "";
$dh = opendir(LOCALE.LOCALESET."search");
while (false !== ($entry = readdir($dh))) {
	if (substr($entry, 0, 1) != "." && $entry != "index.php") {
		echo "ENTRY: $entry<br />";
		include LOCALE.LOCALESET."search/".$entry;
		foreach ($locale as $key => $value) {
			if (preg_match("/400/", $key)) {
				$entry = str_replace(".php", "", $entry);
				$default_search .= "<option value='".$entry."'".($settings2['default_search'] == $entry ? " selected='selected'" : "").">".$value."</option>\n";
			}
		}
		unset($locale);
	}
}
closedir($dh); unset($locale);
include LOCALE.LOCALESET."search.php";
$default_search .= "<option value='all'".($settings2['default_search'] == 'all' ? " selected='selected'" : "").">".$locale['407']."</option>\n";
$cache = str_replace("[DEFAULT_SEARCH]", $default_search, $cache);
ob_end_clean();
echo $cache;

echo "<script type='text/javascript'>\n";
echo "/* <![CDATA[ */\n";
echo "jQuery('#site_protocol').change(function () {\n";
echo "var value_protocol = jQuery('#site_protocol').val();\n";
echo "jQuery('#display_protocol').text(value_protocol);\n";
echo "}).keyup();\n";
echo "jQuery('#site_host').keyup(function () {\n";
echo "var value_host = jQuery('#site_host').val();\n";
echo "jQuery('#display_host').text(value_host);\n";
echo "}).keyup();\n";
echo "jQuery('#site_port').keyup(function () {\n";
echo "var value_port = ':'+jQuery('#site_port').val();\n";
echo "if (value_port == ':' || value_port == ':0' || value_port == ':90' || value_port == ':443') {\n";
echo "var value_port = '';\n";
echo "}\n";
echo "jQuery('#display_port').text(value_port);\n";
echo "}).keyup();\n";
echo "jQuery('#site_path').keyup(function () {\n";
echo "var value_path = jQuery('#site_path').val();\n";
echo "jQuery('#display_path').text(value_path);\n";
echo "}).keyup();\n";
echo "/* ]]>*/\n";
echo "</script>";

require LOCALE.LOCALESET."global.php";
require_once THEMES."templates/footer.php";

?>