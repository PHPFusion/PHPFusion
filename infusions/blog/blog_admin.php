<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blog_admin.php
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
pageAccess('BLOG');
require_once THEMES."templates/admin_header.php";
include INFUSIONS."blog/locale/".LOCALESET."blog_admin.php";
include LOCALE.LOCALESET."admin/settings.php";
require_once INFUSIONS."blog/classes/Functions.php";
require_once INFUSIONS."blog/classes/Admin.php";
require_once INCLUDES."infusions_include.php";
$blog_settings = get_settings("blog");
add_breadcrumb(array('link' => INFUSIONS.'blog/blog_admin.php'.$aidlink, 'title' => $locale['blog_0405']));
if (isset($_POST['cancel'])) {
	redirect(FUSION_SELF.$aidlink);
}
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['blog_id']) && isnum($_GET['blog_id'])) {
	$del_data['blog_id'] = $_GET['blog_id'];
	$result = dbquery("SELECT blog_image, blog_image_t1, blog_image_t2 FROM ".DB_BLOG." WHERE blog_id='".$del_data['blog_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		if (!empty($data['blog_image']) && file_exists(IMAGES_B.$data['blog_image'])) {
			unlink(IMAGES_B.$data['blog_image']);
		}
		if (!empty($data['blog_image_t1']) && file_exists(IMAGES_B_T.$data['blog_image_t1'])) {
			unlink(IMAGES_B_T.$data['blog_image_t1']);
		}
		if (!empty($data['blog_image_t2']) && file_exists(IMAGES_B_T.$data['blog_image_t2'])) {
			unlink(IMAGES_B_T.$data['blog_image_t2']);
		}
		$result = dbquery("DELETE FROM ".DB_BLOG." WHERE blog_id='".$del_data['blog_id']."'");
		$result = dbquery("DELETE FROM ".DB_COMMENTS."  WHERE comment_item_id='".$del_data['blog_id']."' and comment_type='B'");
		$result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='".$del_data['blog_id']."' and rating_type='B'");
		dbquery_insert(DB_BLOG, $del_data, 'delete');
		addNotice('warning', $locale['blog_0412']);
		redirect(FUSION_SELF.$aidlink);
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}
$allowed_pages = array("blog",
	"blog_category",
	"blog_form",
	"submissions",
	"settings");
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_pages) ? $_GET['section'] : "blog";
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['blog_id']) && isnum($_GET['blog_id'])) ? TRUE : FALSE;
$master_title['title'][] = $locale['blog_0400'];
$master_title['id'][] = 'blog';
$master_title['icon'] = '';
$master_title['title'][] = $edit ? $locale['blog_0402'] : $locale['blog_0401'];
$master_title['id'][] = 'blog_form';
$master_title['icon'] = '';
$master_title['title'][] = $locale['blog_0502'];
$master_title['id'][] = 'blog_category';
$master_title['icon'] = '';
$master_title['title'][] = $locale['blog_0406'];
$master_title['id'][] = 'settings';
$master_title['icon'] = '';
$master_title['title'][] = $locale['blog_0600'];
$master_title['id'][] = 'submissions';
$master_title['icon'] = '';
$tab_active = $_GET['section'];
opentable($locale['blog_0405']);
echo opentab($master_title, $tab_active, 'blog', 1);
switch ($_GET['section']) {
	case "blog_category":
		include "admin/blog_cat.php";
		break;
	case "settings":
		include "admin/blog_settings.php";
		break;
	case "blog_form":
		add_breadcrumb(array('link' => '', 'title' => $edit ? $locale['blog_0402'] : $locale['blog_0401']));
		include "admin/blog.php";
		break;
	case "submissions":
		//include LOCALE.LOCALESET."admin/submissions.php";
		include "admin/blog_submissions.php";
		break;
	default:
		blog_listing();
}
echo closetab();
closetable();
require_once THEMES."templates/footer.php";
if (isset($_GET['section']) && $_GET['section'] == 'nform') {
	add_breadcrumb(array('link' => '',
					   'title' => isset($_GET['blog_id']) ? $locale['blog_0402'] : $locale['blog_0401']));
	echo opentabbody($master_title['title'][1], 'nform', $tab_active, 1);
	blog_form();
	echo closetabbody();
}
echo closetab();
closetable();
if (isset($_GET['section']) && $_GET['section'] == 'sform') {
	include LOCALE.LOCALESET."admin/settings.php";
	add_breadcrumb(array('link' => '', 'title' => $locale['blog_blog_settings']));
	$settings2 = array();
	$result = dbquery("SELECT * FROM ".DB_SETTINGS_INF);
	while ($data = dbarray($result)) {
		$settings2[$data['settings_name']] = $data['settings_value'];
	}
	if (isset($_POST['savesettings'])) {
		$settings2 = array('blog_image_link' => form_sanitizer($_POST['blog_image_link'], '0', 'blog_image_link'),
			'blog_image_frontpage' => form_sanitizer($_POST['blog_image_frontpage'], '0', 'blog_image_frontpage'),
			'blog_image_readmore' => form_sanitizer($_POST['blog_image_readmore'], '0', 'blog_image_readmore'),
			'blog_thumb_ratio' => form_sanitizer($_POST['blog_thumb_ratio'], '0', 'blog_thumb_ratio'),
			'blog_thumb_w' => form_sanitizer($_POST['blog_thumb_w'], '300', 'blog_thumb_w'),
			'blog_thumb_h' => form_sanitizer($_POST['blog_thumb_h'], '150', 'blog_thumb_h'),
			'blog_photo_w' => form_sanitizer($_POST['blog_photo_w'], '400', 'blog_photo_w'),
			'blog_photo_h' => form_sanitizer($_POST['blog_photo_h'], '300', 'blog_photo_h'),
			'blog_photo_max_w' => form_sanitizer($_POST['blog_photo_max_w'], '1800', 'blog_photo_max_w'),
			'blog_photo_max_h' => form_sanitizer($_POST['blog_photo_max_h'], '1600', 'blog_photo_max_h'),
			'blog_photo_max_b' => form_sanitizer($_POST['calc_b'], '150', 'calc_b')*form_sanitizer($_POST['calc_c'], '100000', 'calc_c'),
			'blog_pagination' => form_sanitizer($_POST['blog_pagination'], '12', 'blog_pagination'),);
		foreach ($settings2 as $settings_key => $settings_value) {
			$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='".$settings_value."' WHERE settings_name='".$settings_key."'");
			if (!$result) {
				$defender->stop();
				addNotice('danger', $locale['blog_901']);
				break;
			}
		}
		if (!defined('FUSION_NULL')) {
			addNotice('success', $locale['blog_900']);
			redirect(FUSION_SELF.$aidlink."&amp;section=sform&amp;settings");
		}
	}
	opentable($locale['blog_settings']);
	echo "<div class='well'>".$locale['blog_description']."</div>";
	$formaction = FUSION_SELF.$aidlink."&amp;section=sform&amp;settings";
	echo openform('settingsform', 'post', $formaction, array('max_tokens' => 1));
	$opts = array('0' => $locale['952'], '1' => $locale['953b']);
	$cat_opts = array('0' => $locale['959'], '1' => $locale['960']);
	$thumb_opts = array('0' => $locale['955'], '1' => $locale['956']);
	$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
	$calc_c = calculate_byte($settings2['blog_photo_max_b']);
	$calc_b = $settings2['blog_photo_max_b']/$calc_c;
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-8'>\n";
	openside('');
	echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='blog_pagination'>".$locale['669b']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('blog_pagination', '', $settings2['blog_pagination'], array('class' => 'pull-left',
			'max_length' => 4,
			'number' => 1,
			'width' => '150px'))."
	</div>
	<div class='col-xs-12 col-sm-3'>
	<label for='blog_thumb_w'>".$locale['601']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('blog_thumb_w', '', $settings2['blog_thumb_w'], array('class' => 'pull-left',
			'max_length' => 4,
			'number' => 1,
			'width' => '150px'))."
	<i class='entypo icancel pull-left m-r-10 m-l-0 m-t-10'></i>
	".form_text('blog_thumb_h', '', $settings2['blog_thumb_h'], array('class' => 'pull-left',
			'max_length' => 4,
			'number' => 1,
			'width' => '150px'))."
	<small class='m-l-10 mid-opacity text-uppercase pull-left m-t-10'>( ".$locale['604']." )</small>
	</div>
</div>";
	echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='blog_thumb_w'>".$locale['602']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('blog_photo_w', '', $settings2['blog_photo_w'], array('class' => 'pull-left',
			'max_length' => 4,
			'number' => 1,
			'width' => '150px'))."
	<i class='entypo icancel pull-left m-r-10 m-l-0 m-t-10'></i>
	".form_text('blog_photo_h', '', $settings2['blog_photo_h'], array('class' => 'pull-left',
			'max_length' => 4,
			'number' => 1,
			'width' => '150px'))."
	<small class='m-l-10 mid-opacity text-uppercase pull-left m-t-10'>( ".$locale['604']." )</small>
	</div>
</div>";
	echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='blog_thumb_w'>".$locale['603']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('blog_photo_max_w', '', $settings2['blog_photo_max_w'], array('class' => 'pull-left',
			'max_length' => 4,
			'number' => 1,
			'width' => '150px'))."
	<i class='entypo icancel pull-left m-r-10 m-l-0 m-t-10'></i>
	".form_text('blog_photo_max_h', '', $settings2['blog_photo_max_h'], array('class' => 'pull-left',
			'max_length' => 4,
			'number' => 1,
			'width' => '150px'))."
	<small class='m-l-10 mid-opacity text-uppercase pull-left m-t-10'>( ".$locale['604']." )</small>
	</div>
</div>";
	echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='calc_b'>".$locale['605']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('calc_b', '', $calc_b, array('required' => 1,
			'number' => 1,
			'error_text' => $locale['error_rate'],
			'width' => '100px',
			'max_length' => 4,
			'class' => 'pull-left m-r-10'))."
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
	echo form_select('blog_image_link', $locale['951'], $settings2['blog_image_link'], array("options" => $opts));
	echo form_select('blog_image_frontpage', $locale['957'], $settings2['blog_image_frontpage'], array("options" => $cat_opts));
	echo form_select('blog_image_readmore', $locale['958'], $settings2['blog_image_readmore'], array("options" => $cat_opts));
	echo form_select('blog_thumb_ratio', $locale['954'], $settings2['blog_thumb_ratio'], array("options" => $thumb_opts));
	closeside();
	echo "</div></div>\n";
	echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-primary'));
	echo closeform();
	closetable();
}
/**
 * Blog Listing HTML
 */
function blog_listing() {
	global $aidlink, $locale;
	$result2 = dbquery("
	SELECT blog_id, blog_subject, blog_image, blog_image_t1, blog_image_t2, blog_blog, blog_draft FROM ".DB_BLOG."
	WHERE ".(multilang_table("BL") ? "blog_language='".LANGUAGE."' AND " : "")." blog_cat='0'
	ORDER BY blog_draft DESC, blog_sticky DESC, blog_datestamp DESC
	");
	echo "<div class='m-t-20'>\n";
	echo opencollapse('blog-list');
	// uncategorized listing
	echo "<div class='panel panel-default'>\n";
	echo "<div class='panel-heading clearfix'>\n";
	echo "<div class='overflow-hide'>\n";
	echo "<span class='display-inline-block strong'><a ".collapse_header_link('blog-list', '0', TRUE, 'm-r-10').">".$locale['blog_0424']."</a></span>\n";
	echo "<span class='badge m-r-10'>".dbrows($result2)."</span>\n";
	echo "<span class='text-smaller mid-opacity'>".LANGUAGE."</span>";
	echo "</div>\n";
	echo "</div>\n"; // end panel heading
	echo "<div ".collapse_footer_link('blog-list', '0', TRUE).">\n";
	echo "<ul class='list-group m-10'>\n";
	if (dbrows($result2) > 0) {
		while ($data2 = dbarray($result2)) {
			echo "<li class='list-group-item'>\n";
			echo "<div class='pull-left m-r-10'>\n";
			$image_thumb = get_blog_image_path($data2['blog_image'], $data2['blog_image_t1'], $data2['blog_image_t2']);
			if (!$image_thumb) $image_thumb = IMAGES."imagenotfound70.jpg";
			echo thumbnail($image_thumb, '50px');
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo "<div><span class='strong text-dark'>".$data2['blog_subject']."</span><br/>".fusion_first_words(stripslashes($data2['blog_blog']), '50')."</div>\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;section=blog_form&amp;blog_id=".$data2['blog_id']."'>".$locale['blog_0420']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;section=blog_form&amp;blog_id=".$data2['blog_id']."' onclick=\"return confirm('".$locale['blog_0451']."');\">".$locale['blog_0421']."</a>\n";
			echo "</div>\n";
			echo "</li>\n";
		}
	} else {
		echo "<div class='panel-body text-center'>\n";
		echo $locale['blog_0456'];
		echo "</div>\n";
	}
	echo "</ul>\n";
	echo "</div>\n"; // panel container
	echo "</div>\n"; // panel default
	$result = dbquery("SELECT blog.*, cat.blog_cat_id, cat.blog_cat_name, cat.blog_cat_image, cat.blog_cat_language,
			count(blog_id) as blog_count,
			count(child.blog_cat_id) as blog_parent_count
			FROM ".DB_BLOG_CATS." cat
			LEFT JOIN ".DB_BLOG_CATS." child on child.blog_cat_parent = cat.blog_cat_id
			LEFT JOIN ".DB_BLOG." blog on (cat.blog_cat_id = blog.blog_cat)
			".(multilang_table("BL") ? "WHERE cat.blog_cat_language='".LANGUAGE."'" : "")." GROUP BY cat.blog_cat_id ORDER BY cat.blog_cat_name");
	if (dbrows($result) > 0) {
		while ($data = dbarray($result)) {
			echo "<div class='panel panel-default'>\n";
			echo "<div class='panel-heading clearfix'>\n";
			echo "<div class='btn-group pull-right m-t-5'>\n";
			//echo "<a class='btn btn-default' href='".ADMIN."blog_cats.php".$aidlink."&amp;action=delete&amp;cat_id=".$data['blog_cat_id']."' onclick=\"return confirm('".$locale['']."');\"><i class='fa fa-trash m-r-5'></i> ".$locale['']."</a>\n";
			echo "<a class='btn btn btn-default' href='".clean_request("section=blog_category&action=edit&cat_id=".$data['blog_cat_id'], array("aid"))."'>".$locale['edit']."</a>";
			echo "<a class='".($data['blog_count'] || $data['blog_parent_count'] ? "disabled" : "")." btn btn-danger' href='".clean_request("section=blog_category&action=delete&cat_id=".$data['blog_cat_id'], array("aid"))."' onclick=\"return confirm('".$locale['blog_0451b']."');\"><i class='fa fa-trash'></i> ".$locale['delete']."</a>\n";
			echo "</div>\n";
			echo "<div class='overflow-hide p-r-10'>\n";
			echo "<span class='display-inline-block strong'><a ".collapse_header_link('blog-list', $data['blog_cat_id'], '0', 'm-r-10').">".$data['blog_cat_name']."</a></span>\n";
			echo "<span class='badge m-r-10'>".$data['blog_count']."</span>";
			echo "<span class='text-smaller mid-opacity'>".LANGUAGE."</span>";
			echo "</div>\n"; /// end overflow-hide
			echo "</div>\n"; // end panel heading
			echo "<div ".collapse_footer_link('blog-list', $data['blog_cat_id'], '0').">\n";
			echo "<ul class='list-group'>\n";
			$result2 = dbquery("SELECT blog_id, blog_subject, blog_image, blog_image_t1, blog_image_t2, blog_blog, blog_draft FROM ".DB_BLOG." ".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." blog_cat='".$data['blog_cat_id']."' ORDER BY blog_draft DESC, blog_sticky DESC, blog_datestamp DESC");
			if (dbrows($result2) > 0) {
				while ($data2 = dbarray($result2)) {
					echo "<li class='list-group-item'>\n";
					echo "<div class='pull-left m-r-10'>\n";
					$image_thumb = get_blog_image_path($data2['blog_image'], $data2['blog_image_t1'], $data2['blog_image_t2']);
					if (!$image_thumb) $image_thumb = IMAGES."imagenotfound70.jpg";
					echo thumbnail($image_thumb, '50px');
					echo "</div>\n";
					echo "<div class='overflow-hide'>\n";
					echo "<div><span class='strong text-dark'>".$data2['blog_subject']."</span><br/>".fusion_first_words(stripslashes($data2['blog_blog']), '50')."\n</div>\n";
					echo "<div class='pull-right'>\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;section=blog_form&amp;blog_id=".$data2['blog_id']."'>".$locale['blog_0420']."</a> -\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;section=blog_form&amp;blog_id=".$data2['blog_id']."' onclick=\"return confirm('".$locale['blog_0451']."');\">".$locale['blog_0421']."</a>\n";
					echo "</div>\n";
					echo "</li>\n";
				}
			} else {
				echo "<div class='panel-body text-center'>\n";
				echo $locale['blog_0456'];
				echo "</div>\n";
			}
			// blog listing.
			echo "</ul>\n";
			echo "</div>\n"; // panel container
			echo "</div>\n"; // panel default
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

/**
 * Function to progressively return closest full image_path
 * @param $blog_image
 * @param $blog_image_t1
 * @param $blog_image_t2
 * @return string
 */
function get_blog_image_path($blog_image, $blog_image_t1, $blog_image_t2, $hiRes = FALSE) {
	return PHPFusion\Blog\Functions::get_blog_image_path($blog_image, $blog_image_t1, $blog_image_t2, $hiRes);
}