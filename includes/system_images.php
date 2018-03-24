<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: system_images.php
| Author: Max "Matonor" Toball
| Co-Author: Takács Ákos (Rimelek)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\ImageRepo;

/**
 * Get the imagepath or the html "img" tag
 *
 * @param string $image The name of the image.
 * @param string $alt "alt" attribute of the image
 * @param string $style "style" attribute of the image
 * @param string $title "title" attribute of the image
 * @param string $atts Custom attributes of the image
 *
 * @return string The path of the image if the first argument is given,
 * but others not. Otherwise the html "img" tag
 */
function get_image($image, $alt = "", $style = "", $title = "", $atts = "") {
    return ImageRepo::getImage($image, $alt, $style, $title, $atts);
}

/**
 * Set a path of an image
 *
 * @param string $name
 * @param string $path
 */
function set_image($name, $path) {
    ImageRepo::setImage($name, $path);
}

/**
 * Replace a part in each path
 *
 * @param string $source
 * @param string $target
 */
function redirect_img_dir($source, $target) {
    ImageRepo::replaceInAllPath($source, $target);
}
