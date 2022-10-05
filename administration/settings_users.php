<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_users.php
| Author: Core Development Team
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
pageaccess('S9');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');

add_breadcrumb(['link' => ADMIN.'settings_user.php'.fusion_get_aidlink(), 'title' => $locale['admins_user_settings']]);

$settings = fusion_get_settings();

if (check_post('savesettings')) {

    $inputData = [

        'enable_deactivation'   => check_post('enable_deactivation') ? 1 : 0,
        'deactivation_period'   => sanitizer('deactivation_period', '365', 'deactivation_period'),
        'deactivation_response' => sanitizer('deactivation_response', '14', 'deactivation_response'),
        'deactivation_action'   => sanitizer('deactivation_action', '0', 'deactivation_action'),
        'hide_userprofiles'     => check_post('hide_userprofiles') ? 1 : 0,
        'avatar_filesize'       => sanitizer('calc_b', '15', 'calc_b') * sanitizer('calc_c', '100000', 'calc_c'),
        'avatar_width'          => sanitizer('avatar_width', '100', 'avatar_width'),
        'avatar_height'         => sanitizer('avatar_height', '100', 'avatar_height'),
        'avatar_ratio'          => sanitizer('avatar_ratio', '0', 'avatar_ratio'),
        'username_change'       => post('username_change') ? 1 : 0,
        'username_ban'          => stripinput(post('username_ban')),
        'userthemes'            => check_post('userthemes') ? 1 : 0,
    ];

    if (fusion_safe()) {

        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ]);
        }

        if (!post('enable_deactivation')) {
            $result = dbquery("UPDATE ".DB_USERS." SET user_status='0' WHERE user_status='5'");
        }

        addnotice('success', $locale['admins_900']);
        redirect(FUSION_REQUEST);
    }
}

opentable($locale['admins_user_settings']);
echo "<h5 class='mb-5'>".$locale['admins_user_description']."</h5>";
echo openform('settingsFrm', 'POST');

openside('Account Monitoring Settings');
echo form_checkbox('enable_deactivation', $locale['admins_1002'], $settings['enable_deactivation'], ['toggle' => TRUE, 'reverse_label' => FALSE]);
echo '<div class="row"><div class="col-xs-12 col-sm-4">';
echo form_text('deactivation_period', $locale['admins_1003'], $settings['deactivation_period'], [
    'max_length'  => 3,
    'inner_width' => '150px',
    'type'        => 'number',
    'ext_tip'     => $locale['admins_1004']
]);
echo '</div><div class="col-xs-12 col-sm-4">';
echo form_text('deactivation_response', $locale['admins_1005'], $settings['deactivation_response'], [
    'max_length'  => 3,
    'inner_width' => '150px',
    'type'        => 'number',
    'ext_tip'     => $locale['admins_1006']
]);
echo '</div><div class="col-xs-12 col-sm-4">';
echo form_select('deactivation_action', $locale['admins_1011'], $settings['deactivation_action'], ['inline' => FALSE, 'width' => '100%', 'inner_width' => '100%', 'options' => ['0' => $locale['admins_1012'], '1' => $locale['admins_1013']]]);
echo '</div></div>';
closeside();

openside('User Avatar');
echo '<label class="control-label" for="photo_max_w">'.$locale['admins_1008'].'</label>
<div class="row"><div class="col-xs-12 col-sm-6">';
echo form_text('avatar_width', $locale['admins_1015'].' (px)', $settings['avatar_width'], [
    'max_length'     => 4,
    'type'           => 'number',
    'prepend'        => TRUE,
    'placeholder'    => $locale['admins_1015'],
    'floating_label' => TRUE,
]);
echo '</div><div class="col-xs-12 col-sm-6">';
echo form_text('avatar_height', $locale['admins_1016'].' (px)', $settings['avatar_height'], [
    'max_length'     => 4,
    'type'           => 'number',
    'prepend'        => TRUE,
    'placeholder'    => $locale['admins_1016'],
    'floating_label' => TRUE,
]);
echo '</div></div>';

echo '<div class="row"><div class="col-xs-12 col-sm-6">';

$calc_c = calculate_byte($settings['avatar_filesize']);
$calc_b = $settings['avatar_filesize'] / $calc_c;

echo form_text('calc_b', $locale['admins_605'], $calc_b, [
    'required'    => TRUE,
    'inline'      => FALSE,
    'type'        => 'number',
    'error_text'  => $locale['error_rate'],
    'width'       => '100%',
    'max_length'  => 4,
    'append'      => TRUE,
    'append_html' => form_select('calc_c', '', $calc_c, [
        'options'     => [
            1       => $locale['global_461'],
            1024    => 'Kb',
            1048576 => 'Mb'
        ], // $locale['admins_1020'],
        'placeholder' => $locale['choose'],
        'class'       => 'm-0',
        'width'       => '100',
        'inner_width' => '100px',
    ])
]);
echo '</div><div class="col-xs-12 col-sm-6">';
echo form_select('avatar_ratio', $locale['admins_1001'], $settings['avatar_ratio'], [
    'options'     => ['0' => $locale['admins_955'], '1' => $locale['admins_956']],
    'inline'      => FALSE,
    'inner_width' => '100%',
    'width'       => '100%'
]);
echo '</div></div>';
closeside();

openside('Profile Page Settings');
echo form_checkbox('hide_userprofiles', $locale['admins_673'], $settings['hide_userprofiles'], ['toggle' => TRUE]);
echo form_checkbox('username_change', $locale['admins_691'], $settings['username_change'], ['toggle' => TRUE]);
echo form_checkbox('userthemes', $locale['admins_668'], $settings['userthemes'], ['toggle' => TRUE]);

tablebreak();
echo form_select('username_ban', $locale['admins_649'], $settings['username_ban'], [
    'placeholder' => $locale['admins_411'],
    'inline'      => FALSE,
    'tags'        => TRUE,
    'width'       => '100%',
    'inner_width' => '100%'
]);
closeside();

echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
echo closeform();
closetable();

require_once THEMES.'templates/footer.php';
