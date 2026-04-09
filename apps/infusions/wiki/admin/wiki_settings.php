<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: wiki_settings.php
| Author: RobiNN
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

$locale = fusion_get_locale();
$wiki_settings = get_settings('wiki');

if (isset($_POST['savesettings'])) {
    $settings = [
        'wiki_allow_submission' => form_sanitizer($_POST['wiki_allow_submission'], 0, 'wiki_allow_submission')
    ];

    if (\defender::safe()) {
        foreach ($settings as $settings_name => $settings_value) {
            $inputSettings = [
                'settings_name'  => $settings_name,
                'settings_value' => $settings_value,
                'settings_inf'   => 'wiki'
            ];

            dbquery_insert(DB_SETTINGS_INF, $inputSettings, 'update', ['primary_key' => 'settings_name']);
        }

        add_notice('success', $locale['wiki_218']);
        redirect(FUSION_REQUEST);
    }
}

echo openform('settingsform', 'post', FUSION_REQUEST);

echo form_select('wiki_allow_submission', $locale['wiki_050'], $wiki_settings['wiki_allow_submission'], [
    'inline'  => TRUE,
    'options' => [$locale['disable'], $locale['enable']]
]);

echo form_button('savesettings', $locale['save'], $locale['save'], ['class' => 'btn-success', 'icon' => 'fa fa-hdd-o']);
echo closeform();
