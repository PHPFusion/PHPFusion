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
$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'settings_theme.php'.fusion_get_aidlink(), 'title' => $locale['theme_settings']]);

$settings_theme = [
    'admin_theme' => fusion_get_settings('admin_theme'),
    'theme'       => fusion_get_settings('theme'),
    'bootstrap'   => fusion_get_settings('bootstrap'),
    'entypo'      => fusion_get_settings('entypo'),
    'fontawesome' => fusion_get_settings('fontawesome'),
];

if (isset($_POST['savesettings'])) {

    $settings_theme = [
        'admin_theme' => form_sanitizer($_POST['admin_theme'], $settings_theme['admin_theme'], 'admin_theme'),
        'theme'       => form_sanitizer($_POST['theme'], $settings_theme['theme'], 'theme'),
        'bootstrap'   => form_sanitizer($_POST['bootstrap'], '0', 'bootstrap'),
        'entypo'      => form_sanitizer($_POST['entypo'], '0', 'entypo'),
        'fontawesome' => form_sanitizer($_POST['fontawesome'], '0', 'fontawesome'),
    ];

    if (\defender::safe()) {
        foreach ($settings_theme as $settings_key => $settings_value) {
            $data = [
                'settings_name'  => $settings_key,
                'settings_value' => $settings_value
            ];
            dbquery_insert(DB_SETTINGS, $data, 'update', array('primary_key' => 'settings_name'));
        }
        addNotice('success', $locale['900']);
        redirect(FUSION_REQUEST);
    }
}

$theme_files = makefilelist(THEMES, ".|..|templates|admin_themes", TRUE, "folders");

$admin_theme_files = makefilelist(THEMES."admin_themes/", ".|..", TRUE, "folders");

opentable($locale['theme_settings']);
echo "<div class='well'>".$locale['theme_description']."</div>";
echo openform('settingsform', 'post', FUSION_REQUEST, ['max_tokens' => 2]);
echo "<div class='row'><div class='col-xs-12 col-sm-12 col-md-6'>\n";

openside('');

$opts = array();
foreach ($theme_files as $file) {
    $opts[$file] = $file;
}

echo form_select('theme', $locale['418'], $settings_theme['theme'], [
    'options'        => $opts,
    'callback_check' => 'theme_exists',
    'inline'         => TRUE,
    'error_text'     => $locale['error_invalid_theme'],
    'width'          => '100%'
]);
// Admin Panel theme requires extra checks
$opts = array();
foreach ($admin_theme_files as $file) {
    $opts[$file] = $file;
}
echo form_select('admin_theme', $locale['418a'], $settings_theme['admin_theme'], [
    'options'    => $opts,
    'inline'     => TRUE,
    'error_text' => $locale['error_value'],
    'width'      => '100%'
]);

$choice_opts = [
    0 => $locale['disable'],
    1 => $locale['enable']
];
echo form_select('bootstrap', $locale['437'], $settings_theme['bootstrap'], [
                 'options' => $choice_opts,
                 'inline'  => TRUE
]);
echo form_select('entypo', $locale['441'], $settings_theme['entypo'], [
                 'options' => $choice_opts,
                  'inline' => TRUE
]);
echo form_select('fontawesome', $locale['442'], $settings_theme['fontawesome'], [
                 'options' => $choice_opts,
                 'inline'  => TRUE
]);

closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-success']);
echo closeform();
closetable();
require_once THEMES."templates/footer.php";
