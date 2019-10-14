<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Magazine/classes/Main.inc
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
namespace Magazine;

use \PHPFusion\SiteLinks;

class Main extends Core {
    public function __construct() {
        $settings = fusion_get_settings();

        add_to_head('<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700,400italic"/>');

        $menu_options = [
            'id'               => 'main-menu',
            'nav_class'        => 'nav navbar-nav navbar-right primary',
            'container_fluid'  => TRUE,
            'show_header'      => '<a class="navbar-brand" href="'.BASEDIR.$settings['opening_page'].'"><img src="'.BASEDIR.$settings['sitebanner'].'" alt="'.$settings['sitename'].'" class="img-responsive"/></a>',
            'grouping'         => TRUE,
            'links_per_page'   => 10,
            'html_pre_content' => $this->UserMenu()
        ];

        echo SiteLinks::setSubLinks($menu_options)->showSubLinks();

        if ($this->GetParam('container') == TRUE) {
            echo '<div class="container-fluid">';
        }

            echo defined('AU_CENTER') && AU_CENTER ? AU_CENTER : '';
            echo showbanners(1);

            echo '<div class="row">';

                $content = ['sm' => 12, 'md' => 12, 'lg' => 12];
                $left    = ['sm' => 3,  'md' => 2,  'lg' => 2];
                $right   = ['sm' => 3,  'md' => 2,  'lg' => 2];

                if (defined('LEFT') && LEFT && $this->GetParam('left') == TRUE) {
                    $content['sm'] = $content['sm'] - $left['sm'];
                    $content['md'] = $content['md'] - $left['md'];
                    $content['lg'] = $content['lg'] - $left['lg'];
                }

                if (defined('RIGHT') && RIGHT && $this->GetParam('right') == TRUE || $this->GetParam('right_content')) {
                    $content['sm'] = $content['sm'] - $right['sm'];
                    $content['md'] = $content['md'] - $right['md'];
                    $content['lg'] = $content['lg'] - $right['lg'];
                }

                if (defined('LEFT') && LEFT  && $this->GetParam('left') == TRUE) {
                    echo '<div class="col-xs-12 col-sm-'.$left['sm'].' col-md-'.$left['md'].' col-lg-'.$left['lg'].'">';
                        echo LEFT;
                    echo '</div>';
                }

                echo '<div class="col-xs-12 col-sm-'.$content['sm'].' col-md-'.$content['md'].' col-lg-'.$content['lg'].'">';

                    if ($this->GetParam('notices')) {
                        echo renderNotices(getNotices(['all', FUSION_SELF]));
                    }

                    echo defined('U_CENTER') && U_CENTER ? U_CENTER : '';
                    echo CONTENT;
                    echo defined('L_CENTER') && L_CENTER ? L_CENTER : '';
                    echo showbanners(2);
                echo '</div>';

                if (defined('RIGHT') && RIGHT && $this->GetParam('right') == TRUE || $this->GetParam('right_content')) {
                    echo '<div class="col-xs-12 col-sm-'.$right['sm'].' col-md-'.$right['md'].' col-lg-'.$right['lg'].'">';
                        echo $this->GetParam('right_content');
                        echo ($this->GetParam('right') == TRUE && defined('RIGHT') && RIGHT) ? RIGHT : '';
                    echo '</div>';
                }

            echo '</div>';

            echo defined('BL_CENTER') && BL_CENTER ? BL_CENTER : '';

        if ($this->GetParam('container') == TRUE) {
            echo '</div>';
        }

        $this->Footer();
    }

    private function Footer() {
        $locale = fusion_get_locale('', MG_LOCALE);
        $settings = fusion_get_settings();
        $theme_settings = get_theme_settings('Magazine');

        echo '<footer id="main-footer"><div class="container-fluid">';
            echo '<div class="row">';
                echo defined('USER1') && USER1 ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.USER1.'</div>' : '';
                echo defined('USER2') && USER2 ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.USER2.'</div>' : '';
                echo defined('USER3') && USER3 ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.USER3.'</div>' : '';
                echo defined('USER4') && USER4 ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.USER4.'</div>' : '';
            echo '</div>';

            echo '<div class="text-center">'.showFooterErrors().'</div>';

            echo '<div class="m-t-20">';
                echo '<div class="row">';
                    echo '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 visible-xs">';
                        echo '<div class="text-center"><img src="'.BASEDIR.$settings['sitebanner'].'" alt="'.$settings['sitename'].'" class="img-responsive" style="display: inline;"/></div>';
                    echo '</div>';

                    echo '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 text-left">';
                        echo nl2br(parse_textarea($settings['footer'], FALSE, TRUE));
                    echo '</div>';

                    echo '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 hidden-xs">';
                        echo '<div class="text-center"><img src="'.BASEDIR.$settings['sitebanner'].'" alt="'.$settings['sitename'].'" class="img-responsive" style="display: inline;"/></div>';
                    echo '</div>';

                    echo '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">';
                        echo '<div class="social-links text-right">';

                            if (!empty($theme_settings['github_url'])) {
                                echo '<a href="'.$theme_settings['github_url'].'" target="_blank"><i class="fa fa-github"></i></a>';
                            }

                            if (!empty($theme_settings['facebook_url'])) {
                                echo '<a href="'.$theme_settings['facebook_url'].'" target="_blank"><i class="fa fa-facebook"></i></a>';
                            }

                            if (!empty($theme_settings['twitter_url'])) {
                                echo '<a href="'.$theme_settings['twitter_url'].'" target="_blank"><i class="fa fa-twitter"></i></a>';
                            }
                        echo '</div>';
                    echo '</div>';
                echo '</div>';

                echo '<div class="text-center" style="margin-top: 30px;">'.showcopyright('', TRUE).showprivacypolicy().'</div>';
                if ($settings['rendertime_enabled'] == 1 || $settings['rendertime_enabled'] == 2) {
                    echo '<div class="text-center">';
                    echo showrendertime();
                    echo showMemoryUsage();
                    echo '</div>';
                }
                echo '<div class="text-center strong">Magazine theme &copy; '.date('Y').' '.$locale['created_by'].' <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a></div>';
                echo showcounter();
            echo '</div>';
        echo '</div></footer>';
    }

    private function UserMenu() {
        $locale = fusion_get_locale('', MG_LOCALE);
        $settings = fusion_get_settings();
        $userdata = fusion_get_userdata();
        $languages = fusion_get_enabled_languages();

        if (iMEMBER) {
            $name = $locale['MG_001'].$userdata['user_name'];
        } else {
            $name = $locale['login'].($settings['enable_registration'] ? '/'.$locale['register'] : '');
        }

        ob_start();
        echo '<ul class="nav navbar-nav navbar-right secondary m-r-0">';
            if (count($languages) > 1) {
                echo '<li class="dropdown language-switcher">';
                    echo '<a href="#" class="dropdown-toggle pointer" data-toggle="dropdown" title="'.LANGUAGE.'">';
                        echo '<i class="fa fa-globe"></i> ';
                        echo '<img src="'.BASEDIR.'locale/'.LANGUAGE.'/'.LANGUAGE.'-s.png" alt="'.translate_lang_names(LANGUAGE).'"/>';
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

            echo '<li id="user-info" class="dropdown">';
                echo '<a href="#" id="user-menu" class="dropdown-toggle" data-toggle="dropdown">'.$name.' <span class="caret"></span></a>';

                if (iMEMBER) {
                    echo '<ul class="dropdown-menu" aria-labelledby="user-menu" style="min-width: 180px;">';
                        echo '<li><a href="'.BASEDIR.'profile.php?lookup='.$userdata['user_id'].'"><i class="m-r-5 fa fa-fw fa-user-circle-o"></i>'.$locale['view'].' '.$locale['profile'].'</a></li>';
                        echo '<li><a href="'.BASEDIR.'messages.php"><i class="m-r-5 fa fa-fw fa-envelope-o"></i> '.$locale['message'].'</a></li>';
                        echo '<li><a href="'.BASEDIR.'edit_profile.php"><i class="m-r-5 fa fa-fw fa-pencil"></i> '.$locale['UM080'].'</a></li>';
                        echo iADMIN ? '<li role="separator" class="divider"></li>' : '';
                        echo iADMIN ? '<li><a href="'.ADMIN.'index.php'.fusion_get_aidlink().'&pagenum=0"><i class="m-r-5 fa fa-fw fa-dashboard"></i> '.$locale['global_123'].'</a></li>' : '';
                        echo '<li role="separator" class="divider"></li>';
                        echo '<li><a href="'.BASEDIR.'index.php?logout=yes"><i class="m-r-5 fa fa-fw fa-sign-out"></i> '.$locale['logout'].'</a></li>';
                    echo '</ul>';
                } else {
                    echo '<ul class="dropdown-menu login-menu" aria-labelledby="user-menu">';
                        echo '<li>';
                            $action_url = FUSION_SELF.(FUSION_QUERY ? '?'.FUSION_QUERY : '');
                            if (isset($_GET['redirect']) && strstr($_GET['redirect'], '/')) {
                                $action_url = cleanurl(urldecode($_GET['redirect']));
                            }

                            echo openform('loginform', 'post', $action_url, ['form_id' => 'login-form']);
                            switch ($settings['login_method']) {
                                case 2:
                                    $placeholder = $locale['global_101c'];
                                    break;
                                case 1:
                                    $placeholder = $locale['global_101b'];
                                    break;
                                default:
                                    $placeholder = $locale['global_101a'];
                            }

                            echo form_text('user_name', '', '', ['placeholder' => $placeholder, 'required' => TRUE, 'input_id' => 'username']);
                            echo form_text('user_pass', '', '', ['placeholder' => $locale['global_102'], 'type' => 'password', 'required' => TRUE, 'input_id' => 'userpassword']);
                            echo form_checkbox('remember_me', $locale['global_103'], '', ['value' => 'y', 'class' => 'm-0', 'reverse_label' => TRUE, 'input_id' => 'rememberme']);
                            echo form_button('login', $locale['global_104'], '', ['class' => 'btn-primary btn-sm m-b-5', 'input_id' => 'loginbtn']);
                            echo closeform();
                        echo '</li>';
                        echo '<li>'.str_replace(['[LINK]', '[/LINK]'], ['<a href="'.BASEDIR.'lostpassword.php">', '</a>'], $locale['global_106']).'</li>';
                        if ($settings['enable_registration']) echo '<li><a href="'.BASEDIR.'register.php">'.$locale['register'].'</a></li>';
                    echo '</ul>';
                }
            echo '</li>';
        echo '</ul>';

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}
