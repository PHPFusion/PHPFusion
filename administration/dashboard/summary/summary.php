<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: summary.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

/**
 * Summary Admin Dashboard Widget
 * Widget to display infusions latest activity
 * Limited to core infusions only
 * @return string
 */
function display_summary_widget() {
    global $news, $articles, $forum, $weblinks, $download, $photos;

    $locale = fusion_get_locale();
    $aidlink= fusion_get_aidlink();
    $content = FALSE;
    $tpl = \PHPFusion\Template::getInstance('summary-widget');
    $tpl->set_template(__DIR__.'/summary.html');
    // Check news infusion
    if (infusion_exists('news')) {
        $info = [
            "stat_icons" => "
            <span class='m-r-10'><i title='".$locale['269']."' class='fas fa-comment m-r-5 text-lighter'></i>".format_num($news['news'])."</span>
            <span class='pull-right m-r-10'><i title='".$locale['257']."' class='fas fa-comment m-r-5 text-lighter'></i>".format_num($news['comment'])."</span>
            <span class='pull-right m-r-10'><i title='".$locale['254']."' class='fas fa-users m-r-5 text-lighter'></i>".format_num($news['submit'])."</span>
            ",
            "icon" => "<span class='admin-icon news'><img alt='".$locale['269']." ".$locale['258']."' src='".get_image("ac_N")."'/></span>",
            "title" => $locale['269'],
            "date" => "No recent activity",
            "text" => ""
        ];
        $result = dbquery("SELECT news_id, news_subject, news_datestamp FROM ".DB_NEWS." WHERE news_draft=0 ORDER BY news_datestamp DESC LIMIT 1");
        if (dbrows($result)) {
            $data = dbarray($result);
            $info['date'] = showdate('%b %d, %R %p', $data['news_datestamp']);
            $info['text'] = "<a href='".INFUSIONS."news/news_admin.php$aidlink&amp;action=edit&amp;ref=news_form&amp;news_id=".$data['news_id']."'>".$data['news_subject']."</a>";
        }
        $tpl->set_block('li', $info);
        $content = TRUE;
    }
    // Check articles infusion
    if (infusion_exists('articles')) {
        $info = [
            "stat_icons" => "
            <span class='m-r-5'><i title='".$locale['257']."' class='fas fa-edit m-r-10 text-lighter'></i> ".format_num($articles['article'])." ".$locale['270']."</span>
            <span class='m-r-5'><i title='".$locale['257']."' class='fas fa-comment m-r-10 text-lighter'></i>".format_num($articles['comment'])."</span>
            <span class='m-r-5'><i title='".$locale['254']."' class='fas fa-thumbtack m-r-10 text-lighter'></i>".format_num($articles['submit'])."</span>
            ",
            "icon" => "<span class='admin-icon articles'><img alt='".$locale['A']."' src='".get_image("ac_A")."'/></span>",
            "title" => $locale['A'],
            "date" => "No recent activity",
            "text" => ""
        ];
        $result = dbquery("SELECT article_id, article_subject, article_datestamp FROM ".DB_ARTICLES." WHERE article_draft=0 ORDER BY article_datestamp DESC LIMIT 1");
        if (dbrows($result)) {
            $data = dbarray($result);
            $info['date'] = showdate('%b %d, %R %p', $data['article_datestamp']);
            $info['text'] = "<a href='".INFUSIONS."articles/articles_admin.php$aidlink'>".$data['article_subject']."</a>";
        }
        $tpl->set_block('li', $info);
        $content = TRUE;
    }
    // Check forum infusion
    if (infusion_exists('forum')) {
        $info = [
            "stat_icons" => "
            <span class='m-r-5'><i title='".$locale['256']."' class='fas fa-edit m-r-5 text-lighter'></i>".format_num($forum['thread'])."</span>
            <span class='m-r-5'><i title='".$locale['259']."' class='fas fa-comment text-lighter m-r-5'></i>".format_num($forum['post'])."</span>
            <span class='m-r-5'><i title='".$locale['260']."' class='fas fa-users text-lighter m-r-5'></i>".format_num($forum['users'])."</span>
            ",
            "icon" => "<span class='admin-icon forum'><img alt='".$locale['F']."' src='".get_image("ac_F")."'/></span>",
            "title" => $locale['F'],
            "date" => "No recent activity",
            "text" => ""
        ];

        $result = dbquery("SELECT p.post_id, p.thread_id, t.thread_subject, p.post_datestamp FROM ".DB_FORUM_POSTS." p
            INNER JOIN ".DB_FORUM_THREADS." t ON t.thread_id = p.thread_id
            ORDER BY post_datestamp DESC LIMIT 1");
        if (dbrows($result)) {
            $data = dbarray($result);
            $info['text'] = "<a href='".INFUSIONS."forum/viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['post_id']."'>".$data['thread_subject']."</a>";
            $info['date'] = showdate('%b %d, %R %p', $data['post_datestamp']);
        }
        $tpl->set_block('li', $info);
        $content = TRUE;
    }
    // Check download infusion
    if (infusion_exists('downloads')) {
        $info = [
            "stat_icons" => "
            <span class='pull-right m-r-5'><i title='".$locale['257']."' class='fas fa-comment m-r-5 text-lighter'></i>".format_num($download['download'])."</span>\n
            <span class='pull-right m-r-5'><i title='".$locale['257']."' class='fas fa-comment m-r-5 text-lighter'></i>".format_num($download['comment'])."</span>
            <span class='pull-right m-r-5'><i title='".$locale['254']."' class='fas fa-thumbtack m-r-5 text-lighter'></i>".format_num($download['submit'])."</span>
            ",
            "icon" => "<span class='admin-icon downloads'><img alt='".$locale['D']."' src='".get_image("ac_D")."'/></span>",
            "title" => $locale['D'],
            "date" => "No recent activity",
            "text" => ""
        ];
        $result = dbquery("SELECT download_id, download_title, download_datestamp FROM ".DB_DOWNLOADS." ORDER BY download_datestamp DESC LIMIT 1");
        if (dbrows($result)) {
            $data = dbarray($result);
            $info['text'] = "<a href='".INFUSIONS."downloads/downloads_admin.php$aidlink'>".$data['download_title']."</a>";
            $info['date'] = showdate('%b %d, %R %p', $data['download_datestamp']);
        }
        $tpl->set_block('li', $info);
        $content = TRUE;
    }
    // Check weblinks infusion
    if (infusion_exists('weblinks')) {
        $info = [
            "stat_icons" => "
            <span class='pull-right m-r-5'>".format_num($weblinks['weblink'])." ".$locale['271']."</span>\n
            <span class='pull-right m-r-5'><i title='".$locale['254']."' class='fas fa-thumbtack m-r-10 text-lighter'></i>".format_num($weblinks['submit'])."</span>
            ",
            "icon" => "<span class='admin-icon weblinks'><img alt='".$locale['271']." ".$locale['258']."' src='".get_image("ac_W")."'/></span>",
            "title" => $locale['W'],
            "date" => "No recent activity",
            "text" => ""
        ];

        $result = dbquery("SELECT weblink_id, weblink_name, weblink_datestamp FROM ".DB_WEBLINKS." ORDER BY weblink_datestamp DESC LIMIT 1");
        if (dbrows($result)) {
            $data = dbarray($result);
            $info['text'] = "<a href='".INFUSIONS."weblinks/weblinks_admin.php$aidlink'>".$data['weblink_name']."</a>";
            $info['date'] = showdate('%b %d, %R %p', $data['weblink_datestamp']);
        }
        $tpl->set_block('li', $info);
        $content = TRUE;
    }
    // Check gallery infusion
    if (infusion_exists('gallery')) {
        $info = [
            "stat_icons" => "
            <span class='pull-right'>".format_num($photos['photo'])." ".$locale['261']."</span>\n
            <span class='pull-right m-r-5'><i title='".$locale['257']."' class='fas fa-comment m-r-10 text-lighter'></i>".format_num($photos['comment'])."</span>
            <span class='pull-right m-r-5'><i title='".$locale['254']."' class='fas fa-thumbtack m-r-10  text-lighter'></i>".format_num($photos['submit'])."</span>
            ",
            "icon" => "<span class='admin-icon gallery'><img alt='".$locale['272']." ".$locale['258']."' src='".get_image("ac_PH")."'/></span>",
            "title" => $locale['PH'],
            "date" => "No recent activity",
            "text" => ""
        ];
        $result = dbquery("SELECT photo_id, photo_title, photo_datestamp FROM ".DB_PHOTOS." ORDER BY photo_datestamp DESC LIMIT 1");
        if (dbrows($result)) {
            $data = dbarray($result);
            $info['date'] = showdate('%b %d, %R %p', $data['photo_datestamp']);
            $info['text'] = "<a href='".INFUSIONS."gallery/gallery_admin.php$aidlink'>".number_format($photos['photo'])." ".$locale['261']."</a>";
        }
        $tpl->set_block('li', $info);
        $content = TRUE;
    }

    if ($content === FALSE) {
        $tpl->set_block('li_na');
    }
    $tpl->set_tag('opensidex', fusion_get_function("opensidex", "Site Summary"));
    $tpl->set_tag('closesidex', fusion_get_function("closesidex", ""));

    return $tpl->get_output();
}
