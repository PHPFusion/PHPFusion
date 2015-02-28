<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: photo_functions_include
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

function createthumbnail($filetype, $origfile, $thumbfile, $new_w, $new_h) {

	global $settings;

	if ($filetype == 1) { $origimage = imagecreatefromgif($origfile); }
	elseif ($filetype == 2) { $origimage = imagecreatefromjpeg($origfile); }
	elseif ($filetype == 3) { $origimage = imagecreatefrompng($origfile); }

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
		$thumbimage = imagecreate($thumb_w,$thumb_h);
		$result = imagecopyresized($thumbimage, $origimage, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
	} else {
		$thumbimage = imagecreatetruecolor($thumb_w,$thumb_h);
		if ($filetype == 3) {
			imagealphablending($thumbimage, false);
			imagesavealpha($thumbimage, true);
		}
		$result = imagecopyresampled($thumbimage, $origimage, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
	}

	touch($thumbfile);

	if ($filetype == 1) { imagegif($thumbimage, $thumbfile); }
	elseif ($filetype == 2) { imagejpeg($thumbimage, $thumbfile); }
	elseif ($filetype == 3) { imagepng($thumbimage, $thumbfile); }

	imagedestroy($origimage);
	imagedestroy($thumbimage);
}

function createsquarethumbnail($filetype, $origfile, $thumbfile, $new_size) {
	global $settings;
	if ($filetype == 1) { $origimage = imagecreatefromgif($origfile); }
	elseif ($filetype == 2) { $origimage = imagecreatefromjpeg($origfile); }
	elseif ($filetype == 3) { $origimage = imagecreatefrompng($origfile); }

	$old_x = imagesx($origimage);
	$old_y = imagesy($origimage);

	$x = 0; $y = 0;

	if ($old_x > $old_y) {
		$x = ceil(($old_x - $old_y) / 2);
		$old_x = $old_y;
	} elseif ($old_y > $old_x) {
		$y = ceil(($old_y - $old_x) / 2);
		$old_y = $old_x;
	}
	$new_image = imagecreatetruecolor($new_size,$new_size);
	if ($filetype == 3 && $settings['thumb_compression'] != "gd1") {
		imagealphablending($new_image, false);
		imagesavealpha($new_image, true);
	}
	imagecopyresampled($new_image,$origimage,0,0,$x,$y,$new_size,$new_size,$old_x,$old_y);

	if ($filetype == 1) { imagegif($new_image,$thumbfile,100); }
	elseif ($filetype == 2) { imagejpeg($new_image,$thumbfile,100); }
	elseif ($filetype == 3) { imagepng($new_image,$thumbfile,5); }

	imagedestroy($origimage);
	imagedestroy($new_image);
}

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
?>