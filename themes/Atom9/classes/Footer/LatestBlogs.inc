<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom9/classes/Footer/LatestBlogs.inc
| Author: Frederick MC Chan (Chan)
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Atom9Theme\Footer;

class LatestBlogs {
    public static function Panel() {
        $locale = fusion_get_locale('', ATOM9_LOCALE);

        ob_start();

        echo '<h3>'.$locale['A9_009'].'</h3>';

        $blogs = function_exists('infusion_exists') ? infusion_exists('blog') : db_exists(DB_PREFIX.'blog');
        if ($blogs) {
            $result = dbquery("SELECT blog_id, blog_subject
                FROM ".DB_BLOG."
                ".(multilang_table("BL") ? "WHERE ".in_group('blog_language', LANGUAGE)." AND" : "WHERE")." ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().")
                AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'
                ORDER BY blog_start DESC
                LIMIT 5
            ");

            if (dbrows($result) > 0) {
                echo '<ul>';
                while ($data = dbarray($result)) {
                    echo '<li><a href="'.INFUSIONS.'blog/blog.php?readmore='.$data['blog_id'].'">'.$data['blog_subject'].'</a></li>';
                }
                echo '</ul>';
            } else {
                echo $locale['A9_010'];
            }
        } else {
            echo $locale['A9_011'];
        }

        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }
}
