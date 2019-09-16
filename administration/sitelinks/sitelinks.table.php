<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sitelinks.table.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Interfaces\TableSDK;

class SiteLinks_Table implements TableSDK {

    private $visibility_opts = [];

    private $position_opts = [];

    private $allowed_actions = [];

    private $menu_id = 0;

    public function __construct() {

        $this->visibility_opts = \PHPFusion\SiteLinks::get_LinkVisibility();

        $this->position_opts = \PHPFusion\SiteLinks::get_SiteLinksPosition();

        $this->allowed_actions = array_flip(["publish", "unpublish", "move", "move_confirm", "delete"]);

        $this->menu_id = get('menu', FILTER_VALIDATE_INT) ?: 1;
    }

    /**
     * Site links data
     *
     * @return array
     */
    public function data() {

        $menu_id = $this->menu_id > 1 ? $this->menu_id : 1;

        return [
            'table'      => DB_SITE_LINKS,
            'id'         => 'link_id',
            'parent'     => 'link_cat',
            'title'      => 'link_name',
            'conditions' => 'base.link_position='.$menu_id,
            'debug' => FALSE,
        ];
    }

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
     * 'key_values' => 'Are you sure to delete this record?'
     * ],
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
    public function properties() {

        $locale = fusion_get_locale();

        $aidlink = fusion_get_aidlink();

        $language = stripinput(get('language') ?: 'English');

        $link_filters = [];
        $result = dbquery("SELECT link_id, link_name FROM ".DB_SITE_LINKS." WHERE link_cat=0 AND link_position='$this->menu_id' AND link_language='$language'");
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $link_filters[$data['link_id']] = $data['link_name'];
            }
        }

        return [
            'table_id'           => 'site-links-admin',
            'table_class'        => '',
            'no_record'          => $locale['SL_0062'],
            'edit_link'          => TRUE,
            'edit_link_format'   => FUSION_SELF.$aidlink."&amp;section=links&amp;ref=link_form&amp;action=edit&amp;link_id=",
            'delete_link'        => TRUE,
            'delete_link_format' => FUSION_SELF.$aidlink."&amp;action=delete&amp;link_id=",
            'view_link'          => TRUE,
            'search_col'         => 'link_name',
            'multilang_col'      => 'link_language',
            'updated_message'    => '',
            'deleted_message'    => '',

            // can't add normal array into list
            'action_filters'     => [
                'move' => $locale['move'],
            ],

            // can't find children -- bug
            'dropdown_filters'   => [
                'link_id' => [
                    'type'    => 'array',
                    'title'   => $locale['SL_0029'],
                    'options' => $link_filters
                ]
            ]

        ];

    }

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
    public function column() {

        $locale = fusion_get_locale();

        return [
            'link_id'         => [
                'title'  => $locale['SL_0073'],
                'format' => '<i class="pointer handle fas fa-arrows"></i>'
            ],
            'link_name'       => [
                'title'       => $locale['SL_0050'],
                'delete_link' => TRUE,
                'edit_link'   => TRUE,
            ],
            'link_status'     => [
                'title'   => $locale['SL_0031'],
                'options' => [
                    1 => $locale['published'],
                    0 => $locale['unpublished']
                ]
            ],
            'link_icon'       => [
                'title'  => $locale['SL_0070'],
                'format' => '<i class=":link_icon"></i>'
            ],
            'link_window'     => [
                'title'   => $locale['SL_0071'],
                'options' => [
                    0 => $locale['no'],
                    1 => $locale['yes']
                ]
            ],
            'link_position'   => [
                'title'   => $locale['SL_0072'],
                'options' => $this->position_opts
            ],
            'link_visibility' => [
                'title'   => $locale['SL_0051'],
                'options' => $this->visibility_opts,
            ],
            'link_order'      => [
                'title' => $locale['SL_0052']
            ]
        ];
    }

    /**
     * Every row of the array is a field input.
     *
     * @return array
     */
    public function quickEdit() {
        return [];
    }
}
