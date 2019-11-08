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
defined( 'IN_FUSION' ) || exit;
if ( !defined( 'FORUM_EXISTS' ) ) {
    if ( get_settings( 'forum' ) ) {
        define( 'FORUM_EXISTS', TRUE );
    }
}
if ( !defined( "LASTVISITED" ) ) {
    define( 'LASTVISITED', Authenticate::setLastVisitCookie() );
}

if ( !defined( "FORUM" ) ) {
    define( "FORUM", INFUSIONS."forum/" );
}
if ( !defined( "RANKS" ) ) {
    define( "RANKS", FORUM."ranks/" );
}

if ( !defined( "DB_FORUM_ATTACHMENTS" ) ) {
    define( "DB_FORUM_ATTACHMENTS", DB_PREFIX."forum_attachments" );
}
if ( !defined( "DB_FORUM_POLL_OPTIONS" ) ) {
    define( "DB_FORUM_POLL_OPTIONS", DB_PREFIX."forum_poll_options" );
}
if ( !defined( "DB_FORUM_POLL_VOTERS" ) ) {
    define( "DB_FORUM_POLL_VOTERS", DB_PREFIX."forum_poll_voters" );
}
if ( !defined( "DB_FORUM_POLLS" ) ) {
    define( "DB_FORUM_POLLS", DB_PREFIX."forum_polls" );
}
if ( !defined( "DB_FORUM_POSTS" ) ) {
    define( "DB_FORUM_POSTS", DB_PREFIX."forum_posts" );
}
if ( !defined( "DB_FORUM_RANKS" ) ) {
    define( "DB_FORUM_RANKS", DB_PREFIX."forum_ranks" );
}
if ( !defined( "DB_FORUM_THREAD_NOTIFY" ) ) {
    define( "DB_FORUM_THREAD_NOTIFY", DB_PREFIX."forum_thread_notify" );
}
if ( !defined( "DB_FORUM_THREADS" ) ) {
    define( "DB_FORUM_THREADS", DB_PREFIX."forum_threads" );
}
if ( !defined( "DB_FORUM_VOTES" ) ) {
    define( "DB_FORUM_VOTES", DB_PREFIX."forum_votes" );
}
if ( !defined( "DB_FORUM_USER_REP" ) ) {
    define( "DB_FORUM_USER_REP", DB_PREFIX."forum_user_reputation" );
}
if ( !defined( "DB_FORUMS" ) ) {
    define( "DB_FORUMS", DB_PREFIX."forums" );
}

const DB_FORUM_THREAD_LOGS = DB_PREFIX.'forum_thread_logs';


\PHPFusion\Admins::getInstance()->setAdminPageIcons( "F", "<i class='admin-ico fa fa-fw fa-comment-o'></i>" );
\PHPFusion\Admins::getInstance()->setAdminPageIcons( "FR", "<i class='admin-ico fa fa-fw fa-gavel'></i>" );
\PHPFusion\Admins::getInstance()->setFolderPermissions( 'forum', [
    'infusions/forum/attachments/' => TRUE,
    'infusions/forum/images/'      => TRUE
] );

if ( !defined( "FORUM_LOCALE" ) ) {
    if ( file_exists( INFUSIONS."forum/locale/".LOCALESET."forum.php" ) ) {
        define( "FORUM_LOCALE", INFUSIONS."forum/locale/".LOCALESET."forum.php" );
    } else {
        define( "FORUM_LOCALE", INFUSIONS."forum/locale/English/forum.php" );
    }
}

if ( !defined( "FORUM_ADMIN_LOCALE" ) ) {
    if ( file_exists( INFUSIONS."forum/locale/".LOCALESET."forum_admin.php" ) ) {
        define( "FORUM_ADMIN_LOCALE", INFUSIONS."forum/locale/".LOCALESET."forum_admin.php" );
    } else {
        define( "FORUM_ADMIN_LOCALE", INFUSIONS."forum/locale/English/forum_admin.php" );
    }
}

if ( !defined( "FORUM_RANKS_LOCALE" ) ) {
    if ( file_exists( INFUSIONS."forum/locale/".LOCALESET."forum_ranks.php" ) ) {
        define( "FORUM_RANKS_LOCALE", INFUSIONS."forum/locale/".LOCALESET."forum_ranks.php" );
    } else {
        define( "FORUM_RANKS_LOCALE", INFUSIONS."forum/locale/English/forum_ranks.php" );
    }
}

if ( !defined( "FORUM_TAGS_LOCALE" ) ) {
    if ( file_exists( INFUSIONS."forum/locale/".LOCALESET."forum_tags.php" ) ) {
        define( "FORUM_TAGS_LOCALE", INFUSIONS."forum/locale/".LOCALESET."forum_tags.php" );
    } else {
        define( "FORUM_TAGS_LOCALE", INFUSIONS."forum/locale/English/forum_tags.php" );
    }
}

if ( !defined( "SETTINGS_LOCALE" ) ) {
    if ( file_exists( LOCALE.LOCALESET."admin/settings.php" ) ) {
        define( "SETTINGS_LOCALE", LOCALE.LOCALESET."admin/settings.php" );
    } else {
        define( "SETTINGS_LOCALE", LOCALE."English/admin/settings.php" );
    }
}

if ( !defined( "DB_FORUM_TAGS" ) ) {
    define( "DB_FORUM_TAGS", DB_PREFIX."forum_thread_tags" );
}
if ( !defined( "DB_FORUM_REPORTS" ) ) {
    define( "DB_FORUM_REPORTS", DB_PREFIX."forum_reports" );
}
if ( !defined( "DB_FORUM_MOODS" ) ) {
    define( "DB_FORUM_MOODS", DB_PREFIX."forum_post_mood" );
}

if ( !defined( "DB_FORUM_POST_NOTIFY" ) ) {
    define( "DB_FORUM_POST_NOTIFY", DB_PREFIX."forum_post_notify" );
}

if ( !defined( "FORUM_CLASS" ) ) {
    define( "FORUM_CLASS", INFUSIONS."forum/classes/" );
}
if ( !defined( "FORUM_SECTIONS" ) ) {
    define( "FORUM_SECTIONS", INFUSIONS."forum/sections/" );
}
if ( !defined( "FORUM_TEMPLATES" ) ) {
    define( "FORUM_TEMPLATES", INFUSIONS."forum/templates/" );
}

/**
 * New API for user fields
 * Documentation to add a custom user field page in the new User Fields 2.0
 */
if ( infusion_exists( 'forum' ) ) {
    //Now add a link to your sitelinks with the url:  BASEDIR.'edit_profile?ref=forum'
    $userFields = \PHPFusion\UserFields::getInstance();
    $userFields->addOutputPage( 'forum', 'Forum', FORUM.'profile.php' );
}

/**
 * @param $data
 *
 * @return string
 */
function forum_activity_title( $data ) {
    //print_p($data);
    $locale['thread_title'] = '%s created a new thread %s - %s';
    if ( $data['action_item_type'] == 'forum' ) {
        $user = fusion_get_user( $data['action_user_id'] );
        $profile_link = profile_link( $user['user_id'], $user['user_name'], $user['user_status'] );
        switch ( $data['action_type'] ) {
            case 'thread_new':
                return sprintf( $locale['thread_title'], $profile_link, '<strong>'.html_entity_decode( $data['action_subject'], ENT_QUOTES ).'</strong>', timer( $data['action_datestamp'] ) );
                break;
        }
    }
}

/**
 * @param $data
 *
 * @return string
 */
function forum_activity_content( $data ) {
    if ( $data['action_item_type'] == 'forum' ) {
        switch ( $data['action_type'] ) {
            case 'thread_new':
                return parse_textarea( $data['action_content'] );
                break;
        }
    }
    return '';
}

// when deleting a forum user.
function forum_delete_user( $data ) {
    $user_id = $data['user_id'];
    $param = [ ':uid' => $user_id ];
    // Delete threads made by user
    dbquery( "DELETE FROM ".DB_FORUM_THREADS." WHERE thread_author=:uid", $param );
    // Delete posts made by user
    dbquery( "DELETE FROM ".DB_FORUM_POSTS." WHERE post_author=:uid", $param );
    // Delete user notification track by user
    dbquery( "DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE notify_user=:uid", $param );
    // Delete votes on forum threads by user
    dbquery( "DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE forum_vote_user_id=:uid", $param );
    // Update thread stats
    $threads = dbquery( "SELECT * FROM ".DB_FORUM_THREADS." WHERE thread_lastuser=:uid", $param );
    if ( dbrows( $threads ) ) {
        while ( $thread = dbarray( $threads ) ) {
            
            // Update thread last post author, date and id
            $last_thread_post = dbarray( dbquery( "SELECT post_id, post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE thread_id='".$thread['thread_id']."' ORDER BY post_id DESC LIMIT 0,1" ) );
            dbquery( "UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=:thread_lastpost, thread_lastpostid=:thread_lastpostid, thread_lastuser=:thread_lastuser WHERE thread_id=:thread_id",
                [
                    ':thread_lastpost'   => $last_thread_post['post_datestamp'],
                    ':thread_lastpostid' => $last_thread_post['post_id'],
                    ':thread_lastuser'   => $last_thread_post['post_author'],
                    ':thread_id'         => $thread['thread_id']
                ] );
            
            // Update thread posts count
            $posts_count = dbcount( "(post_id)", DB_FORUM_POSTS, "thread_id=:thread_id", [ ':thread_id' => $thread['thread_id'] ] );
            dbquery( "UPDATE ".DB_FORUM_THREADS." SET thread_postcount=:thread_postcount WHERE thread_id=:thread_id", [ ':thread_postcount' => $posts_count, ':thread_id' => $thread['thread_id'] ] );
            
            // Update forum threads count and posts count
            // forum_postcount, forum_threadcount,
            
            // forum_lastuser, forum_lastpostid , forum_lastpost,
            list( $threadcount, $postcount ) = dbarraynum( dbquery( "SELECT COUNT(thread_id), SUM(thread_postcount) FROM ".DB_FORUM_THREADS." WHERE forum_id=:forum_id AND thread_lastuser=:thread_lastuser AND thread_hidden=:thread_hidden", [ ':forum_id' => $thread['forum_id'], ':thread_lastuser' => $user_id, ':thread_hidden' => '0' ] ) );
            if ( isnum( $threadcount ) && isnum( $postcount ) ) {
                dbquery( "UPDATE ".DB_FORUMS." SET forum_postcount=:forum_postcount, forum_threadcount=:forum_threadcount WHERE forum_id=:forum_id AND forum_lastuser=:forum_lastuser",
                    [
                        ':forum_postcount'   => $postcount,
                        ':forum_threadcount' => $threadcount,
                        ':forum_id'          => $thread['forum_id'],
                        ':forum_lastuser'    => $user_id
                    ] );
            }
            
        }
    }
    // If thread started by user, delete the thread, and all posts within the thread
    $threads = dbquery( "SELECT * FROM ".DB_FORUM_THREADS." WHERE thread_author=:uid", $param );
    if ( dbrows( $threads ) ) {
        while ( $thread = dbarray( $threads ) ) {
            // Delete the posts made by other users in threads started by deleted user
            if ( $thread['thread_postcount'] > 0 ) {
                dbquery( "DELETE FROM ".DB_FORUM_POSTS." WHERE thread_id=:tid", [ ':tid' => $thread['thread_id'] ] );
            }
            // Delete polls in threads and their associated poll options and votes cast by other users in threads started by deleted user
            if ( $thread['thread_poll'] == 1 ) {
                dbquery( "DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id=:tid", [ ':tid' => $thread['thread_id'] ] );
                dbquery( "DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id=:tid", [ ':tid' => $thread['thread_id'] ] );
                dbquery( "DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id=:tid", [ ':tid' => $thread['thread_id'] ] );
            }
            // Update forum count?
        }
    }
    // Update forums
    $forums = dbquery( "SELECT * FROM ".DB_FORUMS." WHERE forum_lastuser=:uid", $param );
    if ( dbrows( $forums ) ) {
        while ( $forum = dbarray( $forums ) ) {
            // Update forum last post
            $last_forum_post = dbarray( dbquery( "SELECT post_id, post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE forum_id='".$forum['forum_id']."' ORDER BY post_id DESC LIMIT 0,1" ) );
            dbquery( "UPDATE ".DB_FORUMS." SET forum_lastpost=:lastpost, forum_lastuser=:lastuser, forum_lastpostid=:lastpostid WHERE forum_id=:fid AND forum_lastuser=:forum_lastuser",
                [
                    ':lastpost'       => (int)$last_forum_post['post_datestamp'],
                    ':lastuser'       => (int)$last_forum_post['post_author'],
                    ':lastpostid'     => (int)$last_forum_post['post_id'],
                    ':fid'            => (int)$forum['forum_id'],
                    ':forum_lastuser' => (int)$user_id
                ] );
        }
    }
    // Recount posts
    $count_posts = dbquery( "SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." GROUP BY post_author" );
    if ( dbrows( $count_posts ) ) {
        while ( $data = dbarray( $count_posts ) ) {
            // Update the posts count for all users
            dbquery( "UPDATE ".DB_USERS." SET user_posts=:user_posts WHERE user_id=:uid", [ ':user_posts' => $data['num_posts'], ':uid' => $data['post_author'] ] );
        }
    }
}

fusion_add_hook( 'profile_activity_title', 'forum_activity_title' );

fusion_add_hook( 'profile_activity_content', 'forum_activity_content' );

fusion_add_hook( 'admin_user_delete', 'forum_delete_user' ); // when admin deletes a user, forum must be updated.

// add hook for increment post on each post count
// a delete hook for - delete post, and delete thread
