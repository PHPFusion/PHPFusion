<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_main.php
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

use PHPFusion\QuantumFields;

defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');

$settings = fusion_get_settings();

$contents = [
    'post'        => 'pf_post',
    'view'        => 'pf_view',
    'button'      => 'pf_button',
    'js'          => 'pf_js',
    'link'        => ($admin_link ?? ''),
    'settings'    => TRUE,
    'title'       => $locale['main_settings'],
    'description' => $locale['main_description'],
    'actions'     => ['post' => ['savesettings'=>'settingsFrm']],
];

/*
 * Post Non-js
 */
function pf_post() {

    $locale = fusion_get_locale();

    $inputData = [
        'siteintro'          => form_sanitizer(addslashes($_POST['siteintro']), '', 'siteintro'),
        'sitename'           => form_sanitizer($_POST['sitename'], '', 'sitename'),
        'sitebanner'         => form_sanitizer($_POST['sitebanner'], '', 'sitebanner'),
        'siteemail'          => form_sanitizer($_POST['siteemail'], '', 'siteemail'),
        'siteusername'       => form_sanitizer($_POST['siteusername'], '', 'siteusername'),
        'footer'             => form_sanitizer(addslashes($_POST['footer']), '', 'footer'),
        'site_protocol'      => form_sanitizer($_POST['site_protocol'], '', 'site_protocol'),
        'site_host'          => form_sanitizer($_POST['site_host'], '', 'site_host'),
        'site_path'          => form_sanitizer($_POST['site_path'], '', 'site_path'),
        'site_port'          => form_sanitizer($_POST['site_port'], '', 'site_port'),
        'description'        => form_sanitizer($_POST['description'], '', 'description'),
        'keywords'           => form_sanitizer($_POST['keywords'], '', 'keywords'),
        'opening_page'       => form_sanitizer($_POST['opening_page'], '', 'opening_page'),
        'default_search'     => form_sanitizer($_POST['default_search'], '', 'default_search'),
        'exclude_left'       => form_sanitizer($_POST['exclude_left'], '', 'exclude_left'),
        'exclude_upper'      => form_sanitizer($_POST['exclude_upper'], '', 'exclude_upper'),
        'exclude_aupper'     => form_sanitizer($_POST['exclude_aupper'], '', 'exclude_aupper'),
        'exclude_lower'      => form_sanitizer($_POST['exclude_lower'], '', 'exclude_lower'),
        'exclude_blower'     => form_sanitizer($_POST['exclude_blower'], '', 'exclude_blower'),
        'exclude_right'      => form_sanitizer($_POST['exclude_right'], '', 'exclude_right'),
        'exclude_user1'      => form_sanitizer($_POST['exclude_user1'], '', 'exclude_user1'),
        'exclude_user2'      => form_sanitizer($_POST['exclude_user2'], '', 'exclude_user2'),
        'exclude_user3'      => sanitizer('exclude_user3', '', 'exclude_user3'),
        'exclude_user4'      => sanitizer('exclude_user4', '', 'exclude_user4'),
        'logoposition_xs'    => sanitizer('logoposition_xs', '', 'logoposition_xs'),
        'logoposition_sm'    => sanitizer('logoposition_sm', '', 'logoposition_sm'),
        'logoposition_md'    => sanitizer('logoposition_md', '', 'logoposition_md'),
        'logoposition_lg'    => sanitizer('logoposition_lg', '', 'logoposition_lg'),
        'domain_server'      => sanitizer('domain_server', '', 'domain_server'),
        'privacy_policy'     => form_sanitizer($_POST['privacy_policy'], '', 'privacy_policy', count(fusion_get_enabled_languages()) > 1),
        'license_agreement'  => form_sanitizer($_POST['license_agreement'], '', 'license_agreement', count(fusion_get_enabled_languages()) > 1),
        'license_lastupdate' => ($_POST['license_agreement'] != fusion_get_settings('license_agreement') ? time() : fusion_get_settings('license_lastupdate'))
    ];

    $inputData += getServerConfig($inputData);

    if (fusion_safe()) {

        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ]);
        }
        add_notice('success', $locale['900']);
        redirect(FUSION_REQUEST);
    }

}

/**
 * @param array $inputData
 *
 * @return array
 */
function getServerConfig(array $inputData)
: array {

    if ( strpos($inputData['site_host'], "/") !== FALSE ) {
        $inputData['site_host'] = explode("/", $inputData['site_host'], 2);
        if ( $inputData['site_host'][1] != "" ) {
            $_POST['site_path'] = "/" . $inputData['site_host'][1];
        }
        $inputData['site_host'] = $inputData['site_host'][0];
    }

    $inputData['siteurl'] = $inputData['site_protocol'] . "://" . $inputData['site_host'] . ( $inputData['site_port'] ? ":" . $inputData['site_port'] : "" ) . $inputData['site_path'];

    if ( ! empty($inputData['domain_server']) ) {
        $inputData['domain_server'] = str_replace(PHP_EOL, '|', $inputData['domain_server']);
    }

    return $inputData;
}

/*
 * View
 */
function pf_view() {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    echo openform('settingsFrm', 'POST');
    echo '<h6>Site Information</h6>';
    openside('Title & description<small>The details used to identify your publication around the web</small>', TRUE);
    echo form_text('sitename', $locale['402'], $settings['sitename'], [
            'required'   => TRUE,
            'inline'     => FALSE,
            'max_length' => 255,
            'error_text' => $locale['error_value']
        ]).
        form_text('keywords', $locale['410'], $settings['keywords'], [
            'autosize' => TRUE,
            'ext_tip'  => $locale['411']
        ]).
        form_textarea('description', $locale['409'], $settings['description'], [
            'autosize' => TRUE
        ]).
        form_text('opening_page', $locale['413'], $settings['opening_page'], [
            'required'   => TRUE,
            'max_length' => 100,
            'error_text' => $locale['error_value']
        ]);
    closeside();


    openside('Site credentials<small>Set the server information</small>', TRUE);
    echo form_text('siteusername', $locale['406'], $settings['siteusername'], [
            'required'   => TRUE,
            'inline'     => FALSE,
            'max_length' => 32,
            'error_text' => $locale['error_value']
        ]).
        form_text('siteemail', $locale['405'], $settings['siteemail'], [
            'inline'     => FALSE,
            'required'   => TRUE,
            'max_length' => 128,
            'type'       => 'email'
        ]);
    closeside();

    echo '<h6>Site Settings</h6>';
    openside('Publication Settings<small>Set the opening description</small>', TRUE);
    echo form_textarea('siteintro', $locale['407'], stripslashes($settings['siteintro']), [
            'autosize' => TRUE,
			'bbcode'=>true,
        ]).
        form_textarea('footer', $locale['412'], stripslashes($settings['footer']), [
            'autosize' => TRUE,
        ]);
    closeside();

    openside('Logo Settings<small>Set the site logo configurations</small>', TRUE);
    echo form_text('sitebanner', $locale['404'], $settings['sitebanner'], [
                'inline'     => FALSE,
                'required'   => TRUE,
                'error_text' => $locale['error_value']
            ]
        ).
        form_select('logoposition_xs', $locale['404XS'], $settings['logoposition_xs'], [
            'inline'  => FALSE,
            'options' => [
                'logo-xs-left'   => $locale['404left'],
                'logo-xs-center' => $locale['404center'],
                'logo-xs-right'  => $locale['404right']
            ]
        ]).
        form_select('logoposition_sm', $locale['404SM'], $settings['logoposition_sm'], [
            'inline'  => FALSE,
            'options' => [
                'logo-sm-left'   => $locale['404left'],
                'logo-sm-center' => $locale['404center'],
                'logo-sm-right'  => $locale['404right']
            ]
        ]).
        form_select('logoposition_md', $locale['404MD'], $settings['logoposition_md'], [
            'inline'  => FALSE,
            'options' => [
                'logo-md-left'   => $locale['404left'],
                'logo-md-center' => $locale['404center'],
                'logo-md-right'  => $locale['404right']
            ]
        ]).
        form_select('logoposition_lg', $locale['404LG'], $settings['logoposition_lg'], [
            'inline'  => FALSE,
            'options' => [
                'logo-lg-left'   => $locale['404left'],
                'logo-lg-center' => $locale['404center'],
                'logo-lg-right'  => $locale['404right']
            ]
        ]);
    closeside();

    openside($locale['419'].'<small>Set the default search configurations</small>', TRUE);
    echo form_select('default_search', '', $settings['default_search'], [
        'options'        => get_default_search_opts(),
        'callback_check' => 'validate_default_search'
    ]);
    closeside();

    openside($locale['401a'].'<small>'.$locale['401b'].'</small>', TRUE);
    echo "<div class='spacer-xs'>\n";
    echo "<i class='fa fa-external-link m-r-10'></i>";
    echo "<span id='display_protocol'>".$settings['site_protocol']."</span>://";
    echo "<span id='display_host'>".$settings['site_host']."</span>";
    echo "<span id='display_port'>".($settings['site_port'] ? ":".$settings['site_port'] : "")."</span>";
    echo "<span id='display_path'>".$settings['site_path']."</span>";
    echo "</div>";
    echo '<div class="flex-row">';
    echo form_select('site_protocol', $locale['426'], $settings['site_protocol'], [
        'inline'     => FALSE,
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
        'inline'     => FALSE,
        'max_length' => 255,
        'error_text' => $locale['error_value']
    ]);
    echo form_text('site_path', $locale['429'], $settings['site_path'], [
        'required'   => TRUE,
        'inline'     => FALSE,
        'regex'      => '\/([a-z0-9-_]+\/)*?',
        'max_length' => 255
    ]);
    echo form_text('site_port', $locale['430'], $settings['site_port'], [
        'inline'         => FALSE,
        'required'       => FALSE,
        'placeholder'    => 80,
        'max_length'     => 5,
        'type'           => 'number',
        'inner_width'    => '150px',
        'error_text'     => $locale['430_error'],
        'callback_check' => 'validate_site_port',
        'ext_tip'        => $locale['430_desc']
    ]);
    echo '</div>';
    closeside();
    // Domain names
    openside($locale['444'].'<small>'.nl2br($locale['444a']).'</small>', TRUE);
    $domain_server = str_replace('|', PHP_EOL, $settings['domain_server']);
    echo form_textarea('domain_server', $locale['444b'], $domain_server, [
        'inline'   => FALSE,
        'autosize' => TRUE, 'placeholder' => "example1.com\nexample2.com\n"]);
    closeside();
    openside('Panel Restrictions<small>Set panel display restrictions and exclusions</small>', TRUE);
    echo '<div class="spacer-sm">'.$locale['424'].'</div>';
    echo form_textarea('exclude_left', $locale['420'], $settings['exclude_left'], [
        'autosize' => TRUE
    ]);
    echo form_textarea('exclude_upper', $locale['421'], $settings['exclude_upper'], ['autosize' => TRUE]);
    echo form_textarea('exclude_aupper', $locale['435'], $settings['exclude_aupper'], ['autosize' => TRUE]);
    echo form_textarea('exclude_lower', $locale['422'], $settings['exclude_lower'], ['autosize' => TRUE]);
    echo form_textarea('exclude_blower', $locale['436'], $settings['exclude_blower'], ['autosize' => TRUE]);
    echo form_textarea('exclude_right', $locale['423'], $settings['exclude_right'], ['autosize' => TRUE]);
    echo form_textarea('exclude_user1', $locale['443a'], $settings['exclude_user1'], ['autosize' => TRUE]);
    echo form_textarea('exclude_user2', $locale['443b'], $settings['exclude_user2'], ['autosize' => TRUE]);
    echo form_textarea('exclude_user3', $locale['443c'], $settings['exclude_user3'], ['autosize' => TRUE]);
    echo form_textarea('exclude_user4', $locale['443d'], $settings['exclude_user4'], ['autosize' => TRUE]);
    closeside();

    echo '<h6>Policy</h6>';
    openside('Privacy Policy Settings<small>Site policy license</small>', TRUE);
    if (count(fusion_get_enabled_languages()) > 1) {
        echo QuantumFields::form_multilocale('privacy_policy', $locale['820'], $settings['privacy_policy'], [
            'autosize'  => 1,
            'form_name' => 'settingsform',
            'function'  => 'form_textarea'
        ]);
    } else {
        echo form_textarea('privacy_policy', $locale['820'], $settings['privacy_policy'], [
            'autosize'  => 1,
            'form_name' => 'settingsform',
        ]);
    }
    closeside();
    openside('Membership Terms<small>Terms of Use and license agreement for new user registration</small>', TRUE);
    if (count(fusion_get_enabled_languages()) > 1) {
        echo QuantumFields::form_multilocale('license_agreement', $locale['559'], $settings['license_agreement'], [
            'form_name' => 'settingsform',
            'input_id'  => 'enable_license_agreement',
            'autosize'  => TRUE,
            'type'      => $settings['tinymce_enabled'] ? 'tinymce' : 'html',
            'function'  => 'form_textarea'
        ]);
    } else {
        echo form_textarea('license_agreement', $locale['559'], $settings['license_agreement'], [
            'form_name' => 'settingsform',
            //'type'      => $settings['tinymce_enabled'] ? 'tinymce' : 'html',
        ]);
    }
    closeside();

    echo '<noscript>';
    echo '<div class="spacer-sm">';
    echo pf_button();
    echo '</div>';
    echo '</noscript>';

    echo closeform();
}

/*
 * Button
 */
function pf_button() {
    $locale = fusion_get_locale();
    return form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-primary']);
}

/*
 * Js
 */
function pf_js() {
    return '
    $("#site_protocol").change(function(){$("#display_protocol").text($(this).val())}),
    $("#site_host").keyup(function(){$("#display_host").text($(this).val())}),
    $("#site_path").keyup(function(){$("#display_path").text($(this).val())}),
    $("#site_port").keyup(function(){if(":"==(t=":"+$(this).val())||":0"==t||":90"==t||":443"==t)var t="";$("#display_port").text(t)});
    ';
}

/**
 * Get the default search options
 * with file exists validation of the PHPFusion Search SDK files.
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

    return (in_array($value, array_keys($search_opts)));
}

/**
 * Site Port validation rules
 *
 * @param $value
 *
 * @return bool
 */
function validate_site_port($value) {
    return ((isnum($value) || empty($value)) && in_array($value, [0, 80, 443]) or $value < 65001);
}

/*
 *
    $("#site_protocol").change(function() {$("#display_protocol").text($(this).val());});
    $("#site_host").keyup(function() {$("#display_host").text($(this).val());});
    $("#site_path").keyup(function() {$("#display_path").text($(this).val());});
    $("#site_port").keyup(function() {
        var value_port = ":"+ $(this).val();
        if (value_port == ":" || value_port == ":0" || value_port == ":90" || value_port == ":443") {
            var value_port = "";
        }
        $("#display_port").text(value_port);
    });
 */
