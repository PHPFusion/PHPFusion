<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: latest_downloads_panel.php
| Author: Nick Jones (Digitanium)
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

openside($locale['global_032']);
$result = dbquery(
		"SELECT td.download_id, td.download_title, td.download_cat, td.download_datestamp,
				tc.download_cat_id, tc.download_cat_access 
			FROM ".DB_DOWNLOADS." td
			INNER JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
			".(iSUPERADMIN ? "" : " WHERE ".groupaccess('download_cat_access'))." 
			ORDER BY download_datestamp DESC LIMIT 0,5");
if (dbrows($result)) {
	while($data = dbarray($result)) {
		$download_title = trimlink($data['download_title'], 23);
		echo THEME_BULLET." <a href='".BASEDIR."downloads.php?download_id=".$data['download_id']."' title='".$data['download_title']."' class='side'>".$download_title."</a><br />\n";
	}
} else {
	echo "<div style='text-align:center'>".$locale['global_033']."</div>\n";
}
closeside();
?>