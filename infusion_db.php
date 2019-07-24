<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/infusion_db.php
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
if (!defined('FORUM_EXISTS')) {
    if (get_settings('forum')) {
        define('FORUM_EXISTS', true);
    }
}
if (!defined("LASTVISITED")) {
    define('LASTVISITED', Authenticate::setLastVisitCookie());
}

if (!defined("FORUM")) {
    define("FORUM", INFUSIONS."forum/");
}
if (!defined("RANKS")) {
    define("RANKS", FORUM."ranks/");
}

if (!defined("DB_FORUM_ATTACHMENTS")) {
    define("DB_FORUM_ATTACHMENTS", DB_PREFIX."forum_attachments");
}
if (!defined("DB_FORUM_POLL_OPTIONS")) {
    define("DB_FORUM_POLL_OPTIONS", DB_PREFIX."forum_poll_options");
}
if (!defined("DB_FORUM_POLL_VOTERS")) {
    define("DB_FORUM_POLL_VOTERS", DB_PREFIX."forum_poll_voters");
}
if (!defined("DB_FORUM_POLLS")) {
    define("DB_FORUM_POLLS", DB_PREFIX."forum_polls");
}
if (!defined("DB_FORUM_POSTS")) {
    define("DB_FORUM_POSTS", DB_PREFIX."forum_posts");
}
if (!defined("DB_FORUM_RANKS")) {
    define("DB_FORUM_RANKS", DB_PREFIX."forum_ranks");
}
if (!defined("DB_FORUM_THREAD_NOTIFY")) {
    define("DB_FORUM_THREAD_NOTIFY", DB_PREFIX."forum_thread_notify");
}
if (!defined("DB_FORUM_THREADS")) {
    define("DB_FORUM_THREADS", DB_PREFIX."forum_threads");
}
if (!defined("DB_FORUM_VOTES")) {
    define("DB_FORUM_VOTES", DB_PREFIX."forum_votes");
}
if (!defined("DB_FORUM_USER_REP")) {
    define("DB_FORUM_USER_REP", DB_PREFIX."forum_user_reputation");
}
if (!defined("DB_FORUMS")) {
    define("DB_FORUMS", DB_PREFIX."forums");
}

const DB_FORUM_THREAD_LOGS = DB_PREFIX.'forum_thread_logs';


\PHPFusion\Admins::getInstance()->setAdminPageIcons("F", "<i class='admin-ico fa fa-fw fa-comment-o'></i>");
\PHPFusion\Admins::getInstance()->setAdminPageIcons("FR", "<i class='admin-ico fa fa-fw fa-gavel'></i>");
\PHPFusion\Admins::getInstance()->setFolderPermissions('forum', [
    'infusions/forum/attachments/' => TRUE,
    'infusions/forum/images/'      => TRUE
]);

if (!defined("FORUM_LOCALE")) {
    if (file_exists(INFUSIONS."forum/locale/".LOCALESET."forum.php")) {
        define("FORUM_LOCALE", INFUSIONS."forum/locale/".LOCALESET."forum.php");
    } else {
        define("FORUM_LOCALE", INFUSIONS."forum/locale/English/forum.php");
    }
}

if (!defined("FORUM_ADMIN_LOCALE")) {
    if (file_exists(INFUSIONS."forum/locale/".LOCALESET."forum_admin.php")) {
        define("FORUM_ADMIN_LOCALE", INFUSIONS."forum/locale/".LOCALESET."forum_admin.php");
    } else {
        define("FORUM_ADMIN_LOCALE", INFUSIONS."forum/locale/English/forum_admin.php");
    }
}

if (!defined("FORUM_RANKS_LOCALE")) {
    if (file_exists(INFUSIONS."forum/locale/".LOCALESET."forum_ranks.php")) {
        define("FORUM_RANKS_LOCALE", INFUSIONS."forum/locale/".LOCALESET."forum_ranks.php");
    } else {
        define("FORUM_RANKS_LOCALE", INFUSIONS."forum/locale/English/forum_ranks.php");
    }
}

if (!defined("FORUM_TAGS_LOCALE")) {
    if (file_exists(INFUSIONS."forum/locale/".LOCALESET."forum_tags.php")) {
        define("FORUM_TAGS_LOCALE", INFUSIONS."forum/locale/".LOCALESET."forum_tags.php");
    } else {
        define("FORUM_TAGS_LOCALE", INFUSIONS."forum/locale/English/forum_tags.php");
    }
}

if (!defined("SETTINGS_LOCALE")) {
    if (file_exists(LOCALE.LOCALESET."admin/settings.php")) {
        define("SETTINGS_LOCALE", LOCALE.LOCALESET."admin/settings.php");
    } else {
        define("SETTINGS_LOCALE", LOCALE."English/admin/settings.php");
    }
}

if (!defined("DB_FORUM_TAGS")) {
    define("DB_FORUM_TAGS", DB_PREFIX."forum_thread_tags");
}
if (!defined("DB_FORUM_REPORTS")) {
    define("DB_FORUM_REPORTS", DB_PREFIX."forum_reports");
}
if (!defined("DB_FORUM_MOODS")) {
    define("DB_FORUM_MOODS", DB_PREFIX."forum_post_mood");
}

if (!defined("DB_FORUM_POST_NOTIFY")) {
    define("DB_FORUM_POST_NOTIFY", DB_PREFIX."forum_post_notify");
}

if (!defined("FORUM_CLASS")) {
    define("FORUM_CLASS", INFUSIONS."forum/classes/");
}
if (!defined("FORUM_SECTIONS")) {
    define("FORUM_SECTIONS", INFUSIONS."forum/sections/");
}
if (!defined("FORUM_TEMPLATES")) {
    define("FORUM_TEMPLATES", INFUSIONS."forum/templates/");
}
/**
 * New API for user fields
 * Documentation to add a custom user field page in the new User Fields 2.0
 */
if (infusion_exists('forum')) {
    //Now add a link to your sitelinks with the url:  BASEDIR.'edit_profile?ref=forum'
    $userFields = \PHPFusion\UserFields::getInstance();
    $userFields->addOutputPage('forum', 'Forum', FORUM.'profile.php');
}

function forum_activity_title($data) {
    //print_p($data);
    $locale['thread_title'] = '%s created a new thread %s - %s';
    if ($data['action_item_type'] == 'forum') {
        $user = fusion_get_user($data['action_user_id']);
        $profile_link = profile_link($user['user_id'], $user['user_name'], $user['user_status']);
        switch($data['action_type']) {
            case 'thread_new':
                return sprintf($locale['thread_title'], $profile_link, '<strong>'.html_entity_decode($data['action_subject'], ENT_QUOTES).'</strong>', timer($data['action_datestamp']) );
                break;
        }
    }
}

function forum_activity_content($data) {
    if ($data['action_item_type'] == 'forum') {
        switch($data['action_type']) {
            case 'thread_new':
                return parse_textarea($data['action_content']);
                break;
        }
    }
    return '';
}


fusion_add_hook('profile_activity_title', 'forum_activity_title');

fusion_add_hook('profile_activity_content', 'forum_activity_content');