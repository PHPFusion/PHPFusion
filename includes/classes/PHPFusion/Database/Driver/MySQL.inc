<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: MySQL.inc
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
namespace PHPFusion\Database\Driver;

use PHPFusion\Database\AbstractDatabaseDriver;
use PHPFusion\Database\Exception\ConnectionException;
use PHPFusion\Database\Exception\SelectionException;

class MySQL extends AbstractDatabaseDriver {
    /**
     * @var resource|NULL
     */
    private $connection = NULL;

    /**
     * Close the connection
     */
    public function close() {
        if ($this->isConnected()) {
            mysql_close($this->connection);
        }
    }

    /**
     * @return bool TRUE if the connection is alive
     */
    public function isConnected() {
        return is_resource($this->connection);
    }

    /**
     * Count the number of rows in a table filtered by conditions
     *
     * @param string $field      Parenthesized field name
     * @param string $table      Table name
     * @param string $conditions conditions after "where"
     * @param array  $parameters
     *
     * @return int
     */
    public function count($field, $table, $conditions = "", array $parameters = []) {
        $cond = ($conditions ? " WHERE ".$conditions : "");
        $sql = "SELECT COUNT".$field." FROM ".$table.$cond;
        $result = $this->query($sql, $parameters);

        return $result ? $this->fetchFirstColumn($result) : FALSE;
    }

    /**
     * Fetch the first column of a specific row
     *
     * @param mixed $result The result of a query
     * @param int   $row
     *
     * @return mixed
     */
    public function fetchFirstColumn($result, $row = 0) {
        $value = mysql_result($result, 0);

        return $value ?: FALSE;
    }

    /**
     * Count the number of selected rows by the given query
     *
     * @param mixed $result The result of a query
     *
     * @return int
     */
    public function countRows($result) {
        return @mysql_num_rows($result);
    }

    /**
     * Count the number of selected columns by the given query
     *
     * @param mixed $result The result of a query
     *
     * @return int
     */
    public function countColumns($result) {
        return @mysql_num_fields($result);
    }

    /**
     * Fetch one row as an associative array
     *
     * @param mixed $result The result of a query
     *
     * @return array|bool Associative array
     */
    public function fetchAssoc($result) {
        $row = @mysql_fetch_assoc($result);

        return $row ?: FALSE;
    }

    /**
     * Fetch one row as a numeric array
     *
     * @param mixed $result The result of a query
     *
     * @return array|bool Numeric array
     */
    public function fetchRow($result) {
        $row = @mysql_fetch_row($result);

        return $row ?: FALSE;
    }

    /**
     * Get the last inserted auto increment id
     *
     * @return int
     */
    public function getLastId() {
        return (int)mysql_insert_id($this->connection);
    }

    /**
     * Get the database server version
     *
     * @return string
     */
    public function getServerVersion() {
        return mysql_get_server_info($this->connection);
    }

    /**
     * Connect to the database
     *
     * @param string $host    Server domain or IP followed by an optional port definition
     * @param string $user
     * @param string $pass    Password
     * @param string $db      The name of the database
     * @param array  $options Currently only one option exists: charset
     *
     * @throws SelectionException When the selection of the database was unsuccessful
     * @throws ConnectionException When the connection could not be established
     */
    protected function connect($host, $user, $pass, $db, array $options = []) {
        $options += [
            'charset' => 'utf8mb4',
            'port'    => 3306
        ];
        $this->connection = @mysql_connect($host, $user, $pass);
        if (!$this->connection) {
            throw new ConnectionException(mysql_error($this->connection), mysql_errno($this->connection));
        }
        mysql_set_charset($options['charset'], $this->connection);

        if (!@mysql_select_db($db, $this->connection)) {
            throw new SelectionException(mysql_error($this->connection), mysql_errno($this->connection));
        }
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
     *
     * @return mixed The result of the query or FALSE on error
     */
    protected function _query($query, array $parameters = []) {
        if ($parameters) {
            foreach ($parameters as $k => $parameter) {
                $parameters[$k] = $this->quote($parameter);
            }
            $query = strtr($query, $parameters);
        }
        $result = mysql_query($query, $this->connection);
        if (!$result) {
            trigger_error(mysql_error($this->connection)." <strong>@ ".$query."</strong>", E_USER_ERROR);
        }

        return $result ?: FALSE;
    }

    /**
     * Implementation of \PDO::quote()
     *
     * @see http://php.net/manual/en/pdo.quote.php
     *
     * @param $value
     *
     * @return string
     */
    public function quote($value) {
        $type = $this->getParameterType($value);
        if (self::PARAM_NULL === $type) {
            return 'NULL';
        } else if (self::PARAM_BOOL === $type) {
            return $value ? 'TRUE' : 'FALSE';
        } else if (self::PARAM_INT === $type) {
            return $value;
        }

        return "'".mysql_real_escape_string(strval($value), $this->connection)."'";
    }

}
