<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
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

    /*
     * array(
        'db'               => '', // database
        'id_col'           => '', // id column
        'cat_col'          => '', // category column
        'title_col'        => '', // title column
        'custom_query'     => '',
        'unroot'           => TRUE, // true to allow 'uncategorized' options.
        'multiple'         => FALSE, // true for checkboxes instead of radios
        'parent_db'        => '', // current item db
        'parent_id_col'    => '', // current item db id column
        'parent_cat_col'   => '', // current item db category column
        'parent_title_col' => '',
        'select2_disabled' => TRUE,
    );
     */
    public function categories($data);

    public function tags($data);

}