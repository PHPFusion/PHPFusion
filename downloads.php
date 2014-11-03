<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
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
		$cdata = dbarray(dbquery("SELECT download_cat_access FROM ".DB_DOWNLOAD_CATS." ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")." download_cat_id='".$data['download_cat']."'"));
		if (checkgroup($cdata['download_cat_access'])) {
			$result = dbquery("UPDATE ".DB_DOWNLOADS." SET download_count=download_count+1 WHERE download_id='".$download_id."'");
			if (!empty($data['download_file']) && file_exists(DOWNLOADS.$data['download_file'])) {
				$res = 1;
				require_once INCLUDES."class.httpdownload.php";
				ob_end_clean();
				$object = new httpdownload;
				$object->set_byfile(DOWNLOADS.$data['download_file']);
				$object->use_resume = TRUE;
				$object->download();
				exit;
			} elseif (!empty($data['download_url'])) {
				$res = 1;
				redirect($data['download_url']);
			}
		}
	}
	if ($res == 0) {
		redirect("downloads.php");
	}
}
// Statistics
$dl_stats = "";
$dl_stats .= "<div class='row m-t-20'>\n";
$dl_stats .= "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
$dl_stats .= "<h4><strong>".$locale['441']."</strong>\n</h4>";
$result = dbquery("SELECT td.download_id, td.download_title, td.download_count, td.download_cat,
            tc.download_cat_id, tc.download_cat_access
            FROM ".DB_DOWNLOADS." td
            LEFT JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
            ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('download_cat_access')."
            ORDER BY download_count DESC LIMIT 0,15
            ");
if (dbrows($result) != 0) {
	$dl_stats .= "<div class='list-group'>\n";
	while ($data = dbarray($result)) {
		$dl_stats .= "<div class='list-group-item'>";
		$download_title = $data['download_title'];
		$dl_stats .= "<a href='".FUSION_SELF."?download_id=".$data['download_id']."' title='".$download_title."'>".trimlink($data['download_title'], 100)."</a>";
		$dl_stats .= "<span class='badge'>".$data['download_count']." </span>\n";
		$dl_stats .= "</div>\n";
	}
	$dl_stats .= "</div>\n";
}
$dl_stats .= "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
$dl_stats .= "<h4><strong>".$locale['442']."</strong>\n</h4>";
$result = dbquery("SELECT td.download_id, td.download_title, td.download_count, td.download_cat, td.download_datestamp, tc.download_cat_id, tc.download_cat_access
		FROM ".DB_DOWNLOADS." td
		LEFT JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
		".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('download_cat_access')."
		ORDER BY download_datestamp DESC LIMIT 0,15");
if (dbrows($result) != 0) {
	$dl_stats .= "<div class='list-group'>\n";
	while ($data = dbarray($result)) {
		$dl_stats .= "<div class='list-group-item'>";
		$download_title = $data['download_title'];
		$dl_stats .= " <a href='".FUSION_SELF."?download_id=".$data['download_id']."' title='".$download_title."'>".trimlink($data['download_title'], 100)."</a>";
		$dl_stats .= "<span class='badge'>".$data['download_count']." </span>\n";
		$dl_stats .= "</div>\n";
	}
	$dl_stats .= "</div>\n";
}
$dl_stats .= "</div>\n</div>\n</div>\n";

// Filter form, list of existing cats and downloads
if (!isset($_GET['download_id']) || !isnum($_GET['download_id'])) {
	opentable($locale['400']);
	echo "<!--pre_download_idx-->\n";
	$cat_list_result = dbquery("SELECT download_cat_id, download_cat_name
		FROM ".DB_DOWNLOAD_CATS." ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('download_cat_access')."
		ORDER BY download_cat_name");
	$cats_list = array();
	$filter = "";
	$order_by = "";
	$sort = "";
	$getString = "";
	if (dbrows($cat_list_result)) {
		$catlist_opts['all'] = $locale['451'];
		while ($cat_list_data = dbarray($cat_list_result)) {
			$catlist_opts[$cat_list_data['download_cat_id']] = $cat_list_data['download_cat_name'];
		}
		if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart']) || $_GET['rowstart'] > dbrows($cat_list_result)) {
			$_GET['rowstart'] = 0;
		}
		if (isset($_POST['cat_id']) && isnum($_POST['cat_id']) && $_POST['cat_id'] != "all") {
			$order_by_allowed = array("download_id", 'download_title', "download_user", "download_count",
									  "download_datestamp");
			$getString[] = "cat_id=".$_POST['cat_id'];
			if (isset($_POST['orderby']) && in_array($_POST['orderby'], $order_by_allowed)) {
				$getString[] = "orderby=".$_POST['orderby'];
			}
			if (isset($_POST['sort']) && $_POST['sort'] == "DESC") {
				$getString[] = "sort=DESC";
			}
			// parse to get.
			$val = '';
			$i = 1;
			foreach ($getString as $redirectVal) {
				$val .= $i == 1 ? "$redirectVal" : "&amp;$redirectVal";
				$i++;
			}
			redirect(FUSION_SELF.($val ? "?$val" : ''));
		} elseif (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
			$_data = dbarray(dbquery("SELECT download_cat_id, download_cat_name FROM ".DB_DOWNLOAD_CATS." WHERE download_cat_id='".$_GET['cat_id']."' LIMIT 1"));
			$filter = " AND download_cat_id='".$_GET['cat_id']."'";
			$cat_id = isset($_GET['cat_id']) ? $_GET['cat_id'] : '';
			$order_by = isset($_GET['orderby']) ? "AND " : '';
			$sort = isset($_GET['sort']) && $_GET['sort'] == "DESC" ? 'DESC' : 'ASC';
		}
		echo "<ol class='breadcrumb'>\n";
		echo "<li><a href='".FUSION_SELF."'>".$locale['417']."</a></li>\n";
		echo isset($_GET['cat_id']) && isnum($_GET['cat_id']) ? "<li><a href='".FUSION_SELF."?cat_id=".$_data['download_cat_id']."'>".$_data['download_cat_name']."</a></li>\n" : '';
		echo "</ol>\n";
		echo "<div class='panel panel-default'>\n";
		echo "<div class='panel-body p-b-0'>\n";
		echo openform('searchform', 'searchform', 'post', BASEDIR."search.php", array('downtime' => 0,));
		echo form_text($locale['460'], 'stext', 'search_downloads', '', array('placeholder' => $locale['461'],
																			  'append_button' => 1));
		echo form_hidden('stype', 'stype', 'stype', 'downloads');
		echo closeform();
		echo "</div>\n";
		echo "<div class='panel-footer clearfix'>\n";
		echo openform('filter_form', 'filter_form', 'post', FUSION_SELF, array('downtime' => 0, 'notice' => 0));
		echo form_select($locale['462'], 'cat_id', 'cat_id', $catlist_opts, isset($_GET['cat_id']) ? $_GET['cat_id'] : '', array('class' => 'pull-left',
																																 'inline' => 1));
		add_to_jquery("
            $('#cat_id').select2().bind('change', function() { this.form.submit(); });
            ");
		if (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
			$order_opts = array('download_id' => $locale['452'], 'download_title' => $locale['453'],
								'download_user' => $locale['454'], 'download_count' => $locale['455'],
								'download_datestamp' => $locale['456']);
			$sort_opts = array('ASC' => $locale['457'], 'DESC' => $locale['458']);
			echo form_button($locale['459'], 'filter_button', 'filter_button', $locale['459'], array('class' => 'pull-right btn-default'));
			echo form_select('', 'sort', 'sort_downloads', $sort_opts, $sort, array('class' => 'pull-right',
																					'width' => '150px'));
			echo form_select('', 'orderby', 'orderby_downloads', $order_opts, $order_by, array('class' => 'pull-right m-r-10',
																							   'width' => '100px'));
			echo "<span class='p-r-15 pull-right'><strong>".$locale['463']."</strong></span>\n";
			add_to_jquery("
                $('#sort_downloads, #orderby_downloads').select2().bind('change', function() { this.form.submit(); });
                $('#filter_button').hide();
                ");
		}
		echo closeform();
		echo "</div>\n</div>\n";
	}
	$cat_result = dbquery("SELECT download_cat_id, download_cat_name, download_cat_description, download_cat_access, download_cat_sorting
			FROM ".DB_DOWNLOAD_CATS."
			".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('download_cat_access').$filter."
			ORDER BY download_cat_name");
	if (dbrows($cat_result)) {
		echo "<div class='list-group'>\n";
		echo "<div class='list-group-item'>\n";
		echo "<span class='pull-left'><strong>".$locale['415']." ".dbcount("(download_cat)", DB_DOWNLOADS)." </strong>\n</span>\n";
		$i_alt = dbresult(dbquery("SELECT SUM(download_count) FROM ".DB_DOWNLOADS), 0);
		echo "<span class='pull-right'><strong>".$locale['440']." ".($i_alt ? $i_alt : "0")."</strong></span><br/>\n";
		echo "</div>\n";
		while ($cat_data = dbarray($cat_result)) {
			echo "<div class='list-group-item'>\n";
			echo "<h4><a href='".FUSION_SELF."?cat_id=".$cat_data['download_cat_id']."'><strong>".$cat_data['download_cat_name']."</strong></a></h4>\n";
			echo (isset($_POST['cat_id']) && isnum($_POST['cat_id']) && $cat_data['download_cat_description']) ? "<span>".$cat_data['download_cat_description']."</span>\n<br/>" : '';
			if (checkgroup($cat_data['download_cat_access'])) {
				echo "<!--pre_download_cat-->";
				$rows = dbcount("(download_id)", DB_DOWNLOADS, "download_cat='".$cat_data['download_cat_id']."'");
				if (!isset($_GET['rowstart'.$cat_data['download_cat_id']]) || !isnum($_GET['rowstart'.$cat_data['download_cat_id']]) || $_GET['rowstart'.$cat_data['download_cat_id']] > $rows) {
					$_GET['rowstart'.$cat_data['download_cat_id']] = 0;
				}
				if ($rows != 0) {
					$result = dbquery("SELECT td.download_id, td.download_user, td.download_datestamp, td.download_image_thumb, td.download_cat,
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
					$numrows = dbrows($result);
					$i = 1;
					while ($data = dbarray($result)) {
						if ($data['download_datestamp']+604800 > time()+($settings['timeoffset']*3600)) {
							$new = " <span class='label label-success'>".$locale['410']."</span>";
						} else {
							$new = "";
						}
						if ($data['download_image_thumb']) {
							$img_thumb = DOWNLOADS."images/".$data['download_image_thumb'];
						} else {
							$img_thumb = DOWNLOADS."images/no_image.jpg";
						}
						$comments_count = dbcount("(comment_id)", DB_COMMENTS, "comment_type='D' AND comment_item_id='".$data['download_id']."'");
						echo "<div class='media clearfix'>\n";
						echo ($settings['download_screenshot']) ? "<a class='pull-left' href='".FUSION_SELF."?cat_id=".$cat_data['download_cat_id']."&amp;download_id=".$data['download_id']."'>\n<img class='img-responsive img-thumbnail' src='".$img_thumb."' style='float: left;margin:3px;' alt='".$data['download_title']."' />\n</a>\n" : '';
						echo "<div class='media-body'>\n";
						echo "<h4 class='media-heading'><a href='".FUSION_SELF."?cat_id=".$cat_data['download_cat_id']."&amp;download_id=".$data['download_id']."'>".$data['download_title']."</a> <small>$new</small></h4>\n";
						echo "<div class='media-info'><strong>\n";
						echo "<i title='".$locale['421']."' class='entypo calendar text-lighter'></i> ".showdate("shortdate", $data['download_datestamp'])."\n";
						echo "<i title='".$locale['422']."' class='entypo user text-lighter'></i> ".profile_link($data['user_id'], $data['user_name'], $data['user_status']);
						echo($data['download_version'] ? "<i title='".$locale['423']."' class='entypo flow-branch'></i>  ".$data['download_version'] : "--");
						echo "<i title='".$locale['424']."' class='entypo cloud text-lighter'></i> ".number_format($data['download_count']);
						echo($data['count_votes'] > 0 ? str_repeat("<img src='".get_image("star")."' alt='*' title='".$locale['426']."' style='vertical-align:middle; width:10px;height:10px;' />", ceil($data['sum_rating']/$data['count_votes'])) : "");
						echo "</strong></div>";
						echo $data['download_description_short'] ? $data['download_description_short'] : '';
						echo "</div>\n</div>\n";
					}
					if ($rows > $settings['downloads_per_page']) {
						echo "<div class='text-center'>\n".makepagenav($_GET['rowstart'.$cat_data['download_cat_id']], $settings['downloads_per_page'], $rows, 3, FUSION_SELF."?cat_id=".$cat_data['download_cat_id'].$getString."&amp;", "rowstart".$cat_data['download_cat_id'])."\n</div>\n";
					}
				} else {
					echo $locale['431'];
					echo "<!--sub_download_cat-->";
				}
			}
			echo "</div>\n";
		}
	} else {
		echo "<div style='text-align:center'><br />\n".$locale['430']."<br /><br />\n</div>\n";
	}
	echo "<!--sub_download_idx-->";
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
	$result = dbquery("SELECT td.*,
				tc.download_cat_id, tc.download_cat_access, tc.download_cat_name,
				tu.user_id, tu.user_name, tu.user_status, tu.user_avatar, tu.user_level
                FROM ".DB_DOWNLOADS." td
                LEFT JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
                LEFT JOIN ".DB_USERS." tu ON td.download_user=tu.user_id
                ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND" : "WHERE")." download_id='".$_GET['download_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		if (!checkgroup($data['download_cat_access'])) {
			redirect(FUSION_SELF);
		}
		opentable($locale['400'].": ".$data['download_title']);
		echo "<ol class='breadcrumb'>\n";
		echo "<li><a href='".BASEDIR."downloads.php'>".$locale['417']."</a> </li>\n";
		echo "<li><a href='".BASEDIR."downloads.php?cat_id=".$data['download_cat']."'>".$data['download_cat_name']."</a></li>\n";
		echo "<li>".$data['download_title']."</li>\n";
		echo "</ol>\n";
		echo "<!--pre_download_details-->\n";
		echo "<h2>".$data['download_title']." ".$data['download_version']."</h2>\n";
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-8 col-md-8 col-lg-8'>\n";
		echo "<div class='panel panel-default'>\n";
		echo "<div class='panel-body'>\n";
		$na = $locale['429a'];
		echo $data['download_image'] && file_exists(DOWNLOADS."images/".$data['download_image']) ? "<img class='img-responsive' src='".DOWNLOADS."images/".$data['download_image']."' />" : "<img class='img-responsive' src=\"holder.js/500x250/text:$na/grey\" />";
		echo "<div>\n";
		echo "<a href='".BASEDIR."downloads.php?cat_id=".$data['download_cat_id']."&amp;file_id=".$data['download_id']."' class='btn btn-success m-t-10 btn-block' target='_blank'><strong>".$locale['416']."
                ".($data['download_filesize'] ? "(".$data['download_filesize'].")" : '')."
                </strong></a>\n";
		if ($settings['download_screenshot'] && $data['download_image'] != "") {
			echo "<a class='tozoom btn btn-primary m-t-10 btn-block' href='".DOWNLOADS."images/".$data['download_image']."'><strong>".$locale['419']."</strong></a>\n";
		}
		echo "</div>\n";
		echo "</div>\n</div>\n";
		echo $data['download_description'] != "" ? nl2br(parseubb(parsesmileys($data['download_description']))) : nl2br(stripslashes($data['download_description_short']));
		echo "</div><div class='col-xs-12 col-sm-4 col-md-4 col-lg-4 text-smaller'>\n";
		if ($data['download_homepage'] != "") {
			if (!strstr($data['download_homepage'], "http://") && !strstr($data['download_homepage'], "https://")) {
				$urlprefix = "http://";
			} else {
				$urlprefix = "";
			}
			echo "<div class='panel panel-default'>\n<div class='panel-body'>\n";
			echo "<div class='row m-0'>\n<label class='text-left col-xs-12 col-sm-5 col-md-5 col-lg-5 p-l-0'>".$locale['418']."</label>\n";
			echo "<a href='".$urlprefix.$data['download_homepage']."' title='".$urlprefix.$data['download_homepage']."' target='_blank'>".$locale['418a']."</a>";
			echo "</div>\n";
			echo "<div class='row m-0'>\n<label class='text-left col-xs-12 col-sm-5 col-md-5 col-lg-5 p-l-0'>".$locale['427']."</label>\n";
			echo "".showdate("shortdate", $data['download_datestamp'])."\n";
			echo "</div>\n";
			if ($data['download_version'] != "" || $data['download_license'] != "" || $data['download_os'] != "" || $data['download_copyright'] != "") {
				echo "<div class='row m-0'>\n<label class='text-left col-xs-12 col-sm-5 col-md-5 col-lg-5 p-l-0'>".$locale['428'].":</label> ".$data['download_copyright']."</div>";
				echo $data['download_version'] ? "<div class='row m-0'>\n<label class='text-left col-xs-12 col-sm-5 col-md-5 col-lg-5 p-l-0'>".$locale['413']."</label> ".$data['download_version']."</div>" : '';
				echo $data['download_license'] ? "<div class='row m-0'>\n<label class='text-left col-xs-12 col-sm-5 col-md-5 col-lg-5 p-l-0'>".$locale['411']."</label> ".$data['download_license']."</div>" : '';
				echo $data['download_os'] ? "<div class='row m-0'>\n<label class='text-left col-xs-12 col-sm-5 col-md-5 col-lg-5 p-l-0'>".$locale['412']."</label> ".$data['download_os']."</div>" : '';
			}
			echo "</div>\n</div>\n";
		}
		echo "<div class='panel panel-default'>\n<div class='panel-body'>\n";
		echo "<img style='max-width:20px; margin-right:10px;' src='".get_image("downloads")."' alt='".$locale['424']."' /><span class='icon-sm'><strong>".$data['download_count']."</strong></span> ".$locale['416']."\n";
		echo "</div>\n</div>\n";
		echo "<div class='clearfix'>\n";
		echo "<div class='pull-left m-r-10'>".display_avatar($data, '50px')."</div>\n";
		echo "<strong>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</strong><br/>\n".getuserlevel($data['user_level'])." ";
		echo "</div>\n";
		echo "</div>\n</div>\n";
		echo "<!--sub_download_details-->\n";
		closetable();
		echo "<!--pre_download_comments-->\n";
		include INCLUDES."comments_include.php";
		include INCLUDES."ratings_include.php";
		if ($data['download_allow_comments']) {
			showcomments("D", DB_DOWNLOADS, "download_id", $_GET['download_id'], FUSION_SELF."?cat_id=".$data['download_cat']."&amp;download_id=".$_GET['download_id']);
		}
		if ($data['download_allow_ratings']) {
			showratings("D", $_GET['download_id'], FUSION_SELF."?cat_id=".$data['download_cat']."&amp;download_id=".$_GET['download_id']);
		}
	}
}
echo $dl_stats;
closetable();
require_once THEMES."templates/footer.php";
?>