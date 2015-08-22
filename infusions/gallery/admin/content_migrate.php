<?php
// move photos from v7 photo folder to v9 folder

//require_once "../../../maincore.php";
//require_once THEMES."templates/header.php";
$directory = array();
$dir = IMAGES."photoalbum/";
$temp = opendir($dir);
if (file_exists($dir)) {
	while ($folder = readdir($temp)) {
		if (!in_array($folder, array("..", ".", '.DS_Store', 'index.php'))) {
			$directory[] = $folder;
			if (is_file($folder)) {
				rename(IMAGES."photoalbum/".$folder, INFUSIONS."gallery/photos/".$folder);
			}
		}
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
//require_once THEMES."templates/footer.php";