<?php

use PHPFusion\Infusions\News\Classes\NewsHelper;

require_once __DIR__.'/../../../maincore.php';

$draw = post('draw');

$row_start = post('start');
// rows display per page
$rows_per_page = post('length');

// column index
$column_index = post(['order', 0, 'column']);
// column name
$column_name = post(['order', $column_index, 'data']);
// asc or desc
$column_sort_order = post(['order', 0, 'dir']);
// search value
$search_value = post(['search', 'value']);

// build conditions by custom filters
if ($search_by_status = post('status', FILTER_VALIDATE_INT)) {
    $search_cond[] = "news_status='$search_by_status'";
}
if ($search_by_visibility = post('visibility', FILTER_VALIDATE_INT)) {
    $search_cond[] = "news_visibility='$search_by_visibility' AND ".groupaccess('news_visibility');
}
if ($search_by_category = post('category', FILTER_VALIDATE_INT)) {
    $search_cond[] = "news_cat='$search_by_category'";
}
if ($search_by_language = post('language')) {
    $search_cond[] = "(news_language='$search_by_language' || ".in_group('news_language', $search_by_language).")";
}
if ($search_by_author = post('author', FILTER_VALIDATE_INT)) {
    $search_cond[] = "news_name='$search_by_author'";
}

$news = [];
//if (fusion_authenticate_user(get('auth_token'))) {
//if (checkrights('N')) {
// we need to check how to get the page-count
$news_query = new NewsHelper();

// add rowstart security
$news_query = $news_query->getNewsQuery([
    'rowstart'  => (int)$row_start,
    'limit'     => (int)$rows_per_page,
    'condition' => (!empty($search_cond) ? implode(" AND ", $search_cond) : ''),
    'group_by'  => '',
    'order_by'  => '',
]);

$result = $news_query['result'];
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
//}

//echo json_encode(['data' => $news]);

## Response
$response = [
    "draw"                 => (int)$draw,
    "iTotalRecords"        => (int)$news_query['max_rows'],
    "iTotalDisplayRecords" => (int)$news_query['max_rows'],
    "data"                 => $news,
    //'news_query'           => $news_query,
];

echo json_encode($response, JSON_PRETTY_PRINT);
