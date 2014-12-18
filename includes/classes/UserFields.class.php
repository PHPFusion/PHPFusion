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
require_once CLASSES."QuantumFields.class.php";

class UserFields extends quantumFields {
	public $displayTerms = 0;
	public $displayValidation = 0;
	public $errorsArray = array();
	public $formaction = FUSION_REQUEST; // changed in API 1.02
	public $formname = "inputform";
	public $isAdminPanel = FALSE;
	public $postName;
	public $postValue;
	public $showAdminOptions = FALSE;
	public $showAdminPass = TRUE;
	public $showAvatarInput = TRUE;
	public $showAvatarOutput = TRUE; // new in API 1.02 - opts to turn off Avatar
	public $showPages = TRUE; // new in API 1.02 - opts to break fields into pages.
	public $baseRequest = FALSE; // new in API 1.02 - turn fusion_self to fusion_request - 3rd party pages. Turn this on if you have more than one $_GET pagination str.
	public $skipCurrentPass = FALSE;
	public $registration = FALSE;
	public $userData = array("user_name", "user_password", "user_admin_password", "user_email");

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
	//	private $field_index = 'user_id';

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
			$link = array();
			$find = array(); $replace = array();
			while ($data = dbarray($result)) {
				$find[] = "&amp;profiles=".$data['field_cat_id'];
				$replace[] = '';
				$link[] = $data;
			}
			// self regeneration.
			if (count($link) && !empty($link)) {
				$i = 0;
				foreach ($link as $data) {
					$base_request = strtr(FUSION_REQUEST, array_combine($find, $replace));
					$cur_link = '';
					if (isset($_GET['profiles']) && $_GET['profiles'] == $data['field_cat_id']) {
						$cur_link = FUSION_REQUEST;
					} else {
						if ($this->baseRequest) {
							$cur_link = $base_request."&amp;";
						} else {
							$cur_link = BASEDIR;
							if ($this->method == 'input') {
								$cur_link .= "edit_profile.php";
							} else {
								$cur_link .= "profile.php";
							}
							$cur_link .= isset($_GET['aid']) ? $aidlink."&amp;profiles=".$data['field_cat_id'] : "?profiles=".$data['field_cat_id'];
							$cur_link .= "&amp;lookup=".$this->userData['user_id'];
						}
					}

					$section[] = array(
						'active'=>(isset($_GET['profiles']) && $_GET['profiles'] == $data['field_cat_id']) ? 1 : (!isset($_GET['profiles']) && $i == 0 ? 1 : 0),
						'link'=>$cur_link,
						'name'=>ucwords($data['field_cat_name'])
					);
					//$html .= "<li ".(isset($_GET['profiles']) && $_GET['profiles'] == strtolower($data['field_cat_name']) ? "class='active'" : '')." />";
					//$html .= "<a href='".(isset($_GET['profiles']) && $_GET['profiles'] == strtolower($data['field_cat_name']) ? FUSION_REQUEST : "".($this->baseRequest ? $base_request."&amp;" : "".(isset($_GET['aid']) ? $this->formaction."&amp;" : BASEDIR."".($this->method == 'input' ? "edit_profile.php?" : "profile.php?")."")."")."profiles=".strtolower($data['field_cat_name'])."".(isset($_GET['aid']) ? '' : "&amp;lookup=".$this->userData['user_id']."")."")." '>".($data['field_cat_class'] ? "<i class='m-r-10 entypo ".$data['field_cat_class']."'/></i>" : "")."".ucwords($data['field_cat_name'])."</a></li>\n";
					$i++;
				}
			}
		}
		return $section;
	}

	/* Old Field Input */
	/*
	public function displayInput() {
		global $locale, $aidlink;
		$this->method = "input";
		$info = array();
		$info['item'] = openform($this->formname, $this->formname, 'post', $this->formaction, array('enctype' => "".($this->showAvatarInput ? 1 : 0)."", 'downtime' => 0));
		if (!$this->registration && !isset($_GET['aid'])) {
			// edit profile
			$title = $locale['uf_100'];
			$Output = $this->renderBasicInputFields();
			if (isset($_GET['profiles'])) {
				if ($_GET['profiles'] == 'biography') {
					$title = $locale['uf_101'];
					$Output = $this->renderFields();
				} elseif ($_GET['profiles'] == 'avatar' && ($this->showAvatarInput)) {
					$title = $locale['uf_102'];
					$Output = $this->renderAvatarInput();
				} else {
					$title = ucwords($_GET['profiles'])." Settings"; // this need fix.
					$Output = $this->renderFields();
				}
			}
		} else {
			// Main Registration Mode and admin
			$title = $locale['u101'];
			$Output = $this->renderBasicInputFields();
			$Output .= $this->renderFields();
		}

		if (!$this->registration && !isset($_GET['aid'])) {
			// edit profile.
			$info['section'][] = array('link'=>isset($_GET['aid']) ? $this->formaction : BASEDIR."edit_profile.php", 'name'=>$locale['uf_103']);
			$ecc = $this->renderPageLink();
			$info['section'] = array_merge($info['section'], $ecc['section']);
		} else {
//			$this->html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
		}

		$info['item'] = $Output;
		if ($this->displayValidation == 1) {
			$info['item'] .= $this->renderValidation();
		}
		if ($this->displayTerms == 1) {
			$info['item'] .= $this->renderTerms();
		}
		$info['item'] .= closeform();
		$info['button'] = $this->renderButton();

		//$this->html .= "</div></div>\n";
		//$this->html .= "</div></div>\n";
		//$this->html .= "</table>\n</form>\n";
		/* $this->js .= "<script type='text/javascript'>\n";
		$this->js .= "	function ValidateForm(frm) {\n";
		$this->js .= "		if ($(frm.user_new_password).val() != \"\") {\n";
		$this->js .= "			if ($(frm.user_new_password2).val() != $(frm.user_new_password).val()) {\n";
		$this->js .= "				$(frm.user_new_password2).addClass(\"tbl-error\");\n";
		$this->js .= "				alert(\"".$locale['u132'].$locale['u143']."\");\n";
		$this->js .= "				return false;\n";
		$this->js .= "			}\n";
		$this->js .= "			$(frm.user_new_password2).removeClass(\"tbl-error\");\n";
		if ($this->registration == FALSE) {
			$this->js .= "			if ($(frm.user_password).val() == \"\") {\n";
			$this->js .= "				$(frm.user_password).addClass(\"tbl-error\");\n";
			$this->js .= "				alert(\"".$locale['u138']."\");\n";
			$this->js .= "				return false;\n";
			$this->js .= "			}\n";
		}
		$this->js .= "			$(frm.user_password).removeClass(\"tbl-error\");\n";
		$this->js .= "			if ($(frm.user_new_password).val() == $(frm.user_password).val()) {\n";
		$this->js .= "				$(frm.user_new_password).addClass(\"tbl-error\");\n";
		$this->js .= "				$(frm.user_new_password2).addClass(\"tbl-error\");\n";
		$this->js .= "				alert(\"".$locale['u134'].$locale['u146'].$locale['u133']."\");\n";
		$this->js .= "				return false;\n";
		$this->js .= "			}\n";
		$this->js .= "			$(frm.user_new_password).removeClass(\"tbl-error\");\n";
		$this->js .= "			$(frm.user_new_password2).removeClass(\"tbl-error\");\n";
		$this->js .= "		}\n";
		$this->js .= $this->javaScriptRequired;
		$this->js .= "	}\n";
		$this->js .= $this->javaScriptOther;
		$this->js .= "</script>\n";
		add_to_footer($this->js);

		add_to_jquery("
        $('#".$this->postName."').bind('click', function(e){ ValidateForm('#".$this->formname."')});
        ");

		//render_registerform($info);

		//echo $this->html;
	} */
	/* Original Output Renders */
	/*
	public function displayOutput() {
		global $locale, $userdata;
		$this->method = "display";
		$this->html .= ($this->showPages) ? "<section class='row'>\n" : '';
		$this->html .= ($this->showPages) ? "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3 pull-left'>\n" : '';
		// display menu we'll skip into page according to the user fields class.
//		$find = array('&amp;profiles=biography');
//		$replace = array("");
		$result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_cat_page='1' ORDER BY field_cat_order");
		if (dbrows($result) > 0) {
			while ($data = dbarray($result)) {
				$find[] = "&amp;profiles=".strtolower($data['field_cat_name'])."";
				$replace[] = '';
			}
		}
		$base_request = strtr(FUSION_REQUEST, array_combine($find, $replace));
		if ($this->showPages) {
			$title = $locale['uf_106'];
			//$Output = $this->renderBasicOutputFields();
			if (isset($_GET['profiles'])) {
				if ($_GET['profiles'] == 'biography') {
			//		$title = $locale['uf_104'];
			//		$Output = $this->renderFields();
				} else {
					$title = ucwords($_GET['profiles'])." Settings";
					$Output = $this->renderFields();
				}
			}
		} else {
			// i think admin uses this.
			//$Output = $this->renderBasicOutputFields();
			//$Output .= $this->renderFields();
			//$Output .= iADMIN && checkrights("M") ? $this->renderIPOutput() : '';
			//$Output .= $this->userData['user_groups'] ? $this->renderUserGroups() : '';
			//$Output .= ($this->showAdminOptions && iADMIN && checkrights("M") && $this->userData['user_id'] != $userdata['user_id'] && $this->userData['user_level'] < 102) ? $this->renderAdminOptions() : '';
		}

		//$this->html .= ($this->showPages) ? "<ul id='profile-li'>\n" : '';
		//$this->html .= ($this->showPages) ? "<li ".(!isset($_GET['profiles']) ? "class='active'" : '')."><a href='".($this->baseRequest ? $base_request : BASEDIR."profile.php?lookup=".$this->userData['user_id'])."'><i class='entypo cog m-r-10'></i>".$locale['uf_103']."</a></li>\n" : '';
		//$this->html .= ($this->showPages) ? "<li ".(isset($_GET['profiles']) && $_GET['profiles'] == 'biography' ? "class='active'" : '')."><a href='".(isset($_GET['profiles']) && $_GET['profiles'] == 'biography' ? FUSION_REQUEST : "".($this->baseRequest ? $base_request."&amp;" : BASEDIR."profile.php?")."profiles=biography&amp;lookup=".$this->userData['user_id']."")." '><i class='entypo lock m-r-10'></i>".$locale['uf_104']."</a></li>\n" : '';
		//$this->html .= ($this->showPages) ? $this->renderPageLink() : '';
		//$this->html .= ($this->showPages) ? "</div>\n" : '';
		//$this->html .= ($this->showPages) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : '';
		//$this->html .= ($this->showPages) ? "<div class='panel panel-default tbl-border'>\n" : '';
		//$this->html .= ($this->showPages) ? "<div class='panel-body'>\n" : '';
		//$this->html .= $Output;
		//$this->html .= ($this->showPages) ? "</div>\n</div>\n" : '';
		//$this->html .= ($this->showPages) ? "</div>\n" : '';
		//$this->html .= ($this->showPages) ? "</section>\n" : '';
		//echo $this->html;
	}
	private function renderBasicOutputFields() {
		global $locale, $userdata, $aidlink;
		$rowspan = 4;
		$html = "<table cellpadding='0' cellspacing='1' class='table table-responsive profile tbl-border center'>\n";
		$html .= "<tr><td colspan='3' class='tbl2'><strong>Basic Information</strong></td></tr>";
		$returnFields = $this->basicOutputField($locale['u063'], getuserlevel($this->userData['user_level']), "profile_user_level");
		if (iADMIN || $this->userData['user_hide_email'] == 0) {
			$rowspan = $rowspan+1;
			$returnFields .= $this->basicOutputField($locale['u064'], hide_email($this->userData['user_email']), "profile_user_email");
		}
		$returnFields .= $this->basicOutputField($locale['u066'], showdate("longdate", $this->userData['user_joined']), "profile_user_joined");
		$lastVisit = $this->userData['user_lastvisit'] ? showdate("longdate", $this->userData['user_lastvisit']) : $locale['u042'];
		$returnFields .= $this->basicOutputField($locale['u067'], $lastVisit, "profile_user_visit");
		if ($this->userData['user_avatar'] == "" || !file_exists(IMAGES."avatars/".$this->userData['user_avatar'])) {
			$this->userData['user_avatar'] = "noavatar100.png";
		}
		$html .= $this->basicOutputField($locale['u068'], $this->userData['user_name'], "profile_user_name", $rowspan);
		$html .= $returnFields;
		if (iMEMBER && $userdata['user_id'] != $this->userData['user_id']) {
			$html .= "<tr><td colspan='3' class='user_profile_opts center tbl2'>";
			$html .= "<a href='".BASEDIR."messages.php?msg_send=".$this->userData['user_id']."' title='".$locale['u043']."'>".$locale['u043']."</a>\n";
			if (iADMIN && checkrights("M") && $this->userData['user_level'] != "103" && $this->userData['user_id'] != "1") {
				$html .= " - <a href='".ADMIN."members.php".$aidlink."&amp;step=log&amp;user_id=".$this->userData['user_id']."'>".$locale['u054']."</a>";
			}
			$html .= "<!--user_profile_opts-->";
			$html .= "</td>\n</tr>\n";
		}
		$html .= "</table>\n";
		if (iADMIN && $this->userData['user_status'] > 0) {
			$html .= "<div style='margin:5px'></div>\n";
			$html .= "<table cellpadding='0' cellspacing='1' class='table table-responsive profile tbl-border center'>\n<tr>\n";
			$html .= "<td colspan='2' class='tbl2'><strong>".$locale['u055']."</strong> ".getuserstatus($this->userData['user_status'])."</td>\n";
			$html .= "</tr>\n";
			$html .= $this->basicOutputField($locale['u056'], $this->userData['suspend_reason'], "profile_user_reason");
			$html .= "</table>\n";
		}
		return $html;
	}
	private function basicOutputField($name, $value, $class, $rowspan = 0) {
		global $locale;
		$returnHTML = "<tr>\n";
		if ($rowspan > 0 && $this->showAvatarOutput) {
			$returnHTML .= "<td rowspan='".$rowspan."' valign='top' class='tbl profile_user_avatar' width='1%'><!--profile_user_avatar-->";
			$returnHTML .= "<img class='img-thumbnail img-responsive' src='".IMAGES."avatars/".$this->userData['user_avatar']."' class='avatar' alt='".$locale['u062']."' title='".$locale['u062']."' />";
			$returnHTML .= "</td>\n";
		}
		$returnHTML .= "<td class='tbl1'>".$name."</td>\n";
		$returnHTML .= "<td align='right' class='".$class." tbl1'><!--".$class."-->".$value."</td>\n";
		$returnHTML .= "</tr>\n";
		return $returnHTML;
	}
	*/


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
		require_once THEMES."templates/global/profile.php";
		render_userform($this->info);
	}

	/* injected core input fields */
	private function basicInputFields() {
		global $locale;
		$html = '';
		// Account info
		// Username
		$html .= form_para($locale['u129'], 'account', 'profile_category_name');
		$html .= (iADMIN || $this->_userNameChange) ? form_text($locale['u127'], 'user_name', 'user_name', '', array('max_length'=>30, 'required'=>1, 'error_text'=>$locale['u122'], 'inline'=>1)) : '';
		// Login Password
		$passRequired = $this->skipCurrentPass ? 1 : 0; // $locale['u136'], password cannot be left empty.
		$passRequired = $this->isAdminPanel ? 0 : $passRequired;
		$html .= (!$this->registration) ? "<div class='alert alert-info'>".$locale['u100']."</div>" : '';
		$html .= form_para($locale['u132'], 'password', 'profile_category_name');
		if (!$this->skipCurrentPass) {
			$html .= form_text($locale['u133'], 'user_password', 'user_password_id', '', array('password'=>1, 'autocomplete_off'=>1, 'inline'=>1, 'max_length'=>64, 'error_text'=>$locale['u136']));
		}
		$html .= form_text($this->registration ? $locale['u133'] : $locale['u134'], 'user_new_password',' user_new_password', '', array('password'=>1, 'autocomplete_off'=>1, 'inline'=>1, 'max_length'=>64, 'error_text'=>$locale['u133']));
		$html .= form_text($locale['u135'], 'user_new_password2', 'user_new_password2', '', array('password'=>1, 'autocomplete_off'=>1, 'inline'=>1, 'max_length'=>64, 'error_text'=>$locale['u133']));
		$html .= "<div class='col-xs-12 col-sm-offset-3 col-md-offset-3 col-lg-offset-3'><span class='text-smaller'>".$locale['u147']."</span></div>\n";
		// Admin Password
		if ($this->showAdminPass && iADMIN) {
			//$html .= "<tr>\n<td colspan='2' class='profile_category_name tbl2'><strong>".$locale['u130']."</strong></td></tr>\n";
			if ($this->userData['user_admin_password']) {
				$html .= form_text($locale['u131'], 'user_admin_password', 'user_admin_password', '', array('password'=>1, 'autocomplete_off'=>1, 'inline'=>1, 'max_length'=>64, 'error_text'=>$locale['u136']));
			}
			$html .= form_text(($this->userData['user_admin_password'] ? $locale['u144'] : $locale['u131']), 'user_new_admin_password', 'user_new_admin_password', '', array('password'=>1, 'autocomplete_off'=>1, 'inline'=>1, 'max_length'=>64, 'error_text'=>$locale['u136']));
			$html .= form_text($locale['u145'], 'user_new_admin_password2', 'user_new_admin_password2', '', array('password'=>1, 'autocomplete_off'=>1, 'inline'=>1, 'max_length'=>64, 'error_text'=>$locale['u136']));
			$html .= "<div class='col-xs-12 col-sm-offset-3 col-md-offset-3 col-lg-offset-3'><span class='text-smaller'>".$locale['u147']."</span></div>\n";
		}
		// email field
		$html .= form_para($locale['u064'], 'email', 'profile_category_name');
		$html .= form_text($locale['u128'], 'user_email', 'user_email', '', array('email'=>1, 'inline'=>1, 'max_length'=>'100', 'error_text'=>$locale['u126']));
		// Hide email toggler
		$hide = isset($this->userData['user_hide_email']) ? $this->userData['user_hide_email'] : 1;
		$hide = isset($_POST['user_hide_email']) && isnum($_POST['user_hide_email']) ? $_POST['user_hide_email'] : $hide;
		$html .= form_btngroup($locale['u051'], 'user_hide_email', 'user_hide_email', array($locale['u053'], $locale['u052']), $hide, array('inline'=>1));
		return $html;
	}

	private function UserForm() {
		global $locale, $userdata, $aidlink;
		// Page Navigation - lets just shut off for registration - stupid to have multi page registration page
		if ($this->paginate && !$this->registration)  $this->info['section'] = $this->renderPageLink();
		$this->info['register'] = $this->registration;
		// Form Section
		// open form for token
		$this->info['openform'] = openform($this->formname, $this->formname, 'post', $this->formaction, array('enctype' => "".($this->showAvatarInput ? 1 : 0)."", 'downtime' => 0));
		// Basic account credentials - valid or we need to render hidden fields?
		if (!isset($_GET['profiles']) or (isset($_GET['profiles']) && $_GET['profiles'] == 1)) $this->info['basic_field'] = $this->basicInputFields();
		// Extended UF modules
		$this->get_userFields();
		// Captcha
		if ($this->displayValidation == 1) $this->info['validate'] = $this->renderValidation();
		// Website terms
		if ($this->displayTerms == 1) $this->info['terms'] = $this->renderTerms();
		// Close form html tag
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
		// Dyanmics core UF fields
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
		$result = dbquery("SELECT field.*,
				cat.field_cat_id, cat.field_cat_name, cat.field_parent,
				root.field_cat_id as page_id, root.field_cat_name as page_name, root.field_cat_db, root.field_cat_index
				FROM ".DB_USER_FIELDS." field
				LEFT JOIN ".DB_USER_FIELD_CATS." cat ON (cat.field_cat_id = field.field_cat)
				LEFT JOIN ".DB_USER_FIELD_CATS." root on (cat.field_parent = root.field_cat_id)
				".($this->registration ? "WHERE field_registration = '1'" : '')."
				ORDER BY root.field_cat_order, cat.field_cat_order, field.field_order");
		if (dbrows($result)>0) {
			$index_page_id = isset($_GET['profiles']) && isnum($_GET['profiles']) ? $_GET['profiles'] : 1;
			$this->info['user_field'] = ($this->method == 'display') ? array() : '';
			while ($data = dbarray($result)) {
				if ($data['field_cat_id']) $category[$data['field_parent']][$data['field_cat_id']] = $data['field_cat_name'];
				if ($data['field_cat']) $item[$data['field_cat']][] = $data;
				if ($data['field_cat_db'] && $data['field_cat_index'] && $data['field_cat_db'] !== 'users') {
					// extend userData
					$this->callback_data += dbarray(dbquery("SELECT * FROM ".DB_PREFIX.$data['field_cat_db']." WHERE ".$data['field_cat_index']."='".$this->userData['user_id']."'"));
				}
			}
			// filter display - input and display method.
			if (isset($category[$index_page_id])) {
				foreach($category[$index_page_id] as $cat_id => $cat) {
					if ($this->method == 'input') { // model as string because nothing to template in dynamics output.
						$this->info['user_field'] .= form_para($cat, $cat_id, 'profile_category_name');
						if (isset($item[$cat_id])) {
							foreach($item[$cat_id] as $field_id => $field) {
								$this->info['user_field'] .= $this->phpfusion_field_DOM($field);
							}
						}
					} elseif ($this->method == 'display') { // model as array because profile can be templated.
						$this->info['user_field'][$cat_id]['title'] = form_para($cat, $cat_id, 'profile_category_name');
						if (isset($item[$cat_id])) {
							foreach($item[$cat_id] as $field_id => $field) {
								if ($this->callback_data[$field['field_name']] && $this->phpfusion_field_DOM($field)) $this->info['user_field'][$cat_id]['fields'][$field['field_id']] = $this->phpfusion_field_DOM($field);
							}
						}
					}
				} // end foreach
			}
		}
	}


	/* Accessories MVC */
	private function renderIPOutput() {
		global $locale;
		$this->html .= "<div style='margin:5px'></div>\n";
		$this->html .= "<table cellpadding='0' cellspacing='1' class='table table-responsive profile tbl-border center'>\n<tr>\n";
		$this->html .= "<td colspan='2' class='tbl2'><strong>".$locale['u048']."</strong></td>\n";
		$this->html .= "</tr>\n";
		$this->html .= $this->basicOutputField($locale['u049'], $this->userData['user_ip'], "profile_user_ip");
		$this->html .= "</table>\n";
	}
	private function renderUserGroups() {
		global $locale;
		$this->html .= "<div style='margin:5px'></div>\n";
		$this->html .= "<table cellpadding='0' cellspacing='1' width='400' class='profile tbl-border center '>\n<tr>\n";
		$this->html .= "<td class='tbl2'><strong>".$locale['u057']."</strong></td>\n";
		$this->html .= "</tr>\n<tr>\n";
		$this->html .= "<td class='tbl1'>\n";
		$user_groups = strpos($this->userData['user_groups'], ".") == 0 ? substr($this->userData['user_groups'], 1) : $this->userData['user_groups'];
		$user_groups = explode(".", $user_groups);
		for ($i = 0; $i < count($user_groups); $i++) {
			$this->html .= "<div style='float:left'><a href='".FUSION_SELF."?group_id=".$user_groups[$i]."'>".getgroupname($user_groups[$i])."</a></div>\n";
			$this->html .= "<div style='float:right'>".getgroupname($user_groups[$i], TRUE)."</div>\n";
			$this->html .= "<div style='float:none;clear:both'></div>\n";
		}
		$this->html .= "</td>\n</tr>\n</table>\n";
	}
	private function renderAdminOptions() {
		global $locale, $groups_cache, $aidlink;
		if (!$groups_cache) {
			cache_groups();
		}
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
		$this->javaScriptOther .= "$('#agreement').bind('click', function() { checkagreement(); });";
		$this->javaScriptOther .= "	function checkagreement() {\n";
		$this->javaScriptOther .= "		if(document.inputform.agreement.checked) {\n";
		$this->javaScriptOther .= "			document.inputform.register.disabled=false;\n";
		$this->javaScriptOther .= "		} else {\n";
		$this->javaScriptOther .= "			document.inputform.register.disabled=true;\n";
		$this->javaScriptOther .= "		}\n";
		$this->javaScriptOther .= "	}\n";
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
		//$html .= "<tr>\n<td align='center' colspan='2'><br />\n";
		if (!$this->skipCurrentPass) {
			$html .= "<input type='hidden' name='user_hash' value='".$this->userData['user_password']."' />\n";
		}
		$html .= "<button type='submit' name='".$this->postName."' value='".$this->postValue."' class='btn btn-primary'".$dissabled." />".$this->postValue."</button>\n";
		//$this->html .= "</td>\n</tr>\n";
		return $html;
	}

	// Drop: Old model.
	private function renderFields() {
		global $settings, $locale, $userdata;
		$html = '';
		// API 1.02
		$user_data = $this->userData;
		if (isset($_GET['profiles']) && ($_GET['profiles'] !== 'biography') && !$this->registration) {
			$user_data = $this->_findDB(); // Find new user_data on 3rd party database.
		}
		$profile_method = $this->method;
		$fields = array();
		$cats = array();
		$obActiva = FALSE;
		$i = 0;
		if ($this->registration) {
			// on registration
			$where = "WHERE field_registration='1'";
		} else {
			// on edit.
			$where = (isset($_GET['profiles']) && $_GET['profiles'] !== 'biography') ? "WHERE tufc.field_cat_page='1' AND tufc.field_cat_name LIKE '".strtolower(stripinput($_GET['profiles']))."'" : "WHERE tufc.field_cat_page !='1'";
		}

		$result = dbquery("
        SELECT tufc.*, tuf.* FROM ".DB_USER_FIELD_CATS." tufc
         INNER JOIN ".DB_USER_FIELDS." tuf ON (tufc.field_cat_id = tuf.field_cat)
        ".$where." ORDER BY field_cat_order, field_order
        ");

		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				$required = $data['field_required'] == 1 ? "<span class='required'>*</span>" : "";
				if ($i != $data['field_cat']) {
					if ($obActiva) {
						$fields[$i] = ob_get_contents();
						ob_end_clean();
						$obActiva = FALSE;
					}
					$i = $data['field_cat'];
					$cats[] = array("field_cat_name" => $data['field_cat_name'], "field_cat" => $data['field_cat']);
				}
				if (!$obActiva) {
					ob_start();
					$obActiva = TRUE;
				}
				if (file_exists(LOCALE.LOCALESET."user_fields/".$data['field_name'].".php")) {
					include_once LOCALE.LOCALESET."user_fields/".$data['field_name'].".php";
				}
				if (file_exists(INCLUDES."user_fields/".$data['field_name']."_include.php")) {
					include_once INCLUDES."user_fields/".$data['field_name']."_include.php";
				}
			}
		} else {
			echo (!$this->registration) ? "<div class='alert alert-danger text-center'>".$locale['108']."</div>\n" : '';
		}
		if ($obActiva) {
			$fields[$i] = ob_get_contents();
			ob_end_clean();
		}
		$i = 1;
		$c_html = '';
		foreach ($cats as $cat) {
			if (array_key_exists($cat['field_cat'], $fields) && $fields[$cat['field_cat']]) {
				$html .= "<!--userfield_precat_".$i."-->\n";
				// this is show in profile.
				if ($this->method == "display") {
					$html .= "<div style='margin:5px'></div>\n";
					$html .= "<table style='width:100%;' class='table table-responsive profile_category tbl-border center'>\n<tbody>\n";
				}
				$html .= "<tr>\n";
				$html .= "<td colspan='2' class='profile_category_name tbl2'><strong>".$cat['field_cat_name']."</strong></td>\n";
				$html .= "</tr>\n";
				$html .= $fields[$cat['field_cat']];
				if ($fields[$cat['field_cat']]) {
					$c_html = 1;
				}
				$i++;
				if ($this->method == "display") {
					$html .= "</tbody>\n</table>\n";
				}
			}
		}
		if (!$c_html) {
			$html .= (!$this->registration) ? "<div class='text-center'>".sprintf($locale['uf_107'], ucwords($this->userData['user_name']))."</div>\n" : '';
		}
		if (count($fields > 0)) {
			$html .= "<!--userfield_end-->\n";
		}
		return $html;
	}





	// deprecate
	private function isError() {
		if (count($this->errorsArray) == 0) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	// deprecate
	private function getErrorClass($field) {
		if (isset($this->errorsArray[$field])) {
			return " tbl-error";
		} else {
			return "";
		}
	}
	// deprecate
	private function setRequiredJavaScript($field, $message) {
		$this->javaScriptRequired .= "		if (frm.".$field.".value==\"\") {\n";
		$this->javaScriptRequired .= "			alert(\"".$message."\");\n";
		$this->javaScriptRequired .= "			return false;\n";
		$this->javaScriptRequired .= "		}\n";
	}
}

?>