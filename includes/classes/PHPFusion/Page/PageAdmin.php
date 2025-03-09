<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: PageAdmin.php
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
namespace PHPFusion\Page;

use PHPFusion\Page\Composer\PageComposer;
use PHPFusion\Page\Composer\PageList;

/**
 * Class PageAdmin
 *
 * @package PHPFusion\Page
 */
class PageAdmin extends PageModel {
    protected static $pageInstance = NULL;
    protected static $locale = [];
    protected static $textarea_options = [];
    protected static $is_editing = FALSE;
    private static $allowed_admin_pages = ['cp1', 'compose_frm'];
    private static $current_section = '';
    private static $current_status = '';
    private static $current_action = '';
    private static $current_page_id = 0;
    private static $composer_mode = '';
    private static $allowed_composer_mode = ['pg_content', 'pg_settings', 'pg_composer'];

    /**
     * Returns list of administration mode
     *
     * @return array
     */
    public static function getAllowedComposerMode() {
        return (array)self::$allowed_composer_mode;
    }

    /**
     * Return page composer object
     *
     * @return object
     */
    public static function getComposerAdminInstance() {
        if (empty(self::$pageInstance)) {
            self::$pageInstance = new static;
            self::setPageAdminConfig();
        }

        return (object)self::$pageInstance;
    }

    /**
     * Init
     */
    public static function setPageAdminConfig() {
        self::$current_section = isset($_GET['section']) && in_array($_GET['section'], self::$allowed_admin_pages) ? $_GET['section'] : self::$allowed_admin_pages[0];
        self::$current_status = isset($_GET['status']) && isnum($_GET['status']) ? $_GET['status'] : self::$current_status;
        self::$current_action = isset($_GET['action']) ? $_GET['action'] : self::$current_action;
        self::$current_page_id = isset($_GET['cpid']) && isnum($_GET['cpid']) ? intval($_GET['cpid']) : self::$current_page_id;
        $_POST['page_id'] = isset($_POST['page_id']) && isnum($_POST['page_id']) ? $_POST['page_id'] : 0;
        self::$locale = fusion_get_locale('', [LOCALE.LOCALESET.'admin/sitelinks.php', LOCALE.LOCALESET.'admin/custom_pages.php']);
        $request = clean_request('', ['aid', 'section', 'action', 'cpid'], TRUE);
        $request = str_replace('&amp;', '&', $request);
        add_to_jquery("
        function SetTinyMCE(val) {
            now=new Date();\n"."now.setTime(now.getTime()+1000*60*60*24*365);
            expire=(now.toGMTString());\n"."document.cookie=\"custom_pages_tinymce=\"+escape(val)+\";expires=\"+expire;
            location.href='".$request."';
        }
        $('#tinymce_switch').bind('click', function() {
            SetTinyMCE(".(!isset($_COOKIE['custom_pages_tinymce']) || $_COOKIE['custom_pages_tinymce'] == 0 ? 1 : 0).");
        });
        ");
        self::$textarea_options = [
            'width'     => '100%',
            'height'    => '260px',
            'form_name' => 'inputform',
            'type'      => "html",
            'class'     => 'm-t-20',
        ];

        if ((isset($_COOKIE['custom_pages_tinymce']) && $_COOKIE['custom_pages_tinymce'] == 1) || fusion_get_settings('tinymce_enabled')) {
            self::$textarea_options = [
                "type"    => "tinymce",
                "tinymce" => "advanced",
                "class"   => "m-t-20",
                "height"  => "400px",
            ];
        }
    }

    /**
     * Display page
     */
    public function displayPage() {

        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        add_to_title(self::$locale['global_201'].self::$locale['page_0100']);
        add_breadcrumb(['link' => ADMIN.'custom_pages.php'.fusion_get_aidlink(), 'title' => self::$locale['page_0100']]);
        $tree = dbquery_tree_full(DB_CUSTOM_PAGES, 'page_id', 'page_cat');
        $tree_index = tree_index($tree);
        make_page_breadcrumbs($tree_index, $tree, 'page_id', 'page_title', 'pref');

        self::$is_editing = (isset($_GET['action']) && $_GET['action'] == 'edit') ? 1 : 0;

        if (self::$current_section == "cp2") {
            add_breadcrumb(['link' => ADMIN.'custom_pages.php'.fusion_get_aidlink(), 'title' => self::$is_editing ? self::$locale['page_0201'] : self::$locale['page_0200']]);
        } else if (self::$current_section == 'compose_frm') {
            // there are 3 sections
            switch (self::getComposerMode()) {
                case 'pg_settings':
                    $title = self::$locale['page_0202'];
                    break;
                case 'pg_composer':
                    $title = self::$locale['page_0203'];
                    break;
                default:
                    $title = self::$locale['page_0204'];
            }

            add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $title]);
        }
        $tab_title['title'][] = self::$current_section == 'compose_frm' ? self::$locale['back'] : self::$locale['page_0205'];
        $tab_title['id'][] = 'cp1';
        $tab_title['icon'][] = self::$current_section == 'compose_frm' ? 'fa fa-arrow-left' : '';
        if (self::$current_section == 'compose_frm') {
            $tab_title['title'][] = self::$is_editing ? self::$locale['page_0201'] : self::$locale['page_0200'];
            $tab_title['id'][] = 'compose_frm';
            $tab_title['icon'][] = 'fa fa-pencil';
        }
        $tab_active = self::$current_section;
        switch (self::$current_action) {
            case 'edit':
                if (!empty(self::$current_page_id)) {
                    self::$data = self::loadCustomPage(self::$current_page_id);
                    if (empty(self::$data)) {
                        redirect(FUSION_SELF.fusion_get_aidlink());
                    }
                    fusion_confirm_exit();
                    opentable(self::$locale['page_0201']);
                } else {
                    redirect(FUSION_SELF.fusion_get_aidlink());
                }
                break;
            case 'delete':
                if (!empty(self::$current_page_id)) {
                    self::deleteCustomPage(self::$current_page_id);
                    dbquery("DELETE FROM ".DB_CUSTOM_PAGES_GRID." WHERE page_id=".self::$current_page_id);
                    dbquery("DELETE FROM ".DB_CUSTOM_PAGES_CONTENT." WHERE page_id=".self::$current_page_id);
                    addnotice('success', self::$locale['page_0400']);
                }
                redirect(FUSION_SELF.fusion_get_aidlink());
                break;
            default:
                opentable(self::$locale['page_0100']);
        }
        echo opentab($tab_title, $tab_active, 'cpa', TRUE, '', 'section', ['action', 'cpid']);
        if (self::$current_section == "compose_frm") {
            PageComposer::displayContent();
        } else {
            PageList::displayContent();
        }
        echo closetab();
        closetable();
    }

    /**
     * Get page administration mode
     *
     * @return string
     */
    public static function getComposerMode() {
        if (empty(self::$composer_mode)) {
            self::$composer_mode = isset($_GET['composer_tab']) && in_array($_GET['composer_tab'],
                self::$allowed_composer_mode) ? $_GET['composer_tab'] : 'pg_content';
        }
        return (string)self::$composer_mode;
    }
}
