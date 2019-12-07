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
    private static $instance = NULL;
    public $info = [];
    private $locale = [];

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayFaq() {
        $this->locale = fusion_get_locale("", FAQ_LOCALE);
        add_to_title($this->locale['faq_0900']);
        $this->info['faq_tablename'] = $this->locale['faq_0900'];
        if (iMEMBER && self::$faq_settings['faq_allow_submission']) {
            display_faq_submissions($this->display_submission_form());
        } else {
            $info['no_submissions'] = $this->locale['faq_0922'];
            $info += $this->info;
            display_faq_submissions($info);
        }
    }

    private function display_submission_form() {
        $criteriaArray = [
            'faq_id'       => 0,
            'faq_cat_id'   => 0,
            'faq_answer'   => "",
            'faq_question' => "",
            'faq_language' => LANGUAGE,
            'faq_status'   => 1
        ];

        if (dbcount("(faq_cat_id)", DB_FAQ_CATS, (multilang_table("FQ") ? in_group('faq_cat_language', LANGUAGE) : ""))) {
            // Save
            if (isset($_POST['submit_link'])) {
                $submit_info['faq_question'] = parse_textarea($_POST['faq_question']);
                $criteriaArray = [
                    'faq_cat_id'   => form_sanitizer($_POST['faq_cat_id'], 0, 'faq_cat_id'),
                    'faq_question' => form_sanitizer($submit_info['faq_question'], '', 'faq_question'),
                    'faq_answer'   => form_sanitizer($_POST['faq_answer'], '', 'faq_answer'),
                    'faq_language' => form_sanitizer($_POST['faq_language'], LANGUAGE, 'faq_language'),
                    'faq_status'   => 1
                ];
                // Save
                if (\defender::safe()) {
                    $inputArray = [
                        'submit_type'      => 'q',
                        'submit_user'      => fusion_get_userdata('user_id'),
                        'submit_datestamp' => TIME,
                        'submit_criteria'  => \defender::encode($criteriaArray)
                    ];
                    dbquery_insert(DB_SUBMISSIONS, $inputArray, 'save');
                    addNotice('success', $this->locale['faq_0910']);
                    redirect(clean_request('submitted=q', ['stype'], TRUE));
                }
            }

            if (isset($_GET['submitted']) && $_GET['submitted'] == "q") {
                $info['confirm'] = [
                    'title'       => $this->locale['faq_0911'],
                    'submit_link' => "<a href='".BASEDIR."submit.php?stype=q'>".$this->locale['faq_0912']."</a>",
                    'index_link'  => "<a href='".BASEDIR."index.php'>".str_replace("[SITENAME]", fusion_get_settings("sitename"), $this->locale['faq_0913'])."</a>"
                ];
                $info += $this->info;
                return (array)$info;
            } else {
                $options = [];
                $faq_data = [];
                $faq_result = dbquery("SELECT faq_cat_id, faq_cat_name FROM ".DB_FAQ_CATS.(multilang_table("FQ") ? " WHERE ".in_group('faq_cat_language', LANGUAGE) : "")." ORDER BY faq_cat_name ASC");
                if (dbrows($faq_result)) {
                    $options[0] = $this->locale['faq_0010'];
                    while ($faq_data = dbarray($faq_result)) {
                        $options[$faq_data['faq_cat_id']] = $faq_data['faq_cat_name'];
                    }
                }

                $info['item'] = [
                    'guidelines'     => str_replace("[SITENAME]", fusion_get_settings("sitename"), $this->locale['faq_0920']),
                    'openform'       => openform('submit_form', 'post', BASEDIR."submit.php?stype=q", ['enctype' => self::$faq_settings['faq_allow_submission'] ? TRUE : FALSE]),
                    'faq_question'   => form_text('faq_question', $this->locale['faq_0100'], $criteriaArray['faq_question'],
                        [
                            'error_text' => $this->locale['faq_0271'],
                            'required'   => TRUE
                        ]),
                    'faq_answer'     => form_textarea('faq_answer', $this->locale['faq_0251'], $criteriaArray['faq_answer'],
                        [
                            'required'  => TRUE,
                            'type'      => fusion_get_settings('tinymce_enabled') ? 'tinymce' : 'html',
                            'tinymce'   => fusion_get_settings('tinymce_enabled') && iADMIN ? 'advanced' : 'simple',
                            'autosize'  => TRUE,
                            'form_name' => 'submit_form'
                        ]),
                    'faq_cat_id'     => form_select('faq_cat_id', $this->locale['faq_0252'], $criteriaArray['faq_cat_id'],
                        [
                            'inner_width' => '250px',
                            'inline'      => TRUE,
                            'options'     => $options
                        ]),
                    'faq_language'   => (multilang_table('FQ') ? form_select('faq_language[]', $this->locale['language'], $criteriaArray['faq_language'],
                        [
                            'options'     => fusion_get_enabled_languages(),
                            'placeholder' => $this->locale['choose'],
                            'width'       => '250px',
                            'inline'      => TRUE,
                            'multiple'    => TRUE,
                            'delimeter'   => '.'
                        ]) : form_hidden('faq_language', '', $criteriaArray['faq_language'])),
                    'faq_submit'     => form_button('submit_link', $this->locale['submit'], $this->locale['submit'], ['class' => 'btn-success', 'icon' => 'fa fa-fw fa-hdd-o']),
                    'criteria_array' => $criteriaArray

                ];
                $info += $this->info;
                return (array)$info;
            }
        } else {
            $info['no_submissions'] = $this->locale['faq_0923'];
            $info += $this->info;
            return (array)$info;
        }
    }
}
