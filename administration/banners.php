<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: banners.php
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
require_once "../maincore.php";
require_once THEMES."templates/admin_header.php";
pageAccess('SB');

class Banners {
    protected static $banner_settings = array();
    private static $instance = NULL;
    private static $locale = array();

    public function __construct() {
        $this->set_locale();
        $banner_settings = self::get_banner_settings();
        $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';

        switch ($_GET['action']) {
            case 'delete':
            	if (empty($_GET['banner_id'])) {
					\defender::stop();
					addNotice('danger', self::$locale['BN_014']);
					redirect(clean_request("", array("section=banners_list", "aid"), TRUE));
            	}
                break;
            default:
                 break;
        }
		add_breadcrumb(array('link' => ADMIN.'banners.php'.fusion_get_aidlink(), 'title' => self::$locale['BN_000']));
    }

    public static function getInstance($key = TRUE) {
        if (self::$instance === NULL) {
            self::$instance = new static();
        	self::$instance->set_bannerdb();
        }

        return self::$instance;
    }

    public static function get_banner_settings() {
        if (empty(self::$banner_settings)) {
            self::$banner_settings = fusion_get_settings();
        }
        return self::$banner_settings;
    }

    private static function set_locale() {
        self::$locale = fusion_get_locale("", LOCALE.LOCALESET."admin/banners.php");
    }

    private function set_bannerdb() {
		if (isset($_POST['upload_banner'])) {
	    	if (\defender::safe()) {
        		if (!empty($_FILES['banner_image']) && is_uploaded_file($_FILES['banner_image']['tmp_name'])) {
					$upload = form_sanitizer($_FILES['banner_image'], "", "banner_image");
        			if ($upload['error'] == 0) {
             		addNotice('success', self::$locale['BN_010']);
		        	redirect(clean_request("", array("section=banners_list", "aid"), TRUE));
            		}
	    		} else {
             		addNotice('danger', self::$locale['BN_011']);
		        	redirect(clean_request("", array("section=banners_list", "aid"), TRUE));
	    		}
        	}
		}

		if (isset($_POST['save_banners'])) {

	    	$settings_main = array(
	        	'sitebanner' => isset($_POST['sitebanner']) ? $_POST['sitebanner'] : self::$banner_settings['sitebanner'],
	        	'sitebanner1' => isset($_POST['sitebanner1']) ? addslash($_POST['sitebanner1']) : self::$banner_settings['sitebanner1'],
	        	'sitebanner2' => isset($_POST['sitebanner2']) ? addslash($_POST['sitebanner2']) : self::$banner_settings['sitebanner2'],
	    	);

	    	if (\defender::safe()) {
				foreach ($settings_main as $settings_key => $settings_value) {
					dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_value."' WHERE settings_name='".$settings_key."'");
             		addNotice('success', self::$locale['BN_012']);
				}
		        redirect(clean_request("", array("section=banners_list", "aid"), TRUE));
    		}
		}


		if (isset($_GET['action']) && $_GET['action'] == 'delete') {
	    	$settings_main = array(
	        	'sitebanner' => isset($_GET['banner_id']) && $_GET['banner_id'] == 'sitebanner' ? '' : self::$banner_settings['sitebanner'],
	        	'sitebanner1' => isset($_GET['banner_id']) && $_GET['banner_id'] == 'sitebanner1' ? '' : self::$banner_settings['sitebanner1'],
	        	'sitebanner2' => isset($_GET['banner_id']) && $_GET['banner_id'] == 'sitebanner2' ? '' : self::$banner_settings['sitebanner2'],
	    	);

	    	if (\defender::safe()) {
				foreach ($settings_main as $settings_key => $settings_value) {
					dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_value."' WHERE settings_name='".$settings_key."'");
				}
             	addNotice('warning', self::$locale['BN_013']);
		        redirect(clean_request("", array("section=banners_list", "aid"), TRUE));
    		}
		}
    }

    public function display_admin() {
		$aidlink = fusion_get_aidlink();

		opentable(self::$locale['BN_000']);
		$allowed_section = array("banners_form", "banners_list", "bannerUp_form");
		$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'banners_list';
		$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? TRUE : FALSE;

		$master_tab_title['title'][] = self::$locale['BN_000'];
		$master_tab_title['id'][] = 'banners_list';
		$master_tab_title['icon'][] = "";

		$master_tab_title['title'][] = self::$locale['BN_020'];
		$master_tab_title['id'][] = 'bannerUp_form';
		$master_tab_title['icon'][] = "";

		if ($edit) {
		$master_tab_title['title'][] = self::$locale['edit'];
		$master_tab_title['id'][] = 'banners_form';
		$master_tab_title['icon'][] = "";

		}

		echo opentab($master_tab_title, $_GET['section'], 'banners_list', TRUE);
			switch ($_GET['section']) {
				case "banners_form":
		if ($edit) {
			add_breadcrumb(array('link' => ADMIN.'banners.php'.$aidlink, 'title' => $master_tab_title['title'][2]));
					$this->bannerForm();
		}
				break;
				case "bannerUp_form":
			add_breadcrumb(array('link' => ADMIN.'banners.php'.$aidlink, 'title' => $master_tab_title['title'][1]));
					$this->UpbannerForm();
				break;
				default:
			add_breadcrumb(array('link' => ADMIN.'banners.php'.$aidlink, 'title' => $master_tab_title['title'][0]));
					$this->list_banner();
				break;
			}
		echo closetab();
        closetable();
    }

    public function list_banner() {

	openside(self::$locale['BN_000']);

	openside(self::$locale['BN_021']);
		echo showbanners();
	closeside();

		echo openform('bannerform', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=banners_form");
	openside(self::$locale['sitebanner']);
		echo "<div class='pull-left'><img src='".BASEDIR.fusion_get_settings("sitebanner")."' alt='' style='border: 0;' /></div>\n";
	closeside();
		echo "<div class='pull-left'><a class='btn btn-default btn-sm' href='".FUSION_SELF.fusion_get_aidlink()."&amp;section=banners_form&amp;action=edit&amp;banner_id=sitebanner'><i class='fa fa-edit fa-fw'></i> ".self::$locale['edit']."</a>\n";
		echo "<a class='btn btn-default btn-sm' href='".FUSION_SELF.fusion_get_aidlink()."&amp;section=banners_list&amp;action=delete&amp;banner_id=sitebanner' onclick=\"return confirm('".self::$locale['BN_016']."');\"><i class='fa fa-trash fa-fw'></i> ".self::$locale['delete']."</a>\n</div>\n";
	openside(self::$locale['sitebanner1']);
		!empty(self::$banner_settings['sitebanner1']) ? eval("?><div class='pull-left'>".stripslashes(fusion_get_settings("sitebanner1"))."</div>\n<?php ") : "";
	closeside();
		echo "<div class='pull-left'><a class='btn btn-default btn-sm' href='".FUSION_SELF.fusion_get_aidlink()."&amp;section=banners_form&amp;action=edit&amp;banner_id=sitebanner1'><i class='fa fa-edit fa-fw'></i> ".self::$locale['edit']."</a>";
		echo "<a class='btn btn-default btn-sm' href='".FUSION_SELF.fusion_get_aidlink()."&amp;section=banners_list&amp;action=delete&amp;banner_id=sitebanner1' onclick=\"return confirm('".self::$locale['BN_017']."');\"><i class='fa fa-trash fa-fw'></i> ".self::$locale['delete']."</a></div>\n";
	openside(self::$locale['sitebanner2']);
		!empty(self::$banner_settings['sitebanner2']) ? eval("?><div class='pull-right'>".stripslashes(fusion_get_settings("sitebanner2"))."</div>\n<?php ") : "";
	closeside();
		echo "<div class='pull-left'><a class='btn btn-default btn-sm' href='".FUSION_SELF.fusion_get_aidlink()."&amp;section=banners_form&amp;action=edit&amp;banner_id=sitebanner2'><i class='fa fa-edit fa-fw'></i> ".self::$locale['edit']."</a>";
		echo "<a class='btn btn-default btn-sm' href='".FUSION_SELF.fusion_get_aidlink()."&amp;section=banners_list&amp;action=delete&amp;banner_id=sitebanner2' onclick=\"return confirm('".self::$locale['BN_017']."');\"><i class='fa fa-trash fa-fw'></i> ".self::$locale['delete']."</a></div>\n";
    echo closeform();
	closeside();
    }

    public function bannerForm() {
    openside('');

		echo openform('banner_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=banners_list&amp;action=edit", array('enctype' => TRUE));

		echo form_textarea($_GET['banner_id'], self::$locale[$_GET['banner_id']], stripslashes(self::$banner_settings[$_GET['banner_id']]), array(
		    "preview" => TRUE,
		    "type" => fusion_get_settings("tinymce_enabled") ? "tinymce" : "html",
		    "tinymce" => fusion_get_settings("tinymce_enabled") && iADMIN ? "advanced" : "simple",
		    "autosize" => TRUE,
		    "form_name" => "banner_form",
		    "wordcount" => TRUE,
		    "inline" => FALSE,
		    'placeholder' => ($_GET['banner_id'] == 'sitebanner' ? self::$locale['BN_015'] : "")
		));
		echo form_button('save_banners', self::$locale['save'], self::$locale['save'], array('class' => 'btn-success'));
		echo closeform();
	closeside();
    }

    public function UpbannerForm() {
        $default_options = array(
	        'upload_path' => IMAGES,
	        'delete_original' => TRUE,
            'template' => 'modern',
	        'type' => 'image',
	        'required' => TRUE,
            'class' => 'm-b-0',
            'multiple' => FALSE
        );
		echo openform('banner_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=banners_list&amp;action=edit", array('enctype' => TRUE));
		echo form_fileinput('banner_image', self::$locale['BN_020'], '', $default_options);
		echo form_button('upload_banner', self::$locale['BN_022'], self::$locale['BN_022'], array('class' => 'btn-primary'));
		echo closeform();
    }

}

Banners::getInstance(TRUE)->display_admin();

require_once THEMES."templates/footer.php";
