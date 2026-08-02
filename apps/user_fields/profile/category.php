<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'profile/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

return [
    'key'         => 'profile',
    'label'       => $locale['pro_100'],
    'description' => $locale['pro_101'],
    'icon'        => 'user-round',
    'order'       => 10,
    'policy'      => 'open',
    'group_label' => $locale['pro_102'],
];
