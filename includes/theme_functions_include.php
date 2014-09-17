<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme_functions_include.php
| Author: Nick Jones (Digitanium), Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
function load_bootstrap() {
	define('bootstrapped', TRUE);
	require_once INCLUDES."output_handling_include.php";
	add_to_head("<meta http-equiv='X-UA-Compatible' content='IE=edge' />");
	add_to_head("<meta name='viewport' content='width=device-width, initial-scale=1.0' />");
	add_to_head("<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>");
	add_to_head("<script type='text/javascript' src='".INCLUDES."bootstrap/holder.js'></script>");
	add_to_head("<link href='".INCLUDES."bootstrap/bootstrap.min.css' rel='stylesheet' media='screen' />");
}

if ($settings['bootstrap']) {
	load_bootstrap();
}
add_to_head("<link href='".THEMES."templates/default.css' rel='stylesheet' media='screen' />");
add_to_head("<link href='".INCLUDES."font/entypo/entypo.css' rel='stylesheet' media='screen' />");
function openmodal($id, $title, $opts = FALSE) {
	if (!empty($opts)) {
		// trigger via button or via load.
		if (array_key_exists('button_id', $opts) && $opts['button_id']) {
			add_to_jquery("
                   $('#".$opts['button_id']."').bind('click', function(e){
                          $('#".$id."-Modal').modal('show');
                   });
                ");
		} else {
			add_to_jquery("
                   $('#".$id."-Modal').modal('show');
                ");
		}
	} else {
		add_to_footer("
                   <script type='text/javascript'>
                   $('#".$id."-Modal').modal('show');
                   </script>
                ");
	}
	$html = '';
	$html .= "<div class='modal fade' id='$id-Modal' tabindex='-1' role='dialog' aria-labelledby='$id-ModalLabel' aria-hidden='true'>\n";
	$html .= "<div class='modal-dialog modal-lg'>\n";
	$html .= "<div class='modal-content'>\n";
	$html .= "<div class='modal-header'>";
	$html .= "<button type='button' class='btn btn-sm pull-right btn-default' data-dismiss='modal'><i class='entypo cross'></i> Close</button>\n";
	$html .= "<h4 class='modal-title text-dark' id='myModalLabel'>$title</h4>\n";
	$html .= "</div>\n";
	$html .= "<div class='modal-body'>\n";
	return $html;
}

function closemodal() {
	$html = '';
	$html .= "</div></div></div></div>\n";
	return $html;
}

function progress_bar($percent, $title = FALSE, $class = FALSE, $height = FALSE, $reverse = FALSE) {
	$height = (!$height) ? $height : '20px';
	$reverse = $reverse ? TRUE : FALSE;
	if ($percent > 71) {
		$auto_class = ($reverse) ? 'progress-bar-danger' : 'progress-bar-success';
	} elseif ($percent > 55) {
		$auto_class = ($reverse) ? 'progress-bar-warning' : 'progress-bar-info';
	} elseif ($percent > 25) {
		$auto_class = ($reverse) ? 'progress-bar-info' : 'progress-bar-warning';
	} elseif ($percent < 25) {
		$auto_class = ($reverse) ? 'progress-bar-success' : 'progress-bar-danger';
	}
	$class = (!$class) ? $auto_class : $class;
	$html = "<div class='text-right m-b-10'><span class='pull-left'>$title</span><span class='clearfix'>$percent%</span></div>\n";
	$html .= "<div class='progress' style='height: ".$height." !important;'>\n";
	$html .= "<div class='progress-bar ".$class." bar' role='progressbar' aria-valuenow='$percent' aria-valuemin='0' aria-valuemax='100' style='width: $percent%'>\n";
	$html .= "</div></div>\n";
	return $html;
}

function admin_message($text, $class = FALSE) {
	$class = $class ? $class : 'alert-info';
	return "<div class='alert $class text-center alert-dismissable' style='color:#222'>
    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
    <strong>$text</strong></div>\n";
}

function check_panel_status($side) {
	global $settings;
	$exclude_list = "";
	if ($side == "left") {
		if ($settings['exclude_left'] != "") {
			$exclude_list = explode("\r\n", $settings['exclude_left']);
		}
	} elseif ($side == "upper") {
		if ($settings['exclude_upper'] != "") {
			$exclude_list = explode("\r\n", $settings['exclude_upper']);
		}
	} elseif ($side == "aupper") {
		if ($settings['exclude_aupper'] != "") {
			$exclude_list = explode("\r\n", $settings['exclude_aupper']);
		}
	} elseif ($side == "lower") {
		if ($settings['exclude_lower'] != "") {
			$exclude_list = explode("\r\n", $settings['exclude_lower']);
		}
	} elseif ($side == "blower") {
		if ($settings['exclude_blower'] != "") {
			$exclude_list = explode("\r\n", $settings['exclude_blower']);
		}
	} elseif ($side == "right") {
		if ($settings['exclude_right'] != "") {
			$exclude_list = explode("\r\n", $settings['exclude_right']);
		}
	}
	if (is_array($exclude_list)) {
		$script_url = explode("/", $_SERVER['PHP_SELF']);
		$url_count = count($script_url);
		$base_url_count = substr_count(BASEDIR, "/")+1;
		$match_url = "";
		while ($base_url_count != 0) {
			$current = $url_count-$base_url_count;
			$match_url .= "/".$script_url[$current];
			$base_url_count--;
		}
		if (!in_array($match_url, $exclude_list) && !in_array($match_url.(FUSION_QUERY ? "?".FUSION_QUERY : ""), $exclude_list)) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		return TRUE;
	}
}

function showbanners($display = "") {
	global $settings;
	ob_start();
	if ($display == 2) {
		if ($settings['sitebanner2']) {
			eval("?>".stripslashes($settings['sitebanner2'])."<?php ");
		}
	} else {
		if ($display == "" && $settings['sitebanner2']) {
			eval("?><div style='float: right;'>".stripslashes($settings['sitebanner2'])."</div>\n<?php ");
		}
		if ($settings['sitebanner1']) {
			eval("?>".stripslashes($settings['sitebanner1'])."\n<?php ");
		} elseif ($settings['sitebanner']) {
			echo "<a href='".$settings['siteurl']."'><img src='".BASEDIR.$settings['sitebanner']."' alt='".$settings['sitename']."' style='border: 0;' /></a>\n";
		} else {
			echo "<a href='".$settings['siteurl']."'>".$settings['sitename']."</a>\n";
		}
	}
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function showsublinks($sep = "&middot;", $class = "") {
	global $settings;
	require_once INCLUDES."mobile.menu.inc.php";
	$mobile_icon = isset($default_mobile_icon) ? $default_mobile_icon : '';
	$sres = dbquery("SELECT link_name, link_url, link_window, link_visibility FROM ".DB_SITE_LINKS."
	        ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_position>='2' ORDER BY link_order");
	$mobile_link = array();
	if (dbrows($sres)) {
		$i = 0;
		if ($settings['bootstrap']) {
			$res = "<nav class='navbar' role='navigation'>\n";
			$res .= "<div class='mobile-menu'>\n<button type='button' class='navbar-toggle collapsed' data-toggle='collapse' data-target='#mp'><i class='entypo menu'></i></button>\n</div>\n";
			$res .= "<div id='mp' class='navbar-collapse collapse'>\n"; // collect all navbar item.
			$res .= "<ul class='nav navbar-nav hidden-xs'>\n";
		} else {
			$res = "<ul>\n";
		}
		while ($sdata = dbarray($sres)) {
			$mobile_link[$sdata['link_name']] = $sdata['link_url']; // order, visibility, language - complied.
			$li_class = $class;
			$i++;
			if ($sdata['link_url'] != "---" && checkgroup($sdata['link_visibility'])) {
				$link_target = ($sdata['link_window'] == "1" ? " target='_blank'" : "");
				if ($i == 1) {
					$li_class .= ($li_class ? " " : "")."first-link";
				}
				if (START_PAGE == $sdata['link_url']) {
					$li_class .= ($li_class ? " " : "")."current-link";
				}
				if (preg_match("!^(ht|f)tp(s)?://!i", $sdata['link_url'])) {
					$res .= "<li".($li_class ? " class='".$li_class."'" : "").">".$sep."<a href='".$sdata['link_url']."'".$link_target.">\n";
					$res .= "<span>".parseubb($sdata['link_name'], "b|i|u|color|img")."</span></a></li>\n";
				} else {
					$res .= "<li".($li_class ? " class='".$li_class."'" : "").">".$sep."<a href='".BASEDIR.$sdata['link_url']."'".$link_target.">\n";
					$res .= "<span>".parseubb($sdata['link_name'], "b|i|u|color|img")."</span></a></li>\n";
				}
			}
		}
		if ($settings['bootstrap']) {
			$res .= "</ul>\n";
			$res .= "<!--start of mobile menu -->\n";
			$res .= "<div class='hidden-sm hidden-md hidden-lg mobile-panel m-0'>\n";
			$res .= "<div class='mobile-pane'>\n";
			$res .= "<div class='mobile-header'>\n";
			$res .= "<button class='btn mobile-btn-close' data-toggle='collapse' data-target='#mp'>Close</button>\n";
			$res .= "<div class='mobile-header-text text-center'>Navigation</div>";
			$res .= "</div>\n";
			if (count($mobile_link) > 0) {
				$res .= "<div class='row m-0 mobile-body'>\n";
				foreach ($mobile_link as $link_name => $link_url) {
					$icon = array_key_exists($link_url, $mobile_icon) ? $mobile_icon[$link_url] : 'entypo layout';
					$res .= "<div class='col-xs-3 mobile-grid text-center'><a href='$link_url' class='btn btn-menu btn-block btn-default m-b-10'><i class='".$icon."'></i><br/><span class='mobile-text'>".trimlink($link_name, 10)."</span></a></div>\n";
				}
				$res .= "</div>\n";
			}
			$res .= "</div>\n";
			$res .= "</div>\n";
			$res .= "<!--end of mobile menu -->\n";
		} else {
			$res .= "</ul>\n";
		}
		$res .= "</div>\n";
		$res .= "</nav>\n";
		return $res;
	}
}

function showsubdate() {
	global $settings;
	return ucwords(showdate($settings['subheaderdate'], time()));
}

function newsposter($info, $sep = "", $class = "") {
	global $locale;
	$res = "";
	$link_class = $class ? " class='$class' " : "";
	$res = THEME_BULLET." <span ".$link_class.">".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</span> ";
	$res .= $locale['global_071'].showdate("newsdate", $info['news_date']);
	$res .= $info['news_ext'] == "y" || $info['news_allow_comments'] ? $sep."\n" : "\n";
	return "<!--news_poster-->".$res;
}

function newsopts($info, $sep, $class = "") {
	global $locale, $settings;
	$res = "";
	$link_class = $class ? " class='$class' " : "";
	if (!isset($_GET['readmore']) && $info['news_ext'] == "y") $res = "<a href='news.php?readmore=".$info['news_id']."'".$link_class.">".$locale['global_072']."</a> ".$sep." ";
	if ($info['news_allow_comments'] && $settings['comments_enabled'] == "1") {
		$res .= "<a href='news.php?readmore=".$info['news_id']."#comments'".$link_class.">".$info['news_comments'].($info['news_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a> ".$sep." ";
	}
	if ($info['news_ext'] == "y" || ($info['news_allow_comments'] && $settings['comments_enabled'] == "1")) {
		$res .= $info['news_reads'].$locale['global_074']."\n ".$sep;
	}
	$res .= "<a href='print.php?type=N&amp;item_id=".$info['news_id']."'><img src='".get_image("printer")."' alt='".$locale['global_075']."' style='vertical-align:middle;border:0;' /></a>\n";
	return "<!--news_opts-->".$res;
}

function newscat($info, $sep = "", $class = "") {
	global $locale;
	$res = "";
	$link_class = $class ? " class='$class' " : "";
	$res .= $locale['global_079'];
	if ($info['cat_id']) {
		$res .= "<a href='news_cats.php?cat_id=".$info['cat_id']."'$link_class>".$info['cat_name']."</a>";
	} else {
		$res .= "<a href='news_cats.php?cat_id=0'$link_class>".$locale['global_080']."</a>";
	}
	return "<!--news_cat-->".$res." $sep ";
}

function articleposter($info, $sep = "", $class = "") {
	global $locale, $settings;
	$res = "";
	$link_class = $class ? " class='$class' " : "";
	$res = THEME_BULLET." ".$locale['global_070']."<span ".$link_class.">".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</span>\n";
	$res .= $locale['global_071'].showdate("newsdate", $info['article_date']);
	$res .= ($info['article_allow_comments'] && $settings['comments_enabled'] == "1" ? $sep."\n" : "\n");
	return "<!--article_poster-->".$res;
}

function articleopts($info, $sep) {
	global $locale, $settings;
	$res = "";
	if ($info['article_allow_comments'] && $settings['comments_enabled'] == "1") {
		$res = "<a href='articles.php?article_id=".$info['article_id']."#comments'>".$info['article_comments'].($info['article_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a> ".$sep."\n";
	}
	$res .= $info['article_reads'].$locale['global_074']." ".$sep."\n";
	$res .= "<a href='print.php?type=A&amp;item_id=".$info['article_id']."'><img src='".get_image("printer")."' alt='".$locale['global_075']."' style='vertical-align:middle;border:0;' /></a>\n";
	return "<!--article_opts-->".$res;
}

function articlecat($info, $sep = "", $class = "") {
	global $locale;
	$res = "";
	$link_class = $class ? " class='$class' " : "";
	$res .= $locale['global_079'];
	if ($info['cat_id']) {
		$res .= "<a href='articles.php?cat_id=".$info['cat_id']."'$link_class>".$info['cat_name']."</a>";
	} else {
		$res .= "<a href='articles.php?cat_id=0'$link_class>".$locale['global_080']."</a>";
	}
	return "<!--article_cat-->".$res." $sep ";
}

function itemoptions($item_type, $item_id) {
	global $locale, $aidlink;
	$res = "";
	if ($item_type == "N") {
		if (iADMIN && checkrights($item_type)) {
			$res .= "<!--article_news_opts--> &middot; <a href='".ADMIN."news.php".$aidlink."&amp;action=edit&amp;news_id=".$item_id."'><img src='".get_image("edit")."' alt='".$locale['global_076']."' title='".$locale['global_076']."' style='vertical-align:middle;border:0;' /></a>\n";
		}
	} elseif ($item_type == "A") {
		if (iADMIN && checkrights($item_type)) {
			$res .= "<!--article_admin_opts--> &middot; <a href='".ADMIN."articles.php".$aidlink."&amp;action=edit&amp;article_id=".$item_id."'><img src='".get_image("edit")."' alt='".$locale['global_076']."' title='".$locale['global_076']."' style='vertical-align:middle;border:0;' /></a>\n";
		}
	}
	return $res;
}

function showrendertime($queries = TRUE) {
	global $locale, $mysql_queries_count, $settings;
	if ($settings['rendertime_enabled'] == 1 || ($settings['rendertime_enabled'] == 2 && iADMIN)) {
		$res = sprintf($locale['global_172'], substr((get_microtime()-START_TIME), 0, 4));
		$res .= ($queries ? " - $mysql_queries_count ".$locale['global_173'] : "");
		return $res;
	} else {
		return "";
	}
}

function showcopyright($class = "", $nobreak = FALSE) {
	$link_class = $class ? " class='$class' " : "";
	$res = "Powered by <a href='https://www.php-fusion.co.uk'".$link_class.">PHP-Fusion</a> Copyright &copy; ".date("Y")." PHP-Fusion Inc";
	$res .= ($nobreak ? "&nbsp;" : "<br />\n");
	$res .= "Released as free software without warranties under <a href='http://www.fsf.org/licensing/licenses/agpl-3.0.html'".$link_class.">GNU Affero GPL</a> v3.\n";
	return $res;
}

function showcounter() {
	global $locale, $settings;
	if ($settings['visitorcounter_enabled']) {
		return "<!--counter-->".number_format($settings['counter'])." ".($settings['counter'] == 1 ? $locale['global_170'] : $locale['global_171']);
	} else {
		return "";
	}
}

function panelbutton($state, $bname) {
	$bname = preg_replace("/[^a-zA-Z0-9\s]/", "_", $bname);
	if (isset($_COOKIE["fusion_box_".$bname])) {
		if ($_COOKIE["fusion_box_".$bname] == "none") {
			$state = "off";
		} else {
			$state = "on";
		}
	}
	return "<img src='".get_image("panel_".($state == "on" ? "off" : "on"))."' id='b_".$bname."' class='panelbutton' alt='' onclick=\"javascript:flipBox('".$bname."')\" />";
}

function panelstate($state, $bname, $element = "div") {
	$bname = preg_replace("/[^a-zA-Z0-9\s]/", "_", $bname);
	if (isset($_COOKIE["fusion_box_".$bname])) {
		if ($_COOKIE["fusion_box_".$bname] == "none") {
			$state = "off";
		} else {
			$state = "on";
		}
	}
	return "<$element id='box_".$bname."'".($state == "off" ? " style='display:none'" : "").">\n";
}

// v6 compatibility
function opensidex($title, $state = "on") {
	openside($title, TRUE, $state);
}

function closesidex() {
	closeside();
}

function tablebreak() {
	return TRUE;
}

function make_breadcrumb($title, $db, $id_col, $cat_col, $name_col, $id, $class = FALSE) {
	global $aidlink;
	echo "<ol class='breadcrumb $class'><i class='entypo location'></i>\n";
	echo "<li><a href='".FUSION_SELF.$aidlink."' class='section'/>$title</a></li>\n";
	breadcrumb_items($db, $id_col, $cat_col, $name_col, $id);
	echo "</ol>\n";
}

function breadcrumb_items($db, $id_col, $cat_col, $name_col, $id) {
	global $aidlink;
	$result = dbquery("SELECT $id_col, $cat_col, $name_col FROM $db WHERE $id_col='$id' LIMIT 1");
	if (dbrows($result) > 0) {
		$data = dbarray($result);
		if ($data[$cat_col] > 0) {
			echo breadcrumb_items($db, $id_col, $cat_col, $name_col, $data[$cat_col]);
			echo "<li><a class='section' href='".FUSION_SELF.$aidlink."&amp;cid=".$data[$id_col]."'>".$data[$name_col]."</a></li>\n";
		} else {
			echo "<li><a class='section' href='".FUSION_SELF.$aidlink."&amp;cid=".$data[$id_col]."'>".$data[$name_col]."</a></li>\n";
		}
	}
}

function display_avatar($userdata, $size, $class = FALSE) {
	$class = ($class) ? "class='$class'" : '';
	if (array_key_exists('user_avatar', $userdata) && $userdata['user_avatar'] && file_exists(IMAGES."avatars/".$userdata['user_avatar'])) {
		$userdata['user_id'] = array_key_exists('user_id', $userdata) && $userdata['user_id'] ? $userdata['user_id'] : 1;
		return "<a $class href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'><img class='img-responsive img-thumbnail m-r-10' style='display:inline; max-width:$size; max-height:$size;' src='".IMAGES."avatars/".$userdata['user_avatar']."'></a>\n";
	} else {
		$userdata['user_id'] = array_key_exists('user_id', $userdata) && $userdata['user_id'] ? $userdata['user_id'] : 1;
		return "<a $class href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'><img class='img-responsive img-thumbnail m-r-10' style='display:inline; max-width:$size; max-height:$size;' src='".IMAGES."avatars/noavatar100.png'></a>\n";
	}
}

function timer($updated = FALSE) {
	if (!$updated) {
		$updated = time();
	}
	$updated = stripinput($updated);
	$current = time();
	$calculated = $current-$updated;
	$second = 1;
	$minute = $second*60;
	$hour = $minute*60; // microseconds
	$day = 24*$hour;
	$month = days_current_month()*$day;
	$year = (date("L", $updated) > 0) ? 366*$day : 365*$day;
	if ($calculated < 1) {
		return "just now";
	}
	$timer = array($year => "year", $month => "month", $day => "day", $hour => "hour", $minute => "minute",
				   $second => "second");
	foreach ($timer as $arr => $unit) {
		$calc = $calculated/$arr; // balance timestamp
		if ($calc >= 1) {
			// stops the rest of the loop.
			$answer = round($calc);
			$s = ($answer > 1) ? "s" : "";
			return "<abbr class='atooltip' data-toggle='tooltip' data-placement='top' title='".showdate('newsdate', $updated)."'>$answer ".$unit.$s." ago</abbr>";
		}
	}
}

function days_current_month() {
	// calculate number of days in a month
	$year = showdate("%Y", time());
	$month = showdate("%m", time());
	return $month == 2 ? ($year%4 ? 28 : ($year%100 ? 29 : ($year%400 ? 28 : 29))) : (($month-1)%7%2 ? 30 : 31);
}

function countdown($time) {
	$updated = stripinput($time);
	$second = 1;
	$minute = $second*60;
	$hour = $minute*60; // microseconds
	$day = 24*$hour;
	$month = days_current_month()*$day;
	$year = (date("L", $updated) > 0) ? 366*$day : 365*$day;
	$timer = array($year => "year", $month => "month", $day => "day", $hour => "hour", $minute => "minute",
				   $second => "second");
	foreach ($timer as $arr => $unit) {
		$calc = $updated/$arr; // balance timestamp
		if ($calc >= 1) {
			$answer = round($calc);
			$s = ($answer > 1) ? "s" : "";
			return "<abbr class='atooltip' data-toggle='tooltip' data-placement='top' title='~".showdate('newsdate', $updated+time())."'>$answer ".$unit.$s."</abbr>";
		}
	}
	if (!isset($answer)) {
		return "<abbr class='atooltip' data-toggle='tooltip' data-placement='top' title='".showdate('newsdate', time())."'>now</abbr>";
	}
}

// Simplified Tabs - Without Hierarchy
function tab_active($tab_title, $num) { // tab title is array.
	if (isset($_GET['section']) && $_GET['section']) {
		$section = $_GET['section'];
		$count = count($tab_title['title']);
		if ($count > 0) {
			for ($i = 0; $i <= $count; $i++) {
				$id = $tab_title['id'][$i];
				if ($section == $id) {
					return $id;
				}
			}
		} else {
			return $num;
		}
	} else {
		// to get the current active by id and title added together.
		$id = $tab_title['id'][$num];
		$title = $tab_title['title'][$num];
		$v_link = str_replace(" ", "-", $title);
		$v_link = str_replace("/", "-", $v_link);
		return "".$id."$v_link";
	}
}

function opentab($tab_title, $link_active_arrkey, $id, $link = FALSE) {
	global $aidlink;
	$link_mode = $link == 1 ? 1 : 0;
	$html = "<div class='nav-wrapper'>\n";
	$html .= "<ul class='nav nav-tabs' $id >\n";
	foreach ($tab_title['title'] as $arr => $v) {
		$v_link = str_replace(" ", "-", $v);
		$v_link = str_replace("/", "-", $v_link);
		$v_title = str_replace("-", " ", $v);
		$icon = (isset($tab_title['icon'][$arr])) ? $tab_title['icon'][$arr] : "";
		$id = $tab_title['id'][$arr];
		$link_url = ($aidlink && $link_mode) ? FUSION_SELF.(isset($_GET['aid']) ? $aidlink."&amp;" : '?')."section=".$id."" : FUSION_SELF."?section=$id";
		if ($link_mode) {
			$html .= ($link_active_arrkey == $id) ? "<li class='active'>\n" : "<li>\n";
		} else {
			$html .= ($link_active_arrkey == "".$id."$v_link") ? "<li class='active'>\n" : "<li>\n";
		}
		$html .= "<a ".(!$link_mode ? "data-toggle='tab' href='#".$id."$v_link'" : "href='$link_url'")." >\n".($icon ? "<i class='$icon'></i>" : '')." ".$v_title." </a>\n";
		$html .= "</li>\n";
	} // end foreach
	$html .= "</ul>\n";
	$html .= "<div class='tab-content' >\n";
	return $html;
}

function opentabbody($tab_title, $id, $link_active_arrkey = FALSE, $link = FALSE) {
	if (isset($_GET['section']) || $link) {
		$link = '';
		if ($link_active_arrkey == $id) {
			$status = 'in active';
		} else {
			$status = '';
		}
	} else {
		if (is_array($tab_title)) {
			$title = $tab_title['title'];
			$link = str_replace(" ", "-", $title);
			$link = str_replace("/", "-", $link);
		} else {
			$link = str_replace(" ", "-", $tab_title);
			$link = str_replace("/", "-", $link);
		}
		if ($link_active_arrkey == "".$id."$link") {
			$status = "in active";
		} else {
			$status = "";
		}
	}
	return "<div class='normal-tab-pane tab-pane fade ".$status."' id='".$id."$link'>\n";
}

function closetabbody() { return "</div>\n"; }

function closetab() { return "</div>\n</div>\n"; }

?>