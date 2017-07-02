<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: latest_comments_panel.php
| Author: PHP-Fusion Development Team
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

include_once INFUSIONS."latest_comments_panel/templates.php";
$displayComments = 10;
$comments_per_page = fusion_get_settings('comments_per_page');

$comment_query = "SELECT tc.comment_id, tc.comment_item_id, tc.comment_type, tc.comment_message, tu.user_id, tu.user_name, tu.user_status
    FROM ".DB_COMMENTS." tc
    LEFT JOIN ".DB_USERS." tu ON tu.user_id = tc.comment_name
    WHERE tc.comment_hidden='0'
    ORDER BY tc.comment_datestamp DESC
";
$result = dbquery($comment_query);

$info['title'] = $locale['global_025'];
$info['theme_bullet'] = THEME_BULLET;

if (dbrows($result)) {
    $i = 0;

	while ($data = dbarray($result)) {
        if ($i == $displayComments) {
            break;
        }
		switch ($data['comment_type']) {
			case "N":
		$ndata = [
			':n_id'       => $data['comment_item_id'],
			':n_stime'    => time(),
			':n_etime'    => time(),
			':n_language' => LANGUAGE,
			];
			$news_query = "SELECT
				ns.news_subject
				FROM ".DB_NEWS." as ns
				LEFT JOIN ".DB_NEWS_CATS." as nc ON nc.news_cat_id = ns.news_cat
				WHERE ns.news_id=:n_id AND (ns.news_start='0' OR ns.news_start<=:n_stime)
				AND (ns.news_end='0' OR ns.news_end>=:n_etime) AND ns.news_draft='0'
				AND ".groupaccess('ns.news_visibility')."
				".(multilang_table("NS") ? "AND ns.news_language=:n_language" : "")."
				ORDER BY ns.news_datestamp DESC";
			$results = dbquery($news_query, $ndata);

			if (dbrows($results)) {
				$news_data = dbarray($results);

				$commentStart = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='".$data['comment_item_id']."' AND comment_type='N' AND comment_id<=".$data['comment_id']);
				$commentStart = $commentStart > $comments_per_page ? "&amp;c_start_news_comments=".((floor($commentStart / $comments_per_page) * $comments_per_page) - $comments_per_page) : "";

				$item = [
				    'subject'      => $news_data['news_subject'],
				    'link_url'     => INFUSIONS."news/news.php?readmore=".$data['comment_item_id'].$commentStart."#c".$data['comment_id'],
				    'link_title'   => $data['comment_message'],
				    'profile_link' => profile_link($data['user_id'], $data['user_name'], $data['user_status'])
				];
				$i++;
                $info['item'][] = $item;
			}
			continue;
			case "A":
			$ndata = [
				':n_id'       => $data['comment_item_id'],
				':n_language' => LANGUAGE,
				];
			$article_query = "SELECT
				ar.article_subject
				FROM ".DB_ARTICLES." as ar
				INNER JOIN ".DB_ARTICLE_CATS." as ac ON ac.article_cat_id = ar.article_cat
				WHERE ar.article_id=:n_id AND ar.article_draft='0' AND ".groupaccess('ar.article_visibility').(multilang_table("AR") ? " AND ar.article_language=:n_language" : "")."
				ORDER BY ar.article_datestamp DESC";
			$results = dbquery($article_query, $ndata);

			if (dbrows($results)) {
				$article_data = dbarray($results);

				$commentStart = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='".$data['comment_item_id']."' AND comment_type='A' AND comment_id<=".$data['comment_id']);
				$commentStart = $commentStart > $comments_per_page ? "&amp;c_start_news_comments=".((floor($commentStart / $comments_per_page) * $comments_per_page) - $comments_per_page) : "";

				$item = [
				    'subject'      => $article_data['article_subject'],
				    'link_url'     => INFUSIONS."articles/articles.php?article_id=".$data['comment_item_id'].$commentStart."#c".$data['comment_id'],
				    'link_title'   => $data['comment_message'],
				    'profile_link' => profile_link($data['user_id'], $data['user_name'], $data['user_status'])
				];
				$i++;
                $info['item'][] = $item;
			}
			continue;
			case "P":
			$ndata = [
				':n_id'       => $data['comment_item_id'],
				':n_language' => LANGUAGE,
				];
			$article_query = "SELECT
				p.photo_title
				FROM ".DB_PHOTOS." as p
				INNER JOIN ".DB_PHOTO_ALBUMS." as a ON p.album_id=a.album_id
				WHERE p.photo_id=:n_id AND ".groupaccess('a.album_access').(multilang_table("PG") ? " AND a.album_language=:n_language" : "")."
				ORDER BY p.photo_datestamp DESC";
			$results = dbquery($article_query, $ndata);

			if (dbrows($results)) {
				$photo_data = dbarray($results);
				$commentStart = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='".$data['comment_item_id']."' AND comment_type='P' AND comment_id<=".$data['comment_id']);
				$commentStart = $commentStart > $comments_per_page ? "&amp;c_start_news_comments=".((floor($commentStart / $comments_per_page) * $comments_per_page) - $comments_per_page) : "";

				$item = [
				    'subject'      => $photo_data['photo_title'],
				    'link_url'     => INFUSIONS."gallery/gallery.php?photo_id=".$data['comment_item_id'].$commentStart."#c".$data['comment_id'],
				    'link_title'   => $data['comment_message'],
				    'profile_link' => profile_link($data['user_id'], $data['user_name'], $data['user_status'])
				];
				$i++;
                $info['item'][] = $item;
			}
			continue;
			case "D":
			$ndata = [
				':n_id'       => $data['comment_item_id'],
				':n_language' => LANGUAGE,
				];
			$download_query = "SELECT
				d.download_title
				FROM ".DB_DOWNLOADS." as d
				INNER JOIN ".DB_DOWNLOAD_CATS." as c ON c.download_cat_id=d.download_cat
				WHERE d.download_id=:n_id AND ".groupaccess('d.download_visibility').(multilang_table("DL") ? " AND c.download_cat_language=:n_language" : "")."
				ORDER BY d.download_datestamp DESC";
			$results = dbquery($download_query, $ndata);

			if (dbrows($results)) {
				$download_data = dbarray($results);

				$commentStart = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='".$data['comment_item_id']."' AND comment_type='D' AND comment_id<=".$data['comment_id']);
				$commentStart = $commentStart > $comments_per_page ? "&amp;c_start_news_comments=".((floor($commentStart / $comments_per_page) * $comments_per_page) - $comments_per_page) : "";

				$item = [
				    'subject'      => $download_data['download_title'],
				    'link_url'     => INFUSIONS."downloads/downloads.php?download_id=".$data['comment_item_id'].$commentStart."#c".$data['comment_id'],
				    'link_title'   => $data['comment_message'],
				    'profile_link' => profile_link($data['user_id'], $data['user_name'], $data['user_status'])
				];
				$i++;
                $info['item'][] = $item;
			}
			continue;
			case "B":
			$ndata = [
				':n_id'       => $data['comment_item_id'],
				':n_language' => LANGUAGE,
				];
			$blog_query = "SELECT
				d.blog_subject
				FROM ".DB_BLOG." as d
				INNER JOIN ".DB_BLOG_CATS." as c ON c.blog_cat_id=d.blog_cat
				WHERE d.blog_id=:n_id AND ".groupaccess('d.blog_visibility').(multilang_table("BL") ? " AND d.blog_language=:n_language" : "")."
				ORDER BY d.blog_datestamp DESC";
			$results = dbquery($blog_query, $ndata);

			if (dbrows($results)) {
				$blog_data = dbarray($results);

				$commentStart = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='".$data['comment_item_id']."' AND comment_type='B' AND comment_id<=".$data['comment_id']);
				$commentStart = $commentStart > $comments_per_page ? "&amp;c_start_news_comments=".((floor($commentStart / $comments_per_page) * $comments_per_page) - $comments_per_page) : "";

				$item = [
				    'subject'      => $blog_data['blog_subject'],
				    'link_url'     => INFUSIONS."blog/blog.php?readmore=".$data['comment_item_id'].$commentStart."#c".$data['comment_id'],
				    'link_title'   => $data['comment_message'],
				    'profile_link' => profile_link($data['user_id'], $data['user_name'], $data['user_status'])
				];
				$i++;
                $info['item'][] = $item;
			}
			continue;

        }

    }
} else {
	$info['no_rows'] = $locale['global_026'];
}

render_latest_comments($info);
