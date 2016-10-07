<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: wordpress_multisite.php
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
// user can edit this for now.
$table_prefix = 'wordpress_';

// Not sure if Rimelek's auto connection module requires User and Password to external db.
$user_name = 'root';
$user_password = 'root';

/**
 * Then we do auto definition
 */

define("WORDPRESS_USER_DB", $table_prefix."table_name");