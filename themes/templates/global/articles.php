<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined('render_article')) {
	function render_article($subject, $article, $info) {
		global $locale, $settings, $aidlink;
		$category = "<a href='".BASEDIR."articles.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>\n";
		$comment = "<a href='".BASEDIR."articles.php?article_id=".$info['article_id']."#comments'>".$info['article_comments']." comment</a>\n";
		echo render_breadcrumbs();
		echo "<!--pre_article-->";
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
		echo "<div class='news-user-info clearfix m-b-10'>\n";
		echo "<h4>About <a href='".BASEDIR."profile.php?lookup=".$info['user_id']."'>".$info['user_name']."</a>\n</h4>";
		echo "<div class='pull-left m-r-10'>".display_avatar($info, '80px')."</div>\n";
		echo "<strong>".getuserlevel($info['user_level'])."</strong><br/>\n";
		echo "<strong>Joined since: ".showdate('newsdate', $info['user_joined'])."</strong><br/>\n";
		echo "</div>\n";
		echo "</article>";
		echo "<!--sub_article-->";
		echo $info['page_nav'];
		echo $info['article_allow_comments'] ? showcomments("A", DB_ARTICLES, "article_id", $_GET['article_id'], FUSION_SELF."?article_id=".$_GET['article_id']) : '';
		echo $info['article_allow_ratings'] ? showratings("A", $_GET['article_id'], FUSION_SELF."?article_id=".$_GET['article_id']) : '';
	}
}

if (!defined('render_articles_main')) {
	function render_articles_main($info) {
		global $locale;
		echo render_breadcrumbs();
		echo "<!--pre_article_idx-->\n";
		opentable($locale['400']);
		if (isset($info['articles']['item'])) {
			$counter = 0;
			$columns = 2;
			echo "<div class='row'>\n";
			foreach($info['articles']['item'] as $data) {
				if ($counter != 0 && ($counter%$columns == 0)) {
					echo "</div>\n<div class='row'>\n";
				}
				echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
				echo "<!--article_idx_cat_name-->\n";
				echo "<h4><a href='".BASEDIR."articles.php?cat_id=".$data['article_cat_id']."'><strong>".$data['article_cat_name']."</a></strong> <span class='small2'>(".$data['article_count'].")</span></h4>";
				echo ($data['article_cat_description'] != "") ? $data['article_cat_description'] : "";
				echo "</div>\n";
				$counter++;
			}
			echo "</div>\n";
		} else {
			echo "<div style='text-align:center'><br />\n".$locale['401']."<br /><br />\n</div>\n";
		}
		closetable();
		echo "<!--sub_article_idx-->\n";
	}
}

if (!defined('render_articles_category')) {
	function render_articles_category($info) {
		global $locale;
		if (isset($info['articles']['category'])) {
			$data = $info['articles']['category'];
			echo render_breadcrumbs();
			echo "<!--pre_article_cat-->";
			opentable($locale['400'].": ".$data['article_cat_name']);
			if (isset($info['articles']['item'])) {
				foreach($info['articles']['item'] as $cdata) {
					echo "<h4 class='display-inline-block strong'><a href='".BASEDIR."articles.php?article_id=".$cdata['article_id']."'>".$cdata['article_subject']."</a></strong></h4> <span class='label label-success m-l-5'>".$cdata['new']."</span><br/>\n";
					echo preg_replace("/<!?--\s*pagebreak\s*-->/i", "", stripslashes($cdata['article_snippet']))."\n";
				}
				echo !empty($info['page_nav']) ? "<div class='m-t-5'>".$info['page_nav']."</div>\n" : '';
			} else {
				echo "<div class='well text-center'>".$locale['403']."</div>\n";
			}
			echo "<!--sub_article_cat-->";
			closetable();
		}
	}
}
?>