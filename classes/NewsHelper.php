<?php
namespace PHPFusion\Infusions\News\Classes;

class NewsHelper {

    /**
     * @param array $filters
     *
     * @return array|string
     */
    public function getNewsQuery(array $filters = []) {
        $defaults = [
            'rowstart'  => 0,
            'limit'     => 0,
            'condition' => '',
            'select'    => '',
            'group_by'  => '',
            'order_by'  => '',
        ];

        $filters += $defaults;

        $news_settings = get_settings('news');
        $cat_filter = $this->getNewsFilter();
        $pattern = "SELECT %s(nr.rating_vote) FROM ".DB_RATINGS." AS nr WHERE nr.rating_item_id = n.news_id AND nr.rating_type = 'N'";
        $sql_count = sprintf($pattern, 'COUNT');
        $sql_sum = sprintf($pattern, 'SUM');
        $default_paging = !empty($news_settings['news_pagination']) ? $news_settings['news_pagination'] : 12;
        $limit = ($filters['limit'] ?: $default_paging);

        $filter_conds[] = (multilang_column('NS') ? in_group('news_language', LANGUAGE) : "");
        $filter_conds[] = groupaccess('news_visibility');
        $filter_conds[] = "(news_start='0'||news_start<='".time()."') AND (news_end='0'||news_end>='".time()."')";
        $filter_conds[] = "news_draft=0";

        $select_conds = (($filters['select']) ? $filters['select'].',' : '');

        $max_rows = dbcount("(news_id)", DB_NEWS." n", ($filters['condition'] ?: implode(' AND ', $filter_conds)));
        $rowstart = (int)$filters['rowstart'];
        if ($rowstart > $max_rows) {
            $rowstart = $max_rows;
        }

        $query = "SELECT n.*, nc.*, nu.user_id, nu.user_name, nu.user_status, nu.user_avatar , nu.user_level, nu.user_joined, ($sql_sum) 'news_sum_rating', ($sql_count) 'news_count_votes', 
        (SELECT COUNT(ncc.comment_id) FROM ".DB_COMMENTS." ncc WHERE ncc.comment_item_id = n.news_id AND ncc.comment_type = 'N' AND ncc.comment_hidden = '0') 'count_comment',
        $select_conds             
        ni.news_image, ni.news_image_t1, ni.news_image_t2 
        FROM ".DB_NEWS." n
        LEFT JOIN ".DB_NEWS_IMAGES." ni ON ni.news_id=n.news_id AND ".(check_get('readmore') ? "n.news_image_full_default=ni.news_image_id" : "n.news_image_front_default=ni.news_image_id")."
        LEFT JOIN ".DB_USERS." nu ON n.news_name=nu.user_id
        LEFT JOIN ".DB_NEWS_CATS." nc ON n.news_cat=nc.news_cat_id
        WHERE ".($filters['condition'] ? $filters['condition'] : implode(' AND ', $filter_conds))."                        
        GROUP BY ".(!empty($filters['group_by']) ? $filters['group_by'] : 'news_id')."
        ORDER BY ".(!empty($filters['order_by']) ? $filters['order_by'].',' : '')." news_sticky DESC".($cat_filter['order'] ? ", ".$cat_filter['order'] : '')."
        LIMIT $rowstart, $limit";
        $result = dbquery($query);

        return [
            'query'    => $query,
            'result'   => $result,
            'rows'     => (int)dbrows($result),
            'rowstart' => (int)$rowstart,
            'max_rows' => (int)$max_rows,
            'limit'    => (int)$limit,
            'filters'  => $filters
        ];

    }

    /**
     * Sql filter between $_GET['type']
     * most commented
     * most recent news
     * most rated
     */
    protected function getNewsFilter() {
        // allowable filter type
        $filter = ['recent', 'comment', 'rating'];
        $type = get('type');
        if (in_array($type, $filter)) {
            $cat_filter['order'] = 'news_datestamp DESC';
            if ($type == 'recent') {
                // order by datestamp.
                $cat_filter['order'] = 'news_datestamp DESC';
            } else if ($type == 'comment') {
                // order by comment_count
                $cat_filter = [
                    'order' => 'count_comment DESC',
                    //'count' => 'COUNT(td.comment_item_id) AS count_comment,',
                    //'join'  => "LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.news_id AND td.comment_type='N' AND td.comment_hidden='0'",
                ];
            } else if ($type == 'rating') {
                // order by download_title
                $cat_filter = [
                    'order' => 'news_sum_rating DESC',
                    //'count' => 'IF(SUM(tr.rating_vote)>0, SUM(tr.rating_vote), 0) AS sum_rating, COUNT(tr.rating_item_id) AS count_votes,',
                    //'join'  => "LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tn.news_id AND tr.rating_type='N'",
                ];
            }
        } else {
            $cat_filter['order'] = 'news_datestamp DESC';
        }

        return $cat_filter;
    }

}
