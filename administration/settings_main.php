<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_main.php
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
pageAccess('S1');
require_once THEMES.'templates/admin_header.php';

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'settings_main.php'.fusion_get_aidlink(), 'title' => $locale['main_settings']]);
/**
 * Get the default search options
 * with file exists validation of the PHP-Fusion Search SDK files.
 *
 * @return array
 */
function get_default_search_opts() {
    $locale = fusion_get_locale();

    static $search_opts = [];

    if (empty($search_opts)) {
        $search_opts += [
            'all' => $locale['419a'],
        ];

        if ($handle = opendir(INCLUDES."search/")) {
            while (FALSE !== ($file = readdir($handle))) {
                if (preg_match("/_include.php/i", $file)) {
                    $name = '';
                    $search_name = explode("_", $file);
                    $locale += fusion_get_locale('', LOCALE.LOCALESET."search/".$search_name[1].".php");
                    foreach ($locale as $key => $value) {
                        if (preg_match("/400/i", $key)) {
                            $name = $key;
                        }
                    }

                    if (isset($locale[$name])) {
                        $file = str_replace(['search_', '_include.php', '_include_button.php'], '', $file);
                        $search_opts[$file] = $locale[$name];
                    }
                }
            }
            closedir($handle);
        }

        $infusions = makefilelist(INFUSIONS, ".|..|index.php", TRUE, "folders");
        if (!empty($infusions)) {
            foreach ($infusions as $infusions_to_check) {
                if (is_dir(INFUSIONS.$infusions_to_check.'/search/')) {
                    $inf_files = makefilelist(INFUSIONS.$infusions_to_check.'/search/', ".|..|index.php", TRUE, "files");

                    if (!empty($inf_files)) {
                        foreach ($inf_files as $file) {
                            if (preg_match("/_include.php/i", $file)) {
                                $file = str_replace(['search_', '_include.php', '_include_button.php'], '', $file);

                                if (file_exists(INFUSIONS.$infusions_to_check.'/locale/'.LOCALESET."search/".$file.".php")) {
                                    $locale_file = INFUSIONS.$infusions_to_check.'/locale/'.LOCALESET."search/".$file.".php";
                                } else {
                                    $locale_file = INFUSIONS.$infusions_to_check."/locale/English/search/".$file.".php";
                                }

                                $locale += fusion_get_locale('', $locale_file);
                                $search_opts[$file] = !empty($locale[$file.'.php']) ? $locale[$file.'.php'] : $file;
                            }
                        }
                    }
                }
            }
        }

    }

    return (array)$search_opts;
}

/**
 * Default Search file validation rules
 *
 * @param $value
 *
 * @return bool
 */
function validate_default_search($value) {
    $search_opts = get_default_search_opts();

    return (in_array($value, array_keys($search_opts)) ? TRUE : FALSE);
}

/**
 * Site Port validation rules
 *
 * @param $value
 *
 * @return bool
 */
function validate_site_port($value) {
    return ((isnum($value) || empty($value)) && in_array($value, [0, 80, 443]) or $value < 65001) ? TRUE : FALSE;
}

$settings = fusion_get_settings();

// Saving settings
if (isset($_POST['savesettings'])) {
    $inputData = [
        'siteintro'       => descript(addslashes($_POST['siteintro'])),
        'sitename'        => form_sanitizer($_POST['sitename'], '', 'sitename'),
        'sitebanner'      => form_sanitizer($_POST['sitebanner'], '', 'sitebanner'),
        'siteemail'       => form_sanitizer($_POST['siteemail'], '', 'siteemail'),
        'siteusername'    => form_sanitizer($_POST['siteusername'], '', 'siteusername'),
        'footer'          => descript(addslashes($_POST['footer'])),
        'site_protocol'   => form_sanitizer($_POST['site_protocol'], '', 'site_protocol'),
        'site_host'       => form_sanitizer($_POST['site_host'], '', 'site_host'),
        'site_path'       => form_sanitizer($_POST['site_path'], '', 'site_path'),
        'site_port'       => form_sanitizer($_POST['site_port'], '', 'site_port'),
        'description'     => form_sanitizer($_POST['description'], '', 'description'),
        'keywords'        => form_sanitizer($_POST['keywords'], '', 'keywords'),
        'opening_page'    => form_sanitizer($_POST['opening_page'], '', 'opening_page'),
        'default_search'  => form_sanitizer($_POST['default_search'], '', 'default_search'),
        'exclude_left'    => form_sanitizer($_POST['exclude_left'], '', 'exclude_left'),
        'exclude_upper'   => form_sanitizer($_POST['exclude_upper'], '', 'exclude_upper'),
        'exclude_aupper'  => form_sanitizer($_POST['exclude_aupper'], '', 'exclude_aupper'),
        'exclude_lower'   => form_sanitizer($_POST['exclude_lower'], '', 'exclude_lower'),
        'exclude_blower'  => form_sanitizer($_POST['exclude_blower'], '', 'exclude_blower'),
        'exclude_right'   => form_sanitizer($_POST['exclude_right'], '', 'exclude_right'),
        'exclude_user1'   => form_sanitizer($_POST['exclude_user1'], '', 'exclude_user1'),
        'exclude_user2'   => form_sanitizer($_POST['exclude_user2'], '', 'exclude_user2'),
        'exclude_user3'   => form_sanitizer($_POST['exclude_user3'], '', 'exclude_user3'),
        'exclude_user4'   => form_sanitizer($_POST['exclude_user4'], '', 'exclude_user4'),
        'logoposition_xs' => form_sanitizer($_POST['logoposition_xs'], '', 'logoposition_xs'),
        'logoposition_sm' => form_sanitizer($_POST['logoposition_sm'], '', 'logoposition_sm'),
        'logoposition_md' => form_sanitizer($_POST['logoposition_md'], '', 'logoposition_md'),
        'logoposition_lg' => form_sanitizer($_POST['logoposition_lg'], '', 'logoposition_lg'),
        'domain_server'   => form_sanitizer($_POST['domain_server'], '', 'domain_server')
    ];

    if (strpos($inputData['site_host'], "/") !== FALSE) {
        $inputData['site_host'] = explode("/", $inputData['site_host'], 2);
        if ($inputData['site_host'][1] != "") {
            $_POST['site_path'] = "/".$inputData['site_host'][1];
        }
        $inputData['site_host'] = $inputData['site_host'][0];
    }

    $inputData['siteurl'] = $inputData['site_protocol']."://".$inputData['site_host'].($inputData['site_port'] ? ":".$inputData['site_port'] : "").$inputData['site_path'];

    if (!empty($inputData['domain_server'])) {
        $inputData['domain_server'] = str_replace(PHP_EOL, '|', $inputData['domain_server']);
    }

    if (\defender::safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ]);
        }

        addNotice("success", $locale['900']);
        redirect(FUSION_REQUEST);
    }
}

echo fusion_get_function('opentable', $locale['main_settings']);
echo "<div class='well'>".$locale['main_description']."</div>";
echo openform('settingsform', 'post', FUSION_REQUEST);
echo "<div class='row'><div class='col-xs-12 col-sm-12 col-md-6'>\n";
echo fusion_get_function('openside', '');
echo form_text('sitename', $locale['402'], $settings['sitename'], [
    'inline'     => FALSE,
    'max_length' => 255,
    'required'   => TRUE,
    'error_text' => $locale['error_value']
]);
echo form_text('siteemail', $locale['405'], $settings['siteemail'], [
    'inline'     => FALSE,
    'required'   => TRUE,
    'max_length' => 128,
    'type'       => 'email'
]);
echo form_text('siteusername', $locale['406'], $settings['siteusername'], [
    'required'   => TRUE,
    'inline'     => FALSE,
    'max_length' => 32,
    'error_text' => $locale['error_value']
]);
echo form_text('opening_page', $locale['413'], $settings['opening_page'], [
    'required'   => TRUE,
    'max_length' => 100,
    'error_text' => $locale['error_value']
]);
echo form_textarea('siteintro', $locale['407'], stripslashes($settings['siteintro']), [
    'type'     => 'tinymce',
    'tinymce'  => 'simple',
    'autosize' => TRUE
]);
echo form_textarea('footer', $locale['412'], stripslashes($settings['footer']), [
    'autosize' => TRUE,
    'type'     => 'tinymce',
    'tinymce'  => 'simple'
]);
echo fusion_get_function('closeside', '');

echo fusion_get_function('openside', '');
echo form_text('sitebanner', $locale['404'], $settings['sitebanner'], [
    'inline'     => TRUE,
    'required'   => TRUE,
    'error_text' => $locale['error_value']]);

$options_xs = [
    'logo-xs-left'   => $locale['404left'],
    'logo-xs-center' => $locale['404center'],
    'logo-xs-right'  => $locale['404right']
];

echo form_select('logoposition_xs', $locale['404XS'], $settings['logoposition_xs'], [
    'inline'  => TRUE,
    'options' => $options_xs
]);

$options_sm = [
    'logo-sm-left'   => $locale['404left'],
    'logo-sm-center' => $locale['404center'],
    'logo-sm-right'  => $locale['404right']
];

echo form_select('logoposition_sm', $locale['404SM'], $settings['logoposition_sm'], [
    'inline'  => TRUE,
    'options' => $options_sm
]);

$options_md = [
    'logo-md-left'   => $locale['404left'],
    'logo-md-center' => $locale['404center'],
    'logo-md-right'  => $locale['404right']
];

echo form_select('logoposition_md', $locale['404MD'], $settings['logoposition_md'], [
    'inline'  => TRUE,
    'options' => $options_md
]);

$options_lg = [
    'logo-lg-left'   => $locale['404left'],
    'logo-lg-center' => $locale['404center'],
    'logo-lg-right'  => $locale['404right']
];

echo form_select('logoposition_lg', $locale['404LG'], $settings['logoposition_lg'], [
    'inline'  => TRUE,
    'options' => $options_lg
]);
echo fusion_get_function('closeside', '');

echo fusion_get_function('openside', '');
echo form_textarea('description', $locale['409'], $settings['description'], ['autosize' => TRUE]);
echo form_textarea('keywords', $locale['410'], $settings['keywords'], ['autosize' => TRUE, 'ext_tip' => $locale['411']]);
echo form_select('default_search', $locale['419'], $settings['default_search'], [
    'options'        => get_default_search_opts(),
    'callback_check' => 'validate_default_search'
]);

echo fusion_get_function('closeside', '');
echo "</div><div class='col-xs-12 col-sm-12 col-md-6'>\n";

echo fusion_get_function('openside', '');
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-3'>\n";
echo "<strong>".$locale['401a']."</strong><br/><i>".$locale['401b']."</i>";
echo "<div class='spacer-xs'>\n";
echo "<i class='fa fa-external-link m-r-10'></i>";
echo "<span id='display_protocol'>".$settings['site_protocol']."</span>://";
echo "<span id='display_host'>".$settings['site_host']."</span>";
echo "<span id='display_port'>".($settings['site_port'] ? ":".$settings['site_port'] : "")."</span>";
echo "<span id='display_path'>".$settings['site_path']."</span>";
echo "</div>\n";
echo "</div>\n<div class='col-xs-12 col-sm-9'>\n";
echo form_select('site_protocol', $locale['426'], $settings['site_protocol'], [
    'inline'     => TRUE,
    'regex'      => 'http(s)?',
    'error_text' => $locale['error_value'],
    'options'    => [
        'http'             => 'http://',
        'https'            => 'https://',
        'invalid_protocol' => $locale['445']
    ]
]);
echo form_text('site_host', $locale['427'], $settings['site_host'], [
    'required'   => TRUE,
    'inline'     => TRUE,
    'max_length' => 255,
    'error_text' => $locale['error_value']
]);
echo form_text('site_path', $locale['429'], $settings['site_path'], [
    'required'   => TRUE,
    'inline'     => TRUE,
    'regex'      => '\/([a-z0-9-_]+\/)*?',
    'max_length' => 255
]);
echo form_text('site_port', $locale['430'], $settings['site_port'], [
    'inline'         => TRUE,
    'required'       => FALSE,
    'placeholder'    => 80,
    'max_length'     => 5,
    'type'           => 'number',
    'inner_width'    => '150px',
    'error_text'     => $locale['430_error'],
    'callback_check' => 'validate_site_port',
    'ext_tip'        => $locale['430_desc']
]);
echo "</div>\n</div>\n";
// Domain names
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-3'>\n";
echo "<strong>".$locale['444']."</strong><br/><i>".nl2br($locale['444a'])."</i>";
echo "</div>\n<div class='col-xs-12 col-sm-9'>\n";
$domain_server = str_replace('|', PHP_EOL, $settings['domain_server']);
echo form_textarea('domain_server', $locale['444b'], $domain_server, ['autosize' => TRUE, 'placeholder' => "example1.com\nexample2.com\n"]);
echo "</div>\n</div>\n";
echo fusion_get_function('closeside', '');

echo fusion_get_function('openside', '');
echo "<div class='alert alert-info'>".$locale['424']."</div>";
echo form_textarea('exclude_left', $locale['420'], $settings['exclude_left'], ['autosize' => TRUE]);
echo form_textarea('exclude_upper', $locale['421'], $settings['exclude_upper'], ['autosize' => TRUE]);
echo form_textarea('exclude_aupper', $locale['435'], $settings['exclude_aupper'], ['autosize' => TRUE]);
echo form_textarea('exclude_lower', $locale['422'], $settings['exclude_lower'], ['autosize' => TRUE]);
echo form_textarea('exclude_blower', $locale['436'], $settings['exclude_blower'], ['autosize' => TRUE]);
echo form_textarea('exclude_right', $locale['423'], $settings['exclude_right'], ['autosize' => TRUE]);
echo form_textarea('exclude_user1', $locale['443a'], $settings['exclude_user1'], ['autosize' => TRUE]);
echo form_textarea('exclude_user2', $locale['443b'], $settings['exclude_user2'], ['autosize' => TRUE]);
echo form_textarea('exclude_user3', $locale['443c'], $settings['exclude_user3'], ['autosize' => TRUE]);
echo form_textarea('exclude_user4', $locale['443d'], $settings['exclude_user4'], ['autosize' => TRUE]);
echo fusion_get_function('closeside', '');
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-primary']);
echo closeform();

echo fusion_get_function('closetable');
$minified = '$("#site_protocol").change(function(){$("#display_protocol").text($(this).val())}),$("#site_host").keyup(function(){$("#display_host").text($(this).val())}),$("#site_path").keyup(function(){$("#display_path").text($(this).val())}),$("#site_port").keyup(function(){if(":"==(t=":"+$(this).val())||":0"==t||":90"==t||":443"==t)var t="";$("#display_port").text(t)});';
add_to_jquery($minified);

/*
add_to_jquery("
    $('#site_protocol').change(function() {
        $('#display_protocol').text($(this).val());
    });
    $('#site_host').keyup(function() {
        $('#display_host').text($(this).val());
    });
    $('#site_path').keyup(function() {
        $('#display_path').text($(this).val());
    });
    $('#site_port').keyup(function() {
        var value_port = ':'+ $(this).val();
        if (value_port == ':' || value_port == ':0' || value_port == ':90' || value_port == ':443') {
            var value_port = '';
        }
        $('#display_port').text(value_port);
    });
");*/

require_once THEMES.'templates/footer.php';
