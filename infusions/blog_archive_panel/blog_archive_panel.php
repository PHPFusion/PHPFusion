<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blog_archive_panel.php
| Author: J.Falk (Falk)
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

if (file_exists(INFUSIONS."blog/locale/".LOCALESET."blog.php")) {
    $locale += fusion_get_locale("", INFUSIONS."blog/locale/".LOCALESET."blog.php");
} else {
    $locale += fusion_get_locale("", INFUSIONS."blog/locale/English/blog.php");
}

openside($locale['blog_1004']);
$blog_result = "SELECT
				blog_id, blog_subject, blog_datestamp
				FROM ".DB_BLOG."
				WHERE blog_draft = 0
				AND ".groupaccess('blog_visibility').(multilang_table("BL") ? " AND blog_language='".LANGUAGE."'" : "")."
				ORDER BY blog_datestamp DESC
				";
$result = dbquery($blog_result);
if (dbrows($result)) {
    echo "<ul class='blog_archive_inner list-style-none' id='blog_archive'>\n";
    $data = [];
    while ($row = dbarray($result)) {
        $year = date('Y', $row['blog_datestamp']);
        $month = showdate('%b', $row['blog_datestamp']);
        $data[$year][$month][] = $row;
    }
    foreach ($data as $blog_year => $blog_months) {
        echo '<li>';
            echo '<a data-toggle="collapse" data-parent="#blog_archive" href="#link-'.$blog_year.'"><b>'.$blog_year.'</b></a>';
            echo '<ul id="link-'.$blog_year.'" class="collapse">';
            foreach ($blog_months as $blog_month => $blog_entries) {
                echo "<li class='m-l-10'><strong>".$blog_month."</strong></li>";
                foreach ($blog_entries as $blog_entry) {
                    echo "<li class='m-l-20'><a href='".INFUSIONS."blog/blog.php?readmore=".$blog_entry['blog_id']."'>".trimlink($blog_entry['blog_subject'],
                                                                                                                                 25)."</a></li>";
                }
            }
            echo '</ul>';
        echo '</li>';
    }
    echo "</ul>\n";
} else {
    echo "<div class='text-center'>".$locale['blog_3000']."</div>\n";
}
closeside();