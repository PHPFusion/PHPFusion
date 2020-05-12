<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/admin/ranks.php
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

use PHPFusion\Infusions\Forum\Classes\ForumServer;
use PHPFusion\Interfaces\TableSDK;
use PHPFusion\Tables;

class Ranks extends AdminInterface {

    protected $data = [
        'rank_id'            => 0,
        'rank_title'         => '',
        'rank_image'         => '',
        'rank_posts'         => 0,
        'rank_type'          => 2,
        'rank_apply_normal'  => '',
        'rank_apply_special' => '',
        'rank_apply'         => '',
        'rank_language'      => LANGUAGE,
    ];

    public function viewRanksAdmin() {

        pageAccess('F');

        $forum_settings = $this->get_forum_settings();

        if ($forum_settings['forum_ranks']) {

            echo "<div class='".grid_row()."'>\n<div class='".grid_column_size(100, 100, 100, 40)."'>\n";

            $this->showRankForm();

            echo "</div>\n<div class='".grid_column_size(100, 100, 100, 60)."'>\n";

            new Tables(new Rank_Table());

            echo "</div>\n</div>\n";

        } else {

            echo '<h3>'.self::$locale['forum_rank_403'].'</h3>';
            echo "<div class='well text-center'>";
            echo sprintf(self::$locale['forum_rank_450'], "<a href='".clean_request("section=fs", ["section"], FALSE)."'>".self::$locale['forum_rank_451']."</a>");
            echo "</div>";
        }
    }

    protected function post_forum_ranks() {

        if (isset($_POST['save_rank'])) {

            $this->data = [
                'rank_id'            => form_sanitizer($_POST['rank_id'], '0', 'rank_id'),
                'rank_title'         => form_sanitizer($_POST['rank_title'], '', 'rank_title'),
                'rank_image'         => form_sanitizer($_POST['rank_image'], "", "rank_image"),
                'rank_language'      => form_sanitizer($_POST['rank_language'], "", "rank_language"),
                'rank_posts'         => isset($_POST['rank_posts']) && isnum($_POST['rank_posts']) ? $_POST['rank_posts'] : 0,
                'rank_type'          => isset($_POST['rank_type']) && isnum($_POST['rank_type']) ? $_POST['rank_type'] : 0,
                'rank_apply_normal'  => isset($_POST['rank_apply_normal']) ? $_POST['rank_apply_normal'] : USER_LEVEL_MEMBER,
                'rank_apply_special' => isset($_POST['rank_apply_special']) && isnum($_POST['rank_apply_special']) ? $_POST['rank_apply_special'] : 0,
            ];
            $this->data += [
                'rank_apply' => $this->data['rank_type'] == 2 ? $this->data['rank_apply_special'] : $this->data['rank_apply_normal']
            ];

            if (fusion_safe()) {

                if (!empty($this->data['rank_id']) && !$this->check_duplicate_ranks()) {
                    /**
                     * Update
                     */
                    dbquery_insert(DB_FORUM_RANKS, $this->data, "update");
                    addNotice('info', self::$locale['forum_rank_411']);
                    redirect(clean_request("section", ["rank_id", "ref"], FALSE));

                } else if (!$this->check_duplicate_ranks()) {
                    /**
                     * Save New
                     */
                    dbquery_insert(DB_FORUM_RANKS, $this->data, "save");
                    addNotice('info', self::$locale['forum_rank_410']);
                    redirect(clean_request("section", ["rank_id", "ref"], FALSE));

                }
            }
        }

        if (isset($_GET['delete']) && isnum($_GET['delete'])) {
            dbquery("DELETE FROM ".DB_FORUM_RANKS." WHERE rank_id='".$_GET['delete']."'");
            addNotice("success", self::$locale['forum_rank_412']);
            redirect(clean_request("section=fr", ["delete", "ref"], FALSE));
        }
    }

    protected function check_duplicate_ranks() {
        $comparing_data = dbarray(
            dbquery(
                "SELECT rank_apply FROM ".DB_FORUM_RANKS." WHERE rank_id='".$this->data['rank_id']."'"
            ));
        if (
            ($this->data['rank_apply'] < USER_LEVEL_MEMBER && $this->data['rank_apply'] != $comparing_data['rank_apply'])
            && (dbcount("(rank_id)",
                DB_FORUM_RANKS,
                (multilang_table("FR") ? in_group('rank_language', LANGUAGE)." AND" : "")."
                                    rank_id!='".$this->data['rank_id']."' AND rank_apply='".$this->data['rank_apply']."'"))
        ) {
            addNotice('info', self::$locale['forum_rank_413']);
            redirect(clean_request("section=fr", [""], FALSE));
        }

        return FALSE;
    }

    private function showRankForm() {

        add_to_footer("<script src='".FORUM."admin/admin_rank.js'></script>");

        $array_apply_normal_opts = [
            USER_LEVEL_MEMBER      => self::$locale['forum_rank_424'],
            '-104'                 => self::$locale['forum_rank_425'],
            USER_LEVEL_ADMIN       => self::$locale['forum_rank_426'],
            USER_LEVEL_SUPER_ADMIN => self::$locale['forum_rank_427']
        ];

        // Special Select
        $groups_arr = getusergroups();
        $groups_except = [USER_LEVEL_PUBLIC, USER_LEVEL_MEMBER, USER_LEVEL_ADMIN, USER_LEVEL_SUPER_ADMIN];
        $group_opts = [];
        foreach ($groups_arr as $group) {
            if (in_array($group[0], $groups_except)) {
                $group_opts[$group[0]] = $group[1];
            }
        }

        $language_opts = fusion_get_enabled_languages();

        $this->post_forum_ranks();

        if ($rank_id = get('rank_id', FILTER_VALIDATE_INT)) {
            $result = dbquery("SELECT * FROM ".DB_FORUM_RANKS." WHERE rank_id='".intval($rank_id)."'");
            if (dbrows($result)) {
                $this->data = dbarray($result);
            }

        }

        echo openform('rank_form', 'post').

            form_hidden('rank_id', '', $this->data['rank_id']).

            form_text('rank_title', self::$locale['forum_rank_420'], $this->data['rank_title'], ['required' => TRUE, 'inline' => FALSE, 'error_text' => self::$locale['forum_rank_414']]).

            form_select('rank_image', self::$locale['forum_rank_421'], $this->data['rank_image'], ['inline' => FALSE, 'options' => $this->get_rank_images()]);

        if (multilang_table("FR")) {

            echo form_select('rank_language[]', self::$locale['global_ML100'], $this->data['rank_language'], [
                'inline'      => FALSE,
                'options'     => $language_opts,
                'placeholder' => self::$locale['choose'],
                'multiple'    => TRUE
            ]);

        } else {

            echo form_hidden('rank_language', '', $this->data['rank_language']);
        }

        echo form_checkbox('rank_type', self::$locale['forum_rank_429'], $this->data['rank_type'],
                [
                    'options' => [
                        self::$locale['forum_rank_429c'],
                        self::$locale['forum_rank_429b'],
                        self::$locale['forum_rank_429a'],
                    ],
                    'type'    => 'radio',
                    'inline'  => FALSE,
                ]).
            form_text('rank_posts', self::$locale['forum_rank_422'], $this->data['rank_posts'],
                [
                    'inline'      => FALSE,
                    'type'        => 'number',
                    'inner_width' => '150px',
                    'disabled'    => $this->data['rank_type'] != 0
                ]
            ).

            "<span id='select_normal' ".($this->data['rank_type'] == 2 ? "style:'display:none;'" : '').">\n".

            form_select('rank_apply_normal', self::$locale['forum_rank_423'], $this->data['rank_apply'], ['inline' => FALSE, 'options' => $array_apply_normal_opts]).

            "</span>\n<span id='select_special' ".($this->data['rank_type'] != 2 ? "style:'display:none;'" : '').">\n".

            form_select('rank_apply_special', self::$locale['forum_rank_423'], $this->data['rank_apply'], ['inline' => FALSE, 'options' => $group_opts]).

            "</span>\n".

            // change locale to save rank or update rank
            form_button('save_rank', self::$locale['save'], self::$locale['save'], ['class' => 'btn-success m-r-10']).

            closeform();

    }
}

/**
 * Class Rank_Table
 *
 * @package PHPFusion\Infusions\Forum\Admin
 */
class Rank_Table implements TableSDK {
    /**
     * Locale
     * @var array|null
     */
    private $locale = [];

    /**
     * Rank_Table constructor.
     */
    public function __construct() {
        $this->locale = fusion_get_locale();
    }

    /**
     * @return array
     */
    public function data() {
        return [
            'table'      => DB_FORUM_RANKS,
            'id'         => 'rank_id',
            'title'      => 'rank_title',
            'conditions' => (multilang_table("FR") ? in_group('rank_language', LANGUAGE) : ""),
            'order'      => 'rank_type DESC, rank_apply DESC, rank_posts'
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
        $aidlink = fusion_get_aidlink();
        return [
            'table_id'           => 'forum-ranks-list',
            'no_record'          => $this->locale['forum_rank_437'],
            'edit_link_format'   => FORUM.'admin/forums.php'.$aidlink.'&amp;section=fr&ref=rank_form&rank_id=',
            'delete_link_format' => FORUM.'admin/forums.php'.$aidlink.'&amp;section=fr&ref=rank_form&delete=',
            'search_col'         => 'rank_title',
            'order_col' => [
                'rank_title' => 'rank-title',
                'rank_type' => 'rank-type',
            ]
        ];
    }

    /**
     * @return array
     */
    public function column() {

        return [
            'rank_title' => [
                'title'       => $this->locale['forum_rank_430'],
                'edit_link'   => TRUE,
                'delete_link' => TRUE,
            ],
            'rank_apply' => [
                'title'    => $this->locale['forum_rank_431'],
                'callback' => ['PHPFusion\\Infusions\\Forum\\Classes\\Admin\\Rank_Table', 'getRankApply'],
            ],
            'rank_image' => [
                'title'    => $this->locale['forum_rank_432'],
                'callback' => ['PHPFusion\\Infusions\\Forum\\Classes\\Admin\\Rank_Table', 'getRankImage'],
            ],
            'rank_type'  => [
                'title'    => $this->locale['forum_rank_438'],
                'callback' => ['PHPFusion\\Infusions\\Forum\\Classes\\Admin\\Rank_Table', 'getRankType'],
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


    /**
     * @param $data
     *
     * @return string
     */
    public function getRankType($data) {
        if ($data[':rank_type'] == 0) {
            return (string)$data[':rank_posts'];
        } else if ($data[':rank_type'] == 1) {
            return (string)$this->locale['forum_rank_429b'];
        } else {
            return (string)$this->locale['forum_rank_429a'];
        }
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function getRankImage($data) {
        $ranks = ForumServer::get_forum_rank($data[':rank_posts'], $data[':rank_apply'], $data[':rank_apply']);
        return "<img src='".$ranks['rank_image_src']."'>";
    }

    public function getRankApply($data) {
        return ($data[':rank_apply'] == -104 ? $this->locale['forum_rank_425'] : getgroupname($data[':rank_apply']));
    }

}
