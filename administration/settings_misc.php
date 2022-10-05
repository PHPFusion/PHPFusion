<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_misc.php
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
pageaccess('S6');

$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/settings.php");
$settings = fusion_get_settings();

add_breadcrumb(['link' => ADMIN.'settings_misc.php'.fusion_get_aidlink(), 'title' => $locale['admins_misc_settings']]);

if (check_post('savesettings')) {
    $inputData = [
        'tinymce_enabled'        => check_post('tinymce_enabled') ? 1 : 0,
        'smtp_host'              => sanitizer('smtp_host', '', 'smtp_host'),
        'smtp_port'              => sanitizer('smtp_port', '', 'smtp_port'),
        'smtp_auth'              => check_post('smtp_auth') && !empty(post('smtp_username')) && !empty(post('smtp_password')) ? 1 : 0,
        'smtp_username'          => sanitizer('smtp_username', '', 'smtp_username'),
        'smtp_password'          => sanitizer('smtp_password', '', 'smtp_password'),
        'thumb_compression'      => sanitizer('thumb_compression', '0', 'thumb_compression'),
        'comments_enabled'       => check_post('comments_enabled') ? 1 : 0,
        'ratings_enabled'        => check_post('ratings_enabled') ? 1 : 0,
        'visitorcounter_enabled' => check_post('visitorcounter_enabled') ? 1 : 0,
        'rendertime_enabled'     => sanitizer('rendertime_enabled', '0', 'rendertime_enabled'),
        'index_url_bbcode'       => check_post('index_url_bbcode') ? 1 : 0,
        'index_url_userweb'      => check_post('index_url_userweb') ? 1 : 0,
        'create_og_tags'         => check_post('create_og_tags') ? 1 : 0,
        'update_checker'         => check_post('update_checker') ? 1 : 0,
        'error_logging_enabled'  => post('error_logging_enabled') ? 1 : 0,
        'error_logging_method'   => sanitizer('error_logging_method', '', 'error_logging_method'),
    ];

    if (fusion_safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ]);
        }

        addnotice('success', $locale['admins_900']);
        redirect(FUSION_REQUEST);
    }
}

opentable($locale['admins_misc_settings']);
echo "<div class='mb-5'><h5>".$locale['admins_misc_description']."</h5></div>";

echo openform('settingsFrm', 'POST');
$choice_arr = [1 => $locale['yes'], 0 => $locale['no']];

openside('Server Email Settings');
echo '<div class="row"><div class="col-xs-12 col-sm-4">';
echo form_text('smtp_host', $locale['admins_664'], $settings['smtp_host'], [
    'max_length'     => 200,
    'floating_label' => TRUE,
]);
echo '</div><div class="col-xs-12 col-sm-4">';
echo form_text('smtp_port', $locale['admins_674'], $settings['smtp_port'], [
    'max_length'     => 10,
    'floating_label' => TRUE,
]);
echo '</div><div class="col-xs-12 col-sm-4">';
echo form_select('smtp_auth', $locale['admins_698'], $settings['smtp_auth'], [
    'options'        => $choice_arr,
    'placeholder'    => $locale['admins_698'],
    'ext_tip'        => $locale['admins_665'],
    'floating_label' => TRUE,
]);
echo '</div></div>';

echo '<div class="row" id="emailCredential" style="display:'.($settings['smtp_auth'] ? 'block' : 'none').';"><div class="col-xs-12">';
echo form_text('smtp_username', $locale['admins_666'], $settings['smtp_username'], [
    'max_length' => 100,
]);
echo form_text('smtp_password', $locale['admins_667'], $settings['smtp_password'], [
    'max_length' => 100,
]);
echo '</div></div>';
closeside();

openside('Footer Settings');
echo form_checkbox('visitorcounter_enabled', $locale['admins_679'], $settings['visitorcounter_enabled'], [
    'toggle' => TRUE
]);
echo form_select('rendertime_enabled', $locale['admins_688'], $settings['rendertime_enabled'], [
    'inline'      => FALSE,
    'options'     => ['0' => $locale['no'], '1' => $locale['admins_689'], '2' => $locale['admins_690']],
    'width'       => '100%',
    'inner_width' => '100%',
]);
closeside();

add_to_jquery("
$('#smtp_auth').on('change', function(e) {
    let eec = $('#emailCredential');
    eec.hide();
    if ( $(this).val() == 1 ) {     
        eec.slideDown();         
    }
});
");

openside('Form Settings');
echo form_checkbox('tinymce_enabled', $locale['admins_662'], $settings['tinymce_enabled'], [
    'toggle' => TRUE
]);
echo form_select('thumb_compression', $locale['admins_606'], $settings['thumb_compression'], [
    'options'     => ['gd1' => $locale['admins_607'], 'gd2' => $locale['admins_608']],
    'width'       => '100%',
    'inner_width' => '100%',
]);
closeside();

openside('Ratings and Comments Settings');
echo form_checkbox('ratings_enabled', $locale['admins_672'], $settings['ratings_enabled'], [
    'toggle' => TRUE
]);
echo form_checkbox('comments_enabled', 'Enabled comments system', $settings['comments_enabled'], [
    'toggle' => TRUE
]);
closeside();

openside('Search and Indexing Settings');
echo form_checkbox('index_url_bbcode', $locale['admins_1031'], $settings['index_url_bbcode'], [
    'toggle' => TRUE
]);
echo form_checkbox('index_url_userweb', $locale['admins_1032'], $settings['index_url_userweb'], [
    'toggle' => TRUE
]);
echo form_checkbox('create_og_tags', $locale['admins_1030'], $settings['create_og_tags'], [
    'toggle' => TRUE
]);
closeside();

openside('Error Logging');
echo form_checkbox('error_logging_enabled', $locale['admins_security_015'], $settings['error_logging_enabled'], [
    'toggle' => TRUE
]);
echo form_select('error_logging_method', $locale['admins_security_016'], $settings['error_logging_method'], [
    'options'     => [
        'file'     => $locale['admins_security_017'],
        'database' => $locale['admins_security_018']
    ],
    'width'       => '100%',
    'inner_width' => '100%'
]);
closeside();

openside('');
//To move to System Admin Upgrade
echo form_checkbox('update_checker', $locale['admins_610'], $settings['update_checker'], [
    'toggle' => TRUE
]);
closeside();

echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
echo closeform();
closetable();
require_once THEMES.'templates/footer.php';
