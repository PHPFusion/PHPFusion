<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_theme.php
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

    $settings_theme['admin_theme'] = form_sanitizer($_POST['admin_theme'], "", "admin_theme");
    if (\defender::safe()) {
        dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_theme['admin_theme']."' WHERE settings_name='admin_theme'");
    }
    $settings_theme['theme'] = form_sanitizer($_POST['theme'], "", "theme");
    if (\defender::safe()) {
        dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_theme['theme']."' WHERE settings_name='theme'");
    }
    $settings_theme['bootstrap'] = form_sanitizer($_POST['bootstrap'], 0, "bootstrap");
    if (\defender::safe()) {
        dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_theme['bootstrap']."' WHERE settings_name='bootstrap'");
    }
    $settings_theme['entypo'] = form_sanitizer($_POST['entypo'], 0, "entypo");
    if (\defender::safe()) {
        dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_theme['entypo']."' WHERE settings_name='entypo'");
    }
    $settings_theme['fontawesome'] = form_sanitizer($_POST['fontawesome'], 0, "fontawesome");
    if ($defender->safe()) {
        dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_theme['fontawesome']."' WHERE settings_name='fontawesome'");
    }
	if (\defender::safe()) {
		addNotice("success", "<i class='fa fa-check-square-o m-r-10 fa-lg'></i>".$locale['900']);
		redirect(FUSION_SELF.$aidlink);
	}
}
$theme_files = makefilelist(THEMES, ".|..|templates|admin_themes", TRUE, "folders");
$admin_theme_files = makefilelist(THEMES."admin_themes/", ".|..", TRUE, "folders");

opentable($locale['theme_settings']);
echo "<div class='well'>".$locale['theme_description']."</div>";
echo openform('settingsform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 2));
echo "<div class='row'><div class='col-xs-12 col-sm-12 col-md-6'>\n";
openside('');
$opts = array();
foreach ($theme_files as $file) {
	$opts[$file] = $file;
}

//$opts['invalid_theme'] = 'None (test purposes)';

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
	'width' => '100%'
));

$choice_opts = array(
    0 => $locale['disable'],
    1 => $locale['enable']
);
echo form_select('bootstrap', $locale['437'], $settings_theme['bootstrap'],
                 array("options" => $choice_opts, 'inline' => 1));
echo form_select('entypo', $locale['441'], $settings_theme['entypo'], array("options" => $choice_opts, 'inline' => 1));
echo form_select('fontawesome', $locale['442'], $settings_theme['fontawesome'],
                 array("options" => $choice_opts, 'inline' => 1));

closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success'));
echo closeform();
closetable();
require_once THEMES."templates/footer.php";