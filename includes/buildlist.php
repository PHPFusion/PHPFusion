<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: buildlist.php
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
defined('IN_FUSION') || exit;

$settings = fusion_get_settings();
$locale = fusion_get_locale();

$image_files = [];

$temp = opendir(IMAGES);
while ($file = readdir($temp)) {
    if (!in_array($file, [".", "..", "/", "index.php", "imagelist.js"]) && !is_dir(IMAGES.$file)) {
        $image_files[] = "['".$locale['422'].": ".$file."','".$settings['siteurl']."images/".$file."'], ";
    }
}
closedir($temp);

if (defined('IMAGES_A')) {
    $temp = opendir(IMAGES_A);
    while ($file = readdir($temp)) {
        if (!in_array($file, [".", "..", "/", "index.php"])) {
            $image_files[] = "['".$locale['423'].": ".$file."','".$settings['siteurl']."infusions/articles/images/".$file."'], ";
        }
    }
    closedir($temp);
}

if (defined('IMAGES_N')) {
    $temp = opendir(IMAGES_N);
    while ($file = readdir($temp)) {
        if (!in_array($file, [".", "..", "/", "index.php"]) && !is_dir(IMAGES_N.$file)) {
            $image_files[] = "['".$locale['424'].": ".$file."','".$settings['siteurl']."infusions/news/images/".$file."'], ";
        }
    }
    closedir($temp);
}

if (defined('IMAGES_NC')) {
    $temp = opendir(IMAGES_NC);
    while ($file = readdir($temp)) {
        if (!in_array($file, [".", "..", "/", "index.php"]) && !is_dir(IMAGES_NC.$file)) {
            $image_files[] = "['".$locale['427'].": ".$file."','".$settings['siteurl']."infusions/news/news_cats/".$file."'], ";
        }
    }
    closedir($temp);
}

if (defined('GALLERY_EXIST')) {
    $result = dbquery("
        SELECT ".DB_PHOTO_ALBUMS.".album_title, ".DB_PHOTOS.".photo_id
        FROM ".DB_PHOTO_ALBUMS.", ".DB_PHOTOS."
        WHERE ".DB_PHOTO_ALBUMS.".album_id = ".DB_PHOTOS.".album_id
    ");
    $album = [];
    while ($data = dbarray($result)) {
        $album[] = $data['album_title'];
        $album[] = $data['photo_id'];
    }
    $temp = opendir(IMAGES_G);
    while ($file = readdir($temp)) {
        if (!in_array($file, [".", "..", "/", "index.php"]) && !is_dir(IMAGES_G.$file)) {
            $slut = strpos($file, ".");
            $smlg = substr($file, 0, $slut);
            $navn = "";
            for ($i = 1; $i < count($album); $i = $i + 2) {
                if ($smlg == $album[$i]) {
                    $navn = $album[$i - 1];
                    break;
                }
            }
            $image_files[] = "['".$navn." album: ".$file."','".$settings['siteurl']."infusions/gallery/photos/".$file."'], ";
        }
    }
    closedir($temp);
}

sort($image_files);

// compile list -----------------
if (isset($image_files)) {
    $indhold = "var tinyMCEImageList = new Array(";
    for ($i = 0; $i < count($image_files); $i++) {
        $indhold .= $image_files[$i];
    }
    $lang = strlen($indhold) - 2;
    $indhold = substr($indhold, 0, $lang);
    $indhold = $indhold.");";
    $fp = fopen(IMAGES."imagelist.js", "w");
    fwrite($fp, $indhold);
    fclose($fp);
}
