<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
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
defined('IN_FUSION') || exit;

// Better to call this file as core_runtime_include.php
use PHPFusion\Database\DatabaseFactory;

require_once __DIR__.'/autoloader.php';
require_once fusion_detect_installation();
require_once __DIR__.'/multisite_include.php';

// Database handler functions
/**
 * Documentation:
 * Default connection id = "default";
 * To debug sql statements (showing sql queries) insert either of the following line before "require_once footer.php"
 * $log = PHPFusion\Database\Driver\MySQL::getGlobalQueryLog();
 * $log = PHPFusion\Database\Driver\PDOMySQL::getGlobalQueryLog();
 *
 * @todo: missing doc - usage to set true false otherwise.. ?
 * @todo: add a form_select("debug_sql", "Debug SQL?", fusion_get_settings("debug_sql"), array(
 *      "options" => array($locale['disable'], $locale['enable']),
 *        "inline"=>true)); into administration/security_settings.php
 */
DatabaseFactory::setDefaultDriver((!empty($db_driver) && $db_driver === 'pdo' || !empty($pdo_enabled) && $pdo_enabled === 1) ? DatabaseFactory::DRIVER_PDO_MYSQL : DatabaseFactory::DRIVER_MYSQLi);
DatabaseFactory::registerConfiguration(DatabaseFactory::getDefaultConnectionID(), [
    'host'     => $db_host,
    'user'     => $db_user,
    'password' => $db_pass,
    'database' => $db_name,
    'charset'  => 'utf8mb4',
    'debug'    => DatabaseFactory::isDebug(DatabaseFactory::getDefaultConnectionID())
]);
DatabaseFactory::registerConfigurationFromFile(__DIR__.'/../config.db.php');
require_once DB_HANDLERS."all_functions_include.php";
require_once __DIR__."/system_images.php";
require_once __DIR__."/output_handling_include.php";
require_once __DIR__."/translate_include.php";
require_once __DIR__."/sqlhandler.inc.php";
require_once __DIR__."/defender.inc";
require_once __DIR__."/dynamics.inc";
