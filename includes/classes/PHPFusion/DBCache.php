<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: DBCache.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;

use PHPFusion\Cache\Cache;
use PHPFusion\Database\DatabaseFactory;

/**
 * Class DBCache
 * This class utilizes memory caching for database results.
 */
class DBCache {

    /**
     * Instance ID
     *
     * @var
     */
    private static $connect_id;

    /**
     * Instances
     *
     * @var
     */
    private static $connections;

    /**
     * Associative array return counter
     *
     * @var int
     */
    private static $array_counter = 1;

    private $seconds = 120;

    /**
     * @param string $connection
     *
     * @return mixed|static
     */
    public static function getInstance($connection = 'default') {

        self::$connect_id = $connection;

        if (empty(self::$connections)) {
            self::$connections[$connection] = new static;
        }

        return self::$connections[$connection];
    }

    /**
     * Runs the flush command
     * Clears out all cached results
     */
    public function flush() {
        if (Cache::getInstance()->isConnected()) {
            Cache::getInstance()->flush();
        }
    }

    /**
     * @param string $key
     */
    public function delete($key) {
        if (Cache::getInstance()->isConnected()) {
            Cache::getInstance()->delete($key);
        }
    }

    /**
     * @param int $seconds
     */
    public function setSeconds($seconds = 120) {
        $this->seconds = $seconds;
    }

    /**
     * Cached query
     *
     * @param string $key
     * @param string $query
     * @param array  $parameters
     *
     * @return false|int|mixed
     */
    public function dbquery($key, $query, $parameters) {

        self::$array_counter = 0;

        if (Cache::getInstance()->isConnected()) {
            if (!empty(Cache::getInstance()->get($key))) {
                return Cache::getInstance()->get($key);
            }

            try {
                $dbfactory = DatabaseFactory::getConnection(self::$connect_id);

                $query = $dbfactory->query($query, $parameters);

                $cache = [
                    "rows"     => $dbfactory->countRows($query),
                    "array"    => $dbfactory->fetchAllAssoc($query),
                    "arraynum" => $dbfactory->fetchRow($query)
                ];

                Cache::getInstance()->set($key, $cache, $this->seconds);

                return $cache;
            } catch (\Exception $e) {
                set_error(E_CORE_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());

                return NULL;
            }
        } else {
            $dbfactory = DatabaseFactory::getConnection(self::$connect_id);

            $query = $dbfactory->query($query, $parameters);

            return [
                "rows"     => $dbfactory->countRows($query),
                "array"    => $dbfactory->fetchAllAssoc($query),
                "arraynum" => $dbfactory->fetchRow($query)
            ];
        }
    }

    /**
     * Return number of rows
     *
     * @param mixed $result
     *
     * @return int
     */
    public function dbrows($result) {
        if (is_array($result) && isset($result["rows"])) {
            return (int)$result["rows"];
        }
        return 0;
    }

    /**
     * Returns associative object array
     *
     * @param mixed $result
     *
     * @return array|null
     */
    public function dbarray($result) {
        if (is_array($result) && isset($result["array"])) {
            if (isset($result["array"][self::$array_counter])) {
                $results = $result["array"][self::$array_counter];
                self::$array_counter++;
                return $results;
            }
        }
        return NULL;
    }

    /**
     * @param mixed $result
     *
     * @return array|mixed
     */
    public function dbarraynum($result) {
        if (is_array($result) && isset($result["arraynum"])) {
            return $result["arraynum"];
        }
        return [];
    }

    /**
     * @param mixed  $result
     * @param string $column
     *
     * @return mixed
     */
    public function dbresult($result, $column) {
        if (is_array($result) && isset($result["array"])) {
            if (isset($result["array"][self::$array_counter])) {
                $results = $result["array"][self::$array_counter];
                $keys = array_flip(array_keys($results));
                if (isset($keys[$column])) {
                    $column_name = $keys[$column];
                    return $results[$column_name];
                }
            }
        }
        return NULL;
    }

}
