<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom9/classes/Footer/LatestArticles.inc
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

class LatestArticles {
    public static function Panel() {
        $locale = fusion_get_locale('', ATOM9_LOCALE);

        ob_start();

        echo '<h3>'.$locale['A9_003'].'</h3>';

        $articles = function_exists('infusion_exists') ? infusion_exists('articles') : db_exists(DB_PREFIX.'articles');
        if ($articles) {
            $result = dbquery("SELECT a.article_id, a.article_subject, u.user_id, u.user_name, u.user_status, u.user_avatar
                FROM ".DB_ARTICLES." AS a
                INNER JOIN ".DB_ARTICLE_CATS." AS ac ON a.article_cat=ac.article_cat_id
                LEFT JOIN ".DB_USERS." u ON u.user_id = a.article_name
                WHERE a.article_draft='0' AND ac.article_cat_status='1' AND ".groupaccess("a.article_visibility")." AND ".groupaccess("ac.article_cat_visibility")."
                ".(multilang_table("AR") ? "AND ".in_group('a.article_language', LANGUAGE)." AND ".in_group('ac.article_cat_language', LANGUAGE) : '')."
                ORDER BY a.article_datestamp DESC
                LIMIT 5
            ");

            if (dbrows($result) > 0) {
                echo '<ul>';
                while ($data = dbarray($result)) {
                    echo '<li><a href="'.INFUSIONS.'articles/articles.php?article_id='.$data['article_id'].'">'.$data['article_subject'].'</a></li>';
                }
                echo '</ul>';
            } else {
                echo $locale['A9_004'];
            }
        } else {
            echo $locale['A9_005'];
        }

        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }
}
