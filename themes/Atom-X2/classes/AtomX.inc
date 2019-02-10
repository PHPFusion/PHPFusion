<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom-X2/classes/AtomX.inc
| Author: PHP-Fusion Inc
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
namespace AtomX2Theme;

use \PHPFusion\SiteLinks;

class AtomX extends Components {
    public function RenderPage() {
        echo '<section id="topcontent"><div class="container">';
            echo '<nav class="nav atom-x-nav">';
                echo $this->UserMenu();
                echo '<div class="display-inline-block logo"><a href="'.BASEDIR.$this->settings['opening_page'].'" class="'.$this->settings['logoposition_xs'].' '.$this->settings['logoposition_sm'].' '.$this->settings['logoposition_md'].' '.$this->settings['logoposition_lg'].'"><img src="'.BASEDIR.$this->settings['sitebanner'].'" alt="Logo" class="img-responsive"/></a></div>';
            echo '</nav>';
            echo '<nav class="nav atom-x-subnav">';
                $nav = SiteLinks::setSubLinks([
                    'id'                => 'atom-menu',
                    'navbar_class'      => 'navbar-default',
                    'nav_class'         => 'nav navbar-nav primary',
                    'show_header'       => TRUE,
                    'language_switcher' => TRUE
                ]);
                $nav->addMenuLink(1000, $this->locale['search'], 0, '#', '', FALSE, FALSE,  FALSE, FALSE, 'search-btn');
                echo SiteLinks::getInstance('atom-menu')->showSubLinks();
            echo '</nav>';
        echo '</div></section>'; // #topcontent

        echo '<section id="maincontent" class="p-0"><div class="container">';

            if (iMEMBER) {
                $this->UserInfoBar();
            }

            new SearchEngine();

            echo '<div class="notices">';
                echo renderNotices(getNotices(['all', FUSION_SELF]));
            echo '</div>';

            if (self::GetParam('section_header')) {
                echo '<section class="section-block '.self::GetParam('section_header_class').'"><div class="container-fluid">';
                    echo self::GetParam('section_header');
                echo '</div></section>';
            }

            echo '<section class="p-b-20 '.self::GetParam('mainbody_class').'" id="mainbody">';
                echo defined('AU_CENTER') && AU_CENTER ? AU_CENTER : '';
                echo showbanners(1);

                echo '<div class="row">';

                $content = ['sm' => 12, 'md' => 12, 'lg' => 12];
                $left    = ['sm' => 3,  'md' => 2,  'lg' => 2];
                $right   = ['sm' => 3,  'md' => 3,  'lg' => 3];

                if (defined('LEFT') && LEFT) {
                    $content['sm'] = $content['sm'] - $left['sm'];
                    $content['md'] = $content['md'] - $left['md'];
                    $content['lg'] = $content['lg'] - $left['lg'];
                }

                if (defined('RIGHT') && RIGHT && $this->GetParam('right') == TRUE || $this->GetParam('right_content')) {
                    $content['sm'] = $content['sm'] - $right['sm'];
                    $content['md'] = $content['md'] - $right['md'];
                    $content['lg'] = $content['lg'] - $right['lg'];
                }

                if (defined('LEFT') && LEFT) {
                    echo '<div class="col-xs-12 col-sm-'.$left['sm'].' col-md-'.$left['md'].' col-lg-'.$left['lg'].'">';
                        echo LEFT;
                    echo '</div>';
                }

                echo '<div class="col-xs-12 col-sm-'.$content['sm'].' col-md-'.$content['md'].' col-lg-'.$content['lg'].'">';
                    echo defined('U_CENTER') && U_CENTER ? U_CENTER : '';
                    echo CONTENT;
                    echo defined('L_CENTER') && L_CENTER ? L_CENTER : '';
                    echo showbanners(2);
                echo '</div>';

                if (defined('RIGHT') && RIGHT && $this->GetParam('right') == TRUE || $this->GetParam('right_content')) {
                    echo '<div class="col-xs-12 col-sm-'.$right['sm'].' col-md-'.$right['md'].' col-lg-'.$right['lg'].'">';
                        echo ($this->GetParam('right') == TRUE && defined('RIGHT') && RIGHT) ? RIGHT : '';
                        echo $this->GetParam('right_content');
                    echo '</div>';
                }

                echo '</div>';

                echo defined('BL_CENTER') && BL_CENTER ? BL_CENTER : '';
            echo '</section>'; // #mainbody
        echo '</div></section>'; // .#maincontent

        echo '<section id="bottomcontent" class="p-0"><div class="container">';

            if ((defined('USER1') && USER1) ||(defined('USER2') && USER2) || (defined('USER3') && USER3) || (defined('USER4') && USER4)) {
                echo '<section class="greycontent">';
                    echo '<div class="row">';
                        echo defined('USER1') && USER1 ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.USER1.'</div>' : '';
                        echo defined('USER2') && USER2 ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.USER2.'</div>' : '';
                        echo defined('USER3') && USER3 ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.USER3.'</div>' : '';
                        echo defined('USER4') && USER4 ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.USER4.'</div>' : '';
                    echo '</div>';
                echo '</section>';
            }

            echo '<footer id="footer" class="m-b-20"><div class="container-fluid">';
                echo showFooterErrors();

                echo '<div class="row">';
                    echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">';
                        echo '<i class="fa fa-rocket"></i> '.$this->locale['AX9_009'];
                        echo '<br/>'.$this->locale['AX9_010'];
                    echo '</div>';
                    echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 text-right">';
                        echo showcopyright().showprivacypolicy();

                        if ($this->settings['rendertime_enabled'] == 1 || $this->settings['rendertime_enabled'] == 2) {
                            echo '<br/><small>'.showrendertime().showMemoryUsage().'</small>';
                        }

                        echo '<br/>'.showcounter();
                    echo '</div>';
                echo '</div>';

                echo nl2br(parse_textarea($this->settings['footer'], FALSE, TRUE));
            echo '</div></footer>'; // #footer

        echo '</div></section>'; // #bottomcontent
    }

    private function UserMenu() {
        if (iMEMBER) {
            $name = $this->locale['AX9_001'].$this->userdata['user_name'];
        } else {
            $name = $this->locale['login'];
            $name .= $this->settings['enable_registration'] ? ' / '.$this->locale['register'] : '';
        }

        ob_start();
        echo '<ul class="list-style-none pull-right m-r-20">';
            echo '<li id="user-info" class="dropdown">';
                echo '<button type="button" id="user-menu" class="dropdown-toggle btn btn-primary btn-sm" style="margin-top: 8px;" data-toggle="dropdown">'.$name.' <span class="caret"></span></button>';

                if (iMEMBER) {
                    echo '<ul class="dropdown-menu" aria-labelledby="user-menu">';
                        echo '<li class="dropdown-header">'.$this->locale['AX9_002'].'</li>';
                        echo '<li><a href="'.BASEDIR.'profile.php?lookup='.$this->userdata['user_id'].'">'.$this->locale['view'].' '.$this->locale['profile'].'</a></li>';
                        echo '<li><a href="'.BASEDIR.'messages.php">'.$this->locale['message'].'</a></li>';

                        echo '<li><a href="'.BASEDIR.'edit_profile.php">'.$this->locale['UM080'].'</a></li>';
                        echo iADMIN ? '<li role="separator" class="divider"></li>' : '';
                        echo iADMIN ? '<li><a href="'.ADMIN.'index.php'.fusion_get_aidlink().'&pagenum=0">'.$this->locale['global_123'].'</a></li>' : '';
                        echo '<li role="separator" class="divider"></li>';
                        echo '<li><a href="'.BASEDIR.'index.php?logout=yes">'.$this->locale['logout'].'</a></li>';
                    echo '</ul>';
                } else {
                    echo '<ul class="dropdown-menu login-menu" aria-labelledby="user-menu">';
                        echo '<li class="dropdown-header">'.$this->locale['AX9_003'].'</li>';
                        echo '<li>';
                            $action_url = FUSION_SELF.(FUSION_QUERY ? '?'.FUSION_QUERY : '');
                            if (isset($_GET['redirect']) && strstr($_GET['redirect'], '/')) {
                                $action_url = cleanurl(urldecode($_GET['redirect']));
                            }

                            echo openform('loginform', 'post', $action_url, ['form_id' => 'login-form']);
                            switch ($this->settings['login_method']) {
                                case 2:
                                    $placeholder = $this->locale['global_101c'];
                                    break;
                                case 1:
                                    $placeholder = $this->locale['global_101b'];
                                    break;
                                default:
                                    $placeholder = $this->locale['global_101a'];
                            }

                            echo form_text('user_name', '', '', ['placeholder' => $placeholder, 'required' => TRUE, 'input_id' => 'username']);
                            echo form_text('user_pass', '', '', ['placeholder' => $this->locale['global_102'], 'type' => 'password', 'required' => TRUE, 'input_id' => 'userpassword']);
                            echo form_checkbox('remember_me', $this->locale['global_103'], '', ['value' => 'y', 'class' => 'm-0', 'reverse_label' => TRUE, 'input_id' => 'rememberme']);
                            echo form_button('login', $this->locale['global_104'], '', ['class' => 'btn-primary btn-sm m-b-5', 'input_id' => 'loginbtn']);
                            echo closeform();
                        echo '</li>';
                        echo '<li>'.str_replace(['[LINK]', '[/LINK]'], ['<a href="'.BASEDIR.'lostpassword.php">', '</a>'], $this->locale['global_106']).'</li>';
                        if ($this->settings['enable_registration']) echo '<li><a href="'.BASEDIR.'register.php">'.$this->locale['register'].'</a></li>';
                    echo '</ul>';
                }
            echo '</li>';
        echo '</ul>';

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}
