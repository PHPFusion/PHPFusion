<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Admins.inc
| Author: Frederick MC Chan
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;

/**
 * Class Admin
 * This class is called in templates/admin_header.php
 * Determine how to we set variables on 3rd party script
 *
 */
class Admins {

    private static $instance = NULL;
    /**
     * @var array - default section icons
     */
    public $admin_section_icons = array(
        '0' => "<i class='fa fa-fw fa-dashboard'></i>",
        '1' => "<i class='fa fa-fw fa-microphone'></i>",
        '2' => "<i class='fa fa-fw fa-users'></i>",
        '3' => "<i class='fa fa-fw fa-cog'></i>",
        '4' => "<i class='fa fa-fw fa-wrench'></i>",
        '5' => "<i class='fa fa-fw fa-cubes'></i>"
    );
    /**
     * Default core administration pages
     *
     * @var array
     */
    public $admin_page_icons = array(
        'CP'   => "<i class='admin-ico fa fa-fw fa-leaf'></i>", // custom page
        'IM'   => "<i class='admin-ico fa fa-fw fa-picture-o'></i>", // Images
        'PM'   => "<i class='admin-ico fa fa-fw fa-envelope-o'></i>", // private message
        'APWR' => "<i class='admin-ico fa fa-fw fa-medkit'></i>", // Admin Password Reset
        'AD'   => "<i class='admin-ico fa fa-fw fa-user-md'></i>", // Administrator
        'B'    => "<i class='admin-ico fa fa-fw fa-ban'></i>", // Blacklist
        'M'    => "<i class='admin-ico fa fa-fw fa-user'></i>", // Members
        'MI'   => "<i class='admin-ico fa fa-fw fa-barcode'></i>", // Migration tool
        'SU'   => "<i class='admin-ico fa fa-fw fa-file-o'></i>", // User Submissions
        'UF'   => "<i class='admin-ico fa fa-fw fa-table'></i>", // User Fields
        'UG'   => "<i class='admin-ico fa fa-fw fa-users'></i>", // user groups
        'UL'   => "<i class='admin-ico fa fa-fw fa-coffee'></i>", // user logs
        'SB'   => "<i class='admin-ico fa fa-fw fa-language'></i>", // Banners
        'BB'   => "<i class='admin-ico fa fa-fw fa-bold'></i>", // Bbcode
        'DB'   => "<i class='admin-ico fa fa-fw fa-history'></i>", // database backup
        'MAIL' => "<i class='admin-ico fa fa-fw fa-send'></i>", // Email templates
        'ERRO' => "<i class='admin-ico fa fa-fw fa-bug'></i>", // Error Logs
        'I'    => "<i class='admin-ico fa fa-fw fa-cubes'></i>", // Infusions
        'P'    => "<i class='admin-ico fa fa-fw fa-desktop'></i>", // Panels
        'PL'   => "<i class='admin-ico fa fa-fw fa-puzzle-piece'></i>", // Permalink
        'PI'   => "<i class='admin-ico fa fa-fw fa-info-circle'></i>", // php Info
        'ROB'  => "<i class='admin-ico fa fa-fw fa-android'></i>", // robots.txt
        'SL'   => "<i class='admin-ico fa fa-fw fa-link'></i>", // Site Links
        'SM'   => "<i class='admin-ico fa fa-fw fa-smile-o'></i>", // Smileys
        'TS'   => "<i class='admin-ico fa fa-fw fa-magic'></i>", // Theme
        'U'    => "<i class='admin-ico fa fa-fw fa-database'></i>", // Upgrade
        'LANG' => "<i class='admin-ico fa fa-fw fa-flag'></i>", // Language Settings
        'S1'   => "<i class='admin-ico fa fa-fw fa-hospital-o'></i>", // Main Settings
        'S2'   => "<i class='admin-ico fa fa-fw fa-clock-o'></i>", // Time and Date
        'S3'   => "<i class='admin-ico fa fa-fw fa-magic'></i>", // Theme Settings
        'S4'   => "<i class='admin-ico fa fa-fw fa-key'></i>", // Registration Settings
        'S6'   => "<i class='admin-ico fa fa-fw fa-gears'></i>", // Miscellaneous Settings
        'S7'   => "<i class='admin-ico fa fa-fw fa-envelope-square'></i>", // PM Settings
        'S9'   => "<i class='admin-ico fa fa-fw fa-users'></i>", // User Management
        'S10'  => "<i class='admin-ico fa fa-fw fa-arrow-circle-up'></i>", // Items Per Page
        'S12'  => "<i class='admin-ico fa fa-fw fa-shield'></i>", // Security Settings
    );
    /**
     * @var array
     */
    private $admin_sections = array(1 => FALSE, 2 => FALSE, 3 => FALSE, 4 => FALSE, 5 => FALSE);
    private static $admin_pages = array();
    /**
     * @var array
     */
    private $admin_page_link = array();
    /**
     *    Constructor class. No Params
     */
    private $current_page = '';
    private static $locale = '';
    private $comment_type = array();
    private $submit_type = array();
    private $submit_link = array();
    private $link_type = array();
    private $submit_data = [];

    private function __construct() {}

    /**
     * @return object
     */
    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
            self::$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/main.php');
        }

        return (object)self::$instance;
    }

    public function setAdmin() {
        if (!defined('IN_SETUP')) {
            self::$admin_pages = $this->getAdminPages();
            $this->admin_sections = array(
                0 => self::$locale['ac00'],
                1 => self::$locale['ac01'],
                2 => self::$locale['ac02'],
                3 => self::$locale['ac03'],
                4 => self::$locale['ac04'],
                5 => self::$locale['ac05'],
            );
            $this->current_page = $this->_currentPage();
        }
    }

    /**
     * @return array
     */
    public function getAdminPages() {
        if (empty(self::$admin_pages)) {
            $result = dbquery("SELECT admin_title, admin_page, admin_rights, admin_link FROM ".DB_ADMIN." ORDER BY admin_page DESC, admin_title ASC");
            $rows = dbrows($result);
            if ($rows) {
                while ($data = dbarray($result)) {
                    if ($data['admin_link'] != "reserved" && checkrights($data['admin_rights'])) {
                        $data['admin_title'] = preg_replace("/&(?!(#\d+|\w+);)/", "&amp;", $data['admin_title']);
                        self::$admin_pages[$data['admin_page']][] = $data;
                    }
                }
            }
        }

        return self::$admin_pages;
    }

    /**
     * Build a return that always synchronize with the DB_ADMIN url.
     */
    public function _currentPage() {
        $path = $_SERVER['PHP_SELF'];
        if (defined('START_PAGE')) {
            $path_info = pathinfo(START_PAGE);
            if (stristr(FUSION_REQUEST, '/administration/')) {
                $path = $path_info['filename'].'.php';
            } else {
                $path = '../'.$path_info['dirname'].'/'.$path_info['filename'].'.php';
            }
        }
        return $path;
    }

    /**
     * @param $page          - 0-5 is core section pages. 6 and above are free to use.
     * @param $section_title - Section title
     * @param $icons         - Section Icons
     */
    public function addAdminSection($page, $section_title, $icons) {
        $this->admin_sections[$page] = $section_title;
        $this->admin_section_icons[$page] = $icons;
        self::$admin_pages[$page] = array();
    }

    public function getAdminLocale() {
        if (empty(self::$locale)) {
            self::$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/main.php');
        }
    }

    public function setAdminBreadcrumbs() {
        BreadCrumbs::getInstance()->addBreadCrumb([
            'link'  => ADMIN.'index.php'.fusion_get_aidlink().'&amp;pagenum=0',
            'title' => self::$locale['ac10']
        ]);
        $acTab = (isset($_GET['pagenum']) && isnum($_GET['pagenum'])) ? $_GET['pagenum'] : $this->_isActive();
        if ($acTab != 0 && $acTab <= 5) {
            BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => ADMIN.fusion_get_aidlink()."&amp;pagenum=".$acTab,
                'title' => self::$locale['ac0'.$acTab]
            ]);
        }

    }

    /**
     * Determine which section is currently active.
     *
     * @return int|string
     */
    public function _isActive() {
        $active_key = 0;
        self::$admin_pages = $this->getAdminPages();
        if (empty($active_key) && !empty(self::$admin_pages)) {
            foreach (self::$admin_pages as $key => $data) {
                $link = array();
                foreach ($data as $arr => $admin_data) {
                    $link[] = $admin_data['admin_link'];
                }
                $data_link = array_flip($link);
                if (isset($data_link[$this->_currentPage()])) {
                    return $key;
                }
            }
        }

        return '0';
    }

    /**
     * @return array
     */
    public function getAdminPageIcons() {
        return $this->admin_page_icons;
    }

    /**
     * @param $rights
     * @param $icons
     */
    public function setAdminPageIcons($rights, $icons) {
        $this->admin_page_icons[$rights] = $icons;
    }

    public function getLinkType($type = NULL) {
        return ($type !== NULL ? (isset($this->link_type[$type]) ? $this->link_type[$type] : NULL) : $this->link_type);
    }

    /**
     * @param $type - link prefix
     * @param $link - link url
     */
    public function setLinkType($type, $link) {
        $this->link_type[$type] = $link;
    }

    /**
     * Get Submit Type
     * @param null $type submit stype prefix
     *
     * @return array|mixed|null
     */
    public function getSubmitType($type = NULL) {
        return ($type !== NULL ? (isset($this->submit_type[$type]) ? $this->submit_type[$type] : NULL) : $this->submit_type);
    }
    /**
     * @param $type  - submissions prefix
     * @param $title - title
     */
    public function setSubmitType($type, $title) {
        $this->submit_type[$type] = $title;
    }

    /**
     * @param $type  - submissions prefix
     * @param $options - array(infusion_name, link, submit_link, submit_locale, title,admin_link)
     */
    public function setSubmitData($type, array $options = array()) {
        infusion_exists($options['infusion_name']) ? $this->submit_data[$type] = $options : NULL;
    }

    public function getSubmitData($type = NULL) {
        return ($type !== NULL ? (isset($this->submit_data[$type]) ? $this->submit_data[$type] : NULL) : $this->submit_data);
    }
    /**
     * @param $link     Admin submission url
     * @param $type     Submissions stype prefix
     */
    public function setSubmitLink($type, $link) {
        $this->submit_link[$type] = $link;
    }

    public function getSubmitLink($type = NULL) {
        return ($type !== NULL ? (isset($this->submit_link[$type]) ? $this->submit_link[$type] : NULL) : $this->submit_link);
    }

    public function getCommentType($type = NULL) {
        return ($type !== NULL ? (isset($this->comment_type[$type]) ? $this->comment_type[$type] : NULL) : $this->comment_type);
    }

    /**
     * @param $type  - comment prefix
     * @param $title - title
     */
    public function setCommentType($type, $title) {
        $this->comment_type[$type] = $title;
    }

    /**
     * @return array
     */
    public function getAdminPageLink() {
        return $this->admin_page_link;
    }

    /**
     * @return mixed
     */
    public function getCurrentPage() {
        return $this->current_page;
    }

    /**
     * Displays vertical collapsible administration navigation
     *
     * @param bool|FALSE $image_icon
     *
     * @return string
     */
    public function vertical_admin_nav($image_icon = FALSE) {

        $aidlink = fusion_get_aidlink();
        $admin_sections = self::getAdminSections();
        $admin_pages = self::getAdminPages();

        $html = "<ul id='adl' class='admin-vertical-link'>\n";

        foreach ($admin_sections as $i => $section_name) {

            $active = ((isset($_GET['pagenum']) && $_GET['pagenum'] == $i) || (!isset($_GET['pagenum']) && $this->_isActive() == $i)) ? TRUE : FALSE;

            $html .= "<li class='".($active ? 'active panel' : 'panel')."' >\n";

            if (!empty($admin_pages[$i]) && is_array($admin_pages[$i])) {
                add_to_jquery('$("[data-toggle=collapse]").click(function(){$(this).find(".adl-drop i").toggleClass("fa-angle-left fa-angle-down");});');

                $html .= "<a class='adl-link ".($active ? '' : 'collapsed')."' data-parent='#adl' data-toggle='collapse' href='#adl-$i'>".$this->get_admin_section_icons($i)." <span class='adl-section-name'>".$section_name.($i > 4 ? " (".count($admin_pages[$i]).")" : "")."</span> ".($i > 0 ? "<span class='adl-drop pull-right'><i class='fa fa-angle-".($active ? "left" : "down")."'></i></span>" : '')."</a>\n";
                $html .= "<div id='adl-$i' class='collapse ".($active ? 'in' : '')."'>\n";
                $html .= "<ul class='admin-submenu'>\n";

                foreach ($admin_pages[$i] as $key => $data) {

                    $title = $data['admin_title'];
                    if ($data['admin_page'] !== 5) {
                        $title = isset(self::$locale[$data['admin_rights']]) ? self::$locale[$data['admin_rights']] : $title;
                    }

                    $secondary_active = $data['admin_link'] == $this->_currentPage() ? "class='active'" : '';

                    $icons = ($image_icon === TRUE) ? "<img class='admin-image' src='".get_image("ac_".$data['admin_rights'])."' alt='$title'/>" : $this->get_admin_icons($data['admin_rights']);

                    $html .= checkrights($data['admin_rights']) ? "<li $secondary_active><a href='".ADMIN.$data['admin_link'].$aidlink."'>".$icons." <span class='adl-submenu-title'>".$title."</span></a></li>\n" : "";
                }

                $html .= "</ul>\n";
                $html .= "</div>\n";
            } else {
                $html .= "<a class='adl-link' href='".ADMIN."index.php".$aidlink."&amp;pagenum=0'>".$this->get_admin_section_icons($i)." <span class='adl-section-name'>".$section_name."</span> ".($i > 0 ? "<span class='adl-drop pull-right'></span>" : '')."</a>\n";
            }
            $html .= "</li>\n";
        }

        $html .= "</ul>\n";

        return $html;
    }

    /**
     * @return array
     */
    public function getAdminSections() {
        return $this->admin_sections;
    }

    /**
     * @param $page_number
     *
     * @return string
     */
    public function get_admin_section_icons($page_number) {
        if (!empty($this->admin_section_icons[$page_number]) && $this->admin_section_icons[$page_number]) {
            return $this->admin_section_icons[$page_number];
        }

        return FALSE;
    }

    /**
     * Replace admin page icons
     *
     * @param $page
     * @param $icons
     */
    public function setAdminSectionIcons($page, $icons) {
        if (isset($this->admin_section_icons[$page])) {
            $this->admin_section_icons[$page] = $icons;
        }
    }

    /**
     * Get the administration page icons
     *
     * @param $admin_rights
     *
     * @return bool
     */
    public function get_admin_icons($admin_rights) {
        // admin rights might not yield an icon & admin_icons override might not have the key.
        if (isset($this->admin_page_icons[$admin_rights]) && $this->admin_page_icons[$admin_rights]) {
            return $this->admin_page_icons[$admin_rights];
        }

        return FALSE;
    }

    /**
     * Displays horizontal administration navigation
     *
     * @param bool $icon_only
     *
     * @return string
     */
    public function horizontal_admin_nav($icon_only = FALSE) {
        $aidlink = fusion_get_aidlink();
        $html = "<ul class='admin-horizontal-link'>\n";
        foreach ($this->admin_sections as $i => $section_name) {
            $active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && $this->_isActive() == $i) ? 1 : 0;
            $admin_text = $icon_only == FALSE ? " ".$section_name : "";
            $html .= "<li ".($active ? "class='active'" : '')."><a title='".$section_name."' href='".ADMIN.$aidlink."&amp;pagenum=$i'>".$this->get_admin_section_icons($i).$admin_text."</a></li>\n";
        }
        $html .= "</ul>\n";

        return $html;
    }
}
