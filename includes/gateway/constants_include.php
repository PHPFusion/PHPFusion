<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: constants_include.php
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

const SCRIPT_ROOT = __DIR__;

// number of allowed page requests for the user
const CONTROL_MAX_REQUESTS = 2;

// time interval to start counting page requests (seconds)
const CONTROL_REQ_TIMEOUT = 1;

// seconds to punish the user who has exceeded in doing requests
const CONTROL_BAN_TIME = 120 * 120;

// writable directory to keep script data
const SCRIPT_TMP_DIR = SCRIPT_ROOT."/flood";

const CONTROL_DB = SCRIPT_TMP_DIR."/ctrl";
const CONTROL_LOCK_DIR = SCRIPT_TMP_DIR."/lock";
define("CONTROL_LOCK_FILE", CONTROL_LOCK_DIR."/".md5(USER_IP));
