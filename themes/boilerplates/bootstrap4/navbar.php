<?php

use PHPFusion\Steam;

/**
 * Bootstrap 4 Navigation Render
 * Class Navbar
 */
class Navbar {

    public static function getSearch($info) {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        if ($info['searchbar']) {
            // Add in a new default search form.
            // LI first
            return openform('searchform', 'post', FUSION_ROOT.BASEDIR.'search.php?stype='.$settings['default_search'],
                    [
                        'inline' => TRUE,
                        //'remote_url' => $settings['site_path'].'search.php'
                    ]
                ).form_text('stext', '', '',
                    [
                        'placeholder'        => $locale['search'],
                        'append_button'      => TRUE,
                        'append_type'        => "submit",
                        "append_form_value"  => $locale['search'],
                        "append_value"       => "<i class='".$info['search_icon']."' title='".$locale['search']."'></i>",
                        "append_button_name" => "search",
                        "append_class"       => $info['search_btn_class'],
                        'class'              => 'm-0',
                    ]
                ).closeform();
        }
        return '';
    }

    /**
     * @param $info
     *
     * @return string
     * @throws ReflectionException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function showSubLinks($info) {
        $settings = fusion_get_settings();
        $locale = fusion_get_locale();
        if ($info['show_header']) {
            if ($info['show_banner']) {
                $info['banner'] = ($info['show_banner'] ? ($info['custom_banner'] ? $info['custom_banner'] : BASEDIR.$settings['sitebanner']) : '');
                $info['banner_title'] = $settings['sitename'];
                $info['banner_link'] = $info['custom_banner_link'] ?: BASEDIR.$settings['opening_page'];
                $info['banner_position'] = $settings['logoposition_xs']." ".$settings['logoposition_sm']." ".$settings['logoposition_md']." ".$settings['logoposition_lg'];
                $info['banner_class'] = (!empty($info['banner_class']) ? $info['banner_class'] : '');
            }
        }
        $info['search'] = Steam::getInstance()->load('Navigation')->search($info);
        if (!$info['navbar_class']) {
            $info['navbar_class'] = 'navbar-expand-lg navbar-light bg-light';
        }
        $info['responsive_class'] = ($info['responsive'] ? 'navbar-collapse collapse' : 'menu');
        if ($info['login']) {
            if (iMEMBER) {
                if (iADMIN) {
                    $info['admin'] = [
                        'link' => ADMIN.'index.php'.fusion_get_aidlink(),
                        'title' => $locale['global_123']
                    ];
                }
                $info['logout'] = [
                    'link'  => clean_request('logout=yes', ['logout'], FALSE),
                    'title' => $locale['logout']
                ];
            } else {
                $info['login'] = [
                    'link'  => BASEDIR.'login.php',
                    'title' => $locale['login']
                ];
                $info['register'] = [
                    'link'  => BASEDIR.'register.php',
                    'title' => $locale['register']
                ];
            }
        }

        //print_P($info);
        ksort($info);

        return fusion_render(BOILERPLATES.'bootstrap4/html/', 'navbar.twig', $info, TRUE);
    }

}
