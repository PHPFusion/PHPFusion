<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks_settings.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }
pageAccess('W');

add_breadcrumb(array('link'=>INFUSIONS.'weblinks/weblinks_admin.php'.$aidlink.'&amp;section=weblinks_settings', 'title'=>'Weblink Settings'));

if (isset($_POST['savesettings'])) {
	$inputArray = array(
		"links_per_page" => form_sanitizer($_POST['links_per_page'], 0, "links_per_page"),
		"links_allow_submission" => isset($_POST['links_allow_submission']) ? 1 : 0,
		"links_extended_required" => isset($_POST['links_extended_required']) ? 1 : 0,
	);

	if (defender::safe()) {
		foreach ($inputArray as $settings_name => $settings_value) {
			$inputSettings = array(
				"settings_name" => $settings_name, "settings_value" => $settings_value, "settings_inf" => "weblinks",
			);
			dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", array("primary_key" => "settings_name"));
		}
		addNotice("success", $locale['900']);
		redirect(FUSION_REQUEST);
	} else {
		addNotice('danger', $locale['901']);
	}
}

echo openform('settingsform', 'post', FUSION_REQUEST, array('class' => "m-t-20"));
echo "<div class='well'>".$locale['wl_0006']."</div>";
echo "<div class='row'><div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside("");
echo form_text('links_per_page', $locale['603'], $wl_settings['links_per_page'], array('inline'=>1, 'required' => 1, 'error_text' => $locale['error_value'], 'number' => 1, 'width' => '250px'));
closeside();
echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside("");
echo form_checkbox('links_allow_submission', $locale['wl_0601'], $wl_settings['links_allow_submission']);
echo form_checkbox('links_extended_required', $locale['wl_0602'], $wl_settings['links_extended_required']);
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['604'], $locale['604'], array('class' => 'btn-success'));
echo closeform();