<?php
    /*-------------------------------------------------------+
    | PHP-Fusion Content Management System
    | Copyright (C) PHP-Fusion Inc
    | http://www.php-fusion.co.uk/
    +--------------------------------------------------------+
    | Filename: dashboard.tpl.php
    | Author: Hien (Frederick MC Chan)
    | Author: Domi (Joakim Falk)
    +--------------------------------------------------------+
    | This program is released as free software under the
    | Affero GPL license. You can redistribute it and/or
    | modify it under the terms of this license which you
    | can read by viewing the included agpl.txt or online
    | at www.gnu.org/licenses/agpl.html. Removal of this
    | copyright header is strictly prohibited without
    | written permission from the original author(s).
    +--------------------------------------------------------*/

    // These are dashboard that are available for Developers to try out.

    // In V8, these are called plugins, When a certain file has these functions,

    // There will be an admin interface in infusions SDK that we will be delivering to
    // allow infusions plugin to render its plugins via Theme Engine.

    // forum view post required function
    function forum_rowstart($thread_id, $post_id)
    {
        $forum_max_rowstart = 20;
        $result = dbquery("SELECT post_id FROM ".DB_POSTS." WHERE thread_id='".$thread_id."' ORDER BY post_datestamp ASC ");
        if (dbrows($result)>0) {
            $i = 1;
            while ($data = dbarray($result)) {
                if ($post_id == $data['post_id']) {

                    if ($i>$forum_max_rowstart) {
                        return "&amp;rowstart=".($i/$forum_max_rowstart*$forum_max_rowstart-1)."";
                        exit();
                    }
                }
                $i++;
            }
        }
    }
    // forum fancy image
    function forum_img($forum_id) {
        $img_array = array(
            '12' => ASSETS."bg/forum/chillout_forum.jpg",
            '37' => ASSETS."bg/forum/6_forum.png",
            '104' => ASSETS."bg/forum/archive.jpg",
            '100' => ASSETS."bg/forum/staff_forum.png",
            '101' => ASSETS."bg/forum/mgt_forum.png",
            '102' => ASSETS."bg/forum/pfdn_forum.png",
            '55' => ASSETS."bg/forum/admin_forum.png",
            '57' => ASSETS."bg/forum/7_forum.png",
            '86' => ASSETS."bg/forum/nss.png",
            '76' => ASSETS."bg/forum/addon_forum.png",
            '81' => ASSETS."bg/forum/mod_support.png",
            '120' => ASSETS."bg/forum/licensing.png",
        );
        $image = $img_array[$forum_id];
        return $image;
    }
    // latest active and user started threads merged.
    function forum_dashboard()
    {
        global $user_data;
        $html = '';
        // forum

        // Set how many items you want.
        $latest = 10;
        $your_post = 10;

        // Latest Active Threads Postings
        $result = dbquery("
            SELECT tf.forum_access,
            tt.thread_id, tt.thread_subject, tt.forum_id, tt.thread_lastpost, tt.thread_lastuser, tt.thread_postcount,
            tu.user_id AS user_id1, tu.user_name AS user_name1, tu.user_status AS user_status1, tu.user_avatar AS user_avatar1,
            tu2.user_id AS user_id2, tu2.user_name AS user_name2, tu2.user_status AS user_status2, tu2.user_avatar AS user_avatar2,
            tp.post_message,
            img.forum_id as master_id, img.forum_name as master_name,
            ts.post_message as post_message_last, ts.post_id as post_id_last
            FROM ".DB_THREADS." tt
            INNER JOIN ".DB_FORUMS." tf ON tt.forum_id = tf.forum_id
            LEFT JOIN ".DB_FORUMS." img ON tf.forum_cat=img.forum_id
            LEFT JOIN ".DB_USERS." tu ON tt.thread_author = tu.user_id
            LEFT JOIN ".DB_USERS." tu2 ON tt.thread_lastuser = tu2.user_id
            INNER JOIN ".DB_POSTS." tp ON tt.thread_id = tp.thread_id
            INNER JOIN ".DB_POSTS." ts ON tt.thread_lastpost = ts.post_datestamp
            WHERE ".groupaccess('tf.forum_access')." AND tt.thread_hidden='0'
            GROUP BY tt.thread_id
            ORDER BY tt.thread_lastpost DESC
            LIMIT 0,$latest
	        ");
        if (dbrows($result)) {
            while ($forum_data = dbarray($result)) {
                $forum_data['forum_color'] = '#1585d4';
                $forum_post[$forum_data['thread_id']] = $forum_data;
            }
        }

        // All User Postings
        $result2 = dbquery("
            SELECT tf.forum_access,
            tt.thread_id, tt.thread_subject, tt.forum_id, tt.thread_lastpost, tt.thread_lastuser, tt.thread_postcount,
            tu.user_id AS user_id1, tu.user_name AS user_name1, tu.user_status AS user_status1, tu.user_avatar AS user_avatar1,
            tu2.user_id AS user_id2, tu2.user_name AS user_name2, tu2.user_status AS user_status2, tu2.user_avatar AS user_avatar2,
            tp.post_message,
            img.forum_id as master_id, img.forum_name as master_name,
            ts.post_message as post_message_last, ts.post_id as post_id_last
            FROM ".DB_THREADS." tt
            INNER JOIN ".DB_FORUMS." tf ON tt.forum_id = tf.forum_id
            LEFT JOIN ".DB_FORUMS." img ON tf.forum_cat=img.forum_id
            LEFT JOIN ".DB_USERS." tu ON tt.thread_author = tu.user_id
            LEFT JOIN ".DB_USERS." tu2 ON tt.thread_lastuser = tu2.user_id
            INNER JOIN ".DB_POSTS." tp ON tt.thread_id = tp.thread_id
            INNER JOIN ".DB_POSTS." ts ON tt.thread_lastpost = ts.post_datestamp
            WHERE tt.thread_author='1' AND ".groupaccess('tf.forum_access')." AND tt.thread_hidden='0'
            GROUP BY tt.thread_id
            ORDER BY tt.thread_lastpost DESC
            LIMIT 0,100
	        ");
        while ($forum_data_2 = dbarray($result2)) {
                //print_p($fdata2);
                $forum_data_2['forum_color'] = '#f0ad4e';
                $forum_post[$forum_data_2['thread_id']] = $forum_data_2;
            }

        if (!empty($forum_post)) {
            $i = 0;
            $html .= "<div class='row'>\n";
            //$html .= "<div class='m-t-10 text-right'>\n<small><a class='btn btn-primary btn-sm' href='".FORUM."'><i class='entypo comment'></i> Go to Forum</a>\n</small>\n</div>\n";

            foreach($forum_post as $data) {
                if ($i=='4') {
                    $i = 0;
                    $html .= "</div><div class='row'>\n";
                }

                $post_message = parseubb(nl2br(stripslashes($data['post_message'])));
                $post_message_last = parseubb(nl2br(stripslashes($data['post_message_last'])));
                $avatar_1 = ($data['user_avatar1'] && file_exists((IMAGES."avatars/".$data['user_avatar1']))) ? "<img style='width:25px;' class='m-r-10 img-rounded' src='".IMAGES."avatars/".$data['user_avatar1']."'/>" : "<img style='width:25px;' class='img-rounded m-r-10' src='".IMAGES."avatars/noavatar50.png'/>";
                $avatar_2 = ($data['user_avatar2'] && file_exists((IMAGES."avatars/".$data['user_avatar2']))) ? "<img style='width:25px;' class='m-r-10 img-rounded' src='".IMAGES."avatars/".$data['user_avatar2']."'/>" : "<img style='width:25px;' class='img-rounded m-r-10' src='".IMAGES."avatars/noavatar50.png'/>";

                $html .= "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
                $html .= "<img class='img-responsive' style='max-width:95%' src='".forum_img($data['master_id'])."'>\n";
                $html .= "<aside style='max-width:95%; background:#000; border:none; border-radius:0; padding:5px 15px; margin-bottom:0px;'>\n";
                $html .= "<h4><a style='color:#fff' href='#'>".$data['master_name']."</a></h4>\n";
                $html .= "</aside>\n";
                $html .= "<aside style='max-width:95%; background:".$data['forum_color']."; border:none; border-radius:0; padding:5px 15px; margin-bottom:0px;'>\n";
                $html .= "<h5><a style='font-size:13px; font-weight:600; color:#fff' href='".FORUM."viewthread.php?thread_id=".$data['thread_id'].forum_rowstart($data['thread_id'], $data['post_id_last'])."#post_".$data['post_id_last']."'>".$data['thread_subject']."</a></h5>\n";
                $html .= "</aside>\n";
                $html .= "<aside style='margin-bottom: 0px; max-height: 260px; font-weight:600; font-size:12px; overflow:hidden; max-width:95%; background:#f1f1f1; border:none; border-radius:0; padding:5px 15px;'>\n";
                $html .= "$avatar_1";
                $html .= $post_message;
                $html .= "</aside>\n";
                $html .= "</aside>\n";
                if (($data['thread_postcount']-1) > 0) {
                    $html .= "<aside style='border:none; padding:5px 15px; margin-bottom: 0px; max-height: 260px; font-weight:600; font-size:12px; overflow:hidden; max-width:95%; background:#f1f1f1; border-radius:0;'>\n";
                    $html .= "<hr style='border-top:1px dashed #ccc; margin:10px 0px;'>\n";
                    $html .= "<p>".($data['thread_postcount']-1)." ".((($data['thread_postcount']-1) > 1) ? 'replies': 'reply')." to this topic.<p>";
                    $html .= "<a href='".FUSION_SELF."?lookup=".$data['user_id2']."'>$avatar_2</a>";
                    $html .= $post_message_last;
                    $html .= "</aside>\n";
                }
                $html .= "<aside style='max-height: 260px; overflow:hidden; font-weight:400; max-width:95%; background:#f1f1f1; border:none; border-radius:0; padding:5px 15px;'>\n";
                $html .= "<small>Posted ".timer($data['thread_lastpost'])." <a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."'>Read More <i class='entypo circled-right'></i></a></small> \n";
                $html .= "</aside>\n";
                $html .= "</div>\n";
                $i++;
            }
            $html .= "</div>\n";
        }



        return $html;
    }
    // tracked forum threads
    function forum_tracked()
    {
        global $userdata;
        $html = '';
        $check_track = 1;
        if ($check_track) {
            $result = dbquery("
            SELECT tf.forum_access, tn.thread_id, tn.notify_datestamp, tn.notify_user,
            tt.thread_subject, tt.forum_id, tt.thread_lastpost, tt.thread_lastuser, tt.thread_postcount,
            tu.user_id AS user_id1, tu.user_name AS user_name1, tu.user_status AS user_status1, tu.user_avatar AS user_avatar1,
            tu2.user_id AS user_id2, tu2.user_name AS user_name2, tu2.user_status AS user_status2, tu2.user_avatar AS user_avatar2,
            tp.post_message,
            img.forum_id as master_id, img.forum_name as master_name,
            ts.post_message as post_message_last, ts.post_id as post_id_last
            FROM ".DB_THREAD_NOTIFY." tn
            INNER JOIN ".DB_THREADS." tt ON tn.thread_id = tt.thread_id
            INNER JOIN ".DB_FORUMS." tf ON tt.forum_id = tf.forum_id
            LEFT JOIN ".DB_FORUMS." img ON tf.forum_cat=img.forum_id
            LEFT JOIN ".DB_USERS." tu ON tt.thread_author = tu.user_id
            LEFT JOIN ".DB_USERS." tu2 ON tt.thread_lastuser = tu2.user_id
            INNER JOIN ".DB_POSTS." tp ON tt.thread_id = tp.thread_id
            INNER JOIN ".DB_POSTS." ts ON tt.thread_lastpost = ts.post_datestamp
            WHERE tn.notify_user=".$userdata['user_id']." AND ".groupaccess('forum_access')." AND tt.thread_hidden='0'
            GROUP BY tn.thread_id
            ORDER BY tn.notify_datestamp DESC
            LIMIT 0,10
	        ");

            if (dbrows($result)>0) {
                $i = 0;
                $html .= "<div class='row'>\n";
                while ($data = dbarray($result)) {
                    if ($i=='4') {
                        $i = 0;
                        $html .= "</div><div class='row'>\n";
                    }

                    $post_message = parseubb(nl2br(stripslashes($data['post_message'])));
                    $post_message_last = parseubb(nl2br(stripslashes($data['post_message_last'])));
                    $avatar_1 = ($data['user_avatar1'] && file_exists((IMAGES."avatars/".$data['user_avatar1']))) ? "<img style='width:25px;' class='m-r-10 img-rounded' src='".IMAGES."avatars/".$data['user_avatar1']."'/>" : "<img style='width:25px;' class='img-rounded m-r-10' src='".IMAGES."avatars/noavatar50.png'/>";
                    $avatar_2 = ($data['user_avatar2'] && file_exists((IMAGES."avatars/".$data['user_avatar2']))) ? "<img style='width:25px;' class='m-r-10 img-rounded' src='".IMAGES."avatars/".$data['user_avatar2']."'/>" : "<img style='width:25px;' class='img-rounded m-r-10' src='".IMAGES."avatars/noavatar50.png'/>";
                    // need a master forum_id.
                    $html .= "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
                    $html .= "<img class='img-responsive' style='max-width:95%' src='".forum_img($data['master_id'])."'>\n";
                    $html .= "<aside style='max-width:95%; background:#000; border:none; border-radius:0; padding:5px 15px; margin-bottom:0px;'>\n";
                    $html .= "<h4><a style='color:#fff' href='#'>".$data['master_name']."</a></h4>\n";
                    $html .= "</aside>\n";
                    $html .= "<aside style='max-width:95%; background:#5cb85c; border:none; border-radius:0; padding:5px 15px; margin-bottom:0px;'>\n";
                    $html .= "<h5><a style='font-size:13px; font-weight:600; color:#fff' href='".FORUM."viewthread.php?thread_id=".$data['thread_id'].forum_rowstart($data['thread_id'], $data['post_id_last'])."#post_".$data['post_id_last']."'>".$data['thread_subject']."</a></h5>\n";
                    $html .= "</aside>\n";

                    $html .= "<aside style='padding:5px 15px; margin-bottom: 0px; max-height: 260px; font-weight:600; font-size:12px; overflow:hidden; max-width:95%; background:#f1f1f1; border:none; border-radius:0;'>\n";
                    $html .= "<a href='".FUSION_SELF."?lookup=".$data['user_id1']."'>$avatar_1</a>";
                    $html .= $post_message;
                    $html .= "</aside>\n";
                    if (($data['thread_postcount']-1) > 0) {
                    $html .= "<aside style='border:none; padding:5px 15px; margin-bottom: 0px; max-height: 260px; font-weight:600; font-size:12px; overflow:hidden; max-width:95%; background:#f1f1f1; border-radius:0;'>\n";
                    $html .= "<hr style='border-top:1px dashed #ccc; margin:10px 0px;'>\n";
                    $html .= "<p>".($data['thread_postcount']-1)." ".((($data['thread_postcount']-1) > 1) ? 'replies': 'reply')." to this topic.<p>";
                    $html .= "<a href='".FUSION_SELF."?lookup=".$data['user_id2']."'>$avatar_2</a>";
                    $html .= $post_message_last;
                    $html .= "</aside>\n";
                    }
                    $html .= "<aside style='margin-bottom:0px; max-height: 260px; overflow:hidden; font-weight:400; max-width:95%; background:#f1f1f1; border:none; border-radius:0; padding:5px 15px;'>\n";
                    $html .= "<small>Posted ".timer($data['thread_lastpost'])." <a href='".FORUM."viewthread.php?thread_id=".$data['thread_id'].forum_rowstart($data['thread_id'], $data['post_id_last'])."#post_".$data['post_id_last']."'>Read More <i class='entypo circled-right'></i></a></small> \n";
                    $html .= "</aside>\n";

                    $html .= "<aside style='max-height: 260px; overflow:hidden; font-weight:400; max-width:95%; background:#f1f1f1; border:none; border-radius:0; padding:5px 15px;'>\n";
                    $html .= "<a class='btn btn-xs btn-primary' href='".INFUSIONS."forum_threads_list_panel_dev/my_tracked_threads.php?delete=".$data['thread_id']."'/>Stop Tracking</a>\n";
                    $html .= "</aside>\n";

                    $html .= "</div>\n";
                    $i++;
                }
                $html .= "</div>\n";
            }
        } else {
            $html .= "You do not have any tracked threads now.";
        }

        return $html;

    }
    // news - deprecated
    function news_dashboard()
    {   global $user_data, $settings;
        $news_max = 3;
        $result = dbquery(
            "SELECT a.*, b.*, c.user_id, c.user_name, c.user_status FROM ".DB_NEWS." a
                LEFT JOIN ".DB_NEWS_CATS." b ON a.news_cat=b.news_cat_id
                LEFT JOIN ".DB_USERS." c ON a.news_name=c.user_id
                WHERE ".groupaccess('news_visibility')." AND (news_start='0'||news_start<=".time().")
                AND (news_end='0'||news_end>=".time().") AND news_draft='0'
                 ORDER BY news_sticky DESC, news_datestamp DESC LIMIT 0 , $news_max
                ");

        $html = '';
        $html .= "<div class='panel panel-default'>\n";
        $html .= "<div class='panel-body'/>\n";
        $html .= "<p><span class='m-r-10'><img src='".THEME_IMG."icons/mt.png'></span><strong>MT Annoucements</strong>\n</p>\n";
        $html .= "<ul class='profile-side-ul'/>\n";

        while ($data = dbarray($result)) {
            $count_comment = dbcount("('comment_id')", DB_COMMENTS, "comment_type='N' AND comment_item_id='".$data['news_id']."'");
            $html .= "<li><div class='row'>\n";
            $html .= "<div class='col-xs-12 col-sm-1 col-md-1 col-lg-1'>\n<img style='max-width:32px; margin: 5px auto;' src='".THEME_IMG."icons/news.png'></div>\n";
            $html .= "<div class='col-xs-12 col-sm-8 col-md-8 col-lg-8'>\n <a style='font-size:13px; color: #428bca' href='".BASEDIR."news.php?readmore=".$data['news_id']."'>".$data['news_subject']."</a> in <small><a href='".BASEDIR."news.php?category=".$data['news_cat']."'>".$data['news_cat_name']."</a> - ".timer($data['news_datestamp'])."</small><br/><small>".trim_text(nl2br($data['news_news']),100)." <p><a style='color: #428bca' href='".BASEDIR."news.php?readmore=".$data['news_id']."'>Read Full.</a></p>\n </small></div>\n";
            $html .= "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n <small>By : ".profile_link($data['news_name'], $data['user_name'], $data['user_status'])." on ".showdate('shortdate', $data['news_datestamp'])." <br/>\n";
            if ($data['news_allow_comments'] == '1') {
                if ($count_comment < 1) {
                    $html .= "<a href='".BASEDIR."news.php?readmore=".$data['news_id']."#comment'>Leave a comment</a><br/>\n";
                } else {
                    $html .= "<a href='".BASEDIR."news.php?readmore=".$data['news_id']."#comment'> $count_comment ".(($count_comment > 1) ? "Comments" : "Comment")." </a><br/>\n";
                }
            } else {
                $html .= "Comments Disabled";
            }
            $html .= $data['news_reads']." ".(($data['news_reads'] > 1) ? "Reads" : "Read")."</small></div>\n";
            $html .= "</div></li>\n";
        }
        $html .= "<li>\n";
        $html .= "<small><a class='btn btn-default btn-sm' href='".BASEDIR."news.php'><i class='entypo docs'></i> Read all News</a>\n</small>\n";
        $html .= "</li>\n";
        $html .= "</ul>\n";

        $html .= "</div>\n</div>\n";
        return $html;
    }

?>