<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: preview.ajax.php
| Author: Frederick MC CHan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once dirname(__FILE__)."../../../../../maincore.php";
require_once THEMES."templates/render_functions.php";

$text = stripinput($_POST['text']);

// filter to relative path conversion
echo "<div class='preview-response clearfix p-20'>\n";

// Set get_image paths based on URI. This is ajax request file. It doesn't return a standard BASEDIR.
$prefix_ = "";
if (!fusion_get_settings("site_seo") && isset($_POST['url'])) {
	$uri = pathinfo($_POST['url']);
	$count =  substr($_POST['url'], -1) == "/" ? substr_count($uri['dirname'], "/") : substr_count($uri['dirname'], "/")-1;
	$prefix_ = str_repeat("../", $count);
	foreach (cache_smileys() as $smiley) {
		$smiley_path = fusion_get_settings('siteurl')."images/smiley/".$smiley['smiley_image'];
		\PHPFusion\ImageRepo::setImage("smiley_".$smiley['smiley_text'], $smiley_path);
	}
}

if ($_POST['editor'] == 'html') {
    $text = htmlspecialchars($text);
	$text = parsesmileys(nl2br(html_entity_decode(stripslashes($text))));
	if (isset($_POST['mode']) && $_POST['mode'] == 'admin') {
		$images = str_replace('../../../', '', IMAGES);
		$text = str_replace(IMAGES, $images, $text);
		$text = str_replace(IMAGES_N, $images, $text);
        $text = parse_imageDir($text, $prefix_."images/");
	}
    echo nl2br(html_entity_decode($text, ENT_QUOTES, $locale['charset'])) ?: "<p class='text-center'>".$locale['nopreview']."</p>\n";
} elseif ($_POST['editor'] == 'bbcode') {
    $text = htmlspecialchars($text);
	$text = parseubb(parsesmileys($text));
	if (isset($_POST['mode']) && $_POST['mode'] == 'admin') {
		$images = str_replace('../../../', '', IMAGES);
		$text = str_replace(IMAGES, $images, $text);
		$text = str_replace(IMAGES_N, $images, $text);
        $text = parse_imageDir($text, $prefix_."images/");
	}
    echo nl2br(html_entity_decode($text, ENT_QUOTES, $locale['charset'])) ?: "<p class='text-center'>".$locale['nopreview']."</p>\n";
} else {
    $text = htmlspecialchars($text);
	$text = parsesmileys($text);
	if (isset($_POST['mode']) && $_POST['mode'] == 'admin') {
		$images = str_replace('../../../', '', IMAGES);
		$text = str_replace(IMAGES, $images, $text);
		$text = str_replace(IMAGES_N, $images, $text);
	}
	echo parse_imageDir(nl2br(html_entity_decode($text, ENT_QUOTES, $locale['charset']))) ? : "<p class='text-center'>".$locale['nopreview']."</p>\n";
}
echo "</div>\n";