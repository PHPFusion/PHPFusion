<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
if (!function_exists('render_article')) {
    function render_article($subject, $article, $info) {
        $locale = fusion_get_locale();
        $category = "<a href='".INFUSIONS."articles/articles.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>\n";
        $comment = "<a href='".INFUSIONS."articles/articles.php?article_id=".$info['article_id']."#comments'> ".format_word($info['article_comments'],
                                                                                                                            $locale['fmt_comment'])." </a>\n";
        echo render_breadcrumbs();
        echo "<!--pre_article-->";
        echo "<article>\n";
        echo "<div class='news-action pull-right'>";
        echo "<a title='".$locale['global_075']."' href='".BASEDIR."print.php?type=A&amp;item_id=".$info['article_id']."'><i class='entypo print'></i></a>";
        echo !empty($info['edit_link']) ? "<a href='".$info['edit_link']."' title='".$locale['global_076']."' /><i class='entypo pencil'></i></a>\n" : '';
        echo "</div>\n";
        echo "<div class='news-info'>".ucfirst($locale['posted'])." <span class='news-date'>".showdate("newsdate",
                                                                                                       $info['article_date'])."</span> ".$locale['in']." $category ".$locale['and']." $comment</div>\n";
        echo "<h2 class='news-title'>$subject</h2>";
        echo "<div class='article'>\n";
        echo ($info['article_breaks'] == "y" ? nl2br($article) : $article)."<br />\n";
        echo "</div>\n";
        echo "<hr />\n";
        echo "<div class='news-user-info clearfix m-b-10'>\n";
        echo "<h4>".$locale['about']." <a href='".BASEDIR."profile.php?lookup=".$info['user_id']."'>".$info['user_name']."</a>\n</h4>";
        echo "<div class='pull-left m-r-10'>".display_avatar($info, '80px')."</div>\n";
        echo "<strong>".getuserlevel($info['user_level'])."</strong><br/>\n";
        echo "<strong>".$locale['joined'].showdate("newsdate", $info['user_joined'])."</strong><br/>\n";
        echo "</div>\n";
        echo "</article>";
        echo "<!--sub_article-->";
        echo $info['page_nav'];
        echo "<hr />\n";
        if ($info['article_allow_comments']) {
            showcomments("A", DB_ARTICLES, "article_id", $_GET['article_id'],
                         INFUSIONS."articles/articles.php?article_id=".$_GET['article_id']);
        }
        if ($info['article_allow_ratings']) {
            showratings("A", $_GET['article_id'], INFUSIONS."articles/articles.php?article_id=".$_GET['article_id']);
        }
    }
}


if (!function_exists('render_articles_main')) {
    /**
     * Main Articles Template
     * @param $info
     */
    function render_articles_main($info) {
        $locale = fusion_get_locale();
        echo render_breadcrumbs();
        echo "<!--pre_article_idx-->\n";
        opentable($locale['400']);
        if (isset($info['articles']['item'])) {
            $counter = 0;
            $columns = 2;
            echo "<div class='row m-b-20'>\n";
            foreach ($info['articles']['item'] as $data) {
                if ($counter != 0 && ($counter % $columns == 0)) {
                    echo "</div>\n<div class='row'>\n";
                }
                echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
                echo "<!--article_idx_cat_name-->\n";
                echo "<h3 class='display-inline-block m-r-10'>
                        <a href='".INFUSIONS."articles/articles.php?cat_id=".$data['article_cat_id']."'>
					        <strong>".$data['article_cat_name']."</a></strong>
					    </a>
                    </h3>
					<i class='fa fa-folder'></i> ".$data['article_sub_count']."
					<i class='fa fa-file-o'></i> ".$data['article_count'];

                echo ($data['article_cat_description'] != "") ? "<div>".parse_textarea($data['article_cat_description'])."</div>" : "";
                echo "</div>\n";
                $counter++;
            }
            echo "</div>\n";
        } else {
            echo "<div style='text-align:center'><br />\n".$locale['401']."<br /><br />\n</div>\n";
        }
        closetable();
        echo "<!--sub_article_idx-->\n";
    }
}

if (!function_exists('render_articles_category')) {

    /**
     * Article Category View
     * @param $info
     */
    function render_articles_category($info) {
        $locale = fusion_get_locale();
        if (isset($info['articles']['category'])) {
            $data = $info['articles']['category'];
            echo render_breadcrumbs();
            echo "<!--pre_article_cat-->";
            opentable($locale['400'].": ".$data['article_cat_name']);
            if (!empty($info['articles']['child_categories'])) {
                $counter = 0;
                $columns = 2;

                echo "<aside class='list-group-item m-b-20'>\n";
                echo "<div class='row'>\n";

                foreach ($info['articles']['child_categories'] as $catID => $catData) {

                    if ($counter != 0 && ($counter % $columns == 0)) {
                        echo "</div>\n<div class='row'>\n";
                    }

                    echo "<div class='col-xs-12 col-sm-6'>\n";
                    echo "<!--article_idx_cat_name-->\n";

                    echo "<h3 class='display-inline-block m-r-10'>
                        <a href='".INFUSIONS."articles/articles.php?cat_id=".$catData['article_cat_id']."'><strong>".$catData['article_cat_name']."</a></strong></a>
                    <i class='fa fa-folder'></i> ".$catData['article_sub_count']."
                    <i class='fa fa-file-o'></i> ".$catData['article_count'].
                    "</h3>";

                    echo ($catData['article_cat_description'] != "") ? "<div>".parse_textarea($catData['article_cat_description'])."</div>" : "";
                    echo "</div>\n";
                    $counter++;
                }
                echo "</div>\n";
                echo "</aside>\n";
            }
            if (isset($info['articles']['item'])) {
                foreach ($info['articles']['item'] as $cdata) {
                    echo "<aside>\n";
                    echo "<h4 class='display-inline-block'><strong><a href='".INFUSIONS."articles/articles.php?article_id=".$cdata['article_id']."'>".$cdata['article_subject']."</a></strong>
                    <span class='label label-success m-l-5'>".$cdata['new']."</span>
                    </h4><br/>\n";
                    echo preg_replace("/<!?--\s*pagebreak\s*-->/i", "", stripslashes($cdata['article_snippet']))."\n";
                    echo "</aside>\n";
                    echo "<hr/>\n";
                }
                echo !empty($info['page_nav']) ? "<div class='m-t-5'>".$info['page_nav']."</div>\n" : '';
            } else {
                echo "<div class='well text-center'>".$locale['403']."</div>\n";
            }
            echo "<!--sub_article_cat-->";
            closetable();
        }
    }
}