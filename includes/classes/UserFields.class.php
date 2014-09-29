<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: UserFields.class.php
| Author: Hans Kristian Flaatten (Starefossen)
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

class UserFields {
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
	// API 1.02
	private $field_db = DB_USERS;
	private $field_index = 'user_id';
	private $html = "";
	private $js = "";
	private $javaScriptOther;
	private $javaScriptRequired;
	private $method;
	private $_userNameChange = TRUE;

	public function displayInput() {
		global $locale, $aidlink;
		$this->method = "input";
		$enctype = $this->showAvatarInput ? " enctype='multipart/form-data'" : "";
		$this->html .= openform($this->formname, $this->formname, 'post', $this->formaction, array('enctype' => "".($this->showAvatarInput ? 1 : 0)."", 'downtime' => 0));
		if (!$this->registration && !isset($_GET['aid'])) {
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
			$title = $locale['u101'];
			$Output = $this->renderBasicInputFields();
			$Output .= $this->renderFields();
		}
		$this->html .= "<div class='row m-b-20'>\n";
		if (!$this->registration && !isset($_GET['aid'])) {
			// edit profile.
			add_to_title($locale['u102']);
			$this->html .= "<div class='col-xs-12 col-sm-3 col-md-2 col-lg-2 p-r-0'>\n";
			$this->html .= "<ul id='profile-li' class='pull-left m-t-10'>\n";
			$this->html .= "<li ".(!isset($_GET['profiles']) ? "class='active'" : '')."><a href='".(isset($_GET['aid']) ? $this->formaction : BASEDIR."edit_profile.php")."'><i class='entypo cog m-r-10'></i>".$locale['uf_103']."</a></li>\n";
			$this->html .= "<li ".(isset($_GET['profiles']) && $_GET['profiles'] == 'biography' ? "class='active'" : '')."><a href='".(isset($_GET['aid']) ? $this->formaction."&amp;" : BASEDIR."edit_profile.php?")."profiles=biography'><i class='entypo lock m-r-10'></i>".$locale['uf_104']."</a></li>\n";
			$this->html .= ($this->showAvatarInput) ? "<li ".(isset($_GET['profiles']) && $_GET['profiles'] == 'avatar' ? "class='active'" : '')." style='border-bottom:1px solid #ccc;'><a href='".(isset($_GET['aid']) ? $this->formaction."&amp;" : BASEDIR."edit_profile.php?")."profiles=avatar'><i class='entypo picture m-r-10'></i>".$locale['uf_105']."</a></li>\n" : '';
			$this->html .= $this->renderPageLink();
			$this->html .= "</ul>\n";
			$this->html .= "</div>\n";
			$this->html .= "<div class='col-xs-12 col-sm-9 col-md-10 col-lg-10'>\n";
		} else {
			$this->html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
		}
		$this->html .= "<div class='panel panel-default' style='border:0px;'>\n";
		$this->html .= "<div class='panel-body'>\n";
		$this->html .= "<table cellpadding='0' cellspacing='0' class='table center edit-profile table-responsive'>\n";
		$this->html .= $Output;
		if ($this->displayValidation == 1) {
			$this->renderValidation();
		}
		if ($this->displayTerms == 1) {
			$this->renderTerms();
		}
		$this->renderButton();
		$this->html .= "</div></div>\n";
		$this->html .= "</div></div>\n";
		$this->html .= "</table>\n</form>\n";
		$this->js .= "<script type='text/javascript'>\n";
		$this->js .= "/*<![CDATA[*/\n";
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
		$this->js .= "/*]]>*/\n";
		$this->js .= "</script>\n";
		add_to_footer($this->js);
		add_to_jquery("
        $('#".$this->postName."').bind('click', function(e){ ValidateForm('#".$this->formname."')});
        ");
		echo $this->html;
	}

	public function displayOutput() {
		global $locale, $userdata;
		$this->method = "display";
		$this->html .= ($this->showPages) ? "<section class='row'>\n" : '';
		$this->html .= ($this->showPages) ? "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3 pull-left'>\n" : '';
		// display menu we'll skip into page according to the user fields class.
		$find = array('&amp;profiles=biography');
		$replace = array("");
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
			$Output = $this->renderBasicOutputFields();
			if (isset($_GET['profiles'])) {
				if ($_GET['profiles'] == 'biography') {
					$title = $locale['uf_104'];
					$Output = $this->renderFields();
				} else {
					$title = ucwords($_GET['profiles'])." Settings";
					$Output = $this->renderFields();
				}
			}
		} else {
			$Output = $this->renderBasicOutputFields();
			//$Output .= $this->renderFields();
			$Output .= iADMIN && checkrights("M") ? $this->renderIPOutput() : '';
			$Output .= $this->userData['user_groups'] ? $this->renderUserGroups() : '';
			$Output .= ($this->showAdminOptions && iADMIN && checkrights("M") && $this->userData['user_id'] != $userdata['user_id'] && $this->userData['user_level'] < 102) ? $this->renderAdminOptions() : '';
		}
		$this->html .= ($this->showPages) ? "<ul id='profile-li'>\n" : '';
		$this->html .= ($this->showPages) ? "<li ".(!isset($_GET['profiles']) ? "class='active'" : '')."><a href='".($this->baseRequest ? $base_request : BASEDIR."profile.php?lookup=".$this->userData['user_id'])."'><i class='entypo cog m-r-10'></i>General</a></li>\n" : '';
		$this->html .= ($this->showPages) ? "<li ".(isset($_GET['profiles']) && $_GET['profiles'] == 'biography' ? "class='active'" : '')."><a href='".(isset($_GET['profiles']) && $_GET['profiles'] == 'biography' ? FUSION_REQUEST : "".($this->baseRequest ? $base_request."&amp;" : BASEDIR."profile.php?")."profiles=biography&amp;lookup=".$this->userData['user_id']."")." '><i class='entypo lock m-r-10'></i>User Information</a></li>\n" : '';
		$this->html .= ($this->showPages) ? $this->renderPageLink() : '';
		$this->html .= ($this->showPages) ? "</div>\n" : '';
		$this->html .= ($this->showPages) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : '';
		$this->html .= ($this->showPages) ? "<div class='panel panel-default tbl-border'>\n" : '';
		$this->html .= ($this->showPages) ? "<div class='panel-body'>\n" : '';
		$this->html .= $Output;
		$this->html .= ($this->showPages) ? "</div>\n</div>\n" : '';
		$this->html .= ($this->showPages) ? "</div>\n" : '';
		$this->html .= ($this->showPages) ? "</section>\n" : '';
		echo $this->html;
	}

	public function setUserNameChange($value) {
		$this->_userNameChange = $value;
	}

	private function renderValidation() {
		global $settings, $locale;
		$_CAPTCHA_HIDE_INPUT = FALSE;
		$this->html .= "<tr>\n<td valign='top' class='tbl'><label for='captcha_code'>".$locale['u190'];
		$this->html .= "<span style='color:#ff0000'>*</span></label></td>\n<td class='tbl'>";
		ob_start();
		include INCLUDES."captchas/".$settings['captcha']."/captcha_display.php";
		$this->html .= ob_get_contents();
		ob_end_clean();
		if (!$_CAPTCHA_HIDE_INPUT) {
			$this->html .= "</td>\n</tr>\n<tr>";
			$this->html .= "<td class='tbl".$this->getErrorClass("user_captcha")."'><label for='captcha_code'>".$locale['u191']."</label></td>\n";
			$this->html .= "<td class='tbl".$this->getErrorClass("user_captcha")."'>";
			$this->html .= "<input type='text' id='captcha_code' name='captcha_code' class='textbox' autocomplete='off' style='width:100px' />";
		}
		$this->html .= "</td>\n</tr>\n";
	}

	private function renderTerms() {
		global $locale;
		$this->html .= "<tr>\n<td class='tbl'>".$locale['u192']."<span style='color:#ff0000'>*</span></td>\n";
		$this->html .= "<td class='tbl'><input type='checkbox' id='agreement' name='agreement' value='1' onclick='checkagreement()' />\n";
		$this->html .= "<span class='small'><label for='agreement'>".$locale['u193']."</label></span>\n";
		$this->html .= "</td>\n</tr>\n";
		$this->javaScriptOther .= "	function checkagreement() {\n";
		$this->javaScriptOther .= "		if(document.inputform.agreement.checked) {\n";
		$this->javaScriptOther .= "			document.inputform.register.disabled=false;\n";
		$this->javaScriptOther .= "		} else {\n";
		$this->javaScriptOther .= "			document.inputform.register.disabled=true;\n";
		$this->javaScriptOther .= "		}\n";
		$this->javaScriptOther .= "	}\n";
	}

	private function basicInputField($name, $text, $length, $isRequired = "", $type = "text", $haveValue = TRUE, $error_class = "") {
		$errorClass = $error_class != "" ? $error_class : $name;
		$class = $errorClass." tbl".$this->getErrorClass($errorClass);
		if ($haveValue) {
			$value = isset($this->userData[$name]) ? $this->userData[$name] : "";
			$value = isset($_POST[$name]) ? stripinput($_POST[$name]) : $value;
		} else {
			$value = "";
		}
		if ($isRequired != "") {
			$required = "<span style='color:#ff0000'>*</span>";
			$this->setRequiredJavaScript($name, $isRequired);
		} else {
			$required = "";
		}
		$returnHTML = "<tr>\n";
		$returnHTML .= "<td class='".$class."' width='150'><label for='".$name."'>".$text.$required."</label></td>\n";
		$returnHTML .= "<td class='".$class."'>";
		$returnHTML .= "<input type='".$type."' id='".$name."' name='".$name."' maxlength='".$length."' class='textbox form-control' value='".$value."' style='width:250px;'".($type == "password" ? " autocomplete='off'" : "")." />";
		$returnHTML .= "</td>\n</tr>\n";
		return $returnHTML;
	}

	private function renderBasicInputFields() {
		global $locale;
		$html = '';
		// Account info
		$html .= "<tr>\n<td colspan='2' class='profile_category_name tbl2'><strong>".$locale['u129']."</strong></td></tr>\n";
		// Username
		$html .= (iADMIN || $this->_userNameChange ? $this->basicInputField("user_name", $locale['u127'], "30", $locale['u122']) : "");
		// Login Password
		$passRequired = $this->skipCurrentPass ? $locale['u136'] : "";
		$passRequired = $this->isAdminPanel ? "" : $passRequired;
		$html .= (!$this->registration) ? "<tr>\n<td colspan='2'><div class='alert alert-info'>".$locale['u100']."</div>\n</td></tr>\n" : '';
		$html .= "<tr>\n<td colspan='2' class='profile_category_name tbl2'><strong>".$locale['u133']."</strong></td>\n</tr>\n";
		if (!$this->skipCurrentPass) {
			$html .= $this->basicInputField("user_password", $locale['u133'], "64", "", "password", FALSE, "user_password");
		}
		$html .= $this->basicInputField("user_new_password", ($this->registration == TRUE ? $locale['u133'] : $locale['u134']), "64", $passRequired, "password", FALSE, "user_password");
		$html .= $this->basicInputField("user_new_password2", $locale['u135'], "64", $passRequired, "password", FALSE, "user_password");
		$html .= "<tr>\n<td class='tbl'></td>\n<td class='tbl'><small>".$locale['u147']."</small></td>\n</tr>\n";
		// Admin Password
		if ($this->showAdminPass && iADMIN) {
			$html .= "<tr>\n<td colspan='2' class='profile_category_name tbl2'><strong>".$locale['u130']."</strong></td></tr>\n";
			if ($this->userData['user_admin_password']) {
				$html .= $this->basicInputField("user_admin_password", $locale['u131'], "64", "", "password", FALSE, "user_admin_password");
			}
			$html .= $this->basicInputField("user_new_admin_password", ($this->userData['user_admin_password'] ? $locale['u144'] : $locale['u131']), "64", "", "password", FALSE, "user_admin_password");
			$html .= $this->basicInputField("user_new_admin_password2", $locale['u145'], "64", "", "password", FALSE, "user_admin_password");
			$html .= "<tr>\n<td class='tbl'></td>\n<td class='tbl'><small>".$locale['u147']."</small></td>\n</tr>\n";
		}
		// email field
		$html .= "<tr>\n<td colspan='2' class='profile_category_name tbl2'><strong>".$locale['u064']."</strong></td></tr>\n";
		$html .= $this->basicInputField("user_email", $locale['u128'], "100", $locale['u126']);
		// Hide email toggler
		$hide = isset($this->userData['user_hide_email']) ? $this->userData['user_hide_email'] : 1;
		$hide = isset($_POST['user_hide_email']) && isnum($_POST['user_hide_email']) ? $_POST['user_hide_email'] : $hide;
		$html .= "<tr>\n";
		$html .= "<td class='tbl'>".$locale['u051']."</td>\n<td class='tbl'>";
		$html .= "<label><input type='radio' name='user_hide_email' value='1'".($hide == 1 ? " checked='checked'" : "")." />".$locale['u052']."</label>\n";
		$html .= "<label><input type='radio' name='user_hide_email' value='0'".($hide == 0 ? " checked='checked'" : "")." />".$locale['u053']."</label>";
		$html .= "</td>\n</tr>\n";
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
		$lastVisit = $this->userData['user_lastvisit'] ? showdate("longdate", $this->userData['user_lastvisit']) : $locale['u042'];
		$returnFields .= $this->basicOutputField($locale['u066'], showdate("longdate", $this->userData['user_joined']), "profile_user_joined");
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

	// UF API 1.02
	private function _findDB() {
		if (isset($_GET['profiles']) && $_GET['profiles'] !== 'biography') {
			$result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_cat_page='1' AND field_cat_name LIKE '".strtolower(stripinput($_GET['profiles']))."' LIMIT 1");
			if (dbrows($result) > 0) {
				$data = dbarray($result);
				if ($data['field_cat_db']) {
					$this->field_db = DB_PREFIX.$data['field_cat_db'];
					$this->field_index = $data['field_cat_index'];
					$uquery = dbquery("SELECT * FROM ".$this->field_db." WHERE ".$this->field_index."='".$this->userData['user_id']."' LIMIT 1");
					if (dbrows($uquery) > 0) {
						$data = dbarray($uquery);
						return $data;
					}
				} else {
					return $this->userData;
				}
			}
		}
	}

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
					include LOCALE.LOCALESET."user_fields/".$data['field_name'].".php";
				}
				if (file_exists(INCLUDES."user_fields/".$data['field_name']."_include.php")) {
					include INCLUDES."user_fields/".$data['field_name']."_include.php";
				}
			}
		} else {
			echo "<div class='alert alert-danger text-center'>".$locale['108']."</div>\n";
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
				$c_html .= $fields[$cat['field_cat']];
				$html .= $c_html;
				$i++;
				if ($this->method == "display") {
					$html .= "</tbody>\n</table>\n";
				}
			}
		}
		if (!$c_html) {
			$html .= "<div class='text-center'>".sprintf($locale['uf_107'], ucwords($this->userData['user_name']))."</div>\n";
		}
		if (count($fields > 0)) {
			$html .= "<!--userfield_end-->\n";
		}
		return $html;
	}

	/* Construct of New Page and it's display input. */
	private function renderPageLink() {
		// build this page.
		$html = "";
		$result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_cat_page='1' ORDER BY field_cat_order");
		if (dbrows($result) > 0) {
			$find = array('&amp;profiles=biography');
			$replace = array("");
			$link = array();
			while ($data = dbarray($result)) {
				$find[] = "&amp;profiles=".strtolower($data['field_cat_name'])."";
				$replace[] = '';
				$link[] = $data;
			}
			// self regeneration.
			if (count($link) && !empty($link)) {
				foreach ($link as $data) {
					$base_request = strtr(FUSION_REQUEST, array_combine($find, $replace));
					$html .= "<li ".(isset($_GET['profiles']) && $_GET['profiles'] == strtolower($data['field_cat_name']) ? "class='active'" : '')." />";
					$html .= "<a href='".(isset($_GET['profiles']) && $_GET['profiles'] == strtolower($data['field_cat_name']) ? FUSION_REQUEST : "".($this->baseRequest ? $base_request."&amp;" : "".(isset($_GET['aid']) ? $this->formaction."&amp;" : BASEDIR."".($this->method == 'input' ? "edit_profile.php?" : "profile.php?")."")."")."profiles=".strtolower($data['field_cat_name'])."".(isset($_GET['aid']) ? '' : "&amp;lookup=".$this->userData['user_id']."")."")." '>".($data['field_cat_class'] ? "<i class='m-r-10 entypo ".$data['field_cat_class']."'/></i>" : "")."".ucwords($data['field_cat_name'])."</a></li>\n";
				}
			}
		}
		return $html;
	}

	private function renderButton() {
		$dissabled = $this->displayTerms == 1 ? " disabled='disabled'" : "";
		$this->html .= "<tr>\n<td align='center' colspan='2'><br />\n";
		if (!$this->skipCurrentPass) {
			$this->html .= "<input type='hidden' name='user_hash' value='".$this->userData['user_password']."' />\n";
		}
		$this->html .= "<button type='submit' name='".$this->postName."' value='".$this->postValue."' class='btn btn-primary'".$dissabled." />".$this->postValue."</button>\n";
		$this->html .= "</td>\n</tr>\n";
	}

	private function isError() {
		if (count($this->errorsArray) == 0) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	private function getErrorClass($field) {
		if (isset($this->errorsArray[$field])) {
			return " tbl-error";
		} else {
			return "";
		}
	}

	private function setRequiredJavaScript($field, $message) {
		$this->javaScriptRequired .= "		if (frm.".$field.".value==\"\") {\n";
		$this->javaScriptRequired .= "			alert(\"".$message."\");\n";
		$this->javaScriptRequired .= "			return false;\n";
		$this->javaScriptRequired .= "		}\n";
	}
}

?>