<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: downloads.php
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
include LOCALE.LOCALESET."downloads.php";

add_to_title($locale['global_200'].$locale['400']);

// download the file
if (isset($_GET['file_id']) && isnum($_GET['file_id'])) {
	$download_id = stripinput($_GET['file_id']);
	$res = 0;
	if ($data = dbarray(dbquery("SELECT download_url, download_file, download_cat FROM ".DB_DOWNLOADS." WHERE download_id='".$download_id."'"))) {
		$cdata = dbarray(dbquery("SELECT download_cat_access FROM ".DB_DOWNLOAD_CATS." WHERE download_cat_id='".$data['download_cat']."'"));
		if (checkgroup($cdata['download_cat_access'])) {
			$result = dbquery("UPDATE ".DB_DOWNLOADS." SET download_count=download_count+1 WHERE download_id='".$download_id."'");
			if (!empty($data['download_file']) && file_exists(DOWNLOADS.$data['download_file'])) {
				$res = 1;
				require_once INCLUDES."class.httpdownload.php";
				ob_end_clean();
				$object = new httpdownload;
				$object->set_byfile(DOWNLOADS.$data['download_file']);
				$object->use_resume = true;
				$object->download();
				exit;
			} elseif (!empty($data['download_url'])) {
				$res = 1;
				redirect($data['download_url']);
			}
		}
	}
	if ($res == 0) { redirect("downloads.php"); }
}
// Statistics
$dl_stats = "";
$i_alt = dbresult(dbquery("SELECT SUM(download_count) FROM ".DB_DOWNLOADS), 0);

$dl_stats .= "<table cellpadding='0' cellspacing='1' class='tbl-border' style='width:100%;'>\n";
$dl_stats .= "<tr>\n<td class='tbl2' valign='middle'><img src='".get_image("statistics")."' alt='".$locale['429']."' /></td>\n";
$dl_stats .= "<td width='100%' align='left' class='tbl1'>\n";
$dl_stats .= "<span class='small'>".$locale['415']." ".dbcount("(download_cat)", DB_DOWNLOADS)."</span><br />\n";
$dl_stats .= "<span class='small'>".$locale['440']." ".($i_alt ? $i_alt : "0")."</span><br />";

$result = dbquery(
		"SELECT td.download_id, td.download_title, td.download_count, td.download_cat,
				tc.download_cat_id, tc.download_cat_access
		FROM ".DB_DOWNLOADS." td
		LEFT JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
		WHERE ".groupaccess('download_cat_access')."
		ORDER BY download_count DESC LIMIT 0,1");

if (dbrows($result) != 0) {
	while ($data = dbarray($result)) {
		$download_title = $data['download_title'];
		$dl_stats .= "<span class='small'>".$locale['441'];
		$dl_stats .= " <a href='".FUSION_SELF."?download_id=".$data['download_id']."' title='".$download_title."' class='side'>".trimlink($data['download_title'], 100)."</a>";
		$dl_stats .= " [ ".$data['download_count']." ]</span><br />";
	}
}

$result = dbquery(
		"SELECT td.download_id, td.download_title, td.download_count, td.download_cat, td.download_datestamp,
				tc.download_cat_id, tc.download_cat_access
		FROM ".DB_DOWNLOADS." td
		LEFT JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
		WHERE ".groupaccess('download_cat_access')."
		ORDER BY download_datestamp DESC LIMIT 0,1");
if (dbrows($result) != 0) {
	while ($data = dbarray($result)) {
		$download_title = $data['download_title'];
		$dl_stats .= "<span class='small'>".$locale['442'];
		$dl_stats .= " <a href='".FUSION_SELF."?download_id=".$data['download_id']."' title='".$download_title."' class='side'>".trimlink($data['download_title'], 100)."</a>";
		$dl_stats .= " [ ".$data['download_count']." ]</span><br />";
	}
}
$dl_stats .= "</td>\n</tr>\n</table>\n";

// Filter form, list of existing cats and downloads
if (!isset($_GET['download_id']) || !isnum($_GET['download_id'])) {
	opentable($locale['400']);
	echo "<!--pre_download_idx-->\n";
	$cat_list_result = dbquery(
		"SELECT download_cat_id, download_cat_name
		FROM ".DB_DOWNLOAD_CATS." WHERE ".groupaccess('download_cat_access')."
		ORDER BY download_cat_name");
	$cats_list = ""; $filter = ""; $order_by = ""; $sort = ""; $getString = "";
	if (dbrows($cat_list_result)) {
		while ($cat_list_data = dbarray($cat_list_result)) {
			$sel = (isset($_GET['cat_id']) && $_GET['cat_id'] == $cat_list_data['download_cat_id'] ? " selected='selected'" : "");
			$cats_list .= "<option value='".$cat_list_data['download_cat_id']."'".$sel.">".$cat_list_data['download_cat_name']."</option>";
		}

		if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart']) || $_GET['rowstart'] > dbrows($cat_list_result)) { $_GET['rowstart'] = 0; }
		if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) && $_GET['cat_id'] != "all") {
			$filter .= " AND download_cat_id='".$_GET['cat_id']."'";
			$order_by_allowed = array("download_id", "download_user", "download_count", "download_datestamp");
			if (isset($_GET['orderby']) && in_array($_GET['orderby'], $order_by_allowed)) {
				$order_by = $_GET['orderby'];
				$getString .= "&amp;orderby=".$order_by;
			} else {
				$order_by = "";
			}
			if (isset($_GET['sort']) && $_GET['sort'] == "DESC") {
				$sort = "DESC";
				$getString .= "&amp;sort=DESC";
			} else {
				$sort = "ASC";
			}
		} else {
			$filter = ""; $order_by = ""; $sort = ""; // Can be removed
		}

		echo "<form name='filter_form' method='get' action='".FUSION_SELF."'>\n";
		echo "<table class='tbl' cellpadding='1' cellspacing='0' style='width:100%;'>\n";
		echo "<tr>\n";
		echo "<td class='tbl1' style='width:40%; text-align:left;'>".$locale['450']."</td>\n";
		echo "<td class='tbl1' style='width:60%; text-align:right;'>".$locale['462']."\n";
		echo "<select name='cat_id' class='textbox' onchange='this.form.submit();'>\n";
		echo "<option value='all'>".$locale['451']."</option>".$cats_list."</select>\n";
		echo "</td>\n";
		echo "</tr>\n";
		if (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
			echo "<tr>\n";
			echo "<td class='tbl1' style='width:40%; text-align:left;'></td>\n";
			echo "<td class='tbl1' style='width:60%; text-align:right;'>";
			echo $locale['463']." <select name='orderby' class='textbox' onchange='this.form.submit();'>\n";
			echo "<option value='download_id'".($order_by == "download_id" ? " selected='selected'" : "").">".$locale['452']."</option>\n";
			echo "<option value='download_title'".($order_by == "download_title" ? " selected='selected'" : "").">".$locale['453']."</option>\n";
			echo "<option value='download_user'".($order_by == "download_user" ? " selected='selected'" : "").">".$locale['454']."</option>\n";
			echo "<option value='download_count'".($order_by == "download_count" ? " selected='selected'" : "").">".$locale['455']."</option>\n";
			echo "<option value='download_datestamp'".($order_by == "download_datestamp" ? " selected='selected'" : "").">".$locale['456']."</option>\n";
			echo "</select>\n";
			echo "<select name='sort' class='textbox' onchange='this.form.submit();'>\n";
			echo "<option value='ASC'".($sort == "ASC" ? " selected='selected'" : "").">".$locale['457']."</option>\n";
			echo "<option value='DESC'".($sort == "DESC" ? " selected='selected'" : "").">".$locale['458']."</option>\n";
			echo "</select>";
			echo "</td>\n";
			echo "</tr>\n";
		}
		echo "<tr>\n";
		echo "<td class='tbl1' style='width:40%; text-align:left;'></td>\n";
		echo "<td class='tbl1' style='width:60%; text-align:right;'>";
		echo "<input id='filter_button' type='submit' class='button' value='".$locale['459']."' />";
		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n</form>\n";
		echo "<hr /><div style='text-align:right;'>\n";
		echo "<form name='searchform' method='get' action='".BASEDIR."search.php'>\n";
		echo "<span class='small'>".$locale['460']." </span>\n";
		echo "<input type='text' name='stext' class='textbox' style='width:150px' />\n";
		echo "<input type='submit' name='search' value='".$locale['461']."' class='button' />\n";
		echo "<input type='hidden' name='stype' value='downloads' />\n";
		echo "</form>\n";
		echo "</div>";

		echo "<script language='JavaScript' type='text/javascript'>\n";
		echo "/* <![CDATA[ */\n";
		echo "jQuery(document).ready(function() {
				jQuery('#filter_button').hide();
			});";
		echo "/* ]]>*/\n";
		echo "</script>\n";
	}

	$cat_result = dbquery(
			"SELECT download_cat_id, download_cat_name, download_cat_description, download_cat_access, download_cat_sorting
			FROM ".DB_DOWNLOAD_CATS."
			WHERE ".groupaccess('download_cat_access').$filter."
			ORDER BY download_cat_name");
	if (dbrows($cat_result)) {
		echo "<br /><table class='tbl-border center' cellpadding='1' cellspacing='2' style='width:100%;'>\n";
		echo "<tr>\n";
		echo "<td class='tbl2' colspan='2'>".$locale['420']."</td>\n";
		echo "<td class='tbl2' style='width:1%;'>".$locale['421']."</td>\n";
		echo "<td class='tbl2' style='width:1%;'>".$locale['422']."</td>\n";
		echo "<td class='tbl2' style='width:1%;'>".$locale['423']."</td>\n";
		echo "<td class='tbl2' style='width:1%;'>".$locale['424']."</td>\n";
		echo "<td class='tbl2' style='width:1%;'>".$locale['425']."</td>\n";
		echo "<td class='tbl2' style='width:1%;'>".$locale['426']."</td>\n";
		echo "</tr>\n";
		while($cat_data = dbarray($cat_result)) {
			echo "<tr><td colspan='8' class='tbl2' style='text-align:left;font-weight:bold;'>".$cat_data['download_cat_name']."</td></tr>\n";
			if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) && $cat_data['download_cat_description'] != "") {
				echo "<tr><td colspan='8' class='tbl1 small' style='text-align:left;'>".$cat_data['download_cat_description']."</td></tr>\n";
			}
			if (checkgroup($cat_data['download_cat_access'])) {
				echo "<!--pre_download_cat-->";
				$rows = dbcount("(download_id)", DB_DOWNLOADS, "download_cat='".$cat_data['download_cat_id']."'");
				if (!isset($_GET['rowstart'.$cat_data['download_cat_id']]) || !isnum($_GET['rowstart'.$cat_data['download_cat_id']]) || $_GET['rowstart'.$cat_data['download_cat_id']] > $rows) { $_GET['rowstart'.$cat_data['download_cat_id']] = 0; }
				if ($rows != 0) {
						$result = dbquery(
							"SELECT td.download_id, td.download_user, td.download_datestamp, td.download_image_thumb, td.download_cat,
									td.download_title, td.download_version, td.download_count, td.download_description_short,
									tu.user_id, tu.user_name, tu.user_status,
							SUM(tr.rating_vote) AS sum_rating,
							COUNT(tr.rating_item_id) AS count_votes
							FROM ".DB_DOWNLOADS." td
							LEFT JOIN ".DB_USERS." tu ON td.download_user=tu.user_id
							LEFT JOIN ".DB_RATINGS." tr ON tr.rating_item_id = td.download_id AND tr.rating_type='D'
							WHERE download_cat='".$cat_data['download_cat_id']."'
							GROUP BY download_id
							ORDER BY ".($order_by == "" ? $cat_data['download_cat_sorting'] : $order_by." ".$sort)."
							LIMIT ".$_GET['rowstart'.$cat_data['download_cat_id']].",".$settings['downloads_per_page']);
					$numrows = dbrows($result); $i = 1;
					while ($data = dbarray($result)) {
						if ($data['download_datestamp'] + 604800 > time() + ($settings['timeoffset'] * 3600)) {
							$new = " <span class='small'>".$locale['410']."</span>";
						} else {
							$new = "";
						}
						if ($data['download_image_thumb']) {
							$img_thumb = DOWNLOADS."images/".$data['download_image_thumb'];
						} else {
							$img_thumb = DOWNLOADS."images/no_image.jpg";
						}
						$comments_count = dbcount("(comment_id)", DB_COMMENTS, "comment_type='D' AND comment_item_id='".$data['download_id']."'");
						echo "<tr>\n";
						echo "<td class='tbl2' style='width:1%;'>".$new."</td>\n";
						echo "<td class='tbl1' style='text-align:left;'><a href='".FUSION_SELF."?cat_id=".$cat_data['download_cat_id']."&amp;download_id=".$data['download_id']."'>".$data['download_title']."</a></td>\n";
						echo "<td class='tbl2' style='text-align:center;'>".showdate("shortdate", $data['download_datestamp'])."</td>\n";
						echo "<td class='tbl2' style='text-align:center;'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
						echo "<td class='tbl2' style='text-align:center;'>".($data['download_version'] ?  $data['download_version'] : "--")."</td>\n";
						echo "<td class='tbl2' style='text-align:center;'>".$data['download_count']."</td>\n";
						echo "<td class='tbl2' style='text-align:center;'>".$comments_count."</td>\n";
						echo "<td class='tbl2' style='white-space:nowrap;'>".($data['count_votes'] > 0 ? str_repeat("<img src='".get_image("star")."' alt='*' style='vertical-align:middle; width:10px;height:10px;' />", ceil($data['sum_rating'] / $data['count_votes'])) : "--")."</td>\n";
						echo "</tr>\n";
						echo "<tr>\n";
						echo "<td colspan='8' class='tbl1 small'>\n";
						if ($settings['download_screenshot']) {
							echo "<img src='".$img_thumb."' style='float: left;margin:3px;' alt='".$data['download_title']."' />\n";
						}
						if ($data['download_description_short']) {
							echo nl2br(stripslashes($data['download_description_short']));
						}
						echo "</td>\n</tr>\n";
					}
					if ($rows > $settings['downloads_per_page']) {
						echo "<tr>\n<td colspan='8' class='tbl2' style='text-align:center;'>\n".makepagenav($_GET['rowstart'.$cat_data['download_cat_id']], $settings['downloads_per_page'], $rows, 3, FUSION_SELF."?cat_id=".$cat_data['download_cat_id'].$getString."&amp;", "rowstart".$cat_data['download_cat_id'])."\n</td></tr>\n"; }
				} else {
					echo "<tr>\n<td class='tbl1' colspan='8' style='text-align:center'>".$locale['431']."</td></tr>\n";
					echo "<!--sub_download_cat-->";
				}
			}
		}
		echo "</table>\n";
	} else {
		echo "<div style='text-align:center'><br />\n".$locale['430']."<br /><br />\n</div>\n";
	}
	echo "<!--sub_download_idx-->";
	closetable();
}

// Download details
if (isset($_GET['download_id']) && isnum($_GET['download_id'])) {
	add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
	add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
	add_to_head("<script type='text/javascript'>\n
	/* <![CDATA[ */\n
	jQuery(document).ready(function(){
		jQuery('a.tozoom').colorbox();
	});\n
	/* ]]>*/\n
	</script>\n");

	$result = dbquery(
		"SELECT td.*,
				tc.download_cat_id, tc.download_cat_access, tc.download_cat_name,
				tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_DOWNLOADS." td
			LEFT JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
			LEFT JOIN ".DB_USERS." tu ON td.download_user=tu.user_id
			WHERE download_id='".$_GET['download_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		if (!checkgroup($data['download_cat_access'])) { redirect(FUSION_SELF);}
		opentable($locale['400'].": ".$data['download_title']);
		echo "<!--pre_download_details-->\n";
		echo "<div class='tbl-border' style='margin-bottom:10px; padding:3px;'>\n";
		echo "<div class='forum-caption' style='text-align:left;'>\n";
		echo "<a href='".FUSION_SELF."'>".$locale['417']."</a> &gt; <a href='".FUSION_SELF."?cat_id=".$data['download_cat']."'>".$data['download_cat_name']."</a> &gt; <strong>".$data['download_title']."</strong>";
		echo "</div>\n</div>\n";

		echo "<table width='100%' cellpadding='0' cellspacing='1' class='tbl-border center'>\n";
		echo "<tr>\n<td class='tbl1' colspan='2'><h2>".$data['download_title']." ".$data['download_version']."</h2><hr /></td></tr>\n";
		echo "<tr>\n<td class='tbl1' style='vertical-align:top;'>".($data['download_description'] != "" ? nl2br(parseubb(parsesmileys($data['download_description']))) : nl2br(stripslashes($data['download_description_short'])))."</td>";
		echo "<td class='tbl1' style='width:20%;text-align:center;vertical-align:top;'>";
		echo "<table width='100%' cellpadding='0' cellspacing='1' class='tbl-border center'>\n";
		if ($data['download_homepage'] != "") {
			if (!strstr($data['download_homepage'], "http://") && !strstr($data['download_homepage'], "https://")) {
				$urlprefix = "http://";
			} else {
				$urlprefix = "";
			}
			echo "<tr><td class='tbl2' style='text-align:center;'>";
			echo "<img src='".get_image("homepage")."' alt='".$locale['418']."' /><br />";
			echo "<a href='".$urlprefix.$data['download_homepage']."' title='".$urlprefix.$data['download_homepage']."' target='_blank'>".$locale['418']."</a>";
			echo "</td>\n</tr>\n";
		}

		if ($settings['download_screenshot'] && $data['download_image'] != "") {
			echo "<tr>\n";
			echo "<td class='tbl2' style='text-align:center;'><img src='".get_image("screenshot")."' alt='".$locale['419']."' /><br />\n";
			echo "<a class='tozoom' href='".DOWNLOADS."images/".$data['download_image']."'>".$locale['419']."</a>\n";
			echo "</td>\n</tr>\n";
		}
		echo "<tr>\n";
		echo "<td class='tbl2' style='text-align:center;'><img src='".get_image("calendar")."' alt='".$locale['427']."' />\n";
		echo "<br />".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."\n";
		echo "<br />".showdate("longdate", $data['download_datestamp'])."\n";
		echo "</td>\n</tr>\n";
		echo "<tr>\n";
		echo "<td class='tbl2' style='text-align:center;'>\n";
		echo "<img src='".get_image("downloads")."' alt='".$locale['424']."' /><br />".$locale['416']." ".$data['download_count']."\n";
		echo "</td>\n</tr>\n";
		if ($data['download_version'] != "" || $data['download_license'] != "" || $data['download_os'] != "" || $data['download_copyright'] != "") {
			echo "<tr>\n<td class='tbl2' style='text-align:center;'><img src='".get_image("info")."' alt='".$locale['428']."' /><br />\n";
		}
		if ($data['download_version'] != "") {
			echo $locale['413']." ".$data['download_version']."<br />\n";
		}
		if ($data['download_license'] != "") {
			echo $locale['411']." ".$data['download_license']."<br />\n";
		}
		if ($data['download_os'] != "") {
			echo $locale['412']." ".$data['download_os']."<br />\n";
		}
		if ($data['download_copyright'] != "") {
			echo "&copy; ".$data['download_copyright']."<br />\n";
		}
		if ($data['download_version'] != "" || $data['download_license'] != "" || $data['download_os'] != "" || $data['download_copyright'] != "") {
			echo "</td>\n</tr>\n";
		}
		echo "</table>\n";
		echo "</td></tr>\n";
		echo "<tr>\n";
		echo "<td class='tbl1' colspan='2' style='text-align:center;'><hr />\n";
		echo "<strong>".$locale['416'].":</strong><br />\n";
		echo "<a href='".FUSION_SELF."?cat_id=".$data['download_cat']."&amp;file_id=".$data['download_id']."' target='_blank'>".get_image("download", $locale['416'], "border:none;", $locale['416'])."</a>\n";
		if ($data['download_filesize'] != "") {
			echo "<br />(".$data['download_filesize'].")\n";
		}
		echo "</td>\n</tr>\n";
		echo "</table>\n";

		echo "<!--sub_download_details-->\n";
		closetable();
		echo "<!--pre_download_comments-->\n";
		include INCLUDES."comments_include.php";
		include INCLUDES."ratings_include.php";
		if ($data['download_allow_comments']) { showcomments("D", DB_DOWNLOADS, "download_id", $_GET['download_id'], FUSION_SELF."?cat_id=".$data['download_cat']."&amp;download_id=".$_GET['download_id']); }
		if ($data['download_allow_ratings']) { showratings("D", $_GET['download_id'], FUSION_SELF."?cat_id=".$data['download_cat']."&amp;download_id=".$_GET['download_id']); }
	}
}

echo $dl_stats;

require_once THEMES."templates/footer.php";
?>