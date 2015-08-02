<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_theme.php
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
pageAccess('S3');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
add_breadcrumb(array('link' => ADMIN."settings_theme.php".$aidlink, 'title' => $locale['theme_settings']));

// These are the default settings and the only settings we expect to be posted
$settings_theme = array(
	'admin_theme' => fusion_get_settings('admin_theme'),
	'theme' => fusion_get_settings('theme'),
	'bootstrap' => fusion_get_settings('bootstrap'),
	'entypo' => fusion_get_settings('entypo'),
	'fontawesome' => fusion_get_settings('fontawesome'),
);

// Saving settings
if (isset($_POST['savesettings'])) {
	foreach ($settings_theme as $key => $value) {
		if (isset($_POST[$key])) {
			$settings_theme[$key] = form_sanitizer($_POST[$key], $settings_theme[$key], $key);
		} else {
			$settings_theme[$key] = form_sanitizer($settings_theme[$key], $settings_theme[$key], $key);
		}
		if (!defined('FUSION_NULL')) {
			dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_theme[$key]."' WHERE settings_name='".$key."'");
		}
	}
	if (!defined('FUSION_NULL')) {
		addNotice("success", "<i class='fa fa-check-square-o m-r-10 fa-lg'></i>".$locale['900']);
		redirect(FUSION_SELF.$aidlink);
	}
}
$theme_files = makefilelist(THEMES, ".|..|templates|admin_templates", TRUE, "folders");
$admin_theme_files = makefilelist(THEMES."admin_templates/", ".|..", TRUE, "folders");

opentable($locale['main_settings']);
echo "<div class='well'>".$locale['main_description']."</div>";
echo openform('settingsform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 2));
echo "<div class='row'><div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
$opts = array();
foreach ($theme_files as $file) {
	$opts[$file] = $file;
}
$opts['invalid_theme'] = 'None (test purposes)';

echo form_select('theme', $locale['418'], $settings_theme['theme'], array('options' => $opts,
	'callback_check' => 'theme_exists',
	'inline' => 1,
	'error_text' => $locale['error_invalid_theme'],
	'width' => '100%'));
// Admin Panel theme requires extra checks
$opts = array();
foreach ($admin_theme_files as $file) {
	$opts[$file] = $file;
}
echo form_select('admin_theme', $locale['418a'], $settings_theme['admin_theme'], array('options' => $opts,
	'inline' => 1,
	'error_text' => $locale['error_value'],
	'width' => '100%'));
echo form_checkbox('bootstrap', $locale['437'], $settings_theme['bootstrap'], array('toggle' => 1, 'inline' => 1));
echo form_checkbox('entypo', $locale['441'], $settings_theme['entypo'], array('toggle' => 1, 'inline' => 1));
echo form_checkbox('fontawesome', $locale['442'], $settings_theme['fontawesome'], array('toggle' => 1, 'inline' => 1));
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success'));
echo closeform();
closetable();
require_once THEMES."templates/footer.php";