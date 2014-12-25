<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: home.php
| Author: Chubatyj Vitalij (Rizado)
| Web: http://chubatyj.ru/
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "maincore.php";

require_once THEMES."templates/header.php";

include LOCALE.LOCALESET."homepage.php";

$acclevel = isset($userdata['user_level']) ? $userdata['user_level'] : 0;

$content_installed = false;

if (db_exists(DB_NEWS) && db_exists(DB_NEWS_CATS)) {

	opentable($locale['home_0000']);

	$result = dbquery("SELECT ne.news_id, ne.news_subject, ne.news_news, ne.news_datestamp, us.user_id, us.user_name, us.user_status, nc.news_cat_id, nc.news_cat_name FROM ".DB_NEWS." as ne LEFT JOIN ".DB_NEWS_CATS." as nc ON nc.news_cat_id = ne.news_cat INNER JOIN ".DB_USERS." as us ON ne.news_name = us.user_id WHERE (".time()." > ne.news_start OR ne.news_start = 0) AND (".time()." < ne.news_end OR ne.news_end = 0) AND ne.news_visibility <= ".$acclevel." ORDER BY ne.news_datestamp DESC LIMIT 3");

	if (dbrows($result)) {

		$colwidth = floor(12 / mysql_num_rows($result));

		echo "<div class='row'>\n";

		while ($data = dbarray($result)) {

			$news_cat = $data['news_cat_id'] ? "<a href='".BASEDIR."news_cats.php?cat_id=".$data['news_cat_id']."'>".$data['news_cat_name']."</a>" : $locale['home_0102'];

			echo "<div class='col-xs-".$colwidth." col-sm-".$colwidth." col-md-".$colwidth." col-lg-".$colwidth." content'>\n";
			echo "<h3><a href='".BASEDIR."news.php?readmore=".$data['news_id']."'>".$data['news_subject']."</a></h3>\n";
			echo "<div class='small m-b-10'>".$locale['home_0105'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])." ".showdate('newsdate', $data['news_datestamp']).$locale['home_0106'].$news_cat."</div>\n";
			echo "<div>".stripslashes($data['news_news'])."</div>\n";
			echo "</div>\n";
		}
		echo "</div>\n";
	} else {
		echo $locale['home_0050'];
	}

	closetable();

	$content_installed = true;
}

if (db_exists(DB_ARTICLES) && db_exists(DB_ARTICLE_CATS)) {

	opentable($locale['home_0001']);

	$result = dbquery("SELECT ar.article_id, ar.article_subject, ar.article_snippet, ar.article_datestamp, ac.article_cat_id, ac.article_cat_name, us.user_id, us.user_name, us.user_status FROM ".DB_ARTICLES." as ar INNER JOIN ".DB_ARTICLE_CATS." as ac ON ac.article_cat_id = ar.article_cat INNER JOIN ".DB_USERS." as us ON us.user_id = ar.article_name WHERE ar.article_visibility <= ".$acclevel." ORDER BY ar.article_datestamp DESC LIMIT 3;");

	if (dbrows($result)) {

		$colwidth = floor(12 / mysql_num_rows($result));

		echo "<div class='row'>\n";

		while ($data = dbarray($result)) {

			$article_cat = "<a href='".BASEDIR."articles.php?cat_id=".$data['article_cat_id']."'>".$data['article_cat_name']."</a>";

			echo "<div class='col-xs-".$colwidth." col-sm-".$colwidth." col-md-".$colwidth." col-lg-".$colwidth." content'>\n";
			echo "<h3><a href='".BASEDIR."articles.php?article_id=".$data['article_id']."'>".$data['article_subject']."</a></h3>\n";
			echo "<div class='small m-b-10'>".$locale['home_0105'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])." ".showdate('newsdate', $data['article_datestamp']).$locale['home_0106'].$article_cat."</div>\n";
			echo "<div>".stripslashes($data['article_snippet'])."</div>\n";
			echo "</div>\n";
		}
		echo "</div>\n";
	} else {
		echo $locale['home_0051'];
	}

	closetable();

	$content_installed = true;
}

if (db_exists(DB_BLOG) && db_exists(DB_BLOG_CATS)) {

	opentable($locale['home_0002']);

	$result = dbquery("SELECT bl.blog_id, bl.blog_subject, bl.blog_blog, bl.blog_datestamp, us.user_id, us.user_name, us.user_status, bc.blog_cat_id, bc.blog_cat_name FROM ".DB_BLOG." as bl LEFT JOIN ".DB_BLOG_CATS." as bc ON bc.blog_cat_id = bl.blog_cat INNER JOIN ".DB_USERS." as us ON bl.blog_name = us.user_id WHERE (".time()." > bl.blog_start OR bl.blog_start = 0) AND (".time()." < bl.blog_end OR bl.blog_end = 0) AND bl.blog_visibility <= ".$acclevel." ORDER BY bl.blog_datestamp DESC LIMIT 3");

	if (dbrows($result)) {

		$colwidth = floor(12 / mysql_num_rows($result));

		echo "<div class='row'>\n";

		while ($data = dbarray($result)) {

			$blog_cat = $data['blog_cat_id'] ? "<a href='".BASEDIR."blog_cats.php?cat_id=".$data['blog_cat_id']."'>".$data['blog_cat_name']."</a>" : $locale['home_0102'];

			echo "<div class='col-xs-".$colwidth." col-sm-".$colwidth." col-md-".$colwidth." col-lg-".$colwidth." content'>\n";
			echo "<h3><a href='".BASEDIR."blog.php?readmore=".$data['blog_id']."'>".$data['blog_subject']."</a></h3>\n";
			echo "<div class='small m-b-10'>".$locale['home_0105'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])." ".showdate('newsdate', $data['blog_datestamp']).$locale['home_0106'].$blog_cat."</div>\n";
			echo "<div>".stripslashes($data['blog_blog'])."</div>\n";
			echo "</div>\n";
		}
		echo "</div>\n";
	} else {
		echo $locale['home_0052'];
	}

	closetable();

	$content_installed = true;
}

if (db_exists(DB_DOWNLOADS) && db_exists(DB_DOWNLOAD_CATS)) {

	opentable($locale['home_0003']);

	$result = dbquery("SELECT dl.download_id, dl.download_title, dl.download_description_short, dl.download_datestamp, dc.download_cat_id, dc.download_cat_name, us.user_id, us.user_name, us.user_status FROM ".DB_DOWNLOADS." dl INNER JOIN ".DB_DOWNLOAD_CATS." dc ON dc.download_cat_id = dl.download_cat INNER JOIN ".DB_USERS." us ON us.user_id = dl.download_user WHERE dl.download_visibility <= ".$acclevel." ORDER BY dl.download_datestamp DESC LIMIT 3");

	if (dbrows($result)) {

		$colwidth = floor(12 / mysql_num_rows($result));

		echo "<div class='row'>\n";

		while ($data = dbarray($result)) {

			$dl_cat = "<a href='".BASEDIR."downloads.php?cat_id=".$data['download_cat_id']."'>".$data['download_cat_name']."</a>";

			echo "<div class='col-xs-".$colwidth." col-sm-".$colwidth." col-md-".$colwidth." col-lg-".$colwidth." content'>\n";
			echo "<h3><a href='".BASEDIR."downloads.php?cat_id=".$data['download_cat_id']."&download_id=".$data['download_id']."'>".$data['download_title']."</a></h3>\n";
			echo "<div class='small m-b-10'>".$locale['home_0105'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])." ".showdate('newsdate', $data['download_datestamp']).$locale['home_0106'].$dl_cat."</div>\n";
			echo "<div>".stripslashes($data['download_description_short'])."</div>\n";
			echo "</div>\n";
		}
		echo "</div>\n";
	} else {
		echo $locale['home_0053'];
	}

	closetable();

	$content_installed = true;
}

if (!$content_installed) {
	opentable($locale['home_0100']);

	echo $locale['home_0101'];

	closetable();
}

require_once THEMES."templates/footer.php";
?>