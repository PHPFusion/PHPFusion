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
        'enable_deactivation'   => post('enable_deactivation') ? 1 : 0,
        'deactivation_period'   => sanitizer('deactivation_period', '365', 'deactivation_period'),
        'deactivation_response' => sanitizer('deactivation_response', '14', 'deactivation_response'),
        'deactivation_action'   => sanitizer('deactivation_action', '0', 'deactivation_action'),
        'hide_userprofiles'     => post('hide_userprofiles') ? 1 : 0,
        'avatar_filesize'       => sanitizer('calc_b', '15', 'calc_b') * sanitizer('calc_c', '100000', 'calc_c'),
        'avatar_width'          => sanitizer('avatar_width', '100', 'avatar_width'),
        'avatar_height'         => sanitizer('avatar_height', '100', 'avatar_height'),
        'avatar_ratio'          => sanitizer('avatar_ratio', '0', 'avatar_ratio'),
        'username_change'       => post('username_change') ? 1 : 0,
        'username_ban'          => stripinput(post('username_ban')),
        'userthemes'            => post('userthemes') ? 1 : 0,
        'multiple_logins'       => post('multiple_logins') ? 1 : 0,
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
echo "<div class='well'>".$locale['admins_user_description']."</div>";
echo openform('settingsform', 'post');
echo "<div class='row'>\n<div class='col-xs-12 col-sm-6'>\n";
openside('');

echo form_checkbox('enable_deactivation', $locale['admins_1002'], $settings['enable_deactivation'], ['toggle' => TRUE]);
echo form_text('deactivation_period', $locale['admins_1003'], $settings['deactivation_period'], [
    'max_length'  => 3,
    'inner_width' => '150px',
    'type'        => 'number',
    'ext_tip'     => $locale['admins_1004']
]);

echo form_text('deactivation_response', $locale['admins_1005'], $settings['deactivation_response'], [
    'max_length'  => 3,
    'inner_width' => '150px',
    'type'        => 'number',
    'ext_tip'     => $locale['admins_1006']
]);
echo form_select('deactivation_action', $locale['admins_1011'], $settings['deactivation_action'], ['options' => ['0' => $locale['admins_1012'], '1' => $locale['admins_1013']]]);
closeside();
openside('');
echo "<div class='row'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3' for='photo_max_w'>".$locale['admins_1008']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
    ".form_text('avatar_width', '', $settings['avatar_width'], [
        'class'         => 'pull-left m-r-10',
        'max_length'    => 4,
        'type'          => 'number',
        'prepend'       => TRUE,
        'prepend_value' => $locale['admins_1015'],
        'width'         => '170px'
    ])."
    ".form_text('avatar_height', '', $settings['avatar_height'], [
        'class'         => 'pull-left',
        'max_length'    => 4,
        'type'          => 'number',
        'prepend'       => TRUE,
        'prepend_value' => $locale['admins_1016'],
        'width'         => '170px'
    ])."
    </div>
</div>";

$calc_c = calculate_byte($settings['avatar_filesize']);
$calc_b = $settings['avatar_filesize'] / $calc_c;

echo "<div class='row'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3' for='calc_b'>".$locale['admins_605']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
    ".form_text('calc_b', '', $calc_b, [
        'required'   => TRUE,
        'type'       => 'number',
        'error_text' => $locale['error_rate'],
        'width'      => '150px',
        'max_length' => 4,
        'class'      => 'pull-left m-r-10'
    ])."
    ".form_select('calc_c', '', $calc_c, [
        'options'     => $locale['admins_1020'],
        'placeholder' => $locale['choose'],
        'class'       => 'pull-left',
        'width'       => '180px'
    ])."
    </div>
</div>
";
$ratio_opts = ['0' => $locale['admins_955'], '1' => $locale['admins_956']];
echo form_select('avatar_ratio', $locale['admins_1001'], $settings['avatar_ratio'], [
    'options' => $ratio_opts,
    'inline'  => TRUE,
    'width'   => '100%'
]);
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-6'>\n";
openside('');
echo form_checkbox('hide_userprofiles', $locale['admins_673'], $settings['hide_userprofiles'], ['toggle' => TRUE]);
closeside();
openside('');
echo form_checkbox('username_change', $locale['admins_691'], $settings['username_change'], ['toggle' => TRUE]);
echo form_checkbox('userthemes', $locale['admins_668'], $settings['userthemes'], ['toggle' => TRUE]);
echo form_checkbox('multiple_logins', $locale['admins_1014'], $settings['multiple_logins'], ['toggle' => TRUE, 'ext_tip' => $locale['admins_1014a']]);
closeside();
openside('');
echo form_textarea('username_ban', $locale['admins_649'], $settings['username_ban'], [
    'placeholder' => $locale['admins_411'],
    'autosize'    => TRUE
]);
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
echo closeform();
closetable();
require_once THEMES.'templates/footer.php';

//dbquery("INSERT INTO ".DB_SETTINGS." (settings_name, settings_value) VALUES ('username_ban', '')");
