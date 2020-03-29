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
$column_name = news_filter_column_name($column_index);
//post(['order', $column_index, 'data'])
// asc or desc
$column_sort_order = post(['order', 0, 'dir']);

if (!$column_name) {
    $column_name = 'news_id';
    $column_sort_order = 'DESC';
}

// search value
$select_cond = '';
$select_order = '';
if ($search_value = post(['search', 'value'])) {
    // news subject conditions in fulltext search
    $select_cond = "(match(n.news_subject) AGAINST ('$search_value' IN BOOLEAN MODE)) 'score'";
    $search_cond[] = "(match(n.news_subject) AGAINST ('$search_value' IN BOOLEAN MODE))";
    $select_order = 'score DESC, ';
}

// build conditions by custom filters
if ($search_by_status = post('status', FILTER_VALIDATE_INT)) {
    switch ($search_by_status) {
        case 1: // draft only
            $search_cond[] = "n.news_draft=1";
        case 2: // sticky only
            $search_cond[] = "n.news_sticky=1";
        default:
    }
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
$news_query = [];
if (fusion_authenticate_user(get('auth_token'))) {
    //if (checkrights('N')) {
    // we need to check how to get the page-count
    $news_query = new NewsHelper();

    // add rowstart security
    $news_query = $news_query->getNewsQuery([
        'rowstart'  => (int)$row_start,
        'limit'     => (int)$rows_per_page,
        'condition' => (!empty($search_cond) ? implode(" AND ", array_filter($search_cond)) : ''),
        'group_by'  => '',
        'select'    => $select_cond,
        'order_by'  => $select_order.$column_name.' '.strtoupper($column_sort_order),
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
                'subject'  => "<a class='strong' href='".INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;section=news&amp;action=edit&amp;id=".$data['news_id']."'>".$data['news_subject']."</a><div class='table-actions'>
<a href='".INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;section=news&amp;action=edit&amp;id=".$data['news_id']."'>Edit</a> |
<a onclick='confirm(\"Are you sure you want to delete this news?\"); return false;' href='".INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;section=news&amp;action=delete&amp;id=".$data['news_id']."'>Delete</a> |
<a target='_blank' href='".INFUSIONS."news/news.php?readmore=".$data['news_id']."'>View</a></div>",
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
}

## Response
$response = [
    "draw"                 => (int)$draw,
    "iTotalRecords"        => (int)$news_query['max_rows'],
    "iTotalDisplayRecords" => (int)$news_query['max_rows'],
    "data"                 => $news,
    'news_query'           => $news_query,
];

echo json_encode($response, JSON_PRETTY_PRINT);

function news_filter_column_name($value) {
    $array = [
        '0'  => 'news_subject',
        '1'  => 'news_draft',
        '2'  => 'news_sticky',
        '3'  => 'news_cat',
        '4'  => 'news_name',
        '5'  => 'news_visibility',
        '6'  => 'news_datestamp',
        '7'  => 'news_start',
        '8'  => 'news_end',
        '9'  => 'news_reads',
        '10' => 'news_id',
    ];
    return (isset($array[$value]) ? $array[$value] : '');
}
