<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_input.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

function postform($info) {
	global $locale;
	echo render_breadcrumbs();
	opentable($info['title']);
	// New template
	echo "<!--pre_form-->\n";
	echo "<h4 class='m-b-20'>".$info['description']."</h4>\n";
	echo $info['openform'];

	echo $info['subject_field'];
	echo $info['message_field'];
	echo $info['edit_reason_field'];
	echo $info['forum_id_field'];
	echo $info['thread_id_field'];

	// No more logical interpretations - all done by core. Just check if available, echo.
	$tab_title['title'][0] = $locale['forum_0602'];
	$tab_title['id'][0] = 'postopts';
	$tab_title['icon'][0] = '';

	$tab_active = tab_active($tab_title, isset($_POST['add_poll_option']) ? 2 : 0);
	$tab_content = opentabbody($tab_title['title'][0], 'postopts', $tab_active); // first one is guaranteed to be available
	$tab_content .= "<div class='p-15'>\n";
	$tab_content .= $info['delete_field'];
	$tab_content .= $info['sticky_field'];
	$tab_content .= $info['notify_field'];
	$tab_content .= $info['lock_field'];
	$tab_content .= $info['hide_edit_field'];
	$tab_content .= $info['smileys_field'];
	$tab_content .= $info['signature_field'];
	$tab_content .= "</div>\n";
	$tab_content .= closetabbody();

	if (!empty($info['attachment_field'])) {
		$tab_title['title'][1] = $info['attachment_field']['title'];
		$tab_title['id'][1] = 'attach_tab';
		$tab_title['icon'][1] = '';
		$tab_active = tab_active($tab_title, isset($_POST['add_poll_option']) ? 2 : 0);
		$tab_content .= opentabbody($tab_title['title'][1], 'attach_tab', $tab_active);
		$tab_content .= "<div class='p-15 clearfix'>\n";
		$tab_content .= $info['attachment_field']['field'];
		$tab_content .= "</div>\n";
		$tab_content .= closetabbody();
	}

	if (!empty($info['poll'])) {
		$tab_title['title'][2] = $info['poll']['title']; //!$data['edit'] ? 'Add Poll' : $locale['forum_0603'];
		$tab_title['id'][2] = 'poll_tab';
		$tab_title['icon'][2] = '';
		$tab_active = tab_active($tab_title, isset($_POST['add_poll_option']) ? 2 : 0);
		$tab_content .= opentabbody($tab_title['title'][2], 'poll_tab', $tab_active);
		$tab_content .= "<div class='p-15 clearfix'>\n";
		$tab_content .= $info['poll']['field'];
		$tab_content .= "</div>\n";
		$tab_content .= closetabbody();
	}

	echo opentab($tab_title, $tab_active, 'newthreadopts');
	echo $tab_content;
	echo closetab();
	echo $info['post_buttons'];
	echo $info['closeform'];
	echo "<!--end_form-->\n";
	closetable();
	if (!empty($info['last_posts_reply'])) {
		echo "<div class='well m-t-20'>\n";
		echo $info['last_posts_reply'];
		echo "</div>\n";
	}
}

function pollform($info) {
	echo render_breadcrumbs();
	opentable($info['title']);
	echo "<h4 class='m-b-20'>".$info['description']."</h4>\n";
	echo "<!--pre_form-->\n";
	echo $info['field']['openform'];
	echo $info['field']['poll_field'];
	echo $info['field']['poll_button'];
	echo $info['field']['closeform'];
	closetable();
}


/**
 * @param $info - template data
 */
function postformssss($info) {


	/* $data += array(
		//'edit' => !empty($data['edit']) && $data['edit'] == 1 ? 1 : 0,
		//'new' => !empty($data['new']) && $data['new'] == 1 ? 1 : 0,
		//'reply' => !empty($data['reply']) && $data['reply'] == 1 ? 1 : 0,
		'forum_id' => dbcount("('forum_id')", DB_FORUMS, "forum_id = '".$_GET['forum_id']."'") ? $_GET['forum_id'] : 0,
		'thread_subject' => !empty($data['thread_subject']) ? $data['thread_subject'] : '',
		'post_message' => !empty($data['post_message']) ? $data['post_message'] : '',
		'thread_sticky' => !empty($data['thread_sticky']) ? $data['thread_sticky'] : '',
		'thread_locked' => !empty($data['thread_locked']) ? $data['thread_locked'] : '',
		'post_smileys' => !empty($data['post_smileys']) ? $data['post_smileys'] : '',
		'post_showsig' => !empty($data['post_showsig']) ? $data['post_showsig'] : '1',
		'notify_me' => !empty($data['notify_me']) ? $data['notify_me'] : '',
		'thread_poll' => !empty($data['thread_poll']) && $data['thread_poll'] == 1 ? 1 : 0,
		'forum_poll_title' => !empty($data['forum_poll_title']) ? $data['forum_poll_title'] : '',
		'poll_opts' => !empty($data['poll_opts']) ? $data['poll_opts'] : '',
		// For Post Editing only. Requires $data['edit'] = 1.
		'first_post' => !empty($data['first_post']) && !empty($data['edit']) ? $data['first_post'] : '',
		'post_editreason' => !empty($data['post_editreason']) && !empty($data['edit']) ? $data['post_editreason'] : '',
	); */

	/* if ($data['edit']) {
		opentable();
	} elseif ($data['reply']) {
		opentable($locale['forum_0503']);
	} else {
		opentable($locale['forum_0501']);
	} */

	/* $formaction = $settings['site_seo'] == 1 ? FUSION_ROOT : '';
	if ($data['edit']) {
	//	$formaction .= INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$_GET['forum_id']."&amp;thread_id=".$_GET['thread_id']."&amp;post_id=".$_GET['post_id'];
	} elseif ($data['reply']) {
		//$formaction .= INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$_GET['forum_id']."&amp;thread_id=".$_GET['thread_id'];
		if (isset($_GET['quote'])) {
			//$formaction .= INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$_GET['forum_id']."&amp;thread_id=".$_GET['thread_id']."&amp;post_id=".$_GET['post_id']."&amp;quote=".$_GET['quote'];
		}
	} elseif ($data['new']) {
	//	$formaction .= INFUSIONS."forum/newthread.php?action=newthread&amp;forum_id=".$_GET['forum_id'];
	} */



	/* if ((iMOD || iSUPERADMIN) && !$data['reply']) {
		//echo form_checkbox('thread_sticky', $locale['forum_0620'], $data['thread_sticky'], array('class' => 'm-b-0'));
		//echo form_checkbox('thread_locked', $locale['forum_0621'], $data['thread_locked'], array('class' => 'm-b-0'));
		if ($data['edit']) {
			//echo form_checkbox('hide_edit', $locale['forum_0627'], '', array('class' => 'm-b-0'));
			//echo form_checkbox('post_locked', $locale['forum_0628'], $data['post_locked'], array('class' => 'm-b-0'));
		}
	} */

	//if (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) {
	//	echo form_checkbox('post_showsig', $locale['forum_0623'], $data['post_showsig'], array('class' => 'm-b-0'));
	//}
	//if ($settings['thread_notify'] && !$data['edit']) {
	//	echo form_checkbox('notify_me', $locale['forum_0626'], $data['notify_me'], array('class' => 'm-b-0'));
	//}

	if ($info['forum_poll'] && checkgroup($info['forum_poll']) && ($data['edit'] or $data['new'])) {
		echo opentabbody($tab_title['title'][2], 'pollopts', $tab_active);
		echo "<div class='p-15 clearfix'>\n";
		echo form_text('forum_poll_title', $locale['forum_0604'], $data['forum_poll_title'], array('max_length' => 255,
			'placeholder' => 'Enter a Poll Title',
			'inline' => 1));
		echo form_hidden('', 'thread_poll', 'thread_poll', $data['thread_poll']);
		$i = 1;
		if (isset($data['poll_opts']) && !empty($data['poll_opts'])) {
			foreach ($data['poll_opts'] as $poll_option) {
				echo form_text("poll_options[$i]", $locale['forum_0605'].' '.$i, $poll_option, array('max_length' => 255,
					'placeholder' => 'Poll Options',
					'inline' => 1,
					'class' => 'm-b-0'));
				if ($data['edit']) {
					echo "<div class='col-xs-12 col-sm-offset-3 m-t-5'>\n";
					echo form_button("update_poll_option[$i]", $locale['forum_0609'], $locale['forum_0609'], array('class' => 'btn-xs btn-default m-r-10'));
					echo form_button("delete_poll_option[$i]", $locale['forum_0610'], $locale['forum_0610'], array('class' => 'btn-xs btn-default m-r-10'));
					echo "</div>\n";
				}
				echo "<hr/>";
				if ($i == count($data['poll_opts'])) {
					if ($data['edit']) {
						$i++;
						echo form_text("poll_options[$i]", $locale['forum_0605'].' '.$i, '', array('max_length' => 255,
							'placeholder' => 'Poll Options',
							'inline' => 1,
							'class' => 'm-b-0'));
					}
					echo "<div class='col-xs-12 col-sm-offset-3 m-b-10'>\n";
					echo form_button('add_poll_option', $locale['forum_0608'], $locale['forum_0608'], array('class' => 'btn-default btn-sm m-r-10',
						'icon' => 'entypo plus-circled'));
					if ($data['edit']) {
						echo form_button('update_poll_title', $locale['forum_0609'], $locale['forum_0609'], array('class' => 'btn-default btn-sm m-r-10'));
						echo form_button('delete_poll', $locale['forum_0610'], $locale['forum_0610'], array('class' => 'btn-default btn-sm m-r-10'));
					}
					echo "</div>\n";
				}
				$i++;
			}
		} else {
			echo form_text("poll_options[1]", $locale['forum_0606'], '', array('max_length' => 255,
				'placeholder' => 'Poll Options',
				'inline' => 1));
			echo form_text("poll_options[2]", $locale['forum_0607'], '', array('max_length' => 255,
				'placeholder' => 'Poll Options',
				'inline' => 1));
			echo "<div class='col-xs-12 col-sm-offset-3'>\n";
			echo form_button('add_poll_option', $locale['forum_0608'], $locale['forum_0608'], array('class' => 'btn-default btn-sm'));
			echo "</div>\n";
		}
		echo "</div>\n";
		echo "</div>\n";
		echo closetabbody();
	}




	//echo form_button('previewpost', $data['edit'] ? $locale['forum_0505'] : $locale['forum_0500'], $data['edit'] ? $locale['forum_0505'] : $locale['forum_0500'], array('class' => 'btn-default btn-sm m-r-10'));
	if ($data['edit']) {
		//echo form_button("savechanges", $locale['forum_0508'], $locale['forum_0508'], array('class' => 'btn-primary btn-sm'));
	} elseif ($data['reply']) {

	} else {
		//echo form_button('postnewthread', $locale['forum_0501'], $locale['forum_0501'], array('class' => 'btn-primary btn-sm'));
	}




	if ($data['new']) {
		echo "<!--sub_postnewthread-->";
	} elseif ($data['reply']) {
		echo "<hr>\n";

}
}
/*
if (!function_exists('post_preview')) {
function post_preview($data) {
	global $locale, $userdata, $can_poll;
	echo openmodal('preview', $locale['forum_0500']);
	echo "<div class='clearfix'>\n";
	echo "<div class='pull-left m-r-10 text-center' style='width:100px'>\n".display_avatar($userdata, '50px', '', '', 'img-rounded m-0')." <br/>
		  <span class='strong display-inline-block m-t-10'>".profile_link($userdata['user_id'], $userdata['user_name'], $userdata['user_status'])."</span><br/>\n
		  <span class='display-inline-block m-t-10 text-smaller'>".getuserlevel($userdata['user_level'])."</span>
		  </div>\n";
	echo "<div class='overflow-hide'>\n";
	echo "<h4 class='m-b-10'>".$data['thread_subject']."</h4>\n";
	echo "<span>".$locale['forum_0524']." ".showdate('forumdate', time())."</span>\n<hr class='m-t-10 m-b-10'/>";
	echo "<p>\n".$data['preview_message']."</p>\n";
	// poll.
	if ($can_poll && !empty($data['forum_poll_title']) && !empty($data['poll_opts'])) {
		echo "<div class='panel panel-default'>\n";
		echo "<div class='panel-body'>\n";
		echo "<span class='text-bigger strong display-inline-block m-b-10'><i class='entypo chart-pie'></i>".$data['forum_poll_title']."</span>\n";
		echo "<hr class='m-t-0 m-b-10'/>\n";
		echo "<ul class='p-l-20 p-t-0'>\n";
		$i = 0;
		foreach ($data['poll_opts'] as $poll_option) {
			echo "<li><label for='opt-".$i."'><input id='opt-".$i."' type='radio' name='poll_option' value='".$i."' class='m-r-20'> <span class='m-l-10'>$poll_option</span>\n</label></li>\n";
			$i++;
		}
		echo "</ul>\n";
		echo "<hr class='m-t-10 m-b-10'/>\n";
		echo form_button('vote', $locale['forum_2010'], 'vote', array('class' => 'btn btn-sm btn-primary m-l-20 ',
			'deactivate' => 1));
		echo "</div>\n";
		echo "</div>\n";
	}
	echo "</div>\n";
	echo "</div>\n";
	echo closemodal();
	}
}
*/