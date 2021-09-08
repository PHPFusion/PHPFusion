<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: MemcacheCache.php
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
 * Class MemcacheCache
 *
 * @package PHPFusion\Cache\Storage
 */
class MemcacheCache implements ICache {
    /**
     * @var \Memcache
     */
    private $memcache;

    /**
     * @var bool
     */
    private $connection = TRUE;

    /**
     * @var bool
     */
    private $is_memcached = FALSE;

    /**
     * MemcacheCache constructor.
     *
     * @param array $config
     *
     * @throws CacheException
     */
    public function __construct($config) {
        if (class_exists('\Memcached')) {
            $this->memcache = new \Memcached();
            $this->is_memcached = TRUE;
        } else if (class_exists('\Memcache')) {
            $this->memcache = new \Memcache();
        } else {
            throw new CacheException('Failed to load Memcached or Memcache Class.');
        }

        foreach ($config['memcache_hosts'] as $host) {
            if (substr($host, 0, 7) != 'unix://') {
                list($host, $port) = explode(':', $host);
                if (!$port) {
                    $port = 11211;
                }
            } else {
                $port = 0;
            }

            $this->memcache->addServer($host, $port);

            $stats = $this->memcache->getStats();
            $this->connection = isset($stats) && (!empty($stats['pid']) && $stats['pid'] > 0);
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
        if ($seconds !== NULL) {
            $time = 0;

            if (!empty($this->get($key.'_time')) && !empty($this->get($key))) {
                $time = $this->get($key.'_time');
            }

            if (($time + $seconds) < time()) {
                if ($this->is_memcached) {
                    $this->memcache->set($key.'_time', time(), $seconds);
                    $this->memcache->set($key, $data, $seconds);
                } else {
                    $this->memcache->set($key.'_time', time(), NULL, $seconds);
                    $this->memcache->set($key, $data, NULL, $seconds);
                }
            }
        } else {
            if ($this->is_memcached) {
                $this->memcache->set($key, $data, $seconds);
            } else {
                $this->memcache->set($key, $data, NULL, $seconds);
            }
        }
    }

    /**
     * Return data by key
     *
     * @param string $key
     *
     * @return array|false|string
     */
    public function get($key) {
        return $this->memcache->get($key);
    }

    /**
     * Delete data from cache
     *
     * @param string $key
     */
    public function delete($key) {
        $this->memcache->delete($key);
    }

    /**
     * Delete all data from cache
     */
    public function flush() {
        $this->memcache->flush();
    }
}
