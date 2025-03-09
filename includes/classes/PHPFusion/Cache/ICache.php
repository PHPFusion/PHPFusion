<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: ICache.php
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

interface ICache {
    /**
     * Check connection
     *
     * @return mixed|bool
     */
    public function isConnected();

    /**
     * Save data in cache
     *
     * @param string $key cache key
     * @param mixed  $data
     * @param int    $seconds
     */
    public function set($key, $data, $seconds);

    /**
     * Return data by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Delete data from cache
     *
     * @param string $key
     */
    public function delete($key);

    /**
     * Delete all data from cache
     */
    public function flush();
}
