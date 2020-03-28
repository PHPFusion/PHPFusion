<?php

use PHPFusion\Infusions\News\Classes\NewsAdminModel;
use PHPFusion\Infusions\News\Classes\NewsHelper;

require_once __DIR__.'/../../../maincore.php';

$draw = post('draw');
$row_start = post('start');
// rows display per page
$row_per_page = post('length');
// column index
$column_index = post(['order', 0, 'column']);
// column name
$column_name = post(['order', $column_index, 'data']);
// asc or desc
$column_sort_order = post(['order', 0, 'dir']);
// search value
$search_value = post(['search', 'value']);

// custom fields post
$search_by_status = post('status');

$search_by_visibility = post('visibility');

$news = [];
if (fusion_authenticate_user(get('auth_token'))) {
    //if (checkrights('N')) {
    // we need to check how to get the page-count
    $news_query = new NewsHelper();
    $news_query = $news_query->getNewsQuery([

    ]);

    $result = dbquery("SELECT n.*, 
        u.user_id, u.user_name, u.user_status,
        nc.news_cat_id, nc.news_cat_name
        FROM ".DB_NEWS." n 
        LEFT JOIN  ".DB_USERS." u ON u.user_id=n.news_name
        LEFT JOIN ".DB_NEWS_CATS." nc on nc.news_cat_id=n.news_cat 
        ORDER BY news_id DESC");
    if (dbrows($result)) {
        require_once INCLUDES.'theme_functions_include.php';

        while ($data = dbarray($result)) {
            $category = "Uncategorized";
            if ($data['news_cat_id']) {
                $category = "<a href='".INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;section=category&id=".$data['news_cat_id']."'>".$data['news_cat_name']."</a>";
            }

            $news[] = [
                'subject'  => $data['news_subject'],
                'draft'    => $data['news_draft'],
                'sticky'   => $data['news_sticky'],
                'category' => $category,
                'poster'   => profile_link($data['user_id'], $data['user_name'], $data['user_status']),
                'access'   => getgroupname($data['news_visibility']),
                'date'     => showdate('longdate', $data['news_datestamp']),
                'start'    => showdate('longdate', $data['news_start']),
                'stop'     => showdate('longdate', $data['news_end']),
                'reads'    => $data['news_reads'],
                'id'       => $data['news_id']
            ];
        }
    }
    //} else {
    //    set_error(E_USER_NOTICE, 'Failed check on news table listing', 'news_admin.php', '0', 'Ajax Error');
    //}
}
echo json_encode(['data' => $news]);

## Response
$response = [
    "draw"                 => intval($draw),
    "iTotalRecords"        => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData"               => $data
];
