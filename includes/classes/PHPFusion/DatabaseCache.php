<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHPFusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: dbcache_handler.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Cache\Cache;
use PHPFusion\Database\DatabaseFactory;

(defined("IN_FUSION") || exit);

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
    public static function getInstance($connection = 'default'): DBCache {

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
        Cache::getInstance()->flush();
    }

    /**
     * @param $key
     */
    public function delete($key) {
        Cache::getInstance()->delete($key);
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
     * @param $key
     * @param $query
     * @param $parameters
     *
     * @return false|int|mixed
     */
    public function dbquery($key, $query, $parameters) {

        self::$array_counter = 0;

        if ($cache = Cache::getInstance()->get($key)) {
            if ($cache["delays"] === $this->seconds) {
                return $cache;
            }
        }

        try {

            $dbfactory = DatabaseFactory::getConnection(self::$connect_id);

            $query = $dbfactory->query($query, $parameters);

            $cache = [
                "rows"     => $dbfactory->countRows($query),
                "array"    => $dbfactory->fetchAllAssoc($query),
                "arraynum" => $dbfactory->fetchRow($query),
                "delays"   => $this->seconds
            ];

            Cache::getInstance()->set($key, $cache, $this->seconds);

            return $cache;

        } catch (\Exception $e) {

            set_error(E_CORE_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());

            return NULL;
        }
    }

    /**
     * Return number of rows
     *
     * @param $result
     *
     * @return int
     */
    public function dbrows($result) {
        if (is_array($result) && isset($result["rows"])) {
            return (int)$result["rows"];
        }
        return (int)0;
    }

    /**
     * Returns associative object array
     *
     * @param     $result
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
     * @param $result
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
     * @param $result
     * @param $column
     *
     * @return mixed|string
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
        return "";
    }

}

/**
 * @param       $key
 * @param       $query
 * @param array $parameters
 *
 * @return false|int|mixed
 */
function cdquery($key, $query, $parameters = []) {
    return DBCache::getInstance()->dbquery($key, $query, $parameters);
}

/**
 * @param $result
 *
 * @return int
 */
function cdrows($result) {
    return DBCache::getInstance()->dbrows($result);
}

/**
 * @param $result
 *
 * @return array|null
 */
function cdarray($result) {
    return DBCache::getInstance()->dbarray($result);
}

/**
 * @param $result
 *
 * @return array|mixed
 */
function cdarraynum($result) {
    return DBCache::getInstance()->dbarraynum($result);
}

/**
 * @param $result
 * @param $row
 *
 * @return mixed|string
 */
function cdresult($result, $row) {
    return DBCache::getInstance()->dbresult($result, $row);
}

/**
 * Runs cache flush command
 */
function cdflush() {
    DBCache::getInstance()->flush();
}

/**
 * Resets the cache and invalidates it
 * @param $key
 *
 * @return mixed
 */
function cdreset($key) {
    DBCache::getInstance()->delete($key);
}

/**
 * Hierarchy Full Data Output
 *
 * @param        $key
 * @param string $db
 * @param string $id_col
 * @param string $cat_col
 * @param null   $filter        replace conditional structure
 * @param null   $query_replace replace the entire query structure
 *
 * @return array Returns cat-id relationships with full data
 */
function cdquery_tree_full($key, string $db, string $id_col, string $cat_col, $filter = NULL, $query_replace = NULL) {
    $index = [];
    $query = "SELECT * FROM ".$db." ".$filter;
    if (!empty($query_replace)) {
        $query = $query_replace;
    }
    $query = cdquery($key, $query);
    while ($row = cdarray($query)) {
        $id = $row[$id_col];
        $parent_id = $row[$cat_col] === NULL ? "0" : $row[$cat_col];
        $index[$parent_id][$id] = $row;
    }

    return (array)$index;
}


/**
 * Hierarchy ID to Category Output
 *
 * @param        $key
 * @param string $db            Table name
 * @param string $id_col        ID column
 * @param string $cat_col       Category column
 * @param null   $filter        Conditions
 * @param null   $query_replace Replace the entire query
 *
 * @return array Returns cat-id relationships
 */
function cdquery_tree($key, string $db, string $id_col, string $cat_col, $filter = NULL, $query_replace = NULL) {
    $index = [];
    $query = "SELECT $id_col, $cat_col FROM ".$db." ".$filter;
    if (!empty($query_replace)) {
        $query = $query_replace;
    }
    $result = cdquery($key, $query);
    while ($row = cdarray($result)) {
        $id = $row[$id_col];
        $parent_id = $row[$cat_col] === NULL ? "NULL" : $row[$cat_col];
        $index[$parent_id][] = $id;
    }

    return (array)$index;
}



/**
 * Get cache database configurations
 *
 * @param array $config
 *
 * @return array
 *              "storage" - file|redis|memcache
 *              "memcache_hosts" - ['localhost:11211', '192.168.1.100:11211', 'unix:///var/tmp/memcached.sock']
 *              "redis_hosts" - ['localhost:6379', '192.168.1.100:6379:1:passwd']
 *              "path" - BASEDIR."cache/data/" for Filecache
 */
function default_cd_config($config = []) {
    $default_config = [
        "storage"        => "memcached",
        "memcache_hosts" => ["localhost:11211"],
        "redis_hosts"    => ['localhost:6379'],
        "path"           => BASEDIR."cache/data/"
    ];
    $config += $default_config;

    return (array)$config;
}
