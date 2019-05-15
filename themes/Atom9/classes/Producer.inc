<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom9/classes/Producer.inc
| Author: Frederick MC Chan (Chan)
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Atom9Theme;

use PHPFusion\Rewrite\Router;
use \PHPFusion\SiteLinks;

class Producer extends Core {
    protected static $instance = NULL;

    public static function GetInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    private function DispalyNavbar() {
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();
        $languages = fusion_get_enabled_languages();
        $settings = fusion_get_settings();

        $logo = '<a href="'.BASEDIR.$settings['opening_page'].'" class="navbar-brand"><img class="img-responsive" src="'.BASEDIR.$settings['sitebanner'].'" alt="'.$settings['sitename'].'"/><span class="p-l-15">'.$settings['sitename'].'</span></a>';

        ob_start();
        echo '<ul class="nav navbar-nav secondary navbar-right">';
            echo '<li><a href="#" id="search-btn" title="'.$locale['search'].'"><i class="fa fa-search"></i></a></li>';

            if (count($languages) > 1) {
                echo '<li class="dropdown language-switcher">';
                    echo '<a href="#" class="dropdown-toggle pointer" data-toggle="dropdown" title="'.LANGUAGE.'">';
                        echo '<i class="fa fa-globe"></i> ';
                        echo '<img class="current" style="margin-top: -5px;" src="'.BASEDIR.'locale/'.LANGUAGE.'/'.LANGUAGE.'-s.png" alt="'.translate_lang_names(LANGUAGE).'"/>';
                        echo '<span class="caret"></span>';
                    echo '</a>';

                    echo '<ul class="dropdown-menu">';
                        foreach ($languages as $language_folder => $language_name) {
                            echo '<li><a class="display-block" href="'.clean_request('lang='.$language_folder, ['lang'], FALSE).'">';
                                echo '<img class="m-r-5" src="'.BASEDIR.'locale/'.$language_folder.'/'.$language_folder.'-s.png" alt="'.$language_folder.'"/> ';
                                echo $language_name;
                            echo '</a></li>';
                        }
                    echo '</ul>';
                echo '</li>';
            }

            if (iMEMBER) {
                echo '<li class="dropdown">';
                    echo '<a href="#" class="dropdown-toggle pointer" data-toggle="dropdown">';
                        echo display_avatar($userdata, '18px', '', FALSE, 'img-circle m-r-5');
                        echo $userdata['user_name'].' <span class="caret"></span>';
                    echo '</a>';
                    echo '<ul class="dropdown-menu dropdown-user" role="menu">';
                        echo '<li><a href="'.BASEDIR.'profile.php?lookup='.$userdata['user_id'].'">'.$locale['profile'].'</a></li>';
                        echo '<li><a href="'.BASEDIR.'edit_profile.php">'.$locale['UM080'].'</a></li>';

                        if (iADMIN) {
                            echo '<li><a href="'.ADMIN.'index.php'.fusion_get_aidlink().'&pagenum=0">'.$locale['global_123'].'</a></li>';
                        }

                        echo '<li><a href="'.BASEDIR.'index.php?logout=yes">'.$locale['logout'].'</a></li>';
                    echo '</ul>';
                echo '</li>';

                $msg_count = dbcount(
                    "('message_id')",
                    DB_MESSAGES, "message_to=:my_id AND message_read=:unread AND message_folder=:inbox",
                    [':inbox' => 0, ':my_id' => $userdata['user_id'], ':unread' => 0]
                );

                $messages_count = '';
                if ($msg_count > 0) {
                    $messages_count = '<span class="label label-danger msg-count">'.$msg_count.'</span>';
                }

                // echo '<li><a href="#"><i class="fa fa-bell"></i></a></li>';
                echo '<li><a href="'.BASEDIR.'messages.php" title="'.$locale['UM081'].'"><i class="fa fa-envelope-o"></i>'.$messages_count.'</a></li>';
            } else {
                echo '<li><a href="'.BASEDIR.'login.php" title="'.$locale['login'].'"><i class="fa fa-sign-in"></i></a></li>';
                if ($settings['enable_registration']) {
                    echo '<li><a href="'.BASEDIR.'register.php" title="'.$locale['register'].'"><i class="fa fa-user-plus"></i></a></li>';
                }
            }
        echo '</ul>';

        add_to_jquery('
            $("#search-btn").on("click", function (e) {
                e.preventDefault();
                $(".searchform").toggle();
                $(".primary").toggle();
            });
        ');

        echo '<div class="searchform" style="display: none;">';
        echo openform('searchform', 'post', $settings['siteurl'].'search.php?stype=all', [
            'remote_url' => $settings['site_path'].'search.php',
            'class'      => 'navbar-form navbar-left'
        ]);

        echo form_text('stext', '', '', [
            'class'              => 'm-t-3',
            'group_size'         => 'lg',
            'placeholder'        => $locale['search'],
            'append_button'      => TRUE,
            'append_type'        => "submit",
            'append_form_value'  => $locale['search'],
            'append_value'       => '<i class="fa fa-search"></i>',
            'append_button_name' => 'search',
            'append_class'       => 'btn btn-default'
        ]);
        echo closeform();
        echo '</div>';

        $user_menu = ob_get_contents();
        ob_end_clean();

        $menu_options = [
            'id'             => 'main-menu',
            'navbar_class'   => 'navbar-default',
            'nav_class'      => 'nav navbar-nav primary',
            'container'      => TRUE,
            'grouping'       => TRUE,
            'links_per_page' => 7,
            'show_header'    => $logo,
            'html_content'   => $user_menu
        ];

        echo '<div style="min-height: 67px;">';
            echo SiteLinks::setSubLinks($menu_options)->showSubLinks();
        echo '</div>';

        //add_to_jquery('$("#main-menu").affix({offset: {top: 67}});');
    }

    private function DispalyBanner() {
        $settings = fusion_get_settings();

        if ($this->GetParam('atom_banner') == TRUE) {
            echo '<div class="atom-banner">';
                $file_path = str_replace(ltrim($settings['site_path'], '/'), '', preg_replace('/^\//', '', FUSION_REQUEST));
                if ($settings['site_seo'] && defined('IN_PERMALINK')) {
                    require_once CLASSES.'PHPFusion/Rewrite/Router.inc';
                    $file_path = Router::getRouterInstance()->getCurrentURL();
                }

                if ($settings['opening_page'] == $file_path) {
                    add_to_head('<style type="text/css">.body-wrapper{margin-top: 200px;}</style>');
                    echo '<div class="clearfix" style="margin-top: 100px;"><div class="container text-center">';
                        echo '<a href="'.BASEDIR.$settings['opening_page'].'" class="display-inline-block"><img class="img-responsive" src="'.BASEDIR.$settings['sitebanner'].'" alt="'.$settings['sitename'].'"/></a>';
                        echo '<h1 style="font-size: 5rem;text-transform: uppercase;">'.$settings['sitename'].'</h1>';
                    echo '</div></div>';
                }
            echo '</div>';
        }
    }

    private function DispalyBody() {
        $theme_settings = get_theme_settings('Atom9');

        $notices = getNotices(['all', FUSION_SELF]);

        if (!empty($notices) && $this->GetParam('notices') == TRUE) {
            echo '<section class="top">';
                echo '<div class="container container-top">';
                    echo renderNotices($notices);
                echo '</div>';
            echo '</section>';
        }

        echo '<section class="body-wrapper">';
            echo '<div class="container container-body">';

                echo showbanners(1);

                if ($this->GetParam('panels') == TRUE) {
                    if (defined('AU_CENTER') && AU_CENTER) {
                        echo '<div class="content_top"><div class="container-content-top">'.AU_CENTER.'</div></div>';
                    }
                }

                $content = ['sm' => 12, 'md' => 12, 'lg' => 12];
                $left    = ['sm' => 3,  'md' => 2,  'lg' => 2];
                $right   = ['sm' => 3,  'md' => 2,  'lg' => 2];

                $left_side = TRUE;
                $right_side = TRUE;
                if ($theme_settings['2columns_layout'] == 1) {
                    $left_side = $theme_settings['column_side'] == 'LEFT' ? TRUE : FALSE;
                    $right_side = $theme_settings['column_side'] == 'RIGHT' ? TRUE : FALSE;
                }

                if ($this->GetParam('panels') == TRUE) {
                    if ((defined('LEFT') && LEFT) && $left_side == TRUE && $this->GetParam('left') == TRUE || $this->GetParam('left_content')) {
                        $content['sm'] = $content['sm'] - $left['sm'];
                        $content['md'] = $content['md'] - $left['md'];
                        $content['lg'] = $content['lg'] - $left['lg'];
                    }

                    if ((defined('RIGHT') && RIGHT) && $right_side == TRUE && $this->GetParam('right') == TRUE || $this->GetParam('right_content')) {
                        $content['sm'] = $content['sm'] - $right['sm'];
                        $content['md'] = $content['md'] - $right['md'];
                        $content['lg'] = $content['lg'] - $right['lg'];
                    }
                }

                echo '<div class="row">';
                    if ($this->GetParam('panels') == TRUE) {
                        if ((defined('LEFT') && LEFT) && $left_side == TRUE && $this->GetParam('left') == TRUE || $this->GetParam('left_content')) {
                            echo '<div class="col-xs-12 col-sm-'.$left['sm'].' col-md-'.$left['md'].' col-lg-'.$left['lg'].'">';
                                echo $this->GetParam('left') == TRUE && defined('RIGHT') && RIGHT && $right_side == FALSE ? RIGHT : '';
                                echo $this->GetParam('left') == TRUE && defined('LEFT') && LEFT ? LEFT : '';
                                echo $this->GetParam('left_content');
                            echo '</div>';
                        }
                    }

                    echo '<div class="col-xs-12 col-sm-'.$content['sm'].' col-md-'.$content['md'].' col-lg-'.$content['lg'].'">';
                        if ($this->GetParam('panels') == TRUE) {
                            echo defined('U_CENTER') && U_CENTER ? '<div class="top">'.U_CENTER.'</div>' : '';
                        }
                        echo CONTENT;
                        if ($this->GetParam('panels') == TRUE) {
                            echo defined('L_CENTER') && L_CENTER ? L_CENTER : '';
                        }
                    echo '</div>';

                    if ($this->GetParam('panels') == TRUE) {
                        if ((defined('RIGHT') && RIGHT) && $right_side == TRUE && $this->GetParam('right') == TRUE || $this->GetParam('right_content')) {
                            echo '<div class="col-xs-12 col-sm-'.$right['sm'].' col-md-'.$right['md'].' col-lg-'.$right['lg'].'">';
                                echo $this->GetParam('right') == TRUE && defined('RIGHT') && RIGHT ? RIGHT : '';
                                echo $this->GetParam('right') == TRUE && defined('LEFT') && LEFT && $left_side == FALSE ? LEFT : '';
                                echo $this->GetParam('right_content');
                            echo '</div>';
                        }
                    }
                echo '</div>'; // .row

                if ($this->GetParam('panels') == TRUE) {
                    if (defined('BL_CENTER') && BL_CENTER) {
                        echo '<div class="bottom"><div class="container-bottom">'.BL_CENTER.'</div></div>';
                    }
                }
                echo showbanners(2);

            echo '</div>'; // .container-body
        echo '</section>'; // .body-wrapper
    }

    private function DispalyFooter() {
        $theme_settings = get_theme_settings('Atom9');
        $locale = fusion_get_locale('', ATOM9_LOCALE);

        echo '<section class="container-footer"><div class="container">';
            echo '<a id="toTop" href="#"><i class="fa fa-chevron-up fa-lg"></i></a>';
            add_to_jquery('$("#toTop").on("click",function(e){e.preventDefault();$("html, body").animate({scrollTop:0},800);});');

            $errors = showFooterErrors();
            if ($errors) {
                echo '<div class="errors text-center">'.$errors.'</div>';
            }

            if (!empty($theme_settings['facebook_url']) || !empty($theme_settings['twitter_url'])) {
                echo '<div class="social m-b-20">';
                    echo '<span class="m-r-20">'.$locale['A9_001'].' </span>';

                    if (!empty($theme_settings['facebook_url'])) {
                        echo '<a href="'.$theme_settings['facebook_url'].'" target="_blank"><i class="fa fa-facebook-square fa-2x fa-fw"></i></a>';
                    }

                    if (!empty($theme_settings['twitter_url'])) {
                        echo '<a href="'.$theme_settings['twitter_url'].'" target="_blank"><i class="fa fa-twitter-square fa-2x fa-fw"></i></a>';
                    }
                echo '</div>';
            }

            if (defined('USER1') || defined('USER2') || defined('USER3') || defined('USER4')) {
                echo '<div class="row">';
                    echo (defined('USER1') && USER1) ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.(defined('USER1') ? USER1 : '').'</div>' : '';
                    echo (defined('USER2') && USER2) ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.(defined('USER2') ? USER2 : '').'</div>' : '';
                    echo (defined('USER3') && USER3) ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.(defined('USER3') ? USER3 : '').'</div>' : '';
                    echo (defined('USER4') && USER4) ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.(defined('USER4') ? USER4 : '').'</div>' : '';
                echo '</div>';
            }

            if (self::FooterPanels() == TRUE) {
                echo '<div class="row">';
                    echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.self::GetFooterPanel('footer_col1').'</div>';
                    echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.self::GetFooterPanel('footer_col2').'</div>';
                    echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.self::GetFooterPanel('footer_col3').'</div>';
                    echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.self::GetFooterPanel('footer_col4').'</div>';
                echo '</div>';
            }
        echo '</div></section>'; // .container-footer
    }

    private function DispalyCopyright() {
        $locale = fusion_get_locale('', ATOM9_LOCALE);
        $settings = fusion_get_settings();

        echo '<section class="container-copyright"><div class="container"><div class="row">';
            echo '<div class="col-xs-12 col-sm-5">';
                echo showcopyright().showprivacypolicy();
                echo '<br/>'.$locale['A9_015'];

                if ($settings['rendertime_enabled'] == 1 || $settings['rendertime_enabled'] == 2) {
                    echo '<br/><small>';
                        echo showrendertime();
                        echo showMemoryUsage();
                    echo '</small>';
                }
            echo '</div>';

            echo '<div class="col-xs-12 col-sm-7 text-right">';
                echo nl2br(parse_textarea($settings['footer'], FALSE, TRUE));

                if ($settings['visitorcounter_enabled']) {
                    echo '<br/>';
                    echo showcounter();
                }
            echo '</div>';
        echo '</div></div></section>'; // .container-copyright
    }

    public static function Util() {
        $reference = get_breadcrumbs();
        $page_title = fusion_get_locale('home');

        if (!empty($reference)) {
            $reference = end($reference);
            $page_title = $reference['title'];
        }

        if (!stristr($_SERVER['REQUEST_URI'], '?')) {
            $link = SiteLinks::get_current_SiteLinks('', 'link_name');

            if (!empty($link)) {
                $page_title = $link;
            }
        }

        echo '<div class="util">';
            echo '<div class="container-util">';
                echo '<h2>'.$page_title.'</h2>';
                echo '<div><i class="fa fa-map-o"></i> '.render_breadcrumbs().'</div>';
            echo '</div>';
        echo '</div>';
    }

    public function render_page() {
        $this->DispalyNavbar();
        $this->DispalyBanner();
        $this->DispalyBody();
        $this->DispalyFooter();
        $this->DispalyCopyright();
    }
}
