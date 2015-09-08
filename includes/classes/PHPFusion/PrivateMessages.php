<?php
namespace PHPFusion;
if (!defined("IN_FUSION")) {
	die("Access Denied");
}

class PrivateMessages {
	private $user_pm_settings = array(
		"pm_inbox" => 0,
		"pm_savebox" => 0,
		"pm_sentbox" => 0,
		"pm_email_notify" => 0,
	);

	public function __construct() {
		global $userdata, $locale;
		// Sanitization
		// Check if the folder name is a valid one
		if (!isset($_GET['folder']) || !preg_check("/^(inbox|outbox|archive|options)$/", $_GET['folder'])) {
			$_GET['folder'] = "inbox";
		}
		// prohibits send message to non-existing user
		function validate_user($user_id) {
			global $aidlink;
			if (isnum($user_id) && dbcount("(user_id)", DB_USERS, "user_id='".intval($user_id)."' AND user_status == '0'")) {
				return TRUE;
			}
			return FALSE;
		}

		if (isset($_POST['msg_send']) && isnum($_POST['msg_send']) && validate_user($_POST['msg_send'])) {
			$_GET['msg_send'] = $_POST['msg_send'];
		}
		// prohibits send message to non-existing group
		$user_group = fusion_get_groups();
		unset($user_group[0]);
		if (isset($_POST['msg_to_group']) && isnum($_POST['msg_to_group']) && isset($user_group[$_POST['msg_to_group']])) {
			$_GET['msg_to_group'] = $_POST['msg_to_group'];
		}
		// User PM settings
		$this->user_pm_settings = $this->get_pm_settings($userdata['user_id']);
		// UI variables
		$this->info = array(
			"folders" => array(
				"inbox" => array("link" => FUSION_SELF."?folder=inbox", "title" => $locale['402']),
				"outbox" => array("link" => FUSION_SELF."?folder=outbox", "title" => $locale['403']),
				"archive" => array("link" => FUSION_SELF."?folder=archive", "title" => $locale['404']),
				"options" => array("link" => FUSION_SELF."?folder=options", "title" => $locale['425']),
			),
			"chat_rows" => 0,
			"channel" => "",
			"inbox_total" => dbrows(dbquery("SELECT count('message_id') as total FROM ".DB_MESSAGES." WHERE message_user='".$userdata['user_id']."' AND message_folder='0' GROUP BY message_subject")),
			"outbox_total" => dbrows(dbquery("SELECT count('message_id') as total FROM ".DB_MESSAGES." WHERE message_user='".$userdata['user_id']."' AND message_folder='1' GROUP BY message_subject")),
			"archive_total" => dbrows(dbquery("SELECT message_id FROM ".DB_MESSAGES." WHERE message_user='".$userdata['user_id']."' AND message_folder='2' GROUP BY message_subject")),
			"button" => array(
				"new" => array(
					'link' => FUSION_SELF."?folder=".$_GET['folder']."&amp;msg_send=0",
					'name' => $locale['401']
				),
				"options" => array('link' => FUSION_SELF."?folder=options", 'name' => $locale['425']),
			),
		);
	}

	/**
	 * Update and consolidate user's pm settings
	 * @param $user_id
	 * @return array|bool
	 */
	private function get_pm_settings($user_id) {
		global $userdata;
		$user_pm_settings = array(
			"user_id" => $userdata['user_id'],
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

	// there are 5 parts in PM
	public function inbox() {
		global $locale, $userdata;
		add_to_title($locale['global_201'].$locale['402']);
		$this->info['button'] += array(
			"back" => array("link" => BASEDIR."messages.php?folder=inbox", "title" => $locale['back']),
		);



		if ($this->info['inbox_total'] > 0) {
			// fetch message sent to user
			$result = dbquery("SELECT m.*,
			u.user_id, u.user_name, u.user_status, u.user_avatar,
			max(m.message_id) as last_message
			FROM ".DB_MESSAGES." m
			LEFT JOIN ".DB_USERS." u ON (m.message_from=u.user_id)
			WHERE message_to='".$userdata['user_id']."'
			GROUP BY m.message_from
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
			} else {
				$this->info['no_item'] = $locale['471'];
			}
		}
		// for archive and delete - inbox_actions
		ob_start();
		echo openform("actionform", "post", (fusion_get_settings("site_seo") ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder']."&amp;msg_read=".$_GET['msg_read']);
		?>
		<!-- pm_idx -->
		<div class="dropdown display-inline-block m-r-10">
			<a href="#" data-toggle="dropdown" class="btn btn-default dropdown-toggle"><i id="chkv" class="fa fa-square-o"></i> <span class="caret"></span></a>
			<ul class="dropdown-menu">
				<li><a id="check_all_pm" data-action="check" class="pointer">All</a></li>
				<li><a id="check_unread_pm" data-action="check" class="pointer">Unread</a></li>
				<li><a id="check_read_pm" data-action="check" class="pointer">Read</a></li>
			</ul>
		</div>
		<?php

		echo form_hidden("selectedPM", "", "");
		echo "<div class='btn-group display-inline-block m-r-10'>\n";
		echo form_button("archive_pm", "", "archive_pm", array("icon"=>"fa fa-archive"));
		echo form_button("delete_pm", "", "delete_pm", array("icon"=>"fa fa-trash-o"));
		echo "</div>\n";
		?>
		<div class="dropdown display-inline-block m-r-10">
			<a href="#" data-toggle="dropdown" class="btn btn-default dropdown-toggle">More... <span
					class="caret"></span></a>
			<ul class="dropdown-menu">
				<li><a href="">Mark All as Read</a></li>
				<li><a href="">Mark as Read</a></li>
				<li><a href="">Mark as Unread</a></li>
				<li><a href="">Mark All as Unread</a></li>
			</ul>
		</div>
		<?php

		echo closeform();
		$this->info['actions_form'] = ob_get_contents();
		ob_end_clean();

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
		// save message, delete message
		if (isset($_GET['msg_read'])) {
			$this->info['reply_form'] = openform('inputform', 'post', (fusion_get_settings("site_seo") ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder']."&amp;msg_read=".$_GET['msg_read'])
										.form_textarea('message', 'message', '', array(
					'required' => 1,
					'error_text' => '',
					'autosize' => 1,
					'no_resize' => 0,
					'preview' => 1,
					'form_name' => 'inputform',
					'bbcode' => 1
				))
										.form_button('cancel', $locale['cancel'], $locale['cancel']).form_button('send_message', $locale['430'], $locale['430'], array(
					'class' => 'btn btn-sm m-l-10 btn-primary'
				))
										.closeform();
			//$this->info['reply_form']['save_message'] = form_button('save_message', $locale['412'], $locale['412'], array('class' => 'btn btn-sm btn-default'));
			//$this->info['reply_buttons']['delete_message'] = form_button('delete_message', $locale['416'], $locale['416'], array('class' => 'btn btn-sm btn-default'));
		}
		if (isset($_GET['msg_send'])) {
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
				$input_header = form_user_select('msg_send', '', isset($_GET['msg_send']) && isnum($_GET['msg_send'] ? : ''), array(
					'input_id' => 'msgsend2',
					"inline" => TRUE,
					'placeholder' => $locale['421']
				));
			}
			$this->info['reply_form'] = openform('inputform', 'post', "".(fusion_get_settings("site_seo") ? FUSION_ROOT : '').BASEDIR."messages.php?folder=".$_GET['folder']."&amp;msg_read=".$_GET['msg_read']).$input_header.form_text('subject', '', '', array(
					'max_length' => 32,
					'placeholder' => $locale['405']
				)).form_textarea('message', 'message', '', array(
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
		render_mailbox($this->info);
	}

	public function outbox() {
	}

	public function archive() {
	}

	public function message_form() {
	}

	public function message_settings() {
		global $userdata, $locale;
		$data = $this->user_pm_settings;
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