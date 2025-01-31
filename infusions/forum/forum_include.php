<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: forum_include.php
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

/**
 * Appends increment integer on multiple files on same post
 *
 * @param $file
 *
 * @return string
 */
function attach_exists($file) {
    $dir = INFUSIONS."forum/attachments/";
    $i = 1;
    $file_name = substr($file, 0, strrpos($file, "."));
    $file_ext = strrchr($file, ".");
    while (file_exists($dir.$file)) {
        $file = $file_name."_".$i.$file_ext;
        $i++;
    }

    return $file;
}

function forum_rank_cache() {
    return \PHPFusion\Forums\ForumServer::forumRankCache();
}

function show_forum_rank($posts, $level, $groups) {
    return PHPFusion\Forums\ForumServer::showForumRank($posts, $level, $groups);
}

/**
 * Display an image
 *
 * @param $file
 *
 * @return string
 */
function display_image($file) {
    $size = @getimagesize(INFUSIONS."forum/attachments/".$file);
    if ($size[0] > 300 || $size[1] > 200) {
        if ($size[0] <= $size[1]) {
            $img_w = round(($size[0] * 200) / $size[1]);
            $img_h = 200;
        } else if ($size[0] > $size[1]) {
            $img_w = 300;
            $img_h = round(($size[1] * 300) / $size[0]);
        } else {
            $img_w = 300;
            $img_h = 200;
        }
    } else {
        $img_w = $size[0];
        $img_h = $size[1];
    }
    if ($size[0] != $img_w || $size[1] != $img_h) {
        $res = "<a href='".INFUSIONS."forum/attachments/".$file."'><img src='".INFUSIONS."forum/attachments/".$file."' width='".$img_w."' height='".$img_h."' style='border:0;' alt='".$file."' /></a>";
    } else {
        $res = "<img src='".INFUSIONS."forum/attachments/".$file."' width='".$img_w."' height='".$img_h."' style='border:0;' alt='".$file."' />";
    }

    return $res;
}

/**
 * Display attached image with a certain given width and height.
 *
 * @param        $file
 * @param int    $width
 * @param int    $height
 * @param string $rel
 *
 * @return string
 */
function display_image_attach($file, $width = 50, $height = 50, $rel = "") {
    if (file_exists(INFUSIONS."forum/attachments/".$file)) {
        $size = @getimagesize(INFUSIONS."forum/attachments/".$file);
        if ($size [0] > $height || $size [1] > $width) {
            if ($size [0] < $size [1]) {
                $img_w = round(($size [0] * $width) / $size [1]);
                $img_h = $width;
            } else if ($size [0] > $size [1]) {
                $img_w = $height;
                $img_h = round(($size [1] * $height) / $size [0]);
            } else {
                $img_w = $height;
                $img_h = $width;
            }
        } else {
            $img_w = $size [0];
            $img_h = $size [1];
        }
        $res = "<a target='_blank' href='".INFUSIONS."forum/attachments/".$file."' rel='attach_".$rel."' title='".$file."'><img src='".INFUSIONS."forum/attachments/".$file."' alt='".$file."' style='border:none; width:".$img_w."px; height:".$img_h."px;' /></a>\n";
    } else {
        $res = fusion_get_locale('forum_0188');
    }

    return $res;
}

function define_forum_mods($info) {
    PHPFusion\Forums\Moderator::defineForumMods($info);
}

function verify_forum($forum_id) {
    return PHPFusion\Forums\ForumServer::verifyForum($forum_id);
}

function verify_post($post_id) {
    return PHPFusion\Forums\ForumServer::verifyPost($post_id);
}

function verify_thread($thread_id) {
    return PHPFusion\Forums\ForumServer::verifyThread($thread_id);
}

function get_thread($thread_id) {
    return \PHPFusion\Forums\Threads\ForumThreads::getThread($thread_id);
}

function parse_forum_mods($forum_mods) {
    return PHPFusion\Forums\Moderator::parseForumMods($forum_mods);
}

function get_recent_topics($forum_id = 0) {
    return PHPFusion\Forums\ForumServer::getRecentTopics($forum_id);
}

function set_forum_icons(array $icons = []) {
    PHPFusion\Forums\ForumServer::setForumIcons($icons);
}

function get_forum($forum_id = 0, $forum_branch = 0) {
    return PHPFusion\Forums\Forum::getForum($forum_id, $forum_branch);
}

function get_forum_icons($type = '') {
    return \PHPFusion\Forums\ForumServer::getForumIcons($type);
}

/**
 * @deprecated use get_forum_icons()
 */
function get_forumIcons($type = '') {
    return get_forum_icons($type);
}
