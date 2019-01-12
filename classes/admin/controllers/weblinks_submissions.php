<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/admin/controllers/weblinks_submissions.php
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

class WeblinksSubmissionsAdmin extends WeblinksAdminModel {
    private static $instance = NULL;
    private $inputArray = [];
    private $locale = [];
    private $dataUser = [];
    private $weblinksettings = [];

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Handle Publish of a Weblink Submission
     */
    private function PostSubmission() {

        if (isset($_POST['publish_submission'])) {

            // Check posted Informations
            $this->inputArray = [
                'weblink_name'        => form_sanitizer($_POST['weblink_name'], '', 'weblink_name'),
                'weblink_description' => form_sanitizer($_POST['weblink_description'], '', 'weblink_description'),
                'weblink_url'         => form_sanitizer($_POST['weblink_url'], '', 'weblink_url'),
                'weblink_cat'         => form_sanitizer($_POST['weblink_cat'], 0, 'weblink_cat'),
                'weblink_datestamp'   => form_sanitizer($_POST['weblink_datestamp'], time(), 'weblink_datestamp'),
                'weblink_visibility'  => form_sanitizer($_POST['weblink_visibility'], 0, 'weblink_visibility'),
                'weblink_status'      => form_sanitizer($_POST['weblink_status'], 0, 'weblink_status'),
                'weblink_count'       => '0',
                'weblink_language'    => form_sanitizer($_POST['weblink_language'], LANGUAGE, 'weblink_language'),
                'weblink_user_name'   => form_sanitizer($_POST['weblink_user_name'], '', 'weblink_user_name'),
            ];

            // Handle
            if (\defender::safe()) {
                // Publish Submission
                dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id=:submitid AND submit_type=:submittype", [':submitid' => intval($_GET['submit_id']), ':submittype' => 'l']);
                dbquery_insert(DB_WEBLINKS, $this->inputArray, 'save');
                addNotice('success', $this->locale['WLS_0060']);
                redirect(clean_request('', ['submit_id'], FALSE));
            }
        }
    }

    /**
     * Delete a Weblink Submission
     */
    private function DeleteSubmission() {
        if (isset($_POST['delete_submission'])) {
            dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id=:submitid AND submit_type=:submittype", [':submitid' => intval($_GET['submit_id']), ':submittype' => 'l']);
            addNotice('success', $this->locale['WLS_0061']);
            redirect(clean_request('', ['submit_id'], FALSE));
        }
    }

    /**
     * Get unserialize Datas for a Submission
     */
    private function unserializeData() {
        $result = dbquery("SELECT s.*
            FROM ".DB_SUBMISSIONS." AS s
            WHERE s.submit_id=:submitid
            LIMIT 0,1", [':submitid' => intval($_GET['submit_id'])]
        );

        if (dbrows($result) > 0) {
            $data = dbarray($result);
            $submit_criteria = \defender::decode($data['submit_criteria']);
            $returnInformations = [
                'weblink_user_name'   => $data['submit_user'],
                'weblink_name'        => $submit_criteria['weblink_name'],
                'weblink_cat'         => $submit_criteria['weblink_cat'],
                'weblink_description' => phpentities(stripslashes($submit_criteria['weblink_description'])),
                'weblink_url'         => $submit_criteria['weblink_url'],
                'weblink_visibility'  => 0,
                'weblink_language'    => $submit_criteria['weblink_language'],
                'weblink_datestamp'   => $data['submit_datestamp'],
                'weblink_user'        => $data['submit_user'],
                'weblink_status'      => 1
            ];
            return $returnInformations;
        } else {
            redirect(clean_request("", [], FALSE));
        }

        return NULL;
    }

    /**
     * Display Form
     */
    private function displayForm() {

        // Textarea Settings
        if (!fusion_get_settings("tinymce_enabled")) {
            $weblinkSnippetSettings = [
                'required'    => TRUE,
                'preview'     => TRUE,
                'html'        => TRUE,
                'autosize'    => TRUE,
                'placeholder' => $this->locale['WLS_0255'],
                'error_text'  => $this->locale['WLS_0270']
            ];
        } else {
            $weblinkSnippetSettings = [
                'required'   => TRUE,
                'type'       => "tinymce",
                'tinymce'    => "advanced",
                'error_text' => $this->locale['WLS_0270']
            ];
        }

        // Start Form
        echo openform('submissionform', 'post', FUSION_REQUEST);
        echo form_hidden('weblink_user_name', '', $this->inputArray['weblink_user_name']);
        self::displayFormButtons("formstart", FALSE);

        echo "<div class='well clearfix m-t-15'>\n";
        echo "<div class='pull-left'>\n";
        echo display_avatar($this->dataUser, '30px', '', TRUE, 'img-rounded m-t-5 m-r-5');
        echo "</div>\n";
        echo "<div class='overflow-hide'>\n";
        $submissionUser = ($this->dataUser['user_name'] != $this->locale['user_na'] ? profile_link($this->dataUser['user_id'], $this->dataUser['user_name'], $this->dataUser['user_status']) : $this->locale['user_na']);
        $submissionDate = showdate("shortdate", $this->inputArray['weblink_datestamp']);
        $submissionTime = timer($this->inputArray['weblink_datestamp']);

        $replacements = ["{%SUBMISSION_AUTHOR%}" => $submissionUser, "{%SUBMISSION_DATE%}" => $submissionDate, "{%SUBMISSION_TIME%}" => $submissionTime];
        $submissionInfo = strtr($this->locale['WLS_0350']."<br />".$this->locale['WLS_0351'], $replacements);

        echo $submissionInfo;
        echo "</div>\n";
        echo "</div>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
        echo form_text('weblink_name', $this->locale['WLS_0201'], $this->inputArray['weblink_name'], [
            'required'    => TRUE,
            'placeholder' => $this->locale['WLS_0201'],
            'error_text'  => $this->locale['WLS_0252']
        ]);

        echo form_text('weblink_url', $this->locale['WLS_0253'], $this->inputArray['weblink_url'], [
            'required'    => TRUE,
            'type'        => "url",
            'placeholder' => "http://"
        ]);

        echo form_textarea('weblink_description', $this->locale['WLS_0254'], $this->inputArray['weblink_description'], $weblinkSnippetSettings);

        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";

        openside($this->locale['WLS_0260']);
        $options = [0 => $this->locale['draft'], 1 => $this->locale['publish']];
        echo form_select('weblink_status', $this->locale['status'], $this->inputArray['weblink_status'], [
            'inner_width' => '100%',
            'options'     => $options
        ]);

        echo form_select_tree('weblink_cat', $this->locale['WLS_0101'], $this->inputArray['weblink_cat'], [
            'no_root'     => TRUE,
            'inner_width' => '100%',
            'placeholder' => $this->locale['choose'],
            'query'       => (multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."'" : "")
            ], DB_WEBLINK_CATS, 'weblink_cat_name', 'weblink_cat_id', 'weblink_cat_parent'
        );

        echo form_select('weblink_visibility', $this->locale['WLS_0103'], $this->inputArray['weblink_visibility'], [
            'options'     => fusion_get_groups(),
            'placeholder' => $this->locale['choose'],
            'inner_width' => '100%'
        ]);

        if (multilang_table("WL")) {
        	echo form_select('weblink_language', $this->locale['language'], $this->inputArray['weblink_language'], [
        	    'options'     => fusion_get_enabled_languages(),
        	    'placeholder' => $this->locale['choose'],
        	    'inner_width' => '100%'
        	]);
        } else {
        	echo form_hidden('weblink_language', '', $this->inputArray['weblink_language']);
        }

        echo form_datepicker('weblink_datestamp', $this->locale['WLS_0106'], $this->inputArray['weblink_datestamp'], [
            'inner_width' => '100%'
        ]);

        closeside();
        echo closeform();
        echo "</div>\n";
        echo "</div>\n";
    }

    /**
     * Display Buttons for Form
     *
     * @param      $unique_id
     * @param bool $breaker
     */
    private function displayFormButtons($unique_id, $breaker = TRUE) {
        echo "<div class='spacer-sm'>";
        echo form_button('publish_submission', $this->locale['publish'], $this->locale['publish'], [
            'class'    => 'btn-success m-r-10',
            'icon'     => 'fa fa-hdd-o',
            'input-id' => 'publish_submission-'.$unique_id
        ]);
        echo form_button('delete_submission', $this->locale['delete'], $this->locale['delete'], [
            'class'    => 'btn-danger',
            'icon'     => 'fa fa-trash',
            'input-id' => 'delete_submission-'.$unique_id
        ]);
        echo "</div>";
        if ($breaker) {
            echo "<hr/>";
        }
    }

    /**
     * Display List with Submissions
     */
    private function displaySubmissionList() {

        $result = dbquery("SELECT s.submit_id, s.submit_criteria, s.submit_datestamp, su.user_id, su.user_name, su.user_status, su.user_avatar
            FROM ".DB_SUBMISSIONS." AS s
            LEFT JOIN ".DB_USERS." AS su ON su.user_id = s.submit_user
            WHERE s.submit_type=:type
            ORDER BY submit_datestamp DESC", [':type' => 'l']
        );
        $rows = dbrows($result);
        if ($rows > 0) {
        	echo "<div class='well m-t-15'>".sprintf($this->locale['WLS_0063'], format_word($rows, $this->locale['fmt_submission']))."</div>\n";
            echo "<div class='table-responsive'><table class='table table-striped'>\n";
            echo "<thead>\n";
            echo "<tr>\n";
            echo "<th>".$this->locale['WLS_0200']."</th>\n";
            echo "<th>".$this->locale['WLS_0201']."</th>\n";
            echo "<th>".$this->locale['WLS_0202']."</th>\n";
            echo "<th>".$this->locale['WLS_0203']."</th>\n";
            echo "</tr>\n";
            echo "</thead>\n";
            echo "<tbody>\n";
            while ($data = dbarray($result)) {
            	$submitData = \defender::decode($data['submit_criteria']);

                $submitUser = $this->locale['user_na'];
                if ($data['user_name']) {
                	$submitUser = display_avatar($data, '20px', '', FALSE, 'img-rounded m-r-5');
                	$submitUser .= profile_link($data['user_id'], $data['user_name'], $data['user_status']);
                }

                $reviewLink = clean_request("submit_id=".$data['submit_id'], ['section', 'aid'], TRUE);
                echo "<tr>\n";
                echo "<td>".$data['submit_id']."</td>\n";
                echo "<td><a href='".$reviewLink."'>".$submitData['weblink_name']."</a></td>\n";
                echo "<td>".$submitUser."</td>\n";
                echo "<td>".timer($data['submit_datestamp'])."</td>\n";
                echo "</tr>\n";
            }
            echo "</tbody>\n";
            echo "</table>\n</div>";
        } else {
        	echo "<div class='well text-center m-t-20'>".$this->locale['WLS_0062']."</div>\n";
        }
    }

    /**
     * Display Admin Area
     */
    public function displayWeblinksAdmin() {
        pageAccess("W");

        $this->locale = self::get_WeblinkAdminLocale();
        $this->weblinksettings = self::get_weblink_settings();

        // Handle a Submission
        if (isset($_GET['submit_id']) && isnum($_GET['submit_id'])) {
            $max_rows = dbcount("(submit_id)", DB_SUBMISSIONS, "submit_id=:submitid AND submit_type=:submittype", [':submitid' => $_GET['submit_id'], ':submittype' => 'l']);
            $this->inputArray = self::unserializeData();
            // Get Infos about Submissioner
            $result = dbquery("SELECT user_id, user_name, user_status, user_avatar FROM ".DB_USERS." WHERE user_id=:userid LIMIT 0,1", [':userid' => $this->inputArray['weblink_user']]);
            if (dbrows($result) > 0) {
                $this->dataUser = dbarray($result);
            } else {
                $this->dataUser = ['user_id' => $this->inputArray['weblink_name'], 'user_name' => $this->locale['user_na'], 'user_status' => 0, 'user_avatar' => FALSE];
            }

            if (isset($_POST['publish_submission']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id'])) && $max_rows) {
                self::PostSubmission();
            } else if (isset($_POST['delete_submission']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id'])) && $max_rows) {
                self::DeleteSubmission();
            }
            // Display Form with Buttons
            self::displayForm();

        } else {
            self::displaySubmissionList();
        }

    }
}
