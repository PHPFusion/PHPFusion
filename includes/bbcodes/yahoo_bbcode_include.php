<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: yahoo_bbcode_include.php
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

$text = preg_replace('#\[yahoo\](.*?)\[/yahoo\]#si', '<strong>'.$locale['bb_yahoo'].':</strong> <a href=\'http://search.yahoo.com/search?ei=UTF-8&amp;fr=sfp&amp;p=\1\' target=\'_blank\'>\1</a>', $text);
?>