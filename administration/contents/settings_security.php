<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_security.php
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

use PHPFusion\Sessions;

defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', LOCALE . LOCALESET . 'admin/settings.php');

$settings = fusion_get_settings();

$contents = [
    'post'        => 'pf_post',
    'view'        => 'pf_view',
    'button'      => 'pf_button',
    'js'          => 'pf_js',
    'link'        => ( $admin_link ?? '' ),
    'settings'    => TRUE,
    'title'       => $locale['security_settings'],
    'description' => $locale['security_settings'],
    'actions'     => [ 'post' => [ 'savesettings' => 'settingsFrm', 'clearcache' => 'settingsform' ] ],
];

function pf_post() {

    $settings = fusion_get_settings();

    $locale = fusion_get_locale();

    if ( $action = post('form_action') ) {

        if ( $action == 'savesettings' ) {
            // Save settings after validation
            $inputData = [
                'captcha'               => sanitizer('captcha', '', 'captcha'),
                'display_validation'    => post('display_validation') ? 1 : 0,
                'allow_php_exe'         => post('allow_php_exe') ? 1 : 0,
                'flood_interval'        => sanitizer('flood_interval', 15, 'flood_interval'),
                'flood_autoban'         => post('flood_autoban') ? 1 : 0,
                'maintenance_level'     => sanitizer('maintenance_level', USER_LEVEL_ADMIN, 'maintenance_level'),
                'maintenance'           => post('maintenance') ? 1 : 0,
                'maintenance_message'   => sanitizer('maintenance_message', '', 'maintenance_message'),
                'bad_words_enabled'     => post('bad_words_enabled') ? 1 : 0,
                'bad_words'             => post('bad_words'),
                'bad_word_replace'      => sanitizer('bad_word_replace', '', 'bad_word_replace'),
                'database_sessions'     => sanitizer('database_sessions', 0, 'database_sessions'),
                'form_tokens'           => sanitizer('form_tokens', '', 'form_tokens'),
                'mime_check'            => post('mime_check') ? 1 : 0,
                'error_logging_enabled' => post('error_logging_enabled') ? 1 : 0,
                'error_logging_method'  => sanitizer('error_logging_method', '', 'error_logging_method'),
                'admin_timeout'         => sanitizer('admin_timeout', '', 'admin_timeout'),
                'admin_timeout_period'  => sanitizer('admin_timeout_period', '', 'admin_timeout_period'),
            ];

            // Validate extra fields
            if ( $inputData['captcha'] == 'grecaptcha' ) {
                // appends captcha settings
                $inputData += [
                    'recaptcha_public'  => form_sanitizer($_POST['recaptcha_public'], '', 'recaptcha_public'),
                    'recaptcha_private' => form_sanitizer($_POST['recaptcha_private'], '', 'recaptcha_private'),
                    'recaptcha_theme'   => form_sanitizer($_POST['recaptcha_theme'], '', 'recaptcha_theme'),
                    'recaptcha_type'    => form_sanitizer($_POST['recaptcha_type'], '', 'recaptcha_type'),
                ];
            }

            if ( fusion_safe() ) {

                foreach ( $inputData as $settings_name => $settings_value ) {

                    dbquery('UPDATE ' . DB_SETTINGS . ' SET settings_value=:settings_value WHERE settings_name=:settings_name',
                            [
                                ':settings_value' => $settings_value,
                                ':settings_name'  => $settings_name
                            ]);

                }

                add_notice('success', $locale['900']);

            }
            else {
                add_notice('danger', $locale['901']);
                add_notice('danger', $locale['696']);
                add_notice('danger', $locale['900']);
            }

            redirect(FUSION_REQUEST);
        }

        if ( $action == 'clearcache' ) {
            if ( $settings['database_sessions'] ) {
                $session = Sessions::getInstance(COOKIE_PREFIX . 'session');
                $session->_purge();
            }
            else {
                // Where system has been disabled and instance could not be found, invoke manually.
                dbquery('DELETE FROM ' . DB_SESSIONS);
            }
            add_notice('success', $locale['security_007']);
            redirect(FUSION_REQUEST);
        }
    }

}

function pf_view() {

    $settings = fusion_get_settings();
    $locale = fusion_get_locale();
    $is_multilang = count(fusion_get_enabled_languages()) > 1;


    echo openform('settingsFrm', 'POST');

    echo '<h6>Sessions Configuration</h6>';
    // This opens roadmaps to load balancers.
    openside($locale['security_001'] . '<small>' . $locale['security_002'] . '</small>', TRUE);
    echo form_select('database_sessions', $locale['security_003'], $settings['database_sessions'], [
        'options' => [
            1 => $locale['security_004'],
            0 => $locale['security_005']
        ],
    ]);
    closeside();

    openside('Administrator Password Timeout<small>Length of each administration login session</small>', TRUE);
    echo '<div class="row"><div class="col-xs-12 col-sm-12 col-md-6 col-lg-3">';
    echo form_text('admin_timeout', 'Duration', $settings['admin_timeout'], [
        'width' => '100%',
    ]);
    echo '</div><div class="col-xs-12 col-sm-12 col-md-6 col-lg-3">';
    echo form_select('admin_timeout_period', 'Period', $settings['admin_timeout_period'], [
        'options' => [
            1      => 'Minute(s)',
            60     => 'Hour(s)',
            1440   => 'Day(s)',
            10080  => 'Week(s)',
            43800  => 'Month(s)',
            525600 => 'Year(s)',
        ],
        'width'   => '100%'
    ]);
    echo '</div>';
    closeside();

    openside($locale['security_008'] . '<small>' . $locale['security_009'] . '</small>', TRUE);
    echo form_btngroup('form_tokens', '', $settings['form_tokens'], [ 'options' => range(0, 10) ]);
    closeside();

    echo '<h6>System Maintenance</h6>';
    openside();
    echo form_checkbox('maintenance', $locale['657'], $settings['maintenance'], [ 'toggle' => TRUE ]);
    closeside();
    echo '<div id="ac_maintenance" style="display:' . ( $settings[ 'maintenance' ? 'block' : 'none' ] ) . ';">';
    //openside('Maintenance Settings<small>Configuration for maintenance mode</small>', TRUE);
    openside();
    echo form_select('maintenance_level', $locale['675'], $settings['maintenance_level'], [
        'options' => [
            USER_LEVEL_ADMIN       => $locale['676'],
            USER_LEVEL_SUPER_ADMIN => $locale['677'],
            USER_LEVEL_MEMBER      => $locale['678']
        ],
        'width'   => '100%'
    ]);
    echo form_textarea('maintenance_message', $locale['658'], stripslashes($settings['maintenance_message']), [
        'autosize'  => TRUE,
        //'html' => !$settings['tinymce_enabled'],
        'form_name' => 'settingsform'
    ]);
    closeside();
    echo '</div>';

    echo '<h6>Captchas</h6>';
    openside();
    echo form_checkbox('display_validation', $locale['553'], $settings['display_validation'], [
        'toggle' => TRUE,
        'class'  => 'm-t-10'
    ]);
    closeside();
    openside('Captcha Options<small>Captcha plugin configurations</small>', TRUE);
    echo form_select('captcha', $locale['693'], $settings['captcha'], [
        'options' => get_captchas(),
        'class'   => 'm-b-0'
    ]);
    echo "<div id='extDiv' " . ( $settings['captcha'] !== 'grecaptcha' ? "style='display:none;'" : '' ) . ">\n";
    if ( ! $settings['recaptcha_public'] ) {
        $link = [
            'start' => '[RECAPTCHA_LINK]',
            'end'   => '[/RECAPTCHA_LINK]',
        ];
        $link_replacements = [
            'start' => "<a href='https://www.google.com/recaptcha/admin' target='_BLANK'>",
            'end'   => "</a>\n",
        ];
        $locale['no_keys'] = str_replace($link, $link_replacements, $locale['no_keys']);
        echo "<div class='alert alert-warning m-t-10'><i class='fa fa-google fa-lg fa-fw'></i> " . $locale['no_keys'] . "</div>\n";
    }

    echo form_text('recaptcha_public', $locale['grecaptcha_0100'], $settings['recaptcha_public'], [
        'placeholder' => $locale['grecaptcha_placeholder_1'],
        'required'    => FALSE
    ]);
    echo form_text('recaptcha_private', $locale['grecaptcha_0101'], $settings['recaptcha_private'], [
        'placeholder' => $locale['grecaptcha_placeholder_2'],
        'required'    => FALSE
    ]);
    echo form_select('recaptcha_theme', $locale['grecaptcha_0102'], $settings['recaptcha_theme'], [
        'options'     => [
            'light' => $locale['grecaptcha_0102a'],
            'dark'  => $locale['grecaptcha_0102b']
        ],
        'inner_width' => '100%',
        'width'       => '100%'
    ]);
    echo form_select('recaptcha_type', $locale['grecaptcha_0103'], $settings['recaptcha_type'], [
        'options'     => [
            'text'  => $locale['grecaptcha_0103a'],
            'audio' => $locale['grecaptcha_0103b']
        ],
        'type'        => 'number',
        'inner_width' => '100%',
        'width'       => '100%',
        'required'    => TRUE
    ]);
    echo "</div>";
    closeside();

    echo '<h6>File Upload Security</h6>';
    openside();
    echo form_checkbox('mime_check', $locale['699f'], $settings['mime_check'], [
        'toggle' => TRUE
    ]);
    closeside();
    // upgrade api to flow with checkboxes.
    // fix btn group css.
    // fix secondary btn delete
    echo '<h6>Flood Security</h6>';
    openside();
    echo form_checkbox('flood_autoban', $locale['680'], $settings['flood_autoban'], [ 'toggle' => TRUE ]);
    closeside();
    echo '<div id="a_flood_autoban" style="display:' . ( $settings[ 'flood_autoban' ? 'block' : 'none' ] ) . ';">';
    openside();
    echo form_text('flood_interval', $locale['660'], $settings['flood_interval'], [
        'type'        => 'number',
        'inner_width' => '150px',
        'max_length'  => 2
    ]);
    closeside();
    echo '</div>';

    echo '<h6>Errors and Debugging</h6>';
    openside();
    echo form_checkbox('error_logging_enabled', $locale['security_015'], $settings['error_logging_enabled'], [
        'toggle' => TRUE
    ]);
    closeside();
    echo '<div id="a_error_logging_enabled" style="display:' . ( $settings[ 'error_logging_enabled' ? 'block' : 'none' ] ) . ';">';
    openside();
    echo form_select('error_logging_method', $locale['security_016'], $settings['error_logging_method'], [
        'options' => [
            'file'     => $locale['security_017'],
            'database' => $locale['security_018']
        ],

    ]);
    closeside();
    echo '</div>';

    echo '<h6>Badwords</h6>';
    openside();
    echo form_checkbox('bad_words_enabled', $locale['659'], $settings['bad_words_enabled'], [
        'toggle' => TRUE
    ]);
    closeside();
    echo '<div id="a_bad_words_enabled" style="display:' . ( $settings[ 'bad_words_enabled' ? 'block' : 'none' ] ) . ';">';
    openside();
    echo form_text('bad_word_replace', $locale['654'], $settings['bad_word_replace']);
    echo form_textarea('bad_words', $locale['651'], $settings['bad_words'], [
        'placeholder' => $locale['652'],
        'autosize'    => TRUE
    ]);
    closeside();
    echo '</div>';

    echo '<h6>Very dangerous situation</h6>';
    openside($locale['694'] . '<small>' . $locale['695'] . '</small>', TRUE);
    echo form_select('allow_php_exe', $locale['694'], $settings['allow_php_exe'], [
        'options' => [
            0 => $locale['disable'],
            1 => $locale['enable']
        ]
    ]);
    closeside();
    echo "</div>\n</div>\n";

    echo '<noscript>';
    echo '<div class="spacer-sm">';
    echo pf_button();
    echo '</div>';
    echo '</noscript>';

    echo closeform();

}

function pf_button() {

    $locale = fusion_get_locale();

    return form_button('clearcache', $locale['security_006'], 'clearcache', [ 'class' => 'btn-default' ]) .
           form_button('savesettings', $locale['750'], $locale['750'], [ 'class' => 'btn-primary' ]);
}

function pf_js() {

    return "
    checkboxToggle('maintenance', 'ac_maintenance');
    
    checkboxToggle('error_logging_enabled', 'a_error_logging_enabled');
    
    checkboxToggle('bad_words_enabled', 'a_bad_words_enabled');
    
    checkboxToggle('flood_autoban', 'a_flood_autoban');
    
    val = $('#captcha').select2().val(); if (val == 'grecaptcha') { $('#extDiv').slideDown('slow'); } else { $('#extDiv').slideUp('slow'); } $('#captcha').bind('change', function() { var val = $(this).select2().val(); if (val == 'grecaptcha') { $('#extDiv').slideDown('slow'); } else { $('#extDiv').slideUp('slow');}});";
}


/* Get all available captchas */
function get_captchas() {

    $available_captchas = [];
    if ( $temp = opendir(INCLUDES . "captchas/") ) {
        while ( FALSE !== ( $file = readdir($temp) ) ) {
            if ( $file != "." && $file != ".." && is_dir(INCLUDES . "captchas/" . $file) ) {
                $available_captchas[ $file ] = ! empty($locale[ $file ]) ? $locale[ $file ] : $file;
            }
        }
    }

    return $available_captchas;
}
