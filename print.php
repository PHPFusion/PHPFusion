<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: print.php
| Author: Nick Jones (Digitanium)
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
include LOCALE.LOCALESET."print.php";

if ($settings['maintenance'] == "1" && ((iMEMBER && $settings['maintenance_level'] == "1" && $userdata['user_id'] != "1") || ($settings['maintenance_level'] > $userdata['user_level']))) { redirect(BASEDIR."maintenance.php"); }
if (iMEMBER) { $result = dbquery("UPDATE ".DB_USERS." SET user_lastvisit='".time()."', user_ip='".USER_IP."', user_ip_type='".USER_IP_TYPE."' WHERE user_id='".$userdata['user_id']."'"); }

echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>\n";
echo "<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='".$locale['xml_lang']."' lang='".$locale['xml_lang']."'>\n";
echo "<head>\n<title>".$settings['sitename']."</title>\n";
echo "<meta http-equiv='Content-Type' content='text/html; charset=".$locale['charset']."' />\n";
echo "<meta name='description' content='".$settings['description']."' />\n";
echo "<meta name='keywords' content='".$settings['keywords']."' />\n";
echo "<style type='text/css'>
	* { background: transparent !important; color: #444 !important; text-shadow: none; }
	body { font-family:Verdana,Tahoma,Arial,Sans-Serif;font-size:14px; }
	hr { display:block; height:1px; border:0; border-top:1px solid #ccc; margin:1em 0; padding:0; }
	.small { font-family:Verdana,Tahoma,Arial,Sans-Serif;font-size:12px; }
	.small2 { font-family:Verdana,Tahoma,Arial,Sans-Serif;font-size:12px;color:#666; }
	a, a:visited { color: #444 !important; text-decoration: underline; }
	a:after { content: ' (' attr(href) ')'; }
	abbr:after { content: ' (' attr(title) ')'; }
	pre, blockquote { border: 1px solid #999; page-break-inside: avoid; }
	img { page-break-inside: avoid; }
	@page { margin: 0.5cm; }
	p, h2, h3 { orphans: 3; widows: 3; }
	h2, h3 { page-break-after: avoid; }
</style>\n";
echo "</head>\n<body>\n";
if ((isset($_GET['type']) && $_GET['type'] == "A") && (isset($_GET['item_id']) && isnum($_GET['item_id']))) {
	$result = dbquery(
		"SELECT ta.article_subject, ta.article_article, ta.article_breaks, article_datestamp, tac.article_cat_access,
		tu.user_id, tu.user_name, tu.user_status
		FROM ".DB_ARTICLES." ta
		INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
		LEFT JOIN ".DB_USERS." tu ON ta.article_name=tu.user_id
		WHERE article_id='".$_GET['item_id']."' AND article_draft='0'"
	);
	$res = false;
	if (dbrows($result)) {
		$data = dbarray($result);
		if (checkgroup($data['article_cat_access'])) {
			$res = true;
			$article = str_replace("<--PAGEBREAK-->", "", stripslashes($data['article_article']));
			if ($data['article_breaks'] == "y") { $article = nl2br($article); }
			echo "<strong>".$data['article_subject']."</strong><br />\n";
			echo "<span class='small'>".$locale['400'].profile_link($data['user_id'], $data['user_name'], $data['user_status']).$locale['401'].ucfirst(showdate("longdate", $data['article_datestamp']))."</span>\n";
			echo "<hr />".$article."\n";
		}
	}
	if (!$res) { redirect("index.php"); }
} elseif ((isset($_GET['type']) && $_GET['type'] == "N") && (isset($_GET['item_id']) && isnum($_GET['item_id']))) {
	$result = dbquery(
		"SELECT tn.news_subject, tn.news_news, tn.news_extended, tn.news_breaks, tn.news_datestamp, tn.news_visibility,
		tu.user_id, tu.user_name, tu.user_status
		FROM ".DB_NEWS." tn
		LEFT JOIN ".DB_USERS." tu ON tn.news_name=tu.user_id
		WHERE news_id='".$_GET['item_id']."' AND news_draft='0'"
	);
	$res = false;
	if (dbrows($result) != 0) {
		$data = dbarray($result);
		if (checkgroup($data['news_visibility'])) {
			$res = true;
			$news = stripslashes($data['news_news']);
			if ($data['news_breaks'] == "y") { $news = nl2br($news); }
			if ($data['news_extended']) {
				$news_extended = stripslashes($data['news_extended']);
				if ($data['news_breaks'] == "y") { $news_extended = nl2br($news_extended); }
			} else {
				$news_extended = "";
			}
			echo "<strong>".$data['news_subject']."</strong><br />\n";
			echo "<span class='small'>".$locale['400'].profile_link($data['user_id'], $data['user_name'], $data['user_status']).$locale['401'].ucfirst(showdate("longdate", $data['news_datestamp']))."</span>\n";
			echo "<hr />".$news."\n";
			if ($news_extended) { echo "<hr />\n<strong>".$locale['402']."</strong>\n<hr />\n$news_extended\n"; }
		}
	}
	if (!$res) { redirect("index.php"); }
} elseif ((isset($_GET['type']) && $_GET['type'] == "F") && (isset($_GET['thread']) && isnum($_GET['thread'])) && !isset($_GET['post'])) {
	$posts_per_page = 20;
	if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }

	$result = dbquery(
		"SELECT fp.post_message, fp.post_datestamp, fp.post_edittime, fp.post_author, fp.post_edituser,
		fu.user_name AS user_name, fu.user_status AS user_status, fe.user_name AS edit_name, fe.user_status AS edit_status,
		ft.thread_subject, ff.forum_access
		FROM ".DB_THREADS." ft
		INNER JOIN ".DB_POSTS." fp ON ft.thread_id = fp.thread_id
		INNER JOIN ".DB_FORUMS." ff ON ff.forum_id = ft.forum_id
		INNER JOIN ".DB_USERS." fu ON fu.user_id = fp.post_author
		LEFT JOIN ".DB_USERS." fe ON fe.user_id = fp.post_edituser
		WHERE ft.thread_id=".$_GET['thread']."
		ORDER BY fp.post_datestamp
		LIMIT ".$_GET['rowstart'].",$posts_per_page"
	);
	$res = false; $i = 0;
	if (dbrows($result)) {
		while ($data = dbarray($result)) {
			if (checkgroup($data['forum_access'])) {
				$res = true;
				if ($i == 0) echo $locale['500']." <strong>".$settings['sitename']." :: ".$data['thread_subject']."</strong><hr /><br />\n";
				echo "<div style='margin-left:20px'>\n";
				echo "<div style='float:left'>".$locale['501'].profile_link($data['post_author'], $data['user_name'], $data['user_status']).$locale['502'].showdate("forumdate", $data['post_datestamp'])."</div><div style='float:right'>#".($i+1)."</div><div style='float:none;clear:both'></div><hr />\n";
				echo nl2br(parseubb(parsesmileys($data['post_message'])));
				if ($data['edit_name']!='') {
					echo "<div style='margin-left:20px'>\n<hr />\n";
					echo $locale['503'].profile_link($data['post_edituser'], $data['edit_name'], $data['edit_status']).$locale['502'].showdate("forumdate", $data['post_edittime']);
					echo "</div>\n";
				}
				echo "</div>\n";
				echo "<br />\n";
				$i++;
			}
		}
	}
	if (!$res) { redirect("index.php"); }
} elseif ((isset($_GET['type']) && $_GET['type'] == "F") && (isset($_GET['thread']) && isnum($_GET['thread'])) && (isset($_GET['post']) && isnum($_GET['post'])) && (isset($_GET['nr']) && isnum($_GET['nr']))) {
	$result = dbquery(
		"SELECT fp.post_message, fp.post_datestamp, fp.post_edittime, fp.post_author as post_author, fp.post_edituser,
		fu.user_name AS user_name, fu.user_status AS user_status, fe.user_name AS edit_name, fe.user_status AS edit_status,
		ft.thread_subject, ff.forum_access
		FROM ".DB_THREADS." ft
		INNER JOIN ".DB_POSTS." fp ON ft.thread_id = fp.thread_id
		INNER JOIN ".DB_FORUMS." ff ON ff.forum_id = ft.forum_id
		INNER JOIN ".DB_USERS." fu ON fu.user_id = fp.post_author
		LEFT JOIN ".DB_USERS." fe ON fe.user_id = fp.post_edituser
		WHERE ft.thread_id=".$_GET['thread']." AND fp.post_id = ".$_GET['post']
	);
	$res = false;
	if (dbrows($result)) {
		$data = dbarray($result);
		if (checkgroup($data['forum_access'])) {
			$res = true;
			echo $locale['500']." <strong>".$settings['sitename']." :: ".$data['thread_subject']."</strong><hr /><br />\n";
			echo "<div style='margin-left:20px'>\n";
			echo "<div style='float:left'>".$locale['501'].profile_link($data['post_author'], $data['user_name'], $data['user_status']).$locale['502'].showdate("forumdate", $data['post_datestamp'])."</div><div style='float:right'>#".$_GET['nr']."</div><div style='float:none;clear:both'></div><hr />\n";
			echo nl2br(parseubb(parsesmileys($data['post_message'])));
			if ($data['edit_name'] != "") {
				echo "<div style='margin-left:20px'>\n<hr />\n";
				echo $locale['503'].profile_link($data['post_edituser'], $data['edit_name'], $data['edit_status']).$locale['502'].showdate("forumdate", $data['post_edittime']);
				echo "</div>\n";
			}
			echo "</div>\n";
			echo "<br />\n";
		}
	}
	if (!$res) { redirect("index.php"); }
} elseif (isset($_GET['type']) && $_GET['type'] == "T" && $settings['enable_terms'] == 1) {
	echo "<strong>".$settings['sitename']." ".$locale['600']."</strong><br />\n";
	echo "<span class='small'>".$locale['601']." ".ucfirst(showdate("longdate", $settings['license_lastupdate']))."</span>\n";
	echo "<hr />".stripslashes($settings['license_agreement'])."\n";
} else {
	redirect("index.php");
}
echo "</body>\n</html>\n";

if (ob_get_length() !== FALSE){
	ob_end_flush();
}

mysql_close($db_connect);
?>