<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: !autolink_bbcode_include.php
| Author: Wooya
| Edited: slawekneo
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

if(!function_exists("bbcode_off")) {
	function bbcode_off($text, $part) {
		if($part == 1) {
			$text = str_replace("[", " &#91;", $text);
			$text = str_replace("]", "&#93; ", $text);
		}
		if($part == 2) {
			$text = preg_replace("^<a href='(.*?)' target='_blank' title='autolink'>(.*?)</a>^si", "\\1", $text);
			$text = str_replace(" &#91;", "&#91;", $text);
			$text = str_replace("&#93; ", "&#93;", $text);
		}
	return $text;
	}
}

if($codde = substr_count($text, "[code]") > 0) $text = preg_replace("#\[code\](.*?)\[/code\]#sie", "'[code]'.bbcode_off('\\1', '1').'[/code]'", $text);
if($geshii = substr_count($text, "[geshi=") > 0) $text = preg_replace("#\[geshi=(.*?)\](.*?)\[/geshi\]#sie", "'[geshi=\\1]'.bbcode_off('\\2', '1').'[/geshi]'", $text);
if($phpp = substr_count($text, "[php]") > 0) $text = preg_replace("#\[code\](.*?)\[/code\]#sie", "'[code]'.bbcode_off('\\1', '1').'[/code]'", $text);

$text = str_replace(array("]","&gt;", "[", "&lt;"), array("]&nbsp;", "&gt; ", " &nbsp;[", " &lt;"), $text);

$text = preg_replace('#(^|[\n ])((http|https|ftp|ftps)://[\w\#$%&~/.\-;:=,?@\[\]\(\)+]*)#sie', "'\\1<a href=\''.trim('\\2').'\' target=\'_blank\' title=\'autolink\'>'.trimlink('\\2', 20).(strlen('\\2')>30?substr('\\2', strlen('\\2')-10, strlen('\\2')):'').' </a>'", $text);
$text = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]\(\)+]*)#sie", "'\\1<a href=\'http://'.trim('\\2').'\' target=\'_blank\' title=\'autolink\'>'.trimlink('\\2', 20).(strlen('\\1')>30?substr('\\2', strlen('\\2')-10, strlen('\\2')):'').' </a>'", $text);
$text = preg_replace("#([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#sie", "hide_email('\\1@\\2')", $text);

if($codde > 0) $text = preg_replace("#\[code\](.*?)\[/code\]#sie", "'[code]'.bbcode_off('\\1', '2').'[/code]'", $text);
if($geshii > 0) $text = preg_replace("#\[geshi=(.*?)\](.*?)\[/geshi\]#sie", "'[geshi=\\1]'.bbcode_off('\\2', '2').'[/geshi]'", $text);
if($phpp > 0) $text = preg_replace("#\[php\](.*?)\[/php\]#sie", "'[php]'.bbcode_off('\\1', '2').'[/php]'", $text);

$text = str_replace(array("]&nbsp;", "&gt; ", " &nbsp;[", " &lt;"), array("]","&gt;", "[", "&lt;"), $text);

?>