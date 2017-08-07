<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/admin/controllers/faq_submissions.inc
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
namespace PHPFusion\FAQ;

class FaqSubmissionsAdmin extends FaqAdminModel {

    private static $instance = NULL;
    private $locale = [];
    private $inputArray = [];
    private static $defArray = array(
        "faq_breaks" => "y",
        "faq_visibility" => 0,
        "faq_status" => 1,
    );

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    private function handlePostSubmission() {

        if (isset($_POST['publish_submission']) || isset($_POST['preview_submission'])) {

            // Check posted Informations
            $faq_answer = "";
            if ($_POST['faq_answer']) {
                $faq_answer = (fusion_get_settings("allow_php_exe") ? htmlspecialchars($_POST['faq_answer']) : stripslashes($_POST['faq_answer']));
            }

            $SaveinputArray = array(
                'faq_question'   => form_sanitizer($_POST['faq_question'], '', 'faq_question'),
                'faq_cat_id'     => form_sanitizer($_POST['faq_cat_id'], 0, 'faq_cat_id'),
                'faq_visibility' => form_sanitizer($_POST['faq_visibility'], 0, 'faq_visibility'),
                'faq_datestamp'  => form_sanitizer($_POST['faq_datestamp'], time(), 'faq_datestamp'),
                'faq_name'       => form_sanitizer($_POST['faq_name'], 0, 'faq_name'),
                'faq_answer'     => form_sanitizer($faq_answer, '', 'faq_answer'),
                'faq_status'     => isset($_POST['faq_status']) ? '1' : '0',
                'faq_breaks'     => 'n',
                'faq_language'   => form_sanitizer($_POST['faq_language'], LANGUAGE, 'faq_language')
            );

            // Line Breaks
            if (fusion_get_settings("tinymce_enabled") != 1) {
                $SaveinputArray['faq_breaks'] = isset($_POST['faq_breaks']) ? "y" : "n";
            }

            // Handle
            if (\defender::safe()) {

                // Publish Submission
                if (isset($_POST['publish_submission'])) {
                    dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."' AND submit_type='q'");
                    dbquery_insert(DB_FAQS, $SaveinputArray, "save");
                    addNotice("success", ($SaveinputArray['faq_status'] ? $this->locale['faq_0060'] : $this->locale['faq_0061']));
                    redirect(clean_request("", array("submit_id"), FALSE));
                }

                // Preview Submission
                if (isset($_POST['preview_submission'])) {
                    $footer = openmodal("faq_preview", "<i class='fa fa-eye fa-lg m-r-10'></i> ".$this->locale['preview'].": ".$SaveinputArray['faq_question']);
                    if ($SaveinputArray['faq_answer']) {
                        $footer .=  "<hr class='m-t-20 m-b-20'>\n";
                        $footer .=  ($SaveinputArray['faq_breaks'] == "n" ? parse_textarea($SaveinputArray['faq_answer']) : nl2br(parse_textarea($SaveinputArray['faq_answer'])));
                    }
                    $footer .= closemodal();
                    add_to_footer($footer);
                }
            }
        }
    }

    /**
     * Display Form
     */
    private function displayForm() {


      // Textarea Settings
        if (!fusion_get_settings("tinymce_enabled")) {
            $faqExtendedSettings = array(
                "required"   => TRUE, "preview" => true, "html" => true, "autosize" => true, "placeholder" => $this->locale['faq_0253'],
                "error_text" => $this->locale['faq_0270'], "form_name" => "articleform", "wordcount" => true
            );
        } else {
            $faqExtendedSettings = array("required" => TRUE, "type" => "tinymce", "tinymce" => "advanced", "error_text" => $this->locale['faq_0270']);
        }

        // Start Form
        echo openform("submissionform", "post", FUSION_REQUEST);
        echo form_hidden("faq_name", "", $this->inputArray['user_id']);
        ?>
        <div class="well clearfix">
          <div class="pull-left">
            <?php echo display_avatar($this->inputArray, "30px", "", FALSE, "img-rounded m-r-5"); ?>
          </div>
          <div class="overflow-hide">
            <?php
            $submissionUser = ($this->inputArray['user_name'] != $this->locale['user_na'] ? profile_link($this->inputArray['user_id'], $this->inputArray['user_name'], $this->inputArray['user_status']) : $this->locale['user_na']);
            $submissionDate = showdate("shortdate", $this->inputArray['submit_datestamp']);
            $submissionTime = timer($this->inputArray['submit_datestamp']);

            $replacements = array("{%SUBMISSION_AUTHOR%}" => $submissionUser, "{%SUBMISSION_DATE%}" => $submissionDate, "{%SUBMISSION_TIME%}" => $submissionTime);
            $submissionInfo = strtr($this->locale['faq_0350']."<br />".$this -> locale['faq_0351'], $replacements);

            echo $submissionInfo;
            ?>
          </div>
        </div>
        <?php self::displayFormButtons("formstart", true); ?>

        <!-- Display Form -->
        <div class="row">

            <!-- Display Left Column -->
            <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8">
                <?php
                echo form_text("faq_question", $this->locale['faq_0100'], $this->inputArray['faq_question'], array(
                    "required" => true, "max_lenght" => 200, "error_text" => $this->locale['faq_0270']
                ));

                echo form_textarea("faq_answer", $this->locale['faq_0251'], $this->inputArray['faq_answer'], $faqExtendedSettings);
                ?>
            </div>

            <!-- Display Right Column -->
            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
                <?php

                openside($this->locale['faq_0259']);
                $options = [];
                $faq_result = dbquery("SELECT faq_cat_id, faq_cat_name FROM ".DB_FAQ_CATS." ORDER BY faq_cat_name ASC");
                if (dbrows($faq_result)) {
                    $options[0] = $this->locale['faq_0010'];
                    while ($faq_data = dbarray($faq_result)) {
                        $options[$faq_data['faq_cat_id']] = $faq_data['faq_cat_name'];
                    }
                }
                echo form_select('faq_cat_id', $this->locale['faq_0252'], $this->inputArray['faq_cat_id'], array(
                    'inner_width' => '100%',
                    'inline'      => TRUE,
                    'options'     => $options,
                ));
                echo form_select('faq_visibility', $this->locale['faq_0106'], $this->inputArray['faq_visibility'], array(
                    'options' => fusion_get_groups(), 'placeholder' => $this->locale['choose'], 'inner_width' => '100%', 'inline' => TRUE,
                ));

                if (multilang_table('FQ')) {
                    echo form_select('faq_language', $this->locale['language'], $this->inputArray['faq_language'], array(
                        'options' => fusion_get_enabled_languages(), 'placeholder' => $this->locale['choose'], 'inner_width' => '100%', 'inline' => TRUE,
                    ));
                } else {
                    echo form_hidden('faq_language', "", $this->inputArray['faq_language']);
                }

                echo form_datepicker('faq_datestamp', $this->locale['faq_0203'], $this->inputArray['submit_datestamp'], array(
                    'inline' => TRUE, 'inner_width' => '100%'
                ));

                closeside();

                openside($this->locale['faq_0259']);

                echo form_checkbox('faq_status', $this->locale['faq_0255'], $this->inputArray['faq_status'], array(
                    'class' => 'm-b-5', 'reverse_label' => TRUE
                ));

                if (fusion_get_settings('tinymce_enabled') != 1) {
                    echo form_checkbox('faq_breaks', $this->locale['faq_0256'], $this->inputArray['faq_breaks'], array(
                        'value' => 'y', 'class' => 'm-b-5', 'reverse_label' => TRUE
                    ));
                }

                closeside();
                ?>

            </div>
        </div>
        <?php
        self::displayFormButtons("formend", false);
        echo closeform();
    }

    /**
     *  Display Buttons for Form
     *
     * @param      $unique_id
     * @param bool $breaker
     */
    private function displayFormButtons($unique_id, $breaker = true) {
        ?>
        <div class="m-t-20">
          <?php echo form_button("preview_submission", $this->locale['preview'], $this->locale['preview'], array("class" => "btn-default", "icon" => "fa fa-fw fa-eye", "input-id" => "preview_submission-".$unique_id."")); ?>
          <?php echo form_button("publish_submission", $this->locale['publish'], $this->locale['publish'], array("class" => "btn-success m-r-10", "icon" => "fa fa-fw fa-hdd-o", "input-id" => "publish_submission-".$unique_id."")); ?>
          <?php echo form_button("delete_submission", $this->locale['delete'], $this->locale['delete'], array("class" => "btn-danger", "icon" => "fa fa-fw fa-trash", "input-id" => "delete_submission-".$unique_id."")); ?>
        </div>
        <?php if ($breaker) { ?><hr /><?php } ?>
        <?php
    }

    private static function submitData(array $filters = array()) {

        $query = "SELECT s.*".(!empty($filters['criteria']) ? $filters['criteria'] : "")."
                FROM ".DB_SUBMISSIONS." s
                ".(!empty($filters['join']) ? $filters['join'] : "")."
                WHERE ".(!empty($filters['where']) ? $filters['where'] : "")."
                ORDER BY s.submit_datestamp DESC
                ";

        $result = dbquery($query, $filters['wheredata']);

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $info[] = $data;
            }
        return $info;
        }
        return FALSE;
    }
    /**
     * Display List with Submissions
     */
    private function displaySubmissionList() {

        $criteria = array(
            'criteria' => ", u.user_id, u.user_name, u.user_status, u.user_avatar",
            'join' => "LEFT JOIN ".DB_USERS." AS u ON u.user_id=s.submit_user",
            'where' => 's.submit_type=:submit_type',
            'wheredata' => array (
                ':submit_type' => 'q'
                )

        );
        $data = self::submitData($criteria);

        echo "<div class='table-responsive'><table class='table table-striped'>\n";
        echo "<thead>\n";
            echo "<tr>\n";
                echo "<td class='strong'>".$this->locale['faq_0200']."</td>\n";
        echo "<td class='strong col-xs-5'>".$this->locale['faq_0100']."</td>\n";
                echo "<td class='strong'>".$this->locale['faq_0202']."</td>\n";
                echo "<td class='strong'>".$this->locale['faq_0203']."</td>\n";
                echo "<td class='strong'>".$this->locale['faq_0204']."</td>\n";
            echo "</tr>\n";
        echo "</thead>\n";
        echo "<tbody>\n";

        if (!empty($data)) {

            foreach($data as $key => $info){
                $submitData = unserialize($info['submit_criteria']);
                $submitUser = $this->locale['user_na'];
                if ($info['user_name']) {
                    $submitUser = display_avatar($info, '20px', '', TRUE, 'img-rounded m-r-5');
                    $submitUser .= profile_link($info['user_id'], $info['user_name'], $info['user_status']);
                }

                $reviewLink = clean_request("section=submissions&submit_id=".$info['submit_id'], array("section", "ref", "action", "submit_id"), false);

                echo "<tr>\n";
                    echo "<td>".$info['submit_id']."</td>\n";
                    echo "<td><span class='text-dark'>".$submitData['faq_question']."</span></td>\n";
                    echo "<td>".$submitUser."</td>\n";
                    echo "<td>".timer($info['submit_datestamp'])."</td>\n";
                    echo "<td>\n";
                    echo "<a href=".$reviewLink." title=".$this->locale['faq_0205']." class='btn btn-default btn-sm'><i class='fa fa-fw fa-eye'></i> ".$this->locale['faq_0205']."</a\n";
                    echo "</td>\n";
                echo "</tr>\n";

            }
        } else {
            echo "<tr>\n";
            echo "<td colspan='5' class='text-center'>".$this->locale['faq_0063']."</td>\n";
            echo "</tr>\n";
        }

        echo "</tbody>\n";
        echo "</table>\n</div>";
    }
    /**
     * Display Admin Area
    */
    public function displayFaqAdmin() {
        pageAccess("FQ");

        $this->locale = self::get_faqAdminLocale();
        //$this->articleSettings = self::get_article_settings();

        // Handle a Submission
        if (isset($_GET['submit_id']) && isNum($_GET['submit_id']) && dbcount("(submit_id)", DB_SUBMISSIONS, "submit_id='".$_GET['submit_id']."' AND submit_type='q'")) {
            //$this->inputArray = self::unserializeData();
        $criteria = array(
            'criteria' => ", u.user_id, u.user_name, u.user_status, u.user_avatar",
            'join' => "LEFT JOIN ".DB_USERS." AS u ON u.user_id=s.submit_user",
            'where' => 's.submit_type=:submit_type AND s.submit_id=:submit_id',
            'wheredata' => array (
                ':submit_id' => $_GET['submit_id'],
                ':submit_type' => 'q'
                )
        );
        $data = self::submitData($criteria);
        $data[0] += self::$defArray;
        $data[0] += unserialize($data[0]['submit_criteria']);
        $this->inputArray = $data[0];
            // Delete, Publish, Preview

            self::handleDeleteSubmission();
            self::handlePostSubmission();

            // Display Form with Buttons
            self::displayForm();

        // Display List
        } else {
            self::displaySubmissionList();
        }
    }

    private function handleDeleteSubmission() {
        if (isset($_POST['delete_submission'])) {
            dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."' AND submit_type='q'");
            addNotice("success", $this->locale['faq_0062']);
            redirect(clean_request("", array("submit_id"), FALSE));
        }
    }

}
