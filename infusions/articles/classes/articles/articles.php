<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: articles.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Articles;

abstract class Articles extends ArticlesServer {
    private static $locale = [];
    public $info = [];

    protected function __construct() {
    }

    /**
     * Executes main page information
     *
     * @return array
     */
    public function setArticlesInfo() {
        self::$locale = fusion_get_locale("", ARTICLE_LOCALE);

        if (file_exists(INFUSIONS.'rss_feeds_panel/feeds/rss_articles.php')) {
            add_to_head('<link rel="alternate" type="application/rss+xml" title="'.fusion_get_locale('article_0000').' - RSS Feed" href="'.fusion_get_settings('siteurl').'infusions/rss_feeds_panel/feeds/rss_articles.php"/>');
        }

        set_title(self::$locale['article_0000']);

        add_breadcrumb(['link' => INFUSIONS.'articles/articles.php', 'title' => self::$locale['article_0000']]);

        $info = [
            'article_cat_id'          => 0,
            'article_cat_name'        => self::$locale['article_0001'],
            'article_cat_description' => '',
            'article_cat_language'    => LANGUAGE,
            'article_categories'      => [],
            'article_item_rows'       => 0,
            'article_last_updated'    => 0,
            'article_items'           => []
        ];
        $info = array_merge($info, self::getArticleFilters());
        $info = array_merge($info, self::getArticleCategories());
        $info = array_merge($info, self::getArticleItems());
        $this->info = $info;

        return $info;

    }

    /**
     * Outputs core filters variables
     *
     * @return array
     */
    private function getArticleFilters() {
        $array['allowed_filters'] = [
            'recent' => self::$locale['article_0030'],
            'read'   => self::$locale['article_0063']
        ];

        if (fusion_get_settings('comments_enabled') == 1) {
            $array['allowed_filters']['comment'] = self::$locale['article_0031'];
        }

        if (fusion_get_settings('ratings_enabled') == 1) {
            $array['allowed_filters']['rating'] = self::$locale['article_0032'];
        }

        foreach ($array['allowed_filters'] as $type => $filter_name) {
            $filter_link = INFUSIONS."articles/articles.php?".(get('cat_id', FILTER_VALIDATE_INT) ? "cat_id=".$_GET['cat_id']."&amp;" : "")."type=".$type;
            $array['article_filter'][$filter_link] = $filter_name;
            unset($filter_link);
        }

        return $array;
    }

    /**
     * Outputs category variables
     *
     * @return array
     */
    protected function getArticleCategories() {
        $info['article_categories'] = [];
        $result = dbquery("SELECT article_cat_id, article_cat_name
            FROM ".DB_ARTICLE_CATS."
            WHERE article_cat_status='1' AND ".groupaccess("article_cat_visibility")."
            ".(multilang_table("AR") ? " AND ".in_group('article_cat_language', LANGUAGE) : "")."
            ORDER BY article_cat_id ASC
        ");
        if (dbrows($result) > 0) {
            while ($cdata = dbarray($result)) {
                $info['article_categories'][$cdata['article_cat_id']] = [
                    'link' => INFUSIONS."articles/articles.php?cat_id=".$cdata['article_cat_id'],
                    'name' => $cdata['article_cat_name']
                ];
            }
        }

        return $info;
    }

    /**
     * Get article item
     *
     * @param array $filter
     *
     * @return array
     */
    public function getArticleItems($filter = []) {
        $info['article_total_rows'] = dbcount("(article_id)", DB_ARTICLES, groupaccess("article_visibility")." AND article_draft='0'");

        if ($info['article_total_rows']) {
            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['article_total_rows'] ? intval($_GET['rowstart']) : 0;

            $result = dbquery($this->getArticlesQuery($filter));

            $info['article_item_rows'] = dbrows($result);
            if ($info['article_item_rows'] > 0) {
                $article_count = 0;
                $article_info = [];
                while ($data = dbarray($result)) {

                    $article_count++;
                    if ($article_count == 1) {
                        $info['article_last_updated'] = $data['article_datestamp'];
                    }

                    $articleData = self::getArticlesData($data);
                    $article_info[$article_count] = $articleData;

                }
                $info['article_items'] = $article_info;
            }
        }

        return $info;
    }

    /**
     * @param array $filters array('condition', 'order', 'limit')
     *
     * @return string
     */
    protected static function getArticlesQuery(array $filters = []) {
        $article_settings = self::getArticleSettings();
        $pattern = "SELECT %s(ar.rating_vote) FROM ".DB_RATINGS." ar WHERE ar.rating_item_id = a.article_id AND ar.rating_type = 'A'";
        $sql_count = sprintf($pattern, 'COUNT');
        $sql_sum = sprintf($pattern, 'SUM');

        return "SELECT a.*, ac.*, au.user_id, au.user_name, au.user_status, au.user_avatar, au.user_level, au.user_joined,
            ($sql_sum) AS sum_rating,
            ($sql_count) AS count_votes,
            (SELECT COUNT(ad.comment_id) FROM ".DB_COMMENTS." ad WHERE ad.comment_item_id = a.article_id AND ad.comment_type = 'A') AS comments_count
            FROM ".DB_ARTICLES." AS a
            LEFT JOIN ".DB_USERS." AS au ON a.article_name=au.user_id
            LEFT JOIN ".DB_ARTICLE_CATS." AS ac ON a.article_cat=ac.article_cat_id
            ".(multilang_table("AR") ? "WHERE ".in_group('a.article_language', LANGUAGE)." AND ".in_group('ac.article_cat_language', LANGUAGE)." AND " : "WHERE ")."
            a.article_draft='0' AND ".groupaccess("a.article_visibility")." AND ac.article_cat_status='1' AND ".groupaccess("ac.article_cat_visibility")."
            ".(!empty($filters['condition']) ? " AND ".$filters['condition'] : "")."
            GROUP BY a.article_id
            ORDER BY ".self::checkArticlesFilter()."
            LIMIT ".(!empty($filters['limit']) ? $filters['limit'] : "".$_GET['rowstart'].",".(!empty($article_settings['article_pagination']) ? $article_settings['article_pagination'] : 15)."")."
        ";
    }

    /**
     * Sql filter between $_GET['type']
     * most commented
     * most recent article
     * most rated
     */
    private static function checkArticlesFilter() {
        /* Filter Construct */
        $filter = ['recent', 'read', 'comment', 'rating'];

        if (isset($_GET['type']) && in_array($_GET['type'], $filter)) {
            switch ($_GET['type']) {
                case "recent":
                    $catfilter = "a.article_datestamp DESC";
                    break;
                case "read":
                    $catfilter = "a.article_reads DESC";
                    break;
                case "comment":
                    $catfilter = "comments_count DESC";
                    break;
                case "rating":
                    $catfilter = "sum_rating DESC";
                    break;
                default:
                    $catfilter = "a.article_datestamp DESC";
            }
        } else {
            $catfilter = "a.article_datestamp DESC";
        }

        return $catfilter;
    }

    /**
     * Parse MVC Data output
     *
     * @param array $data dbarray of articleQuery()
     *
     * @return array
     */
    private static function getArticlesData(array $data) {
        self::$locale = fusion_get_locale("", ARTICLE_LOCALE);

        if (!empty($data)) {

            // Subject
            $articleSubject = stripslashes($data['article_subject']);
            // Page Nav
            $articlePagenav = "";
            $pagecount = 1;

            // Article Texts
            $data['article_snippet'] = parse_text($data['article_snippet'], [
                'parse_smileys'        => FALSE,
                'default_image_folder' => NULL,
                'add_line_breaks'      => $data['article_breaks'] == 'y'
            ]);

            $data['article_article'] = parse_text($data['article_article'], [
                'parse_smileys'        => FALSE,
                'parse_bbcode'         => FALSE,
                'default_image_folder' => NULL,
                'add_line_breaks'      => $data['article_breaks'] == 'y'
            ]);

            $articleText = preg_replace("/<!?--\s*pagebreak\s*-->/i", "", $data['article_snippet']);

            // Handle Text
            if (isset($_GET['article_id'])) {
                $articleText = !empty($data['article_article']) ? $data['article_article'] : $data['article_snippet'];

                // Handle Pages
                $articleText = preg_split("/<!?--\s*pagebreak\s*-->/i", $articleText);
                $pagecount = count($articleText);
                if (is_array($articleText)) {
                    $articleText = $articleText[$_GET['rowstart']];
                }
                if ($pagecount > 1) {
                    $articlePagenav = makepagenav($_GET['rowstart'], 1, $pagecount, 3, INFUSIONS."articles/articles.php?article_id=".$data['article_id']."&amp;");
                }

            }

            // Admin Informations
            $adminActions = [];
            if (iADMIN && checkrights("A")) {
                $adminActions = [
                    'edit'   => [
                        'link'  => INFUSIONS."articles/articles_admin.php".fusion_get_aidlink()."&amp;action=edit&amp;ref=article_form&amp;article_id=".$data['article_id'],
                        'title' => self::$locale['edit']
                    ],
                    'delete' => [
                        'link'  => INFUSIONS."articles/articles_admin.php".fusion_get_aidlink()."&amp;action=delete&amp;ref=article_form&amp;article_id=".$data['article_id'],
                        'title' => self::$locale['delete']
                    ]
                ];
            }

            // Build Array
            $info = [
                # Article Category
                'article_cat_id'           => $data['article_cat'],
                'article_cat_name'         => $data['article_cat_name'],
                # Article Informations
                'article_id'               => $data['article_id'],
                'article_subject'          => $articleSubject,
                'article_article'          => $articleText,
                'article_keywords'         => $data['article_keywords'],
                'article_ext'              => $data['article_article'] ? "y" : "n",
                # Article Author
                'user_id'                  => $data['user_id'],
                'user_name'                => $data['user_name'],
                'user_status'              => $data['user_status'],
                'user_avatar'              => $data['user_avatar'],
                'user_level'               => $data['user_level'],
                # Article Stats
                'article_reads'            => $data['article_reads'],
                'article_date'             => $data['article_datestamp'],
                # Comments and Ratings
                'article_comments'         => $data['comments_count'],
                'article_sum_rating'       => !empty($data['sum_rating']) ? $data['sum_rating'] : 0,
                'article_count_votes'      => $data['count_votes'],
                'article_allow_comments'   => $data['article_allow_comments'],
                'article_allow_ratings'    => $data['article_allow_ratings'],
                'article_display_comments' => $data['article_allow_comments'] ? display_comments($data['comments_count'], INFUSIONS."articles/articles.php?article_id=".$data['article_id']."#comments", "", 2) : "",
                'article_display_ratings'  => $data['article_allow_ratings'] ? display_ratings($data['sum_rating'], $data['count_votes'], INFUSIONS."articles/articles.php?article_id=".$data['article_id']."#postrating", "", 2) : "",
                # Links and Admin Actions
                'article_url'              => INFUSIONS."articles/articles.php?article_id=".$data['article_id'],
                'article_cat_url'          => INFUSIONS."articles/articles.php?cat_id=".$data['article_cat_id'],
                'article_anchor'           => "<a name='article_".$data['article_id']."' id='article_".$data['article_id']."'></a>",
                'print_link'               => BASEDIR."print.php?type=A&amp;item_id=".$data['article_id'],
                'admin_actions'            => $adminActions,
                # Page Nav
                'page_count'               => $pagecount,
                'article_pagenav'          => $articlePagenav
            ];
            $info += $data;

            return $info;
        }

        return [];
    }

    /**
     * Executes category information - $_GET['cat_id']
     *
     * @param $article_cat_id
     *
     * @return array
     */
    public function setArticlesCatInfo($article_cat_id) {
        self::$locale = fusion_get_locale("", ARTICLE_LOCALE);

        $info = [
            'article_cat_id'          => 0,
            'article_cat_name'        => self::$locale['article_0001'],
            'article_cat_description' => '',
            'article_cat_language'    => LANGUAGE,
            'article_categories'      => [],
            'article_item_rows'       => 0,
            'article_last_updated'    => 0,
            'article_items'           => []
        ];
        $info = array_merge($info, self::getArticleFilters());
        $info = array_merge($info, self::getArticleCategories());

        $max_article_rows = '';

        // Filtered by Category ID.
        $select = "SELECT * FROM ".DB_ARTICLE_CATS." WHERE ".(multilang_table("AR") ? in_group('article_cat_language', LANGUAGE)." AND " : '')." article_cat_id=:cat_id AND article_cat_status=:status AND ".groupaccess("article_cat_visibility");
        $bind = [
            ':cat_id' => intval($article_cat_id),
            ':status' => 1
        ];
        $result = dbquery($select, $bind);
        if (dbrows($result)) {
            $data = dbarray($result);
            set_title(self::$locale['article_0000']);
            add_breadcrumb([
                'link'  => INFUSIONS."articles/articles.php",
                'title' => self::$locale['article_0000']
            ]);
            add_to_title(self::$locale['global_201'].$data['article_cat_name']);

            // Predefined variables, do not edit these values
            $article_cat_index = dbquery_tree(DB_ARTICLE_CATS, "article_cat_id", "article_cat_parent");

            // build categorial data.
            $info['article_cat_id'] = $data['article_cat_id'];
            $info['article_cat_name'] = $data['article_cat_name'];
            $info['article_cat_description'] = parse_text($data['article_cat_description'], ['add_line_breaks', TRUE]);
            $info['article_cat_language'] = $data['article_cat_language'];

            $max_article_rows = dbcount("(article_id)", DB_ARTICLES, "article_cat='".$data['article_cat_id']."' AND ".groupaccess("article_visibility")." AND article_draft='0'");

            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_article_rows ? intval($_GET['rowstart']) : 0;

            if ($max_article_rows) {
                $result = dbquery($this->getArticlesQuery(['condition' => "a.article_cat='".$data['article_cat_id']."'"]));
                $info['article_item_rows'] = dbrows($result);
                $info['article_total_rows'] = $max_article_rows;
                $this->articleCatBreadcrumbs($article_cat_index);
            }

        } else {
            redirect(INFUSIONS."articles/articles.php");
        }

        /**
         * Parse
         */
        if ($max_article_rows) {
            $article_count = 0;
            $article_info = [];
            while ($data = dbarray($result)) {
                $article_count++;
                if ($article_count == 1) {
                    $info['article_last_updated'] = $data['article_datestamp'];
                }
                $article_info[$article_count] = self::getArticlesData($data);
            }
            $info['article_items'] = $article_info;
        }

        $this->info = $info;

        return $info;
    }

    /**
     * Articles Category Breadcrumbs Generator
     *
     * @param $article_cat_index - hierarchy array
     */
    private function articleCatBreadcrumbs($article_cat_index) {
        $locale = fusion_get_locale("", ARTICLE_LOCALE);

        /* Make an infinity traverse */
        function breadcrumb_arrays($index, $id) {
            $crumb = [];
            if (isset($index[get_parent($index, $id)])) {

                $_name = dbarray(dbquery("SELECT article_cat_id, article_cat_name, article_cat_parent
                FROM ".DB_ARTICLE_CATS.(multilang_table("AR") ? " WHERE ".in_group('article_cat_language', LANGUAGE)." AND " : " WHERE ")."
                article_cat_id=:id AND article_cat_status=:status AND ".groupaccess("article_cat_visibility")." ",
                        [
                            ':id'     => $id,
                            ':status' => 1
                        ])
                );

                $crumb = [
                    'link'  => INFUSIONS."articles/articles.php?cat_id=".$_name['article_cat_id'],
                    'title' => $_name['article_cat_name']
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

        // then we make an infinity recursive function to loop/break it out.
        $crumb = breadcrumb_arrays($article_cat_index, $_GET['cat_id']);
        $title_count = !empty($crumb['title']) && is_array($crumb['title']) ? count($crumb['title']) > 1 : 0;
        // then we sort in reverse.
        if ($title_count) {
            krsort($crumb['title']);
            krsort($crumb['link']);
        }
        if ($title_count) {
            foreach ($crumb['title'] as $i => $value) {
                add_breadcrumb(['link' => $crumb['link'][$i], 'title' => $value]);
                if ($i == count($crumb['title']) - 1) {
                    add_to_title($locale['global_201'].$value);
                }
            }
        } else if (isset($crumb['title'])) {
            //add_to_title($locale['global_201'].$crumb['title']);
            add_breadcrumb(['link' => $crumb['link'], 'title' => $crumb['title']]);
        }
    }

    /**
     * Executes single article item information - $_GET['readmore']
     *
     * @param $article_id
     *
     * @return array
     */
    public function setArticlesItemInfo($article_id) {
        self::$locale = fusion_get_locale("", ARTICLE_LOCALE);
        $info = [];

        add_breadcrumb([
            'link'  => INFUSIONS."articles/articles.php",
            'title' => self::$locale['article_0000']
        ]);

        $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? intval($_GET['rowstart']) : 0;

        $result = dbquery(self::getArticlesQuery(['condition' => "a.article_id='".intval($article_id)."'", 'limit' => "0,1"]));

        if (dbrows($result) > 0) {
            $data = dbarray($result);

            if ($data['article_keywords'] !== "") {
                set_meta("keywords", $data['article_keywords']);
            }

            if (!isset($_POST['post_comment']) && !isset($_POST['post_rating']) && empty($_GET['rowstart'])) {
                dbquery("UPDATE ".DB_ARTICLES." SET article_reads=article_reads+1 WHERE article_id='".$_GET['article_id']."'");
                $data['article_reads']++;
            }

            $article_subject = $data['article_subject'];

            $_GET['cat_id'] = $data['article_cat_id'];

            $article_cat_index = dbquery_tree(DB_ARTICLE_CATS, "article_cat_id", "article_cat_parent");

            set_title(self::$locale['article_0000'].self::$locale['global_201']);
            add_to_title($article_subject);

            $this->articleCatBreadcrumbs($article_cat_index);

            add_breadcrumb([
                'link'  => INFUSIONS."articles/articles.php?article_id=".$data['article_id'],
                'title' => $data['article_subject']
            ]);

            $default_info = [
                'article_item'     => '',
                'article_filter'   => [],
                'article_category' => []
            ];
            $info = array_merge($default_info, self::getArticleFilters());
            $info = array_merge($info, self::getArticleCategories());

            $articleData = self::getArticlesData($data);
            $articleData['article_show_ratings'] = self::getArticlesRatings($data);
            $articleData['article_show_comments'] = self::getArticlesComments($data);
            $info['article_item'] = $articleData;
        } else {
            redirect(INFUSIONS."articles/articles.php");
        }

        return $info;

    }

    /**
     * Display Ratings on an Article
     *
     * @param $data
     *
     * @return string
     */
    private static function getArticlesRatings($data) {
        $html = '';
        if (fusion_get_settings('ratings_enabled') && $data['article_allow_ratings'] == TRUE) {
            ob_start();
            require_once INCLUDES."ratings_include.php";
            showratings("A", $data['article_id'], BASEDIR."infusions/articles/articles.php?article_id=".$data['article_id']);
            $html = ob_get_clean();
        }

        return (string)$html;
    }

    /**
     * Display Comments on an Article
     *
     * @param $data
     *
     * @return string
     */
    private static function getArticlesComments($data) {
        $html = "";
        if (fusion_get_settings('comments_enabled') && $data['article_allow_comments'] == TRUE) {
            ob_start();
            require_once INCLUDES."comments_include.php";
            showcomments("A", DB_ARTICLES, "article_id", $data['article_id'], BASEDIR."infusions/articles/articles.php?article_id=".$data['article_id'], FALSE);
            $html = ob_get_clean();
        }

        return (string)$html;
    }

    protected function __clone() {
    }
}
