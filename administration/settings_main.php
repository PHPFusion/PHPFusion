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
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>$message</div></div>\n";
	}
}

if (isset($_POST['savesettings'])) {
	$error = 0;
	$htc = "";
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
	$admin_theme = form_sanitizer($_POST['admin_theme'], '', 'admin_theme');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$admin_theme' WHERE settings_name='admin_theme'") : '';
	$theme = form_sanitizer($_POST['theme'], '', 'theme');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$theme' WHERE settings_name='theme'") : '';
	$bootstrap = form_sanitizer($_POST['bootstrap'], 0, 'bootstrap');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$bootstrap' WHERE settings_name='bootstrap'") : '';
	$site_seo = form_sanitizer($_POST['site_seo'], 0, 'site_seo');
	$result = !defined('FUSION_NULL') ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$site_seo' WHERE settings_name='site_seo'") : '';
	if ($site_seo == 1) {
		// create .htaccess
			if (!file_exists(BASEDIR.".htaccess")) {
			if (file_exists(BASEDIR."_htaccess") && function_exists("rename")) {
				@rename(BASEDIR."_htaccess", BASEDIR.".htaccess");
			} else {
				$handle = fopen(BASEDIR.".htaccess", "w");
				fclose($handle);
			}
		}
		// write file. wipe out all .htaccess current configuration.
		$htc .= "Options +SymLinksIfOwnerMatch\r\n";
		$htc .= "RewriteEngine On\r\n";
		$htc .= "RewriteBase ".$settings['site_path']."\r\n";
		$htc .= "# Fix Apache internal dummy connections from breaking [(site_url)] cache\r\n";
		$htc .= "RewriteCond %{HTTP_USER_AGENT} ^.*internal\ dummy\ connection.*$ [NC]\r\n";
		$htc .= "RewriteRule .* - [F,L]\r\n";
		$htc .= "# Exclude /assets and /manager directories and images from rewrite rules\r\n";
		$htc .= "RewriteRule ^(administration|themes)/*$ - [L]\r\n";
		$htc .= "RewriteCond %{REQUEST_FILENAME} !-f\r\n";
		$htc .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
		$htc .= "RewriteCond %{REQUEST_FILENAME} !-l\r\n";
		$htc .= "RewriteCond %{REQUEST_URI} !^/(administration|config|rewrite.php)\r\n";
		$htc .= "RewriteRule ^(.*?)$ rewrite.php [L]\r\n";
		$temp = fopen(BASEDIR.".htaccess", "w");
		if (fwrite($temp, $htc)) {
			fclose($temp);
		}
	} else {
		// enable default error handler in .htaccess
		if (!file_exists(BASEDIR.".htaccess")) {
			if (file_exists(BASEDIR."_htaccess") && function_exists("rename")) {
				@rename(BASEDIR."_htaccess", BASEDIR.".htaccess");
			} else {
				// create a file.
				$handle = fopen(BASEDIR.".htaccess", "w");
				fclose($handle);
			}
		}
		//  Wipe out all .htaccess rewrite rules and add error handler only
		$htc = "ErrorDocument 400 ".$settings['siteurl']."error.php?code=400\r\n";
		$htc .= "ErrorDocument 401 ".$settings['siteurl']."error.php?code=401\r\n";
		$htc .= "ErrorDocument 403 ".$settings['siteurl']."error.php?code=403\r\n";
		$htc .= "ErrorDocument 404 ".$settings['siteurl']."error.php?code=404\r\n";
		$htc .= "ErrorDocument 500 ".$settings['siteurl']."error.php?code=500\r\n";
		$temp = fopen(BASEDIR.".htaccess", "w");
		if (fwrite($temp, $htc)) {
			fclose($temp);
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
	if (!defined('FUSION_NULL')) {
		redirect(FUSION_SELF.$aidlink."&amp;error=0");
	}
}
$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}
$theme_files = makefilelist(THEMES, ".|..|templates|admin_templates", TRUE, "folders");
$admin_theme_files = makefilelist(THEMES."admin_templates/", ".|..",  TRUE, "folders");

opentable($locale['400']);
echo openform('settingsform', 'settingsform', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));
echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
echo form_text($locale['402'], 'sitename', 'sitename', $settings2['sitename'], array('max_length' => 255, 'required' => 1, 'error_text' => $locale['error_value']));
echo form_text($locale['404'], 'sitebanner', 'sitebanner', $settings2['sitebanner'], array('required' => 1, 'error_text' => $locale['error_value']));
echo form_text($locale['405'], 'siteemail', 'siteemail', $settings2['siteemail'], array('max_length' => 128, 'required' => 1, 'error_text' => $locale['error_value'], 'email' => 1));
echo form_text($locale['406'], 'username', 'username', $settings2['siteusername'], array('max_length' => 32, 'required' => 1, 'error_text' => $locale['error_value']));
$opts = array('http' => 'http://', 'https' => 'https://');
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
echo form_select($locale['426'], 'site_protocol', 'site_protocol', $opts, $settings2['site_protocol'], array('width'=>'100%', 'required' => 1, 'error_text' => $locale['error_value']));
echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
echo form_text($locale['427'], 'site_host', 'site_host', $settings2['site_host'], array('max_length' => 255, 'required' => 1, 'error_text' => $locale['error_value']));
echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
echo form_text($locale['429'], 'site_path', 'site_path', $settings2['site_path'], array('max_length' => 255, 'required' => 1, 'error_text' => $locale['error_value']));
echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
echo form_text($locale['430'], 'site_port', 'site_port', $settings2['site_port'], array('max_length' => 4, 'required' => 1, 'error_text' => $locale['error_value']));
echo "</div>\n</div>\n";
echo "<div class='well'><strong>\n".$locale['431']." ";
echo "<span id='display_protocol'>".$settings2['site_protocol']."</span>://";
echo "<span id='display_host'>".$settings2['site_host']."</span>";
echo "<span id='display_port'>".($settings2['site_port'] ? ":".$settings2['site_port'] : "")."</span>";
echo "<span id='display_path'>".$settings2['site_path']."</span>";
echo "</strong></div>\n";
echo "</div>\n</div>\n";

echo "<div class='panel panel-default'>\n";
echo "<div class='panel-heading'><strong>".$locale['432']."</strong></div>\n";
echo "<div class='panel-body'>\n";
echo form_textarea($locale['407'], 'intro', 'intro', $settings2['siteintro']);
echo form_textarea($locale['409'], 'description', 'description', $settings2['description']);
echo form_textarea($locale['410']."<br/><small>".$locale['411']."</small>", 'keywords', 'keywords', $settings2['keywords']);
echo form_textarea($locale['412'], 'footer', 'footer', $settings2['footer'], array('required' => 1, 'error_text' => $locale['error_value']));
echo form_text($locale['413'], 'opening_page', 'opening_page', $settings2['opening_page'], array('max_length' => 100, 'required' => 1, 'error_text' => $locale['error_value']));
echo "</div>\n</div>\n";
echo "<div class='panel panel-default'>\n<div class='panel-body'>\n";
if ($userdata['user_theme'] == "Default") {
	if ($settings2['theme'] != str_replace(THEMES, "", substr(THEME, 0, strlen(THEME)-1))) {
		echo "<div id='close-message'><div class='admin-message alert alert-warning m-t-10'>".$locale['global_302']."</div></div>\n";
	}
}
$opts = array();
foreach ($theme_files as $file) {
	$opts[$file] = $file;
}
echo form_select($locale['418'], 'theme', 'theme', $opts, $settings2['theme'], array("required" => 1, 'error_text' => $locale['error_value']));
$opts = array();
foreach ($admin_theme_files as $file) {
	$opts[$file] = $file;
}
echo form_select($locale['418a'], 'admin_theme', 'admin_theme', $opts, $settings2['admin_theme'], array("required" => 1, 'error_text' => $locale['error_value']));

$opts = array('0' => $locale['no'], '1' => $locale['yes']);
echo form_toggle($locale['437'], 'bootstrap', 'bootstrap', $opts, $settings2['bootstrap']);
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
echo form_select($locale['419'], 'default_search', 'default_search', $opts, $settings2['default_search'], array('required' => 1));
$opts = array('0' => $locale['no'], '1' => $locale['yes']);
echo form_toggle($locale['438'], 'site_seo', 'site_seo', $opts, $settings2['site_seo']);
echo form_textarea($locale['420']."<small>".$locale['424']."</small><br/>\n", 'exclude_left', 'exclude_left', $settings2['exclude_left']);
echo form_textarea($locale['421']."<small>".$locale['424']."</small><br/>\n", 'exclude_upper', 'exclude_upper', $settings2['exclude_upper']);
echo form_textarea($locale['435']."<small>".$locale['424']."</small><br/>\n", 'exclude_aupper', 'exclude_aupper', $settings2['exclude_aupper']);
echo form_textarea($locale['422']."<small>".$locale['424']."</small><br/>\n", 'exclude_lower', 'exclude_lower', $settings2['exclude_lower']);
echo form_textarea($locale['436']."<small>".$locale['424']."</small><br/>\n", 'exclude_blower', 'exclude_blower', $settings2['exclude_blower']);
echo form_textarea($locale['423']."<small>".$locale['424']."</small><br/>\n", 'exclude_right', 'exclude_right', $settings2['exclude_right']);
echo form_hidden('', 'old_localeset', 'old_localeset', $settings2['locale']);
echo "</div>\n</div>\n";
echo form_button($locale['750'], 'savesettings', 'savesettings', $locale['750'], array('class' => 'btn-primary'));
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