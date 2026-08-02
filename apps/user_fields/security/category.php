<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'security/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

return [
    'key'         => 'security',
    'label'       => $locale['sec_100'],
    'description' => $locale['sec_101'],
    'icon'        => 'shield-check',
    'order'       => 25,
    'policy'      => 'trusted',
    'group_label' => $locale['sec_102'],
];
