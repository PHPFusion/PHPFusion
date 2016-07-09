<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: threads.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Forums\Threads;

use PHPFusion\Forums\ForumServer;

class ForumThreads extends ForumServer {

    // here must not have a construct. It is mainly for functions

    /**
     * Get thread structure on specific id.
     * @param int $thread_id
     */
    public static function get_thread($thread_id = 0) {

        $userdata = fusion_get_userdata();

        $userid = !empty($userdata['user_id']) ? (int) $userdata['user_id'] : 0;

        $data = array();

        $result = dbquery("
				SELECT t.*, f.*,
				f2.forum_name 'forum_cat_name', f2.forum_access 'parent_access',
				u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_joined,
				IF (n.thread_id > 0, 1 , 0) as user_tracked,
				count(v.vote_user) 'thread_rated',
				count(p.forum_vote_user_id) 'poll_voted'
				FROM ".DB_FORUM_THREADS." t
				INNER JOIN ".DB_USERS." u on t.thread_author = u.user_id
				INNER JOIN ".DB_FORUMS." f ON t.forum_id=f.forum_id
				LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
				LEFT JOIN ".DB_FORUM_VOTES." v on v.thread_id = t.thread_id AND v.vote_user='".intval($userid)."' AND v.forum_id=f.forum_id AND f.forum_type='4'
				LEFT JOIN ".DB_FORUM_POLL_VOTERS." p on p.thread_id = t.thread_id AND p.forum_vote_user_id='".intval($userid)."' AND t.thread_poll='1'
				LEFT JOIN ".DB_FORUM_THREAD_NOTIFY." n on n.thread_id = t.thread_id and n.notify_user = '".intval($userid)."'
				".(multilang_table("FO") ? "WHERE f.forum_language='".LANGUAGE."' AND" : "WHERE")."
				".groupaccess('f.forum_access')." AND t.thread_id='".intval($thread_id)."' AND t.thread_hidden='0'
				");
        if (dbrows($result) > 0) {
            $data = dbarray($result);
            define_forum_mods($data);
        }

        return (array) $data;
    }




}

