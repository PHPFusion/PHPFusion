<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: messages.php
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

if (!function_exists('render_inbox')) {
	function render_inbox($info) {
		global $locale, $userdata, $settings, $msg_settings;
		opentable($locale['400']);
		add_to_jquery("
			var l_height = $('.left-pm').height(), r_height = $('.right-pm').height();
			if (l_height > r_height) { $('.right-pm').height(l_height); } else $('.left-pm').height(r_height);
		");
		echo "<div class='row'>\n";
		echo "<div class='left-pm col-xs-12 col-sm-4 col-md-4 col-lg-4 p-0' style=' ".($_GET['folder'] != 'options' ? 'border-right:1px solid #ddd; overflow-y: auto;' : 'border-bottom:1px solid #ddd; height:50px;')."'>\n";
		echo "<div class='btn-group p-l-10' style='height:49px;'>\n";
		echo "<a href='".FUSION_SELF."?folder=inbox' class='btn btn-sm btn-default text-dark ".($_GET['folder'] == "inbox" ? "active" : '')."'>".$locale['402']."</a>";
		echo "<a href='".FUSION_SELF."?folder=outbox'  class='btn btn-sm btn-default text-dark ".($_GET['folder'] == "outbox" ? "active" : '')."'>".$locale['403']."</a>";
		echo "<a href='".FUSION_SELF."?folder=archive'  class='btn btn-sm btn-default text-dark ".($_GET['folder'] == "archive" ? "active" : '')."'>".$locale['404']."</a>";
		echo "</div>\n";
		if ($_GET['folder'] == 'inbox' && isset($info['pm_inbox']) && $info['pm_inbox']) {
			echo "<div class='p-10'>\n";
			$cfg = ($info['pm_inbox'] != 0 ? : 1);
			$percent = number_format(($info['inbox_total']/$cfg)*99, 0);
			echo progress_bar($percent, $locale['UM098'], '', '15px');
			echo "</div>\n";
		} elseif ($_GET['folder'] == 'outbox' && isset($info['pm_outbox']) && $info['pm_outbox'] != 0) {
			echo "<div class='p-10'>\n";
			$cfg = ($info['pm_outbox'] != 0 ? : 1);
			$percent = number_format(($info['outbox_total']/$cfg)*99, 0);
			echo progress_bar($percent, $locale['UM098'], '', '15px');
			echo "</div>\n";
		} elseif ($_GET['folder'] == 'archive' && isset($info['pm_archive']) && $info['pm_archive'] != 0) {
			echo "<div class='p-10'>\n";
			$cfg = ($info['pm_archive'] != 0 ? : 1);
			$percent = number_format(($info['archive_total']/$cfg)*99, 0);
			echo progress_bar($percent, $locale['UM098'], '', '15px');
			echo "</div>\n";
		}
		// Left bar content
		if ($_GET['folder'] != 'options') {
			echo "<div style='border-top:1px solid #ccc; border-bottom:1px solid #ccc;' class='p-10'>\n";
			echo form_text('', 'pm_filter', 'pm_filter', '', array('placeholder' => $locale['470'], 'append_button' => '1'));
			echo "</div>\n";
			// LEFT BAR LIST
			echo "<div class='msg-list-item list-group bbr-0'>\n";
			if ($info['total_rows']) {
				foreach ($info['chat_list'] as $_msg) {
					echo "<!--- start message list -->\n";
					echo "<div class='list-group-item clearfix ".(isset($_GET['msg_user']) && $_GET['msg_user'] == $_msg['user_id'] ? 'active' : '')." bbr-0 br-l-0 br-r-0 br-t-0'>\n";
					echo "<div class='pull-left m-r-10'>\n".display_avatar($_msg, '50px')." </div>\n";
					echo "<div class='overflow-hide'>";
					//@todo : What to do with this
					//echo "<div class='btn-group pull-right m-b-0'>\n";
					//echo form_button('', 'delmsg', 'delmsg', 1, array('class'=>'btn btn-xs btn-default', 'icon'=>'entypo trash'));
					//echo form_button('', 'save', 'save', 1, array('class'=>'btn btn-xs btn-default', 'icon'=>'entypo cloud'));
					//echo "</div>\n";
					echo "<span class='strong'>".$_msg['user_name']."</span><span class='text-smaller'> - ".date('d M', $_msg['message_datestamp'])."</span><br/>";
					echo "<a href='".BASEDIR."messages.php?folder=".$_GET['folder']."&amp;msg_user=".$_msg['user_id']."' class='display-inline-block ".($_msg['message_read'] > 0 ? 'text-dark text-normal' : '')."'>".trimlink($_msg['message_subject'], 50)."</a>\n";
					echo "</div>\n";
					echo "</div>\n";
					echo "<!--- end message list -->\n";
				}
			} else {
				echo "<div class='list-group-item text-center bbr-0 br-0'>".$locale['461']."</div>";
			}
			echo "</div>\n";
		}
		// Right Column
		echo "</div><div class='right-pm col-xs-12 col-sm-8 col-md-8 col-lg-8 p-0'>\n";
		$this_selected = isset($_GET['msg_user']) && isnum($_GET['msg_user']) ? $info['data'][$_GET['msg_user']]['0'] : '';
		echo "<div class='clearfix p-10 p-t-0' style='height:50px; border-bottom:1px solid #ddd;'>\n";
		echo "<div class='btn-group pull-right'>\n";
		echo "<a class='btn btn-sm btn-default text-dark' href='".FUSION_SELF."?folder=".$_GET['folder']."&amp;msg_send=0'><i class='entypo plus-circled'></i> ".$locale['401']."</a>\n";
		echo "<a href='".FUSION_SELF."?folder=options'  class='btn btn-sm btn-default text-dark ".($_GET['folder'] == "options" ? "active" : '')."'>".$locale['425']."</a>";
		echo "</div>\n";
		if ($_GET['folder'] != 'options') {
			echo "<h4 id='person'>\n";
			if (isset($_GET['msg_send']) && isnum($_GET['msg_send'])) {
				echo $locale['420'];
			} else {
				echo $info['total_rows'] && isset($_GET['msg_user']) && !empty($this_selected) ? profile_link($this_selected['user_id'], $this_selected['user_name'], $this_selected['user_status']) : $locale['466'];
			}
			echo "</h4>\n";
		}
		echo "</div>\n";
		if ($_GET['folder'] != 'options') {
			if ($info['total_rows']) echo openform('inputform', 'inputform', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder'].(isset($_GET['msg_user']) ? "&msg_user=".$_GET['msg_user']."" : '').(isset($_GET['msg_read']) ? "&msg_read=".$_GET['msg_read']."" : ''), array('notice' => 0));
			// action buttons
			if ($info['total_rows'] && isset($_GET['msg_user']) && !empty($this_selected) && $_GET['folder'] == 'inbox' && !isset($_GET['msg_read'])) {
				echo "<div class='clearfix p-10' style='border-bottom:1px solid #ddd;'>\n";
				echo "<div class='btn-group pull-right'>\n";
				if ($_GET['folder'] == "inbox") {
					echo form_button($locale['412'], 'save_msg', 'save_msg', $locale['412'], array('class' => 'btn btn-xs btn-default'));
				}
				//@todo: unfriendly unarchiving.
				//if ($_GET['folder'] == "archive") {
				//echo form_button($locale['413'], 'unsave_msg', 'unsave_msg', $locale['413'], array('class'=>'btn btn-xs btn-default'));
				//}
				echo form_button($locale['414'], 'read_msg', 'read_msg', $locale['414'], array('class' => 'btn btn-xs btn-default'));
				echo form_button($locale['415'], 'unread_msg', 'unread_msg', $locale['415'], array('class' => 'btn btn-xs btn-default'));
				echo form_button($locale['416'], 'delete_msg', 'delete_msg', $locale['416'], array('class' => 'btn btn-xs btn-default'));
				echo "</div>\n";
				echo "<a onclick=\"javascript:setChecked('inputform','check_mark[]',1);return false;\">".$locale['410']."</a> |\n";
				echo "<a onclick=\"javascript:setChecked('inputform','check_mark[]',0);return false;\">".$locale['411']."</a>\n";
				echo "</div>\n";
			} elseif (isset($_GET['msg_read'])) {
				echo "<div class='clearfix p-10' style='border-bottom:1px solid #ddd;'>\n";
				echo "<div class='btn-group'>\n";
				if ($_GET['folder'] == "inbox") {
					echo form_button($locale['412'], 'save', 'save', $locale['412'], array('class' => 'btn btn-sm btn-default'));
				}
				echo form_button($locale['416'], 'delete', 'delete', $locale['416'], array('class' => 'btn btn-sm btn-default'));
				echo "</div>\n";
				echo "</div>\n";
			}

			// Main View Section

			if (isset($_GET['msg_send']) && $_GET['msg_send'] == 0) {
				// New Form
				echo "<div class='msg-form m-t-20 p-l-10 p-r-10'>\n";
				echo openform('inputform', 'inputform', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder']."&amp;msg_send=".$_GET['msg_send']."");
				if (iADMIN && !isset($_GET['msg_id'])) {
					echo "<a class='pull-right m-b-10 display-inline-block' id='mass_send'>".$locale['434']."</a><br/>";
					echo form_user_select('', 'msg_send', 'msg_send', isset($_GET['msg_send']) && isnum($_GET['msg_send'] ? : ''), array('placeholder' => $locale['421']));
					$user_groups = getusergroups();
					while (list($key, $user_group) = each($user_groups)) {
						if ($user_group['0'] != "0") {
							$user_types[$user_group[0]] = $user_group[1];
						}
					}
					echo "<div id='msg_to_group-field' class='form-group display-none'>\n";
					echo "<label for='mg_to_group' class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0'>".$locale['434']." <input id='all_check' name='chk_sendtoall' type='checkbox' class='pull-left display-inline-block' style='margin-right:10px !important;' /></label>\n";
					echo "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n";
					echo form_select('', 'msg_to_group', 'msg_to_group', $user_types, '', array('width' => '250px', 'class' => 'm-b-0'));
					echo "</div>\n</div>\n";
					add_to_jquery("
						$('#mass_send').bind('click', function() {
							$('#msg_to_group-field').toggleClass('display-none');
							$('#msg_send-field').toggleClass('display-none');
							var invisible = $('#msg_to_group-field').hasClass('display-none');
							if (invisible) {
								$('#all_check').prop('checked', false);
							} else {
								$('#all_check').prop('checked', true);
							}
						});
					");
				} elseif (!isset($_GET['msg_id'])) {
					echo form_user_select('', 'msg_send', 'msg_send', isset($_GET['msg_send']) && isnum($_GET['msg_send'] ? : ''), array('inline' => 1, 'placeholder' => $locale['421']));
				}
				echo form_text('', 'subject', 'subject', '', array('max_length' => 32, 'placeholder' => $locale['405']));
				echo "<hr class='m-t-0'/><br/>";
				echo form_textarea('', 'message', 'message', '', array('class' => 'm-t-20', 'autosize' => 1, 'bbcode' => 1, 'preview' => 1, 'resize' => 0, 'form_name' => 'inputform', 'placeholder' => $locale['422']));
				echo "<div class='text-right'>\n";
				echo form_button($locale['435'], 'cancel', 'cancel', $locale['435'], array('class' => 'btn btn-sm btn-default m-r-10'));
				echo form_button($locale['430'], 'send_message', 'send_message', $locale['430'], array('class' => 'btn btn-sm btn-primary'));
				echo "</div>\n";
				echo "</div>\n";

			} elseif (isset($_GET['msg_read'])) {

				echo "<div id='msg-container' class='p-10 clearfix'>\n";
				if (!empty($info['message'])) {
					$i = 0;
					echo "<p><span class='strong'>Subject :</strong> ".censorwords($info['message'][0]['message_subject'])."</p>";
					foreach ($info['message'] as $date => $mdata) {
						echo "<!--- start message item -->\n";
						echo "<div class='clearfix m-b-10'>\n";
						echo "<div class='pull-left m-r-5'>\n";
						echo display_avatar($mdata, '50px');
						echo "</div>\n";
						echo "<div class='overflow-hide'>\n";
						echo "<span class='pull-right text-smaller'>".date('d/m/y, h:i a', $mdata['message_datestamp'])."</span>\n";
						echo profile_link($mdata['user_id'], $mdata['user_name'], $mdata['user_status']);
						echo "<p>".censorwords($mdata['message_message'])."</p>";
						echo "</span>\n";
						echo "</div>\n";
						echo "</div>\n";
						echo "<!--- end message item -->\n";
						$i++;
					}
				}
				echo "</div>\n";

			} else {

				// Listing of Subjects.
				if ($info['total_rows'] && isset($_GET['msg_user']) && !empty($this_selected)) {
					echo "<div id='msg-container' class='p-10 clearfix'>\n";
					foreach ($info['data'][$_GET['msg_user']] as $messages) {
						echo "<!--- start message item -->\n";
						echo "<div class='clearfix m-b-10 m-t-10'>\n";
						if ($_GET['folder'] == 'outbox') {
							echo "<div class='pull-left m-r-5'>\n".display_avatar($userdata, '50px')."</div>\n";
						} else {
							echo "<div class='pull-left m-r-5'>\n".display_avatar($messages, '50px')."</div>\n";
						}
						echo "<div class='overflow-hide'>\n";
						echo "<span class='pull-right text-smaller'>".date('d/m/y, h:i a', $messages['message_datestamp'])." <input type='checkbox' name='check_mark[]' value='".$messages['message_id']."' /></span>\n";
						echo "<span class='strong'><a ".($messages['message_read'] ? "class='text-dark mid-opacity'" : "")." href='".BASEDIR."messages.php?folder=".$_GET['folder']."&msg_user=".$_GET['msg_user']."&amp;msg_read=".$messages['message_id']."'>".censorwords($messages['message_subject'])."</a></span><br/>";
						echo "<p ".($messages['message_read'] ? "class='mid-opacity'" : "")." >".trim_word(censorwords($messages['message_message']), 50)."</p>";
						echo "</span>\n";
						echo "</div>\n";
						echo "</div>\n";
						echo "<!--- end message item -->\n";
					}
					echo "</div>\n";
				} else {
					echo "<div class='text-center text-lighter m-t-20'>".$locale['467']."</div>\n";
				}
			}
			echo closeform();

			// @todo need to check if to use with endless scroll or use page navigation
			if ($info['total_rows'] > 20) echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 20, $info['total_rows'], 3, FUSION_SELF."?folder=".$_GET['folder']."&amp;")."\n</div>\n";

			// do a postbox for new messages here.
			if ($info['total_rows'] && isset($_GET['msg_user']) && !empty($this_selected)) {
				if (!isset($_GET['msg_read']) && $_GET['folder'] !== 'outbox' && $_GET['folder'] !== 'archive') {
					echo "<hr class='m-t-0'/>";
					if ($info['total_rows']) echo openform('qform', 'qform', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder']."".(isset($_GET['msg_user']) ? "&msg_user=".$_GET['msg_user']."" : ''));
					echo "<div class='p-10'>\n";
					echo "<div class='m-b-10 strong'>".sprintf($locale['468'], profile_link($this_selected['user_id'], $this_selected['user_name'], $this_selected['user_status']))."</div>\n";
					echo form_text('', 'subject', 'subject', '', array('placeholder' => $locale['405'], 'resize' => 0, 'autosize' => 1));
					echo "<hr class='m-t-0'/><br/>\n";
					echo form_textarea('', 'message', 'message', '', array('placeholder' => $locale['422'], 'resize' => 0, 'autosize' => 1, 'bbcode' => 1, 'form_name' => 'qform', 'preview' => 1));
					echo form_hidden('', 'msg_send', 'msg_send', $_GET['msg_user']);
					echo form_button($locale['430'], 'send_message', 'send_message', $locale['430'], array('class' => 'btn btn-primary btn-sm'));
					echo "</div>\n";
					echo closeform();

				} elseif (isset($_GET['msg_read']) && $_GET['folder'] !== 'outbox' && $_GET['folder'] !== 'archive') {

					echo "<hr class='m-t-0'/>";
					if ($info['total_rows']) echo openform('qform', 'qform', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').FUSION_SELF."?folder=".$_GET['folder'].(isset($_GET['msg_user']) ? "&msg_user=".$_GET['msg_user']."" : '').(isset($_GET['msg_read']) ? "&msg_read=".$_GET['msg_read']."" : ''));
					echo "<div class='p-10'>\n";
					echo "<div class='m-b-10 strong'>".sprintf($locale['469'], profile_link($this_selected['user_id'], $this_selected['user_name'], $this_selected['user_status']))."</div>\n";
					$message_subject = '';
					foreach ($info['message'] as $data) {
						if ($data['message_id'] == $_GET['msg_read']) {
							$message_subject = $data['message_subject'];
							break;
						}
					}
					//print_p($message_subject);
					// var_dump($message_subject);
					echo form_textarea('', 'message', 'message', '', array('placeholder' => $locale['422'], 'resize' => 0, 'autosize' => 1, 'bbcode' => 1, 'form_name' => 'qform', 'preview' => 1));
					echo form_hidden('', 'subject', 'subject', $message_subject);
					echo form_hidden('', 'msg_send', 'msg_send', $_GET['msg_user']);
					echo form_button($locale['430'], 'send_message', 'send_message', $locale['430'], array('class' => 'btn btn-primary btn-sm'));
					echo "</div>\n";
					echo closeform();
				}
			}
			echo "</div></div>\n";
		} else {
			// Options section
			echo "</div></div>\n";
			echo openform('pm_form', 'pm_form', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder']);
			echo "<div class='option-form m-t-20'>\n";
			echo form_button($locale['623'], 'save_options', 'save_options', $locale['623'], array('class' => 'btn btn-sm btn-primary pull-right'));
			echo form_toggle($locale['621'], 'pm_email_notify', 'pm_email_notify', array($locale['632'], $locale['631']), $info['pm_email_notify']);
			echo form_toggle($locale['622'], 'pm_save_sent', 'pm_save_sent', array($locale['632'], $locale['631']), $info['pm_save_sent']);
			echo form_hidden('', 'update_type', 'update_type', $info['update_type']);
			echo "</div>\n";
			echo closeform();
		}
		closetable();
	}
}

?>