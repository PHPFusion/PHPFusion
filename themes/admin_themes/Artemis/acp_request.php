<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: acp_request.php
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

// For PHP-Fusion
require_once "../../../maincore.php";
require_once "autoloader.php";

/**
 * GET search parameter
 * Required - (string) aid
 * Required - (string) appString (min length 2)
 */
$request = new \Artemis\Subcontroller\get_apps();
exit();
