<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Material/acp_search.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once '../../../maincore.php';
define('MDT', THEMES.'admin_themes/Material/');

if (!defined('MDT_LOCALE')) {
    if (file_exists(THEMES.'admin_themes/Material/locale/'.LANGUAGE.'.php')) {
        define('MDT_LOCALE', THEMES.'admin_themes/Material/locale/'.LANGUAGE.'.php');
    } else {
        define('MDT_LOCALE', THEMES.'admin_themes/Material/locale/English.php');
    }
}

require_once MDT.'acp_autoloader.php';

new Material\Search();
exit();
