<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: my_posts.php
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
$inf_settings = get_settings('forum');
$locale = fusion_get_locale();
$userdata = fusion_get_userdata();

add_to_title($locale['global_200'].$locale['global_042']);

$result = dbquery("SELECT tp.post_id FROM ".DB_FORUM_POSTS." tp
    INNER JOIN ".DB_FORUM_THREADS." tt ON tp.thread_id = tt.thread_id
    INNER JOIN ".DB_FORUMS." tf ON tp.forum_id = tf.forum_id
    ".(multilang_table("FO") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND" : "WHERE")." ".groupaccess('forum_access')." AND post_author='".$userdata['user_id']."' AND post_hidden='0' AND thread_hidden='0'");
$rows = dbrows($result);

opentable($locale['global_042']);
if ($rows) {
    $_GET['rowstart'] = !isset($_GET['rowstart']) || !isnum($_GET['rowstart']) ? 0 : $_GET['rowstart'];
    $result = dbquery("SELECT tp.forum_id, tp.thread_id, tp.post_id, tp.post_author, tp.post_datestamp,
        tf.forum_name, tf.forum_access, tt.thread_subject, tt.thread_postcount
        FROM ".DB_FORUM_POSTS." tp
        INNER JOIN ".DB_FORUMS." tf ON tp.forum_id=tf.forum_id
        INNER JOIN ".DB_FORUM_THREADS." tt ON tp.thread_id=tt.thread_id
        ".(multilang_table("FO") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND" : "WHERE")." ".groupaccess('tf.forum_access')." AND tp.post_author='".$userdata['user_id']."' AND post_hidden='0' AND thread_hidden='0'
        ORDER BY tp.post_datestamp DESC LIMIT ".$_GET['rowstart'].",20");

        echo "<div class='table-responsive'><table class='table table-striped'>";
            echo "<thead><tr>";
                echo "<th><strong>".$locale["global_048"]."</strong></th>";
                echo "<th><strong>".$locale["global_044"]."</strong></th>";
                echo "<th><strong>".$locale["global_049"]."</strong></th>";
            echo "</tr></thead>";
            echo "<tbody>";
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
                        echo "<td>".trimlink($data['forum_name'], 30)."</td>\n";
                        echo "<td><a href='".FORUM."viewthread.php?thread_id=".$data['thread_id'].$thread_rowstart."&amp;pid=".$data['post_id']."#post_".$data['post_id']."' title='".$data['thread_subject']."'>".trimlink($data['thread_subject'], 40)."</a></td>\n";
                        echo "<td>".showdate("forumdate", $data['post_datestamp'])."</td>\n";
                    echo "</tr>\n";
                }
            echo "</tbody>";
        echo "</table></div>";

    if ($rows > 20) {
        echo "<div class='text-center'>".makepagenav($_GET['rowstart'], 20, $rows, 3, INFUSIONS."forum_threads_list_panel/my_posts.php?")."</div>\n";
    }
} else {
    echo "<div class='text-center'>".$locale['global_054']."</div>\n";
}
closetable();

require_once THEMES.'templates/footer.php';
