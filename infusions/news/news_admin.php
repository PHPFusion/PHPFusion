<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news_admin.php
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
require_once "../../maincore.php";
pageAccess("N");
require_once THEMES."templates/admin_header.php";
include INFUSIONS."news/locale/".LOCALESET."news_admin.php";
include LOCALE.LOCALESET."admin/settings.php";
require_once INCLUDES."infusions_include.php";
$news_settings = get_settings("news");
add_breadcrumb(array('link' => FUSION_SELF.$aidlink, 'title' => $locale['news_0000']));
if (isset($_POST['cancel'])) {
	redirect(FUSION_SELF.$aidlink);
}
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
		$result = dbquery("DELETE FROM ".DB_NEWS." WHERE news_id='".$del_data['news_id']."'");
		$result = dbquery("DELETE FROM ".DB_COMMENTS."  WHERE comment_item_id='".$del_data['news_id']."' and comment_type='N'");
		$result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='".$del_data['news_id']."' and rating_type='N'");
		dbquery_insert(DB_NEWS, $del_data, 'delete');
		addNotice('warning', $locale['news_0102']);
		redirect(FUSION_SELF.$aidlink);
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}
$allowed_pages = array(
	"news", "news_category", "news_form", "submissions", "settings"
);
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_pages) ? $_GET['section'] : 'news';
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['news_id']) && isnum($_GET['news_id'])) ? TRUE : FALSE;
$master_title['title'][] = $locale['news_0000'];
$master_title['id'][] = 'news';
$master_title['icon'] = '';
$master_title['title'][] = $edit ? $locale['news_0003'] : $locale['news_0002'];
$master_title['id'][] = 'news_form';
$master_title['icon'] = '';
$master_title['title'][] = $locale['news_0020'];
$master_title['id'][] = 'news_category';
$master_title['icon'] = '';
$master_title['title'][] = $locale['news_0023'];
$master_title['id'][] = 'submissions';
$master_title['icon'] = '';
$master_title['title'][] = isset($_GET['settings']) ? $locale['news_0004'] : $locale['news_0004'];
$master_title['id'][] = 'settings';
$master_title['icon'] = '';
$tab_active = $_GET['section'];
opentable($locale['news_0001']);
echo opentab($master_title, $tab_active, "news_admin", 1);
switch ($_GET['section']) {
	case "news_category":
		include "admin/news_cat.php";
		break;
	case "settings":
		include "admin/news_settings.php";
		break;
	case "news_form":
		add_breadcrumb(array('link' => '', 'title' => $edit ? $locale['news_0003'] : $locale['news_0002']));
		include "admin/news.php";
		break;
	case "submissions":
		include "admin/news_submissions.php";
		break;
	default:
		news_listing();
}
echo closetab();
closetable();
require_once THEMES."templates/footer.php";
function news_listing() {
	global $aidlink, $locale;
	$result2 = dbquery("
	SELECT news_id, news_subject, news_image_t1, news_news, news_draft FROM ".DB_NEWS."
	WHERE ".(multilang_table("NS") ? "news_language='".LANGUAGE."' AND " : "")." news_cat='0'
	ORDER BY news_draft DESC, news_sticky DESC, news_datestamp DESC
	");
	echo "<div class='m-t-20'>\n";
	echo opencollapse('news-list');
	echo "<div class='panel panel-default'>\n";
	echo "<div class='panel-heading clearfix'>\n";
	echo "<div class='overflow-hide'>\n";
	echo "<span class='display-inline-block strong'><a ".collapse_header_link('news-list', '0', TRUE, 'm-r-10').">".$locale['news_0202']."</a></span>\n";
	echo "<span class='badge m-r-10'>".dbrows($result2)."</span>";
	echo "<span class='text-smaller mid-opacity'>".LANGUAGE."</span>";
	echo "</div>\n";
	echo "</div>\n"; // end panel heading
	echo "<div ".collapse_footer_link('news-list', '0', TRUE).">\n";
	echo "<ul class='list-group p-15'>\n";
	if (dbrows($result2) > 0) {
		while ($data2 = dbarray($result2)) {
			echo "<li class='list-group-item'>\n";
			echo "<div class='pull-left m-r-10'>\n";
			$img_thumb = ($data2['news_image_t1']) ? IMAGES_N_T.$data2['news_image_t1'] : IMAGES."imagenotfound70.jpg";
			echo thumbnail($img_thumb, '50px');
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			$newsText = strip_tags(html_entity_decode($data2['news_news']));
			echo "<div><span class='strong text-dark'>".$data2['news_subject']."</span><br/>".fusion_first_words($newsText, '50')."</div>\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;section=news_form&amp;news_id=".$data2['news_id']."'>".$locale['edit']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;news_id=".$data2['news_id']."' onclick=\"return confirm('".$locale['news_0251']."');\">".$locale['delete']."</a>\n";
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
	$result = dbquery("
	SELECT cat.news_cat_id, cat.news_cat_name, cat.news_cat_image, cat.news_cat_language,
	count(news.news_id) as news_count,
	count(child.news_cat_id) as news_parent_count
	FROM ".DB_NEWS_CATS." cat
	LEFT JOIN ".DB_NEWS_CATS." child on child.news_cat_parent = cat.news_cat_id
	LEFT JOIN ".DB_NEWS." news on news.news_cat = cat.news_cat_id
	".(multilang_table("NS") ? "WHERE cat.news_cat_language='".LANGUAGE."'" : "")." GROUP BY cat.news_cat_id ORDER BY cat.news_cat_name
	");
	if (dbrows($result) > 0) {
		while ($data = dbarray($result)) {
			echo "<div class='panel panel-default'>\n";
			echo "<div class='panel-heading clearfix'>\n";
			echo "<div class='btn-group pull-right m-t-5'>\n";
			echo "<a class='btn btn btn-default' href='".clean_request("section=news_category&action=edit&cat_id=".$data['news_cat_id'], array("aid"))."'>".$locale['edit']."</a>";
			echo "<a class='".($data['news_count'] || $data['news_parent_count'] ? "disabled" : "")." btn btn-danger' href='".clean_request("section=news_category&action=delete&cat_id=".$data['news_cat_id'], array("aid"))."' onclick=\"return confirm('".$locale['news_0252']."');\"><i class='fa fa-trash'></i> ".$locale['delete']."</a>\n";
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
					$newsText = strip_tags(html_entity_decode($data2['news_news']));
					echo "<div><span class='strong text-dark'>".$data2['news_subject']."</span><br/>".fusion_first_words($newsText, 50)."</div>\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;section=news_form&amp;news_id=".$data2['news_id']."'>".$locale['edit']."</a> -\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;news_id=".$data2['news_id']."' onclick=\"return confirm('".$locale['news_0251']."');\">".$locale['delete']."</a>\n";
					echo "</li>\n";
				}
			} else {
				echo "<div class='panel-body text-center'>\n";
				echo $locale['news_0254'];
				echo "</div>\n";
			}
			echo "</ul>\n";
			echo "</div>\n</div>\n"; // panel container, default
		}
	}
	echo closecollapse();
	echo "</div>\n";
}

/**
 * Returns nearest data unit
 * @param $total_bit
 * @return int
 */
function calculate_byte($total_bit) {
	$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
	foreach ($calc_opts as $byte => $val) {
		if ($total_bit/$byte <= 999) {
			return (int)$byte;
		}
	}
	return 1000000;
}