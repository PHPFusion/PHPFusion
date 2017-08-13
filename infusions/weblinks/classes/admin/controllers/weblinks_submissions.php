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

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Handle Preview and Publish of a Article Submission
     */
    private function PostSubmission() {

        if (isset($_POST['publish_submission'])) {

            // Check posted Informations
            $this->inputArray = array(
                'weblink_name' => form_sanitizer($_POST['weblink_name'], '', 'weblink_name'),
                'weblink_description' => form_sanitizer($_POST['weblink_description'], "", "weblink_description"),
                'weblink_url' => form_sanitizer($_POST['weblink_url'], "", 'weblink_url'),
                'weblink_cat' => form_sanitizer($_POST['weblink_cat'], 0, 'weblink_cat'),
                'weblink_datestamp' => form_sanitizer($_POST['weblink_datestamp'], time(), 'weblink_datestamp'),
                'weblink_visibility' => form_sanitizer($_POST['weblink_visibility'], 0, 'weblink_visibility'),
                "weblink_status" => isset($_POST['weblink_status']) ? "1" : "0",
                "weblink_count" => "0",
                'weblink_language' => form_sanitizer($_POST['weblink_language'], LANGUAGE, 'weblink_language'),
                'weblink_user_name' => form_sanitizer($_POST['weblink_user_name'], '', 'weblink_user_name'),
            );

            // Handle
            if (\defender::safe()) {

                // Publish Submission
                if (isset($_POST['publish_submission'])) {
                    dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($_GET['submit_id'])."' AND submit_type='l'");
                    dbquery_insert(DB_WEBLINKS, $this->inputArray, "save");
                    addNotice("success", $this->locale['WLS_0060']);
                    redirect(clean_request("", array("submit_id"), FALSE));
                }

            }
        }
    }

    /**
     * Delete a Article Submission
     */
    private function DeleteSubmission() {
        if (isset($_POST['delete_submission'])) {
            dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($_GET['submit_id'])."' AND submit_type='l'");
            addNotice("success", $this->locale['WLS_0061']);
            redirect(clean_request("", array("submit_id"), FALSE));
        }
    }

    /**
     * Get unserialize Datas for a Submission
     */
    private function unserializeData() {

        $result = dbquery("
            SELECT
                s.*
            FROM ".DB_SUBMISSIONS." AS s
            WHERE s.submit_id='".intval($_GET['submit_id'])."'
            LIMIT 0,1
        ");

        if (dbrows($result) > 0) {
            $data = dbarray($result);
            $submit_criteria = unserialize($data['submit_criteria']);
            $returnInformations = array(
                "weblink_user_name" => $data['submit_user'],
                "weblink_name" => $submit_criteria['weblink_name'],
                "weblink_cat" => $submit_criteria['weblink_cat'],
                "weblink_description" => phpentities(stripslashes($submit_criteria['weblink_description'])),
                "weblink_url" => $submit_criteria['weblink_url'],
                "weblink_visibility" => 0,
                "weblink_language" => $submit_criteria['weblink_language'],
                "weblink_datestamp" => $data['submit_datestamp'],
                "weblink_user" => $data['submit_user'],
                "weblink_status" => 0
            );
            return $returnInformations;
        } else {
            redirect(clean_request("", array(), FALSE));
        }
    }

    /**
     * Display Form
     */
    private function displayForm() {

        // Textarea Settings
        if (!fusion_get_settings("tinymce_enabled")) {
            $weblinkSnippetSettings = array(
                "required" => true, "preview" => true, "html" => true, "autosize" => true, "placeholder" => $this->locale['WLS_0255'],
                "error_text" => $this->locale['WLS_0270']
            );
        } else {
            $weblinkSnippetSettings  = array("required" => true, "type" => "tinymce", "tinymce" => "advanced", "error_text" => $this->locale['WLS_0270']);
        }

        // Start Form
        echo openform("submissionform", "post", FUSION_REQUEST);
        echo form_hidden("weblink_status", "", 1);
        echo form_hidden("weblink_user_name", "", $this->inputArray['weblink_user_name']);
        ?>
        <div class="well clearfix">
          <div class="pull-left">
            <?php echo display_avatar($this -> dataUser, "30px", "", FALSE, "img-rounded"); ?>
          </div>
          <div class="overflow-hide">
            <?php
            $submissionUser = ($this -> dataUser['user_name'] != $this->locale['user_na'] ? profile_link($this -> dataUser['user_id'], $this -> dataUser['user_name'], $this -> dataUser['user_status']) : $this -> locale['user_na']);
            $submissionDate = showdate("shortdate", $this -> inputArray['weblink_datestamp']);
            $submissionTime = timer($this -> inputArray['weblink_datestamp']);

            $replacements = array("{%SUBMISSION_AUTHOR%}" => $submissionUser, "{%SUBMISSION_DATE%}" => $submissionDate, "{%SUBMISSION_TIME%}" => $submissionTime);
            $submissionInfo = strtr($this->locale['WLS_0350']."<br />".$this->locale['WLS_0351'], $replacements);

            echo $submissionInfo;
            ?>
          </div>
        </div>
        <?php  ?>

        <!-- Display Form -->
        <div class="row">

            <!-- Display Left Column -->
            <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8">
                <?php

                echo form_text("weblink_name", $this->locale['WLS_0201'], $this->inputArray['weblink_name'], array(
                    "required" => true, "placeholder" => $this->locale['WLS_0201'], "error_text" => $this->locale['WLS_0252']
                ));

                echo form_text("weblink_url", $this->locale['WLS_0253'], $this->inputArray['weblink_url'], array(
                    "required" => true, "type" => "url", "placeholder" => "http://"
                ));

                echo form_textarea("weblink_description", $this->locale['WLS_0254'], $this->inputArray['weblink_description'], $weblinkSnippetSettings);

                ?>
            </div>

            <!-- Display Right Column -->
            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
                <?php

                openside($this->locale['WLS_0260']);

                echo form_select_tree("weblink_cat", $this->locale['WLS_0101'], $this->inputArray['weblink_cat'], array(
                    "no_root" => TRUE,
                    "inner_width" => "100%",
                    "placeholder" => $this->locale['choose'],
                    "query" => (multilang_table("WL") ? "WHERE weblink_cat_language='".LANGUAGE."'" : "")
                ), DB_WEBLINK_CATS, "weblink_cat_name", "weblink_cat_id", "weblink_cat_parent");

                echo form_select('weblink_visibility', $this->locale['WLS_0103'], $this->inputArray['weblink_visibility'], array(
                    "options" => fusion_get_groups(), "placeholder" => $this->locale['choose'], "inner_width" => "100%",
                ));

                if (multilang_table("WL")) {
                    echo form_select("weblink_language", $this->locale['language'], $this->inputArray['weblink_language'], array(
                        "options" => fusion_get_enabled_languages(), "placeholder" => $this->locale['choose'], "inner_width" => "100%",
                    ));
                } else {
                    echo form_hidden("article_language", "", $this->inputArray['article_language']);
                }

                /**/
                echo form_datepicker("weblink_datestamp", $this->locale['WLS_0103'], $this->inputArray['weblink_datestamp'], array(
                    "inner_width" => "100%"
                ));
                self::displayFormButtons("formstart", FALSE);
                closeside();

                ?>

            </div>
        </div>
        <?php
        self::displayFormButtons("formend", false);
        echo closeform();
    }

    /**
     * Display Buttons for Form
     */
    private function displayFormButtons($unique_id, $breaker = true) {
        ?>
        <div class="m-t-20">
          <?php echo form_button("publish_submission", $this->locale['publish'], $this->locale['publish'], array("class" => "btn-success m-r-10", "icon" => "fa fa-fw fa-hdd-o", "input-id" => "publish_submission-".$unique_id."")); ?>
          <?php echo form_button("delete_submission", $this->locale['delete'], $this->locale['delete'], array("class" => "btn-danger m-r-10", "icon" => "fa fa-fw fa-trash", "input-id" => "delete_submission-".$unique_id."")); ?>
        </div>
        <?php if ($breaker) { ?><hr /><?php } ?>
        <?php
    }

    /**
     * Display List with Submissions
     */
    private function displaySubmissionList() {

        $result = dbquery("
            SELECT
                s.submit_id, s.submit_criteria, s.submit_datestamp, u.user_id, u.user_name, u.user_status, u.user_avatar
            FROM ".DB_SUBMISSIONS." AS s
            LEFT JOIN ".DB_USERS." AS u ON u.user_id=s.submit_user
            WHERE s.submit_type='l'
            ORDER BY submit_datestamp DESC
        ");
        ?>

        <!-- Display Table -->
        <div class="table-responsive"><table class="table table-striped">
            <thead>
            <tr>
                <td class="strong"><?php echo $this->locale['WLS_0200']; ?></td>
                <td class="strong col-xs-5"><?php echo $this->locale['WLS_0201'] ?></td>
                <td class="strong"><?php echo $this->locale['WLS_0202'] ?></td>
                <td class="strong"><?php echo $this->locale['WLS_0203'] ?></td>
                <td class="strong"><?php echo $this->locale['WLS_0204'] ?></td>
            </tr>
            </thead>
            <tbody>
            <?php if (dbrows($result) > 0) :
                while ($data = dbarray($result)) : ?>
                    <?php
                    $submitData = unserialize($data['submit_criteria']);

                    $submitUser = $this->locale['user_na'];
                    if ($data['user_name']) {
                        $submitUser = display_avatar($data, "20px", "", FALSE, "img-rounded m-r-5");
                        $submitUser .= profile_link($data['user_id'], $data['user_name'], $data['user_status']);
                    }

                    $reviewLink = clean_request("section=submissions&submit_id=".$data['submit_id'], array("section", "ref", "action", "submit_id"), false);
                    ?>
                    <tr>
                        <td>#<?php echo $data['submit_id']; ?></td>
                        <td><span class="text-dark"><?php echo $submitData['weblink_name']; ?></span></td>
                        <td><?php echo $submitUser; ?></td>
                        <td><?php echo timer($data['submit_datestamp']); ?></td>
                        <td>
                          <a href="<?php echo $reviewLink; ?>" title="<?php echo $this->locale['WLS_0205']; ?>" class="btn btn-default btn-sm"><i class="fa fa-fw fa-eye"></i> <?php echo $this->locale['WLS_0205']; ?></a>
                        </td>
                    </tr>
                    <?php
                endwhile;
            else: ?>
                <tr>
                    <td colspan="5" class="text-center"><?php echo $this->locale['WLS_0062']; ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table></div>
        <?php
    }


    /**
     * Display Admin Area
    */
    public function displayWeblinksAdmin() {
        pageAccess("W");

        $this->locale = self::get_WeblinkAdminLocale();
        $this->weblinksettings = self::get_weblink_settings();

        // Handle a Submission
        if (isset($_GET['submit_id']) && isNum($_GET['submit_id']) && dbcount("(submit_id)", DB_SUBMISSIONS, "submit_id='".$_GET['submit_id']."' AND submit_type='l'")) {
            $this->inputArray = self::unserializeData();

            // Get Infos about Submissioner
            $resultUser = dbquery("SELECT user_id, user_name, user_status, user_avatar FROM ".DB_USERS." WHERE user_id='".$this -> inputArray['weblink_user']."' LIMIT 0,1");
            if (dbrows($resultUser) > 0) {
                $this -> dataUser = dbarray($resultUser);
            } else {
                $this -> dataUser = array("user_id" => $this -> inputArray['weblink_name'], "user_name" => $this -> locale['user_na'], "user_status" => 0, "user_avatar" => false);
            }

            // Delete, Publish, Preview
            self::DeleteSubmission();
            self::PostSubmission();

            // Display Form with Buttons
            self::displayForm();

        // Display List
        } else {
            self::displaySubmissionList();
        }
    }
}
