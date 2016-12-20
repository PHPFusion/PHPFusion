<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: error.php
| Author: Joakim Falk (Falk)
| Author: Robert Gaudyn (Wooya)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "maincore.php";
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
            $pathInfo = pathinfo($_SERVER['REQUEST_URI']);
            $pathDepth = (substr($_SERVER['REQUEST_URI'], -1) == "/" ? substr_count($pathInfo['dirname'], "/") : substr_count($pathInfo['dirname'], "/") - 1);
            $actualDepth = ($pathDepth ? str_repeat("../", $pathDepth) : '');
            $replace = $m[1]."=".$m[2].($actualDepth).$m[3];
            return $replace;
        }
    }
    return preg_replace_callback("$findHTMLTags", "replaceHTMLTags", $output);
}
add_handler("replaceDir");
$locale = fusion_get_locale("", LOCALE.LOCALESET."error.php");
$data = array(
    "title" => $locale['errunk'],
    "image" => IMAGES."unknown.png"
);

if (isset($_GET['code'])) {
    switch ($_GET['code']) {
        case 401:
            header("HTTP/1.1 401 Unauthorized");
            $data = array(
                "title" => $locale['err401'],
                "image" => IMAGES."error/401.png"
            );
            break;
        case 403:
            header("HTTP/1.1 403 Forbidden");
            $data = array(
                "title" => $locale['err403'],
                "image" => IMAGES."error/403.png"
            );
            break;
        case 404:
            header("HTTP/1.1 404 Not Found");
            $data = array(
                "title" => $locale['err404'],
                "image" => IMAGES."error/404.png"
            );
            break;
        case 500:
            header("HTTP/1.1 500 Internal Server Error");
            $data = array(
                "title" => $locale['err500'],
                "image" => IMAGES."error/500.png"
            );
            break;
    }
}

display_error_page($data);

require_once THEMES."templates/footer.php";