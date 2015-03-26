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

use PHPFusion\Database\DatabaseFactory;

require_once __DIR__.'/autoloader.php';

require_once __DIR__.'/core_functions_include.php';
require_once fusion_detect_installation();
require_once __DIR__.'/core_constants_include.php';
require_once __DIR__.'/multisite_include.php';

// TODO: Remove this check and keep only the new version
if (isset($OOPDBLayer) and $OOPDBLayer === TRUE) {
	//New database handler functions based on enhanced OO solution
	DatabaseFactory::setDefaultDriver(intval($pdo_enabled) === 1 ? DatabaseFactory::DRIVER_PDO_MYSQL : DatabaseFactory::DRIVER_MYSQL);
	DatabaseFactory::registerConfiguration(DatabaseFactory::getDefaultConnectionID(), array(
		'host' => $db_host,
		'user' => $db_user,
		'password' => $db_pass,
		'database' => $db_name,
		'debug' => DatabaseFactory::isDebug(DatabaseFactory::getDefaultConnectionID())
	));
	DatabaseFactory::registerConfigurationFromFile(__DIR__.'/../config.db.php');
	require_once DB_HANDLERS."all_functions_include.php";
} else {
	//old database handler functions
	require_once DB_HANDLERS.(intval($pdo_enabled) === 1 ? 'pdo' : 'mysql')."_functions_include.php";
}
require_once __DIR__."/system_images.php";

require_once __DIR__."/output_handling_include.php";
require_once __DIR__."/translate_include.php";
require_once __DIR__."/notify/notify.inc.php";
require_once __DIR__."/sqlhandler.inc.php";
require_once __DIR__."/defender.inc.php";
require_once __DIR__."/dynamics/dynamics.inc.php";