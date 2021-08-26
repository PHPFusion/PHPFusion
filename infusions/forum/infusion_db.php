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
}
