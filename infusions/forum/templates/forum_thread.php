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

/**
 * Thread Page HTML
 */
if (!function_exists('render_thread')) {
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
		echo "<h2 class='m-t-0 thread-header pull-left m-r-20'>".$data['thread_subject']."</h2>\n";
		echo "</div>\n";

		echo "<div class='last-updated'>".$locale['forum_0363'].timer($data['thread_lastpost'])." <i class='fa fa-calendar fa-fw'></i></div>\n";

		if (isset($info['poll'])) echo "<div class='well'>".$info['poll_form']."</div>\n";

		if ($info['permissions']['can_post']) {
			echo "<div class='pull-right'>\n";
			if ($info['permissions']['can_poll']) {
				echo "<a class='btn btn-success btn-sm ".(!empty($info['thread']['thread_poll']) ? 'disabled' : '')."' title='".$buttons['poll']['name']."' href='".$buttons['poll']['link']."'>".$buttons['poll']['name']." <i class='fa fa-pie-chart'></i> </a>\n";
			}
			echo "<a class='btn btn-primary btn-sm ".(empty($buttons['newthread']) ? 'disabled' : '')." ' href='".$buttons['newthread']['link']."'>".$buttons['newthread']['name']."</a>\n";
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
		echo !empty($buttons['notify']) ? "<a class='btn btn-default btn-sm' title='".$buttons['notify']['name']."' href='".$buttons['notify']['link']."'>".$buttons['notify']['name']." <i class='fa fa-eye'></i></a>\n" : '';
		echo "<a class='btn btn-default btn-sm' title='".$buttons['print']['name']."' href='".$buttons['print']['link']."'>".$buttons['print']['name']." <i class='fa fa-print'></i> </a>\n";
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
					echo "<a class='m-l-20 btn btn-success btn-md vatop ".(empty($buttons['reply']) ? 'disabled' : '')."' href='".$buttons['reply']['link']."'>".$buttons['reply']['name']."</a>\n";
					echo "</div>\n";
				}
			}
		}
		//if (isset($info['page_nav'])) echo "<div id='forum_bottom' class='text-left m-b-10 text-lighter clearfix'>\n".$info['page_nav']."</div>\n";
		if (iMOD) echo $info['mod_form'];
		// Thread buttons, bottom
		if (iMEMBER && $info['permissions']['can_post']) {
			echo "<div class='text-right m-t-20'>\n";
			echo "<a class='btn btn-primary btn-sm m-r-5 ".(empty($buttons['newthread']) ? 'disabled' : '')." ' href='".$buttons['newthread']['link']."'>".$buttons['newthread']['name']."</a>\n";
			echo "<a class='btn btn-primary btn-sm ".(empty($buttons['reply']) ? 'disabled' : '')."' href='".$buttons['reply']['link']."'>".$buttons['reply']['name']."</a>\n";
			echo "</div>\n";
		}
		echo $info['close_post_form'];

		// Add styling here because it's more flexible for custom style whether horizontal or vertical
		if (!empty($info['thread_users'])) {
			echo "<div class='list-group-item m-t-20'>\n";
			echo "<small class='strong'><i class='fa fa-users fa-fw'></i><span class='m-r-10'>".$locale['forum_0581']."</span>";
			$i = 1; $max = count($info['thread_users']);
			foreach($info['thread_users'] as $user_id => $users) {
				echo $users;
				echo $max == $i ? " " : ", ";
				$i++;
			}
			echo "</small></div>\n";
		}

		echo $info['quick_reply_form'];
	}
}

/* Post Item */
if (!function_exists('render_post_item')) {
	function render_post_item($data) {
		global $forum_settings;
		echo "
		<div id='".$data['marker']['id']."' class='clearfix post_items'>\n
		<div class='forum_avatar m-r-15 text-center'>\n
		".$data['user_avatar']."
		".($forum_settings['forum_rank_style'] == '1' ? "<div class='text-center m-t-10'>".$data['user_rank']."</div>\n": '')."
		</div>\n
		<div class='pull-right m-l-10'>".$data['post_checkbox']."</div>\n
		<div class='dropdown pull-right'>\n
		<a class='dropdown' data-toggle='dropdown'><i class='text-dark fa fa-fw fa-ellipsis-v'></i></a>\n
		<ul class='dropdown-menu'>\n
		<li class='dropdown-header'>".$data['user_ip']."</li>\n
		<li class='dropdown-header'>".$data['user_post_count']."</li>\n
		".($data['user_message']['link'] !=="" ? "<li><a href='".$data['user_message']['link']."' title='".$data['user_message']['name']."'>".$data['user_message']['name']."</a></li>\n" : "")."
		".($data['user_web']['link'] !=="" ? "<li><a href='".$data['user_web']['link']."' title='".$data['user_web']['name']."'>".$data['user_web']['name']."</a></li>\n" : "")."
		<li><a href='".$data['print']['link']."' title='".$data['print']['name']."'>".$data['print']['name']."</a></li>\n
		<li class='divider'></li>\n
		".(isset($data['post_quote']) && !empty($data['post_quote']) ? "<li><a href='".$data['post_quote']['link']."' title='".$data['post_quote']['name']."'>".$data['post_quote']['name']."</a></li>\n" : '')."
		".(isset($data['post_edit']) && !empty($data['post_edit']) ? "<li><a href='".$data['post_edit']['link']."' title='".$data['post_edit']['name']."'>".$data['post_edit']['name']."</a></li>\n" : '')."
		</ul>\n
		</div>\n
		<div class='overflow-hide'>\n
		<!--forum_thread_user_name-->\n
		<div class='m-b-10'>\n
		<span style='height:5px; width:10px; border-radius:50%; color:#5CB85C'><i class='fa ".($data['user_online'] ? "fa-circle" : "fa-circle-thin")."'></i></span>\n
		<span class='text-smaller'><span class='forum_poster'>".$data['user_profile_link']."</span>
		".($forum_settings['forum_rank_style'] == '0' ? "<span class='forum_rank'>\n".$data['user_rank']."</span>\n" : '')."
		".$data['post_shortdate']." </span>\n
		<span class='text-smaller'>\n
		".(isset($data['post_quote']) && !empty($data['post_quote']) ? "&middot; <a class='quote-link' href='".$data['post_quote']['link']."' title='".$data['post_quote']['name']."'>".$data['post_quote']['name']."</a>\n" : '')."
		".(isset($data['post_reply']) && !empty($data['post_reply']) ? "&middot; <a class='reply-link' href='".$data['post_reply']['link']."' title='".$data['post_reply']['name']."'>".$data['post_reply']['name']."</a>\n" : '')."
		".(isset($data['post_edit']) && !empty($data['post_edit']) ? "&middot; <a class='edit-link' href='".$data['post_edit']['link']."' title='".$data['post_edit']['name']."'>".$data['post_edit']['name']."</a>\n" : "")."
		</span>\n
		</div>\n
		<!--forum_thread_prepost_".$data['post_id']."-->\n
		".($data['post_votebox'] ? "<div class='pull-left m-r-15'>".$data['post_votebox']."</div>" : '')."
		<div class='display-block ".($data['post_votebox'] !=='' ? 'overflow-hide' : '')."'>\n

		".$data['post_message']."
		".($data['user_sig'] ? "<div class='forum_sig text-smaller'>".$data['user_sig']."</div>\n" : "")."
		".($data['post_attachments'] ? "<div class='m-10'>".$data['post_attachments']."</div>" : "")."
		</div>\n
		<!--sub_forum_post_message-->\n
		<div class='text-right'>\n
		<div class='edit_reason m-b-10'>".$data['post_edit_reason']."</div>\n
		</div>\n
		</div>\n
		</div>\n
		";
		/*
		 * <div class='text-right m-t-10'>\n
		<a class='btn btn-primary btn-xs' href='".$data['post_quote']['link']."' title='".$data['post_quote']['name']."'>".$data['post_quote']['name']."</a>\n
		<a class='btn btn-default btn-xs' href='".$data['post_edit']['link']."' title='".$data['post_edit']['name']."'>".$data['post_edit']['name']."</a>\n
		</div>\n
		 */
	}
}
