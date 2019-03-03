<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/forum_include.php
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
defined('IN_FUSION') || exit;

function attach_exists($file) {
    return \PHPFusion\Forums\Functions::attach_exists($file);
}

function forum_rank_cache() {
    return \PHPFusion\Forums\ForumServer::forum_rank_cache();
}

function show_forum_rank($posts, $level, $groups) {
    return PHPFusion\Forums\ForumServer::show_forum_rank($posts, $level, $groups);
}

function display_image($file) {
    PHPFusion\Forums\Functions::display_image($file);
}

function display_image_attach($file, $width = 50, $height = 50, $rel = "") {
    return PHPFusion\Forums\Functions::display_image_attach($file, $width, $height, $rel);
}

function define_forum_mods($info) {
    PHPFusion\Forums\Moderator::define_forum_mods($info);
}

function verify_forum($forum_id) {
    return PHPFusion\Forums\ForumServer::verify_forum($forum_id);
}

function verify_post($post_id) {
    return PHPFusion\Forums\ForumServer::verify_post($post_id);
}

function verify_thread($thread_id) {
    return PHPFusion\Forums\ForumServer::verify_thread($thread_id);
}

function get_thread($thread_id) {
    return \PHPFusion\Forums\Threads\ForumThreads::get_thread($thread_id);
}

/**
 * Cast Question Votes
 *
 * @param     $info
 * @param int $points
 *
 * @todo: move and improvise the voting system
 */

function set_forumVotes($info, $points = 0) {
    $userdata = fusion_get_userdata();;
    // @todo: extend on user's rank threshold before can vote. - Reputation threshold- Roadmap 9.1
    // @todo: allow multiple votes / drop $res - Roadmap 9.1
    if (checkgroup($info['forum_vote']) && dbcount("('thread_id')", DB_FORUM_THREADS, "thread_locked='0'")) {
        $data = [
            'forum_id'       => $_GET['forum_id'],
            'thread_id'      => $_GET['thread_id'],
            'post_id'        => $_GET['post_id'],
            'vote_points'    => $points,
            'vote_user'      => $userdata['user_id'],
            'vote_datestamp' => time(),
        ];
        $hasVoted = dbcount("('vote_user')", DB_FORUM_VOTES,
            "vote_user='".intval($userdata['user_id'])."' AND thread_id='".intval($_GET['thread_id'])."'");
        if (!$hasVoted) {
            $isSelfPost = dbcount("('post_id')", DB_FORUM_POSTS,
                "post_id='".intval($_GET['post_id'])."' AND post_user='".intval($userdata['user_id'])."");
            if (!$isSelfPost) {
                $result = dbquery_insert(DB_FORUM_VOTES, $data, 'save', ['noredirect' => 1, 'no_unique' => 1]);
                if ($result && $info['forum_answer_threshold'] > 0) {
                    $vote_result = dbquery("SELECT SUM('vote_points'), thread_id FROM ".DB_FORUM_VOTES." WHERE post_id='".$data['post_id']."'");
                    $v_data = dbarray($vote_result);
                    if ($info['forum_answer_threshold'] != 0 && $v_data['vote_points'] >= $info['forum_answer_threshold']) {
                        dbquery("UPDATE ".DB_FORUM_THREADS." SET 'thread_locked'='1' WHERE thread_id='".$v_data['thread_id']."'");
                    }
                }
                redirect(FORUM."viewthread.php?thread_id=".$_GET['thread_id']."&amp;post_id=".$_GET['post_id']);
            } else {
                redirect(FORUM."viewthread.php?thread_id=".$_GET['thread_id']."&amp;post_id=".$_GET['post_id'].'&error=vote_self');
            }
        } else {
            redirect(FORUM."viewthread.php?thread_id=".$_GET['thread_id']."&amp;post_id=".$_GET['post_id'].'&error=vote');
        }
    }
}

function parse_forumMods($forum_mods) {
    return PHPFusion\Forums\Moderator::parse_forum_mods($forum_mods);
}

function get_recentTopics($forum_id = 0) {
    return PHPFusion\Forums\ForumServer::get_recentTopics($forum_id);
}

function set_forumIcons(array $icons = []) {
    PHPFusion\Forums\ForumServer::set_forumIcons($icons);
}

function get_forum($forum_id = 0, $forum_branch = 0) {
    return PHPFusion\Forums\Forum::get_forum($forum_id, $forum_branch);
}

function get_forumIcons($type = '') {
    return \PHPFusion\Forums\ForumServer::get_ForumIcons($type);
}
