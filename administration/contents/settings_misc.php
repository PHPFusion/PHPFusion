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

defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', LOCALE . LOCALESET . 'admin/settings.php');

$settings = fusion_get_settings();

$contents = [
    'post'        => 'pf_post',
    'view'        => 'pf_view',
    'button'      => 'pf_button',
    'js'          => 'pf_js',
    'settings'    => TRUE,
    'link'        => ( $admin_link ?? '' ),
    'title'       => $locale['misc_settings'],
    'description' => $locale['misc_description'],
    'actions'     => [ 'post' => [ 'savesettings' => 'settingsFrm' ] ]
];

function pf_post() {

    $locale = fusion_get_locale();

    if ( admin_post('savesettings') ) {
        $inputData = [
            'tinymce_enabled'        => post('tinymce_enabled') ? 1 : 0,
            'smtp_host'              => form_sanitizer($_POST['smtp_host'], '', 'smtp_host'),
            'smtp_port'              => form_sanitizer($_POST['smtp_port'], '', 'smtp_port'),
            'smtp_auth'              => isset($_POST['smtp_auth']) && ! empty($_POST['smtp_username']) && ! empty($_POST['smtp_password']) ? 1 : 0,
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

        if ( fusion_safe() ) {
            foreach ( $inputData as $settings_name => $settings_value ) {
                dbquery("UPDATE " . DB_SETTINGS . " SET settings_value=:settings_value WHERE settings_name=:settings_name",
                        [
                            ':settings_value' => $settings_value,
                            ':settings_name'  => $settings_name
                        ]);
            }

            add_notice('success', $locale['900']);
            redirect(FUSION_REQUEST);
        }
    }
}

function pf_view() {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();
    $choice_arr = [ '1' => $locale['yes'], '0' => $locale['no'] ];

    echo openform('settingsFrm', 'POST');

    openside();
    echo form_checkbox('tinymce_enabled', $locale['662'], $settings['tinymce_enabled'], [ 'toggle' => TRUE ]);
    closeside();

    echo '<h6>Email Server</h6>';
    openside('Email Settings<small>Server email configurations</small>', TRUE);
    echo form_text('smtp_host', $locale['664'], $settings['smtp_host'], [
        'max_length' => 200,
    ]);
    echo form_text('smtp_port', $locale['674'], $settings['smtp_port'], [
        'max_length' => 10,
    ]);
    echo form_select('smtp_auth', $locale['698'], $settings['smtp_auth'], [
        'options' => $choice_arr,
        'ext_tip' => $locale['665']
    ]);
    echo form_text('smtp_username', $locale['666'], $settings['smtp_username'], [
        'max_length' => 100,
    ]);
    echo form_text('smtp_password', $locale['667'], $settings['smtp_password'], [
        'max_length' => 100,
    ]);
    closeside();

    echo '<h6>Post, Comments & Ratings</h6>';
    openside();
    echo form_checkbox('guestposts', $locale['655'], $settings['guestposts'], [ 'toggle' => TRUE ]);
    closeside();
    openside();
    echo form_checkbox('comments_enabled', $locale['671'], $settings['comments_enabled'], [ 'toggle' => TRUE ]);
    closeside();
    echo '<div id="a_comments_enabled" style="display:' . ( $settings['comments_enabled'] ? 'block' : 'none' ) . ';">';
    openside('');
    echo form_text('comments_per_page', $locale['913'], $settings['comments_per_page'], [
        //'inline'      => TRUE,
        'error_text'  => $locale['error_value'],
        'type'        => 'number',
        'inner_width' => '150px'
    ]);
    echo form_checkbox('comments_sorting', $locale['684'], $settings['comments_sorting'], [
        //'inline'  => TRUE,
        'options' => [ 'ASC' => $locale['685'], 'DESC' => $locale['686'] ],
        'type'    => 'radio'
    ]);
    closeside();
    openside();
    echo form_checkbox('comments_avatar', $locale['656'], $settings['comments_avatar'], [ 'toggle' => TRUE ]);
    closeside();
    echo '</div>';
    openside();
    echo form_checkbox('ratings_enabled', $locale['672'], $settings['ratings_enabled'], [ 'toggle' => TRUE ]);
    closeside();
    openside('Image Handling<small>Image compression configurations</small>', TRUE);
    echo form_select('thumb_compression', $locale['606'], $settings['thumb_compression'], [
        'options' => [ 'gd1' => $locale['607'], 'gd2' => $locale['608'] ],
        'width'   => '100%'
    ]);
    closeside();
    openside();
    echo form_checkbox('index_url_bbcode', $locale['1031'], $settings['index_url_bbcode'], [ 'toggle' => TRUE ]);
    closeside();
    openside();
    echo form_checkbox('index_url_userweb', $locale['1032'], $settings['index_url_userweb'], [ 'toggle' => TRUE ]);
    closeside();
    openside();
    echo form_checkbox('create_og_tags', $locale['1030'], $settings['create_og_tags'], [ 'toggle' => TRUE ]);
    closeside();
    openside();
    echo form_checkbox('rendertime_enabled', $locale['688'], $settings['rendertime_enabled'], [
        'options' => [ '0' => $locale['no'], '1' => $locale['689'], '2' => $locale['690'] ],
        'type'    => 'radio'
    ]);
    closeside();
    openside();
    echo form_checkbox('visitorcounter_enabled',
                       $locale['679'],
                       $settings['visitorcounter_enabled'],
                       [ 'toggle' => TRUE ]);
    closeside();
    openside();
    echo form_checkbox('devmode', $locale['609'], $settings['devmode'], [ 'toggle' => TRUE ]);
    closeside();
    openside();
    echo form_checkbox('update_checker', $locale['610'], $settings['update_checker'], [ 'toggle' => TRUE ]);
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

    echo '<noscript>';
    echo '<div class="spacer-sm">';
    echo pf_button();
    echo '</div>';
    echo '</noscript>';

    echo closeform();
}

function pf_button() {

    $locale = fusion_get_locale();

    return form_button('savesettings', $locale['750'], $locale['750'], [ 'class' => 'btn-primary' ]);
}

function pf_js() {

    return "checkboxToggle('comments_enabled', 'a_comments_enabled');";
}

