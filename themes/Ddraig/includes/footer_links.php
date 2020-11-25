<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: footer_links.php
| Author: JoiNNN
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

?>
    <!-- Latest news -->
    <div class="section flleft">
<?php
echo "<h4>".$locale['latest_news']."</h4><ul>\n";
$result = dbquery("SELECT news_id, news_subject
                FROM ".DB_NEWS."
                WHERE ".groupaccess('news_visibility')."
                AND news_draft='0'
                ORDER BY news_datestamp DESC LIMIT 5");
if (dbrows($result) != 0) {
    while ($data = dbarray($result)) {
        $newsid = $data['news_id'];
        $newstitle = trimlink($data['news_subject'], 25);
        echo "<li><a href='".BASEDIR."news.php?readmore=".$data['news_id']."'  title='".$data['news_subject']."' target='_blank'>".$newstitle."</a></li>\n";
    }
} else {
    echo "<li>".$locale['no_news']."</li>\n";
}
echo "</ul>\n";
echo "</div>\n";

?>
    <!-- Newest Threads -->
    <div class="section flleft">
<?php
echo "<h4>".$locale['global_021']."</h4><ul>\n";
$result = dbquery("
        SELECT tt.forum_id, tt.thread_id, tt.thread_subject, tt.thread_lastpost FROM ".DB_THREADS." tt
        INNER JOIN ".DB_FORUMS." tf ON tt.forum_id=tf.forum_id
        WHERE ".groupaccess('tf.forum_access')." AND tt.thread_hidden='0'
        ORDER BY thread_lastpost DESC LIMIT 5");
if (dbrows($result)) {
    while ($data = dbarray($result)) {
        $itemsubject = trimlink($data['thread_subject'], 25);
        echo "<li><a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."' title='".$data['thread_subject']."'>".$itemsubject."</a></li>\n";
    }
} else {
    echo "<li>".$locale['global_023']."</li>\n";
}
echo "</ul>\n";
echo "</div>\n";

?>
    <!-- Hottest Threads -->
    <div class="section flleft">
<?php
echo "<h4>".$locale['global_022']."</h4><ul>\n";
$timeframe = ($settings['popular_threads_timeframe'] != 0 ? "thread_lastpost >= ".(time() - (int)$settings['popular_threads_timeframe']) : "");
list($min_posts) = dbarraynum(dbquery("SELECT thread_postcount FROM ".DB_THREADS.($timeframe ? " WHERE ".$timeframe : "")." ORDER BY thread_postcount DESC LIMIT 4,1"));
$timeframe = ($timeframe ? " AND tt.".$timeframe : "");
$result = dbquery("
        SELECT tf.forum_id, tt.thread_id, tt.thread_subject, tt.thread_postcount
        FROM ".DB_FORUMS." tf
        INNER JOIN ".DB_THREADS." tt USING(forum_id)
        WHERE ".groupaccess('tf.forum_access')." AND tt.thread_postcount >= '".$min_posts."'".$timeframe." AND tt.thread_hidden='0'
        ORDER BY thread_postcount DESC, thread_lastpost DESC LIMIT 5");
if (dbrows($result) != 0) {
    while ($data = dbarray($result)) {
        $itemsubject = trimlink($data['thread_subject'], 25);
        echo "<li style='position:relative'><a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."' title='".$data['thread_subject']."'>".$itemsubject."</a><span style='position:absolute;right:0' class='side-small'>[".($data['thread_postcount'] - 1)."]</span></li>\n";
    }
} else {
    echo "<li>".$locale['global_023']."</li>\n";
}
echo "</ul>\n";
echo "</div>\n";

?>
    <!-- Latest Weblinks -->
    <div class="section flleft">
<?php
echo "<h4>".$locale['latest_weblinks']."</h4><ul>\n";
$result = dbquery("SELECT * FROM ".DB_WEBLINKS." ORDER BY weblink_datestamp DESC LIMIT 5");
if (dbrows($result) != 0) {
    while ($data = dbarray($result)) {
        $itemsubjectlink = trimlink($data['weblink_name'], 25);
        $itemdescriptionlink = trimlink($data['weblink_description'], 50);
        echo "<li><a href='".$data['weblink_url']."' title='".$itemdescriptionlink."' target='_blank'>".$itemsubjectlink."</a></li>\n";
    }
} else {
    echo "<li>".$locale['no_links']."</li>\n";
}
echo "</ul>\n";
echo "</div>\n";
