<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: photo.php
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
require_once __DIR__.'/../../maincore.php';
if (!defined('GALLERY_EXIST')) {
    redirect(BASEDIR."error.php?code=404");
}

require_once INCLUDES."infusions_include.php";
$gallery_settings = get_settings("gallery");
/**
 * Converts Hex to RGB
 *
 * @param string $hex
 *
 * @return array
 */
function convert_color($hex) {
    global $locale;
    $hex = str_replace("#", "", $hex);
    if (preg_match('/^[[:xdigit:]]+$/', $hex)) {
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $red = $green = $blue = 0;
        sscanf($hex, '%2x%2x%2x', $red, $green, $blue);
        $color['success'] = TRUE;
        $color['r'] = (int)$red;
        $color['g'] = (int)$green;
        $color['b'] = (int)$blue;
    } else {
        $color['success'] = FALSE;
        $color['error'] = $locale['global_900'];
    }

    return $color;
}

function RGBtoArray($rgb) {
    if (stristr($rgb, "rgb(")) {
        $rgb_value = str_replace("rgb(", "", $rgb);
        $rgb_value = str_replace(")", "", $rgb);
        $rgb_value = explode(",", $rgb);
        if (count($rgb_value) == 3) {
            return [
                "r" => $rgb_value[0],
                "g" => $rgb_value[1],
                "b" => $rgb_value[2],
            ];
        } else {
            return "bad rgb value. it does not contain 3 comma delimiter";
        }
    } else {
        return "value is not accepted";
    }
}

if (isset($_GET['photo_id']) && isnum($_GET['photo_id'])) {
    $result = dbquery("SELECT
	ta.album_id, ta.album_title, ta.album_description, ta.album_access, tp.photo_title,
	tp.photo_filename, tp.photo_thumb2
	FROM ".DB_PHOTOS." tp INNER JOIN ".DB_PHOTO_ALBUMS." ta USING (album_id)
	WHERE photo_id=".$_GET['photo_id']." GROUP BY tp.photo_id
	");
    $data = dbarray($result);

    if (checkgroup($data['album_access'])) {
        $parts = explode(".", $data['photo_filename']);
        $wm_file1 = $parts[0]."_w1.".$parts[1];
        $wm_file2 = $parts[0]."_w2.".$parts[1];
        if (!isset($_GET['full'])) {
            $wm_file = IMAGES_G.$wm_file1; //w1 - full
        } else {
            $wm_file = IMAGES_G.$wm_file2; //w2 - normal
        }

        header("Content-type: image/jpeg");
        $img = IMAGES_G.$data['photo_filename'];
        $cop = BASEDIR.$gallery_settings['photo_watermark_image'];
        $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpeg', 'jpg'])) {
            $image = ImageCreateFromJPEG($img);
        } else {
            if ($ext === 'png') {
                $image = ImageCreateFromPNG($img);
            } else {
                if ($ext === 'gif') {
                    $image = ImageCreateFromGIF($img);
                    $sizeX = ImagesX($image);
                    $sizeY = ImagesY($image);
                    $image_tmp = ImageCreateTrueColor($sizeX, $sizeY);
                    $ica = ImageColorAllocate($image_tmp, 255, 255, 255);
                    ImageFill($image_tmp, 0, 0, $ica);
                    if (fusion_get_settings("thumb_compression") == "gd2") {
                        ImageCopyResampled($image_tmp, $image, 0, 0, 0, 0, $sizeX, $sizeY, $sizeX, $sizeY);
                    } else {
                        ImageCopyResized($image_tmp, $image, 0, 0, 0, 0, $sizeX, $sizeY, $sizeX, $sizeY);
                    }
                    $tmp = IMAGES_G.md5(time().$img).'.tmp';
                    ImageJPEG($image_tmp, $tmp);
                    ImageDestroy($image_tmp);
                    $image = ImageCreateFromJPEG($tmp);
                    unlink($tmp);
                }
            }
        }

        $image2 = FALSE;
        if ((file_exists($cop) && strtolower(pathinfo($cop, PATHINFO_EXTENSION)) === 'png') && !empty($gallery_settings['photo_watermark'])) {
            $image_dim_x = ImagesX($image);
            $image_dim_y = ImagesY($image);
            $copyright = ImageCreateFromPNG($cop);
            $copyright_dim_x = ImagesX($copyright);
            $copyright_dim_y = ImagesY($copyright);
            $where_x = $image_dim_x - $copyright_dim_x - 5;
            $where_y = $image_dim_y - $copyright_dim_y - 5;
            ImageCopy($image, $copyright, $where_x, $where_y, 0, 0, $copyright_dim_x, $copyright_dim_y);
            $thumb_w = 0;
            $thumb_h = 0;
            if (!isset($_GET['full'])) {
                if ($image_dim_x > $gallery_settings['photo_w'] || $image_dim_y > $gallery_settings['photo_h']) {
                    if ($image_dim_x < $image_dim_y) {
                        $thumb_w = round(($image_dim_x * $gallery_settings['photo_h']) / $image_dim_y);
                        $thumb_h = $gallery_settings['photo_h'];
                    } else if ($image_dim_x > $image_dim_y) {
                        $thumb_w = $gallery_settings['photo_w'];
                        $thumb_h = round(($image_dim_y * $gallery_settings['photo_w']) / $image_dim_x);
                    } else {
                        $thumb_w = $gallery_settings['photo_w'];
                        $thumb_h = $gallery_settings['photo_h'];
                    }
                } else {
                    $thumb_w = $image_dim_x;
                    $thumb_h = $image_dim_y;
                }
                $image2 = ImageCreateTrueColor($thumb_w, $thumb_h);
                if (fusion_get_settings("thumb_compression") == "gd2") {
                    ImageCopyResampled($image2, $image, 0, 0, 0, 0, $thumb_w, $thumb_h, $image_dim_x, $image_dim_y);
                } else {
                    ImageCopyResized($image2, $image, 0, 0, 0, 0, $thumb_w, $thumb_h, $image_dim_x, $image_dim_y);
                }
                ImageDestroy($image);
            }

            if ($gallery_settings['photo_watermark_text']) {
                $enc = ["&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;"];
                $dec = ["&", "\"", "'", "\\", '\"', "\'", "<", ">"];
                // drop the function and use a rgb output.

                $black = ImageColorAllocate(($image2 ? $image2 : $image), 0, 0, 0);
                // lets just do a rgb value instead of converting.
                // bugged
                //@todo: drop function and scan image brightness to go for either black or white.
                $colors1 = convert_color($gallery_settings['photo_watermark_text_color1']);
                $colors2 = convert_color($gallery_settings['photo_watermark_text_color2']);
                $colors3 = convert_color($gallery_settings['photo_watermark_text_color3']);

                $color1 = ImageColorAllocate(($image2 ? $image2 : $image), $colors1['r'], $colors1['g'], $colors1['b']);
                $color2 = ImageColorAllocate(($image2 ? $image2 : $image), $colors2['r'], $colors2['g'], $colors2['b']);
                $color3 = ImageColorAllocate(($image2 ? $image2 : $image), $colors3['r'], $colors3['g'], $colors3['b']);
                //move text y
                $mty1 = ($thumb_h ? $thumb_h : $image_dim_y) - ($thumb_h ? 40 : 50) - 25;
                $mty2 = ($thumb_h ? $thumb_h : $image_dim_y) - ($thumb_h ? 25 : 35) - 20;
                $mty3 = ($thumb_h ? $thumb_h : $image_dim_y) - ($thumb_h ? 15 : 20) - 15;
                $album_title = str_replace("\r", "", $data['album_title']);
                $album_title = str_replace("\n", "", $album_title);
                $album_title = preg_replace("[\[(.*?)\]]", "", $album_title);
                $album_title = preg_replace("<\<(.*?)\>>", "", $album_title);
                $album_title = trimlink($album_title, 75);
                $album_title = str_replace($enc, $dec, $album_title);
                $album_description = str_replace("\r", "", $data['album_description']);
                $album_description = str_replace("\n", "", $album_description);
                $album_description = preg_replace("[\[(.*?)\]]", "", $album_description);
                $album_description = preg_replace("<\<(.*?)\>>", "", $album_description);
                $album_description = trimlink($album_description, 75);
                $album_description = str_replace($enc, $dec, $album_description);
                $photo_title = str_replace("\r", "", $data['photo_title']);
                $photo_title = str_replace("\n", "", $photo_title);
                $photo_title = preg_replace("[\[(.*?)\]]", "", $photo_title);
                $photo_title = preg_replace("<\<(.*?)\>>", "", $photo_title);
                $photo_title = trimlink($photo_title, 75);
                $photo_title = str_replace($enc, $dec, $photo_title);
                $album_title_font_size = !isset($_GET['full']) ? 3 : 5;
                $album_descr_font_size = !isset($_GET['full']) ? 1 : 3;
                $photo_title_font_size = !isset($_GET['full']) ? 1 : 3;

                $fontfile = dirname(__FILE__).'/font/NotoSansRegular.ttf';
                //album title
                imagettftext(($image2 ? $image2 : $image), $album_title_font_size + 10, 0, 10 + 10, $mty1 - 1, $black, $fontfile, $album_title);
                imagettftext(($image2 ? $image2 : $image), $album_title_font_size + 10, 0, 10 + 10, $mty1 + 1, $black, $fontfile, $album_title);
                imagettftext(($image2 ? $image2 : $image), $album_title_font_size + 10, 0, 10 + 9, $mty1, $black, $fontfile, $album_title);
                imagettftext(($image2 ? $image2 : $image), $album_title_font_size + 10, 0, 10 + 11, $mty1, $black, $fontfile, $album_title);
                imagettftext(($image2 ? $image2 : $image), $album_title_font_size + 10, 0, 10 + 10, $mty1, $color1, $fontfile, $album_title);
                //album info
                imagettftext(($image2 ? $image2 : $image), $album_descr_font_size + 10, 0, 10 + 10, $mty2 - 1, $black, $fontfile,$album_description);
                imagettftext(($image2 ? $image2 : $image), $album_descr_font_size + 10, 0, 10 + 10, $mty2 + 1, $black, $fontfile, $album_description);
                imagettftext(($image2 ? $image2 : $image), $album_descr_font_size + 10, 0, 10 + 9, $mty2, $black, $fontfile, $album_description);
                imagettftext(($image2 ? $image2 : $image), $album_descr_font_size + 10, 0, 10 + 11, $mty2, $black, $fontfile, $album_description);
                imagettftext(($image2 ? $image2 : $image), $album_descr_font_size + 10, 0, 10 + 10, $mty2, $color2, $fontfile, $album_description);
                //photo name
                imagettftext(($image2 ? $image2 : $image), $photo_title_font_size + 10, 0, 10 + 10, $mty3 - 1, $black, $fontfile, $photo_title);
                imagettftext(($image2 ? $image2 : $image), $photo_title_font_size + 10, 0, 10 + 10, $mty3 + 1, $black, $fontfile, $photo_title);
                imagettftext(($image2 ? $image2 : $image), $photo_title_font_size + 10, 0, 10 + 9, $mty3, $black, $fontfile, $photo_title);
                imagettftext(($image2 ? $image2 : $image), $photo_title_font_size + 10, 0, 10 + 11, $mty3, $black, $fontfile, $photo_title);
                imagettftext(($image2 ? $image2 : $image), $photo_title_font_size + 10, 0, 10 + 10, $mty3, $color3, $fontfile, $photo_title);
            }
        }
        //create image
        if ($gallery_settings['photo_watermark_save']) {
            ImageJPEG(($image2 ? $image2 : $image), $wm_file);
        }
        ImageJPEG((isset($image2) && $image2 ? $image2 : $image));
        ImageDestroy((isset($image2) && $image2 ? $image2 : $image));
        if (isset($copyright) && is_resource($copyright)) {
            ImageDestroy($copyright);
        }
    } else {
        redirect(BASEDIR."index.php");
    }
} else {
    redirect(BASEDIR."index.php");
}
