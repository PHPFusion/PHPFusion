<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_migrate.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

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

// Move all forum attachments
$attachment_files = makefilelist(BASEDIR."forum/attachments/", ".|..|index.php", TRUE);
foreach ($attachment_files as $file) {
	rename(BASEDIR."forum/attachments/".$file, INFUSIONS."forum/attachments/".$file);
}


// Remove the whole old dir including rouge files
rrmdir(BASEDIR."forum/attachments");
