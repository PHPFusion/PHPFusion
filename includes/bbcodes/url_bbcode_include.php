<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: url_bbcode_include.php
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

if (!function_exists('replace_url')) {
    function replace_url($m) {
        $index_url_bbcode = fusion_get_settings('index_url_bbcode');

        // Get input url if any, if not get the content as a url but check if has a schema, if not add one
        $this_url = (!empty($m['url']) ? (preg_match("#^((f|ht)tp(s)?://)#i",
            $m['url']) ? $m['url'] : "http://".$m['url']) : (preg_match("#^((f|ht)tp(s)?://)#i",
            $m['content']) ? $m['content'] : "http://".$m['content']));

        // Trim only the default url
        $content = (empty($m['url']) ? trimlink($m['content'], 40).(strlen($m['content']) > 40 ? substr($m['content'], strlen($m['content']) - 10,
                strlen($m['content'])) : '') : $m['content']);

        return ($index_url_bbcode ? "" : "<!--noindex-->")."<a href='$this_url' target='_blank' ".($index_url_bbcode ? "" : "rel='nofollow noopener noreferrer' ")."title='".urldecode($this_url)."'>".$content."</a>".($index_url_bbcode ? "" : "<!--/noindex-->");
    }
}

$text = preg_replace_callback('#\[url(=(?P<url>(((f|ht)tp(s)?://)|www)(.*?)))?\](?P<content>.*?)\[/url\]#i', 'replace_url', $text);
