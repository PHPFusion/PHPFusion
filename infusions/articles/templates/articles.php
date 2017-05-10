<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (!function_exists("display_main_articles")) {
    /**
     * Articles Page Template
     *
     * @param $info
     */
    function display_main_articles($info) {

        $articles_settings = \PHPFusion\Articles\ArticlesServer::get_article_settings();
        $locale = fusion_get_locale();

        add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.cookie.js'></script>");

        $cookie_expiry = time() + 7 * 24 * 3600;
        if (empty($_COOKIE['fusion_articles_view'])) {
            setcookie("fusion_articles_view", 1, $cookie_expiry);
        } elseif (isset($_POST['switchview']) && isnum($_POST['switchview'])) {
            setcookie("fusion_articles_view", intval($_POST['switchview'] == 2 ? 2 : 1), $cookie_expiry);
            redirect(FUSION_REQUEST);
        }

        opentable($locale['article_0000']);
        echo render_breadcrumbs();

        if (is_array($info['article_categories']) && !empty($info['article_categories'])) {
            ?>

            <div class="panel panel-default panel-articles-header">

                <!-- Display Informations -->
                <div class="panel-body">
                    <div class="pull-right">
                        <a class="btn btn-sm btn-default" href="<?php echo INFUSIONS."articles/articles.php"; ?>" title="<?php echo $locale['article_0001']; ?>"><i class="fa fa-fw fa-desktop"></i> <?php echo $locale['article_0001']; ?></a>
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="collapse" data-target="#articlescat" aria-expanded="true" aria-controls="articlescat" title="<?php echo $locale['article_0002']; ?>">
                            <i class="fa fa-fw fa-folder"></i> <?php echo $locale['article_0002']; ?>
                        </button>
                    </div>
                    <div class="overflow-hide">
                        <h3 class="display-inline text-dark"><?php echo $info['article_cat_name']; ?></h3><br/>
                        <?php if ($info['article_cat_description']) { ?>
                            <div class="article-cat-description"><?php echo $info['article_cat_description']; ?></div>
                            <br/>
                        <?php } ?>
                        <span class="strong text-smaller"><?php echo $locale['article_0004']; ?></span>
                        <span class="text-dark text-smaller"><?php echo($info['article_last_updated'] > 0 ? showdate("newsdate", $info['article_last_updated']) : $locale['na']); ?></span>
                    </div>
                </div>

                <!-- Diplay Categories -->
                <div id="articlescat" class="panel-collapse collapse m-b-10">
                    <!--pre_articles_cat_idx-->
                    <ul class="list-group">
                        <li class="list-group-item">
                            <hr class="m-t-0 m-b-5">
                            <span class="display-inline-block m-b-10 strong text-smaller text-uppercase"><?php echo $locale['article_0003']; ?></span><br/>
                            <?php
                            foreach ($info['article_categories'] as $cat_id => $cat_data) {
                                if (!isset($_GET['cat_id']) || $_GET['cat_id'] != $cat_id) {
                                    echo "<a href='".INFUSIONS."articles/articles.php?cat_id=".$cat_id."' class='btn btn-sm btn-default m-5'>".$cat_data['name']."</a>";
                                }
                            }
                            ?>
                        </li>
                    </ul>
                    <!--sub_articles_cat_idx-->
                </div>
            </div>

            <!-- Display Sorting Options -->
            <div class="row m-t-20 m-b-20">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

                    <!-- Display View Options -->
                    <?php echo openform("viewform", "post", FUSION_REQUEST, array("max_tokens" => 1, "class" => "pull-right display-inline-block m-l-10")); ?>
                    <div class="btn-group">
                        <?php $active = isset($_COOKIE['fusion_articles_view']) && isnum($_COOKIE['fusion_articles_view']) && $_COOKIE['fusion_articles_view'] == 2 ? 2 : 1; ?>
                        <?php echo form_button("switchview", "<i class='fa fa-fw fa-th-large'></i> ".$locale['article_0040'], "1", array("class" => "btn-sm btn-default nsv".($active == "1" ? " active" : ""), "alt" => $locale['article_0040'])); ?>
                        <?php echo form_button("switchview", "<i class='fa fa-fw fa-bars'></i> ".$locale['article_0041'], "2", array("class" => "btn-sm btn-default nsv".($active == "2" ? " active" : ""), "alt" => $locale['article_0041'])); ?>
                    </div>
                    <?php echo closeform(); ?>

                    <!-- Display Filters -->
                    <div class="display-inline-block">
                        <span class="text-dark strong m-r-10"><?php echo $locale['show']; ?></span>
                        <?php $i = 0;
                        foreach ($info['article_filter'] as $link => $title) {
                            $filter_active = (!isset($_GET['type']) && $i == '0') || isset($_GET['type']) && stristr($link, $_GET['type']) ? "text-dark strong" : "";
                            echo "<a href='".$link."' class='display-inline $filter_active m-r-10'>".$title."</a>";
                            $i++;
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Display Articles -->
            <?php
            $articleColumn = $active == 2 ? 12 : 4;
            if (!empty($info['article_items'])) {
                ?>
                <div class="row">
                    <?php foreach ($info['article_items'] as $i => $article_info) { ?>
                        <div class="col-xs-12 col-sm-<?php echo $articleColumn; ?> col-md-<?php echo $articleColumn; ?> col-lg-<?php echo $articleColumn; ?>">
                            <?php echo (isset($_GET['cat_id'])) ? "<!--pre_articles_cat_idx-->\n" : "<!--articles_prepost_".$i."-->\n"; ?>
                            <?php render_article($article_info['article_subject'], $article_info['article_article'], $article_info, ($active == 2 ? true : false)); ?>
                            <?php echo (isset($_GET['cat_id'])) ? "<!--sub_articles_cat_idx-->" : "<!--sub_articles_idx-->\n"; ?>
                        </div>
                    <?php } ?>
                </div>
                <?php
                if ($info['article_total_rows'] > $articles_settings['article_pagination']) {
                    $type_start = isset($_GET['type']) ? "type=".$_GET['type']."&amp;" : "";
                    $cat_start = isset($_GET['cat_id']) ? "cat_id=".$_GET['cat_id']."&amp;" : ""; ?>
                    <div class="text-center m-t-10 m-b-10">
                        <?php echo makepagenav($_GET['rowstart'], $articles_settings['article_pagination'], $info['article_total_rows'], 3, INFUSIONS."articles/articles.php?".$cat_start.$type_start); ?>
                    </div>
                    <?php
                }
            } else { ?>
                <div class="well text-center"><?php echo(isset($_GET['cat_id']) ? $locale['article_0062'] : $locale['article_0061']); ?></div>
                <?php
            }
        } else {
            echo "<div class='well text-center'>".$locale['article_0060']."</div>";
        }
        closetable();
    }
}

if (!function_exists("render_article")) {
    /**
     * Articles Item Container
     *
     * @param      $subject
     * @param      $article
     * @param      $info
     * @param bool $list_view
     */
    function render_article($subject, $article, $info, $list_view = FALSE) {
        $locale = fusion_get_locale();
        // List
        if ($list_view) {
            ?>
            <article class="panel panel-default clearfix" style="min-height: 150px;">
                <div class="panel-body">
                    <h4 class="article-title panel-title">
                        <a href="<?php echo INFUSIONS."articles/articles.php?article_id=".$info['article_id']; ?>" class="text-dark strong"><?php echo $subject; ?></a>
                    </h4>
                    <div class="article-text m-t-10">
                        <?php echo trim_text(parse_textarea($article, TRUE, TRUE, FALSE, '', TRUE), 250); ?>
                    </div>
                    <hr/>
                    <div class="article-footer m-t-5">
                        <i class="fa fa-fw fa-folder"></i>
                        <a href="<?php echo INFUSIONS."articles/articles.php?cat_id=".$info['article_cat_id']; ?>" title="<?php echo $info['article_cat_name']; ?>"><?php echo $info['article_cat_name']; ?></a>
                        <i class="fa fa-fw fa-eye m-l-10"></i> <?php echo format_word($info['article_reads'], $locale['fmt_read']); ?>
                        <?php if ($info['article_allow_comments']) { ?>
                            <i class="fa fa-fw fa-comments m-l-10"></i>
                            <a href="<?php echo INFUSIONS."articles/articles.php?article_id=".$info['article_id']."#comments"; ?>" title="<?php echo format_word($info['article_comments'], $locale['fmt_comment']); ?>">
                                <?php echo format_word($info['article_comments'], $locale['fmt_comment']); ?>
                            </a>
                        <?php } ?>
                        <?php if ($info['article_allow_ratings']) { ?>
                            <i class="fa fa-fw fa-bar-chart m-l-10"></i>
                            <a href="<?php echo INFUSIONS."articles/articles.php?article_id=".$info['article_id']."#comments"; ?>" title="<?php echo format_word($info['article_count_votes'], $locale['fmt_rating']); ?>">
                                <?php echo format_word($info['article_count_votes'], $locale['fmt_rating']); ?>
                            </a>
                        <?php } ?>
                        <a href="<?php echo $info['print_link']; ?>" title="<?php echo $locale['print']; ?>" target="_blank"><i class="fa fa-fw fa-print m-l-10"></i> <?php echo $locale['print']; ?></a>
                        <?php if (!empty($info['admin_actions'])) { ?>
                            <a href="<?php echo $info['admin_actions']['edit']['link']; ?>" title="<?php echo $info['admin_actions']['edit']['title']; ?>"><i class="fa fa-fw fa-pencil m-l-10"></i> <?php echo $locale['edit']; ?></a>
                        <?php } ?>
                    </div>
                </div>
            </article>
            <?php
            // Gallery
        } else {
            ?>
            <!--articles_prepost_<?php echo $info['article_id']; ?>-->
            <article class="panel panel-default" style="min-height: 250px;">
                <div class="panel-body">

                    <h4 class="article-title panel-title">
                        <a href="<?php echo INFUSIONS."articles/articles.php?article_id=".$info['article_id']; ?>" class="text-dark strong"><?php echo $subject; ?></a>
                    </h4>

                    <div class="article-text m-t-5" style="height: 200px;">
                        <?php echo trim_text(parse_textarea($article, TRUE, TRUE, FALSE, '', TRUE), 250); ?>
                    </div>

                    <div class="article-category m-t-5">
                        <i class="fa fa-fw fa-folder"></i>
                        <a href="<?php echo INFUSIONS."articles/articles.php?cat_id=".$info['article_cat_id']; ?>" title="<?php echo $info['article_cat_name']; ?>"><?php echo $info['article_cat_name']; ?></a>
                    </div>
                </div>

                <div class="article-footer panel-footer">
                    <i class="fa fa-fw fa-eye m-l-10"></i> <?php echo $info['article_reads']; ?>

                    <?php if ($info['article_allow_comments']) { ?>
                        <i class="fa fa-fw fa-comments m-l-10"></i>
                        <a href="<?php echo INFUSIONS."articles/articles.php?article_id=".$info['article_id']."#comments"; ?>" title="<?php echo format_word($info['article_comments'], $locale['fmt_comment']); ?>">
                            <?php echo $info['article_comments']; ?>
                        </a>
                    <?php } ?>

                    <?php if ($info['article_allow_ratings']) { ?>
                        <i class="fa fa-fw fa-bar-chart m-l-10"></i>
                        <a href="<?php echo INFUSIONS."articles/articles.php?article_id=".$info['article_id']."#comments"; ?>" title="<?php echo format_word($info['article_count_votes'], $locale['fmt_rating']); ?>">
                            <?php echo $info['article_count_votes']; ?>
                        </a>
                    <?php } ?>

                    <a href="<?php echo $info['print_link']; ?>" title="<?php echo $locale['print']; ?>" target="_blank"><i class="fa fa-fw fa-print m-l-10"></i></a>

                    <?php if (!empty($info['admin_actions'])) { ?>
                        <a href="<?php echo $info['admin_actions']['edit']['link']; ?>" title="<?php echo $info['admin_actions']['edit']['title']; ?>"><i class="fa fa-fw fa-pencil m-l-10"></i></a>
                    <?php } ?>
                </div>
            </article>
            <?php
        }
    }
}

if (!function_exists("render_article_item")) {
    /**
     * Articles Item Page Template
     *
     * @param $info
     */
    function render_article_item($info) {

        $locale = fusion_get_locale();
        $articles_settings = \PHPFusion\Articles\ArticlesServer::get_article_settings();
        $data = $info['article_item'];

        opentable($locale['article_0000']);
        echo render_breadcrumbs(); ?>

        <!--articles_pre_readmore-->
        <article class="article-item" style="display: block; width: 100%; overflow: hidden;">
            <h2 class="text-left"><?php echo $data['article_subject']; ?></h2>

            <div class="article-article text-dark m-t-20 m-b-20">
                <p>
                    <?php echo $data['article_article']; ?>
                </p>
            </div>

            <div class="text-center">
                <?php echo $data['article_pagenav']; ?>
            </div>
            <div style="clear: both;"></div>

            <div class="well m-t-15 text-center">
                <i class="fa fa-fw fa-user m-l-10"></i> <?php echo profile_link($data['user_id'], $data['user_name'], $data['user_status']); ?>
                <i class="fa fa-fw fa-calendar m-l-10"></i> <?php echo showdate("newsdate", $data['article_datestamp']); ?>
                <i class="fa fa-fw fa-eye m-l-10"></i> <?php echo format_word($data['article_reads'], $locale['fmt_read']); ?>

                <?php if ($data['article_allow_comments']) { ?>
                    <i class="fa fa-fw fa-comments m-l-10"></i> <?php echo format_word($data['article_comments'], $locale['fmt_comment']); ?>
                <?php } ?>
                <?php if ($data['article_allow_ratings']) { ?>
                    <i class="fa fa-fw fa-bar-chart m-l-10"></i> <?php echo format_word($data['article_count_votes'], $locale['fmt_rating']); ?>
                <?php } ?>

                <i class="fa fa-fw fa-print m-l-10"></i> <a href="<?php echo $data['print_link']; ?>" title="<?php echo $locale['print']; ?>" target="_blank"><?php echo $locale['print']; ?></a>

                <?php if (!empty($data['admin_actions'])) { ?>
                    <hr>
                    <div class="btn-group">
                        <a href="<?php echo $data['admin_actions']['edit']['link']; ?>" title="<?php echo $locale['edit']; ?>" class="btn btn-default"><i class="fa fa-fw fa-pencil"></i> <?php echo $data['admin_actions']['edit']['title']; ?></a>
                        <a href="<?php echo $data['admin_actions']['delete']['link']; ?>" title="<?php echo $locale['delete']; ?>" class="btn btn-default"><i class="fa fa-fw fa-trash"></i> <?php echo $data['admin_actions']['delete']['title']; ?></a>
                    </div>
                <?php } ?>

            </div>

            <!--articles_sub_readmore-->
            <?php
            echo($data['article_show_comments'] ? "<hr />".$data['article_show_comments']."\n" : "");
            echo($data['article_show_ratings'] ? "<hr />".$data['article_show_ratings']."\n" : "");
            ?>

        </article>
        <?php
        closetable();
    }
}
