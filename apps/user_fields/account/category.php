<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'account/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

return [
    'key'         => 'account',
    'label'       => $locale['acc_100'],
    'description' => $locale['acc_101'],
    'icon'        => 'settings',
    'order'       => 20,
    'policy'      => 'trusted',
    'group_label' => $locale['acc_102'],
];
