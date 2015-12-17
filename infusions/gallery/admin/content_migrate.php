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

$directory = array();
$dir = IMAGES."photoalbum/";
$temp = opendir($dir);
	while ($folder = readdir($temp)) {
		if (!in_array($folder, array("..", ".", '.DS_Store', 'index.php'))) {
			$directory[] = $folder;
			print_r($folder);
				rename(IMAGES."photoalbum/".$folder, INFUSIONS."gallery/photos/".$folder);
		}
	}

if (!empty($directory)) {
	foreach($directory as $folder) {
		if (file_exists(IMAGES."photoalbum/".$folder."/")) {
			$dir = opendir(IMAGES."photoalbum/".$folder."/");
			while ($subfolder = readdir($dir)) {
				if (!in_array($subfolder, array("..", ".", '.DS_Store', 'index.php'))) {
					if (is_file($subfolder)) {
						rename(IMAGES."photoalbum/".$folder."/".$subfolder, INFUSIONS."gallery/photos/".$subfolder);
					}
				}
			}
		}
	}
}
require_once THEMES."templates/footer.php";