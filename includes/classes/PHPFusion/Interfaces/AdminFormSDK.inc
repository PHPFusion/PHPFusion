<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: AdminFormSDK.inc
| Author: Frederick MC Chan
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Interfaces;

/**
 * Interface AdminFormSDK
 *
 * @package PHPFusion
 */
interface AdminFormSDK {

    public function properties();

    public function data();

    /**
     * @param $data
     *
     * @return mixed
     */
    public function fields($data);

    public function custom($data);

    /**
     * Insert SQL
     * @param $data
     *
     * @return id
     */
    public function save($data);

    /**
     * Update SQL
     * @param $data
     *
     * @return int
     */
    public function update($data);

    /**
     * Delete SQL
     * @param $data
     *
     * @return mixed
     */
    public function remove($data);

    public function categories($data);

    public function tags($data);

}