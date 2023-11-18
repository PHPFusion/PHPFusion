<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_security.php
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

require_once __DIR__ . '/../maincore.php';
require_once THEMES . 'templates/admin_header.php';
pageaccess( 'S12' );

$locale = fusion_get_locale( '', LOCALE . LOCALESET . 'admin/settings.php' );
$settings = fusion_get_settings();

add_breadcrumb( ['link' => ADMIN . 'settings_security.php' . fusion_get_aidlink(), 'title' => $locale['admins_security_settings']] );

$is_multilang = count( fusion_get_enabled_languages() ) > 1;

$time_duration_opts = [
    's' => $locale['second_s'],
    'm' => $locale['minute_s'],
    'h' => $locale['hour_s'],
    'j' => $locale['day_s'],
];

if (check_post( 'savesettings' )) {
    // Save settings after validation
    $inputData = [
        'captcha'                  => sanitizer( 'captcha', '', 'captcha' ),
        'display_validation'       => post( 'display_validation' ) ? 1 : 0,
        'flood_interval'           => sanitizer( 'flood_interval', 15, 'flood_interval' ),
        'flood_autoban'            => post( 'flood_autoban' ) ? 1 : 0,
        'maintenance_level'        => sanitizer( 'maintenance_level', -102, 'maintenance_level' ),
        'maintenance'              => post( 'maintenance' ) ? 1 : 0,
        'maintenance_message'      => sanitizer( 'maintenance_message', '', 'maintenance_message' ),
        'bad_words_enabled'        => post( 'bad_words_enabled' ) ? 1 : 0,
        'bad_words'                => stripinput( post( 'bad_words' ) ),
        'bad_word_replace'         => sanitizer( 'bad_word_replace', '', 'bad_word_replace' ),
        'blaclist_site'            => sanitizer( 'blaclist_site', '', 'blaclist_site' ),
        'database_sessions'        => sanitizer( 'database_sessions', 0, 'database_sessions' ),
        'form_tokens'              => sanitizer( 'form_tokens', '', 'form_tokens' ),
        'mime_check'               => post( 'mime_check' ) ? 1 : 0,
        'devmode'                  => post( 'devmode' ) ? 1 : 0,
        'login_method'             => sanitizer( 'login_method', '0', 'login_method' ),
        'multiple_logins'          => check_post( 'multiple_logins' ) ? 1 : 0,
        'auth_login_enabled'       => check_post( 'auth_login_enabled' ) ? 1 : 0,
        'auth_login_length'        => sanitizer( 'auth_login_length', '', 'auth_login_length' ),
        'auth_login_attempts'      => sanitizer( 'auth_login_attempts', '', 'auth_login_attempts' ),
        'auth_login_expiry'        => calculate_time( sanitizer( 'auth_login_expiry', '300', 'auth_login_expiry' ), sanitizer( 'auth_login_expiry_c', 's', 'auth_login_expiry_c' ) ),
        'login_session_expiry'     => calculate_time( sanitizer( 'login_session_expiry', '300', 'login_session_expiry' ), sanitizer( 'login_session_expiry_c', 's', 'login_session_expiry_c' ) ),
        'login_session_ext_expiry' => calculate_time( sanitizer( 'login_session_ext_expiry', '300', 'login_session_ext_expiry' ), sanitizer( 'login_session_ext_expiry_c', 's', 'login_session_ext_expiry_c' ) ),
        'admin_session_expiry'     => calculate_time( sanitizer( 'admin_session_expiry', '300', 'admin_session_expiry' ), sanitizer( 'admin_session_expiry_c', 's', 'admin_session_expiry_c' ) ),
        'admin_session_ext_expiry' => calculate_time( sanitizer( 'admin_session_ext_expiry', '300', 'admin_session_ext_expiry' ), sanitizer( 'admin_session_ext_expiry_c', 's', 'admin_session_ext_expiry_c' ) ),
    ];

    // Validate extra fields
    if ($inputData['captcha'] == 'grecaptcha' || $inputData['captcha'] == 'grecaptcha3') {
        // appends captcha settings
        $inputData += [
            'recaptcha_public'  => sanitizer( 'recaptcha_public', '', 'recaptcha_public' ),
            'recaptcha_private' => sanitizer( 'recaptcha_private', '', 'recaptcha_private' )
        ];

        if ($inputData['captcha'] == 'grecaptcha') {
            $inputData += [
                'recaptcha_theme' => sanitizer( 'recaptcha_theme', '', 'recaptcha_theme' ),
                'recaptcha_type'  => sanitizer( 'recaptcha_type', '', 'recaptcha_type' )
            ];
        }

        if ($inputData['captcha'] == 'grecaptcha3') {
            $inputData += [
                'recaptcha_score' => sanitizer( 'recaptcha_score', '', 'recaptcha_score' )
            ];
        }
    }

    if (fusion_safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery( "UPDATE " . DB_SETTINGS . " SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ] );
        }

        addnotice( 'success', $locale['admins_900'] );
        redirect( FUSION_REQUEST );

    } else {

        addnotice('danger', $locale['admins_901']);
        addnotice('danger', $locale['admins_696']);
        addnotice('danger', $locale['admins_900']);
    }

}

opentable( $locale['admins_683'] );
echo "<div class='mb-5'><h5>" . $locale['admins_security_description'] . "</h5></div>";

echo openform( 'settingsFrm', 'POST' );

//Refactor to include tab
$tab['title'][] = 'General Security';
$tab['id'][] = 'gesc';
$tab['title'][] = 'Login Security';
$tab['id'][] = 'lesc';
$tab['title'][] = 'Maintenance Mode';
$tab['id'][] = 'masc';
$tab['title'][] = 'Post Security';
$tab['id'][] = 'postsec';
$tab_active = tab_active( $tab, 0 );

echo opentab( $tab, $tab_active, 'security', FALSE, '', '', [], TRUE );
echo opentabbody( $tab['title'][0], $tab['id'][0], $tab_active );
echo "<div class='mb-3'><h5>General Security Settings</h5></div>";
echo form_checkbox( 'devmode', $locale['admins_609'], $settings['devmode'], [
    'toggle' => TRUE
] );
echo form_checkbox( 'mime_check', $locale['admins_699f'], $settings['mime_check'], [
    'toggle' => TRUE
] );

echo "<div class='mb-3'><h5>" . $locale['admins_security_001'] . "</h5>" . $locale['admins_security_002'] . "</div>";
// This opens roadmaps to load balancers.
echo '<div class="row"><div class="col-xs-12 col-sm-6">';
echo form_btngroup( 'database_sessions', $locale['admins_security_003'], $settings['database_sessions'], [
    'options' => [
        1 => $locale['admins_security_004'],
        0 => $locale['admins_security_005']
    ],
    'btn_class'   => 'btn-default m-b-0',
    'inline'  => FALSE,
] );
echo form_button( 'clear_cache', $locale['admins_security_006'], 'clear_cache', ['icon' => 'redo', 'class' => 'btn-primary spacer-sm'] );
echo '</div><div class="col-xs-12 col-sm-6">';
echo form_btngroup( 'form_tokens', $locale['admins_security_008'], $settings['form_tokens'], ['options' => range( 1, 10 ), 'ext_tip' => $locale['admins_security_009']] );
echo '</div></div>';
echo closetabbody();

echo opentabbody( $tab['title'][1], $tab['id'][1], $tab_active );
echo "<div class='mb-3'><h5>2 Factor Authentication Settings</h5></div>";

echo form_checkbox( 'auth_login_enabled', 'Enable Login 2-Factor Passcode', $settings['auth_login_enabled'], ['toggle' => TRUE, 'reverse_label' => TRUE] );

echo '<div class="row"><div class="col-xs-12 col-sm-4">';
echo form_text( 'auth_login_length', 'Passcode Length', $settings['auth_login_length'], ['type' => 'number', 'required' => TRUE] );
echo '</div><div class="col-xs-12 col-sm-4">';
echo form_text( 'auth_login_attempts', 'Maximum Accepted Failure Attempts', $settings['auth_login_attempts'], ['type' => 'number', 'required' => TRUE] );
echo '</div><div class="col-xs-12 col-sm-4">';
echo form_text( 'auth_login_expiry', 'Auth Expiry Time', settings_users_seconds( $settings['auth_login_expiry'] ), [
    'type'        => 'number',
    'required'    => TRUE,
    'append'      => TRUE,
    'append_html' => form_select( 'auth_login_expiry_c', '', settings_users_time( $settings['auth_login_expiry'] ), [
        'class'       => 'm-0',
        'required'    => TRUE,
        'options'     => $time_duration_opts,
        'inner_width' => '120px'
    ] )
] );
echo '</div></div>';
tablebreak();
echo "<div class='mb-3'><h5>Login System Settings</h5></div>";
echo form_checkbox( 'multiple_logins', $locale['admins_1014'], $settings['multiple_logins'], ['toggle' => TRUE] );
echo form_select( 'login_method', $locale['admins_699'], $settings['login_method'], [
    'inline'      => FALSE,
    'width'       => '100%',
    'inner_width' => '100%',
    'options'     => [
        '0' => $locale['global_101'],
        '1' => $locale['admins_699e'],
        '2' => $locale['admins_699b']
    ]
] );
echo '<div class="row"><div class="col-xs-12 col-sm-6">';
echo form_text( 'login_session_expiry', 'Login session time', settings_users_seconds( $settings['login_session_expiry'] ), [
    "mask"        => "00",
    'required'    => TRUE,
    'append'      => TRUE,
    'append_html' => form_select( 'login_session_expiry_c', '', settings_users_time( $settings['login_session_expiry'] ), [
        'class'       => 'm-0',
        'options'     => $time_duration_opts,
        'inner_width' => '100px',
    ] )
] );
echo '</div><div class="col-xs-12 col-sm-6">';
echo form_text( 'login_session_ext_expiry', 'Extended login session time', settings_users_seconds( $settings['login_session_ext_expiry'] ), [
    "mask"        => "00",
    'required'    => TRUE,
    'append'      => TRUE,
    'append_html' => form_select( 'login_session_ext_expiry_c', '', settings_users_time( $settings['login_session_ext_expiry'] ), [
        'class'       => 'm-0',
        'options'     => $time_duration_opts,
        'inner_width' => '100px',
    ] )
] );
echo '</div></div>';

echo '<div class="row"><div class="col-xs-12 col-sm-6">';
echo form_text( 'admin_session_expiry', 'Admin login session time', settings_users_seconds( $settings['admin_session_expiry'] ), [
    "mask"        => "00",
    'required'    => TRUE,
    'append'      => TRUE,
    'append_html' => form_select( 'admin_session_expiry_c', '', settings_users_time( $settings['admin_session_expiry'] ), [
        'class'       => 'm-0',
        'options'     => $time_duration_opts,
        'inner_width' => '100px',
    ] )
] );
echo '</div><div class="col-xs-12 col-sm-6">';
echo form_text( 'admin_session_ext_expiry', 'Admin extended login session time', settings_users_seconds( $settings['admin_session_ext_expiry'] ), [
    "mask"        => "00",
    'required'    => TRUE,
    'append'      => TRUE,
    'append_html' => form_select( 'admin_session_ext_expiry_c', '', settings_users_time( $settings['admin_session_ext_expiry'] ), [
        'class'       => 'm-0',
        'options'     => $time_duration_opts,
        'inner_width' => '100px',
    ] )
] );
echo '</div></div>';
echo closetabbody();

echo opentabbody( $tab['title'][2], $tab['id'][2], $tab_active );
echo "<div class='mb-3'><h5>System Maintenance Settings</h5></div>";

echo form_checkbox( 'maintenance', $locale['admins_657'], $settings['maintenance'], [
    'toggle' => TRUE
] );
echo form_select( 'maintenance_level', $locale['admins_675'], $settings['maintenance_level'], [
    'options'     => [
        USER_LEVEL_ADMIN       => $locale['admins_676'],
        USER_LEVEL_SUPER_ADMIN => $locale['admins_677'],
        USER_LEVEL_MEMBER      => $locale['admins_678']
    ],
    'inline'      => FALSE,
    'width'       => '100%',
    'inner_width' => '100%'
] );
echo form_textarea( 'maintenance_message', $locale['admins_658'], stripslashes( $settings['maintenance_message'] ), ['autosize' => TRUE, 'html' => !fusion_get_settings( 'tinymce_enabled' ), 'form_name' => 'settingsform'] );
echo closetabbody();

echo opentabbody( $tab['title'][3], $tab['id'][3], $tab_active );
echo "<div class='mb-3'><h5>Flood Protection Settings</h5></div>";
echo form_checkbox( 'flood_autoban', $locale['admins_680'], $settings['flood_autoban'], ['toggle' => TRUE] );
echo form_text( 'flood_interval', $locale['admins_660'], $settings['flood_interval'], [
    'type'        => 'number',
    'inner_width' => '150px',
    'width'       => '150px',
    'max_length'  => 2
] );

echo "<div class='mb-3'><h5>Captcha Integration</h5>Captcha systems are used identify real humans before allowing them to post in the website.</div>";
echo form_select( 'captcha', $locale['admins_693'], $settings['captcha'], [
    'options'     => available_captchas(),
    'inline'      => FALSE,
    'width'       => '100%',
    'inner_width' => '100%',
] );

echo "<div id='extDiv' " . ($settings['captcha'] != 'grecaptcha' || $settings['captcha'] != 'grecaptcha3' ? "style='display:none;'" : '') . ">";
if (!$settings['recaptcha_public']) {
    $link = [
        'start' => '[RECAPTCHA_LINK]',
        'end'   => '[/RECAPTCHA_LINK]',
    ];
    $link_replacements = [
        'start' => "<a href='https://www.google.com/recaptcha/admin' target='_BLANK'>",
        'end'   => "</a>\n",
    ];
    $locale['no_keys'] = str_replace( $link, $link_replacements, $locale['no_keys'] );
    echo "<div class='alert alert-warning m-t-10'><i class='fa fa-google fa-lg fa-fw'></i> " . $locale['no_keys'] . "</div>\n";
}
echo '</div>';

echo '<div class="row"><div class="col-xs-12 col-sm-6">';
echo form_text( 'recaptcha_public', $locale['grecaptcha_0100'], $settings['recaptcha_public'], [
    'placeholder' => $locale['grecaptcha_placeholder_1'],
    'required'    => FALSE
] );
echo '</div><div class="col-xs-12 col-sm-6">';
echo form_text( 'recaptcha_private', $locale['grecaptcha_0101'], $settings['recaptcha_private'], [
    'placeholder' => $locale['grecaptcha_placeholder_2'],
    'required'    => FALSE
] );
echo '</div></div>';

echo '<div class="row" id="grecaptcha2" ' . ($settings['captcha'] == 'grecaptcha3' ? 'style="display:none;"' : '') . '><div class="col-xs-12 col-sm-6">';
echo form_select( 'recaptcha_theme', $locale['grecaptcha_0102'], $settings['recaptcha_theme'], [
    'options'     => [
        'light' => $locale['grecaptcha_0102a'],
        'dark'  => $locale['grecaptcha_0102b']
    ],
    'inner_width' => '100%',
    'width'       => '100%'
] );
echo '</div><div class="col-xs-12 col-sm-6">';
echo form_select( 'recaptcha_type', $locale['grecaptcha_0103'], $settings['recaptcha_type'], [
    'options'     => [
        'text'  => $locale['grecaptcha_0103a'],
        'audio' => $locale['grecaptcha_0103b']
    ],
    'type'        => 'number',
    'inner_width' => '100%',
    'width'       => '100%',
    'required'    => TRUE
] );
echo '</div></div>';

echo '<div class="row" id="grecaptcha3" ' . ($settings['captcha'] == 'grecaptcha' ? 'style="display:none;"' : '') . '><div class="col-xs-12 col-sm-6">';
echo form_select( 'recaptcha_score', $locale['grecaptcha_0104'], $settings['recaptcha_score'], [
    'options'     => [
        '1.0' => '1.0',
        '0.9' => '0.9',
        '0.8' => '0.8',
        '0.7' => '0.7',
        '0.6' => '0.6',
        '0.5' => '0.5',
        '0.4' => '0.4',
        '0.3' => '0.3',
        '0.2' => '0.2',
        '0.1' => '0.1'
    ],
    'inline'      => FALSE,
    'width'       => '100%',
    'inner_width' => '100%',
] );
echo '</div><div class="col-xs-12 col-sm-6">';
echo form_select( 'display_validation', $locale['admins_553'], $settings['display_validation'], [
    'options'     => [0 => $locale['disable'], 1 => $locale['enable']],
    'inline'      => FALSE,
    'width'       => '100%',
    'inner_width' => '100%',
] );
echo '</div></div>';
echo form_text('blaclist_site', $locale['admins_security_019'], $settings['blaclist_site'], [
    'type'        => 'url',
    'regex'       => 'http(s)?\:\/\/(.*?)',
    'placeholder' => $locale['admins_security_020']
]);
tablebreak();
echo "<div class='mb-3'><h5>Bad words filters</h5></div>";
echo form_checkbox( 'bad_words_enabled', $locale['admins_659'], $settings['bad_words_enabled'], [
    'toggle' => TRUE
] );
echo form_text( 'bad_word_replace', $locale['admins_654'], $settings['bad_word_replace'] );
echo form_textarea( 'bad_words', $locale['admins_651'], $settings['bad_words'], [
    'placeholder'    => $locale['admins_652'],
    //'autosize'    => TRUE,
    'tags'           => TRUE,
    'floating_label' => TRUE,
    'width'          => '100%',
    'inner_width'    => '100%',
] );

echo closetabbody();
echo closetab();

echo '<div class="mt-3 m-t-20">';
echo form_button( 'savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary'] );
echo '</div>';
echo closeform();
closetable();

add_to_jquery( "
function recaptcha(val) {
    if (val == 'grecaptcha3') {
        $('#grecaptcha3').slideDown('slow');
        $('#grecaptcha2').hide();
    } else {
        $('#grecaptcha3').hide();
        $('#grecaptcha2').slideDown('slow');
    }

    if (val == 'grecaptcha') {
        $('#grecaptcha2').slideDown('slow');
        $('#grecaptcha3').hide();
    } else {
        $('#grecaptcha2').hide();
        $('#grecaptcha3').slideDown('slow');
    }

    if (val == 'grecaptcha' || val == 'grecaptcha3') {
        $('#extDiv').slideDown('slow');
    } else {
        $('#extDiv').hide();
    }
}
recaptcha($('#captcha').select2().val());

$('#captcha').bind('change', function() {
    recaptcha($(this).select2().val());
});
" );

if (check_post( 'clear_cache' )) {
    if ($settings['database_sessions']) {
        $session = \PHPFusion\Sessions::getInstance( COOKIE_PREFIX . 'session' );
        $session->_purge();
    } else {
        // Where system has been disabled and instance could not be found, invoke manually.
        dbquery( "DELETE FROM " . DB_SESSIONS );
    }
    addnotice( 'success', $locale['admins_security_007'] );
    redirect( FUSION_REQUEST );
}

require_once THEMES . 'templates/footer.php';


function available_captchas() {
    $available_captchas = [];
    if ($temp = opendir( INCLUDES . "captchas/" )) {
        while (FALSE !== ($file = readdir( $temp ))) {
            if ($file != "." && $file != ".." && is_dir( INCLUDES . "captchas/" . $file )) {
                $available_captchas[$file] = !empty( $locale[$file] ) ? $locale[$file] : $file;
            }
        }
    }
    return $available_captchas;
}


function settings_users_seconds( $value ) {
    $arr = [
        86400, 3600, 60
    ];
    foreach ($arr as $seconds) {
        if ($value >= $seconds && $value / $seconds >= 1) { // make sure that the number is divisible and do not return numbers less than 1
            return $value / $seconds;
        }
    }
    return $value;
}

// change to the largest denominator value
function settings_users_time( $value ) {
    if ($value >= 86400) { // return day
        return 'j';
    } else if ($value >= 3600) {
        return 'h';
    } else if ($value >= 60) {
        return 'm';
    }
    return 's';
}
