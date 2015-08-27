<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks_admin.php
| Author: PHP-Fusion Developer Team
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
pageAccess('W');
require_once THEMES."templates/admin_header.php";
require_once INCLUDES."html_buttons_include.php";
require_once INCLUDES."infusions_include.php";
include INFUSIONS."weblinks/locale/".LOCALESET."weblinks_admin.php";
$wl_settings = get_settings("weblinks");

$allowed_pages = array(
	"weblinks_form",
	"weblinks_category",
	"submissions",
	"settings"
);
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_pages) ? $_GET['section'] : 'weblinks';
$weblink_edit = isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['weblink_id']) && isnum($_GET['weblink_id']) ? TRUE : FALSE;
$weblinkCat_edit = isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['cat_id']) && isnum($_GET['cat_id']) ? TRUE : FALSE;
$master_title['title'][] = $locale['wl_0003'];
$master_title['id'][] = 'weblinks';
$master_title['icon'] = '';
$master_title['title'][] = $weblink_edit ? $locale['wl_0002'] : $locale['wl_0001'];
$master_title['id'][] = 'weblinks_form';
$master_title['icon'] = '';
$master_title['title'][] = $weblinkCat_edit ? $locale['wl_0005'] : $locale['wl_0004'];
$master_title['id'][] = 'weblinks_category';
$master_title['icon'] = '';
$master_title['title'][] = $locale['wl_0500'];
$master_title['id'][] = 'submissions';
$master_title['icon'] = '';
$master_title['title'][] = $locale['wl_0600'];
$master_title['id'][] = 'settings';
$master_title['icon'] = '';
$tab_active = $_GET['section'];
opentable($locale['wl_0200']);
echo opentab($master_title, $tab_active, "weblinks_admin", 1);

switch ($_GET['section']) {
	case "weblinks_form":
		add_breadcrumb(array('link'=>"", 'title'=>$master_title['title'][1]));
		include "admin/weblinks.php";
		break;
	case "weblinks_category":
		add_breadcrumb(array('link'=>"", 'title'=>$master_title['title'][2]));
		include "admin/weblinks_cats.php";
		break;
	case "settings":
		add_breadcrumb(array('link'=>"", 'title'=>$locale['wl_0600']));
		include "admin/weblinks_settings.php";
		break;
	case "submissions":
		add_breadcrumb(array('link'=>"", 'title'=>$locale['wl_0500']));
		include "admin/weblinks_submissions.php";
		break;
	default:
		weblinks_listing();
}
echo closetab();
closetable();
require_once THEMES."templates/footer.php";
/**
 * Weblink Directory Listing
 */
function weblinks_listing() {
	global $aidlink, $locale;
	// do a filter here
	$limit = 15;
	$total_rows = dbcount("(weblink_id)", DB_WEBLINKS);
	$rowstart = isset($_GET['rowstart']) && ($_GET['rowstart'] <= $total_rows) ? $_GET['rowstart'] : 0;
	// add a filter browser
	$catOpts = array(
		"all" => $locale['wl_0402'],
	);
	$categories = dbquery("select weblink_cat_id, weblink_cat_name
				from ".DB_WEBLINK_CATS." ".(multilang_table("WL") ? "where weblink_cat_language='".LANGUAGE."'" : "")."");
	if (dbrows($categories) > 0) {
		while ($cat_data = dbarray($categories)) {
			$catOpts[$cat_data['weblink_cat_id']] = $cat_data['weblink_cat_name'];
		}
	}
	// prevent xss
	$catFilter = "";
	if (isset($_GET['filter_cid']) && isnum($_GET['filter_cid']) && isset($catOpts[$_GET['filter_cid']])) {
		if ($_GET['filter_cid'] > 0) {
			$catFilter = "and weblink_cat='".intval($_GET['filter_cid'])."'";
		} else {
			$catFilter = "";
		}
	}

	$result = dbquery("
	select w.*, cat.weblink_cat_id, cat.weblink_cat_name
	FROM ".DB_WEBLINKS." w
	left join ".DB_WEBLINK_CATS." cat on cat.weblink_cat_id = w.weblink_cat
	WHERE ".(multilang_table("WL") ? "cat.weblink_cat_language='".LANGUAGE."'" : "")." ".$catFilter."
	order by weblink_name asc, weblink_datestamp desc LIMIT $rowstart, $limit
	");
	$rows = dbrows($result);

	if ($rows > 0) {

		echo "<div class='clearfix m-b-20'>\n";
		echo "<span class='pull-right m-t-10'>".sprintf($locale['wl_0501'], $rows, $total_rows)."</span>\n";
		if (!empty($catOpts) > 0 && $total_rows > 0) {
			echo "<div class='pull-left m-t-10 m-r-10'>".$locale['wl_0400']."</div>\n";
			echo "<div class='dropdown pull-left m-t-5 m-r-10' style='position:relative'>\n";
			echo "<a class='dropdown-toggle btn btn-default btn-sm' style='width: 200px;' data-toggle='dropdown'>\n<strong>\n";
			if (isset($_GET['filter_cid']) && isset($catOpts[$_GET['filter_cid']])) {
				echo $catOpts[$_GET['filter_cid']];
			} else {
				echo $locale['wl_0401'];
			}
			echo " <span class='caret'></span></strong>\n</a>\n";
			echo "<ul class='dropdown-menu' style='max-height:180px; width:200px; overflow-y: auto'>\n";
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

		echo "<table class='table table-responsive center'>\n<thead>\n";
		echo "<tr>\n";
		echo "<th class='col-xs-4'>".$locale['wl_0200']."</th>\n";
		echo "<th>".$locale['wl_0201']."</th>\n";
		echo "<th>".$locale['wl_0203']."</th>\n";
		echo "<th>".$locale['wl_0204']."</th>\n";
		echo "<th>".$locale['wl_0208']."</th>\n";
		echo "</tr>\n</thead>\n<tbody>\n";

		while ($data = dbarray($result)) {
			echo "<tr>\n";
			echo "<td>".$data['weblink_name']."</td>\n";
			echo "<td>".$data['weblink_cat_name']."</td>\n";
			echo "<td><a href='".$data['weblink_url']."' target='_blank'>".$data['weblink_name']."</a></td>\n";
			echo "<td>".$data['weblink_id']."</td>\n";
			echo "<td>\n";
			echo "<div class='btn-group'>\n";
			echo "<a class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=weblinks_form&amp;action=edit&amp;weblink_id=".$data['weblink_id']."'>".$locale['wl_0205']."</a>";
			echo "<a class='btn btn-default btn-sm'  href='".FUSION_SELF.$aidlink."&amp;section=weblinks_form&amp;action=delete&amp;weblink_id=".$data['weblink_id']."&amp;weblink_id=".$data['weblink_id']."' onclick=\"return confirm('".$locale['wl_0303']."');\">".$locale['wl_0206']."</a>
			</div>\n</td>\n";
			echo "</tr>\n";
		}
		echo "</tbody>\n</table>\n";
	} else {
		echo "<div class='well m-t-20 text-center'>\n".$locale['wl_0207']."<br />";
	}
}