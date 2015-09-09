<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: CustomPage.php
| Author: Frederick MC Chan (hien)
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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}

class CustomPage {
	/**
	 * @var array
	 */
	private $data = array(
		'page_id' => '',
		'page_title' => '',
		'link_id' => 0,
		'link_order' => 0,
		'page_link_cat' => 0,
		'page_access' => 0,
		'page_content' => '',
		'page_keywords' => '',
		'page_language' => LANGUAGE,
		'page_allow_comments' => 0,
		'page_allow_ratings' => 0,
	);

	/**
	 * @return data array from object initial or constructor when overriden.
	 */
	public function getData() {
		return $this->data;
	}

	public function __construct() {
		global $aidlink, $locale;
		$_POST['page_id'] = isset($_POST['page_id']) && isnum($_POST['page_id']) ? $_POST['page_id'] : 0;
		$_GET['status'] = isset($_GET['status']) ? $_GET['status'] : '';
		$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
		$title = '';
		switch ($_GET['action']) {
			case 'edit':
				fusion_confirm_exit();
				$this->data = self::load_customPage($_GET['cpid']);
				if (empty($this->data)) redirect(FUSION_SELF.$aidlink);
				opentable($locale['401']);
				break;
			case 'delete':
				self::delete_customPage($_GET['cpid']);
				break;
			default:
				opentable($locale['400']);
		}
		self::customPage_selector();
		add_breadcrumb(array('link' => ADMIN.'custom_pages.php'.$aidlink, 'title' => $locale['403']));
		$this->data = self::set_customPage($this->data);
		if (isset($_POST['cancel'])) redirect(FUSION_SELF.$aidlink);
	}

	/**
	 * This function loads the data
	 * @param $id
	 * @return array|bool
	 */
	public static function load_customPage($id) {
		if (isnum($id)) {
			$data = dbarray(dbquery("
				SELECT cp.*, link.link_id, link.link_order
               FROM ".DB_CUSTOM_PAGES." cp
               LEFT JOIN ".DB_SITE_LINKS." link on (cp.page_link_cat = link.link_cat AND link.link_url='viewpage.php?page_id=$id' )
               WHERE page_id= '".intval($id)."' "));
			return $data;
		}
		return array();
	}

	/**
	 * SQL update or save data
	 * @param $data
	 * @return array
	 */
	protected function set_customPage($data) {
		global $aidlink, $locale;
		if (isset($_POST['save'])) {
			$data = array(
				'page_id' => form_sanitizer($_POST['page_id'], 0, 'page_id'),
				'link_id' => form_sanitizer($_POST['link_id'], 0, 'link_id'),
				'link_order' => form_sanitizer($_POST['link_order'], 0, 'link_order'),
				'page_link_cat' => form_sanitizer($_POST['page_link_cat'], 0, 'page_link_cat'),
				'page_title' => form_sanitizer($_POST['page_title'], '', 'page_title'),
				'page_access' => form_sanitizer($_POST['page_access'], 0, 'page_access'),
				'page_content' => addslash($_POST['page_content']),
				'page_keywords' => form_sanitizer($_POST['page_keywords'], '', 'page_keywords'),
				'page_language' => implode('.', isset($_POST['page_language']) ? sanitize_array($_POST['page_language']) : array()),
				'page_allow_comments' => isset($_POST['page_allow_comments']) ? 1 : 0,
				'page_allow_ratings' => isset($_POST['page_allow_ratings']) ? 1 : 0
			);
			if (self::verify_customPage($data['page_id'])) {
				// update
				dbquery_insert(DB_CUSTOM_PAGES, $data, 'update');
				if (isset($_POST['add_link'])) {
					self::set_customPageLinks($data);
				}
				if (!defined('FUSION_NULL')) {
					addNotice('info', $locale['411']);
					redirect(FUSION_SELF.$aidlink."&amp;pid=".$data['page_id']);
				}
			} else {
				// save
				dbquery_insert(DB_CUSTOM_PAGES, $data, 'save');
				$data['page_id'] = dblastid();
				if (isset($_POST['add_link'])) {
					self::set_customPageLinks($data);
				}
				if (!defined('FUSION_NULL')) {
					addNotice('success', $locale['410']);
					redirect(FUSION_SELF.$aidlink."&amp;pid=".$data['page_id']);
				}
			}
		}
		return $data;
	}

	/* SQL update or save link */
	/**
	 * Set CustomPage Links into Navigation Bar
	 * @param $data
	 */
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
			'link_order' => !empty($data['link_order']) ? $data['link_order'] : 0
		);
		if (\PHPFusion\SiteLinks::verify_edit($link_data['link_id'])) {
			// update
			$link_data['link_order'] = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_cat='".$link_data['link_cat']."'"), 0)+1;
			dbquery_insert(DB_SITE_LINKS, $link_data, 'update');
		} else {
			$link_data['link_order'] = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_cat='".$link_data['link_cat']."'"), 0)+1;
			dbquery_insert(DB_SITE_LINKS, $link_data, 'save');
		}
	}

	/**
	 * SQL delete page
	 * @param $page_id
	 */
	protected function delete_customPage($page_id) {
		global $aidlink, $locale;
		if (isnum($page_id) && self::verify_customPage($page_id)) {
			$result = dbquery("DELETE FROM ".DB_CUSTOM_PAGES." WHERE page_id='".intval($page_id)."'");
			if ($result) $result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_url='viewpage.php?page_id=".intval($page_id)."'");
			if ($result) {
				addNotice('warning', $locale['413']);
				redirect(FUSION_SELF.$aidlink);
			}
		}
	}

	/**
	 * Authenticate the page ID is valid
	 * @param $id
	 * @return bool|string
	 */
	protected function verify_customPage($id) {
		if (isnum($id)) {
			return dbcount("(page_id)", DB_CUSTOM_PAGES, "page_id='".intval($id)."'");
		}
		return FALSE;
	}

	/**
	 * Displays Custom Page Selector
	 */
	public static function customPage_selector() {
		global $aidlink, $locale;
		$result = dbquery("SELECT page_id, page_title, page_language FROM ".DB_CUSTOM_PAGES." ".(multilang_table("CP") ? "WHERE page_language='".LANGUAGE."'" : "")." ORDER BY page_title");
		if (dbrows($result) != 0) {
			$edit_opts = array();
			while ($data = dbarray($result)) {
				$edit_opts[$data['page_id']] = $data['page_title'];
			}
			echo "<div class='pull-right'>\n";
			echo openform('selectform', 'get', ADMIN.'custom_pages.php'.$aidlink, array('max_tokens' => 1));
			echo "<div class='pull-left m-t-5 m-r-10'>\n";
			echo form_select('cpid', '', isset($_POST['page_id']) && isnum($_POST['page_id']) ? $_POST['page_id'] : '', array("options" => $edit_opts));
			echo form_hidden('section', '', 'cp2');
			echo form_hidden('aid', '', iAUTH);
			echo "</div>\n";
			echo form_button('action', $locale['420'], 'edit', array('class' => 'btn-default btn-sm pull-left m-l-10 m-r-10'));
			echo form_button('action', $locale['421'], 'delete', array(
				'class' => 'btn-danger btn-sm pull-left',
				'icon' => 'fa fa-trash'
			));
			echo closeform();
			echo "</div>\n";
		}
	}

	public static function listPage() {
		global $locale, $aidlink;
		$data = array();
		// now load new page
		$result = dbquery("SELECT page_id, page_link_cat, page_title, page_access, page_allow_comments, page_allow_ratings, page_language FROM ".DB_CUSTOM_PAGES." ORDER BY page_id ASC");
		if (dbrows($result) > 0) {
			while ($cdata = dbarray($result)) {
				$data[$cdata['page_id']] = $cdata;
			}
		}
		$choice = array('0' => $locale['no'], '1' => $locale['yes']);
		add_to_jquery("
		$('.actionbar').hide();
		$('tr').hover(
			function(e) { $('#coupon-'+ $(this).data('id') +'-actions').show(); },
			function(e) { $('#coupon-'+ $(this).data('id') +'-actions').hide(); }
		);
		$('.qform').hide();
		");
		echo "<div class='m-t-20'>\n";
		echo "<table class='table table-responsive table-striped table-hover'>\n";
		echo "<tr>\n";
		echo "<th>".$locale['cp_100']."</th>\n";
		echo "<th>".$locale['cp_101']."</th>\n";
		echo "<th>".$locale['cp_102']."</th>\n";
		echo "<th>".$locale['cp_103']."</th>\n";
		echo "<th>".$locale['cp_104']."</th>\n";
		echo "<th>".$locale['cp_105']."</th>\n";
		echo "<th>".$locale['cp_106']."</th>\n";
		echo "</tr>\n";
		if (!empty($data)) {
			echo "<tbody id='custompage-links' class='connected'>\n";
			foreach ($data as $id => $pageData) {
				$display_lang = $pageData['page_language'];
				echo "<tr id='listItem_".$pageData['page_id']."' data-id='".$pageData['page_id']."' class='list-result pointer'>\n";
				echo "<td>".$pageData['page_id']."</td>\n";
				echo "<td class='col-sm-4'>".$pageData['page_title']."\n";
				echo "<div class='actionbar text-smaller' id='coupon-".$pageData['page_id']."-actions'>
				<a target='_new' href='".BASEDIR."viewpage.php?page_id=".$pageData['page_id']."'>".$locale['view']."</a> |
				<a href='".FUSION_SELF.$aidlink."&amp;section=cp2&amp;action=edit&amp;cpid=".$pageData['page_id']."'>".$locale['edit']."</a> |
				<a class='delete' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;cpid=".$pageData['page_id']."' onclick=\"return confirm('".$locale['450']."');\">".$locale['delete']."</a>
				</div>\n";
				echo "</td>\n";
				echo "<td>".getgroupname($pageData['page_access'])."</td>\n";
				echo "<td>".$display_lang."</td>\n";
				echo "<td>".$choice[$pageData['page_allow_comments']]."</td>\n";
				echo "<td>".$choice[$pageData['page_allow_ratings']]."</td>\n";
				echo "<td>".($pageData['page_link_cat'] ? $choice[1] : $choice[0])."</td>\n";
				echo "</tr>\n";
			}
			echo "</tbody>\n";
		}
		echo "</table>\n";
		echo "</div>\n";
	}

	/**
	 * The HTML form
	 * @param $data
	 */
	public static function customPage_form($data) {
		global $aidlink, $locale;
		if (isset($_COOKIE['custom_pages_tinymce']) && $_COOKIE['custom_pages_tinymce'] == 1 && fusion_get_settings('tinymce_enabled')) {
			echo "<script>advanced();</script>\n";
		} else {
			require_once INCLUDES."html_buttons_include.php";
		}
		if (isset($_POST['preview'])) {
			$data = array(
				'page_id' => form_sanitizer($_POST['page_id'], 0, 'page_id'),
				'link_id' => form_sanitizer($_POST['link_id'], 0, 'link_id'),
				'link_order' => form_sanitizer($_POST['link_order'], 0, 'link_order'),
				'page_link_cat' => form_sanitizer($_POST['page_link_cat'], 0, 'page_link_cat'),
				'page_title' => form_sanitizer($_POST['page_title'], '', 'page_title'),
				'page_access' => form_sanitizer($_POST['page_access'], 0, 'page_access'),
				'page_content' => form_sanitizer($_POST['page_content'], "", "page_content"),
				'page_keywords' => form_sanitizer($_POST['page_keywords'], '', 'page_keywords'),
				'page_language' => implode('.', isset($_POST['page_language']) ? sanitize_array($_POST['page_language']) : array()),
				'page_allow_comments' => isset($_POST['page_allow_comments']) ? 1 : 0,
				'page_allow_ratings' => isset($_POST['page_allow_ratings']) ? 1 : 0
			);
			if (\defender::safe()) {
				echo openmodal("cp_preview", $locale['429']);
				echo "<h3>".$data['page_title']."</h3>\n";
				echo "<p>".html_entity_decode(stripslashes($data['page_content']))."</p>\n";
				echo closemodal();
			}
		}
		echo openform('inputform', 'post', FUSION_REQUEST, array("class" => "m-t-20"));
		if (isset($_POST['edit']) && isset($_POST['page_id'])) {
			echo form_hidden('edit', '', 'edit');
		}
		echo "<div class='row m-t-20' >\n";
		echo "<div class='col-xs-12 col-sm-8'>\n";
		echo form_text('page_title', $locale['422'], $data['page_title'], array('required' => 1));
		echo form_select('page_keywords', $locale['432'], $data['page_keywords'], array(
			'max_length' => 320,
			'width' => '100%',
			'tags' => 1,
			'multiple' => 1,
		));
		echo form_textarea('page_content', '', $data['page_content'], (isset($_COOKIE['custom_pages_tinymce']) && $_COOKIE['custom_pages_tinymce'] == 1 && fusion_get_settings('tinymce_enabled') ? array() : array(
			'width' => '100%',
			'height' => '260px',
			'form_name' => 'inputform',
			'html' => 1,
			'class' => 'm-t-20',
		)));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-4'>\n";
		if (fusion_get_settings('tinymce_enabled')) {
			openside('');
			echo "<div class='strong m-b-10'>".$locale['460']."</div>\n";
			$val = !isset($_COOKIE['custom_pages_tinymce']) || $_COOKIE['custom_pages_tinymce'] == 0 ? $locale['461'] : $locale['462'];
			echo form_button('tinymce_switch', $val, $val, array('class' => 'btn-default', 'type' => 'button'));
			add_to_jquery("
			$('#tinymce_switch').bind('click', function() {
				SetTinyMCE(".(!isset($_COOKIE['custom_pages_tinymce']) || $_COOKIE['custom_pages_tinymce'] == 0 ? 1 : 0).");
			});
			");
			closeside();
		}
		if (fusion_get_settings('comments_enabled') == "0" || fusion_get_settings('ratings_enabled') == "0") {
			echo "<div class='tbl2 well'>\n";
			if (fusion_get_settings('comments_enabled') == "0" && fusion_get_settings('ratings_enabled') == "0") {
				$sys = $locale['457'];
			} elseif (fusion_get_settings('comments_enabled') == "0") {
				$sys = $locale['455'];
			} else {
				$sys = $locale['456'];
			}
			echo sprintf($locale['454'], $sys);
			echo "</div>\n";
		}
		openside();
		echo form_select_tree("page_link_cat", $locale['SL_0029'], $data['page_link_cat'], array(
			"parent_value" => $locale['parent'],
			'width' => '100%',
			'query' => (multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : '')." link_position >= 2",
			'disable_opts' => $data['link_id'],
			'hide_disabled' => 1
		), DB_SITE_LINKS, "link_name", "link_id", "link_cat");
		if (!$data['page_id']) {
			echo form_checkbox('add_link', $locale['426'], 1);
		}
		echo form_checkbox('page_allow_comments', $locale['427'], $data['page_allow_comments'], array('class' => 'm-b-0'));
		echo form_checkbox('page_allow_ratings', $locale['428'], $data['page_allow_ratings'], array('class' => 'm-b-0'));
		echo form_hidden('link_id', '', $data['link_id']);
		echo form_hidden('link_order', '', $data['link_order']);
		echo form_button('save', $locale['430'], $locale['430'], array('class' => 'btn-primary m-r-10 m-t-10'));
		echo form_button('preview', $locale['429'], $locale['429'], array('class' => 'btn-default m-r-10 m-t-10'));
		closeside();
		openside();
		if (multilang_table("CP")) {
			$languages = !empty($data['page_language']) ? explode('.', $data['page_language']) : array();
			foreach (fusion_get_enabled_languages() as $language) {
				echo form_checkbox('page_language[]', $language, in_array($language, $languages) ? 1 : 0, array(
					'class' => 'm-b-0',
					'value' => $language,
					'input_id' => 'page_lang-'.$language
				));
			}
		} else {
			echo form_hidden('page_language', '', $data['page_language']);
		}
		closeside();
		openside();
		echo form_select('page_access', $locale['423'], $data['page_access'], array(
			'options' => fusion_get_groups(),
			'width' => '100%'
		));
		closeside();
		echo "</div></div>\n";
		echo form_hidden('page_id', '', $data['page_id']);
		echo form_button('save', $locale['430'], $locale['430'], array('class' => 'btn-primary m-r-10'));
		if (isset($_POST['edit'])) echo form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default m-r-10'));
		echo closeform();
		closetable();
		add_to_jquery("
			$('#delete').bind('click', function() { confirm('".$locale['450']."'); });
			$('#save').bind('click', function() {
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
