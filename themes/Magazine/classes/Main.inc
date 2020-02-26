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

        $twig = twig_init(THEME.'twig');

        $menu_options = [
            'id'                => 'main-menu',
            'nav_class_primary' => 'nav navbar-nav navbar-right primary',
            'container_fluid'   => TRUE,
            'header_content'    => '<a class="navbar-brand" href="'.BASEDIR.$settings['opening_page'].'"><img src="'.BASEDIR.$settings['sitebanner'].'" alt="'.$settings['sitename'].'" class="img-responsive"/></a>',
            'grouping'          => TRUE,
            'links_per_page'    => 10,
            'html_pre_content'  => $this->userMenu(),
            'show_header'       => TRUE
        ];

        $content = ['sm' => 12, 'md' => 12, 'lg' => 12];
        $left    = ['sm' => 3,  'md' => 2,  'lg' => 2];
        $right   = ['sm' => 3,  'md' => 2,  'lg' => 2];

        if (defined('LEFT') && LEFT && $this->getParam('left') == TRUE) {
            $content['sm'] = $content['sm'] - $left['sm'];
            $content['md'] = $content['md'] - $left['md'];
            $content['lg'] = $content['lg'] - $left['lg'];
        }

        if (defined('RIGHT') && RIGHT && $this->getParam('right') == TRUE || $this->getParam('right_content')) {
            $content['sm'] = $content['sm'] - $right['sm'];
            $content['md'] = $content['md'] - $right['md'];
            $content['lg'] = $content['lg'] - $right['lg'];
        }

        $context = [
            'locale'        => fusion_get_locale('', MG_LOCALE),
            'settings'      => $settings,
            'themesettings' => get_theme_settings('Magazine'),
            'mainmenu'      => SiteLinks::setSubLinks($menu_options)->showSubLinks(),
            'getparam'      => ['container' => $this->getParam('container')],
            'banner1'       => showbanners(1),
            'banner2'       => showbanners(2),
            'ifleft'        => (defined('LEFT') && LEFT && $this->getParam('left') == TRUE) ? TRUE : FALSE,
            'left'          => $left,
            'content'       => $content,
            'notices'       => $this->getParam('notices') ? renderNotices(getNotices(['all', FUSION_SELF])) : '',
            'ifright'       => defined('RIGHT') && RIGHT && $this->getParam('right') == TRUE || $this->getParam('right_content') ? TRUE : FALSE,
            'right'         => $right,
            'right_content' => $this->getParam('right_content'),
            'right_const'   => ($this->getParam('right') == TRUE && defined('RIGHT') && RIGHT) ? RIGHT : '',
            'errors'        => showFooterErrors(),
            'footer_text'   => nl2br(parse_textarea($settings['footer'], FALSE, TRUE)),
            'copyright'     => showcopyright('', TRUE).showprivacypolicy(),
            'ifrendertime'  => ($settings['rendertime_enabled'] == 1 || $settings['rendertime_enabled'] == 2) ? TRUE : FALSE,
            'rendertime'    => showrendertime(),
            'memoryusage'   => showMemoryUsage(),
            'counter'       => showcounter()
        ];

        echo $twig->render('bootstrap3/theme.twig', $context);
    }

    private function userMenu() {
        $locale = fusion_get_locale('', MG_LOCALE);
        $settings = fusion_get_settings();
        $userdata = fusion_get_userdata();

        $twig = twig_init(THEME.'twig', true);

        if (iMEMBER) {
            $name = $locale['MG_001'].$userdata['user_name'];
        } else {
            $name = $locale['login'].($settings['enable_registration'] ? '/'.$locale['register'] : '');
        }

        ob_start();
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
            echo form_button('login', $locale['global_104'], 'login', ['class' => 'btn-primary btn-sm m-b-5', 'input_id' => 'loginbtn']);
            echo closeform();
            echo '</li>';
            echo '<li>'.str_replace(['[LINK]', '[/LINK]'], ['<a href="'.BASEDIR.'lostpassword.php">', '</a>'], $locale['global_106']).'</li>';
            if ($settings['enable_registration']) echo '<li><a href="'.BASEDIR.'register.php">'.$locale['register'].'</a></li>';
            echo '</ul>';
        }
        echo '</li>';
        $html = ob_get_contents();
        ob_end_clean();

        $context = [
            'locale'       => fusion_get_locale('', MG_LOCALE),
            'settings'     => $settings,
            'languages'    => fusion_get_enabled_languages(),
            'current_lang' => translate_lang_names(LANGUAGE),
            'html' => $html // W.I.P.
        ];

        return $twig->render('bootstrap3/usermenu.twig', $context);
    }
}
