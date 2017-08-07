<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_threads_list_panel.php
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

include_once INCLUDES."infusions_include.php";

$inf_settings = get_settings('forum');
$userdata = fusion_get_userdata();
$locale = fusion_get_locale('', FORUM_LOCALE);

if (!$inf_settings) {
    // Forum is not installed
    return;
}

$lastvisited = defined('LASTVISITED') ? LASTVISITED : TIME;

$result = dbquery("SELECT f.forum_id, f.forum_cat, f.forum_name, f.forum_lastpost, f.forum_postcount,
    f.forum_threadcount, f.forum_lastuser, f.forum_access,
    t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject, t.thread_postcount, t.thread_views, t.thread_lastuser, t.thread_poll,
    u.user_id, u.user_name, u.user_status, u.user_avatar
    FROM ".DB_FORUMS." f
    LEFT JOIN ".DB_FORUM_THREADS." t ON f.forum_id = t.forum_id
    LEFT JOIN ".DB_USERS." u ON t.thread_lastuser = u.user_id
    ".(multilang_table("FO") ? "WHERE f.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('f.forum_access')." AND f.forum_type!='1' AND f.forum_type!='3' AND t.thread_hidden='0'
    GROUP BY t.thread_id ORDER BY t.thread_lastpost DESC LIMIT ".$inf_settings['numofthreads']);

if (dbrows($result)) {
    opentable($locale['global_040']);
        echo "<div class='table-responsive'><table class='table table-striped'>";
            echo "<thead><tr>";
                echo "<td class='min'>&nbsp;</td>\n";
                echo "<td><strong>".$locale['global_044']."</strong></td>\n";
                echo "<td><strong>".$locale['global_045']."</strong></td>\n";
                echo "<td><strong>".$locale['global_046']."</strong></td>\n";
                echo "<td><strong>".$locale['global_047']."</strong></td>\n";
            echo "</thead></tr>";
            echo "<tbody>";
                while ($data = dbarray($result)) {
                    echo "<tr>";
                        echo "<td>";
                        if ($data['thread_lastpost'] > $lastvisited) {
                            $thread_match = $data['thread_id']."\|".$data['thread_lastpost']."\|".$data['forum_id'];
                            if (iMEMBER && ($data['thread_lastuser'] == $userdata['user_id'] || preg_match("(^\.{$thread_match}$|\.{$thread_match}\.|\.{$thread_match}$)", $userdata['user_threads']))) {
                                echo "<i class='fa fa-folder fa-2x'></i>";
                            } else {
                                echo "<i class='fa fa-folder fa-2x text-danger'></i>";
                            }
                        } else {
                            echo "<i class='fa fa-folder fa-2x'></i>";
                        }
                        if ($data['thread_poll']) {
                            $thread_poll = "<span class='small' style='font-weight:bold'>[".$locale['global_051']."]</span> ";
                        } else {
                            $thread_poll = "";
                        }
                        echo "</td>\n";
                        echo "<td>".$thread_poll."
                        <a class='strong' href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['thread_lastpostid']."#post_".$data['thread_lastpostid']."' title='".$data['thread_subject']."'>".trimlink($data['thread_subject'], 30)."</a>
                        <br />\n ".$locale['in']." <a href='".FORUM."index.php?viewforum&forum_id=".$data['forum_id']."' title='".$data['forum_name']."'>".trimlink($data['forum_name'], 30)."</a></td>\n";
                        echo "<td>".$data['thread_views']."</td>\n";
                        echo "<td>".($data['thread_postcount'] - 1)."</td>\n";
                        echo "<td>".profile_link($data['thread_lastuser'], $data['user_name'], $data['user_status'])."<br />\n".showdate("forumdate", $data['thread_lastpost'])."</td>\n";
                    echo "</tr>\n";
                }
            echo "</tbody>";
        echo "</table>\n</div>";

        if (iMEMBER) {
            echo "<div class='text-center'>\n";
                echo "<div class='btn-group'>
                <a class='btn btn-default' href='".INFUSIONS."forum_threads_list_panel/my_threads.php'>".$locale['global_041']."</a>\n
                <a class='btn btn-default' href='".INFUSIONS."forum_threads_list_panel/my_posts.php'>".$locale['global_042']."</a>\n
                <a class='btn btn-default' href='".INFUSIONS."forum_threads_list_panel/new_posts.php'>".$locale['global_043']."</a>";
                if ($inf_settings['thread_notify']) {
                    echo "<a class='btn btn-default' href='".INFUSIONS."forum_threads_list_panel/my_tracked_threads.php'>".$locale['global_056']."</a>";
                }
                echo "</div>\n";
            echo "</div>\n";
        }
    closetable();
}
