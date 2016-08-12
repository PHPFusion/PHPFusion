<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: PageComposer.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\Page\Composer;

use PHPFusion\Page\Composer\Network\ComposeContent;
use PHPFusion\Page\Composer\Network\ComposeEngine;
use PHPFusion\Page\Composer\Network\ComposeSettings;
use PHPFusion\Page\PageAdmin;

class PageComposer extends PageAdmin {

    private static $composerMode = 'pg_content';
    private static $allowed_composer_mode = array('pg_content', 'pg_settings', 'pg_composer');

    /**
     * Display Composer need to echo
     */
    public static function displayContent() {

        self::$composerMode = isset($_GET['composer_tab']) && in_array($_GET['composer_tab'],
                                                                       self::$allowed_composer_mode) ? $_GET['composer_tab'] : self::$allowed_composer_mode[0];
        if (!empty(self::$composerMode)) {
            self::set_Page();
        }

        $textArea_config = array(
            'width' => '100%',
            'height' => '260px',
            'form_name' => 'inputform',
            'type' => "html",
            'class' => 'm-t-20',
        );
        if ((isset($_COOKIE['custom_pages_tinymce']) && $_COOKIE['custom_pages_tinymce'] == 1) || fusion_get_settings('tinymce_enabled')) {
            $textArea_config = array(
                "type" => "tinymce",
                "tinymce" => "advanced",
                "class" => "m-t-20",
                "height" => "400px",
            );
        }

        echo openform('inputform', 'post', FUSION_REQUEST, array("class" => "m-t-20"));
        echo form_hidden('page_id', '', self::$data['page_id']);
        // Too much clutter on the middle, there is not enough room to see the design later.
        // Really need 2-3 tabs to control these things, start with basic ones first.
        // have a page description so admin knows what to do with it.

        $composerTab['title'][] = 'Page Content';
        $composerTab['id'][] = 'pg_content';
        if (self::$data['page_id']) { // only available when save
            $composerTab['title'][] = 'Page Composer';
            $composerTab['id'][] = 'pg_composer';

            $composerTab['title'][] = 'Page Settings';
            $composerTab['id'][] = 'pg_settings';
        }

        $currentComposerTab = isset($_GET['composer_tab']) && in_array($_GET['composer_tab'],
                                                                       $composerTab['id']) ? $_GET['composer_tab'] : $composerTab['id'][0];
        self::$composerMode = $currentComposerTab;

        echo opentab($composerTab, self::$composerMode, 'composer_tab', TRUE, 'm-t-10', 'composer_tab');

        echo "<div class='m-t-10'>";
        echo form_button('save', self::$locale['430'], self::$locale['430'], array('class' => 'btn-primary m-r-10'));
        if (isset($_POST['edit'])) {
            echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel'],
                             array('class' => 'btn-default m-r-10'));
        }
        echo form_button('save_and_close', 'Save and Close', 'Save and Close', array('class' => 'btn-success m-r-10'));
        echo "</div>\n";
        echo "<hr/>";

        switch ($currentComposerTab) {
            case 'pg_settings':
                ComposeSettings::displayContent();
                break;
            case 'pg_composer':
                ComposeEngine::displayContent();
                break;
            default:
                ComposeContent::displayContent();
        }
        echo closetab();
        echo closeform();
    }

    /**
     * SQL update or save data
     */
    protected static function set_Page() {

        if (isset($_POST['save']) or isset($_POST['save_and_close'])) {

            // We must identify which page is saving by using get
            // pg_content, pg_composer, pg_settings
            switch (self::$composerMode) {
                case 'pg_composer':
                    break;
                case 'pg_settings':
                    self::$data = array(
                        'page_id' => form_sanitizer($_POST['page_id'], 0, 'page_id'),
                        'page_header_panel' => !empty($_POST['page_header_panel']) ? 1 : 0,
                        'page_footer_panel' => !empty($_POST['page_footer_panel']) ? 1 : 0,
                        'page_left_panel' => !empty($_POST['page_left_panel']) ? 1 : 0,
                        'page_right_panel' => !empty($_POST['page_right_panel']) ? 1 : 0,
                        'page_top_panel' => !empty($_POST['page_top_panel']) ? 1 : 0,
                        'page_bottom_panel' => !empty($_POST['page_bottom_panel']) ? 1 : 0,
                        'page_link_cat' => self::$data['page_link_cat'],
                        'page_title' => self::$data['page_title'],
                        'page_access' => self::$data['page_access'],
                    );
                    break;
                case 'pg_content';
                    self::$data = array(
                        'page_id' => form_sanitizer($_POST['page_id'], 0, 'page_id'),
                        'page_cat' => form_sanitizer($_POST['page_cat'], 0, 'page_cat'),
                        'page_title' => form_sanitizer($_POST['page_title'], '', 'page_title'),
                        'page_access' => form_sanitizer($_POST['page_access'], 0, 'page_access'),
                        'page_content' => addslash($_POST['page_content']),
                        'page_keywords' => form_sanitizer($_POST['page_keywords'], '', 'page_keywords'),
                        'page_status' => form_sanitizer($_POST['page_status'], '', 'page_status'),
                        'page_datestamp' => form_sanitizer($_POST['page_datestamp'], '', 'page_datestamp'),
                        'page_language' => isset($_POST['page_language']) ? form_sanitizer($_POST['page_language'], "",
                                                                                           "page_language") : LANGUAGE,
                    );
                    break;

            }
            // Debug process
            //\defender::stop();
            //print_p($_POST);
            //print_p(self::$data, 1);

            if (\defender::safe()) {
                if (self::verify_customPage(self::$data['page_id'])) {
                    dbquery_insert(DB_CUSTOM_PAGES, self::$data, 'update');
                    addNotice('success', self::$locale['411']);
                } else {
                    dbquery_insert(DB_CUSTOM_PAGES, self::$data, 'save');
                    self::$data['page_id'] = dblastid();
                    if (!empty($data['add_link'])) {
                        self::set_customPageLinks(self::$data);
                    }
                    addNotice('success', self::$locale['410']);
                }

                if (isset($_POST['save'])) {
                    redirect(clean_request('action=edit&cpid='.self::$data['page_id'],
                                           array('section', 'composer_tab', 'aid'), TRUE));
                } elseif (isset($_POST['save_and_close'])) {
                    redirect(FUSION_SELF.fusion_get_aidlink()."&amp;pid=".self::$data['page_id']);
                }
            }


        }
    }

}