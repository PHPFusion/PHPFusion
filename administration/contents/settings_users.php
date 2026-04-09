<?php

/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_users.php
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
    'link'        => ( $admin_link ?? '' ),
    'settings'    => TRUE,
    'title'       => $locale['user_settings'],
    'description' => $locale['user_description'],
    'actions'     => [ 'post' => [ 'savesettings' => 'settingsFrm' ] ]
];

function pf_post() {

    $locale = fusion_get_locale();

    if ( admin_post('savesettings') ) {
        $inputData = [
            'enable_deactivation'   => post('enable_deactivation') ? 1 : 0,
            'deactivation_period'   => form_sanitizer($_POST['deactivation_period'], '365', 'deactivation_period'),
            'deactivation_response' => form_sanitizer($_POST['deactivation_response'], '14', 'deactivation_response'),
            'deactivation_action'   => form_sanitizer($_POST['deactivation_action'], '0', 'deactivation_action'),
            'hide_userprofiles'     => post('hide_userprofiles') ? 1 : 0,
            'avatar_filesize'       => form_sanitizer($_POST['calc_b'],
                                                      '15',
                                                      'calc_b') * form_sanitizer($_POST['calc_c'], '100000', 'calc_c'),
            'avatar_width'          => form_sanitizer($_POST['avatar_width'], '100', 'avatar_width'),
            'avatar_height'         => form_sanitizer($_POST['avatar_height'], '100', 'avatar_height'),
            'avatar_ratio'          => form_sanitizer($_POST['avatar_ratio'], '0', 'avatar_ratio'),
            'username_change'       => post('username_change') ? 1 : 0,
            'username_ban'          => stripinput($_POST['username_ban']),
            'userthemes'            => post('userthemes') ? 1 : 0,
            'multiple_logins'       => post('multiple_logins') ? 1 : 0,
        ];

        if ( \defender::safe() ) {
            foreach ( $inputData as $settings_name => $settings_value ) {
                dbquery("UPDATE " . DB_SETTINGS . " SET settings_value=:settings_value WHERE settings_name=:settings_name",
                        [
                            ':settings_value' => $settings_value,
                            ':settings_name'  => $settings_name
                        ]);
            }

            if ( ! post('enable_deactivation') ) {
                $result = dbquery("UPDATE " . DB_USERS . " SET user_status='0' WHERE user_status='5'");
            }

            add_notice('success', $locale['900']);
            redirect(FUSION_REQUEST);
        }
    }
}

function pf_view() {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    echo openform('settingsFrm', 'POST');

    echo '<h6>User Account Settings</h6>';

    openside();
    echo form_checkbox('hide_userprofiles', $locale['673'], $settings['hide_userprofiles'], [ 'toggle' => TRUE ]);
    closeside();
    openside();
    echo form_checkbox('userthemes', $locale['668'], $settings['userthemes'], [ 'toggle' => TRUE ]);
    closeside();
    openside();
    echo form_checkbox('multiple_logins',
                       $locale['1014'],
                       $settings['multiple_logins'],
                       [ 'toggle' => TRUE, 'tip' => $locale['1014a'] ]);
    closeside();
    openside();
    echo form_checkbox('username_change', $locale['691'], $settings['username_change'], [ 'toggle' => TRUE ]);
    closeside();
    openside('Username settings<small>User profile and account configuration</small>', TRUE);
    echo form_textarea('username_ban', $locale['649'], $settings['username_ban'], [
        'placeholder' => $locale['411'],
        'autosize'    => TRUE
    ]);

    echo "<div class='row'>
    <label class='control-label col-xs-12' for='photo_max_w'>" . $locale['1008'] . "</label>
    <div class='col-xs-12'>
    " . form_text('avatar_width', '', $settings['avatar_width'], [
            'class'         => 'pull-left m-r-10',
            'max_length'    => 4,
            'type'          => 'number',
            'prepend'       => TRUE,
            'prepend_value' => $locale['1015'],
            'width'         => '170px'
        ]) . "
    " . form_text('avatar_height', '', $settings['avatar_height'], [
            'class'         => 'pull-left',
            'max_length'    => 4,
            'type'          => 'number',
            'prepend'       => TRUE,
            'prepend_value' => $locale['1016'],
            'width'         => '170px'
        ]) . "
    </div></div>";
    $calc_c = calculate_byte($settings['avatar_filesize']);
    $calc_b = $settings['avatar_filesize'] / $calc_c;

    echo "<div class='row'>
    <label class='control-label col-xs-12' for='calc_b'>" . $locale['605'] . "</label>
    <div class='col-xs-12'>
    " . form_text('calc_b', '', $calc_b, [
            'required'   => TRUE,
            'type'       => 'number',
            'error_text' => $locale['error_rate'],
            'width'      => '150px',
            'max_length' => 4,
            'class'      => 'pull-left m-r-10'
        ]) . "
    " . form_select('calc_c', '', $calc_c, [
            'options'     => $locale['1020'],
            'placeholder' => $locale['choose'],
            'class'       => 'pull-left',
            'width'       => '180px'
        ]) . "
    </div></div>";
    echo form_select('avatar_ratio', $locale['1001'], $settings['avatar_ratio'], [
        'options' => [ '0' => $locale['955'], '1' => $locale['956'] ],
        'inline'  => FALSE,
        'width'   => '100%'
    ]);
    closeside();

    echo '<h6>Deactivation Settings</h6>';
    openside();
    echo form_checkbox('enable_deactivation', $locale['1002'], $settings['enable_deactivation'], [ 'toggle' => TRUE ]);
    closeside();
    openside('Inactive period<small>' . $locale['1004'] . '</small>', TRUE);
    echo form_text('deactivation_period', $locale['1003'], $settings['deactivation_period'], [
        'max_length'  => 3,
        'inner_width' => '150px',
        'type'        => 'number',
    ]);
    echo form_text('deactivation_response', $locale['1005'], $settings['deactivation_response'], [
        'max_length'  => 3,
        'inner_width' => '150px',
        'type'        => 'number',
        'ext_tip'     => $locale['1006']
    ]);
    echo form_select('deactivation_action',
                     $locale['1011'],
                     $settings['deactivation_action'],
                     [ 'options' => [ '0' => $locale['1012'], '1' => $locale['1013'] ] ]);
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

function pf_js() {}
