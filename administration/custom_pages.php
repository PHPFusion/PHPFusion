<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: custom_pages.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
if (!checkrights("CP") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/custom_pages.php";
include LOCALE.LOCALESET."admin/sitelinks.php";

class CustomPage {

	private $data = array(
		'page_id' => '',
		'page_title' => '',
		'link_id' => 0,
		'page_link_cat' => 0,
		'page_access' => 0,
		'page_content' => '',
		'page_keywords' => '',
		'page_language' => '',
		'page_allow_comments' => 0,
		'page_allow_ratings' => 0,
	);


	public function __construct() {
		global $aidlink, $locale;
		$_POST['page_id'] = isset($_POST['page_id']) && isnum($_POST['page_id']) ? $_POST['page_id'] : 0;
		$_GET['status'] = isset($_GET['status']) ? $_GET['status'] : '';
		self::get_message();
		if (isset($_POST['edit']) && (isset($_POST['page_id']) && isnum($_POST['page_id']))) {
			$this->data = self::load_customPage($_POST['page_id']);
			if (empty($this->data)) redirect(FUSION_SELF.$aidlink);
			opentable($locale['401'].": [".$_POST['page_id']."] ".$this->data['page_title']);
		} else {
			self::customPage_selector();
			opentable($locale['400']);
		}
		$this->data = self::set_customPage($this->data);
		$this->data = self::preview_custompage($this->data);
		self::delete_customPage($_POST['page_id']);
		if (isset($_POST['cancel'])) redirect(FUSION_SELF.$aidlink);
	}

	/**
	 * @return data array from object initial or constructor when overriden.
	 */
	public function getData() {
		return $this->data;
	}

	/* This function loads the data */
	public static function load_customPage($id) {
		if (isnum($id)) {
			$data = dbarray(dbquery("
				SELECT cp.*, link.link_id
               FROM ".DB_CUSTOM_PAGES." cp
               LEFT JOIN ".DB_SITE_LINKS." link on (cp.page_link_cat = link.link_cat AND link.link_url='viewpage.php?page_id=$id' )
               WHERE page_id= '".intval($id)."' ")
			);
			return $data;
		}
		return array();
	}

	/* SQL update or save data */
	protected function set_customPage($data) {
		global $aidlink;
		if (isset($_POST['save'])) {
			$data = array(
				'page_id' => form_sanitizer($_POST['page_id'], 0, 'page_id'),
				'link_id' => form_sanitizer($_POST['link_id'], 0, 'link_id'),
				'page_link_cat' => form_sanitizer($_POST['page_link_cat'], 0, 'page_link_cat'),
				'page_title' => form_sanitizer($_POST['page_title'], '', 'page_title'),
				'page_access' => form_sanitizer($_POST['page_access'], 0, 'page_access'),
				'page_content' => addslash($_POST['page_content']),
				'page_keywords' => form_sanitizer($_POST['page_keywords'], '', 'page_keywords'),
				'page_language' => form_sanitizer($_POST['page_language'], '', 'page_language'),
				'page_allow_comments' => form_sanitizer($_POST['page_allow_comments'], '0', 'page_allow_comments'),
				'page_allow_ratings' => form_sanitizer($_POST['page_allow_ratings'], '0', 'page_allow_ratings'),
			);

			if (self::verify_customPage($data['page_id'])) {
				// update
				dbquery_insert(DB_CUSTOM_PAGES, $data, 'update');
				self::set_customPageLinks($data);
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;status=su&amp;pid=".$data['page_id']);
			} else {
				// save
				dbquery_insert(DB_CUSTOM_PAGES, $data, 'save');
				$data['page_id'] = dblastid();
				self::set_customPageLinks($data);
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;status=sn&amp;pid=".$data['page_id']);
			}
		}
		return $data;
	}

	/* SQL update or save link */
	protected function set_customPageLinks($data) {
		$link_data = array(
			'link_id' => !empty($data['link_id']) ? $data['link_id'] : 0,
			'link_cat' => $data['page_link_cat'],
			'link_name' => $data['page_title'],
			'link_url' => 'viewpage.php?page_id='.$data['page_id'],
			'link_icon' => '',
			'link_language' => $data['page_language'],
			'link_visibility' => 0,
			'link_position' => 2,
			'link_window' => 0,
		);
		if (\PHPFusion\SiteLinks::verify_edit($link_data['link_id'])) {
			// update
			$link_data['link_order'] = dbarray(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." WHERE link_id='".$link_data['link_id']."'"));
			dbquery_insert(DB_SITE_LINKS, $link_data, 'update');
		} else {
			$link_data['link_order'] = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_cat='".$data['page_link_cat']."'"), 0)+1;
			dbquery_insert(DB_SITE_LINKS, $link_data, 'save');
		}
	}

	/* SQL delete page */
	protected function delete_customPage($page_id) {
		global $aidlink;
		if (isset($_POST['delete']) && self::verify_customPage($page_id)) {
			$result = dbquery("DELETE FROM ".DB_CUSTOM_PAGES." WHERE page_id='".intval($page_id)."'");
			$result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_url='viewpage.php?page_id=".intval($page_id)."'");
			if ($result) redirect(FUSION_SELF.$aidlink."&status=del");
		}
	}

	/* Authenticate the page ID is valid */
	protected function verify_customPage($id) {
		if (isnum($id)) {
			return dbcount("(page_id)", DB_CUSTOM_PAGES, "page_id='".intval($id)."'");
		}
		return FALSE;
	}

	/* Display Message Output */
	public static function get_message() {
		global $locale;
		$message = '';
		switch ($_GET['status']) {
			case 'sn':
				$message = $locale['410']."<br />\n".$locale['412']."\n";
				$message .= "<a href='".BASEDIR."viewpage.php?page_id=".intval($_GET['pid'])."'>viewpage.php?page_id=".intval($_GET['pid'])."</a>\n";
				break;
			case 'su':
				$message = $locale['411']."<br />\n".$locale['412']."\n";
				$message .= "<a href='".BASEDIR."viewpage.php?page_id=".intval($_GET['pid'])."'>viewpage.php?page_id=".intval($_GET['pid'])."</a>\n";
				break;
			case 'del':
				$message = $locale['413'];
				break;
		}
		if ($message) {
			echo admin_message($message);
		}
	}

	private function preview_custompage($data) {
		if (isset($_POST['preview'])) {
			$page_title = stripinput($_POST['page_title']);
			$page_content = stripslash($_POST['page_content']);
			echo openmodal('page_preview', $page_title, array('class'=>'modal-center modal-lg'));
			eval("?>".$page_content."<?php ");
			echo closemodal();
		}
		return $data;
	}

	/* Displays Custom Page Selector */
	public static function customPage_selector() {
		global $aidlink, $locale;
		$result = dbquery("SELECT page_id, page_title, page_language FROM ".DB_CUSTOM_PAGES." ".(multilang_table("CP") ? "WHERE page_language='".LANGUAGE."'" : "")." ORDER BY page_title");
		if (dbrows($result) != 0) {
			$edit_opts = array();
			while ($data = dbarray($result)) {
				$edit_opts[$data['page_id']] = $data['page_title'];
			}
			opentable($locale['402']);
			openside('');
			echo openform('selectform', 'selectform', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));
			echo "<div class='pull-left m-t-5 m-r-10'>\n";
			echo form_select('', 'page_id', 'page_id', $edit_opts, isset($_POST['page_id']) && isnum($_POST['page_id']) ? $_POST['page_id'] : '');
			echo "</div>\n";
			echo form_button($locale['420'], 'edit', 'edit', $locale['420'], array('class' => 'btn-default pull-left m-l-10 m-r-10'));
			echo form_button($locale['421'], 'delete', 'delete', $locale['421'], array('class' => 'btn-danger pull-left'));
			echo closeform();
			closeside();
			closetable();
		}
	}

	/* Returns array of userGroups */
	public static function get_visibilityOpts() {
		$access_opts = array();
		$user_groups = getusergroups();
		while (list($key, $user_group) = each($user_groups)) {
			$access_opts[$user_group['0']] = $user_group['1'];
		}
		return $access_opts;
	}

	/* The HTML form */
	public static function customPage_form($data) {
		global $aidlink, $locale;
		if (isset($_COOKIE['custom_pages_tinymce']) && $_COOKIE['custom_pages_tinymce'] == 1 && fusion_get_settings('tinymce_enabled')) {
			echo "<script>\n";
			echo "advanced();";
			echo "</script>\n";
		} else {
			require_once INCLUDES."html_buttons_include.php";
		}
		echo openform('inputform', 'inputform', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));
		if (isset($_POST['edit']) && isset($_POST['page_id'])) {
			echo form_hidden('', 'edit', 'edit', 'edit');
		}
		// port to dynamics now.
		echo "<div class='row m-t-20' >\n";
		echo "<div class='col-xs-12 col-sm-8'>\n";
		echo form_text($locale['422'], 'page_title', 'page_title', $data['page_title'], array('required' => 1));
		echo form_select($locale['432'], 'page_keywords', 'page_keywords', array(), $data['page_keywords'], array('max_length' => 320,
			'width' => '100%',
			'tags' => 1,
			'multiple' => 1
            )
        );
		echo form_textarea($locale['424'], 'page_content', 'page_content', $data['page_content'], (isset($_COOKIE['custom_pages_tinymce']) && $_COOKIE['custom_pages_tinymce'] == 1 && fusion_get_settings('tinymce_enabled') ? array() : array('autosize'=>1)));
		if (!isset($_COOKIE['custom_pages_tinymce']) || !$_COOKIE['custom_pages_tinymce'] || !fusion_get_settings('tinymce_enabled')) {
			openside();
			echo "<button type='button' class='btn btn-sm btn-default button m-b-10' value='".$locale['431']."' onclick=\"insertText('page_content', '&lt;!--PAGEBREAK--&gt;');\">".$locale['431']."</button>\n";
			echo "<button type='button' class='btn btn-sm btn-default button m-b-10' value='&lt;?php?&gt;' onclick=\"addText('page_content', '&lt;?php\\n', '\\n?&gt;');\">&lt;?php?&gt;</button>\n";
			echo "<button type='button' class='btn btn-sm btn-default button m-b-10' value='&lt;p&gt;' onclick=\"addText('page_content', '&lt;p&gt;', '&lt;/p&gt;');\">&lt;p&gt;</button>\n";
			echo "<button type='button' class='btn btn-default btn-sm button m-b-10' value='&lt;br /&gt;' onclick=\"insertText('page_content', '&lt;br /&gt;');\">&lt;br /&gt;</button>\n";
			echo display_html("inputform", "page_content", TRUE);
			closeside();
		}

		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-4'>\n";
		if (fusion_get_settings('tinymce_enabled')) {
			openside('');
			echo "<div class='strong m-b-10'>".$locale['460']."</div>\n";
			$val = !isset($_COOKIE['custom_pages_tinymce']) || $_COOKIE['custom_pages_tinymce'] == 0 ? $locale['461'] : $locale['462'];
			echo form_button($val, 'tinymce_switch', 'tinymce_switch', $val, array('class' => 'btn-default', 'type' => 'button'));
			add_to_jquery("
			$('#tinymce_switch').bind('click', function() {
				SetTinyMCE(".(!isset($_COOKIE['custom_pages_tinymce']) || $_COOKIE['custom_pages_tinymce'] == 0 ? 1 : 0).");
			});
			");
			closeside();
		}
		openside();
		echo form_select_tree($locale['SL_0029'], "page_link_cat", "page_link_cat", $data['page_link_cat'],
		  	array(
			"parent_value" => $locale['parent'],
			'width' => '100%',
			'query' => (multilang_table("SL") ? "WHERE link_language='".LANGUAGE."'" : ''),
			'disable_opts' => $data['link_id'],
			'hide_disabled' => 1), DB_SITE_LINKS, "link_name", "link_id", "link_cat");
		if (!$data['page_id']) { // we need to get rid of this if we want to constant pairing.
			echo form_checkbox($locale['426'], 'add_link', 'add_link', 1);
		}
		echo form_hidden('', 'link_id', 'link_id', $data['link_id']);
		closeside();

		openside();
		if (multilang_table("CP")) {
			echo form_select($locale['global_ML100'], 'page_language', 'page_language', fusion_get_enabled_languages(), $data['page_language'], array('width'=>'100%'));
		} else {
			echo form_hidden('', 'page_language', 'page_language', $data['page_language']);
		}
		echo form_select($locale['423'], 'page_access', 'page_access', self::get_visibilityOpts(), $data['page_access'], array('width'=>'100%'));
		closeside();

		openside();
		echo form_checkbox($locale['427'], 'page_allow_comments', 'page_allow_comments', $data['page_allow_comments']);
		if (fusion_get_settings('comments_enabled') == "0") {
			echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
		}
		echo form_checkbox($locale['428'], 'page_allow_ratings', 'page_allow_ratings', $data['page_allow_ratings']);
		if (fusion_get_settings('ratings_enabled') == "0") {
			echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
		}
		closeside();

		if (fusion_get_settings('comments_enabled') == "0" || fusion_get_settings('ratings_enabled') == "0") {
			$sys = "";
			if (fusion_get_settings('comments_enabled')  == "0" && fusion_get_settings('ratings_enabled')  == "0") {
				$sys = $locale['457'];
			} elseif (fusion_get_settings('comments_enabled')  == "0") {
				$sys = $locale['455'];
			} else {
				$sys = $locale['456'];
			}
			echo "<div style='color:red;font-weight:bold;margin-right:5px;'>*</span>".sprintf($locale['454'], $sys)."</div>\n";
		}
		echo "</div>\n</div>\n";

		echo form_hidden('', 'page_id', 'page_id', $data['page_id']);
		echo form_button($locale['429'], 'preview', 'preview', $locale['429'], array('class' => 'btn-primary m-r-10'));
		echo form_button($locale['430'], 'save', 'save', $locale['430'], array('class' => 'btn-primary m-r-10'));
		if (isset($_POST['edit'])) echo form_button($locale['cancel'], 'cancel', 'cancel', $locale['cancel'], array('class' => 'btn-default m-r-10'));
		echo closeform();
		closetable();

		add_to_jquery("
			$('#delete').bind('click', function() { confirm('".$locale['450']."'); });
			$('#save, #preview').bind('click', function() {
			var page_title = $('#page_title').val();
			if (page_title =='') { alert('".$locale['451']."'); return false; }
			});
		");

		if (fusion_get_settings('tinymce_enabled')) {
			add_to_jquery("
			function SetTinyMCE(val) {
			now=new Date();\n"."now.setTime(now.getTime()+1000*60*60*24*365);
			expire=(now.toGMTString());\n"."document.cookie=\"custom_pages_tinymce=\"+escape(val)+\";expires=\"+expire;
			location.href='".FUSION_SELF.$aidlink."';
			}
		    ");
		}
	}
}


$customPage = new CustomPage();
$data = $customPage->getData();
$customPage::customPage_form($data);
require_once THEMES."templates/footer.php";
