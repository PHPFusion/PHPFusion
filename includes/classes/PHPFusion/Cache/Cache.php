<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Cache.php
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
     * @var array
     */
    private $config;

    /**
     * @var mixed
     */
    static protected $instance;

    /**
     * Cache constructor.
     *
     * @param array $config
     *
     * @throws CacheException
     *
     * @uses \PHPFusion\Cache\Storage\FileCache
     * @uses \PHPFusion\Cache\Storage\RedisCache
     * @uses \PHPFusion\Cache\Storage\MemcacheCache
     */
    public function __construct($config = []) {
        $this->config = $this->getCacheConfig($config);
        $this->config['storage'] = ucfirst($this->config['storage']).'Cache';

        if (empty($this->config['storage'])) {
            throw new CacheException('Can\'t find cache storage in config.');
        }

        $path = CLASSES.'PHPFusion/Cache/Storage/'.$this->config['storage'].'.php';

        if (file_exists($path)) {
            $class = '\\PHPFusion\\Cache\\Storage\\'.$this->config['storage'];
            $this->cache = new $class($this->config);
        } else {
            throw new CacheException('Cache file '.$path.' not found');
        }
    }

    /**
     * @return mixed|Cache
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
     * @param array $config Custom config.
     *
     * @return array
     */
    public function getCacheConfig($config = []) {
        global $config_inc;

        if (!is_array($config_inc) && empty($config_inc['cache'])) {
            $config_inc = [
                'cache' => [
                    'storage' => 'file',
                    'path'    => BASEDIR.'cache/system/'
                ]
            ];
        }

        $config += $config_inc['cache'];

        return $config;
    }

    /**
     * Check connection
     *
     * @return mixed
     */
    public function isConnected() {
        return $this->cache->isConnected();
    }

    /**
     * Save data in cache
     *
     * @param string $key cache key
     * @param mixed  $data
     * @param int    $seconds
     */
    public function set($key, $data, $seconds = NULL) {
        $this->cache->set($key, $data, $seconds);
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
