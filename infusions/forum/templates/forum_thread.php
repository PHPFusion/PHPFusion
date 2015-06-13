<?php

/* Viewthread.php */

if (!function_exists('render_thread')) {
	function render_thread($info) {
		global $locale;
		// no post items must redirect instead of showing message
		//if (isset($info['post_items']) && !empty($info['post_items'])) {
		$buttons = $info['buttons'];
		$data = $info['thread'];
		$pdata = $info['post_items'];
		// filter UI vars
		$icon = array(
			'',
			'',
			'fa fa-trophy fa-fw'
		);

		echo render_breadcrumbs();
		echo "<div class='clearfix'>\n";
		echo "<h2 class='m-t-0 thread-header pull-left m-r-20'>".$data['thread_subject']."</h2>\n";
		echo "<div class='text-smaller text-lighter m-t-10'><i class='fa fa-calendar'></i> ".$locale['forum_0363'].timer($data['thread_lastpost'])." ".showdate('forumdate', $data['thread_lastpost'])."</div>\n";
		echo "</div>\n";

		// Thread buttons, top
		if ($info['permissions']['can_post']) {
			echo "<div class='pull-right'>\n";
			echo "<a class='btn btn-primary btn-sm m-r-5 ".(empty($buttons['newthread']) ? 'disabled' : '')." ' href='".$buttons['newthread']['link']."'>".$buttons['newthread']['name']."</a>\n";
			echo "</div>\n";
		}
		echo "<div class='btn-group pull-right m-r-10'>\n";
		echo isset($buttons['notify']) ? "<a class='btn btn-default btn-sm' title='".$buttons['notify']['name']."' href='".$buttons['notify']['link']."'>".$buttons['notify']['name']." <i class='fa fa-eye'></i></a>\n" : '';
		echo "<a class='btn btn-default btn-sm' title='".$buttons['notify']['name']."' href='".$buttons['print']['link']."'>".$buttons['print']['name']." <i class='fa fa-print'></i> </a>\n";
		echo "</div>\n";


		$p_title = array();
		foreach($info['post-filters'] as $i => $filters) {
			$p_title['title'][] = $filters['locale'];
			$p_title['id'][] = $info['allowed_post_filters'][$i];
			$p_title['icon'][] = $icon[$i];
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
				if ($i == 1 && $info['permissions']['can_post']) {
					// interject the new thread and reply here.
					echo "<div class='text-right'>\n";
					echo $info['thread_posts'];
					echo "<a class='m-l-20 btn btn-primary btn-sm ".(empty($buttons['reply']) ? 'disabled' : '')."' href='".$buttons['reply']['link']."'>".$buttons['reply']['name']."</a>\n";
					echo "</div>\n";
				}
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
		//} else {
		//	echo "<div class='text-center well'>".$locale['forum_0270']."</div>\n";
		//}
	}
}


/* Post Item */
if (!function_exists('render_post_item')) {
	function render_post_item($data) {
		// global $locale, $inf_settings, $settings; -- these are accessible, but i'm not using it. ;)
		echo "
		<div class='m-b-20 m-t-20 list-group-item'>\n
			<div class='pull-left text-center m-r-15'>\n
				".$data['user_avatar']."
				<div class='forum_rank text-smaller m-10'>\n".$data['user_rank']."</div>\n
				<div class='text-lighter text-smaller'>".$data['user_post_count']."</div>
			</div>\n
			<div id='".$data['marker']['link']."' class='dropdown pull-right'>\n
				<a class='dropdown' data-toggle='dropdown'><i class='fa fa-fw fa-ellipsis-v'></i></a>
				<ul class='dropdown-menu'>\n
					<li class='dropdown-header'>".$data['user_ip']."</li>\n
					<li><a href='".$data['post_quote']['link']."' title='".$data['post_quote']['name']."'>".$data['post_quote']['name']."</a></li>\n
					<li><a href='".$data['post_edit']['link']."' title='".$data['post_edit']['name']."'>".$data['post_edit']['name']."</a></li>\n
					<li><a href='".$data['print']['link']."' title='".$data['print']['name']."'>".$data['print']['name']."</a></li>\n
					<li><a href='".$data['user_web']['link']."' title='".$data['user_web']['name']."'>".$data['user_web']['name']."</a></li>\n
					<li><a href='".$data['user_message']['link']."' title='".$data['user_message']['name']."'>".$data['user_message']['name']."</a></li>\n
				</ul>
			</div>
			<div class='overflow-hide'>
				<!--forum_thread_user_name-->\n
				<div class='m-b-10'>\n
					<span style='height:5px; width:10px; border-radius:50%; color:#5CB85C'><i class='fa ".($data['user_online'] ? "fa-circle" : "fa-circle-thin")."'></i></span>\n
					<span class='text-smaller'><strong>".$data['user_profile_link']."</strong> - ".$data['post_shortdate']." </span>
					<span class='text-smaller'>
					&middot; <a href='".$data['post_quote']['link']."' title='".$data['post_quote']['name']."'>".$data['post_quote']['name']."</a>
					&middot; <a href='".$data['post_reply']['link']."' title='".$data['post_reply']['name']."'>".$data['post_reply']['name']."</a>
					&middot; <a href='".$data['post_edit']['link']."' title='".$data['post_edit']['name']."'>".$data['post_edit']['name']."</a>
					</span>
				</div>
				<!--forum_thread_prepost_".$data['post_id']."-->\n
				<div class='display-block'>
					".$data['post_message']
			 		.($data['post_attachments'] ? "<div class='m-10'>".$data['post_attachments']."</div>" : "")
			 		.($data['post_votebox'] ? "<div class='pull-right'>".$data['post_votebox']."</div>" : '')."
				</div>\n
				<!--sub_forum_post_message-->\n
				<div class='forum_sig text-smaller'>".$data['user_sig']."</div>\n

				<div class='text-right'>
					<div class='edit_reason m-b-10'>".$data['post_edit_reason']."</div>
					<div class='pull-right m-l-10'>".$data['post_checkbox']."</div>\n
				</div>
				<div class='text-right m-t-10'>
					<a class='btn btn-default btn-sm' href='".$data['post_quote']['link']."' title='".$data['post_quote']['name']."'>".$data['post_quote']['name']."</a>
					<a class='btn btn-default btn-sm' href='".$data['post_edit']['link']."' title='".$data['post_edit']['name']."'>".$data['post_edit']['name']."</a>
				</div>\n
			</div>
		</div>
		";
//".$data['post_links']."
/*
 *

 */

	}
}