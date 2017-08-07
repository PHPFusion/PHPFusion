<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_messages.php
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
pageAccess("S7");
require_once THEMES."templates/admin_header.php";
$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'settings_messages.php'.fusion_get_aidlink(), 'title' => $locale['message_settings']]);
$pm_settings = [
    'pm_inbox_limit'   => fusion_get_settings('pm_inbox_limit'),
    'pm_outbox_limit'  => fusion_get_settings('pm_outbox_limit'),
    'pm_archive_limit' => fusion_get_settings('pm_archive_limit'),
    'pm_email_notify'  => fusion_get_settings('pm_email_notify'),
    'pm_save_sent'     => fusion_get_settings('pm_save_sent'),
];

if (isset($_POST['save_settings'])) {

    $pm_settings = [
        'pm_inbox_limit'   => form_sanitizer($_POST['pm_inbox_limit'], '20', 'pm_inbox_limit'),
        'pm_outbox_limit'  => form_sanitizer($_POST['pm_outbox_limit'], '20', 'pm_outbox_limit'),
        'pm_archive_limit' => form_sanitizer($_POST['pm_archive_limit'], '20', 'pm_archive_limit'),
        'pm_email_notify'  => form_sanitizer($_POST['pm_email_notify'], '1', 'pm_email_notify'),
        'pm_save_sent'     => form_sanitizer($_POST['pm_save_sent'], '1', 'pm_save_sent'),
    ];

    if (\defender::safe()) {
        foreach ($pm_settings as $key => $value) {
            $data = [
                'settings_name'  => $key,
                'settings_value' => $value
            ];

            dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$pm_settings[$key]."' WHERE settings_name='".$key."'");
        }
        addNotice('success', $locale['900']);
        redirect(FUSION_REQUEST);
    }
}
opentable($locale['message_settings']);
echo "<div class='well'>".$locale['message_description']."</div>\n";
echo openform('settingsform', 'post', FUSION_REQUEST);
echo "<div class='row'>\n<div class='col-xs-12 col-sm-6'>\n";
openside('');
echo form_text('pm_inbox_limit', $locale['701'], $pm_settings['pm_inbox_limit'], [
    'type'        => 'number',
    'max_length'  => 2,
    'ext_tip'     => $locale['704'],
    'inner_width' => '100px',
    'inline'      => TRUE
]);
echo form_text('pm_outbox_limit', $locale['702'], $pm_settings['pm_outbox_limit'], [
    'type'        => 'number',
    'max_length'  => 2,
    'inner_width' => '100px',
    'inline'      => TRUE
]);
echo form_text('pm_archive_limit', $locale['703'], $pm_settings['pm_archive_limit'], [
    'type'        => 'number',
    'max_length'  => 2,
    'inner_width' => '100px',
    'inline'      => TRUE
]);
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-6'>\n";
openside('');
echo form_select('pm_email_notify', $locale['709'], $pm_settings['pm_email_notify'], [
    'options' => ['1' => $locale['no'], '2' => $locale['yes']],
    'width'   => '100%'
]);
echo form_select('pm_save_sent', $locale['710'], $pm_settings['pm_save_sent'], [
    'options' => ['1' => $locale['no'], '2' => $locale['yes']],
    'width'   => '100%'
]);
closeside();
echo "</div>\n</div>\n";
echo form_button('save_settings', $locale['750'], $locale['750'], ['class' => 'btn-success']);
echo closeform();
closetable();
require_once THEMES."templates/footer.php";
