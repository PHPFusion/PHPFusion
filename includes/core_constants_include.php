<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: core_constants_include.php
| Author: Core Development Team
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

const ADMIN = BASEDIR.'administration/';
const CLASSES = BASEDIR.'includes/classes/';
const DYNAMICS = BASEDIR.'includes/dynamics/';
const INFUSIONS = BASEDIR.'infusions/';
const IMAGES = BASEDIR.'images/';
const INCLUDES = BASEDIR.'includes/';
const LOCALE = BASEDIR.'locale/';
const THEMES = BASEDIR.'themes/';
const TEMPLATES = BASEDIR.'themes/templates/';
const ADMIN_THEMES = BASEDIR.'themes/admin_themes/';
const DB_HANDLERS = BASEDIR.'includes/db_handlers/';
const WIDGETS = BASEDIR.'widgets/';

const FUSION_FOOTER = TEMPLATES.'footer.php';
const FUSION_HEADER = TEMPLATES.'header.php';
const FUSION_ADMIN_HEADER = TEMPLATES.'admin_header.php';

define('FUSION_IP', $_SERVER['REMOTE_ADDR']);
define('USER_IP', $_SERVER['REMOTE_ADDR']);
define('START_TIME', microtime(TRUE));
define('FUSION_ROOT_DIR', dirname(__DIR__).'/');

// Define user levels
const USER_LEVEL_SUPER_ADMIN = -103;
const USER_LEVEL_ADMIN = -102;
const USER_LEVEL_MEMBER = -101;
const USER_LEVEL_PUBLIC = 0;
