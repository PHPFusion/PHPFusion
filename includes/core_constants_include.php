<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
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
defined('IN_FUSION') || exit;

define("ADMIN", BASEDIR."administration/");
define("CLASSES", BASEDIR."includes/classes/");
define("DYNAMICS", BASEDIR."includes/dynamics/");
define("INFUSIONS", BASEDIR."infusions/");
define("IMAGES", BASEDIR."images/");
define("INCLUDES", BASEDIR."includes/");
define("LOCALE", BASEDIR."locale/");
define("THEMES", BASEDIR."themes/");
define("BOILERPLATES", BASEDIR."themes/boilerplates/");
define("SITE_THEMES", BASEDIR."themes/site_themes/");

// @todo convert them all to these...
const TEMPLATES = BASEDIR."themes".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR;
const ADMIN_THEMES = BASEDIR."themes".DIRECTORY_SEPARATOR."admin_themes".DIRECTORY_SEPARATOR;
const ADMIN_TEMPLATES = BASEDIR."themes".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."global".DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR;
const DB_HANDLERS = BASEDIR."includes".DIRECTORY_SEPARATOR."db_handlers".DIRECTORY_SEPARATOR;

define("FUSION_IP", !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
define("QUOTES_GPC", (ini_get('magic_quotes_gpc') ? TRUE : FALSE));
define("USER_IP", $_SERVER['REMOTE_ADDR']);
define("WIDGETS", BASEDIR."widgets/");
// Define script start time
define("START_TIME", microtime(TRUE));
define("FUSION_ROOT_DIR", dirname(__DIR__).'/');
define("TIME", time());
define('PRELOADER', '<div class="fusion-pre-loader"></div>');
// Define user levels
const USER_LEVEL_SUPER_ADMIN = -103;
const USER_LEVEL_ADMIN = -102;
const USER_LEVEL_MEMBER = -101;
const USER_LEVEL_PUBLIC = 0;

// Decimal Level for Relationship Status
const USER_LEVEL_PASSWORD = "-101.999";
const USER_LEVEL_PRIVATE = "-101.999";
const USER_LEVEL_FAMILY = "-101.3";
const USER_LEVEL_FRIENDS = "-101.2";
const USER_LEVEL_MUTUAL_FRIENDS = "-101.1";
