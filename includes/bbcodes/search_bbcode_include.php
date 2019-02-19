<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_bbcode_include.php
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
defined('IN_FUSION') || exit;

include LOCALE.LOCALESET."bbcodes/search.php";

if (!function_exists("search_on")) {
    function search_on($where) {
        if ($where == "all") {
            return fusion_get_locale('407', LOCALE.LOCALESET."search.php");
        } else {
            $name = '';
            $locale = fusion_get_locale('', LOCALE.LOCALESET."/search/".$where.".php");
            foreach ($locale as $key => $value) {
                if (preg_match("/400/", $key)) {
                    $name = $key;
                }
            }

            return $locale[$name];
        }
    }
}

$text = preg_replace(
    '#\[search=(.*?)\](.*?)([\r\n]*)\[/search\]#si',
    '<strong>'.$locale['bb_search_prefix'].' <a href="'.BASEDIR.'search.php?stext='.preg_replace('/<[^<>]+>/i', '', '\\2\\3').'&amp;method=AND&amp;stype=\\1&forum_id=0&datelimit=0&fields=2&sort=datestamp&order=0&chars=50" title="'.preg_replace('/<[^<>]+>/i', '', '\\2\\3').'">\\2\\3</a> '.$locale['bb_search_suffix'].' '.search_on('\\1').'</strong>',
    $text
);
