<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: errors.php
| Author: Joakim Falk (Domi)
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

/**
 * Dir Replacements
 * @param string $output
 * @return mixed
 */
function replaceDir($output="") {
	$findHTMLTags = "/(href|src)='((?!(htt|ft)p(s)?:\\/\\/)[^\\']*)/i";
	if (!function_exists("replaceHTMLTags")) {
		function replaceHTMLTags($m) {
			$pathInfo = pathinfo($_SERVER['REQUEST_URI']);
			$pathDepth =  (substr($_SERVER['REQUEST_URI'], -1) == "/" ? substr_count($pathInfo['dirname'], "/") : substr_count($pathInfo['dirname'], "/")-1);
            $actualDepth = $pathDepth > 0 ? str_repeat("../", $pathDepth): "";
			$replace = $m[1]."='./".($actualDepth).$m[2];
			return $replace;
		}
	}
	return preg_replace_callback("$findHTMLTags", "replaceHTMLTags", $output);
}
add_handler("replaceDir");

$locale = fusion_get_locale("", LOCALE.LOCALESET."error.php");

$text = $locale['errunk'];
$img = "unknown.png";

if (isset($_GET['code'])) {
    switch($_GET['code']) {
        case 401:
            header("HTTP/1.1 401 Unauthorized");
            $text = $locale['err401'];
            $img = "401.png";
            break;
        case 403:
            header("HTTP/1.1 403 Forbidden");
            $text = $locale['err403'];
            $img = "403.png";
            break;
        case 404:
            header("HTTP/1.1 404 Not Found");
            $text = $locale['err404'];
            $img = "404.png";
            break;
        case 500:
            header("HTTP/1.1 500 Internal Server Error");
            $text = $locale['err500'];
            $img = "500.png";
            break;
    }
}

opentable($text);
echo "<table class='table table-responsive' width='100%' style='text-center'>";
echo "<tr>";
echo "<td width='30%' align='center'><img class='img-responsive' src='".IMAGES."error/".$img."' alt='".$text."' border='0'></td>";
echo "<td style='font-size:16px;color:red' align='center'>".$text."</td>";
echo "</tr>";
echo "<tr>";
echo "<td colspan='2' align='center'><b><a class='button' href='".BASEDIR."index.php'>".$locale['errret']."</a></b></td>";
echo "</tr>";
echo "</table>";
closetable();

require_once THEMES."templates/footer.php";