<?php

use PHPFusion\SiteLinks;

boilerplate_set_default('bootstrap4');

function render_page($license = '') {

    $theme_path = THEME.'templates/';
    $settings = fusion_get_settings();
    $horizon_menu_options = [
        'container_fluid'   => TRUE,
        //'container'         => TRUE,
        'searchbar'         => TRUE,
        'navbar_class'      => 'navbar-expand-lg navbar-dark bg-dark navbar-light',
        'show_banner'       => TRUE,
        'login'             => TRUE,
        'language_switcher' => TRUE,
        //'header_content' => '<a class="navbar-brand" href="'.BASEDIR.$settings['opening_page'].'"><img src="'.BASEDIR.$settings['sitebanner'].'" alt="'.$settings['sitename'].'" class="img-responsive"/></a>',
        'grouping'          => TRUE,
        'links_per_page'    => 10,
        //'class'          => 'bg-dark',
        //'html_pre_content'  => $this->userMenu(),
        'show_header'       => TRUE,
    ];

    $content = ['sm' => 12, 'md' => 12, 'lg' => 12, 'xl' => 12];
    $left = ['sm' => 3, 'md' => 3, 'lg' => 2, 'xl' => 2];
    $right = ['sm' => 3, 'md' => 3, 'lg' => 2, 'xl' => 2];

    if (defined('LEFT') && LEFT) {
        $content['sm'] = $content['sm'] - $left['sm'];
        $content['md'] = $content['md'] - $left['md'];
        $content['lg'] = $content['lg'] - $left['lg'];
        $content['xl'] = $content['xl'] - $left['xl'];
    }

    if (defined('RIGHT') && RIGHT) {
        $content['sm'] = $content['sm'] - $right['sm'];
        $content['md'] = $content['md'] - $right['md'];
        $content['lg'] = $content['lg'] - $right['lg'];
        $content['xl'] = $content['xl'] - $right['xl'];
    }

    $showbanner = FALSE;
    if (BASEDIR.$settings['opening_page'] == FUSION_SELF) {
        $showbanner = TRUE;
    }

    $theme_info = [
        'top_navigation' => SiteLinks::setSubLinks($horizon_menu_options)->showSubLinks(),
        'showbanner'     => $showbanner,
        //'locale'        => fusion_get_locale(),
        'settings'       => $settings,
        'notices'        => renderNotices(getNotices(['all', FUSION_SELF])),
        //'themesettings' => get_theme_settings('Horizon'),
        //'mainmenu'      => $sublinks,
        //'getparam'      => ['container' => $this->getParam('container')],
        //'banner1'       => showbanners(1),
        //'banner2'       => showbanners(2),
        'content_grid'   => $content, // content css
        'left_grid'      => $left,
        'right_grid'     => $right,
        //'right_content' => $this->getParam('right_content'),
        //'right_const'   => ($this->getParam('right') == TRUE && defined('RIGHT') && RIGHT) ? RIGHT : '',
        //'errors'        => showFooterErrors(),
        'footer_text'    => nl2br(parse_textarea($settings['footer'], FALSE, TRUE)),
        'copyright'      => showcopyright('', TRUE).showprivacypolicy(),
        'rendertime'     => ($settings['rendertime_enabled'] == 1 || $settings['rendertime_enabled'] == 2) ? showrendertime() : '',
        'memoryusage'    => showMemoryUsage(),
        'counter'        => showcounter(),
        //'admin_login_link' => (iADMIN ? ADMIN.'index.php'.fusion_get_aidlink() : ''),
    ];
    //print_p($theme_info);
    return fusion_render($theme_path, 'theme.twig', $theme_info, TRUE);
}

function openside($title, $image='') {
    echo fusion_render(THEME.'templates/', 'open-side.twig', [
        'title' => $title,
        'image' => $image,
    ], fusion_get_settings('devmode'));
}

function closeside() {
    echo fusion_render(THEME.'templates/', 'close-side.twig', [],FALSE);
}
