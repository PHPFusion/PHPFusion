<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_thread.php
| Author: Hien (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
define("SUB_START_PAGE", "aaaa");
/**
 * Thread Page HTML
 */
if (!function_exists('render_thread')) {
	add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."forum/templates/css/forum.css'>");
	function render_thread($info) {
		global $locale;
		$buttons = !empty($info['buttons']) ? $info['buttons'] : array();
		$data = !empty($info['thread']) ? $info['thread'] : array();
		$pdata = !empty($info['post_items']) ? $info['post_items'] : array();
		$icon = array('','','fa fa-trophy fa-fw');
		$p_title = array();
		echo render_breadcrumbs();
		echo "<div class='clearfix'>\n";
		if (isset($info['page_nav'])) echo "<div id='forum_top' class='pull-right m-t-10 text-lighter clearfix'>\n".$info['page_nav']."</div>\n";
		echo "<h2 class='m-t-0 thread-header pull-left m-r-20'>
		".($data['thread_sticky'] == TRUE ? "<i title='".$locale['forum_0103']."' class='".get_forumIcons("sticky")."'></i>" : "")."
		".($data['thread_locked'] == TRUE ? "<i title='".$locale['forum_0102']."' class='".get_forumIcons("lock")."'></i>" : "")."
		".$data['thread_subject']."</h2>\n";
		echo "</div>\n";
		echo "<div class='last-updated'>".$locale['forum_0363'].timer($data['thread_lastpost'])." <i class='fa fa-calendar fa-fw'></i></div>\n";

		if (!empty($info['poll_form'])) echo "<div class='well'>".$info['poll_form']."</div>\n";

		if ($info['permissions']['can_post']) {
			echo "<div class='pull-right'>\n";
			if ($info['permissions']['can_create_poll']) {
				echo "<a class='btn btn-success btn-sm ".(!empty($info['thread']['thread_poll']) ? 'disabled' : '')."' title='".$buttons['poll']['title']."' href='".$buttons['poll']['link']."'>".$buttons['poll']['title']." <i class='fa fa-pie-chart'></i> </a>\n";
			}
			echo "<a class='btn btn-primary btn-sm ".(empty($buttons['newthread']) ? 'disabled' : '')." ' href='".$buttons['newthread']['link']."'>".$buttons['newthread']['title']."</a>\n";
			echo "</div>\n";
		}

		echo "<div class='top-action-bar'>\n";
		// now change the whole thing to dropdown selector
		$selector['oldest'] = $locale['forum_0180'];
		$selector['latest'] = $locale['forum_0181'];
		echo "<span class='display-inline-block m-r-10 btn-group' style='position:relative; vertical-align:middle;'>\n";
		echo "<a class='btn btn-sm btn-default' data-toggle='dropdown' class='dropdown-toggle'><strong>".$locale['forum_0183']."</strong>
		".(isset($_GET['section']) && in_array($_GET['section'], array_flip($selector)) ? $selector[$_GET['section']] : $locale['forum_0180'])." <span class='caret'></span>
		</a>\n";

		echo "<ul class='dropdown-menu'>\n";
		foreach($info['post-filters'] as $i => $filters) {
			echo "<li><a class='text-smaller' href='".$filters['value']."'>".$filters['locale']."</a></li>\n";
		}

		echo "</ul>\n";

		echo !empty($buttons['notify']) ? "<a class='btn btn-default btn-sm' title='".$buttons['notify']['title']."' href='".$buttons['notify']['link']."'>".$buttons['notify']['title']." <i class='fa fa-eye'></i></a>\n" : '';
		echo "<a class='btn btn-default btn-sm' title='".$buttons['print']['title']."' href='".$buttons['print']['link']."'>".$buttons['print']['title']." <i class='fa fa-print'></i> </a>\n";
		echo "</span>\n";
		echo "</div>\n";

		echo "<!--pre_forum_thread-->\n";
		echo $info['open_post_form'];
		$i = 0;
		if (!empty($pdata)) {
			foreach($pdata as $post_id => $post_data) {
				$i++;
				echo "<!--forum_thread_prepost_".$post_data['post_id']."-->\n";
				render_post_item($post_data, $i);
				if ($post_id == $info['post_firstpost'] && $info['permissions']['can_post']) {
					echo "<div class='text-right'>\n";
					echo "<div class='display-inline-block'>".$info['thread_posts']."</div>\n";
					echo "<a class='m-l-20 btn btn-success btn-md vatop ".(empty($buttons['reply']) ? 'disabled' : '')."' href='".$buttons['reply']['link']."'>".$buttons['reply']['title']."</a>\n";
					echo "</div>\n";
				}
			}
		}

		if (isset($info['page_nav'])) echo "<div id='forum_bottom' class='text-left m-b-10 text-lighter clearfix'>\n".$info['page_nav']."</div>\n";

		if (iMOD) echo $info['mod_form'];
		// Thread buttons, bottom
		if (iMEMBER && $info['permissions']['can_post']) {
			echo "<div class='text-right m-t-20'>\n";
			echo "<a class='btn btn-primary btn-sm m-r-5 ".(empty($buttons['newthread']) ? 'disabled' : '')." ' href='".$buttons['newthread']['link']."'>".$buttons['newthread']['title']."</a>\n";
			echo "<a class='btn btn-primary btn-sm ".(empty($buttons['reply']) ? 'disabled' : '')."' href='".$buttons['reply']['link']."'>".$buttons['reply']['title']."</a>\n";
			echo "</div>\n";
		}
		echo $info['close_post_form'];

		echo $info['quick_reply_form'];

		echo "
		<div class='list-group-item m-t-20'>
			<span>".sprintf($locale['forum_perm_access'], $info['permissions']['can_access'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
			<span>".sprintf($locale['forum_perm_post'], $info['permissions']['can_post'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
			<span>".sprintf($locale['forum_perm_reply'], $info['permissions']['can_reply'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
			";
		if ($data['thread_poll'] == TRUE) {
		 	echo "	<span>".sprintf($locale['forum_perm_edit_poll'], $info['permissions']['can_edit_poll'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
			<span>".sprintf($locale['forum_perm_vote_poll'], $info['permissions']['can_vote_poll'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>";
		} else {
			echo "	<span>".sprintf($locale['forum_perm_create_poll'], $info['permissions']['can_create_poll'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>";
		}
		echo "
			<span>".sprintf($locale['forum_perm_upload'], $info['permissions']['can_upload_attach'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
			<span>".sprintf($locale['forum_perm_download'], $info['permissions']['can_download_attach'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
			";
		if ($data['forum_type'] == "4") {
			echo "<span>".sprintf($locale['forum_perm_rate'], $info['permissions']['can_rate'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>";
		}
		echo "
		</div>\n
		";

		if ($info['forum_moderators']) {
			echo "<div class='list-group-item'>".$locale['forum_0185']." ".$info['forum_moderators']."</div>\n";
		}

		if (!empty($info['thread_users'])) {
			echo "<div class='list-group-item'>\n";
			echo "<span class='m-r-10'>".$locale['forum_0581']."</span>";
			$i = 1; $max = count($info['thread_users']);
			foreach($info['thread_users'] as $user_id => $users) {
				echo $users;
				echo $max == $i ? " " : ", ";
				$i++;
			}
			echo "</div>\n";
		}

	}
}

/* Post Item */
if (!function_exists('render_post_item')) {
	function render_post_item($data) {
		global $forum_settings,$aidlink,$userdata,$locale;
		echo "
		<div id='".$data['marker']['id']."' class='clearfix post_items'>\n
		<div class='forum_avatar text-center'>\n
		".$data['user_avatar_image']."
		".($forum_settings['forum_rank_style'] == '1' ? "<div class='text-center m-t-10'>".$data['user_rank']."</div>\n": '')."
		</div>\n
		<div class='pull-right m-l-10 col-sm-4 col-md-3 m-l-10'>
		<div class='pull-right m-l-10'>".$data['post_checkbox']."</div>\n
		<div class='btn-group dropdown'>\n
		".(isset($data['post_quote']) && !empty($data['post_quote']) ? "<a class='btn btn-default btn-xs quote-link' href='".$data['post_quote']['link']."' title='".$data['post_quote']['title']."'>".$data['post_quote']['title']."</a>\n" : '')."
		".(isset($data['post_reply']) && !empty($data['post_reply']) ? "<a class='btn btn-default btn-xs reply-link' href='".$data['post_reply']['link']."' title='".$data['post_reply']['title']."'>".$data['post_reply']['title']."</a>\n" : '')."
		".(isset($data['post_edit']) && !empty($data['post_edit']) ? "<a class='btn btn-default btn-xs edit-link' href='".$data['post_edit']['link']."' title='".$data['post_edit']['title']."'>".$data['post_edit']['title']."</a>\n" : "")."
		<a class='dropdown btn btn-xs btn-default' data-toggle='dropdown'><i class='fa fa-fw fa-ellipsis-v'></i></a>\n
		<ul class='dropdown-menu'>\n
		<!--forum_thread_user_fields_".$data['post_id']."-->\n
		".($data['user_ip'] ? "<li class='hidden-sm hidden-md hidden-lg'><i class='fa fa-user fa-fw'></i> IP : ".$data['user_ip']."</li>" : "" )."
		<li class='hidden-sm hidden-md hidden-lg'><i class='fa fa-commenting-o fa-fw'></i> ".$data['user_post_count']."</li>
		".($data['user_message']['link'] !=="" ? "<li><a href='".$data['user_message']['link']."' title='".$data['user_message']['title']."'>".$data['user_message']['title']."</a></li>\n" : "");
		if ($data['user_web']['link'] !=="") {
		$data['user_web']['link'] = !preg_match("@^http(s)?\:\/\/@i", $data['user_web']['link']) ? "http://".$data['user_web']['link'] : $data['user_web']['link'];
		echo "<li>".(fusion_get_settings('index_url_userweb') ? "" : "<!--noindex-->")."<a href='".$data['user_web']['link']."' title='".$data['user_web']['title']."' ".(fusion_get_settings('index_url_userweb') ? "" : "rel='nofollow'").">".$data['user_web']['title']."</a>".(fusion_get_settings('index_url_userweb') ? "" : "<!--/noindex-->")."</li>\n";
		}
		echo "<li><a href='".$data['print']['link']."' title='".$data['print']['title']."'>".$data['print']['title']."</a></li>\n
		<li class='divider'></li>\n
		".(isset($data['post_quote']) && !empty($data['post_quote']) ? "<li><a href='".$data['post_quote']['link']."' title='".$data['post_quote']['title']."'>".$data['post_quote']['title']."</a></li>\n" : '')."
		".(isset($data['post_edit']) && !empty($data['post_edit']) ? "<li><a href='".$data['post_edit']['link']."' title='".$data['post_edit']['title']."'>".$locale['forum_0507']."</a></li>\n" : '')."
		<li class='divider'></li>\n";
		if (iADMIN && checkrights("M") && $data['user_id'] != $userdata['user_id'] && $data['user_level'] < 103) {
			echo "<p class='text-center'><a href='".ADMIN."members.php".$aidlink."&amp;step=edit&amp;user_id=".$data['user_id']."'>".$locale['edit']."</a> &middot; ";
			echo "<a href='".ADMIN."members.php".$aidlink."&amp;user_id=".$data['user_id']."&amp;action=1'>".$locale['ban']."</a> &middot; ";
			echo "<a href='".ADMIN."members.php".$aidlink."&amp;step=delete&amp;status=0&amp;user_id=".$data['user_id']."'>".$locale['delete']."</a></p>\n";
		}
		echo "</ul>\n</div>\n";
		echo "<ul class='overflow-hide hidden-xs m-t-15 text-smaller' style='border-left:1px solid #ccc; padding-left:10px;'>
		<!--forum_thread_user_fields_".$data['post_id']."-->\n
		".($data['user_ip'] ? "<li>IP : ".$data['user_ip']."</li>" : "" )."
		<li>".$data['user_post_count']."</li>
		</ul>
		</div>
		<div class='overflow-hide'>\n
		<!--forum_thread_user_name-->\n
		<div class='m-b-10'>\n
		<span style='height:5px; width:10px; border-radius:50%; color:#5CB85C'><i class='fa ".($data['user_online'] ? "fa-circle" : "fa-circle-thin")."'></i></span>\n
		<span class='text-smaller'><span class='forum_poster'>".$data['user_profile_link']."</span>
		".($forum_settings['forum_rank_style'] == '0' ? "<span class='forum_rank'>\n".$data['user_rank']."</span>\n" : '')."
		".$data['post_shortdate']." </span>\n
		</div>\n
		<!--forum_thread_prepost_".$data['post_id']."-->\n
		".($data['post_votebox'] ? "<div class='pull-left m-r-15'>".$data['post_votebox']."</div>" : '')."
		<div class='display-block overflow-hide'>\n
		".$data['post_message']."
		".($data['user_sig'] ? "<div class='forum_sig text-smaller'>".$data['user_sig']."</div>\n" : "")."
		".($data['post_attachments'] ? "<div class='forum_attachments'>".$data['post_attachments']."</div>" : "")."
		</div>
		<!--sub_forum_post_message-->\n
		<div class='text-right'>\n
		<div class='edit_reason m-b-10'>".$data['post_edit_reason']."</div>\n
		</div>\n
		</div>\n
		</div>\n
		";
		/*
		 * <div class='text-right m-t-10'>\n
		<a class='btn btn-primary btn-xs' href='".$data['post_quote']['link']."' title='".$data['post_quote']['title']."'>".$data['post_quote']['title']."</a>\n
		<a class='btn btn-default btn-xs' href='".$data['post_edit']['link']."' title='".$data['post_edit']['title']."'>".$data['post_edit']['title']."</a>\n
		</div>\n
		 */
	}
}
