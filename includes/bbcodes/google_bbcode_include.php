<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: google_bbcode_include.php
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

$text = preg_replace('#\[google\](.*?)\[/google\]#si', '<img src=\'http://www.google.com/logos/Logo_25wht.gif\' width=\'38\' height=\'18\' alt=\'Google Search\' border=\'0\' style=\'vertical-align:middle;\'> <a href=\'http://www.google.com/search?hl=&amp;lr=&amp;q=\1\' target=\'_blank\'>\1</a>', $text);
?>