<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: home.php
| Author: Chubatyj Vitalij (Rizado)
| Web: http://chubatyj.ru/
| Co-Author: Takács Ákos (Rimelek)
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

add_to_title($locale['home']);

$acclevel = isset($userdata['user_level']) ? $userdata['user_level'] : 0;

$configs = array();
$configs[DB_NEWS] = array(
	'select' => "SELECT
					ne.news_id as id, ne.news_subject as title, ne.news_news as content,
					ne.news_datestamp as datestamp, us.user_id, us.user_name,
					us.user_status, nc.news_cat_id as cat_id, nc.news_cat_name as cat_name
				FROM ".DB_NEWS." as ne
				LEFT JOIN ".DB_NEWS_CATS." as nc ON nc.news_cat_id = ne.news_cat
				INNER JOIN ".DB_USERS." as us ON ne.news_name = us.user_id
				WHERE (".time()." > ne.news_start OR ne.news_start = 0)
					AND (".time()." < ne.news_end OR ne.news_end = 0)
					AND ".groupaccess('ne.news_visibility')." ".(multilang_table("NS") ? "AND news_language='".LANGUAGE."'" : "")."
				ORDER BY ne.news_datestamp DESC LIMIT 3",
	'locale' => array(
		'norecord' => $locale['home_0050'],
		'blockTitle' => $locale['home_0000'],
	),
	'categoryLinkPattern' => BASEDIR."news_cats.php?cat_id={cat_id}",
	'contentLinkPattern' => BASEDIR."news.php?readmore={id}"
);

$configs[DB_ARTICLES] = array(
	'select' => "SELECT
					ar.article_id as id, ar.article_subject as title, ar.article_snippet as content,
					ar.article_datestamp as datestamp, ac.article_cat_id as cat_id, ac.article_cat_name as cat_name,
					us.user_id, us.user_name, us.user_status
				FROM ".DB_ARTICLES." as ar
				INNER JOIN ".DB_ARTICLE_CATS." as ac ON ac.article_cat_id = ar.article_cat
				INNER JOIN ".DB_USERS." as us ON us.user_id = ar.article_name
				WHERE ".groupaccess('ar.article_visibility')." ".(multilang_table("AR") ? "AND ac.article_cat_language='".LANGUAGE."'" : "")."
				ORDER BY ar.article_datestamp DESC LIMIT 3",
	'locale' => array(
		'norecord' => $locale['home_0051'],
		'blockTitle' => $locale['home_0001'],
	),
	'categoryLinkPattern' => BASEDIR."articles.php?cat_id={cat_id}",
	'contentLinkPattern' => BASEDIR."articles.php?article_id={id}"
);

$configs[DB_BLOG] = array(
	'select' => "SELECT
					bl.blog_id as id, bl.blog_subject as title, bl.blog_blog as content,
					bl.blog_datestamp as datestamp, us.user_id, us.user_name,
					us.user_status, bc.blog_cat_id as cat_id, bc.blog_cat_name as cat_name
				FROM ".DB_BLOG." as bl
				LEFT JOIN ".DB_BLOG_CATS." as bc ON bc.blog_cat_id = bl.blog_cat
				INNER JOIN ".DB_USERS." as us ON bl.blog_name = us.user_id
				WHERE (".time()." > bl.blog_start OR bl.blog_start = 0)
					AND (".time()." < bl.blog_end OR bl.blog_end = 0)
					AND ".groupaccess('bl.blog_visibility')." ".(multilang_table("BL") ? "AND blog_language='".LANGUAGE."'" : "")."
				ORDER BY bl.blog_datestamp DESC LIMIT 3",
	'locale' => array(
		'norecord' => $locale['home_0052'],
		'blockTitle' => $locale['home_0002']
	),
	'categoryLinkPattern' => BASEDIR."blog_cats.php?cat_id={cat_id}",
	'contentLinkPattern' => BASEDIR."blog.php?readmore={id}"
);

$configs[DB_DOWNLOADS] = array(
	'select' => "SELECT
					dl.download_id as id, dl.download_title as title, dl.download_description_short as content,
					dl.download_datestamp as datestamp, dc.download_cat_id as cat_id, dc.download_cat_name as cat_name,
					us.user_id, us.user_name, us.user_status
				FROM ".DB_DOWNLOADS." dl
				INNER JOIN ".DB_DOWNLOAD_CATS." dc ON dc.download_cat_id = dl.download_cat
				INNER JOIN ".DB_USERS." us ON us.user_id = dl.download_user
				WHERE ".groupaccess('dl.download_visibility')." ".(multilang_table("DL") ? "AND dc.download_cat_language='".LANGUAGE."'" : "")."
				ORDER BY dl.download_datestamp DESC LIMIT 3",
	'locale' => array(
		'norecord' => $locale['home_0053'],
		'blockTitle' => $locale['home_0003']
	),
	'categoryLinkPattern' => BASEDIR."downloads.php?cat_id={cat_id}",
	'contentLinkPattern' => BASEDIR."downloads.php?cat_id={cat_id}&download_id={id}"
);

$contents = array();

foreach ($configs as $table => $config) {
	if (!db_exists($table)) {
		continue;
	}
	$contents[$table] = array(
		'data' => array(),
		'colwidth' => 0,
		'norecord' => $config['locale']['norecord'],
		'blockTitle' => $config['locale']['blockTitle'],
	);
	$result = dbquery($config['select']);
	$items_count = dbrows($result);
	if (!$items_count) {
		continue;
	}

	$contents[$table]['colwidth'] = floor(12 / $items_count);

	$data = array();
	while ($row = dbarray($result)) {
		$keys = array_keys($row);
		foreach ($keys as $i => $key) {
			$keys[$i] = '{'.$key.'}';
		}
		$pairs = array_combine($keys, array_values($row));
		$cat = $row['cat_id'] ? "<a href='".strtr($config['categoryLinkPattern'], $pairs)."'>".$row['cat_name']."</a>" : $locale['home_0102'];
		$data[] = array(
			'cat' => $cat,
			'url' => strtr($config['contentLinkPattern'], $pairs),
			'title' => $row['title'],
			'meta' => $locale['home_0105'].profile_link($row['user_id'], $row['user_name'], $row['user_status'])
				." ".showdate('newsdate', $row['datestamp']).$locale['home_0106'].$cat,
			'content' => stripslashes($row['content'])
		);
	}
	$contents[$table]['data'] = $data;
}

foreach($contents as $content) :
	$colwidth = $content['colwidth'];
	opentable($content['blockTitle']);
	if ($colwidth) :
		$classes = "col-xs-".$colwidth." col-sm-".$colwidth." col-md-".$colwidth." col-lg-".$colwidth." content";
		?>
		<div class='row'>
		<?php foreach($content['data'] as $data) : ?>
			<div class='<?php echo $classes ?>'>
				<h3><a href='<?php echo $data['url'] ?>'><?php echo $data['title'] ?></a></h3>
				<div class='small m-b-10'><?php echo $data['meta'] ?></div>
				<div><?php echo $data['content'] ?></div>
			</div>
		<?php endforeach ?>
		</div>
	<?php else :
		echo $content['norecord'];
	endif;
	closetable();
endforeach;

if (!$contents) {
	opentable($locale['home_0100']);
	echo $locale['home_0101'];
	closetable();
}

require_once THEMES."templates/footer.php";
