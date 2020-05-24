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

namespace PHPFusion\Infusions\News\Classes;

use PHPFusion\BreadCrumbs;
use PHPFusion\Feedback\Comments;

class News extends NewsHelper {

    protected static $locale = [];

    protected static $news_ratings = [];

    protected static $news_comment_count = [];

    public $info = [];

    public static function getNewsURL($news_id) {
        return INFUSIONS.'news/news.php?readmore='.$news_id;
    }

    public static function getNewsCatURL($news_cat_id) {
        return INFUSIONS.'news/news.php?cat_id='.$news_cat_id;
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
     * Executes main page information
     *
     * @return array
     */
    public function getNewsInfo() {
        $this->loadRSSXML();
        $locale = fusion_get_locale('', NEWS_LOCALE);
        $news_settings = get_settings('news');

        set_title($locale['news_0004']);
        add_breadcrumb([
            'link'  => INFUSIONS.'news/news.php',
            'title' => $locale['news_0004']
        ]);

        $info = [
            'news_cat_id'       => (int)0,
            'news_cat_name'     => $locale['news_0004'],
            'news_cat_image'    => '',
            'news_cat_language' => LANGUAGE,
            'news_categories'   => [],
            'news_image'        => '',
            'news_item_rows'    => 0,
            'news_items'        => [],
            'news_nav'          => ''
        ];

        $info = array_merge_recursive($info, $this->getNewsFilter(), $this->getNewsCategory(), $this->getNewsItem());

        if ($info['max_rows'] > $news_settings['news_pagination']) {
            $type_start = check_get('type') ? 'type='.get('type').'&amp;' : '';
            $cat_start = check_get('cat_id') ? 'cat_id='.get('cat_id', FILTER_VALIDATE_INT).'&amp;' : '';
            $info['news_nav'] = makepagenav($info['rowstart'], $news_settings['news_pagination'], $info['max_rows'], 3, INFUSIONS.'news/news.php?'.$cat_start.$type_start);
        }

        $this->info = $info;

        return (array)$info;
    }

    private function loadRSSXML() {
        if (file_exists(INFUSIONS.'rss_feeds_panel/feeds/rss_news.php')) {
            add_to_head('<link rel="alternate" type="application/rss+xml" title="'.fusion_get_locale('news_0004').' - RSS Feed" href="'.fusion_get_settings('siteurl').'infusions/rss_feeds_panel/feeds/rss_news.php"/>');
        }
    }

    /**
     * Outputs core filters variables
     *
     * @return array
     */
    protected function getNewsFilter() {
        $locale = fusion_get_locale();
        $array['allowed_filters'] = [
            'recent' => $locale['news_0011']
        ];

        if (fusion_get_settings('comments_enabled') == 1) {
            $array['allowed_filters']['comment'] = $locale['news_0012'];
        }

        if (fusion_get_settings('ratings_enabled') == 1) {
            $array['allowed_filters']['rating'] = $locale['news_0013'];
        }

        $i = 0;
        foreach ($array['allowed_filters'] as $type => $filter_name) {
            $filter_link = INFUSIONS."news/news.php?".(isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : '')."type=".$type;
            $array['news_filter'][] = [
                'link'   => $filter_link,
                'title'  => $filter_name,
                'active' => (!isset($_GET['type']) && $i == 0) || isset($_GET['type']) && stristr($filter_link, $_GET['type']) ? 1 : 0
            ];
            unset($filter_link);
            $i++;
        }

        return (array)$array;
    }

    /**
     * Outputs category variables
     *
     * @return mixed
     */
    public function getNewsCategory() {
        $array = [];
        $news_cat = [];
        $result = dbquery("SELECT news_cat_id, news_cat_name, news_cat_parent, news_cat_image, news_cat_visibility FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE ".in_group('news_cat_language', LANGUAGE)." AND " : "WHERE ")." news_cat_draft=0 ORDER BY news_cat_sticky DESC, news_cat_id ASC");
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $id = $data['news_cat_id'];
                $parent_id = $data['news_cat_parent'] === NULL ? "NULL" : $data['news_cat_parent'];
                $array['news_categories'][$parent_id][$id] = [
                    'id'         => $data['news_cat_id'],
                    'link'       => INFUSIONS.'news/news.php?cat_id='.$data['news_cat_id'],
                    'parent'     => $data['news_cat_parent'],
                    'name'       => $data['news_cat_name'],
                    'icon'       => IMAGES_NC.$data['news_cat_image'],
                    'visibility' => $data['news_cat_visibility'],
                    'active'     => get('cat_id') == $data['news_cat_id'] ? 1 : 0
                ];
            }

            $array['news_categories'][0][0] = [
                'id'     => 0,
                'link'   => INFUSIONS."news/news.php?cat_id=0",
                'name'   => fusion_get_locale('news_0006', NEWS_LOCALE),
                'active' => check_get('cat_id') && get('cat_id') == 0 ? 1 : 0
            ];

            foreach ($array['news_categories'][0] as $id => $data) {
                $news_cat['news_categories'][$id] = $data;

                if ($id != 0 && $array['news_categories'] != 0) {
                    foreach ($array['news_categories'] as $sub_cats_id => $sub_cats) {
                        foreach ($sub_cats as $sub_cat_id => $sub_cat_data) {
                            if (!empty($sub_cat_data['parent']) && $sub_cat_data['parent'] == $id) {
                                $news_cat['news_categories'][$id]['sub'][$sub_cat_id] = $sub_cat_data;
                            }
                        }
                    }
                }
            }
        }

        return $news_cat;
    }

    /**
     * Get news item
     *
     * @param array $filter
     *
     * @return array
     */
    public function getNewsItem($filter = []) {
        $info = $this->getNewsQuery($filter);
        if ($info['rows']) {
            $news_count = 0;
            $news_info = [];
            while ($data = dbarray($info['result'])) {
                $news_count++;
                if ($news_count == 1) {
                    $info['news_last_updated'] = showdate('newsdate', $data['news_datestamp']);
                }
                $newsData = self::get_NewsData($data);
                $news_info[$news_count] = $newsData;
            }
            $info['news_items'] = $news_info;
        }
        return (array)$info;
    }

    /**
     * Parse MVC Data output
     *
     * @param array $data - dbarray of newsQuery()
     *
     * @return array
     */
    protected static function get_NewsData(array $data) {
        $locale = fusion_get_locale('', NEWS_LOCALE);
        $news_settings = get_settings('news');

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
            $rowstart = get('rowstart', FILTER_VALIDATE_INT);
            if (isset($_GET['readmore'])) {
                if (!empty($news_extended)) {
                    $news_extended = preg_split("/<!?--\s*pagebreak\s*-->/i", $news_extended);
                    $pagecount = count($news_extended);
                    $news_extended = $news_extended[0];
                    if (is_array($news_extended)) {
                        $news_extended = $news_extended[$rowstart];
                    }
                }
                if ($pagecount > 1) {
                    $news_pagenav = makepagenav($rowstart, 1, $pagecount, 3, INFUSIONS."news/news.php?readmore=".$data['news_id']."&amp;");
                }
            }

            $admin_actions = [];
            if (iADMIN && checkrights("N")) {
                $admin_actions = [
                    "edit"   => [
                        'link'  => INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;action=edit&amp;ref=news_form&amp;news_id=".$data['news_id'],
                        'title' => $locale['edit']
                    ],
                    "delete" => [
                        'link'  => INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;action=delete&amp;news_id=".$data['news_id'],
                        'title' => $locale['delete']
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
        $locale = fusion_get_locale('', NEWS_LOCALE);

        $info = [
            'news_cat_id'       => 0,
            'news_cat_name'     => $locale['news_0004'],
            'news_cat_image'    => '',
            'news_cat_language' => LANGUAGE,
            'news_categories'   => [],
            'news_image'        => '',
            'news_item_rows'    => 0,
            'news_items'        => [],
            'news_nav'          => ''
        ];

        $info = array_merge_recursive($info, $this->getNewsFilter());
        $info = array_merge_recursive($info, $this->getNewsCategory());

        // Filtered by Category ID.
        $result = dbquery("SELECT * FROM ".DB_NEWS_CATS." WHERE ".(multilang_table("NS") ? in_group('news_cat_language', LANGUAGE)." AND " : '')." news_cat_id=:cat_id", [':cat_id' => $news_cat_id]);

        $max_news_rows = '';

        if (dbrows($result)) {
            $data = dbarray($result);
            set_title($locale['news_0004'].$locale['global_201']);
            add_to_title($data['news_cat_name']);
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS.'news/news.php',
                'title' => $locale['news_0004']
            ]);
            // Predefined variables, do not edit these values
            $news_cat_index = dbquery_tree(DB_NEWS_CATS, 'news_cat_id', 'news_cat_parent');
            // build categorial data.
            $info['news_cat_id'] = $data['news_cat_id'];
            $info['news_cat_name'] = $data['news_cat_name'];
            $info['news_cat_image_src'] = $data['news_cat_image'] && file_exists(IMAGES_NC.$data['news_cat_image']) ? IMAGES_NC.$data['news_cat_image'] : "";
            $info['news_cat_image'] = $data['news_cat_image'] && file_exists(IMAGES_NC.$data['news_cat_image']) ? "<img class='img-responsive' src='".IMAGES_NC.$data['news_cat_image']."' />" : "<img class='img-responsive' src='holder.js/80x80/text:".$locale['no_image']."/grey' />";
            $info['news_cat_language'] = $data['news_cat_language'];

            $max_news_rows = dbcount("(news_id)", DB_NEWS, "news_cat='".$data['news_cat_id']."' AND ".groupaccess('news_visibility')." AND (news_start='0'||news_start<= '".TIME."') AND (news_end='0'||news_end>='".TIME."') AND news_draft='0'");
            $rowstart = get_rowstart('rowstart', $max_news_rows);
            if ($max_news_rows) {
                $result = dbquery($this->getNewsQuery(['condition' => "news_cat='".$data['news_cat_id']."'", 'rowstart' => get_rowstart('rowstart', $max_news_rows)]));
                $info['news_item_rows'] = dbrows($result);
                $info['news_total_rows'] = $max_news_rows;

                $this->news_cat_breadcrumbs($news_cat_index);

                $news_settings = get_settings('news');

                if ($info['news_total_rows'] > $news_settings['news_pagination']) {
                    $type_start = isset($_GET['type']) ? 'type='.$_GET['type'].'&amp;' : '';
                    $cat_start = isset($_GET['cat_id']) ? 'cat_id='.$_GET['cat_id'].'&amp;' : '';
                    $info['news_nav'] = makepagenav($rowstart, $news_settings['news_pagination'], $info['news_total_rows'], 3, INFUSIONS.'news/news.php?'.$cat_start.$type_start);
                }
            }
            //else {
            /*
             * Mlang hub fix #1424
             * Keep for security issues, maybe need redirect or isset errors problem.
             */
            //redirect(INFUSIONS."news/news.php");
            //}
        } else if (check_get('cat_id') && !get('cat_id')) {

            $max_news_rows = dbcount("(news_id)", DB_NEWS, "news_cat='0' AND ".groupaccess('news_visibility')." AND (news_start='0'||news_start<='".TIME."')
            AND (news_end='0'||news_end>='".TIME."') AND news_draft='0'");

            $rowstart = get_rowstart('rowstart', $max_news_rows);

            if ($max_news_rows) {
                // apply filter.
                $result = dbquery($this->getNewsQuery(['condition' => 'news_cat=0', 'rowstart' => $rowstart]));
                add_breadcrumb([
                    'link'  => INFUSIONS."news/news.php?cat_id=".$_GET['cat_id'],
                    'title' => $locale['news_0006']
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
        $crumb = breadcrumb_arrays($news_cat_index, get('cat_id'));
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
        $locale = fusion_get_locale('', NEWS_LOCALE);
        set_title($locale['news_0004']);

        BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => INFUSIONS.'news/news.php',
            'title' => $locale['news_0004']
        ]);

        $query = $this->getNewsQuery(
            [
                'condition' => 'n.news_id='.intval($news_id),
                'limit'     => '1',
                'rowstart'  => get('rowstart', FILTER_VALIDATE_INT)
            ]
        );
        $result = dbquery($query['query']);

        if (dbrows($result)) {
            $data = dbarray($result);

            if ($data['news_keywords'] !== "") {
                set_meta("keywords", $data['news_keywords']);
            }

            if (!check_post('post_comment') && !check_post('post_rating') && check_get('readmore') && !get('rowstart', FILTER_VALIDATE_INT)) {
                dbquery("UPDATE ".DB_NEWS." SET news_reads=news_reads+1 WHERE news_id=:read_more", [':read_more' => $data['news_id']]);
                $data['news_reads']++;
            }

            $news_subject = $data['news_subject'];

            $cat_id = get('cat_id', FILTER_VALIDATE_INT);

            add_to_title($locale['global_201'].$news_subject);
            $news_cat_index = dbquery_tree(DB_NEWS_CATS, 'news_cat_id', 'news_cat_parent');
            $this->news_cat_breadcrumbs($news_cat_index);

            add_breadcrumb([
                'link'  => INFUSIONS."news/news.php?readmore=".$data['news_id'],
                'title' => $data['news_subject']
            ]);

            $default_info = [
                'news_item'     => '',
                'news_filter'   => [],
                'news_category' => [],
            ];
            $info = array_merge_recursive($default_info, $this->getNewsFilter());
            $info = array_merge_recursive($info, $this->getNewsCategory());

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
