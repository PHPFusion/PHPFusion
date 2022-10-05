<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_main.php
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
pageaccess('S1');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');
$settings = fusion_get_settings();

add_breadcrumb(['link' => ADMIN.'settings_main.php'.fusion_get_aidlink(), 'title' => $locale['admins_main_settings']]);

// Saving settings
if (check_post('savesettings')) {
    $inputData = [
        'siteintro'      => sanitizer('siteintro', '', 'siteintro'),
        'sitename'       => sanitizer('sitename', '', 'sitename'),
        'sitebanner'     => sanitizer('sitebanner', '', 'sitebanner'),
        'siteemail'      => sanitizer('siteemail', '', 'siteemail'),
        'siteusername'   => sanitizer('siteusername', '', 'siteusername'),
        'footer'         => sanitizer('footer', '', 'footer'),
        'site_protocol'  => sanitizer('site_protocol', '', 'site_protocol'),
        'site_host'      => sanitizer('site_host', '', 'site_host'),
        'site_path'      => sanitizer('site_path', '', 'site_path'),
        'site_port'      => sanitizer('site_port', '', 'site_port'),
        'description'    => sanitizer('description', '', 'description'),
        'keywords'       => sanitizer('keywords', '', 'keywords'),
        'opening_page'   => sanitizer('opening_page', '', 'opening_page'),
        'default_search' => sanitizer('default_search', '', 'default_search'),
        'domain_server'  => sanitizer('domain_server', '', 'domain_server'),
        'license'        => sanitizer('license', '', 'license')
    ];

    if (strpos($inputData['site_host'], "/") !== FALSE) {
        $inputData['site_host'] = explode("/", $inputData['site_host'], 2);
        if ($inputData['site_host'][1] != "") {
            $inputData['site_path'] = "/".$inputData['site_host'][1];
        }
        $inputData['site_host'] = $inputData['site_host'][0];
    }

    $inputData['siteurl'] = $inputData['site_protocol']."://".$inputData['site_host'].($inputData['site_port'] ? ":".$inputData['site_port'] : "").$inputData['site_path'];

    if (!empty($inputData['domain_server'])) {
        $inputData['domain_server'] = str_replace(PHP_EOL, '|', $inputData['domain_server']);
    }

    if ($inputData['license'] == 'crl' || $inputData['license'] == 'ccl') {
        $inputData['license_key'] = sanitizer('license_key', '', 'license_key');
    }

    if (fusion_safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ]);
        }

        addnotice("success", $locale['admins_900']);
        redirect(FUSION_REQUEST);
    }
}

opentable($locale['admins_main_settings']);
echo "<div class='mb-5'><h5>".$locale['admins_main_description']."</h5></div>";

$tabs['title'][] = $locale['admins_446'];
$tabs['id'][] = 'general';
$tabs['icon'][] = '';
$tabs['title'][] = $locale['admins_447'];
$tabs['id'][] = 'url';
$tabs['icon'][] = '';
$tabs['title'][] = 'SEF Settings';
$tabs['id'][] = 'sef';
$tabs['icon'][] = '';

$tab_active = tab_active($tabs, 0);

echo openform('settingsFrm', 'POST');

echo opentab($tabs, $tab_active, 'settings', FALSE, 'nav-pills', '', [], TRUE);

echo opentabbody($tabs['title'][0], 'general', $tab_active);

echo form_text('sitename', $locale['admins_402'], $settings['sitename'], [
    'max_length' => 255,
    'required'   => TRUE,
    'error_text' => $locale['error_value']
]);
echo form_text('sitebanner', $locale['admins_404'], $settings['sitebanner'], [
    'required'   => TRUE,
    'error_text' => $locale['error_value'],
]);
echo form_textarea('description', $locale['admins_409'], $settings['description'], [
    'autosize' => TRUE,
]);
tablebreak();

echo '<div class="row"><div class="col-6">';
echo form_text('siteemail', $locale['admins_405'], $settings['siteemail'], [
    'required'   => TRUE,
    'max_length' => 128,
    'type'       => 'email'
]);
echo '</div><div class="col-6">';
echo form_text('siteusername', $locale['admins_406'], $settings['siteusername'], [
    'required'   => TRUE,
    'max_length' => 32,
    'error_text' => $locale['error_value']
]);
echo '</div></div>';

echo form_textarea('siteintro', $locale['admins_407'], stripslashes($settings['siteintro']), [
    'type'      => 'html',
    'autosize'  => TRUE,
    'form_name' => 'settingsform'
]);

echo form_textarea('footer', $locale['admins_412'], stripslashes($settings['footer']), [
    'autosize'  => TRUE,
    'type'      => 'html',
    'form_name' => 'settingsform'
]);
tablebreak();
echo form_select('license', $locale['admins_613'], $settings['license'], [
    'options'     => [
        'agpl' => 'AGPL',
        'epal' => 'EPAL',
        'crl'  => 'CRL',
        'ccl'  => 'CCL'
    ],
    'width'       => '100%',
    'inner_width' => '100%',
]);
echo '<div class="row" id="licenseCredential" style="display:'.(in_array($settings['license'], ['crl', 'ccl']) ? 'block' : 'none').';"><div class="col-xs-12">';
echo form_text('license_key', 'License Key', $settings['license_key'], [
    'required'     => TRUE,
    'max_length'   => 16,
    'mask'         => 'AAAA-AAAA-AAAA-AAAA',
    'mask_options' => ['onKeyPress' => "function(cep, event, currentField, options) { currentField.val( currentField.val().toUpperCase())}"],
    'placeholder'  => '0000-0000-0000-0000',
    'ext_tip'      => 'License key is required for CRL and CCL licensing options'
]);
echo '</div></div>';
echo closetabbody();

add_to_jquery("
$('#license').on('change', function(e) {
    let lec = $('#licenseCredential'), v = $(this).val();        
    if ( v == 'crl' || v == 'ccl') {     
        lec.slideDown();         
    } else {
        lec.hide();
    }
});
");
echo opentabbody($tabs['title'][1], 'url', $tab_active);
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-4'>\n";
echo "<strong>".$locale['admins_401a']."</strong><br/><small>".$locale['admins_401b']."</small>";
echo "<div class='spacer-xs'>\n";
echo "<i class='fa fa-external-link m-r-10'></i>";
echo "<span id='display_protocol'>".$settings['site_protocol']."</span>://";
echo "<span id='display_host'>".$settings['site_host']."</span>";
echo "<span id='display_port'>".($settings['site_port'] ? ":".$settings['site_port'] : "")."</span>";
echo "<span id='display_path'>".$settings['site_path']."</span>";
echo "</div>\n";
echo "</div>\n<div class='col-xs-12 col-sm-8'>\n";
echo "<div class='display-flex flex-row gap-sm'>";

echo form_select('site_protocol', $locale['admins_426'], $settings['site_protocol'], [
    'inline'      => FALSE,
    'width'       => '100%',
    'inner_width' => '100%',
    'regex'       => 'http(s)?',
    'error_text'  => $locale['error_value'],
    'options'     => [
        'http'             => 'http://',
        'https'            => 'https://',
        'invalid_protocol' => $locale['admins_445']
    ]
]);
echo form_text('site_host', $locale['admins_427'], $settings['site_host'], [
    'class'      => 'w-100',
    'required'   => TRUE,
    'max_length' => 255,
    'error_text' => $locale['error_value']
]);
echo form_text('site_path', $locale['admins_429'], $settings['site_path'], [
    'required'   => TRUE,
    'regex'      => '\/([a-z0-9-_]+\/)*?',
    'max_length' => 255
]);
echo "</div>";

echo form_text('site_port', $locale['admins_430'], $settings['site_port'], [
    'required'       => FALSE,
    'placeholder'    => 80,
    'max_length'     => 5,
    'type'           => 'number',
    'error_text'     => $locale['admins_430_error'],
    'callback_check' => 'validate_site_port',
    'ext_tip'        => $locale['admins_430_desc']
]);

echo "</div>\n</div>\n";

// Domain names
echo "<div class='row'>";
echo "<div class='col-xs-12 col-sm-4'>\n";
echo "<strong>".$locale['admins_444']."</strong><br/><small>".nl2br($locale['admins_444a'])."</small>";
echo "</div>\n<div class='col-xs-12 col-sm-8'>\n";
$domain_server = str_replace('|', PHP_EOL, $settings['domain_server']);
echo form_textarea('domain_server', $locale['admins_444b'], $domain_server, ['autosize' => TRUE, 'placeholder' => "example1.com\nexample2.com\n"]);
echo form_text('opening_page', $locale['admins_413'], $settings['opening_page'], [
    'required'   => TRUE,
    'max_length' => 100,
    'error_text' => $locale['error_value'],
]);
echo '</div></div>';


echo closetabbody();

echo opentabbody($tabs['title'][2], 'sef', $tab_active);
echo form_textarea('keywords', $locale['admins_410'], $settings['keywords'], [
    'autosize' => TRUE,
    'ext_tip'  => $locale['admins_411'],
    'inline'   => TRUE
]);
echo form_select('default_search', $locale['admins_419'], $settings['default_search'], [
    'options'        => get_default_search_opts(),
    'callback_check' => 'validate_default_search',
    'inline'         => TRUE
]);

echo closetabbody();

echo closetab();

echo '<div class="mt-3 m-t-20">';
echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
echo '</div>';

echo closeform();
closetable();

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
require_once THEMES.'templates/footer.php';

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
            'all' => $locale['admins_419a'],
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
                    $inf_files = makefilelist(INFUSIONS.$infusions_to_check.'/search/', ".|..|index.php");

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

    return $search_opts;
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
