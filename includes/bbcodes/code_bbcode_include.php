<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: code_bbcode_include.php
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

if (preg_match_all('#\[code(=(.*?))?\](.*?)\[/code\]#si', $text) ||
    preg_match_all('#````(.*?)````#si', $text) ||
    preg_match_all('#\[php\](.*?)\[/php\]#si', $text) ||
    preg_match_all('#\[geshi=(.*?)\](.*?)\[/geshi\]#si', $text)
) {
    add_to_head('<link rel="stylesheet" href="'.INCLUDES.'bbcodes/code/prism.css" type="text/css"/>');
    add_to_footer('<script src="'.INCLUDES.'bbcodes/code/prism.js"></script>');

    $text = preg_replace_callback(
        "#\[code(=(?P<lang>.*?))?\](?P<code>.*?)\[/code\]#si",
        function ($m) use (&$i) {
            global $pid;

            $data = [];

            add_to_head('<link rel="stylesheet" href="'.INCLUDES.'bbcodes/code/prism.css" type="text/css"/>');
            add_to_footer('<script src="'.INCLUDES.'bbcodes/code/prism.js"></script>');

            if (isset($_GET['thread_id'])) {
                if (preg_match("/\/forum\//i", FUSION_REQUEST)) {
                    $result = dbquery("SELECT p.post_id, t.thread_id
                    FROM ".DB_FORUM_POSTS." p
                    INNER JOIN ".DB_FORUM_THREADS." t ON t.thread_id = p.thread_id
                    WHERE p.thread_id='".intval($_GET['thread_id'])."' AND p.post_id ='".intval($pid)."' AND post_hidden='0'
                ");

                    $data = dbarray($result);
                }
            }

            $locale = fusion_get_locale();
            if (preg_match("/\/forum\//i",
                    FUSION_REQUEST) && isset($_GET['thread_id']) && (isset($data['post_id']) && isnum($data['post_id']))
            ) { // this one rely on global.
                $code_save = '<a class="pull-right m-t-0 btn btn-sm btn-default" href="'.INCLUDES.'bbcodes/code_bbcode_save.php?thread_id='.$_GET['thread_id'].'&amp;post_id='.$data['post_id'].'&amp;code_id='.$i.'"><i class="fa fa-download"></i> '.$locale['bb_code_save'].'</a>&nbsp;&nbsp;';
            } else {
                $code_save = '';
            }
            $i++;

            $html = '<div class="code_bbcode">';
            $html .= '<div class="clearfix m-b-5"><strong>'.$locale['bb_code_code'].'</strong>'.$code_save.'</div>';
            $lang = !empty($m['lang']) ? $m['lang'] : 'php';
            $html .= '<pre><code class="language-'.$lang.'">'.formatcode($m['code']).'</code></pre>';
            $html .= '</div>';

            return $html;
        },
        $text
    );

    /*
     * Adds a rule to ```` (markdown) to translate to <code>
     */
    $mcode_count = substr_count($text, "````"); // obtained
    if ($mcode_count) {
        for ($i = 0; $i < $mcode_count; $i++) {
            $text = preg_replace_callback(
                "#````(.*?)````#si",
                function ($m) use (&$i) {
                    return "<pre><code class='language-php'>".formatcode($m['1'])."</code></pre>";
                }, $text);
        }
    }

    $text = preg_replace("#\[php\](.*?)\[/php\]#si", "<pre><code class='language-php'>".formatcode('\\1')."</code></pre>", $text);
    $text = preg_replace("#\[geshi=(.*?)\](.*?)\[/geshi\]#si", "<pre><code class='language-php'>".formatcode('\\2')."</code></pre>", $text);
}
