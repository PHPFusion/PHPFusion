<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /global/forum.forms.php
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
function postform($data, $info) {
	global $locale, $userdata, $settings;
	$data += array('edit' => !empty($data['edit']) && $data['edit'] == 1 ? 1 : 0,
		'new' => !empty($data['new']) && $data['new'] == 1 ? 1 : 0,
		'reply' => !empty($data['reply']) && $data['reply'] == 1 ? 1 : 0,
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
		'post_editreason' => !empty($data['post_editreason']) && !empty($data['edit']) ? $data['post_editreason'] : '',);
	echo "<!--pre_postnewthread-->";
	/* if ($data['edit']) {
		opentable($locale['forum_0507']);
	} elseif ($data['reply']) {
		opentable($locale['forum_0503']);
	} else {
		opentable($locale['forum_0501']);
	} */
	opentable($info['page_title']);
	$formaction = $settings['site_seo'] == 1 ? FUSION_ROOT : '';
	if ($data['edit']) {
		$formaction .= INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$_GET['forum_id']."&amp;thread_id=".$_GET['thread_id']."&amp;post_id=".$_GET['post_id'];
	} elseif ($data['reply']) {
		$formaction .= INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$_GET['forum_id']."&amp;thread_id=".$_GET['thread_id'];
		if (isset($_GET['quote'])) {
			$formaction .= INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$_GET['forum_id']."&amp;thread_id=".$_GET['thread_id']."&amp;post_id=".$_GET['post_id']."&amp;quote=".$_GET['quote'];
		}
	} elseif ($data['new']) {
		$formaction .= INFUSIONS."forum/newthread.php?action=newthread&amp;forum_id=".$_GET['forum_id'];
	}
	echo openform('input_form', 'post', $formaction, array('enctype' => 1, 'max_tokens' => 1));
	if ($data['edit'] or $data['reply']) {
		if ($data['reply']) {
			echo "<h4 class='m-b-20'>".$locale['forum_2000'].$data['thread_subject']."</h4>\n ".form_hidden('', 'thread_subject', 'thread_subject', $data['thread_subject']);
		} else {
			echo $data['first_post'] == $_GET['post_id'] ? form_text('thread_subject', $locale['forum_0600'], $data['thread_subject'], array('required' => 1,
				'placeholder' => $locale['forum_2001'],
				'error_text' => '',
				'class' => 'm-t-20 m-b-20')) : "<h4 class='m-b-20'>".$locale['forum_2002'].$data['thread_subject']."</h4>\n ".form_hidden('', 'thread_subject', 'thread_subject', $data['thread_subject']);
		}
	} else {
		echo form_text('thread_subject', $locale['forum_0600'], $data['thread_subject'], array('required' => 1,
			'placeholder' => $locale['forum_2001'],
			'error_text' => '',
			'class' => 'm-t-20 m-b-20'));
	}
	echo form_textarea('post_message', $locale['forum_0601'], $data['post_message'], array('required' => 1,
		'error_text' => '',
		'autosize' => 1,
		'no_resize' => 1,
		'preview' => 1,
		'form_name' => 'input_form',
		'bbcode' => 1));
	echo $data['edit'] ? form_text('post_editreason', $locale['forum_0611'], $data['post_editreason'], array('placeholder' => 'Edit reasons',
		'error_text' => '',
		'class' => 'm-t-20 m-b-20')) : '';
	$tab_title['title'][0] = $locale['forum_0602'];
	$tab_title['id'][0] = 'postopts';
	$tab_title['icon'][0] = '';
	if ($info['forum_attach'] && checkgroup($info['forum_attach']) && $info['forum_allow_attach']) {
		$tab_title['title'][1] = $locale['forum_0557'];
		$tab_title['id'][1] = 'attach';
		$tab_title['icon'][1] = '';
	}
	if ($info['forum_poll'] && checkgroup($info['forum_poll']) && ($data['edit'] or $data['new'])) {
		$tab_title['title'][2] = !$data['edit'] ? 'Add Poll' : $locale['forum_0603'];
		$tab_title['id'][2] = 'pollopts';
		$tab_title['icon'][2] = '';
	}
	$tab_active = tab_active($tab_title, isset($_POST['add_poll_option']) ? 2 : 0);
	echo opentab($tab_title, $tab_active, 'newthreadopts');
	echo opentabbody($tab_title['title'][0], 'postopts', $tab_active);
	echo "<div class='p-15'>\n";
	if ($data['edit']) {
		echo form_checkbox('delete', $locale['forum_0624'], '', array('class' => 'm-b-0'));
		echo "<hr class='m-t-5 m-b-10'>\n";
	}
	echo form_checkbox('post_smileys', $locale['forum_0622'], $data['post_smileys'], array('class' => 'm-b-0'));
	if ((iMOD || iSUPERADMIN) && !$data['reply']) {
		echo form_checkbox('thread_sticky', $locale['forum_0620'], $data['thread_sticky'], array('class' => 'm-b-0'));
		echo form_checkbox('thread_locked', $locale['forum_0621'], $data['thread_locked'], array('class' => 'm-b-0'));
		if ($data['edit']) {
			echo form_checkbox('hide_edit', $locale['forum_0627'], '', array('class' => 'm-b-0'));
			echo form_checkbox('post_locked', $locale['forum_0628'], $data['post_locked'], array('class' => 'm-b-0'));
		}
	}
	if (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) {
		echo form_checkbox('post_showsig', $locale['forum_0623'], $data['post_showsig'], array('class' => 'm-b-0'));
	}
	if ($settings['thread_notify'] && !$data['edit']) {
		echo form_checkbox('notify_me', $locale['forum_0626'], $data['notify_me'], array('class' => 'm-b-0'));
	}
	echo "</div>\n";
	echo closetabbody();
	if ($info['forum_attach'] && checkgroup($info['forum_attach']) && $info['forum_allow_attach']) {
		echo opentabbody($tab_title['title'][1], 'attach', $tab_active);
		echo "<div class='p-15 clearfix'>\n";
		add_to_head("<script type='text/javascript' src='".INCLUDES."multi_attachment.js'></script>\n");
		if (isset($info['attachment']) && !empty($info['attachment'])) {
			$i = 0;
			foreach ($info['attachment'] as $attach_id => $attach_name) {
				echo "<label><input type='checkbox' name='delete_attach_".$attach_id."' value='1' /> ".$locale['forum_0625']."</label>\n";
				echo "<a href='".INFUSIONS."forum/attachments/".$attach_name."'>".$attach_name."</a> [".parsebytesize(filesize(INFUSIONS."forum/attachments/".$attach_name))."]\n";
				echo "<br/>\n";
				$i++;
			}
		}
		echo "<div class='m-b-10'>".sprintf($locale['forum_0559'], parsebytesize($settings['attachmax']), str_replace(',', ' ', $settings['attachtypes']), $settings['attachmax_count'])."</div>\n";
		echo "<input id='my_file_element' type='file' name='file_1' class='textbox' style='width:200px;' />\n";
		echo "<div class='m-t-10' id='files_list'></div>\n";
		echo "</div>\n";
		echo "<script>\n";
		echo "/* <![CDATA[ */\n";
		echo "<!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->\n";
		echo "var multi_selector = new MultiSelector( document.getElementById( \"files_list\" ), ".($data['edit'] && isset($info['attachmax_count']) ? $info['attachmax_count'] : $settings['attachmax_count']).");\n";
		echo "<!-- Pass in the file element -->\n";
		echo "multi_selector.addElement( document.getElementById( \"my_file_element\" ) );\n";
		echo "/* ]]>*/\n";
		echo "</script>\n";
		echo closetabbody();
	}
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
	echo closetab();
	echo "<div class='m-b-20'>\n";
	echo form_hidden('', 'forum_id', 'forum_id', $data['forum_id']);
	//echo form_button('previewpost', $data['edit'] ? $locale['forum_0505'] : $locale['forum_0500'], $data['edit'] ? $locale['forum_0505'] : $locale['forum_0500'], array('class' => 'btn-default btn-sm m-r-10'));
	if ($data['edit']) {
		echo form_button("savechanges", $locale['forum_0508'], $locale['forum_0508'], array('class' => 'btn-primary btn-sm'));
	} elseif ($data['reply']) {
		echo form_button('postreply', $locale['forum_0504'], $locale['forum_0504'], array('class' => 'btn-primary btn-sm'));
	} else {
		echo form_button('postnewthread', $locale['forum_0501'], $locale['forum_0501'], array('class' => 'btn-primary btn-sm'));
	}
	echo form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default btn-sm m-l-10'));
	echo "</div>\n";
	echo closeform();
	closetable();
	if ($data['new']) {
		echo "<!--sub_postnewthread-->";
	} elseif ($data['reply']) {
		echo "<hr>\n";
		if ($settings['forum_last_posts_reply'] != "0") {
			$result = dbquery("SELECT p.thread_id, p.post_message, p.post_smileys, p.post_author, p.post_datestamp, p.post_hidden,
							u.user_id, u.user_name, u.user_status, u.user_avatar
							FROM ".DB_FORUM_POSTS." p
							LEFT JOIN ".DB_USERS." u ON p.post_author = u.user_id
							WHERE p.thread_id='".$_GET['thread_id']."' AND p.post_hidden='0'
							ORDER BY p.post_datestamp DESC LIMIT 0,".$settings['forum_last_posts_reply']);
			if (dbrows($result)) {
				$title = "";
				if ($settings['forum_last_posts_reply'] == "1") {
					$title = $locale['forum_0525'];
				} else {
					$title = sprintf($locale['forum_0526'], $settings['forum_last_posts_reply']);
				}
				opentable($title);
				echo "<table cellpadding='1' cellspacing='1' width='100%' class='tbl-border forum_thread_table table table-responsive'>\n";
				$i = $settings['forum_last_posts_reply'];
				while ($data = dbarray($result)) {
					$message = $data['post_message'];
					if ($data['post_smileys']) {
						$message = parsesmileys($message);
					}
					$message = parseubb($message);
					echo "<tr>\n<td class='tbl2 forum_thread_user_name' style='width:10%'><!--forum_thread_user_name-->".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
					echo "<td class='tbl2 forum_thread_post_date'>\n";
					echo "<div style='float:right' class='small'>\n";
					echo $i.($i == $settings['forum_last_posts_reply'] ? " (".$locale['forum_0525'].")" : "");
					echo "</div>\n";
					echo "<div class='small'>".$locale['forum_0524'].showdate("forumdate", $data['post_datestamp'])."</div>\n";
					echo "</td>\n";
					echo "</tr>\n<tr>\n<td valign='top' class='tbl2 forum_thread_user_info' style='width:10%'>\n";
					echo display_avatar($data, '50px');
					echo "</td>\n<td valign='top' class='tbl1 forum_thread_user_post'>\n";
					echo nl2br($message);
					echo "</td>\n</tr>\n";
					$i--;
				}
				echo "</table>\n";
				closetable();
			}
		}
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