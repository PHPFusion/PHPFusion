<?php
// user can edit this for now.
$table_prefix = 'wordpress_';

// Not sure if Rimelek's auto connection module requires User and Password to external db.
$user_name = 'root';
$user_password = 'root';

/**
 * Then we do auto definition
 */

define("WORDPRESS_USER_DB", $table_prefix."table_name");