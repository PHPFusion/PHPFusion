<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: youtube_bbcode_include.php
| Author: Wooya
| Modifed: NetriX
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
$text = preg_replace('#\[youtube\](http:|https:)?(\/\/www.youtube\.com\/watch\?v=|\/\/youtu\.be\/)?(.*?)\[/youtube\]#si',
                     '<iframe width="560" height="315" src="https://www.youtube.com/embed/\3" style="border:0" allowfullscreen></iframe>', $text);
