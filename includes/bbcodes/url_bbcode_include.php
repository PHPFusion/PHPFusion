<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2009 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: url_bbcode_include.php
| Author: Wooya
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

//Url BBCode with auto triming long links
$text = preg_replace('#\[url\]([\r\n]*)(http://|ftp://|https://|ftps://)([^\s\'\"]*?)([\r\n]*)\[/url\]#sie', "'<a href=\'\\2\\3\' target=\'_blank\' title=\'\\2\\3\'>'.trimlink('\\2\\3', 20).(strlen('\\2\\3')>30?substr('\\2\\3', strlen('\\2\\3')-10, strlen('\\2\\3')):'').'</a>'", $text);
$text = preg_replace('#\[url\]([\r\n]*)([^\s\'\"]*?)([\r\n]*)\[/url\]#sie', "'<a href=\'http://\\2\' target=\'_blank\' title=\'\\2\'>'.trimlink('\\2', 20).(strlen('\\2')>30?substr('\\2', strlen('\\2')-10, strlen('\\2')):'').'</a>'", $text);
$text = preg_replace('#\[url=([\r\n]*)(http://|ftp://|https://|ftps://)([^\s\'\"]*?)\](.*?)([\r\n]*)\[/url\]#si', '<a href=\'\2\3\' target=\'_blank\' title=\'\2\3\'>\4</a>', $text);
$text = preg_replace('#\[url=([\r\n]*)([^\s\'\"]*?)\](.*?)([\r\n]*)\[/url\]#si', '<a href=\'http://\2\' target=\'_blank\' title=\'\2\'>\3</a>', $text);
?>
