<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Cache.inc
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Cache;

/**
 * Class Cache
 *
 * @package PHPFusion\Cache
 */
class Cache {
    /**
     * Class name
     *
     * @var object
     */
    private $cache;

    /**
     * Current storage
     *
     * @var string
     */
    private $cache_storage;

    /**
     * @var mixed
     */
    static protected $instance;

    /**
     * Cache constructor.
     *
     * @throws CacheException
     */
    public function __construct() {
        $cache_config = $this->getCacheConfig();

        if (empty($cache_config['storage'])) {
            throw new CacheException('Can\'t find cache storage in config.');
        }

        $path = CLASSES.'PHPFusion/Cache/Storage/'.ucfirst($cache_config['storage']).'Cache.inc';

        if (file_exists($path)) {
            $class = '\\PHPFusion\\Cache\\Storage\\'.ucfirst($cache_config['storage']).'Cache';
            $this->cache_storage = ucfirst($cache_config['storage']).'Cache';
            $this->cache = new $class($cache_config);
        } else {
            throw new CacheException('Cache file '.$path.' not found');
        }
    }

    /**
     * @return mixed|Cache
     * @throws CacheException
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Cache();
        }

        return self::$instance;
    }

    /**
     * Get cache config
     *
     * @return array
     * @throws CacheException
     */
    private function getCacheConfig() {
        global $cache_config;

        /**
         * Config
         *
         * $cache_config = [
         *     'storage'        => 'memcache', // file|redis|memcache
         *     'memcache_hosts' => ['localhost:11211'], // e.g. ['localhost:11211', '192.168.1.100:11211', 'unix:///var/tmp/memcached.sock']
         *     'redis_hosts'    => ['localhost:6379'], // e.g. ['localhost:6379', '192.168.1.100:6379:1:passwd']
         *     'path'           => BASEDIR.'cache/'
         * ];
         */

        if (!is_array($cache_config)) {
            throw new CacheException('Missing cache config');
        }

        return $cache_config;
    }

    /**
     * Get current storage type
     *
     * @return string
     */
    public function getStorageType() {
        return $this->cache_storage;
    }

    /**
     * Save data in cache
     *
     * @param string $key cache key
     * @param mixed  $data
     */
    public function set($key, $data) {
        $this->cache->set($key, $data);
    }

    /**
     * Return data by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key) {
        return $this->cache->get($key);
    }

    /**
     * Delete data from cache
     *
     * @param string $key
     */
    public function delete($key) {
        $this->cache->delete($key);
    }

    /**
     * Delete all data from cache
     */
    public function flush() {
        $this->cache->flush();
    }
}
