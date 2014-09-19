<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: viewforum.php
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
require_once dirname(__FILE__)."../../maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."forum/main.php";
if (!defined("SELECT2")) {
	define("SELECT2", TRUE);
	add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js'></script>");
	add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />");
}
if (!isset($lastvisited) || !isnum($lastvisited)) {
	$lastvisited = time();
}
if (!isset($_GET['forum_id']) || !isnum($_GET['forum_id'])) {
	redirect("index.php");
}
if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
	$_GET['rowstart'] = 0;
}
$threads_per_page = $settings['threads_per_page'];
add_to_title($locale['global_200'].$locale['400']);
$result = dbquery("SELECT f.*, f2.forum_name AS forum_cat_name FROM ".DB_FORUMS." f
	LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
	".(multilang_table("FO") ? "WHERE f.forum_language='".LANGUAGE."' AND" : "WHERE")." f.forum_id='".$_GET['forum_id']."'");
if (dbrows($result)) {
	$fdata = dbarray($result);
	if (!checkgroup($fdata['forum_access']) || !$fdata['forum_cat']) {
		redirect("index.php");
	}
} else {
	//echo "lang fail";
	redirect("index.php");
}
if ($fdata['forum_post']) {
	$can_post = checkgroup($fdata['forum_post']);
} else {
	$can_post = FALSE;
}
//locale dependent forum buttons
if (is_array($fusion_images)) {
	if ($settings['locale'] != "English") {
		$newpath = "";
		$oldpath = explode("/", $fusion_images['newthread']);
		for ($i = 0; $i < count($oldpath)-1; $i++) {
			$newpath .= $oldpath[$i]."/";
		}
		if (is_dir($newpath.$settings['locale'])) {
			redirect_img_dir($newpath, $newpath.$settings['locale']."/");
		}
	}
}
//locale dependent forum buttons
if (iSUPERADMIN) {
	define("iMOD", TRUE);
}
if (!defined("iMOD") && iMEMBER && $fdata['forum_moderators']) {
	$mod_groups = explode(".", $fdata['forum_moderators']);
	foreach ($mod_groups as $mod_group) {
		if (!defined("iMOD") && checkgroup($mod_group)) {
			define("iMOD", TRUE);
		}
	}
}
if (!defined("iMOD")) {
	define("iMOD", FALSE);
}

add_to_title($locale['global_201'].$fdata['forum_name']);
if (isset($_POST['delete_threads']) && iMOD) {
	$thread_ids = "";
	if (isset($_POST['check_mark']) && is_array($_POST['check_mark'])) {
		foreach ($_POST['check_mark'] as $thisnum) {
			if (isnum($thisnum)) {
				$thread_ids .= ($thread_ids ? "," : "").$thisnum;
			}
		}
	}
	if ($thread_ids) {
		$result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_POSTS." WHERE thread_id IN (".$thread_ids.") GROUP BY post_author");
		if (dbrows($result)) {
			while ($pdata = dbarray($result)) {
				$result2 = dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts-".$pdata['num_posts']." WHERE user_id='".$pdata['post_author']."'");
			}
		}
		$result = dbquery("SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id IN (".$thread_ids.")");
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				if (file_exists(FORUM."attachments/".$data['attach_name'])) {
					unlink(FORUM."attachments/".$data['attach_name']);
				}
			}
		}
		$result = dbquery("DELETE FROM ".DB_POSTS." WHERE thread_id IN (".$thread_ids.") AND forum_id='".$_GET['forum_id']."'");
		$deleted_posts = mysql_affected_rows();
		$result = dbquery("DELETE FROM ".DB_THREADS." WHERE thread_id IN (".$thread_ids.") AND forum_id='".$_GET['forum_id']."'");
		$deleted_threads = mysql_affected_rows();
		$result = dbquery("DELETE FROM ".DB_THREAD_NOTIFY." WHERE thread_id IN (".$thread_ids.")");
		$result = dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id IN (".$thread_ids.")");
		$result = dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id IN (".$thread_ids.")");
		$result = dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id IN (".$thread_ids.")");
		$result = dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id IN (".$thread_ids.")");
		$result = dbquery("SELECT post_datestamp, post_author FROM ".DB_POSTS." WHERE forum_id='".$_GET['forum_id']."' ORDER BY post_datestamp DESC LIMIT 1");
		if (dbrows($result)) {
			$ldata = dbarray($result);
			$forum_lastpost = "forum_lastpost='".$ldata['post_datestamp']."', forum_lastuser='".$ldata['post_author']."'";
		} else {
			$forum_lastpost = "forum_lastpost='0', forum_lastuser='0'";
		}
		$result = dbquery("UPDATE ".DB_FORUMS." SET ".$forum_lastpost.", forum_postcount=forum_postcount-".$deleted_posts.", forum_threadcount=forum_threadcount-".$deleted_threads." WHERE forum_id='".$_GET['forum_id']."'");
	}
	$rows_left = dbcount("(thread_id)", DB_THREADS, "forum_id='".$_GET['forum_id']."'")-3;
	if ($rows_left <= $_GET['rowstart'] && $_GET['rowstart'] > 0) {
		$_GET['rowstart'] = ((ceil($rows_left/$threads_per_page)-1)*$threads_per_page);
	}
	redirect(FUSION_SELF."?forum_id=".$_GET['forum_id']."&rowstart=".$_GET['rowstart']);
}
opentable($locale['450']);
echo "<!--pre_forum-->\n";
echo "<ol class='forum_breadcrumbs breadcrumb'>\n";
echo "<li><a href='".FORUM."index.php'>".$locale['400']."</a></li>\n";
echo "<li><a href='".BASEDIR."forum/index.php?cat=".$fdata['forum_cat']."'>".$fdata['forum_cat_name']."</a></li>\n";
echo "<li>".$fdata['forum_name']."</li>\n";
echo "</ol>\n";


if (isset($_GET['filter']) && $_GET['filter'] == 1) {
	$time = isset($_GET['time']) && isnum($_GET['time']) ? $_GET['time'] : '';
	$type = isset($_GET['type']) && isnum($_GET['type']) ? $_GET['type'] : '';
	$sort = isset($_GET['sort']) && isnum($_GET['sort']) ? $_GET['sort'] : '';
	$order = isset($_GET['order']) && isnum($_GET['order']) ? $_GET['order'] : '';
	$col_order = 't.thread_lastpost';
	$col_time = 't.thread_lastpost';
	if ($sort == 2) {
		$col_order = 't.thread_postcount';
	} elseif ($sort == 3) {
		$col_order = "t.thread_subject";
	} elseif ($sort == 1) {
		$col_order = "post_datestamp";
		$col_time = "post_datestamp";
	}
	if ($col_time && $time) {
		$time_array = array('1' => time()-(24*60*60), '2' => time()-(7*24*60*60), '3' => time()-(30*24*60*60));
		$cond1 = "AND ($col_time BETWEEN '".$time_array[$time]."' AND '".time()."') ";
	}
	if ($type) {
		if ($type == 1) {
			$cond1 .= "AND (attach_name IS NULL OR attach_name='') AND (forum_poll_title IS NULL OR forum_poll_title='') ";
		} elseif ($type == 2) {
			$cond1 .= "AND attach_name !='' AND forum_poll_title='' ";
		} elseif ($type == 3) {
			$cond1 .= "AND attach_name ='' AND forum_poll_title !='' ";
		}
	}
	//echo $cond1;
	// end of cond1
	$cond2 = '';
	$ordering = ($order) ? "ASC" : "DESC";
	if ($col_order && $ordering) {
		$cond2 = "ORDER BY $col_order $ordering";
	}

	$result = dbquery("SELECT t.thread_id
                FROM ".DB_THREADS." t
                LEFT JOIN ".DB_USERS." tu1 ON t.thread_author = tu1.user_id
                LEFT JOIN ".DB_USERS." tu2 ON t.thread_lastuser = tu2.user_id
                LEFT JOIN ".DB_POSTS." p1 ON p1.thread_id = t.thread_id
                LEFT JOIN ".DB_FORUM_ATTACHMENTS." a ON a.thread_id = t.thread_id
                LEFT JOIN ".DB_FORUM_POLLS." p ON p.thread_id = t.thread_id
                WHERE t.forum_id='".$_GET['forum_id']."' AND thread_hidden='0' $cond1
                GROUP BY thread_id");
	$rows = dbrows($result);
} else {
	$rows = dbcount("(thread_id)", DB_THREADS, "forum_id='".$_GET['forum_id']."' AND thread_hidden='0'");
}
$post_info = "";
if ($rows > $threads_per_page || (iMEMBER && $can_post)) {
	$post_info .= "<table class='table table-responsive'>\n<tr>\n";
	if (iMEMBER && $can_post) {
		$post_info .= "<td align='right' style='border:0; padding:4px 0px 4px 0px'>";
		// need to fix whether a theme has image
		$post_info .= "<a href='".FORUM."post.php?action=newthread&amp;forum_id=".$_GET['forum_id']."'><img src='".get_image("newthread")."' title='".$locale['566']."' alt='".$locale['566']."' style='border:0px;' /></a>";
		$post_info .= "</td>\n";
	}
	$post_info .= "</tr>\n</table>\n";
}
echo $post_info;
echo "<div class='forum-table-container panel-body'>\n";
// forum jumper.
$forum_list = array();
$current_cat = "";
$result2 = dbquery("SELECT f.forum_id, f.forum_name, f2.forum_id AS forum_cat_id, f2.forum_name AS forum_cat_name
                    FROM ".DB_FORUMS." f
                    INNER JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
                    WHERE ".groupaccess('f.forum_access')." AND f.forum_cat!='0' ORDER BY f2.forum_order ASC, f.forum_order ASC");
// group.
while ($data2 = dbarray($result2)) {
	// first, we sort the items out into parent-child array.
	if ($data2['forum_cat_name'] != $current_cat) {
		$forum_list[$data2['forum_cat_id']] = array('text' => $data2['forum_cat_name']);
		$forum_list[$data2['forum_cat_id']]['children'][] = array('id' => $data2['forum_id'],
																  'text' => $data2['forum_name']);
	} else {
		$forum_list[$data2['forum_cat_id']]['children'][] = array('id' => $data2['forum_id'],
																  'text' => $data2['forum_name']);
	}
	$current_cat = $data2['forum_cat_name'];
}
// next json encode every array into a single string
$forum_opts = '';
$i = 0;
foreach ($forum_list as $array) {
	$forum_opts .= ($i == count($forum_list)-1) ? json_encode($array) : json_encode($array).",";
	$i++;
}

echo form_hidden('', 'jump_id', 'jump_id', '');
// finally, push string to select2, and invoke select2 to hidden input.
// .. add a redirect to onchange event.

add_to_jquery("
    var this_data = [];
    this_data.push($forum_opts);
    $('#jump_id').select2({
    placeholder: '".$locale['540']."',
    data : this_data
    }).bind('change', function() {
       document.location.href='".FORUM."viewforum.php?forum_id='+$(this).val();
    });
    ");
if ($rows > $threads_per_page) {
	$filter_url = (isset($_GET['filter']) && $_GET['filter'] == 1) ? "&amp;time=".$_GET['time']."&amp;type=".$_GET['type']."&amp;sort=".$_GET['sort']."&amp;order=".$_GET['order']."&amp;filter=1&amp;" : "&amp;";
	$page_nav = "<div id='pagenav' class='pull-right display-inline-block m-r-10'>\n".makepagenav($_GET['rowstart'], $threads_per_page, $rows, 3, BASEDIR."forum/viewforum.php?forum_id=".$_GET['forum_id'].$filter_url."")."</div>\n";
}
// Add filter
echo form_button($locale['530']." <span class='caret'></span>", 'filter-btn', 'filter-btn', $locale['530'], array('class' => 'btn-primary pull-right', 'type' => 'button'));
echo $page_nav;
echo "</div>\n";

// filter class extract
echo "<div id='filter' class='".(isset($_GET['filter']) && $_GET['filter'] == 1 ? '' : 'display-none')." panel-footer'>\n";
// problem with seo not posting to the correct url.
echo openform('filterform', 'filterform', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').FORUM."viewforum.php?forum_id=".$_GET['forum_id']."&amp;filter=1", array('downtime' => 0));
echo "<div class='row filter-form'>\n";
echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
echo "<span><strong>".$locale['531']."</strong></span>\n<br/>";
$array = array('0' => $locale['531a'], '1' => $locale['531b'], '2' => $locale['531c'], '3' => $locale['531d'],);
foreach ($array as $key => $value) {
	$selected = (isset($_GET['time']) && $_GET['time'] == $key) ? "checked" : "";
	echo "<input id='$key-$value' type='radio' name='time' value='$key' $selected/><label class='m-l-10 text-normal text-smaller' for='$key-$value'>$value</label>\n<br/>\n";
}
echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
echo "<span><strong>".$locale['532']."</strong></span>\n<br/>";
$array = array('0' => $locale['532a'], '1' => $locale['532b'], '2' => $locale['532c'], '3' => $locale['532d'],);
foreach ($array as $key => $value) {
	$selected = (isset($_GET['type']) && $_GET['type'] == $key) ? "checked" : "";
	echo "<input id='$key-$value' type='radio' name='type' value='$key' $selected/><label class='m-l-10 text-normal text-smaller' for='$key-$value'>$value</label>\n<br/>\n";
}
echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
echo "<span><strong>".$locale['533']."</strong></span>\n<br/>";
$array = array('0' => $locale['533a'], '1' => $locale['533b'], '2' => $locale['533c'], '3' => $locale['533d'],);
foreach ($array as $key => $value) {
	$selected = (isset($_GET['sort']) && $_GET['sort'] == $key) ? "checked" : "";
	echo "<input id='$key-$value' type='radio' name='sort' value='$key' $selected/><label class='m-l-10 text-normal text-smaller' for='$key-$value'>$value</label>\n<br/>\n";
}
echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
echo "<span><strong>".$locale['534']."</strong></span>\n<br/>";
$array = array('0' => $locale['534a'], '1' => $locale['534b']);
foreach ($array as $key => $value) {
	$selected = (isset($_GET['order']) && $_GET['order'] == $key) ? "checked" : "";
	echo "<input id='$key-$value' type='radio' name='order' value='$key' $selected/><label class='m-l-10 text-normal text-smaller' for='$key-$value'>$value</label>\n<br/>\n";
}
echo form_button('Go', 'gofilter', 'gofilter', 'go', array('class' => 'btn-primary pull-right'));

echo "</div>\n</div>\n";
echo closeform();
echo "</div>\n";
add_to_jquery("
    $('#filter-btn').bind('click', function() {
        $('#filter').slideToggle();
    });
    ");

if (iMOD) {
	echo openform('mod_form', 'mod_form', 'post', FORUM."viewforum.php?forum_id=".$_GET['forum_id']."&amp;rowstart=".$_GET['rowstart']);
}
echo "<table class='tbl-border forum_table table table-responsive'>\n<thead>\n<tr>\n";
echo "<th class='tbl2 forum-caption' colspan='2'>".$locale['451']."</th>\n";
echo "<th class='tbl2 forum-caption' width='1%' style='white-space:nowrap'>".$locale['452']."</th>\n";
echo "<th class='tbl2 forum-caption' width='1%' style='white-space:nowrap' align='center' >".$locale['453']."</th>\n";
echo "<th class='tbl2 forum-caption' width='1%' style='white-space:nowrap' align='center'>".$locale['454']."</th>\n";
echo "<th class='tbl2 forum-caption' style='width: 250px;'>".$locale['404']."</th>\n</tr>\n</thead>\n<tbody id='threadlisting'>\n"; // <-- filter hit target
if ($rows) {

	if (isset($_POST['gofilter'])) {
			foreach ($_POST as $key => $value) {
			$_fdata[$key] = form_sanitizer($value, '0');
		}
		// redirect to get.
		if (!defined('FUSION_NULL')) {
			$time = isset($_fdata['time']) ? "&time=".$_fdata['time']."" : '&time=0';
			$type = isset($_fdata['type']) ? "&type=".$_fdata['type']."" : '&type=0';
			$sort = isset($_fdata['sort']) ? "&sort=".$_fdata['sort']."" : '&sort=0';
			$order = isset($_fdata['order']) ? "&order=".$_fdata['order']."" : '&order=0';
			$filter = "&amp;filter=1";
			$filter_url = FORUM."viewforum.php?forum_id=".$_GET['forum_id'].$time.$type.$sort.$order.$filter;
			redirect($filter_url);
		}
	}
	if (isset($_GET['filter']) && $_GET['filter'] == 1) {
		$result = dbquery("SELECT t.*, tu1.user_name AS user_author, tu1.user_status AS status_author,
                tu2.user_name AS user_lastuser, tu2.user_status AS status_lastuser, tu2.user_avatar AS user_avatar,
                p1.post_datestamp,
                a.attach_name, p.forum_poll_title
                FROM ".DB_THREADS." t
                LEFT JOIN ".DB_USERS." tu1 ON t.thread_author = tu1.user_id
                LEFT JOIN ".DB_USERS." tu2 ON t.thread_lastuser = tu2.user_id
                LEFT JOIN ".DB_POSTS." p1 ON p1.thread_id = t.thread_id
                LEFT JOIN ".DB_FORUM_ATTACHMENTS." a ON a.thread_id = t.thread_id
                LEFT JOIN ".DB_FORUM_POLLS." p ON p.thread_id = t.thread_id
                WHERE t.forum_id='".$_GET['forum_id']."' AND thread_hidden='0' $cond1
                GROUP BY thread_id $cond2 LIMIT ".$_GET['rowstart'].",$threads_per_page");
		$numrows = dbrows($result);
	} else {
		$result = dbquery("SELECT t.*, tu1.user_name AS user_author, tu1.user_status AS status_author,
            tu2.user_name AS user_lastuser, tu2.user_status AS status_lastuser, tu2.user_avatar AS user_avatar
            FROM ".DB_THREADS." t
            LEFT JOIN ".DB_USERS." tu1 ON t.thread_author = tu1.user_id
            LEFT JOIN ".DB_USERS." tu2 ON t.thread_lastuser = tu2.user_id
            WHERE t.forum_id='".$_GET['forum_id']."' AND thread_hidden='0'
            ORDER BY thread_sticky DESC, thread_lastpost DESC LIMIT ".$_GET['rowstart'].",$threads_per_page");
		$numrows = dbrows($result);
	}
	if ($numrows) {
		while ($tdata = dbarray($result)) {
			$thread_match = $tdata['thread_id']."\|".$tdata['thread_lastpost']."\|".$fdata['forum_id'];
			echo "<tr>\n";
			$icon = '';
			$sticky_status = '';
			// sticky icon
			if ($tdata['thread_sticky'] == 1) {
				$sticky_status = "<span>".$locale['474']." : </span>\n";
				$icon .= "<img class='forum-icon-stickythread' title='".$locale['474']."' src='".get_image("stickythread")."' alt='".$locale['474']."' style='vertical-align:middle;' />\n";
			}
			// hot icon
			if ($tdata['thread_postcount'] >= 50) {
				$icon .= "<img class='forum-icon-hotthread' src='".get_image("hot")."' alt='".$locale['611']."' title='".$locale['611']."' alt='".$locale['611']."' style='vertical-align:middle;' />&nbsp;&nbsp;";
			}
			// attach icon
			$attach_icons = dbquery("SELECT attach_id, attach_ext FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id = '".$tdata['thread_id']."' AND (attach_ext='.zip' OR attach_ext='.rar')");
			if (dbrows($attach_icons)) {
				$icon .= "<img class='forum-icon-attachthread' src='".get_image("attach")."' alt='".$locale['612']."' title='".$locale['612']."' style='vertical-align:middle;' />&nbsp;&nbsp;";
			}
			// image attach icon
			$attach_icons2 = dbquery("SELECT attach_id, attach_ext FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id = '".$tdata['thread_id']."' AND (attach_ext='.gif' OR attach_ext='.jpg' OR attach_ext='.png')");
			if (dbrows($attach_icons2)) {
				$icon .= "<img class='icon-imgattachthread' src='".get_image("image_attach")."' alt='".$locale['613']."' title='".$locale['613']."' style='vertical-align:middle;' />&nbsp;&nbsp;";
			}
			// poll icon
			if ($tdata['thread_poll']) {
				$icon .= "<img class='icon-pollthread' src='".get_image("poll_posticon")."' alt='".$locale['614']."' title='".$locale['614']."' style='vertical-align:middle;' />&nbsp;&nbsp;";
			}
			// what is this?
			if (dbcount("(attach_id)", DB_FORUM_ATTACHMENTS, "thread_id='".$tdata['thread_id']."'") > 0) {
				echo "<div style='float:right'><img src='".get_image("attach")."' alt='".$locale['612']."' title='".$locale['612']."' style='vertical-align:middle;' /></div>";
			}
			// folder graphics
			if ($tdata['thread_locked']) {
				echo "<td align='center' width='25' class='tbl2 forum-icon'><img class='img-responsive' src='".get_image("folderlock")."' alt='".$locale['564']."' /></td>";
			} else {
				// normal folder
				if ($tdata['thread_lastpost'] > $lastvisited) {
					if (iMEMBER && ($tdata['thread_lastuser'] == $userdata['user_id'] || preg_match("(^\.{$thread_match}$|\.{$thread_match}\.|\.{$thread_match}$)", $userdata['user_threads']))) {
						$folder = "<img class='img-responsive' src='".get_image("folder")."' alt='".$locale['561']."' />";
					} else {
						$folder = "<img class='img-responsive' src='".get_image("foldernew")."' alt='".$locale['560']."' />";
					}
				} else {
					$folder = "<img class='img-responsive' src='".get_image("folder")."' alt='".$locale['561']."' />";
				}
				echo "<td align='center' width='1%' class='tbl2 forum-icon' style='white-space:nowrap'>$folder</td>";
			}
			$reps = ceil($tdata['thread_postcount']/$threads_per_page);
			$threadsubject = "<h3 class='display-inline'>$sticky_status<a href='".FORUM."viewthread.php?thread_id=".$tdata['thread_id']."'>".$tdata['thread_subject']."</a> $icon</h3>";
			if ($reps > 1) {
				$ctr = 0;
				$ctr2 = 1;
				$pages = "";
				$middle = FALSE;
				while ($ctr2 <= $reps) {
					if ($reps < 5 || ($reps > 4 && ($ctr2 == 1 || $ctr2 > ($reps-3)))) {
						$pnum = "<a href='viewthread.php?thread_id=".$tdata['thread_id']."&amp;rowstart=$ctr'>$ctr2</a> ";
					} else {
						if ($middle == FALSE) {
							$middle = TRUE;
							$pnum = "... ";
						} else {
							$pnum = "";
						}
					}
					$pages .= $pnum;
					$ctr = $ctr+$threads_per_page;
					$ctr2++;
				}
				$threadsubject .= "<br/><span class='forum-pages'><small>(".$locale['455'].trim($pages).")</small></span>\n";
			}
			echo "<td class='tbl1 forum-name'>";
			if (iMOD) {
				echo "<div class='pull-left m-r-10 display-block' style='height:40px'>\n";
				echo "<input type='checkbox' name='check_mark[]' value='".$tdata['thread_id']."' />\n";
				echo "</div>\n";
			}
			echo $threadsubject;
			echo "</td>\n";
			echo "<td width='1%' class='tbl2' style='white-space:nowrap'>".profile_link($tdata['thread_author'], $tdata['user_author'], $tdata['status_author'])."</td>\n";
			echo "<td align='center' width='1%' class='tbl1' style='white-space:nowrap'>".$tdata['thread_views']."</td>\n";
			echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>".($tdata['thread_postcount']-1)."</td>\n";
			echo "<td class='tbl1' style='white-space:nowrap'>";
			// Show avatar of the last user that made a post
			if ($settings['forum_last_post_avatar'] == 1) {
				echo "<div class='clearfix'>\n";
				if ($tdata['status_lastuser'] != 6 && $tdata['status_lastuser'] != 5) {
					$tdata['user_status'] = $tdata['status_lastuser'];
					$tdata['user_name'] = $tdata['user_lastuser'];
					echo "<div class='pull-left lastpost-avatar m-r-10'>".display_avatar($tdata, '50px')."</div>";
				}
			}
			echo "<span class='lastpost-user small'>by ".profile_link($tdata['thread_lastuser'], $tdata['user_lastuser'], $tdata['status_lastuser'])."</span><br />";
			echo "<span class='lastpost-date small'>".showdate("forumdate", $tdata['thread_lastpost'])."</span>\n";
			echo "</div>\n</td>\n";
			echo "</tr>\n";
		}
	} else {
		echo "<tr>\n<td class='text-center' colspan='6'>".$locale['574']."</td>\n</tr>\n";
	}
	echo "</tbody>\n</table><!--sub_forum_table-->\n";
} else {
	if (!$rows) {
		echo "<tr>\n<td colspan='6' class='tbl1' style='text-align:center'>".$locale['456']."</td>\n</tr>\n</table><!--sub_forum_table-->\n";
	} else {
		echo "</tbody>\n</table><!--sub_forum_table-->\n";
	}
}
if (iMOD) {

	if ($rows) {
		echo "<div class='forum-table-container panel-body'>\n";
		echo "<div class='btn-group m-r-10'>\n";
		echo "<a id='check' class='btn btn-default button' href='#' onclick=\"javascript:setChecked('mod_form','check_mark[]',1);return false;\">".$locale['460']."</a>\n";
		echo "<a id='uncheck' class='btn btn-default button' href='#' onclick=\"javascript:setChecked('mod_form','check_mark[]',0);return false;\">".$locale['461']."</a>\n";
		echo "</div>\n";
		echo form_button($locale['463'], 'delete_threads', 'delete_threads', $locale['463'], array('class'=>'btn-danger m-r-10'));
		echo "</div>\n";
	}
	echo "</form>\n";
	if ($rows) {
		echo "<script type='text/javascript'>\n";
		echo "/* <![CDATA[ */\n";
		echo "function setChecked(frmName,chkName,val) {\n";
		echo "dml=document.forms[frmName];\n"."len=dml.elements.length;\n"."for(i=0;i < len;i++) {\n";
		echo "if(dml.elements[i].name == chkName) {\n"."dml.elements[i].checked = val;\n}\n}\n}\n";
		echo "/* ]]>*/\n";
		echo "</script>\n";
	}
}
echo $post_info; // the button

echo "<table class='tbl-border table table-responsive' border='0' width='100%' align='center'>";
echo "<tr><td><img src='".get_image("foldernew")."' alt='".$locale['560']."' style='vertical-align:middle; width:24px;' /> - ".$locale['470']."</td>";
echo "<td><img src='".get_image("folder")."' alt='".$locale['561']."' style='vertical-align:middle; width:24px;' /> - ".$locale['472']."</td></tr>";
echo "<tr><td><img src='".get_image("folderlock")."' alt='".$locale['564']."' style='vertical-align:middle; width:24px;' /> - ".$locale['473']."</td>";
echo "<td><img src='".get_image("stickythread")."' alt='".$locale['563']."' style='vertical-align:middle; width:24px;' /> - ".$locale['474']."</td></tr>";
echo "<tr><td><img src='".get_image("hot")."' alt='".$locale['611']."' style='vertical-align:middle; width:24px;' /> - ".$locale['611']."</td>";
echo "<td><img src='".get_image("poll_posticon")."' alt='".$locale['614']."' style='vertical-align:middle; width:24px;' /> - ".$locale['614']."</td></tr>";
echo "<tr><td><img src='".get_image("attach")."' alt='".$locale['612']."' style='vertical-align:middle; width:24px;' /> - ".$locale['612']."</td>";
echo "<td><img src='".get_image("image_attach")."' alt='".$locale['613']."' style='vertical-align:middle; width:24px;' /> - ".$locale['613']."</td></tr>";
echo "</table>\n<!--sub_forum-->\n";
closetable();
echo "<script type='text/javascript'>\n"."function jumpforum(forumid) {\n";
echo "document.location.href='".FORUM."viewforum.php?forum_id='+forumid;\n}\n";
echo "</script>\n";
list($threadcount, $postcount) = dbarraynum(dbquery("SELECT COUNT(thread_id), SUM(thread_postcount) FROM ".DB_THREADS." WHERE forum_id='".$_GET['forum_id']."' AND thread_hidden='0'"));
if (isnum($threadcount) && isnum($postcount)) {
	dbquery("UPDATE ".DB_FORUMS." SET forum_postcount='$postcount', forum_threadcount='$threadcount' WHERE forum_id='".$_GET['forum_id']."'");
}
require_once THEMES."templates/footer.php";
?>