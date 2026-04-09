<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_registration.php
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

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');

$settings = fusion_get_settings();

$contents = [
    'post'        => 'pf_post',
    'view'        => 'pf_view',
    'button'      => 'pf_button',
    'js'          => 'pf_js',
    'link'        => ($admin_link ?? ''),
    'settings'    => TRUE,
    'title'       => $locale['register_settings'],
    'description' => $locale['register_description'],
    'actions'     => ['post' => ['savesettings'=>'settingsFrm']]
];


function pf_post() {

    $locale = fusion_get_locale();

    if (admin_post('savesettings')) {

        $inputData = [
            'login_method'        => sanitizer('login_method', '0', 'login_method'),
            'enable_registration' => post('enable_registration') ? 1 : 0,
            'email_verification'  => post('email_verification') ? 1 : 0,
            'admin_activation'    => post('admin_activation') ? 1 : 0,
            'enable_terms'        => post('enable_terms') ? 1 : 0,
            'gateway'             => post('gateway') ? 1 : 0,
            'gateway_method'      => sanitizer('gateway_method', 0, 'gateway_method'),
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
}

function pf_view() {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    echo openform('settingsFrm', 'POST');

    openside();
    echo form_checkbox('enable_terms', $locale['558'], $settings['enable_terms'], ['toggle' => TRUE]);
    closeside();
    openside();
    echo form_checkbox('enable_registration', $locale['551'], $settings['enable_registration'], ['toggle' => TRUE]);
    closeside();
    openside();
    echo form_checkbox('email_verification', $locale['552'], $settings['email_verification'], ['toggle' => TRUE]);
    closeside();
    openside();
    echo form_checkbox('admin_activation', $locale['557'], $settings['admin_activation'], ['toggle' => TRUE]);
    closeside();
    openside('Login<small>Login configurations and methods</small>', TRUE);
    $opts = ['0' => $locale['global_101'], '1' => $locale['699e'], '2' => $locale['699b']];
    echo form_select('login_method', $locale['699'], $settings['login_method'], ['options' => $opts]);
    closeside();
    echo '<h6>Registration Gateway</h6>';
    openside();
    echo form_checkbox('gateway', $locale['security_010'], $settings['gateway'], [
        'toggle' => TRUE
    ]);
    closeside();
    openside($locale['security_011'].'<small>Registration gateway security page configurations</small>', TRUE);
    echo form_select('gateway_method', $locale['security_011'], $settings['gateway_method'], [
        'options' => [
            0 => $locale['security_012'],
            1 => $locale['security_013'],
            2 => $locale['security_014']
        ],
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
    return form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-primary']);
}








