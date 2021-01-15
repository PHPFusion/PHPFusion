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
            $file_time = 0;

            if (file_exists($this->path.$key.'.cache')) {
                $file_time = filemtime($this->path.$key.'.cache');
            }

            if (($file_time + $seconds) < time()) {
                if (@file_put_contents($this->path.$key.'.cache', $data, LOCK_EX) == strlen($data)) {
                    @chmod($this->path.$key.'.cache', 0777);
                }
            }
        } else {
            if (@file_put_contents($this->path.$key.'.cache', $data, LOCK_EX) == strlen($data)) {
                @chmod($this->path.$key.'.cache', 0777);
            }
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
        $data = file_get_contents($this->path.$key.'.cache');
        $data = unserialize($data);

        return $data;
    }

    /**
     * Delete data from cache
     *
     * @param string $key
     */
    public function delete($key) {
        if (file_exists($this->path.$key.'.cache')) {
            @unlink($this->path.$key.'.cache');
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
