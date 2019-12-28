<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_security.php
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

use PHPFusion\Sessions;
use PHPFusion\UserFieldsQuantum;

require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageAccess('S12');

$settings = fusion_get_settings();

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');

add_breadcrumb(['link' => ADMIN.'settings_security.php'.fusion_get_aidlink(), 'title' => $locale['security_settings']]);

$available_captchas = [];
if ($temp = opendir(INCLUDES."captchas/")) {
    while (FALSE !== ($file = readdir($temp))) {
        if ($file != "." && $file != ".." && is_dir(INCLUDES."captchas/".$file)) {
            $available_captchas[$file] = !empty($locale[$file]) ? $locale[$file] : $file;
        }
    }
}

if (post('clear_cache')) {
    if ($settings['database_sessions']) {
        $session = Sessions::getInstance( COOKIE_PREFIX.'session' );
        $session->_purge();
    } else {
        // Where system has been disabled and instance could not be found, invoke manually.
        dbquery("DELETE FROM ".DB_SESSIONS);
    }
    addNotice('success', $locale['security_007']);
    redirect(FUSION_REQUEST);
}

if (post('savesettings')) {
    // Save settings after validation
    $inputData = [
        'captcha'             => sanitizer('captcha', '', 'captcha'),
        'privacy_policy'      => form_sanitizer( $_POST['privacy_policy'], '', 'privacy_policy', TRUE ),
        'allow_php_exe'       => sanitizer('allow_php_exe', 0, 'allow_php_exe'),
        'flood_interval'      => sanitizer('flood_interval', 15, 'flood_interval'),
        'flood_autoban'       => sanitizer('flood_autoban', 0, 'flood_autoban'),
        'maintenance_level'   => sanitizer('maintenance_level', 102, 'maintenance_level'),
        'maintenance'         => sanitizer('maintenance', 0, 'maintenance'),
        'maintenance_message' => descript(addslashes(post('maintenance_message'))),
        'bad_words_enabled'     => sanitizer('bad_words_enabled', 0, 'bad_words_enabled'),
        'bad_words'             => post( 'bad_words' ),
        'bad_word_replace'      => sanitizer('bad_word_replace', '', 'bad_word_replace'),
        'user_name_ban'         => post( 'user_name_ban' ),
        'database_sessions'     => sanitizer('database_sessions', '', 'database_sessions'),
        'form_tokens'           => sanitizer('form_tokens', '', 'form_tokens'),
        'gateway'               => sanitizer('gateway', 0, 'gateway'),
        'error_logging_enabled' => sanitizer('error_logging_enabled', 0, 'error_logging_enabled'),
        'error_logging_method'  => sanitizer('error_logging_method', '', 'error_logging_method'),
        'mime_check'            => form_sanitizer($_POST['mime_check'], '0', 'mime_check'),
    ];

    // Validate extra fields
    if ($inputData['captcha'] == 'grecaptcha' || $inputData['captcha'] == 'grecaptcha3') {
        // appends captcha settings
        $inputData += [
            'recaptcha_public'  => sanitizer('recaptcha_public', '', 'recaptcha_public'),
            'recaptcha_private' => sanitizer('recaptcha_private', '', 'recaptcha_private')
        ];

        if ($inputData['captcha'] == 'grecaptcha') {
            $inputData += [
                'recaptcha_theme' => sanitizer('recaptcha_theme', '', 'recaptcha_theme'),
                'recaptcha_type'  => sanitizer('recaptcha_type', '', 'recaptcha_type')
            ];
        }

        if ($inputData['captcha'] == 'grecaptcha3') {
            $inputData += [
                'recaptcha_score' => sanitizer('recaptcha_score', '', 'recaptcha_score')
            ];
        }
    }

    if (fusion_safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ]);
        }

        addNotice('success', $locale['900']);
    } else {
        addNotice('danger', $locale['901']);
        addNotice('danger', $locale['696']);
        addNotice('danger', $locale['900']);
    }

    redirect(FUSION_REQUEST);
}

$yes_no_array = ['1' => $locale['yes'], '0' => $locale['no']];

opentable($locale['683']);
echo "<div class='well'>".$locale['security_description']."</div>\n";
echo openform('securityfrm', 'post');
echo "<div class='".grid_row()."'>\n";
echo "<div class='".grid_column_size(100, 70)."'>\n";

// This opens roadmaps to load balancers.
openside('');
echo "<div class='".grid_row()."'>";
echo "<div class='".grid_column_size(100, 40, 20)."'>\n";
echo "<strong>".$locale['security_001']."</strong><br/>".$locale['security_002'];
echo "</div><div class='".grid_column_size(100, 70)."'>\n";
echo form_btngroup('database_sessions', $locale['security_003'], $settings['database_sessions'], [
    'options' => [
        1 => $locale['security_004'],
        0 => $locale['security_005']
    ],
    'class'   => 'btn-default m-b-0'
]);
echo form_button('clear_cache', $locale['security_006'], 'clear_cache', ['class' => 'btn-default m-b-20']);
echo "</div></div>";

echo "<div class='".grid_row()."'>";
echo "<div class='".grid_column_size(100, 40, 20)."'>\n";
echo "<strong>".$locale['security_008']."</strong><br/>".$locale['security_009'];
echo "</div><div class='".grid_column_size(100, 70)."'>\n";
echo form_btngroup('form_tokens', '', $settings['form_tokens'], ['options' => range(0, 10)]);
echo "</div></div>";
closeside();
openside('');
$level_array = [
    USER_LEVEL_ADMIN       => $locale['676'],
    USER_LEVEL_SUPER_ADMIN => $locale['677'],
    USER_LEVEL_MEMBER      => $locale['678']
];
echo form_select('maintenance_level', $locale['675'], $settings['maintenance_level'], [
    'options' => $level_array,
    'inline'  => TRUE,
    'width'   => '100%'
]);
$opts = ['1' => $locale['on'], '0' => $locale['off']];
echo form_select('maintenance', $locale['657'], $settings['maintenance'], [
    'options'     => $opts,
    'inline'      => TRUE,
    'width'       => '100%',
    'inner_width' => '100%'
]);
echo form_textarea('maintenance_message', $locale['658'], stripslashes($settings['maintenance_message']), ['autosize' => TRUE, 'html' => !fusion_get_settings('tinymce_enabled') ? TRUE : FALSE, 'form_name' => 'settingsform']);
closeside();
openside('');
echo UserFieldsQuantum::quantum_multilocale_fields( 'privacy_policy', $locale['820'], $settings['privacy_policy'], [
    'autosize'  => 1,
    'form_name' => 'settingsform',
    'html'      => !fusion_get_settings('tinymce_enabled') ? TRUE : FALSE,
    'function'  => 'form_textarea'
]);
closeside();

openside('');
echo form_select('bad_words_enabled', $locale['659'], $settings['bad_words_enabled'], [
    'options'     => $yes_no_array,
    'inner_width' => '100%',
    'width'       => '100%'
]);
echo form_text('bad_word_replace', $locale['654'], $settings['bad_word_replace']);
echo form_textarea('bad_words', $locale['651'], $settings['bad_words'], [
    'placeholder' => $locale['652'],
    'autosize'    => TRUE
]);
echo form_textarea('user_name_ban', $locale['649'], $settings['user_name_ban'], [
    'placeholder' => $locale['411'],
    'autosize'    => TRUE
]);
closeside();

echo "</div><div class='".grid_column_size(100, 30)."'>\n";
openside('');
echo form_select('captcha', $locale['693'], $settings['captcha'], [
    'options'     => $available_captchas,
    'inner_width' => '100%',
    'width'       => '100%'
]);
echo "<div id='extDiv' ".($settings['captcha'] !== 'grecaptcha' || $settings['captcha'] !== 'grecaptcha3' ? "style='display:none;'" : '').">\n";
if (!$settings['recaptcha_public']) {
    $link = [
        'start' => '[RECAPTCHA_LINK]',
        'end'   => '[/RECAPTCHA_LINK]',
    ];
    $link_replacements = [
        'start' => "<a href='https://www.google.com/recaptcha/admin' target='_BLANK'>",
        'end'   => "</a>\n",
    ];
    $locale['no_keys'] = str_replace($link, $link_replacements, $locale['no_keys']);
    echo "<div class='alert alert-warning m-t-10 col-sm-offset-3'><i class='fa fa-google fa-lg fa-fw'></i> ".$locale['no_keys']."</div>\n";
}
echo "<div class='".grid_row()."'>\n";
echo "<div class='hidden-xs col-sm-3 text-right'>\n";
echo thumbnail(INCLUDES.'captchas/grecaptcha/grecaptcha.svg', '196px');
echo "</div>\n<div class='col-xs-12 col-sm-9'>\n";
echo form_text('recaptcha_public', $locale['grecaptcha_0100'], $settings['recaptcha_public'], [
    'placeholder' => $locale['grecaptcha_placeholder_1'],
    'required'    => FALSE
]);
echo form_text('recaptcha_private', $locale['grecaptcha_0101'], $settings['recaptcha_private'], [
    'placeholder' => $locale['grecaptcha_placeholder_2'],
    'required'    => FALSE
]);

echo '<div id="grecaptcha2" '.($settings['captcha'] == 'grecaptcha3' ? 'style="display:none;"' : '').'>';
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
echo '</div>';

echo '<div id="grecaptcha3" '.($settings['captcha'] == 'grecaptcha' ? 'style="display:none;"' : '').'>';
echo form_select('recaptcha_score', $locale['grecaptcha_0104'], $settings['recaptcha_score'], [
    'options' => [
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
    ]
]);
echo '</div>';

echo "</div>\n</div>\n";
echo "</div>\n";
closeside();

openside('');
echo form_select('mime_check', $locale['699f'], $settings['mime_check'], [
    'options'     => $yes_no_array,
    'width'       => '100%',
    'inner_width' => '100%'
]);
closeside();

openside('');
echo form_select('gateway', $locale['security_010'], $settings['gateway'], [
    'options'     => $yes_no_array,
    'width'       => '100%',
    'inner_width' => '100%'
]);
closeside();

openside('');
$flood_opts = ['1' => $locale['on'], '0' => $locale['off']];
echo form_text('flood_interval', $locale['660'], $settings['flood_interval'], [
    'type'        => 'number',
    'inner_width' => '150px',
    'max_length'  => 2
]);
echo form_select('flood_autoban', $locale['680'], $settings['flood_autoban'], [
    'options'     => $flood_opts,
    'width'       => '100%',
    'inner_width' => '100%'
]);
closeside();
openside('');
echo form_select('error_logging_enabled', $locale['security_011'], $settings['error_logging_enabled'], [
    'options'     => $yes_no_array,
    'width'       => '100%',
    'inner_width' => '100%'
]);
echo form_select('error_logging_method', $locale['security_012'], $settings['error_logging_method'], [
    'options'     => [
        'file'     => $locale['security_013'],
        'database' => $locale['security_014']
    ],
    'width'       => '100%',
    'inner_width' => '100%'
]);
closeside();
openside("");
echo "<div class='alert alert-danger'>".$locale['695']."</div>\n";
echo form_select('allow_php_exe', $locale['694'], $settings['allow_php_exe'], [
    'options'     => $yes_no_array,
    'inner_width' => '100%',
    'width'       => '100%'
]);
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-success']);
echo closeform();
closetable();
add_to_jquery("
function recaptcha(val) {
    if (val == 'grecaptcha3') {
        $('#grecaptcha3').slideDown('slow');
        $('#grecaptcha2').slideUp('slow');
    } else {
        $('#grecaptcha3').slideUp('slow');
        $('#grecaptcha2').slideDown('slow');
    }

    if (val == 'grecaptcha') {
        $('#grecaptcha2').slideDown('slow');
        $('#grecaptcha3').slideUp('slow');
    } else {
        $('#grecaptcha2').slideUp('slow');
        $('#grecaptcha3').slideDown('slow');
    }

    if (val == 'grecaptcha' || val == 'grecaptcha3') {
        $('#extDiv').slideDown('slow');
    } else {
        $('#extDiv').slideUp('slow');
    }
}

recaptcha($('#captcha').select2().val());

$('#captcha').bind('change', function() {
    recaptcha($(this).select2().val());
});
");
require_once THEMES.'templates/footer.php';
