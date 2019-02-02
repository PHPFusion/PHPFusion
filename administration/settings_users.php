<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_users.php
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
pageAccess('S9');
require_once THEMES.'templates/admin_header.php';
$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/settings.php");
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'settings_user.php'.fusion_get_aidlink(), 'title' => $locale['user_settings']]);

$settings = fusion_get_settings();

if (isset($_POST['savesettings'])) {
    $inputData = [
        'enable_deactivation'   => form_sanitizer($_POST['enable_deactivation'], '0', 'enable_deactivation'),
        'deactivation_period'   => form_sanitizer($_POST['deactivation_period'], '365', 'deactivation_period'),
        'deactivation_response' => form_sanitizer($_POST['deactivation_response'], '14', 'deactivation_response'),
        'deactivation_action'   => form_sanitizer($_POST['deactivation_action'], '0', 'deactivation_action'),
        'hide_userprofiles'     => form_sanitizer($_POST['hide_userprofiles'], '0', 'hide_userprofiles'),
        'avatar_filesize'       => form_sanitizer($_POST['calc_b'], '15', 'calc_b') * form_sanitizer($_POST['calc_c'], '100000', 'calc_c'),
        'avatar_width'          => form_sanitizer($_POST['avatar_width'], '100', 'avatar_width'),
        'avatar_height'         => form_sanitizer($_POST['avatar_height'], '100', 'avatar_height'),
        'avatar_ratio'          => form_sanitizer($_POST['avatar_ratio'], '0', 'avatar_ratio'),
        'userNameChange'        => form_sanitizer($_POST['userNameChange'], '0', 'userNameChange'),
        'userthemes'            => form_sanitizer($_POST['userthemes'], '0', 'userthemes'),
        'multiple_logins'       => form_sanitizer($_POST['multiple_logins'], '0', 'multiple_logins')

    ];

    if (\defender::safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ]);
        }

        if ($_POST['enable_deactivation'] == '0') {
            $result = dbquery("UPDATE ".DB_USERS." SET user_status='0' WHERE user_status='5'");
        }

        addNotice('success', $locale['900']);
        redirect(FUSION_REQUEST);
    }
}

opentable($locale['user_settings']);
echo "<div class='well'>".$locale['user_description']."</div>";
echo openform('settingsform', 'post', FUSION_REQUEST);
echo "<div class='row'>\n<div class='col-xs-12 col-sm-8'>\n";
openside('');
$choice_opts = ['0' => $locale['no'], '1' => $locale['yes']];
echo form_select('enable_deactivation', $locale['1002'], $settings['enable_deactivation'], ['options' => $choice_opts]);
echo form_text('deactivation_period', $locale['1003'], $settings['deactivation_period'], [
    'max_length'  => 3,
    'inner_width' => '150px',
    'type'        => 'number',
    'ext_tip'     => $locale['1004']
]);

echo form_text('deactivation_response', $locale['1005'], $settings['deactivation_response'], [
    'max_length'  => 3,
    'inner_width' => '150px',
    'type'        => 'number',
    'ext_tip'     => $locale['1006']
]);

$action_opts = ['0' => $locale['1012'], '1' => $locale['1013']];
echo form_select('deactivation_action', $locale['1011'], $settings['deactivation_action'], ['options' => $action_opts]);
closeside();
openside('');
echo "<div class='display-block overflow-hide'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3' for='photo_max_w'>".$locale['1008']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
    ".form_text('avatar_width', '', $settings['avatar_width'], [
        'class'         => 'pull-left m-r-10',
        'max_length'    => 4,
        'type'          => 'number',
        'prepend'       => TRUE,
        'prepend_value' => $locale['1015'],
        'width'         => '170px'
    ])."
    ".form_text('avatar_height', '', $settings['avatar_height'], [
        'class'         => 'pull-left',
        'max_length'    => 4,
        'type'          => 'number',
        'prepend'       => TRUE,
        'prepend_value' => $locale['1016'],
        'width'         => '170px'
    ])."
    </div>
</div>";
$calc_c = calculate_byte($settings['avatar_filesize']);
$calc_b = $settings['avatar_filesize'] / $calc_c;

echo "<div class='display-block overflow-hide'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3' for='calc_b'>".$locale['605']."</label>
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
        'options'     => $locale['1020'],
        'placeholder' => $locale['choose'],
        'class'       => 'pull-left',
        'width'       => '180px'
    ])."
    </div>
</div>
";
$ratio_opts = ['0' => $locale['955'], '1' => $locale['956']];
echo form_select('avatar_ratio', $locale['1001'], $settings['avatar_ratio'], [
    'options' => $ratio_opts,
    'inline'  => TRUE,
    'width'   => '100%'
]);
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-4'>\n";
openside('');
echo form_select('hide_userprofiles', $locale['673'], $settings['hide_userprofiles'], ['options' => $choice_opts]);
closeside();
openside('');
echo form_select('userNameChange', $locale['691'], $settings['userNameChange'], ['options' => $choice_opts]);
echo form_select('userthemes', $locale['668'], $settings['userthemes'], ['options' => $choice_opts]);
echo form_select('multiple_logins', $locale['1014'], $settings['multiple_logins'], ['options' => $choice_opts, 'ext_tip' => $locale['1014a']]);
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-success']);
echo closeform();
closetable();
require_once THEMES.'templates/footer.php';
