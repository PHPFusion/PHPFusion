<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Polaris/widget.php
| Author: Meangczac (Chan), Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__ . '/theme.php';

$settings = get_theme_settings('Polaris');

$locale = fusion_get_locale('', POLARIS_LOCALE);

if (check_post('save_settings')) {

    $settings = [
        'color_scheme' => sanitizer('color_scheme', '', 'color_scheme'),
        'github_url'   => sanitizer('github_url', '', 'github_url'),
        'facebook_url' => sanitizer('facebook_url', '', 'facebook_url'),
        'twitter_url'  => sanitizer('twitter_url', '', 'twitter_url')
    ];

    if (fusion_safe()) {
        foreach ($settings as $settings_name => $settings_value) {
            $db = [
                'settings_name'  => $settings_name,
                'settings_value' => $settings_value,
                'settings_theme' => 'Polaris'
            ];

            dbquery_insert(DB_SETTINGS_THEME, $db, 'update');
        }

        addnotice('success', $locale['POLARIS_300']);
        redirect(FUSION_REQUEST);
    }
}

echo openform('polarisfrm', 'POST');
openside('');
echo form_select('color_scheme', $locale['POLARIS_200'], $settings['color_scheme'], ['inline' => TRUE, 'options' => ['dark' => $locale['POLARIS_202'], 'light' => $locale['POLARIS_201'], 'auto'=> $locale['POLARIS_203']]]);
echo form_text('github_url', $locale['POLARIS_204'], $settings['github_url'], ['inline' => TRUE]);
echo form_text('facebook_url', $locale['POLARIS_205'], $settings['facebook_url'], ['inline' => TRUE]);
echo form_text('twitter_url', $locale['POLARIS_206'], $settings['twitter_url'], ['inline' => TRUE]);
closeside();

echo form_button('save_settings', $locale['save_changes'], 'save', ['class' => 'btn-primary']);
echo closeform();
