<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: fonts/index.php
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
require_once __DIR__."/../../maincore.php";

$https = $_SERVER["HTTPS"] ? "https://" : "http://";
$host = $https.$_SERVER["HTTP_HOST"];
/** If this server is a CDN server, then you may want to allow all (*), otherwise only allow your own server own access */
##header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Origin: '.$host);
header("Content-type: text/css");
CONST BASE_FONTS_DIR = INCLUDES."fonts/";

$family = addslashes($_GET['family']);
if (strpos($family, ":")) {
    list($family, $family_weight) = explode(":", $family);
    $family_request = explode(",", $family_weight);
}

$filter = array('.', '..', '.DS_Store');
$directories = array_diff(scandir(BASE_FONTS_DIR), $filter);

foreach ($directories as $dir) {
    if ($dir == $family) {
        $files = array_diff(scandir(BASE_FONTS_DIR.'/'.$family), $filter);

        foreach ($files as $file) {
            $font = explode('-', $file);
            $font_name = $font[0];
            $font_weight = $font[1];
            $font_format = "";
            $font_style = "";
            $font_italic = "";
            if (strpos($font[2], ".")) {
                // thin.otf
                list($font_style, $font_format) = explode(".", $font[2]);
            } else if (strpos($font[3], ".")) {
                list($font_style, $font_format) = explode(".", $font[3]);
                $font_italic = "i";
            }
            if (!$font_format or !$font_style) {
                throw new Exception("Invalid font archive and could not be loaded");
            }
            if ($font_format == 'ttf') {
                $font_format = 'truetype';
            } else {
                $font_format = 'opentype';
            }
            $font_prop = $font_weight.$font_italic;
            if (!isset($family_request) or (isset($family_request) && in_array($font_prop, $family_request))) :
                ?>
                @font-face {
                font-family: '<?php echo $font_name ?>';
                font-weight: <?php echo $font_weight ?>;
                <?php if ($font_style) : ?>
                font-style: <?php echo $font_style ?>;
            <?php endif; ?>
                src: url('<?php echo BASE_FONTS_DIR ?>/<?php echo $font_name ?>/<?php echo $file ?>') format('<?php echo $font_format ?>');
                }
            <?php
            endif;
        }
    }
}
