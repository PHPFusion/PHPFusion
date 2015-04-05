<?php

// Creating a Working Photo Admin Gallery in under 30 seconds.

/**
 * Step 1 - Initialize the Class
 * The below statement binds the whole AdminUI engine into 1 single string, in this example (the `$eshop_gallery`)
 */
$eshop_gallery = new PHPFusion\Gallery\Admin();

/**
 * Step 2 - Set your upload rules settings.
 */
$eshop_gallery->setUploadSettings(
	array(
		'thumbnail_folder'=>'thumbs',
		'thumbnail' => 1,
		'thumbnail_w' =>  fusion_get_settings('eshop_image_tw'),
		'thumbnail_h' =>  fusion_get_settings('eshop_image_th'),
		'thumbnail_suffix' =>'_t1',
		'thumbnail2'=>1,
		'thumbnail2_w' 	=>  fusion_get_settings('eshop_image_t2w'),
		'thumbnail2_h' 	=>  fusion_get_settings('eshop_image_t2h'),
		'thumbnail2_suffix' => '_t2',
		'delete_original' => 1,
		'max_width'		=>	fusion_get_settings('eshop_image_w'),
		'max_height'	=>	fusion_get_settings('eshop_image_h'),
		'max_byte'		=>	fusion_get_settings('eshop_image_b'),
		'multiple' => 0,
	)
);

/**
 * Step 3 - Setup System Variables
 * a. Set up your Image Upload Path in the System (Relative to BASEDIR)
 * b. Set up your PHOTO_ALBUM database table
 * c. Set up your PHOTO database table
 * d. set up photo comments - true or false
 * e. set up photo ratings - true or false
 */
$eshop_gallery->setImageUploadDir(BASEDIR."eshop/pictures/");
$eshop_gallery->setPhotoCatDb(DB_ESHOP_ALBUMS);
$eshop_gallery->setPhotoDb(DB_ESHOP_PHOTOS);
$eshop_gallery->setGalleryRights('ESHP');
$eshop_gallery->setEnableComments(false);
$eshop_gallery->setAllowComments(false);
$eshop_gallery->setEnableRatings(false);
$eshop_gallery->setAllowRatings(false);
$eshop_gallery->boot();
