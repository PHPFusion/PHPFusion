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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageAccess('S3');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');

add_breadcrumb(['link' => ADMIN.'settings_theme.php'.fusion_get_aidlink(), 'title' => $locale['theme_settings']]);

$settings = fusion_get_settings();

if (post('savesettings')) {
    $inputData = [
        'admin_theme' => sanitizer('admin_theme', $settings['admin_theme'], 'admin_theme'),
        'theme'       => sanitizer('theme', $settings['theme'], 'theme'),
        'bootstrap'   => sanitizer('bootstrap', '0', 'bootstrap'),
        'entypo'      => sanitizer('entypo', '0', 'entypo'),
        'fontawesome' => sanitizer('fontawesome', '0', 'fontawesome'),
    ];

    if (\Defender::safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ]);
        }

        addNotice('success', $locale['900']);
        redirect(FUSION_REQUEST);
    }
}

$theme_files = makefilelist(THEMES, ".|..|templates|admin_themes", TRUE, "folders");

$admin_theme_files = makefilelist(THEMES."admin_themes/", ".|..", TRUE, "folders");

opentable($locale['theme_settings']);
echo "<div class='well'>".$locale['theme_description']."</div>";
echo openform('settingsform', 'post');
echo "<div class='".grid_row()."'>";
echo "<div class='".grid_column_size(100, 100, 50)."'>\n";

openside('');

$opts = [];
foreach ($theme_files as $file) {
    $opts[$file] = $file;
}

echo form_select('theme', $locale['418'], $settings['theme'], [
    'options'        => $opts,
    'callback_check' => 'theme_exists',
    'inline'         => TRUE,
    'error_text'     => $locale['error_invalid_theme'],
    'width'          => '100%'
]);
// Admin Panel theme requires extra checks
$opts = [];
foreach ($admin_theme_files as $file) {
    $opts[$file] = $file;
}
echo form_select('admin_theme', $locale['418a'], $settings['admin_theme'], [
    'options'    => $opts,
    'inline'     => TRUE,
    'error_text' => $locale['error_value'],
    'width'      => '100%'
]);

$choice_opts = [
    0 => $locale['disable'],
    1 => $locale['enable']
];
echo form_select('bootstrap', $locale['437'], $settings['bootstrap'], [
    'options' => $choice_opts,
    'inline'  => TRUE
]);
echo form_select('entypo', $locale['441'], $settings['entypo'], [
    'options' => $choice_opts,
    'inline'  => TRUE
]);
echo form_select('fontawesome', $locale['442'], $settings['fontawesome'], [
    'options' => $choice_opts,
    'inline'  => TRUE
]);

closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-success']);
echo closeform();
closetable();
require_once THEMES.'templates/footer.php';
