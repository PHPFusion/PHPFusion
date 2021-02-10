<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
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
pageAccess('S6');
require_once THEMES.'templates/admin_header.php';
$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/settings.php");
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'settings_misc.php'.fusion_get_aidlink(), 'title' => $locale['misc_settings']]);

$settings = fusion_get_settings();

if (isset($_POST['savesettings'])) {
    $inputData = [
        'tinymce_enabled'        => post('tinymce_enabled') ? 1 : 0,
        'smtp_host'              => form_sanitizer($_POST['smtp_host'], '', 'smtp_host'),
        'smtp_port'              => form_sanitizer($_POST['smtp_port'], '', 'smtp_port'),
        'smtp_auth'              => isset($_POST['smtp_auth']) && !empty($_POST['smtp_username']) && !empty($_POST['smtp_password']) ? 1 : 0,
        'smtp_username'          => form_sanitizer($_POST['smtp_username'], '', 'smtp_username'),
        'smtp_password'          => form_sanitizer($_POST['smtp_password'], '', 'smtp_password'),
        'thumb_compression'      => form_sanitizer($_POST['thumb_compression'], '0', 'thumb_compression'),
        'guestposts'             => post('guestposts') ? 1 : 0,
        'comments_enabled'       => post('comments_enabled') ? 1 : 0,
        'comments_per_page'      => form_sanitizer($_POST['comments_per_page'], '10', 'comments_per_page'),
        'ratings_enabled'        => post('ratings_enabled') ? 1 : 0,
        'visitorcounter_enabled' => post('visitorcounter_enabled') ? 1 : 0,
        'rendertime_enabled'     => form_sanitizer($_POST['rendertime_enabled'], '0', 'rendertime_enabled'),
        'comments_avatar'        => post('comments_avatar') ? 1 : 0,
        'comments_sorting'       => form_sanitizer($_POST['comments_sorting'], 'DESC', 'comments_sorting'),
        'index_url_bbcode'       => post('index_url_bbcode') ? 1 : 0,
        'index_url_userweb'      => post('index_url_userweb') ? 1 : 0,
        'create_og_tags'         => post('create_og_tags') ? 1 : 0,
        'devmode'                => post('devmode') ? 1 : 0,
        'update_checker'         => post('update_checker') ? 1 : 0,
        'number_delimiter'       => sanitizer('number_delimiter', '.', 'number_delimiter'),
        'thousands_separator'    => sanitizer('thousands_separator', ',', 'thousands_separator')
    ];

    if (\defender::safe()) {
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

opentable($locale['misc_settings']);
echo "<div class='well'>".$locale['misc_description']."</div>";
echo openform('settingsform', 'post', FUSION_REQUEST);
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-8'>\n";
openside('');
$choice_arr = ['1' => $locale['yes'], '0' => $locale['no']];
echo form_checkbox('tinymce_enabled', $locale['662'], $settings['tinymce_enabled'], [
    'toggle' => TRUE
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
    'toggle' => TRUE
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
    'toggle' => TRUE
]);
closeside();

echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-4'>\n";
openside('');
$gd_opts = ['gd1' => $locale['607'], 'gd2' => $locale['608']];
echo form_select('thumb_compression', $locale['606'], $settings['thumb_compression'], [
    'options' => $gd_opts,
    'width'   => '100%'
]);
echo form_checkbox('guestposts', $locale['655'], $settings['guestposts'], [
    'toggle' => TRUE
]);
echo form_checkbox('ratings_enabled', $locale['672'], $settings['ratings_enabled'], [
    'toggle' => TRUE
]);
echo form_checkbox('visitorcounter_enabled', $locale['679'], $settings['visitorcounter_enabled'], [
    'toggle' => TRUE
]);
closeside();

openside('');
echo form_checkbox('index_url_bbcode', $locale['1031'], $settings['index_url_bbcode'], [
    'toggle' => TRUE
]);
echo form_checkbox('index_url_userweb', $locale['1032'], $settings['index_url_userweb'], [
    'toggle' => TRUE
]);
echo form_checkbox('create_og_tags', $locale['1030'], $settings['create_og_tags'], [
    'toggle' => TRUE
]);
closeside();

openside('');
echo form_checkbox('devmode', $locale['609'], $settings['devmode'], [
    'toggle' => TRUE
]);
closeside();

openside('');
echo form_checkbox('update_checker', $locale['610'], $settings['update_checker'], [
    'toggle' => TRUE
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
