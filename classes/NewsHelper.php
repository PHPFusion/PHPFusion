<?php
namespace PHPFusion\Infusions\News\Classes;

class NewsHelper {
    /**
     * @param array $filters array('condition', 'order', 'limit')
     *
     * @return string
     */
    public function getNewsQuery(array $filters = []) {
        $news_settings = get_settings('news');
        $cat_filter = self::check_NewsFilter();

        $pattern = "SELECT %s(nr.rating_vote) FROM ".DB_RATINGS." AS nr WHERE nr.rating_item_id = n.news_id AND nr.rating_type = 'N'";
        $sql_count = sprintf($pattern, 'COUNT');
        $sql_sum = sprintf($pattern, 'SUM');

        $rowstart = (!empty($filters['limit']) ? $filters['limit'] : $filters['rowstart']);
        $limit = (!empty($news_settings['news_pagination']) ? $news_settings['news_pagination'] : 12);

        $query = "SELECT n.*, nc.*, nu.user_id, nu.user_name, nu.user_status, nu.user_avatar , nu.user_level, nu.user_joined,
            ($sql_sum) AS news_sum_rating,
            ($sql_count) AS news_count_votes,
            (SELECT COUNT(ncc.comment_id) FROM ".DB_COMMENTS." AS ncc WHERE ncc.comment_item_id = n.news_id AND ncc.comment_type = 'N' AND ncc.comment_hidden = '0') AS count_comment,
            ni.news_image, ni.news_image_t1, ni.news_image_t2
            FROM ".DB_NEWS." n
            LEFT JOIN ".DB_NEWS_IMAGES." ni ON ni.news_id=n.news_id AND ".(check_get('readmore') ? "n.news_image_full_default=ni.news_image_id" : "n.news_image_front_default=ni.news_image_id")."
            LEFT JOIN ".DB_USERS." nu ON n.news_name=nu.user_id
            LEFT JOIN ".DB_NEWS_CATS." nc ON n.news_cat=nc.news_cat_id
            ".(multilang_table("NS") ? "WHERE ".in_group('news_language', LANGUAGE)." AND " : "WHERE ").groupaccess('news_visibility')." AND (news_start='0'||news_start<='".time()."') AND (news_end='0'||news_end>='".time()."') AND news_draft='0'
            ".(!empty($filters['condition']) ? "AND ".$filters['condition'] : '')."
            GROUP BY ".(!empty($filters['group_by']) ? $filters['group_by'] : 'news_id')."
            ORDER BY ".(!empty($filters['order']) ? $filters['order'].',' : '')." news_sticky DESC, ".$cat_filter['order']."
            LIMIT $rowstart, $limit";

        return $query;
    }

}
