<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: adminDashboard.php
| Author: Frederick Chan (deviance)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace Genesis\Viewer;

use Genesis\Model\resource;

/**
 * Class adminDashboard
 *
 * @package Genesis\Viewer
 */
class adminDashboard extends resource {

    public static function do_dashboard() {
        global $members, $forum, $download, $news, $articles, $weblinks, $photos, $global_comments,
               $global_ratings, $global_submissions, $link_type,
               $comments_type, $infusions_count, $global_infusions, $submit_data;

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $userdata = fusion_get_userdata();
        $settings = fusion_get_settings();

        opentable($locale['250']);
        $panels = array(
            'registered'   => array('link' => '', 'title' => 251),
            'cancelled'    => array('link' => 'status=5', 'title' => 263),
            'unactivated'  => array('link' => 'status=2', 'title' => 252),
            'security_ban' => array('link' => 'status=4', 'title' => 253)
        );
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-6 col-lg-3 responsive-admin-column'>\n";

        echo "<div class='list-group'>\n";
        echo "<div class='list-group-item'><h4 class='m-0'>".$settings['sitename']." Summary</h4></div>";
        echo "<div class='list-group-item'>\n";
        echo "<ul class='block'>\n";
        echo "<li><div class='strong m-b-15'>Recently Published</div></li>\n";
        if (infusion_exists('news')) {
            $result = dbquery("SELECT news_id, news_subject, news_datestamp FROM ".DB_NEWS." WHERE news_draft=0 ORDER BY news_datestamp DESC LIMIT 1");
            $content = "<div>".$locale['269']."</div>";
            $content = "There are no news";
            if (dbrows($result)) {
                $data = dbarray($result);
                $content = "<div>".$locale['269']."</div>
                <div class='pull-left text-lighter m-r-10'>".showdate('%b%d, %R %p', $data['news_datestamp'])."</div>\n
                <div class='overflow-hide'>\n
                <a href='".INFUSIONS."news/news_admin.php$aidlink&amp;action=edit&amp;ref=news_form&amp;news_id=".$data['news_id']."'>".$data['news_subject']."</a>\n
                </div>\n";
            }
            echo "<li class='clearfix'>\n";
            echo "<div class='pull-left m-r-10 admin-icon news'><img alt='".$locale['269']." ".$locale['258']."' src='".get_image("ac_N")."'/>\n</div>";
            echo "<div class='display-inline-block'>\n$content</div>";
            echo "<div class='pull-right m-r-10'><i title='".$locale['269']."' class='fas fa-comment m-r-10 text-lighter'></i>".number_format($news['news'])."</div>
            <div class='pull-right m-r-10'><i title='".$locale['257']."' class='fas fa-comment m-r-10 text-lighter'></i>".number_format($news['comment'])."</div>
            <div class='pull-right m-r-10'><i title='".$locale['254']."' class='fas fa-users m-r-10 text-lighter'></i>".number_format($news['submit'])."</div>
            </li>\n";
        }
        if (infusion_exists('articles')) {
            $content = "<div>".$locale['A']."</div>";
            $content .= 'There are no articles';
            $result = dbquery("SELECT article_id, article_subject, article_datestamp FROM ".DB_ARTICLES." WHERE article_draft=0 ORDER BY article_datestamp DESC LIMIT 1");
            if (dbrows($result)) {
                $data = dbarray($result);
                $content = "<div>".$locale['A']."</div>
                <div class='pull-left text-lighter m-r-10'>".showdate('%b%d, %R %p', $data['article_datestamp'])."</div>
                <div class='overflow-hide'>
                <a href='".INFUSIONS."articles/articles_admin.php$aidlink'>".$data['news_subject']."</a>
                </div>
                ";
            }
            echo "<li class='clearfix m-t-10'>\n";
            echo "<div class='pull-right m-r-10'><i title='".$locale['257']."' class='fas fa-edit m-r-10 text-lighter'></i> ".number_format($articles['article'])." ".$locale['270']."</div>\n";
            echo "<div class='pull-right m-r-10'><i title='".$locale['257']."' class='fas fa-comment m-r-10 text-lighter'></i>".number_format($articles['comment'])."</div>
            <div class='pull-right m-r-10'><i title='".$locale['254']."' class='fas fa-thumbtack m-r-10 text-lighter'></i>".number_format($articles['submit'])."</div>\n";

            echo "<div class='pull-left m-r-10 admin-icon articles'><img alt='".$locale['A']."' src='".get_image("ac_A")."'/>\n</div>";
            echo "<div class='display-inline-block'>\n$content</div>\n";
            echo "</li>\n";
        }
        if (infusion_exists('forum')) {
            $content = "<div>".$locale['F']."</div>";
            $content .= 'There are no forum posts';
            $result = dbquery("SELECT p.post_id, p.thread_id, t.thread_subject, p.post_datestamp FROM ".DB_FORUM_POSTS." p
            INNER JOIN ".DB_FORUM_THREADS." t ON t.thread_id = p.thread_id
            ORDER BY post_datestamp DESC LIMIT 1");

            if (dbrows($result)) {
                $data = dbarray($result);
                $content = "<div>".$locale['F']."</div>
                <div class='pull-left text-lighter m-r-10'>".showdate('%b%d, %R %p', $data['post_datestamp'])."</div>
                <div class='overflow-hide'>
                <a href='".INFUSIONS."forum/viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['post_id']."'>".$data['thread_subject']."</a>
                </div>
                ";
            }
            echo "<li class='clearfix m-t-10'>\n";
            echo "<div class='pull-right m-r-10'><i title='".$locale['256']."' class='fas fa-edit m-r-10 text-lighter'></i>".number_format($forum['thread'])."</div>
            <div class='pull-right m-r-10'><i title='".$locale['259']."' class='fas fa-comment m-r-10 text-lighter'></i>".number_format($forum['post'])."</div>
            <div class='pull-right m-r-10'><i title='".$locale['260']."' class='fas fa-users m-r-10 text-lighter'></i>".number_format($forum['users'])."</div>\n";
            echo "<div class='pull-left m-r-10 admin-icon forum'><img alt='".$locale['F']."' src='".get_image("ac_F")."'/>\n</div>";
            echo $content;
            echo "</li>\n";
        }
        if (infusion_exists('downloads')) {
            $content = "<div>".$locale['D']."</div>";
            $content .= 'There are no downloads';
            $result = dbquery("SELECT download_id, download_title, download_datestamp FROM ".DB_DOWNLOADS." ORDER BY download_datestamp DESC LIMIT 1");
            if (dbrows($result)) {
                $data = dbarray($result);
                $content = "<div>".$locale['D']."</div>
                <div class='pull-left text-lighter m-r-10'>".showdate('%b%d, %R %p', $data['download_datestamp'])."</div>
                <div class='overflow-hide'>
               <a href='".INFUSIONS."downloads/downloads_admin.php$aidlink'>".$data['download_title']."</a>
                </div>
                ";
            }
            echo "<li class='clearfix m-t-10'>\n";
            echo "<div class='pull-right'><i title='".$locale['257']."' class='fas fa-comment m-r-10 text-lighter'></i>".number_format($download['download'])." ".$locale['268']."</div>\n";
            echo "<div class='pull-right m-r-10'><i title='".$locale['257']."' class='fas fa-comment m-r-10 text-lighter'></i>".number_format($download['comment'])."</div>
            <div class='pull-right m-r-10'><i title='".$locale['254']."' class='fas fa-thumbtack m-r-10 text-lighter'></i>".number_format($download['submit'])."</div>\n";
            echo "<div class='pull-left m-r-10 admin-icon downloads'><img alt='".$locale['D']."' src='".get_image("ac_D")."'/>\n</div>";
            echo $content;

            echo "</li>\n";
        }
        if (infusion_exists('weblinks')) {
            $content = "<div>".$locale['W']."</div>";
            $content .= 'There are no weblinks';
            $result = dbquery("SELECT weblink_id, weblink_name, weblink_datestamp FROM ".DB_WEBLINKS." ORDER BY weblink_datestamp DESC LIMIT 1");
            if (dbrows($result)) {
                $data = dbarray($result);
                $content = "<div>".$locale['W']."</div>
                <div class='pull-left text-lighter m-r-10'>".showdate('%b%d, %R %p', $data['weblink_datestamp'])."</div>
                <div class='overflow-hide'>
               <a href='".INFUSIONS."weblinks/weblinks_admin.php$aidlink'>".$data['weblink_name']."</a>
                </div>
                ";
            }
            echo "<li class='clearfix m-t-10'>\n";
            echo "<div class='pull-left m-r-10 admin-icon weblinks'><img alt='".$locale['271']." ".$locale['258']."' src='".get_image("ac_W")."'/>\n</div>";
            echo "<div class='pull-right'>".number_format($weblinks['weblink'])." ".$locale['271']."</a></div>\n
            <div class='pull-right m-r-10'><i title='".$locale['254']."' class='fas fa-thumbtack m-r-10 text-lighter'></i>".number_format($weblinks['submit'])."</div>\n";
            echo $content;
            echo "</li>\n";
        }
        if (infusion_exists('gallery')) {

            $content = "<div>".$locale['PH']."</div>";
            $content .= 'There are no gallery';
            $result = dbquery("SELECT photo_id, photo_title, photo_datestamp FROM ".DB_PHOTOS." ORDER BY photo_datestamp DESC LIMIT 1");
            if (dbrows($result)) {
                $data = dbarray($result);
                $content = "<div>".$locale['PH']."</div>
                <div class='pull-left text-lighter m-r-10'>".showdate('%b%d, %R %p', $data['photo_datestamp'])."</div>
                <div class='overflow-hide'>
                <a href='".INFUSIONS."gallery/gallery_admin.php$aidlink'>".number_format($photos['photo'])." ".$locale['261']."</a>
                </div>
                ";
            }
            echo "<li class='clearfix m-t-10'>\n";
            echo "<div class='pull-left m-r-10 admin-icon gallery'><img alt='".$locale['272']." ".$locale['258']."' src='".get_image("ac_PH")."'/>\n</div>";
            echo "<div class='pull-right'>".number_format($photos['photo'])." ".$locale['261']."</a></div>\n
            <div class='pull-right m-r-10'><i title='".$locale['257']."' class='fas fa-comment m-r-10 text-lighter'></i>".number_format($photos['comment'])."</div>
            <div class='pull-right m-r-10'><i title='".$locale['254']."' class='fas fa-thumbtack m-r-10  text-lighter'></i>".number_format($photos['submit'])."</div>\n";
            echo $content;
            echo "</li>\n";
        }
        echo "</ul>\n";
        echo "</div>\n";
        echo "<div class='list-group-item'>\n";
        echo "<h4 class='m-0'>".$locale['277']." <span class='badge pull-right'>".number_format($global_comments['rows'])."</span></h4>";
        echo "</div>\n";
        echo "<div class='list-group-item clearfix'>\n";
        if (count($global_comments['data']) > 0) {
            echo "<ul class='block'>\n";
            foreach ($global_comments['data'] as $i => $comment_data) {
                $comment_item_url = (isset($link_type[$comment_data['comment_type']]) ? "<a href='".sprintf($link_type[$comment_data['comment_type']]."'", $comment_data['comment_item_id'])."'>{%item%}</a>" : '{%item%}');
                $comment_item_name = (isset($comments_type[$comment_data['comment_type']])) ? $comments_type[$comment_data['comment_type']] : $locale['global_073b'];
                echo "<div data-id='$i' class='comment_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
                echo "<div class='pull-left display-inline-block m-r-15' style='margin-top:5px; margin-bottom:10px;'>".display_avatar($comment_data, "50px", '', '', '')."</div>\n";
                echo "<div id='comment_action-$i' class='pull-right dropdown dropdown-menu-right'>\n
                                <a class='dropdown-toggle btn btn-default btn-xs' data-toggle='dropdown'><i class='fa fa-angle-down'></i></a>
                                <ul class='dropdown-menu'>
                                <li><a title='".$locale['274']."' href='".ADMIN."comments.php".$aidlink."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='fa fa-eye fa-fw m-r-10'></i>".$locale['274']."</a></li>
                                <li><a title='".$locale['275']."' href='".ADMIN."comments.php".$aidlink."&amp;action=edit&amp;comment_id=".$comment_data['comment_id']."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='fa fa-edit fa-fw m-r-10'></i>".$locale['275']."</a></li>
                                <li><a title='".$locale['276']."' href='".ADMIN."comments.php".$aidlink."&amp;action=delete&amp;comment_id=".$comment_data['comment_id']."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='fa fa-trash fa-fw m-r-10'></i>".$locale['276']."</a></li>
                                </ul>
                                ";
                echo "</div>\n";
                echo "<div class='pull-right m-r-10 small'>".timer($comment_data['comment_datestamp'])."</div>\n";
                echo "<strong>".(!empty($comment_data['user_id']) ? profile_link($comment_data['user_id'], $comment_data['user_name'], $comment_data['user_status']) : $comment_data['comment_name'])." </strong>\n";
                echo "<span class='text-lighter'>".$locale['273']."</span> ".strtr($comment_item_url, ['{%item%}' => $comment_item_name]);
                $mess = trimlink(strip_tags(parse_textarea($comment_data['comment_message'], FALSE, TRUE)), 70);
                echo "<p>".parse_textarea($mess, TRUE, FALSE)."</p>\n";
                echo "</div>\n";
            }
            if (isset($global_comments['comments_nav'])) {
                echo "<div class='clearfix'>\n";
                echo "<span class='pull-right text-smaller'>".$global_comments['comments_nav']."</span>";
                echo "</div>\n";
            }
            echo "</ul>\n";
        } else {
            echo "<div class='text-center'>".$global_comments['nodata']."</div>\n";
        }
        echo "</div>\n";
        echo "<div class='list-group-item'>\n";
        echo "<h4 class='m-0'>".$locale['278']." <span class='badge pull-right'>".number_format($global_ratings['rows'])."</span></h4>";
        echo "</div>\n";
        echo "<div class='list-group-item clearfix'>\n";
        if (count($global_ratings['data']) > 0) {
            foreach ($global_ratings['data'] as $i => $ratings_data) {
                $ratings_url = isset($link_type[$ratings_data['rating_type']]) ? "<a href='".sprintf($link_type[$ratings_data['rating_type']], $ratings_data['rating_item_id'])."'>{%item%}</a>\n" : "{%item%}";
                $ratings_item = isset($comments_type[$ratings_data['rating_type']]) ? $comments_type[$ratings_data['rating_type']] : $locale['ratings'];
                echo "<!--Start Rating Item-->\n";
                echo "<div class='comment_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
                echo "<div class='pull-left display-inline-block m-r-15' style='margin-top:5px; margin-bottom:10px;'>".display_avatar($ratings_data, "50px", '', '', '')."</div>\n";
                echo "<strong>".profile_link($ratings_data['user_id'], $ratings_data['user_name'], $ratings_data['user_status'])."</strong>\n";
                echo "<span class='text-lighter'>".$locale['273a']." </span>\n";
                echo strtr($ratings_url, ['{%item%}' => $ratings_item]);
                echo "<span class='text-lighter m-l-10'>".str_repeat("<i class='fa fa-star fa-fw'></i>", $ratings_data['rating_vote'])."</span>\n<br/>";
                echo timer($ratings_data['rating_datestamp'])."<br/>\n";
                echo "</div>\n";
                echo "<!--End Rating Item-->\n";
            }
            if (isset($global_ratings['ratings_nav'])) {
                echo "<div class='clearfix'>\n";
                echo "<span class='pull-right text-smaller'>".$global_ratings['ratings_nav']."</span>";
                echo "</div>\n";
            }
        } else {
            echo "<div class='text-center'>".$global_ratings['nodata']."</div>\n";
        }
        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-6 col-lg-3 responsive-admin-column'>\n";
        // lets do an internal analytics
        // members registered
        // members online
        // members
        echo "<div class='list-group'>\n";
        echo "<div class='list-group-item'><h4 class='m-0'>Users</h4></div>";
        echo "<!--Start Members-->\n";
        echo "<div class='list-group-item'>\n";
        echo "<div class='row'>\n";
        foreach ($panels as $panel => $block) {
            $link_start = ''; $link_end = '';
            if (checkrights('M')) {
                $block['link'] = empty($block['link']) ? $block['link'] : '&amp;'.$block['link'];
                $link_start = "<a class='text-sm' href='".ADMIN."members.php".$aidlink.$block['link']."'>";
                $link_end = "</a>\n";
            }
            echo "<div class='col-xs-12 col-sm-3'>\n";
            echo "<h2 class='m-0 text-light text-info'>".number_format($members[$panel])."</h2>\n";
            echo "<span class='m-t-10'>".$link_start.$locale[$block['title']].$link_end."</span>\n";
            echo "</div>\n";
        }
        echo "</div>\n";
        echo "</div>\n";
        echo "<!--End Members-->\n";
        echo "<div class='list-group-item'>\n";
        echo "<h4 class='m-0 display-inline-block'>".$locale['283']."</h4> <span class='pull-right badge'>".number_format((int)$infusions_count)."</span>";
        echo "</div>\n";
        echo "<div class='list-group-item'>\n";
        if ($infusions_count > 0) {
            echo "<div class='comment_content'>\n";
            if (!empty($global_infusions)) {
                foreach ($global_infusions as $inf_id => $inf_data) {
                    echo "<span class='badge m-b-10 m-r-5'>".$inf_data['inf_title']."</span>\n";
                }
            }
            echo "</div>\n";
            echo checkrights("I") ? "<div class='text-right'>\n<a href='".ADMIN."infusions.php".$aidlink."'>".$locale['285']."</a><i class='fas fa-angle-right text-lighter m-l-15'></i></div>\n" : '';
        } else {
            echo "<div class='text-center'>".$locale['284']."</div>\n";
        }
        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-6 col-lg-3 responsive-admin-column'>\n";
        echo "<div class='list-group'>\n<div class='list-group-item'>\n";
        echo "<h4 class='display-inline-block m-0'>".$locale['279']."</h4>\n";
        echo "<span class='pull-right badge'>".number_format($global_submissions['rows'])."</span>\n";
        echo "</div>\n";
        echo "<div class='list-group-item'>\n";
        if (count($global_submissions['data']) > 0) {
            foreach ($global_submissions['data'] as $i => $submit_date) {
                $review_link = sprintf($submit_data[$submit_date['submit_type']]['admin_link'], $submit_date['submit_id']);

                echo "<!--Start Submissions Item-->\n";
                echo "<div data-id='$i' class='submission_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
                echo "<div class='pull-left display-inline-block' style='margin-top:5px; margin-bottom:10px;'>".display_avatar($submit_date, "25px", "", FALSE, "img-rounded m-r-5")."</div>\n";
                echo "<strong>".profile_link($submit_date['user_id'], $submit_date['user_name'], $submit_date['user_status'])." </strong>\n";
                echo "<span class='text-lighter'>".$locale['273b']." <strong>".$submit_data[$submit_date['submit_type']]['submit_locale']."</strong></span><br/>\n";
                echo timer($submit_date['submit_datestamp'])."<br/>\n";
                if (!empty($review_link)) {
                    echo "<a class='btn btn-xs btn-default m-t-5' title='".$locale['286']."' href='".$review_link."'>".$locale['286']."</a>\n";
                }
                echo "</div>\n";
                echo "<!--End Submissions Item-->\n";
            }

            if (isset($global_submissions['submissions_nav'])) {
                echo "<div class='clearfix'>\n";
                echo "<span class='pull-right text-smaller'>".$global_submissions['submissions_nav']."</span>";
                echo "</div>\n";
            }
        } else {
            echo "<div class='text-center'>".$global_submissions['nodata']."</div>\n";
        }
        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-6 col-lg-3 responsive-admin-column'>\n";

        echo "</div>\n</div>\n";
        closetable();
    }

    public static function do_admin_icons() {
        global $admin_icons, $admin_images;

        $aidlink = self::get_aidlink();
        $locale = parent::get_locale();
        //add_to_head('<link href="'.THEME.'templates/css/autogrid.css" rel="stylesheet" />');
        opentable($locale['admin_apps']);
        echo "<div class='row'>\n";
        if (count($admin_icons['data']) > 0) {
            foreach ($admin_icons['data'] as $i => $data) {
                echo "<div class='display-table col-xs-6 col-sm-3 col-md-2' style='height:140px;'>\n";
                if ($admin_images) {
                    echo "<div class='panel-body align-middle text-center' style='width:100%;'>\n";
                    echo "<a href='".$data['admin_link'].$aidlink."'><img style='max-width:48px;' src='".get_image("ac_".$data['admin_rights'])."' alt='".$data['admin_title']."'/>\n</a>\n";
                    echo "<div class='overflow-hide'>\n";
                    echo "<a class='icon_title' href='".$data['admin_link'].$aidlink."'>".$data['admin_title']."</a>\n";
                    echo "</div>\n";
                    echo "</div>\n";
                } else {
                    echo "<span class='small'>".THEME_BULLET." <a href='".$data['admin_link'].$aidlink."'>".$data['admin_title']."</a></span>";
                }
                echo "</div>\n";
            }
        }
        echo "</div>\n";
        closetable();

    }

}
