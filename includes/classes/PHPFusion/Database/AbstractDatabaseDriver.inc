<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: AbstractDatabaseDriver.inc
| Author: Takács Ákos (Rimelek)
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

/**
 * Abstract class for new database handler classes
 */
abstract class AbstractDatabaseDriver {
    /**
     * This is a MySQL error code
     *
     * http://dev.mysql.com/doc/refman/5.5/en/error-messages-server.html
     */
    const ERROR_UNKNOWN_DATABASE = 1049;

    const PARAM_NULL = 'null';
    const PARAM_INT = 'int';
    const PARAM_STR = 'string';
    const PARAM_BOOL = 'bool';

    /**
     * It stores the SQL queries and execution times sent by every instance
     *
     * @var array
     */
    private static $queries = array();

    /**
     * @var bool
     */
    private $debug = FALSE;

    /**
     * @var string
     */
    private $connectionid;

    /**
     * Connect to the database
     *
     * @param string $host Server domain or IP followed by an optional port definition
     * @param string $user
     * @param string $pass Password
     * @param string $db The name of the database
     * @param array  $options Currently only one option exists: charset
     * @throws SelectionException When the selection of the database was unsuccessful
     * @throws ConnectionException When the connection could not be established
     */
    public function __construct($host, $user, $pass, $db, array $options = array()) {
        $options += array(
            'charset' => 'utf8',
            'connectionid' => ''
        );
        $this->connectionid = $options['connectionid'];
        $this->connect($host, $user, $pass, $db, $options);
    }

    /**
     * Connect to the database
     *
     * @param string $host Server domain or IP followed by an optional port definition
     * @param string $user
     * @param string $pass Password
     * @param string $db The name of the database
     * @param array  $options Currently only one option exists: charset
     * @throws SelectionException When the selection of the database was unsuccessful
     * @throws ConnectionException When the connection could not be established
     */
    abstract protected function connect($host, $user, $pass, $db, array $options = array());

    /**
     * Get the number of queries of all connections
     *
     * @return int
     */
    public static function getGlobalQueryCount() {
        $count = 0;
        foreach (self::getGlobalQueryLog() as $connectionID => $queries) {
            $count = $count + count($queries);
        }
        /**
         * Add warning notice for Site Owners - Core Developers only
         */
        //if (iSUPERADMIN && $count > 30)  addNotice('danger', 'There are too many queries this page. Please reduce it to below 30');
        return $count;
    }

    /**
     * Get all queries of all connections
     *
     * @return array structure: array(
     *        $connectionid => array(
     *            array($time, $sql, $parameters),
     *            //...
     *        ),
     *        //...
     * )
     */
    public static function getGlobalQueryLog() {
        return self::$queries;
    }

    /**
     * Get the summarized execution time of all queries of all connections
     *
     * @return float
     */
    public static function getGlobalQueryTimeSum() {
        $sum = 0;
        foreach (self::$queries as $instance) {
            foreach ($instance as $query) {
                $sum += $query[0];
            }
        }

        return $sum;
    }

    /**
     * Returns the type of the given parameter
     *
     * @param string $parameter
     * @return string
     *    Possible values:
     *    <ul>
     *        <li>{@link AbstractDatabaseDriver::PARAM_INT}</li>
     *        <li>{@link AbstractDatabaseDriver::PARAM_STR}</li>
     *        <li>{@link AbstractDatabaseDriver::PARAM_BOOL}</li>
     *        <li>{@link AbstractDatabaseDriver::PARAM_NULL}</li>
     *    </ul>
     */
    public static function getParameterType($parameter) {
        if ($parameter === NULL) {
            return self::PARAM_NULL;
        } elseif (is_bool($parameter)) {
            return self::PARAM_BOOL;
        } elseif (is_int($parameter) or is_float($parameter)) {
            return self::PARAM_INT;
        }

        return self::PARAM_STR;
    }

    /**
     * Close the connection
     */
    abstract public function close();

    /**
     * @return bool TRUE if the connection is closed
     */
    public function isClosed() {
        return !$this->isConnected();
    }

    /**
     * @return bool TRUE if the connection is alive
     */
    abstract public function isConnected();

    /**
     * @return bool
     */
    public function isDebug() {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug($debug = TRUE) {
        $this->debug = (bool)intval($debug);
    }

    /**
     * Get all queries of this connection
     *
     * @return array structure: array(
     *        $connection_hash => array(
     *            array($time, $sql),
     *            //...
     *        ),
     *        //...
     * )
     */
    public function getQueryLog() {
        return self::$queries[$this->connectionid];
    }

    /**
     * Get the number of queries of this connection
     *
     * @return int
     */
    public function getQueryCount() {
        return count(self::$queries[$this->connectionid]);
    }

    /**
     * Get the summarized execution time of all queries of this connection
     *
     * @return float
     */
    public function getQueryTimeSum() {
        $sum = 0;
        foreach (self::$queries[$this->connectionid] as $query) {
            $sum += $query[0];
        }

        return $sum;
    }

    /**
     * Fetch all rows as associative arrays
     *
     * @param $result
     * @return array
     */
    public function fetchAllAssoc($result) {
        $rows = array();
        while ($row = $this->fetchAssoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Fetch one row as an associative array
     *
     * @param mixed $result The result of a query
     * @return array Associative array
     */
    abstract public function fetchAssoc($result);

    /**
     * Fetch all rows as numeric arrays
     *
     * @param $result
     * @return array
     */
    public function fetchAllRows($result) {
        $rows = array();
        while ($row = $this->fetchRow($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Fetch one row as a numeric array
     *
     * @param mixed $result The result of a query
     * @return array Numeric array
     */
    abstract public function fetchRow($result);

    /**
     * Get the next auto_increment id of a table
     *
     * Try to avoid the use of it! {@link getLastId()} after insert
     * is more secure way to get the id of an existing record than
     * get just a potential id.
     *
     * @param string $table
     * @return int|false
     */
    public function getNextId($table) {
        $status = $this->fetchAssoc($this->query(
            'SELECT auto_increment FROM information_schema.tables WHERE
				table_schema = database() AND
				table_name = :table', array(':table' => $table)));

        return empty($status) ? FALSE : (int)$status['auto_increment'];
    }

    /**
     * Send a database query
     *
     * @param string $query SQL
     * @param array  $parameters
     * @return mixed The result of the query or FALSE on error
     */
    public function query($query, array $parameters = array()) {
        self::$queries[$this->connectionid][] = array(0, $query, $parameters, debug_backtrace());
        $query_time = microtime(TRUE);
        $result = $this->_query($query, $parameters);
        $query_time = round((microtime(TRUE) - $query_time), 7);
        self::$queries[$this->connectionid][count(self::$queries[$this->connectionid]) - 1][0] = $query_time;
        return $result ?: FALSE;
    }

    /**
     * Send a database query
     *
     * This method will be called from AbstractDatabase::query()
     * AbstractDatabase::query() will log the queries and check the
     * execution time.
     *
     * @param string $query SQL
     * @param array  $parameters
     * @return mixed The result of the query or FALSE on error
     */
    abstract protected function _query($query, array $parameters = array());

    /**
     * Count the number of rows in a table filtered by conditions
     *
     * @param string $field Parenthesized field name
     * @param string $table Table name
     * @param string $conditions conditions after "where"
     * @param array  $parameters
     * @return int
     */
    abstract public function count($field, $table, $conditions = "", array $parameters = array());

    /**
     * Fetch the first column of a specific row
     *
     * @param mixed $result The result of a query
     * @param int   $row
     * @return mixed
     */
    abstract public function fetchFirstColumn($result, $row = 0);

    /**
     * Count the number of selected rows by the given query
     *
     * @param mixed $result The result of a query
     * @return int
     */
    abstract public function countRows($result);

    /**
     * Get the last inserted auto increment id
     *
     * @return int
     */
    abstract public function getLastId();

    /**
     * Implementation of \PDO::quote()
     *
     * @see http://php.net/manual/en/pdo.quote.php
     *
     * @param $value
     * @return string
     */
    abstract public function quote($value);

    /**
     * Get the database server version
     *
     * @return string
     */
    abstract public function getServerVersion();

}
