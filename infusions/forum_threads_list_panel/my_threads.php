<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: my_threads.php
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

add_to_title($locale['global_200'].$locale['global_041']);
global $lastvisited;

if (!isset($lastvisited) || !isnum($lastvisited)) {
    $lastvisited = time();
}

$rows = dbrows(dbquery("SELECT tt.thread_id FROM ".DB_FORUM_THREADS." tt INNER JOIN ".DB_FORUMS." tf ON tt.forum_id = tf.forum_id
	".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('tf.forum_access')." AND tt.thread_author = '".$userdata['user_id']."' AND tt.thread_hidden='0'"));

if ($rows) {
    if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
        $_GET['rowstart'] = 0;
    }
    $result = dbquery("SELECT tt.forum_id, tt.thread_id, tt.thread_subject, tt.thread_views, tt.thread_lastuser,
		tt.thread_lastpost, tt.thread_postcount, tf.forum_name, tf.forum_access, tu.user_id, tu.user_name,
		tu.user_status
		FROM ".DB_FORUM_THREADS." tt
		INNER JOIN ".DB_FORUMS." tf ON tt.forum_id = tf.forum_id
		INNER JOIN ".DB_USERS." tu ON tt.thread_lastuser = tu.user_id
		".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('tf.forum_access')." AND tt.thread_author = '".$userdata['user_id']."' AND tt.thread_hidden='0'
		ORDER BY tt.thread_lastpost DESC LIMIT ".$_GET['rowstart'].",20");
    $i = 0;

    opentable($locale['global_041']);
        echo '<table class="table table-striped">';
            echo '<thead><tr>';
                echo '<td><strong>'.$locale['global_044'].'</strong></td>';
                echo '<td><strong>'.$locale['global_045'].'</strong></td>';
                echo '<td><strong>'.$locale['global_046'].'</strong></td>';
                echo '<td><strong>'.$locale['global_047'].'</strong></td>';
            echo '</tr></thead>';

            echo '<tbody>';
                while ($data = dbarray($result)) {
                    echo "<tr>\n";
                        echo "<td>";
                        if ($data['thread_lastpost'] > $lastvisited) {
                            $thread_match = $data['thread_id']."\|".$data['thread_lastpost']."\|".$data['forum_id'];
                            echo '<i class="fa fa-folder"></i>';
                        } else {
                            echo '<i class="fa fa-folder"></i>';
                        }
                        echo "</td>\n";
                        echo "<td><a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."' title='".$data['thread_subject']."'>".trimlink($data['thread_subject'], 30)."</a><br />\n".$data['forum_name']."</td>\n";
                        echo "<td>".$data['thread_views']."</td>\n";
                        echo "<td>".($data['thread_postcount'] - 1)."</td>\n";
                        echo "<td>".profile_link($data['thread_lastuser'], $data['user_name'], $data['user_status'])."<br />\n".showdate("forumdate", $data['thread_lastpost'])."</td>\n";
                    echo "</tr>\n";
                    $i++;
                }
            echo '</tbody>';
        echo '</table>';
    closetable();

    if ($rows > 20) {
        echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 20, $rows, 3)."\n</div>\n";
    }
} else {
    opentable($locale['global_041']);
    echo "<div class='text-center'>".$locale['global_053']."</div>\n";
    closetable();
}

require_once THEMES."templates/footer.php";
