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
require_once "maincore.php";

if (!@ini_get("safe_mode")) { define("SAFEMODE", false); } else { define("SAFEMODE", true); }

function convert_color($hex) {
	$len = strlen($hex);
	preg_match("/([0-9]|[A-F]|[a-f]){".$len."}/i", $hex, $arr);
	$hex = $arr[0];
	if ($hex) {
		switch($len) {
			case 2:
				$red = hexdec($hex);
				$green = 0;
				$blue = 0;
			break;
			case 4:
				$red = hexdec(substr($hex, 0, 2));
				$green = hexdec(substr($hex, 2, 2));
				$blue = 0;
			break;
			case 6:
				$red = hexdec(substr($hex, 0, 2));
				$green = hexdec(substr($hex, 2, 2));
				$blue = hexdec(substr($hex, 4, 2));
			break;
		}
		$color['success'] = true;
		$color['r'] = $red;
		$color['g'] = $green;
		$color['b'] = $blue;
		return $color;
	} else {
		$color['success'] = false;
		$color['error'] = $locale['global_900'];
		return $color;
	}
}

if (isset($_GET['photo_id']) && isnum($_GET['photo_id'])) {
	$result = dbquery(
		"SELECT ta.album_id, ta.album_title, ta.album_description, ta.album_access, tp.photo_title, tp.photo_filename, tp.photo_thumb2
		FROM ".DB_PHOTOS." tp INNER JOIN ".DB_PHOTO_ALBUMS." ta USING (album_id)
		WHERE photo_id=".$_GET['photo_id']." GROUP BY tp.photo_id"
	);
	$data = dbarray($result);
	if (checkgroup($data['album_access'])) {
		define("PHOTODIR", PHOTOS.(!SAFEMODE ? "album_".$data['album_id']."/" : ""));
		$parts = explode(".", $data['photo_filename']);
		$wm_file1 = $parts[0]."_w1.".$parts[1];
		$wm_file2 = $parts[0]."_w2.".$parts[1];
		if (!isset($_GET['full'])) {
			$wm_file = PHOTODIR.$wm_file1;
		} else {
			$wm_file = PHOTODIR.$wm_file2;
		}
		header("Content-type: image/jpg");
		$img = PHOTODIR.$data['photo_filename'];
		$cop = BASEDIR.$settings['photo_watermark_image'];
		if (preg_match("/.jpg/i", strtolower($img)) || preg_match("/.jpeg/i", strtolower($img))) {
			$image = ImageCreateFromJPEG($img);
		} else if (preg_match("/.png/i", strtolower($img))) {
			$image = ImageCreateFromPNG($img);
		} else if (preg_match("/.gif/i", strtolower($img))) {
			$image = ImageCreateFromGIF($img);
			$sizeX = ImagesX($image);
			$sizeY = ImagesY($image);
			$image_tmp = ImageCreateTrueColor($sizeX, $sizeY);
			$ica = ImageColorAllocate($image_tmp, 255, 255, 255);
			ImageFill($image_tmp, 0, 0, $ica);
			if ($settings['thumb_compression'] == "gd2") {
				ImageCopyResampled($image_tmp, $image, 0, 0, 0, 0, $sizeX, $sizeY, $sizeX, $sizeY);
			} else {
				ImageCopyResized($image_tmp, $image, 0, 0, 0, 0, $sizeX, $sizeY, $sizeX, $sizeY);
			}
			$tmp = PHOTODIR.md5(time().$img).'.tmp';
			ImageJPEG($image_tmp, $tmp);
			ImageDestroy($image_tmp);
			$image = ImageCreateFromJPEG($tmp);
			unlink($tmp);
		}
		if (file_exists($cop) && preg_match("/.png/i", strtolower($cop)) && $settings['photo_watermark']) {
			$image2 = false;
			$image_dim_x = ImagesX($image);
			$image_dim_y = ImagesY($image);
			$copyright = ImageCreateFromPNG($cop);
			$copyright_dim_x = ImagesX($copyright);
			$copyright_dim_y = ImagesY($copyright);
			$where_x = $image_dim_x - $copyright_dim_x - 5;
			$where_y = $image_dim_y - $copyright_dim_y - 5;
			ImageCopy ($image, $copyright, $where_x, $where_y, 0, 0, $copyright_dim_x, $copyright_dim_y);
			$thumb_w = 0; $thumb_h = 0;
			if (!isset($_GET['full'])) {
				if ($image_dim_x > $settings['photo_w'] || $image_dim_y > $settings['photo_h']) {
					if ($image_dim_x < $image_dim_y) {
						$thumb_w = round(($image_dim_x * $settings['photo_h']) / $image_dim_y);
						$thumb_h = $settings['photo_h'];
					} elseif ($image_dim_x > $image_dim_y) {
						$thumb_w = $settings['photo_w'];
						$thumb_h = round(($image_dim_y * $settings['photo_w']) / $image_dim_x);
					} else {
						$thumb_w = $settings['photo_w'];
						$thumb_h = $settings['photo_h'];
					}
				} else {
					$thumb_w = $image_dim_x;
					$thumb_h = $image_dim_y;
				}
				$image2 = ImageCreateTrueColor($thumb_w, $thumb_h);
				if ($settings['thumb_compression'] == "gd2") {
					ImageCopyResampled($image2, $image, 0, 0, 0, 0, $thumb_w, $thumb_h, $image_dim_x, $image_dim_y);
				} else {
					ImageCopyResized($image2, $image, 0, 0, 0, 0, $thumb_w, $thumb_h, $image_dim_x, $image_dim_y);
				}
				ImageDestroy($image);
			}
			if ($settings['photo_watermark_text']) {
				$enc = array("&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;");
				$dec = array("&", "\"", "'", "\\", '\"', "\'", "<", ">");
				$black = ImageColorAllocate(($image2 ? $image2 : $image), 0, 0, 0);
				$colors1 = convert_color($settings['photo_watermark_text_color1']);
				$colors2 = convert_color($settings['photo_watermark_text_color2']);
				$colors3 = convert_color($settings['photo_watermark_text_color3']);
				$color1 = ImageColorAllocate(($image2 ? $image2 : $image), $colors1['r'], $colors1['g'], $colors1['b']);
				$color2 = ImageColorAllocate(($image2 ? $image2 : $image), $colors2['r'], $colors2['g'], $colors2['b']);
				$color3 = ImageColorAllocate(($image2 ? $image2 : $image), $colors3['r'], $colors3['g'], $colors3['b']);
				//move text y
				$mty1 = ($thumb_h ? $thumb_h : $image_dim_y) - ($thumb_h ? 40 : 50);
				$mty2 = ($thumb_h ? $thumb_h : $image_dim_y) - ($thumb_h ? 25 : 35);
				$mty3 = ($thumb_h ? $thumb_h : $image_dim_y) - ($thumb_h ? 15 : 20);
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
				//album title
				ImageString(($image2 ? $image2 : $image), $album_title_font_size, 10, $mty1 - 1,  $album_title, $black);
				ImageString(($image2 ? $image2 : $image), $album_title_font_size, 10, $mty1 + 1,  $album_title, $black);
				ImageString(($image2 ? $image2 : $image), $album_title_font_size, 9,  $mty1,      $album_title, $black);
				ImageString(($image2 ? $image2 : $image), $album_title_font_size, 11, $mty1,      $album_title, $black);
				ImageString(($image2 ? $image2 : $image), $album_title_font_size, 10, $mty1,      $album_title, $color1);
				//album info
				ImageString(($image2 ? $image2 : $image), $album_descr_font_size, 10, $mty2 - 1,  $album_description, $black);
				ImageString(($image2 ? $image2 : $image), $album_descr_font_size, 10, $mty2 + 1,  $album_description, $black);
				ImageString(($image2 ? $image2 : $image), $album_descr_font_size, 9,  $mty2,      $album_description, $black);
				ImageString(($image2 ? $image2 : $image), $album_descr_font_size, 11, $mty2,      $album_description, $black);
				ImageString(($image2 ? $image2 : $image), $album_descr_font_size, 10, $mty2,      $album_description, $color2);
				//photo name
				ImageString(($image2 ? $image2 : $image), $photo_title_font_size, 10, $mty3 - 1,  $photo_title, $black);
				ImageString(($image2 ? $image2 : $image), $photo_title_font_size, 10, $mty3 + 1,  $photo_title, $black);
				ImageString(($image2 ? $image2 : $image), $photo_title_font_size, 9,  $mty3,      $photo_title, $black);
				ImageString(($image2 ? $image2 : $image), $photo_title_font_size, 11, $mty3,      $photo_title, $black);
				ImageString(($image2 ? $image2 : $image), $photo_title_font_size, 10, $mty3,      $photo_title, $color3);
			}
		}
		//create image
		if ($settings['photo_watermark_save']) { ImageJPEG(($image2 ? $image2 : $image), $wm_file); }
		ImageJPEG((isset($image2) && $image2 ? $image2 : $image));
		ImageDestroy((isset($image2) && $image2 ? $image2 : $image));
		if (isset($copyright) && is_resource($copyright)) {  ImageDestroy($copyright); }
	} else {
		redirect("index.php");
	}
} else {
	redirect("index.php");
}
?>
