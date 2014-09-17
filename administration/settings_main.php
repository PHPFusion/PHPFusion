<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
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
if (!checkrights("S1") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}
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
	//$localeset     = stripinput($_POST['localeset']);
	$old_localeset = stripinput($_POST['old_localeset']);
	$site_host = "";
	$site_path = "/";
	$site_protocol = "http";
	$site_port = "";
	if (in_array($_POST['site_protocol'], array("http", "https"))) {
		$site_protocol = $_POST['site_protocol'];
	}
	if ($_POST['site_host'] && $_POST['site_host'] != "/") {
		$site_host = stripinput($_POST['site_host']);
		if (strpos($site_host, "/") !== FALSE) {
			$site_host = explode("/", $site_host, 2);
			if ($site_host[1] != "") {
				$site_path = "/".$site_host[1];
			}
			$site_host = $site_host[0];
		}
	} else {
		$defender->stop();
		$defender->addNotice($locale['902']);
		//redirect(FUSION_SELF.$aidlink."&error=2");
	}
	if (($_POST['site_path'] && $_POST['site_path'] != "/") || $site_path != "/") {
		if ($site_path == "/") {
			$site_path = stripinput($_POST['site_path']);
		}
		$site_path = (substr($site_path, 0, 1) != "/" ? "/" : "").$site_path.(strrchr($site_path, "/") != "/" ? "/" : "");
	}
	if ((isnum($_POST['site_port']) || $_POST['site_port'] == "") && !in_array($_POST['site_port'], array(0, 80, 443))
	) {
		$site_port = $_POST['site_port'];
	}
	$siteurl = $site_protocol."://".$site_host.($site_port ? ":".$site_port : "").$site_path;
	$sitename = !defined('FUSION_NULL') ? form_sanitizer($_POST['sitename'], '', 'sitename') : '';
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$sitename' WHERE settings_name='sitename'") : '';
	$sitebanner = form_sanitizer($_POST['sitebanner'], '', 'sitebanner');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$sitebanner' WHERE settings_name='sitebanner'") : '';
	$siteemail = form_sanitizer($_POST['siteemail'], '', 'siteemail');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$siteemail' WHERE settings_name='siteemail'") : '';
	$username = form_sanitizer($_POST['username'], '', 'username');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$username' WHERE settings_name='siteusername'") : '';
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$site_protocol."' WHERE settings_name='site_protocol'") : '';
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$site_host."' WHERE settings_name='site_host'") : '';
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$site_path."' WHERE settings_name='site_path'") : '';
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$site_port."' WHERE settings_name='site_port'") : '';
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$siteurl."' WHERE settings_name='siteurl'") : '';
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslashes(addslashes($siteintro))."' WHERE settings_name='siteintro'") : '';
	$description = form_sanitizer($_POST['description'], '', 'description');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$description' WHERE settings_name='description'") : '';
	$keywords = form_sanitizer($_POST['keywords'], '', 'keywords');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$keywords' WHERE settings_name='keywords'") : '';
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslashes(addslashes($sitefooter))."' WHERE settings_name='footer'") : '';
	$opening_page = form_sanitizer($_POST['opening_page'], '', 'opening_page');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$opening_page' WHERE settings_name='opening_page'") : '';
	//$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$localeset' WHERE settings_name='locale'") : '';
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['theme'])."' WHERE settings_name='theme'") : '';
	$bootstrap = form_sanitizer($_POST['bootstrap'], 1, 'bootstrap');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$bootstrap' WHERE settings_name='bootstrap'") : '';
	$site_seo = form_sanitizer($_POST['site_seo'], 0, 'site_seo');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$site_seo' WHERE settings_name='site_seo'") : '';
	if ($site_seo == 1) {
		// enable
		// create .htaccess
		if (!file_exists(BASEDIR.".htaccess")) {
			if (file_exists(BASEDIR."_htaccess") && function_exists("rename")) {
				@rename(BASEDIR."_htaccess", BASEDIR.".htaccess");
			} else {
				// create a file.
				$handle = fopen(BASEDIR.".htaccess", "w");
				fclose($handle);
			}
		}
		// write file. wipe out all .htaccess current configuration.
		$htacess = "Options +FollowSymlinks -MultiViews\n";
		$htacess .= "RewriteEngine On\n";
		$htacess .= "RewriteBase ".$settings['site_path']."\n";
		$htacess .= "# Fix Apache internal dummy connections from breaking [(site_url)] cache\n";
		$htacess .= "RewriteCond %{HTTP_USER_AGENT} ^.*internal\ dummy\ connection.*$ [NC]\n";
		$htacess .= "RewriteRule .* - [F,L]\n";
		$htacess .= "# Exclude /assets and /manager directories and images from rewrite rules\n";
		$htacess .= "RewriteRule ^(administration|themes)/*$ - [L]\n";
		$htacess .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
		$htacess .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
		$htacess .= "RewriteCond %{REQUEST_FILENAME} !-l\n";
		$htacess .= "RewriteCond %{REQUEST_URI} !^/(administration|config|rewrite.php)\n";
		$htacess .= "RewriteRule ^(.*?)$ rewrite.php [L]\n";
		$temp = fopen(BASEDIR.".htaccess", "w");
		if (fwrite($temp, $htacess)) {
			fclose($temp);
		}
	} else {
		// disable
		if (file_exists(BASEDIR.".htaccess")) {
			// delete file. wipe out all main .htaccess settings to prevent redirect and crash the server.
			if (file_exists(BASEDIR.".htaccess") && function_exists("rename")) {
				// rename file.
				@rename(BASEDIR.".htaccess", BASEDIR."_htaccess");
			}
		}
	}
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['default_search'])."' WHERE settings_name='default_search'") : '';
	$exclude_left = form_sanitizer($_POST['exclude_left'], '', 'exclude_left');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$exclude_left' WHERE settings_name='exclude_left'") : '';
	$exclude_upper = form_sanitizer($_POST['exclude_upper'], '', 'exclude_upper');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$exclude_upper' WHERE settings_name='exclude_upper'") : '';
	$exclude_aupper = form_sanitizer($_POST['exclude_aupper'], '', 'exclude_aupper');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$exclude_aupper' WHERE settings_name='exclude_aupper'") : '';
	$exclude_lower = form_sanitizer($_POST['exclude_lower'], '', 'exclude_lower');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$exclude_lower' WHERE settings_name='exclude_lower'") : '';
	$exclude_blower = form_sanitizer($_POST['exclude_blower'], '', 'exclude_blower');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$exclude_blower' WHERE settings_name='exclude_blower'") : '';
	$exclude_right = form_sanitizer($_POST['exclude_right'], '', 'exclude_right');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$exclude_right' WHERE settings_name='exclude_right'") : '';
}
$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}
$theme_files = makefilelist(THEMES, ".|..|templates", TRUE, "folders");
opentable($locale['400']);
echo openform('settingsform', 'settingsform', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));
echo "<table class='table table-responsive center'>\n<tbody>\n<tr>\n";
echo "<td width='35%' class='tbl'><label for='sitename'>".$locale['402']."</label> <span class='required'>*</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
echo form_text('', 'sitename', 'sitename', $settings2['sitename'], array('max_length' => 255, 'required' => 1,
																		 'error_text' => $locale['error_value']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='35%' class='tbl'><label for='sitebanner'>".$locale['404']."</label></td>\n";
echo "<td width='65%' class='tbl'>\n";
echo form_text('', 'sitebanner', 'sitebanner', $settings2['sitebanner'], array('required' => 1,
																			   'error_text' => $locale['error_value']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='35%' class='tbl'><label for='sitemeail'>".$locale['405']."</label> <span class='required'>*</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
echo form_text('', 'siteemail', 'siteemail', $settings2['siteemail'], array('max_length' => 128, 'required' => 1,
																			'error_text' => $locale['error_value'],
																			'email' => 1));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='35%' class='tbl'><label for='username'>".$locale['406']."</label> <span class='required'>*</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
echo form_text('', 'username', 'username', $settings2['siteusername'], array('max_length' => 32, 'required' => 1,
																			 'error_text' => $locale['error_value']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'><strong>".$locale['425']."</strong></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='35%' class='tbl'><label for='site_protocol'>".$locale['426']."</label> <span class='required'>*</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
$opts = array('http' => 'http://', 'https' => 'https://');
echo form_select('', 'site_protocol', 'site_protocol', $opts, $settings2['site_protocol'], array('required' => 1,
																								 'error_text' => $locale['error_value']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='35%' class='tbl' valign='top'><label for='site_host'>".$locale['427']."</label> <span class='required'>*</span><br /><span class='small2'>".$locale['428']." ".$locale['433']."</span></td>\n";
echo "<td width='65%' class='tbl' valign='top'>\n";
echo form_text('', 'site_host', 'site_host', $settings2['site_host'], array('max_length' => 255, 'required' => 1,
																			'error_text' => $locale['error_value']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='35%' class='tbl' valign='top'><label for='site_path'>".$locale['429']."</label> <span class='required'>*</span><br /><span class='small2'>".$locale['428']." /".$locale['434']."/</span></td>\n";
echo "<td width='65%' class='tbl' valign='top'>\n";
echo form_text('', 'site_path', 'site_path', $settings2['site_path'], array('max_length' => 255, 'required' => 1,
																			'error_text' => $locale['error_value']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='35%' class='tbl' valign='top'><label for='site_port'>".$locale['430']."</span> <span class='required'>*</span><br /><span class='small2'>".$locale['408']."</span></td>\n";
echo "<td width='65%' class='tbl' valign='top'>\n";
echo form_text('', 'site_port', 'site_port', $settings2['site_port'], array('max_length' => 4, 'required' => 1,
																			'error_text' => $locale['error_value']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='35%' class='tbl'><strong>".$locale['431']."</strong></td>\n";
echo "<td width='65%' class='tbl'>";
echo "<div class='well'><strong>\n";
echo "<span id='display_protocol'>".$settings2['site_protocol']."</span>://";
echo "<span id='display_host'>".$settings2['site_host']."</span>";
echo "<span id='display_port'>".($settings2['site_port'] ? ":".$settings2['site_port'] : "")."</span>";
echo "<span id='display_path'>".$settings2['site_path']."</span>";
echo "</strong></div>\n";
echo "</td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'><strong>".$locale['432']."</strong></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='35%' class='tbl' valign='top'><label for='intro'>".$locale['407']."</label><br /></td>\n";
echo "<td width='65%' class='tbl' valign='top'>\n";
echo form_textarea('', 'intro', 'intro', $settings2['siteintro']);
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='35%' class='tbl'><label for='description'>".$locale['409']."</label></td>\n";
echo "<td width='65%' class='tbl'>\n";
echo form_textarea('', 'description', 'description', $settings2['description']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td valign='top' width='35%' class='tbl'><label for='keywords'>".$locale['410']."</label><br /><span class='small2'>".$locale['411']."</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
echo form_textarea('', 'keywords', 'keywords', $settings2['keywords']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td valign='top' width='35%' class='tbl'><label for='footer'>".$locale['412']."</label> <span class='required'>*</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
echo form_textarea('', 'footer', 'footer', $settings2['footer'], array('required' => 1,
																	   'error_text' => $locale['error_value']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='35%' valign='top' class='tbl'><label for='opening_page'>".$locale['413']."<label> <span class='required'>*</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
echo form_text('', 'opening_page', 'opening_page', $settings2['opening_page'], array('max_length' => 100,
																					 'required' => 1,
																					 'error_text' => $locale['error_value']));
echo "</tr>\n<tr>\n";
echo "<td width='35%' class='tbl'><label for='theme'>".$locale['418']."</label><span class='required'>*</span>";
if ($userdata['user_theme'] == "Default") {
	if ($settings2['theme'] != str_replace(THEMES, "", substr(THEME, 0, strlen(THEME)-1))) {
		echo "<div id='close-message'><div class='admin-message alert alert-warning m-t-10'>".$locale['global_302']."</div></div>\n";
	}
}
echo "</td>\n";
echo "<td width='65%' class='tbl'>\n";
$opts = array();
foreach ($theme_files as $file) {
	$opts[$file] = $file;
}
echo form_select('', 'theme', 'theme', $opts, $settings2['theme'], array("required" => 1,
																		 'error_text' => $locale['error_value']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='35%' valign='top' class='tbl'><label for='bootstrap'>".$locale['437']."<label> <span class='required'>*</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
$opts = array('0' => $locale['no'], '1' => $locale['yes']);
echo form_select('', 'bootstrap', 'bootstrap', $opts, $settings2['bootstrap'], array("required" => 1,
																					 'error_text' => $locale['error_value']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='35%' class='tbl'><label for='default_search'>".$locale['419']."</label> <span class='required'>*</span></td>\n";
$dir = LOCALE.LOCALESET."search/";
$temp = opendir($dir);
$opts = array();
if (file_exists($dir)) {
	while ($folder = readdir($temp)) {
		if (!in_array($folder, array("..", ".", 'users.json.php', '.DS_Store', 'index.php'))) {
			$val = str_replace(".php", '', $folder);
			$opts[$val] = ucwords($val);
		}
	}
}
echo "<td width='65%' class='tbl'>\n";
echo form_select('', 'default_search', 'default_search', $opts, $settings2['default_search'], array('required' => 1));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='35%' valign='top' class='tbl'><label for='site_seo'>".$locale['438']."<label> <span class='required'>*</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
$opts = array('0' => $locale['no'], '1' => $locale['yes']);
echo form_select('', 'site_seo', 'site_seo', $opts, $settings2['site_seo']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td valign='top' width='35%' class='tbl'><label for='exclude_left'>".$locale['420']."</label><br /><span class='small2'>".$locale['424']."</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
echo form_textarea('', 'exclude_left', 'exclude_left', $settings2['exclude_left']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td valign='top' width='35%' class='tbl'><label for='exclude_upper'>".$locale['421']."</label><br /><span class='small2'>".$locale['424']."</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
echo form_textarea('', 'exclude_upper', 'exclude_upper', $settings2['exclude_upper']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td valign='top' width='35%' class='tbl'><label for='exclude_aupper'>".$locale['435']."</label><br /><span class='small2'>".$locale['424']."</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
echo form_textarea('', 'exclude_aupper', 'exclude_aupper', $settings2['exclude_aupper']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td valign='top' width='35%' class='tbl'><label for='exclude_lower'>".$locale['422']."</label><br /><span class='small2'>".$locale['424']."</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
echo form_textarea('', 'exclude_lower', 'exclude_lower', $settings2['exclude_lower']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td valign='top' width='35%' class='tbl'><label for='exclude_blower'>".$locale['436']."</label><br /><span class='small2'>".$locale['424']."</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
echo form_textarea('', 'exclude_blower', 'exclude_blower', $settings2['exclude_blower']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td valign='top' width='35%' class='tbl'><label for='exclude_right'>".$locale['423']."</label><br /><span class='small2'>".$locale['424']."</span></td>\n";
echo "<td width='65%' class='tbl'>\n";
echo form_textarea('', 'exclude_right', 'exclude_right', $settings2['exclude_right']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />";
echo form_hidden('', 'old_localeset', 'old_localeset', $settings2['locale']);
echo form_button($locale['750'], 'savesettings', 'savesettings', $locale['750'], array('class' => 'btn-primary'));
echo "</td>\n</tr>\n</tbody>\n</table>\n";
echo closeform();
closetable();
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