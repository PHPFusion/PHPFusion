<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: error.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once dirname(__FILE__).'/maincore.php';
require_once THEMES."templates/header.php";
require_once THEMES."templates/global/error.php";
/**
 * Dir Replacements
 * @param string $output
 * @return mixed
 */
function replaceDir($output = "") {
    $findHTMLTags = "/(href|src)=('|\")((?!(htt|ft)p(s)?:\\/\\/)[^\\']*)/i";
    if (!function_exists("replaceHTMLTags")) {
        function replaceHTMLTags($m) {
            return $m[1]."=".$m[2].fusion_get_settings('siteurl').$m[3];
        }
    }

    return preg_replace_callback("$findHTMLTags", "replaceHTMLTags", $output);
}
add_handler("replaceDir");

$locale = fusion_get_locale("", LOCALE.LOCALESET."error.php");

$default = [
    'title'     => $locale['errunk'],
    'image_src' => IMAGES."error/unknown.png",
    'status'    => '505',
    'back'      => [
        'url'    => BASEDIR.'index.php',
        'title'  => $locale['errret']
    ]
];

if (isset($_GET['code'])) {
    switch ($_GET['code']) {
        case 401:
            header("HTTP/1.1 401 Unauthorized");
            $info = [
                'title'     => $locale['err401'],
                'image_src' => IMAGES.'error/401.png',
                'status'    => 401
            ];
            break;
        case 403:
            header("HTTP/1.1 403 Forbidden");
            $info = [
                'title'     => $locale['err403'],
                'image_src' => IMAGES.'error/403.png',
                'status'    => 403,
            ];
            break;
        case 404:
            header("HTTP/1.1 404 Not Found");
            $info = [
                'title'     => $locale['err404'],
                'image_src' => IMAGES.'error/404.png',
                'status'    => 404,
            ];
            break;
        case 500:
            header("HTTP/1.1 500 Internal Server Error");
            $info = [
                'title'     => $locale['err500'],
                'image_src' => IMAGES.'error/500.png',
                'status'    => 500,
            ];
            break;
    }
}

$info += $default;
\PHPFusion\Panels::getInstance()->hide_panel('LEFT');
\PHPFusion\Panels::getInstance()->hide_panel('RIGHT');
ob_start();
display_error_page($info);
echo strtr(ob_get_clean(), [
    '{%title%}'      => $info['title'],
    '{%message%}'    => $locale['errmsg'],
    '{%image_src%}'  => $info['image_src'],
    '{%error_code%}' => $info['status'],
    '{%back_link%}'  => $info['back']['url'],
    '{%back_title%}' => $info['back']['title']
]);
require_once THEMES."templates/footer.php";