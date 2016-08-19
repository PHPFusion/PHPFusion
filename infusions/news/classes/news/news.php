<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
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

use PHPFusion\SiteLinks;

class News extends NewsServer {

    public $info = array();

    /**
     * Executes main page information
     * @return array
     */
    public function set_NewsInfo() {

        $news_settings = $this->get_news_settings();

        $locale = fusion_get_locale('', NEWS_LOCALE);

        set_title(SiteLinks::get_current_SiteLinks("", "link_name"));

        add_breadcrumb(array(
                           'link' => INFUSIONS.'news/news.php',
                           'title' => SiteLinks::get_current_SiteLinks("", "link_name")
                       ));

        $info = array(
            'news_cat_id' => intval(0),
            'news_cat_name' => $locale['news_0007'],
            'news_cat_image' => '',
            'news_cat_language' => LANGUAGE,
            'news_categories' => array(),
            'news_image' => '',
            'news_item_rows' => 0,
            'news_last_updated' => 0,
            'news_items' => array()
        );


        $info['allowed_filters'] = array(
            'recent' => $locale['news_0011'],
            'comment' => $locale['news_0012'],
            'rating' => $locale['news_0013']
        );

        foreach ($info['allowed_filters'] as $type => $filter_name) {
            $filter_link = INFUSIONS."news/news.php?".(isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : '')."type=".$type;
            $info['news_filter'][$filter_link] = $filter_name;
            unset($filter_link);
        }

        /* News Category */
        $result = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS."
        ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : '')." ORDER BY news_cat_id ASC");
        if (dbrows($result) > 0) {
            while ($cdata = dbarray($result)) {
                $info['news_categories'][$cdata['news_cat_id']] = array(
                    'link' => INFUSIONS.'news.php?cat_id='.$cdata['news_cat_id'],
                    'name' => $cdata['news_cat_name']
                );
            }
        }

        $max_news_rows = dbcount("(news_id)", DB_NEWS, groupaccess('news_visibility')." AND (news_start='0'||news_start<=NOW())
		AND (news_end='0'||news_end>=NOW()) AND news_draft='0'");

        if ($max_news_rows) {

            $info['news_total_rows'] = $max_news_rows;

            // Xss
            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_news_rows ? intval($_GET['rowstart']) : 0;

            $result = dbquery($this->get_NewsQuery());

            $info['news_item_rows'] = dbrows($result);
            if ($info['news_item_rows'] > 0) {
                $news_count = 0;
                while ($data = dbarray($result)) {
                    $news_count++;
                    if ($news_count == 1) {
                        $info['news_last_updated'] = $data['news_datestamp'];
                    }
                    $news_info[$news_count] = self::get_NewsData($data);
                }
                $info['news_items'] = $news_info;
            }
        }

        $this->info = $info;

        return (array)$info;

    }

    /**
     * @param array $filters array('condition', 'order', 'limit')
     * @return string
     */
    public static function get_NewsQuery( array $filters = array() ) {

        $news_settings = self::get_news_settings();

        return "SELECT tn.*, tc.*,
				tu.user_id, tu.user_name, tu.user_status, tu.user_avatar , tu.user_level, tu.user_joined,
				SUM(tr.rating_vote) AS sum_rating,
				COUNT(tr.rating_item_id) AS count_votes,
				COUNT(td.comment_item_id) AS count_comment
				FROM ".DB_NEWS." tn
				LEFT JOIN ".DB_USERS." tu ON tn.news_name=tu.user_id
				LEFT JOIN ".DB_NEWS_CATS." tc ON tn.news_cat=tc.news_cat_id
				LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = tn.news_id AND tr.rating_type='N'
				LEFT JOIN ".DB_COMMENTS." td ON td.comment_item_id = tn.news_id AND td.comment_type='N' AND td.comment_hidden='0'
				".(multilang_table("NS") ? "WHERE news_language='".LANGUAGE."' AND" : "WHERE")."
				".groupaccess('news_visibility')." AND (news_start='0'||news_start<=NOW())
				AND (news_end='0'||news_end>=NOW()) AND news_draft='0'
				".(!empty($filters['condition']) ? "AND ".$filters['condition'] : "")."
				GROUP BY ".(!empty($filters['group_by']) ? $filters['group_by'] : 'news_id')."
				ORDER BY ".(!empty($filter['order']) ? $filters['order'] : "")." news_sticky DESC, ".self::get_NewsFilter()."
				LIMIT ".(!empty($filters['limit']) ? $filters['limit'] : $_GET['rowstart'].",".$news_settings['news_pagination']);
    }

    /**
     * Sql filter between $_GET['type']
     * most commented
     * most recent news
     * most rated
     */
    private static function get_NewsFilter() {

        /* Filter Construct */
        $filter = array('recent', 'comment', 'rating');


        if (isset($_GET['type']) && in_array($_GET['type'], $filter)) {
            $current_filter = $_GET['type'];
            $cat_filter = 'news_datestamp DESC';
            if ($current_filter == 'recent') {
                // order by datestamp.
                $cat_filter = 'news_datestamp DESC';
            } elseif ($current_filter == 'comment') {
                // order by comment_count
                $cat_filter = 'count_comment DESC';
            } elseif ($current_filter == 'rating') {
                // order by download_title
                $cat_filter = 'sum_rating DESC';
            }
        } else {
            $cat_filter = 'news_datestamp DESC';
        }

        return (string)$cat_filter;
    }

    /**
     * Parse MVC Data output
     * @param array $data - dbarray of newsQuery()
     * @return array
     */
    public static function get_NewsData(array $data) {

        $news_settings = self::get_news_settings();

        if (!empty($data)) {

            $largeImg = "";
            $news_subject = stripslashes($data['news_subject']);

            $imageSource = IMAGES_N."news_default.jpg";
            if ($data['news_cat_image']) {
                $imageSource = get_image("nc_".$data['news_cat_name']);
            }
            if ($news_settings['news_image_frontpage'] == 0) {
                if ($data['news_image'] && file_exists(IMAGES_N.$data['news_image'])) {
                    $imageSource = IMAGES_N.$data['news_image'];
                    $largeImg = $imageSource;
                }
                if ($data['news_image_t2'] && file_exists(IMAGES_N_T.$data['news_image_t2'])) {
                    $imageSource = IMAGES_N_T.$data['news_image_t2'];
                }
                if ($data['news_image_t1'] && file_exists(IMAGES_N_T.$data['news_image_t1'])) {
                    $imageSource = IMAGES_N_T.$data['news_image_t1'];
                }
            }
            $image = "<img class='img-responsive' src='".$imageSource."' alt='".$data['news_subject']."' />\n";
            if (!empty($data['news_extended'])) {
                $news_image_link = ($news_settings['news_image_link'] == 0 ? INFUSIONS."news/news.php?cat_id=".$data['news_cat'] : INFUSIONS."news/news.php?readmore=".$data['news_id']);
                $news_image = "<a class='img-link' href='$news_image_link'>$image</a>\n";
            } else {
                $news_image = $image;
            }

            $news_cat_image = "<a href='".($news_settings['news_image_link'] == 0 ? "".INFUSIONS."news/news.php?cat_id=".$data['news_cat'] : INFUSIONS."news/news.php?readmore=".$data['news_id'])."'>";

            if ($data['news_image_t2'] && $news_settings['news_image_frontpage'] == 0) {
                $news_cat_image .= $image."</a>";
            } elseif ($data['news_cat_image']) {
                $news_cat_image .= "<img src='".get_image("nc_".$data['news_cat_name'])."' alt='".$data['news_cat_name']."' class='img-responsive news-category' /></a>";
            }

            $news_news = preg_replace("/<!?--\s*pagebreak\s*-->/i", "", ($data['news_breaks'] == "y" ?
                nl2br(parse_textarea($data['news_news'])) : parse_textarea($data['news_news'])
            ));

            $info = array(
                "news_id" => $data['news_id'],
                'news_subject' => $news_subject,
                "news_url" => INFUSIONS.'news/news.php?readmore='.$data['news_id'],
                "news_cat_url" => INFUSIONS.'news/news.php?cat_id='.$data['news_cat_id'],
                "news_image_url" => ($news_settings['news_image_link'] == 0 ? INFUSIONS."news/news.php?cat_id=".$data['news_cat_id'] : INFUSIONS."news/news.php?readmore=".$data['news_id']),
                'news_anchor' => "<a name='news_".$data['news_id']."' id='news_".$data['news_id']."'></a>",
                'news_news' => $news_news,
                "news_keywords" => $data['news_keywords'],
                "user_id" => $data['user_id'],
                "user_name" => $data['user_name'],
                "user_status" => $data['user_status'],
                "user_avatar" => $data['user_avatar'],
                'user_level' => $data['user_level'],
                "news_date" => $data['news_datestamp'],
                "news_cat_id" => $data['news_cat'],
                "news_cat_name" => !empty($data['news_cat_name']) ? $data['news_cat_name'] : fusion_get_locale('news_0006'),
                "news_cat_image" => $news_cat_image,
                "news_image" => $news_image,
                'news_image_src' => $largeImg,
                "news_image_optimized" => $imageSource,
                "news_ext" => $data['news_extended'] ? "y" : "n",
                "news_reads" => $data['news_reads'],
                "news_comments" => $data['count_comment'],
                'news_sum_rating' => $data['sum_rating'] ? $data['sum_rating'] : 0,
                'news_count_votes' => $data['count_votes'],
                "news_allow_comments" => $data['news_allow_comments'],
                "news_display_comments" => $data['news_allow_comments'] ? display_comments($data['count_comment'],
                                                                                           INFUSIONS."news/news.php?readmore=".$data['news_id']."#comments",
                                                                                           '', 2) : '',
                "news_allow_ratings" => $data['news_allow_ratings'],
                "news_display_ratings" => $data['news_allow_ratings'] ? display_ratings($data['sum_rating'], $data['count_votes'], INFUSIONS."news/news.php?readmore=".$data['news_id']."#postrating", '', 2) : '',
                "news_sticky" => $data['news_sticky'],
                "print_link" => BASEDIR."print.php?type=N&amp;item_id=".$data['news_id'],
            );
            $info += $data;
            return (array) $info;

        }

        return array();
    }

    /**
     * Executes category information - $_GET['cat_id']
     * @param $news_cat_id
     * @return array
     */
    public function set_NewsCatInfo($news_cat_id) {

        $locale = fusion_get_locale('', NEWS_LOCALE);

        $info = array(
            'news_cat_id' => 0,
            'news_cat_name' => $locale['news_0007'],
            'news_cat_image' => '',
            'news_cat_language' => LANGUAGE,
            'news_categories' => array(),
            'news_image' => '',
            'news_item_rows' => 0,
            'news_last_updated' => 0,
            'news_items' => array()
        );

        $info['allowed_filters'] = array(
            'recent' => $locale['news_0011'],
            'comment' => $locale['news_0012'],
            'rating' => $locale['news_0013']
        );

        foreach ($info['allowed_filters'] as $type => $filter_name) {
            $filter_link = INFUSIONS."news/news.php?".(isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : '')."type=".$type;
            $info['news_filter'][$filter_link] = $filter_name;
            unset($filter_link);
        }

        /* News Category */
        $result = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS."
        ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : '')." ORDER BY news_cat_id ASC");
        if (dbrows($result) > 0) {
            while ($cdata = dbarray($result)) {
                $info['news_categories'][$cdata['news_cat_id']] = array(
                    'link' => INFUSIONS.'news.php?cat_id='.$cdata['news_cat_id'],
                    'name' => $cdata['news_cat_name']
                );
            }
        }

        // Filtered by Category ID.
        $result = dbquery("SELECT * FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."' AND" : "WHERE")." news_cat_id='".intval($news_cat_id)."'");

        if (dbrows($result)) {

            $data = dbarray($result);

            set_title(SiteLinks::get_current_SiteLinks("", "link_name"));

            add_breadcrumb(array(
                               'link' => INFUSIONS.'news/news.php',
                               'title' => SiteLinks::get_current_SiteLinks("", "link_name")
                           ));
            add_to_title($locale['global_201'].$data['news_cat_name']);

            // Predefined variables, do not edit these values
            $news_cat_index = dbquery_tree(DB_NEWS_CATS, 'news_cat_id', 'news_cat_parent');

            // build categorial data.
            $info['news_cat_id'] = $data['news_cat_id'];
            $info['news_cat_name'] = $data['news_cat_name'];
            $info['news_cat_image_src'] = $data['news_cat_image'] && file_exists(IMAGES_NC.$data['news_cat_image']) ? IMAGES_NC.$data['news_cat_image'] : "";
            $info['news_cat_image'] = $data['news_cat_image'] && file_exists(IMAGES_NC.$data['news_cat_image']) ? "<img class='img-responsive' src='".IMAGES_NC.$data['news_cat_image']."' />" : "<img class='img-responsive' src='holder.js/80x80/text:".$locale['no_image']."/grey' />";
            $info['news_cat_language'] = $data['news_cat_language'];

            $max_news_rows = dbcount("(news_id)", DB_NEWS, "news_cat='".$data['news_cat_id']."' AND
			".groupaccess('news_visibility')." AND (news_start='0'||news_start<= NOW()) AND
			(news_end='0'||news_end>=NOW()) AND news_draft='0'");

            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_news_rows ? intval($_GET['rowstart']) : 0;

            if ($max_news_rows) {

                $result = dbquery($this->get_NewsQuery(array(
                                                           'condition' => "news_cat='".$data['news_cat_id']."'"
                                                       )));
                $info['news_item_rows'] = dbrows($result);
                $info['news_total_rows'] = $max_news_rows;
                $this->news_cat_breadcrumbs($news_cat_index);
            } else {
                redirect(INFUSIONS."news/news.php");
            }

        } elseif ($_GET['cat_id'] == 0) {

            $max_news_rows = dbcount("(news_id)", DB_NEWS, "news_cat='0' AND ".groupaccess('news_visibility')." AND (news_start='0'||news_start<=NOW())
			AND (news_end='0'||news_end>=NOW()) AND news_draft='0'");

            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_news_rows ? intval($_GET['rowstart']) : 0;

            if ($max_news_rows) {
                // apply filter.
                $result = dbquery($this->get_NewsQuery( array('condition' => 'news_cat=0')));
                add_breadcrumb(array(
                                   'link' => INFUSIONS."news/news.php?cat_id=".$_GET['cat_id'],
                                   'title' => $locale['news_0006']
                               ));
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
            while ($data = dbarray($result)) {
                $news_count++;
                if ($news_count == 1) {
                    $info['news_last_updated'] = $data['news_datestamp'];
                }
                $news_info[$news_count] = self::get_NewsData($data);
            }
            $info['news_items'] = $news_info;
        }

        $this->info = $info;
        return (array) $info;
    }

    /**
     * News Category Breadcrumbs Generator
     * @param $news_cat_index - hierarchy array
     */
    private function news_cat_breadcrumbs($news_cat_index) {

        $locale = fusion_get_locale('', NEWS_LOCALE);

        /* Make an infinity traverse */
        function breadcrumb_arrays($index, $id) {
            $crumb = &$crumb;
            if (isset($index[get_parent($index, $id)])) {
                $_name = dbarray(dbquery("SELECT news_cat_id, news_cat_name, news_cat_parent FROM ".DB_NEWS_CATS." WHERE news_cat_id='".$id."'"));
                $crumb = array(
                    'link' => INFUSIONS."news/news.php?cat_id=".$_name['news_cat_id'],
                    'title' => $_name['news_cat_name']
                );
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
        // then we sort in reverse.
        if (count($crumb['title']) > 1) {
            krsort($crumb['title']);
            krsort($crumb['link']);
        }
        if (count($crumb['title']) > 1) {
            foreach ($crumb['title'] as $i => $value) {
                add_breadcrumb(array('link' => $crumb['link'][$i], 'title' => $value));
                if ($i == count($crumb['title']) - 1) {
                    add_to_title($locale['global_201'].$value);
                }
            }
        } elseif (isset($crumb['title'])) {
            add_to_title($locale['global_201'].$crumb['title']);
            add_breadcrumb(array('link' => $crumb['link'], 'title' => $crumb['title']));
        }
    }

    /**
     * Executes single news item information - $_GET['readmore']
     * @param $news_id
     */
    public function set_NewsItemInfo($news_id) {

        $locale = fusion_get_locale('', NEWS_LOCALE);
        $settings = fusion_get_settings();

        set_title(SiteLinks::get_current_SiteLinks("", "link_name"));

        add_breadcrumb(array(
                           'link' => INFUSIONS.'news/news.php',
                           'title' => SiteLinks::get_current_SiteLinks("", "link_name")
                       ));

        $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;
        $result = dbquery(self::get_NewsQuery(array('condition' => 'news_id='.intval($news_id))));

        if (dbrows($result) > 0) {

            include INCLUDES."comments_include.php";
            include INCLUDES."ratings_include.php";

            $data = dbarray($result);

            if ($data['news_keywords'] !== "") {
                set_meta("keywords", $data['news_keywords']);
            }

            if (!isset($_POST['post_comment']) && !isset($_POST['post_rating'])) {
                $result2 = dbquery("UPDATE ".DB_NEWS." SET news_reads=news_reads+1 WHERE news_id='".$_GET['readmore']."'");
                $data['news_reads']++;
            }

            $news_subject = $data['news_subject'];

            $news_news = preg_split("/<!?--\s*pagebreak\s*-->/i", $data['news_breaks'] == "y" ?
                nl2br(parse_textarea($data['news_extended'] ? $data['news_extended'] : $data['news_news'])) :
                parse_textarea($data['news_extended'] ? $data['news_extended'] : $data['news_news'])
            );

            $pagecount = count($news_news);

            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $pagecount  ? $_GET['rowstart'] : 0;

            $admin_actions = array();
            if (iADMIN && checkrights("N")) {
                $admin_actions = array(
                    "edit" => INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;action=edit&amp;section=nform&amp;news_id=".$data['news_id'],
                    "delete" => INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;action=delete&amp;section=nform&amp;news_id=".$data['news_id'],
                );
            }

            $news_info = $this->get_NewsData($data);
            /*
                array(
                "news_id" => $data['news_id'],
                "user_id" => $data['user_id'],
                "user_name" => $data['user_name'],
                "user_status" => $data['user_status'],
                "user_joined" => $data['user_joined'],
                "user_level" => $data['user_level'],
                "user_avatar" => $data['user_avatar'],
                "news_datestamp" => $data['news_datestamp'],
                "news_ialign" => $data['news_ialign'],
                "cat_id" => $data['news_cat'],
                "news_cat_name" => $data['news_cat_name'],
                "news_cat_image_src" => !empty($data['news_cat_image']) && file_exists(IMAGES_NC.$data['news_cat_image']) ? IMAGES_NC.$data['news_cat_image'] : "",
                "news_image_src" => !empty($data['news_image']) && file_exists(IMAGES_N.$data['news_image']) ? IMAGES_N.$data['news_image'] : "",
                "cat_image" => $data['news_cat_image'],
                "news_subject" => $data['news_subject'],
                "news_descr" => $data['news_news'],
                "news_cat_url" => INFUSIONS.'news/news.php?cat_id='.$data['news_cat'],
                'news_url' => INFUSIONS.'news/news.php?readmore='.$data['news_id'],
                'news_news' => $news_news[$_GET['rowstart']],
                "news_ext" => "n",
                "news_keywords" => $data['news_keywords'],
                "news_reads" => $data['news_reads'],
                "news_comments" => $data['count_comment'],
                'news_sum_rating' => $data['sum_rating'] ? $data['sum_rating'] : 0,
                'news_count_votes' => $data['count_votes'],
                "news_allow_comments" => $data['news_allow_comments'],
                'news_allow_ratings' => $data['news_allow_ratings'],
                "news_sticky" => $data['news_sticky'],
                "print_link" => BASEDIR."print.php?type=N&amp;item_id=".$data['news_id'],
                'admin_actions' => $admin_actions,
            ); */


            if (fusion_get_settings("create_og_tags")) {
                add_to_head("<meta property='og:title' content='".$data['news_subject']."' />");
                add_to_head("<meta property='og:description' content='".strip_tags($data['news_news'])."' />");
                add_to_head("<meta property='og:site_name' content='".fusion_get_settings('sitename')."' />");
                add_to_head("<meta property='og:type' content='article' />");
                add_to_head("<meta property='og:url' content='".$settings['siteurl']."infusions/news.php?readmore=".$_GET['readmore']."' />");
                if ($data['news_image']) {
                    $og_image = IMAGES_N.$data['news_image'];
                } else {
                    $og_image = IMAGES_NC.$data['news_cat_image'];
                }
                $og_image = str_replace(BASEDIR, $settings['siteurl'], $og_image);
                add_to_head("<meta property='og:image' content='".$og_image."' />");
            }

            $_GET['cat_id'] = $data['news_cat_id'];

            set_title($news_subject.$locale['global_200'].$locale['news_0004']);

            $news_cat_index = dbquery_tree(DB_NEWS_CATS, 'news_cat_id', 'news_cat_parent');
            $this->news_cat_breadcrumbs($news_cat_index);

            add_breadcrumb(array(
                               'link' => INFUSIONS."news/news.php?readmore=".$data['news_id'],
                               'title' => $data['news_subject']
                           ));

            $info['news_item'] = $news_info;
            $info['news_item']['page_count'] = $pagecount;

        } else {
            redirect(INFUSIONS."news/news.php");
        }

        return $info;

    }
}