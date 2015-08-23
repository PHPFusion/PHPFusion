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
	$links_per_page = form_sanitizer($_POST['links_per_page'], 15, 'links_per_page');
	$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$links_per_page' WHERE settings_name='links_per_page'") : '';
	if (!defined('FUSION_NULL')) {
		addNotice('success', $locale['601']);
		redirect(FUSION_SELF.$aidlink."&amp;section=settings");
	}
}

$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}

$formaction = FUSION_SELF.$aidlink."&amp;section=settings";

opentable($locale['600']);
echo openform('settingsform', 'post', $formaction, array('max_tokens' => 1));
echo "<div class='well'>".$locale['603']."</div>";
echo "<div class='row'><div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
echo form_text('links_per_page', $locale['603'], $settings2['links_per_page'], array('inline'=>1, 'required' => 1, 'error_text' => $locale['error_value'], 'number' => 1, 'width' => '250px'));
closeside('');
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['604'], $locale['604'], array('class' => 'btn-success'));
echo closeform();
closetable();
