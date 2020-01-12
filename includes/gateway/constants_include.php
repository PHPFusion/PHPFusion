<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: constants_include.php
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

define("SCRIPT_ROOT", __DIR__);

// number of allowed page requests for the user
define("CONTROL_MAX_REQUESTS", 2);

// time interval to start counting page requests (seconds)
define("CONTROL_REQ_TIMEOUT", 1);

// seconds to punish the user who has exceeded in doing requests
define("CONTROL_BAN_TIME", 120 * 120);

// writable directory to keep script data
define("SCRIPT_TMP_DIR", SCRIPT_ROOT."/flood");

define("CONTROL_DB", SCRIPT_TMP_DIR."/ctrl");
define("CONTROL_LOCK_DIR", SCRIPT_TMP_DIR."/lock");
define("CONTROL_LOCK_FILE", CONTROL_LOCK_DIR."/".md5(USER_IP));
