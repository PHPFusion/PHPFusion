<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_main.php
| Author: Frederick MC Chan (Hien)
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
/**
 * Forum Page Control
 */
if (!function_exists('render_forum')) {
	function render_forum($info) {
		echo render_breadcrumbs();
		if (isset($_GET['viewforum'])) {
			forum_viewforum($info);
		} else {
			render_forum_main($info);
		}
	}
}

/**
 * Forum Page
 */
if (!function_exists('render_forum_main')) {
	/**
	 * Main Forum Page - Recursive
	 * @param $info
	 */
	function render_forum_main($info, $id = 0) {
		global $locale;
		echo "<div class='forum-title'>".$locale['forum_0013']."</div>\n";
		if (!empty($info['forums'][$id])) {
			$forums = $info['forums'][$id];
			$x = 1;
			echo "<div class='list-group-item'>\n";
			foreach ($forums as $forum_id => $data) {
				if ($data['forum_type'] == '1') {
					echo "<div class='panel panel-default'>\n";
					echo "<div class='panel-heading' ".(isset($data['child']) ? 'style="border-bottom:0;"' : '').">\n";
					echo "<a class='forum-subject' href='".INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$data['forum_id']."&amp;parent_id=".$data['forum_cat']."&amp;forum_branch=".$data['forum_branch']."'>".$data['forum_name']."</a><br/>";
					echo $data['forum_description'] ? "<span class='text-smaller'>".$data['forum_description']."</span>\n<br/>" : '';
					echo "</div>\n";
					if (isset($info['forums'][0][$forum_id]['child'])) {
						echo "<div class='m-10'>\n";
						$i = 1;
						$sub_forums = $info['forums'][0][$forum_id]['child'];
						foreach ($sub_forums as $sub_forum_id => $cdata) {
							render_forum_item_type($cdata, $i);
							$i++;
						}
						echo "</div>\n";
					} else {
						echo "<div class='panel-body text-center'>\n";
						echo $locale['forum_0327'];
						echo "</div>\n";
					}
					echo "</div>\n"; // end panel-default
				} else {
					render_forum_item($data, $x);
					$x++;
				}
			}
			echo "</div>\n";
		} else {
			echo "<div class='well text-center'>".$locale['forum_0328']."</div>\n";
		}
	}
}

/**
 * Forum Item
 */
if (!function_exists('render_forum_item_type')) {
	/**
	 * Switch between different types of forum list containers
	 * @param $data
	 * @param $i
	 */
	function render_forum_item($data, $i) {
		global $locale;
		if ($i > 0) {
			echo "<div id='forum_".$data['forum_id']."' class='forum-container'>\n";
		} else {
			echo "<div id='forum_".$data['forum_id']."' class='panel panel-default'>\n";
			echo "<div class='panel-body'>\n";
		}
		echo "<div class='pull-left forum-thumbnail'>\n";
		if ($data['forum_image'] && file_exists(IMAGES."forum/".$data['forum_image'])) {
			echo thumbnail(IMAGES."forum/".$data['forum_image'], '50px');
		} else {
			echo "<div class='forum-icon'>".$data['forum_icon_lg']."</div>\n";
		}
		echo "</div>\n";
		echo "<div class='overflow-hide'>\n";
		echo "<div class='row m-0'>\n";
		switch ($data['forum_type']) {
			case '3':
				echo "<div class='col-xs-12 col-sm-12'>\n";
				echo "<a class='display-inline-block forum-link' href='".$data['forum_link']."'>".$data['forum_name']."</a>\n<span class='m-l-5'>".$data['forum_new_status']."</span><br/>";
				echo $data['forum_description'] ? "<div class='forum-description'>".$data['forum_description']."</div>\n" : '';
				echo ($data['forum_moderators'] ? "<span class='forum-moderators text-smaller'><strong>".$locale['forum_0007']."</strong>".$data['forum_moderators']."</span>\n" : "")."\n";
				if (isset($data['child'])) {
					echo "<div class='clearfix sub-forum'>\n";
					echo "<div class='pull-left'>\n";
					echo "<i class='entypo level-down'></i>\n";
					echo "</div>\n";
					echo "<div class='overflow-hide'>\n";
					foreach ($data['child'] as $cdata) {
						echo "<span class='nowrap'>\n";
						if (isset($cdata['forum_type'])) {
							echo $data['forum_icon'];
						}
						echo "<a href='".INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$cdata['forum_id']."&amp;parent_id=".$cdata['forum_cat']."&amp;forum_branch=".$cdata['forum_branch']."' class='forum-subforum display-inline-block m-r-10'>".$cdata['forum_name']."</a></span>";
					}
					echo "</div>\n";
					echo "</div>\n";
				}
				echo "</div>\n";
				break;
			default:
				echo "<div class='col-xs-12 col-sm-6'>\n";
				echo "
				<a class='display-inline-block forum-link' href='".$data['forum_link']."'>".$data['forum_name']."</a>\n<span class='m-l-5'>".$data['forum_new_status']."</span><br/>";
				echo $data['forum_description'] ? "<div class='forum-description'>".$data['forum_description']."</div>\n" : '';
				echo ($data['forum_moderators'] ? "<span class='forum-moderators text-smaller'><strong>".$locale['forum_0007']."</strong>".$data['forum_moderators']."</span>\n" : "")."\n";
				if (isset($data['child'])) {
					echo "<div class='clearfix sub-forum'>\n";
					echo "<div class='pull-left'>\n";
					echo "<i class='entypo level-down'></i>\n";
					echo "</div>\n";
					echo "<div class='overflow-hide'>\n";
					foreach ($data['child'] as $cdata) {
						echo "<span class='nowrap'>\n";
						if (isset($cdata['forum_type'])) {
							echo $data['forum_icon'];
						}
						echo "<a href='".INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$cdata['forum_id']."&amp;parent_id=".$cdata['forum_cat']."&amp;forum_branch=".$cdata['forum_branch']."' class='forum-subforum display-inline-block m-r-10'>".$cdata['forum_name']."</a></span>";
					}
					echo "</div>\n";
					echo "</div>\n";
				}
				echo "</div>\n";
				echo "<div class='hidden-xs col-sm-3 col-md-2 p-l-0 text-right'>\n";
				echo "<div class='text-lighter count'>".$data['forum_postcount']."</div>\n";
				echo "<div class='text-lighter count'>".$data['forum_threadcount']."</div>\n";
				echo "</div><div class='forum-lastuser hidden-xs hidden-sm col-md-4'>\n";
				if ($data['forum_lastpostid'] == 0) {
					echo $locale['forum_0005'];
				} else {
					echo "<div class='clearfix'>\n";
					if (!empty($data['last_post']['avatar'])) echo "<div class='pull-left lastpost-avatar m-t-5'>".$data['last_post']['avatar']."</div>";
					echo "<div class='overflow-hide'>\n";
					echo "<span class='forum_profile_link'>".$data['last_post']['profile_link']." ".$data['last_post']['time']."</span>\n";
					echo "<a class='lastpost-goto' href='".$data['last_post']['post_link']."' title='".$data['thread_subject']."'><i class='fa fa-external-link-square'></i></a><br />\n";
					echo fusion_first_words(strip_tags($data['last_post']['message']), 10);
					echo "</div>\n</div>\n";
				}
				echo "</div>\n";
		}
		echo "</div>\n"; // end row
		echo "</div>\n"; // end overflow-hide
		if ($i > 0) {
			echo "</div>\n";
		} else {
			echo "</div>\n</div>\n";
		}
	}
}

/**
 * For $_GET['viewforum'] view present.
 */
if (!function_exists('forum_viewforum')) {
	function forum_viewforum($info) {
		global $locale;
		$data = $info['item'][$_GET['forum_id']];

		echo "<div class='forum-title'>\n";
		echo "<h4>".$data['forum_name']." <span class='sub-title'>".$data['forum_threadcounter']."</span></h4>\n";
		echo "<div class='forum-description'>\n".$data['forum_description']."</div>\n";
		echo "</div>\n";

		if (iMEMBER && $info['permissions']['can_post']) {
			echo "
			<div class='clearfix m-b-20 m-t-20'>\n
				<a title='".$locale['forum_0264']."' class='btn btn-primary btn-sm' href='".$info['new_thread_link']."'>".$locale['forum_0264']."</a>\n
			</div>\n
			";
		}

		echo $data['forum_rules'] ? "<div class='well'><span class='strong'><i class='fa fa-exclamation fa-fw'></i>".$locale['forum_0350']."</span> ".$data['forum_rules']."</div>\n" : '';
		// subforums
		if (isset($info['item'][$_GET['forum_id']]['child'])) {
			echo "<div class='forum-title m-t-20'>".$locale['forum_0351']."</div>\n";
			$i = 1;
			foreach ($info['item'][$_GET['forum_id']]['child'] as $subforum_id => $subforum_data) {
				render_forum_item($subforum_data, $i);
				$i++;
			}
		}
		echo "<!--pre_forum-->\n";
		echo "<div class='forum-title m-t-20'><h6>".$locale['forum_0341']."</h6>\n</div>\n";
		echo "<div class='filter'>\n";
		forum_filter($info);
		echo "</div>\n";
		echo $info['forum_moderators'];
		if (!empty($info['threads'])) {
			echo "<div class='forum-container'>\n";
			if (!empty($info['threads']['sticky'])) {
				foreach ($info['threads']['sticky'] as $cdata) {
					render_thread_item($cdata);
				}
			}
			if (!empty($info['threads']['item'])) {
				foreach ($info['threads']['item'] as $cdata) {
					render_thread_item($cdata);
				}
			}
			echo "</div>\n";
		} else {
			echo "<div class='text-center'>".$locale['forum_0269']."</div>\n";
		}
	}
}
/* display threads -- need to simplify */
if (!function_exists('render_thread_item')) {
	function render_thread_item($data) {
		global $locale, $info, $userdata;
		echo "<div class='thread-item' id='thread_".$data['thread_id']."'>\n";
		echo "<div class='row m-0'>\n";
		echo "<div class='col-xs-12 col-sm-9 col-md-6 p-l-0'>\n";
		echo "<div class='pull-left m-r-10 m-t-5'>\n".$data['thread_lastuser']['avatar']."</div>\n";
		unset($data['thread_status']['icon']);
		$thead_icons = '';
		foreach ($data['thread_status'] as $icon) {
			$thead_icons .= $icon;
		}
		echo "<div class='overflow-hide'>\n";
		echo "<a class='forum-link' href='".$data['thread_link']."'>".$data['thread_subject']."</a>\n<span class='m-l-10 m-r-10 text-lighter'>".$thead_icons."</span>\n";
		echo "<div class='text-smaller'>".$data['thread_starter']."</div>\n";
		echo $data['thread_pages'];
		echo isset($data['track_button']) ? "<div class='forum_track'><a onclick=\"return confirm('".$locale['global_060']."');\" href='".$data['track_button']['link']."'>".$data['track_button']['name']."</a>\n</div>\n" : '';
		echo "</div>\n";
		echo "</div>\n"; // end grid
		echo "<div class='hidden-xs col-sm-3 col-md-3 p-l-0 p-r-0 text-center'>\n";
		echo "<div class='display-inline-block forum-stats p-5 m-r-5 m-b-0'>\n";
		echo "<h4 class='text-bigger strong text-dark m-0'>".number_format($data['thread_views'])."</h4>\n";
		echo "<span>".format_word($data['thread_views'], $locale['fmt_views'], 0)."</span>";
		echo "</div>\n";
		echo "<div class='display-inline-block forum-stats p-5 m-r-5 m-b-0'>\n";
		echo "<h4 class='text-bigger strong text-dark m-0'>".number_format($data['thread_postcount'])."</h4>\n";
		echo "<span>".format_word($data['thread_postcount'], $locale['fmt_post'], 0)."</span>";
		echo "</div>\n";
		if ($data['forum_type'] == '4') {
			echo "<div class='display-inline-block forum-stats p-5 m-r-5 m-b-0'>\n";
			echo "<h4 class='text-bigger strong text-dark m-0'>".number_format($data['vote_count'])."</h4>\n";
			echo "<span>".format_word($data['vote_count'], $locale['fmt_vote'], 0)."</span>";
			echo "</div>\n";
		}
		echo "</div>\n"; // end grid
		echo "<div class='forum-lastuser hidden-xs hidden-sm col-md-3'>
			".$data['thread_lastuser']['profile_link']." ".timer($data['thread_lastuser']['time'])."<br/>
			".fusion_first_words(strip_tags($data['thread_lastuser']['post_message']), 10)."
		</div>\n";
		echo "</div>\n";
		echo "</div>\n";
	}
}
/* My Post Section */
if (!function_exists('render_mypost')) {
	function render_mypost($info) {
		global $locale;
		$type_icon = array('1' => 'entypo folder',
			'2' => 'entypo chat',
			'3' => 'entypo link',
			'4' => 'entypo graduation-cap');
		if (!empty($info['item'])) {
			// sort by date.
			$last_date = '';
			$i = 0;
			foreach ($info['item'] as $data) {
				$cur_date = date('M d, Y', $data['post_datestamp']);
				$xim = '';
				if ($cur_date != $last_date) {
					$last_date = $cur_date;
					$title = "<div class='post_title m-b-10'>Posts on ".$last_date."</div>\n";
					echo $i > 0 ? "</div>\n".$title."<div class='list-group'>\n" : $title."<div class='list-group'>\n";
				}
				echo "<div class='list-group-item clearfix'>\n";
				echo "<div class='pull-left m-r-10'>\n";
				echo "<i class='".$type_icon[$data['forum_type']]." icon-sm low-opacity'></i>";
				echo "</div>\n";
				echo "<div class='overflow-hide'>\n";
				echo "<a class='post_title strong' href='".INFUSIONS."forum/viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['post_id']."#post_".$data['post_id']."' title='".$data['thread_subject']."'>".trimlink($data['thread_subject'], 40)."</a>\n";
				echo "<br/><span class='forum_name'>".trimlink($data['forum_name'], 30)."</span> <span class='thread_date'>&middot; ".showdate("forumdate", $data['post_datestamp'])."</span>\n";
				echo "</div>\n";
				echo "</div>\n";
				$i++;
			}
			echo "</div>\n"; // addition of a div the first time which did not close where $i = 0;
			if ($info['post_rows'] > 20) {
				echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 20, $info['post_rows'], 3)."\n</div>\n";
			}
		} else {
			echo "<div class='well text-center'>".$locale['global_054']."</div>\n";
		}
		// not used locale. global_042, 048, 044, 049
	}
}
/* Latest Section */
if (!function_exists('render_laft')) {
	function render_laft($info) {
		global $locale;
		if (!empty($info['item'])) {
			$i = 0;
			foreach ($info['item'] as $data) {
				// do a thread.
				render_thread_item($data);
				$i++;
			}
		} else {
			echo "<div class='well text-center'>".$locale['global_023']."</div>\n";
		}
		// filter --- this need to be translated to links.
		$opts = array('0' => 'All Results',
			'1' => '1 Day',
			'7' => '7 Days',
			'14' => '2 Weeks',
			'30' => '1 Month',
			'90' => '3 Months',
			'180' => '6 Months',
			'365' => '1 Year');
		echo "<hr/>\n";
		echo openform('filter_form', 'post', INFUSIONS."forum/index.php?section=latest", array('downtime' => 1));
		echo form_select('filter', $locale['forum_0009'], isset($_POST['filter']) && $_POST['filter'] ? $_POST['filter'] : 0, array('options' => $opts,
			'width' => '300px',
			'class' => 'pull-left m-r-10'));
		echo form_button('go', $locale['go'], $locale['go'], array('class' => 'btn-default btn-sm m-b-20'));
		echo closeform();
	}
}
/* Tracked Section */
if (!function_exists('render_tracked')) {
	function render_tracked($info) {
		global $locale;
		if (!empty($info['item'])) {
			$i = 0;
			foreach ($info['item'] as $data) {
				// do a thread.
				render_thread_item($data);
				$i++;
			}
		} else {
			echo "<div class='well text-center'>".$locale['global_059']."</div>\n";
		}
	}
}
/* Forum Filter */
if (!function_exists('forum_filter')) {
	function forum_filter($info) {
		global $locale;
		$selector = array('today' => $locale['forum_p000'],
			'2days' => $locale['forum_p002'],
			'1week' => $locale['forum_p007'],
			'2week' => $locale['forum_p014'],
			'1month' => $locale['forum_p030'],
			'2month' => $locale['forum_p060'],
			'3month' => $locale['forum_p090'],
			'6month' => $locale['forum_p180'],
			'1year' => $locale['forum_p365']);
		$selector2 = array('all' => $locale['forum_0374'],
			'discussions' => $locale['forum_0375'],
			'attachments' => $locale['forum_0376'],
			'poll' => $locale['forum_0377'],
			'solved' => $locale['forum_0378'],
			'unsolved' => $locale['forum_0379'],);
		$selector3 = array('author' => $locale['forum_0380'],
			'time' => $locale['forum_0381'],
			'subject' => $locale['forum_0382'],
			'reply' => $locale['forum_0383'],
			'view' => $locale['forum_0384'],);
		$selector4 = array('ascending' => $locale['forum_0385'],
			'descending' => $locale['forum_0386'],);
		echo $locale['forum_0388'];
		echo "<div class='forum-filter'>\n";
		echo "<button class='btn btn-xs btn-default' data-toggle='dropdown' class='dropdown-toggle'>".(isset($_GET['time']) && in_array($_GET['time'], array_flip($selector)) ? $selector[$_GET['time']] : $locale['forum_0387'])." <span class='caret'></span></button>\n";
		echo "<ul class='dropdown-menu'>\n";
		foreach ($info['filter']['time'] as $filter_locale => $filter_link) {
			echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
		}
		echo "</ul>\n";
		echo "</div>\n";
		echo $locale['forum_0389'];
		echo "<div class='forum-filter'>\n";
		echo "<button class='btn btn-xs btn-default' data-toggle='dropdown' class='dropdown-toggle'>".(isset($_GET['type']) && in_array($_GET['type'], array_flip($selector2)) ? $selector2[$_GET['type']] : $locale['forum_0390'])." <span class='caret'></span></button>\n";
		echo "<ul class='dropdown-menu'>\n";
		foreach ($info['filter']['type'] as $filter_locale => $filter_link) {
			echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
		}
		echo "</ul>\n";
		echo "</div>\n";
		echo $locale['forum_0225'];
		echo "<div class='forum-filter'>\n";
		echo "<button class='btn btn-xs btn-default' data-toggle='dropdown' class='dropdown-toggle'>".(isset($_GET['sort']) && in_array($_GET['sort'], array_flip($selector3)) ? $selector3[$_GET['sort']] : $locale['forum_0391'])." <span class='caret'></span></button>\n";
		echo "<ul class='dropdown-menu'>\n";
		foreach ($info['filter']['sort'] as $filter_locale => $filter_link) {
			echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
		}
		echo "</ul>\n";
		echo "</div>\n";
		echo "<div class='forum-filter'>\n";
		echo "<button class='btn btn-xs btn-default' data-toggle='dropdown' class='dropdown-toggle'>".(isset($_GET['order']) && in_array($_GET['order'], array_flip($selector4)) ? $selector4[$_GET['order']] : $locale['forum_0385'])." <span class='caret'></span></button>\n";
		echo "<ul class='dropdown-menu'>\n";
		foreach ($info['filter']['order'] as $filter_locale => $filter_link) {
			echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
		}
		echo "</ul>\n";
		echo "</div>\n";
	}
}
/* Custom Modal New Topic */
if (!function_exists('forum_newtopic')) {
	function forum_newtopic() {
		global $settings, $locale;
		if (isset($_POST['select_forum'])) {
			$_POST['forum_sel'] = isset($_POST['forum_sel']) && isnum($_POST['forum_sel']) ? $_POST['forum_sel'] : 0;
			redirect(FORUM.'post.php?action=newthread&forum_id='.$_POST['forum_sel']);
		}
		echo openmodal('newtopic', $locale['forum_0057'], array('button_id' => 'newtopic', 'class' => 'modal-md'));
		$index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
		$result = dbquery("SELECT a.forum_id, a.forum_name, b.forum_name as forum_cat_name, a.forum_post
		 FROM ".DB_FORUMS." a
		 LEFT JOIN ".DB_FORUMS." b ON a.forum_cat=b.forum_id
		 WHERE ".groupaccess('a.forum_access')." ".(multilang_table("FO") ? "AND a.forum_language='".LANGUAGE."' AND" : "AND")."
		 (a.forum_type ='2' or a.forum_type='4') AND a.forum_post < ".USER_LEVEL_PUBLIC." AND a.forum_lock !='1' ORDER BY a.forum_cat ASC, a.forum_branch ASC, a.forum_name ASC");
		$options = array();
		if (dbrows($result) > 0) {
			while ($data = dbarray($result)) {
				$depth = get_depth($index, $data['forum_id']);
				if (checkgroup($data['forum_post'])) {
					$options[$data['forum_id']] = str_repeat("&#8212;", $depth).$data['forum_name']." ".($data['forum_cat_name'] ? "(".$data['forum_cat_name'].")" : '');
				}
			}
			echo openform('qp_form', 'post', ($settings['site_seo'] ? FUSION_ROOT : '').FORUM.'index.php', array('notice' => 0,
				'max_tokens' => 1));
			echo "<div class='well clearfix m-t-10'>\n";
			echo form_select('forum_sel', $locale['forum_0395'], '', array('options' => $options,
				'inline' => 1,
				'width' => '100%'));
			echo "<div class='display-inline-block col-xs-12 col-sm-offset-3'>\n";
			echo form_button('select_forum', $locale['forum_0396'], 'select_forum', array('class' => 'btn-primary btn-sm'));
			echo "</div>\n";
			echo "</div>\n";
			echo closeform();
		} else {
			echo "<div class='well text-center'>\n";
			echo $locale['forum_0328'];
			echo "</div>\n";
		}
		echo closemodal();
	}
}
/** Deprecated but has more advanced features . Keep for reference */
if (!function_exists('render_forum2')) {
	function render_forum2($info) {
		global $locale;
		echo render_breadcrumbs();
		$tab_title['title'][] = $locale['forum_0001'];
		$tab_title['id'][] = "thread";
		$tab_title['icon'][] = "fa fa-folder fa-fw";
		$tab_title['title'][] = $locale['forum_0012'];
		$tab_title['id'][] = "latest";
		$tab_title['icon'][] = "fa fa-list-alt fa-fw";
		$tab_title['title'][] = $locale['forum_0011'];
		$tab_title['id'][] = "mypost";
		$tab_title['icon'][] = "fa fa-user";
		$tab_title['title'][] = $locale['global_056'];
		$tab_title['id'][] = "tracked";
		$tab_title['icon'][] = "fa fa-inbox fa-fw";
		$tab_active = isset($_GET['section']) ? $_GET['section'] : 'thread';
		echo opentab($tab_title, $tab_active, 'forum_tabs', FORUM);
		echo opentabbody($tab_title['title'], $tab_active, $tab_active, 'viewforum');
		echo "<div class='m-t-20'>\n";
		if (isset($_GET['viewforum'])) {
			forum_viewforum($info);
		} else {
			if (isset($_GET['section']) && $_GET['section'] == 'mypost') {
				render_mypost($info);
			} elseif (isset($_GET['section']) && $_GET['section'] == 'latest') {
				render_laft($info);
			} elseif (isset($_GET['section']) && $_GET['section'] == 'tracked') {
				render_tracked($info);
			} elseif (!isset($_GET['section']) || isset($_GET['section']) && $_GET['section'] == 'thread') {
				render_forum_main($info);
			}
		}
		echo "</div>\n";
		echo closetabbody();
		echo closetab();
	}
}