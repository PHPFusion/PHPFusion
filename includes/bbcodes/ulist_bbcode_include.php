<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: ulist_bbcode_include.php
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

$count = preg_match_all("#\[ulist=(disc|circle|square)\](.*?)\[/ulist\]#si", $text, $match, PREG_PATTERN_ORDER);
for ($i = 0; $i < $count; $i++) {
    $listitems = explode("\n", $match[2][$i]);
    $listtext = "<ul style='list-style-type:".$match[1][$i].";'>";
    foreach ($listitems as $item) {
        $item = trim($item);
        if (!empty($item)) {
            $listtext .= "<li>".$item."</li>";
        }
    }
    $listtext .= "</ul>";
    $text = str_replace($match[0][$i], $listtext, $text);
}
unset($listitems);
unset($listtext);
unset($match);

