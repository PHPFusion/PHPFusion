<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: threads/filter.php
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

namespace PHPFusion\Infusions\Forum\Classes\Threads;

class ForumThreadFilter {

    public $filter_info = [];

    public $filter_sql = [
        'condition'      => FALSE,
        'order'          => FALSE,
        'join'           => FALSE,
        'select'         => FALSE,
        'time_condition' => FALSE,
        'type_condition' => FALSE,
    ];

    public function set_filterInfo() {

        $locale = fusion_get_locale();

        $time_array = [
            'today'  => strtotime('today'),
            '2days'  => strtotime('-2 days'),
            '1week'  => strtotime('-1 week'),
            '2week'  => strtotime('-2 weeks'),
            '1month' => strtotime('-2 months'),
            '2month' => strtotime('-2 months'),
            '3month' => strtotime('-2 months'),
            '6month' => strtotime('-6 months'),
            '1year'  => strtotime('-1 year'),
        ];

        $type_array = [
            'all'         => '',
            'discussions' => " AND t.thread_poll=0 AND t.thread_id NOT IN (SELECT cx.thread_id FROM ".DB_FORUM_ATTACHMENTS." cx GROUP BY cx.thread_id)",
            'attachments' => " AND (a.attach_id IS NOT NULL OR a.attach_count > 0) ",
            'poll'        => " AND thread_poll ='1'",
            'solved'      => " AND t.thread_answered = '1'",
            'unsolved'    => " AND t.thread_answered = '0'",
        ];
        $sql_select_array = [
            'all'         => '',
            'discussions' => '',
            'attachments' => ", a.attach_id, COUNT(a.attach_id) 'attach_count'",
            'poll'        => '',
            'solved'      => '',
            'unsolved'    => '',
        ];
        $sql_joins_array = [
            'all'         => '',
            'discussions' => '',
            'attachments' => " LEFT JOIN ".DB_FORUM_ATTACHMENTS." a ON a.thread_id=t.thread_id",
            'poll'        => '',
            'solved'      => '',
            'unsolved'    => '',
        ];
        $sort_array = [
            'author'  => 't.thread_author',
            'time'    => 't.thread_lastpost',
            'subject' => 't.thread_subject',
            'reply'   => 't.thread_postcount',
            'view'    => 't.thread_views'
        ];
        $order_array = [
            'ascending'  => 'ASC',
            'descending' => 'DESC'
        ];
        $time = get('time');
        $time = ($time && isset($time_array[$time]) ? $time : '');
        $type = get('type');
        $type = ($type && isset($type_array[$type]) ? $type : '');
        $sort = get('sort');
        $sort = (isset($sort) && isset($sort_array[$sort]) ? $sort : '');
        $order = get('order');
        $order = (isset($order) && isset($order_array[$order]) ? $order : '');

        $select = (isset($sql_select_array[$type]) ? $sql_select_array[$type] : '');
        $joins = (isset($sql_joins_array[$type]) ? $sql_joins_array[$type] : '');

        $timeCol = '';
        $typeCol = '';

        if ($time) {
            if ($time !== 'today') {
                $start_time = intval($time_array[$time]);
                $timeCol = " AND (t.thread_lastpost BETWEEN '$start_time' AND '".TIME."') ";
            } else {
                $timeCol = " AND (t.thread_lastpost >= '".intval($time_array[$time])."') ";
            }
        }
        if ($type) {
            $typeCol = isset($type_array[$type]) ? $type_array[$type] : 'all';
        }

        $sortCol = "ORDER BY t.thread_lastpost ";
        $orderCol = 'DESC';
        if ($sort) {
            $sortCol = "ORDER BY ".$sort_array[$sort]." ";
        }
        if ($order) {
            $orderCol = $order_array[$order];
        }

        $this->filter_sql = [
            'join'           => $joins,
            'select'         => $select,
            'condition'      => $timeCol.$typeCol,
            'order'          => $sortCol.$orderCol,
            'time_condition' => $timeCol,
            'type_condition' => $typeCol,
        ];

        // Filter Links
        $timeExt = $time ? "&time=".$time : '';
        $typeExt = $type ? "&type=".$type : '';
        $sortExt = $sort ? "&sort=".$sort : '';
        $orderExt = $order ? "&order=".$order : '';

        $baseLink = clean_request("", ["time", "type", "sort", "order"], FALSE);
        $parent_id = get('parent_id', FILTER_VALIDATE_INT);
        $forum_id = get('forum_id', FILTER_VALIDATE_INT);
        if (isset($_GET['viewforum']) && $forum_id) {
            $baseLink = INFUSIONS.'forum/index.php?viewforum&forum_id='.$forum_id.($parent_id ? '&amp;parent_id='.$parent_id : '');
        }

        $timeLink = $baseLink.$typeExt.$sortExt.$orderExt;

        $this->filter_info['time'] = [
            $locale['forum_0211'] => $baseLink,
            $locale['forum_0212'] => $timeLink.'&time=today', // must be static.
            $locale['forum_3008'] => $timeLink.'&time=2days',
            $locale['forum_3009'] => $timeLink.'&time=1week',
            $locale['forum_3010'] => $timeLink.'&time=2week',
            $locale['forum_3011'] => $timeLink.'&time=1month',
            $locale['forum_3012'] => $timeLink.'&time=2month',
            $locale['forum_3013'] => $timeLink.'&time=3month',
            $locale['forum_3014'] => $timeLink.'&time=6month',
            $locale['forum_3015'] => $timeLink.'&time=1year'
        ];

        $typeLink = $baseLink.$timeExt.$sortExt.$orderExt;

        $this->filter_info['type'] = [
            $locale['forum_0390'] => $typeLink.'&type=all',
            $locale['forum_0222'] => $typeLink.'&type=discussions',
            $locale['forum_0223'] => $typeLink.'&type=attachments',
            $locale['forum_0224'] => $typeLink.'&type=poll',
            $locale['forum_0378'] => $typeLink.'&type=solved',
            $locale['forum_0379'] => $typeLink.'&type=unsolved',
        ];

        $sortLink = $baseLink.$timeExt.$typeExt.$orderExt;

        $this->filter_info['sort'] = [
            $locale['forum_0052'] => $sortLink.'&sort=author',
            $locale['forum_0381'] => $sortLink.'&sort=time',
            $locale['forum_0051'] => $sortLink.'&sort=subject',
            $locale['forum_0054'] => $sortLink.'&sort=reply',
            $locale['forum_0053'] => $sortLink.'&sort=view',
        ];

        $orderLink = $baseLink.$timeExt.$typeExt.$sortExt;

        $this->filter_info['order'] = [
            $locale['forum_0230'] => $orderLink.'&order=descending',
            $locale['forum_0231'] => $orderLink.'&order=ascending'
        ];

    }

    public function get_filterInfo() {
        return (array)$this->filter_info;
    }

    public function get_filterSQL() {
        return (array)$this->filter_sql;
    }

}
