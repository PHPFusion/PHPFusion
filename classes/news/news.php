<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/classes/news/news.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\News;

use PHPFusion\BreadCrumbs;
use PHPFusion\Feedback\Comments;

abstract class News extends NewsServer {
    protected static $locale = [];
    public $info = [];

    protected static $news_ratings = [];
    protected static $news_comment_count = [];

    protected function __construct() {
    }

    /**
     * Executes main page information
     *
     * @return array
     */
    public function set_NewsInfo() {
        self::$locale = fusion_get_locale('', NEWS_LOCALE);

        if (file_exists(INFUSIONS.'rss_feeds_panel/feeds/rss_news.php')) {
            add_to_head('<link rel="alternate" type="application/rss+xml" title="'.fusion_get_locale('news_0004').' - RSS Feed" href="'.fusion_get_settings('siteurl').'infusions/rss_feeds_panel/feeds/rss_news.php"/>');
        }

        set_title(self::$locale['news_0004']);

        BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => INFUSIONS.'news/news.php',
            'title' => self::$locale['news_0004']
        ]);

        $info = [
            'news_cat_id'       => intval(0),
            'news_cat_name'     => self::$locale['news_0004'],
            'news_cat_image'    => '',
            'news_cat_language' => LANGUAGE,
            'news_categories'   => [],
            'news_image'        => '',
            'news_item_rows'    => 0,
            'news_items'        => []
        ];

        $info = array_merge_recursive($info, self::get_NewsFilter());
        $info = array_merge_recursive($info, self::get_NewsCategory());
        $info = array_merge_recursive($info, self::get_NewsItem());

        $this->info = $info;

        return (array)$info;
    }

    /**
     * Outputs core filters variables
     *
     * @return array
     */
    protected static function get_NewsFilter() {
        $array['allowed_filters'] = [
            'recent'  => self::$locale['news_0011']
        ];

        if (fusion_get_settings('comments_enabled') == 1) {
            $array['allowed_filters']['comment'] = self::$locale['news_0012'];
        }

        if (fusion_get_settings('ratings_enabled') == 1) {
            $array['allowed_filters']['rating'] = self::$locale['news_0013'];
        }

        foreach ($array['allowed_filters'] as $type => $filter_name) {
            $filter_link = INFUSIONS."news/news.php?".(isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : '')."type=".$type;
            $array['news_filter'][$filter_link] = $filter_name;
            unset($filter_link);
        }

        return (array)$array;
    }

    /**
     * Outputs category variables
     *
     * @return mixed
     */
    public static function get_NewsCategory() {
        $array = [];
        $array['news_categories'][0][0] = [
            'link' => INFUSIONS."news/news.php?cat_id=0",
            'name' => fusion_get_locale('news_0006', NEWS_LOCALE)
        ];
        $result = dbquery("SELECT news_cat_id, news_cat_name, news_cat_parent, news_cat_image, news_cat_visibility
        FROM ".DB_NEWS_CATS."
        ".(multilang_table("NS") ? "WHERE ".in_group('news_cat_language', LANGUAGE)." AND " : "WHERE ")." news_cat_draft=0 ORDER BY news_cat_sticky DESC, news_cat_id ASC");
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $id = $data['news_cat_id'];
                $parent_id = $data['news_cat_parent'] === NULL ? "NULL" : $data['news_cat_parent'];
                $array['news_categories'][$parent_id][$id] = [
                    'link'       => INFUSIONS.'news/news.php?cat_id='.$data['news_cat_id'],
                    'parent'     => $data['news_cat_parent'],
                    'name'       => $data['news_cat_name'],
                    'icon'       => IMAGES_NC.$data['news_cat_image'],
                    'visibility' => $data['news_cat_visibility']
                ];
            }
        }

        return $array;
    }

    /**
     * Get news item
     *
     * @param array $filter
     *
     * @return array
     */
    public function get_NewsItem($filter = []) {

        $info['news_total_rows'] = dbcount("(news_id)", DB_NEWS, groupaccess('news_visibility')." AND (news_start='0'||news_start<='".TIME."') AND (news_end='0'||news_end>='".TIME."') AND news_draft='0'");
        if ($info['news_total_rows']) {
            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['news_total_rows'] ? intval($_GET['rowstart']) : 0;
            $result = dbquery(self::get_NewsQuery($filter));
            $info['news_item_rows'] = dbrows($result);
            if ($info['news_item_rows'] > 0) {
                $news_count = 0;
                $news_info = [];

                while ($data = dbarray($result)) {
                    $news_count++;
                    if ($news_count == 1) {
                        $info['news_last_updated'] = showdate('newsdate', $data['news_datestamp']);
                    }
                    $newsData = self::get_NewsData($data);
                    $news_info[$news_count] = $newsData;
                }
                $info['news_items'] = $news_info;
            }
        }

        return (array)$info;
    }

    /**
     * @param array $filters array('condition', 'order', 'limit')
     *
     * @return string
     */
    protected static function get_NewsQuery(array $filters = []) {
        $news_settings = self::get_news_settings();
        $cat_filter = self::check_NewsFilter();
        $pattern = "SELECT %s(nr.rating_vote) FROM ".DB_RATINGS." AS nr WHERE nr.rating_item_id = n.news_id AND nr.rating_type = 'N'";
        $sql_count = sprintf($pattern, 'COUNT');
        $sql_sum = sprintf($pattern, 'SUM');
        $query = "SELECT n.*, nc.*, nu.user_id, nu.user_name, nu.user_status, nu.user_avatar , nu.user_level, nu.user_joined,
            ($sql_sum) AS news_sum_rating,
            ($sql_count) AS news_count_votes,
            (SELECT COUNT(ncc.comment_id) FROM ".DB_COMMENTS." AS ncc WHERE ncc.comment_item_id = n.news_id AND ncc.comment_type = 'N' AND ncc.comment_hidden = '0') AS count_comment,
            ni.news_image, ni.news_image_t1, ni.news_image_t2
            FROM ".DB_NEWS." AS n
            LEFT JOIN ".DB_NEWS_IMAGES." AS ni ON ni.news_id=n.news_id AND ".(!empty($_GET['readmore']) ? "n.news_image_full_default=ni.news_image_id" : "n.news_image_front_default=ni.news_image_id")."
            LEFT JOIN ".DB_USERS." AS nu ON n.news_name=nu.user_id
            LEFT JOIN ".DB_NEWS_CATS." AS nc ON n.news_cat=nc.news_cat_id
            ".(multilang_table("NS") ? "WHERE ".in_group('news_language', LANGUAGE)." AND " : "WHERE ").groupaccess('news_visibility')." AND (news_start='0'||news_start<='".TIME."')
            AND (news_end='0'||news_end>='".TIME."') AND news_draft='0'
            ".(!empty($filters['condition']) ? "AND ".$filters['condition'] : '')."
            GROUP BY ".(!empty($filters['group_by']) ? $filters['group_by'] : 'news_id')."
            ORDER BY ".(!empty($filters['order']) ? $filters['order'].',' : '')." news_sticky DESC, ".$cat_filter['order']."
            LIMIT ".(!empty($filters['limit']) ? $filters['limit'] : $_GET['rowstart'].",".(!empty($news_settings['news_pagination']) ? $news_settings['news_pagination'] : 12));

        return $query;
    }

    public static function getNewsURL($news_id) {
        return INFUSIONS.'news/news.php?readmore='.$news_id;
    }

    public static function getNewsCatURL($news_cat_id) {
        return INFUSIONS.'news/news.php?cat_id='.$news_cat_id;
    }

    /**
     * Count the total votes and total sum of ratings in a news item
     *
     * @param $id
     *
     * @return array
     */
    protected static function count_ratings($id) {
        if (!isset(self::$news_ratings[$id])) {
            self::$news_ratings[$id] = dbarray(dbquery("SELECT
                IF(SUM(rating_vote)>0, SUM(rating_vote), 0) AS news_sum_rating, COUNT(rating_item_id) AS news_count_votes
                FROM ".DB_RATINGS."
                WHERE rating_item_id='".$id."' AND rating_type='N'
             "));
        }

        return (array)self::$news_ratings[$id];
    }

    /**
     * Count the number of comments in a news item
     *
     * @param $id
     *
     * @return int
     */
    protected static function count_comments($id) {
        if (!isset(self::$news_comment_count[$id])) {
            self::$news_comment_count[$id] = dbarray(dbquery("SELECT
                COUNT(comment_item_id) AS count_comment
                FROM ".DB_COMMENTS."
                WHERE comment_item_id='".$id."' AND comment_type='N' AND comment_hidden='0'
             "));

        }

        return (int)self::$news_comment_count[$id]['count_comment'];
    }

    /**
     * Sql filter between $_GET['type']
     * most commented
     * most recent news
     * most rated
     */
    protected static function check_NewsFilter() {

        /* Filter Construct */
        $filter = ['recent', 'comment', 'rating'];

        if (isset($_GET['type']) && in_array($_GET['type'], $filter)) {
            $current_filter = $_GET['type'];
            $cat_filter['order'] = 'news_datestamp DESC';
            if ($current_filter == 'recent') {
                // order by datestamp.
                $cat_filter['order'] = 'news_datestamp DESC';
            } else if ($current_filter == 'comment') {
                // order by comment_count
                $cat_filter = [
                    'order' => 'count_comment DESC',
                    //'count' => 'COUNT(td.comment_item_id) AS count_comment,',
                    //'join'  => "LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.news_id AND td.comment_type='N' AND td.comment_hidden='0'",
                ];
            } else if ($current_filter == 'rating') {
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

    public static function get_NewsImage($data, $thumbnail = FALSE, $link = FALSE, $image_width = '') {
        require_once(INCLUDES.'theme_functions_include.php');

        $imageOptimized = IMAGES_N."news_default.jpg";
        $imageRaw = '';

        if (self::get_news_settings('news_image_frontpage')) {
            if ($data['news_cat_image']) {
                $imageOptimized = get_image("nc_".$data['news_cat_name']);
                $imageRaw = $imageOptimized;
            }
        } else {
            if ($data['news_image'] || $data['news_cat_image']) {
                if (!empty($data['news_image_t1']) && file_exists(IMAGES_N_T.$data['news_image_t1'])) {
                    $imageOptimized = IMAGES_N_T.$data['news_image_t1'];
                } else if (!empty($data['news_image_t2']) && file_exists(IMAGES_N_T.$data['news_image_t2'])) {
                    $imageOptimized = IMAGES_N_T.$data['news_image_t2'];
                } else if ($data['news_image'] && file_exists(IMAGES_N.$data['news_image'])) {
                    $imageOptimized = IMAGES_N.$data['news_image'];
                    $imageRaw = $imageOptimized;
                } else {
                    $imageRaw = get_image('imagenotfound');
                }
            } else {
                $imageRaw = get_image('imagenotfound');
            }
        }

        if ($thumbnail) {
            return thumbnail($imageOptimized, ($image_width ?: self::get_news_settings('news_thumb_w')).'px', $link === TRUE && $data['news_extended'] ? INFUSIONS.'/news/news.php?readmore='.$data['news_id'] : FALSE);
        }

        if ($link === TRUE && $data['news_extended']) {
            return "<a class='img-link' href='".INFUSIONS.'news/news.php?readmore='.$data['news_id']."'>\n
            <img class='img-responsive' src='$imageRaw' alt='".$data['news_subject']."' />\n
            </a>\n";
        }

        return "<img class='img-responsive' src='$imageRaw' alt='".$data['news_subject']."' />\n";
    }

    /**
     * Parse MVC Data output
     *
     * @param array $data - dbarray of newsQuery()
     *
     * @return array
     */
    protected static function get_NewsData(array $data) {
        self::$locale = fusion_get_locale('', NEWS_LOCALE);
        $news_settings = self::get_news_settings();

        if (!empty($data)) {

            $news_subject = stripslashes($data['news_subject']);
            $info['news_link'] = $news_settings['news_image_link'] == 0 ? INFUSIONS."news/news.php?cat_id=".$data['news_cat'] : INFUSIONS."news/news.php?readmore=".$data['news_id'];
            $info['print_url'] = BASEDIR."print.php?type=N&amp;item_id=".$data['news_id'];

            $imageSource = IMAGES_N."news_default.jpg";
            $imageRaw = '';
            if (!empty($data['news_cat_image'])) {
                $imageSource = get_image("nc_".$data['news_cat_name']);
                $imageRaw = $imageSource;
            }
            if (!$news_settings['news_image_frontpage']) {
                if (!empty($data['news_image']) && file_exists(IMAGES_N.$data['news_image'])) {
                    $imageSource = IMAGES_N.$data['news_image'];
                    $imageRaw = $imageSource;
                }
                if (!empty($data['news_image_t2']) && file_exists(IMAGES_N_T.$data['news_image_t2'])) {
                    $imageSource = IMAGES_N_T.$data['news_image_t2'];
                }
                if (!empty($data['news_image_t1']) && file_exists(IMAGES_N_T.$data['news_image_t1'])) {
                    $imageSource = IMAGES_N_T.$data['news_image_t1'];
                }
            }

            // Image with link always use the hi-res ones
            $image = "<img class='img-responsive' src='$imageSource' alt='".$data['news_subject']."' />\n";

            if (!empty($data['news_extended'])) {
                $news_image = "<a class='img-link' href='".$info['news_link']."'>$image</a>\n";
            } else {
                $news_image = $image;
            }

            $news_cat_image = "<a href='".$info['news_link']."'>";
            if (!empty($data['news_image_t2']) && $news_settings['news_image_frontpage'] == 0) {
                $news_cat_image .= $image."</a>";
            } else if (!empty($data['news_cat_image'])) {
                $news_cat_image .= "<img src='".get_image("nc_".$data['news_cat_name'])."' alt='".$data['news_cat_name']."' class='img-responsive news-category' /></a>";
            }

            $news_pagenav = "";
            $pagecount = 1;

            $data['news_news'] = parse_textarea($data['news_news'], TRUE, FALSE, TRUE, FALSE, ($data['news_breaks'] == "y" ? TRUE : FALSE));
            $data['news_extended'] = parse_textarea($data['news_extended'], TRUE, FALSE, TRUE, FALSE, ($data['news_breaks'] == "y" ? TRUE : FALSE));

            $news_news = preg_replace("/<!?--\s*pagebreak\s*-->/i", "", $data['news_news']);
            $news_extended = $data['news_extended'];
            if (isset($_GET['readmore'])) {
                if (!empty($news_extended)) {
                    $news_extended = preg_split("/<!?--\s*pagebreak\s*-->/i", $news_extended);
                    $pagecount = count($news_extended);
                    $news_extended = $news_extended[0];
                    if (is_array($news_extended) && isset($_GET['rowstart']) && isnum($_GET['rowstart'])) {
                        $news_extended = $news_extended[$_GET['rowstart']];
                    }
                }
                if ($pagecount > 1) {
                    $news_pagenav = makepagenav($_GET['rowstart'], 1, $pagecount, 3, INFUSIONS."news/news.php?readmore=".$data['news_id']."&amp;");
                }
            }

            $admin_actions = [];
            if (iADMIN && checkrights("N")) {
                $admin_actions = [
                    "edit"   => [
                        'link'  => INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;action=edit&amp;ref=news_form&amp;news_id=".$data['news_id'],
                        'title' => self::$locale['edit']
                    ],
                    "delete" => [
                        'link'  => INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;action=delete&amp;news_id=".$data['news_id'],
                        'title' => self::$locale['delete']
                    ]
                ];
            }

            $news_sum_rating = 0;
            $news_count_votes = 0;
            if ($data['news_allow_ratings']) {
                $news_count_votes = !empty($data['news_count_votes']) ? $data['news_count_votes'] : 0;
                $news_sum_rating = !empty($data['news_sum_rating']) ? $data['news_sum_rating'] : 0;
            }

            $info = [
                "news_id"               => $data['news_id'],
                'news_subject'          => $news_subject,
                'news_link'             => $info['news_link'],
                "news_url"              => INFUSIONS.'news/news.php?readmore='.$data['news_id'],
                "news_cat_link"         => ($data['news_cat_id'] ? INFUSIONS.'news/news.php?cat_id='.$data['news_cat_id'] : ''),
                'news_anchor'           => "<a name='news_".$data['news_id']."' id='news_".$data['news_id']."'></a>",
                'news_news'             => $news_news,
                'news_extended'         => $news_extended,
                'page_count'            => $pagecount,
                "news_keywords"         => $data['news_keywords'],
                "user_id"               => $data['user_id'],
                "user_name"             => $data['user_name'],
                "user_status"           => $data['user_status'],
                "user_avatar"           => $data['user_avatar'],
                'user_level'            => $data['user_level'],
                "news_date"             => $data['news_datestamp'],
                "news_cat_id"           => $data['news_cat'],
                "news_cat_name"         => !empty($data['news_cat_name']) ? $data['news_cat_name'] : fusion_get_locale('news_0006'),
                "news_image_url"        => ($news_settings['news_image_link'] == 0 ? INFUSIONS."news/news.php?cat_id=".$data['news_cat_id'] : INFUSIONS."news/news.php?readmore=".$data['news_id']),
                "news_cat_image"        => $news_cat_image,
                "news_image"            => $news_image, // image with news link enclosed
                'news_image_src'        => $imageRaw, // raw full image
                "news_image_optimized"  => $imageSource, // optimized image
                "news_ext"              => $data['news_extended'] ? "y" : "n",
                "news_reads"            => $data['news_reads'],
                "news_comments"         => $data['count_comment'],
                'news_sum_rating'       => $news_sum_rating,
                'news_count_votes'      => $news_count_votes,
                "news_allow_comments"   => $data['news_allow_comments'],
                "news_display_comments" => $data['news_allow_comments'] ? display_comments($data['count_comment'], INFUSIONS."news/news.php?readmore=".$data['news_id']."#comments", '', 1) : '',
                "news_allow_ratings"    => $data['news_allow_ratings'],
                "news_display_ratings"  => $data['news_allow_ratings'] ? display_ratings($news_sum_rating, $news_count_votes, INFUSIONS."news/news.php?readmore=".$data['news_id']."#postrating", '', 1) : '',
                'news_pagenav'          => $news_pagenav,
                'news_admin_actions'    => $admin_actions,
                "news_sticky"           => $data['news_sticky'],
                "print_link"            => BASEDIR."print.php?type=N&amp;item_id=".$data['news_id'],
            ];
            $info += $data;

            return (array)$info;
        }

        return [];
    }

    /**
     * Executes category information - $_GET['cat_id']
     *
     * @param $news_cat_id
     *
     * @return array
     */
    public function set_NewsCatInfo($news_cat_id) {
        self::$locale = fusion_get_locale('', NEWS_LOCALE);

        $info = [
            'news_cat_id'       => 0,
            'news_cat_name'     => self::$locale['news_0004'],
            'news_cat_image'    => '',
            'news_cat_language' => LANGUAGE,
            'news_categories'   => [],
            'news_image'        => '',
            'news_item_rows'    => 0,
            'news_items'        => []
        ];

        $info = array_merge_recursive($info, self::get_NewsFilter());
        $info = array_merge_recursive($info, self::get_NewsCategory());

        // Filtered by Category ID.
        $result = dbquery("SELECT * FROM ".DB_NEWS_CATS." WHERE ".(multilang_table("NS") ? in_group('news_cat_language', LANGUAGE)." AND " : '')." news_cat_id=:cat_id", [':cat_id' => $news_cat_id]);

        $max_news_rows = '';

        if (dbrows($result)) {
            $data = dbarray($result);
            set_title(self::$locale['news_0004'].self::$locale['global_201']);
            add_to_title($data['news_cat_name']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS.'news/news.php',
                'title' => self::$locale['news_0004']
            ]);
            // Predefined variables, do not edit these values
            $news_cat_index = dbquery_tree(DB_NEWS_CATS, 'news_cat_id', 'news_cat_parent');
            // build categorial data.
            $info['news_cat_id'] = $data['news_cat_id'];
            $info['news_cat_name'] = $data['news_cat_name'];
            $info['news_cat_image_src'] = $data['news_cat_image'] && file_exists(IMAGES_NC.$data['news_cat_image']) ? IMAGES_NC.$data['news_cat_image'] : "";
            $info['news_cat_image'] = $data['news_cat_image'] && file_exists(IMAGES_NC.$data['news_cat_image']) ? "<img class='img-responsive' src='".IMAGES_NC.$data['news_cat_image']."' />" : "<img class='img-responsive' src='holder.js/80x80/text:".self::$locale['no_image']."/grey' />";
            $info['news_cat_language'] = $data['news_cat_language'];

            $max_news_rows = dbcount("(news_id)", DB_NEWS, "news_cat='".$data['news_cat_id']."' AND ".groupaccess('news_visibility')." AND (news_start='0'||news_start<= '".TIME."') AND (news_end='0'||news_end>='".TIME."') AND news_draft='0'");

            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_news_rows ? intval($_GET['rowstart']) : 0;

            if ($max_news_rows) {

                $result = dbquery(self::get_NewsQuery(['condition' => "news_cat='".$data['news_cat_id']."'"]));
                $info['news_item_rows'] = dbrows($result);
                $info['news_total_rows'] = $max_news_rows;
                $this->news_cat_breadcrumbs($news_cat_index);
            }
            //else {
            /*
             * Mlang hub fix #1424
             * Keep for security issues, maybe need redirect or isset errors problem.
             */
            //redirect(INFUSIONS."news/news.php");
            //}
        } else if ($_GET['cat_id'] == 0) {

            $max_news_rows = dbcount("(news_id)", DB_NEWS, "news_cat='0' AND ".groupaccess('news_visibility')." AND (news_start='0'||news_start<='".TIME."')
            AND (news_end='0'||news_end>='".TIME."') AND news_draft='0'");

            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_news_rows ? intval($_GET['rowstart']) : 0;

            if ($max_news_rows) {
                // apply filter.
                $result = dbquery(self::get_NewsQuery(['condition' => 'news_cat=0']));

                BreadCrumbs::getInstance()->addBreadCrumb([
                    'link'  => INFUSIONS."news/news.php?cat_id=".$_GET['cat_id'],
                    'title' => self::$locale['news_0006']
                ]);

                $info['news_total_rows'] = $max_news_rows;
                $info['news_item_rows'] = dbrows($result);
            } else {
                redirect(INFUSIONS."news/news.php");
            }

        } else {
            redirect(INFUSIONS."news/news.php");
        }

        /**
         * Parse
         */
        if ($max_news_rows) {
            $news_count = 0;
            $news_info = [];

            while ($data = dbarray($result)) {
                $news_count++;
                if ($news_count == 1) {
                    $info['news_last_updated'] = showdate('newsdate', $data['news_datestamp']);
                }
                $news_info[$news_count] = self::get_NewsData($data);
            }
            $info['news_items'] = $news_info;
        }
        $this->info = $info;

        return (array)$info;
    }

    /**
     * News Category Breadcrumbs Generator
     *
     * @param $news_cat_index - hierarchy array
     */
    private function news_cat_breadcrumbs($news_cat_index) {
        $locale = fusion_get_locale('', NEWS_LOCALE);

        /* Make an infinity traverse */
        function breadcrumb_arrays($index, $id) {

            $crumb = [];
            if (isset($index[get_parent($index, $id)])) {
                $_name = dbarray(dbquery("SELECT news_cat_id, news_cat_name, news_cat_parent FROM ".DB_NEWS_CATS." WHERE news_cat_id='".$id."'"));
                $crumb = [
                    'link'  => INFUSIONS."news/news.php?cat_id=".$_name['news_cat_id'],
                    'title' => $_name['news_cat_name']
                ];
                if (isset($index[get_parent($index, $id)])) {
                    if (get_parent($index, $id) == 0) {
                        return $crumb;
                    }
                    $crumb_1 = breadcrumb_arrays($index, get_parent($index, $id));
                    $crumb = array_merge_recursive($crumb, $crumb_1); // convert so can comply to Fusion Tab API.
                }
            }

            return $crumb;
        }

        // then we make a infinity recursive function to loop/break it out.
        $crumb = breadcrumb_arrays($news_cat_index, $_GET['cat_id']);
        $title_count = !empty($crumb['title']) && is_array($crumb['title']) ? count($crumb['title']) > 1 : 0;
        // then we sort in reverse.
        if ($title_count) {
            krsort($crumb['title']);
            krsort($crumb['link']);
        }
        if ($title_count) {
            foreach ($crumb['title'] as $i => $value) {
                BreadCrumbs::getInstance()->addBreadCrumb(['link' => $crumb['link'][$i], 'title' => $value]);
                if ($i == count($crumb['title']) - 1) {
                    add_to_title($locale['global_201'].$value);
                }
            }
        } else if (isset($crumb['title'])) {
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => $crumb['link'], 'title' => $crumb['title']]);
        }
    }

    /**
     * Executes single news item information - $_GET['readmore']
     *
     * @param $news_id
     *
     * @return array
     */
    public function set_NewsItemInfo($news_id) {
        self::$locale = fusion_get_locale('', NEWS_LOCALE);
        $info = [];

        set_title(self::$locale['news_0004']);

        BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => INFUSIONS.'news/news.php',
            'title' => self::$locale['news_0004']
        ]);

        $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? intval($_GET['rowstart']) : 0;

        $result = dbquery(
            self::get_NewsQuery(
                [
                    'condition' => 'n.news_id='.intval($news_id),
                    'limit'     => '1'
                ]
            )
        );

        if (dbrows($result)) {
            $data = dbarray($result);

            if ($data['news_keywords'] !== "") {
                set_meta("keywords", $data['news_keywords']);
            }

            if (!isset($_POST['post_comment']) && !isset($_POST['post_rating']) && isset($_GET['readmore']) && empty($_GET['rowstart'])) {
                dbquery("UPDATE ".DB_NEWS." SET news_reads=news_reads+1 WHERE news_id=:read_more", [':read_more' => intval($_GET['readmore'])]);
                $data['news_reads']++;
            }

            $news_subject = $data['news_subject'];

            $_GET['cat_id'] = $data['news_cat_id'];

            add_to_title(self::$locale['global_201'].$news_subject);

            $news_cat_index = dbquery_tree(DB_NEWS_CATS, 'news_cat_id', 'news_cat_parent');

            $this->news_cat_breadcrumbs($news_cat_index);

            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS."news/news.php?readmore=".$data['news_id'],
                'title' => $data['news_subject']
            ]);

            $default_info = [
                'news_item'     => '',
                'news_filter'   => [],
                'news_category' => [],
            ];
            $info = array_merge_recursive($default_info, self::get_NewsFilter());
            $info = array_merge_recursive($info, self::get_NewsCategory());

            $newsData = self::get_NewsData($data);

            $newsData['news_show_ratings'] = self::get_NewsRatings($data, $data['news_id']);
            $newsData['news_show_comments'] = self::get_NewsComments($data, $data['news_id']);
            $newsData['news_gallery'] = self::get_NewsGalleryData($data);

            $info['news_item'] = $newsData;

        } else {
            redirect(INFUSIONS."news/news.php");
        }

        return (array)$info;

    }

    protected static function get_NewsRatings($data, $item_id) {
        $html = '';
        if (fusion_get_settings('ratings_enabled') && $data['news_allow_ratings'] == TRUE) {
            ob_start();
            require_once INCLUDES."ratings_include.php";
            showratings("N", $item_id, BASEDIR."infusions/news/news.php?readmore=".$item_id);
            $html = ob_get_clean();
        }

        return (string)$html;
    }

    protected static function get_NewsComments($data, $item_id) {
        $html = '';
        if (fusion_get_settings('comments_enabled') && $data['news_allow_comments'] == TRUE) {
            $html .= Comments::getInstance(
                [
                    'comment_item_type'     => 'N',
                    'comment_db'            => DB_NEWS,
                    'comment_col'           => 'news_id',
                    'comment_item_id'       => $item_id,
                    'clink'                 => INFUSIONS.'news/news.php?readmore='.$item_id,
                    'comment_count'         => TRUE,
                    'comment_allow_subject' => FALSE,
                    'comment_allow_reply'   => TRUE,
                    'comment_allow_post'    => TRUE,
                    'comment_once'          => FALSE,
                ], 'news_comments'
            )->showComments();
        }

        return (string)$html;
    }

    protected static function get_NewsGalleryData($data) {
        $row = [];
        $result = dbquery("SELECT * FROM ".DB_NEWS_IMAGES." WHERE news_id='".$data['news_id']."'");
        if (dbrows($result) > 0) {
            while ($gData = dbarray($result)) {
                $row[$gData['news_image_id']] = $gData;
            }
        }

        return (array)$row;
    }

    protected function __clone() {
    }
}
