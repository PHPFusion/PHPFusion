<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: RedisCache.php
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
namespace PHPFusion\Cache\Storage;

use PHPFusion\Cache\CacheException;
use PHPFusion\Cache\ICache;

/**
 * Class RedisCache
 *
 * @package PHPFusion\Cache\Storage
 */
class RedisCache implements ICache {
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var bool
     */
    private $connection = TRUE;

    /**
     * RedisCache constructor.
     *
     * @param array $config
     *
     * @throws CacheException
     */
    public function __construct($config) {
        if (class_exists('\Redis')) {
            $this->redis = new \Redis();
        } else {
            throw new CacheException('Failed to load Redis Class.');
        }

        foreach ($config['redis_hosts'] as $host) {
            list($host, $port, $database, $password) = array_pad(explode(':', $host, 4), 4, NULL);

            $host = ($host !== NULL) ? $host : '127.0.0.1';
            $port = ($port !== NULL) ? $port : 6379;
            $database = ($database !== NULL) ? $database : 0;

            try {
                $this->redis->connect($host, $port);
            } catch (\Exception $e) {
                set_error(E_CORE_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
                $this->connection = FALSE;
            }

            if ($password != NULL && $this->redis->auth($password) === FALSE) {
                throw new CacheException('Could not authenticate with Redis server. Please check password.');
            }

            if ($database != 0 && $this->redis->select($database) === FALSE) {
                throw new CacheException('Could not select Redis database. Please check database setting.');
            }
        }
    }

    /**
     * Check connection
     *
     * @return bool
     */
    public function isConnected() {
        return $this->connection;
    }

    /**
     * Save data in cache
     *
     * @param string $key cache key
     * @param mixed  $data
     * @param int    $seconds
     */
    public function set($key, $data, $seconds = NULL) {
        $data = serialize($data);

        if ($seconds !== NULL) {
            $time = 0;

            if (!empty($this->get($key.'_time')) && !empty($this->get($key))) {
                $time = $this->get($key.'_time');
            }

            if (($time + $seconds) < time()) {
                $this->redis->set($key.'_time', time(), $seconds);
                $this->redis->set($key, $data, $seconds);
            }
        } else {
            $this->redis->set($key, $data, $seconds);
        }
    }

    /**
     * Return data by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key) {
        return unserialize($this->redis->get($key));
    }

    /**
     * Delete data from cache
     *
     * @param string $key
     */
    public function delete($key) {
        $this->redis->del($key);
    }

    /**
     * Delete all data from cache
     */
    public function flush() {
        $this->redis->flushAll();
    }
}
