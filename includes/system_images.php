<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
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
 * Get the imagepath or <img> tag.
 *
 * @param string $image The name of the image. Default system images: up, down, imagenotfound, left, right, noavatar, panel_on, panel_off
 * @param string $alt   The alt attribute of the image.
 * @param string $style The style attribute of the image.
 * @param string $title The title attribute of the image.
 * @param string $atts  Custom attributes of the image.
 *
 * @return string The path of the image if the first argument is given, but others not. Otherwise, <img> tag.
 */
function get_image($image, $alt = "", $style = "", $title = "", $atts = "") {
    return ImageRepo::getImage($image, $alt, $style, $title, $atts);
}

/**
 * Set a path of an image.
 *
 * @param string $name The name of an already defined image whose location you want to change, or your own image.
 * @param string $path The path to the image you are setting.
 */
function set_image($name, $path) {
    ImageRepo::setImage($name, $path);
}

/**
 *  Get the icon or <i> tag
 *
 * @param        $icon
 * @param string $class
 * @param string $tooltip
 *
 * @return string
 */
function get_icon($icon, string $class = "", $tooltip = "") {
    return ImageRepo::getIcon($icon, $class, $tooltip);
}

/**
 * Sets a class for an icon
 *
 * @param $name
 * @param $value
 */
function set_icon($name, $value) {
    ImageRepo::setIcon($name, $value);
}


/**
 * Replace a part in each image path.
 *
 * @param string $source Source path.
 * @param string $target Target path.
 */
function redirect_img_dir($source, $target) {
    ImageRepo::replaceInAllPath($source, $target);
}
