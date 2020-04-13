<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme.adminpanel.php
| Author: Frederick Chan (deviance)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Inspire;

use PHPFusion\Admins;
use PHPFusion\BreadCrumbs;
use Twig\Environment;
use Twig\TwigFunction;

class AdminPanel extends Helper {

    private static $current_url = [];
    private static $opentablecount = 0;
    private $admins = NULL;
    private $active_rights = 0;

    public function __construct() {
        parent::__construct();
        $notices = render_notices(get_notices());
        //print_P($notices);

        $this->admins = Admins::getInstance();
        $rendertime_enabled = fusion_get_settings("rendertime_enabled") ? TRUE : FALSE;
        $footer_arr = [];
        if ($rendertime_enabled) {
            self::$locale['copyright'] = '';
            $footer_arr = [
                'render_time'  => showrendertime(),
                'memory_usage' => showMemoryUsage(),
                'copyright'    => self::$locale['copyright'].showdate("%Y", time())." - ".fusion_get_settings("sitename"),
            ];
        }
        $info = [
            'userdata'        => $this->userdata(),
            'notices'         => $notices,
            'copyright'       => showcopyright('', TRUE),
            'errors'          => showFooterErrors(),
            'footer'          => $footer_arr,
            'version'         => 'PHP-Fusion CMS v'.fusion_get_settings('version'),
            'content'         => CONTENT,
            'theme_name'      => 'Inspire Admin Theme',
            'sections'        => $this->showSections(),
            'aidlink'         => fusion_get_aidlink(),
            'admin_home_link' => ADMIN.fusion_get_aidlink(),
            'feather_path'    => INSPIRE.'templates/assets/img/feather-sprite.svg',
            'languages'       => fusion_get_language_switch(),
            'site_language'   => LANGUAGE,
        ];

        $twig = twig_init(INSPIRE.'templates/', TRUE);
        echo $this->addTwigFunction($twig)->render('inspire.twig', $info);
    }

    private function userdata() {
        $userdata = fusion_get_userdata();
        $userdata['user_level'] = getgroupname($userdata['user_level']);
        $userdata['user_avatar'] = display_avatar($userdata, '50px', 'rounded-circle', FALSE, 'rounded-circle');
        return $userdata;
    }

    /**
     * Primary Sectional Menu
     */
    private function showSections() {
        $aidlink = fusion_get_aidlink();
        $sections = $this->admins->getAdminSections();
        //$pages = $this->admins->getAdminPages();
        $section_count = count($sections);
        $nav = [];
        $pagenum = get('pagenum', FILTER_VALIDATE_INT); // when there is a filter icon.

        $this->admin_section_icons[] = "<i class='fa fa-chevron-circle-left'></i>\n";
        $this->admins->setAdminSectionIcons(0, 'home'); // Admin home
        $this->admins->setAdminSectionIcons(1, 'layout'); // Content Admin
        $this->admins->setAdminSectionIcons(2, 'users'); // User Admin
        $this->admins->setAdminSectionIcons(3, 'sliders'); // System Admin
        $this->admins->setAdminSectionIcons(4, 'settings'); // Settings
        $this->admins->setAdminSectionIcons(5, 'box'); // Infusions

        //$count = 0;
        // Core sections
        foreach ($sections as $i => $section_name) {
            if ($i == 5)
                break;

            $pages = $this->cacheAdminPages($i);
            $i_active = FALSE;
            if ($pagenum) {
                $i_active = $pagenum == $i ? TRUE : FALSE;
            }
            $active_class = ($i_active || (!$pagenum && $this->_isActive() == $i) && !check_get('inspired') ? 'active' : '');
            $is_menu_action = $i + 1 == $section_count ? TRUE : FALSE;
            $has_page = isset($pages[$i]) ? TRUE : FALSE;
            $href_src = "";
            if ($has_page) {
                $href_src = "data-load=\"$i\"";
            } else if (!$is_menu_action) {
                $href_src = "href=\"".ADMIN.$aidlink."&amp;pagenum=$i\"";
            }

            $nav[$i] = [
                'active_class' => $active_class,
                'has_page'     => $has_page,
                'href_src'     => $href_src,
                'section_name' => $section_name,
                'menu_action'  => ' menu-action ',
                'icon'         => $this->admins->getAdminSectionIcons($i),
                'pages'        => $pages,
            ];
        }

        $infusions = $this->cacheAdminPages(5);
        foreach ($infusions as $inf_rights => $infusion) {
            $active_class = ((!$pagenum && $this->_isActive() == $inf_rights) || $infusion['admin_active'] && !check_get('inspired') ? 'class="active"' : '');
            $has_page = isset($pages[$inf_rights]) ? TRUE : FALSE;
            $href_src = '';
            if ($has_page) {
                $href_src = "data-load=\"$inf_rights\"";
            }
            $nav[$inf_rights] = [
                'active_class' => $active_class,
                'has_page'     => $has_page,
                'href_src'     => $href_src,
                'section_name' => $infusion['admin_title'],
                'menu_action'  => ' menu-action ',
                'icon'         => $this->admins->getAdminIcons($inf_rights),
                'pages'        => $this->cacheAdminPages($inf_rights),
            ];
        }


        $nav += $this->getThemeSections();

        return (array)$nav;
    }

    private function cacheAdminPages($key) {
        $pages = Admins::getInstance()->getAdminPages();
        ksort($pages);
        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $is_current_page = parent::_currentPage();
        $current_pages = [];
        if (!empty($pages[$key]) && is_array($pages[$key])) {
            $page_array = $pages[$key];
            foreach ($page_array as $page_index => $data) {
                $rights = $data['admin_rights'];
                $pos = strpos($data['admin_rights'], '__');
                if ($pos) {
                    $rights = substr($data['admin_rights'], 0, $pos);
                }
                if (checkrights($rights)) {
                    $data['admin_active'] = ($data['admin_link'] == $is_current_page ? ' class="active"' : '');
                    if ($data['admin_page'] !== 5) {
                        $data['admin_title'] = isset($locale[$data['admin_rights']]) ? $locale[$data['admin_rights']] : $data['admin_title'];
                    }
                    $data['admin_link'] = ADMIN.$data['admin_link'].$aidlink;
                    $data['admin_image'] = $this->getAdminIcons('ac_'.$data['admin_rights']);
                    $current_pages[$data['admin_rights']] = $data;
                }
            }
        }
        return $current_pages;
    }

    private function getThemeSections() {
        $nav = [];
        // need to push the development sections in.
        if (fusion_get_settings('devmode')) {
            $dev_pages = $this->getDevelopmentPages();
            $nav[6] = [
                'active_class' => (check_get('inspired') ? 'class="active"' : ''),
                'has_page'     => FALSE,
                'href_src'     => 'package.html',
                'section_name' => 'Developer',
                'menu_action'  => ' menu-action ',
                'icon'         => 'coffee',
                'pages'        => $dev_pages,
            ];
        }
        return $nav;
    }

    /**
     * Inpire Theme Package Test Files
     *
     * @return array
     */
    public function getDevelopmentPages() {
        static $files;
        $aidlink = fusion_get_aidlink();
        $inspired = get('inspired');

        $nav = [];
        $folder_path = INSPIRE.'/tests/';
        if (empty($files)) {
            $files = makefilelist($folder_path, '.|..|._DS_Store', TRUE, 'files', 'ico|php');
        }
        $counter = 0;
        foreach ($files as $file_name) {
            $nav['IN_'.$counter] = [
                'admin_page'   => 6,
                'admin_title'  => $this->getTestPageTitle($file_name),
                'admin_rights' => 'THEME',
                'admin_link'   => INSPIRE.'theme.tests.php'.$aidlink.'&inspired='.$file_name,
                'admin_image'  => '',
                'admin_active' => $inspired == $file_name ? ' class="active" ' : FALSE,

            ];
            $counter++;
        }

        return $nav;
    }

    /**
     * Inspire Theme Test Files Translations
     *
     * @param $filename
     *
     * @return mixed|string
     */
    private function getTestPageTitle($filename) {
        $arr = [
            '404.html'             => 'Error 404',
            '500.html'             => 'Error 500',
            'activity_stream.html' => 'Activity Stream',
            'agile_board.html'     => 'Agile Board',
            'contacts.html'        => 'Contacts',
            'article.html'         => 'Articles',
            'blog.html'            => 'Blog',
        ];
        return (isset($arr[$filename]) ? $arr[$filename] : 'Title Missing');
    }

    public function addTwigFunction(Environment $twig) {
        $twig->addFunction(new TwigFunction('feathercon', function () {
            return call_user_func_array([$this, 'featherCon'], func_get_args());
        }));
        $twig->addFunction(new TwigFunction('languagecon', function () {
            return call_user_func_array([$this, 'languageCon'], func_get_args());
        }));

        return $twig;
    }

    public static function getInstance() {
        return new static;
    }

    public static function opentable($title, $links = []) {
        $breadcrumbs = BreadCrumbs::getInstance();
        $breadcrumbs->setLastClickable(TRUE);
        $title = strip_tags($title);
        if (!self::$opentablecount) {
            echo fusion_render(INSPIRE.'templates/', 'opentable.twig', array(
                'title'       => $title,
                'breadcrumbs' => self::renderBreadcrumbs($breadcrumbs->toArray()),
                'links'       => $links,
            ), TRUE);
            self::$opentablecount++;
        }
        return '';

    }

    private static function renderBreadcrumbs($array) {
        $twig = twig_init(INSPIRE.'templates/', TRUE);
        return $twig->render('breadcrumbs.twig', array(
            'home_icon'   => self::featherCon('home', 'sm'),
            'breadcrumbs' => $array
        ));
    }

    /**
     * Displays a feather icon
     *
     * @param        $value
     * @param string $class
     *
     * @return string
     */
    public static function featherCon($value, $class = '') {
        if ($class) {
            $class = " ".$class;
        }
        return '<svg class="feather'.$class.'"><use xlink:href="'.THEMES.'admin_themes/Inspire/templates/assets/img/feather-sprite.svg#'.$value.'"></use></svg>';
    }

    public static function closetable() {
        echo '</div></div>';
    }

    public static function openside($title = FALSE, $links = [], $options = []) {
        $default_options = [
            'link_class' => 'nav-pills' // support type 'pills', 'tabs'
        ];
        $options += $default_options;

        echo fusion_render(INSPIRE.'templates', 'opensidex.twig', array(
            'title'   => $title,
            'links'   => $links,
            'options' => $options,
        ), TRUE);
    }

    public static function closeside($footer = '') {
        echo fusion_render(INSPIRE.'templates', 'closesidex.twig', array('content' => $footer));
    }

    public static function opensidex($title = FALSE, array $links = [], array $options = []) {
        if (!defined('sidex_js')) {
            define('sidex_js', TRUE);
            add_to_jquery(/** @lang JavaScript */ "
            $('body').on('click', '.side-panel .sidex', function(e) {
                let sidexBody = $(this).parent('.side-panel').find('.body');
                let caret = $(this).parent('.side-panel').find('.panel-caret');
                // latest change
                if (sidexBody.is(':visible')) {
                    sidexBody.slideUp(0);
                    caret.removeClass('fa-caret-up').addClass('fa-caret-down');
                } else {
                    sidexBody.slideDown(0);
                    caret.removeClass('fa-caret-down').addClass('fa-caret-up');
                }
              });
        ");
        }

        return fusion_render(INSPIRE.'templates', 'opensidex.twig', [
            'title'    => $title,
            'links'    => $links,
            'options'  => $options,
            'is_sidex' => TRUE,
        ], TRUE);
    }

    public static function closesidex($footer = '') {
        return fusion_render(INSPIRE.'templates', 'closesidex.twig', [
            'content' => $footer
        ], TRUE);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function languageCon($value) {
        $folder_path = INSPIRE.'templates/assets/img/flags/';
        $flag_path = [
            'English'    => '260-united-kingdom.svg',
            'Polish'     => '211-poland.svg',
            'Afrikaans'  => '036-central-african-republic.svg',
            'Arabic'     => '133-saudi-arabia.svg',
            'Chinese'    => '034-china.svg',
            'Bulgarian'  => '168-bulgaria.svg',
            'Czech'      => '149-czech-republic.svg',
            'Danish'     => '174-denmark.svg',
            'Dutch'      => '237-netherlands.svg',
            'French'     => '195-france.svg',
            'German'     => '162-germany.svg',
            'Hungarian'  => '115-hungary.svg',
            'Lithanian'  => '064-lithuania.svg',
            'Norwegian'  => '143-norway.svg',
            'Portuguese' => '224-portugal.svg',
            'Romanian'   => '109-romania.svg',
            'Russian'    => '248-russia.svg',
            'Slovak'     => '091-slovakia.svg',
            'Spanish'    => '128-spain.svg',
            'Swedish'    => '184-sweden.svg',
            'Turkish'    => '218-turkey.svg',
            'Ukrainian'  => '145-ukraine.svg',
            'Vietnamese' => '220-vietnam.svg',
            'Malay'      => '118-malaysia.svg',
            'Thai'       => '238-thailand.svg',
            'Hindi'      => '246-india.svg',
            'Europe'     => '259-european-union.svg'
        ];

        $fetch_path = $folder_path.$flag_path['English'];
        $title = 'English';

        if (isset($flag_path[$value])) {
            $fetch_path = $folder_path.$flag_path[$value];
            $title = $value;
        }
        return "<img class='flag-icon' src='".$fetch_path."' alt='$title'/>";
    }

    private function getMessages() {
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();

        $messages = [];

        $msg_count_sql = "message_to = '".$userdata['user_id']."' AND message_user='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'";

        $msg_search_sql = "
                        SELECT message_id, message_subject,
                        message_from 'sender_id', u.user_name 'sender_name', u.user_avatar 'sender_avatar', u.user_status 'sender_status',
                        message_datestamp
                        FROM ".DB_MESSAGES."
                        INNER JOIN ".DB_USERS." u ON u.user_id=message_from
                        WHERE message_to = '".$userdata['user_id']."' AND message_user='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'
                        GROUP BY message_id
                        ";

        if (dbcount("(message_id)", DB_MESSAGES, $msg_count_sql)) {

            $msg_result = dbquery($msg_search_sql);

            if (dbrows($msg_result) > 0) {

                while ($data = dbarray($msg_result)) {

                    $messages[] = [
                        "link"      => BASEDIR."messages.php?folder=inbox&amp;msg_read=".$data['message_id'],
                        "title"     => $data['message_subject'],
                        "sender"    => [
                            "user_id"     => $data['sender_id'],
                            "user_name"   => $data['sender_name'],
                            "user_avatar" => $data['sender_avatar'],
                            "user_status" => $data['sender_status'],
                        ],
                        "datestamp" => timer($data['message_datestamp']),
                    ];

                }

            }

        }

        $html = '<li class="dropdown hidden-xs hidden-sm">';
        if (!empty($messages)) {
            $html .= '
            <a class="dropdown-toggle" data-toggle="dropdown" title="'.$locale['message'].'" href="'.BASEDIR.'messages.php">
                <i class="fal fa-envelope fa-lg"></i>
                <span class="badge message_alert">'.count($messages).'</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">';
            foreach ($messages as $message_data) {
                $html .= '
                <li>
                    <a href="'.$message_data['link'].'">
                        <div class="pull-left">
                        '.display_avatar($message_data['sender'], "30px", "", FALSE, "img-rounded m-t-5").'
                        </div>
                        <div class="overflow-hide">
                        <strong>'.$message_data['title'].'</strong>
                        <br/>
                        <small>'.$message_data['datestamp'].'</small>
                        </div>
                    </a>
                </li>
                ';
            }
            $html .= '</ul>';
        } else {
            $html .= '<a title="'.$locale['message'].'" href="'.BASEDIR.'messages.php">
                      <i class="fal fa-envelope fa-lg"></i>
            </a>
            ';
        }
        $html .= "</li>";

        return $html;
    }

    private function adminHeader() {

        $locale = fusion_get_locale();
        $aidlink = self::get_aidlink();
        $admin = Admins::getInstance();
        $sections = $admin->getAdminSections();
        $admin_pages = $admin->getAdminPages();
        $active_section = $admin->_isActive();
        $page_title = self::get_title();
        ?>
        <div class="head-title">
            <?php
            $header_text = "<h4>".$page_title['icon'].$page_title['title']."</h4>";
            if (isset($sections[$active_section]) && !empty($admin_pages[$active_section])) { // the current active section is present.
                if (isset($admin_pages[$this->active_rights])) {
                    $sections = $admin_pages[$this->active_rights]; // this is just the root of subpage. dropdown array is not present
                    if (!empty($sections)) {
                        $tab = $this->__tab($admin_pages, $sections);
                    }
                }
            }
            echo(!empty($tab) ? $tab : $header_text);
            ?>
        </div>
        <nav role="navigation">
            <div class="search">
                <?php echo form_text("search_app", "", "", [
                    'prepend'       => TRUE,
                    'prepend_value' => 'Search',
                    'append'        => TRUE,
                    'append_value'  => '<i class="fal fa-search"></i>',
                    'class'         => 'm-b-0', "placeholder" => $locale['spotlight_search'], 'width' => '100%']); ?>
            </div>
            <ul class="nav">
                <li class="hidden-xs hidden-sm">
                    <a title="<?php echo $locale['settings'] ?>" href="<?php echo ADMIN."settings_main.php".$aidlink ?>">
                        <i class="fal fa-cog fa-lg"></i>
                    </a>
                </li>
                <?php

                ?>
                <li>
                    <a title="<?php echo fusion_get_settings('sitename') ?>" href="<?php echo BASEDIR."index.php" ?>">
                        <i class="fal fa-home fa-lg"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle pointer" data-toggle="dropdown">
                        <?php echo display_avatar(fusion_get_userdata(), '50px', '', FALSE, 'img-circle') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                        <?php
                        $u_drop_links = Helper::get_udrop();
                        if (!empty($u_drop_links)) {
                            foreach ($u_drop_links as $link => $title) {
                                if ($link == "---") {
                                    echo "<li class=\"divider\"></li>\n";
                                } else {
                                    echo "<li><a href='$link'>$title</a></li>\n";
                                }
                            }
                        }
                        ?>
                    </ul>
                </li>
            </ul>
        </nav>
        <?php
    }

    private function __tab($admin_pages, $array, $i = 0) {
        $html = &$html;
        foreach ($array as $rights => $arr) {
            $match_c = $this->checkCurrentActive($arr['admin_link']);
            $match_p = $this->checkParentActive($admin_pages, $rights);
            if ($match_c || $match_p || $arr['admin_active']) {
                //$html .= "<li".$class.">\n";
                //$html .= "<a".$toggle_class." href='".$arr['admin_link']."' $data_attr>".$arr['admin_image'].$arr['admin_title'].$caret."</a>\n";
                if (isset($admin_pages[$rights])) {
                    // now we need to check how many keys this guy has.
                    $html = &$html;
                    $html .= "<ul id='c-app-$rights' class='nav nav-tabs'>".$this->__li($admin_pages, $admin_pages[$rights], $i)."</ul>\n";
                }
                //$html .= "</li>\n";
            }
        }
        return (string)$html;
    }

    /**
     * Given a url, check if currently have active.
     *
     * @param $url
     *
     * @return bool
     */
    private function checkCurrentActive($url) {

        if ($url !== '#' or $url !== "---") {

            if (empty(self::$current_url)) {

                self::$current_url = ((array)parse_url(htmlspecialchars_decode(server('REQUEST_URI')))) + ['path' => '', 'query' => ''];

                self::$current_url['path'] = str_replace(INFUSIONS, '/infusions/', self::$current_url['path']);

                if (self::$current_url['query']) {
                    parse_str(self::$current_url['query'], self::$current_url['current_query']);
                }
            }

            $current_url = ((array)parse_url(htmlspecialchars_decode($url))) + [
                    'path'  => '',
                    'query' => ''
                ];
            $current_url['path'] = strtr($current_url['path'], [
                INFUSIONS => '/infusions/',
                '..'      => ''
            ]);

            if (self::$current_url['path'] == $current_url['path']) {

                if (!empty($current_url['query'])) {
                    parse_str($current_url['query'], $queries);
                }

                if (isset(self::$current_url['current_query']) && isset($queries)) {
                    if (count(self::$current_url['current_query']) === count($queries)) {
                        if (empty(array_diff(self::$current_url['current_query'], $queries))) {
                            return TRUE;
                        }
                    }
                }
            }
        }
        return FALSE;
    }

    /**
     * Checks if there is a child with current active.
     *
     * @param $admin_pages
     * @param $rights
     *
     * @return bool
     */
    private function checkParentActive($admin_pages, $rights) {
        if (isset($admin_pages[$rights])) {
            foreach ($admin_pages[$rights] as $c_rights => $c_arr) {
                if ($c_arr['admin_active']) {
                    return TRUE;
                }
                if ($this->checkCurrentActive($c_arr['admin_link'])) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * Recursive function to build the dropdown collapse
     *
     * @param     $admin_pages
     * @param     $array
     * @param int $i
     *
     * @return string
     */
    private function __li($admin_pages, $array, $i = 0) {
        $html = &$html;
        foreach ($array as $rights => $arr) {
            $class = '';
            $caret = '';
            $toggle_class = '';
            $in = '';
            $data_attr = '';
            // child active
            $match_c = $this->checkCurrentActive($arr['admin_link']);
            //print_p($match_c);
            // parent active
            $match_p = $this->checkParentActive($admin_pages, $rights);
            //print_p($match_p);
            if ($match_c || $match_p || $arr['admin_active']) {
                $class .= " class='active'";
                if ($match_p)
                    $in = " in";
            }
            if (isset($admin_pages[$rights])) {
                $toggle_class = " data-toggle='collapse' data-parent='#sub_menu' href='#c-app-$rights'";
                $caret = " <b class='".($i > 1 ? "fas fa-caret-right" : "fas fa-caret-down")." pull-right m-t-5 m-l-10'></b>";
            }
            if (!empty($arr['data'])) {
                foreach ($arr['data'] as $key => $val) {
                    $data_attr .= $key.'="'.$val.'" ';
                }
            }
            $html .= "<li".$class.">\n";
            $html .= "<a".$toggle_class." href='".$arr['admin_link']."' $data_attr>".$arr['admin_image'].$arr['admin_title'].$caret."</a>\n";
            if (isset($admin_pages[$rights])) {
                // now we need to check how many keys this guy has.
                $html = &$html;
                $html .= "<!--dropdown--->\n";
                $html .= "<ul id='c-app-$rights' class='collapse".$in."'>".$this->__li($admin_pages, $admin_pages[$rights], $i)."</ul>\n";
                $html .= "<!--//dropdown--->\n";
            }
            $html .= "</li>\n";
        }

        return (string)$html;
    }

    private function display_admin_icon($rights) {
        $image = get_image($rights);
        if (!empty($image)) {
            if (preg_check("/\<(i|span|b) class=(.*?)\><\/(i|span|b)>/im", $image)) {
                return $image;
            }
            return "<img class='icon-xs display-inline m-r-5' src='$image'/>";
        }
        return '';
    }

    private function display_admin_pages() {
        $aidlink = fusion_get_aidlink();
        $admin = Admins::getInstance();
        $sections = $admin->getAdminSections();
        //print_P($sections);
        $admin_pages = $admin->getAdminPages();
        //print_p($admin_pages, 1);
        $active_section = $admin->_isActive();
        //print_p($active_section);
        $current_page = $admin->_currentPage();

        echo "<div class='submenu-header'>";
        echo "<h4><i class='fal fa-dice-d20 m-r-5'></i> GENESIS <sup>9</sup></h4>";
        echo "</div>";

        echo "<nav role='navigation'>";
        echo "<ul role='presentation'>\n";
        if (isset($sections[$active_section]) && !empty($admin_pages[$active_section])) { // the current active section is present.

            foreach ($admin_pages[$active_section] as $key => $admin_data) {
                if ($current_page == $admin_data['admin_link']) {
                    $this->active_rights = $admin_data['admin_rights']; // is correct
                }
            }
            //print_P($active_rights);

            if (isset($admin_pages[$this->active_rights])) {
                // get current section
                $sections = $admin_pages[$this->active_rights]; // this is just the root of subpage. dropdown array is not present.
                if (!empty($sections)) {
                    echo $this->__li($admin_pages, $sections);
                }
            } else {
                if (!empty($sections)) {
                    $i = 0;
                    foreach ($sections as $section_name) {
                        echo "<li><a href='".ADMIN."index.php".$aidlink."&amp;pagenum=".$i."'>".$section_name."</a></li>\n";
                        $i++;
                    }
                }
            }
        } else {
            if (!empty($sections)) {
                $i = 0;
                foreach ($sections as $section_name) {
                    echo "<li><a href='".ADMIN."index.php".$aidlink."&amp;pagenum=".$i."'>".$section_name."</a></li>\n";
                    $i++;
                }
            }
        }
        echo "</ul>\n";
        echo "</nav>\n";
    }

}
