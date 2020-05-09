<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/admin/tags.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Infusions\Forum\Classes\Admin;

use PHPFusion\Interfaces\TableSDK;
use PHPFusion\Tables;

class ForumAdminTags extends AdminInterface {

    protected $data = [
        'tag_id'          => 0,
        'tag_title'       => '',
        'tag_description' => '',
        'tag_color'       => '#2e8c65',
        'tag_status'      => 1,
        'tag_language'    => LANGUAGE,
    ];

    /**
     * Admin interface
     */
    public function viewTagsAdmin() {
        pageAccess('F');

        echo "<div class='".grid_row()."'>\n<div class='".grid_column_size(100, 100, 50, 40)."'>\n";

        echo $this->displayTagForm();

        echo "</div>\n<div class='".grid_column_size(100, 100, 50, 60)."'>\n";

        new Tables(new Tags_List());

        echo "</div>\n</div>\n";
    }

    protected function displayTagForm() {

        if (post('cancel_tag')) {
            redirect(clean_request("", ["tag_id", "ref"], FALSE));
        }

        // Special Select
        $groups_arr = getusergroups();
        $groups_except = [USER_LEVEL_PUBLIC, USER_LEVEL_MEMBER, USER_LEVEL_ADMIN, USER_LEVEL_SUPER_ADMIN];
        $group_opts = [];
        foreach ($groups_arr as $group) {
            if (!in_array($group[0], $groups_except)) {
                $group_opts[$group[0]] = $group[1];
            }
        }

        $language_opts = fusion_get_enabled_languages();

        $this->post_tags();

        if ($tag_id = get('tag_id', FILTER_VALIDATE_INT)) {
            $result = dbquery("SELECT * FROM ".DB_FORUM_TAGS." WHERE tag_id=:tid", [':tid' => intval($tag_id)]);
            if (dbrows($result)) {
                $this->data = dbarray($result);
            }
        }

        $button_locale = $this->data['tag_id'] ? self::$locale['forum_tag_0208'] : self::$locale['forum_tag_0103'];

        echo openform('tag_form', 'post').

            form_hidden('tag_id', '', $this->data['tag_id']).

            form_text('tag_title', self::$locale['forum_tag_0200'], $this->data['tag_title'], ['required' => TRUE, 'error_text' => self::$locale['414'], "inline" => FALSE]).

            form_colorpicker('tag_color', self::$locale['forum_tag_0202'], $this->data['tag_color'], ['inline' => FALSE, 'required' => TRUE]).

            form_textarea('tag_description', self::$locale['forum_tag_0201'], $this->data['tag_description'], ['inline' => FALSE ]);

        if (multilang_table("FR")) {

            echo form_select('tag_language[]', self::$locale['forum_tag_0203'], $this->data['tag_language'], [
                'inline'      => FALSE,
                'options'     => $language_opts,
                'placeholder' => self::$locale['choose'],
                'multiple'    => TRUE
             ]);

        } else {
            echo form_hidden('tag_language', '', $this->data['tag_language']);
        }

        echo form_checkbox('tag_status', self::$locale['forum_tag_0204'], $this->data['tag_status'],
                ['options' => [
                    1 => self::$locale['forum_tag_0205'],
                    0 => self::$locale['forum_tag_0206'],
                ],
                 'type'    => 'radio',
                 'inline'  => FALSE,
                ]).

            form_button('save_tag', $button_locale, $button_locale, ['class' => 'btn-primary m-r-10']).

            form_button('cancel_tag', self::$locale['cancel'], self::$locale['cancel'], ['class' => 'btn-default', 'icon' => 'fa fa-times']).

            closeform();
    }

    protected function post_tags() {

        if (isset($_POST['save_tag'])) {

            $this->data = [
                'tag_id'          => form_sanitizer($_POST['tag_id'], '0', 'tag_id'),
                'tag_title'       => form_sanitizer($_POST['tag_title'], '', 'tag_title'),
                'tag_color'       => form_sanitizer($_POST['tag_color'], '', 'tag_color'),
                'tag_description' => form_sanitizer($_POST['tag_description'], '', 'tag_description'),
                'tag_status'      => form_sanitizer($_POST['tag_status'], '', 'tag_status'),
                'tag_language'    => form_sanitizer($_POST['tag_language'], LANGUAGE, 'tag_language'),
            ];

            if (fusion_safe()) {

                if (!empty($this->data['tag_id'])) {
                    /**
                     * Update
                     */
                    dbquery_insert(DB_FORUM_TAGS, $this->data, "update");
                    addNotice('success', self::$locale['forum_tag_0106']);
                    redirect(clean_request("section=ft", ["tag_id", "ref"], FALSE));

                } else {
                    /**
                     * Save New
                     */
                    dbquery_insert(DB_FORUM_TAGS, $this->data, "save");
                    addNotice('success', self::$locale['forum_tag_0105']);
                    redirect(clean_request("section=ft", ["tag_id", "ref"], FALSE));

                }
            }
        }

        if (isset($_GET['delete']) && isnum($_GET['delete'])) {
            dbquery("DELETE FROM ".DB_FORUM_TAGS." WHERE tag_id='".$_GET['delete']."'");
            addNotice("success", self::$locale['forum_tag_0107']);
            redirect(clean_request("section=ft", ["delete", "ref"], FALSE));
        }
    }

}

/**
 * Class Tags_List
 *
 * @package PHPFusion\Infusions\Forum\Admin
 */
class Tags_List implements TableSDK {

    private $locale = [];

    /**
     * Tags_List constructor.
     */
    public function __construct() {
        $this->locale = fusion_get_locale();
    }

    /**
     * @return array
     */
    public function data() {
        return [
            'table'      => DB_FORUM_TAGS,
            'id'         => 'tag_id',
            'title'      => 'tag_title',
            'conditions' => (multilang_table("FO") ? in_group('tag_language', LANGUAGE) : ""),
            'order'      => 'tag_id DESC, tag_title ASC'
        ];
    }

    /**
     * @return array
     */
    public function properties() {

        $aidlink = fusion_get_aidlink();

        return [
            'table_id'           => 'tags-list',
            'no_record'          => $this->locale['forum_tag_0209'],
            'edit_link_format'   => FORUM.'admin/forums.php'.$aidlink.'&amp;section=ft&tag_id=',
            'delete_link_format' => FORUM.'admin/forums.php'.$aidlink.'&amp;section=ft&&delete=',
            'search_col'         => 'tag_title',
            'order_col'          => [
                'tag_title' => 'tag-title',
                'tag_type'  => 'tag-type',
            ]
        ];
    }

    /**
     * @return array
     */
    public function column() {
        return [
            'tag_title'       => [
                'title'       => $this->locale['forum_tag_0200'],
                'edit_link'   => TRUE,
                'delete_link' => TRUE,
            ],
            'tag_color'       => [
                'title'  => $this->locale['forum_tag_0202'],
                'format' => '<i class="fa fa-square fa-2x fa-fw m-r-10" style="color: :tag_color"></i>',
            ],
            'tag_description' => [
                'title' => $this->locale['forum_tag_0201'],
            ],
            'tag_status'      => [
                'title'   => $this->locale['forum_tag_0204'],
                'options' => [
                    1 => $this->locale['forum_tag_0205'],
                    0 => $this->locale['forum_tag_0206']
                ]
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
