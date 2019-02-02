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
require_once __DIR__.'/../maincore.php';
pageAccess("S7");
require_once THEMES.'templates/admin_header.php';
$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'settings_messages.php'.fusion_get_aidlink(), 'title' => $locale['message_settings']]);

$settings = fusion_get_settings();

if (isset($_POST['save_settings'])) {
    $inputData = [
        'pm_inbox_limit'   => form_sanitizer($_POST['pm_inbox_limit'], '20', 'pm_inbox_limit'),
        'pm_outbox_limit'  => form_sanitizer($_POST['pm_outbox_limit'], '20', 'pm_outbox_limit'),
        'pm_archive_limit' => form_sanitizer($_POST['pm_archive_limit'], '20', 'pm_archive_limit'),
        'pm_email_notify'  => form_sanitizer($_POST['pm_email_notify'], '1', 'pm_email_notify'),
        'pm_save_sent'     => form_sanitizer($_POST['pm_save_sent'], '1', 'pm_save_sent'),
    ];

    if (\defender::safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ]);
        }

        addNotice('success', $locale['900']);
        redirect(FUSION_REQUEST);
    }
}

if (isset($_POST['delete-messages'])) {
    dbquery("TRUNCATE TABLE ".DB_MESSAGES);
    addNotice('success', $locale['712']);
    redirect(FUSION_REQUEST);
}

opentable($locale['message_settings']);
echo "<div class='well'>".$locale['message_description']."</div>\n";
echo openform('settingsform', 'post', FUSION_REQUEST);
echo "<div class='row'>\n<div class='col-xs-12 col-sm-6'>\n";
openside();
echo form_text('pm_inbox_limit', $locale['701'], $settings['pm_inbox_limit'], [
    'type'        => 'number',
    'max_length'  => 2,
    'ext_tip'     => $locale['704'],
    'inner_width' => '100px',
    'inline'      => TRUE
]);
echo form_text('pm_outbox_limit', $locale['702'], $settings['pm_outbox_limit'], [
    'type'        => 'number',
    'max_length'  => 2,
    'inner_width' => '100px',
    'inline'      => TRUE
]);
echo form_text('pm_archive_limit', $locale['703'], $settings['pm_archive_limit'], [
    'type'        => 'number',
    'max_length'  => 2,
    'inner_width' => '100px',
    'inline'      => TRUE
]);
closeside();

echo "</div>\n<div class='col-xs-12 col-sm-6'>\n";
openside();
echo form_select('pm_email_notify', $locale['709'], $settings['pm_email_notify'], [
    'options' => ['1' => $locale['no'], '2' => $locale['yes']],
    'width'   => '100%'
]);
echo form_select('pm_save_sent', $locale['710'], $settings['pm_save_sent'], [
    'options' => ['1' => $locale['no'], '2' => $locale['yes']],
    'width'   => '100%'
]);
closeside();

// Danger zone
echo "<div class='panel panel-danger'><div class='panel-body'>";
openform('delete-pm', 'post', FUSION_REQUEST);
fusion_confirm_exit();
add_to_jquery("$('#delete-messages').bind('click', function() { return confirm('".$locale['713']."'); });");
echo form_button('delete-messages', $locale['714'], $locale['714'], ['class' => 'btn-danger', 'icon' => 'fa fa-trash-o']);
echo "</div></div>";

echo "</div>\n</div>\n";
echo form_button('save_settings', $locale['750'], $locale['750'], ['class' => 'btn-success']);
echo closeform();
closetable();
require_once THEMES.'templates/footer.php';
