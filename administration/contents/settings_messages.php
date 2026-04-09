<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_messages.php
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
    'title'       => $locale['message_settings'],
    'description' => $locale['message_description'],
    'actions'     => [
        'post' => [
            'savesettings' => 'settingsFrm',
            'deletepm'     => 'delFrm'
        ]
    ]
];

function pf_post() {

    $locale = fusion_get_locale();

        if (admin_post('savesettings')) {
        $inputData = [
            'pm_inbox_limit'   => form_sanitizer($_POST['pm_inbox_limit'], '20', 'pm_inbox_limit'),
            'pm_outbox_limit'  => form_sanitizer($_POST['pm_outbox_limit'], '20', 'pm_outbox_limit'),
            'pm_archive_limit' => form_sanitizer($_POST['pm_archive_limit'], '20', 'pm_archive_limit'),
            'pm_email_notify'  => form_sanitizer($_POST['pm_email_notify'], '1', 'pm_email_notify'),
            'pm_save_sent'     => form_sanitizer($_POST['pm_save_sent'], '1', 'pm_save_sent'),
        ];

        if ( \defender::safe() ) {
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

        if (admin_post('deletepm')) {
        dbquery("TRUNCATE TABLE " . DB_MESSAGES);
        add_notice('success', $locale['712']);
        redirect(FUSION_REQUEST);
    }
}

function pf_view() {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    echo openform('delFrm', 'POST').closeform();


    echo openform('settingsFrm', 'POST');
    echo '<h6>Private Messages</h6>';
    openside('Private Message System<small>Private messaging system configurations</small>', TRUE);
    echo form_text('pm_inbox_limit',
                   $locale['701'] . '<small>' . $locale['704'] . '</small>',
                   $settings['pm_inbox_limit'],
                   [
                       'type'        => 'number',
                       'max_length'  => 2,
                       'inner_width' => '100px',
                   ]);
    echo form_text('pm_outbox_limit',
                   $locale['702'] . '<small>' . $locale['704'] . '</small>',
                   $settings['pm_outbox_limit'],
                   [
                       'type'        => 'number',
                       'max_length'  => 2,
                       'inner_width' => '100px',
                   ]);
    echo form_text('pm_archive_limit',
                   $locale['703'] . '<small>' . $locale['704'] . '</small>',
                   $settings['pm_archive_limit'],
                   [
                       'type'        => 'number',
                       'max_length'  => 2,
                       'inner_width' => '100px',
                   ]);
    closeside();
    openside('Messaging Notification<small>Notification configurations for private messages</small>', TRUE);
    echo form_select('pm_email_notify', $locale['709'], $settings['pm_email_notify'], [
        'options' => [ '1' => $locale['no'], '2' => $locale['yes'] ],
        'width'   => '100%'
    ]);
    echo form_select('pm_save_sent', $locale['710'], $settings['pm_save_sent'], [
        'options' => [ '1' => $locale['no'], '2' => $locale['yes'] ],
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

    return
        form_button('deletepm', $locale['714'], $locale['714'], [ 'class' => 'btn-danger', 'icon' => 'fad fa-trash' ]) .
        form_button('savesettings', $locale['750'], $locale['750'], [ 'class' => 'btn-primary' ]);
}

function pf_js() {

    $locale = fusion_get_locale();

    return "$('#deletepm').bind('click', function() { return confirm('" . $locale['713'] . "'); });";
}
