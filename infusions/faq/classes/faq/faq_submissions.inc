<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/classes/faq/faq_submissions.php
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

class FaqSubmissions extends FaqServer {

    public $info = array();
    private $locale = array();
    private static $instance = NULL;

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayFaq() {

        $this->locale = fusion_get_locale("", FAQ_ADMIN_LOCALE);
        add_to_title($this->locale['faq_0900']);
        opentable("<i class='fa fa-globe fa-lg m-r-10'></i>".$this->locale['faq_0900']);
        if (self::$faq_settings['faq_allow_submission']) {
            if (iMEMBER) {
                $this->display_submission_form();
            } else {
                echo "<div class='well text-center'>".$this->locale['faq_0921']."</div>\n";
            }
        } else {
            echo "<div class='well text-center'>".$this->locale['faq_0922']."</div>\n";
        }
        closetable();
    }

    private function display_submission_form() {
        $criteriaArray = array(
            'faq_id'       => 0,
            'faq_cat_id'   => 0,
            'faq_answer'   => "",
            'faq_question' => "",
            'faq_language' => LANGUAGE,
        );
        // Cancel Form
        if (isset($_POST['cancel'])) {
            redirect(FUSION_REQUEST);
        }
        if (dbcount("(faq_id)", DB_FAQS, (multilang_table("FQ") ? "faq_language='".LANGUAGE."' AND " : "")."faq_status=1 AND ".groupaccess("faq_visibility")."")) {
            // Save
            if (isset($_POST['submit_link'])) {
                $submit_info['faq_question'] = parse_textarea($_POST['faq_question']);
                $criteriaArray = array(
                    'faq_cat_id'   => form_sanitizer($_POST['faq_cat_id'], 0, 'faq_cat_id'),
                    'faq_question' => form_sanitizer($submit_info['faq_question'], '', 'faq_question'),
                    'faq_answer'   => form_sanitizer($_POST['faq_answer'], '', 'faq_answer'),
                    'faq_language' => form_sanitizer($_POST['faq_language'], LANGUAGE, 'faq_language'),
                );
                // Save
                if (\defender::safe() && isset($_POST['submit_link'])) {
                    $inputArray = array(
                        'submit_type'      => 'q',
                        'submit_user'      => fusion_get_userdata('user_id'),
                        'submit_datestamp' => time(),
                        'submit_criteria'  => addslashes(serialize($criteriaArray))
                    );
                    dbquery_insert(DB_SUBMISSIONS, $inputArray, 'save');
                    addNotice('success', $this->locale['faq_0910']);
                    redirect(clean_request('submitted=q', array('stype'), TRUE));
                }
            }

            if (isset($_GET['submitted']) && $_GET['submitted'] == "q") {
                echo "<div class='well text-center text-strong'><p>".$this->locale['faq_0911']."</p>";
                echo "<p><a href='".BASEDIR."submit.php?stype=q' title=".$this->locale['faq_0912'].">".$this->locale['faq_0912']."</a></p>";
                echo "<p><a href='".BASEDIR."index.php'>".str_replace("[SITENAME]", fusion_get_settings("sitename"), $this->locale['faq_0913'])."</a></p>\n";
                echo "</div>\n";
            } else {
                echo "<div class='alert alert-info m-b-20 submission-guidelines text-center'>".str_replace("[SITENAME]", fusion_get_settings("sitename"), $this->locale['faq_0920'])."</div>\n";
                echo openform('submit_form', 'post', BASEDIR."submit.php?stype=q");
                echo form_text('faq_question', $this->locale['faq_0250'], $criteriaArray['faq_question'], array(
                    "error_text" => $this->locale['faq_0271'],
                    'required'   => TRUE
                ));
                $textArea_opts = array(
                    'required'  => TRUE,
                    'type'      => fusion_get_settings('tinymce_enabled') ? 'tinymce' : 'html',
                    'tinymce'   => fusion_get_settings('tinymce_enabled') && iADMIN ? 'advanced' : 'simple',
                    'autosize'  => TRUE,
                    'form_name' => 'submit_form',
                );
                echo form_textarea('faq_answer', $this->locale['faq_0251'], $criteriaArray['faq_answer'], $textArea_opts);
                $options = [];
                $faq_result = dbquery("SELECT faq_cat_id, faq_cat_name FROM ".DB_FAQ_CATS." ORDER BY faq_cat_name ASC");
                if (dbrows($faq_result)) {
                    $options[0] = $this->locale['faq_0010'];
                    while ($faq_data = dbarray($faq_result)) {
                        $options[$faq_data['faq_cat_id']] = $faq_data['faq_cat_name'];
                    }
                }
                echo form_select('faq_cat_id', $this->locale['faq_0252'], $faq_data['faq_cat_id'], array(
                    'inner_width' => '100%',
                    'inline'      => TRUE,
                    'options'     => $options,
                ));

                if (multilang_table('FQ')) {
                    echo form_select('faq_language', $this->locale['language'], $criteriaArray['faq_language'], array(
                        'options' => fusion_get_enabled_languages(), 'placeholder' => $this->locale['choose'], 'inner_width' => '100%',
                    ));
                } else {
                    echo form_hidden('faq_language', '', $criteriaArray['faq_language']);
                }
                echo form_button('cancel_link', $this->locale['cancel'], $this->locale['cancel'], array('class' => 'btn-default', 'icon' => 'fa fa-fw fa-times'));
                echo form_button('submit_link', $this->locale['save'], $this->locale['save'], array('class' => 'btn-success', 'icon' => 'fa fa-fw fa-hdd-o'));
                echo closeform();
            }
        } else {
            echo "<div class='well text-center'><p>".$this->locale['faq_0923']."</p></div>\n";
        }
    }
}