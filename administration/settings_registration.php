<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_registration.php
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
pageaccess('S4');

$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/settings.php");
$settings = fusion_get_settings();

add_breadcrumb(['link' => ADMIN.'settings_registration.php'.fusion_get_aidlink(), 'title' => $locale['admins_register_settings']]);

$is_multilang = count(fusion_get_enabled_languages()) > 1;

if (check_post('savesettings')) {

    $inputData = [
        'privacy_policy'      => sanitizer($is_multilang ? ['privacy_policy'] : 'privacy_policy', '', 'privacy_policy', $is_multilang),
        'privacy_lastupdate'  => (check_post('privacy_policy') != fusion_get_settings('privacy_policy') ? time() : fusion_get_settings('privacy_lastupdate')),
        'license_agreement'   => sanitizer($is_multilang ? ['license_agreement'] : 'license_agreement', '', 'license_agreement', $is_multilang),
        'license_lastupdate'  => (check_post('license_agreement') != fusion_get_settings('license_agreement') ? time() : fusion_get_settings('license_lastupdate')),
        'enable_registration' => check_post('enable_registration') ? 1 : 0,
        'email_verification'  => check_post('email_verification') ? 1 : 0,
        'admin_activation'    => check_post('admin_activation') ? 1 : 0,
        'enable_terms'        => check_post('enable_terms') ? 1 : 0,
        'gateway'             => check_post('gateway') ? 1 : 0,
        'gateway_method'      => sanitizer('gateway_method', 0, 'gateway_method'),
    ];

    if (fusion_safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ]);
        }

        addnotice('success', $locale['admins_900']);

        if (check_post('delete_gw_tmp')) {
            if (is_file(INCLUDES.'gateway/flood/ctrl')) {
                @unlink(INCLUDES.'gateway/flood/ctrl');
            }

            $path = INCLUDES.'gateway/flood/lock/';
            $tmp_files = makefilelist(INCLUDES.'gateway/flood/lock/', 'index.php');
            foreach ($tmp_files as $file) {
                if (is_file($path.$file)) {
                    @unlink($path.$file);
                }
            }

            addnotice('success', $locale['admins_gateway_002']);
        }

        redirect(FUSION_REQUEST);
    }
}

opentable($locale['admins_register_settings']);
echo "<div class='mb-5'><h5>".$locale['admins_register_description']."</h5></div>";

echo openform('settingsFrm', 'POST');

openside('General Registration Settings');
echo form_checkbox('enable_registration', $locale['admins_551'], $settings['enable_registration'], [
    'toggle' => TRUE
]);
echo form_checkbox('email_verification', $locale['admins_552'], $settings['email_verification'], [
    'toggle' => TRUE
]);
echo form_checkbox('admin_activation', $locale['admins_557'], $settings['admin_activation'], [
    'toggle' => TRUE
]);
closeside();

openside('Terms of Agreements & Policies');
echo form_checkbox('enable_terms', $locale['admins_558'], $settings['enable_terms'], [
    'toggle' => TRUE
]);
if ($is_multilang == TRUE) {
    echo \PHPFusion\Quantum\QuantumHelper::quantumMultilocaleFields('license_agreement', $locale['admins_559'], $settings['license_agreement'], [
        'form_name' => 'settingsform',
        'input_id'  => 'enable_license_agreement',
        'autosize'  => (bool)fusion_get_settings('tinymce_enabled'),
        'type'      => (fusion_get_settings('tinymce_enabled') ? 'tinymce' : 'html'),
        'function'  => 'form_textarea'
    ]);
} else {
    echo form_textarea('license_agreement', $locale['admins_559'], $settings['license_agreement'], [
        'form_name' => 'settingsform',
        'autosize'  => (bool)fusion_get_settings('tinymce_enabled'),
        'html'      => !fusion_get_settings('tinymce_enabled')
    ]);
}
tablebreak();
if ($is_multilang == TRUE) {
    echo \PHPFusion\Quantum\QuantumHelper::quantumMultilocaleFields('privacy_policy', $locale['admins_820'], $settings['privacy_policy'], [
        'autosize'  => 1,
        'form_name' => 'settingsform',
        'html'      => !fusion_get_settings('tinymce_enabled'),
        'function'  => 'form_textarea'
    ]);
} else {
    echo form_textarea('privacy_policy', $locale['admins_820'], $settings['privacy_policy'], [
        'autosize'  => 1,
        'form_name' => 'settingsform',
        'html'      => !fusion_get_settings('tinymce_enabled')
    ]);
}
closeside();

openside('Fusion GateWay');
echo form_checkbox('gateway', $locale['admins_security_010'], $settings['gateway'], [
    'toggle' => TRUE
]);
echo form_checkbox('delete_gw_tmp', $locale['admins_gateway_001'], '', ['toggle' => TRUE]);
echo form_select('gateway_method', $locale['admins_security_011'], $settings['gateway_method'], [
    'options'     => [
        0 => $locale['admins_security_012'],
        1 => $locale['admins_security_013'],
        2 => $locale['admins_security_014']
    ],
    'width'       => '100%',
    'inner_width' => '100%',
    'inline'      => FALSE,
]);
closeside();

echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
echo closeform();
closetable();
require_once THEMES.'templates/footer.php';
