<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: banners.php
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
pageaccess('SB');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');
$settings = fusion_get_settings();

add_breadcrumb(['link' => ADMIN.'banners.php'.fusion_get_aidlink(), 'title' => $locale['admins_850']]);

$settings_main = [
    'sitebanner1' => $settings['sitebanner1'],
    'sitebanner2' => $settings['sitebanner2']
];

if (check_post('save_banners') || check_post('preview_banners')) {
    $settings_main = [
        'sitebanner1' => sanitizer('sitebanner1', '', 'sitebanner1'),
        'sitebanner2' => sanitizer('sitebanner2', '', 'sitebanner2')
    ];

    if (check_post('preview_banners') && fusion_safe()) {
        $modal = openmodal('banners_preview', $locale['admins_855']);
        $modal .= fusion_get_function('openside', $locale['admins_851']);
        if (!empty($settings_main['sitebanner1'])) {
            $modal .= parse_text($settings_main['sitebanner1'], ['parse_smileys' => FALSE, 'parse_bbcode' => FALSE]);
        }
        $modal .= fusion_get_function('closeside', '');
        $modal .= fusion_get_function('openside', $locale['admins_852']);
        if (!empty($settings_main['sitebanner2'])) {
            $modal .= parse_text($settings_main['sitebanner1'], ['parse_smileys' => FALSE, 'parse_bbcode' => FALSE]);
        }
        $modal .= fusion_get_function('closeside', '');
        $modal .= closemodal();
        add_to_footer($modal);
    } else {
        if (fusion_safe()) {
            foreach ($settings_main as $settings_key => $settings_value) {
                dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:value WHERE settings_name=:name", [':value' => $settings_value, ':name' => $settings_key]);
                addnotice('success', $locale['admins_900']);
            }

            redirect(FUSION_REQUEST);
        }
    }
}

opentable($locale['admins_850']);
echo openform('banner_form', 'post', FUSION_REQUEST);
echo form_textarea('sitebanner1', $locale['admins_851'], $settings_main['sitebanner1'], [
    'type'      => 'html',
    'form_name' => 'banner_form',
    'inline'    => FALSE
]);
echo form_textarea('sitebanner2', $locale['admins_852'], $settings_main['sitebanner2'], [
    'type'      => 'html',
    'form_name' => 'banner_form',
    'inline'    => FALSE
]);
echo form_button('preview_banners', $locale['admins_855'], $locale['admins_855'], ['class' => 'btn-default m-r-10']);
echo form_button('save_banners', $locale['admins_854'], $locale['admins_854'], ['class' => 'btn-success']);

echo closeform();
closetable();

require_once THEMES.'templates/footer.php';
