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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageaccess('S12');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');
$settings = fusion_get_settings();

add_breadcrumb(['link' => ADMIN.'settings_security.php'.fusion_get_aidlink(), 'title' => $locale['admins_security_settings']]);

$available_captchas = [];
if ($temp = opendir(INCLUDES."captchas/")) {
    while (FALSE !== ($file = readdir($temp))) {
        if ($file != "." && $file != ".." && is_dir(INCLUDES."captchas/".$file)) {
            $available_captchas[$file] = !empty($locale[$file]) ? $locale[$file] : $file;
        }
    }
}

$is_multilang = count(fusion_get_enabled_languages()) > 1;

if (check_post('clear_cache')) {
    if ($settings['database_sessions']) {
        $session = \PHPFusion\Sessions::getInstance(COOKIE_PREFIX.'session');
        $session->_purge();
    } else {
        // Where system has been disabled and instance could not be found, invoke manually.
        dbquery("DELETE FROM ".DB_SESSIONS);
    }
    addnotice('success', $locale['admins_security_007']);
    redirect(FUSION_REQUEST);
}

if (check_post('savesettings')) {
    // Save settings after validation
    $inputData = [
        'captcha'             => sanitizer('captcha', '', 'captcha'),
        'display_validation'  => post('display_validation') ? 1 : 0,
        'privacy_policy'      => sanitizer($is_multilang ? ['privacy_policy'] : 'privacy_policy', '', 'privacy_policy', $is_multilang),
        'flood_interval'      => sanitizer('flood_interval', 15, 'flood_interval'),
        'flood_autoban'       => post('flood_autoban') ? 1 : 0,
        'maintenance_level'   => sanitizer('maintenance_level', -102, 'maintenance_level'),
        'maintenance'         => post('maintenance') ? 1 : 0,
        'maintenance_message' => sanitizer('maintenance_message', '', 'maintenance_message'),
        'bad_words_enabled'   => post('bad_words_enabled') ? 1 : 0,
        'bad_words'           => stripinput(post('bad_words')),
        'bad_word_replace'    => sanitizer('bad_word_replace', '', 'bad_word_replace'),
        'database_sessions'   => sanitizer('database_sessions', 0, 'database_sessions'),
        'form_tokens'         => sanitizer('form_tokens', '', 'form_tokens'),
        'mime_check'          => post('mime_check') ? 1 : 0,
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

        addnotice('success', $locale['admins_900']);
    } else {
        addnotice('danger', $locale['admins_901']);
        addnotice('danger', $locale['admins_696']);
        addnotice('danger', $locale['admins_900']);
    }

    redirect(FUSION_REQUEST);
}

opentable($locale['admins_683']);
echo "<div class='well'>".$locale['admins_security_description']."</div>\n";
echo openform('settingsform', 'post');
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-8'>\n";
// This opens roadmaps to load balancers.
openside('');
echo "<div class='row'><div class='col-xs-12 col-sm-3'>\n";
echo "<strong>".$locale['admins_security_001']."</strong><br/>".$locale['admins_security_002'];
echo "</div><div class='col-xs-12 col-sm-9'>\n";
echo form_btngroup('database_sessions', $locale['admins_security_003'], $settings['database_sessions'], [
    'options' => [
        1 => $locale['admins_security_004'],
        0 => $locale['admins_security_005']
    ],
    'class'   => 'btn-default m-b-0'
]);
echo form_button('clear_cache', $locale['admins_security_006'], 'clear_cache', ['class' => 'btn-default m-b-20']);
echo "</div></div>";

echo "<div class='row'><div class='col-xs-12 col-sm-3'>\n";
echo "<strong>".$locale['admins_security_008']."</strong><br/>".$locale['admins_security_009'];
echo "</div><div class='col-xs-12 col-sm-9'>\n";
echo form_btngroup('form_tokens', '', $settings['form_tokens'], ['options' => range(0, 10)]);
echo "</div></div>";
closeside();
openside('');
echo form_select('maintenance_level', $locale['admins_675'], $settings['maintenance_level'], [
    'options' => [
        USER_LEVEL_ADMIN       => $locale['admins_676'],
        USER_LEVEL_SUPER_ADMIN => $locale['admins_677'],
        USER_LEVEL_MEMBER      => $locale['admins_678']
    ],
    'inline'  => TRUE,
    'width'   => '100%'
]);

echo form_checkbox('maintenance', $locale['admins_657'], $settings['maintenance'], [
    'toggle' => TRUE
]);
echo form_textarea('maintenance_message', $locale['admins_658'], stripslashes($settings['maintenance_message']), ['autosize' => TRUE, 'html' => !fusion_get_settings('tinymce_enabled'), 'form_name' => 'settingsform']);
closeside();
openside('');
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
echo "</div><div class='col-xs-12 col-sm-4'>\n";
openside('');
echo form_select('captcha', $locale['admins_693'], $settings['captcha'], [
    'options' => $available_captchas,
    'class'   => 'm-b-0'
]);
echo "<div id='extDiv' ".($settings['captcha'] != 'grecaptcha' || $settings['captcha'] != 'grecaptcha3' ? "style='display:none;'" : '').">\n";
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
    echo "<div class='alert alert-warning m-t-10'><i class='fa fa-google fa-lg fa-fw'></i> ".$locale['no_keys']."</div>\n";
}
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

echo "</div>\n";

echo form_checkbox('display_validation', $locale['admins_553'], $settings['display_validation'], [
    'toggle' => TRUE,
    'class'  => 'm-t-10'
]);
closeside();

openside('');
echo form_checkbox('mime_check', $locale['admins_699f'], $settings['mime_check'], [
    'toggle' => TRUE
]);
closeside();

openside('');
echo form_text('flood_interval', $locale['admins_660'], $settings['flood_interval'], [
    'type'        => 'number',
    'inner_width' => '150px',
    'max_length'  => 2
]);
echo form_checkbox('flood_autoban', $locale['admins_680'], $settings['flood_autoban'], [
    'toggle' => TRUE
]);
closeside();
openside('');
echo form_checkbox('bad_words_enabled', $locale['admins_659'], $settings['bad_words_enabled'], [
    'toggle' => TRUE
]);
echo form_text('bad_word_replace', $locale['admins_654'], $settings['bad_word_replace']);
echo form_textarea('bad_words', $locale['admins_651'], $settings['bad_words'], [
    'placeholder' => $locale['admins_652'],
    'autosize'    => TRUE
]);
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
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
