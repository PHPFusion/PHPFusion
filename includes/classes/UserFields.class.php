<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

class UserFields {
	public $displayTerms 				= 0;
	public $displayValidation 			= 0;
	public $errorsArray 				= array();
	public $formaction 					= FUSION_SELF;
	public $formname 					= "inputform";
	public $isAdminPanel 				= false;
	public $postName;
	public $postValue;
	public $showAdminOptions 			= false;
	public $showAdminPass 				= true;
	public $showAvatarInput 			= true;
	public $skipCurrentPass 			= false;
	public $registration				= false;
	public $userData 					= array("user_name", "user_password", "user_admin_password", "user_email");

	private $html						= "";
	private $js							= "";
	private $javaScriptOther;
	private $javaScriptRequired;
	private $method;

	private $_userNameChange		= true;

	public function displayInput() {
		global $locale;

		$this->method = "input";

		$enctype = $this->showAvatarInput ? " enctype='multipart/form-data'" : "";
		$this->html .= "<form name='".$this->formname."' method='post' action='".$this->formaction."'".$enctype." onsubmit='return ValidateForm(this)'>\n";
		$this->html .= "<table cellpadding='0' cellspacing='0' class='center edit-profile'>\n";

		$this->renderBasicInputFields();
		$this->renderFields();
		if ($this->displayValidation == 1) { $this->renderValidation(); }
		if ($this->displayTerms == 1) { $this->renderTerms(); }
		$this->renderButton();

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
		if ($this->registration == false) {
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

		echo $this->html;
	}

	public function displayOutput() {
		global $locale, $userdata;

		$this->method = "display";

		$this->renderBasicOutputFields();
		$this->renderFields();
		if (iADMIN && checkrights("M")) { $this->renderIPOutput(); }
		if ($this->userData['user_groups']) { $this->renderUserGroups(); }
		if ($this->showAdminOptions && iADMIN && checkrights("M") && $this->userData['user_id'] != $userdata['user_id'] && $this->userData['user_level'] < 102) {
			$this->renderAdminOptions();
		}

		echo $this->html;
	}

	public function setUserNameChange($value) {
		$this->_userNameChange = $value;
	}

	private function renderValidation() {
		global $settings, $locale;

		$_CAPTCHA_HIDE_INPUT = false;

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

		$this->html .= "<tr>\n<td class='tbl'>".$locale['u192'] ."<span style='color:#ff0000'>*</span></td>\n";
		$this->html .= "<td class='tbl'><input type='checkbox' id='agreement' name='agreement' value='1' onclick='checkagreement()' />\n";
		$this->html .= "<span class='small'><label for='agreement'>".$locale['u193'] ."</label></span>\n";
		$this->html .= "</td>\n</tr>\n";

		$this->javaScriptOther .= "	function checkagreement() {\n";
		$this->javaScriptOther .= "		if(document.inputform.agreement.checked) {\n";
		$this->javaScriptOther .= "			document.inputform.register.disabled=false;\n";
		$this->javaScriptOther .= "		} else {\n";
		$this->javaScriptOther .= "			document.inputform.register.disabled=true;\n";
		$this->javaScriptOther .= "		}\n";
		$this->javaScriptOther .= "	}\n";
	}

	private function basicInputField($name, $text, $length, $isRequired = "", $type = "text", $haveValue = true, $error_class = "") {
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
		$returnHTML .= "<input type='".$type."' id='".$name."' name='".$name."' maxlength='".$length."' class='textbox' value='".$value."' style='width:200px;'".($type == "password" ? " autocomplete='off'" : "")." />";
		$returnHTML .= "</td>\n</tr>\n";

		return $returnHTML;
	}

	private function renderBasicInputFields() {
		global $locale;

		// Login Password
		$passRequired = $this->skipCurrentPass ? $locale['u136'] : "";
		$passRequired = $this->isAdminPanel ? "" : $passRequired;
		$this->html .= "<tr>\n<td colspan='2' class='profile_category_name tbl2'><strong>".$locale['u132']."</strong></td>\n</tr>\n";
		if (!$this->skipCurrentPass) {
			$this->html .= $this->basicInputField("user_password", $locale['u133'], "64", "", "password", false, "user_password");
		}
		$this->html .= $this->basicInputField("user_new_password", ($this -> registration == TRUE ? $locale['u133'] : $locale['u134']), "64", $passRequired, "password", false, "user_password");
		$this->html .= "<tr>\n<td class='tbl'></td>\n<td class='tbl'><span class='small2'>".$locale['u147']."</span></td>\n</tr>\n";
		$this->html .= $this->basicInputField("user_new_password2", $locale['u135'], "64", $passRequired, "password", false, "user_password");

		// Admin Password
		if ($this->showAdminPass && iADMIN) {
			$this->html .= "<tr>\n<td colspan='2' class='profile_category_name tbl2'><strong>".$locale['u132']."</strong></td></tr>\n";
			if ($this->userData['user_admin_password']) {
				$this->html .= $this->basicInputField("user_admin_password", $locale['u131'], "64", "", "password", false, "user_admin_password");
			}
			$this->html .= $this->basicInputField("user_new_admin_password", ($this -> userData['user_admin_password'] ? $locale['u144'] : $locale['u131']), "64", "", "password", false, "user_admin_password");
			$this->html .= "<tr>\n<td class='tbl'></td>\n<td class='tbl'><span class='small2'>".$locale['u147']."</span></td>\n</tr>\n";
			$this->html .= $this->basicInputField("user_new_admin_password2", $locale['u145'], "64", "", "password", false, "user_admin_password");
		}

		// Hide email
		$this->html .= "<tr>\n<td colspan='2' class='profile_category_name tbl2'><strong>".$locale['u129']."</strong></td></tr>\n";
		$this->html .= (iADMIN || $this->_userNameChange ? $this->basicInputField("user_name", $locale['u127'], "30", $locale['u122']) : "");
		$this->html .= $this->basicInputField("user_email", $locale['u128'], "100", $locale['u126']);
		$hide = isset($this->userData['user_hide_email']) ? $this->userData['user_hide_email'] : 1;
		$hide = isset($_POST['user_hide_email']) && isnum($_POST['user_hide_email']) ? $_POST['user_hide_email'] : $hide;
		$this->html .= "<tr>\n";
		$this->html .= "<td class='tbl'>".$locale['u051']."</td>\n<td class='tbl'>";
		$this->html .= "<label><input type='radio' name='user_hide_email' value='1'".($hide == 1 ? " checked='checked'" : "")." />".$locale['u052']."</label>\n";
		$this->html .= "<label><input type='radio' name='user_hide_email' value='0'".($hide == 0 ? " checked='checked'" : "")." />".$locale['u053']."</label>";
		$this->html .= "</td>\n</tr>\n";

		// User Avatar
		if ($this->showAvatarInput) { $this->renderAvatarInput(); }
	}

	private function basicOutputField($name, $value, $class, $rowspan = 0) {
		global $locale;

		$returnHTML = "<tr>\n";
		if ($rowspan > 0) {
			$returnHTML .= "<td rowspan='".$rowspan."' valign='top' class='tbl profile_user_avatar' width='1%'><!--profile_user_avatar-->";
			$returnHTML .= "<img src='".IMAGES."avatars/".$this->userData['user_avatar']."' class='avatar' alt='".$locale['u062']."' title='".$locale['u062']."' />";
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

		$this->html .= "<table cellpadding='0' cellspacing='1' width='400' class='profile tbl-border center'>\n";
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

		$this->html .= $this->basicOutputField($locale['u068'], $this->userData['user_name'], "profile_user_name", $rowspan);
		$this->html .= $returnFields;

		if (iMEMBER && $userdata['user_id'] != $this->userData['user_id']) {
			$this->html .= "<tr><td colspan='3' class='user_profile_opts center tbl2'>";
			$this->html .= "<a href='".BASEDIR."messages.php?msg_send=".$this->userData['user_id']."' title='".$locale['u043']."'>".$locale['u043']."</a>\n";
			if (iADMIN && checkrights("M") && $this->userData['user_level'] != "103" && $this->userData['user_id'] != "1") {
				$this->html .= " - <a href='".ADMIN."members.php".$aidlink."&amp;step=log&amp;user_id=".$this->userData['user_id']."'>".$locale['u054']."</a>";
			}
			$this->html .= "<!--user_profile_opts-->";
			$this->html .= "</td>\n</tr>\n";
		}
		$this->html .= "</table>\n";

		if (iADMIN && $this->userData['user_status'] > 0) {
			$this->html .= "<div style='margin:5px'></div>\n";
			$this->html .= "<table cellpadding='0' cellspacing='1' width='400' class='profile tbl-border center'>\n<tr>\n";
			$this->html .= "<td colspan='2' class='tbl2'><strong>".$locale['u055']."</strong> ".getuserstatus($this->userData['user_status'])."</td>\n";
			$this->html .= "</tr>\n";
			$this->html .= $this->basicOutputField($locale['u056'], $this->userData['suspend_reason'], "profile_user_reason");
			$this->html .= "</table>\n";
		}
	}

	private function renderIPOutput() {
		global $locale;

		$this->html .= "<div style='margin:5px'></div>\n";
		$this->html .= "<table cellpadding='0' cellspacing='1' width='400' class='profile tbl-border center'>\n<tr>\n";
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
			$this->html .= "<div style='float:right'>".getgroupname($user_groups[$i], true)."</div>\n";
			$this->html .= "<div style='float:none;clear:both'></div>\n";
		}
		$this->html .= "</td>\n</tr>\n</table>\n";
	}

	private function renderAdminOptions() {
		global $locale, $groups_cache, $aidlink;

		if (!$groups_cache) { cache_groups(); }

		$user_groups_opts = "";

		$this->html .= "<div style='margin:5px'></div>\n";
		$this->html .= "<form name='admin_form' method='post' action='".FUSION_SELF."?lookup=".$this->userData['user_id']."'>\n";
		$this->html .= "<table cellpadding='0' cellspacing='0' width='400' class='profile tbl-border center'>\n<tr>\n";
		$this->html .= "<td class='tbl2' colspan='2'><strong>".$locale['u058']."</strong></td>\n";
		$this->html .= "</tr>\n<tr>\n";
		$this->html .= "<td class='tbl1'><!--profile_admin_options-->\n";
		$this->html .= "<a href='".ADMIN."members.php".$aidlink."&amp;step=edit&amp;user_id=".$this->userData['user_id']."'>".$locale['u069']."</a> ::\n";
		$this->html .= "<a href='".ADMIN."members.php".$aidlink."&amp;action=1&amp;user_id=".$this->userData['user_id']."'>".$locale['u070']."</a> ::\n";
		$this->html .= "<a href='".ADMIN."members.php".$aidlink."&amp;action=3&amp;user_id=".$this->userData['user_id']."'>".$locale['u071']."</a> ::\n";
		$this->html .= "<a href='".ADMIN."members.php".$aidlink."&amp;step=delete&amp;status=0&amp;user_id=".$this->userData['user_id']."' onclick=\"return confirm('".$locale['u073']."');\">".$locale['u072']."</a>\n";
		$this->html .= "</td>\n";
		if (count($groups_cache) > 0) {
			foreach($groups_cache as $group) {
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

		$this->html .= "<tr>\n";
		$this->html .= "<td valign='top' class='tbl".$this->getErrorClass("user_avatar")."'>";
		$this->html .= "<label for='user_avatar_upload'>".$locale['u185']."</label></td>\n";
		$this->html .= "<td class='tbl".$this->getErrorClass("user_avatar")."'>";

		if (isset($this->userData['user_avatar']) && $this->userData['user_avatar'] != "") {
			$this->html .= "<label for='user_avatar_upload'><img src='".IMAGES."avatars/".$this->userData['user_avatar']."' alt='".$locale['u185']."' />";
			$this->html .= "</label>\n<br />\n";
			$this->html .= "<input type='checkbox' name='delAvatar' value='1' class='textbox' /> ".$locale['u187']."<br />\n<br />\n";
		}

		$this->html .= "<input type='file' id='user_avatar_upload' name='user_avatar' class='textbox' style='width:200px;' /><br />\n";
		$this->html .= "<span class='small2'>".$locale['u186']."</span><br />\n<span class='small2'>";
		$this->html .= sprintf($locale['u184'], parsebytesize($settings['avatar_filesize']), $settings['avatar_width'], $settings['avatar_height']);
		$this->html .= "</span></td>\n</tr>\n";
	}

	private function renderFields() {
		global $settings, $locale, $userdata;

		$user_data 		= $this->userData;
		$profile_method = $this->method;
		$fields 		= array();
		$cats 			= array();
		$obActiva 		= false;
		$i 				= 0;

		if ($this->registration) {
			$where = "WHERE field_registration='1'";
		} else {
			$where = "";
		}

		$result = dbquery(
			"SELECT * FROM ".DB_USER_FIELDS." tuf
			INNER JOIN ".DB_USER_FIELD_CATS." tufc ON tuf.field_cat = tufc.field_cat_id
			".$where."
			ORDER BY field_cat_order, field_order"
		);
		if (dbrows($result)) {
			while($data = dbarray($result)) {
				$required = $data['field_required'] == 1 ? "<span style='color:#ff0000'>*</span>" : "";
				if ($i != $data['field_cat']) {
					if ($obActiva) {
						$fields[$i] = ob_get_contents();
						ob_end_clean();
						$obActiva = false;
					}
					$i = $data['field_cat'];
					$cats[] = array(
						"field_cat_name" => $data['field_cat_name'],
						"field_cat" => $data['field_cat']
					);
				}
				if (!$obActiva) {
					ob_start();
					$obActiva = true;
				}
				if (file_exists(LOCALE.LOCALESET."user_fields/".$data['field_name'].".php")) {
					include LOCALE.LOCALESET."user_fields/".$data['field_name'].".php";
				}
				if (file_exists(INCLUDES."user_fields/".$data['field_name']."_include.php")) {
					include INCLUDES."user_fields/".$data['field_name']."_include.php";
				}
			}
		}
		if ($obActiva) {
			$fields[$i] = ob_get_contents();
			ob_end_clean();
		}

		$i = 1;
		foreach ($cats as $cat) {
			if (array_key_exists($cat['field_cat'], $fields) && $fields[$cat['field_cat']]) {
				$this->html .= "<!--userfield_precat_".$i."-->\n";
				if ($this->method == "display") {
					$this->html .= "<div style='margin:5px'></div>\n";
					$this->html .= "<table cellpadding='0' cellspacing='1' width='400' class='profile_category tbl-border center'>\n";
				}
				$this->html .= "<tr>\n";
				$this->html .= "<td colspan='2' class='profile_category_name tbl2'><strong>".$cat['field_cat_name']."</strong></td>\n";
				$this->html .= "</tr>\n".$fields[$cat['field_cat']];
				$i++;
				if ($this->method == "display") {
					$this->html .= "</table>\n";
				}
			}
		}
		if (count($fields > 0)) {
			$this->html .= "<!--userfield_end-->\n";
		}
	}

	private function renderButton() {
		$dissabled = $this->displayTerms == 1 ? " disabled='disabled'" : "";

		$this->html .= "<tr>\n<td align='center' colspan='2'><br />\n";
		if (!$this->skipCurrentPass) {
			$this->html .= "<input type='hidden' name='user_hash' value='".$this->userData['user_password']."' />\n";
		}
		$this->html .= "<input type='submit' name='".$this->postName."' value='".$this->postValue."' class='button'".$dissabled." />\n";
		$this->html .= "</td>\n</tr>\n";
	}

	private function isError() {
		if (count($this->errorsArray) == 0) {
			return false;
		} else {
			return true;
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