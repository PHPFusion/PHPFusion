<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: gallery_admin.php
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
require_once "../../maincore.php";
pageAccess('PH');

require_once THEMES."templates/admin_header.php";
include INFUSIONS."gallery/locale/".LOCALESET."gallery_admin.php";
require_once INCLUDES."bbcode_include.php";
require_once INCLUDES."photo_functions_include.php";
require_once INFUSIONS."gallery/classes/Admin.php";

add_breadcrumb(array('link'=>INFUSIONS."gallery/gallery_admin.php".$aidlink, 'title'=>$locale['photo_000']));

$gallery_settings = new PHPFusion\Gallery\Admin();

require_once INCLUDES."infusions_include.php";
$settings_inf = get_settings('gallery');
$gallery_settings->setUploadSettings(
	array(
		'thumbnail_folder'=>'thumbs',
		'thumbnail' => 1,
		'thumbnail_w' =>  $settings_inf['thumb_w'],
		'thumbnail_h' =>  $settings_inf['thumb_h'],
		'thumbnail_suffix' =>'_t1',
		'thumbnail2'=>1,
		'thumbnail2_w' 	=>  $settings_inf['photo_w'],
		'thumbnail2_h' 	=>  $settings_inf['photo_h'],
		'thumbnail2_suffix' => '_t2',
		'delete_original' => 1,
		'max_width'		=>	$settings_inf['photo_max_w'],
		'max_height'	=>	$settings_inf['photo_max_h'],
		'max_byte'		=>	$settings_inf['photo_max_b'],
		'multiple' => 0,
	)
);

$gallery_settings->setImageUploadDir(INFUSIONS."gallery/albums/");
$gallery_settings->setPhotoCatDb(DB_PHOTO_ALBUMS);
$gallery_settings->setPhotoDb(DB_PHOTOS);
$gallery_settings->setGalleryRights('PH');
$gallery_settings->setEnableComments(false);
$gallery_settings->setEnableRatings(false);
$gallery_settings->setAllowComments('comments_enabled');
$gallery_settings->setAllowRatings('ratings_enabled');
$gallery_settings->boot();

require_once THEMES."templates/footer.php";
