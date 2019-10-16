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
    private $submit_id = 0;

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Handle Preview and Publish of a Weblink Submission
     */
    private function PostSubmission() {

        if (isset($_POST['publish_submission'])) {

            // Check posted Informations
            $weblink_status = filter_input(INPUT_POST, 'weblink_status', FILTER_VALIDATE_INT);
            $this->inputArray = [
                'weblink_name'        => form_sanitizer(filter_input(INPUT_POST, 'weblink_name', FILTER_DEFAULT), '', 'weblink_name'),
                'weblink_description' => form_sanitizer(filter_input(INPUT_POST, 'weblink_description', FILTER_DEFAULT), "", "weblink_description"),
                'weblink_url'         => form_sanitizer(filter_input(INPUT_POST, 'weblink_url', FILTER_DEFAULT), "", 'weblink_url'),
                'weblink_cat'         => form_sanitizer(filter_input(INPUT_POST, 'weblink_cat', FILTER_VALIDATE_INT), 0, 'weblink_cat'),
                'weblink_datestamp'   => form_sanitizer(filter_input(INPUT_POST, 'weblink_datestamp', FILTER_DEFAULT), time(), 'weblink_datestamp'),
                'weblink_visibility'  => form_sanitizer(filter_input(INPUT_POST, 'weblink_visibility', FILTER_VALIDATE_INT), 0, 'weblink_visibility'),
                "weblink_status"      => !empty($weblink_status) ? $weblink_status : "0",
                "weblink_count"       => "0",
                'weblink_language'    => form_sanitizer(filter_input(INPUT_POST, 'weblink_language', FILTER_DEFAULT), LANGUAGE, 'weblink_language'),
                'weblink_user_name'   => form_sanitizer(filter_input(INPUT_POST, 'weblink_user_name', FILTER_DEFAULT), '', 'weblink_user_name'),
            ];

            // Handle
            if (\defender::safe()) {

                // Publish Submission
                $publish_submission = filter_input(INPUT_POST, 'publish_submission', FILTER_DEFAULT);
                if (!empty($publish_submission)) {
                    dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id=:submitid AND submit_type=:submittype", [':submitid' => (int)$this->submit_id, ':submittype' => 'l']);
                    dbquery_insert(DB_WEBLINKS, $this->inputArray, 'save');
                    addNotice('success', $this->locale['WLS_0060']);
                    redirect(clean_request('', ['submit_id'], FALSE));
                }

            }
        }
    }

    /**
     * Delete a Weblink Submission
     */
    private function DeleteSubmission() {
        $delete_submission = filter_input(INPUT_POST, 'delete_submission', FILTER_DEFAULT);

        if (!empty($delete_submission)) {
            dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id = :submitid AND submit_type = :submittype", [':submitid' => (int)$delete_submission, ':submittype' => 'l']);
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
            WHERE s.submit_id = :submitid
            LIMIT 0,1
        ", [':submitid' => $this->submit_id]);

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
                'weblink_status'      => 0
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
        echo form_hidden('weblink_status', '', 1);
        echo form_hidden('weblink_user_name', '', $this->inputArray['weblink_user_name']);
        ?>
        <div class="well clearfix m-t-15">
            <div class="pull-left">
                <?php echo display_avatar($this->dataUser, "30px", "", FALSE, "img-rounded m-t-5 m-r-5"); ?>
            </div>
            <div class="overflow-hide">
                <?php
                $submissionUser = ($this->dataUser['user_name'] != $this->locale['user_na'] ? profile_link($this->dataUser['user_id'], $this->dataUser['user_name'], $this->dataUser['user_status']) : $this->locale['user_na']);
                $submissionDate = showdate("shortdate", $this->inputArray['weblink_datestamp']);
                $submissionTime = timer($this->inputArray['weblink_datestamp']);

                $replacements = ["{%SUBMISSION_AUTHOR%}" => $submissionUser, "{%SUBMISSION_DATE%}" => $submissionDate, "{%SUBMISSION_TIME%}" => $submissionTime];
                $submissionInfo = strtr($this->locale['WLS_0350']."<br />".$this->locale['WLS_0351'], $replacements);

                echo $submissionInfo;
                ?>
            </div>
        </div>
        <?php ?>

        <!-- Display Form -->
        <div class="row">

            <!-- Display Left Column -->
            <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8">
                <?php

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

                ?>
            </div>

            <!-- Display Right Column -->
            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
                <?php

                openside($this->locale['WLS_0260']);

                echo form_select_tree('weblink_cat', $this->locale['WLS_0101'], $this->inputArray['weblink_cat'], [
                    'no_root'     => TRUE,
                    'inner_width' => '100%',
                    'placeholder' => $this->locale['choose'],
                    'query'       => (multilang_table("WL") ? "WHERE ".in_group('weblink_cat_language', LANGUAGE) : "")
                ], DB_WEBLINK_CATS, "weblink_cat_name", "weblink_cat_id", "weblink_cat_parent");

                echo form_select('weblink_visibility', $this->locale['WLS_0103'], $this->inputArray['weblink_visibility'], [
                    'options'     => fusion_get_groups(),
                    'placeholder' => $this->locale['choose'],
                    'inner_width' => "100%"
                ]);

                if (multilang_table("WL")) {
                    echo form_select('weblink_language[]', $this->locale['language'], $this->inputArray['weblink_language'], [
                        'options'     => fusion_get_enabled_languages(),
                        'placeholder' => $this->locale['choose'],
                        'inner_width' => '100%',
                        'multiple'    => TRUE,
                        'delimeter'   => '.'
                    ]);
                } else {
                    echo form_hidden('article_language', '', $this->inputArray['article_language']);
                }

                /**/
                echo form_datepicker('weblink_datestamp', $this->locale['WLS_0103'], $this->inputArray['weblink_datestamp'], [
                    'inner_width' => '100%'
                ]);
                self::displayFormButtons("formstart", FALSE);
                closeside();

                ?>

            </div>
        </div>
        <?php
        self::displayFormButtons("formend", FALSE);
        echo closeform();
    }

    /**
     * Display Buttons for Form
     *
     * @param      $unique_id
     * @param bool $breaker
     */
    private function displayFormButtons($unique_id, $breaker = TRUE) {
        echo "<div class='m-t-20'>";
        echo form_button('publish_submission', $this->locale['publish'], $this->locale['publish'], [
            'class'    => "btn-success m-r-10",
            'icon'     => "fa fa-fw fa-hdd-o",
            'input-id' => "publish_submission-".$unique_id
        ]);
        echo form_button('delete_submission', $this->locale['delete'], $this->locale['delete'], [
            'class'    => "btn-danger m-r-10",
            'icon'     => "fa fa-fw fa-trash",
            'input-id' => "delete_submission-".$unique_id
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

        $result = dbquery("
            SELECT s.submit_id, s.submit_criteria, s.submit_datestamp, u.user_id, u.user_name, u.user_status, u.user_avatar
            FROM ".DB_SUBMISSIONS." AS s
            LEFT JOIN ".DB_USERS." AS u ON u.user_id=s.submit_user
            WHERE s.submit_type='l'
            ORDER BY submit_datestamp DESC
        ");
        ?>

        <!-- Display Table -->
        <div class="table-responsive m-t-10">
            <table class="table table-striped">
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
                        $submitData = \defender::decode($data['submit_criteria']);

                        $submitUser = $this->locale['user_na'];
                        if ($data['user_name']) {
                            $submitUser = display_avatar($data, "20px", "", FALSE, "img-rounded m-r-5");
                            $submitUser .= profile_link($data['user_id'], $data['user_name'], $data['user_status']);
                        }

                        $reviewLink = clean_request("section=submissions&submit_id=".$data['submit_id'], ["section", "ref", "action", "submit_id"], FALSE);
                        ?>
                        <tr>
                            <td>#<?php echo $data['submit_id']; ?></td>
                            <td><span class="text-dark"><?php echo $submitData['weblink_name']; ?></span></td>
                            <td><?php echo $submitUser; ?></td>
                            <td><?php echo timer($data['submit_datestamp']); ?></td>
                            <td>
                                <a href="<?php echo $reviewLink; ?>" title="<?php echo $this->locale['WLS_0205']; ?>"
                                   class="btn btn-default btn-sm"><i
                                            class="fa fa-fw fa-eye"></i> <?php echo $this->locale['WLS_0205']; ?></a>
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
            </table>
        </div>
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
        $this->submit_id = filter_input(INPUT_GET, 'submit_id', FILTER_VALIDATE_INT);
        if (!empty($this->submit_id) && dbcount("(submit_id)", DB_SUBMISSIONS, "submit_id=:submitid AND submit_type=:submittype", [':submitid' => (int)$this->submit_id, ':submittype' => 'l'])) {
            $this->inputArray = self::unserializeData();

            // Get Infos about Submissioner
            $resultUser = dbquery("SELECT user_id, user_name, user_status, user_avatar FROM ".DB_USERS." WHERE user_id=:userid LIMIT 0,1", [':userid' => $this->inputArray['weblink_user']]);
            if (dbrows($resultUser) > 0) {
                $this->dataUser = dbarray($resultUser);
            } else {
                $this->dataUser = ['user_id' => $this->inputArray['weblink_name'], 'user_name' => $this->locale['user_na'], 'user_status' => 0, 'user_avatar' => FALSE];
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
