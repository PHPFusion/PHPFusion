<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: photo_functions_include
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

function createthumbnail($filetype, $origfile, $thumbfile, $new_w, $new_h) {
    $settings = fusion_get_settings();
    $origimage = '';
    if ($filetype == 1) {
        $origimage = imagecreatefromgif($origfile);
    } elseif ($filetype == 2) {
        $origimage = imagecreatefromjpeg($origfile);
    } elseif ($filetype == 3) {
        $origimage = imagecreatefrompng($origfile);
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
    };
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
    } elseif ($filetype == 2) {
        imagejpeg($thumbimage, $thumbfile, 100);
    } elseif ($filetype == 3) {
        imagepng($thumbimage, $thumbfile, 9, PNG_ALL_FILTERS);
    }
    imagedestroy($origimage);
    imagedestroy($thumbimage);
}

function createsquarethumbnail($filetype, $origfile, $thumbfile, $new_size) {
    $settings = fusion_get_settings();
    $origimage = '';
    if ($filetype == 1) {
        $origimage = imagecreatefromgif($origfile);
    } elseif ($filetype == 2) {
        $origimage = imagecreatefromjpeg($origfile);
    } elseif ($filetype == 3) {
        $origimage = imagecreatefrompng($origfile);
    }
    $old_x = imagesx($origimage);
    $old_y = imagesy($origimage);
    $x = 0;
    $y = 0;
    if ($old_x > $old_y) {
        $x = ceil(($old_x - $old_y) / 2);
        $old_x = $old_y;
    } elseif ($old_y > $old_x) {
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
    } elseif ($filetype == 2) {
        imagejpeg($new_image, $thumbfile, 100);
    } elseif ($filetype == 3) {
        imagepng($new_image, $thumbfile, 9, PNG_ALL_FILTERS);
    }
    imagedestroy($origimage);
    imagedestroy($new_image);
}

// returns the image name.
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
 * Retrieve Information about a specific Image
 * @param $imagePath
 * @return array|bool
 * Courtesy of : drpain.webster.org.za @ php.net
 */
function exif($imagePath) {
    global $locale;
    error_reporting(0); // turn off everything. most of Photoshop images are unsupported.
    // Check if the variable is set and if the file itself exists before continuing
    if ((isset($imagePath)) and (file_exists($imagePath)) and !is_dir($imagePath)) {
        // There are 2 arrays which contains the information we are after, so it's easier to state them both
        $exif_base = @getimagesize($imagePath);
        $exif_ifd0 = @exif_read_data($imagePath, 'IFD0', 0);
        $exif_exif = @exif_read_data($imagePath, 'EXIF', 0);
        //error control
        $notFound = $locale['na'];
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
        $return = array();
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
 * Copy a file from any source to any destination
 * @param $source -- copy file from URL
 * @param $destination -- copy file to folder
 */
function copy_file($source, $destination) {
    $upload['name'] = '';
    $upload['error'] = TRUE;
    function getimg($url) {
        $headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
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