<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles.php
| Author: PHP-Fusion Development Team
| Co-Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once file_exists('maincore.php') ? 'maincore.php' : __DIR__."/../../maincore.php";
if (!db_exists(DB_ARTICLES)) {
    redirect(BASEDIR."error.php?code=404");
}
require_once THEMES."templates/header.php";
require_once INCLUDES."infusions_include.php";

if (file_exists(INFUSIONS."articles/locale/".LOCALESET."articles.php")) {
    include INFUSIONS."articles/locale/".LOCALESET."articles.php";
} else {
    include INFUSIONS."articles/locale/English/articles.php";
}

include INFUSIONS."articles/templates/articles.php";

$info = array();
$locale = fusion_get_locale();

add_to_title($locale['global_200'].\PHPFusion\SiteLinks::get_current_SiteLinks("", "link_name"));
add_breadcrumb(array('link' => INFUSIONS.'articles/articles.php', 'title' => \PHPFusion\SiteLinks::get_current_SiteLinks("", "link_name")));

$article_settings = get_settings("article");
$article_cat_index = dbquery_tree(DB_ARTICLE_CATS, 'article_cat_id', 'article_cat_parent',
                                  "".(multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."'" : '')."");

/* Render Articles */
if (isset($_GET['article_id']) && isnum($_GET['article_id'])) {

    $result = dbquery("SELECT ta.article_subject, ta.article_snippet, ta.article_article, ta.article_keywords, ta.article_breaks,
		ta.article_datestamp, ta.article_reads, ta.article_allow_comments, ta.article_allow_ratings,
		tac.article_cat_id, tac.article_cat_name,
		tu.user_id, tu.user_name, tu.user_status, tu.user_avatar, tu.user_joined, tu.user_level
		FROM ".DB_ARTICLES." ta
		INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
		LEFT JOIN ".DB_USERS." tu ON ta.article_name=tu.user_id
		".(multilang_table("AR") ? "WHERE tac.article_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('article_visibility')." AND article_id='".$_GET['article_id']."' AND article_draft='0'");
    if (dbrows($result) > 0) {
        $data = dbarray($result);
        require_once INCLUDES."comments_include.php";
        require_once INCLUDES."ratings_include.php";

        $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;
        if (($_GET['rowstart'] == 0) && empty($_POST)) {
            dbquery("UPDATE ".DB_ARTICLES." SET article_reads=article_reads+1 WHERE article_id='".$_GET['article_id']."'");
        }

        $article = preg_split("/<!?--\s*pagebreak\s*-->/i", parse_textarea($data['article_article'], FALSE, FALSE, TRUE, IMAGES));

        $pagecount = count($article);

        $article_subject = stripslashes($data['article_subject']);

        add_breadcrumb(array(
                           'link' => INFUSIONS.'articles/articles.php?cat_id='.$data['article_cat_id'],
                           'title' => $data['article_cat_name']
                       ));

        add_breadcrumb(array(
                           'link' => INFUSIONS.'articles/articles.php?article_id='.$_GET['article_id'],
                           'title' => $data['article_subject']
                       ));

        if ($data['article_keywords'] !== "") {
            set_meta("keywords", $data['article_keywords']);
        }

        $article_info = array(
            "article_id" => $_GET['article_id'],
            "article_subject" => $article_subject,
            "article_snippet" => parse_textarea($data['article_snippet'], FALSE, FALSE, TRUE, IMAGES),
            "article_article" => $article,
            "cat_id" => $data['article_cat_id'],
            "cat_name" => $data['article_cat_name'],
            "user_id" => $data['user_id'],
            "user_name" => $data['user_name'],
            "user_status" => $data['user_status'],
            "user_avatar" => $data['user_avatar'],
            "user_joined" => $data['user_joined'],
            "user_level" => $data['user_level'],
            "article_date" => $data['article_datestamp'],
            "article_breaks" => $data['article_breaks'],
            "article_comments" => dbcount("(comment_id)", DB_COMMENTS, "comment_type='A' AND comment_item_id='".$_GET['article_id']."'"),
            "article_reads" => $data['article_reads'],
            "article_allow_comments" => $data['article_allow_comments'],
            "article_allow_ratings" => $data['article_allow_ratings'],
            "page_nav" => $pagecount > 1 ? makepagenav($_GET['rowstart'], 1, $pagecount, 3,
                                                       INFUSIONS."articles/articles.php?article_id=".$_GET['article_id']."&amp;") : "",
            "edit_link" => "",
        );
        if (iADMIN && checkrights("A")) {
            $article_info['edit_link'] = INFUSIONS."articles/articles_admin.php".$aidlink."&amp;action=edit&amp;section=article_form&amp;article_id=".$article_info['article_id'];
        }

        set_title($article_subject.$locale['global_200'].$locale['400']);

        render_article($article_subject, $article[$_GET['rowstart']], $article_info);
    } else {
        redirect(INFUSIONS."articles/articles.php");
    }
} elseif (!isset($_GET['cat_id']) || !isnum($_GET['cat_id'])) {
    // category query
    set_title($locale['400']);

    $result = dbquery("SELECT
		ac.article_cat_id, ac.article_cat_name, ac.article_cat_description, count(a.article_id) 'article_count', count(ac2.article_cat_id) 'article_sub_count'
		FROM ".DB_ARTICLE_CATS." ac
		LEFT JOIN ".DB_ARTICLE_CATS." ac2 on ac.article_cat_id=ac2.article_cat_parent
		LEFT JOIN ".DB_ARTICLES." a on a.article_cat=ac.article_cat_id
		".(multilang_table("AR") ? "WHERE ac.article_cat_language='".LANGUAGE."' AND" : "WHERE")."
		ac.article_cat_parent = '0'
		GROUP BY ac.article_cat_id
		ORDER BY ac.article_cat_name
		");

    $info['articles_rows'] = dbrows($result);

    if ($info['articles_rows'] > 0) {
        while ($data = dbarray($result)) {
            $data['article_cat_description'] = parse_textarea($data['article_cat_description'], FALSE, FALSE, TRUE, IMAGES);
            $info['articles']['item'][] = $data;
        }
    }
    render_articles_main($info);

} else {

    // View articles in a category
    $result = dbquery("SELECT * FROM ".DB_ARTICLE_CATS." where article_cat_id='".intval($_GET['cat_id'])."' ORDER BY article_cat_name");

    if (dbrows($result) != 0) {

        $cdata = dbarray($result);

        $info['articles']['child_categories'] = array();
        // get child category
        $child_result = dbquery("SELECT
		ac.article_cat_id, ac.article_cat_name, ac.article_cat_description, count(a.article_id) 'article_count', count(ac2.article_cat_id) 'article_sub_count'
		FROM ".DB_ARTICLE_CATS." ac
		LEFT JOIN ".DB_ARTICLE_CATS." ac2 ON ac.article_cat_id=ac2.article_cat_parent
		LEFT JOIN ".DB_ARTICLES." a on a.article_cat=ac.article_cat_id AND a.article_draft ='0' AND ".groupaccess("a.article_visibility")."
		".(multilang_table("AR") ? "and a.article_language='".LANGUAGE."'" : "")."
		".(multilang_table("AR") ? "WHERE ac.article_cat_language='".LANGUAGE."' AND" : "WHERE")."
		ac.article_cat_parent = '".intval($cdata['article_cat_id'])."'
		GROUP BY ac.article_cat_id
		ORDER BY ac.article_cat_name
		");

        if (dbrows($child_result) > 0) {
            while ($childData = dbarray($child_result)) {
                $info['articles']['child_categories'][$childData['article_cat_id']] = $childData;
            }
        }


        set_title($cdata['article_cat_name'].$locale['global_200'].$locale['400']);

        articleCats_breadcrumbs($article_cat_index);

        $info['articles']['category'] = $cdata;

        // xss
        $info['articles_max_rows'] = dbcount("(article_id)", DB_ARTICLES, "article_cat='".$_GET['cat_id']."' AND article_draft='0'");

        $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['articles_max_rows']) ? $_GET['rowstart'] : "0";

        if ($info['articles_max_rows'] > 0) {

            $a_result = dbquery("
                        SELECT * FROM ".DB_ARTICLES."
						WHERE article_cat='".intval($_GET['cat_id'])."' AND article_draft='0' AND ".groupaccess('article_visibility')."
						ORDER BY ".$cdata['article_cat_sorting']."
						LIMIT ".intval($_GET['rowstart']).", ".intval($article_settings['article_pagination']));

            $info['articles_rows'] = dbrows($a_result);

            while ($data = dbarray($a_result)) {

                $data['article_snippet'] = parse_textarea($data['article_snippet'], FALSE, FALSE, TRUE, IMAGES);
                $data['article_article'] = preg_split("/<!?--\s*pagebreak\s*-->/i", parse_textarea($data['article_article'], FALSE, FALSE, TRUE, IMAGES));
                $data['new'] = ($data['article_datestamp'] + 604800 > time() + (fusion_get_settings("timeoffset") * 3600)) ? $locale['402'] : '';
                $info['articles']['item'][] = $data;

            }

            $info['page_nav'] = ($info['articles_max_rows'] > $article_settings['article_pagination']) ? makepagenav($_GET['rowstart'],
                                                                                                                     $article_settings['article_pagination'],
                                                                                                                     $info['articles_max_rows'], 3,
                                                                                                                     FUSION_SELF."?cat_id=".$_GET['cat_id']."&amp;") : "";

        }

    } else {
        redirect(INFUSIONS.'articles/articles.php');
    }
    render_articles_category($info);
}

require_once THEMES."templates/footer.php";

/**
 * Article Category Breadcrumbs Generator
 * @param $forum_index
 */
function articleCats_breadcrumbs($index) {
    global $locale;

    function breadcrumb_arrays($index, $id) {
        $crumb = &$crumb;
        if (isset($index[get_parent($index, $id)])) {
            $_name = dbarray(dbquery("SELECT article_cat_id, article_cat_name, article_cat_parent FROM ".DB_ARTICLE_CATS." ".(multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."' and " : "where ")."
				article_cat_id='".intval($id)."'"));
            $crumb = array(
                'link' => INFUSIONS."articles/articles.php?cat_id=".$_name['article_cat_id'],
                'title' => $_name['article_cat_name']
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
    $crumb = breadcrumb_arrays($index, $_GET['cat_id']);
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
                add_to_meta($value);
            }
        }
    } elseif (isset($crumb['title'])) {
        add_to_title($locale['global_201'].$crumb['title']);
        add_to_meta($crumb['title']);
        add_breadcrumb(array('link' => $crumb['link'], 'title' => $crumb['title']));
    }
}
