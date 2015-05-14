<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_registration.php
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
pageAccess('S4');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";

add_breadcrumb(array('link'=>ADMIN."settings_register.php".$aidlink, 'title'=>$locale['register_settings']));

if ($settings['tinymce_enabled']) {
	echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
} else {
	require_once INCLUDES."html_buttons_include.php";
}
$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}
if (isset($_POST['savesettings'])) {
	$error = 0;
	if (addslash($_POST['license_agreement']) != $settings2['license_agreement']) {
		$license_lastupdate = time();
	} else {
		$license_lastupdate = $settings2['license_lastupdate'];
	}
	$license_agreement = addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['license_agreement']));
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['enable_registration']) ? $_POST['enable_registration'] : "1")."' WHERE settings_name='enable_registration'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['email_verification']) ? $_POST['email_verification'] : "1")."' WHERE settings_name='email_verification'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['admin_activation']) ? $_POST['admin_activation'] : "0")."' WHERE settings_name='admin_activation'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['display_validation']) ? $_POST['display_validation'] : "1")."' WHERE settings_name='display_validation'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['enable_terms']) ? $_POST['enable_terms'] : "0")."' WHERE settings_name='enable_terms'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$license_agreement' WHERE settings_name='license_agreement'");
	if (!$result) {
		$error = 1;
	}
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$license_lastupdate' WHERE settings_name='license_lastupdate'");
	if (!$result) {
		$error = 1;
	}
	if ($error) {
		addNotice('danger', $locale['901']);
	} else {
		addNotice('success', $locale['900']);
	}
	redirect(FUSION_SELF.$aidlink);
}

opentable($locale['register_settings']);
echo openform('settingsform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
$opts = array('1' => $locale['518'], '0' => $locale['519']);
echo "<div class='well'>".$locale['register_description']."</div>\n";
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-8'>\n";
openside('');
echo form_select('enable_terms', $locale['558'], $opts, $settings2['enable_terms']);
echo form_textarea('license_agreement', $locale['559'], $settings2['license_agreement'], array(
	'form_name' => 'settingsform',
	'input_id' => 'enable_license_agreement',
	'autosize' => !$settings['tinymce_enabled'],
	'html'=> !$settings['tinymce_enabled'])
);
closeside();
echo "</div><div class='col-xs-12 col-sm-4'>\n";
openside('');
echo form_select('enable_registration', $locale['551'], $opts, $settings2['enable_registration']);
echo form_select('email_verification', $locale['552'], $opts, $settings2['email_verification']);
echo form_select('admin_activation', $locale['557'],  $opts, $settings2['admin_activation']);
echo form_select('display_validation', $locale['553'], $opts, $settings2['display_validation']);
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success'));
echo closeform();
closetable();
require_once THEMES."templates/footer.php";
?>