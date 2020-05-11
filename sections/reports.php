<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/sections/latest.php
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

use PHPFusion\Infusions\Forum\Classes\ForumModerator;
use PHPFusion\Infusions\Forum\Classes\ForumServer;
use PHPFusion\Infusions\Forum\Classes\Moderator\ForumMod;

$userdata = fusion_get_userdata();
$locale = fusion_get_locale();
$forum_moderator = new ForumModerator();
$forum_moderator::setForumMods(array("forum_mods"=>""));
if (!iMOD) {
    redirect(FORUM."index.php");
}
$forum_settings = ForumServer::get_forum_settings();
$this->forum_info['title'] = "Forum Reports";
$this->forum_info['link'] = FORUM;
$open_reports = dbcount("(report_id)", DB_FORUM_REPORTS, "report_status=0");
$closed_reports = dbcount("(report_id)", DB_FORUM_REPORTS, "report_status=1");
$this->forum_info['section_links'] = [
    "active" => [
        "title" => "Active Reports",
        "link"  => FORUM."index.php?section=reports&amp;type=active",
        "count" => $open_reports ? format_num($open_reports) : "",
    ],
    "closed" => [
        "title" => "Closed Reports",
        "link"  => FORUM."index.php?section=reports&amp;type=closed",
        "count" => $closed_reports ? format_num($closed_reports) : "",
    ],
];
$read = FALSE;
$rid = get('rid', FILTER_VALIDATE_INT);
if ($rid) {
    $this->forum_info['description'] = "Report details";
    $read = TRUE;
    $report_status = [
        0 => "New",
        1 => "Closed",
    ];

    $report_action = [
        0 => "Do not change",
        1 => "Delete Post",
        2 => "Delete and Ban",
        3 => "Rejected"
    ];
    $rdata = [
        "report_alerts"  => 0,
        "report_comment" => "",
        "report_actions" => 0,
    ];
    $count_query = "SELECT f.report_id
                    FROM ".DB_FORUM_REPORTS." f
                    INNER JOIN ".DB_FORUM_POSTS." p ON f.post_id=p.post_id
                    INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
                    INNER JOIN ".DB_FORUMS." tf ON p.forum_id = tf.forum_id
                    ".(multilang_table("FO") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND " : "WHERE ").groupaccess('tf.forum_access')."
                    AND f.report_id='".intval($rid)."'
                    GROUP BY f.post_id";
    $query = "SELECT  f.post_id 'report_post_id', t.thread_id, t.thread_subject, t.thread_author, t.thread_lastuser, t.thread_lastpost, t.thread_lastpostid,
            t.thread_postcount, t.thread_locked, t.thread_sticky, t.thread_poll, t.thread_postcount, t.thread_views, t.thread_tags,
            t.forum_id 'forum_id', tf.*, f.*, p.post_id, p.post_message, p.post_smileys,
            COUNT(pv.forum_vote_user_id) 'poll_voted',
            IF (n.thread_id > 0, 1 , 0) 'user_tracked'
            FROM ".DB_FORUM_REPORTS." f
            INNER JOIN ".DB_FORUM_POSTS." p ON f.post_id=p.post_id
            INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
            INNER JOIN ".DB_FORUMS." tf ON p.forum_id = tf.forum_id
            LEFT JOIN ".DB_FORUM_POLL_VOTERS." pv ON pv.thread_id = t.thread_id AND pv.forum_vote_user_id='".$userdata['user_id']."' AND t.thread_poll=1
            LEFT JOIN ".DB_FORUM_THREAD_NOTIFY." n ON n.thread_id = t.thread_id AND n.notify_user = '".$userdata['user_id']."'
            ".(multilang_table("FO") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND " : "WHERE ").groupaccess('tf.forum_access')."
            AND f.report_id='".intval($rid)."' ORDER BY f.report_datestamp DESC";
} else {
    $status = isset($_GET['type']) && $_GET['type'] == "closed" ? 1 : 0;
    $this->forum_info['description'] = "This is a list of all reports which are still open";
    if ($status) {
        $this->forum_info['description'] = "This is a list of all reports which are closed";
    }
    $count_query = "SELECT f.report_id
                    FROM ".DB_FORUM_REPORTS." f
                    INNER JOIN ".DB_FORUM_POSTS." p ON f.report_post_id=p.post_id
                    INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
                    INNER JOIN ".DB_FORUMS." tf ON p.forum_id = tf.forum_id
                    ".(multilang_table("FO") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND " : "WHERE ").groupaccess('tf.forum_access')."
                    AND f.report_status='$status'
                    GROUP BY f.report_post_id";

    $query = "SELECT t.thread_id, t.thread_subject, t.thread_author, t.thread_lastuser, t.thread_lastpost, t.thread_lastpostid,
            t.thread_postcount, t.thread_locked, t.thread_sticky, t.thread_poll, t.thread_postcount, t.thread_views,
            t.forum_id 'forum_id', tf.*, f.*, p.post_id, p.post_message, p.post_smileys,
            COUNT(pv.forum_vote_user_id) 'poll_voted',
            IF (n.thread_id > 0, 1 , 0) 'user_tracked'
            FROM ".DB_FORUM_REPORTS." f
            INNER JOIN ".DB_FORUM_POSTS." p ON f.report_post_id=p.post_id
            INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
            INNER JOIN ".DB_FORUMS." tf ON p.forum_id = tf.forum_id
            LEFT JOIN ".DB_FORUM_POLL_VOTERS." pv ON pv.thread_id = t.thread_id AND pv.forum_vote_user_id='".$userdata['user_id']."' AND t.thread_poll=1
            LEFT JOIN ".DB_FORUM_THREAD_NOTIFY." n ON n.thread_id = t.thread_id AND n.notify_user = '".$userdata['user_id']."'
            ".(multilang_table("FO") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND " : "WHERE ").groupaccess('tf.forum_access')."
            AND f.report_status='$status' GROUP BY f.report_post_id ORDER BY f.report_datestamp DESC";
}

$threads = ForumServer::thread(FALSE)->getThreadInfo(0, ["item_id" => "report_id", "count_query" => $count_query, "query" => $query]);
$this->forum_info = array_merge_recursive($this->forum_info, $threads);

if ($rid) {
    if (!empty($this->forum_info['threads']['item'][$rid])) {
        $rdata = $this->forum_info['threads']['item'][$rid];

        $forum_threads = ForumServer::thread(FALSE);
        $pdata = $forum_threads->getThreadPost(0, $rdata['report_post_id']);
        $post = $pdata['post_items'][$rdata['report_post_id']];

        if (!empty($_POST)) {
            if (isset($_POST['close_report'])) {
                dbquery("UPDATE ".DB_FORUM_REPORTS." SET report_status=1 WHERE report_id=:rid", [':rid' => intval($rid)]);
                add_notice("success", "Report has been closed");
            } elseif (isset($_POST['delete_report'])) {
                dbquery("DELETE FROM ".DB_FORUM_REPORTS." WHERE report_id=:rid", [':rid' => intval($rid)]);
                add_notice("success", "Report has been deleted");
            } elseif (isset($_POST['open_report'])) {
                dbquery("UPDATE ".DB_FORUM_REPORTS." SET report_status=0 WHERE report_id=:rid", [':rid' => intval($rid)]);
                add_notice("success", "Report has been reopened");
            } elseif (isset($_POST['close_delete'])) {
                $post_param = [":pid" => $post['post_id']];
                $remove_mood = "DELETE FROM ".DB_FORUM_POST_NOTIFY." WHERE post_id=:pid";
                dbquery($remove_mood, $post_param);
                // Find and delete physical attachment files
                $del_attachment = "SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id=:pid";
                $result = dbquery($del_attachment, $post_param);
                if (dbrows($result)) {
                    while ($adata = dbarray($result)) {
                        $file_path = INFUSIONS."forum/attachments/".$adata['attach_name'];
                        if (file_exists($file_path) && !is_dir($file_path)) {
                            @unlink($file_path);
                        }
                    }
                }
                // Delete attachment records
                $delete_attachments = "DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id=:pid";
                dbquery($delete_attachments, $post_param);
                if (!empty($post['is_first_post'])) {
                    // Just do reset instead of removing.
                    $amend_query = "UPDATE ".DB_FORUM_POSTS." SET post_message=:message, post_author=:new_aid WHERE post_id=:pid";
                    $fpost_param = [':pid' => intval($post['post_id']), ':message' => "", ':new_aid' => '-1'];
                    dbquery($amend_query, $fpost_param);
                } else {
                    // Delete post
                    $delete_forum_posts = "DELETE FROM ".DB_FORUM_POSTS." WHERE post_id=:pid";
                    dbquery($delete_forum_posts, $post_param);
                }
                // Recalculate Authors Post
                dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts-1 WHERE user_id='".intval($post['post_author'])."'");
                // Update Thread
                $thread_count = FALSE;
                if (!dbcount("(post_id)", DB_FORUM_POSTS, "thread_id=:tid", [":tid" => $post['thread_id']])) {
                    dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_id=:tid", [":tid" => $post['thread_id']]);
                } else {
                    // Find last post
                    $find_lastpost = "SELECT post_datestamp, post_author, post_id FROM ".DB_FORUM_POSTS." WHERE thread_id=:tid ORDER BY post_datestamp DESC LIMIT 1";
                    $cpdata = dbarray(dbquery($find_lastpost, [":tid" => $post['thread_id']]));
                    dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=:time, thread_lastpostid=:pid, thread_postcount=:count, thread_lastuser=:uid WHERE thread_id=:tid",
                        [
                            ":time"  => $cpdata['post_datestamp'],
                            ":pid"   => $cpdata['post_id'],
                            ":count" => dbcount("(post_id)", DB_FORUM_POSTS, "thread_id=:tid", [":tid" => $post['thread_id']]),
                            ":uid"   => $cpdata['post_author'],
                            ":tid"   => $post['thread_id']
                        ]);
                    $thread_count = TRUE;
                }
                // Update Forum
                $forum_mods = new ForumMod($forum_moderator);
                $forum_mods->refreshForums();

                dbquery("DELETE FROM ".DB_FORUM_REPORTS." WHERE report_id=:rid", [':rid' => intval($rid)]);
                add_notice('success', $locale['success-DP001']);
                redirect(clean_request("", ["rid"], FALSE));
            } elseif (isset($_POST['close_lock'])) {
                dbquery("UPDATE ".DB_FORUM_POSTS." SET post_locked=1 WHERE post_id=:pid", [":pid" => $post['post_id']]);
                dbquery("UPDATE ".DB_FORUM_REPORTS." SET report_status=1 WHERE report_id=:rid", [':rid' => intval($rid)]);
                add_notice("success", "Post has been locked and user can no longer edit the post.");
            }
            redirect(FUSION_REQUEST);
        }

        $reporter = fusion_get_user($rdata['report_user']);
        $this->forum_info['reporter'] = [
            "user_id"           => $reporter['user_id'],
            "user_name"         => $reporter['user_name'],
            "user_profile_link" => profile_link($reporter['user_id'], $reporter['user_name'], $reporter['user_status']),
            "user_avatar"       => display_avatar($reporter, "25px"),
        ];
        $this->forum_info['report_id'] = $rdata['report_id'];
        $this->forum_info['report_status'] = $report_status[$rdata['report_status']];
        $this->forum_info['report_comment'] = parse_text($rdata['report_comment']);
        $this->forum_info['report_date'] = showdate("forumdate", $rdata['report_datestamp']);
        $this->forum_info['report_updated_date'] = showdate("forumdate", $rdata['report_updated']);
        $this->forum_info['report_time'] = timer($rdata['report_datestamp']);
        $this->forum_info['report_updated_time'] = timer($rdata['report_updated']);

        // Calculate Rowstart
        $rowstart = dbresult(dbquery("SELECT count(1) FROM ".DB_FORUM_POSTS." WHERE post_id < :pid AND thread_id=:tid", [
            ':tid' => $rdata['thread_id'],
            ':pid' => $rdata['report_post_id']]), 0);
        $rowstart = $rowstart > $forum_settings['posts_per_page'] ? "&amp;rowstart=".((ceil($rowstart / $forum_settings['posts_per_page']) - 1) * $forum_settings['posts_per_page']) : "";

        $this->forum_info['post_link'] = FORUM."viewthread.php?thread_id=".$rdata['thread_id'].$rowstart."&amp;pid=".$rdata['report_post_id']."#post_".$rdata['report_post_id'];
        $this->forum_info['thread_link'] = FORUM."viewthread.php?thread_id=".$rdata['thread_id'];

        if ($rdata['report_status']) { // is closed
            $action_button = form_button("open_report", "Open Report", "open_report");
        } else {
            $action_button = form_button("close_report", "Close Report", "close_report").form_button("close_delete", "Delete Post", "close_delete").
                form_button("close_lock", "Lock Post", "close_lock");
        }
        $this->forum_info['report_action'] = openform('report_actions', 'post', FUSION_REQUEST).
            $action_button.
            form_button("delete_report", "Delete Report", "delete_report", ['class' => "m-l-5 btn-danger"]);

        $this->forum_info = $this->forum_info + $pdata;

    } else {
        redirect(clean_request("", ["rid"], FALSE));
    }
}

//showBenchmark(TRUE);
