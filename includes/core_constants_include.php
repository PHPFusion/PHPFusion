<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: includes/core_constants_include.php
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

// Path definitions
define("BASEDIR", dirname(fusion_get_relative_path_to_config()).'/');
define("ADMIN", BASEDIR."administration/");
define("CLASSES", BASEDIR."includes/classes/");
define("DOWNLOADS", BASEDIR."downloads/");
define("IMAGES", BASEDIR."images/");
define("IMAGES_A", IMAGES."articles/");
define("IMAGES_N", IMAGES."news/");
define("IMAGES_N_T", IMAGES."news/thumbs/");
define("IMAGES_NC", IMAGES."news_cats/");
define("IMAGES_B", IMAGES."blog/");
define("IMAGES_B_T", IMAGES."blog/thumbs/");
define("IMAGES_BC", IMAGES."blog_cats/");
define("RANKS", IMAGES."ranks/");
define("INCLUDES", BASEDIR."includes/");
define("LOCALE", BASEDIR."locale/");
define("FORUM", BASEDIR."forum/");
define("INFUSIONS", BASEDIR."infusions/");
define("PHOTOS", IMAGES."photoalbum/");
define("SHOP", BASEDIR."eshop/");
define("THEMES", BASEDIR."themes/");
define("DB_HANDLERS", BASEDIR."includes/db_handlers/");

require_once __DIR__.'/multisite_include.php';