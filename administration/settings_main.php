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
require_once "../maincore.php";
pageAccess('S1');
require_once THEMES."templates/admin_header.php";

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link'  => ADMIN.'settings_main.php'.fusion_get_aidlink(),
                                                      'title' => $locale['main_settings']]);
/**
 * Get the default search options
 * with file exists validation of the PHP-Fusion Search SDK files.
 * @return array
 */
function get_default_search_opts() {
    global $locale;
    static $search_opts = array();
    static $filename_locale = array();
    if (empty($search_opts)) {
        $search_opts += array(
            'all' => $locale['419a'],
        );
        // Place converter to translate the names of the SDK files
        include LOCALE.LOCALESET."search/converter.php";
        $search_dir = INCLUDES."search/";
        $dir = LOCALE.LOCALESET."search/";
        $search_files = makefilelist($search_dir, TRUE, '.|..|.DS_Store|index.php');
        $search_files = array_flip($search_files);
        $search_locale_files = makefilelist($dir, TRUE, '.|..|.DS_Store|index.php');
        if (!empty($search_locale_files)) {
            foreach ($search_locale_files as $file_to_check) {
                $search_api_file = 'search_'.str_replace('.php', '_include.php', $file_to_check);
                $search_btn_file = 'search_'.str_replace('.php', '_include_button.php', $file_to_check);
                if (isset($search_files[$search_api_file]) && isset($search_files[$search_btn_file]) && isset($filename_locale[$file_to_check])) {
                    $search_opts[$file_to_check] = ucwords($filename_locale[$file_to_check]);
                }
            }
        }
    }
    return (array)$search_opts;
}

/**
 * Default Search file validation rules
 * @param $value
 * @return bool
 */
function validate_default_search($value) {
    $search_opts = get_default_search_opts();

    return (in_array($value, array_keys($search_opts)) ? TRUE : FALSE);
}

/**
 * Site Port validation rules
 * @param $value
 * @return bool
 */
function validate_site_port($value) {
    return ((isnum($value) || empty($value)) && in_array($value, array(0, 80, 443)) or $value < 65001) ? TRUE : FALSE;
}

// These are the default settings and the only settings we expect to be posted
$settings_main = array(
    'siteintro'       => fusion_get_settings('siteintro'),
    'sitename'        => fusion_get_settings('sitename'),
    'sitebanner'      => fusion_get_settings('sitebanner'),
    'siteemail'       => fusion_get_settings('siteemail'),
    'siteusername'    => fusion_get_settings('siteusername'),
    'footer'          => fusion_get_settings('footer'),
    'site_protocol'   => fusion_get_settings('site_protocol'),
    'site_host'       => fusion_get_settings('site_host'),
    'site_path'       => fusion_get_settings('site_path'),
    'site_port'       => fusion_get_settings('site_port'),
    'description'     => fusion_get_settings('description'),
    'keywords'        => fusion_get_settings('keywords'),
    'opening_page'    => fusion_get_settings('opening_page'),
    'default_search'  => fusion_get_settings('default_search'),
    'exclude_left'    => fusion_get_settings('exclude_left'),
    'exclude_upper'   => fusion_get_settings('exclude_upper'),
    'exclude_aupper'  => fusion_get_settings('exclude_aupper'),
    'exclude_lower'   => fusion_get_settings('exclude_lower'),
    'exclude_blower'  => fusion_get_settings('exclude_blower'),
    'exclude_right'   => fusion_get_settings('exclude_right'),
    'exclude_user1'   => fusion_get_settings('exclude_user1'),
    'exclude_user2'   => fusion_get_settings('exclude_user2'),
    'exclude_user3'   => fusion_get_settings('exclude_user3'),
    'exclude_user4'   => fusion_get_settings('exclude_user4'),
    'logoposition_xs' => fusion_get_settings('logoposition_xs'),
    'logoposition_sm' => fusion_get_settings('logoposition_sm'),
    'logoposition_md' => fusion_get_settings('logoposition_md'),
    'logoposition_lg' => fusion_get_settings('logoposition_lg')
);

// Saving settings
if (isset($_POST['savesettings'])) {

    $settings_main = [
        'siteintro'       => descript(addslashes(addslashes($_POST['siteintro']))),
        'sitename'        => form_sanitizer($_POST['sitename'], '', 'sitename'),
        'sitebanner'      => form_sanitizer($_POST['sitebanner'], '', 'sitebanner'),
        'siteemail'       => form_sanitizer($_POST['siteemail'], '', 'siteemail'),
        'siteusername'    => form_sanitizer($_POST['siteusername'], '', 'siteusername'),
        'footer'          => descript(addslashes(addslashes($_POST['footer']))),
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
        'logoposition_lg' => form_sanitizer($_POST['logoposition_lg'], '', 'logoposition_lg')
    ];

    if (strpos($settings_main['site_host'], "/") !== FALSE) {
        $settings_main['site_host'] = explode("/", $settings_main['site_host'], 2);
        if ($settings_main['site_host'][1] != "") {
            $_POST['site_path'] = "/".$settings_main['site_host'][1];
        }
        $settings_main['site_host'] = $settings_main['site_host'][0];
    }

    $settings_main['siteurl'] = $settings_main['site_protocol']."://".$settings_main['site_host'].($settings_main['site_port'] ? ":".$settings_main['site_port'] : "").$settings_main['site_path'];

    if (\defender::safe()) {
        foreach ($settings_main as $settings_key => $settings_value) {
            $data = [
                'settings_name'  => $settings_key,
                'settings_value' => $settings_value
            ];
            dbquery_insert(DB_SETTINGS, $data, 'update', array('primary_key' => 'settings_name'));
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
echo form_text('sitename', $locale['402'], $settings_main['sitename'], [
               'inline'     => FALSE,
               'max_length' => 255,
               'required'   => TRUE,
               'error_text' => $locale['error_value']
               ]);

echo form_text('siteemail', $locale['405'], $settings_main['siteemail'], [
               'inline'     => FALSE,
               'required'   => TRUE,
               'max_length' => 128,
               'type'       => 'email'
               ]);

echo form_text('siteusername', $locale['406'], $settings_main['siteusername'], [
               'required'   => TRUE,
               'inline'     => FALSE,
               'max_length' => 32,
               'error_text' => $locale['error_value']
               ]);

echo form_text('opening_page', $locale['413'], $settings_main['opening_page'], [
               'required'   => TRUE,
               'max_length' => 100,
               'error_text' => $locale['error_value']
               ]);

echo form_textarea('siteintro', $locale['407'], stripslashes($settings_main['siteintro']), [
                   'type'     =>'tinymce',
                   'tinymce'  =>'simple',
                   'autosize' => TRUE]);

echo form_textarea('footer', $locale['412'], stripslashes($settings_main['footer']), [
                   'autosize' => TRUE,
                   'type'     =>'tinymce',
                   'tinymce'  =>'simple'
                   ]);

echo fusion_get_function('closeside', '');

echo fusion_get_function('openside', '');
echo form_text('sitebanner', $locale['404'], $settings_main['sitebanner'], [
               'inline'     => TRUE,
               'required'   => TRUE,
               'error_text' => $locale['error_value']]);

$options_xs = [
    'logo-xs-left'   => $locale['404left'],
    'logo-xs-center' => $locale['404center'],
    'logo-xs-right'  => $locale['404right']
];

echo form_select('logoposition_xs', $locale['404XS'], $settings_main['logoposition_xs'], [
                 'inline'  => TRUE,
                 'options' => $options_xs
                 ]);

$options_sm = [
    'logo-sm-left'   => $locale['404left'],
    'logo-sm-center' => $locale['404center'],
    'logo-sm-right'  => $locale['404right']
];

echo form_select('logoposition_sm', $locale['404SM'], $settings_main['logoposition_sm'], [
                 'inline'  => TRUE,
                 'options' => $options_sm
                 ]);

$options_md = [
    'logo-md-left'   => $locale['404left'],
    'logo-md-center' => $locale['404center'],
    'logo-md-right'  => $locale['404right']
];

echo form_select('logoposition_md', $locale['404MD'], $settings_main['logoposition_md'], [
                  'inline'  => TRUE,
                  'options' => $options_md
                  ]);

$options_lg = [
    'logo-lg-left'   => $locale['404left'],
    'logo-lg-center' => $locale['404center'],
    'logo-lg-right'  => $locale['404right']
];

echo form_select('logoposition_lg', $locale['404LG'], $settings_main['logoposition_lg'], [
                 'inline'  => TRUE,
                 'options' => $options_lg
                 ]);

echo fusion_get_function('closeside', '');

echo fusion_get_function('openside', '');
echo form_textarea('description', $locale['409'], $settings_main['description'], ['autosize' => TRUE]);
echo form_textarea('keywords', $locale['410'], $settings_main['keywords'], ['autosize' => TRUE, 'ext_tip' => $locale['411']]);
echo form_select('default_search', $locale['419'], $settings_main['default_search'], [
                 'options'        => get_default_search_opts(),
                 'callback_check' => 'validate_default_search'
                 ]);

echo fusion_get_function('closeside', '');
echo "</div><div class='col-xs-12 col-sm-12 col-md-6'>\n";

echo fusion_get_function('openside', '');
echo "<div class='alert alert-success'>\n";
echo "<i class='fa fa-external-link m-r-10'></i>";
echo "<span id='display_protocol'>".$settings_main['site_protocol']."</span>://";
echo "<span id='display_host'>".$settings_main['site_host']."</span>";
echo "<span id='display_port'>".($settings_main['site_port'] ? ":".$settings_main['site_port'] : "")."</span>";
echo "<span id='display_path'>".$settings_main['site_path']."</span>";
echo "</div>\n";

$opts = ['http' => 'http://', 'https' => 'https://'];
$opts['invalid_protocol'] = 'Invalid (test purposes)';

echo form_select('site_protocol', $locale['426'], $settings_main['site_protocol'], [
                 'inline'     => TRUE,
                 'regex'      => 'http(s)?',
                 'error_text' => $locale['error_value'],
                 'options'    => $opts
                 ]);

echo form_text('site_host', $locale['427'], $settings_main['site_host'], [
               'required'   => TRUE,
               'inline'     => TRUE,
               'max_length' => 255,
               'error_text' => $locale['error_value']
               ]);

echo form_text('site_path', $locale['429'], $settings_main['site_path'], [
               'required'   => TRUE,
               'inline'     => TRUE,
               'regex'      => '\/([a-z0-9-_]+\/)*?',
               'max_length' => 255
               ]);

echo form_text('site_port', $locale['430'], $settings_main['site_port'], [
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

echo fusion_get_function('closeside', '');

echo fusion_get_function('openside', '');
echo "<div class='alert alert-info'>".$locale['424']."</div>";
echo form_textarea('exclude_left', $locale['420'], $settings_main['exclude_left'], ['autosize' => TRUE]);
echo form_textarea('exclude_upper', $locale['421'], $settings_main['exclude_upper'], ['autosize' => TRUE]);
echo form_textarea('exclude_aupper', $locale['435'], $settings_main['exclude_aupper'], ['autosize' => TRUE]);
echo form_textarea('exclude_lower', $locale['422'], $settings_main['exclude_lower'], ['autosize' => TRUE]);
echo form_textarea('exclude_blower', $locale['436'], $settings_main['exclude_blower'], ['autosize' => TRUE]);
echo form_textarea('exclude_right', $locale['423'], $settings_main['exclude_right'], ['autosize' => TRUE]);
echo form_textarea('exclude_user1', $locale['443a'], $settings_main['exclude_user1'], ['autosize' => TRUE]);
echo form_textarea('exclude_user2', $locale['443b'], $settings_main['exclude_user2'], ['autosize' => TRUE]);
echo form_textarea('exclude_user3', $locale['443c'], $settings_main['exclude_user3'], ['autosize' => TRUE]);
echo form_textarea('exclude_user4', $locale['443d'], $settings_main['exclude_user4'], ['autosize' => TRUE]);
echo fusion_get_function('closeside', '');
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-primary']);
echo closeform();

echo fusion_get_function('closetable');

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
");

require_once THEMES."templates/footer.php";
