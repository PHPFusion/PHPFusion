<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: movie_bbcode_include.php
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

$text = preg_replace('#\[movie=youtube\](http:|https:)?(\/\/www.youtube\.com\/watch\?v=|\/\/youtu\.be\/)?(.*?)\[/movie\]#si', '<strong>'.$locale['bb_movie_youtube'].'</strong><br /><iframe width="425" height="350" src="http://www.youtube.com/v/\3" frameborder="0"></iframe>', $text);

$text = preg_replace('#\[movie=google\](.*?)\[/movie\]#si', '<strong>'.$locale['bb_movie_googlevideo'].':</strong><br /><object width="425" height="350"><param name="movie" value="http://video.google.com/googleplayer.swf?docId=\1"></param><param name="wmode" value="transparent"></param><embed src="http://video.google.com/googleplayer.swf?docId=\1" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>', $text);
?>