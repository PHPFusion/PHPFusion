<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: upgrade.php
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
require_once "../maincore.php";
if (!checkrights("U") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";

if (file_exists(LOCALE.LOCALESET."admin/upgrade.php")) {
	include LOCALE.LOCALESET."admin/upgrade.php";
} else {
	include LOCALE."English/admin/upgrade.php";
}


opentable($locale['400']);

// Execute Gallery migration script if called
if (isset($_GET['migrate_gallery'])) {
require_once ADMIN."upgrade/gallery_migrate.php";
echo "<div class='well'>Your Photoalbums have been moved</div>";
}

// Execute Forum attachment migration script if called
if (isset($_GET['migrate_forum'])) {
require_once ADMIN."upgrade/forum_migrate.php";
echo "<div class='well'>Your Forum attachments have been moved</div>";
}

// Execute download migration script if called
if (isset($_GET['migrate_downloads'])) {
require_once ADMIN."upgrade/downloads_migrate.php";
echo "<div class='well'>Your downloads have been moved</div>";
}

if (str_replace(".", "", $settings['version']) == "90000") {
echo "<div class='text-center m-b-20'><div class='btn-group'>";

	if (file_exists(IMAGES."photoalbum/index.php")) {
		echo "<a class='btn btn-default' href='".FUSION_SELF.$aidlink."&amp;migrate_gallery'>Migrate Albums to 9 folder</a>";
	}

	if (file_exists(BASEDIR."forum/attachments/index.php")) {
		echo "<a class='btn btn-default' href='".FUSION_SELF.$aidlink."&amp;migrate_forum'>Migrate forum attachments to 9 folder</a>";
	}
	
	if (file_exists(BASEDIR."downloads/index.php")) {
		echo "<a class='btn btn-default' href='".FUSION_SELF.$aidlink."&amp;migrate_downloads'>Migrate downloads to 9 folder</a>";
	}

echo "</div></div>";
}

if (str_replace(".", "", $settings['version']) < "9001") {

// We provide an empty upgrade with only migration buttons as default, upgrade files are in separate folders for 7.

} else {
	echo "<div class='well text-center'>".$locale['401']."</div>\n";
}

closetable();
require_once THEMES."templates/footer.php";