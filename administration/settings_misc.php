<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_misc.php
| Author: Core Development Team (coredevs@phpfusion.com)
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
pageAccess('S6');

$settings = fusion_get_settings();

$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/settings.php");

add_breadcrumb(['link' => ADMIN.'settings_misc.php'.fusion_get_aidlink(), 'title' => $locale['misc_settings']]);

if (post('savesettings')) {
    $inputData = [
        'tinymce_enabled'        => sanitizer('tinymce_enabled', '0', 'tinymce_enabled'),
        'smtp_host'              => sanitizer('smtp_host', '', 'smtp_host'),
        'smtp_port'              => sanitizer('smtp_port', '', 'smtp_port'),
        'smtp_auth'              => post('smtp_auth') && !empty(post('smtp_username')) && !empty(post('smtp_password')) ? 1 : 0,
        'smtp_username'          => sanitizer('smtp_username', '', 'smtp_username'),
        'smtp_password'          => sanitizer('smtp_password', '', 'smtp_password'),
        'thumb_compression'      => sanitizer('thumb_compression', '0', 'thumb_compression'),
        'guestposts'             => sanitizer('guestposts', '0', 'guestposts'),
        'comments_enabled'       => sanitizer('comments_enabled', '0', 'comments_enabled'),
        'comments_per_page'      => sanitizer('comments_per_page', '10', 'comments_per_page'),
        'ratings_enabled'        => sanitizer('ratings_enabled', '0', 'ratings_enabled'),
        'visitorcounter_enabled' => sanitizer('visitorcounter_enabled', '0', 'visitorcounter_enabled'),
        'rendertime_enabled'     => sanitizer('rendertime_enabled', '0', 'rendertime_enabled'),
        'comments_avatar'        => sanitizer('comments_avatar', '0', 'comments_avatar'),
        'comments_sorting'       => sanitizer('comments_sorting', 'DESC', 'comments_sorting'),
        'index_url_bbcode'       => sanitizer('index_url_bbcode', '0', 'index_url_bbcode'),
        'index_url_userweb'      => sanitizer('index_url_userweb', '0', 'index_url_userweb'),
        'create_og_tags'         => sanitizer('create_og_tags', '0', 'create_og_tags'),
        'devmode'                => sanitizer('devmode', '0', 'devmode'),
        'update_checker'         => sanitizer('update_checker', '0', 'update_checker'),
        'number_delimiter'       => sanitizer('number_delimiter', '.', 'number_delimiter'),
        'thousands_separator'    => sanitizer('thousands_separator', ',', 'thousands_separator')
    ];

    if (fusion_safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ]);
        }

        add_notice('success', $locale['900']);
        redirect(FUSION_REQUEST);
    }
}

opentable($locale['misc_settings']);
echo "<div class='well'>".$locale['misc_description']."</div>";
echo openform('settingsform', 'post', FUSION_REQUEST);
echo "<div class='".grid_row()."'>\n";
echo "<div class='".grid_column_size(100, 100, 70)."'>\n";
openside('');
$choice_arr = ['1' => $locale['yes'], '0' => $locale['no']];
echo form_select('tinymce_enabled', $locale['662'], $settings['tinymce_enabled'], [
    'options' => $choice_arr,
    'inline'  => TRUE,
    'ext_tip' => $locale['663']
]);
closeside();
openside('');
echo form_text('smtp_host', $locale['664'], $settings['smtp_host'], [
    'max_length' => 200,
    'inline'     => TRUE
]);
echo form_text('smtp_port', $locale['674'], $settings['smtp_port'], [
    'max_length' => 10,
    'inline'     => TRUE
]);
echo form_select('smtp_auth', $locale['698'], $settings['smtp_auth'], [
    'options' => $choice_arr,
    'inline'  => TRUE,
    'ext_tip' => $locale['665']
]);
echo form_text('smtp_username', $locale['666'], $settings['smtp_username'], [
    'max_length' => 100,
    'inline'     => TRUE
]);
echo form_text('smtp_password', $locale['667'], $settings['smtp_password'], [
    'max_length' => 100,
    'inline'     => TRUE
]);
closeside();
openside('');
$opts = ['0' => $locale['no'], '1' => $locale['689'], '2' => $locale['690']];
echo form_checkbox('rendertime_enabled', $locale['688'], $settings['rendertime_enabled'], [
    'options' => $opts,
    'inline'  => TRUE,
    'type'    => 'radio'
]);
closeside();

openside('');
echo form_checkbox('comments_enabled', $locale['671'], $settings['comments_enabled'], [
    'inline'  => TRUE,
    'options' => $choice_arr,
    'type'    => 'radio'
]);
echo form_text('comments_per_page', $locale['913'], $settings['comments_per_page'], [
    'inline'      => TRUE,
    'error_text'  => $locale['error_value'],
    'type'        => 'number',
    'inner_width' => '150px'
]);

$sort_opts = ['ASC' => $locale['685'], 'DESC' => $locale['686']];
echo form_checkbox('comments_sorting', $locale['684'], $settings['comments_sorting'], [
    'inline'  => TRUE,
    'options' => $sort_opts,
    'type'    => 'radio'
]);
echo form_checkbox('comments_avatar', $locale['656'], $settings['comments_avatar'], [
    'inline'  => TRUE,
    'options' => $choice_arr,
    'type'    => 'radio'
]);
closeside();

echo "</div>\n<div class='".grid_column_size(100, 100, 30)."'>\n";
openside('');
$gd_opts = ['gd1' => $locale['607'], 'gd2' => $locale['608']];
echo form_select('thumb_compression', $locale['606'], $settings['thumb_compression'], [
    'options' => $gd_opts,
    'width'   => '100%'
]);
echo form_select('guestposts', $locale['655'], $settings['guestposts'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
echo form_select('ratings_enabled', $locale['672'], $settings['ratings_enabled'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
echo form_select('visitorcounter_enabled', $locale['679'], $settings['visitorcounter_enabled'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
echo form_select('create_og_tags', $locale['1030'], $settings['create_og_tags'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
closeside();
openside('');
echo form_select('index_url_bbcode', $locale['1031'], $settings['index_url_bbcode'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
echo form_select('index_url_userweb', $locale['1032'], $settings['index_url_userweb'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
closeside();

openside('');
echo form_select('devmode', $locale['609'], $settings['devmode'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
closeside();

openside('');
echo form_select('update_checker', $locale['610'], $settings['update_checker'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
closeside();

openside('');

$options = [
    '.' => '.',
    ',' => ','
];
echo form_select('number_delimiter', $locale['611'], $settings['number_delimiter'], [
    'options' => $options,
    'width'   => '100%'
]);

echo form_select('thousands_separator', $locale['612'], $settings['thousands_separator'], [
    'options' => $options,
    'width'   => '100%'
]);

closeside();

echo "</div>\n</div>";
echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-success']);
echo closeform();
closetable();
require_once THEMES.'templates/footer.php';
