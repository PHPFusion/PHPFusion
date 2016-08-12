<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
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
 * @package PHPFusion\Page
 */
class PageAdmin extends PageModel {

    protected static $page_instance = NULL;

    protected static $data = array(
        'page_id' => 0,
        'page_cat' => 0,
        'page_link_cat' => 0,
        'page_title' => '',
        'page_access' => iGUEST,
        'page_content' => '',
        'page_keywords' => '',
        'page_status' => 0,
        'page_user' => 0,
        'page_datestamp' => 0,
        'page_allow_comments' => 0,
        'page_allow_ratings' => 0,
        'page_language' => LANGUAGE,
        'link_id' => 0,
        'link_order' => 0,
    );

    protected static $locale = array();
    protected static $textarea_options = array();

    private static $allowed_admin_pages = array('cp1', 'compose_frm');

    private static $current_section = '';
    private static $current_status = '';
    private static $current_action = '';
    private static $current_pageId = 0;

    /**
     * Return page composer object
     * @return null|static
     */
    public static function getComposerAdminInstance() {
        if (empty(self::$page_instance)) {
            self::$page_instance = new Static;
            self::set_PageAdminInfo();
        }

        return (object)self::$page_instance;
    }

    public static function set_PageAdminInfo() {

        self::$current_section = isset($_GET['section']) && in_array($_GET['section'],
                                                                     self::$allowed_admin_pages) ? $_GET['section'] : self::$allowed_admin_pages[0];
        self::$current_status = isset($_GET['status']) && isnum($_GET['status']) ? $_GET['status'] : self::$current_status;
        self::$current_action = isset($_GET['action']) ? $_GET['action'] : self::$current_action;
        self::$current_pageId = isset($_GET['cpid']) && isnum($_GET['cpid']) ? intval($_GET['cpid']) : self::$current_pageId;
        $_POST['page_id'] = isset($_POST['page_id']) && isnum($_POST['page_id']) ? $_POST['page_id'] : 0;
        self::$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/sitelinks.php');
        self::$locale += fusion_get_locale('', LOCALE.LOCALESET.'admin/custom_pages.php');

        self::$textarea_options = array(
            'width' => '100%',
            'height' => '260px',
            'form_name' => 'inputform',
            'type' => "html",
            'class' => 'm-t-20',
        );

        if ((isset($_COOKIE['custom_pages_tinymce']) && $_COOKIE['custom_pages_tinymce'] == 1) || fusion_get_settings('tinymce_enabled')) {
            self::$textarea_options = array(
                "type" => "tinymce",
                "tinymce" => "advanced",
                "class" => "m-t-20",
                "height" => "400px",
            );
        }
    }

    public function display_page() {

        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        add_to_title(self::$locale['global_201'].self::$locale['403']);
        add_breadcrumb(array('link' => ADMIN.'custom_pages.php'.fusion_get_aidlink(), 'title' => self::$locale['403']));
        $tree = dbquery_tree_full(DB_CUSTOM_PAGES, 'page_id', 'page_cat');
        $tree_index = tree_index($tree);
        make_page_breadcrumbs($tree_index, $tree, 'page_id', 'page_title', 'pref');

        $edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? 1 : 0;
        if (self::$current_section == "cp2") {
            add_breadcrumb(array(
                               'link' => ADMIN.'custom_pages.php'.fusion_get_aidlink(),
                               'title' => $edit ? self::$locale['401'] : self::$locale['400']
                           ));
        }

        $tab_title['title'][] = self::$current_section == 'compose_frm' ? 'Back' : 'Current Pages';
        $tab_title['id'][] = 'cp1';
        $tab_title['icon'][] = '';

        if (self::$current_section == 'compose_frm') {
            $tab_title['title'][] = $edit ? 'Edit Page' : 'Create New Page';
            $tab_title['id'][] = 'compose_frm';
            $tab_title['icon'][] = '';
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
                    opentable(self::$locale['401']);
                } else {
                    redirect(FUSION_SELF.fusion_get_aidlink());
                }

                break;
            case 'delete':
                if (!empty(self::$current_pageId)) {
                    self::delete_customPage(self::$current_pageId);
                } else {
                    redirect(FUSION_SELF.fusion_get_aidlink());
                }
                break;
            default:
                opentable(self::$locale['403']);
        }

        echo opentab($tab_title, $tab_active, 'cpa', TRUE, '', 'section', array('action', 'cpid'));
        if (self::$current_section == "compose_frm") {
            PageComposer::displayContent();
        } else {
            PageList::displayContent();
        }
        echo closetab();
        echo closetable();
    }
}