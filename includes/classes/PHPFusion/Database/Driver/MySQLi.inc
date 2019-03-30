<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: MySQLi.inc
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

class MySQLi extends AbstractDatabaseDriver {
    /**
     * @var \mysqli|NULL
     */
    private $connection = NULL;

    /**
     * Close the connection
     */
    public function close() {
        if ($this->isConnected()) {
            mysqli_close($this->connection);
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
        $result->data_seek($row);
        $value = mysqli_fetch_row($result);
        if (isset($value[0])) {
            return $value[0];
        }

        return FALSE;

        //return mysqli_fetch_row($result) ? mysqli_fetch_row($result): FALSE;
    }

    /**
     * Count the number of selected rows by the given query
     *
     * @param mixed $result The result of a query
     *
     * @return int
     */
    public function countRows($result) {
        if (is_object($result)) {
            return $result->num_rows ?: 0;
        }

        return 0;
    }

    /**
     * Count the number of selected columns by the given query
     *
     * @param mixed $result The result of a query
     *
     * @return int
     */
    public function countColumns($result) {
        if (is_object($result)) {
            return $result->field_count ?: 0;
        }

        return 0;
    }

    /**
     * Fetch one row as an associative array
     *
     * @param mixed $result The result of a query
     *
     * @return array|bool Associative array
     */
    public function fetchAssoc($result) {
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row;
        }

        return FALSE;
    }

    /**
     * Fetch one row as a numeric array
     *
     * @param mixed $result The result of a query
     *
     * @return array|bool Numeric array
     */
    public function fetchRow($result) {
        $row = mysqli_fetch_row($result);

        return $row ?: FALSE;
    }

    /**
     * Get the last inserted auto increment id
     *
     * @return int
     */
    public function getLastId() {
        return (int)mysqli_insert_id($this->connection);
    }

    /**
     * Get the database server version
     *
     * @return string
     */
    public function getServerVersion() {
        return mysqli_get_server_info($this->connection);
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
        if ($this->connection === NULL) {
            $options += [
                'charset' => 'utf8mb4',
                'port'    => 3306
            ];

            $this->connection = mysqli_connect($host, $user, $pass, $db, $options['port']);
            if (!$this->connection) {
                throw new ConnectionException(mysqli_error($this->connection), mysqli_errno($this->connection));
            }
            mysqli_set_charset($this->connection, $options['charset']);
            if (!@mysqli_select_db($this->connection, "$db")) {
                throw new SelectionException(mysqli_error($this->connection), mysqli_errno($this->connection));
            }
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
        $result = mysqli_query($this->connection, $query);
        if (!$result) {
            trigger_error(mysqli_error($this->connection)." <strong>@ ".$query."</strong>", E_USER_NOTICE);
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
        $type = self::getParameterType($value);

        if (self::PARAM_NULL === $type) {
            return 'NULL';
        } else if (self::PARAM_BOOL === $type) {
            return $value ? 'TRUE' : 'FALSE';
        } else if (self::PARAM_INT === $type) {
            return $value;
        }

        return "'".mysqli_real_escape_string($this->connection, strval($value))."'";
    }

}
