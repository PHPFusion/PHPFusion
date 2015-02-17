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
		add_to_head("<link href='".THEMES."templates/global/css/messages.css' rel='stylesheet'/>\n");
		global $locale, $userdata, $settings, $msg_settings;
		opentable($locale['400']);
		echo "<div class='row'>\n";
		/* Start Left Column */
		echo "<div class='left_pm col-xs-12 col-sm-4 col-md-4 col-lg-4 p-t-20 p-l-0 p-r-0'>\n";
		echo "<div class='btn-group'>\n";
		echo "<a href='".FUSION_SELF."?folder=inbox' class='btn btn-sm btn-default text-dark ".($_GET['folder'] == "inbox" ? "active" : '')."'>".$locale['402']." (".$info['inbox_total'].")</a>";
		echo "<a href='".FUSION_SELF."?folder=outbox'  class='btn btn-sm btn-default text-dark ".($_GET['folder'] == "outbox" ? "active" : '')."'>".$locale['403']." (".$info['outbox_total'].")</a>";
		echo "<a href='".FUSION_SELF."?folder=archive'  class='btn btn-sm btn-default text-dark ".($_GET['folder'] == "archive" ? "active" : '')."'>".$locale['404']." (".$info['archive_total'].")</a>";
		echo "</div>\n";
		/* Progress bars */
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
		if (isset($_GET['folder']) && $_GET['folder'] !=='options') {
			render_chat_list($info);
		}
		/* End Left Column */

		/* Start Right Column */
		echo "</div><div class='right_pm col-xs-12 col-sm-8 col-md-8 col-lg-8 p-0'>\n";
		echo "<div class='msg_header_bar clearfix p-10 p-t-10'>\n";
		echo "<div class='pull-right m-t-10'>\n";
		echo "<a class='btn btn-sm btn-primary text-white m-r-10' href='".$info['button']['new']['link']."'><i class='entypo plus-circled'></i>".$info['button']['new']['name']."</a>\n";
		echo "<a class='btn btn-sm btn-default text-dark ".($_GET['folder'] == "options" ? "active" : '')."' href='".$info['button']['options']['link']."'>".$info['button']['options']['name']."</a>";
		echo "</div>\n";

		/* Channel title */
		echo "<div class='overflow-hide'>\n";
		//if ($_GET['folder'] != 'options') {
			echo "<span class='channel_title'>\n";
			if (isset($_GET['msg_send']) && isnum($_GET['msg_send'])) {
				echo $locale['420'];
			} elseif (isset($_GET['msg_user'])) {
				echo 'All conversation with '.$info['channel'];
			} else {
				echo $info['channel'];
			}
			echo "</span>\n";
		//}
		echo "</div>\n</div>\n";
		// action buttons
		if ($info['chat_rows'] && isset($_GET['msg_user']) or isset($_GET['msg_read'])) {
			echo openform('inputform', 'inputform', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder'].(isset($_GET['msg_user']) ? "&msg_user=".$_GET['msg_user']."" : '').(isset($_GET['msg_read']) ? "&msg_read=".$_GET['msg_read']."" : ''), array('notice' => 0, 'downtime' => 1));
				echo "<div class='msg_buttons_bar clearfix p-10'>\n";
				if (isset($_GET['msg_user']) && $_GET['folder'] == 'inbox' &&  !isset($_GET['msg_read'])) {
					echo "<div class='btn-group pull-right'>\n";
					if ($_GET['folder'] == "inbox") echo form_button($locale['412'], 'save_msg', 'save_msg', $locale['412'], array('class' => 'btn btn-sm btn-default'));
					echo form_button($locale['414'], 'read_msg', 'read_msg', $locale['414'], array('class' => 'btn-sm btn-default'));
					echo form_button($locale['415'], 'unread_msg', 'unread_msg', $locale['415'], array('class' => 'btn-sm btn-default'));
					echo form_button($locale['416'], 'delete_msg', 'delete_msg', $locale['416'], array('class' => 'btn-sm btn-default'));
					echo "</div>\n";
					echo "<div class='btn-group'>\n";
					echo form_button($locale['410'], 'setcheck_all', 'setcheck_all', $locale['410'], array('class'=>'btn-sm btn-default', 'type'=>'button'));
					echo form_button($locale['411'], 'setcheck_none', 'setcheck_none', $locale['410'], array('class'=>'btn-sm btn-default', 'type'=>'button'));
					echo "</div>\n";
				} elseif (isset($_GET['msg_read'])) {
					echo "<div class='btn-group'>\n";
					if ($_GET['folder'] == "inbox") {
						echo form_button($locale['412'], 'save', 'save', $locale['412'], array('class' => 'btn btn-sm btn-default'));
					}
					echo form_button($locale['416'], 'delete', 'delete', $locale['416'], array('class' => 'btn btn-sm btn-default'));
					echo "</div>\n";
				}
				echo "</div>\n";
				//add_to_jquery("$('#delete').bind('click', function() {	confirm('".$locale['470']."');	return false; });");
			}
		// Send Message
		if (isset($_GET['msg_send']) && $_GET['msg_send'] == 0) {
				// New Form
				echo "<div class='msg-form m-t-20 p-l-10 p-r-10'>\n";
				echo openform('inputform', 'inputform', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder']."&amp;msg_send=".$_GET['msg_send']."", array('downtime' => 1));
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
				echo closeform();
			}
		// Read Message
		elseif (isset($_GET['msg_read'])) {

				echo "<div id='msg-container' class='p-10 clearfix'>\n";

				if (!empty($info['message'])) {
					$i = 0;
					echo "<p><span class='strong'>Subject :</strong> ".censorwords($info['message'][0]['message_subject'])."</p>";
					foreach ($info['message'] as $date => $mdata) {
						echo "<!--- start message item -->\n";
						echo "<div class='list-group-item clearfix p-b-10'>\n";
						echo "<div class='pull-left m-r-5'>\n";
						echo display_avatar($mdata, '50px');
						echo "</div>\n";
						echo "<div class='overflow-hide'>\n";
						echo "<span class='pull-right text-smaller'>".date('d/m/y, h:i a', $mdata['message_datestamp'])."</span>\n";
						echo profile_link($mdata['user_id'], $mdata['user_name'], $mdata['user_status']);
						echo "<p>".fusion_first_words(censorwords(parseubb(parsesmileys($mdata['message_message']))), 50)."</p>";
						echo "</span>\n";
						echo "</div>\n";
						echo "</div>\n";
						echo "<!--- end message item -->\n";
						$i++;
					}
				}
				echo "</div>\n";

			}
		/* Index view after selecting channel */
		elseif (isset($_GET['msg_user']) && isnum($_GET['msg_user'])) {
			// Listing of Subjects.
			if (!empty($info['item'])) {
				echo "<div id='msg-container' class='p-10 clearfix'>\n";
				echo "<span class='channel_title'>".$locale['431']."</span>\n";
				foreach ($info['item'] as $messages) {
					echo "<!--- start message item -->\n";
					echo "<div class='list-group-item clearfix m-b-10 m-t-10'>\n";
						echo "<div class='pull-left m-r-5'>\n".display_avatar($messages, '40px', '', TRUE, '')."</div>\n";
						echo "<div class='overflow-hide'>\n";
						echo "<input class='checkbox pull-right' type='checkbox' name='check_mark[]' value='".$messages['message_id']."' />";
						echo "<span class='pull-right m-r-10'>".date('d/m/y, h:i a', $messages['message_datestamp'])."</span>\n";
						echo "<span class='strong'><a href='".BASEDIR."messages.php?folder=".$_GET['folder']."&msg_user=".$_GET['msg_user']."&amp;msg_read=".$messages['message_id']."'>".censorwords($messages['message_subject'])."</a></span><br/>";
						echo "<p>".censorwords(parseubb(parsesmileys($messages['message_message'])))."</p>";
						echo "</span>\n";
						echo "</div>\n";
						echo "</div>\n";
						echo "<!--- end message item -->\n";
					}
					echo "</div>\n";
				} else {
					echo "<div class='well text-center text-dark m-t-20'>".$locale['467']."</div>\n";
				}
			echo closeform();
		}

		elseif (isset($_GET['folder']) && $_GET['folder'] == 'options') {

			echo "<div class='list-group-item' style='margin:15px;'>\n";
			echo openform('pm_form', 'pm_form', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder'], array('downtime' => 1));
			echo form_checkbox($locale['621'], 'pm_email_notify', 'pm_email_notify', $info['pm_email_notify']);
			echo form_checkbox($locale['622'], 'pm_save_sent', 'pm_save_sent', $info['pm_save_sent']);
			echo form_button($locale['623'], 'save_options', 'save_options', $locale['623'], array('class' => 'btn btn-sm btn-default'));
			echo closeform();
			echo "</div>\n";

		}

		else {
			echo "<div class='text-center p-15'>".$locale['471']."</div>\n";
		}

		// @todo need to check if to use with endless scroll or use page navigation
		//if ($info['chat_rows'] > 20) echo "<div align='center' class='m-t-5'>\n".makepagenav($_GET['rowstart'], 20, $info['chat_rows'], 3, FUSION_SELF."?folder=".$_GET['folder']."&amp;")."\n</div>\n";

		// do a postbox for new messages here.
		if ($info['chat_rows'] && isset($_GET['msg_user'])) {
				if (!isset($_GET['msg_read']) && $_GET['folder'] !== 'outbox' && $_GET['folder'] !== 'archive') {
					echo "<hr class='m-t-0'/>";
					echo "</form>\n";
					if ($info['chat_rows']) echo openform('qform', 'qform', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder']."".(isset($_GET['msg_user']) ? "&msg_user=".$_GET['msg_user']."" : ''), array('downtime' => 1));
					echo "<div class='p-10'>\n";
					echo "<div class='m-b-10 strong'>".sprintf($locale['468'], $info['channel'])."</div>\n";
					echo form_text('', 'subject', 'subject', '', array('placeholder' => $locale['405'], 'resize' => 0, 'autosize' => 1));
					echo "<hr class='m-t-0'/><br/>\n";
					echo form_textarea('', 'message', 'message', '', array('placeholder' => $locale['422'], 'resize' => 0, 'autosize' => 1, 'bbcode' => 1, 'form_name' => 'qform', 'preview' => 1));
					echo form_hidden('', 'msg_send', 'msg_send', $_GET['msg_user']);
					echo form_button($locale['430'], 'send_message', 'send_message', $locale['430'], array('class' => 'btn btn-primary btn-sm'));
					echo "</div>\n";
					echo closeform();

				} elseif (isset($_GET['msg_read']) && $_GET['folder'] !== 'outbox' && $_GET['folder'] !== 'archive') {

					echo "<hr class='m-t-0'/>";
					echo "</form>\n";
					if ($info['chat_rows']) echo openform('qform', 'qform', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').FUSION_SELF."?folder=".$_GET['folder'].(isset($_GET['msg_user']) ? "&msg_user=".$_GET['msg_user']."" : '').(isset($_GET['msg_read']) ? "&msg_read=".$_GET['msg_read']."" : ''), array('downtime' => 1));
					echo "<div class='p-10'>\n";
					echo "<div class='m-b-10 strong'>".sprintf($locale['469'], $info['channel'])."</div>\n";
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
		closetable();
	}
}

if (!function_exists('render_chat_list')) {
	function render_chat_list($info) {
		global $locale;
		echo "<div class='msg-list-item list-group'>\n";
		if ($info['chat_rows'] > 0) {
			foreach ($info['chat_list'] as $contact_id => $chat_list) {
				echo "<!--- start message list -->\n";
				echo "<div class='list-group-item clearfix ".(isset($_GET['msg_user']) && $_GET['msg_user'] == $chat_list['contact_id'] ? 'active' : '')." bbr-0 br-l-0 br-r-0'>\n";
				echo "<div class='pull-left m-r-10'>\n".display_avatar($chat_list['contact_user'], '40px', '', TRUE, '')." </div>\n";
				echo "<div class='overflow-hide'>";
				echo "<span class='profile_link'>".profile_link($chat_list['contact_user']['user_id'], $chat_list['contact_user']['user_name'], $chat_list['contact_user']['user_status'])."</span><span class='text-smaller'> - ".date('d M', $chat_list['message_datestamp'])."</span><br/>";
				echo "<a href='".$chat_list['message']['link']."' class='display-inline-block ".($chat_list['message_read'] > 0 ? 'text-dark text-normal' : '')."'>".trimlink($chat_list['message']['name'], 50)."</a>\n";
				echo "</div>\n";
				echo "</div>\n";
				echo "<!--- end message list -->\n";
			}
		} else {
			echo "<div class='list-group-item text-center bbr-0 br-0'>".$locale['461']."</div>";
		}
		echo "</div>\n";
	}
}
?>