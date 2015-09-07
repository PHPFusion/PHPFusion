<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_messages.php
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
require_once "../maincore.php";
pageAccess("S7");
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
add_breadcrumb(array('link' => ADMIN."settings_messages.php".$aidlink, 'title' => $locale['message_settings']));
/* Beta testers upgrade */
$settings = fusion_get_settings();

if (!isset($settings['pm_inbox_limit'])) {
	$new_settings = array(
		"settings_name" => "pm_inbox_limit",
		"settings_value" => 20,
	);
	dbquery_insert(DB_SETTINGS, $new_settings, "save", array("primary_key" => "settings_name"));
	if (!isset($settings['pm_outbox_limit'])) {
		$new_settings = array(
			"settings_name" => "pm_outbox_limit",
			"settings_value" => 20,
		);
		dbquery_insert(DB_SETTINGS, $new_settings, "save", array("primary_key" => "settings_name"));
		if (!isset($settings['pm_archive_limit'])) {
			$new_settings = array(
				"settings_name" => "pm_archive_limit",
				"settings_value" => 20,
			);
			dbquery_insert(DB_SETTINGS, $new_settings, "save", array("primary_key" => "settings_name"));
		}
		if (!isset($settings['pm_email_notify'])) {
			$new_settings = array(
				"settings_name" => "pm_email_notify",
				"settings_value" => 0,
			);
			dbquery_insert(DB_SETTINGS, $new_settings, "save", array("primary_key" => "settings_name"));
		}
		if (!isset($settings['pm_save_sent'])) {
			$new_settings = array(
				"settings_name" => "pm_save_sent",
				"settings_value" => TRUE,
			);
			dbquery_insert(DB_SETTINGS, $new_settings, "save", array("primary_key" => "settings_name"));
		}
		addNotice("success", "Beta upgrade done! New PM system installed. You do not need to reinstall 9, but all existing global configuration for PM is now reset to default.");
	}
	redirect(FUSION_SELF.$aidlink);
}

$pm_settings = array(
	"pm_inbox_limit" => fusion_get_settings("pm_inbox_limit"),
	"pm_outbox_limit" => fusion_get_settings("pm_outbox_limit"),
	"pm_archive_limit" => fusion_get_settings("pm_archive_limit"),
	"pm_email_notify" => fusion_get_settings("pm_email_notify"),
	"pm_save_sent" => fusion_get_settings("pm_save_sent"),
);
// end of beta testers upgrade
if (isset($_POST['save_settings'])) {
	foreach ($pm_settings as $key => $value) {
		if (isset($_POST[$key])) {
			$pm_settings[$key] = form_sanitizer($_POST[$key], $pm_settings[$key], $key);
		} else {
			$pm_settings[$key] = form_sanitizer($pm_settings[$key], $pm_settings[$key], $key);
		}
		if (defender::safe()) {
			dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$pm_settings[$key]."' WHERE settings_name='".$key."'");
		}
	}
	if (defender::safe()) {
		addNotice("success", $locale['900']);
		redirect(FUSION_SELF.$aidlink);
	}
}
opentable($locale['message_settings']);
echo openform('settingsform', 'post', FUSION_SELF.$aidlink);
echo "<div class='well'>".$locale['message_description']."</div>\n";
echo "<div class='row'>";
echo "<div class='col-xs-12 col-sm-6'>\n";
openside('');
echo "<span class='pull-right m-b-10 text-smaller'>".$locale['704']."</span>\n";
echo form_text('pm_inbox_limit', $locale['701'], $pm_settings['pm_inbox_limit'], array(
	"type" => "number",
	'max_length' => 2,
	'width' => '100px',
	'inline' => 1
));
echo form_text('pm_outbox_limit', $locale['702'], $pm_settings['pm_outbox_limit'], array(
	"type" => "number",
	'max_length' => 2,
	'width' => '100px',
	'inline' => 1
));
echo form_text('pm_archive_limit', $locale['703'], $pm_settings['pm_archive_limit'], array(
	"type" => "number",
	"max_length" => 2,
	'width' => '100px',
	'inline' => 1
));
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-6'>\n";
openside('');
echo form_select('pm_email_notify', $locale['709'], $pm_settings['pm_email_notify'], array(
	'options' => array('0' => $locale['519'], '1' => $locale['518']),
	'inline' => TRUE,
	'width' => '100%'
));
echo form_select('pm_save_sent', $locale['710'], $pm_settings['pm_save_sent'], array(
	'options' => array('0' => $locale['519'], '1' => $locale['518']),
	'inline' => 1,
	'width' => '100%'
));
closeside();
echo "</div>\n</div>\n";
echo form_button('save_settings', $locale['750'], $locale['750'], array('class' => 'btn-success'));
echo closeform();
closetable();
require_once THEMES."templates/footer.php";