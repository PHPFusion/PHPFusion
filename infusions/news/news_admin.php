<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news_admin.php
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
require_once "../../maincore.php";
pageAccess('N');

require_once THEMES."templates/admin_header.php";
include INFUSIONS."news/locale/".LOCALESET."news_admin.php";
require_once INCLUDES."infusions_include.php";
$news_settings = get_settings("news");

add_breadcrumb(array('link'=>FUSION_SELF.$aidlink, 'title'=>$locale['news_0000']));

if (isset($_POST['cancel'])) { redirect(FUSION_SELF.$aidlink); }
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['news_id']) && isnum($_GET['news_id'])) {
	$del_data['news_id'] = $_GET['news_id'];
	$result = dbquery("SELECT news_image, news_image_t1, news_image_t2 FROM ".DB_NEWS." WHERE news_id='".$del_data['news_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		if (!empty($data['news_image']) && file_exists(IMAGES_N.$data['news_image'])) {
			unlink(IMAGES_N.$data['news_image']);
		}
		if (!empty($data['news_image_t1']) && file_exists(IMAGES_N_T.$data['news_image_t1'])) {
			unlink(IMAGES_N_T.$data['news_image_t1']);
		}
		if (!empty($data['news_image_t2']) && file_exists(IMAGES_N_T.$data['news_image_t2'])) {
			unlink(IMAGES_N_T.$data['news_image_t2']);
		}
		$result = dbquery("DELETE FROM ".DB_NEWS." WHERE news_id='".$del_data['news_id'] ."'");
		$result = dbquery("DELETE FROM ".DB_COMMENTS."  WHERE comment_item_id='".$del_data['news_id'] ."' and comment_type='N'");
		$result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='".$del_data['news_id'] ."' and rating_type='N'");
		dbquery_insert(DB_NEWS, $del_data, 'delete');
		addNotice('warning', $locale['news_0102']);
		redirect(FUSION_SELF.$aidlink);
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}

$allowed_pages = array('news', 'nform', 'sform');

$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_pages) ? $_GET['section'] : 'news';
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['news_id']) && isnum($_GET['news_id'])) ? 1 : 0;

$master_title['title'][] = $locale['news_0000'];
$master_title['id'][] = 'news';
$master_title['icon'] = '';

$master_title['title'][] = $edit ? $locale['news_0003'] : $locale['news_0002'];
$master_title['id'][] = 'nform';
$master_title['icon'] = '';

$master_title['title'][] = isset($_GET['settings']) ? $locale['news_0004'] : $locale['news_0004'];
$master_title['id'][] = 'sform';
$master_title['icon'] = '';

$tab_active =  tab_active($master_title, $_GET['section'] , 1);

opentable($locale['news_0001']);
echo opentab($master_title, $tab_active, 'news', 1);

echo opentabbody($master_title['title'][0], 'news', $tab_active, 1);
news_listing();
echo closetabbody();

if (isset($_GET['section']) && $_GET['section'] == 'nform') {
	add_breadcrumb(array('link'=>'', 'title'=>$edit ? $locale['news_0003'] : $locale['news_0002']));
	echo opentabbody($master_title['title'][1], 'nform', $tab_active, 1);
	news_form();
	echo closetabbody();
} elseif (isset($_GET['section']) && $_GET['section'] == 'sform') {
include LOCALE.LOCALESET."admin/settings.php";
add_breadcrumb(array('link'=>'', 'title'=>$locale['news_settings']));
	echo opentabbody($master_title['title'][2], 'sform', $tab_active, 1);
if (isset($_POST['savesettings'])) {
	$error = 0;
	$news_pagination = form_sanitizer($_POST['news_pagination'], '12', 'news_pagination');
	$news_image_link = form_sanitizer($_POST['news_image_link'], '0', 'news_image_link');
	$news_image_frontpage = form_sanitizer($_POST['news_image_frontpage'], '0', 'news_image_frontpage');
	$news_image_readmore = form_sanitizer($_POST['news_image_readmore'], '0', 'news_image_readmore');
	$news_thumb_ratio = form_sanitizer($_POST['news_thumb_ratio'], '0', 'news_thumb_ratio');
	$news_thumb_w = form_sanitizer($_POST['news_thumb_w'], '300', 'news_thumb_w');
	$news_thumb_h = form_sanitizer($_POST['news_thumb_h'], '150', 'news_thumb_h');
	$news_photo_w = form_sanitizer($_POST['news_photo_w'], '400', 'news_photo_w');
	$news_photo_h = form_sanitizer($_POST['news_photo_h'], '300', 'news_photo_h');
	$news_photo_max_w = form_sanitizer($_POST['news_photo_max_w'], '1800', 'news_photo_max_w');
	$news_photo_max_h = form_sanitizer($_POST['news_photo_max_h'], '1600', 'news_photo_max_h');
	$news_photo_max_b = form_sanitizer($_POST['calc_b'], '150', 'calc_b')*form_sanitizer($_POST['calc_c'], '100000', 'calc_c');
	if (!defined('FUSION_NULL')) {
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$news_image_link' WHERE settings_name='news_image_link'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$news_image_frontpage' WHERE settings_name='news_image_frontpage'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$news_image_readmore' WHERE settings_name='news_image_readmore'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$news_thumb_ratio' WHERE settings_name='news_thumb_ratio'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$news_thumb_w' WHERE settings_name='news_thumb_w'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$news_thumb_h' WHERE settings_name='news_thumb_h'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$news_photo_w' WHERE settings_name='news_photo_w'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$news_photo_h' WHERE settings_name='news_photo_h'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$news_photo_max_w' WHERE settings_name='news_photo_max_w'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$news_photo_max_h' WHERE settings_name='news_photo_max_h'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$news_photo_max_b' WHERE settings_name='news_photo_max_b'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$news_pagination' WHERE settings_name='news_pagination'");
		if (!$result) {
			$error = 1;
		}
		if ($error) {
			addNotice('danger', $locale['901']);
		} else {
			addNotice('success', $locale['900']);
		}
		redirect(FUSION_SELF.$aidlink."&amp;section=sform&amp;settings");
	}
}

$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS_INF);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}

opentable($locale['news_settings']);
echo "<div class='well'>".$locale['news_description']."</div>";
$formaction = FUSION_SELF.$aidlink."&amp;section=sform&amp;settings";
echo openform('settingsform', 'post', $formaction, array('max_tokens' => 1));
$opts = array('0' => $locale['952'], '1' => $locale['953']);
$cat_opts = array('0' => $locale['959'], '1' => $locale['960']);
$thumb_opts = array('0' => $locale['955'], '1' => $locale['956']);
$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
$calc_c = calculate_byte($settings2['news_photo_max_b']);
$calc_b = $settings2['news_photo_max_b']/$calc_c;

echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-8'>\n";
openside('');
echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='news_pagination'>".$locale['669c']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('news_pagination', '', $settings2['news_pagination'], array('class' => 'pull-left', 'max_length' => 4, 'number'=>1, 'width'=>'150px'))."
	</div>
	<div class='col-xs-12 col-sm-3'>
	<label for='blog_thumb_w'>".$locale['601']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('news_thumb_w', '', $settings2['news_thumb_w'], array('class' => 'pull-left', 'max_length' => 4, 'number'=>1, 'width'=>'150px'))."
	<i class='entypo icancel pull-left m-r-10 m-l-0 m-t-10'></i>
	".form_text('news_thumb_h', '', $settings2['news_thumb_h'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1, 'width'=>'150px'))."
	<small class='m-l-10 mid-opacity text-uppercase pull-left m-t-10'>( ".$locale['604']." )</small>
	</div>
</div>";
echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='news_photo_w'>".$locale['602']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('news_photo_w', '', $settings2['news_photo_w'], array('class' => 'pull-left', 'max_length' => 4, 'number'=>1, 'width'=>'150px'))."
	<i class='entypo icancel pull-left m-r-10 m-l-0 m-t-10'></i>
	".form_text('news_photo_h', '', $settings2['news_photo_h'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1, 'width'=>'150px'))."
	<small class='m-l-10 mid-opacity text-uppercase pull-left m-t-10'>( ".$locale['604']." )</small>
	</div>
</div>";
echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='blog_thumb_w'>".$locale['603']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('news_photo_max_w', '', $settings2['news_photo_max_w'], array('class' => 'pull-left', 'max_length' => 4, 'number'=>1, 'width'=>'150px'))."
	<i class='entypo icancel pull-left m-r-10 m-l-0 m-t-10'></i>
	".form_text('news_photo_max_h', '', $settings2['news_photo_max_h'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1, 'width'=>'150px'))."
	<small class='m-l-10 mid-opacity text-uppercase pull-left m-t-10'>( ".$locale['604']." )</small>
	</div>
</div>";
echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='calc_b'>".$locale['605']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('calc_b', '', $calc_b, array('required' => 1, 'number' => 1, 'error_text' => $locale['error_rate'], 'width' => '100px', 'max_length' => 4, 'class' => 'pull-left m-r-10'))."
	".form_select('calc_c', '', $calc_c, array('options' => $calc_opts,
		'placeholder' => $locale['choose'],
		'class' => 'pull-left',
		'width' => '180px'))."
	</div>
</div>
";
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-4'>\n";
openside('');
	echo form_select('news_image_link', $locale['951'], $settings2['news_image_link'], array("options" => $opts));
	echo form_select('news_image_frontpage', $locale['957'], $settings2['news_image_frontpage'], array("options" => $cat_opts));
	echo form_select('news_image_readmore', $locale['958'], $settings2['news_image_readmore'], array("options" => $cat_opts));
	echo form_select('news_thumb_ratio', $locale['954'], $settings2['news_thumb_ratio'], array("options" => $thumb_opts));
	closeside();
echo "</div></div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-primary'));

echo closeform();
closetable();
echo closetabbody();
}
echo closetab();
closetable();


if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['news_id'])) {
	add_to_jquery("
		// change the name of the second tab and activate it.
		$('#tab-nformAdd-News').text('".$locale['news_0003']."');
		$('#news a:last').tab('show');
		");
}

require_once THEMES."templates/footer.php";

function news_listing() {
	global $aidlink, $locale;
	echo "<div class='m-t-20'>\n";
	echo opencollapse('news-list');
	// uncategorized listing
	echo "<div class='panel panel-default'>\n";
	echo "<div class='panel-heading clearfix'>\n";
	echo "<div class='overflow-hide'>\n";
	echo "<span class='display-inline-block strong'><a ".collapse_header_link('news-list', '0', '0', 'm-r-10').">".$locale['news_0202']."</a></span>\n";
	echo "<span class='badge m-r-10'>".dbcount("(news_id)", DB_NEWS, "news_cat='0'")."</span>";
	echo "<span class='text-smaller mid-opacity'>".LANGUAGE."</span>";
	echo "</div>\n";
	echo "</div>\n"; // end panel heading
	echo "<div ".collapse_footer_link('news-list','0', '0').">\n";
	echo "<ul class='list-group p-15'>\n";
	$result2 = dbquery("SELECT news_id, news_subject, news_image_t1, news_news, news_draft FROM ".DB_NEWS." WHERE ".(multilang_table("NS") ? "news_language='".LANGUAGE."' AND " : "")."news_cat='0' ORDER BY news_draft DESC, news_sticky DESC, news_datestamp DESC");
	if (dbrows($result2) > 0) {
		while ($data2 = dbarray($result2)) {
			echo "<li class='list-group-item'>\n";
			echo "<div class='pull-left m-r-10'>\n";
			$img_thumb = ($data2['news_image_t1']) ? IMAGES_N_T.$data2['news_image_t1'] : IMAGES."imagenotfound70.jpg";
			echo thumbnail($img_thumb, '50px');
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo "<div><span class='strong text-dark'>".$data2['news_subject']."</span><br/>".fusion_first_words($data2['news_news'], '50')."</div>\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;section=nform&amp;news_id=".$data2['news_id']."'>".$locale['edit']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;news_id=".$data2['news_id']."' onclick=\"return confirm('".$locale['news_0251']."');\">".$locale['delete']."</a>\n";
			echo "</div>\n";
			echo "</li>\n";
		}
	} else {
		echo "<div class='panel-body text-center'>\n";
		echo $locale['news_0254'];
		echo "</div>\n";
	}
	// news listing.
	echo "</ul>\n";
	echo "</div>\n"; // panel container
	echo "</div>\n"; // panel default

	$result = dbquery("SELECT cat.news_cat_id, cat.news_cat_name, cat.news_cat_image, cat.news_cat_language, count(news.news_id) as news_count
				FROM ".DB_NEWS_CATS." cat
				LEFT JOIN ".DB_NEWS." news on news.news_cat = cat.news_cat_id
				".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")." GROUP BY news_cat_id ORDER BY news_cat_name");
	if (dbrows($result) > 0) {
		while ($data = dbarray($result)) {
			echo "<div class='panel panel-default'>\n";
			echo "<div class='panel-heading clearfix'>\n";
			echo "<div class='btn-group pull-right m-t-5'>\n";
			echo "<a class='btn btn btn-default' href='".ADMIN."news_cats.php".$aidlink."&amp;action=edit&amp;section=nform&amp;cat_id=".$data['news_cat_id']."'>".$locale['edit']."</a>";
			echo "<a class='btn btn-danger' href='".ADMIN."news_cats.php".$aidlink."&amp;action=delete&amp;cat_id=".$data['news_cat_id']."' onclick=\"return confirm('".$locale['news_0252']."');\"><i class='fa fa-trash'></i> ".$locale['delete']."</a>\n";
			echo "</div>\n";
			echo "<div class='overflow-hide p-r-10'>\n";
			echo "<span class='display-inline-block strong'><a ".collapse_header_link('news-list', $data['news_cat_id'], '0', 'm-r-10').">".$data['news_cat_name']."</a></span>\n";
			echo "<span class='badge m-r-10'>".$data['news_count']."</span>";
			echo "<span class='text-smaller mid-opacity'>".LANGUAGE."</span>";
			echo "</div>\n"; /// end overflow-hide
			echo "</div>\n"; // end panel heading
			echo "<div ".collapse_footer_link('news-list', $data['news_cat_id'], '0').">\n";
			echo "<ul class='list-group p-15'>\n";
			$result2 = dbquery("SELECT news_id, news_subject, news_image_t1, news_news, news_draft FROM ".DB_NEWS." ".(multilang_table("NS") ? "WHERE news_language='".LANGUAGE."' AND" : "WHERE")." news_cat='".$data['news_cat_id']."' ORDER BY news_draft DESC, news_sticky DESC, news_datestamp DESC");
			if (dbrows($result2) > 0) {
				while ($data2 = dbarray($result2)) {
					echo "<li class='list-group-item'>\n";
					echo "<div class='pull-left m-r-10'>\n";
					$img_thumb = ($data2['news_image_t1']) ? IMAGES_N_T.$data2['news_image_t1'] : IMAGES."imagenotfound70.jpg";
					echo thumbnail($img_thumb, '50px');
					echo "</div>\n";
					echo "<div class='overflow-hide'>\n";
					echo "<div><span class='strong text-dark'>".$data2['news_subject']."</span><br/>".fusion_first_words($data2['news_news'], '50')."</div>\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;section=nform&amp;news_id=".$data2['news_id']."'>".$locale['edit']."</a> -\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;section=nform&amp;news_id=".$data2['news_id']."' onclick=\"return confirm('".$locale['news_0251']."');\">".$locale['delete']."</a>\n";
					echo "</div>\n";
					echo "</li>\n";
				}
			} else {
				echo "<div class='panel-body text-center'>\n";
				echo $locale['news_0254'];
				echo "</div>\n";
			}
			// news listing.
			echo "</ul>\n";
			echo "</div>\n"; // panel container
			echo "</div>\n"; // panel default
		}
	}
	echo closecollapse();
	echo "</div>\n";
}

function news_form() {
	global $userdata, $locale, $news_settings, $settings, $aidlink, $defender;
	$language_opts = fusion_get_enabled_languages();
	$formaction = FUSION_SELF.$aidlink."&amp;section=nform";

	$data = array(
		'news_draft' => 0,
		'news_sticky' => 0,
		'news_news' => '',
		'news_datestamp' => time(),
		'news_extended' => '',
		'news_keywords' => '',
		'news_breaks' => 'n',
		'news_allow_comments' => 1,
		'news_allow_ratings' => 1,
		'news_language' => LANGUAGE,
		'news_visibility' => 0,
		'news_subject' => '',
		'news_start' => '',
		'news_end' => '',
		'news_cat'	=> 0,
		'news_image'	=> '',
		'news_ialign' => 'pull-left',
	);


	if ($settings['tinymce_enabled']) {
		echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
		$data['news_breaks'] = 'n';
	} else {
		require_once INCLUDES."html_buttons_include.php";
		$data['news_breaks'] = 'y';
	}


	if (isset($_POST['save'])) {

		$data = array(
			'news_id' => isset($_POST['news_id']) ? form_sanitizer($_POST['news_id'], '', 'news_id') : '',
			'news_subject' => form_sanitizer($_POST['news_subject'], '', 'news_subject'),
			'news_cat' => form_sanitizer($_POST['news_cat'], 0, 'news_cat'),
			'news_name' =>  $userdata['user_id'],
			'news_news' =>	addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['news_news'])),
			'news_extended' =>	addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['news_extended'])),
			'news_keywords'	=>	form_sanitizer($_POST['news_keywords'], '', 'news_keywords'),
			'news_datestamp' => form_sanitizer($_POST['news_datestamp'], time(), 'news_datestamp'),
			'news_start' => form_sanitizer($_POST['news_start'], 0, 'news_start'),
			'news_end' => form_sanitizer($_POST['news_end'], 0, 'news_end'),
			'news_visibility' => form_sanitizer($_POST['news_visibility'], 0, 'news_visibility'),
			'news_draft' => isset($_POST['news_draft']) ? "1" : "0",
			'news_sticky' => isset($_POST['news_sticky']) ? "1" : "0",
			'news_allow_comments' => isset($_POST['news_allow_comments']) ? "1" : "0",
			'news_allow_ratings' => isset($_POST['news_allow_ratings']) ? "1" : "0",
			'news_language' => form_sanitizer($_POST['news_language'], '', 'news_language')
		);

		if (isset($_FILES['news_image'])) {
			$upload = form_sanitizer($_FILES['news_image'], '', 'news_image');
			if (!empty($upload)) {
				$data['news_image'] = $upload['image_name'];
				$data['news_image_t1'] = $upload['thumb1_name'];
				$data['news_image_t2'] = $upload['thumb2_name'];
				$data['news_ialign'] = (isset($_POST['news_ialign']) ? $_POST['news_ialign'] : "pull-left");
			} else {
				$data['news_image'] = (isset($_POST['news_image']) ? $_POST['news_image'] : "");
				$data['news_image_t1'] = (isset($_POST['news_image_t1']) ? $_POST['news_image_t1'] : "");
				$data['news_image_t2'] = (isset($_POST['news_image_t2']) ? $_POST['news_image_t2'] : "");
				$data['news_ialign'] = (isset($_POST['news_ialign']) ? $_POST['news_ialign'] : "pull-left");
			}
		}

		if ($settings['tinymce_enabled'] != 1) {
			$data['news_breaks'] = isset($_POST['line_breaks']) ? "y" : "n";
		} else {
			$data['news_breaks'] = "n";
		}

		if ($data['news_sticky'] == "1") $result = dbquery("UPDATE ".DB_NEWS." SET news_sticky='0' WHERE news_sticky='1'"); // reset other sticky
		// delete image
		if (isset($_POST['del_image'])) {
			if (!empty($data['news_image']) && file_exists(IMAGES_N.$data['news_image'])) {
				unlink(IMAGES_N.$data['news_image']);
			}
			if (!empty($data['news_image_t1']) && file_exists(IMAGES_N_T.$data['news_image_t1'])) {
				unlink(IMAGES_N_T.$data['news_image_t1']);
			}
			if (!empty($data['news_image_t2']) && file_exists(IMAGES_N_T.$data['news_image_t2'])) {
				unlink(IMAGES_N_T.$data['news_image_t2']);
			}
			$data['news_image'] = "";
			$data['news_image_t1'] = "";
			$data['news_image_t2'] = "";
		}

		$rows = dbcount("('news_id')", DB_NEWS, "news_id='".$data['news_id']."'");
		if ($rows >0) {
			dbquery_insert(DB_NEWS, $data, 'update');
			addNotice('info', $locale['news_0101']);
			if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;status=su");
		} else {
			dbquery_insert(DB_NEWS, $data, 'save');
			addNotice('sucess', $locale['news_0100']);
			if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;status=sn");
		}
	}

	$result = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")." ORDER BY news_cat_name");
	$news_cat_opts = array();
	$news_cat_opts['0'] = $locale['news_0202'];
	if (dbrows($result)) {
		while ($odata = dbarray($result)) {
			$news_cat_opts[$odata['news_cat_id']] = $odata['news_cat_name'];
		}
	}

	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['news_id']) && isnum($_POST['news_id'])) || (isset($_GET['news_id']) && isnum($_GET['news_id']))) {
		$result = dbquery("SELECT * FROM ".DB_NEWS." WHERE news_id='".(isset($_POST['news_id']) ? $_POST['news_id'] : $_GET['news_id'])."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$formaction = FUSION_SELF.$aidlink."&amp;section=nform&amp;action=edit&amp;news_id=".$data['news_id'];
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}

	if (isset($_POST['preview'])) {
		$data['news_subject'] = form_sanitizer($_POST['news_subject'], '', 'news_subject');
		$data['news_cat'] = isnum($_POST['news_cat']) ? $_POST['news_cat'] : "0";
		$data['news_language'] = form_sanitizer($_POST['news_language'], '', 'news_language');
		$data['news_news'] = phpentities(stripslash($_POST['news_news']));
		$data['news_news'] = str_replace("src='".str_replace("../", "", IMAGES_N), "src='".IMAGES_N, stripslash($_POST['news_news']));
		$data['news_extended'] = '';
		if ($_POST['news_extended']) {
			$data['news_extended'] = phpentities(stripslash($_POST['news_extended']));
			$data['news_extended'] = str_replace("src='".str_replace("../", "", IMAGES_N), "src='".IMAGES_N, stripslash($_POST['news_extended']));
		}
		$data['news_keywords'] = form_sanitizer($_POST['news_keywords'], '', 'news_keywords');
		$data['news_breaks'] = "";
		if (isset($_POST['line_breaks'])) {
			$data['news_breaks'] = " 1";
			$data['news_news'] = nl2br($data['news_news']);
			if ($data['news_extended']) {
				$data['news_extended'] = nl2br($data['news_extended']);
			}
		}
		$data['news_start'] = (isset($_POST['news_start']) && $_POST['news_start']) ? $_POST['news_start'] : '';
		$data['news_end'] = (isset($_POST['news_end']) && $_POST['news_end']) ? $_POST['news_end'] : '';
		$data['news_image'] = isset($_POST['news_image']) ? $_POST['news_image'] : '';
		$data['news_image_t1'] = (isset($_POST['news_image_t1']) ? $_POST['news_image_t1'] : "");
		$data['news_image_t2'] = (isset($_POST['news_image_t2']) ? $_POST['news_image_t2'] : "");
		$data['news_ialign'] = (isset($_POST['news_ialign']) ? $_POST['news_ialign'] : "pull-left");
		$data['news_visibility'] = isnum($_POST['news_visibility']) ? $_POST['news_visibility'] : "0";
		$data['news_draft'] = isset($_POST['news_draft']) ? " 1" : "";
		$data['news_sticky'] = isset($_POST['news_sticky']) ? " 1" : "";
		$data['news_allow_comments'] = isset($_POST['news_allow_comments']) ? " 1" : "";
		$data['news_allow_ratings'] = isset($_POST['news_allow_ratings']) ? " 1" : "";
		$data['news_datestamp'] = isset($_POST['news_datestamp']) ? $_POST['news_datestamp'] : '';
		if (!defined('FUSION_NULL')) {
			echo openmodal('news_preview', 'News Preview');
			echo $data['news_news'];
			echo "<hr/>\n";
			if (isset($data['news_extended'])) {
				echo $data['news_extended'];
			}
			echo closemodal();
		}
	}

	echo "<div class='m-t-20'>\n";
	echo openform('inputform', 'post', $formaction, array('enctype' => 1, 'max_tokens' => 1));
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
	echo form_text('news_subject', $locale['news_0200'], $data['news_subject'], array('required' => 1, 'max_length' => 200, 'error_text' => $locale['news_0250']));
	// move keywords here because it's required
	echo form_select('news_keywords', $locale['news_0205'], $data['news_keywords'], array('max_length' => 320,
		'width' => '100%',
		'error_text' => $locale['news_0255'],
		'tags' => 1,
		'multiple' => 1));
	echo "<div class='pull-left m-r-10 display-inline-block'>\n";
	echo form_datepicker('news_start', $locale['news_0206'], $data['news_start'], array('placeholder' => $locale['news_0208']));
	echo "</div>\n<div class='pull-left m-r-10 display-inline-block'>\n";
	echo form_datepicker('news_end', $locale['news_0207'], $data['news_end'], array('placeholder' => $locale['news_0208']));
	echo "</div>\n";
	echo "</div>\n";
	echo "<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
	openside('');
	echo form_select_tree("news_cat", $locale['news_0201'], $data['news_cat'], array("parent_value" => $locale['news_0202'], "query" => (multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")), DB_NEWS_CATS, "news_cat_name", "news_cat_id", "news_cat_parent");
	echo form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default m-r-10'));
	echo form_button('save', $locale['news_0241'], $locale['news_0241'], array('class' => 'btn-success', 'icon'=>'fa fa-square-check-o'));
	closeside();
	echo "</div>\n</div>\n";

	// second row
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
	openside('');
	if ($data['news_image'] != "" && $data['news_image_t1'] != "") {
		echo "<label><img src='".IMAGES_N_T.$data['news_image_t1']."' alt='".$locale['news_0216']."' /><br />\n";
		echo "<input type='checkbox' name='del_image' value='y' /> ".$locale['delete']."</label>\n";
		echo "<input type='hidden' name='news_image' value='".$data['news_image']."' />\n";
		echo "<input type='hidden' name='news_image_t1' value='".$data['news_image_t1']."' />\n";
		echo "<input type='hidden' name='news_image_t2' value='".$data['news_image_t2']."' />\n";
		$alignOptions = array('pull-left' => $locale['left'],
			'news-img-center' => $locale['center'],
			'pull-right' => $locale['right']);
		echo form_select('news_ialign', $locale['news_0218'], $data['news_ialign'], array("options" => $alignOptions));
	} else {
		$file_input_options = array(
			'upload_path' => IMAGES_N,
			'max_width' => $news_settings['news_photo_max_w'],
			'max_height' => $news_settings['news_photo_max_h'],
			'max_byte' => $news_settings['news_photo_max_b'],
			// set thumbnail
			'thumbnail' => 1,
			'thumbnail_w' => $news_settings['news_thumb_w'],
			'thumbnail_h' => $news_settings['news_thumb_h'],
			'thumbnail_folder' => 'thumbs',
			'delete_original' => 0,
			// set thumbnail 2 settings
			'thumbnail2' => 1,
			'thumbnail2_w' => $news_settings['news_photo_w'],
			'thumbnail2_h' => $news_settings['news_photo_h'],
			'type' => 'image'
		);
		echo form_fileinput("news_image", $locale['news_0216'], "", $file_input_options);
		echo "<div class='small m-b-10'>".sprintf($locale['news_0217'], parsebytesize($news_settings['news_photo_max_b']))."</div>\n";
		$alignOptions = array('pull-left' => $locale['left'],
			'news-img-center' => $locale['center'],
			'pull-right' => $locale['right']);
		echo form_select('news_ialign', $locale['news_0218'], $data['news_ialign'], array("options" => $alignOptions));
	}
	$fusion_mce = array();
	if (!$settings['tinymce_enabled']) {
		$fusion_mce = array('preview' => 1, 'html' => 1, 'autosize' => 1, 'form_name' => 'inputform');
	}
	closeside();
	echo form_textarea('news_news', $locale['news_0203'], $data['news_news'], $fusion_mce);
	echo form_textarea('news_extended', $locale['news_0204'], $data['news_extended'], $fusion_mce);
	echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
	openside('');
	if (multilang_table("NS")) {
		echo form_select('news_language', $locale['global_ML100'], $data['news_language'], array('options' => fusion_get_enabled_languages(),
			'placeholder' => $locale['choose'],
			'width' => '100%'));
	} else {
		echo form_hidden('news_language', '', $data['news_language']);
	}
	echo form_hidden('news_datestamp', '', $data['news_datestamp']);
	echo form_select('news_visibility', $locale['news_0209'], $data['news_visibility'], array('options' => fusion_get_groups(),
		'placeholder' => $locale['choose'],
		'width' => '100%'));
	closeside();
	openside('');
	if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
		$sys = "";
		if ($settings['comments_enabled'] == "0" && $settings['ratings_enabled'] == "0") {
			$sys = $locale['comments_ratings'];
		} elseif ($settings['comments_enabled'] == "0") {
			$sys = $locale['comments'];
		} else {
			$sys = $locale['ratings'];
		}
		echo "<span style='color:red;font-weight:bold;margin-right:5px;'>*</span>".sprintf($locale['news_0253'], $sys)."</span><br/>\n";
	}
	echo "<label><input type='checkbox' name='news_draft' value='yes'".($data['news_draft'] ? "checked='checked'" : "")." /> ".$locale['news_0210']."</label><br />\n";
	echo "<label><input type='checkbox' name='news_sticky' value='yes'".($data['news_sticky'] ? "checked='checked'" : "")."  /> ".$locale['news_0211']."</label><br />\n";
	if ($settings['tinymce_enabled'] != 1) {
		echo "<label><input type='checkbox' name='line_breaks' value='yes'".($data['news_breaks'] ? "checked='checked'" : "")." /> ".$locale['news_0212']."</label><br />\n";
	}
	echo "<label><input type='checkbox' name='news_allow_comments' value='yes' onclick='SetRatings();'".($data['news_allow_comments'] ? "checked='checked'" : "")." /> ".$locale['news_0213']."</label><br/>";
	echo "<label><input type='checkbox' name='news_allow_ratings' value='yes'".($data['news_allow_ratings'] ? "checked='checked'" : "")." /> ".$locale['news_0214']."</label>";
	closeside();
	if (isset($_GET['action']) && isset($_GET['news_id']) && isnum($_GET['news_id']) || (isset($_POST['preview']) && (isset($_POST['news_id']) && isnum($_POST['news_id']))) || (isset($_GET['news_id']) && isnum($_GET['news_id']))) {
		$news_id = isset($_GET['news_id']) && isnum($_GET['news_id']) ? $_GET['news_id'] : '';
		echo form_hidden('news_id', '', $news_id);
	}
	echo "</div>\n</div>\n";
	echo form_button('preview', $locale['news_0240'], $locale['news_0240'], array('class' => 'btn-default m-r-10'));
	echo form_button('save', $locale['news_0241'], $locale['news_0241'], array('class' => 'btn-success', 'icon'=>'fa fa-square-check-o'));
	echo closeform();
	echo "</div>\n";
}

function calculate_byte($download_max_b) {
	$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
	foreach ($calc_opts as $byte => $val) {
		if ($download_max_b/$byte <= 999) {
			return $byte;
		}
	}
	return 1000000;
}
