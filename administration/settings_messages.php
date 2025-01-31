<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_messages.php
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
pageaccess('S7');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');
add_breadcrumb(['link' => ADMIN.'settings_messages.php'.fusion_get_aidlink(), 'title' => $locale['admins_message_settings']]);

$settings = fusion_get_settings();

if (check_post('save_settings')) {
    $inputData = [
        'pm_inbox_limit'   => sanitizer('pm_inbox_limit', '20', 'pm_inbox_limit'),
        'pm_outbox_limit'  => sanitizer('pm_outbox_limit', '20', 'pm_outbox_limit'),
        'pm_archive_limit' => sanitizer('pm_archive_limit', '20', 'pm_archive_limit'),
        'pm_email_notify'  => sanitizer('pm_email_notify', '1', 'pm_email_notify'),
        'pm_save_sent'     => sanitizer('pm_save_sent', '1', 'pm_save_sent')
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

if (check_post('delete_messages')) {
    dbquery("TRUNCATE TABLE ".DB_MESSAGES);
    addnotice('success', $locale['admins_712']);
    redirect(FUSION_REQUEST);
}

opentable($locale['admins_message_settings']);
echo "<div class='well'>".$locale['admins_message_description']."</div>\n";
echo openform('settingsform', 'post', FUSION_REQUEST);
echo "<div class='row'>\n<div class='col-xs-12 col-sm-6'>\n";
openside($locale['admins_707']);
echo form_text('pm_inbox_limit', $locale['admins_701'], $settings['pm_inbox_limit'], [
    'type'        => 'number',
    'max_length'  => 2,
    'ext_tip'     => $locale['admins_704'],
    'inner_width' => '100px',
    'inline'      => TRUE
]);
echo form_text('pm_outbox_limit', $locale['admins_702'], $settings['pm_outbox_limit'], [
    'type'        => 'number',
    'max_length'  => 2,
    'inner_width' => '100px',
    'inline'      => TRUE
]);
echo form_text('pm_archive_limit', $locale['admins_703'], $settings['pm_archive_limit'], [
    'type'        => 'number',
    'max_length'  => 2,
    'inner_width' => '100px',
    'inline'      => TRUE
]);
closeside();

// Danger zone
openside('');
fusion_confirm_exit();
add_to_jquery("$('#delete_messages').bind('click', function() { return confirm('".$locale['admins_713']."'); });");
echo form_button('delete_messages', $locale['admins_714'], $locale['admins_714'], ['class' => 'btn-danger', 'icon' => 'fa fa-trash-o']);
closeside();

echo "</div>\n<div class='col-xs-12 col-sm-6'>\n";
openside($locale['admins_708']);
echo '<div class="m-b-10">'.$locale['admins_711'].'</div>';
echo form_select('pm_email_notify', $locale['admins_709'], $settings['pm_email_notify'], [
    'options' => ['1' => $locale['no'], '2' => $locale['yes']],
    'width'   => '100%'
]);
echo form_select('pm_save_sent', $locale['admins_710'], $settings['pm_save_sent'], [
    'options' => ['1' => $locale['no'], '2' => $locale['yes']],
    'width'   => '100%'
]);
closeside();

echo "</div>\n</div>\n";
echo form_button('save_settings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-success']);
echo closeform();
closetable();
require_once THEMES.'templates/footer.php';
