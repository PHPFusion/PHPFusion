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
require_once __DIR__.'/../maincore.php';
require_once THEMES."templates/admin_header.php";

pageAccess('S9');
$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'settings_security.php'.fusion_get_aidlink(), 'title' => $locale['security_settings']]);
$available_captchas = [];
if ($temp = opendir(INCLUDES."captchas/")) {
    while (FALSE !== ($file = readdir($temp))) {
        if ($file != "." && $file != ".." && is_dir(INCLUDES."captchas/".$file)) {
            $available_captchas[$file] = $locale[$file];
        }
    }
}

$settings = fusion_get_settings();
$settings_data = [
    'captcha'             => $settings['captcha'],
    'privacy_policy'      => $settings['privacy_policy'],
    'allow_php_exe'       => $settings['allow_php_exe'],
    'flood_interval'      => $settings['flood_interval'],
    'flood_autoban'       => $settings['flood_autoban'],
    'maintenance_level'   => $settings['maintenance_level'],
    'maintenance'         => $settings['maintenance'],
    'maintenance_message' => $settings['maintenance_message'],
    'maintenance_level'   => $settings['maintenance_level'],
    'bad_words_enabled'   => $settings['bad_words_enabled'],
    'bad_words'           => $settings['bad_words'],
    'bad_word_replace'    => $settings['bad_word_replace'],
    'user_name_ban'       => $settings['user_name_ban'],
    'database_sessions'   => $settings['database_sessions'],
];

if (isset($_POST['clear_cache'])) {
    if ($settings_data['database_sessions']) {
        $session = \PHPFusion\Sessions::getInstance(COOKIE_PREFIX.'session');
        $session->_purge();
    } else {
        // Where system has been disabled and instance could not be found, invoke manually.
        dbquery("DELETE FROM ".DB_SESSIONS);
    }
    addNotice('success', $locale['security_007']);
    redirect(FUSION_REQUEST);
}

if (isset($_POST['savesettings'])) {
    // Save settings after validation
    $settings_data = [
        'captcha'             => form_sanitizer($_POST['captcha'], '', 'captcha'),
        'privacy_policy'      => addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['privacy_policy'])),
        'allow_php_exe'       => form_sanitizer($_POST['allow_php_exe'], 0, 'allow_php_exe'),
        'flood_interval'      => form_sanitizer($_POST['flood_interval'], 15, 'flood_interval'),
        'flood_autoban'       => form_sanitizer($_POST['flood_autoban'], 0, 'flood_autoban'),
        'maintenance_level'   => form_sanitizer($_POST['maintenance_level'], 102, 'maintenance_level'),
        'maintenance'         => form_sanitizer($_POST['maintenance'], 0, 'maintenance'),
        'maintenance_message' => addslash(descript($_POST['maintenance_message'])),
        'bad_words_enabled'   => form_sanitizer($_POST['bad_words_enabled'], 0, 'bad_words_enabled'),
        'bad_words'           => stripinput($_POST['bad_words']),
        'bad_word_replace'    => form_sanitizer($_POST['bad_word_replace'], '', 'bad_word_replace'),
        'user_name_ban'       => form_sanitizer($_POST['user_name_ban'], '', 'user_name_ban'),
        'database_sessions'   => form_sanitizer($_POST['database_sessions'], '', 'database_sessions')
    ];

    // Validate extra fields
    if ($settings_data['captcha'] == "grecaptcha") {
        // appends captcha settings
        $settings_data += [
            'recaptcha_public'  => form_sanitizer($_POST['recaptcha_public'], '', 'recaptcha_public'),
            'recaptcha_private' => form_sanitizer($_POST['recaptcha_private'], '', 'recaptcha_private'),
            'recaptcha_theme'   => form_sanitizer($_POST['recaptcha_theme'], '', 'recaptcha_theme'),
            'recaptcha_type'    => form_sanitizer($_POST['recaptcha_type'], '', 'recaptcha_type'),
        ];
    }

    if (\defender::safe()) {
        foreach ($settings_data as $key => $value) {
            $data = [
                'settings_name'  => $key,
                'settings_value' => $value,
            ];
            dbquery_insert(DB_SETTINGS, $data, 'update', ['primary_key' => 'settings_name']);
        }
        addNotice('success', $locale['900']);

    } else {
        addNotice('danger', $locale['901']);
        addNotice('danger', $locale['696']);
        addNotice('danger', $locale['900']);
    }

    redirect(FUSION_REQUEST);
}

opentable($locale['683']);
echo "<div class='well'>".$locale['security_description']."</div>\n";
echo openform('settingsform', 'post', FUSION_REQUEST);
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-8'>\n";

// This opens roadmaps to load balancers.
openside('');
echo "<div class='row'><div class='col-xs-12 col-sm-3'>\n";
echo "<strong>".$locale['security_001']."</strong><br/>".$locale['security_002'];
echo "</div><div class='col-xs-12 col-sm-9'>\n";
echo form_btngroup('database_sessions', $locale['security_003'], $settings['database_sessions'], [
    'options' => [
        1 => $locale['security_004'],
        0 => $locale['security_005']
    ],
]);
echo form_button('clear_cache', $locale['security_006'], 'clear_cache');
echo "</div></div>";
closeside();

openside('');
echo form_select('captcha', $locale['693'], $settings['captcha'], [
    'options' => $available_captchas,
    'inline'  => TRUE
]);
echo "<div id='extDiv' ".($settings['captcha'] !== 'grecaptcha' ? "style='display:none;'" : '').">\n";
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
    echo "<div class='alert alert-warning col-sm-offset-3'><i class='fa fa-google fa-lg fa-fw'></i> ".$locale['no_keys']."</div>\n";
}
echo "<div class='row'>\n";
echo "<div class='hidden-xs col-sm-3 text-right'>\n";
echo thumbnail(INCLUDES."captchas/grecaptcha/grecaptcha.png", "196px");
echo "</div>\n<div class='col-xs-12 col-sm-9'>\n";
echo form_text('recaptcha_public', $locale['grecaptcha_0100'], $settings['recaptcha_public'], [
    'inline'      => TRUE,
    'placeholder' => $locale['grecaptcha_placeholder_1'],
    'required'    => FALSE
]);
echo form_text('recaptcha_private', $locale['grecaptcha_0101'], $settings['recaptcha_private'], [
    'inline'      => TRUE,
    'placeholder' => $locale['grecaptcha_placeholder_2'],
    'required'    => FALSE
]);
echo form_select('recaptcha_theme', $locale['grecaptcha_0102'], $settings['recaptcha_theme'], [
    'options'     => [
        'light' => $locale['grecaptcha_0102a'],
        'dark'  => $locale['grecaptcha_0102b']
    ],
    'inline'      => TRUE,
    'inner_width' => '100%',
    'width'       => '100%',
]);
echo form_select('recaptcha_type', $locale['grecaptcha_0103'], $settings['recaptcha_type'], [
    'options'     => [
        'text'  => $locale['grecaptcha_0103a'],
        'audio' => $locale['grecaptcha_0103b']
    ],
    'inline'      => TRUE,
    'type'        => 'number',
    'inner_width' => '100%',
    'width'       => '100%',
    'required'    => TRUE
]);
echo "</div>\n</div>\n";
echo "</div>\n";
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
    'inner_width' => '100%',
    'width'       => '100%',
]);
echo form_textarea('maintenance_message', $locale['658'], $settings['maintenance_message'], ['autosize' => TRUE]);
closeside();
openside('');
echo form_textarea('privacy_policy', $locale['820'], $settings['privacy_policy'], [
    'autosize'  => 1,
    'form_name' => 'settingsform',
    'html'      => !fusion_get_settings('tinymce_enabled') ? TRUE : FALSE
]);
closeside();

echo "</div><div class='col-xs-12 col-sm-4'>\n";
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
    'inner_width' => '100%',
    'width'       => '100%',
]);
closeside();
openside('');
$yes_no_array = ['1' => $locale['yes'], '0' => $locale['no']];
echo form_select('bad_words_enabled', $locale['659'], $settings['bad_words_enabled'], [
    'options'     => $yes_no_array,
    'inner_width' => '100%',
    'width'       => '100%',
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
openside("");
echo "<div class='alert alert-danger'>".$locale['695']."</div>\n";
echo form_select('allow_php_exe', $locale['694'], $settings['allow_php_exe'], [
    'options'     => $yes_no_array,
    'inner_width' => '100%',
    'width'       => '100%',
]);
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-success']);
echo closeform();
closetable();
add_to_jquery("
val = $('#captcha').select2().val();
if (val == 'grecaptcha') {
    $('#extDiv').slideDown('slow');
} else {
    $('#extDiv').slideUp('slow');
}
$('#captcha').bind('change', function() {
    var val = $(this).select2().val();
    if (val == 'grecaptcha') {
        $('#extDiv').slideDown('slow');
    } else {
        $('#extDiv').slideUp('slow');
    }
});
");
require_once THEMES."templates/footer.php";
