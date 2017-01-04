<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: OpenGraphArticles.php
| Author: Chubatyj Vitalij (Rizado)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;

class OpenGraphArticles extends OpenGraph {
	public static function ogArticle($article_id = 0) {
		$settings = fusion_get_settings();
		$info = array();

		$result = dbquery("SELECT `article_subject`, `article_snippet`, `article_keywords` FROM `" . DB_ARTICLES . "` WHERE `article_id` = '$article_id'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$info['url'] = $settings['siteurl'].'infusions/articles/articles.php?readmore='.$article_id;
			$info['keywords'] = $data['article_keywords'] ? $data['article_keywords'] : fusion_get_settings('keywords');
			$info['image'] = $settings['siteurl'].'images/favicons/mstile-150x150.png';
			$info['title'] = $data['article_subject'].' - '.fusion_get_settings('sitename');
			$info['description'] = $data['article_snippet'] ? fusion_first_words(strip_tags(html_entity_decode($data['article_snippet'])), 50) : $settings['description'];
			$info['type'] = 'article';
		}

		OpenGraphArticles::setValues($info);
	}
}
