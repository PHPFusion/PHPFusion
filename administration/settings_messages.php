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
require_once THEMES.'templates/admin_header.php';
pageAccess( "S7" );

$settings = fusion_get_settings();

$locale = fusion_get_locale( '', LOCALE.LOCALESET.'admin/settings.php' );

add_breadcrumb( [ 'link' => ADMIN.'settings_messages.php'.fusion_get_aidlink(), 'title' => $locale['message_settings'] ] );

if ( check_post( 'save_settings' ) ) {
    $inputData = [
        'pm_inbox_limit'   => sanitizer( 'pm_inbox_limit', '20', 'pm_inbox_limit' ),
        'pm_outbox_limit'  => sanitizer( 'pm_outbox_limit', '20', 'pm_outbox_limit' ),
        'pm_archive_limit' => sanitizer( 'pm_archive_limit', '20', 'pm_archive_limit' ),
        'pm_email_notify'  => ( check_post( 'pm_email_notify' ) ? sanitizer( 'pm_email_notify', '1', 'pm_email_notify' ) : 0 ),
        'pm_save_sent'     => ( check_post( 'pm_save_sent' ) ? sanitizer( 'pm_save_sent', '1', 'pm_save_sent' ) : 0 )
    ];

    if ( fusion_safe() ) {
        foreach ( $inputData as $settings_name => $settings_value ) {
            $data = [
                'settings_name'  => $settings_name,
                'settings_value' => $settings_value
            ];
            dbquery_insert( DB_SETTINGS, $data, 'update', [ 'primary_key' => 'settings_name' ] );
        }

        addNotice( 'success', $locale['900'] );
        redirect( FUSION_REQUEST );
    }
}

if ( check_post( 'delete-messages' ) ) {
    dbquery( "TRUNCATE TABLE ".DB_MESSAGES );
    addNotice( 'success', $locale['712'] );
    redirect( FUSION_REQUEST );
}

opentable( $locale['message_settings'] );
echo openform( 'messagesfrm', 'post' );
echo "<p>".$locale['message_description']."</p>\n";
echo "<hr/>\n";
echo "<div class='".grid_row()."'>\n";
echo "<div class='".grid_column_size( 100, 20 )."'>\n";
echo "<h4 class='m-0'>".$locale['message_settings']."</h4>";
echo "</div>\n<div class='".grid_column_size( 100, 70 )."'>\n";
echo form_checkbox( 'pm_email_notify', $locale['709'], $settings['pm_email_notify'], [ 'reverse_label' => TRUE ] );
echo form_checkbox( 'pm_save_sent', $locale['710'], $settings['pm_save_sent'], [ 'reverse_label' => TRUE ] );
echo "</div>\n</div>\n";
echo "<hr/>\n";

echo form_text( 'pm_inbox_limit', $locale['701'], $settings['pm_inbox_limit'], [
    'type'        => 'number',
    'max_length'  => 2,
    'ext_tip'     => $locale['704'],
    'inner_width' => '100px',
    'inline'      => TRUE
] );
echo form_text( 'pm_outbox_limit', $locale['702'], $settings['pm_outbox_limit'], [
    'type'        => 'number',
    'max_length'  => 2,
    'inner_width' => '100px',
    'inline'      => TRUE
] );
echo form_text( 'pm_archive_limit', $locale['703'], $settings['pm_archive_limit'], [
    'type'        => 'number',
    'max_length'  => 2,
    'inner_width' => '100px',
    'inline'      => TRUE
] );
echo "<hr/>\n";

// Danger zone
echo "<div class='".grid_row()."'>\n";
echo "<div class='".grid_column_size( 100, 20 )."'>\n";
echo "</div>\n<div class='".grid_column_size( 100, 70 )."'>\n";
openform( 'delete-pm', 'post', FUSION_REQUEST );
fusion_confirm_exit();
add_to_jquery( "$('#delete-messages').bind('click', function() { return confirm('".$locale['713']."'); });" );
echo form_button( 'delete-messages', $locale['714'], $locale['714'], [ 'class' => 'btn-danger', 'icon' => 'fa fa-trash-o' ] );
echo "</div>\n</div>\n";

echo form_button( 'save_settings', $locale['750'], $locale['750'], [ 'class' => 'btn-success' ] );
echo closeform();
closetable();
require_once THEMES.'templates/footer.php';
