<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: new_posts.php
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
require_once __DIR__.'/../../maincore.php';
if (!defined('FORUM_EXIST')) {
    redirect(BASEDIR."error.php?code=404");
}

if (!iMEMBER) {
    redirect(BASEDIR."index.php");
}

require_once THEMES.'templates/header.php';

$lastvisited = defined('LASTVISITED') ? LASTVISITED : TIME;
$inf_settings = get_settings('forum');
$locale = fusion_get_locale();

add_to_title($locale['global_200'].$locale['global_043']);

opentable($locale['global_043']);
$result = dbquery("SELECT tp.post_id FROM ".DB_FORUM_POSTS." tp
    LEFT JOIN ".DB_FORUMS." tf ON tp.forum_id = tf.forum_id
    LEFT JOIN ".DB_FORUM_THREADS." tt ON tp.thread_id = tt.thread_id
    ".(multilang_table("FO") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND" : "WHERE")." ".groupaccess('tf.forum_access')." AND tp.post_hidden='0' AND tt.thread_hidden='0' AND (tp.post_datestamp > ".$lastvisited." OR tp.post_edittime > ".$lastvisited.")");
$rows = dbrows($result);
$threads = 0;

if ($rows) {
    $_GET['rowstart'] = !isset($_GET['rowstart']) || !isnum($_GET['rowstart']) ? 0 : $_GET['rowstart'];

    $result = dbquery("SELECT tp.forum_id, tp.thread_id, tp.post_id, tp.post_author, IF(tp.post_datestamp>tp.post_edittime, tp.post_datestamp, tp.post_edittime) AS post_timestamp,
        tf.forum_name, tf.forum_access, tt.thread_subject, tt.thread_postcount, tu.user_id, tu.user_name, tu.user_status
        FROM ".DB_FORUM_POSTS." tp
        LEFT JOIN ".DB_FORUMS." tf ON tp.forum_id = tf.forum_id
        LEFT JOIN ".DB_FORUM_THREADS." tt ON tp.thread_id = tt.thread_id
        LEFT JOIN ".DB_USERS." tu ON tp.post_author = tu.user_id
        ".(multilang_table("FO") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND" : "WHERE")." ".groupaccess('tf.forum_access')." AND tp.post_hidden='0' AND tt.thread_hidden='0' AND (tp.post_datestamp > '".$lastvisited."' OR tp.post_edittime > '".$lastvisited."')
        GROUP BY tp.thread_id
        ORDER BY post_timestamp DESC LIMIT ".$_GET['rowstart'].",20");

    echo "<div class='table-responsive'><table class='table table-striped'>";
        echo "<thead><tr>";
            echo "<th><strong>".$locale["global_048"]."</strong></th>";
            echo "<th><strong>".$locale["global_044"]."</strong></th>";
            echo "<th><strong>".$locale["global_050"]."</strong></th>";
        echo "</tr></thead>";
        echo "<tbody>";
            $threads = dbrows($result);
            while ($data = dbarray($result)) {
                $thread_rowstart = '';
                if (!empty($inf_settings['posts_per_page']) && $data['thread_postcount'] > $inf_settings['posts_per_page']) {
                    $thread_posts = dbquery("SELECT p.post_id, p.forum_id, p.thread_id, p.post_author, p.post_datestamp
                                    FROM ".DB_FORUM_POSTS." p
                                    LEFT JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
                                    WHERE p.forum_id='".$data['forum_id']."' AND p.thread_id='".$data['thread_id']."' AND thread_hidden='0' AND post_hidden='0'
                                    ORDER BY post_datestamp ASC");
                    if (dbrows($thread_posts)) {
                        $counter = 1;
                        while ($thread_post_data = dbarray($thread_posts)) {
                            if ($thread_post_data['post_id'] == $data['post_id']) {
                                $thread_rowstart = $inf_settings['posts_per_page'] * floor(($counter - 1) / $inf_settings['posts_per_page']);
                                $thread_rowstart = "&amp;rowstart=".$thread_rowstart;
                            }
                            $counter++;
                        }
                    }
                }
                echo "<tr>\n";
                echo "<td>".$data['forum_name']."</td>\n";
                echo "<td><a href='".INFUSIONS."forum/viewthread.php?thread_id=".$data['thread_id'].$thread_rowstart."&amp;pid=".$data['post_id']."#post_".$data['post_id']."'>".trimlink($data['thread_subject'], 40)."</a></td>\n";
                echo "<td>".profile_link($data['post_author'], $data['user_name'], $data['user_status'])."<br />\n".showdate("forumdate", $data['post_timestamp'])."</td>\n";
                echo "</tr>\n";
            }
        echo "</tbody>";
    echo "</table></div>";

    echo "<div class='text-center'>".sprintf($locale["global_055"], $rows, $threads)."</div>";
} else {
    echo "<div class='text-center'>".sprintf($locale['global_055'], 0, 0)."</div>\n";
}

closetable();

if ($threads > 20) {
    echo "<div class='text-center'>".makepagenav($_GET['rowstart'], 20, $threads, 3, INFUSIONS."forum_threads_list_panel/new_posts.php?")."</div>\n";
}
require_once THEMES.'templates/footer.php';
