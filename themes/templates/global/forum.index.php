<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /global/forum.index.php
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

/* New API in forum template
set_forumIcons(
	array(
		'main' => 'entypo folder',
		'thread' => 'entypo chat',
		'link'	=>	'entypo link',
		'question' => 'entypo graduation cap',
	)
);
*/

/* Old Forum index master template either drop it or make it 100% LAFT replacement*/
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
			}
			elseif (isset($_GET['section']) && $_GET['section'] == 'latest') {
					render_laft($info);
			}
			elseif (isset($_GET['section']) && $_GET['section'] == 'tracked') {
					render_tracked($info);
			}
			elseif (!isset($_GET['section']) || isset($_GET['section']) && $_GET['section'] == 'thread') {
					render_forum_main($info);
			}
		}
		echo "</div>\n";
		echo closetabbody();
		echo closetab();
	}
}

if (!function_exists('render_forum')) {
	function render_forum($info) {
		global $locale;
		echo render_breadcrumbs();
				if (isset($_GET['viewforum'])) {
					forum_viewforum($info);
			} else {
				render_forum_main($info);
		}
	}
}

/* Forum index master template */
if (!function_exists('render_forum_main')) {
	/**
	 * Main Forum Page - Recursive
	 * @param $info
	 */
	function render_forum_main($info, $id = 0) {
		global $locale;
		if (!empty($info['forums'][$id])) {
			$forums = $info['forums'][$id];
			$x = 1;
			foreach($forums as $forum_id => $data) {
				if ($data['forum_type'] == '1') {
					echo "<div class='panel panel-default'>\n";
					echo "<div class='panel-heading' ".(isset($data['child']) ? 'style="border-bottom:0;"' : '').">\n";
					echo "<a class='forum-subject' href='".FORUM."index.php?viewforum&amp;forum_id=".$data['forum_id']."&amp;parent_id=".$data['forum_cat']."&amp;forum_branch=".$data['forum_branch']."'>".$data['forum_name']."</a><br/>";
					echo $data['forum_description'] ? "<span class='text-smaller'>".$data['forum_description']."</span>\n<br/>" : '';
					echo "</div>\n";
					if (isset($info['forums'][$forum_id])) {
						echo "<div class='m-10'>\n";
						$i = 1;
						$sub_forums = $info['forums'][$forum_id];
						foreach($sub_forums as $sub_forum_id => $cdata) {
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
					render_forum_item_type($data, $x);
					$x++;
				}
			}
		} else {
			echo "<div class='well text-center'>".$locale['forum_0328']."</div>\n";
		}
	}
}

if (!function_exists('render_forum_item_type')) {
	/**
	 * Switch between different types of forum list containers
	 * @param $data
	 * @param $i
	 */
	function render_forum_item_type($data, $i) {
		global $locale, $settings;
		if ($i>0) {
			echo "<div id='forum_".$data['forum_id']."' class='forum-list list-group-item'>\n";
		} else {
			echo "<div id='forum_".$data['forum_id']."' class='panel panel-default'>\n";
			echo "<div class='panel-body'>\n";
		}
		echo "<div class='pull-left m-r-10 forum-thumbnail'>\n";
		if ($data['forum_image'] && file_exists(IMAGES."forum/".$data['forum_image'])) {
			echo thumbnail(IMAGES."forum/".$data['forum_image'], '40px');
		} else {
			echo $data['forum_icon_lg'];
		}
		echo "</div>\n";
		echo "<div class='overflow-hide'>\n";
		echo "<div class='row'>\n";
		switch($data['forum_type']) {
			case '3':
			echo "<div class='col-xs-12 col-sm-12'>\n";
				echo "<a class='display-inline-block forum-subject' href='".$data['forum_link']."'>".$data['forum_name']."</a>\n<span class='m-l-5'>".$data['forum_new_status']."</span><br/>";
				echo $data['forum_description'] ? "<div class='forum-description'>".$data['forum_description']."</div>\n" : '';
				echo ($data['forum_moderators'] ? "<span class='forum-moderators text-smaller'><strong>".$locale['forum_0007']."</strong>".$data['forum_moderators']."</span>\n" : "")."\n";
		if (isset($data['child'])) {
			echo "<div class='clearfix'>\n";
			echo "<div class='pull-left'>\n";
			echo "<i class='entypo level-down'></i>\n";
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			foreach($data['child'] as $cdata) {
				echo "<span class='nowrap'>\n";
				if (isset($cdata['forum_type'])) {
							echo $data['forum_icon'];
				}
				echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$cdata['forum_id']."&amp;parent_id=".$cdata['forum_cat']."&amp;forum_branch=".$cdata['forum_branch']."' class='forum-subforum display-inline-block m-r-10'>".$cdata['forum_name']."</a></span>";
			}
			echo "</div>\n";
			echo "</div>\n";
		}
		echo "</div>\n";
				break;
			default:
				echo "<div class='col-xs-12 col-sm-6'>\n";
				echo "<a class='display-inline-block text-bigger strong' href='".$data['forum_link']."'>".$data['forum_name']."</a>\n<span class='m-l-5'>".$data['forum_new_status']."</span><br/>";
				echo $data['forum_description'] ? "<div class='forum-description'>".$data['forum_description']."</div>\n" : '';
				echo ($data['forum_moderators'] ? "<span class='forum-moderators text-smaller'><strong>".$locale['forum_0007']."</strong>".$data['forum_moderators']."</span>\n" : "")."\n";
				if (isset($data['child'])) {
					echo "<div class='clearfix'>\n";
					echo "<div class='pull-left'>\n";
					echo "<i class='entypo level-down'></i>\n";
			echo "</div>\n";
					echo "<div class='overflow-hide'>\n";
					foreach($data['child'] as $cdata) {
						echo "<span class='nowrap'>\n";
						if (isset($cdata['forum_type'])) {
							echo $data['forum_icon'];
						}
						echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$cdata['forum_id']."&amp;parent_id=".$cdata['forum_cat']."&amp;forum_branch=".$cdata['forum_branch']."' class='forum-subforum display-inline-block m-r-10'>".$cdata['forum_name']."</a></span>";
					}
			        echo "</div>\n";
					echo "</div>\n";
				}
				echo "</div>\n";
				echo "<div class='col-xs-12 col-sm-2 text-right'>\n";
				echo "<div class='text-lighter'>".$data['forum_postcount']."</div>\n";
				echo "<div class='text-lighter'>".$data['forum_threadcount']."</div>\n";
				echo "</div><div class='col-xs-12 col-sm-4'>\n";
				if ($data['forum_lastpostid'] == 0) {
				echo $locale['forum_0005'];
			} else {
				echo "<div class='clearfix'>\n";
					if ($settings['forum_last_post_avatar'] == 1) {	echo "<div class='pull-left lastpost-avatar m-r-10 m-t-5'>".$data['forum_last_post_avatar']."</div>"; }
				echo "<div class='overflow-hide'>\n";
					echo "<a class='lastpost-title strong' href='".$data['forum_last_post_thread_link']."' title='".$data['thread_subject']."'>".trimlink($data['thread_subject'], 25)."</a> ";
					echo "<a class='lastpost-goto' href='".$data['forum_last_post_link']."' title='".$data['thread_subject']."'><i class='fa fa-external-link-square'></i></a><br/>\n";
					echo "<span class='forum_profile_link'>".$data['forum_last_post_profile_link']."</span><br />\n";
					echo "<span class='lastpost-date text-smaller'>".$data['forum_last_post_date']."</span> \n";
				echo "</div>\n</div>\n";
			}
			echo "</div>\n";
		}
		echo "</div>\n"; // end row
		echo "</div>\n"; // end overflow-hide
		if ($i > 0)  {
			echo "</div>\n";
		} else {
			echo "</div>\n</div>\n";
		}
	}
}

/* Forum View - ex viewforum.php */
if (!function_exists('forum_viewforum')) {
	function forum_viewforum($info) {
		global $locale;
		$data = $info['item'][$_GET['forum_id']];
		echo "<h3 class='m-t-20 m-b-5'>".$data['forum_name']."</h3>\n";
		echo $data['forum_description'];

		// subforums
		if (isset($info['item'][$_GET['forum_id']]['child'])) {
			echo "<div class='panel panel-default m-t-10'>\n";
			echo "<div class='panel-heading strong'>".$locale['forum_0351']."</div>\n";
			$i = 1;
			echo "<div class='panel-body'>\n";
			foreach ($info['item'][$_GET['forum_id']]['child'] as $subforum_id => $subforum_data) {
				render_forum_item_type($subforum_data, $i);
				$i++;
			}
			echo "</div>\n";
			echo "</div>\n";
		}

		if (iMEMBER && $info['permissions']['can_post']) {
			echo "<div class='clearfix m-b-20 m-t-20'>\n
					<a title='".$locale['forum_0264']."' class='btn btn-primary btn-sm text-white pull-right' href='".$info['new_thread_link']."'>".$locale['forum_0264']."</a>
				</div>";
		}

		echo "<!--pre_forum-->\n";
		echo "<div class='panel panel-default m-t-15'>\n";
		if (!empty($data['forum_threadcounter'])) {
			echo "<div class='panel-heading strong p-b-15'>".format_word(isset($data['forum_threadcounter']) ? $data['forum_threadcounter'] : 0, $locale['fmt_thread'])."</div>\n";
		}
		echo $data['forum_rules'] ? "<div class='panel-heading p-5'><div class='alert alert-info m-b-0'><span class='strong'><i class='fa fa-exclamation fa-fw'></i>".$locale['forum_0350']."</span> ".$data['forum_rules']."</div></div>" : '';
		if ($data['forum_type'] > 1) {
			echo "<div class='panel-heading'>\n";
			forum_filter($info);
			echo "</div>\n";
		}
		echo "<div class='panel-body'>\n";
		echo $info['forum_moderators'];
		if (!empty($info['threads'])) {
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
		} else {
			echo "<div class='text-center'>".$locale['forum_0269']."</div>\n";
		}
		echo "</div>\n";
		echo "</div>\n";
	}
}

/* display threads -- need to simplify */
if (!function_exists('render_thread_item')) {
	function render_thread_item($data) {
		global $locale, $info, $userdata;

		echo "<div id='thread_".$data['thread_id']."' class='list-group-item'>\n";

		echo "<div class='row m-0'>\n";
		echo "<div class='col-xs-12 col-sm-9 col-md-6 p-l-0'>\n";
			echo "<div class='pull-left m-r-10 m-t-5' style='width:30px; font-size:150%;'>\n".$data['thread_status']['icon']."</div>\n";
				// unset icon, since it's used
				unset($data['thread_status']['icon']);
				$thead_icons = '';
				foreach($data['thread_status'] as $icon) {
					$thead_icons .= $icon;
				}
			echo "<div class='overflow-hide'>\n";
			echo "<a class='text-bigger' href='".$data['thread_link']."'>".$data['thread_subject']."</a> <span class='m-l-10 m-r-10 text-lighter'>".$thead_icons."</span>  ".$data['thread_pages']."";
			echo "<div class='m-b-10'>".$data['thread_starter']."</div>\n";
			echo isset($data['track_button']) ? "<div class='forum_track'><a onclick=\"return confirm('".$locale['global_060']."');\" href='".$data['track_button']['link']."'>".$data['track_button']['name']."</a>\n</div>\n" : '';
			echo "</div>\n";
		echo "</div>\n"; // end grid
		echo "<div class='hidden-xs col-sm-3 col-md-3 p-l-0 p-r-0 text-center'>\n";
		echo "<div class='display-inline-block forum-stats well p-5 m-r-5 m-b-0'>\n";
		echo "<h4 class='text-bigger strong text-dark m-0'>".number_format($data['thread_views'])."</h4>\n";
		echo "<span>".format_word($data['thread_views'], $locale['fmt_views'], 0)."</span>";
		echo "</div>\n";
		echo "<div class='display-inline-block forum-stats well p-5 m-r-5 m-b-0'>\n";
		echo "<h4 class='text-bigger strong text-dark m-0'>".number_format($data['thread_postcount'])."</h4>\n";
		echo "<span>".format_word($data['thread_postcount'], $locale['fmt_post'], 0)."</span>";
		echo "</div>\n";

		if ($data['forum_type'] == '4') {
			echo "<div class='display-inline-block forum-stats well p-5 m-r-5 m-b-0'>\n";
			echo "<h4 class='text-bigger strong text-dark m-0'>".number_format($data['vote_count'])."</h4>\n";
			echo "<span>".format_word($data['vote_count'], $locale['fmt_vote'], 0)."</span>";
			echo "</div>\n";
		}
		echo "</div>\n"; // end grid
		echo "<div class='hidden-xs hidden-sm col-md-3 p-l-0'>".$data['thread_lastuser']."</div>\n";
		echo "</div>\n";
		echo "</div>\n";
	}
}

/* Viewthread.php */
if (!function_exists('render_thread')) {
	function render_thread($info) {
		global $locale;
		if (isset($info['post_items']) && !empty($info['post_items'])) {
			$pdata = $info['post_items'];
			$buttons = $info['buttons'];
			$data = $info['thread'];
			
			echo render_breadcrumbs();
			echo "<div class='clearfix m-b-20'>\n";
				echo "<div class='thread-buttons btn-group pull-right m-t-20'>\n";
					echo isset($buttons['notify']) ? "<a class='btn btn-default btn-sm' href='".$buttons['notify']['link']."'><i class='entypo twitter'></i> ".$buttons['notify']['name']."</a>\n" : '';
					echo "<a class='btn btn-default btn-sm m-l-10' href='".$buttons['print']['link']."'><i class='entypo print'></i> ".$buttons['print']['name']."</a>\n";
			echo "</div>\n";
				echo "<div class='overflow-hide'>\n";
					echo "<h2 class='m-t-0 m-b-10 thread-header'>".$data['thread_subject']."</h2>\n";
					echo $locale['forum_0006']." <a class='forum_cat label label-info' href='".$data['forum_link']."'>".$data['forum_name']."</a>\n<br/>";
					echo "<div class='text-lighter m-t-10'><i class='fa fa-calendar'></i> ".$locale['forum_0363'].timer($data['thread_lastpost'])." ".showdate('forumdate', $data['thread_lastpost'])."</div>\n";
			echo "</div>\n";
			echo "</div>\n";

			// Thread buttons, top
		if (iMEMBER && $info['permissions']['can_post']) {
			echo "<div class='pull-right'>\n";
			echo "<a class='btn btn-primary btn-sm m-r-5 ".(empty($buttons['newthread']) ? 'disabled' : '')." ' href='".$buttons['newthread']['link']."'>".$buttons['newthread']['name']."</a>\n";
			echo "<a class='btn btn-primary btn-sm ".(empty($buttons['reply']) ? 'disabled' : '')."' href='".$buttons['reply']['link']."'>".$buttons['reply']['name']."</a>\n";
			echo "</div>\n";
		}
		
			// filter UI vars
			$icon = array(
				'fa fa-sort-alpha-asc fa-fw',
				'fa fa-sort-alpha-desc fa-fw',
				'fa fa-trophy fa-fw'
			);
			if (isset($info['post-filters'])) {
				foreach($info['post-filters'] as $i => $filters) {
					$p_title['title'][] = $filters['locale'];
					$p_title['id'][] = $info['allowed_post_filters'][$i];
					$p_title['icon'][] = $icon[$i];
				}
			}
			$tab_active = isset($_GET['section']) && $_GET['section'] ? $_GET['section'] : 'oldest';

			echo opentab($p_title, $tab_active, 'post_tabs', 1);
			echo opentabbody('', $tab_active, $tab_active, 1);
			echo "<div id='forum_top' class='text-left m-b-10 m-t-10 text-lighter clearfix'>\n".$info['page_nav']."</div>\n";
			if (isset($info['poll'])) {
				echo $info['poll_form'];
			}
			echo "<!--pre_forum_thread-->\n";
			echo $info['open_post_form'];
			$i = 0;
			foreach($pdata as $post_id => $post_data) {
				$i++;
				echo "<!--forum_thread_prepost_".$post_data['post_id']."-->\n";
				render_post_item($post_data, $i);
			}
			echo "<div id='forum_bottom' class='text-left m-b-10 text-lighter clearfix'>\n".$info['page_nav']."</div>\n";
			// Moderation Panel
			if (iMOD) echo $info['mod_form'];

			// Thread buttons, bottom
			if (iMEMBER && $info['permissions']['can_post']) {
				echo "<div class='text-right m-t-20'>\n";
				echo "<a class='btn btn-primary btn-sm m-r-5 ".(empty($buttons['newthread']) ? 'disabled' : '')." ' href='".$buttons['newthread']['link']."'>".$buttons['newthread']['name']."</a>\n";
				echo "<a class='btn btn-primary btn-sm ".(empty($buttons['reply']) ? 'disabled' : '')."' href='".$buttons['reply']['link']."'>".$buttons['reply']['name']."</a>\n";
				echo "</div>\n";
			}
			echo $info['close_post_form'];
			echo $info['quick_reply_form'];
			echo "</div>\n";
			echo "</div>\n";
			echo "</div>\n";
		} else {
			echo "<div class='text-center well'>".$locale['forum_0270']."</div>\n";
		}
	}
}

/* Post Item */
if (!function_exists('render_post_item')) {
	function render_post_item($data) {
		global $locale;
		echo "<!--forum_thread_prepost_".$data['post_id']."-->\n";
		echo "<div class='panel panel-default'>";
		echo "<div class='panel-heading'>";
		echo "<div class='pull-right'>(".$data['user_ip'].") ".$data['post_marker']." ".$data['post_checkbox']."</div>\n";
		echo "<a>".$data['user_profile_link']."</a>";
		echo "</div>";
		echo "<div class='panel-body'>";
			echo "<div class='row'>";
			echo "<div class='col-xs-12 col-sm-2 text-center'>\n";
			echo "<!--forum_thread_user_name-->\n";
			echo "<div class='clearfix m-t-10'>\n";
					echo "<span class='position-absolute' style='display-block p-5 height:5px; width:10px; margin-left:20px; margin-top:40px; border-radius:50%; color:#5CB85C'><i class='fa ".($data['user_online'] ? "fa-circle" : "fa-circle-thin")."'></i></span>";
					echo $data['user_avatar'];
			echo "</div>\n";
				echo "<div class='forum_rank m-t-10'>\n".$data['rank_img']."</div>\n";
				echo "<div class='text-lighter'>".$data['user_post_count']."</div>\n";
			echo "</div>";

			echo "<div class='col-xs-12 col-sm-10'>";
				if ($data['post_votebox']) {
			echo "<div class='pull-right'>\n";
					echo $data['post_votebox'];
			echo "</div>\n";
		}
			echo "<div class='overflow-hide'>\n";
			echo "<div class='text-lighter m-b-10'>".$data['post_date']."</div>";
			echo $data['post_message'];
			echo "<!--sub_forum_post_message-->";

			echo "<hr /><div class='forum_sig'>".$data['user_sig']."</div>";
			echo "<br /> <div class='edit_reason'>".$data['post_edit_reason']."</div>";
			echo $data['post_attachments'];

		echo "</div>\n";
		echo "</div>";
		echo "</div>\n";

		echo "</div><div class='panel-footer text-right'>";
			echo "<!--sub_forum_post-->";
			echo $data['post_links'];
		echo "</div>";
		echo "</div>";
	}
}

/* My Post Section */
if (!function_exists('render_mypost')) {
	function render_mypost($info) {
		global $locale;
		$type_icon = array('1'=>'entypo folder', '2'=>'entypo chat', '3'=>'entypo link', '4'=>'entypo graduation-cap');
		if (!empty($info['item'])) {
			// sort by date.
			$last_date = ''; $i = 0;
			foreach($info['item'] as $data) {
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
				echo "<a class='post_title strong' href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['post_id']."#post_".$data['post_id']."' title='".$data['thread_subject']."'>".trimlink($data['thread_subject'], 40)."</a>\n";
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
			foreach($info['item'] as $data) {
				// do a thread.
				render_thread_item($data);
				$i++;
			}
		} else {
			echo "<div class='well text-center'>".$locale['global_023']."</div>\n";
		}
		// filter --- this need to be translated to links.
		$opts = array('0' => 'All Results', '1' => '1 Day', '7' => '7 Days', '14' => '2 Weeks', '30' => '1 Month',
			'90' => '3 Months', '180' => '6 Months', '365' => '1 Year');
		echo "<hr/>\n";
		echo openform('filter_form', 'post', FORUM."index.php?section=latest", array('downtime' => 1));
		echo form_select('filter', $locale['forum_0009'], $opts, isset($_POST['filter']) && $_POST['filter'] ? $_POST['filter'] : 0, array('width' => '300px', 'class'=>'pull-left m-r-10'));
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
			foreach($info['item'] as $data) {
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
		$selector = array(
			'today' => $locale['forum_p000'],
			'2days' => $locale['forum_p002'],
			'1week' => $locale['forum_p007'],
			'2week' => $locale['forum_p014'],
			'1month' => $locale['forum_p030'],
			'2month' => $locale['forum_p060'],
			'3month' => $locale['forum_p090'],
			'6month' => $locale['forum_p180'],
			'1year' => $locale['forum_p365']
		);
		$selector2 = array(
			'all' => $locale['forum_0374'],
			'discussions' => $locale['forum_0375'],
			'attachments' => $locale['forum_0376'],
			'poll' => $locale['forum_0377'],
			'solved' => $locale['forum_0378'],
			'unsolved' => $locale['forum_0379'],
		);
		$selector3 = array(
			'author' => $locale['forum_0380'],
			'time' => $locale['forum_0381'],
			'subject' => $locale['forum_0382'],
			'reply' => $locale['forum_0383'],
			'view' => $locale['forum_0384'],
		);
		$selector4 = array(
			'ascending' => $locale['forum_0385'],
			'descending' => $locale['forum_0386'],
		);
		echo $locale['forum_0388'];
		echo "<span class='display-inline-block m-l-10 m-r-10' style='position:relative; vertical-align:middle;'>\n";
		echo "<button class='btn btn-xs btn-default' data-toggle='dropdown' class='dropdown-toggle'>".(isset($_GET['time']) && in_array($_GET['time'], array_flip($selector)) ? $selector[$_GET['time']] : $locale['forum_0387'])." <span class='caret'></span></button>\n";
		echo "<ul class='dropdown-menu'>\n";
		foreach($info['filter']['time'] as $filter_locale => $filter_link) {
			echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
		}
		echo "</ul>\n";
		echo "</span>\n";
		echo $locale['forum_0389'];

		echo "<span class='display-inline-block m-l-10 m-r-10' style='position:relative; vertical-align:middle;'>\n";
		echo "<button class='btn btn-xs btn-default' data-toggle='dropdown' class='dropdown-toggle'>".(isset($_GET['type']) && in_array($_GET['type'], array_flip($selector2)) ? $selector2[$_GET['type']] : $locale['forum_0390'])." <span class='caret'></span></button>\n";
		echo "<ul class='dropdown-menu'>\n";
		foreach($info['filter']['type'] as $filter_locale => $filter_link) {
			echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
		}
		echo "</ul>\n";
		echo "</span>\n";
		echo $locale['forum_0225'];

		echo "<span class='display-inline-block m-l-10 m-r-10' style='position:relative; vertical-align:middle;'>\n";
		echo "<button class='btn btn-xs btn-default' data-toggle='dropdown' class='dropdown-toggle'>".(isset($_GET['sort']) && in_array($_GET['sort'], array_flip($selector3)) ? $selector3[$_GET['sort']] : $locale['forum_0391'])." <span class='caret'></span></button>\n";
		echo "<ul class='dropdown-menu'>\n";
		foreach($info['filter']['sort'] as $filter_locale => $filter_link) {
			echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
		}
		echo "</ul>\n";
		echo "</span>\n";

		echo "<span class='display-inline-block' style='position:relative; vertical-align:middle;'>\n";
		echo "<button class='btn btn-xs btn-default' data-toggle='dropdown' class='dropdown-toggle'>".(isset($_GET['order']) && in_array($_GET['order'], array_flip($selector4)) ? $selector4[$_GET['order']] : $locale['forum_0385'])." <span class='caret'></span></button>\n";
		echo "<ul class='dropdown-menu'>\n";
		foreach($info['filter']['order'] as $filter_locale => $filter_link) {
			echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
		}
		echo "</ul>\n";
		echo "</span>\n";
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

		echo openmodal('newtopic', $locale['forum_0057'], array('button_id'=>'newtopic', 'class'=>'modal-md'));
		$index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
		$result = dbquery("SELECT a.forum_id, a.forum_name, b.forum_name as forum_cat_name, a.forum_post
		 FROM ".DB_FORUMS." a
		 LEFT JOIN ".DB_FORUMS." b ON a.forum_cat=b.forum_id
		 WHERE ".groupaccess('a.forum_access')." ".(multilang_table("FO") ? "AND a.forum_language='".LANGUAGE."' AND" : "AND")."
		 (a.forum_type ='2' or a.forum_type='4') AND a.forum_post < ".USER_LEVEL_PUBLIC." AND a.forum_lock !='1' ORDER BY a.forum_cat ASC, a.forum_branch ASC, a.forum_name ASC");
		$options = array();
		if (dbrows($result)>0) {
			while ($data = dbarray($result)) {
				$depth = get_depth($index, $data['forum_id']);
				if (checkgroup($data['forum_post'])) {
					$options[$data['forum_id']] = str_repeat("&#8212;", $depth).$data['forum_name']." ".($data['forum_cat_name'] ? "(".$data['forum_cat_name'].")" : '');
				}
			}
			echo openform('qp_form', 'post', ($settings['site_seo'] ? FUSION_ROOT : '').FORUM.'index.php', array('notice'=>0, 'max_tokens' => 1));
			echo "<div class='well clearfix m-t-10'>\n";
			echo form_select('forum_sel', $locale['forum_0395'],  $options, '', array('inline'=>1, 'width'=>'100%'));
			echo "<div class='display-inline-block col-xs-12 col-sm-offset-3'>\n";
			echo form_button('select_forum', $locale['forum_0396'], 'select_forum', array('class'=>'btn-primary btn-sm'));
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
?>