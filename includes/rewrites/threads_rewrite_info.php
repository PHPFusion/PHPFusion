<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news_rewrite_include.php
| Author: Ankur Thakur
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

$permalink_name = $locale['pl_threads_title'];
$permalink_desc = $locale['pl_threads_desc'];
$permalink_tags_desc = array(
	"%thread_id%" => $locale['pl_tags_001'],
	"%thread_title%" => $locale['pl_tags_002'],
	"%post_id%" => $locale['pl_tags_003'],
	"%thread_rowstart%" => $locale['pl_tags_004']
);
?>