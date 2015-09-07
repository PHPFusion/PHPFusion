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
		$this->user_pm_settings = $this->get_pm_settings($userdata['user_id']);
		// UI variables
		$this->info = array(
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
		render_inbox($info);
	}
}