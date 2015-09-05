<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: downloads.php
| Author: Nick Jones (Digitanium)
| Co-Author: PHP-Fusion Development Team
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
pageAccess('D');
require_once THEMES."templates/admin_header.php";
include INFUSIONS."downloads/locale/".LOCALESET."downloads_admin.php";
include LOCALE.LOCALESET."admin/settings.php";
require_once INCLUDES."infusions_include.php";
$dl_settings = get_settings("downloads");
add_breadcrumb(array('link' => FUSION_SELF.$aidlink, 'title' => $locale['download_0001']));

$allowed_section = array("downloads", "download_form", "download_settings" , "download_category", "submissions");
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'downloads';
$_GET['download_cat_id'] = isset($_GET['download_cat_id']) && isnum($_GET['download_cat_id']) ? $_GET['download_cat_id'] : 0;

$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') && isset($_GET['download_id']) ? true : false;
$catEdit = isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['cat_id']) ? true : false;
// master template
$master_tab_title['title'][] = $locale['download_0000'];
$master_tab_title['id'][] = "downloads";
$master_tab_title['icon'][] = "";

$master_tab_title['title'][] = $edit ? $locale['download_0003'] : $locale['download_0002'];
$master_tab_title['id'][] = "download_form";
$master_tab_title['icon'][] = "";

$master_tab_title['title'][] = $catEdit ? $locale['download_0021'] : $locale['download_0022'];
$master_tab_title['id'][] = "download_category";
$master_tab_title['icon'][] = "";

$master_tab_title['title'][] = $locale['download_settings'];
$master_tab_title['id'][] = "download_settings";
$master_tab_title['icon'][] = "";

$master_tab_title['title'][] = $locale['download_0049'];
$master_tab_title['id'][] = "submissions";
$master_tab_title['icon'][] = "";

opentable($locale['download_0001']);
echo opentab($master_tab_title, $_GET['section'], "download_admin", true);
switch($_GET['section']) {
	case "download_category":
		add_breadcrumb(array('link' => ADMIN."download_cats.php".$aidlink, 'title' => $locale['download_0001']));
		include "admin/download_cats.php";
		break;
	case "download_settings":
		add_breadcrumb(array('link' => '', 'title' => $locale['download_settings']));
		include "admin/download_settings.php";
		break;
	case "submissions":
		add_breadcrumb(array("link"=>"", "title"=>$locale['download_0049']));
		include "admin/download_submissions.php";
		break;
	case "download_form":
		add_breadcrumb(array('link' => '', 'title' => $edit ? $locale['download_0003'] : $locale['download_0002']));
		if (dbcount("('download_cat_id')", DB_DOWNLOAD_CATS, "")) {
			include "admin/downloads.php";
		} else {
			echo "<div class='well text-center'>\n";
			echo "".$locale['download_0251']."<br />\n".$locale['download_0252']."<br />\n";
			echo "<a href='".INFUSIONS."downloads/downloads_admin.php".$aidlink."&amp;section=download_category'>".$locale['download_0253']."</a>".$locale['download_0254'];
			echo "</div>\n";
		}
		break;
	default:
		download_listing();
		break;
}
echo closetab();
require_once THEMES."templates/footer.php";

/* Download Listing */
function download_listing() {
	global $aidlink, $locale;

	$limit = 15;
	$total_rows = dbcount("(download_id)", DB_DOWNLOADS, "");
	$rowstart = isset($_GET['rowstart']) && ($_GET['rowstart'] <= $total_rows) ? $_GET['rowstart'] : 0;

	// add a filter browser
	$catOpts['all'] = $locale['download_0004'];

	$categories = dbquery("select download_cat_id, download_cat_name
				from ".DB_DOWNLOAD_CATS." ".(multilang_table("DL") ? "where download_cat_language='".LANGUAGE."'" : "")."");
	if (dbrows($categories)>0) {
		while ($cat_data = dbarray($categories)) {
			$catOpts[$cat_data['download_cat_id']] = $cat_data['download_cat_name'];
		}
	}
	// prevent xss
	$catFilter = "";
	if (isset($_GET['filter_cid']) && isnum($_GET['filter_cid']) && isset($catOpts[$_GET['filter_cid']])) {
		if ($_GET['filter_cid'] > 0) {
			$catFilter = "download_cat='".intval($_GET['filter_cid'])."'";
		}
	}

	$langFilter = multilang_table("DL") ? "download_cat_language='".LANGUAGE."'" : "";

	if ($catFilter && $langFilter) {
		$filter = $catFilter." AND ".$langFilter;
	} else {
		$filter = $catFilter.$langFilter;
	}

	$result = dbquery("
	SELECT d.*, dc.download_cat_id, dc.download_cat_name
	FROM ".DB_DOWNLOADS." d
	INNER JOIN ".DB_DOWNLOAD_CATS." dc on d.download_cat = dc.download_cat_id
	".($filter ? "WHERE ".$filter : "")."
	ORDER BY dc.download_cat_sorting LIMIT $rowstart, $limit
	");

	$rows = dbrows($result);
	echo "<div class='clearfix'>\n";
	echo "<span class='pull-right m-t-10'>".sprintf($locale['download_0005'], $rows, $total_rows)."</span>\n";

	if (!empty($catOpts) >0 && $total_rows >0) {
		echo "<div class='pull-left m-t-5 m-r-10'>".$locale['download_0010']."</div>\n";
		echo "<div class='dropdown pull-left m-r-10' style='position:relative'>\n";
		echo "<a class='dropdown-toggle btn btn-default btn-sm' style='width: 200px;' data-toggle='dropdown'>\n<strong>\n";
		if (isset($_GET['filter_cid']) && isset($catOpts[$_GET['filter_cid']])) {
			echo $catOpts[$_GET['filter_cid']];
		} else {
			echo $locale['download_0011'];
		}
		echo " <span class='caret'></span></strong>\n</a>\n";
		echo "<ul class='dropdown-menu' style='max-height:180px; width:200px; overflow-y: auto'>\n";
		foreach($catOpts as $catID => $catName) {
			$active = isset($_GET['filter_cid']) && $_GET['filter_cid'] == $catID ? true : false;
			echo "<li".($active ? " class='active'" : "").">\n<a class='text-smaller' href='".clean_request("filter_cid=".$catID, array("section", "rowstart", "aid"), true)."'>\n";
			echo $catName;
			echo "</a>\n</li>\n";
		}
		echo "</ul>\n";
		echo "</div>\n";
	}
	if ($total_rows > $rows) {
		echo makepagenav($rowstart, $limit, $total_rows, $limit, clean_request("", array("aid","section"), true)."&amp;");
	}
	echo "</div>\n";

	echo "<ul class='list-group m-10'>\n";
	if ($rows > 0) {
		while ($data2 = dbarray($result)) {
			$download_url = '';
			if (!empty($data2['download_file']) && file_exists(DOWNLOADS."files/".$data2['download_file'])) {
				$download_url = INFUSIONS."downloads/downloads.php?file_id=".$data2['download_id'];
			} elseif (!strstr($data2['download_url'], "http://") && !strstr($data2['download_url'], "../")) {
				$download_url = BASEDIR.$data2['download_url'];
			}
			echo "<li class='list-group-item'>\n";
			echo "<div class='pull-right'>\n".$locale['download_0207']."
			<a style='width:auto;' href='".FUSION_SELF.$aidlink."&amp;section=download_category&amp;action=edit&amp;cat_id=".$data2['download_cat_id']."' class='badge'>
			".$data2['download_cat_name']."</a>
			</div>\n";
			echo "<div class='pull-left m-r-10'>\n";
			echo thumbnail(DOWNLOADS."images/".$data2['download_image_thumb'], '50px');
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo "<span class='strong text-dark'>".$data2['download_title']."</span><br/>\n";
			$dlText = strip_tags(html_entity_decode($data2['download_description_short']));
			echo fusion_first_words($dlText, '50');
			echo "<div class='m-t-5'>\n";
			echo "<a class='m-r-10' target='_blank' href='$download_url'>".$locale['download_0226']."</a>\n";
			echo "<a class='m-r-10' href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;section=download_form&amp;download_id=".$data2['download_id']."'>".$locale['edit']."</a>\n";
			echo "<a  class='m-r-10' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;section=download_form&amp;download_id=".$data2['download_id']."' onclick=\"return confirm('".$locale['download_0255']."');\">".$locale['delete']."</a>\n";
			echo "</div>\n";
			echo "</div>\n";
			echo "</li>\n";
		}
	} else {
		echo "<li class='panel-body text-center'>\n";
		echo $locale['download_0250'];
		echo "</li>\n";
	}
	echo "</ul>\n";
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