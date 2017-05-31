<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: PDOMySQL.inc
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
namespace PHPFusion\Database\Driver;

use PDO;
use PDOException;
use PDOStatement;
use PHPFusion\Database\AbstractDatabaseDriver;
use PHPFusion\Database\Exception\ConnectionException;
use PHPFusion\Database\Exception\SelectionException;

class PDOMySQL extends AbstractDatabaseDriver {

    private static $paramTypeMap = array(
        self::PARAM_INT => PDO::PARAM_INT,
        self::PARAM_BOOL => PDO::PARAM_BOOL,
        self::PARAM_STR => PDO::PARAM_STR,
        self::PARAM_NULL => PDO::PARAM_NULL
    );
    /**
     * @var \PDO
     */
    private $connection = NULL;

    /**
     * Close the connection
     */
    public function close() {
        $this->connection = NULL;
    }

    /**
     * @return bool TRUE if the connection is alive
     */
    public function isConnected() {
        return $this->connection instanceof PDO;
    }

    /**
     * Send a database query
     *
     * @param string $query SQL
     * @param array  $parameters
     * @return PDOStatement or FALSE on error
     */
    public function _query($query, array $parameters = array()) {
        try {
            $result = $this->connection->prepare($query);
            foreach ($parameters as $key => $parameter) {
                $result->bindValue($key, $parameter, self::$paramTypeMap[self::getParameterType($parameter)]);
            }
            $result->execute();

            return $result;
        } catch (PDOException $e) {
            trigger_error("Query Error: ".$query."<br/>Stack Trace: ".$e->getTraceAsString()."<br/>Error Nature: ".$e->getMessage(), E_USER_NOTICE);
            return FALSE;
        }
    }

    /**
     * Count the number of rows in a table filtered by conditions
     *
     * @param string $field Parenthesized field name
     * @param string $table Table name
     * @param string $conditions conditions after "where"
     * @param array  $parameters
     * @return int
     */
    public function count($field, $table, $conditions = "", array $parameters = array()) {
        $cond = ($conditions ? " WHERE ".$conditions : "");
        $sql = "SELECT COUNT".$field." FROM ".$table.$cond;
        $statement = $this->query($sql, $parameters);

        return $statement ? $statement->fetchColumn() : FALSE;
    }

    /**
     * Fetch the first column of a specific row
     *
     * @param \PDOStatement $statement
     * @param int           $row
     * @return mixed
     */
    public function fetchFirstColumn($statement, $row = 0) {
        //seek
        if ($statement !==FALSE) {
            for ($i = 0; $i < $row; $i++) {
                $statement->fetchColumn();
            }
            //returns false when an error occurs
            return $statement->fetchColumn();
        }
    }

    /**
     * Count the number of affected rows by the given query
     *
     * @param \PDOStatement $statement
     * @return int
     */
    public function countRows($statement) {
        if ($statement !== FALSE) {
            return $statement->rowCount();
        }
    }

    /**
     * Fetch one row as an associative array
     *
     * @param \PDOStatement $statement
     * @return array Associative array
     */
    public function fetchAssoc($statement) {
        if ($statement) {
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            return $statement->fetch();
        }
    }

    /**
     * Fetch one row as a numeric array
     *
     * @param \PDOStatement $statement
     * @return array Numeric array
     */
    public function fetchRow($statement) {
        if ($statement !== FALSE) {
            $statement->setFetchMode(PDO::FETCH_NUM);

            return $statement->fetch();
        }
    }

    /**
     * Get the last inserted auto increment id
     *
     * @return int
     */
    public function getLastId() {
        return (int)$this->connection->lastInsertId();
    }

    /**
     * Implementation of \PDO::quote()
     *
     * @see http://php.net/manual/en/pdo.quote.php
     *
     * @param $value
     * @return string
     */
    public function quote($value) {
        return $this->connection->quote($value);
    }

    /**
     * Get the database server version
     *
     * @return string
     */
    public function getServerVersion() {
        return $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
     * Connect to the database
     *
     * @param string $host Server domain or IP followed by an optional port definition
     * @param string $user
     * @param string $pass Password
     * @param string $db   The name of the database
     * @param array  $options
     *
     * @throws \ErrorException  When the connection could not be established
     */
    protected function connect($host, $user, $pass, $db, array $options = array()) {
        if ($this->connection === NULL) {
            $options += array(
                'charset' => 'utf8',
            );
            try {
                $pdo = $this->connection = new PDO("mysql:host=".$host.";dbname=".$db.";charset=".$options['charset'], $user, $pass,
                    array(
                        /*
                         * Inserted to solve the issue of the ignored charset in the connection string.
                         * DO NOT REMOVE THE CHARSET FROM THE CONNECTION STRING. That is still needed!
                         */
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$options['charset'].', sql_mode=ALLOW_INVALID_DATES'
                    )
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
                $pdo->setAttribute(PDO::ATTR_PERSISTENT, FALSE);
            } catch (PDOException $error) {
                //$file = debug_print_backtrace();
                throw $error->getCode() === self::ERROR_UNKNOWN_DATABASE
                    ? new SelectionException($error->getMessage(), $error->getCode(), $error)
                    : new \ErrorException($error->getMessage(), $error->getCode(), 1, debug_backtrace()[1]['file']); //new ConnectionException($error->getMessage(), $error->getCode(), $error);
            }
        }
    }

}
