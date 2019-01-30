<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Page/PageAdmin.inc
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

use PHPFusion\BreadCrumbs;
use PHPFusion\Page\Composer\PageComposer;
use PHPFusion\Page\Composer\PageList;

/**
 * Class PageAdmin
 *
 * @package PHPFusion\Page
 */
class PageAdmin extends PageModel {
    protected static $page_instance = NULL;
    protected static $locale = [];
    protected static $textarea_options = [];
    protected static $is_editing = FALSE;
    private static $allowed_admin_pages = ['cp1', 'compose_frm'];
    private static $current_section = '';
    private static $current_status = '';
    private static $current_action = '';
    private static $current_pageId = 0;
    private static $composerMode = '';
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
        if (empty(self::$page_instance)) {
            self::$page_instance = new static;
            self::set_PageAdminConfig();
        }

        return (object)self::$page_instance;
    }

    /**
     * Init
     */
    public static function set_PageAdminConfig() {

        self::$current_section = isset($_GET['section']) && in_array($_GET['section'],
            self::$allowed_admin_pages) ? $_GET['section'] : self::$allowed_admin_pages[0];
        self::$current_status = isset($_GET['status']) && isnum($_GET['status']) ? $_GET['status'] : self::$current_status;
        self::$current_action = isset($_GET['action']) ? $_GET['action'] : self::$current_action;
        self::$current_pageId = isset($_GET['cpid']) && isnum($_GET['cpid']) ? intval($_GET['cpid']) : self::$current_pageId;
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

    public function display_page() {

        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        add_to_title(self::$locale['global_201'].self::$locale['page_0100']);
        BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'custom_pages.php'.fusion_get_aidlink(), 'title' => self::$locale['page_0100']]);
        $tree = dbquery_tree_full(DB_CUSTOM_PAGES, 'page_id', 'page_cat');
        $tree_index = tree_index($tree);
        make_page_breadcrumbs($tree_index, $tree, 'page_id', 'page_title', 'pref');

        self::$is_editing = (isset($_GET['action']) && $_GET['action'] == 'edit') ? 1 : 0;

        if (self::$current_section == "cp2") {
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'custom_pages.php'.fusion_get_aidlink(), 'title' => self::$is_editing ? self::$locale['page_0201'] : self::$locale['page_0200']]);
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

            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $title]);
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
                if (!empty(self::$current_pageId)) {
                    self::$data = self::load_customPage(self::$current_pageId);
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
                if (!empty(self::$current_pageId)) {
                    self::delete_customPage(self::$current_pageId);
                    dbquery("DELETE FROM ".DB_CUSTOM_PAGES_GRID." WHERE page_id=".self::$current_pageId);
                    dbquery("DELETE FROM ".DB_CUSTOM_PAGES_CONTENT." WHERE page_id=".self::$current_pageId);
                    addNotice('success', self::$locale['page_0400']);
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
        echo closetable();
    }

    /**
     * Get page administration mode
     *
     * @return string
     */
    public static function getComposerMode() {
        if (empty(self::$composerMode)) {
            self::$composerMode = isset($_GET['composer_tab']) && in_array($_GET['composer_tab'],
                self::$allowed_composer_mode) ? $_GET['composer_tab'] : 'pg_content';
        }
        return (string)self::$composerMode;
    }
}
