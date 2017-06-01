<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/sections/latest.php
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
require_once INCLUDES."mimetypes_include.php";
$userdata = fusion_get_userdata();
$locale = fusion_get_locale();

$default_max_count = 200; // Hardcoded to fetch only latest 200 posts
$count_sql = '';
$last_bind = [
    ':hidden' => 0,
];
$latest_sql = "SELECT t.*, tf.*
	FROM ".DB_FORUM_THREADS." t
	INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id	
	".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('tf.forum_access')." AND t.thread_hidden=:hidden";
if (!empty($_POST['filter_date'])) {
    $time_sql = " AND t.thread_lastpost < :time";
    $latest_sql .= $count_sql;
    $last_bind[':time'] = (TIME - ($_POST['filter_date'] * 24 * 3600));
}
$thread_count = dbcount("(thread_id)", DB_FORUM_THREADS." t INNER JOIN ".DB_FORUMS." tf ON tf.forum_id=t.forum_id", (multilang_table("FO") ? "tf.forum_language='".LANGUAGE."' AND " : '').groupaccess('tf.forum_access')." AND t.thread_hidden=:hidden $time_sql", $last_bind);
$thread_count = $default_max_count > $thread_count ? $thread_count : $default_max_count;
$last_bind[':rowstart'] = isset($_GET['v_rowstart']) && $_GET['v_rowstart'] <= $thread_count ? $_GET['v_rowstart'] : 0;
$last_bind[':threads_pp'] = $forum_settings['threads_per_page'];
$latest_sql .= " GROUP BY t.thread_id ORDER BY t.thread_lastpost DESC LIMIT :rowstart, :threads_pp";

if ($thread_count) {
    $result = dbquery($latest_sql, $last_bind);
    $this->forum_info['thread_rows'] = dbrows($result);
    $this->forum_info['pagenav'] = '';

    if (dbrows($result)) {
        $opts = array(
            '0'   => $locale['forum_p999'],
            '1'   => $locale['forum_p001'],
            '7'   => $locale['forum_p007'],
            '14'  => $locale['forum_p014'],
            '30'  => $locale['forum_p030'],
            '90'  => $locale['forum_p090'],
            '180' => $locale['forum_p180'],
            '365' => $locale['forum_3015']
        );

        $this->forum_info['threads_time_filter'] = openform('filter_form', 'post', INFUSIONS."forum/index.php?section=latest").
            form_select('filter_date', $locale['forum_0009'], (isset($_POST['filter_date']) && $_POST['filter_date'] ? $_POST['filter_date'] : 0), array(
                'options' => $opts,
                'width'   => '300px',
                'class'   => 'pull-left m-r-10',
                'stacked' => form_button('go', $locale['go'], $locale['go'], array('class' => 'btn-default')),
            )).closeform();

        if ($thread_count > $this->forum_info['thread_rows']) {
            $this->forum_info['pagenav'] = makepagenav($_GET['v_rowstart'], $forum_settings['threads_per_page'], $thread_count, 3, FORUM."index.php?section=latest&amp;", 'v_rowstart');
        }

        while ($threads = dbarray($result)) {

            //$this->forum_info['moderators'] = \PHPFusion\Forums\Moderator::parse_forum_mods($threads['forum_mods']); // this is latest thread, do not require moderator intervention?
            $icon = "";
            $match_regex = $threads['thread_id']."\|".$threads['thread_lastpost']."\|".$threads['forum_id'];
            if ($threads['thread_lastpost'] > $this->forum_info['lastvisited']) {
                if (iMEMBER && ($threads['thread_lastuser'] == $userdata['user_id'] || preg_match("(^\.{$match_regex}$|\.{$match_regex}\.|\.{$match_regex}$)", $userdata['user_threads']))) {
                    $icon = "<i class='".get_forumIcons('thread')."' title='".$locale['forum_0261']."'></i>";
                } else {
                    $icon = "<i class='".get_forumIcons('new')."' title='".$locale['forum_0260']."'></i>";
                }
            }
            $author = array(
                'user_id'     => $threads['thread_author'],
                'user_name'   => fusion_get_user($threads['thread_author'], 'user_name'),
                'user_status' => fusion_get_user($threads['thread_author'], 'user_status'),
                'user_avatar' => fusion_get_user($threads['thread_author'], 'user_avatar')
            );
            $lastuser = array(
                'user_id'     => $threads['thread_lastuser'],
                'user_name'   => fusion_get_user($threads['thread_lastuser'], 'user_name'),
                'user_status' => fusion_get_user($threads['thread_lastuser'], 'user_status'),
                'user_avatar' => fusion_get_user($threads['thread_lastuser'], 'user_avatar'),
            );
            // Adds formatted result
            $threads += array(
                "thread_link"         => array(
                    "link"  => FORUM."viewthread.php?thread_id=".$threads['thread_id'],
                    "title" => $threads['thread_subject']
                ),
                "forum_type"          => $threads['forum_type'],
                "thread_pages"        => makepagenav(0, $forum_settings['posts_per_page'], $threads['thread_postcount'], 3, FORUM."viewthread.php?thread_id=".$threads['thread_id']."&amp;"),
                "thread_icons"        => array(
                    'lock'   => $threads['thread_locked'] ? "<i class='".get_forumIcons('lock')."' title='".$locale['forum_0263']."'></i>" : '',
                    'sticky' => $threads['thread_sticky'] ? "<i class='".get_forumIcons('sticky')."' title='".$locale['forum_0103']."'></i>" : '',
                    'poll'   => $threads['thread_poll'] ? "<i class='".get_forumIcons('poll')."' title='".$locale['forum_0314']."'></i>" : '',
                    'hot'    => $threads['thread_postcount'] >= 20 ? "<i class='".get_forumIcons('hot')."' title='".$locale['forum_0311']."'></i>" : '',
                    'reads'  => $threads['thread_views'] >= 20 ? "<i class='".get_forumIcons('reads')."' title='".$locale['forum_0311']."'></i>" : '',
                    'icon'   => $icon,
                ),
                "thread_starter_text" => $locale['forum_0006'].' '.$locale['by']." ".profile_link($author['user_id'], $author['user_name'], $author['user_status'])."</span>",
                "thread_starter"      => [
                    'user'         => $author,
                    'avatar'       => display_avatar($author, '30px', '', '', ''),
                    'profile_link' => profile_link($author['user_id'], $author['user_name'], $author['user_status']),
                ],
                "thread_last"         => array(
                    'user'         => $lastuser,
                    'avatar'       => display_avatar($lastuser, '30px', '', '', ''),
                    'profile_link' => profile_link($lastuser['user_id'], $lastuser['user_name'], $lastuser['user_status']),
                    'time'         => $threads['thread_lastpost'],
                    "formatted"    => "<div class='pull-left'>".display_avatar($lastuser, '30px', '', '', '')."</div><div class='overflow-hide'>".$locale['forum_0373']." <span class='forum_profile_link'>".profile_link($lastuser['user_id'], $lastuser['user_name'], $lastuser['user_status'])."</span><br/> ".timer($threads['thread_lastpost'])."</div>"
                ),
            );
            $this->forum_info['item'][$threads['thread_id']] = $threads;
        }
    }
}