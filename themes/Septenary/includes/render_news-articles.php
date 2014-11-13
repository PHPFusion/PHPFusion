<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2014 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Septenary Theme
| Filename: render_news-articles.php
| Version: 1.00
| Author: PHP-Fusion Mods UK
| Developer & Designer: Craig, Hien
| Site: http://www.phpfusionmods.co.uk
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
/* Removed local function to use Default render_news() Function */

function render_article($subject, $article, $info) {
	global $locale, $settings, $aidlink;
	$category = "<a href='".BASEDIR."articles.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>\n";
	$comment = "<a href='".BASEDIR."articles.php?article_id=".$info['article_id']."#comments'>".$info['article_comments']." comment</a>\n";
	echo "<article>\n";
	echo "<div class='news-action text-right'>";
	echo "<a title='".$locale['global_075']."' href='".BASEDIR."print.php?type=A&amp;item_id=".$info['article_id']."'><i class='entypo print'></i></a>";
	echo iADMIN && checkrights("A") ? "<a href='".ADMIN."articles.php".$aidlink."&amp;action=edit&amp;article_id=".$info['article_id']."' title='".$locale['global_076']."' /><i class='entypo pencil'></i></a>\n" : '';
	echo "</div>\n";
	echo "<div class='news-info'>Posted <span class='news-date'>".showdate("%d %b %Y", $info['article_date'])."</span> in $category and $comment</div>\n";
	echo "<h2 class='news-title'>$subject</h2>";
	echo "<div class='article'>\n";
	echo ($info['article_breaks'] == "y" ? nl2br($article) : $article)."<br />\n";
	echo "</div>\n";
	echo "<div class='news-user-info'>\n";
	echo "<h4>About <a href='".BASEDIR."profile.php?lookup=".$info['user_id']."'>".$info['user_name']."</a>\n</h4>";
	echo "<div class='pull-left m-r-10'>".display_avatar($info, '80px')."</div>\n";
	echo "<strong>".getuserlevel($info['user_level'])."</strong><br/>\n";
	echo "<strong>Joined since: ".showdate('newsdate', $info['user_joined'])."</strong><br/>\n";
	echo "</div>\n";
	echo "</article>";
}
?>