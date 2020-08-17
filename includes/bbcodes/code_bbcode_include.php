<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: code_bbcode_include.php
| Author: Core Development Team (coredevs@phpfusion.com)
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
add_to_head('<link rel="stylesheet" href="'.INCLUDES.'bbcodes/code/prism.css" type="text/css"/>');
add_to_footer('<script src="'.INCLUDES.'bbcodes/code/prism.js"></script>');

if (preg_match_all('#\[code(=(.*?))?\](.*?)\[/code\]#si', $text) ||
    preg_match_all('#```(.*?)```#si', $text) ||
    preg_match_all('#`(.*?)`#si', $text) ||
    preg_match_all('#\[php\](.*?)\[/php\]#si', $text) ||
    preg_match_all('#\[geshi=(.*?)\](.*?)\[/geshi\]#si', $text)
) {
    $text = preg_replace_callback(
        "#\[code(=(?P<lang>.*?))?\](?P<code>.*?)\[/code\]#si",
        function ($m) {
            global $pid;
            static $i = 0;
            $locale = fusion_get_locale();
            $tid = get('thread_id', FILTER_VALIDATE_INT);
            $code_save = '';
            if (preg_match("/\/forum\//i", FUSION_REQUEST)) {
                if ($tid) {
                    $result = dbquery("SELECT p.post_id, t.thread_id
                    FROM ".DB_FORUM_POSTS." p
                    INNER JOIN ".DB_FORUM_THREADS." t ON t.thread_id = p.thread_id
                    WHERE p.thread_id=:tid AND p.post_id=:pid AND post_hidden='0'
                ", [
                        'tid' => (int) $tid,
                        ':pid' => (int) $pid
                    ]);
                    $data = dbarray($result);
                    $code_save = '<a class="pull-right m-t-0 btn btn-sm btn-default" href="'.INCLUDES.'bbcodes/code_bbcode_save.php?thread_id='.$data['thread_id'].'&amp;post_id='.$data['post_id'].'&amp;code_id='.$i.'"><i class="fa fa-download"></i> '.$locale['bb_code_save'].'</a>';
                }
            }
            $i++;
            $code = format_code($m['code']);
            $steam = new \PHPFusion\Steam();
            $html = $steam->load('Blocks')->code(
                $locale['bb_code_code'],
                '<pre><code class="language-'.(!empty($m['lang']) ? $m['lang'] : 'php').' line-numbers">{%code%}</code></pre>',
                $code_save
            );
            // strip all template whitespace
            $html = trim(preg_replace('/\s\s+/', '', $html));
            $html = str_replace('{%code%}', $code, $html);

            return (string)$html;

        }, $text);

    /*
     * Adds a rule to ``` (markdown) to translate to <code>
     */
    $mcode_count = substr_count($text, "```"); // obtained
    if ($mcode_count) {
        for ($i = 0; $i < $mcode_count; $i++) {
            $text = preg_replace_callback(
                "#```(.*?)```#si",
                function ($m) use (&$i) {
                    return "<pre><code class='language-php'>".format_code($m['1'])."</code></pre>";
                }, $text);
        }
    }

    $text = preg_replace("#`(.*?)`#si", '<code>\\1</code>', $text);
    $text = preg_replace("#\[php\](.*?)\[/php\]#si", "<pre><code class='language-php'>".format_code('\\1')."</code></pre>", $text);
    $text = preg_replace("#\[geshi=(.*?)\](.*?)\[/geshi\]#si", "<pre><code class='language-php'>".format_code('\\2')."</code></pre>", $text);
}
