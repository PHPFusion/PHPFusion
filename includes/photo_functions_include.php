<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: photo_functions_include.php
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
defined('IN_FUSION') || exit;

/**
 * Create a thumbnail image from an already uploaded image on your server.
 *
 * @param string $filetype  This function only supports three filetypes: gif, jpeg, png, webp
 * @param string $origfile  Path to the orginal file you want to create a thumbnail of.
 * @param string $thumbfile Path to the thumbnail file you want to create.
 * @param int    $new_w     Max width for thumbnail image.
 * @param int    $new_h     Max height for thumbnail image.
 */
function createthumbnail($filetype, $origfile, $thumbfile, $new_w, $new_h) {
    $settings = fusion_get_settings();
    $origimage = '';
    if ($filetype == 1) {
        $origimage = imagecreatefromgif($origfile);
    } else if ($filetype == 2) {
        $origimage = imagecreatefromjpeg($origfile);
    } else if ($filetype == 3) {
        $origimage = imagecreatefrompng($origfile);
    } else if ($filetype == 4) {
        $origimage = imagecreatefromwebp($origfile);
    }

    $old_x = imagesx($origimage);
    $old_y = imagesy($origimage);
    $ratio_x = $old_x / $new_w;
    $ratio_y = $old_y / $new_h;
    if ($ratio_x > $ratio_y) {
        $thumb_w = round($old_x / $ratio_x);
        $thumb_h = round($old_y / $ratio_x);
    } else {
        $thumb_w = round($old_x / $ratio_y);
        $thumb_h = round($old_y / $ratio_y);
    }
    if ($settings['thumb_compression'] == "gd1") {
        $thumbimage = imagecreate($thumb_w, $thumb_h);
        imagecopyresized($thumbimage, $origimage, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
    } else {
        $thumbimage = imagecreatetruecolor($thumb_w, $thumb_h);
        if ($filetype == 3) {
            imagealphablending($thumbimage, FALSE);
            imagesavealpha($thumbimage, TRUE);
        }
        imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
    }

    touch($thumbfile);

    if ($filetype == 1) {
        imagegif($thumbimage, $thumbfile);
    } else if ($filetype == 2) {
        imagejpeg($thumbimage, $thumbfile, 100);
    } else if ($filetype == 3) {
        imagepng($thumbimage, $thumbfile, 9, PNG_ALL_FILTERS);
    } else if ($filetype == 4) {
        imagewebp($thumbimage, $thumbfile);
    }

    imagedestroy($origimage);
    imagedestroy($thumbimage);
}

/**
 * Create a square thumbnail image from an already uploaded image on your server.
 *
 * @param string $filetype  This function only supports three filetypes: gif, jpeg, png, webp
 * @param string $origfile  Path to the orginal file you want to create a thumbnail of.
 * @param string $thumbfile Path to the thumbnail file you want to create.
 * @param int    $new_size  Max size for thumbnail image.
 */
function createsquarethumbnail($filetype, $origfile, $thumbfile, $new_size) {
    $settings = fusion_get_settings();
    $origimage = '';
    if ($filetype == 1) {
        $origimage = imagecreatefromgif($origfile);
    } else if ($filetype == 2) {
        $origimage = imagecreatefromjpeg($origfile);
    } else if ($filetype == 3) {
        $origimage = imagecreatefrompng($origfile);
    } else if ($filetype == 4) {
        $origimage = imagecreatefromwebp($origfile);
    }

    $old_x = imagesx($origimage);
    $old_y = imagesy($origimage);
    $x = 0;
    $y = 0;
    if ($old_x > $old_y) {
        $x = ceil(($old_x - $old_y) / 2);
        $old_x = $old_y;
    } else if ($old_y > $old_x) {
        $y = ceil(($old_y - $old_x) / 2);
        $old_y = $old_x;
    }
    $new_image = imagecreatetruecolor($new_size, $new_size);
    if ($filetype == 3 && $settings['thumb_compression'] != "gd1") {
        imagealphablending($new_image, FALSE);
        imagesavealpha($new_image, TRUE);
    }
    imagecopyresampled($new_image, $origimage, 0, 0, $x, $y, $new_size, $new_size, $old_x, $old_y);
    if ($filetype == 1) {
        imagegif($new_image, $thumbfile);
    } else if ($filetype == 2) {
        imagejpeg($new_image, $thumbfile, 100);
    } else if ($filetype == 3) {
        imagepng($new_image, $thumbfile, 9, PNG_ALL_FILTERS);
    } else if ($filetype == 4) {
        imagewebp($new_image, $thumbfile);
    }

    imagedestroy($origimage);
    imagedestroy($new_image);
}

/**
 * Find another available image name based on the image in the folder.
 *
 * @param string $dir   The directory to check for the image, remember a / at the end of the directory path.
 * @param string $image The image inside the directory you want to check for.
 *
 * @return string
 */
function image_exists($dir, $image) {
    $i = 1;
    $image_name = substr($image, 0, strrpos($image, "."));
    $image_ext = strrchr($image, ".");
    while (file_exists($dir.$image)) {
        $image = $image_name."_".$i.$image_ext;
        $i++;
    }

    return $image;
}

/**
 * Get information about a specific image.
 *
 * @param string $image_path Path to the image.
 *
 * @return array|bool
 */
function exif($image_path) {
    error_reporting(0); // turn off everything. most of Photoshop images are unsupported.
    // Check if the variable is set and if the file itself exists before continuing
    if ((isset($image_path)) and (file_exists($image_path)) and !is_dir($image_path)) {
        // There are 2 arrays which contains the information we are after, so it's easier to state them both
        $exif_base = getimagesize($image_path);
        if (function_exists('exif_read_data')) {
            $exif_ifd0 = exif_read_data($image_path, 'IFD0', 0);
            $exif_exif = exif_read_data($image_path, 'EXIF', 0);
        }
        $notFound = fusion_get_locale('na');
        // Make
        if (isset($exif_ifd0['Make'])) {
            $camMake = $exif_ifd0['Make'];
        } else {
            $camMake = $notFound;
        }
        // Model
        if (isset($exif_ifd0['Model'])) {
            $camModel = $exif_ifd0['Model'];
        } else {
            $camModel = $notFound;
        }
        // Exposure
        if (isset($exif_ifd0['ExposureTime'])) {
            $camExposure = $exif_ifd0['ExposureTime'];
        } else {
            $camExposure = $notFound;
        }
        // Aperture
        if (isset($exif_ifd0['COMPUTEED']) && @array_key_exists('ApertureFNumber', $exif_ifd0['COMPUTED'])) {
            $camAperture = $exif_ifd0['COMPUTED']['ApertureFNumber'];
        } else {
            $camAperture = $notFound;
        }
        // Date
        if (isset($exif_ifd0['DateTime'])) {
            $camDate = $exif_ifd0['DateTime'];
        } else {
            $camDate = $notFound;
        }
        // ISO
        if (isset($exif_exif['ISOSpeedRatings'])) {
            $camIso = $exif_exif['ISOSpeedRatings'];
        } else {
            $camIso = $notFound;
        }
        $return = [];
        $return['width'] = $exif_base[0];
        $return['height'] = $exif_base[1];
        $return['mime'] = $exif_base['mime'];
        $return['channels'] = $exif_base['channels'];
        $return['bits'] = $exif_base['bits'];
        $return['make'] = $camMake;
        $return['model'] = $camModel;
        $return['exposure'] = $camExposure;
        $return['aperture'] = $camAperture;
        $return['date'] = $camDate;
        $return['iso'] = $camIso;

        return $return;
    } else {
        return FALSE;
    }
}

/**
 * Copy a file from any source to any destination.
 *
 * @param string $source      Copy file from URL.
 * @param string $destination Destination folder.
 *
 * @return array
 */
function copy_file($source, $destination) {
    $upload['name'] = '';
    $upload['error'] = TRUE;
    function getimg($url) {
        $headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg, image/png, image/webp';
        $headers[] = 'Connection: Keep-Alive';
        $headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
        $user_agent = 'php';
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        $return = curl_exec($process);
        curl_close($process);

        return $return;
    }

    $file = basename($source);
    $image_name = image_exists($destination, $file);
    $image = getimg($source);
    if ($image) {
        $fopen = file_put_contents($destination.'/'.$image_name, $image);
        if (!empty($fopen)) {
            $upload['name'] = $file;
            $upload['error'] = FALSE;
        }
    }

    return $upload;
}
