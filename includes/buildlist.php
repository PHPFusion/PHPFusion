<?php
/*---------------------------------------------------+
| buildlist.php - iLister enginge.
+----------------------------------------------------+
| Copyright (C) 2005 Johs Lind
| http://www.geltzer.dk/
| Inspired by: PHP-Fusion 6 coding
+----------------------------------------------------+
| Released under the terms & conditions of v2 of the
| GNU General Public License. For details refer to
| the included gpl.txt file or visit http://gnu.org
+----------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

$image_files = array();

// images ------------------------
$temp = opendir(IMAGES);
while ($file = readdir($temp)) {
	if (!in_array($file, array(".", "..", "/", "index.php", "imagelist.js")) && !is_dir(IMAGES.$file)) {
		$image_files[] = "['".$locale['422'].": ".$file."','".$settings['siteurl']."images/".$file."'], ";
	}
}
closedir($temp);

// articles ---------------
$temp = opendir(IMAGES_A);
while ($file = readdir($temp)) {
	if (!in_array($file, array(".", "..", "/", "index.php"))) {
		$image_files[] = "['".$locale['423'].": ".$file."','".$settings['siteurl']."images/articles/".$file."'], ";
	}
}
closedir($temp);
	
// news -------------------
$temp = opendir(IMAGES_N);
while ($file = readdir($temp)) {
	if (!in_array($file, array(".", "..", "/", "index.php")) && !is_dir(IMAGES_N.$file)) {
		$image_files[] = "['".$locale['424'].": ".$file."','".$settings['siteurl']."images/news/".$file."'], ";
	}
}
closedir($temp);
	
// news cats -------------------
$temp = opendir(IMAGES_NC);
while ($file = readdir($temp)) {
	if (!in_array($file, array(".", "..", "/", "index.php")) && !is_dir(IMAGES_NC.$file)) {
		$image_files[] = "['".$locale['427'].": ".$file."','".$settings['siteurl']."images/news_cats/".$file."'], ";
	}
}
closedir($temp);

// photoalbum -------------------
$result = dbquery("
	SELECT ".DB_PHOTO_ALBUMS.".album_title, ".DB_PHOTOS.".photo_id
	FROM ".DB_PHOTO_ALBUMS.", ".DB_PHOTOS."
	WHERE ".DB_PHOTO_ALBUMS.".album_id = ".DB_PHOTOS.".album_id 
");

$album = array();

while ($data = dbarray($result)) {
	$album[] = $data['album_title'];
	$album[] = $data['photo_id'];
}

$temp = opendir(PHOTOS);
while ($file = readdir($temp)) {
	if (!in_array($file, array(".", "..", "/", "index.php")) && !is_dir(PHOTOS.$file)) {
		$slut = strpos($file,".");
		$smlg = substr($file,0,$slut);
		$navn = "";
		for ($i = 1; $i < count($album); $i = $i + 2){
			if ($smlg == $album[$i]) {
				$navn = $album[$i - 1];
				break;
			}
		}
		$image_files[] = "['".$navn." album: ".$file."','".$settings['siteurl']."images/photoalbum/".$file."'], ";
	}
}
closedir($temp);

sort($image_files);

// compile list -----------------
if (isset($image_files)) {
	$indhold = "var tinyMCEImageList = new Array(";
	for ($i = 0; $i < count($image_files); $i++){
		$indhold .= $image_files[$i];
	}
	$lang = strlen($indhold) - 2;
	$indhold = substr($indhold, 0, $lang);
	$indhold = $indhold.");";
	$fp = fopen(IMAGES."imagelist.js", "w");
	fwrite($fp, $indhold);
	fclose($fp);
}
?>