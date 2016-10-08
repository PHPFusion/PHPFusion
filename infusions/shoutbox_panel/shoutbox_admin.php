<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: shoutbox_admin.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../../maincore.php";
require_once THEMES."templates/admin_header.php";
include INFUSIONS."shoutbox_panel/infusion_db.php";
pageAccess("S");

class Shoutbox_admin {
    protected static $sb_settings = array();
    private static $instance = NULL;
    private static $locale = array();
    private static $limit = 20;
    private $data = array(
		'shout_id' 			=> 0,
		'shout_name' 		=> '',
		'shout_message' 	=> '',
		'shout_datestamp' 	=> '',
		'shout_ip' 			=> '',
		'shout_ip_type'		=> '4',
		'shout_hidden'		=> '',
		'shout_language'	=> LANGUAGE,
	);

    public function __construct() {
        $this->set_locale();
        $sb_settings = self::get_sb_settings();
        $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
        switch ($_GET['action']) {
            case 'select_delete':
            if (empty($_POST['rights'])) {
				\defender::stop();
				addNotice('danger', self::$locale['SB_noentries']);
				redirect(clean_request("", array("section=shoutbox", "aid"), TRUE));
            }
			self::delete_select($_POST['rights']);
			break;
            case 'delete':
			self::delete_SB($_GET['shout_id']);
			break;
            case 'edit':
			$this->data = self::_selectedSB($_GET['shout_id']);
			break;
            default:
			break;
        }
		add_breadcrumb(array('link' => INFUSIONS.'shoutbox_panel/shoutbox_admin.php'.fusion_get_aidlink(), 'title' => self::$locale['SB_title']));
        //self::set_adminsdb();
    }

    public static function getInstance($key = TRUE) {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public static function get_sb_settings() {
        if (empty(self::$sb_settings)) {
            self::$sb_settings = fusion_get_settings("shoutbox_panel");
        }
        return self::$sb_settings;
    }

    private static function set_locale() {
        self::$locale = fusion_get_locale("", INFUSIONS."shoutbox_panel/locale/".LANGUAGE.".php");
    }

    private static function delete_select($id) {
    	if (!empty($id)) {
			$cnt = count($id);
			$i = 0;
    		foreach ($id as $key => $right) {
    		    if (self::verify_sbdb($key)) {
					dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_id='".intval($key)."'");
					$i++;
    		    }
    		}
			addNotice('warning', $cnt." / ".$i.self::$locale['SB_shout_deleted']);
    	}
    }

    private static function delete_SB($id) {
        if (self::verify_sbdb($id)) {
            dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_id='".intval($id)."'");
            addNotice('warning', self::$locale['SB_shout_deleted']);
	        redirect(clean_request("", array("section=shoutbox", "aid"), TRUE));
        }
    }

    static function verify_sbdb($id) {
        if (isnum($id)) {
            return dbcount("(shout_id)", DB_SHOUTBOX, "shout_id='".intval($id)."'");
        }
        return FALSE;
    }

    public function _countSB($opt) {
        $DBc = dbcount("(shout_id)", DB_SHOUTBOX, $opt);
    	return $DBc;
    }

    public function _selectDB($rows) {
		$result = dbquery("SELECT s.shout_id, s.shout_name, s.shout_message, s.shout_datestamp, s.shout_language, s.shout_ip, s.shout_hidden, u.user_id, u.user_name, u.user_avatar, u.user_status
			FROM ".DB_SHOUTBOX." s
			LEFT JOIN ".DB_USERS." u ON s.shout_name=u.user_id
			ORDER BY shout_datestamp DESC
			LIMIT ".intval($rows).", ".self::$limit
		);
        return $result;
    }

    public function _selectedSB($ids) {
        if (self::verify_sbdb($ids)) {
			$result = dbquery("SELECT shout_id, shout_name, shout_message, shout_datestamp, shout_ip, shout_ip_type, shout_hidden, shout_language
				FROM ".DB_SHOUTBOX."
				WHERE shout_id=".intval($ids)
			);
        	if (dbrows($result) > 0) {
				return $data = dbarray($result);
        	}
		}
    }

    public function display_admin() {
		$aidlink = fusion_get_aidlink();
		$allowed_section = array("shoutbox", "shoutbox_form", "shoutbox_settings");
		$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'shoutbox';
		$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') && isset($_GET['shout_id']) ? TRUE : FALSE;
		$_GET['shout_id'] = isset($_GET['shout_id']) && isnum($_GET['shout_id']) ? $_GET['shout_id'] : 0;

		opentable(self::$locale['SB_admin1']);
		$master_tab_title['title'][] = self::$locale['SB_admin1'];
		$master_tab_title['id'][] = "shoutbox";
		$master_tab_title['icon'][] = "";

		$master_tab_title['title'][] = $edit ? self::$locale['SB_edit'] : self::$locale['SB_add'];
		$master_tab_title['id'][] = "shoutbox_form";
		$master_tab_title['icon'][] = "";

		$master_tab_title['title'][] = self::$locale['SB_settings'];
		$master_tab_title['id'][] = "shoutbox_settings";
		$master_tab_title['icon'][] = "";

		echo opentab($master_tab_title, $_GET['section'], "shoutbox", TRUE);
		switch ($_GET['section']) {
			case "shoutbox_form":
			add_to_title(self::$locale['SB_edit']);
			$this->sbForm();
			break;
			case "shoutbox_settings":
			add_to_title(self::$locale['SB_settings']);
			$this->settings_Form();
			break;
			default:
			$this->sb_listing();
			add_to_title(self::$locale['SB_title']);
			break;
		}
		echo closetab();
		closetable();
    }

    public function sb_listing() {
		$aidlink = fusion_get_aidlink();
		$total_rows = $this->_countSB("");
		$rowstart = isset($_GET['rowstart']) && ($_GET['rowstart'] <= $total_rows) ? $_GET['rowstart'] : 0;
		$result = $this->_selectDB($rowstart);
		$rows = dbrows($result);

		echo "<div class='clearfix'>\n";
		echo "<span class='pull-right m-t-10'>".sprintf(self::$locale['SB_entries'], $rows, $total_rows)."</span>\n";
		echo "</div>\n";
		echo ($total_rows > $rows) ? makepagenav($rowstart, self::$limit, $total_rows, self::$limit, clean_request("", array("aid", "section"), TRUE)."&amp;") : "";

		if ($rows > 0) {
			echo "<div class='list-group'>\n";
			echo openform('sb_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;action=select_delete&amp;section=shoutbox");
			while ($data = dbarray($result)) {
				echo "<div class='list-group-item' style='min-height:100px;'>\n";
				echo "<div class='pull-left m-r-10'>".display_avatar($data, '90px', '', TRUE, 'img-rounded')."</div>\n";
				echo "<div class='comment-name'>";
				echo $data['user_name'] ? "<span class='slink'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</span>" : $data['shout_name'];
				echo "</span>\n</div>\n";
				echo "<div class='m-t-5'>\n";
				echo "<p><span class='small'>".self::$locale['SB_on_date'].showdate("longdate", $data['shout_datestamp'])."</p>";
				echo self::$locale['SB_visbility'].getgroupname($data['shout_hidden'])."<br />";
				echo self::$locale['SB_lang'].translate_lang_names($data['shout_language'])."<br />";
				echo self::$locale['SB_userip'].$data['shout_ip']."<br />\n";
				echo "<div class='pull-right m-r-10 m-t-10'><small>".form_checkbox("rights[".$data['shout_id']."]", '', '', array("inline" => FALSE))." </div>";
				echo "<a class='pull-right m-r-10 btn btn-danger btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=shoutbox&amp;action=delete&amp;shout_id=".$data['shout_id']."' onclick=\"return confirm('".self::$locale['SB_warning_shout']."');\">".self::$locale['delete']."<i class='fa fa-trash m-l-10'></i></a>";
				echo "<a class='pull-right m-r-10 btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=shoutbox_form&amp;action=edit&amp;shout_id=".$data['shout_id']."'><i class='fa fa-edit fa-fw'></i> ".self::$locale['edit']."</a>";
				echo "</small>\n</div>\n";
				echo parse_textarea($data['shout_message'], TRUE, TRUE, TRUE, "images/");
				echo "</div>\n";
			}
			echo "</div>\n";
			echo form_button('sb_admins', self::$locale['SB_selected_shout'], self::$locale['SB_selected_shout'], array('class' => 'btn-primary'));
			echo closeform();
		} else {
			echo "<div class='text-center'><br />".self::$locale['SB_no_msgs']."</div>\n";
		}
    }

    public function settings_Form() {
        $aidlink = fusion_get_aidlink();
        $sb_settings = self::get_sb_settings();
		$sb_date = array(
			'90' => "90".self::$locale['SB_days'],
			'60' => "60".self::$locale['SB_days'],
			'30' => "30".self::$locale['SB_days'],
			'20' => "20".self::$locale['SB_days'],
			'10' => "10".self::$locale['SB_days'],
		);

		if (isset($_POST['sb_settings'])) {
			$inputArray = array(
				'visible_shouts' => form_sanitizer($_POST['visible_shouts'], 0, "visible_shouts"),
				'guest_shouts' => form_sanitizer($_POST['guest_shouts'], 0, "guest_shouts"),
				'hidden_shouts' => form_sanitizer($_POST['hidden_shouts'], 0, "hidden_shouts"),
			);
			if (\defender::safe()) {
				foreach ($inputArray as $settings_name => $settings_value) {
					$inputSettings = array(
						"settings_name" => $settings_name, "settings_value" => $settings_value, "settings_inf" => "shoutbox_panel",
					);
					dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", array("primary_key" => "settings_name"));
				}
				addNotice("success", self::$locale['SB_update_ok']);
				redirect(clean_request("section=shoutbox_settings", array("", "aid"), TRUE));
			}
		}

		if (isset($_POST['sb_delete_old']) && isset($_POST['num_days']) && isnum($_POST['num_days'])) {
			$deletetime = time() - (intval($_POST['num_days']) * 86400);
			$numrows = dbcount("(shout_id)", DB_SHOUTBOX, "shout_datestamp < '".$deletetime."'");
			$result = dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_datestamp < '".$deletetime."'");
			addNotice("warning", number_format(intval($numrows))." / ".$sb_date[$_POST['num_days']].self::$locale['SB_shout_deleted']);
			redirect(clean_request("section=shoutbox_settings", array("", "aid"), TRUE));
		}
		openside('');
		echo openform('shoutbox', 'post', FUSION_SELF.$aidlink."&amp;section=shoutbox_settings");
		echo form_select('num_days', self::$locale['SB_delete_old'], '', array('inline' => TRUE, 'inner_width' => '300px', 'options' => $sb_date));
		echo form_button('sb_delete_old', self::$locale['SB_submit'], self::$locale['SB_submit'], array('class' => 'btn-primary'));
		echo closeform();
		closeside();

		add_to_jquery("$('#sb_delete_old').bind('click', function() { confirm('".self::$locale['SB_warning_shouts']."'); });");

		openside('');
		echo openform('shoutbox2', 'post', FUSION_SELF.$aidlink."&amp;section=shoutbox_settings");
		echo form_text('visible_shouts', self::$locale['SB_visible_shouts'], $sb_settings['visible_shouts'], array('required' => TRUE, 'inline' => TRUE, 'inner_width' => '100px', "type" => "number"));
		$opts = array('1' => self::$locale['yes'], '0' => self::$locale['no'],);
		echo form_select('guest_shouts', self::$locale['SB_guest_shouts'], $sb_settings['guest_shouts'], array('inline' => TRUE, 'inner_width' => '100px', 'options' => $opts));
		echo form_select('hidden_shouts', self::$locale['SB_hidden_shouts'], $sb_settings['hidden_shouts'], array('inline' => TRUE, 'inner_width' => '100px', 'options' => $opts));
		echo form_button('sb_settings', self::$locale['SB_submit'], self::$locale['SB_submit'], array('class' => 'btn-primary'));
		echo closeform();
		closeside();
    }

    public function sbForm() {
        fusion_confirm_exit();

        if (isset($_POST['shout_admins'])) {
			$this->data = array(
				'shout_id' => form_sanitizer($_POST['shout_id'], 0, "shout_id"),
				'shout_message' => form_sanitizer($_POST['shout_message'], '', 'shout_message'),
				'shout_hidden' => isset($_POST['shout_visibility']) ? form_sanitizer($_POST['shout_visibility'], '', 'shout_visibility') : 0,
				'shout_language' => form_sanitizer($_POST['shout_language'], LANGUAGE, "shout_language"),
			);

			if (empty($this->data['shout_id'])){
				$this->data += array(
					'shout_name' => fusion_get_userdata("user_id"),
					'shout_datestamp' => time(),
					'shout_ip' => USER_IP,
					'shout_ip_type' => USER_IP_TYPE,
					'shout_hidden' => isset($_POST['shout_visibility']) ? form_sanitizer($_POST['shout_visibility'], '', 'shout_visibility') : 0,
				);
			}

			if (\defender::safe()) {
				dbquery_insert(DB_SHOUTBOX, $this->data, empty($this->data['shout_id']) ? "save" : "update");
				addNotice("success", self::$locale['SB_shout_updated']);
				redirect(clean_request("section=shoutbox", array("", "aid"), TRUE));
			}
        }
        openside('');
        echo openform('sb_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=shoutbox_form");
	    echo form_hidden('shout_id', '', $this->data['shout_id']);
		echo form_textarea('shout_message', self::$locale['SB_message'], $this->data['shout_message'], array('required' => TRUE, 'form_name' => 'sb_form', 'wordcount' => TRUE, 'maxlength' => '200', 'type' => 'bbcode', 'input_bbcode' => 'smiley|b|u|url|color'));

		if (multilang_table("SB")) {
			echo form_select('shout_language', self::$locale['global_ML100'], $this->data['shout_language'], array(
				'required' => TRUE,
				'options' => fusion_get_enabled_languages(),
				'placeholder' => self::$locale['choose'],
			));
		} else {
			echo form_hidden('shout_language', '', $this->data['shout_language']);
		}

        if (self::$sb_settings['hidden_shouts']) {
			echo form_select('shout_visibility', self::$locale['SB_visbility'], $this->data['shout_hidden'], array(
				'options' => fusion_get_groups(),
				'placeholder' => self::$locale['choose'],
			));
        }
		echo form_button('shout_admins', empty($_GET['shout_id']) ? self::$locale['SB_save_shout'] : self::$locale['SB_update_shout'], empty($_GET['blacklist_id']) ? self::$locale['SB_save_shout'] : self::$locale['SB_update_shout'], array('class' => 'btn-primary'));
		echo closeform();
		closeside();
    }

}

Shoutbox_admin::getInstance(TRUE)->display_admin();

require_once THEMES."templates/footer.php";