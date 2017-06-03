<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
namespace PHPFusion\Forums\Threads;

class ThreadFilter {

    public $filter_info = array();

    public $filter_sql = array(
        'condition' => FALSE,
        'order' => FALSE,
    );

    public function set_filterInfo() {

        $locale = fusion_get_locale("", FORUM_LOCALE);

        $time_array = array(
            'today' => strtotime('today'),
            '2days' => strtotime('-2 days'),
            '1week' => strtotime('-1 week'),
            '2week' => strtotime('-2 weeks'),
            '1month' => strtotime('-2 months'),
            '2month' => strtotime('-2 months'),
            '3month' => strtotime('-2 months'),
            '6month' => strtotime('-6 months'),
            '1year' => strtotime('-1 year'),
        );

        $type_array = array(
            'all'         => '',
            'discussions' => "AND (a.attach_id IS NULL or attach_count ='0') AND thread_poll='0'",
            'attachments' => "AND (a.attach_id IS NULL OR attach_count > '0') AND thread_poll='0'",
            'poll'        => "AND (a.attach_id IS NULL or attach_count ='0') AND thread_poll ='1'",
            'solved'      => "AND t.thread_answered = '1'",
            'unsolved'    => "AND t.thread_answered = '0'",
        );

        $sort_array = array(
            'author' => 't.thread_author',
            'time' => 't.thread_lastpost',
            'subject' => 't.thread_subject',
            'reply' => 't.thread_postcount',
            'view' => 't.thread_views'
        );

        $order_array = array(
            'ascending' => 'ASC',
            'descending' => 'DESC'
        );

        $time = (isset($_GET['time']) && isset($time_array[$_GET['time']]) ? $_GET['time'] : '');
        $type = (isset($_GET['type']) && isset($type_array[$_GET['type']]) ? $_GET['type'] : '');
        $sort = (isset($_GET['sort']) && isset($sort_array[$_GET['sort']]) ? $_GET['sort'] : '');
        $order = (isset($_GET['order']) && isset($order_array[$_GET['order']]) ? $_GET['order'] : '');

        $timeCol = '';
        $typeCol = '';

        if ($time) {
            if ($time !== 'today') {
                $start_time = intval( $time_array[ $time ] );
                $timeCol = "AND (t.thread_lastpost BETWEEN '$start_time' AND '".TIME."')";
            } else {
                $timeCol = "AND (t.thread_lastpost >= ".intval($time_array[$time]).")";
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

        $this->filter_sql = array(
            'condition' => $timeCol.$typeCol,
            'order' => $sortCol.$orderCol,
        );

        // Filter Links
        $timeExt = isset($_GET['time']) ? "&amp;time=".$time : '';
        $typeExt = isset($_GET['type']) ? "&amp;type=".$type : '';
        $sortExt = isset($_GET['sort']) ? "&amp;sort=".$sort : '';
        $orderExt = isset($_GET['order']) ? "&amp;order=".$order : '';

        $baseLink = clean_request("", array("time", "type", "sort", "order"), FALSE);
        if (isset($_GET['viewforum']) && isset($_GET['forum_id'])) {
            $baseLink = INFUSIONS.'forum/index.php?viewforum&amp;forum_id='.$_GET['forum_id'].''.(isset($_GET['parent_id']) ? '&amp;parent_id='.$_GET['parent_id'].'' : '');
        }

        $timeLink = $baseLink.$typeExt.$sortExt.$orderExt;

        $this->filter_info['time'] = array(
            $locale['forum_0211'] => $baseLink,
            $locale['forum_0212'] => $timeLink.'&amp;time=today', // must be static.
            $locale['forum_3008'] => $timeLink.'&amp;time=2days',
            $locale['forum_3009'] => $timeLink.'&amp;time=1week',
            $locale['forum_3010'] => $timeLink.'&amp;time=2week',
            $locale['forum_3011'] => $timeLink.'&amp;time=1month',
            $locale['forum_3012'] => $timeLink.'&amp;time=2month',
            $locale['forum_3013'] => $timeLink.'&amp;time=3month',
            $locale['forum_3014'] => $timeLink.'&amp;time=6month',
            $locale['forum_3015'] => $timeLink.'&amp;time=1year'
        );

        $typeLink = $baseLink.$timeExt.$sortExt.$orderExt;

        $this->filter_info['type'] = array(
            $locale['forum_0390'] => $typeLink.'&amp;type=all',
            $locale['forum_0222'] => $typeLink.'&amp;type=discussions',
            $locale['forum_0223'] => $typeLink.'&amp;type=attachments',
            $locale['forum_0224'] => $typeLink.'&amp;type=poll',
            $locale['forum_0378'] => $typeLink.'&amp;type=solved',
            $locale['forum_0379'] => $typeLink.'&amp;type=unsolved',
        );

        $sortLink = $baseLink.$timeExt.$typeExt.$orderExt;

        $this->filter_info['sort'] = array(
            $locale['forum_0052'] => $sortLink.'&amp;sort=author',
            $locale['forum_0381'] => $sortLink.'&amp;sort=time',
            $locale['forum_0051'] => $sortLink.'&amp;sort=subject',
            $locale['forum_0054'] => $sortLink.'&amp;sort=reply',
            $locale['forum_0053'] => $sortLink.'&amp;sort=view',
        );

        $orderLink = $baseLink.$timeExt.$typeExt.$sortExt;

        $this->filter_info['order'] = array(
            $locale['forum_0230'] => $orderLink.'&amp;order=descending',
            $locale['forum_0231'] => $orderLink.'&amp;order=ascending'
        );

    }

    public function get_filterInfo() {
        return (array) $this->filter_info;
    }

    public function get_filterSQL() {
        return (array) $this->filter_sql;
    }

}