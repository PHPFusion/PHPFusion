<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: weblinks/classes/weblinks/weblinks_submissions.php
| Author: Core Development Team
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

class WeblinksSubmissions extends WeblinksServer {
    private static $instance = NULL;
    private static $weblink_settings = [];
    private $locale;
    public $info = [];

    protected function __construct() {
        $this->locale = fusion_get_locale("", WEBLINK_ADMIN_LOCALE);
        self::$weblink_settings = self::getWeblinkSettings();
    }

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayWeblinks() {
        add_to_title($this->locale['WLS_0900']);

        $this->info['weblink_tablename'] = $this->locale['WLS_0900'];

        if (iMEMBER && self::$weblink_settings['links_allow_submission'] && checkgroup(self::$weblink_settings['links_submission_access'])) {
            display_weblink_submissions($this->displaySubmissionForm());
        } else {
            $info['no_submissions'] = $this->locale['WLS_0922'];
            $info += $this->info;
            display_weblink_submissions($info);
        }
    }

    private function displaySubmissionForm() {
        $settings = fusion_get_settings();

        $criteriaArray = [
            'weblink_name'        => '',
            'weblink_cat'         => 0,
            'weblink_url'         => '',
            'weblink_description' => '',
            'weblink_language'    => LANGUAGE,
        ];

        if (dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, (multilang_table("WL") ? in_group('weblink_cat_language', LANGUAGE)." AND " : "")."weblink_cat_status=1 AND ".groupaccess("weblink_cat_visibility")."")) {

            // Save
            if (check_post('submit_link')) {

                $criteriaArray = [
                    'weblink_cat'         => sanitizer('weblink_cat', 0, 'weblink_cat'),
                    'weblink_name'        => sanitizer('weblink_name', '', 'weblink_name'),
                    'weblink_description' => sanitizer('weblink_description', '', 'weblink_description'),
                    'weblink_url'         => sanitizer('weblink_url', '', 'weblink_url'),
                    'weblink_language'    => sanitizer(['weblink_language'], LANGUAGE, 'weblink_language'),
                ];

                // Save
                if (fusion_safe()) {
                    $inputArray = [
                        'submit_type'      => 'l',
                        'submit_user'      => fusion_get_userdata('user_id'),
                        'submit_datestamp' => time(),
                        'submit_criteria'  => \Defender::encode($criteriaArray)
                    ];
                    dbquery_insert(DB_SUBMISSIONS, $inputArray, 'save');
                    addnotice('success', $this->locale['WLS_0910']);
                    redirect(clean_request('submitted=l', ['stype'], TRUE));
                }
            }

            if (check_get('submitted') && get('submitted') == "l") {
                $info['confirm'] = [
                    'title'       => $this->locale['WLS_0911'],
                    'submit_link' => "<a href='".BASEDIR."submit.php?stype=l'>".$this->locale['WLS_0912']."</a>",
                    'index_link'  => "<a href='".BASEDIR.$settings['opening_page']."'>".str_replace("[SITENAME]", $settings['sitename'], $this->locale['WLS_0913'])."</a>"
                ];
            } else {
                $info['item'] = [
                    'guidelines'          => str_replace("[SITENAME]", $settings['sitename'], $this->locale['WLS_0920']),
                    'openform'            => openform('submit_form', 'post', BASEDIR."submit.php?stype=l", ['enctype' => (bool)self::$weblink_settings['links_allow_submission']]),
                    'weblink_cat'         => form_select_tree('weblink_cat', $this->locale['WLS_0101'], $criteriaArray['weblink_cat'],
                        [
                            'no_root'     => TRUE,
                            'inline'      => TRUE,
                            'placeholder' => $this->locale['choose'],
                            'query'       => (multilang_table("WL") ? "WHERE ".in_group('weblink_cat_language', LANGUAGE) : "")
                        ], DB_WEBLINK_CATS, 'weblink_cat_name', 'weblink_cat_id', 'weblink_cat_parent'),
                    'weblink_name'        => form_text('weblink_name', $this->locale['WLS_0201'], $criteriaArray['weblink_name'],
                        [
                            'required'    => TRUE,
                            'inline'      => TRUE,
                            'placeholder' => $this->locale['WLS_0201'],
                            'error_text'  => $this->locale['WLS_0252']
                        ]),
                    'weblink_url'         => form_text('weblink_url', $this->locale['WLS_0253'], $criteriaArray['weblink_url'],
                        [
                            'required'    => TRUE,
                            'inline'      => TRUE,
                            'type'        => "url",
                            'placeholder' => "http://"
                        ]),
                    'weblink_language'    => (multilang_table('WL') ? form_select('weblink_language[]', $this->locale['language'], $criteriaArray['weblink_language'],
                        [
                            'options'     => fusion_get_enabled_languages(),
                            'placeholder' => $this->locale['choose'],
                            'width'       => '250px',
                            'inline'      => TRUE,
                            'multiple'    => TRUE
                        ]) : form_hidden('weblink_language', '', $criteriaArray['weblink_language'])),
                    'weblink_description' => form_textarea('weblink_description', $this->locale['WLS_0254'], $criteriaArray['weblink_description'],
                        [
                            'required'      => (bool)self::$weblink_settings['links_extended_required'],
                            'type'          => $settings['tinymce_enabled'] ? 'tinymce' : 'html',
                            'tinymce'       => $settings['tinymce_enabled'] && iADMIN ? 'advanced' : 'simple',
                            'tinymce_image' => FALSE,
                            'autosize'      => TRUE,
                            'form_name'     => 'submit_form',
                        ]),
                    'weblink_submit'      => form_button('submit_link', $this->locale['submit'], $this->locale['submit'], ['class' => 'btn-success', 'icon' => 'fa fa-fw fa-hdd-o'])

                ];

            }
            $info += $this->info;
            return $info;

        }
        $info['no_submissions'] = $this->locale['WLS_0923'];
        $info += $this->info;
        return $info;
    }
}
