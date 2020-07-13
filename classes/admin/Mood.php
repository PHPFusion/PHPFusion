<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/admin/mood.php
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
use PHPFusion\UserFieldsQuantum;

class Mood extends AdminInterface {
    /**
     * Forum mood data
     *
     * @var array
     */
    private $data = [
        'mood_id'          => 0,
        'mood_name'        => '',
        'mood_description' => '',
        'mood_icon'        => '',
        'mood_notify'      => USER_LEVEL_MEMBER,
        'mood_access'      => USER_LEVEL_MEMBER,
        'mood_status'      => 1,
    ];

    public function viewMoodAdmin() {
        pageAccess('F');

        echo "<div class='".grid_row()."'>\n<div class='".grid_column_size(100, 100, 100, 40)."'>\n";

        $this->showMoodForm();

        echo "</div>\n<div class='".grid_column_size(100, 100, 100, 60)."'>\n";

        new Tables(new Mood_List());

        echo "</div>\n</div>\n";

    }

    /**
     * Displays forum mood form
     */
    private function showMoodForm() {

        if (post('cancel_mood')) {
            redirect(clean_request('', ['mood_id', 'ref'], FALSE));
        }

        $this->updateMood();

        $groups = fusion_get_groups();

        unset($groups[0]);

        if ($mood_id = get('mood_id', FILTER_VALIDATE_INT)) {

            $result = dbquery("SELECT * FROM ".DB_FORUM_MOODS." WHERE mood_id=:mid", [':mid' => intval($mood_id)]);

            if (dbrows($result)) {

                $this->data = dbarray($result);
            }
        }

        echo openform("mood_form", 'post').

            form_hidden('mood_id', '', $this->data['mood_id']).

            UserFieldsQuantum::quantum_multilocale_fields('mood_name', self::$locale['forum_094'], $this->data['mood_name'], ['required' => TRUE, 'inline' => FALSE, 'placeholder' => self::$locale['forum_096']]).

            UserFieldsQuantum::quantum_multilocale_fields('mood_description', self::$locale['forum_095'], $this->data['mood_description'], ['required' => TRUE, 'inline' => FALSE, 'placeholder' => self::$locale['forum_097'], 'ext_tip' => self::$locale['forum_098']]).

            form_text('mood_icon', self::$locale['forum_099'], $this->data['mood_icon'],
                ['inline' => TRUE, 'width' => '350px', 'placeholder' => 'fa fa-thumbs-up']).

            form_checkbox('mood_status', self::$locale['forum_100'], $this->data['mood_status'],
                ['options' => [
                    self::$locale['forum_101'],
                    self::$locale['forum_102']
                ],
                 'inline'  => FALSE,
                 'type'    => 'radio'
                ]).

            form_checkbox('mood_notify', self::$locale['forum_103'], $this->data['mood_notify'], ['options' => $groups, 'inline' => FALSE, 'type' => 'radio']).

            form_checkbox('mood_access', self::$locale['forum_104'], $this->data['mood_access'], ['options' => $groups, 'inline' => FALSE, 'type' => 'radio']).

            form_button('save_mood', !empty($this->data['mood_id']) ? self::$locale['forum_106'] : self::$locale['forum_105'], self::$locale['save_changes'], ['class' => 'btn-primary m-r-10']).

            form_button('cancel_mood', self::$locale['cancel'], self::$locale['cancel'], ['icon' => 'fa fa-times']).

            closeform();
    }

    /**
     * Post execution of forum mood
     */
    protected function updateMood() {

        if (post('save_mood')) {
            $this->data = [
                'mood_id'          => sanitizer('mood_id', 0, 'mood_id'),
                'mood_name'        => form_sanitizer($_POST['mood_name'], '', 'mood_name', TRUE),
                'mood_description' => form_sanitizer($_POST['mood_description'], '', 'mood_description', TRUE),
                'mood_icon'        => sanitizer('mood_icon', '', 'mood_icon'),
                'mood_status'      => sanitizer('mood_status', '', 'mood_status'),
                'mood_notify'      => sanitizer('mood_notify', '', 'mood_notify'),
                'mood_access'      => sanitizer('mood_access', '', 'mood_access'),
            ];

            if (fusion_safe()) {
                if (!empty($this->data['mood_id'])) {
                    dbquery_insert(DB_FORUM_MOODS, $this->data, 'update');
                    add_notice('success', self::$locale['forum_notice_16']);
                } else {
                    dbquery_insert(DB_FORUM_MOODS, $this->data, 'save');
                    add_notice('success', self::$locale['forum_notice_15']);
                }
                redirect(clean_request('', ['mood_id', 'ref'], FALSE));
            }
        }

        if ($delete_id = get('delete', FILTER_VALIDATE_INT)) {
            add_notice('success', self::$locale['forum_notice_14']);

            dbquery("DELETE FROM ".DB_FORUM_MOODS." WHERE mood_id=:did", [':did' => $delete_id]);

            redirect(clean_request("section=fmd", ["delete", "ref"], FALSE));
        }
    }

}

/**
 * Class Mood_List
 *
 * @package PHPFusion\Infusions\Forum\Admin
 */
class Mood_List implements TableSDK {

    private $locale = [];

    /**
     * Mood_List constructor.
     */
    public function __construct() {
        $this->locale = fusion_get_locale();
    }

    /**
     * @return array
     */
    public function data() {
        return [
            'table'  => DB_FORUM_MOODS,
            'select' => "count(pn.post_id) AS 'mood_count'",
            'joins'  => 'LEFT JOIN '.DB_FORUM_POST_NOTIFY.' pn ON pn.notify_mood_id=base.mood_id',
            'group'  => 'mood_id',
            'order'  => 'mood_id ASC, mood_name ASC',
            'id'     => 'mood_id',
            'title'  => 'mood_name',
        ];
    }

    /**
     * @return array
     */
    public function properties() {

        $aidlink = fusion_get_aidlink();

        return [
            'table_id'           => 'forum-mood-list',
            'no_record'          => $this->locale['forum_114'],
            'edit_link_format'   => FORUM.'admin/forums.php'.$aidlink.'&amp;section=fmd&mood_id=',
            'delete_link_format' => FORUM.'admin/forums.php'.$aidlink.'&amp;section=fmd&delete=',
            'search_col'         => 'rank_title',
            'order_col'          => [
                'mood_name'   => 'mood-title',
                'mood_status' => 'mood-status',
                'mood_notify' => 'mood-notify',
                'mood_access' => 'mood-access',
                'mood_count'  => 'mood-post'
            ]
        ];
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function getMoodDescription($data) {
        return (string)sprintf( $this->locale['forum_113'], ucfirst( fusion_get_userdata( "user_name" ) ), fusion_parse_locale( $data[':mood_description'] ) );
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function getGroupName1($data) {
        return (string)getgroupname($data[':mood_notify']);
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function getGroupName2($data) {
        return (string)getgroupname($data[':mood_access']);
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function getMoodPostCount($data) {
        return (string)format_word($data[':mood_count'], $this->locale['fmt_post']);
    }

    /**
     * @return array
     */
    public function column() {

        return [
            'mood_name'        => [
                'title'       => $this->locale['forum_107'],
                'edit_link'   => TRUE,
                'delete_link' => TRUE,
                'multilang'   => TRUE,
            ],
            'mood_description' => [
                'title'    => $this->locale['forum_108'],
                'callback' => ['PHPFusion\\Infusions\\Forum\\Classes\\Admin\\Mood_List', 'getMoodDescription'],
            ],
            'mood_icon'        => [
                'title' => $this->locale['forum_109'],
                'icon'  => TRUE,
            ],
            'mood_count'       => [
                'title'  => $this->locale['forum_115'],
                'number' => TRUE,
            ],
            'mood_notify'      => [
                'title'    => $this->locale['forum_110'],
                'callback' => ['PHPFusion\\Infusions\\Forum\\Classes\\Admin\\Mood_List', 'getGroupName1'],
            ],
            'mood_access'      => [
                'title'    => $this->locale['forum_111'],
                'callback' => ['PHPFusion\\Infusions\\Forum\\Classes\\Admin\\Mood_List', 'getGroupName2'],
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
