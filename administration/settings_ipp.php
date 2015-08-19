<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_ipp.php
| Author: Hans Kristian Flaatten (Starefossen)
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
pageAccess('S10');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";

add_breadcrumb(array('link'=>ADMIN.'settings_ipp.php'.$aidlink, 'title'=>$locale['ipp_settings']));

if (isset($_POST['savesettings'])) {
	$links_per_page = form_sanitizer($_POST['links_per_page'], 15, 'links_per_page');
	$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$links_per_page' WHERE settings_name='links_per_page'") : '';
	$comments_per_page = form_sanitizer($_POST['comments_per_page'], 10, 'comments_per_page');
	$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$comments_per_page' WHERE settings_name='comments_per_page'") : '';
	if (!defined('FUSION_NULL')) {
		addNotice('success', $locale['900']);
		redirect(FUSION_SELF.$aidlink);
	}
}
$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}
opentable($locale['ipp_settings']);
echo openform('settingsform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
echo "<div class='well'>".$locale['ipp_description']."</div>";
echo "<div class='row'><div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
echo form_text('links_per_page', $locale['912'], $settings2['links_per_page'], array('inline'=>1, 'required' => 1, 'error_text' => $locale['error_value'], 'number' => 1, 'width' => '250px'));
echo form_text('comments_per_page', $locale['913'], $settings2['comments_per_page'], array('inline'=>1, 'required' => 1, 'error_text' => $locale['error_value'], 'number' => 1, 'width' => '250px'));
closeside('');
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success'));

echo closeform();
closetable();
require_once THEMES."templates/footer.php";
