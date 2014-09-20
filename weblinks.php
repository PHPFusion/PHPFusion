<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks.php
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
include LOCALE.LOCALESET."weblinks.php";
if (isset($_GET['weblink_id']) && isnum($_GET['weblink_id'])) {
	$res = 0;
	if ($data = dbarray(dbquery("SELECT weblink_url,weblink_cat FROM ".DB_WEBLINKS." WHERE weblink_id='".$_GET['weblink_id']."'"))) {
		$cdata = dbarray(dbquery("SELECT weblink_cat_access FROM ".DB_WEBLINK_CATS." WHERE weblink_cat_id='".$data['weblink_cat']."'"));
		if (checkgroup($cdata['weblink_cat_access'])) {
			$res = 1;
			$result = dbquery("UPDATE ".DB_WEBLINKS." SET weblink_count=weblink_count+1 WHERE weblink_id='".$_GET['weblink_id']."'");
			redirect($data['weblink_url']);
		}
	}
	if ($res == 0) {
		redirect(FUSION_SELF);
	}
}
add_to_title($locale['global_200'].$locale['400']);
if (!isset($_GET['cat_id']) || !isnum($_GET['cat_id'])) {
	opentable($locale['400']);
	$result = dbquery("SELECT weblink_cat_id, weblink_cat_name, weblink_cat_description FROM ".DB_WEBLINK_CATS." ".(multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('weblink_cat_access')." ORDER BY weblink_cat_name");
	$rows = dbrows($result);
	if ($rows != 0) {
		$counter = 0;
		$columns = 2;
		echo "<div class='row m-0'>\n";

		while ($data = dbarray($result)) {
			$image = get_image('folder');
			$num = dbcount("(weblink_cat)", DB_WEBLINKS, "weblink_cat='".$data['weblink_cat_id']."'");
			if ($counter != 0 && ($counter%$columns == 0)) {
				echo "</div>\n<div class='row m-0'>\n";
			}
			echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6 p-t-20'>\n";

				echo "<div class='media'>\n";
				echo "<a class='pull-left flleft' href='".FUSION_SELF."?cat_id=".$data['weblink_cat_id']."'>\n";
				echo "<img class='media-object' src='".$image."' alt='".$data['weblink_cat_name']."'>\n";
				echo "</a>\n";
				echo "<div class='media-body'>\n";
				echo "<h4 class='media-heading'><a href='".FUSION_SELF."?cat_id=".$data['weblink_cat_id']."'>".$data['weblink_cat_name']."</a> <span class='small'>$num</span></h4>\n";
				if ($data['weblink_cat_description'] != "") {
					echo "<span class='small'>".$data['weblink_cat_description']."</span>";
				}
				echo "</div>\n</div>\n";
			echo "</div>\n";
			$counter++;
		}
		echo "</div>\n";
	} else {
		echo "<div style='text-align:center'><br />\n".$locale['430']."<br /><br />\n</div>\n";
	}
	closetable();
} else {
	$res = 0;
	$result = dbquery("SELECT weblink_cat_name, weblink_cat_sorting, weblink_cat_access FROM ".DB_WEBLINK_CATS." ".(multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."' AND" : "WHERE")." weblink_cat_id='".$_GET['cat_id']."'");
	if (dbrows($result) != 0) {
		$cdata = dbarray($result);
		if (checkgroup($cdata['weblink_cat_access'])) {
			$res = 1;
			add_to_title($locale['global_201'].$cdata['weblink_cat_name']);

			// go for breadcrumbs.
			echo "<ol class='breadcrumb'>\n";
			echo "<li><a href='".BASEDIR."weblinks.php'>".$locale['400']."</a></li>\n";
			echo "<li>".$cdata['weblink_cat_name']."</a></li>\n";
			echo "</ol>\n";

			opentable($locale['400'].": ".$cdata['weblink_cat_name']);
			$rows = dbcount("(weblink_id)", DB_WEBLINKS, "weblink_cat='".$_GET['cat_id']."'");
			if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
				$_GET['rowstart'] = 0;
			}
			if ($rows != 0) {
				$result = dbquery("SELECT weblink_id, weblink_name, weblink_description, weblink_datestamp, weblink_count FROM ".DB_WEBLINKS." WHERE weblink_cat='".$_GET['cat_id']."' ORDER BY ".$cdata['weblink_cat_sorting']." LIMIT ".$_GET['rowstart'].",".$settings['links_per_page']);
				$numrows = dbrows($result);
				$i = 1;
				while ($data = dbarray($result)) {
					if ($data['weblink_datestamp']+604800 > time()+($settings['timeoffset']*3600)) {
						$new = " <span class='label label-success m-r-10'>".$locale['410']."</span>";
					} else {
						$new = "";
					}
					echo "<aside class='display-inline-block ".($i > 1 ? 'm-t-20' : '' )."' style='width:100%;'>\n";
					echo "<h4><a href='".BASEDIR."weblinks.php?cat_id=".$_GET['cat_id']."&amp;weblink_id=".$data['weblink_id']."' target='_blank'><strong>".$data['weblink_name']."</strong></a></h4>\n";
					echo $new;
					echo "<span class='text-lighter display-inline m-r-20'><strong>".$locale['411']."</strong> ".showdate("shortdate", $data['weblink_datestamp'])."</span>\n";
					echo "<span class='text-lighter display-inline'><strong>".$locale['412']."</strong> ".$data['weblink_count']."</span>\n";
					if ($data['weblink_description'] != "") echo "<div class='weblink-text text-smaller'>".nl2br(stripslashes($data['weblink_description']))."</div>\n";
					echo "</aside>\n";
					$i++;
				}
				closetable();
				if ($rows > $settings['links_per_page']) {
					echo "<div class='text-center m-t-10' align='center'>\n".makepagenav($_GET['rowstart'], $settings['links_per_page'], $rows, 3, BASEDIR."weblinks.php?cat_id=".$_GET['cat_id']."&amp;")."\n</div>\n";
				}
			} else {
				echo $locale['431']."\n";
				closetable();
			}
		}
	}
	if ($res == 0) {
		redirect(FUSION_SELF);
	}
}
require_once THEMES."templates/footer.php";
?>