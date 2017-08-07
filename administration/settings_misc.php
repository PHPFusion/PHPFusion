<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_misc.php
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
pageAccess('S6');
require_once THEMES."templates/admin_header.php";
$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/settings.php");
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'settings_misc.php'.fusion_get_aidlink(), 'title' => $locale['misc_settings']]);

$settings_misc = [
    'tinymce_enabled'        => fusion_get_settings('tinymce_enabled'),
    'smtp_host'              => fusion_get_settings('smtp_host'),
    'smtp_port'              => fusion_get_settings('smtp_port'),
    'smtp_auth'              => fusion_get_settings('smtp_auth'),
    'smtp_username'          => fusion_get_settings('smtp_username'),
    'smtp_password'          => fusion_get_settings('smtp_password'),
    'thumb_compression'      => fusion_get_settings('thumb_compression'),
    'mime_check'             => fusion_get_settings('mime_check'),
    'guestposts'             => fusion_get_settings('guestposts'),
    'comments_enabled'       => fusion_get_settings('comments_enabled'),
    'comments_per_page'      => fusion_get_settings('comments_per_page'),
    'ratings_enabled'        => fusion_get_settings('ratings_enabled'),
    'visitorcounter_enabled' => fusion_get_settings('visitorcounter_enabled'),
    'rendertime_enabled'     => fusion_get_settings('rendertime_enabled'),
    'comments_avatar'        => fusion_get_settings('comments_avatar'),
    'comments_sorting'       => fusion_get_settings('comments_sorting'),
    'index_url_bbcode'       => fusion_get_settings('index_url_bbcode'),
    'index_url_userweb'      => fusion_get_settings('index_url_userweb'),
    'create_og_tags'         => fusion_get_settings('create_og_tags')
];

if (isset($_POST['savesettings'])) {

    $settings_misc = [
        'tinymce_enabled'        => form_sanitizer($_POST['tinymce_enabled'], '0', 'tinymce_enabled'),
        'smtp_host'              => form_sanitizer($_POST['smtp_host'], '', 'smtp_host'),
        'smtp_port'              => form_sanitizer($_POST['smtp_port'], '', 'smtp_port'),
        'smtp_auth'              => isset($_POST['smtp_auth']) && !empty($_POST['smtp_username']) && !empty($_POST['smtp_password']) ? 1 : 0,
        'smtp_username'          => form_sanitizer($_POST['smtp_username'], '', 'smtp_username'),
        'smtp_password'          => form_sanitizer($_POST['smtp_password'], '', 'smtp_password'),
        'thumb_compression'      => form_sanitizer($_POST['thumb_compression'], '0', 'thumb_compression'),
        'mime_check'             => form_sanitizer($_POST['mime_check'], '0', 'mime_check'),
        'guestposts'             => form_sanitizer($_POST['guestposts'], '0', 'guestposts'),
        'comments_enabled'       => form_sanitizer($_POST['comments_enabled'], '0', 'comments_enabled'),
        'comments_per_page'      => form_sanitizer($_POST['comments_per_page'], '10', 'comments_per_page'),
        'ratings_enabled'        => form_sanitizer($_POST['ratings_enabled'], '0', 'ratings_enabled'),
        'visitorcounter_enabled' => form_sanitizer($_POST['visitorcounter_enabled'], '0', 'visitorcounter_enabled'),
        'rendertime_enabled'     => form_sanitizer($_POST['rendertime_enabled'], '0', 'rendertime_enabled'),
        'comments_avatar'        => form_sanitizer($_POST['comments_avatar'], '0', 'comments_avatar'),
        'comments_sorting'       => form_sanitizer($_POST['comments_sorting'], 'DESC', 'comments_sorting'),
        'index_url_bbcode'       => form_sanitizer($_POST['index_url_bbcode'], '0', 'index_url_bbcode'),
        'index_url_userweb'      => form_sanitizer($_POST['index_url_userweb'], '0', 'index_url_userweb'),
        'create_og_tags'         => form_sanitizer($_POST['create_og_tags'], '0', 'create_og_tags')
    ];

    if (\defender::safe()) {
        foreach ($settings_misc as $settings_name => $settings_value) {
            $data = [
                'settings_name'  => $settings_name,
                'settings_value' => $settings_value
            ];
            dbquery_insert(DB_SETTINGS, $data, 'update', ['primary_key' => 'settings_name', 'keep_session' => TRUE]);
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
echo form_select('tinymce_enabled', $locale['662'], $settings_misc['tinymce_enabled'], [
    'options' => $choice_arr,
    'inline'  => TRUE,
    'ext_tip' => $locale['663']
]);
closeside();
openside('');
echo form_text('smtp_host', $locale['664']."<br/>", $settings_misc['smtp_host'], [
    'max_length' => 200,
    'inline'     => TRUE
]);
echo form_text('smtp_port', $locale['674'], $settings_misc['smtp_port'], [
    'max_length' => 10,
    'inline' => TRUE
]);
echo form_select('smtp_auth', $locale['698'], $settings_misc['smtp_auth'], [
    'options' => $choice_arr,
    'inline'  => TRUE,
    'ext_tip' => $locale['665']
]);
echo form_text('smtp_username', $locale['666'], $settings_misc['smtp_username'], [
    'max_length' => 100,
    'inline'     => TRUE
]);
echo form_text('smtp_password', $locale['667'], $settings_misc['smtp_password'], [
    'max_length' => 100,
    'inline'     => TRUE
]);
closeside();
openside('');
$opts = ['0' => $locale['no'], '1' => $locale['689'], '2' => $locale['690']];
echo form_checkbox('rendertime_enabled', $locale['688'], $settings_misc['rendertime_enabled'], [
    'options' => $opts,
    'inline'  => TRUE,
    'type'    => 'radio',
]);
closeside();

openside('');
echo form_checkbox('comments_enabled', $locale['671'], $settings_misc['comments_enabled'], [
    'inline'  => TRUE,
    'options' => $choice_arr,
    'type'    => 'radio'
]);
echo form_text('comments_per_page', $locale['913'], $settings_misc['comments_per_page'], [
    'inline'      => TRUE,
    'error_text'  => $locale['error_value'],
    'type'        => 'number',
    'inner_width' => '150px'
]);

$sort_opts = ['ASC' => $locale['685'], 'DESC' => $locale['686']];
echo form_checkbox('comments_sorting', $locale['684'], $settings_misc['comments_sorting'], [
    'inline'  => TRUE,
    'options' => $sort_opts,
    'type'    => 'radio',
]);
echo form_checkbox('comments_avatar', $locale['656'], $settings_misc['comments_avatar'], [
    'inline'  => TRUE,
    'options' => $choice_arr,
    'type'    => 'radio',
]);
closeside();

echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-4'>\n";
openside('');
$gd_opts = ['gd1' => $locale['607'], 'gd2' => $locale['608']];
echo form_select('thumb_compression', $locale['606'], $settings_misc['thumb_compression'], [
    'options' => $gd_opts,
    'width'   => '100%'
]);
echo form_select('mime_check', $locale['699f'], $settings_misc['mime_check'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
echo form_select('guestposts', $locale['655'], $settings_misc['guestposts'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
echo form_select('ratings_enabled', $locale['672'], $settings_misc['ratings_enabled'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
echo form_select('visitorcounter_enabled', $locale['679'], $settings_misc['visitorcounter_enabled'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
echo form_select('create_og_tags', $locale['1030'], $settings_misc['create_og_tags'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
closeside();
openside('');
echo form_select('index_url_bbcode', $locale['1031'], $settings_misc['index_url_bbcode'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
echo form_select('index_url_userweb', $locale['1032'], $settings_misc['index_url_userweb'], [
    'options' => $choice_arr,
    'width'   => '100%'
]);
closeside();
echo "</div>\n</div>";
echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-success']);
echo closeform();
closetable();
require_once THEMES."templates/footer.php";
