<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: !autolink_bbcode_include.php
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
namespace PHPFusion\BBCode\Autolink;

defined('IN_FUSION') || exit;

if (!function_exists('PHPFusion\BBCode\Autolink\run')) {
    function bbcode_off($text, $part) {
        if ($part == 1) {
            $text = str_replace("[", " &#91;", $text);
            $text = str_replace("]", "&#93; ", $text);
        } else if ($part == 2) {
            $text = preg_replace('^<a href="(.*?)" target="_blank" title="autolink" rel="nofollow noopener noreferrer">(.*?)</a>^si', '\1', $text);
            $text = str_replace(" &#91;", "&#91;", $text);
            $text = str_replace("&#93; ", "&#93;", $text);
        }

        return $text;
    }

    function callbackPreCode($matches) {
        return '[code]'.bbcode_off($matches[1], 1).'[/code]';
    }

    function callbackPostCode($matches) {
        return '[code]'.bbcode_off($matches[1], 2).'[/code]';
    }

    function callbackPreGeshi($matches) {
        return '[geshi='.$matches[1].']'.bbcode_off($matches[2], '1').'[/geshi]';
    }

    function callbackPostGeshi($matches) {
        return '[geshi='.$matches[1].']'.bbcode_off($matches[2], 2).'[/geshi]';
    }

    function callbackPrePHP($matches) {
        return '[php]'.bbcode_off($matches[1], 1).'[/php]';
    }

    function callbackPostPHP($matches) {
        return '[php]'.bbcode_off($matches[1], 2).'[/php]';
    }

    function callbackURLWithProtocol($matches) {
        $len = strlen($matches[2]);

        return $matches[1].'<a href="'.$matches[2].'" target="_blank" title="autolink" rel="nofollow noopener noreferrer">'
            .trimlink($matches[2], 20)
            .($len > 30 ? substr($matches[2], $len - 10, $len) : '').'</a>';
    }

    function callbackURLWithoutProtocol($matches) {
        $len = strlen($matches[2]);

        return $matches[1].'<a href="http://'.$matches[2].'" target="_blank" title="autolink" rel="nofollow noopener noreferrer">'
            .trimlink($matches[2], 20)
            .(strlen($matches[1]) > 30 ? substr($matches[2], $len - 10, $len) : '').'</a>';
    }

    function callbackMail($matches) {
        return hide_email($matches[0]);
    }

    function run($text) {
        $containsCode = strpos($text, '[code]') !== FALSE;
        $containsGeshi = strpos($text, '[geshi]') !== FALSE;
        $containsPHP = strpos($text, '[php]') !== FALSE;
        if ($containsCode) {
            $text = preg_replace_callback('#\[code\](.*?)\[/code\]#si', __NAMESPACE__.'\callbackPreCode', $text);
        }
        if ($containsGeshi) {
            $text = preg_replace_callback('#\[geshi=(.*?)\](.*?)\[/geshi\]#si', __NAMESPACE__.'\callbackPreGeshi', $text);
        }
        if ($containsPHP) {
            $text = preg_replace_callback('#\[php\](.*?)\[/php\]#si', __NAMESPACE__.'\callbackPrePHP', $text);
        }
        $text = str_replace(["]", "&gt;", "[", "&lt;"], ["]&nbsp;", "&gt; ", " &nbsp;[", " &lt;"], $text);
        $text = preg_replace_callback('#(^|[\n ])((http|https|ftp|ftps)://[\w\#$%&~/.\-;:=,?@\[\]\(\)+]*)#si',
            __NAMESPACE__.'\callbackURLWithProtocol', $text);
        $text = preg_replace_callback('#(^|\s)((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]\(\)+]*)#si', __NAMESPACE__.'\callbackURLWithoutProtocol', $text);
        $text = preg_replace_callback('#[a-z0-9_.-]+?@[\w\-]+\.([\w\-\.]+\.)*[\w]+#si', __NAMESPACE__.'\callbackMail', $text);
        if ($containsCode) {
            $text = preg_replace_callback('#\[code\](.*?)\[/code\]#si', __NAMESPACE__.'\callbackPostCode', $text);
        }
        if ($containsGeshi) {
            $text = preg_replace_callback('#\[geshi=(.*?)\](.*?)\[/geshi\]#si', __NAMESPACE__.'\callbackPostGeshi', $text);
        }
        if ($containsPHP) {
            $text = preg_replace_callback('#\[php\](.*?)\[/php\]#si', __NAMESPACE__.'\callbackPostPHP', $text);
        }

        return str_replace(["]&nbsp;", "&gt; ", " &nbsp;[", " &lt;"], ["]", "&gt;", "[", "&lt;"], $text);
    }
}

$text = run($text);
