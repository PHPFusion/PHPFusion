<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/classes/weblinks/weblinks_submissions.php
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
namespace PHPFusion\Weblinks;

use PHPFusion\SiteLinks;

class WeblinksSubmissions extends WeblinksServer {

    public $info = array();
    private static $instance = NULL;

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayWeblinks() {
        $this->locale = fusion_get_locale("", WEBLINK_ADMIN_LOCALE);
        $weblink_settings = self::get_weblink_settings();
		add_to_title($this->locale['WLS_0900']);

		opentable("<i class='fa fa-globe fa-lg m-r-10'></i>".$this->locale['WLS_0900']);

        	if (iMEMBER && $weblink_settings['links_allow_submission']) {
        	    $this->display_submission_form();
        	} else {
    		echo "<div class='well text-center'>".$this->locale['WLS_0922']."</div>\n";
     	   }

		closetable();
    }

    private function display_submission_form() {

        $weblink_settings = self::get_weblink_settings();

    $criteriaArray = array(
        "weblink_name" => "",
        "weblink_cat" => 0,
        "weblink_url" => "",
        "weblink_description" => "",
		"weblink_language" => LANGUAGE,
    );

		// Cancel Form
        if (isset($_POST['cancel'])) {
            redirect(FUSION_REQUEST);
        }

    if (dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, (multilang_table("WL") ? "weblink_cat_language='".LANGUAGE."' AND " : "")."weblink_cat_status=1 AND ".groupaccess("weblink_cat_visibility")."")) {

		// Save
		if (isset($_POST['submit_link'])) {

            $submit_info['weblink_description'] = nl2br(parseubb(stripinput($_POST['weblink_description'])));

			$criteriaArray = array(
				"weblink_cat" => form_sanitizer($_POST['weblink_cat'], 0, "weblink_cat"),
				"weblink_name" => form_sanitizer($_POST['weblink_name'], "", "weblink_name"),
                "weblink_description" => form_sanitizer($submit_info['weblink_description'], "", "weblink_description"),
				"weblink_url" => form_sanitizer($_POST['weblink_url'], "", "weblink_url"),
				"weblink_language" => form_sanitizer($_POST['weblink_language'], LANGUAGE, "weblink_language"),
			);

			// Save
			if (\defender::safe() && isset($_POST['submit_link'])) {
				$inputArray = array(
					"submit_type" => "l", "submit_user" => fusion_get_userdata('user_id'), "submit_datestamp" => time(),
					"submit_criteria" => addslashes(serialize($criteriaArray))
				);
				dbquery_insert(DB_SUBMISSIONS, $inputArray, "save");
				addNotice("success", $this->locale['WLS_0910']);
				redirect(clean_request("submitted=l", array("stype"), TRUE));
			}

		}

        if (isset($_GET['submitted']) && $_GET['submitted'] == "l") {

            echo "<div class='well text-center text-strong'><p>".$this->locale['WLS_0911']."</p>";
            echo "<p><a href='".BASEDIR."submit.php?stype=l' title=".$this->locale['WLS_0912'].">".$this->locale['WLS_0912']."</a></p>";
            echo "<p><a href='".BASEDIR."index.php'>".str_replace("[SITENAME]", fusion_get_settings("sitename"), $this->locale['WLS_0913'])."</a></p>\n";
            echo "</div>\n";

        } else {


            echo "<div class='alert alert-info m-b-20 submission-guidelines text-center'>".str_replace("[SITENAME]", fusion_get_settings("sitename"),
                                                                                           $this->locale['WLS_0920'])."</div>\n";

            echo openform('submit_form', 'post', BASEDIR."submit.php?stype=l");

            echo form_select_tree("weblink_cat", $this->locale['WLS_0256'], $criteriaArray['weblink_cat'], array(
                "no_root" => TRUE,
                "placeholder" => $this->locale['choose'],
                "query" => (multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."'" : "")
            ), DB_WEBLINK_CATS, "weblink_cat_name", "weblink_cat_id", "weblink_cat_parent");

            echo form_text('weblink_name', $this->locale['WLS_0250'], $criteriaArray['weblink_name'], array(
                "placeholder" => $this->locale['WLS_0251'],
                "error_text" => $this->locale['WLS_0252'],
                'required' => TRUE
            ));

            echo form_text('weblink_url', $this->locale['WLS_0253'], $criteriaArray['weblink_url'], array(
                "type" => "url",
                "placeholder" => "http://",
                "required" => TRUE,
            ));

			if (multilang_table("WL")) {
				echo form_select("weblink_language", $this->locale['language'], $criteriaArray['weblink_language'], array(
					"options" => fusion_get_enabled_languages(), "placeholder" => $this->locale['choose'], "inner_width" => "100%",
				));
			} else {
				echo form_hidden("weblink_language", "", $criteriaArray['weblink_language']);
			}

            $textArea_opts = array(
                "required" => $weblink_settings['links_extended_required'] ? TRUE : FALSE,
                "type" => fusion_get_settings("tinymce_enabled") ? "tinymce" : "html",
                "tinymce" => fusion_get_settings("tinymce_enabled") && iADMIN ? "advanced" : "simple",
                "autosize" => TRUE,
                "form_name" => "submit_form",
            );

            echo form_textarea('weblink_description', $this->locale['WLS_0254'], $criteriaArray['weblink_description'], $textArea_opts);

			echo form_button("cancel_link", $this->locale['cancel'], $this->locale['cancel'], array("class" => "btn-default m-r-10", "icon" => "fa fa-fw fa-times"));
            echo form_button('submit_link', $this->locale['save'], $this->locale['save'], array('class' => "btn-success m-r-10", "icon" => "fa fa-fw fa-hdd-o"));

            echo closeform();
        }

    } else {
	echo "<div class='well text-center'><p>".$this->locale['WLS_0923']."</p></div>\n";
	}
    }

}
