<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles.php
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
pageAccess("A");
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
include INFUSIONS."articles/locale/".LOCALESET."articles_admin.php";
require_once INCLUDES."infusions_include.php";
add_breadcrumb(array('link' => INFUSIONS.'articles/articles_admin.php'.$aidlink, 'title' => $locale['articles_0001']));
$article_settings = get_settings("article");

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['article_id']) && isnum($_GET['article_id'])) {
	$del_data['article_id'] = $_GET['article_id'];
	$result = dbquery("SELECT article_id, article_subject FROM ".DB_ARTICLES." WHERE article_id='".$del_data['article_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		$result = dbquery("DELETE FROM ".DB_ARTICLES." WHERE article_id='".$data['article_id']."'");
		$result = dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_item_id='".$data['article_id']."' and comment_type='A'");
		$result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='".$data['article_id']."' and rating_type='A'");
		dbquery_insert(DB_ARTICLES, $data, 'delete');
		addNotice('warning', $locale['articles_0102']);
		redirect(FUSION_SELF.$aidlink);
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}
$allowed_pages = array(
	"article",
	"article_category",
	"article_form",
	"submissions",
	"settings"
);
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_pages) ? $_GET['section'] : "article";
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['article_id']) && isnum($_GET['article_id'])) ? TRUE : FALSE;
$edit_cat = (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['cat_id']) && isnum($_GET['cat_id'])) ? TRUE : FALSE;

$master_title['title'][] = $locale['articles_0000'];
$master_title['id'][] = 'article';
$master_title['icon'] = '';

$master_title['title'][] = $edit ? $locale['articles_0003'] : $locale['articles_0002'];
$master_title['id'][] = 'article_form';
$master_title['icon'] = '';

$master_title['title'][] = $edit_cat ? $locale['articles_0022'] : $locale['articles_0020'];
$master_title['id'][] = 'article_category';
$master_title['icon'] = '';

$master_title['title'][] = $locale['articles_0030'];
$master_title['id'][] = 'settings';
$master_title['icon'] = '';

$master_title['title'][] = $locale['articles_0040'];
$master_title['id'][] = 'submissions';
$master_title['icon'] = '';

$tab_active = isset($_GET['section']) && in_array($_GET['section'], $master_title['id']) ? $_GET['section'] : "article";

opentable($locale['articles_0001']);
echo opentab($master_title, $tab_active, 'article', 1);
switch ($_GET['section']) {
	case "article_category":
		add_breadcrumb(array("link" => FUSION_REQUEST, "title" => $master_title['title'][2]));
		include "admin/article_cat.php";
		break;
	case "settings":
		add_breadcrumb(array('link' => "", 'title' => $locale['articles_0030']));
		include "admin/article_settings.php";
		break;
	case "article_form":
		if (dbcount("(article_cat_id)", DB_ARTICLE_CATS, (multilang_table("AR") ? "article_cat_language='".LANGUAGE."'" : ""))) {
			add_breadcrumb(array('link' => '', 'title' => $edit ? $locale['articles_0003'] : $locale['articles_0002']));
			include "admin/article.php";
		} else {
			opentable($locale['articles_0001']);
			echo "<div class='well text-center'>".$locale['articles_0252']."<br />\n".$locale['articles_0253']."<br />\n";
			echo "<a href='".clean_request("section=article_category", array("aid"), TRUE)."'>".$locale['articles_0254']."</a>".$locale['articles_0255']."</div>\n";
			closetable();
		}
		break;
	case "submissions":
		include "admin/article_submissions.php";
		break;
	default:
		article_listing();
}
echo closetab();
closetable();
require_once THEMES."templates/footer.php";


function article_listing() {

	global $aidlink, $locale;

	// Remodel display results into straight view instead category container sorting.
	// consistently monitor sql results rendertime. -- Do not Surpass 0.15
	// all blog are uncategorized by default unless specified.

    $limit = 15;
	$total_rows = dbcount("(article_id)", DB_ARTICLES, (multilang_table("AR") ? "article_language='".LANGUAGE."'" : ""));
	$rowstart = isset($_GET['rowstart']) && ($_GET['rowstart'] <= $total_rows) ? $_GET['rowstart'] : 0;
	// add a filter browser
	$catOpts = array(
		"all" => $locale['articles_0023'],
	);
	$categories = dbquery("select article_cat_id, article_cat_name
				from ".DB_ARTICLE_CATS." ".(multilang_table("AR") ? "where article_cat_language='".LANGUAGE."'" : "")."");
	if (dbrows($categories) > 0) {
		while ($cat_data = dbarray($categories)) {
			$catOpts[$cat_data['article_cat_id']] = $cat_data['article_cat_name'];
		}
	}
	// prevent xss
	$catFilter = "";
	if (isset($_GET['filter_cid']) && isnum($_GET['filter_cid']) && isset($catOpts[$_GET['filter_cid']])) {
		if ($_GET['filter_cid'] > 0) {
			$catFilter = "and ".in_group("article_cat", intval($_GET['filter_cid']));
		}
	}

	$langFilter = multilang_table("AR") ? "article_language='".LANGUAGE."'" : "";

	if ($catFilter && $langFilter) {
		$filter = $catFilter." AND ".$langFilter;
	} else {
		$filter = $catFilter.$langFilter;
	}

	$result = dbquery("
	SELECT a.article_id, a.article_cat, a.article_subject, a.article_snippet, a.article_draft,
	cat.article_cat_id, cat.article_cat_name
	FROM ".DB_ARTICLES." a
	LEFT JOIN ".DB_ARTICLE_CATS." cat on cat.article_cat_id=a.article_cat
	".($filter ? "WHERE ".$filter : "")."
	ORDER BY article_draft DESC, article_datestamp DESC LIMIT $rowstart, $limit
	");

	$rows = dbrows($result);

    echo "<div class='clearfix'>\n";
	echo "<span class='pull-right m-t-10'>".sprintf($locale['articles_0024'], $rows, $total_rows)."</span>\n";
	if (!empty($catOpts) > 0 && $total_rows > 0) {
		echo "<div class='pull-left m-t-5 m-r-10'>".$locale['articles_0025']."</div>\n";
		echo "<div class='dropdown pull-left m-r-10' style='position:relative'>\n";
		echo "<a class='dropdown-toggle btn btn-default btn-sm' style='width: 200px;' data-toggle='dropdown'>\n<strong>\n";
		if (isset($_GET['filter_cid']) && isset($catOpts[$_GET['filter_cid']])) {
			echo $catOpts[$_GET['filter_cid']];
		} else {
			echo $locale['articles_0026'];
		}
		echo " <span class='caret'></span></strong>\n</a>\n";
		echo "<ul class='dropdown-menu' style='max-height:180px; width:200px; overflow-y: scroll'>\n";
		foreach ($catOpts as $catID => $catName) {
			$active = isset($_GET['filter_cid']) && $_GET['filter_cid'] == $catID ? TRUE : FALSE;
			echo "<li".($active ? " class='active'" : "").">\n<a class='text-smaller' href='".clean_request("filter_cid=".$catID, array(
					"section",
					"rowstart",
					"aid"
				), TRUE)."'>\n";
			echo $catName;
			echo "</a>\n</li>\n";
		}
		echo "</ul>\n";
		echo "</div>\n";
	}
	if ($total_rows > $rows) {
		echo makepagenav($rowstart, $limit, $total_rows, $limit, clean_request("", array(
									  "aid",
									  "section"
								  ), TRUE)."&amp;");
	}
	echo "</div>\n";
	echo "<ul class='list-group m-10'>\n";
	if ($rows > 0) {
		while ($data2 = dbarray($result)) {
			echo "<li class='list-group-item'>\n";
			echo "<div class='clearfix'>\n";
			echo "<div class='m-b-10 pull-right'><strong>".$locale['articles_0340'].":</strong>\n";
			echo "<a class='display-inline-block badge' style='width:auto;' href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data2['article_cat_id']."&amp;section=article_category'>";
			echo $data2['article_cat_name'];
			echo "</a>";
			echo "</div>\n";
			echo "<span class='strong text-dark'>".$data2['article_subject']."</span>\n";
			echo "</div>\n";
			$articleText = strip_tags(parse_textarea($data2['article_snippet']));
			echo fusion_first_words($articleText, '50');
			echo "<div class='block m-t-10'>
			<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;section=article_form&amp;article_id=".$data2['article_id']."'>".$locale['edit']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;section=article&amp;article_id=".$data2['article_id']."'
			onclick=\"return confirm('".$locale['articles_0251']."');\">".$locale['delete']."</a>\n";
			echo "</div>\n";
			echo "</li>\n";
		}
	} else {
		echo "<div class='panel-body text-center'>\n";
		echo $locale['articles_0343'];
		echo "</div>\n";
	}
	echo "</ul>\n";
	if ($total_rows > $rows) echo makepagenav($rowstart, $limit, $total_rows, $limit, clean_request("", array(
														   "aid",
														   "section"
													   ), TRUE)."&amp;");
}

/*
add_to_jquery("
            function DeleteArticle() { return confirm('".$locale['articles_0251']."');}
            $('#save, #preview').bind('click', function(e) {
            var subject = $('#subject').val();
            if (subject == '') { alert('".$locale['articles_0250']."'); return false; }
            });
            "); */
