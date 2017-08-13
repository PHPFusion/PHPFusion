<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: my_tracked_threads.php
| Author: Robert Gaudyn (Wooya)
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

$locale = fusion_get_locale();
$userdata = fusion_get_userdata();

require_once THEMES."templates/header.php";

if (isset($_GET['delete']) && isnum($_GET['delete']) && dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY,
                                                                "thread_id='".$_GET['delete']."' AND notify_user='".$userdata['user_id']."'")
) {
    $result = dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id=".$_GET['delete']." AND notify_user=".$userdata['user_id']);
    redirect(FUSION_SELF);
}

$_GET['rowstart'] = !isset($_GET['rowstart']) || !isnum($_GET['rowstart']) ? 0 : $_GET['rowstart'];

opentable($locale['global_056']);

$result = dbquery("SELECT tn.thread_id FROM ".DB_FORUM_THREAD_NOTIFY." tn
    INNER JOIN ".DB_FORUM_THREADS." tt ON tn.thread_id = tt.thread_id
    INNER JOIN ".DB_FORUMS." tf ON tt.forum_id = tf.forum_id
    ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND" : "WHERE")." tn.notify_user=".$userdata['user_id']." AND ".groupaccess('forum_access')." AND tt.thread_hidden='0'");
$rows = dbrows($result);

if ($rows) {
    $result = dbquery("
        SELECT tf.forum_access, tn.thread_id, tn.notify_datestamp, tn.notify_user,
        tt.thread_subject, tt.forum_id, tt.thread_lastpost, tt.thread_lastuser, tt.thread_postcount,
        tu.user_id AS user_id1, tu.user_name AS user_name1, tu.user_status AS user_status1,
        tu2.user_id AS user_id2, tu2.user_name AS user_name2, tu2.user_status AS user_status2
        FROM ".DB_FORUM_THREAD_NOTIFY." tn
        INNER JOIN ".DB_FORUM_THREADS." tt ON tn.thread_id = tt.thread_id
        INNER JOIN ".DB_FORUMS." tf ON tt.forum_id = tf.forum_id
        LEFT JOIN ".DB_USERS." tu ON tt.thread_author = tu.user_id
        LEFT JOIN ".DB_USERS." tu2 ON tt.thread_lastuser = tu2.user_id
        INNER JOIN ".DB_FORUM_POSTS." tp ON tt.thread_id = tp.thread_id
        ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND" : "WHERE")." tn.notify_user=".$userdata['user_id']." AND ".groupaccess('forum_access')." AND tt.thread_hidden='0'
        GROUP BY tn.thread_id
        ORDER BY tn.notify_datestamp DESC
        LIMIT ".$_GET['rowstart'].",10
    ");
    echo "<div class='table-responsive'><table class='table table-striped'>\n";
         echo "<thead><tr>\n";
              echo "<td><strong>".$locale['global_044']."</strong></td>\n";
              echo "<td><strong>".$locale['global_050']."</strong></td>\n";
              echo "<td><strong>".$locale['global_047']."</strong></td>\n";
              echo "<td><strong>".$locale['global_046']."</strong></td>\n";
              echo "<td><strong>".$locale['global_057']."</strong></td>\n";
              echo "</tr>\n</thead>";
              echo "<tbody>";
                   while ($data = dbarray($result)) {
                        echo "<tr>\n";
                        echo "<td><a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."'>".$data['thread_subject']."</a></td>\n";
                        echo "<td>".profile_link($data['user_id1'], $data['user_name1'], $data['user_status1'])."</td>\n";
                        echo "<td>".profile_link($data['user_id2'], $data['user_name2'], $data['user_status2'])."<br />".showdate("forumdate", $data['thread_lastpost'])."</td>\n";
                        echo "<td>".($data['thread_postcount'] - 1)."</td>\n";
                        echo "<td><a href='".INFUSIONS."forum_threads_list_panel/my_tracked_threads.php?delete=".$data['thread_id']."' onclick=\"return confirm('".$locale['global_060']."');\">".$locale['global_058']."</a></td>\n";
                        echo "</tr>\n";
                   }
              echo "</tbody>";
    echo "</table>\n</div>";

    echo "<div class='text-center'>".makePageNav($_GET['rowstart'], 10, $rows, 3, FUSION_SELF."?")."</div>\n";
} else {
    echo "<div class='text-center'>".$locale['global_059']."</div>\n";
}
closetable();

require_once THEMES."templates/footer.php";
