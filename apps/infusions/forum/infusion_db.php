<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: infusion_db.php
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
defined('IN_FUSION') || exit;

use PHPFusion\Admins;

// Locales
define("FORUM_LOCALE", fusion_get_inf_locale_path('forum.php', INFUSIONS."forum/locale/"));
define("FORUM_ADMIN_LOCALE", fusion_get_inf_locale_path('forum_admin.php', INFUSIONS."forum/locale/"));
define("FORUM_RANKS_LOCALE", fusion_get_inf_locale_path('forum_ranks.php', INFUSIONS."forum/locale/"));
define("FORUM_TAGS_LOCALE", fusion_get_inf_locale_path('forum_tags.php', INFUSIONS."forum/locale/"));

// Paths
const FORUM = INFUSIONS."forum/";
const RANKS = FORUM."ranks/";
const FORUM_CLASSES = INFUSIONS."forum/classes/";
const FORUM_SECTIONS = INFUSIONS."forum/sections/";
const FORUM_TEMPLATES = INFUSIONS."forum/templates/";

// Database
const DB_FORUM_ATTACHMENTS = DB_PREFIX."forum_attachments";
const DB_FORUM_POLL_OPTIONS = DB_PREFIX."forum_poll_options";
const DB_FORUM_POLL_VOTERS = DB_PREFIX."forum_poll_voters";
const DB_FORUM_POLLS = DB_PREFIX."forum_polls";
const DB_FORUM_POSTS = DB_PREFIX."forum_posts";
const DB_FORUM_RANKS = DB_PREFIX."forum_ranks";
const DB_FORUM_THREAD_NOTIFY = DB_PREFIX."forum_thread_notify";
const DB_FORUM_THREADS = DB_PREFIX."forum_threads";
const DB_FORUM_VOTES = DB_PREFIX."forum_votes";
const DB_FORUM_USER_REP = DB_PREFIX."forum_user_reputation";
const DB_FORUMS = DB_PREFIX."forums";
const DB_FORUM_TAGS = DB_PREFIX."forum_thread_tags";
const DB_FORUM_MOODS = DB_PREFIX."forum_post_mood";
const DB_POST_NOTIFY = DB_PREFIX."forum_post_notify";

define('LASTVISITED', Authenticate::setLastVisitCookie());

// Admin Settings
Admins::getInstance()->setAdminPageIcons("F", "<i class='admin-ico fa fa-fw fa-comment-o'></i>");
Admins::getInstance()->setAdminPageIcons("FR", "<i class='admin-ico fa fa-fw fa-gavel'></i>");
Admins::getInstance()->setFolderPermissions('forum', [
    'infusions/forum/attachments/' => TRUE,
    'infusions/forum/images/'      => TRUE
]);

Admins::getInstance()->setCustomFolder('F', [
    [
        'path'  => FORUM.'images',
        'URL'   => fusion_get_settings('siteurl').'infusions/forum/images/',
        'alias' => 'forum'
    ]
]);

if (defined('FORUM_EXISTS')) {
    function forum_cron_job24h() {
        dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE notify_datestamp <:notify_datestamp", [':notify_datestamp' => time() - 1209600]);
    }

    /**
     * @uses forum_cron_job24h()
     */
    fusion_add_hook('cron_job24h', 'forum_cron_job24h');

    function forum_cron_job24h_users_data($data) {
        dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_author=:user_id", [':user_id' => $data['user_id']]);
        dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE post_author=:user_id", [':user_id' => $data['user_id']]);
        dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE notify_user=:user_id", [':user_id' => $data['user_id']]);
    }

    /**
     * @uses forum_cron_job24h_users_data()
     */
    fusion_add_hook('cron_job24h_users_data', 'forum_cron_job24h_users_data');


    function fusion_forum_delete_user($user_id) {
        dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_author=:thread_author", [':thread_author' => $user_id]);
        dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE post_author=:post_author", [':post_author' => $user_id]);
        dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE notify_user=:notify_user", [':notify_user' => $user_id]);
        dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE forum_vote_user_id=:forum_vote_user_id", [':forum_vote_user_id' => $user_id]);
        $threads = dbquery("SELECT * FROM ".DB_FORUM_THREADS." WHERE thread_lastuser=:thread_lastuser", [':thread_lastuser' => $user_id]);
        if (dbrows($threads)) {
            while ($thread = dbarray($threads)) {
                // Update thread last post author, date and id
                $last_thread_post = dbarray(dbquery("SELECT post_id, post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE thread_id=:thread_id ORDER BY post_id DESC LIMIT 0,1", [':thread_id' => $thread['thread_id']]));
                dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=:thread_lastpost, thread_lastpostid=:thread_lastpostid, thread_lastuser=:thread_lastuser WHERE thread_id=:thread_id",
                    [
                        ':thread_lastpost'   => $last_thread_post['post_datestamp'],
                        ':thread_lastpostid' => $last_thread_post['post_id'],
                        ':thread_lastuser'   => $last_thread_post['post_author'],
                        ':thread_id'         => $thread['thread_id']
                    ]);
                // Update thread posts count
                $posts_count = dbcount("(post_id)", DB_FORUM_POSTS, "thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_postcount=:thread_postcount WHERE thread_id=:thread_id", [':thread_postcount' => $posts_count, ':thread_id' => $thread['thread_id']]);
                // Update forum threads count and posts count
                [$threadcount, $postcount] = dbarraynum(dbquery("SELECT COUNT(thread_id), SUM(thread_postcount) FROM ".DB_FORUM_THREADS." WHERE forum_id=:forum_id AND thread_lastuser=:thread_lastuser AND thread_hidden=:thread_hidden", [':forum_id' => $thread['forum_id'], ':thread_lastuser' => $user_id, ':thread_hidden' => '0']));
                if (isnum($threadcount) && isnum($postcount)) {
                    dbquery("UPDATE ".DB_FORUMS." SET forum_postcount=:forum_postcount, forum_threadcount=:forum_threadcount WHERE forum_id=:forum_id AND forum_lastuser=:forum_lastuser",
                        [
                            ':forum_postcount'   => $postcount,
                            ':forum_threadcount' => $threadcount,
                            ':forum_id'          => $thread['forum_id'],
                            ':forum_lastuser'    => $user_id
                        ]);
                }
            }
        }
        $forums = dbquery("SELECT * FROM ".DB_FORUMS." WHERE forum_lastuser=:forum_lastuser", [':forum_lastuser' => $user_id]);
        if (dbrows($forums)) {
            while ($forum = dbarray($forums)) {
                // find the user one before the current user's post
                $last_forum_post = dbarray(dbquery("SELECT post_id, post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE forum_id=:forum_id ORDER BY post_id DESC LIMIT 0,1", [':forum_id' => $forum['forum_id']]));
                dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost=:forum_lastpost, forum_lastuser=:forum_lastuser WHERE forum_id=:forum_id AND forum_lastuser=:forum_lastuser",
                    [
                        ':forum_lastpost' => $last_forum_post['post_datestamp'],
                        ':forum_id'       => $forum['forum_id'],
                        ':forum_lastuser' => $user_id
                    ]);
            }
        }
        // Delete all threads that has been started by the user.
        $threads = dbquery("SELECT * FROM ".DB_FORUM_THREADS." WHERE thread_author=:thread_author", [':thread_author' => $user_id]);
        if (dbrows($threads)) {
            while ($thread = dbarray($threads)) {
                // Delete the posts made by other users in threads started by deleted user
                if ($thread['thread_postcount'] > 0) {
                    dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                }
                // Delete polls in threads and their associated poll options and votes cast by other users in threads started by deleted user
                if ($thread['thread_poll'] == 1) {
                    dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                    dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                    dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                }
            }
        }
        $count_posts = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." GROUP BY post_author");
        if (dbrows($count_posts)) {
            while ($data = dbarray($count_posts)) {
                // Update the posts count for all users
                dbquery("UPDATE ".DB_USERS." SET user_posts=:user_posts WHERE user_id=:user_id", [':user_posts' => $data['num_posts'], ':user_id' => $data['post_author']]);
            }
        }

    }

    /*
     * @uses fusion_forum_delete_user()
     */
    fusion_add_hook('fusion_delete_user', 'fusion_forum_delete_user');

}
