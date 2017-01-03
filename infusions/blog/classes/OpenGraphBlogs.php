<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: OpenGraphBlogs.php
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


class OpenGraphBlogs extends OpenGraph {
	public static function ogBlog($blog_id = 0) {
		$settings = fusion_get_settings();
		$info = array();

		$result = dbquery("SELECT `blog_subject`, `blog_blog`, `blog_keywords`, `blog_image_t1` FROM `" . DB_BLOG . "` WHERE `blog_id` = '$blog_id'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$info['url'] = $settings['siteurl'].'infusions/blog/blog.php?readmore='.$blog_id;
			$info['keywords'] = $data['blog_keywords'] ? $data['blog_keywords'] : fusion_get_settings('keywords');
			$info['title'] = $data['blog_subject'].' - '.fusion_get_settings('sitename');
			$info['description'] = $data['blog_blog'] ? fusion_first_words(strip_tags(html_entity_decode($data['blog_blog'])), 50) : $settings['description'];
			$info['type'] = 'article';
			if (!empty($data['blog_image_t1'])) {
				$info['image'] = $settings['siteurl'].'infusions/blog/images/thumbs/' . $data['blog_image_t1'];
			} else {
				$info['image'] = $settings['siteurl'].'images/favicons/mstile-150x150.png';
			}
		}

		OpenGraphBlogs::setValues($info);
	}
}
