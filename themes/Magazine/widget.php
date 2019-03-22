<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Magazine/widget.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
$settings = get_theme_settings('Magazine');
$locale = fusion_get_locale('', MG_LOCALE);

if (isset($_POST['save_settings'])) {
    $settings = [
        'github_url'   => form_sanitizer($_POST['github_url'], '', 'github_url'),
        'facebook_url' => form_sanitizer($_POST['facebook_url'], '', 'facebook_url'),
        'twitter_url'  => form_sanitizer($_POST['twitter_url'], '', 'twitter_url')
    ];

    if (\defender::safe()) {
        foreach ($settings as $settings_name => $settings_value) {
            $db = [
                'settings_name'  => $settings_name,
                'settings_value' => $settings_value,
                'settings_theme' => 'Magazine'
            ];

            dbquery_insert(DB_SETTINGS_THEME, $db, 'update');
        }

        addNotice('success', $locale['MG_201']);
        redirect(FUSION_REQUEST);
    }
}

echo openform('main_settings', 'post', FUSION_REQUEST);
openside('');
echo form_text('github_url', $locale['MG_202'], $settings['github_url'], ['type' => 'url', 'inline' => TRUE]);
echo form_text('facebook_url', $locale['MG_203'], $settings['facebook_url'], ['type' => 'url', 'inline' => TRUE]);
echo form_text('twitter_url', $locale['MG_204'], $settings['twitter_url'], ['type' => 'url', 'inline' => TRUE]);
closeside();

echo form_button('save_settings', $locale['save_changes'], 'save', ['class' => 'btn-primary']);
echo closeform();
