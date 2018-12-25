<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: gg_bbcode_include.php
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

$text = preg_replace('#\[gg\]([0-9]*?)\[/gg\]#si', '<strong>'.$locale['bb_gg'].'</strong> <img src=\'http://status.gadu-gadu.pl/users/status.asp?id=\1\' alt=\'\1\' border=\'0\' style=\'vertical-align:middle\'><a href=\'gg:\1\' target=\'_blank\'>\1</a>', $text);
?>