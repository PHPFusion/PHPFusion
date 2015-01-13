<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: UserFields.class.php
| Author: Hans Kristian Flaatten (Starefossen)
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
require_once THEMES."templates/global/profile.php";

class UserFields extends QuantumFields {
	public $displayTerms = 0;
	public $displayValidation = 0;
	public $errorsArray = array();
	public $formaction = FUSION_REQUEST; // changed in API 1.02
	public $formname = "inputform";
	public $postName;
	public $postValue;
	public $showAdminOptions = FALSE;
	public $showAdminPass = TRUE;
	public $showAvatarInput = TRUE;
	public $baseRequest = FALSE; // new in API 1.02 - turn fusion_self to fusion_request - 3rd party pages. Turn this on if you have more than one $_GET pagination str.
	public $skipCurrentPass = FALSE;
	public $registration = FALSE;
	public $userData = array("user_id"=>'', 'user_realname'=>'', "user_name"=>'', "user_password"=>'', "user_admin_password"=>'', "user_email"=>'', "user_language"=>LANGUAGE, 'user_timezone'=>'Europe/London');

	/* Quantum Fields Extensions */
	public $system_title = '';
	public $admin_rights = '';
	public $locale_file = '';
	public $category_db = '';
	public $field_db = '';
	public $plugin_folder = '';
	public $plugin_locale_folder = '';
	public $debug = FALSE;

	// API 1.02
	private $html = "";
	private $js = "";
	private $javaScriptOther;
	private $javaScriptRequired;
	public $method;
	private $_userNameChange = TRUE;

	/* User Fields class 9.00 */
	private $info = array(); // MVC var
	public $paginate = TRUE; // new in API v2.00

	public function setUserNameChange($value) {
		$this->_userNameChange = $value;
	}

	/* Page Navigation with UF Cats */
	private function renderPageLink() {
		// build this page.
		global $locale, $aidlink;
		$section = array();
		$result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_parent='0' ORDER BY field_cat_order");
		if (dbrows($result) > 0) {
			$cur = ($this->method == 'input') ? 'edit_profile.php' : 'profile.php';
			$aid = isset($_GET['aid']) ? $aidlink.'&amp;' : '';
			while ($data = dbarray($result)) {
				$section[] = array(
					'active'=>(isset($_GET['profiles']) && $_GET['profiles'] == $data['field_cat_id']) ? 1 : (!isset($_GET['profiles']) && $i == 0 ? 1 : 0),
					'link'=>$cur.clean_request($aid.'profiles='.$data['field_cat_id'].'&amp;lookup='.$this->userData['user_id'], array()),
					'name'=>ucwords(self::parse_label($data['field_cat_name']))
				);
			}
		}
		return $section;
	}

	/*-----------------------------------------
	+ User Form 2.0 for Version 9.00
	+ Generates only array and calls up an external
	+ template for maximum modding configurations
	+ returns $this->info + $this->displayMethod
	+ returns $this->userData
	+ External Template is an OOP object.
	+ ---------------------------------------------*/
	/* Main user fields form */
	public function renderInput() {
		$this->UserForm();
		include THEMES."templates/global/profile.php";
		render_userform($this->info);
	}

	/* Basic Account Settings */
	private function basicInputFields() {
		global $locale, $settings, $language_opts;
		$html = '';

		// need to call back all the file.
		$user_name = isset($_POST['user_name']) ? form_sanitizer($_POST['user_name'], '') : $this->userData['user_name'];
		$user_email = isset($_POST['user_email']) ? form_sanitizer($_POST['user_email'], '') : $this->userData['user_email'];

		// Username
		$html .= form_para($locale['u129'], 'account', 'profile_category_name');
		$html .= (iADMIN || $this->_userNameChange) ? form_text($locale['u127'], 'user_name', 'user_name', $user_name, array('max_length'=>30, 'required'=>1, 'error_text'=>$locale['u122'], 'inline'=>1)) : '';
		// Login Password
		$passRequired = $this->registration ? 1 : 0;
		if (!$this->registration) $html .= form_hidden('', 'user_id', 'user_id', isset($this->userData['user_id']) && isnum($this->userData['user_id']) ? $this->userData['user_id']: 0);
		$html .= (!$this->registration) ? "<div class='alert alert-info'>".$locale['u100']."</div>" : '';
		$html .= form_para($locale['u132'], 'password', 'profile_category_name');
		if (!$passRequired) { // will not show on register.
			$html .= form_text($locale['u133'], 'user_password', 'user_password', '', array('password'=>1, 'autocomplete_off'=>1, 'inline'=>1, 'max_length'=>64, 'error_text'=>$locale['u136']));
		}
		$html .= form_text($this->registration ? $locale['u133'] : $locale['u134'], 'user_new_password',' user_new_password', '', array('password'=>1, 'autocomplete_off'=>1, 'inline'=>1, 'max_length'=>64, 'error_text'=>$locale['u133'], 'required'=>$passRequired));
		$html .= form_text($locale['u135'], 'user_new_password2', 'user_new_password2', '', array('password'=>1, 'autocomplete_off'=>1, 'inline'=>1, 'max_length'=>64, 'error_text'=>$locale['u133'], 'required'=>$passRequired));
		$html .= "<div class='col-xs-12 col-sm-offset-3 col-md-offset-3 col-lg-offset-3'><span class='text-smaller'>".$locale['u147']."</span></div>\n";
		// Admin Password
		if ($this->showAdminPass && iADMIN) {
			if ($this->userData['user_admin_password']) {
				$html .= form_text($locale['u131'], 'user_admin_password', 'user_admin_password', '', array('password'=>1, 'autocomplete_off'=>1, 'inline'=>1, 'max_length'=>64, 'error_text'=>$locale['u136']));
			}
			$html .= form_text(($this->userData['user_admin_password'] ? $locale['u144'] : $locale['u131']), 'user_new_admin_password', 'user_new_admin_password', '', array('password'=>1, 'autocomplete_off'=>1, 'inline'=>1, 'max_length'=>64, 'error_text'=>$locale['u136']));
			$html .= form_text($locale['u145'], 'user_new_admin_password2', 'user_new_admin_password2', '', array('class'=>'m-b-0', 'password'=>1, 'autocomplete_off'=>1, 'inline'=>1, 'max_length'=>64, 'error_text'=>$locale['u136']));
			$html .= "<div class='col-xs-12 col-sm-offset-3 col-md-offset-3 col-lg-offset-3'><span class='text-smaller'>".$locale['u147']."</span></div>\n";
		}
		if (!$this->registration) {
			if (isset($this->userData['user_avatar']) && $this->userData['user_avatar'] != "") {
				$this->html .= "<label for='user_avatar_upload'><img src='".IMAGES."avatars/".$this->userData['user_avatar']."' alt='".$locale['u185']."' />";
				$this->html .= "</label>\n<br />\n";
				$this->html .= "<input type='checkbox' name='delAvatar' value='1' class='textbox' /> ".$locale['u187']."<br />\n<br />\n";
			}
			// avatar field
			$html .= form_fileinput($locale['u185'], 'user_avatar', 'user_avatar_upload', '', '', array('type'=>'image', 'inline'=>1, 'width'=>'100%', 'class'=>'m-t-10 m-b-0'));
			$html .= "<div class='col-xs-12 col-sm-offset-3 col-md-offset-3 col-lg-offset-3'><span class='text-smaller'>".sprintf($locale['u184'], parsebytesize($settings['avatar_filesize']), $settings['avatar_width'], $settings['avatar_height'])."</span></div>\n";

		}

		// email field
		$html .= form_para($locale['u064'], 'email', 'profile_category_name');
		$html .= form_text($locale['u128'], 'user_email', 'user_email', $user_email, array('email'=>1, 'inline'=>1, 'max_length'=>'100', 'error_text'=>$locale['u126']));
		// Hide email toggler
		$hide = isset($this->userData['user_hide_email']) ? $this->userData['user_hide_email'] : 1;
		$hide = isset($_POST['user_hide_email']) && isnum($_POST['user_hide_email']) ? $_POST['user_hide_email'] : $hide;
		$html .= form_btngroup($locale['u051'], 'user_hide_email', 'user_hide_email', array($locale['u053'], $locale['u052']), $hide, array('inline'=>1));

        return $html;
	}

	private function UserForm() {
		global $settings, $userdata, $aidlink;
		// Page Navigation - lets just shut off for registration - stupid to have multi page registration page
		if ($this->paginate && !$this->registration)  $this->info['section'] = $this->renderPageLink();
		$this->info['register'] = $this->registration;
		$this->info['basic_field'] = '';
		//$form_action = str_replace($settings['site_path'], '', FUSION_REQUEST);
		$form_action = FUSION_REQUEST;
		// Form Section
		$this->info['openform'] = openform($this->formname, $this->formname, 'post', $form_action, array('enctype' => "".($this->showAvatarInput ? 1 : 0)."", 'downtime' => 0));

		if (!isset($_GET['profiles']) or (isset($_GET['profiles']) && $_GET['profiles'] == 1)) $this->info['basic_field'] = $this->basicInputFields();
			// Extended UF modules
			if (defined('ADMIN_PANEL')) $this->info['basic_field'] = $this->basicInputFields(); // basic account info
			$this->get_userFields();
		// Captcha
		if ($this->displayValidation == 1 && !defined('ADMIN_PANEL')) $this->info['validate'] = $this->renderValidation();
		// Website terms
		if ($this->displayTerms == 1) $this->info['terms'] = $this->renderTerms();
		// Close form html tag

        // User Hash
        if (!$this->skipCurrentPass) { 	// meaning we are not going to skip the password check if new password is not empty
            $this->info['basic_field'] .= "<input type='hidden' name='user_hash' value='".$this->userData['user_password']."' />\n";
        }
		$this->info['closeform'] = closeform();
		// Button
		$this->info['button'] = $this->renderButton();
	}

	/*-----------------------------------------
	+ User Profile 2.0 for Version 9.00
	+ Generates only array and calls up an external
	+ template for maximum modding configurations
	+ returns $this->info + $this->displayMethod
	+ returns $this->userData
	+ External Template is an OOP object.
	+ ---------------------------------------------*/
	/* Front End output is MVC - Theme it as you wish. Off you go :P */
	public function renderOutput() {
		$this->UserProfile();
		require_once THEMES."templates/global/profile.php";
		render_userprofile($this->info);
	}
	/* New profile page output */
	private function UserProfile() {
		global $locale, $userdata, $aidlink;
		if (!isset($_GET['profiles']) or isset($_GET['profiles']) && $_GET['profiles'] == 1) {
			if (empty($this->userData['user_avatar']) or !file_exists(IMAGES."avatars/".$this->userData['user_avatar'])) $this->userData['user_avatar'] = "noavatar100.png";
			$this->info['core_field']['profile_user_avatar'] = array('title'=>$locale['u186'], 'value'=>$this->userData['user_avatar'], 'status'=>$this->userData['user_status']);
			// user name
			$this->info['core_field']['profile_user_name'] = array('title'=>$locale['u068'], 'value'=>ucwords($this->userData['user_name']));
			// user level
			$this->info['core_field']['profile_user_level'] = array('title'=>$locale['u063'], 'value'=>getuserlevel($this->userData['user_level']));
			// user email
			if (iADMIN || $this->userData['user_hide_email'] == 0) $this->info['core_field']['profile_user_email'] = array('title'=>$locale['u064'], 'value'=>hide_email($this->userData['user_email']));
			// user joined
			$this->info['core_field']['profile_user_joined'] = array('title'=>$locale['u066'], 'value'=>showdate("longdate", $this->userData['user_joined']));
			// user last visit
			$lastVisit = $this->userData['user_lastvisit'] ? showdate("longdate", $this->userData['user_lastvisit']) : $locale['u042'];
			$this->info['core_field']['profile_user_visit'] = array('title'=>$locale['u067'], 'value'=>$lastVisit);
			// user status
			if (iADMIN && $this->userData['user_status'] > 0) {
				$this->info['core_field']['profile_user_status'] = array('title'=>$locale['u055'], 'value'=>getuserstatus($this->userData['user_status']));
				$this->info['core_field']['profile_user_reason'] = array('title'=>$locale['u056'], 'value'=>$this->userData['suspend_reason']);
			}
			// IP
			(iADMIN && checkrights("M")) ? $this->info['core_field']['profile_user_ip'] = array('title'=>$locale['u049'], 'value'=>$this->userData['user_ip']) : '';
			// Groups - need translating.
			$this->info['core_field']['profile_user_group']['title'] = $locale['u057'];
			$user_groups = strpos($this->userData['user_groups'], ".") == 0 ? substr($this->userData['user_groups'], 1) : $this->userData['user_groups'];
			$user_groups = explode(".", $user_groups);
			$grp_html = '';
			$user_groups = array_filter($user_groups);
			if (!empty($user_groups)) {
				for ($i = 0; $i < count($user_groups); $i++) {
					$grp_html .= "<span class='user_group'><a href='".FUSION_SELF."?group_id=".$user_groups[$i]."'>".getgroupname($user_groups[$i], TRUE)."</a></span>";
				}
				$this->info['core_field']['profile_user_group']['value'] = $grp_html;
			} else {
				$this->info['core_field']['profile_user_group']['value'] = $locale['user_na'];
			}
		}

		$this->info['section'] = $this->renderPageLink();

		// Module Items
		$this->get_userFields();
		// Dynamics core UF fields
		$this->info['item'][3] = '';
		// buttons.. 2 of them.
		if (iMEMBER && $userdata['user_id'] != $this->userData['user_id']) {
			$this->info['buttons'][] = array('link'=>BASEDIR."messages.php?msg_send=".$this->userData['user_id'], 'name'=>$locale['u043']);
			if (iADMIN && checkrights("M") && $this->userData['user_level'] != "103" && $this->userData['user_id'] != "1") {
				$this->info['buttons'][] = array('link'=>ADMIN."members.php".$aidlink."&amp;step=log&amp;user_id=".$this->userData['user_id'], 'name'=>$locale['u054']);
			}
		}
	}


	/* Fetches UF Module Shells and extends Userdata with 3rd party Databases */
	// reacts with $method var ('input', 'display');
	private function get_userFields() {
		global $locale, $settings, $aidlink;
		$this->callback_data = $this->userData;
		$first_rows = dbarray(dbquery("SELECT field_cat_id FROM ".DB_USER_FIELD_CATS." WHERE field_parent ='0' AND field_cat_id !='1' order by field_cat_order ASC limit 1"));
		$index_page_id = isset($_GET['profiles']) && isnum($_GET['profiles']) ? $_GET['profiles'] : $first_rows['field_cat_id'];
		$result = dbquery("SELECT field.*,
				cat.field_cat_id, cat.field_cat_name, cat.field_parent,
				root.field_cat_id as page_id, root.field_cat_name as page_name, root.field_cat_db, root.field_cat_index
				FROM ".DB_USER_FIELDS." field
				LEFT JOIN ".DB_USER_FIELD_CATS." cat ON (cat.field_cat_id = field.field_cat)
				LEFT JOIN ".DB_USER_FIELD_CATS." root on (cat.field_parent = root.field_cat_id)
				WHERE cat.field_cat_id='$index_page_id' OR root.field_cat_id='$index_page_id'
				ORDER BY root.field_cat_order, cat.field_cat_order, field.field_order");
		if (dbrows($result)>0) {
			$this->info['user_field'] = ($this->method == 'display') ? array() : '';
			// loop
			while ($data = dbarray($result)) {
				if ($data['field_cat_id']) $category[$data['field_parent']][$data['field_cat_id']] = self::parse_label($data['field_cat_name']);
				if ($data['field_cat']) $item[$data['field_cat']][] = $data;
				if ($data['field_cat_db'] && $data['field_cat_index'] && $data['field_cat_db'] !== 'users') {
					// extend userData
					$this->callback_data += dbarray(dbquery("SELECT * FROM ".DB_PREFIX.$data['field_cat_db']." WHERE ".$data['field_cat_index']."='".$this->userData['user_id']."'"));
				}
			}

			// require to inject id when id not present in other pages to make reference as Quantum Fields $index_value
			if ($index_page_id !=='1' && $this->method !=='display') {
				$this->info['user_field'] .= form_hidden('', 'user_id', 'user_id', $this->userData['user_id']);
				$this->info['user_field'] .= form_hidden('', 'user_name', 'user_name', $this->userData['user_name']);
			}
			// filter display - input and display method.
			if (isset($category[$index_page_id])) {
				foreach($category[$index_page_id] as $cat_id => $cat) {
					if ($this->method == 'input') { // model as string because nothing to template in dynamics output.
						$this->info['user_field'] .= form_para($cat, $cat_id, 'profile_category_name');
						if (isset($item[$cat_id])) {
							foreach($item[$cat_id] as $field_id => $field) {
								if (!is_array($this->phpfusion_field_DOM($field))) {
									$this->info['user_field'] .= $this->phpfusion_field_DOM($field);
								}
							}
						}
					} elseif ($this->method == 'display') { // model as array because profile can be templated.
						$this->info['user_field'][$cat_id]['title'] = form_para($cat, $cat_id, 'profile_category_name');
						if (isset($item[$cat_id])) {
							foreach($item[$cat_id] as $field_id => $field) {
								if (isset($this->callback_data[$field['field_name']]) && $this->callback_data[$field['field_name']] && $this->phpfusion_field_DOM($field)) $this->info['user_field'][$cat_id]['fields'][$field['field_id']] = $this->phpfusion_field_DOM($field);
							}
						}
					}
				} // end foreach
			}
		}
	}

	/* Accessories */
	private function renderAdminOptions() {
		global $locale, $aidlink;
		
		$groups_cache = cache_groups();
		$user_groups_opts = "";
		$this->html .= "<div style='margin:5px'></div>\n";
		$this->html .= "<form name='admin_form' method='post' action='".FUSION_SELF."?lookup=".$this->userData['user_id']."'>\n";
		$this->html .= "<table cellpadding='0' cellspacing='0' class='table table-responsive profile tbl-border center'>\n<tr>\n";
		$this->html .= "<td class='tbl2' colspan='2'><strong>".$locale['u058']."</strong></td>\n";
		$this->html .= "</tr>\n<tr>\n";
		$this->html .= "<td class='tbl1'><!--profile_admin_options-->\n";
		$this->html .= "<a href='".ADMIN."members.php".$aidlink."&amp;step=edit&amp;user_id=".$this->userData['user_id']."'>".$locale['u069']."</a> ::\n";
		$this->html .= "<a href='".ADMIN."members.php".$aidlink."&amp;action=1&amp;user_id=".$this->userData['user_id']."'>".$locale['u070']."</a> ::\n";
		$this->html .= "<a href='".ADMIN."members.php".$aidlink."&amp;action=3&amp;user_id=".$this->userData['user_id']."'>".$locale['u071']."</a> ::\n";
		$this->html .= "<a href='".ADMIN."members.php".$aidlink."&amp;step=delete&amp;status=0&amp;user_id=".$this->userData['user_id']."' onclick=\"return confirm('".$locale['u073']."');\">".$locale['u072']."</a>\n";
		$this->html .= "</td>\n";
		if (count($groups_cache) > 0) {
			foreach ($groups_cache as $group) {
				if (!preg_match("(^{$group['group_id']}|\.{$group['group_id']}\.|\.{$group['group_id']}$)", $this->userData['user_groups'])) {
					$user_groups_opts .= "<option value='".$group['group_id']."'>".$group['group_name']."</option>\n";
				}
			}
			if (iADMIN && checkrights("UG") && $user_groups_opts) {
				$this->html .= "<td align='right' class='tbl1'>".$locale['u061'].":\n";
				$this->html .= "<select name='user_group' class='textbox' style='width:100px'>\n".$user_groups_opts."</select>\n";
				$this->html .= "<input type='submit' name='add_to_group' value='".$locale['u059']."' class='button'  onclick=\"return confirm('".$locale['u060']."');\" />\n";
				$this->html .= "</td>\n";
			}
		}
		$this->html .= "</tr>\n</table>\n</form>\n";
	}
	private function renderAvatarInput() {
		global $locale, $settings;
		$html = '';
		$html .= "";
		$html .= "<tr>\n";
		$html .= "<td valign='top' style='border-top:0px;' class='tbl".$this->getErrorClass("user_avatar")."'>";
		$html .= "<label for='user_avatar_upload'>".$locale['u185']."</label></td>\n";
		$html .= "<td style='border-top:0px;' class='tbl".$this->getErrorClass("user_avatar")."'>";
		if (isset($this->userData['user_avatar']) && $this->userData['user_avatar'] != "") {
			$html .= "<label for='user_avatar_upload'><img class='img-thumbnail' src='".IMAGES."avatars/".$this->userData['user_avatar']."' alt='".$locale['u185']."' />";
			$html .= "</label>\n<br />\n";
			$html .= "<input type='checkbox' name='delAvatar' value='1' class='textbox' /> ".$locale['u187']."<br />\n<br />\n";
		}
		$html .= "<input type='file' id='user_avatar_upload' name='user_avatar' class='textbox' style='width:200px;' /><br />\n";
		$html .= "<span class='small2'>".$locale['u186']."</span><br />\n<span class='small2'>";
		$html .= sprintf($locale['u184'], parsebytesize($settings['avatar_filesize']), $settings['avatar_width'], $settings['avatar_height']);
		$html .= "</span>\n";
		$html .= "</td>\n</tr>\n";
		return $html;
	}

	private function renderTerms() {
		global $locale;
		$html = "<div class='form-group clearfix'>";
		$html .= "<label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0'>".$locale['u192']." <span class='required'>*</span></label>";
		$html .= "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n";
		$html .= form_checkbox($locale['u193'], 'agreement', 'agreement', '');
		$html .= "</div>\n";
		add_to_jquery("
		$('#agreement').bind('click', function() {
			if (document.inputform.agreement.checked) {
			document.inputform.register.disabled=false;
			} else {
			document.inputform.register.disabled=true;
			}
		});
		");
		return $html;
	}
	private function renderValidation() {
		global $settings, $locale;
		$_CAPTCHA_HIDE_INPUT = FALSE;
		$html = "<hr>\n";
		$html .= "<div class='form-group clearfix'>";
		$html .= "<label for='captcha_code' class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0'>\n".$locale['u190']." <span class='required'>*</span></label>\n";
		$html .= "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9 p-l-0'>";
		ob_start();
		include INCLUDES."captchas/".$settings['captcha']."/captcha_display.php";
		$html .= ob_get_contents();
		ob_end_clean();
		if (!$_CAPTCHA_HIDE_INPUT) {
			$html .= form_text('', 'captcha_code', 'captcha_code', '', array('inline'=>1, 'required'=>1, 'autocomplete_off'=>1, 'width'=>'200px', 'class'=>'m-t-15', 'placeholder'=>$locale['u191']));
		}
		$html .= "</div>\n";
		$html .= "</div>\n";
		return $html;
	}
	private function renderButton() {
		$dissabled = $this->displayTerms == 1 ? " disabled='disabled'" : "";
		$html = '';
		if (!$this->skipCurrentPass) {
			$html .= "<input type='hidden' name='user_hash' value='".$this->userData['user_password']."' />\n";
		}
		$html .= "<button type='submit' name='".$this->postName."' value='".$this->postValue."' class='btn btn-primary'".$dissabled." />".$this->postValue."</button>\n";
		return $html;
	}

}

?>
