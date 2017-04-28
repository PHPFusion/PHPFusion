<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_security.php
| Author: Paul Beuk (muscapaul)
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
pageAccess('S9');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'settings_security.php'.fusion_get_aidlink(), 'title' => $locale['security_settings']]);
$available_captchas = array();
if ($temp = opendir(INCLUDES."captchas/")) {
    while (FALSE !== ($file = readdir($temp))) {
        if ($file != "." && $file != ".." && is_dir(INCLUDES."captchas/".$file)) {
            $available_captchas[$file] = $locale[$file];
        }
    }
}

$Security_settings = array(
    "captcha"             => fusion_get_settings('captcha'),
    "recaptcha_public"    => fusion_get_settings('recaptcha_public'),
    "recaptcha_private"   => fusion_get_settings('recaptcha_private'),
    "recaptcha_theme"     => fusion_get_settings('recaptcha_theme'),
    "recaptcha_type"      => fusion_get_settings('recaptcha_type'),
    "privacy_policy"      => fusion_get_settings('privacy_policy'),
    "allow_php_exe"       => fusion_get_settings('allow_php_exe'),
    "flood_interval"      => fusion_get_settings('flood_interval'),
    "flood_autoban"       => fusion_get_settings('flood_autoban'),
    "maintenance_level"   => fusion_get_settings('maintenance_level'),
    "maintenance"         => fusion_get_settings('maintenance'),
    "maintenance_message" => fusion_get_settings('maintenance_message'),
    "bad_words_enabled"   => fusion_get_settings('bad_words_enabled'),
    "bad_words"           => fusion_get_settings('bad_words'),
    "bad_word_replace"    => fusion_get_settings('bad_word_replace'),
    "UserName_ban"        => fusion_get_settings('UserName_ban')
);

if (isset($_POST['savesettings'])) {
    $privacy_policy = addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['privacy_policy']));
    $maintenance_message = addslash(descript($_POST['maintenance_message']));
    // Save settings after validation
    $Security_settings = array(
        "captcha"             => form_sanitizer($_POST['captcha'], "", "captcha"),
        "privacy_policy"      => $privacy_policy,
        "allow_php_exe"       => form_sanitizer($_POST['allow_php_exe'], 0, "allow_php_exe"),
        "flood_interval"      => form_sanitizer($_POST['flood_interval'], 15, "flood_interval"),
        "flood_autoban"       => form_sanitizer($_POST['flood_autoban'], 1, "flood_autoban"),
        "maintenance_level"   => form_sanitizer($_POST['maintenance_level'], 102, "maintenance_level"),
        "maintenance"         => form_sanitizer($_POST['maintenance'], 0, "maintenance"),
        "maintenance_message" => form_sanitizer($_POST['maintenance_message'], "", "maintenance_message"),
        "bad_words_enabled"   => form_sanitizer($_POST['bad_words_enabled'], 0, "bad_words_enabled"),
        "bad_words"           => form_sanitizer($_POST['bad_words'], "", "bad_words"),
        "bad_word_replace"    => form_sanitizer($_POST['bad_word_replace'], "", "bad_word_replace"),
        "UserName_ban"        => form_sanitizer($_POST['UserName_ban'], "", "UserName_ban")
    );
    // Validate extra fields
    if ($Security_settings['captcha'] == "grecaptcha") {
        // appends captcha settings
        $Security_settings += array(
            "recaptcha_public"  => form_sanitizer($_POST['recaptcha_public'], "", "recaptcha_public"),
            "recaptcha_private" => form_sanitizer($_POST['recaptcha_private'], "", "recaptcha_private"),
            "recaptcha_theme"   => form_sanitizer($_POST['recaptcha_theme'], "", "recaptcha_theme"),
            "recaptcha_type"    => form_sanitizer($_POST['recaptcha_type'], "", "recaptcha_type"),
        );
    }
    if (\defender::safe()) {
        foreach ($Security_settings as $key => $value) {
            $result = NULL;
            if (\defender::safe()) {
                $Array = array(
                    "settings_name" => $key,
                    "settings_value" => $value,
                );
                dbquery_insert(DB_SETTINGS, $Array, 'update', array("primary_key" => "settings_name"));
            }
        }
        addNotice('success', $locale['900']);
    } else {
        // send message your settings was not safe. :)
        addNotice('danger', $locale['901']);
        addNotice('danger', $locale['696']);
        addNotice('danger', $locale['900']);
    }
    redirect(FUSION_SELF.fusion_get_aidlink());
}

opentable($locale['683']);
echo "<div class='well'>".$locale['security_description']."</div>\n";
echo openform('settingsform', 'post', FUSION_SELF.fusion_get_aidlink());
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-8'>\n";
openside('');
echo form_select('captcha', $locale['693'], $Security_settings['captcha'], array(
    'options' => $available_captchas,
    'inline' => TRUE
));
echo "<div id='extDiv' ".($Security_settings['captcha'] !== 'grecaptcha' ? "style='display:none;'" : '').">\n";
if (!$Security_settings['recaptcha_public']) {
    $link = array(
        "start" => "[RECAPTCHA_LINK]",
        "end" => "[/RECAPTCHA_LINK]",
    );
    $link_replacements = array(
        "start" => "<a href='https://www.google.com/recaptcha/admin' target='_BLANK'>",
        "end" => "</a>\n",
    );
    $locale['no_keys'] = str_replace($link, $link_replacements, $locale['no_keys']);
    echo "<div class='alert alert-warning col-sm-offset-3'><i class='fa fa-google fa-lg fa-fw'></i> ".$locale['no_keys']."</div>\n";
}
echo "<div class='row'>\n";
echo "<div class='hidden-xs col-sm-3 text-right'>\n";
echo thumbnail(INCLUDES."captchas/grecaptcha/grecaptcha.png", "250px");
echo "</div>\n<div class='col-xs-12 col-sm-9'>\n";
echo form_text('recaptcha_public', $locale['grecaptcha_0100'], $Security_settings['recaptcha_public'], array(
    'inline' => TRUE,
    'placeholder' => $locale['grecaptcha_placeholder_1'],
    'required' => FALSE
)); // site key
echo form_text('recaptcha_private', $locale['grecaptcha_0101'], $Security_settings['recaptcha_private'], array(
    'inline' => TRUE,
    'placeholder' => $locale['grecaptcha_placeholder_2'],
    'required' => FALSE
)); // secret key
echo form_select('recaptcha_theme', $locale['grecaptcha_0102'], $Security_settings['recaptcha_theme'], array(
    'options' => array(
        'light' => $locale['grecaptcha_0102a'],
        'dark' => $locale['grecaptcha_0102b']
    ),
    'inline' => TRUE
));
echo form_select('recaptcha_type', $locale['grecaptcha_0103'], $Security_settings['recaptcha_type'], array(
    "options" => array(
        "text" => $locale['grecaptcha_0103a'],
        "audio" => $locale['grecaptcha_0103b']
    ),
    'inline' => TRUE,
    'type' => 'number',
    'width' => '150px',
    'required' => TRUE
));
echo "</div>\n</div>\n";
echo "</div>\n";
closeside();
openside('');
$level_array = array(
    USER_LEVEL_ADMIN => $locale['676'],
    USER_LEVEL_SUPER_ADMIN => $locale['677'],
    USER_LEVEL_MEMBER => $locale['678']
);
echo form_select('maintenance_level', $locale['675'], $Security_settings['maintenance_level'], array(
    'options' => $level_array,
    'inline' => TRUE,
    'width' => '100%'
));
$opts = array('1' => $locale['on'], '0' => $locale['off']);
echo form_select('maintenance', $locale['657'], $Security_settings['maintenance'], array(
    'options' => $opts,
    'inline' => TRUE,
    'width' => '100%'
));
echo form_textarea('maintenance_message', $locale['658'], $Security_settings['maintenance_message'], array('autosize' => TRUE));
closeside();
openside('');
echo form_textarea('privacy_policy', $locale['820'], $Security_settings['privacy_policy'], array(
    'autosize' => 1,
    'form_name' => 'settingsform',
    'html' => !fusion_get_settings('tinymce_enabled') ? TRUE : FALSE
));
closeside();
echo "</div><div class='col-xs-12 col-sm-4'>\n";
openside('');
$flood_opts = array('1' => $locale['on'], '0' => $locale['off']);
echo form_text('flood_interval', $locale['660'], $Security_settings['flood_interval'], array('type' => 'number', 'inner_width' => '150px','max_length' => 2));
echo form_select('flood_autoban', $locale['680'], $Security_settings['flood_autoban'], array(
    'options' => $flood_opts,
    'width' => '100%'
));
closeside();
openside('');
$yes_no_array = array('1' => $locale['yes'], '0' => $locale['no']);
echo form_select('bad_words_enabled', $locale['659'], $Security_settings['bad_words_enabled'], array('options' => $yes_no_array));
echo form_text('bad_word_replace', $locale['654'], $Security_settings['bad_word_replace']);
echo form_textarea('bad_words', $locale['651'], $Security_settings['bad_words'], array(
    'placeholder' => $locale['652'],
    'autosize' => TRUE
));
echo form_textarea('UserName_ban', $locale['649'], $Security_settings['UserName_ban'], array(
    'placeholder' => $locale['411'],
    'autosize' => TRUE
));
closeside();
openside("");
echo "<div class='alert alert-danger'>".$locale['695']."</div>\n";
echo form_select('allow_php_exe', $locale['694'], $Security_settings['allow_php_exe'], array('options' => $yes_no_array));
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success'));
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
