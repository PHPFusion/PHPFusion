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
add_to_breadcrumbs(array('link'=>ADMIN."settings_main.php".$aidlink, 'title'=>$locale['main_settings']));

$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}

if (isset($_POST['savesettings'])) {
	$error = 0;
	$htc = "";

	$settings2 = array(
		'siteintro' => addslashes(addslashes(descript(stripslash($_POST['intro'])))), //... oh my god, this was simplified by merging all commits thus so far.
		'footer' => addslashes(addslashes(descript(stripslash($_POST['footer'])))),
		'site_protocol' => (in_array($_POST['site_protocol'], array('http','https'))) ? stripinput($_POST['site_protocol']) : 'http',
		'site_host' => '',
		'site_path' => '/',
		'siteurl' => '',
		'site_port' => ((isnum($_POST['site_port']) || $_POST['site_port'] == "") && !in_array($_POST['site_port'], array(0, 80, 443))) ? $_POST['site_port'] : '',
		'sitename' => form_sanitizer($_POST['sitename'], '', 'sitename'),
		'sitebanner' => form_sanitizer($_POST['sitebanner'], '', 'sitebanner'),
		'siteemail' => form_sanitizer($_POST['siteemail'], '', 'siteemail'),
		'siteusername' => form_sanitizer($_POST['username'], '', 'username'),
		'description' => form_sanitizer($_POST['description'], '', 'description'),
		'keywords' => form_sanitizer($_POST['keywords'], '', 'keywords'),
		'opening_page' => form_sanitizer($_POST['opening_page'], '', 'opening_page'),
		'admin_theme' => form_sanitizer($_POST['admin_theme'], '', 'admin_theme'),
		'theme' =>  form_sanitizer($_POST['theme'], '', 'theme'),
		'bootstrap' => form_sanitizer($_POST['bootstrap'], 0, 'bootstrap'),
		'default_search' => stripinput($_POST['default_search']),
		'exclude_left' => form_sanitizer($_POST['exclude_left'], '', 'exclude_left'),
		'exclude_upper' => form_sanitizer($_POST['exclude_upper'], '', 'exclude_upper'),
		'exclude_aupper' =>  form_sanitizer($_POST['exclude_aupper'], '', 'exclude_aupper'),
		'exclude_lower' => form_sanitizer($_POST['exclude_lower'], '', 'exclude_lower'),
		'exclude_blower' => form_sanitizer($_POST['exclude_blower'], '', 'exclude_blower'),
		'exclude_right' => form_sanitizer($_POST['exclude_right'], '', 'exclude_right'),
	);

	/** Site Host */
	if ($_POST['site_host'] && $_POST['site_host'] != "/") {
		$settings2['site_host'] = stripinput($_POST['site_host']);
		if (strpos($settings2['site_host'], "/") !== FALSE) {
			$settings2['site_host'] = explode("/", $settings2['site_host'], 2);
			if ($settings2['site_host'][1] != "") {
				$settings2['site_path'] = "/".$settings2['site_host'][1];
			}
			$settings2['site_host'] = $settings2['site_host'][0];
		}
	} else {
		$error = 1;
		$defender->stop();
		$defender->addNotice($locale['902']);
	}

	/** Site Path  -- someone simplify this to 1 line.. this can obviously move into the top */
	if (($_POST['site_path'] && $_POST['site_path'] != "/") || $settings2['site_path'] != "/") {
		if ($settings2['site_path'] == "/") {
			$settings2['site_path'] = stripinput($_POST['site_path']);
		}
		$settings2['site_path'] = (substr($settings2['site_path'], 0, 1) != "/" ? "/" : "").$settings2['site_path'].(strrchr($settings2['site_path'], "/") != "/" ? "/" : "");
	}

	// Parse siteurl externally
	$settings2['siteurl'] = $settings2['site_protocol']."://".$settings2['site_host'].($settings2['site_port'] ? ":".$settings2['site_port'] : "").$settings2['site_path'];

	foreach($settings2 as $settings_key => $settings_value) {
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_value."' WHERE settings_name='".$settings_key."'");
		if (!$result) {
			$defender->stop();
			$error = 1;
			break;
		}
	}

	if (!defined('FUSION_NULL')) {
		// We are adding and setting some notices here
		addNotice("success", "Settings were successfully updated!");
		addNotice("success", "Yet another success!");
		addNotice("warning", "Oh noes, something went wrong :(");
		//setNotice("info", "This notice will overwrite any other notice previously set.");
		// this line can be commented out to prove form resubmision will not trigger further notices
		redirect(FUSION_SELF.$aidlink);
	}
}

$theme_files = makefilelist(THEMES, ".|..|templates|admin_templates", TRUE, "folders");
$admin_theme_files = makefilelist(THEMES."admin_templates/", ".|..", TRUE, "folders");
opentable($locale['main_settings']);

// These are the notices before we start showing them
if (!empty($_SESSION['notices'])) {
	print_p($_SESSION['notices']);
}

// Get all notices
$notices = getNotices();
renderNotices($notices);

/********************************************
Functions below are to be moved into another
file and replace and change the way notify()
function works mainly with the purpose of
stopping directly outputing HTML.

This is just a showcase, feel free to adapt
the code and largely implement it.
 *********************************************/
/**
 * Renders notices
 * Formats and renders notices
 * @param array $notices the array contaning notices
 * @return string the notices formatted as HTML
 */
function renderNotices($notices) {
	echo "<div id='close-message'>\n";
	foreach ($notices as $status => $notice) {
		echo "<div class='admin-message alert alert-".$status." m-t-10'>";
		foreach ($notice as $id => $message) {
			echo $message."<br />";
		}
		echo "</div>\n";
	}
	echo "</div>\n";
}

/**
 * Retrievs all notices
 * Retrievs all notices for the group identified by the key provided
 * @param string $key the key identifying a group holding notices, by default the page name in which the notice was set
 * @param boolean $delete whether to delete or keep a notice message after it was accessed. This only works if the notice
 * was set or added while having $removeAfterAccess set to FALSE
 * @return array the notices for the group identified by the provided key
 */
function getNotices($key = FUSION_SELF, $delete = TRUE) {
	$notices = array();
	if (!empty($_SESSION['notices'])) {
		foreach ($_SESSION['notices'] as $type => $keys) {
			if (isset($keys[$key])) {
				$notices = array_merge_recursive($notices, $keys[$key]);
				if ($delete) $_SESSION['notices'][$type][$key] = array();
			}
		}
	}

	// Cleanup the notices that are meant to be shown only once regardless if they have been accessed or not
	// do redirects before this function is called to ensure these notifications are displayed at least once
	unset($_SESSION['notices']['once']);

	return $notices;
}

/**
 * Adds a notice message
 * Adds a notice message to the group identified by the key provided
 * @param string $status the status of the message
 * @param string $value the message
 * @param string $key the key identifying a group holding notices, by default the page name in which the notice was set
 * @param boolean $removeAfterAccess whether the notice should be automatically removed after it was displayed once,
 * if set to FALSE when getNotices() is called you have the option to keep the notice even after it was accesed
 */
function addNotice($status, $value, $key = FUSION_SELF, $removeAfterAccess = TRUE) {
	$type = $removeAfterAccess ? 'once' : 'persist';
	if (isset($_SESSION['notices'][$type][$key][$status])) {
		array_push($_SESSION['notices'][$type][$key][$status], $value);
	} else {
		$_SESSION['notices'][$type][$key][$status] = array($value);
	}
}

/**
 * Sets a notice message
 * Sets a notice message for the whole group identified by the key provided, this will overwrite any other notices previously set
 * @param string $status the status of the message
 * @param string $value the message
 * @param string $key the key identifying a group holding notices, by default the page name in which the notice was set
 * @param boolean $removeAfterAccess whether the notice should be automatically removed after it was displayed once.
 * If set to FALSE when getNotices() is called you have the option to keep the notice even after it was accesed.
 */
function setNotice($status, $value, $key = FUSION_SELF, $removeAfterAccess = TRUE) {
	$type = $removeAfterAccess ? 'once' : 'persist';
	$_SESSION['notices'][$type][$key] = array($status => array($value));
}

echo "<div class='well'>".$locale['main_description']."</div>";
echo openform('settingsform', 'settingsform', 'post', FUSION_SELF.$aidlink, array('downtime' => 1));
echo "<div class='row'><div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
echo form_text($locale['402'], 'sitename', 'sitename', $settings2['sitename'], array('max_length' => 255, 'required' => 1, 'error_text' => $locale['error_value'], 'inline' => 1));
echo form_text($locale['405'], 'siteemail', 'siteemail', $settings2['siteemail'], array('max_length' => 128, 'required' => 1, 'error_text' => $locale['error_value'], 'email' => 1, 'inline' => 1));
echo form_text($locale['406'], 'username', 'username', $settings2['siteusername'], array('max_length' => 32, 'required' => 1, 'error_text' => $locale['error_value'], 'inline' => 1));
echo form_text($locale['404'], 'sitebanner', 'sitebanner', $settings2['sitebanner'], array('required' => 1, 'error_text' => $locale['error_value'], 'inline' => 1));
closeside();

openside('');
if ($userdata['user_theme'] == "Default") {
	if ($settings2['theme'] != str_replace(THEMES, "", substr(THEME, 0, strlen(THEME)-1))) {
		echo "<div id='close-message'><div class='admin-message alert alert-warning m-t-10'>".$locale['global_302']."</div></div>\n";
	}
}
$opts = array();
foreach ($theme_files as $file) {
	$opts[$file] = $file;
}
echo form_text($locale['413'], 'opening_page', 'opening_page', $settings2['opening_page'], array('inline'=>1, 'max_length' => 100, 'required' => 1, 'error_text' => $locale['error_value']));
echo form_select($locale['418'], 'theme', 'theme', $opts, $settings2['theme'], array('inline'=>1, 'error_text' => $locale['error_value'], 'width' => '100%'));
$opts = array();
foreach ($admin_theme_files as $file) {
	$opts[$file] = $file;
}
echo form_select($locale['418a'], 'admin_theme', 'admin_theme', $opts, $settings2['admin_theme'], array('inline'=>1, 'error_text' => $locale['error_value'], 'width' => '100%'));
echo form_checkbox($locale['437'], 'bootstrap', 'bootstrap', $settings2['bootstrap'], array('inline' => 1));
closeside();

openside('');
echo form_textarea($locale['407'], 'intro', 'intro', $settings2['siteintro'], array('autosize'=>1));
echo form_textarea($locale['409'], 'description', 'description', $settings2['description'], array('autosize'=>1));
echo form_textarea($locale['410']."<br/><small>".$locale['411']."</small>", 'keywords', 'keywords', $settings2['keywords'], array('autosize'=>1));
echo form_textarea($locale['412'], 'footer', 'footer', $settings2['footer'], array('autosize' => 1));
closeside();
echo "</div><div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
$opts = array('http' => 'http://', 'https' => 'https://');
echo "<div class='alert alert-success'>\n";
echo "<i class='fa fa-external-link m-r-10'></i>";
echo "<span id='display_protocol'>".$settings2['site_protocol']."</span>://";
echo "<span id='display_host'>".$settings2['site_host']."</span>";
echo "<span id='display_port'>".($settings2['site_port'] ? ":".$settings2['site_port'] : "")."</span>";
echo "<span id='display_path'>".$settings2['site_path']."</span>";
echo "</div>\n";
echo form_select($locale['426'], 'site_protocol', 'site_protocol', $opts, $settings2['site_protocol'], array('width' => '100%', 'required' => 1, 'error_text' => $locale['error_value']));
echo form_text($locale['427'], 'site_host', 'site_host', $settings2['site_host'], array('max_length' => 255, 'required' => 1, 'error_text' => $locale['error_value']));
echo form_text($locale['429'], 'site_path', 'site_path', $settings2['site_path'], array('max_length' => 255, 'required' => 1, 'error_text' => $locale['error_value']));
echo form_text($locale['430'], 'site_port', 'site_port', $settings2['site_port'], array('max_length' => 4));
closeside();

openside('');
$dir = LOCALE.LOCALESET."search/";
$temp = opendir($dir);
$opts = array();
if (file_exists($dir)) {
	include LOCALE.LOCALESET."search/converter.php";
	while ($folder = readdir($temp)) {
		if (!in_array($folder, array("..", ".", 'users.json.php', 'converter.php', '.DS_Store', 'index.php'))) {
			$val = $filename_locale[$folder];
			$opts[$val] = ucwords($val);
		}
	}
}
echo form_select($locale['419'], 'default_search', 'default_search', $opts, $settings2['default_search'], array('width' => '100%'));
closeside();

openside('');
echo "<div class='alert alert-info'>".$locale['424']."</div>";
echo form_textarea($locale['420'], 'exclude_left', 'exclude_left', $settings2['exclude_left'], array('autosize' => 1,));
echo form_textarea($locale['421'], 'exclude_upper', 'exclude_upper', $settings2['exclude_upper'], array('autosize' => 1));
echo form_textarea($locale['435'], 'exclude_aupper', 'exclude_aupper', $settings2['exclude_aupper'], array('autosize' => 1));
echo form_textarea($locale['422'], 'exclude_lower', 'exclude_lower', $settings2['exclude_lower'], array('autosize' => 1));
echo form_textarea($locale['436'], 'exclude_blower', 'exclude_blower', $settings2['exclude_blower'], array('autosize' => 1));
echo form_textarea($locale['423'], 'exclude_right', 'exclude_right', $settings2['exclude_right'], array('autosize' => 1));
closeside();

echo "</div>\n</div>\n";


echo form_button($locale['750'], 'savesettings', 'savesettings', $locale['750'], array('class' => 'btn-success'));
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