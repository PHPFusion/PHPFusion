<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: error.php
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
require_once __DIR__.'/maincore.php';
require_once THEMES.'templates/header.php';

$locale = fusion_get_locale('', LOCALE.LOCALESET.'error.php');

require_once THEMES."templates/global/error.tpl.php";

add_handler(function ($output = '') {
    return (string)preg_replace_callback("/(href|src)=(\'|\")((?!(htt|ft)p(s)?:\/\/)[^\\\\(\'|\")]*)/im", function ($m) {
        return $m[1]."=".$m[2].fusion_get_settings('siteurl').$m[3];
    }, $output);
});

$info = [];

$default = [
    'title'  => $locale['errunk'],
    'status' => '505',
    'back'   => [
        'url'   => BASEDIR.'index.php',
        'title' => $locale['errret']
    ]
];

if (isset($_GET['code'])) {
    switch ($_GET['code']) {
        case 401:
            header("HTTP/1.1 401 Unauthorized");
            $info = [
                'title'  => $locale['err401'],
                'status' => 401
            ];
            break;
        case 403:
            header("HTTP/1.1 403 Forbidden");
            $info = [
                'title'  => $locale['err403'],
                'status' => 403,
            ];
            break;
        case 404:
            header("HTTP/1.1 404 Not Found");
            $info = [
                'title'  => $locale['err404'],
                'status' => 404,
            ];
            break;
    }
}

$info += $default;

display_error_page($info);

require_once THEMES.'templates/footer.php';
