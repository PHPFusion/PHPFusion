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
interface TableSDK {

    /**
     *  Returns the table data source structure configurations
     *
     *
     * 'debug'                    => FALSE, // True to show the SQL query for the table.
     * 'table'                    => '', // db name
     * 'id'                       => '', // id column name
     * 'title'                    => '', // title column name
     * 'parent'                   => '', // parent column name (if hierarchy)
     * 'limit'                    => 24, // limit
     * 'true_limit'               => FALSE, // if true, the limit is true limit (only limited results will display without page nav)
     * 'joins'                    => '', // INNER JOIN etc on etc=base.id
     * 'select'                   => '', // addditional
     * 'conditions'               => '', // to match list to a condition. string value only
     * 'group'                    => '', // group by column
     * 'image_folder'             => '', // for deletion (i.e. IMAGES.'folder/') , use param for string match
     * 'image_field'              => '', // to delete (i.e. news_image)
     * 'file_field'               => '',  // to delete (i.e. news_attach)
     * 'file_folder'              => '', // to delete files from the folder, use param for string match
     * 'db'                       => [], // to delete other entries on delete -- use this key. Keys: 'select' => 'ratings_id', 'group' => 'ratings_item_id', 'custom' => "rating_type='CLS'"
     * 'delete_function_callback' => '',
     *
     * @return array
     */
    public function data();

    /**
     * Returns the table outlook/presentation configurations
     *
     * 'table_class'        => '',
     * 'header_content'     => '',
     * 'no_record'          => 'There are no records',
     * 'search_label'       => 'Search',
     * 'search_placeholder' => "Search",
     * 'search_col'         => '', // set this value sql column name to have search input input filter
     * 'delete_link' => TRUE,
     * 'edit_link' => TRUE,
     * 'edit_link_format'   => '', // set this to format the edit link
     * 'delete_link_format' => '', // set this to format the delete link
     * 'view_link_format' => '', // set this to format the view link
     *
     * 'edit_key'           => 'edit',
     * 'del_key'            => 'del', // change this to invoke internal table delete function for custom delete link format
     * 'view_key'           => 'view',
     *
     * 'date_col'           => '',  // set this value to sql column name to have date selector input filter
     * 'order_col'          => '', // set this value to sql column name to have sorting column input filter
     * 'multilang_col'      => '', // set this value to have multilanguage column filter
     * 'updated_message'    => 'Entries have been updated', // set this value to have custom success message
     * 'deleted_message'    => 'Entries have been deleted', // set this value to have the custom delete message,
     * 'class'              => '', // table class
     * 'show_count'         => TRUE // show table item count,
     * // This will add an extra link on top of the bulk actions selector
     * 'link_filters'       => [
     * 'group_key' => [
     *                  [$key_values => $key_title],
     *                  [$key_values => $key_title]
     *              ]
     * ]
     * // This will add extra dropdown pair of dropdown selectors to act as column filter that has such value.
     * 'dropdown_filters' => [
     *          'user_level' => [
     *          'type' => 'array', // use 'date' if the column is a datestamp
     *          'title' => $title',
     *          'options' => [ [$key_values => $key_title], [$key_values => $key_title], ... ] ] //$key_values - This is the key to be used on actions_filters_confirm
     *          ]
     * ],
     * // This will add your confirmation messages -- key_values is the key to 'dropdown_filters'['options'][key']
     * 'actions_filters_confirm' => [
                'key_values' => 'Are you sure to delete this record?'
        ],
     *  // This allows you to add more options to the bulk filters.
     * 'action_filters'   => [
     * 'text'     => 'Member Actions',
     * 'label'    => TRUE,
     * 'children' => [
     * Members::USER_BAN          => $locale['ME_500'],
     * Members::USER_REINSTATE    => $locale['ME_501'],
     * Members::USER_SUSPEND      => $locale['ME_503'],
     * Members::USER_SECURITY_BAN => $locale['ME_504'],
     * Members::USER_CANCEL       => $locale['ME_505'],
     * Members::USER_ANON         => $locale['ME_506'],
     * Members::USER_DEACTIVATE   => $locale['ME_507']
     * ]
     * ]
     *
     *
     *
     * @return array
     */
    public function properties();


    /**
     * Returns the column structure configurations
     *
     * 'title'         => '',
     * 'title_class'   => '',
     * 'value_class'   => '',
     * 'edit_link'     => FALSE,
     * 'delete_link'   => FALSE,
     * 'image'         => FALSE,
     * 'image_folder'  => '', // set image folder (method2)
     * 'default_image' => '',
     * 'image_width'   => '', // set image width
     * 'image_class'   => '', // set image class
     * 'icon'          => '',
     * 'empty_value'   => '',
     * 'count'         => [],
     * 'view_link'     => '',
     * 'display'       => [], // API for display
     * 'date'          => FALSE,
     * 'options'       => [],
     * 'user'          => FALSE,
     * 'user_avatar'   => FALSE, // show avatar
     * 'number'        => FALSE,
     * 'format'        => FALSE, // for formatting using strtr
     * 'callback'      => '', // for formatting using function
     * 'debug'         => FALSE,
     * 'visibility'    => FALSE, // set this column to hide by default until user enables it via custom
     *
     * @return array
     */
    public function column();

    /**
     * Every row of the array is a field input.
     *
     * @return array
     */
    public function quickEdit();

}