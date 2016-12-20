<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/sections/tracked.php
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
if (!iMEMBER) {
    redirect(FORUM.'index.php');
}

if (isset($_GET['delete']) && isnum($_GET['delete']) && dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY,
                                                                "thread_id='".$_GET['delete']."' AND notify_user='".$userdata['user_id']."'")
) {
    $result = dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id=".$_GET['delete']." AND notify_user=".$userdata['user_id']);
    redirect(FUSION_SELF);
}

// xss injection
$result = dbquery("SELECT tn.thread_id FROM ".DB_FORUM_THREAD_NOTIFY." tn
            INNER JOIN ".DB_FORUM_THREADS." tt ON tn.thread_id = tt.thread_id
            INNER JOIN ".DB_FORUMS." tf ON tt.forum_id = tf.forum_id
            WHERE tn.notify_user=".$userdata['user_id']." AND ".groupaccess('forum_access')." AND tt.thread_hidden='0'");

$rows = dbrows($result);

if (!isset($_GET['rowstart']) or !isnum($_GET['rowstart']) or $_GET['rowstart'] > $rows) {
    $_GET['rowstart'] = 0;
}

$info['post_rows'] = $rows;

if ($rows) {
    require_once INCLUDES."mimetypes_include.php";

    $info['page_nav'] = ($rows > 10) ? makepagenav($_GET['rowstart'], 16, $rows, 3, FUSION_REQUEST, "rowstart") : "";

    $result = dbquery("
                SELECT tf.forum_id, tf.forum_name, tf.forum_access, tf.forum_type, tf.forum_mods,
                tn.thread_id, tn.notify_datestamp, tn.notify_user,
                ttc.forum_id AS forum_cat_id, ttc.forum_name AS forum_cat_name,
                tp.post_datestamp, tp.post_message,
                tt.thread_subject, tt.forum_id, tt.thread_lastpost, tt.thread_lastpostid, tt.thread_lastuser, tt.thread_postcount, tt.thread_views, tt.thread_locked,
                tt.thread_author, tt.thread_poll, tt.thread_sticky,
                uc.user_id AS s_user_id, uc.user_name AS author_name, uc.user_status AS author_status, uc.user_avatar AS author_avatar,
                u.user_id, u.user_name as last_user_name, u.user_status as last_user_status, u.user_avatar as last_user_avatar,
                count(v.post_id) AS vote_count,
                count(a1.attach_mime) 'attach_image',
				count(a2.attach_mime) 'attach_files'
                FROM ".DB_FORUM_THREAD_NOTIFY." tn
                INNER JOIN ".DB_FORUM_THREADS." tt ON tn.thread_id = tt.thread_id
                INNER JOIN ".DB_FORUMS." tf ON tt.forum_id = tf.forum_id
                LEFT JOIN ".DB_FORUMS." ttc ON ttc.forum_id = tf.forum_cat
                LEFT JOIN ".DB_USERS." uc ON tt.thread_author = uc.user_id
                LEFT JOIN ".DB_USERS." u ON tt.thread_lastuser = u.user_id
                LEFT JOIN ".DB_FORUM_POSTS." tp ON tt.thread_id = tp.thread_id
                LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = tt.thread_id AND tp.post_id = v.post_id
                LEFT JOIN ".DB_FORUM_ATTACHMENTS." a1 on a1.thread_id = tt.thread_id AND a1.attach_mime IN ('".implode(",", img_mimeTypes())."')
				LEFT JOIN ".DB_FORUM_ATTACHMENTS." a2 on a2.thread_id = tt.thread_id AND a2.attach_mime NOT IN ('".implode(",", img_mimeTypes())."')
                WHERE tn.notify_user=".$userdata['user_id']." AND ".groupaccess('forum_access')." AND tt.thread_hidden='0'
                GROUP BY tn.thread_id
                ORDER BY tn.notify_datestamp DESC
                LIMIT ".$_GET['rowstart'].",16
            ");
    $i = 0;
    while ($threads = dbarray($result)) {
        // opt for moderators.
        $this->forum_info['moderators'] = \PHPFusion\Forums\Moderator::parse_forum_mods($threads['forum_mods']);

        $icon = "";
        $match_regex = $threads['thread_id']."\|".$threads['thread_lastpost']."\|".$threads['forum_id'];
        if ($threads['thread_lastpost'] > $this->forum_info['lastvisited']) {
            if (iMEMBER && ($threads['thread_lastuser'] == $userdata['user_id'] || preg_match("(^\.{$match_regex}$|\.{$match_regex}\.|\.{$match_regex}$)",
                                                                                              $userdata['user_threads']))
            ) {
                $icon = "<i class='".get_forumIcons('thread')."' title='".$locale['forum_0261']."'></i>";
            } else {
                $icon = "<i class='".get_forumIcons('new')."' title='".$locale['forum_0260']."'></i>";
            }
        }
        $author = array(
            'user_id' => $threads['thread_author'],
            'user_name' => $threads['author_name'],
            'user_status' => $threads['author_status'],
            'user_avatar' => $threads['author_avatar']
        );
        $lastuser = array(
            'user_id' => $threads['thread_lastuser'],
            'user_name' => $threads['last_user_name'],
            'user_status' => $threads['last_user_status'],
            'user_avatar' => $threads['last_user_avatar']
        );
        $threads += array(
            "thread_link" => array(
                "link" => INFUSIONS."forum/viewthread.php?thread_id=".$threads['thread_id'],
                "title" => $threads['thread_subject']
            ),
            "forum_type" => $threads['forum_type'],
            "thread_pages" => makepagenav(0, $forum_settings['posts_per_page'], $threads['thread_postcount'], 3,
                                          FORUM."viewthread.php?thread_id=".$threads['thread_id']."&amp;"),
            "thread_icons" => array(
                'lock' => $threads['thread_locked'] ? "<i class='".get_forumIcons('lock')."' title='".$locale['forum_0263']."'></i>" : '',
                'sticky' => $threads['thread_sticky'] ? "<i class='".get_forumIcons('sticky')."' title='".$locale['forum_0103']."'></i>" : '',
                'poll' => $threads['thread_poll'] ? "<i class='".get_forumIcons('poll')."' title='".$locale['forum_0314']."'></i>" : '',
                'hot' => $threads['thread_postcount'] >= 20 ? "<i class='".get_forumIcons('hot')."' title='".$locale['forum_0311']."'></i>" : '',
                'reads' => $threads['thread_views'] >= 20 ? "<i class='".get_forumIcons('reads')."' title='".$locale['forum_0311']."'></i>" : '',
                'image' => $threads['attach_image'] > 0 ? "<i class='".get_forumIcons('image')."' title='".$locale['forum_0313']."'></i>" : '',
                'file' => $threads['attach_files'] > 0 ? "<i class='".get_forumIcons('file')."' title='".$locale['forum_0312']."'></i>" : '',
                'icon' => $icon,
            ),
            "thread_starter" => $locale['forum_0006'].timer($threads['post_datestamp'])." ".$locale['by']." ".profile_link($author['user_id'],
                                                                                                                           $author['user_name'],
                                                                                                                           $author['user_status'])."</span>",
            "thread_author" => $author,
            "thread_last" => array(
                'user' => $author,
                'avatar' => display_avatar($lastuser, '30px', '', '', ''),
                'profile_link' => profile_link($lastuser['user_id'], $lastuser['user_name'], $lastuser['user_status']),
                'time' => $threads['post_datestamp'],
                'post_message' => parseubb(parsesmileys($threads['post_message'])),
                "formatted" => "<div class='pull-left'>".display_avatar($lastuser, '30px', '', '', '')."</div>
																				<div class='overflow-hide'>".$locale['forum_0373']." <span class='forum_profile_link'>".profile_link($lastuser['user_id'],
                                                                                                                                                                                     $lastuser['user_name'],
                                                                                                                                                                                     $lastuser['user_status'])."</span><br/>
																				".timer($threads['post_datestamp'])."
																				</div>"
            ),
            "track_button" => array('link' => FORUM."index.php?section=tracked&amp;delete=".$threads['thread_id'], 'title' => $locale['global_058'], 'name' => $locale['forum_0201'])
        );
        // push
        $this->forum_info['item'][$threads['thread_id']] = $threads;
    }
}
