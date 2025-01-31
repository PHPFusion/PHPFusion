<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: FileCache.php
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

use PHPFusion\Cache\ICache;

/**
 * Class FileCache
 *
 * @package PHPFusion\Cache\Storage
 */
class FileCache implements ICache {
    /**
     * @var string
     */
    private $path;

    /**
     * FileCache constructor.
     *
     * @param array $config
     */
    public function __construct($config) {
        $this->path = $config['path'];

        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, TRUE);
        }

        if (!defined('SECRET_KEY')) {
            define('SECRET_KEY', md5('temp_cache_key'));
        }
    }

    /**
     * Check connection
     *
     * @return bool
     */
    public function isConnected() {
        return is_writable($this->path);
    }

    /**
     * Save data in cache
     *
     * @param string $key cache key
     * @param mixed  $data
     * @param int    $seconds
     */
    public function set($key, $data, $seconds = NULL) {
        $key = md5($key.SECRET_KEY);

        $cache_data = [
            'expire' => $seconds,
            'data'   => serialize($data)
        ];

        $file = $this->path.$key.'.cache';
        $json = json_encode($cache_data);
        if (@file_put_contents($file, $json, LOCK_EX) == strlen($json)) {
            @chmod($file, 0777);
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
        $key = md5($key.SECRET_KEY);
        $file = $this->path.$key.'.cache';

        if (is_file($file)) {
            $file_data = file_get_contents($file);
            $cache_data = json_decode($file_data, TRUE);

            if (!empty($cache_data['expire'])) {
                $file_time = 0;

                if (file_exists($file)) {
                    $file_time = filemtime($file);
                }

                if (($file_time + $cache_data['expire']) < time()) {
                    $this->delete($key);
                    return NULL;
                }
            } else {
                $this->delete($key);
                return NULL;
            }

            return unserialize($cache_data['data']);
        }

        return NULL;
    }

    /**
     * Delete data from cache
     *
     * @param string $key
     */
    public function delete($key) {
        $key = md5($key.SECRET_KEY);
        $file = $this->path.$key.'.cache';
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Delete all data from cache
     */
    public function flush() {
        $handle = opendir($this->path);

        if ($handle) {
            while (FALSE !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    @unlink($this->path.$file);
                }
            }

            closedir($handle);
        }
    }
}
