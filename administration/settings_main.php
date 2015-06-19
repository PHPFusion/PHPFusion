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
pageAccess('S1');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";

add_breadcrumb(array('link' => ADMIN."settings_main.php".$aidlink, 'title' => $locale['main_settings']));

// These are the default settings and the only settings we expect to be posted
$settings_main = array(
		'siteintro' => fusion_get_settings('siteintro'),
		'sitename' => fusion_get_settings('sitename'),
		'sitebanner' => fusion_get_settings('sitebanner'),
		'siteemail' => fusion_get_settings('siteemail'),
		'siteusername' => fusion_get_settings('siteusername'),
		'footer' => fusion_get_settings('footer'),
		'site_protocol' => fusion_get_settings('site_protocol'),
		'site_host' => fusion_get_settings('site_host'),
		'site_path' => fusion_get_settings('site_path'),
		'site_port' => fusion_get_settings('site_port'),
		'description' => fusion_get_settings('description'),
		'keywords' => fusion_get_settings('keywords'),
		'opening_page' => fusion_get_settings('opening_page'),
		'default_search' => fusion_get_settings('default_search'),
		'exclude_left' => fusion_get_settings('exclude_left'),
		'exclude_upper' => fusion_get_settings('exclude_upper'),
		'exclude_aupper' => fusion_get_settings('exclude_aupper'),
		'exclude_lower' => fusion_get_settings('exclude_lower'),
		'exclude_blower' => fusion_get_settings('exclude_blower'),
		'exclude_right' => fusion_get_settings('exclude_right')
	);

// Default Search options
$dir = LOCALE.LOCALESET."search/";
$temp = opendir($dir);
$search_opts = array();
if (file_exists($dir)) {
	include LOCALE.LOCALESET."search/converter.php";
	while ($folder = readdir($temp)) {
		if (!in_array($folder, array("..", ".", 'users.json.php', 'converter.php', '.DS_Store', 'index.php'))) {
			$val = $filename_locale[$folder];
			$search_opts[$val] = ucwords($val);
		}
	}
}

// Saving settings
if (isset($_POST['savesettings'])) {
	foreach ($settings_main as $key => $value) {
		if (isset($_POST[$key])) {
			if ($key == 'siteintro') {
				$settings_main['siteintro'] = addslashes(stripinput($_POST['siteintro']));
			} elseif ($key == 'footer') {
				$settings_main['footer'] = addslashes(stripinput($_POST['footer']));
			} elseif ($key == 'site_host') {
				$settings_main['site_host'] = (empty($_POST['site_host']) ? $settings_main['site_host'] : stripinput($_POST['site_host']));
				if (strpos($settings_main['site_host'], "/") !== FALSE) {
					$settings_main['site_host'] = explode("/", $settings_main['site_host'], 2);
					if ($settings_main['site_host'][1] != "") {
						$_POST['site_path'] = "/".$settings_main['site_host'][1];
					}
					$settings_main['site_host'] = $settings_main['site_host'][0];
				}
			} elseif ($key == 'site_port') {
				$settings_main['site_port'] = ((isnum($_POST['site_port']) || $_POST['site_port'] == "") && !in_array($_POST['site_port'], array(0, 80, 443)) && $_POST['site_port'] < 65001) ? $_POST['site_port'] : '';
			} elseif ($key == 'default_search') {
				$settings_main['default_search'] = (in_array(stripinput($_POST['default_search']), $search_opts) ? stripinput($_POST['default_search']) : $settings_main['default_search']);
			} else {
				$settings_main[$key] = form_sanitizer($_POST[$key], $settings_main[$key], $key);
			}
		} else {
			$settings_main[$key] = form_sanitizer($settings_main[$key], $settings_main[$key], $key);
		}
		if (!defined('FUSION_NULL')) {
			dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_main[$key]."' WHERE settings_name='".$key."'");
		}
	}

	if (!defined('FUSION_NULL')) {
		addNotice("success", "<i class='fa fa-check-square-o m-r-10 fa-lg'></i>".$locale['900']);
		$settings_main['siteurl'] = $settings_main['site_protocol']."://".$settings_main['site_host'].($settings_main['site_port'] ? ":".$settings_main['site_port'] : "").$settings_main['site_path'];
		dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_main['siteurl']."' WHERE settings_name='siteurl'");
		redirect(FUSION_SELF.$aidlink);
	}
}

opentable($locale['main_settings']);
	echo "<div class='well'>".$locale['main_description']."</div>";
	echo openform('settingsform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 2));
		echo "<div class='row'><div class='col-xs-12 col-sm-12 col-md-6'>\n";

		openside('');
			echo form_text('sitename', $locale['402'], $settings_main['sitename'], array('max_length' => 255, 'required' => 1, 'error_text' => $locale['error_value'], 'inline' => 1));
			echo form_text('siteemail', $locale['405'], $settings_main['siteemail'], array('max_length' => 128, 'required' => 1, 'type' => 'email', 'inline' => 1));
			echo form_text('siteusername', $locale['406'], $settings_main['siteusername'], array('max_length' => 32, 'required' => 1, 'error_text' => $locale['error_value'], 'inline' => 1));
			echo form_text('sitebanner', $locale['404'], $settings_main['sitebanner'], array('required' => 1, 'error_text' => $locale['error_value'], 'inline' => 1));
		closeside();

		openside('');
			echo form_textarea('description', $locale['409'], $settings_main['description'], array('autosize'=>1));
			echo form_textarea('keywords', $locale['410']."<br/><small>".$locale['411']."</small>", $settings_main['keywords'], array('autosize'=>1));
			echo form_textarea('footer', $locale['412'], stripslashes($settings_main['footer']), array('autosize' => 1));
			echo form_select('default_search', $locale['419'], $search_opts, $settings_main['default_search'], array('width' => '100%'));
		closeside();

		openside('');
			echo "<div class='alert alert-success'>\n";
			echo "<i class='fa fa-external-link m-r-10'></i>";
			echo "<span id='display_protocol'>".$settings_main['site_protocol']."</span>://";
			echo "<span id='display_host'>".$settings_main['site_host']."</span>";
			echo "<span id='display_port'>".($settings_main['site_port'] ? ":".$settings_main['site_port'] : "")."</span>";
			echo "<span id='display_path'>".$settings_main['site_path']."</span>";
			echo "</div>\n";
			$opts = array('http' => 'http://', 'https' => 'https://');
			$opts['invalid_protocol'] = 'Invalid (test purposes)';
			echo form_select('site_protocol', $locale['426'], $opts, $settings_main['site_protocol'], array('regex' => 'http(s)?', 'width' => '100%', 'error_text' => $locale['error_value']));
			echo form_text('site_host', $locale['427'], $settings_main['site_host'], array('max_length' => 255, 'required' => 1, 'error_text' => $locale['error_value']));
			echo form_text('site_path', $locale['429'], $settings_main['site_path'], array('regex' => '\/([a-z0-9-_]+\/)*?', 'max_length' => 255, 'required' => 1));
			echo form_text('site_port', $locale['430'], $settings_main['site_port'], array('max_length' => 5));
		closeside();

		echo "</div><div class='col-xs-12 col-sm-12 col-md-6'>\n";

		openside('');
			echo form_text('opening_page', $locale['413'], $settings_main['opening_page'], array('inline'=>1, 'max_length' => 100, 'required' => 1, 'error_text' => $locale['error_value']));
			echo form_textarea('siteintro', $locale['407'], stripslashes($settings_main['siteintro']), array('autosize'=>1));
		closeside();

		openside('');
			echo "<div class='alert alert-info'>".$locale['424']."</div>";
			echo form_textarea('exclude_left', $locale['420'], $settings_main['exclude_left'], array('autosize' => 1,));
			echo form_textarea('exclude_upper', $locale['421'], $settings_main['exclude_upper'], array('autosize' => 1));
			echo form_textarea('exclude_aupper', $locale['435'], $settings_main['exclude_aupper'], array('autosize' => 1));
			echo form_textarea('exclude_lower', $locale['422'], $settings_main['exclude_lower'], array('autosize' => 1));
			echo form_textarea('exclude_blower', $locale['436'], $settings_main['exclude_blower'], array('autosize' => 1));
			echo form_textarea('exclude_right', $locale['423'], $settings_main['exclude_right'], array('autosize' => 1));
		closeside();

		echo "</div>\n</div>\n";
		echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success'));
		echo closeform();
closetable();

// TODO: Add these with add_to_jquery()
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
