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
    return \PHPFusion\Infusions\Forum\Classes\Forum_Functions::attachmentExists($file);
}

function forum_rank_cache() {
    return \PHPFusion\Infusions\Forum\Classes\Forum_Server::forumRankCache();
}

/*function show_forum_rank($posts, $level, $groups) {
    return \PHPFusion\Infusions\Forum\Classes\Forum_Server::show_forum_rank($posts, $level, $groups);
}*/

function display_image($file) {
    \PHPFusion\Infusions\Forum\Classes\Forum_Functions::displayImage($file);
}

function display_image_attach($file, $width = 50, $height = 50, $rel = "") {
    return \PHPFusion\Infusions\Forum\Classes\Forum_Functions::displayImageAttachments($file, $width, $height, $rel);
}

function set_forum_mods($info) {
    \PHPFusion\Infusions\Forum\Classes\Forum_Moderator::setForumMods($info);
}

function verify_forum($forum_id) {
    return \PHPFusion\Infusions\Forum\Classes\Forum_Server::verify_forum($forum_id);
}

function verify_post($post_id) {
    return \PHPFusion\Infusions\Forum\Classes\Forum_Server::verify_post($post_id);
}

function verify_thread($thread_id) {
    return \PHPFusion\Infusions\Forum\Classes\Forum_Server::verify_thread($thread_id);
}

function get_thread($thread_id) {
    $thread = new \PHPFusion\Infusions\Forum\Classes\Threads\Forum_Threads;
    return $thread->getThreadInfo($thread_id);
}

function get_thread_stats($thread_id) {
    return \PHPFusion\Infusions\Forum\Classes\Threads\Forum_Threads::getStats($thread_id);
}

/**
 * Check if user has seen this thread or not.
 * @param $forum_id
 * @param $thread_lastpost
 * @param $thread_lastuser
 *
 * @return bool
 */
function check_thread_new_status($forum_id, $thread_lastpost, $thread_lastuser) {
    if (iMEMBER) {
        $user_lastvisit = fusion_get_userdata('user_lastvisit');
        $user_id = fusion_get_userdata('user_id');
        $user_threads = fusion_get_userdata('user_threads');
        $forum_match = "\\|".$thread_lastpost."\\|".$forum_id;
        $last_visited = (!empty($user_lastvisit) && isnum($user_lastvisit)) ? $user_lastvisit : TIME;
        if ($thread_lastpost > $last_visited) {
            if ($thread_lastuser !== $user_id || !preg_match("({$forum_match}\\.|{$forum_match}$)", $user_threads)) {
                return TRUE;
            }
        }
    }
    return FALSE;
}

/**
 * Cast Question Votes
 *
 * @param     $info
 * @param int $points
 * @todo: move and improvise the voting system
 * @throws Exception
 */
function set_forum_votes($info, $points = 0) {
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

function display_forum_mods($forum_mods) {
    return \PHPFusion\Infusions\Forum\Classes\Forum_Moderator::displayForumMods($forum_mods);
}

function get_recent_topics($forum_id = 0) {
    return \PHPFusion\Infusions\Forum\Classes\Forum_Server::getRecentTopics($forum_id);
}

function set_forum_icons(array $icons = []) {
    \PHPFusion\Infusions\Forum\Classes\Forum_Server::setForumIcons($icons);
}

function get_forum($forum_id = 0, $forum_branch = 0) {
    return \PHPFusion\Infusions\Forum\Classes\Forum::getForum($forum_id, $forum_branch);
}

function get_forum_icons($type = '') {
    return \PHPFusion\Infusions\Forum\Classes\Forum_Server::getForumIcons($type);
}

function get_forum_template($key = NULL) {
    return \PHPFusion\Infusions\Forum\Classes\Forum_Server::getForumTemplate($key);
}

function get_forum_settings($key = NULL) {
    return \PHPFusion\Infusions\Forum\Classes\Forum_Server::get_forum_settings($key);
}

function parse_attach($message) {
    $re = '/\[attach\](.*?)\[\/attach\]/m';
    preg_match_all($re, $message, $matches, PREG_SET_ORDER, 0);
    if (!empty($matches)) {
        foreach($matches as $match) {
            if (!empty($match[1])) {
                $replace_set[$match[0]] = html_entity_decode('<img class="img-responsive" src="'.FORUM.'fetchimage.php?'.$match[1].'"/>');
            }
        }
    }
    if (!empty($replace_set)) {
        return strtr($message, $replace_set);
    }
    return $message;
}
