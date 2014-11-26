<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blog_cats.php
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
define('BLOG_CAT', TRUE);

require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."blog_cats.php";
add_to_title($locale['global_200'].$locale['400']);
opentable($locale['400']);
if (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
	$res = 0;
	$result = dbquery("SELECT blog_cat_name, blog_cat_id FROM ".DB_BLOG_CATS." ".(multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."' AND" : "WHERE")." blog_cat_id='".$_GET['cat_id']."'");
	if (dbrows($result) || $_GET['cat_id'] == 0) {
		$data = dbarray($result);
		$rows = dbcount("(blog_id)", DB_BLOG, "blog_cat='".$_GET['cat_id']."' AND ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().") AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'");
		if ($rows) {
			$res = 1;
			// Render the breadcrumbs
			echo render_breadcrumbs($data);
			echo "<!--pre_blog_cat--><table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>\n";
			if ($_GET['cat_id'] != 0) {
				echo "<tr>\n<td width='150' class='tbl1' style='vertical-align:top'><!--blog_cat_image--><img class='blog-category' src='".get_image("nc_".$data['blog_cat_name'])."' alt='".$data['blog_cat_name']."' /><br /><br />\n";
				echo "<strong>".$locale['401']."</strong> ".$data['blog_cat_name']."<br />\n<strong>".$locale['402']."</strong> $rows</td>\n";
				echo "<td class='tbl1' style='vertical-align:top'>\n";
			} else {
				echo "<tr>\n<td width='150' class='tbl1' style='vertical-align:top'>".$locale['403']."<br />\n";
				echo "<strong>".$locale['401']."</strong> $rows</td>\n<td class='tbl1' style='vertical-align:top'><!--blog_cat_blog-->\n";
			}
			$result2 = dbquery("SELECT blog_id, blog_subject FROM ".DB_BLOG." ".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." blog_cat='".$_GET['cat_id']."' AND ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().") AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0' ORDER BY blog_datestamp DESC");
			while ($data2 = dbarray($result2)) {
				echo THEME_BULLET." <a href='blog.php?readmore=".$data2['blog_id']."'>".$data2['blog_subject']."</a><br />\n";
			}
			echo "</td>\n</tr>\n<tr>\n<td colspan='2' class='tbl1' style='text-align:center'>".THEME_BULLET." <a href='".FUSION_SELF."'>".$locale['406']."</a>";
			echo "</td>\n</tr>\n</table><!--sub_blog_cat-->\n";
		}
	}
	if (!$res) {
		redirect(FUSION_SELF);
	}
} else {
	$res = 0;
	$result = dbquery("SELECT blog_cat_id, blog_cat_name FROM ".DB_BLOG_CATS." ".(multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."'" : "")." ORDER BY blog_cat_id");
	if (dbrows($result)) {
		echo "<!--pre_blog_cat_idx--><table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>\n";
		while ($data = dbarray($result)) {
			$rows = dbcount("(blog_id)", DB_BLOG, "blog_cat='".$data['blog_cat_id']."' AND ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().") AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'");
			echo "<tr>\n<td width='150' class='tbl1' style='vertical-align:top'><!--blog_cat_image--><img class='blog-category' src='".get_image("nc_".$data['blog_cat_name'])."' alt='".$data['blog_cat_name']."' /><br /><br />\n";
			echo "<strong>".$locale['401']."</strong> ".$data['blog_cat_name']."<br />\n<strong>".$locale['402']."</strong> $rows</td>\n";
			echo "<td class='tbl1' style='vertical-align:top'><!--blog_cat_blog-->\n";
			if ($rows) {
				$result2 = dbquery("SELECT blog_id, blog_subject FROM ".DB_BLOG." ".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." blog_cat='".$data['blog_cat_id']."' AND ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().") AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0' ORDER BY blog_datestamp DESC LIMIT 10");
				while ($data2 = dbarray($result2)) {
					echo THEME_BULLET." <a href='blog.php?readmore=".$data2['blog_id']."'>".$data2['blog_subject']."</a><br />\n";
				}
				if ($rows > 10) {
					echo "<div style='text-align:right'>".THEME_BULLET." <a href='".FUSION_SELF."?cat_id=".$data['blog_cat_id']."'>".$locale['405']."</a></div>\n";
				}
			} else {
				echo THEME_BULLET." ".$locale['404']."\n";
			}
			echo "</td>\n</tr>\n";
		}
		$res = 1;
	}
	$result = dbquery("SELECT * FROM ".DB_BLOG." WHERE blog_cat='0' AND ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().") AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0' ORDER BY blog_datestamp DESC LIMIT 10");
	if (dbrows($result)) {
		if ($res == 0) {
			echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>\n";
		}
		$nrows = dbcount("(blog_id)", DB_BLOG, "blog_cat='0' AND ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().") AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'");
		echo "<tr>\n<td width='150' class='tbl1' style='vertical-align:top'>".$locale['403']."<br />\n";
		echo "<strong>".$locale['402']."</strong> $nrows</td>\n<td class='tbl1' style='vertical-align:top'>\n";
		while ($data = dbarray($result)) {
			echo THEME_BULLET." <a href='blog.php?readmore=".$data['blog_id']."'>".$data['blog_subject']."</a><br />\n";
		}
		$res = 1;
		if ($nrows > 10) {
			echo "<div style='text-align:right'>".THEME_BULLET." <a href='".FUSION_SELF."?cat_id=0'>".$locale['405']."</a></div>\n";
		}
		echo "</td>\n</tr>\n";
	}
	if ($res == 1) {
		echo "</table><!--sub_blog_cat_idx-->\n";
	} else {
		echo "<div style='text-align:center'><br />\n".$locale['407']."<br /><br />\n</div>\n";
	}
}
closetable();
require_once THEMES."templates/footer.php";
?>