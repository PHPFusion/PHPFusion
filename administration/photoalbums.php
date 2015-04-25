<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: photoalbums.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
pageAccess('PH');
require_once THEMES."templates/admin_header.php";
require_once INCLUDES."photo_functions_include.php";
require_once INCLUDES."bbcode_include.php";
include LOCALE.LOCALESET."admin/gallery.php";
add_breadcrumb(array('link'=>ADMIN."photoalbums.php".$aidlink, 'title'=>$locale['photo_000']));
$eshop_gallery = new PHPFusion\Gallery\Admin();
$eshop_gallery->setUploadSettings(
	array(
		'thumbnail_folder'=>'thumbs',
		'thumbnail' => 1,
		'thumbnail_w' =>  fusion_get_settings('thumb_w'),
		'thumbnail_h' =>  fusion_get_settings('thumb_h'),
		'thumbnail_suffix' =>'_t1',
		'thumbnail2'=>1,
		'thumbnail2_w' 	=>  fusion_get_settings('photo_w'),
		'thumbnail2_h' 	=>  fusion_get_settings('photo_h'),
		'thumbnail2_suffix' => '_t2',
		'delete_original' => 1,
		'max_width'		=>	fusion_get_settings('photo_max_w'),
		'max_height'	=>	fusion_get_settings('photo_max_h'),
		'max_byte'		=>	fusion_get_settings('photo_max_b'),
		'multiple' => 0,
	)
);
$eshop_gallery->setImageUploadDir(IMAGES."photoalbum/");
$eshop_gallery->setPhotoCatDb(DB_PHOTO_ALBUMS);
$eshop_gallery->setPhotoDb(DB_PHOTOS);
$eshop_gallery->setGalleryRights('PH');
$eshop_gallery->setEnableComments(false);
$eshop_gallery->setEnableRatings(false);
$eshop_gallery->setAllowComments('comments_enabled');
$eshop_gallery->setAllowRatings('ratings_enabled');
$eshop_gallery->boot();

require_once THEMES."templates/footer.php";