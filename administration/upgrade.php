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
pageAccess("U");

require_once THEMES."templates/admin_header.php";

$settings = fusion_get_settings();

include LOCALE.LOCALESET."admin/upgrade.php";

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'upgrade.php'.fusion_get_aidlink(), 'title' => $locale['400']]);

opentable($locale['400']);

// Execute Gallery migration script if called
if (isset($_GET['migrate_gallery'])) {
    require_once ADMIN."upgrade/gallery_migrate.php";
    echo "<div class='well'>".$locale['700']."</div>";
}

// Execute Forum attachment migration script if called
if (isset($_GET['migrate_forum'])) {
    require_once ADMIN."upgrade/forum_migrate.php";
    echo "<div class='well'>".$locale['701']."</div>";
}

// Execute download migration script if called
if (isset($_GET['migrate_downloads'])) {
    require_once ADMIN."upgrade/downloads_migrate.php";
    echo "<div class='well'>".$locale['702']."</div>";
}

if (str_replace(".", "", $settings['version']) == "90000") {
    echo "<div class='text-center m-b-20'><div class='btn-group'>";

    if (file_exists(IMAGES."photoalbum/index.php")) {
        echo "<a class='btn btn-default' href='".FUSION_SELF.$aidlink."&amp;migrate_gallery'>".$locale['703']."</a>";
    }

    if (file_exists(BASEDIR."forum/attachments/index.php")) {
        echo "<a class='btn btn-default' href='".FUSION_SELF.$aidlink."&amp;migrate_forum'>".$locale['704']."</a>";
    }

    if (file_exists(BASEDIR."downloads/index.php")) {
        echo "<a class='btn btn-default' href='".FUSION_SELF.$aidlink."&amp;migrate_downloads'>".$locale['705']."</a>";
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