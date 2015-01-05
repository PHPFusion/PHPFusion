<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: includes/core_functions_include.php
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

require __DIR__.'/core_functions_include.php';
require fusion_detect_installation();
require __DIR__.'/core_constants_include.php';
require DB_HANDLERS.(intval($pdo_enabled) === 1 ? 'pdo' : 'mysql')."_functions_include.php";
require __DIR__."/system_images.php";