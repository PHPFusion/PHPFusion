<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Artemis Interface
| The Artemis Project - 2014 - 2016 (c)
| Network Data Model Development
| Filename: Artemis_ACP/acp_request.php
| Author: Guidlsquare , enVision Sdn Bhd
| Copyright patent 0517721 IPO
| Author's all rights reserved.
+--------------------------------------------------------+
| Released under PHP-Fusion EPAL
+--------------------------------------------------------*/
namespace Artemis\Viewer;

use Artemis\Model\resource;
use PHPFusion\Admins;

class adminPanel extends resource {

    private static $breadcrumb_shown = FALSE;

    public function __construct() {

        parent::__construct();

        $locale = parent::get_locale();

        $this->do_interface_js();

        $notices = getNotices();

        if (!empty($notices)) {
            echo renderNotices($notices);
        }
        ?>
        <section id="devlpr" class="adminPanel">
            <div class="left_menu">
                <header>
                    <h2>Artemis</h2>
                </header>
                <div class="menu">
                    <?php $this->left_nav(); ?>
                </div>
            </div>
            <div class="app_menu">
                <header class="affix">
                    <h3><?php echo $locale['spotlight'] ?></h3>
                    <?php echo form_text("search_app", "", "", array("placeholder" => $locale['spotlight_search'])); ?>
                </header>
                <div class="app_list">
                    <?php $this->app_nav() ?>
                </div>
            </div>
            <div id="main_content" class="content">
                <header class="header affix" data-spy="affix" data-offset-top="10">
                    <?php $this->adminHeader() ?>
                </header>
                <aside class="header">
                    <?php $this->display_admin_pages(); ?>
                </aside>
                <div class="content">
                    <?php echo CONTENT; ?>
                    <div class="copyright">
                        <?php echo showcopyright('', TRUE) ?>
                    </div>
                </div>
                <span class="main_content_overlay"></span>
            </div>
        </section>
        <footer>
            <ul>
                <?php
                $errors = showFooterErrors();
                if ($errors) {
                    echo "<li>".$errors."</li>\n";
                }
                ?>
                <?php
                if (fusion_get_settings("rendertime_enabled")) : ?>
                    <li><?php echo showrendertime() ?></li>
                    <li><?php echo showMemoryUsage() ?></li>
                    <li><?php self::$locale['copyright'].showdate("%Y", time())." - ".fusion_get_settings("sitename") ?></li>
                <?php endif; ?>
                <li class="pull-right"><strong>Artemis <?php echo self::$locale['render_engine'] ?> 3.2</strong></li>
            </ul>
        </footer>
        <?php
    }

    /**
     * Javascript for Interface
     */
    private function do_interface_js() {
        add_to_jquery("
        menuToggle('".self::$locale['admin_collapse']."');
        $('.menu-action').bind('click', function (e) {
            menu_wrap.toggleClass('collapsed');
            body_wrap.toggleClass('collapsed');
            app_wrap.toggleClass('collapsed');
            menuToggle('".self::$locale['admin_collapse']."');
            e.preventDefault();
        });
        $('#search_app').bind('keyup', function(e) {
            var data = {
                'appString' : $(this).val(),
                'mode' : 'html',
                'url' : '".$_SERVER['REQUEST_URI']."',
            };
            var sendData = $.param(data);
            $.ajax({
                url: '".THEMES."admin_themes/Artemis/acp_request.php".$this->get_aidlink()."',
                dataType: 'html',
                method : 'get',
                type: 'json',
                data: sendData,
                success: function(e) {
                    $('.app_page_list').hide();
                    $('ul#app_search_result').html(e).show();
                },
                error : function(e) {
                    console.log('fail');
                }
            });
        });
        ");

    }

    /**
     * Primary Sectional Menu
     */
    private function left_nav() {
        $aidlink = fusion_get_aidlink();
        $locale = parent::get_locale();

        $sections = Admins::getInstance()->getAdminSections();

        $sections[] = $locale['admin_collapse'];
        $this->admin_section_icons[] = "<i class='fa fa-chevron-circle-left'></i>\n";

        $pages = Admins::getInstance()->getAdminPages();
        $section_count = count($sections);
        ?>
        <ul>
            <?php foreach ($sections as $i => $section_name) :
                $active = ((isset($_GET['pagenum']) && $_GET['pagenum'] == $i) || (!isset($_GET['pagenum']) && $this->_isActive() == $i)) ? TRUE : FALSE;
                $is_menu_action = $i + 1 == $section_count ? TRUE : FALSE;
                $has_page = isset($pages[$i]) ? TRUE : FALSE;
                $href_src = "";
                if ($has_page) {
                    $href_src = "data-load=\"$i\"";
                } elseif (!$is_menu_action) {
                    $href_src = "href=\"".ADMIN.$aidlink."&amp;pagenum=$i\"";
                }
                ?>
                <li <?php echo($active ? " class=\"active\"" : "") ?>>
                    <a class="pointer admin-menu-item<?php echo $is_menu_action ? " menu-action " : "" ?>" title="<?php echo $section_name ?>" <?php echo $href_src ?>>
                        <?php echo Admins::getInstance()->get_admin_section_icons($i)." <span class=\"m-l-10\">$section_name</span> ".($i > 0 ? "<span class='fa fa-caret-right'></span>" : '') ?>
                    </a>
                    <a class="pointer admin-menu-icon<?php echo $is_menu_action ? " menu-action " : "" ?>" title="<?php echo $section_name ?>" <?php echo $href_src ?>>
                        <?php echo Admins::getInstance()->get_admin_section_icons($i) ?>
                    </a>
                </li>
                <?php
            endforeach;
            ?>
        </ul>
        <?php
        add_to_footer("<script type='text/javascript' src='".INCLUDES."jquery/jquery.cookie.js'></script>");
        add_to_footer("<script type='text/javascript' src='".THEMES."admin_themes/Artemis/Drivers/js/leftMenu.js'></script>");
    }

    /**
     * Applications List Menu
     * todo: find corresponding description of admin pages in model - maybe page section
     */
    private function app_nav() {

        $aidlink = parent::get_aidlink();

        $locale = parent::get_locale();

        $sections = Admins::getInstance()->getAdminSections();

        $pages = Admins::getInstance()->getAdminPages();

        $is_current_page = parent::_currentPage();

        echo "<ul id=\"app_search_result\"  style=\"display:none;\"></ul>\n";

        foreach ($sections as $i => $section_name) :

            if (!empty($pages[$i]) && is_array($pages[$i])) :

                echo "<ul id=\"ap-$i\" class=\"app_page_list\" style=\"display:none;\">\n";

                foreach ($pages[$i] as $key => $data) :

                    $secondary_active = $data['admin_link'] == $is_current_page ? "class='active'" : '';

                    $title = $data['admin_title'];

                    $link = ADMIN.$data['admin_link'].$aidlink;

                    if ($data['admin_page'] !== 5) {
                        $title = isset($locale[$data['admin_rights']]) ? $locale[$data['admin_rights']] : $title;
                    }

                    if (checkrights($data['admin_rights'])) :
                        ?>
                        <li <?php echo $secondary_active ?>>
                            <a href="<?php echo $link ?>">
                                <div class="app_icon">
                                    <img class="img-responsive" alt="<?php echo $title ?>" src="<?php echo get_image("ac_".$data['admin_rights']); ?>"/>
                                </div>
                                <div class="apps">
                                    <h4><?php echo $title ?></h4>
                                </div>
                            </a>
                        </li>
                        <?php
                    endif;

                endforeach;

                echo "</ul>\n";

            endif;

        endforeach;
    }

    private function adminHeader() {

        $locale = self::get_locale();

        $userdata = self::get_userdata();

        $aidlink = self::get_aidlink();

        $page_title = self::get_title();
        ?>
        <div class="app_icon">
            <?php echo $page_title['icon'] ?>
        </div>
        <h2>
            <?php echo $page_title['title'] ?>
        </h2>

        <nav>
            <ul class="nav">
                <li class="dropdown">
                    <a class="dropdown-toggle pointer" data-toggle="dropdown">
                        <?php echo display_avatar($userdata, "30px", "m-r-10", "", "img-rounded") ?>
                        <span class="hidden-xs hidden-sm hidden-md">
                            <?php echo $locale['welcome'].", <strong>".$userdata['user_name']."</strong> <span class='caret'></span>\n";
                        ?>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <?php
                        $u_drop_links = resource::get_udrop();
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
                <li class="hidden-xs hidden-sm">
                    <a title="<?php echo $locale['settings'] ?>" href="<?php echo ADMIN."settings_main.php".$aidlink ?>">
                        <?php echo $locale['settings'] ?>
                    </a>
                </li>
                <?php
                echo self::message_notification();
                echo self::admin_language_switcher();
                ?>
                <li>
                    <a title="<?php echo fusion_get_settings('sitename') ?>" href="<?php echo BASEDIR."index.php" ?>">
                        <?php echo $locale['home'] ?>
                    </a>
                </li>
            </ul>
        </nav>
        <?php
    }

    private function message_notification() {
        $locale = self::get_locale();
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

                    $messages[] = array(
                        "link" => BASEDIR."messages.php?folder=inbox&amp;msg_read=".$data['message_id'],
                        "title" => $data['message_subject'],
                        "sender" => array(
                            "user_id" => $data['sender_id'],
                            "user_name" => $data['sender_name'],
                            "user_avatar" => $data['sender_avatar'],
                            "user_status" => $data['sender_status'],
                        ),
                        "datestamp" => timer($data['message_datestamp']),
                    );

                }

            }

        }

        $html = '<li class="dropdown hidden-xs hidden-sm">';
        if (!empty($messages)) {
            $html .= '
            <a class="dropdown-toggle" data-toggle="dropdown" title="'.$locale['message'].'" href="'.BASEDIR.'messages.php">
                <i class="fa fa-envelope-o"></i>
                <span class="badge message_alert">'.count($messages).'</span>
            </a>
            <ul class="dropdown-menu">
            ';
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
            $html .= '
            <a title="'.$locale['message'].'" href="'.BASEDIR.'messages.php">
                <i class="fa fa-envelope-o"></i>
            </a>
            ';
        }
        $html .= "</li>";

        return $html;
    }

    private function display_admin_pages() {

        $aidlink = fusion_get_aidlink();
        $sections = Admins::getInstance()->getAdminSections();
        echo "<nav>";
        echo "<ul>\n";
        if (!empty($sections)) {
            $i = 0;
            foreach ($sections as $section_name) {
                echo "<li><a href='".ADMIN."index.php".$aidlink."&amp;pagenum=".$i."'>".$section_name."</a></li>\n";
                $i++;
            }
        }
        echo "</ul>\n";
        echo "</nav>\n";
    }

    public static function opentable($title, $class = NULL) {
        if (!empty($title)) : ?>
            <header><h3><?php echo $title ?></h3></header>
        <?php endif;
        if (self::$breadcrumb_shown == FALSE) :
            echo render_breadcrumbs();
            self::$breadcrumb_shown = TRUE;
        endif;
        ?>
        <div class="app_table">
        <?php
    }

    public static function closetable($title = FALSE, $class = NULL) {
        ?>
        </div>
        <?php
        if (!empty($title)) {
            echo "<footer><h3>$title</h3></footer>";
        }
    }

    public static function openside($title = FALSE, $class = NULL) {
        if (!empty($title)) : ?>
            <div class="app_aside_head clearfix <?php echo " ".$class ?>"><h5><?php echo $title ?></h5></div>
        <?php endif; ?>
        <div class="app_aside clearfix">
        <?php
    }

    public static function closeside($title = FALSE, $class = NULL) {
        ?>
        </div>
        <?php
        if (!empty($title)) {
            echo "<footer ".($class ? "class='$class'" : "")."><h3>$title</h3></footer>";
        }
    }


}