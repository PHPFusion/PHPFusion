<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: showphoto.php
| Author: Nick Jones (Digitanium)
| Co-Author: Robert Gaudyn (Wooya)
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
include LOCALE.LOCALESET."photogallery.php";

define("SAFEMODE", @ini_get("safe_mode") ? true : false);

echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>\n";
echo "<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='".$locale['xml_lang']."' lang='".$locale['xml_lang']."'>\n";
echo "<head>\n<title>".$settings['sitename']."</title>\n";
echo "<meta http-equiv='Content-Type' content='text/html; charset=".$locale['charset']."' />\n";
echo "<meta name='description' content='".$settings['description']."' />\n";
echo "<meta name='keywords' content='".$settings['keywords']."' />\n";
echo "<link rel='stylesheet' href='".THEME."styles.css' type='text/css' />\n";
echo "<script type='text/javascript' src='".INCLUDES."jscript.js'></script>\n";
echo "</head>\n<body style='margin:0'>\n";

if (isset($_GET['photo_id']) && isnum($_GET['photo_id'])) {
	$result = dbquery(
		"SELECT tp.photo_filename, ta.album_id, ta.album_access FROM ".DB_PHOTOS." tp
		LEFT JOIN ".DB_PHOTO_ALBUMS." ta USING (album_id)
		WHERE photo_id='".$_GET['photo_id']."' GROUP BY tp.photo_id
	");
	if (dbrows($result)) {
		$data = dbarray($result);
		if (!checkgroup($data['album_access'])) {
			redirect(FUSION_SELF);
		} else {
			define("PHOTODIR", PHOTOS.(!SAFEMODE ? "album_".$data['album_id']."/" : ""));
			if ($settings['photo_watermark']==1) {
	         $parts = explode(".", $data['photo_filename']);
	         $wm_file2 = $parts[0]."_w2.".$parts[1];
	         if (!file_exists(PHOTODIR.$wm_file2)) {
		         $photo_file = "photo.php?photo_id=".$_GET['photo_id']."&amp;full";
		      } else {
		         $photo_file = PHOTODIR.$wm_file2;
		      }
			} else {
	         $photo_file = PHOTODIR.$data['photo_filename'];
	      }
			echo "<div style='text-align:center;vertical-align:middle;'><a href=\"javascript:;\" onclick=\"window.close();\"><img src='$photo_file' alt='".$data['photo_filename']."' title='".$locale['458']."' style='border:0px' /></a></div>\n";
		}
	} else {
		echo "<script type='text/javascript'>window.close();</script>\n";
	}
} else {
	redirect("photogallery.php");
}

echo "</body>\n</html>\n";
?>
