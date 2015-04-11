<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: submit.php
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
require_once THEMES."templates/header.php";
include_once INCLUDES."bbcode_include.php";
include LOCALE.LOCALESET."submit.php";
if (!iMEMBER) {
	redirect("index.php");
}
$stype = filter_input(INPUT_GET, 'stype') ? : '';
$submit_info = array();
$modules = array(
	'n' => db_exists(DB_NEWS),
	'p' => db_exists(DB_PHOTO_ALBUMS),
	'a' => db_exists(DB_ARTICLES),
	'd' => db_exists(DB_DOWNLOADS),
	'l' => db_exists(DB_WEBLINKS),
	'b' => db_exists(DB_BLOG)
);
$sum = array_sum($modules);
if (!$sum or empty($modules[$stype])) {
	redirect("index.php");
} elseif ($stype === "l") {
	if (isset($_POST['submit_link'])) {
		$submit_info['link_category'] = form_sanitizer($_POST['link_category'], '', 'link_category');
		$submit_info['link_name'] = form_sanitizer($_POST['link_name'], '', 'link_name');
		$submit_info['link_url'] = form_sanitizer($_POST['link_url'], '', 'link_url');
		$submit_info['link_description'] = form_sanitizer($_POST['link_description'], '', 'link_description');
		if (!defined("FUSION_NULL")) {
			$result = dbquery("INSERT INTO ".DB_SUBMISSIONS." (submit_type, submit_user, submit_datestamp, submit_criteria) VALUES ('l', '".$userdata['user_id']."', '".time()."', '".addslashes(serialize($submit_info))."')");
			add_to_title($locale['global_200'].$locale['400']);
			opentable($locale['400']);
			echo "<div style='text-align:center'><br />\n".$locale['410']."<br /><br />\n";
			echo "<a href='submit.php?stype=l'>".$locale['411']."</a><br /><br />\n";
			echo "<a href='index.php'>".$locale['412']."</a><br /><br />\n</div>\n";
			closetable();
		}
	}
	add_to_title($locale['global_200'].$locale['400']);
	opentable($locale['400']);
	$result = dbquery("SELECT weblink_cat_id, weblink_cat_name FROM ".DB_WEBLINK_CATS." ".(multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."'" : "")." ORDER BY weblink_cat_name");
	if (dbrows($result) > 0) {
		$opts = array();
		while ($data = dbarray($result)) {
			$opts[$data['weblink_cat_id']] = $data['weblink_cat_name'];
		}
		echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
		echo "<div class='alert alert-info m-b-20 submission-guidelines'>".$locale['420']."</div>\n";
		echo openform('submit_form', 'post', ($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."submit.php?stype=l", array('max_tokens' => 1));
		echo form_select('link_category', $locale['421'], $opts, isset($_POST['link_category']) ? $_POST['link_category'] : '', array("required" => 1));
		echo form_text('link_name', $locale['422'], isset($_POST['link_name']) ? $_POST['link_name'] : '', array("required" => 1));
		echo form_text('link_url', $locale['423'], isset($_POST['link_url']) ? $_POST['link_url'] : '', array("required" => 1, 'placeholder' => 'http://'));
		echo form_text('link_description', $locale['424'], isset($_POST['link_description']) ? $_POST['link_description'] : '', array("required" => 1, 'max_length' => '200'));
		echo form_button('submit_link', $locale['425'], $locale['425'], array('class' => 'btn-primary'));
		echo closeform();
		echo "</div>\n</div>\n";
	} else {
		echo "<div class='well' style='text-align:center'><br />\n".$locale['551']."<br /><br />\n</div>\n";
	}
	closetable();
} elseif ($stype === "n") {
	if (isset($_POST['submit_news'])) {
		$submit_info['news_subject'] = form_sanitizer($_POST['news_subject'], '', 'news_subject');
		$submit_info['news_cat'] = isnum($_POST['news_cat']) ? $_POST['news_cat'] : "0";
		$submit_info['news_snippet'] = nl2br(parseubb(stripinput($_POST['news_snippet'])));
		$submit_info['news_snippet'] = form_sanitizer($submit_info['news_snippet'], '', 'news_snippet');
		$submit_info['news_body'] = nl2br(parseubb(stripinput($_POST['news_body'])));
		$submit_info['news_body'] = form_sanitizer($submit_info['news_body'], '', 'news_body');
		if (!defined('FUSION_NULL')) {
			$result = dbquery("INSERT INTO ".DB_SUBMISSIONS." (submit_type, submit_user, submit_datestamp, submit_criteria) VALUES('n', '".$userdata['user_id']."', '".time()."', '".addslashes(serialize($submit_info))."')");
			add_to_title($locale['global_200'].$locale['450']);
			opentable($locale['450']);
			echo "<div style='text-align:center'><br />\n".$locale['460']."<br /><br />\n";
			echo "<a href='submit.php?stype=n'>".$locale['461']."</a><br /><br />\n";
			echo "<a href='index.php'>".$locale['412']."</a><br /><br />\n</div>\n";
			closetable();
		}
	}

	if (isset($_POST['preview_news'])) {
		$news_subject = stripinput($_POST['news_subject']);
		$news_cat = isnum($_POST['news_cat']) ? $_POST['news_cat'] : "0";
		$news_snippet = stripinput($_POST['news_snippet']);
		$news_body = stripinput($_POST['news_body']);
		opentable($news_subject);
		echo $locale['478']." ".nl2br(parseubb($news_snippet))."<br /><br />";
		echo $locale['472']." ".nl2br(parseubb($news_body));
		closetable();
	} else {
		$news_subject = "";
		$news_cat = "0";
		$news_snippet = "";
		$news_body = "";
	}

	$result2 = dbquery("SELECT news_cat_id, news_cat_name, news_cat_language FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")." ORDER BY news_cat_name");

	if (dbrows($result2)) {
		$cat_list = array();
		while ($data2 = dbarray($result2)) {
			$cat_list[$data2['news_cat_id']] = $data2['news_cat_name'];
		}
	}
	add_to_title($locale['global_200'].$locale['450']);
	opentable($locale['450']);
	echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
	echo "<div class='alert alert-info m-b-20 submission-guidelines'>".$locale['470']."</div>\n";
	echo openform('submit_form', 'post', ($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."submit.php?stype=n", array('max_tokens' => 1));
	echo form_text('news_subject', $locale['471'], $news_subject, array("required" => 1));
	echo form_select('news_cat', $locale['476'], $cat_list, $news_cat, array("required" => 1));
	echo form_textarea('news_snippet', $locale['478'], $news_snippet, array('bbcode' => 1, 'form_name' => 'submit_form'));
	echo form_textarea('news_body', $locale['472'], $news_body, array("required" => 1, 'bbcode' => 1, 'form_name' => 'submit_form'));
	echo $settings['site_seo'] ? '' : form_button('preview_news', $locale['474'], $locale['474'], array('class' => 'btn-primary m-r-10'));
	echo form_button('submit_news', $locale['475'], $locale['475'], array('class' => 'btn-primary'));
	echo closeform();
	echo "</div>\n</div>\n";
	closetable();
} elseif ($stype === "b") {
	if (isset($_POST['submit_blog'])) {
		$submit_info['blog_subject'] = form_sanitizer($_POST['blog_subject'], '', 'blog_subject');
		$submit_info['blog_cat'] = isnum($_POST['blog_cat']) ? $_POST['blog_cat'] : "0";
		$submit_info['blog_snippet'] = nl2br(parseubb(stripinput($_POST['blog_snippet'])));
		$submit_info['blog_snippet'] = form_sanitizer($submit_info['blog_snippet'], '', 'blog_snippet');
		$submit_info['blog_body'] = nl2br(parseubb(stripinput($_POST['blog_body'])));
		$submit_info['blog_body'] = form_sanitizer($submit_info['blog_body'], '', 'blog_body');
		if (!defined('FUSION_NULL')) {
			$result = dbquery("INSERT INTO ".DB_SUBMISSIONS." (submit_type, submit_user, submit_datestamp, submit_criteria) VALUES('b', '".$userdata['user_id']."', '".time()."', '".addslashes(serialize($submit_info))."')");
			add_to_title($locale['global_200'].$locale['450b']);
			opentable($locale['450b']);
			echo "<div style='text-align:center'><br />\n".$locale['460b']."<br /><br />\n";
			echo "<a href='submit.php?stype=b'>".$locale['461b']."</a><br /><br />\n";
			echo "<a href='index.php'>".$locale['412b']."</a><br /><br />\n</div>\n";
			closetable();
		}
	}

	if (isset($_POST['preview_blog'])) {
		$blog_subject = stripinput($_POST['blog_subject']);
		$blog_cat = isnum($_POST['blog_cat']) ? $_POST['blog_cat'] : "0";
		$blog_snippet = stripinput($_POST['blog_snippet']);
		$blog_body = stripinput($_POST['blog_body']);
		opentable($blog_subject);
		echo $locale['478b']." ".nl2br(parseubb($blog_snippet))."<br /><br />";
		echo $locale['472b']." ".nl2br(parseubb($blog_body));
		closetable();
	} else {
		$blog_subject = "";
		$blog_cat = "0";
		$blog_snippet = "";
		$blog_body = "";
	}
	
	$result2 = dbquery("SELECT blog_cat_id, blog_cat_name, blog_cat_language FROM ".DB_BLOG_CATS." ".(multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."'" : "")." ORDER BY blog_cat_name");

	if (dbrows($result2)) {
		$cat_list = array();
		while ($data2 = dbarray($result2)) {
			$cat_list[$data2['blog_cat_id']] = $data2['blog_cat_name'];
		}
	}
	add_to_title($locale['global_200'].$locale['450b']);
	opentable($locale['450b']);
	echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
	echo "<div class='alert alert-info m-b-20 submission-guidelines'>".$locale['470b']."</div>\n";
	echo openform('submit_form', 'post', ($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."submit.php?stype=b", array('max_tokens' => 1));
	echo form_text('blog_subject', $locale['471b'], $blog_subject, array("required" => 1));
	echo form_select('blog_cat', $locale['476b'], $cat_list, $blog_cat, array("required" => 1));
	echo form_textarea('blog_snippet', $locale['478b'], $blog_snippet, array('bbcode' => 1, 'form_name' => 'submit_form'));
	echo form_textarea('blog_body', $locale['472b'], $blog_body, array("required" => 1, 'bbcode' => 1, 'form_name' => 'submit_form'));
	echo $settings['site_seo'] ? '' : form_button('preview_blog', $locale['474b'], $locale['474b'], array('class' => 'btn-primary m-r-10'));
	echo form_button('submit_blog', $locale['475b'], $locale['475b'], array('class' => 'btn-primary'));
	echo closeform();
	echo "</div>\n</div>\n";
	closetable();
} elseif ($stype === "a") {
	if (isset($_POST['submit_article'])) {
		if ($_POST['article_subject'] != "" && $_POST['article_body'] != "") {
			$submit_info['article_cat'] = isnum($_POST['article_cat']) ? $_POST['article_cat'] : "0";
			$submit_info['article_subject'] = stripinput($_POST['article_subject']);
			$submit_info['article_snippet'] = nl2br(parseubb(stripinput($_POST['article_snippet'])));
			$submit_info['article_body'] = nl2br(parseubb(stripinput($_POST['article_body'])));
			$result = dbquery("INSERT INTO ".DB_SUBMISSIONS." (submit_type, submit_user, submit_datestamp, submit_criteria) VALUES ('a', '".$userdata['user_id']."', '".time()."', '".addslashes(serialize($submit_info))."')");
			add_to_title($locale['global_200'].$locale['500']);
			opentable($locale['500']);
			echo "<div style='text-align:center'><br />\n".$locale['510']."<br /><br />\n";
			echo "<a href='submit.php?stype=a'>".$locale['511']."</a><br /><br />\n";
			echo "<a href='index.php'>".$locale['412']."</a><br /><br />\n</div>\n";
			closetable();
		}
	} else {
		if (isset($_POST['preview_article'])) {
			$article_cat = isnum($_POST['article_cat']) ? $_POST['article_cat'] : "0";
			$article_subject = stripinput($_POST['article_subject']);
			$article_snippet = stripinput($_POST['article_snippet']);
			$article_body = stripinput($_POST['article_body']);
			opentable($article_subject);
			echo $locale['523']." ".nl2br(parseubb($article_snippet))."<br /><br />";
			echo $locale['524']." ".nl2br(parseubb($article_body));
			closetable();
		}
		if (!isset($_POST['preview_article'])) {
			$article_cat = "0";
			$article_subject = "";
			$article_snippet = "";
			$article_body = "";
		}
		add_to_title($locale['global_200'].$locale['500']);
		opentable($locale['500']);
		$result = dbquery("SELECT article_cat_id, article_cat_name FROM ".DB_ARTICLE_CATS." ".(multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."'" : "")." ORDER BY article_cat_name");
		if (dbrows($result)) {
			$cat_list = array();
			while ($data = dbarray($result)) {
				if (isset($_POST['preview_article'])) {
					$sel = $article_cat == $data['article_cat_id'] ? " selected" : "";
				}
				$cat_list[$data['article_cat_id']] = $data['article_cat_name'];
			}
			echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
			echo "<div class='alert alert-info m-b-20 submission-guidelines'>".$locale['520']."</div>\n";
			echo openform('submit_form', 'post', ($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."submit.php?stype=a", array('max_tokens' => 1));
			echo form_select('article_cat', $locale['521'], $cat_list, isset($_POST['preview_article']) ? $_POST['preview_article'] : '');
			echo form_text('article_subject', $locale['522'], $article_subject, array('required' => 1));
			echo form_textarea('article_snippet', $locale['523'], $article_snippet, array('bbcode' => 1, 'required' => 1, 'form_name' => 'submit_form'));
			echo form_textarea('article_body', $locale['524'], $article_body, array('bbcode' => 1, 'required' => 1, 'form_name' => 'submit_form'));
			echo "</div>\n</div>\n";
			echo $settings['site_seo'] ? '' : form_button('preview_article', $locale['526'], 'preview_article', $locale['526'], array('class' => 'btn-primary m-r-10'));
			echo form_button('submit_article', $locale['527'], $locale['527'], array('class' => 'btn-primary'));
			echo closeform();
		} else {
			echo "<div class='well' style='text-align:center'><br />\n".$locale['551']."<br /><br />\n</div>\n";
		}
		closetable();
	}
} elseif ($stype === "p") {
	if (isset($_POST['submit_photo'])) {
		require_once INCLUDES."photo_functions_include.php";
		$error = "";
		$submit_info['photo_title'] = form_sanitizer($_POST['photo_title'], '', 'photo_title');
		$submit_info['photo_description'] = form_sanitizer($_POST['photo_description'], '', 'photo_description');
		$submit_info['album_id'] = isnum($_POST['album_id']) ? $_POST['album_id'] : "0";
		$submit_info['album_photo_file'] = form_sanitizer($_FILES['album_photo_file'], '', 'album_photo_file');
		add_to_title($locale['global_200'].$locale['570']);
		opentable($locale['570']);
		if (!defined('FUSION_NULL')) {
			$result = dbquery("INSERT INTO ".DB_SUBMISSIONS." (submit_type, submit_user, submit_datestamp, submit_criteria) VALUES ('p', '".$userdata['user_id']."', '".time()."', '".addslashes(serialize($submit_info))."')");
			echo "<div style='text-align:center'><br />\n".$locale['580']."<br /><br />\n";
			echo "<a href='submit.php?stype=p'>".$locale['581']."</a><br /><br />\n";
			echo "<a href='index.php'>".$locale['412']."</a><br /><br />\n</div>\n";
		} else {
			echo "<div style='text-align:center'><br />\n".$locale['600']."<br /><br />\n";
			echo "<br /><br />\n<a href='submit.php?stype=p'>".$locale['581']."</a><br /><br />\n</div>\n";
		}
		closetable();
	}
	$opts = "";
	add_to_title($locale['global_200'].$locale['570']);
	opentable($locale['570']);
	$result = dbquery("SELECT album_id, album_title FROM ".DB_PHOTO_ALBUMS." ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess("album_access")." ORDER BY album_title");
	if (dbrows($result)) {
		$opts = array();
		while ($data = dbarray($result)) {
			$opts[$data['album_id']] = $data['album_title'];
		}
		echo openform('submit_form', 'post', ($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."submit.php?stype=p", array('enc_type' => 1, 'max_tokens' => 1));
		echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
		echo "<div class='alert alert-info m-b-20 submission-guidelines'>".$locale['620']."</div>\n";
		echo form_select( 'album_id', $locale['625'], $opts, '');
		echo form_text('photo_title', $locale['621'], '', array('required' => 1));
		echo form_textarea('photo_description', $locale['622'], '');
		echo sprintf($locale['624'], parsebytesize($settings['photo_max_b']), $settings['photo_max_w'], $settings['photo_max_h'])."<br/>\n";
		echo form_fileinput($locale['623'], 'photo_pic_file', 'photo_pic_file', PHOTOS."submissions/", '', array('type' => 'image', 'required' => 1));
		echo "</div>\n</div>\n";
		echo form_button('submit_photo', $locale['626'], $locale['626'], array('class' => 'btn-primary'));
		echo closeform();
	} else {
		echo "<div class='well' style='text-align:center'><br />\n".$locale['552']."<br /><br />\n</div>\n";
	}
	closetable();
} elseif ($stype === "d") {

	add_to_title($locale['global_200'].$locale['650']);

	if (isset($_POST['submit_download'])) {
		$error = 0;

		$submit_info = array(
			'download_title' => form_sanitizer($_POST['download_title'], '', 'download_title'),
			'download_description' => form_sanitizer($_POST['download_description'], '', 'download_description'),
			'download_description_short' => form_sanitizer($_POST['download_description_short'], '', 'download_description_short'),
			'download_cat' => form_sanitizer($_POST['download_cat'], '0', 'download_cat'),
			'download_homepage' => form_sanitizer($_POST['download_homepage'], '', 'download_homepage'),
			'download_license' => form_sanitizer($_POST['download_license'], '', 'download_license'),
			'download_copyright' => form_sanitizer($_POST['download_copyright'], '', 'download_copyright'),
			'download_os' => form_sanitizer($_POST['download_os'], '', 'download_os'),
			'download_version' => form_sanitizer($_POST['download_version'], '', 'download_version'),
			'download_file' => '',
			'download_url' => '',
		);
		/**
		 * Download File Section
		 */
		if (isset($_FILES['download_file'])) {
			$upload = form_sanitizer($_FILES['download_file'], '', 'download_file');
			if ($upload) {
				$submit_info['download_file'] = $upload['target_file'];
				$submit_info['download_filesize'] = parsebytesize($_FILES['download_file']['size']);

			}
			unset($upload);
		} elseif (isset($_POST['download_url']) && $_POST['download_url'] != "") {
			$submit_info['download_url'] = form_sanitizer($_POST['download_url'], '', 'download_url');
		}

		if (isset($_FILES['download_image'])) {
			$upload = form_sanitizer($_FILES['download_image'], '', 'download_image');
			if ($upload) {
				$submit_info['download_image'] = $upload['image_name'];
				$submit_info['download_image_thumb'] = $upload['thumb1_name'];
				unset($upload);
			}
		}

		// Break form and return errors
		if (!$submit_info['download_file'] && !$submit_info['download_url']) {
			$defender->stop();
			$defender->addNotice($locale['675']);
		}


		if (!defined("FUSION_NULL")) {
			opentable($locale['650']);
			// this is what goes into DB_SUBMISSIONS
			$data = array(
				'submit_type' => 'd',
				'submit_user' => $userdata['user_id'],
				'submit_datestamp' => time(),
				'submit_criteria' => serialize($submit_info),
			);
			$result = dbquery_insert(DB_SUBMISSIONS, $data, 'save');
			if ($result) {
				echo "<div class='well'>\n";
				echo "<p>".$locale['660']."</p>";
				echo "<a href='submit.php?stype=d'>".$locale['661']."</a><br />";
				echo "<a href='index.php'>".$locale['412']."</a>\n<br/>";
				echo "<a href='submit.php?stype=d'>".$locale['661']."</a>\n";
				echo "</div>\n";
			}
			closetable();
		}
	}

	add_to_title($locale['global_200'].$locale['650']);
	opentable($locale['650']);
	$result = dbquery("SELECT download_cat_id, download_cat_name FROM ".DB_DOWNLOAD_CATS." ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")." ORDER BY download_cat_name");
	if (dbrows($result)) {
		$opts = array();
		while ($data = dbarray($result)) {
			$opts[$data['download_cat_id']] = $data['download_cat_name'];
		}
		echo openform('submit_form', 'post', ($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."submit.php?stype=d", array('enctype' => 1, 'max_tokens' => 1));
		echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
		echo "<div class='alert alert-info m-b-20 submission-guidelines'>".$locale['680']."</div>\n";
		echo form_text('download_title', $locale['681'], '', array('required' => 1, 'error_text' => $locale['674']));
		echo form_textarea('download_description_short', $locale['682b'], '', array('bbcode' => 1, 'required' => 1, 'error_text' => $locale['676'],  'form_name' => 'submit_form'));
		echo form_textarea('download_description', $locale['682'], '', array('bbcode' =>1, 'form_name' => 'submit_form'));
		echo form_text('download_url', $locale['683'], '', array('error_text' => $locale['675']));
		echo "<div class='pull-right'>\n<small>\n";
		echo sprintf($locale['694'], parsebytesize($settings['download_max_b']), str_replace(',', ' ', $settings['download_types']))."<br />\n";
		echo "</small>\n</div>\n";
		$file_options = array(
			'max_bytes' => $settings['download_max_b'],
			'valid_ext' => $settings['download_types'],
			'error_text' => $locale['675'],
		);
		echo form_fileinput($locale['684'], 'download_file', 'download_file', DOWNLOADS."submissions/", '', $file_options);
		echo "<div class='pull-right'>\n<small>\n";
		echo sprintf($locale['694b'], parsebytesize($settings['download_screen_max_b']), str_replace(',', ' ', ".jpg,.gif,.png"), $settings['download_screen_max_w'], $settings['download_screen_max_h'])."<br />\n";
		echo "</small>\n</div>\n";
		$file_options = array(
			'max_width' => $settings['download_screen_max_w'],
			'max_height' => $settings['download_screen_max_w'],
			'max_byte' => $settings['download_screen_max_b'],
			'type' => 'image',
			'delete_original' => 0,
			'thumbnail_folder' => '',
			'thumbnail' => 1,
			'thumbnail_suffix'=> '_thumb',
			'thumbnail_w'=> $settings['download_thumb_max_w'],
			'thumbnail_h' => $settings['download_thumb_max_h'],
			'thumbnail2' => 0
		);
		echo form_fileinput($locale['686'], 'download_image', 'download_image', DOWNLOADS."submissions/images/", '', $file_options);
		echo form_select('download_cat', $locale['687'], $opts, '');
		echo form_text('download_license', $locale['688'], '');
		echo form_text('download_os', $locale['689'], '');
		echo form_text('download_version', $locale['690'], '');
		echo form_text('download_homepage', $locale['691'], '');
		echo form_text('download_copyright', $locale['692'], '');
		echo form_hidden('', 'calc_upload', 'calc_upload', '1');
		echo "</div>\n</div>\n";
		echo form_button('submit_download', $locale['695'], $locale['695'], array('class' => 'btn-primary'));
		echo closeform();
	} else {
		echo "<div class='well' style='text-align:center'><br />\n".$locale['551']."<br /><br />\n</div>\n";
	}
	closetable();
}
require_once THEMES."templates/footer.php";
?>