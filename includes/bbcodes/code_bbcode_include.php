<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: code_bbcode_include.php
| Author: Chan & Wooya
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

if (preg_match("/\/forum\//i", FUSION_REQUEST)) {
    global $data;
}
$code_count = substr_count($text, "[code]"); // obtained
if ($code_count) {
    add_to_head("<link rel='stylesheet' href='".INCLUDES."bbcodes/code/prism.css' type='text/css'/>");
    add_to_footer("<script src='".INCLUDES."bbcodes/code/prism.js'></script>");

    for ($i = 0; $i < $code_count; $i++) {

        $text = preg_replace_callback(
            "#\[code\](.*?)\[/code\]#si",
            function ($m) use (&$i) {
                $code_locale = fusion_get_locale('', LOCALE.LOCALESET."bbcodes/code.php");

                global $data;
                if (preg_match("/\/forum\//i",
                               FUSION_REQUEST) && isset($_GET['thread_id']) && (isset($data['post_id']) && isnum($data['post_id']))
                ) { // this one rely on global.
                    $code_save = "<a class='pull-right m-t-0 btn btn-sm btn-default' href='".INCLUDES."bbcodes/code_bbcode_save.php?thread_id=".$_GET['thread_id']."&amp;post_id=".$data['post_id']."&amp;code_id=".$i."'><i class='entypo download'></i> ".$code_locale['bb_code_save']."</a>&nbsp;&nbsp;";
                } else {
                    $code_save = "";
                }
                $i++;

                return "<div class='code_bbcode'><div class='tbl-border tbl2 tbl-code'><strong>".$code_locale['bb_code_code']."</strong>".$code_save."</div><div class='tbl-border tbl1' style='width:100%; white-space:nowrap;overflow:auto;'><pre style='white-space:nowrap'><code class='language-php'>".formatcode($m['1'])."</code></pre></div></div>";
            }, $text);

    }
}

/*
 * Adds a rule to ```` (markdown) to translate to <code>
 */
$mcode_count = substr_count($text, "````"); // obtained
if ($mcode_count) {
    for ($i = 0; $i < $mcode_count; $i++) {
        $text = preg_replace_callback(
            "#````(.*?)````#si",
            function ($m) use (&$i) {
                return "<code>".formatcode($m['1'])."</code>";
            }, $text);

    }
}