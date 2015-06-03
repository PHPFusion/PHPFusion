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
require_once INFUSIONS."blog/classes/Functions.php";
require_once INFUSIONS."blog/classes/Admin.php";

add_breadcrumb(array('link'=>INFUSIONS.'blog/blog_admin.php'.$aidlink, 'title'=>$locale['405']));

if (isset($_POST['cancel'])) { redirect(FUSION_SELF.$aidlink); }
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
		addNotice('warning', $locale['412']);
		redirect(FUSION_SELF.$aidlink);
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}

$_GET['section'] = isset($_GET['section']) ? $_GET['section'] : 'blog';

$master_title['title'][] = $locale['400'];
$master_title['id'][] = 'blog';
$master_title['icon'] = '';

$master_title['title'][] = isset($_GET['blog_id']) ? $locale['402'] : $locale['401'];
$master_title['id'][] = 'nform';
$master_title['icon'] = '';

$settings_access = pageAccess('S13');
if ($settings_access !==0) {
$master_title['title'][] = isset($_GET['settings']) ? $locale['406'] : $locale['406'];
$master_title['id'][] = 'sform';
$master_title['icon'] = '';
}

$tab_active = tab_active($master_title, $_GET['section'], 1);

opentable($locale['405']);
$message = '';
if (isset($_GET['status'])) {
	switch($_GET['status']) {
		case 'sn':
			$message = $locale['410'];
			$status = 'success';
			$icon = "<i class='fa fa-check-square-o fa-lg fa-fw'></i>";
			break;
		case 'su':
			$message = $locale['411'];
			$status = 'info';
			$icon = "<i class='fa fa-check-square-o fa-lg fa-fw'></i>";
			break;
		case 'del':
			$message = $locale['412'];
			$status = 'danger';
			$icon = "<i class='fa fa-trash fa-lg fa-fw'></i>";
			break;
	}
	if ($message) {
		addNotice($status, $icon.$message);
	}
}

echo opentab($master_title, $tab_active, 'blog', 1);
echo opentabbody($master_title['title'][0], 'blog', $tab_active, 1);
blog_listing();
echo closetabbody();
if (isset($_GET['section']) && $_GET['section'] == 'nform') {
	add_breadcrumb(array('link'=>'', 'title'=>isset($_GET['blog_id']) ? $locale['402'] : $locale['401']));
	echo opentabbody($master_title['title'][1], 'nform', $tab_active, 1);
	blog_form();
	echo closetabbody();
}
echo closetab();
closetable();

if (isset($_GET['section']) && $_GET['section'] == 'sform') {
pageAccess('S13');
include LOCALE.LOCALESET."admin/settings.php";

//add_breadcrumb(array('link'=>INFUSIONS."blog/settings_blog.php".$aidlink, 'title'=>$locale['blog_settings']));

$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS_INF);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}

if (isset($_POST['savesettings'])) {

	$settings2 = array(
		'blog_image_link' =>  form_sanitizer($_POST['blog_image_link'], '0', 'blog_image_link'),
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
		'blog_pagination' => form_sanitizer($_POST['blog_pagination'], '12', 'blog_pagination'),
		);
	foreach($settings2 as $settings_key => $settings_value) {
		$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='".$settings_value."' WHERE settings_name='".$settings_key."'");
		if (!$result) {
			$defender->stop();
			addNotice('danger', $locale['901']);
			break;
		}
	}
	if (!defined('FUSION_NULL')) {
		addNotice('success', $locale['900']);
		redirect(FUSION_SELF.$aidlink."&amp;section=sform&amp;settings");
	}
}
opentable($locale['blog_settings']);

echo "<div class='well'>".$locale['blog_description']."</div>";
$formaction = FUSION_SELF.$aidlink."&amp;section=sform&amp;settings";
echo openform('settingsform', 'post',$formaction , array('max_tokens' => 1));
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
	".form_text('blog_pagination', '', $settings2['blog_pagination'], array('class' => 'pull-left', 'max_length' => 4, 'number'=>1, 'width'=>'150px'))."
	</div>
	<div class='col-xs-12 col-sm-3'>
	<label for='blog_thumb_w'>".$locale['601']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('blog_thumb_w', '', $settings2['blog_thumb_w'], array('class' => 'pull-left', 'max_length' => 4, 'number'=>1, 'width'=>'150px'))."
	<i class='entypo icancel pull-left m-r-10 m-l-0 m-t-10'></i>
	".form_text('blog_thumb_h', '', $settings2['blog_thumb_h'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1, 'width'=>'150px'))."
	<small class='m-l-10 mid-opacity text-uppercase pull-left m-t-10'>( ".$locale['604']." )</small>
	</div>
</div>";
echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='blog_thumb_w'>".$locale['602']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('blog_photo_w', '', $settings2['blog_photo_w'], array('class' => 'pull-left', 'max_length' => 4, 'number'=>1, 'width'=>'150px'))."
	<i class='entypo icancel pull-left m-r-10 m-l-0 m-t-10'></i>
	".form_text('blog_photo_h', '', $settings2['blog_photo_h'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1, 'width'=>'150px'))."
	<small class='m-l-10 mid-opacity text-uppercase pull-left m-t-10'>( ".$locale['604']." )</small>
	</div>
</div>";
echo "
<div class='row'>
	<div class='col-xs-12 col-sm-3'>
	<label for='blog_thumb_w'>".$locale['603']."</label>
	</div>
	<div class='col-xs-12 col-sm-9'>
	".form_text('blog_photo_max_w', '', $settings2['blog_photo_max_w'], array('class' => 'pull-left', 'max_length' => 4, 'number'=>1, 'width'=>'150px'))."
	<i class='entypo icancel pull-left m-r-10 m-l-0 m-t-10'></i>
	".form_text('blog_photo_max_h', '', $settings2['blog_photo_max_h'], array('class' => 'pull-left', 'max_length' => 4, 'number' => 1, 'width'=>'150px'))."
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
	".form_select('calc_c', '', $calc_opts, $calc_c, array('placeholder' => $locale['choose'], 'class' => 'pull-left', 'width' => '180px'))."
	</div>
</div>
";
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-4'>\n";
openside('');
echo form_select('blog_image_link',$locale['951'], $opts, $settings2['blog_image_link']);
echo form_select('blog_image_frontpage', $locale['957'], $cat_opts, $settings2['blog_image_frontpage']);
echo form_select('blog_image_readmore', $locale['958'], $cat_opts, $settings2['blog_image_readmore']);
echo form_select('blog_thumb_ratio', $locale['954'], $thumb_opts, $settings2['blog_thumb_ratio']);
closeside();
echo "</div></div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-primary'));

echo closeform();
closetable();

}

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['blog_id'])) {
	add_to_jquery("
		// change the name of the second tab and activate it.
		$('#tab-nformAdd-blog').text('".$locale['402']."');
		$('#blog a:last').tab('show');
		");
}

require_once THEMES."templates/footer.php";

function calculate_byte($download_max_b) {
	$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
	foreach ($calc_opts as $byte => $val) {
		if ($download_max_b/$byte <= 999) {
			return $byte;
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
function get_blog_image_path($blog_image, $blog_image_t1, $blog_image_t2, $hiRes = false) {
	return PHPFusion\Blog\Functions::get_blog_image_path($blog_image, $blog_image_t1, $blog_image_t2, $hiRes);
}

/**
 * Blog Listing HTML
 */
function blog_listing() {
	global $aidlink, $locale;
	echo "<div class='m-t-20'>\n";
	echo opencollapse('blog-list');
	// uncategorized listing
	echo "<div class='panel panel-default'>\n";
	echo "<div class='panel-heading clearfix'>\n";
	echo "<div class='overflow-hide'>\n";
	echo "<h4 class='panel-title display-inline-block'><a ".collapse_header_link('blog-list', '0', '0', 'm-r-10 text-bigger strong').">".$locale['424']."</a> <span class='badge'>".dbcount("(blog_id)", DB_BLOG, "blog_cat='0'")."</span></h4>\n";
	echo "<br/><span class='text-smaller text-uppercase'>".LANGUAGE."</span>";
	echo "</div>\n";
	echo "</div>\n"; // end panel heading
	echo "<div ".collapse_footer_link('blog-list','0', '0').">\n";
	echo "<ul class='list-group m-10'>\n";
	$result2 = dbquery("SELECT blog_id, blog_subject, blog_image, blog_image_t1, blog_image_t2, blog_blog, blog_draft FROM ".DB_BLOG." ".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."'" : "")." AND blog_cat='0' ORDER BY blog_draft DESC, blog_sticky DESC, blog_datestamp DESC");
	if (dbrows($result2) > 0) {
		while ($data2 = dbarray($result2)) {
			echo "<li class='list-group-item'>\n";
			echo "<div class='pull-left m-r-10'>\n";
			$image_thumb = get_blog_image_path($data2['blog_image'], $data2['blog_image_t1'], $data2['blog_image_t2']);
			echo thumbnail($image_thumb, '50px');
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo "<div class='strong text-dark'>".$data2['blog_subject']."</div>".fusion_first_words($data2['blog_blog'], '50')."\n";
			echo "<div class='pull-right'>\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;section=nform&amp;blog_id=".$data2['blog_id']."'>".$locale['420']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;section=nform&amp;blog_id=".$data2['blog_id']."' onclick=\"return confirm('".$locale['451']."');\">".$locale['421']."</a>\n";
			echo "</div>\n";
			echo "</div>\n";
			echo "</li>\n";
		}
	} else {
		echo "<div class='panel-body text-center'>\n";
		echo $locale['456'];
		echo "</div>\n";
	}
	echo "</ul>\n";
	echo "</div>\n"; // panel container
	echo "</div>\n"; // panel default

	$result = dbquery("SELECT blog_cat_id, blog_cat_name, blog_cat_image, blog_cat_language
			, count(blog_id) as blog_count
			FROM ".DB_BLOG_CATS." cat
			LEFT JOIN ".DB_BLOG." blog on (cat.blog_cat_id = blog.blog_cat)
			".(multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."'" : "")." GROUP BY blog_cat_id ORDER BY blog_cat_name");

	if (dbrows($result) > 0) {
		while ($data = dbarray($result)) {
			echo "<div class='panel panel-default'>\n";
			echo "<div class='panel-heading clearfix'>\n";
			echo "<div class='btn-group pull-right m-t-5'>\n";
			echo "<a class='btn btn-default' href='".ADMIN."blog_cats.php".$aidlink."&amp;action=edit&amp;cat_id=".$data['blog_cat_id']."'><i class='fa fa-pencil m-r-5'></i>".$locale['420']."</a>";
			echo "<a class='btn btn-default' href='".ADMIN."blog_cats.php".$aidlink."&amp;action=delete&amp;cat_id=".$data['blog_cat_id']."' onclick=\"return confirm('".$locale['451b']."');\"><i class='fa fa-trash m-r-5'></i> ".$locale['421']."</a>\n";
			echo "</div>\n";
			echo "<div class='overflow-hide p-r-10'>\n";
			echo "<h4 class='panel-title display-inline-block'><a ".collapse_header_link('blog-list', $data['blog_cat_id'], '0', 'm-r-10 text-bigger strong').">".$data['blog_cat_name']."</a> <span class='badge'>".$data['blog_count']."</h4>\n";
			echo "<br/><span class='text-smaller text-uppercase'>".$data['blog_cat_language']."</span>";
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
					echo thumbnail($image_thumb, '50px');
					echo "</div>\n";
					echo "<div class='overflow-hide'>\n";
					echo "<div class='strong text-dark'>".$data2['blog_subject']."</div>".fusion_first_words($data2['blog_blog'], '50')."\n";
					echo "<div class='pull-right'>\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;section=nform&amp;blog_id=".$data2['blog_id']."'>".$locale['420']."</a> -\n";
					echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;section=nform&amp;blog_id=".$data2['blog_id']."' onclick=\"return confirm('".$locale['451']."');\">".$locale['421']."</a>\n";
					echo "</div>\n";
					echo "</li>\n";
				}
			} else {
				echo "<div class='panel-body text-center'>\n";
				echo $locale['456'];
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
 * Blog Input Form HTML
 */
function blog_form() {
	fusion_confirm_exit();
	global $userdata, $locale, $settings, $aidlink;
	$tinyMce = $settings['tinymce_enabled'] ? true : false;
	$fusion_mce = array();
	if ($tinyMce) {
		echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
	} else {
		$fusion_mce = array('preview' => 1, 'html' => 1, 'autosize' => 1, 'form_name' => 'inputform');
		require_once INCLUDES."html_buttons_include.php";
	}

	$language_opts = fusion_get_enabled_languages();

	$data = array(
		'blog_draft' => 0,
		'blog_sticky' => 0,
		'blog_blog' => '',
		'blog_datestamp' => time(),
		'blog_extended' => '',
		'blog_keywords' => '',
		'blog_breaks' => !$tinyMce ? 1 : 0,
		'blog_allow_comments' => 1,
		'blog_allow_ratings' => 1,
		'blog_language' => LANGUAGE,
		'blog_visibility' => 0,
		'blog_subject' => '',
		'blog_start' => '',
		'blog_end' => '',
		'blog_cat' => 0,
		'blog_image' => '',
		'blog_ialign' => 'pull-left',
		'blog_id' => 0,
	);

	if (isset($_POST['save'])) {
		$data = array(
			'blog_id' => isset($_POST['blog_id']) ? form_sanitizer($_POST['blog_id'], '', 'blog_id') : '',
			'blog_subject' => form_sanitizer($_POST['blog_subject'], '', 'blog_subject'),
			'blog_cat' => form_sanitizer($_POST['blog_cat'], 0, 'blog_cat'),
			'blog_name' => $userdata['user_id'],
			'blog_image' => isset($_POST['blog_hidden_image']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['blog_hidden_image']) ? $_POST['blog_hidden_image'] : "") : "",
			'blog_image_t1' => isset($_POST['blog_hidden_image_t1']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['blog_hidden_image_t1']) ? $_POST['blog_hidden_image_t1'] : "") : "",
			'blog_image_t2' => isset($_POST['blog_hidden_image_t2']) ? (preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $_POST['blog_hidden_image_t2']) ? $_POST['blog_hidden_image_t2'] : "") : "",
			'blog_ialign' => form_sanitizer($_POST['blog_ialign'], 'pull-left', 'blog_ialign'),
			'blog_blog' =>  addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['blog_blog'])), // Needed for HTML to work
			'blog_extended' => addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['blog_extended'])),
			'blog_keywords' => form_sanitizer($_POST['blog_keywords'], '', 'blog_keywords'),
			'blog_datestamp' => form_sanitizer($_POST['blog_datestamp'], time(), 'blog_datestamp'),
			'blog_start' => form_sanitizer($_POST['blog_start'], 0, 'blog_start'),
			'blog_end' => form_sanitizer($_POST['blog_end'], 0, 'blog_end'),
			'blog_visibility' => form_sanitizer($_POST['blog_visibility'], '0', 'blog_visibility'),
			'blog_draft' => isset($_POST['blog_draft']) ? 1 : 0,
			'blog_sticky' => isset($_POST['blog_sticky']) ? 1 : 0,
			'blog_allow_comments' =>  isset($_POST['blog_allow_comments']) ? 1 : 0,
			'blog_allow_ratings' => isset($_POST['blog_allow_ratings']) ? 1  : 0,
			'blog_breaks' => isset($_POST['blog_breaks']) && !$tinyMce ? 1 : 0,
			'blog_language' => form_sanitizer($_POST['blog_language'], '', 'blog_language'),
		);
		if (isset($_FILES['blog_image'])) {
			$upload = form_sanitizer($_FILES['blog_image'], '', 'blog_image');
			if (isset($upload['error']) && $upload['error'] == 0) {
				$data['blog_image'] = $upload['image_name'];
				$data['blog_image_t1'] = $upload['thumb1_name'];
				$data['blog_image_t2'] = $upload['thumb2_name'];
			}
		}

		if (isset($data['blog_id']) && PHPFusion\Blog\Functions::validate_blog($data['blog_id'])) {
			$result = dbquery("SELECT blog_image, blog_image_t1, blog_image_t2, blog_sticky, blog_datestamp FROM ".DB_BLOG." WHERE blog_id='".$_POST['blog_id']."'");
			if (dbrows($result)) {
				$data2 = dbarray($result);
				if ($data['blog_sticky'] == "1") {
					dbquery("UPDATE ".DB_BLOG." SET blog_sticky='0' WHERE blog_sticky='1'");
				}
				if (isset($_POST['del_image'])) {
					if (!empty($data2['blog_image']) && file_exists(IMAGES_B.$data2['blog_image'])) {
						@unlink(IMAGES_B.$data2['blog_image']);
						$data['blog_image'] = "";
					}
					if (!empty($data2['blog_image_t1']) && file_exists(IMAGES_B_T.$data2['blog_image_t1'])) {
						@unlink(IMAGES_B_T.$data2['blog_image_t1']);
						$data['blog_image_t1'] = "";
					}
					if (!empty($data2['blog_image_t2']) && file_exists(IMAGES_B_T.$data2['blog_image_t2'])) {
						@unlink(IMAGES_B_T.$data2['blog_image_t2']);
						$data['blog_image_t2'] = "";
					}
				}
				dbquery_insert(DB_BLOG, $data, 'update');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;status=su");
			} else {
				redirect(FUSION_SELF.$aidlink);
			}
		} else {
			if ($data['blog_sticky'] == "1") {
				$result = dbquery("UPDATE ".DB_BLOG." SET blog_sticky='0' WHERE blog_sticky='1'");
			}
			dbquery_insert(DB_BLOG, $data, 'save');
			if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;status=sn");
		}
	}

	$result = dbquery("SELECT blog_cat_id, blog_cat_name FROM ".DB_BLOG_CATS." ".(multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."'" : "")." ORDER BY blog_cat_name");
	$blog_cat_opts = array();
	$blog_cat_opts['0'] = $locale['424'];
	if (dbrows($result)) {
		while ($odata = dbarray($result)) {
			$blog_cat_opts[$odata['blog_cat_id']] = $odata['blog_cat_name'];
		}
	}

	$visibility_opts = array();
	$user_groups = getusergroups();
	while (list($key, $user_group) = each($user_groups)) {
		$visibility_opts[$user_group['0']] = $user_group['1'];
	}

	$formaction = FUSION_SELF.$aidlink."&amp;section=nform";

	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['blog_id']) && isnum($_POST['blog_id'])) || (isset($_GET['blog_id']) && isnum($_GET['blog_id']))) {
		$result = dbquery("SELECT * FROM ".DB_BLOG." WHERE blog_id='".(isset($_POST['blog_id']) ? $_POST['blog_id'] : $_GET['blog_id'])."'");
		if (dbrows($result)) {
			$formaction = FUSION_SELF.$aidlink."&amp;section=nform&amp;action=edit&blog_id=".$_GET['blog_id'];
			$data = dbarray($result);
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}

	if (isset($_POST['preview'])) {
		$data['blog_subject'] = form_sanitizer($_POST['blog_subject'], '', 'blog_subject');
		$data['blog_cat'] = isnum($_POST['blog_cat']) ? $_POST['blog_cat'] : "0";
		$data['blog_language'] = form_sanitizer($_POST['blog_language'], '', 'blog_language');
		$data['blog_blog'] = phpentities(stripslash($_POST['blog_blog']));
		$data['blog_blog'] = str_replace("src='".str_replace("../", "", IMAGES_B), "src='".IMAGES_B, stripslash($_POST['blog_blog']));
		$data['blog_extended'] = '';
		if ($_POST['blog_extended']) {
			$data['blog_extended'] = phpentities(stripslash($_POST['blog_extended']));
			$data['blog_extended'] = str_replace("src='".str_replace("../", "", IMAGES_B), "src='".IMAGES_B, stripslash($_POST['blog_extended']));
		}
		$data['blog_keywords'] = form_sanitizer($_POST['blog_keywords'], '', 'blog_keywords');
		$data['blog_breaks'] = "";
		if (isset($_POST['line_breaks'])) {
			$data['blog_breaks'] = " 1";
			$data['blog_blog'] = nl2br($data['blog_blog']);
			if ($data['blog_extended']) {
				$data['blog_extended'] = nl2br($data['blog_extended']);
			}
		}
		$data['blog_start'] = (isset($_POST['blog_start']) && $_POST['blog_start']) ? $_POST['blog_start'] : '';
		$data['blog_end'] = (isset($_POST['blog_end']) && $_POST['blog_end']) ? $_POST['blog_end'] : '';
		$data['blog_image'] = isset($_POST['blog_image']) ? $_POST['blog_image'] : '';
		$data['blog_image_t1'] = (isset($_POST['blog_image_t1']) ? $_POST['blog_image_t1'] : "");
		$data['blog_image_t2'] = (isset($_POST['blog_image_t2']) ? $_POST['blog_image_t2'] : "");
		$data['blog_ialign'] = (isset($_POST['blog_ialign']) ? $_POST['blog_ialign'] : "pull-left");
		$data['blog_visibility'] = isnum($_POST['blog_visibility']) ? $_POST['blog_visibility'] : "0";
		$data['blog_draft'] = isset($_POST['blog_draft']) ? " 1" : "";
		$data['blog_sticky'] = isset($_POST['blog_sticky']) ? " 1" : "";
		$data['blog_allow_comments'] = isset($_POST['blog_allow_comments']) ? " 1" : "";
		$data['blog_allow_ratings'] = isset($_POST['blog_allow_ratings']) ? " 1" : "";
		$data['blog_datestamp'] = isset($_POST['blog_datestamp']) ? $_POST['blog_datestamp'] : '';
		if (!defined('FUSION_NULL')) {
			echo openmodal('blog_preview', 'blog Preview');
			echo $data['blog_blog'];
			echo "<hr/>\n";
			if (isset($data['blog_extended'])) {
				echo $data['blog_extended'];
			}
			echo closemodal();
		}
	}

	echo "<div class='m-t-20'>\n";
	echo openform('inputform', 'post', $formaction, array('enctype' => 1, 'max_tokens' => 1));
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
	echo form_text('blog_subject', $locale['422'], $data['blog_subject'], array('required' => 1, 'max_length' => 200, 'error_text' => $locale['450']));

	openside('');
	$align_options = array('pull-left'=>$locale['left'], 'blog-img-center'=>$locale['center'], 'pull-right'=>$locale['right']);

	if ($data['blog_image']) {
		echo "<div class='clearfix display-block' style='width:100%'>\n";
		echo "<div class='pull-left'>\n";
		$image_thumb = get_blog_image_path($data['blog_image'], $data['blog_image_t1'], $data['blog_image_t2']);
		echo thumbnail($image_thumb, '100px');
		echo form_checkbox('del_image', $locale['421'], 'y');
		echo "</div><div class='overflow-hide'>\n";
		echo form_hidden('', 'blog_hidden_image', 'blog_hidden_image', $data['blog_image']);
		echo form_hidden('', 'blog_hidden_image_t1', 'blog_hidden_image_t1', $data['blog_image_t1']);
		echo form_hidden('', 'blog_hidden_image_t1', 'blog_hidden_image_t1', $data['blog_image_t2']);
		echo form_select('blog_ialign', $locale['442'], $align_options, $data['blog_ialign']);
		echo "</div></div>\n";
	} else {
		echo form_fileinput($locale['439'], 'blog_image', 'blog_image', IMAGES_B, '', array(
											  'thumbnail_folder'=> 'thumbs',
											  'thumbnail'=>1,
											  'max_width'=> $settings['blog_photo_max_w'],
											  'max_height' => $settings['blog_photo_max_w'],
											  'max_byte'=> $settings['blog_photo_max_b'],
											  'thumbnail2' => 1,
											  'type' => 'image'
											)
		);
		echo "<div class='small m-b-10'>".sprintf($locale['440'], parsebytesize($settings['blog_photo_max_b']))."</div>\n";
		echo form_select('blog_ialign', $locale['442'], $align_options, $data['blog_ialign'], array('inline'=>1));
	}
	closeside();

	echo form_textarea('blog_blog', $locale['425'], $data['blog_blog'], $fusion_mce);

	echo form_textarea('blog_extended', $locale['426'], $data['blog_extended'], $fusion_mce);

	echo "</div>\n";
	echo "<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
	openside('');
	echo form_select_tree("blog_cat", $locale['423'], $data['blog_cat'], array("parent_value" => $locale['424'], "query" => (multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."'" : "")), DB_BLOG_CATS, "blog_cat_name", "blog_cat_id", "blog_cat_parent");
	echo form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default btn-sm m-r-10'));
	echo form_button('save', $locale['437'], $locale['437'], array('class' => 'btn-success btn-sm', 'icon'=>'fa fa-check-square-o'));
	closeside();

	openside('');
	echo form_select('blog_keywords', $locale['443'], array(), $data['blog_keywords'], array('max_length' => 320, 'width'=>'100%', 'error_text' => $locale['457'], 'tags'=>1, 'multiple' => 1));
	closeside();

	openside('');
	echo "<div class='pull-left m-r-10 display-inline-block'>\n";
	echo form_datepicker('blog_start', $locale['427'], $data['blog_start'], array('placeholder' => $locale['429']));
	echo "</div>\n<div class='pull-left m-r-10 display-inline-block'>\n";
	echo form_datepicker('blog_end', $locale['428'], $data['blog_end'], array('placeholder' => $locale['429']));
	echo "</div>\n";
	closeside();

	openside('');
	if (multilang_table("BL")) {
		echo form_select('blog_language', $locale['global_ML100'], $language_opts, $data['blog_language'], array('placeholder' => $locale['choose'], 'width' => '100%'));
	} else {
		echo form_hidden('', 'blog_language', 'blog_langugage', $data['blog_language']);
	}
	echo form_hidden('', 'blog_datestamp', 'blog_datestamp', $data['blog_datestamp']);
	echo form_select('blog_visibility',$locale['430'], $visibility_opts, $data['blog_visibility'], array('placeholder' => $locale['choose'], 'width' => '100%'));
	closeside();
	openside('');
	if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
		$sys = "";
		if ($settings['comments_enabled'] == "0" && $settings['ratings_enabled'] == "0") {
			$sys = $locale['455'];
		} elseif ($settings['comments_enabled'] == "0") {
			$sys = $locale['453'];
		} else {
			$sys = $locale['454'];
		}
		echo "<span class='required m-r-5'>*</span>".sprintf($locale['452'], $sys)."</span><br/>\n";
	}
	echo form_checkbox('blog_draft', $locale['431'], $data['blog_draft'], array('class'=>'m-b-0'));
	echo form_checkbox('blog_sticky', $locale['432'], $data['blog_sticky'], array('class'=>'m-b-0'));
	if (!$tinyMce) {
		echo form_checkbox('blog_breaks', $locale['433'], $data['blog_breaks'], array('class'=>'m-b-0'));
	}
	echo form_checkbox('blog_allow_comments', $locale['434'], $data['blog_allow_comments'], array('class'=>'m-b-0'));
	echo form_checkbox('blog_allow_ratings', $locale['435'], $data['blog_allow_ratings'], array('class'=>'m-b-0'));
	closeside();
	if (isset($_GET['action']) && isset($_GET['blog_id']) && isnum($_GET['blog_id']) || (isset($_POST['preview']) && (isset($_POST['blog_id']) && isnum($_POST['blog_id']))) || (isset($_GET['blog_id']) && isnum($_GET['blog_id']))) {
		$blog_id = isset($_GET['blog_id']) && isnum($_GET['blog_id']) ? $_GET['blog_id'] : '';
		echo form_hidden('', 'blog_id', 'blog_id', $blog_id);
	}
	echo "</div>\n</div>\n";
	echo form_button('preview', $locale['436'], $locale['436'], array('class' => 'btn-default m-r-10'));
	echo form_button('save', $locale['437'], $locale['437'], array('class' => 'btn-success', 'icon'=>'fa fa-check-square-o'));
	echo closeform();
	echo "</div>\n";
}
