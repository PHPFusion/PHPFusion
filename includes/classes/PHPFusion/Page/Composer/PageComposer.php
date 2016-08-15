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

/**
 * Class PageComposer - Framework
 * Binds Network files
 * @package PHPFusion\Page\Composer
 */
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
            self::validate_PageSQL();
        }

        echo openform('inputform', 'post', FUSION_REQUEST, array("class" => "m-t-20"));

        echo form_hidden('page_id', '', self::$data['page_id']);

        $composerTab['title'][] = 'Page Content';
        $composerTab['id'][] = 'pg_content';

        if (self::$data['page_id']) { // only available when page ID is present - i.e. Saved page
            $composerTab['title'][] = 'Page Composer';
            $composerTab['id'][] = 'pg_composer';
            $composerTab['title'][] = 'Page Attributes';
            $composerTab['id'][] = 'pg_settings';
        }

        echo opentab($composerTab, self::$composerMode, 'composer_tab', TRUE, 'm-t-10', 'composer_tab');

        echo "<div class='m-t-10'>";
        echo form_button('save', 'Save Page', 'Save Page', array('class' => 'btn-primary m-r-10'));
        if (isset($_POST['edit'])) {
            echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel'],
                             array('class' => 'btn-default m-r-10'));
        }
        echo form_button('save_and_close', 'Save and Close', 'Save and Close', array('class' => 'btn-success m-r-10'));
        echo "</div>\n";
        echo "<hr/>";

        switch (self::$composerMode) {
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
     * Composer Mode Required - pg_settings, pg_composer, pg_content
     */
    protected static function validate_PageSQL() {

        if (isset($_POST['save']) or isset($_POST['save_and_close'])) {

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
                    if (\defender::safe()) {
                        self::execute_PageSQL();
                        self::execute_PageRedirect();
                    }
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
                    if (\defender::safe()) {
                        self::execute_PageSQL();
                        self::compose_DefaultPage();
                        self::execute_PageRedirect();
                    }
                    break;
            }
        }
    }

    /**
     * Update/Insert and Redirect Sequence - Non Composer
     */
    private static function execute_PageSQL() {
        if (self::verify_customPage(self::$data['page_id'])) {
            dbquery_insert(DB_CUSTOM_PAGES, self::$data, 'update');
            addNotice('success', self::$locale['411']);
        } else {
            dbquery_insert(DB_CUSTOM_PAGES, self::$data, 'save');
            self::$data['page_id'] = dblastid();
            addNotice('success', self::$locale['410']);
        }
    }

    private static function execute_PageRedirect() {
        if (isset($_POST['save'])) {
            redirect(clean_request('action=edit&cpid='.self::$data['page_id'],
                                   array('section', 'composer_tab', 'aid'), TRUE));
        } elseif (isset($_POST['save_and_close'])) {
            redirect(FUSION_SELF.fusion_get_aidlink()."&amp;pid=".self::$data['page_id']);
        }
    }

    /**
     * Run sync between default and composer tables
     */
    private static function compose_DefaultPage() {

        if (!empty(self::$data['page_id']) && \defender::safe()) {

            if (!empty(self::$data['page_content_id']) && !empty(self::$data['page_grid_id'])) {
                // update content
                dbquery("UPDATE ".DB_CUSTOM_PAGES_CONTENT." SET page_content='".self::$data['page_content']."'
                WHERE page_content_id=".self::$data['page_content_id']);

            } else {

                // create new rows
                if (empty(self::$data['page_grid_id'])) {
                    $rowData = array(
                        'page_grid_id' => 0,
                        'page_id' => self::$data['page_id'],
                        'page_grid_column_count' => 1,
                        'page_grid_html_id' => '',
                        'page_grid_class' => '',
                        'page_grid_order' => 1,
                    );
                    dbquery_insert(DB_CUSTOM_PAGES_GRID, $rowData, 'save');
                    $rowData['page_grid_id'] = dblastid();
                }

                if (empty(self::$data['page_content_id'])) {
                    $colData = array(
                        'page_id' => self::$data['page_id'],
                        'page_grid_id' => $rowData['page_grid_id'],
                        'page_content_id' => 0,
                        'page_content_type' => 'content',
                        'page_content' => self::$data['page_content'],
                        'page_content_order' => 1,
                    );
                    dbquery_insert(DB_CUSTOM_PAGES_CONTENT, $colData, 'save');
                    $colData['page_content_id'] = dblastid();
                }

                // Now update the table.
                dbquery("UPDATE ".DB_CUSTOM_PAGES." SET
                        page_grid_id=".$rowData['page_grid_id'].",
                        page_content_id=".$colData['page_content_id']."
                        WHERE page_id=".self::$data['page_id']);

            }
        }

    }

}