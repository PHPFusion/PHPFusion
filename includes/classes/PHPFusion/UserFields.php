<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: UserFields.php
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
namespace PHPFusion;
if (!defined("IN_FUSION")) die("Access Denied");
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
	public $userData = array(
		"user_id" => '',
		"user_name" => '',
		"user_password" => '',
		"user_admin_password" => '',
		"user_email" => '',
		'user_hide_email' => 0,
		"user_language" => LANGUAGE,
		'user_timezone' => 'Europe/London'
	);
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

	/**
	 * Check whether a user field is available/installed
	 * @param $field_name
	 * @return bool
	 */
	public static function check_user_field($field_name) {
		static $list;
		$result = dbquery("SELECT field_name FROM ".DB_USER_FIELDS);
		if (dbrows($result) > 0) {
			while ($data = dbarray($result)) {
				$list[] = $data['field_name'];
			}
		}
		return in_array($field_name, $list) ? TRUE : FALSE;
	}

	/* Page Navigation with UF Cats */
	private function renderPageLink() {
		// build this page.
		global $locale, $aidlink;
		$section = array();
		$result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_parent='0' ORDER BY field_cat_order");
		if (dbrows($result) > 0) {
			$aid = isset($_GET['aid']) ? $aidlink.'&' : '';
			$i = 0;
			while ($data = dbarray($result)) {
				$section[] = array(
					'active' => (isset($_GET['profiles']) && $_GET['profiles'] == $data['field_cat_id']) ? 1 : (!isset($_GET['profiles']) && $i == 0 ? 1 : 0),
					'link' => clean_request($aid.'profiles='.$data['field_cat_id'].'&lookup='.$this->userData['user_id'], array('profiles'), FALSE, '&amp;'),
					'name' => ucwords(self::parse_label($data['field_cat_name']))
				);
				$i++;
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
	public function render_profile_input() {
		global $locale;
		include THEMES."templates/global/profile.php";
		$_GET['section'] = isset($_GET['section']) ? $_GET['section'] : 1;
		$_GET['profiles'] = isset($_GET['profiles']) ? $_GET['profiles'] : 1;
		/* Fields that are ONLY AVAILABLE with REQUEST_URL = ?profiles=1 */
		if ($_GET['profiles'] == '1') {
			// User Name
			$user_name = isset($_POST['user_name']) ? $_POST['user_name'] : $this->userData['user_name'];
			$user_email = isset($_POST['user_email']) ? $_POST['user_email'] : $this->userData['user_email'];
			$user_hide_email = isset($_POST['user_hide_email']) ? $_POST['user_hide_email'] : $this->userData['user_hide_email'];
			$this->info['user_name'] = form_para($locale['u129'], 'account', 'profile_category_name');
			if (iADMIN || $this->_userNameChange) {
				$this->info['user_name'] .= form_text('user_name', $locale['u127'], $user_name, array(
					'max_length' => 30,
					'required' => 1,
					'error_text' => $locale['u122'],
					'inline' => 1
				));
			}
			// User Password
			$this->info['user_password'] = form_para($locale['u132'], 'password', 'profile_category_name');
			if ($this->registration) {
				$this->info['user_password'] .= form_text('user_password1', $locale['u134a'], '', array(
					'type' => 'password',
					'autocomplete_off' => 1,
					'inline' => 1,
					'max_length' => 64,
					'error_text' => $locale['u133'],
					'required' => 1
				));
				$this->info['user_password'] .= form_text('user_password2', $locale['u134b'], '', array(
					'type' => 'password',
					'autocomplete_off' => 1,
					'inline' => 1,
					'max_length' => 64,
					'error_text' => $locale['u133'],
					'required' => 1
				));
			} else {
				$this->info['user_password'] .= form_hidden('user_id', '', isset($this->userData['user_id']) && isnum($this->userData['user_id']) ? $this->userData['user_id'] : 0);
				$this->info['user_password'] .= form_text('user_password', $locale['u135a'], '', array(
					'type' => 'password',
					'autocomplete_off' => 1,
					'inline' => 1,
					'max_length' => 64,
					'error_text' => $locale['u133']
				));
				$this->info['user_password'] .= form_text('user_password1', $locale['u135b'], '', array(
					'type' => 'password',
					'autocomplete_off' => 1,
					'inline' => 1,
					'max_length' => 64,
					'error_text' => $locale['u133']
				));
				$this->info['user_password'] .= form_text('user_password2', $locale['u135c'], '', array(
					'type' => 'password',
					'autocomplete_off' => 1,
					'inline' => 1,
					'max_length' => 64,
					'error_text' => $locale['u133']
				));
				$this->info['user_password'] .= "<input type='hidden' name='user_hash' value='".$this->userData['user_password']."' />\n";
			}
			$this->info['user_password'] .= "<div class='col-xs-12 col-sm-offset-3 col-md-offset-3 col-lg-offset-3'><span class='text-smaller'>".$locale['u147']."</span></div>\n";
			// Admin Password - not available for everyone except edit profile.
			$this->info['user_admin_password'] = '';
			if (!$this->registration && iADMIN && !defined('ADMIN_PANEL')) {
				if ($this->userData['user_admin_password']) {
					$this->info['user_admin_password'] = form_text('user_admin_password', $locale['u144a'], '', array(
						'type' => 'password',
						'autocomplete_off' => 1,
						'inline' => 1,
						'max_length' => 64,
						'error_text' => $locale['u136']
					));
					$this->info['user_admin_password'] .= form_text('user_admin_password1', $locale['u144'], '', array(
						'type' => 'password',
						'autocomplete_off' => 1,
						'inline' => 1,
						'max_length' => 64,
						'error_text' => $locale['u136']
					));
				} else {
					$this->info['user_admin_password'] = form_text('user_admin_password', $locale['u144'], '', array(
						'type' => 'password',
						'autocomplete_off' => 1,
						'inline' => 1,
						'max_length' => 64,
						'error_text' => $locale['u136']
					));
				}
				$this->info['user_admin_password'] .= form_text('user_admin_password2', $locale['u145'], '', array(
					'class' => 'm-b-0',
					'type' => 'password',
					'autocomplete_off' => 1,
					'inline' => 1,
					'max_length' => 64,
					'error_text' => $locale['u136']
				));
				$this->info['user_admin_password'] .= "<div class='col-xs-12 col-sm-offset-3 col-md-offset-3 col-lg-offset-3'><span class='text-smaller'>".$locale['u147']."</span></div>\n";
			}
			// Avatar Field
			$this->info['user_avatar'] = '';
			if (!$this->registration) {
				if (isset($this->userData['user_avatar']) && $this->userData['user_avatar'] != "") {
					$this->info['user_avatar'] = "<label for='user_avatar_upload'><img src='".IMAGES."avatars/".$this->userData['user_avatar']."' alt='".$locale['u185']."' />
											</label>\n<br />\n<input type='checkbox' name='delAvatar' value='1' class='textbox' /> ".$locale['u187']."<br />\n<br />\n";
				} else {
					$this->info['user_avatar'] = form_fileinput('user_avatar', $locale['u185'], '', array(
						'upload_path' => IMAGES."avatars/",
						'input_id' => 'user_avatar_upload',
						'type' => 'image',
						'max_bytes' => fusion_get_settings('avatar_filesize'),
						'max_height' => fusion_get_settings('avatar_width'),
						'max_width' => fusion_get_settings('avatar_height'),
						'inline' => TRUE,
						'thumbnail' => 0,
						'width' => '100%',
						"delete_original" => FALSE,
						'class' => 'm-t-10 m-b-0',
						"error_text" => $locale['u180'],
					));
					$this->info['user_avatar'] .= "<div class='col-xs-12 col-sm-offset-3 col-md-offset-3 col-lg-offset-3'><span class='text-smaller'>
					".sprintf($locale['u184'], parsebytesize(fusion_get_settings('avatar_filesize')), fusion_get_settings('avatar_width'), fusion_get_settings('avatar_height'))."</span></div>\n";
				}
			}
			// Email
			$this->info['user_email'] = form_text('user_email', $locale['u128'], $user_email, array(
				'type' => 'email',
				"required" => TRUE,
				'inline' => 1,
				'max_length' => '100',
				'error_text' => $locale['u126']
			));
			// Hide email toggler
			$this->info['user_hide_email'] = form_btngroup('user_hide_email', $locale['u051'], array(
				$locale['u053'],
				$locale['u052']
			), $user_hide_email, array('inline' => 1));
			// Captcha
			if ($this->displayValidation == 1 && !defined('ADMIN_PANEL')) $this->info['validate'] = $this->renderValidation();
			// Website terms
			if ($this->displayTerms == 1) $this->info['terms'] = $this->renderTerms();
		}
		$this->info += array(
			'register' => $this->registration,
			'pages' => ($this->paginate && !$this->registration) ? $this->info['section'] = $this->renderPageLink() : '',
			'openform' => openform($this->formname, 'post', FUSION_REQUEST, array(
				'enctype' => "".($this->showAvatarInput ? 1 : 0)."",
				'max_tokens' => 1
			)),
			'closeform' => closeform(),
			'button' => $this->renderButton(),
		);
		$this->get_userFields();
		render_userform($this->info);
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
			if (!empty($this->userData['user_avatar']) && file_exists(IMAGES."avatars/".$this->userData['user_avatar'])) {
				$this->userData['user_avatar'] = IMAGES."avatars/".$this->userData['user_avatar'];
			} else {
				$this->userData['user_avatar'] = IMAGES."avatars/noavatar150.png";
			}
			$this->info['core_field']['profile_user_avatar'] = array(
				'title' => $locale['u186'],
				'value' => $this->userData['user_avatar'],
				'status' => $this->userData['user_status']
			);
			// user name
			$this->info['core_field']['profile_user_name'] = array(
				'title' => $locale['u068'],
				'value' => $this->userData['user_name']
			);
			// user level
			$this->info['core_field']['profile_user_level'] = array(
				'title' => $locale['u063'],
				'value' => getgroupname($this->userData['user_level'])
			);
			// user email
			if (iADMIN || $this->userData['user_hide_email'] == 0) $this->info['core_field']['profile_user_email'] = array(
				'title' => $locale['u064'],
				'value' => hide_email($this->userData['user_email'])
			);
			// user joined
			$this->info['core_field']['profile_user_joined'] = array(
				'title' => $locale['u066'],
				'value' => showdate("longdate", $this->userData['user_joined'])
			);
			// user last visit
			$lastVisit = $this->userData['user_lastvisit'] ? showdate("longdate", $this->userData['user_lastvisit']) : $locale['u042'];
			$this->info['core_field']['profile_user_visit'] = array('title' => $locale['u067'], 'value' => $lastVisit);
			// user status
			if (iADMIN && $this->userData['user_status'] > 0) {
				$this->info['core_field']['profile_user_status'] = array(
					'title' => $locale['u055'],
					'value' => getuserstatus($this->userData['user_status'])
				);
				$this->info['core_field']['profile_user_reason'] = array(
					'title' => $locale['u056'],
					'value' => $this->userData['suspend_reason']
				);
			}
			// IP
			(iADMIN && checkrights("M")) ? $this->info['core_field']['profile_user_ip'] = array(
				'title' => $locale['u049'],
				'value' => $this->userData['user_ip']
			) : '';
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
		// @todo: bugfix the href src in Admin
		$this->info['section'] = $this->renderPageLink();
		// Module Items -- got $user_info['field'];
		self::get_userFields();
		// buttons.. 2 of them.
		if (iMEMBER && $userdata['user_id'] != $this->userData['user_id']) {
			$this->info['buttons'][] = array(
				'link' => BASEDIR."messages.php?msg_send=".$this->userData['user_id'],
				'name' => $locale['u043']
			);
			if (iADMIN && checkrights("M") && $this->userData['user_level'] < "-102" && $this->userData['user_id'] != "1") {
				$this->info['buttons'][] = array(
					'link' => ADMIN."members.php".$aidlink."&amp;step=log&amp;user_id=".$this->userData['user_id'],
					'name' => $locale['u054']
				);
				$this->info['buttons'][] = self::renderAdminOptions();
			}
		}
	}


	/* Fetches UF Module extends Userdata with 3rd party Databases */
	// reacts with $method var ('input', 'display');
	private function get_userFields() {
		$this->callback_data = $this->userData;
		$index_page_id = isset($_GET['profiles']) && isnum($_GET['profiles']) ? intval($_GET['profiles']) : 1;
		$result = dbquery("SELECT field.*,
				cat.field_cat_id, cat.field_cat_name, cat.field_parent,
				root.field_cat_id as page_id, root.field_cat_name as page_name, root.field_cat_db, root.field_cat_index
				FROM ".DB_USER_FIELDS." field
				INNER JOIN ".DB_USER_FIELD_CATS." cat ON (cat.field_cat_id = field.field_cat)
				INNER JOIN ".DB_USER_FIELD_CATS." root on (cat.field_parent = root.field_cat_id)
				WHERE cat.field_cat_id='$index_page_id' OR root.field_cat_id='$index_page_id'
				ORDER BY root.field_cat_order, cat.field_cat_order, field.field_order");
		if (dbrows($result) > 0) {
			// loop
			while ($data = dbarray($result)) {
				if ($data['field_cat_id']) $category[$data['field_parent']][$data['field_cat_id']] = self::parse_label($data['field_cat_name']);
				if ($data['field_cat']) $item[$data['field_cat']][] = $data;
				if ($data['field_cat_db'] && $data['field_cat_index'] && $data['field_cat_db'] !== 'users') {
					// extend userData
					$this->callback_data += dbarray(dbquery("SELECT * FROM ".DB_PREFIX.$data['field_cat_db']." WHERE ".$data['field_cat_index']."='".$this->userData['user_id']."'"));
				}
			}
			if ($this->method == 'input') {
				$this->info['user_field'] = $_GET['profiles'] !== 1 ? form_hidden('user_id', '', $this->userData['user_id']) : '';
				$this->info['user_field'] .= $_GET['profiles'] !== 1 ? form_hidden('user_name', '', $this->userData['user_name']) : '';
			} elseif ($this->method == 'display') {
				$this->info['user_field'] = array();
			}
			// filter display - input and display method.
			if (isset($category[$index_page_id])) {
				foreach ($category[$index_page_id] as $cat_id => $cat) {
					if ($this->registration || $this->method == 'input') {
						$this->method = 'input';
						if (isset($item[$cat_id])) {
							$this->info['user_field'] .= form_para($cat, $cat_id, 'profile_category_name');
							foreach ($item[$cat_id] as $field_id => $field) {
								$options = array(
									'show_title' => TRUE,
									'inline' => TRUE,
									'required' => (bool)$field['field_required']
								);
								if ($field['field_type'] == 'file') {
									$options += array(
										'plugin_folder' => $this->plugin_folder,
										'plugin_locale_folder' => $this->plugin_locale_folder
									);
								}
								$this->info['user_field'] .= $this->display_fields($field, $this->userData, $this->method, $options);
							}
						}
					} else {
						$this->method = 'display';
						if (isset($item[$cat_id])) {
							$this->info['user_field'][$cat_id]['title'] = form_para($cat, $cat_id, 'profile_category_name');
							foreach ($item[$cat_id] as $field_id => $field) {
								$render = $this->display_fields($field, $this->userData, $this->method);
								if ((isset($this->callback_data[$field['field_name']]) && $this->callback_data[$field['field_name']] || $field['field_type'] == 'file') && $render) {
									$this->info['user_field'][$cat_id]['fields'][$field['field_id']] = $render;
								}
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
		return $this->html;
	}

	private function renderTerms() {
		global $locale;
		$html = "<div class='form-group clearfix'>";
		$html .= "<label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0'>".$locale['u192']." <span class='required'>*</span></label>";
		$html .= "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n";
		$html .= form_checkbox('agreement', $locale['u193'], '');
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
		global $locale;
		$_CAPTCHA_HIDE_INPUT = FALSE;
		$html = "<hr>\n";
		$html .= "<div class='form-group clearfix'>";
		$html .= "<label for='captcha_code' class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0'>\n".$locale['u190']." <span class='required'>*</span></label>\n";
		$html .= "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>";
		ob_start();
		include INCLUDES."captchas/".fusion_get_settings("captcha")."/captcha_display.php";
		$html .= ob_get_contents();
		ob_end_clean();
		if (!$_CAPTCHA_HIDE_INPUT) {
			$html .= form_text('captcha_code', '', '', array(
				'inline' => 1,
				'required' => 1,
				'autocomplete_off' => 1,
				'width' => '200px',
				'class' => 'm-t-15',
				'placeholder' => $locale['u191']
			));
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
