<?php
namespace PHPFusion;
if (!defined("IN_FUSION")) {
	die("Access Denied");
}

class PrivateMessages {

	private $info = array();

	/**
	 * @param array $info
	 */
	public function setInfo($info) {
		$this->info = $info;
	}

	/**
	 * Update and consolidate user's pm settings
	 * @param $user_id
	 * @return array|bool
	 */
	private static function get_pm_settings($user_id) {
		$user_pm_settings = array(
			"user_id" => $user_id,
			"pm_inbox" => fusion_get_settings("pm_inbox_limit"),
			"pm_savebox" => fusion_get_settings("pm_archive_limit"),
			"pm_sentbox" => fusion_get_settings("pm_outbox_limit"),
		);
		// auto update if account existed
		if (dbcount("(user_id)", DB_MESSAGES_OPTIONS, "user_id='".intval($user_id)."'")) {
			// update existing row
			dbquery_insert(DB_MESSAGES_OPTIONS, $user_pm_settings, "update", array("keep_session" => TRUE));
		} else {
			// create a new row
			$user_pm_settings += array(
				"pm_email_notify" => fusion_get_settings("pm_email_notify"),
				"pm_save_sent" => fusion_get_settings("pm_save_sent")
			);
			dbquery_insert(DB_MESSAGES_OPTIONS, $user_pm_settings, "save", array("keep_session" => TRUE));
		}
		// fetch configuration again
		$result = dbquery("select * from ".DB_MESSAGES_OPTIONS." WHERE user_id='".intval($user_id)."'");
		if (dbrows($result) > 0) {
			$user_pm_settings = dbarray($result);
		}
		// hardcode to override
		if (iADMIN || iSUPERADMIN) {
			// override again
			$user_pm_settings['pm_inbox'] = 0;
			$user_pm_settings['pm_savebox'] = 0;
			$user_pm_settings['pm_sentbox'] = 0;
		}
		return $user_pm_settings;
	}

	/**
	 * Actions : archive
	 * Require - $_POST selectedPM, delete_pm
	 * SQL archive action
	 */
	private function archive_pm() {
		global $userdata;
		$messages = explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ","));
		if (!empty($messages)) {
			foreach ($messages as $message_id) {
				$ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES, "message_id='".intval($message_id)."' and message_user='".intval($userdata['user_id'])."'") ? TRUE : FALSE;
				$within_limit = ($this->user_pm_settings['pm_savebox'] > 0 && $this->user_pm_settings['pm_savebox'] > $this->info['archive_total']) ? TRUE : FALSE;
				if ((iADMIN || iSUPERADMIN ? "" : $ownership && $within_limit) && isset($this->info['items'][$message_id])) {
					$moveData = $this->info['items'][$message_id];
					$moveData['message_folder'] = 2;
					dbquery_insert(DB_MESSAGES, $moveData, 'update');
					addNotice("success", "Private message is archived");
					redirect(clean_request("", array("folder"), TRUE));
				}
			}
		}
	}

	/**
	 * Actions: delete
	 * Require - $_POST selectedPM, delete_pm
	 * SQL delete message pm
	 */
	private function delete_pm() {
		global $userdata;
		$messages = explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ","));
		if (!empty($messages)) {
			foreach ($messages as $message_id) {
				$ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES, "message_id='".intval($message_id)."' and message_user='".intval($userdata['user_id'])."'") ? TRUE : FALSE;
				if ((iADMIN || iSUPERADMIN ? "" : $ownership) && isset($this->info['items'][$message_id])) {
					$moveData = $this->info['items'][$message_id];
					dbquery_insert(DB_MESSAGES, $moveData, 'delete');
					addNotice("success", "Private message deleted");
					redirect(clean_request("", array("folder"), TRUE));
				}
			}
		}
	}

	/**
	 * Reply and send
	 * SQL send pm
	 */
	private function send_message() {
		global $userdata;
		$inputData = array(
			"from" => $userdata['user_id'],
			"to" => form_sanitizer($_POST['msg_send'], '', 'msg_send'),
			"subject" => form_sanitizer($_POST['subject'], '', 'subject'),
			"message" => form_sanitizer($_POST['message'], '', 'message'),
			"smileys" => isset($_POST['chk_disablesmileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $_POST['message']) ? "n" : "y",
		);
		if (\defender::safe()) {
			if (iADMIN && isset($_POST['chk_sendtoall']) && $_POST['msg_to_group']) {
				send_pm($inputData['to'], $inputData['from'], $inputData['subject'], $inputData['message'], $inputData['smileys'], TRUE);
			} else {
				send_pm($inputData['to'], $inputData['from'], $inputData['subject'], $inputData['message'], $inputData['smileys']);
			}
			addNotice("success", "Message Sent");
			redirect(BASEDIR."messages.php");
		}
	}

	// 9.0 new - send pm core functions -- add send to group using recursive statement.
	public static function send_pm($to, $from, $subject, $message, $smileys = 'y', $to_group = FALSE) {
		$locale = array();
		include LOCALE.LOCALESET."messages.php";
		require_once INCLUDES."sendmail_include.php";
		require_once INCLUDES."flood_include.php";
		// ensure to and from is always integer
		$to = intval($to);
		$from = intval($from);
		$smileys = preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message) ? "n" : $smileys;

		if (!$to_group) {
			// send to user
			$pmStatus = self::get_pm_settings($to);
			if (!flood_control("message_datestamp", DB_MESSAGES, "message_from='".intval($from)."'")) {
				// find receipient
				$result = dbquery("SELECT u.user_id, u.user_name, u.user_email, u.user_level,
				mo.pm_email_notify,
				COUNT(m.message_id) 'message_count'
				FROM ".DB_USERS." u
				LEFT JOIN ".DB_MESSAGES_OPTIONS." mo USING(user_id)
				LEFT JOIN ".DB_MESSAGES." m ON m.message_user=u.user_id and message_folder='0'
				WHERE u.user_id='".intval($to)."' GROUP BY u.user_id");
				if (dbrows($result) > 0) {
					$data = dbarray($result);
					// get from
					$result = dbquery("SELECT user_id, user_name FROM ".DB_USERS." WHERE user_id='".$from."'");
					if (dbrows($result)) {
						$userdata = dbarray($result);
						if ($to != $from) {
							if ($data['user_id'] == 1 // recepient is SA
								|| $data['user_level'] < USER_LEVEL_MEMBER || //recepient is Admin
								$pmStatus['pm_inbox'] == "0" || // recepient pm inbox is clean
								($data['message_count']+1) <= $pmStatus['pm_inbox'] // recepient inbox still within limit
							) {
								$inputData = array(
									"message_id" => 0,
									"message_to" => $to,
									"message_user" => $to,
									"message_from" => 2,
									"message_subject" => $subject,
									"message_message" => $message,
									"message_smileys" => $smileys,
									"message_read" => 0,
									"message_datestamp" => time(),
									"message_folder" => 0,
								);
								//print_p($inputData);
								dbquery_insert(DB_MESSAGES, $inputData, "save");
								$send_email = isset($data['pm_email_notify']) ? $data['pm_email_notify'] : $pmStatus['pm_email_notify'];
								if ($send_email == "1") {
									$message_content = str_replace("[SUBJECT]", $subject, $locale['626']);
									$message_content = str_replace("[USER]", $userdata['user_name'], $message_content);
									$template_result = dbquery("SELECT template_key, template_active FROM ".DB_EMAIL_TEMPLATES." WHERE template_key='PM' LIMIT 1");
									if (dbrows($template_result)) {
										$template_data = dbarray($template_result);
										if ($template_data['template_active'] == "1") {
											sendemail_template("PM", $subject, trimlink($message, 150), $userdata['user_name'], $data['user_name'], "", $data['user_email']);
										} else {
											sendemail($data['user_name'], $data['user_email'], fusion_get_settings("siteusername"), fusion_get_settings("siteemail"), $locale['625'], $data['user_name'].$message_content);
										}
									} else {
										sendemail($data['user_name'], $data['user_email'], fusion_get_settings("siteusername"), fusion_get_settings("siteemail"), $locale['625'], $data['user_name'].$message_content);
									}
								}
							} else {
								// Inbox is full
								addNotice("danger", $locale['628']);
							}
						} else {
							// Reciever and sender are the same user
							addNotice("danger", $locale['482']);
						}
					} else {
						// Sender does not exist in DB
						addNotice("danger", $locale['482']);
					}
				} else {
					// recepient not found
					addNotice("danger", $locale['482']);
				}
			} else {
				// flooding
				addNotice("danger", sprintf($locale['487'], fusion_get_settings("flood_interval")));
			}
		} else {
			$result = null;
			if ($to <= -101 && $to >= -103) { // -101, -102, -103 only
				$result = dbquery("SELECT u.user_id, u.user_name, u.user_email, mo.pm_email_notify FROM ".DB_USERS." u
				LEFT JOIN ".DB_MESSAGES_OPTIONS." mo USING(user_id)
				WHERE user_level <='".intval($to)."' AND user_status='0'");
			} else {
				$result = dbquery("SELECT u.user_id, u.user_name, u.user_email, mo.pm_email_notify FROM ".DB_USERS." u
				LEFT JOIN ".DB_MESSAGES_OPTIONS." mo USING(user_id)
				WHERE ".in_group("user_groups", $to)."
				## --- deprecate -- WHERE user_groups REGEXP('^\\\.{$to}$|\\\.{$to}\\\.|\\\.{$to}$') #
				AND user_status='0'");
			}
			if (dbrows($result) > 0) {
				while ($data = dbarray($result)) {
					self::send_pm($data['user_id'], $from, $subject, $message, $smileys, FALSE);
				}
			} else {
				addNotice("danger", "There are no recepients in this group.");
			}
		}
	}

	/**
	 * Actions : marking
	 * Require - $_POST selectedPM, mark
	 * SQL mark all, mark single (read or unread)
	 */
	private function mark_pm() {
		global $userdata;
		switch (form_sanitizer($_POST['mark'], "")) {
			case "mark_all": // mark all as read
				if (!empty($this->info['items'])) {
					foreach ($this->info['items'] as $message_id => $array) {
						$ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES, "message_id='".intval($message_id)."' and message_user='".intval($userdata['user_id'])."'") ? TRUE : FALSE;
						if ((iADMIN || iSUPERADMIN ? "" : $ownership) && isset($this->info['items'][$message_id])) {
							dbquery("UPDATE ".DB_MESSAGES." SET message_read='1' WHERE message_id='".intval($message_id)."'");
						}
					}
					redirect(clean_request("", array("folder"), TRUE));
				}
				break;
			case "unmark_all": // mark all as unread
				if (!empty($this->info['items'])) {
					foreach ($this->info['items'] as $pm_id => $pmData) {
						dbquery("UPDATE ".DB_MESSAGES." SET message_read='0' WHERE message_id='".intval($pm_id)."'");
					}
					redirect(clean_request("", array("folder"), TRUE));
				}
				break;
			case "mark_read":
				$messages = explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ","));
				if (!empty($messages)) {
					foreach ($messages as $message_id) {
						$ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES, "message_id='".intval($message_id)."' and message_user='".intval($userdata['user_id'])."'") ? TRUE : FALSE;
						if ((iADMIN || iSUPERADMIN ? "" : $ownership) && isset($this->info['items'][$message_id])) {
							dbquery("UPDATE ".DB_MESSAGES." SET message_read='1' WHERE message_id='".intval($message_id)."'");
						}
					}
				}
				redirect(clean_request("", array("folder"), TRUE));
				break;
			case "mark_unread":
				$messages = explode(",", rtrim(form_sanitizer($_POST['selectedPM'], "", "selectedPM"), ","));
				if (!empty($messages)) {
					foreach ($messages as $message_id) {
						$ownership = isnum($message_id) && dbcount("(message_id)", DB_MESSAGES, "message_id='".intval($message_id)."' and message_user='".intval($userdata['user_id'])."'") ? TRUE : FALSE;
						if ((iADMIN || iSUPERADMIN ? "" : $ownership) && isset($this->info['items'][$message_id])) {
							dbquery("UPDATE ".DB_MESSAGES." SET message_read='0' WHERE message_id='".intval($message_id)."'");
						}
					}
				}
				redirect(clean_request("", array("folder"), TRUE));
		}
	}

	// there are 5 parts in PM
	public function display_inbox() {
		global $locale, $userdata;
		add_to_title($locale['global_201'].$locale['402']);

		// Get messages
		if ($this->info['inbox_total'] > 0) {
			$result = dbquery("SELECT m.*,
			u.user_id, u.user_name, u.user_status, u.user_avatar,
			max(m.message_id) as last_message
			FROM ".DB_MESSAGES." m
			LEFT JOIN ".DB_USERS." u ON (m.message_from=u.user_id)
			WHERE message_to='".$userdata['user_id']."' and message_folder='0'
			group by message_id
			ORDER BY m.message_datestamp DESC
			");
			$this->info['max_rows'] = dbrows($result);
			if ($this->info['max_rows']) {
				while ($data = dbarray($result)) {
					$data['contact_user'] = array(
						'user_id' => $data['user_id'],
						'user_name' => $data['user_name'],
						'user_status' => $data['user_status'],
						'user_avatar' => $data['user_avatar']
					);
					$data['message'] = array(
						"link" => BASEDIR."messages.php?folder=inbox&amp;msg_read=".$data['message_id'],
						"name" => $data['message_subject'],
						"message_header" => "<strong>".$locale['462'].":</strong> ".$data['message_subject'],
						"message" => parseubb(parsesmileys($data['message_message'])),
					);
					$this->info['items'][$data['message_id']] = $data;
				}
			}
		} else {
			$this->info['no_item'] = $locale['471'];
		}

		// Message Actions
		if (isset($_POST['archive_pm'])) {
			$this->archive_pm();
		} elseif (isset($_POST['delete_pm'])) {
			$this->delete_pm();
		} elseif (isset($_POST['mark'])) {
			$this->mark_pm();
		}

		// The UI actions

		// Actions buttons - archive, delete, mark all read, mark all unread, mark as read, mark as unread
		ob_start();
		if (isset($_GET['msg_read'])) {
			echo openform("actionform", "post", (fusion_get_settings("site_seo") ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder'].(isset($_GET['msg_read']) ? "&amp;msg_read=".$_GET['msg_read'] : ""), array("class" => "display-inline-block m-l-10"));
			echo form_hidden("selectedPM", "", $_GET['msg_read']);
			echo "<div class='btn-group display-inline-block m-r-10'>\n";
			echo form_button("archive_pm", "", "archive_pm", array("icon" => "fa fa-archive"));
			echo form_button("delete_pm", "", "delete_pm", array("icon" => "fa fa-trash-o"));
			echo "</div>\n";
			echo closeform();
		} else {
			echo openform("actionform", "post", (fusion_get_settings("site_seo") ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder'].(isset($_GET['msg_read']) ? "&amp;msg_read=".$_GET['msg_read'] : ""));
			?>
			<!-- pm_idx -->
			<div class="dropdown display-inline-block m-r-10">
				<a href="#" data-toggle="dropdown" class="btn btn-default dropdown-toggle"><i id="chkv"
																							  class="fa fa-square-o"></i>
					<span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li><a id="check_all_pm" data-action="check" class="pointer">All</a></li>
					<li><a id="check_unread_pm" data-action="check" class="pointer">Unread</a></li>
					<li><a id="check_read_pm" data-action="check" class="pointer">Read</a></li>
				</ul>
			</div>
			<?php
			echo form_hidden("selectedPM", "", "");
			echo "<div class='btn-group display-inline-block m-r-10'>\n";
			echo form_button("archive_pm", "", "archive_pm", array("icon" => "fa fa-archive"));
			echo form_button("delete_pm", "", "delete_pm", array("icon" => "fa fa-trash-o"));
			echo "</div>\n";
			?>
			<div class="dropdown display-inline-block m-r-10">
				<a href="#" data-toggle="dropdown" class="btn btn-default dropdown-toggle">More... <span
						class="caret"></span></a>
				<ul class="dropdown-menu">
					<li><?php echo form_button("mark", "Mark All as Read", "mark_all", array("class" => "btn-link")) ?></li>
					<li><?php echo form_button("mark", "Mark as Read", "mark_read", array("class" => "btn-link")) ?></li>
					<li><?php echo form_button("mark", "Mark as Unread", "mark_unread", array("class" => "btn-link")) ?></li>
					<li><?php echo form_button("mark", "Mark All as Unread", "unmark_all", array("class" => "btn-link")) ?></li>
				</ul>
			</div>
			<?php
			echo closeform();
			add_to_jquery("
		function checkedCheckbox() {
			var checkList = '';
			$('input[type=checkbox]').each(function() {
				if (this.checked) {
					checkList += $(this).val()+',';
				}
			});
			return checkList;
		}
		$('#check_all_pm').bind('click', function() {
			var unread_checkbox = $('#unread_tbl tr').find(':checkbox');
			var read_checkbox = $('#read_tbl tr').find(':checkbox');
			var action = $(this).data('action');
			if (action == 'check') {
				unread_checkbox.prop('checked', true);
				read_checkbox.prop('checked', true);
				$('#unread_tbl tr').addClass('warning');
				$('#read_tbl tr').addClass('warning');
				$('#chkv').removeClass('fa fa-square-o').addClass('fa fa-minus-square-o');
				$(this).data('action', 'uncheck');
				$('#selectedPM').val(checkedCheckbox());
			} else {
				unread_checkbox.prop('checked', false);
				read_checkbox.prop('checked', false);
				$('#unread_tbl tr').removeClass('warning');
				$('#read_tbl tr').removeClass('warning');
				$('#chkv').removeClass('fa fa-minus-square-o').addClass('fa fa-square-o');
				$(this).data('action', 'check');
				$('#selectedPM').val(checkedCheckbox());
			}
		});
		$('#check_read_pm').bind('click', function() {
			var read_checkbox = $('#read_tbl tr').find(':checkbox');
			var action = $(this).data('action');
			if (action == 'check') {
				read_checkbox.prop('checked', true);
				$('#read_tbl tr').addClass('warning');
				$('#chkv').removeClass('fa fa-square-o').addClass('fa fa-minus-square-o');
				$(this).data('action', 'uncheck');
				$('#selectedPM').val(checkedCheckbox());
			} else {
				read_checkbox.prop('checked', false);
				$('#read_tbl tr').removeClass('warning');
				$('#chkv').removeClass('fa fa-minus-square-o').addClass('fa fa-square-o');
				$(this).data('action', 'check');
				$('#selectedPM').val(checkedCheckbox());
			}
		});
		$('#check_unread_pm').bind('click', function() {
			var unread_checkbox = $('#unread_tbl tr').find(':checkbox');
			var action = $(this).data('action');
			if (action == 'check') {
				unread_checkbox.prop('checked', true);
				$('#unread_tbl tr').addClass('warning');
				$('#chkv').removeClass('fa fa-square-o').addClass('fa fa-minus-square-o');
				$(this).data('action', 'uncheck');
				$('#selectedPM').val(checkedCheckbox());
			} else {
				unread_checkbox.prop('checked', false);
				$('#unread_tbl tr').removeClass('warning');
				$('#chkv').removeClass('fa fa-minus-square-o').addClass('fa fa-square-o');
				$(this).data('action', 'check');
				$('#selectedPM').val(checkedCheckbox());
			}
		});
		");
			add_to_jquery("
		$('input[type=checkbox]').bind('click', function() {
			var checkList = $('#selectedPM').val();
			if ($(this).is(':checked')) {
			$(this).parents('tr').addClass('warning');
				checkList += $(this).val()+',';
			} else {
				$(this).parents('tr').removeClass('warning');
				checkList = checkList.replace($(this).val()+',', '');
			}
			$('#selectedPM').val(checkList);
		});
		");
		}
		$this->info['actions_form'] = ob_get_contents();
		ob_end_clean();

		// The mail forms
		if (isset($_GET['msg_read']) || isset($_GET['msg_send'])) {
			print_p($_POST);
			if (isset($_POST['send_pm'])) {
				$this->send_message();
			}
			if (isset($_GET['msg_read'])) {
				$this->display_msg_send_short();
			} elseif (isset($_GET['msg_send'])) {
				$this->display_msg_send();
			}
		}

		// Run template
		render_mailbox($this->info);
	}

	public function display_msg_send_short() {
		global $locale;
		$this->info['button'] += array(
			"back" => array("link" => BASEDIR."messages.php?folder=inbox", "title" => $locale['back']),
		);
		// require a message category - to indicate as a reply to:
		$this->info['reply_form'] = openform('inputform', 'post', (fusion_get_settings("site_seo") ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder']."&amp;msg_read=".$_GET['msg_read']).form_textarea('message', 'message', '', array(
				'required' => 1,
				'error_text' => '',
				'autosize' => 1,
				'no_resize' => 0,
				'preview' => 1,
				'form_name' => 'inputform',
				'bbcode' => 1
			)).form_button('cancel', $locale['cancel'], $locale['cancel']).form_button('send_message', $locale['430'], $locale['430'], array(
				'class' => 'btn btn-sm m-l-10 btn-primary'
			)).closeform();
	}

	/**
	 * Private message form
	 */
	public function display_msg_send() {
		global $locale;

		if (iADMIN) {
			$input_header = "<a class='pull-right m-b-10 display-inline-block' id='mass_send'>".$locale['434']."</a><br/>";
			$input_header .= form_user_select('msg_send', '', $_GET['msg_send'], array('placeholder' => $locale['421']));
			$input_header .= "<div id='msg_to_group-field' class='form-group display-none'>\n";
			$input_header .= "<label for='mg_to_group' class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0'>".$locale['434']."
								<input id='all_check' name='chk_sendtoall' type='checkbox' class='pull-left display-inline-block'
							   style='margin-right:10px !important;'/></label>\n";
			$input_header .= "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n";
			$user_groups = fusion_get_groups();
			unset($user_groups[0]);
			$input_header .= form_select('msg_to_group', '', '', array(
				'options' => $user_groups,
				'width' => "100%",
				'class' => 'm-b-0'
			));
			$input_header .= "</div>\n</div>\n";
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
		} else {
			$input_header = form_user_select('msg_send', "Recepient", isset($_GET['msg_send']) && isnum($_GET['msg_send'] ? : ''), array(
				"required" => TRUE,
				'input_id' => 'msgsend2',
				"inline" => TRUE,
				'placeholder' => $locale['421']
			));
		}
		$this->info['reply_form'] = openform('inputform', 'post', (fusion_get_settings("site_seo") ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder']."&amp;msg_send=".$_GET['msg_send']).$input_header.form_text('subject', "Subject", '', array(
				"required" => TRUE,
				"inline" => TRUE,
				'max_length' => 32,
				"width" => "100%",
				'placeholder' => $locale['405']
			)).form_textarea('message', 'message', '', array(
				"required" => TRUE,
				'error_text' => '',
				'autosize' => 1,
				'no_resize' => 0,
				'preview' => 1,
				'form_name' => 'inputform',
				'bbcode' => 1
			)).form_button('cancel', $locale['cancel'], $locale['cancel']).form_button('send_pm', $locale['430'], $locale['430'], array(
				'class' => 'btn m-l-10 btn-primary'
			)).closeform();

	}


	public function outbox() {
	}

	public function archive() {
	}

	public function display_settings() {
		global $userdata, $locale;
		$data = $this->get_pm_settings($userdata['user_id']);
		if (isset($_POST['save_options'])) {
			$data = array(
				"user_id" => $userdata['user_id'],
				"pm_email_notify" => isset($_POST['pm_email_notify']) ? TRUE : FALSE,
				"pm_save_sent" => isset($_POST['pm_save_sent']) ? TRUE : FALSE,
			);
			dbquery_insert(DB_MESSAGES_OPTIONS, $data, "update");
			addNotice("success", $locale['445']);
			redirect(FUSION_REQUEST);
		}
		ob_start();
		echo openform('pm_form', 'post', "".(fusion_get_settings("site_seo") ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder']);
		echo form_checkbox('pm_email_notify', $locale['621'], $data['pm_email_notify']);
		echo form_checkbox('pm_save_sent', $locale['622'], $data['pm_save_sent']);
		echo form_button('save_options', $locale['623'], $locale['623'], array("class" => "btn-primary"));
		echo closeform();
		$info['options_form'] = ob_get_contents();
		ob_end_clean();
		$info += $this->info;
		render_mailbox($info);
	}
}