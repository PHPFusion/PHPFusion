<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: DatabaseFactory.inc
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
namespace PHPFusion\Database;

use PHPFusion\Database\Exception\UndefinedConfigurationException;

class DatabaseFactory {

    /**
     * use mysql_* functions
     */
    const DRIVER_MYSQLi = 'mysqli';

    /**
     * use mysql_* functions
     */
    const DRIVER_MYSQL = 'mysql';

    /**
     * use \PDO class
     */
    const DRIVER_PDO_MYSQL = 'pdo_mysql';

    /**
     * Use the default driver (pdo_mysql)
     */
    const DRIVER_DEFAULT = self::DRIVER_PDO_MYSQL;

    /**
     * MySQL, MySQLi or PDOMySQL
     *
     * @var string
     */
    private static $defaultDriver = self::DRIVER_DEFAULT;

    /**
     * @var bool|array Array of connection IDs or TRUE to debug all connections
     */
    private static $debug = [];

    /**
     * @var string[]
     */
    private static $driverClasses = [
        self::DRIVER_MYSQL     => '\PHPFusion\Database\Driver\MySQL',
        self::DRIVER_MYSQLi    => '\PHPFusion\Database\Driver\MySQLi',
        self::DRIVER_PDO_MYSQL => '\PHPFusion\Database\Driver\PDOMySQL'
    ];

    /**
     * @var Configuration[]
     */
    private static $configurations = [];

    /**
     * @var string
     */
    private static $defaultConnectionID = 'default';

    /**
     * @var AbstractDatabaseDriver[]
     */
    private static $connections = [];

    /**
     * @param string $id
     * @param string $fullClassName
     */
    public static function registerDriverClass($id, $fullClassName) {
        if (is_subclass_of($fullClassName, __NAMESPACE__.'\AbstractDatabaseDriver')
            and !isset(self::$driverClasses[$id])
        ) {
            self::$driverClasses[$id] = $fullClassName;
        }
    }

    /**
     * @param string $file
     */
    public static function registerConfigurationFromFile($file) {
        if (is_file($file)) {
            $configurations = require $file;
            if (is_array($configurations)) {
                DatabaseFactory::registerConfigurations($configurations);
            }
            // TODO Exception otherwise
        }
        // TODO Exception otherwise
    }

    /**
     * @param array $configurations
     */
    public static function registerConfigurations($configurations) {
        foreach ($configurations as $id => $configuration) {
            self::registerConfiguration($id, $configuration);
        }
    }

    /**
     * @param int   $id
     * @param array $configuration
     */
    public static function registerConfiguration($id, array $configuration) {
        $lowerCaseID = strtolower($id);
        if (!isset(self::$configurations[$lowerCaseID])) {
            $configuration += ['debug' => self::isDebug($id)];
            self::$configurations[$lowerCaseID] = new Configuration($configuration);
        }
    }

    /**
     * @param string $connectionid
     *
     * @return bool
     */
    public static function isDebug($connectionid = NULL) {
        if ($connectionid) {
            return (isset(self::$connections[$connectionid])
                ? self::$connections[$connectionid]->isDebug()
                : (self::$debug === TRUE
                    or (is_array(self::$debug) and in_array($connectionid, self::$debug))));
        }
        $connectionids = array_unique(array_merge(
            is_array(self::$debug)
                ? self::$debug : [], array_keys(self::$connections)));

        /**@var $connections AbstractDatabaseDriver */
        foreach ($connectionids as $k => $id) {
            if (isset(self::$connections[$id]) and !self::$connections[$id]->isDebug()) {
                unset($connectionids[$k]);
            }
        }

        return (bool)$connectionids;
    }

    /**
     * @param bool|array $debug
     */
    public static function setDebug($debug = TRUE) {
        self::$debug = is_array($debug) ? $debug : (bool)$debug;
    }

    /**
     * Get the database connection object
     *
     * @param string $id
     *
     * @throws UndefinedConfigurationException
     * @return AbstractDatabaseDriver
     */
    public static function getConnection($id = NULL) {
        $id = strtolower($id ?: self::getDefaultConnectionID());
        if (!isset(self::$configurations[$id])) {
            throw new UndefinedConfigurationException("Unknown configuration id: ".$id);
        }
        if (isset(self::$connections[$id]) and self::$connections[$id]->isConnected()) {
            return self::$connections[$id];
        }
        $conf = self::$configurations[$id];
        self::connect($conf->getHost(), $conf->getUser(), $conf->getPassword(), $conf->getDatabase(), [
            'driver'       => $conf->getDriver(),
            'connectionid' => $id,
            'debug'        => $conf->isDebug()
        ]);

        return self::$connections[$id];
    }

    /**
     * @return string
     */
    public static function getDefaultConnectionID() {
        return self::$defaultConnectionID;
    }

    /**
     * Connect to the database using the default driver
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $db
     * @param array  $options
     *
     * @return AbstractDatabaseDriver
     * @throws Exception\SelectionException
     * @throws Exception\ConnectionException
     */
    public static function connect($host, $user, $password, $db, array $options = []) {
        $configuration = new Configuration();
        $options += [
            'charset'      => $configuration->getCharset(),
            'driver'       => $configuration->getDriver(),
            'connectionid' => self::getDefaultConnectionID(),
            'debug'        => $configuration->isDebug()
        ];
        $id = strtolower($options['connectionid']);
        if (empty(self::$connections[$id])) {
            if (!isset(self::$configurations[$id])) {
                self::registerConfiguration($id, $configuration->toArray());
            }
            if (!isset(self::$connections[$id]) or self::$connections[$id]->isClosed()) {
                $class = self::getDriverClass(strtolower($options['driver']));
                /**@var $connection AbstractDatabaseDriver */
                $connection = new $class($host, $user, $password, $db, $options);
                $connection->setDebug($options['debug']);
                self::$connections[$id] = $connection;
            }
        }

        return self::$connections[$id];
    }

    /**
     * @param string $id
     *
     * @return null|string
     */
    public static function getDriverClass($id = NULL) {
        if ($id === NULL) {
            $id = self::getDefaultDriver();
        }

        return isset(self::$driverClasses[$id]) ? self::$driverClasses[$id] : NULL;
    }

    /**
     * @return string
     */
    public static function getDefaultDriver() {
        return self::$defaultDriver;
    }

    /**
     * @param string $defaultDriver
     */
    public static function setDefaultDriver($defaultDriver) {
        self::$defaultDriver = $defaultDriver;
    }
}
