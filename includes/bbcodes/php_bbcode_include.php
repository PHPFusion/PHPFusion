<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: php_bbcode_include.php
| Author: Wooya
| Fixed: slawekneo
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

require_once INCLUDES.'bbcodes/phphighlight/PHP_Highlight.php';
unset ($matches);
preg_match_all("#\[php\](.*?)\[/php\]#si", $text, $matches, PREG_PATTERN_ORDER);
for ($i = 0; $i < count($matches[0]); $i++) {
    $input = str_replace('<br>', '', str_replace('<br  />', '', str_replace('<br />', '', stripslashes($matches[1][$i]))));
    $search = array("\\", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;", "&amp;");
    $replace = array("\\\\", "\"", "'", "\\", "\"", "\'", "<", ">", "&");
    $input = str_replace($search, $replace, $input);
    $start_php = !preg_match("/<\?php/i", $input) ? "<?php\n" : "";
    $end_php = !preg_match("/\?>/i", $input) ? "\n?>" : "";
    $h = new PHP_Highlight;
    $h->loadString($start_php.$input.$end_php);
    $parsed = $h->toList(TRUE, TRUE, FALSE);
    if (preg_match("/\/forum\//i", FUSION_REQUEST) && isset($data['post_id'])) {
        $php_save = "<a href='".INCLUDES."bbcodes/php_bbcode_save.php?thread_id=".$_GET['thread_id']."&amp;post_id=".$data['post_id']."&amp;code_id=".$i."' title='".$locale['bb_php_save']."'><i class='fa fa-hdd-o'></i></a>&nbsp;&nbsp;";
    } else {
        $php_save = "";
    }
    $text2 = "<div class='tbl-border tbl2' style='width:400px'>".$php_save."<strong>".$locale['bb_php']."</strong></div><div class='tbl-border tbl1' style='height:auto;white-space:nowrap;overflow:auto;background-color:#ffffff;'>".$parsed."</div>";
    $text = str_replace($matches[0][$i], $text2, $text);
    $text = str_replace("<ol>\n", "<ol>", $text);
    $text = str_replace("</li>\n", "</li>", $text);
    $text = str_replace("</ol>\n", "</ol>", $text);
}

