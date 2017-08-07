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
require_once file_exists('maincore.php') ? 'maincore.php' : __DIR__."/../../maincore.php";
if (!db_exists(DB_FORUMS)) {
    redirect(BASEDIR."error.php?code=404");
}

if (!iMEMBER) {
    redirect(BASEDIR."index.php");
}

require_once THEMES."templates/header.php";
$locale = fusion_get_locale();
$userdata = fusion_get_userdata();

add_to_title($locale['global_200'].$locale['global_042']);

$result = dbquery("SELECT tp.post_id FROM ".DB_FORUM_POSTS." tp
    INNER JOIN ".DB_FORUM_THREADS." tt ON tp.thread_id = tt.thread_id
    INNER JOIN ".DB_FORUMS." tf ON tp.forum_id = tf.forum_id
    ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('forum_access')." AND post_author='".$userdata['user_id']."' AND post_hidden='0' AND thread_hidden='0'");
$rows = dbrows($result);

opentable($locale['global_042']);
if ($rows) {
    $_GET['rowstart'] = !isset($_GET['rowstart']) || !isnum($_GET['rowstart']) ? 0 : $_GET['rowstart'];
    $result = dbquery("SELECT tp.forum_id, tp.thread_id, tp.post_id, tp.post_author, tp.post_datestamp,
        tf.forum_name, tf.forum_access, tt.thread_subject
        FROM ".DB_FORUM_POSTS." tp
        INNER JOIN ".DB_FORUMS." tf ON tp.forum_id=tf.forum_id
        INNER JOIN ".DB_FORUM_THREADS." tt ON tp.thread_id=tt.thread_id
        ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('tf.forum_access')." AND tp.post_author='".$userdata['user_id']."' AND post_hidden='0' AND thread_hidden='0'
        ORDER BY tp.post_datestamp DESC LIMIT ".$_GET['rowstart'].",20");

        echo "<div class='table-responsive'><table class='table table-striped'>";
            echo "<thead><tr>";
                echo "<td><strong>".$locale["global_048"]."</strong></td>";
                echo "<td><strong>".$locale["global_044"]."</strong></td>";
                echo "<td><strong>".$locale["global_049"]."</strong></td>";
            echo "</tr></thead>";
            echo "<tbody>";
                while ($data = dbarray($result)) {
                    echo "<tr>\n";
                        echo "<td>".trimlink($data['forum_name'], 30)."</td>\n";
                        echo "<td><a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['post_id']."#post_".$data['post_id']."' title='".$data['thread_subject']."'>".trimlink($data['thread_subject'], 40)."</a></td>\n";
                        echo "<td>".showdate("forumdate", $data['post_datestamp'])."</td>\n";
                    echo "</tr>\n";
                }
            echo "</tbody>";
        echo "</table></div>";

    if ($rows > 20) {
        echo "<div class='text-center'>".makepagenav($_GET['rowstart'], 20, $rows, 3, FUSION_SELF."?")."</div>\n";
    }
} else {
    echo "<div class='text-center'>".$locale['global_054']."</div>\n";
}
closetable();

require_once THEMES."templates/footer.php";
