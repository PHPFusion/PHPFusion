<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/sections/participated.php
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

// Count Max
$result = dbquery("SELECT tp.post_id FROM ".DB_FORUM_POSTS." tp
	INNER JOIN ".DB_FORUM_THREADS." tt ON tp.thread_id = tt.thread_id
	INNER JOIN ".DB_FORUMS." tf ON tp.forum_id = tf.forum_id
	".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('forum_access')." AND post_author='".$userdata['user_id']."' AND post_hidden='0' AND thread_hidden='0'
	GROUP BY tt.thread_id
	");
$this->forum_info['post_rows'] = dbrows($result);
if (dbrows($result) > 0) {

    if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
        $_GET['rowstart'] = 0;
    }

    require_once INCLUDES."mimetypes_include.php";
    $result = dbquery("
		SELECT tp.forum_id, tp.thread_id, tp.post_id, tp.post_author, tp.post_message, tp.post_datestamp,
		t.*, tf.*,
		tu1.user_name AS author_name, tu1.user_status AS author_status, tu1.user_avatar as author_avatar,
		tu2.user_name AS last_user_name, tu2.user_status AS last_user_status, tu2.user_avatar AS last_user_avatar,
		p.forum_poll_title,
		count(v.post_id) AS vote_count,
		a1.attach_name, a1.attach_id,
		a2.attach_name, a2.attach_id,
		count(a1.attach_mime) 'attach_image',
		count(a2.attach_mime) 'attach_files'
		FROM ".DB_FORUM_POSTS." tp
		INNER JOIN ".DB_FORUMS." tf ON tp.forum_id=tf.forum_id
		INNER JOIN ".DB_FORUM_THREADS." t ON tp.thread_id=t.thread_id
		INNER JOIN ".DB_USERS." tu1 ON t.thread_author = tu1.user_id
		LEFT JOIN ".DB_USERS." tu2 ON t.thread_lastuser = tu2.user_id #issue 323
		LEFT JOIN ".DB_FORUM_POLLS." p ON p.thread_id = t.thread_id
		LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = t.thread_id AND tp.post_id = v.post_id
		LEFT JOIN ".DB_FORUM_ATTACHMENTS." a1 on a1.thread_id = t.thread_id AND a1.attach_mime IN ('".implode(",", img_mimeTypes())."')
		LEFT JOIN ".DB_FORUM_ATTACHMENTS." a2 on a2.thread_id = t.thread_id AND a2.attach_mime NOT IN ('".implode(",", img_mimeTypes())."')
		".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('tf.forum_access')." AND tp.post_author='".$userdata['user_id']."'
		AND post_hidden='0' AND thread_hidden='0'
		GROUP BY t.thread_id
		ORDER BY tp.post_datestamp DESC LIMIT ".$_GET['rowstart'].",".$forum_settings['posts_per_page']."
		");
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
        );
        $this->forum_info['item'][$threads['post_id']] = $threads;
    }
}