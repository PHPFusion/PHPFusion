<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Admins.php
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
 */
class Admins {

    private static $instance = NULL;
    private static $admin_pages = [];
    private static $locale = [];
    /**
     * @var array - default section icons
     */
    public $admin_section_icons = [
        '0' => "<i class='fa fa-fw fa-dashboard'></i>",
        '1' => "<i class='fa fa-fw fa-microphone'></i>",
        '2' => "<i class='fa fa-fw fa-users'></i>",
        '3' => "<i class='fa fa-fw fa-wrench'></i>",
        '4' => "<i class='fa fa-fw fa-cog'></i>",
        '5' => "<i class='fa fa-fw fa-cubes'></i>"
    ];
    /**
     * Default core administration pages
     *
     * @var array
     */
    public $admin_page_icons = [
        'AD'   => "<i class='admin-ico fa fa-fw fa-user-md'></i>", // Administrators
        'APWR' => "<i class='admin-ico fa fa-fw fa-medkit'></i>", // Admin Password Reset
        'B'    => "<i class='admin-ico fa fa-fw fa-ban'></i>", // Blacklist
        'BB'   => "<i class='admin-ico fa fa-fw fa-bold'></i>", // BB Codes
        'C'    => "<i class='admin-ico fa fa-fw fa-comments'></i>", // Comments
        'CP'   => "<i class='admin-ico fa fa-fw fa-leaf'></i>", // Custom Pages
        'DB'   => "<i class='admin-ico fa fa-fw fa-history'></i>", // Database Backup
        'ERRO' => "<i class='admin-ico fa fa-fw fa-bug'></i>", // Error Log
        'FM'   => "<i class='admin-ico fa fa-fw fa-folder-open'></i>", // Fusion File Manager
        'I'    => "<i class='admin-ico fa fa-fw fa-cubes'></i>", // Infusions
        'IM'   => "<i class='admin-ico fa fa-fw fa-picture-o'></i>", // Images
        'LANG' => "<i class='admin-ico fa fa-fw fa-flag'></i>", // Language Settings
        'M'    => "<i class='admin-ico fa fa-fw fa-user'></i>", // Members
        'MAIL' => "<i class='admin-ico fa fa-fw fa-send'></i>", // Email Templates
        'MI'   => "<i class='admin-ico fa fa-fw fa-barcode'></i>", // Migration Tool
        'P'    => "<i class='admin-ico fa fa-fw fa-desktop'></i>", // Panels
        'PI'   => "<i class='admin-ico fa fa-fw fa-info-circle'></i>", // Server Info
        'PL'   => "<i class='admin-ico fa fa-fw fa-puzzle-piece'></i>", // Permalinks
        'ROB'  => "<i class='admin-ico fa fa-fw fa-android'></i>", // robots.txt
        'S1'   => "<i class='admin-ico fa fa-fw fa-hospital-o'></i>", // Main Settings
        'S2'   => "<i class='admin-ico fa fa-fw fa-clock-o'></i>", // Time and Date
        'S4'   => "<i class='admin-ico fa fa-fw fa-key'></i>", // Registration Settings
        'S6'   => "<i class='admin-ico fa fa-fw fa-gears'></i>", // Miscellaneous Settings
        'S7'   => "<i class='admin-ico fa fa-fw fa-envelope-square'></i>", // PM Settings
        'S9'   => "<i class='admin-ico fa fa-fw fa-users'></i>", // User Management
        'S12'  => "<i class='admin-ico fa fa-fw fa-shield'></i>", // Security Settings
        'SB'   => "<i class='admin-ico fa fa-fw fa-language'></i>", // Banners
        'SL'   => "<i class='admin-ico fa fa-fw fa-link'></i>", // Site Links
        'SM'   => "<i class='admin-ico fa fa-fw fa-smile-o'></i>", // Smileys
        'TS'   => "<i class='admin-ico fa fa-fw fa-magic'></i>", // Theme Manager
        'U'    => "<i class='admin-ico fa fa-fw fa-database'></i>", // Upgrade
        'UF'   => "<i class='admin-ico fa fa-fw fa-table'></i>", // User Fields
        'UG'   => "<i class='admin-ico fa fa-fw fa-users'></i>", // User Groups
        'UL'   => "<i class='admin-ico fa fa-fw fa-coffee'></i>", // User Log
    ];
    /**
     * @var array
     */
    private $admin_sections = [1 => FALSE, 2 => FALSE, 3 => FALSE, 4 => FALSE, 5 => FALSE];
    /**
     * @var array
     */
    private $admin_page_link = [];
    /**
     *    Constructor class. No Params
     */
    private $current_page = '';
    private $comment_type = [];
    private $submit_type = [];
    private $submit_link = [];
    private $link_type = [];
    private $submit_data = [];
    private $folder_permissions = [];
    private $custom_folders = [];

    public function __construct() {
        self::$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/main.php');
    }

    /**
     * Add instance
     *
     * @return static
     */
    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Cache the Current Field Inputs within Login session.
     *
     * @param string $form_id
     * @param string $form_type
     * @param int    $item_id
     * @param array  $callback_fields
     * @param int    $cache_time
     *
     * @return string
     */
    public function requestCache($form_id, $form_type, $item_id, array $callback_fields = [], $cache_time = 30000) {
        add_to_jquery("
        function timedCacheRequest(timeout) {
            setTimeout(UpdateAdminCache, timeout);
        }
        function UpdateAdminCache(poll) {
            var input_fields = $('#$form_id').serialize();
            var ttl = '$cache_time';
            $.ajax({
            url: '".ADMIN."includes/?api=cache-update',
            type: 'post',
            dataType: 'html',
            data: {
                'fusion_token': '".fusion_get_token($form_id)."',
                'aidlink': '".fusion_get_aidlink()."',
                'fields': input_fields,
                'form_id' : '$form_id',
                'form_type':'$form_type',
                'item_id': '$item_id',
                'callback':'set_cache',
            },
            dataType: 'json',
            success: function (e) {
                console.log(e);
                console.log(poll);
                if (typeof poll === 'undefined') {
                    //console.log('we are starting a long poll now');
                    timedCacheRequest(ttl);
                }
            }
            });
        }
        // add abort long poll once we change fields.
        $('#".$form_id." :input').blur(function(e) {
            UpdateAdminCache(1);
        });
        ");
        if (!isset($_GET['autosave'])) {
            // not to pass prefix xhr for security reason.
            add_to_jquery("setTimeout( function(e) { UpdateAdminCache() }, $cache_time);");
        }

        $html = "";
        if (!empty($_SESSION['form_cache'][$form_id][$form_type][$item_id])) {

            $html .= "<div class='list-group text-normal m-t-10 m-b-10'><div class='list-group-item'>".self::$locale['290']." <a href='".clean_request('autosave=view', ['autosave'], FALSE)."'>".self::$locale['291']."</a></div></div>";

            if (isset($_GET['autosave']) && $_GET['autosave'] == 'view') {

                $html .= "<div id='rev-window'>\n";
                $html .= fusion_get_function('openside', "<h4><i class='fas fa-thumbtack m-r-10'></i>".self::$locale['292']."</h4>");
                $session = htmlspecialchars_decode($_SESSION['form_cache'][$form_id][$form_type][$item_id]);
                $session = str_replace('&#039;', "'", $session);
                parse_str($session, $data);
                unset($data['form_id']);
                unset($data['fusion_token']);

                $html .= "<dl id='restore_results' class='dl-horizontal'>\n";
                $fill_js = "";
                foreach ($data as $field_name => $value) {
                    $value = descript($value);
                    if (isset($callback_fields[$field_name])) {
                        $html .= "<dt>".$callback_fields[$field_name]."</dt>\n";
                        $html .= "<dd class='m-b-15'><samp>";
                        $html .= nl2br(html_entity_decode($value));
                        $html .= "</samp>".form_hidden('s_'.$field_name, '', str_replace("'", '&#039;', $value))."</dd>\n";
                        $fill_js .= "
                            var c = $('#s_$field_name').val();
                            $('#".$field_name."').val(c);
                        ";
                    }
                }
                $html .= "</dl>\n";
                $html .= "<div class='text-right'>\n";
                $html .= "<button name='cancel_session' type='button' class='btn btn-default' value='cancel_session'>".self::$locale['cancel']."</button>\n";
                $html .= "<button name='fill_session' type='button' class='btn btn-primary' value='fill_session'>".self::$locale['293']."</button>\n";
                $html .= "</div>\n";
                $html .= fusion_get_function('closeside', '');
                $html .= "</div>\n";
                add_to_jquery("
                    $('button[name^=\"fill_session\"]').bind('click', function(e) {
                        $fill_js
                        $('#rev-window').hide();
                        UpdateAdminCache();
                    });
                    $('button[name^=\"cancel_session\"]').bind('click', function(e) {
                        $('#rev-window').hide();
                        UpdateAdminCache();
                    });
            ");
            }
        }

        return $html;
    }

    /**
     * Set admin sections
     */
    public function setAdminPages() {
        self::$admin_pages = $this->getAdminPages();
        $this->admin_sections = array_filter(array_merge([
            0 => self::$locale['ac00'],
            1 => self::$locale['ac01'],
            2 => self::$locale['ac02'],
            3 => self::$locale['ac03'],
            4 => self::$locale['ac04'],
            5 => self::$locale['ac05'],
        ], $this->admin_sections));
        $this->admin_sections = array_values($this->admin_sections);
        $this->current_page = $this->currentPage();
    }

    /**
     * @param int $page
     *
     * @return array
     */
    public function getAdminPages($page = NULL) {
        $locale = fusion_get_locale();

        self::$admin_pages = array_filter(self::$admin_pages);
        if (empty(self::$admin_pages)) {
            $result = cdquery('adminpages', "SELECT * FROM ".DB_ADMIN." WHERE admin_language='".LANGUAGE."' ORDER BY admin_page DESC, admin_id ASC, admin_title ASC");
            if (cdrows($result)) {
                while ($data = cdarray($result)) {
                    if (file_exists(ADMIN.$data['admin_link']) || file_exists(INFUSIONS.$data['admin_link'])) {
                        if (checkrights($data['admin_rights']) && $data['admin_link'] != "reserved") {
                            $data['admin_title'] = isset($locale[$data['admin_rights']]) ? $locale[$data['admin_rights']] : $data['admin_title'];
                            self::$admin_pages[$data['admin_page']][] = $data;
                        }
                    }
                }
            }
        }
        return $page === NULL ? self::$admin_pages : (isset(self::$admin_pages[$page]) ? self::$admin_pages[$page] : self::$admin_pages);
    }

    /**
     * Build a return that always synchronize with the DB_ADMIN url.
     */
    public function currentPage() {
        $path = $_SERVER['PHP_SELF'];
        if (defined('START_PAGE')) {
            $path_info = pathinfo(strtok(START_PAGE, '?'));
            if (stristr(FUSION_REQUEST, '/administration/')) {
                $path = $path_info['filename'].'.php';
            } else {
                $path = '../'.$path_info['dirname'].'/'.$path_info['filename'].'.php';
            }
        }

        return $path;
    }

    /**
     * Determine which section is currently active.
     *
     * @return int|string
     */
    public function isActive() {
        $active_key = 0;
        self::$admin_pages = $this->getAdminPages();
        if (empty($active_key) && !empty(self::$admin_pages)) {
            foreach (self::$admin_pages as $key => $data) {
                $link = [];
                foreach ($data as $admin_data) {
                    $link[] = $admin_data['admin_link'];
                }
                $data_link = array_flip($link);
                if (isset($data_link[$this->currentPage()])) {
                    return $key;
                }
            }
        }

        return '0';
    }

    /**
     * @param int    $page          0-5 is core section pages. 6 and above are free to use.
     * @param string $section_title Section title
     * @param string $icon          Section icon
     */
    public function addAdminSection($page, $section_title, $icon) {
        $this->admin_sections[$page] = $section_title;
        $this->admin_section_icons[$page] = $icon;
        self::$admin_pages[$page] = [];
    }

    /**
     * Set admin breadcrumbs
     */
    public function setAdminBreadcrumbs() {
        add_breadcrumb([
            'link'  => ADMIN.'index.php'.fusion_get_aidlink().'&amp;pagenum=0',
            'title' => self::$locale['ac10']
        ]);
        $acTab = (isset($_GET['pagenum']) && isnum($_GET['pagenum'])) ? $_GET['pagenum'] : $this->isActive();
        if ($acTab != 0 && $acTab <= 5) {
            add_breadcrumb([
                'link'  => ADMIN.fusion_get_aidlink()."&amp;pagenum=".$acTab,
                'title' => self::$locale['ac0'.$acTab]
            ]);
        }

    }

    /**
     * @return array
     */
    public function getAdminPageIcons() {
        return $this->admin_page_icons;
    }

    /**
     * @param string $rights
     * @param string $icons
     */
    public function setAdminPageIcons($rights, $icons) {
        $this->admin_page_icons[$rights] = $icons;
    }

    /**
     * @param string $type
     *
     * @return array|mixed|null
     */
    public function getLinkType($type = NULL) {
        return ($type !== NULL ? (isset($this->link_type[$type]) ? $this->link_type[$type] : NULL) : $this->link_type);
    }

    /**
     * @param string $type Link prefix
     * @param string $link Link url
     */
    public function setLinkType($type, $link) {
        $this->link_type[$type] = $link;
    }

    /**
     * Get submit type
     *
     * @param string $type submit stype prefix
     *
     * @return array|mixed|null
     */
    public function getSubmitType($type = NULL) {
        return ($type !== NULL ? (isset($this->submit_type[$type]) ? $this->submit_type[$type] : NULL) : $this->submit_type);
    }

    /**
     * @param string $type  Submissions prefix
     * @param string $title Title
     */
    public function setSubmitType($type, $title) {
        $this->submit_type[$type] = $title;
    }

    /**
     * @param string $type
     *
     * @return array|mixed|null
     */
    public function getSubmitData($type = NULL) {
        return ($type !== NULL ? (isset($this->submit_data[$type]) ? $this->submit_data[$type] : NULL) : $this->submit_data);
    }

    /**
     * @param string $type    Submissions prefix
     * @param array  $options array(infusion_name, link, submit_link, submit_locale, title,admin_link)
     */
    public function setSubmitData($type, array $options = []) {
        if (defined(strtoupper($options['infusion_name']).'_EXISTS')) {
            $this->submit_data[$type] = $options;
        }
    }

    /**
     * @param string $type
     *
     * @return array|mixed|null
     */
    public function getSubmitLink($type = NULL) {
        return ($type !== NULL ? (isset($this->submit_link[$type]) ? $this->submit_link[$type] : NULL) : $this->submit_link);
    }

    /**
     * @param string $link Admin submission url
     * @param string $type Submissions stype prefix
     */
    public function setSubmitLink($type, $link) {
        $this->submit_link[$type] = $link;
    }

    /**
     * @param string $type
     *
     * @return array|mixed|null
     */
    public function getCommentType($type = NULL) {
        return ($type !== NULL ? (isset($this->comment_type[$type]) ? $this->comment_type[$type] : NULL) : $this->comment_type);
    }

    /**
     * @param string $type  Comment prefix
     * @param string $title Title
     */
    public function setCommentType($type, $title) {
        $this->comment_type[$type] = $title;
    }

    /**
     * @param string $type Infusion name
     */
    public function getFolderPermissions($type = NULL) {
        return ($type !== NULL ? (isset($this->folder_permissions[$type]) ? $this->folder_permissions[$type] : NULL) : $this->folder_permissions);
    }

    /**
     * @param string $type    Infusion name
     * @param array  $options array(image_folder => TRUE or FALSE)
     */
    public function setFolderPermissions($type, array $options = []) {
        if (defined(strtoupper($type).'_EXISTS')) {
            $this->folder_permissions[$type] = $options;
        }
    }

    /**
     * @param string $rights
     *
     * @return array|null
     */
    public function getCustomFolders($rights = NULL) {
        return ($rights !== NULL ? (isset($this->custom_folders[$rights]) ? $this->custom_folders[$rights] : NULL) : $this->custom_folders);
    }

    /**
     * A custom folder that appears in the file manager
     *
     * @param string $rights
     * @param array  $options setCustomFolder('N', [['path' => IMAGES_N, 'URL' => fusion_get_settings('siteurl').'infusions/news/images/', 'alias' => 'news']]);
     */
    public function setCustomFolder($rights, $options = []) {
        $this->custom_folders[$rights] = $options;
    }

    /**
     * @return array
     */
    public function getAdminPageLink() {
        return $this->admin_page_link;
    }

    /**
     * @return string
     */
    public function getCurrentPage() {
        return $this->current_page;
    }

    /**
     * @return array
     */
    public function getAdminSections() {
        return $this->admin_sections;
    }

    /**
     * @param int $page_number
     *
     * @return string
     */
    public function getAdminSectionIcons($page_number) {
        if (!empty($this->admin_section_icons[$page_number]) && $this->admin_section_icons[$page_number]) {
            return $this->admin_section_icons[$page_number];
        }

        return FALSE;
    }

    /**
     * Replace admin page icons
     *
     * @param int    $page
     * @param string $icon
     */
    public function setAdminSectionIcons($page, $icon) {
        if (isset($this->admin_section_icons[$page])) {
            $this->admin_section_icons[$page] = $icon;
        }
    }

    /**
     * Get the administration page icons
     *
     * @param string $admin_rights
     *
     * @return bool
     */
    public function getAdminIcons($admin_rights) {
        // admin rights might not yield an icon & admin_icons override might not have the key.
        if (isset($this->admin_page_icons[$admin_rights]) && $this->admin_page_icons[$admin_rights]) {
            return $this->admin_page_icons[$admin_rights];
        }

        return FALSE;
    }

    /**
     * Displays vertical collapsible administration navigation
     *
     * @param bool $image_icon
     *
     * @return string
     */
    public function verticalAdminNav($image_icon = FALSE) {
        $aidlink = fusion_get_aidlink();
        $admin_sections = self::getAdminSections();
        $admin_pages = self::getAdminPages();

        add_to_jquery('$("[data-toggle=collapse]").click(function () {$(this).find(".adl-drop i").toggleClass("fa-angle-left fa-angle-down");});');

        $html = "<ul id='adl' class='admin-vertical-link'>\n";

        foreach ($admin_sections as $i => $section_name) {
            $active = ((isset($_GET['pagenum']) && $_GET['pagenum'] == $i) || (!isset($_GET['pagenum']) && $this->isActive() == $i));

            $html .= "<li class='".($active ? 'active panel' : 'panel')."' >\n";

            if (!empty($admin_pages[$i]) && is_array($admin_pages[$i])) {
                $html .= "<a class='adl-link ".($active ? '' : 'collapsed')."' data-parent='#adl' data-toggle='collapse' href='#adl-$i' aria-expanded='false' aria-controls='#adl-$i'>".$this->getAdminSectionIcons($i)." <span class='adl-section-name'>".$section_name."</span> ".($i > 0 ? "<span class='adl-drop pull-right'><i class='fa fa-angle-".($active ? "left" : "down")."'></i></span>" : '')."</a>\n";
                $html .= "<ul id='adl-$i' class='admin-submenu collapse ".($active ? 'in' : '')."'>\n";

                foreach ($admin_pages[$i] as $data) {
                    $secondary_active = $data['admin_link'] == $this->currentPage();
                    $icons = ($image_icon === TRUE) ? "<img class='admin-image' src='".get_image("ac_".$data['admin_rights'])."' alt='".$data['admin_title']."'>" : $this->getAdminIcons($data['admin_rights']);

                    $html .= checkrights($data['admin_rights']) ? "<li".($secondary_active ? " class='active'" : '')."><a href='".ADMIN.$data['admin_link'].$aidlink."'>".$icons." <span class='adl-submenu-title'>".$data['admin_title']."</span></a></li>\n" : "";
                }

                $html .= "</ul>\n";
            } else {
                $html .= "<a class='adl-link' href='".ADMIN."index.php".$aidlink."&amp;pagenum=0'>".$this->getAdminSectionIcons($i)." <span class='adl-section-name'>".$section_name."</span> ".($i > 0 ? "<span class='adl-drop pull-right'></span>" : '')."</a>\n";
            }
            $html .= "</li>\n";
        }

        $html .= "</ul>\n";

        return $html;
    }

    /**
     * Displays horizontal administration navigation
     *
     * @param bool $icon_only
     *
     * @return string
     */
    public function horizontalAdminNav($icon_only = FALSE) {
        $aidlink = fusion_get_aidlink();
        $html = "<ul class='admin-horizontal-link'>\n";
        foreach ($this->admin_sections as $i => $section_name) {
            $active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && $this->isActive() == $i) ? 1 : 0;
            $admin_text = $icon_only == FALSE ? " ".$section_name : "";
            $html .= "<li ".($active ? "class='active'" : '')."><a title='".$section_name."' href='".ADMIN.$aidlink."&amp;pagenum=$i'>".$this->getAdminSectionIcons($i).$admin_text."</a></li>\n";
        }
        $html .= "</ul>\n";

        return $html;
    }

    /**
     * Build a return that always synchronize with the DB_ADMIN url.
     *
     * @deprecated use currentPage()
     */
    public function _currentPage() {
        return $this->currentPage();
    }

    /**
     * Determine which section is currently active.
     *
     * @return int|string
     *
     * @deprecated use isActive()
     */
    public function _isActive() {
        return $this->isActive();
    }

    /**
     * Displays vertical collapsible administration navigation
     *
     * @param bool $image_icon
     *
     * @return string
     *
     * @deprecated use verticalAdminNav()
     */
    public function vertical_admin_nav($image_icon = FALSE) {
        return $this->verticalAdminNav($image_icon);
    }

    /**
     * Displays horizontal administration navigation
     *
     * @param bool $icon_only
     *
     * @return string
     * @deprecated use horizontalAdminNav()
     */
    public function horizontal_admin_nav($icon_only = FALSE) {
        return $this->horizontalAdminNav($icon_only);
    }

    /**
     * Get the administration page icons
     *
     * @param string $admin_rights
     *
     * @return bool
     * @deprecated use getAdminIcons()
     */
    public function get_admin_icons($admin_rights) {
        return $this->getAdminIcons($admin_rights);
    }

    /**
     * @param int $page_number
     *
     * @return string
     * @deprecated use getAdminSectionIcons()
     */
    public function get_admin_section_icons($page_number) {
        return $this->getAdminSectionIcons($page_number);
    }
}
