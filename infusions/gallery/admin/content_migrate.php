<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: content_migrate.php
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
require_once "../../../maincore.php";
require_once THEMES."templates/header.php";

// use checkrights outside of Administration
if (!checkrights("PH")) { redirect(BASEDIR."index.php"); }

/**
 * Remove all files, subdirs and ultimatly the directory in a given dir
 * @param $dir
 */
 
function rrmdir($dir) { 
	if (is_dir($dir)) { 
		$objects = scandir($dir); 
			foreach ($objects as $object) { 
				if ($object != "." && $object != "..") { 
					if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
				} 
			} 
		reset($objects); 
		rmdir($dir); 
	} 
}

/**
 * Returns all photos inside the album into an array
 * @param $album_id
 */

function move_photos($album_id) {
	$result = dbquery("SELECT * FROM ".DB_PHOTOS." WHERE album_id='".$album_id."'");
    if (dbrows($result)>0) {
        while ($photo_data = dbarray($result)) {
			rename(IMAGES."photoalbum/album_".$album_id."/".$photo_data['photo_filename'], INFUSIONS."gallery/photos/".$photo_data['photo_filename']);
			rename(IMAGES."photoalbum/album_".$album_id."/".$photo_data['photo_thumb1'], INFUSIONS."gallery/photos/".$photo_data['photo_thumb1']);
			rename(IMAGES."photoalbum/album_".$album_id."/".$photo_data['photo_thumb2'], INFUSIONS."gallery/photos/".$photo_data['photo_thumb2']);
		}
    }
}

$result = dbquery("SELECT * FROM ".DB_PHOTO_ALBUMS);

if (dbrows($result) > 0) {
	while ($data = dbarray($result)) {
		// Rename the album thumb here
		rename(IMAGES."photoalbum/".$data['album_thumb'], INFUSIONS."gallery/photos/".$data['album_thumb']);

		// Call the album directory rename function here
		move_photos($data['album_id']);
	}
}


//Remove the whole old photoalbum dir
rrmdir(IMAGES."photoalbum");

require_once THEMES."templates/footer.php";
