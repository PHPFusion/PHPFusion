<?php
/**
 * Bootstrap 4 Navigation Render
 * Class Navbar
 */
class Navbar {

    const Default_ID = 'DefaultMenu';

    /**
     * @param $info
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function showSubLinks($info) {
        $settings = fusion_get_settings();
        $default_options = [
            'id'                  => self::Default_ID,
            'navbar_class'        => 'navbar-expand-lg navbar-light bg-light',
            'responsive_class'    => $info['response'] ? 'navbar-collapse collapse' : 'menu',
            'nav_class_primary'   => 'mr-auto',
            'nav_class_secondary' => 'ml-auto',
        ];

        $banner_info = [];
        if ($info['show_header']) {
            if ($info['show_banner']) {
                $info['banner'] = ($info['show_banner'] ? ($info['custom_banner'] ? $info['custom_banner'] : $settings['sitebanner']) : '');
                $info['banner_title'] = $settings['sitename'];
                $info['banner_link'] = $info['custom_banner_link'] ?: BASEDIR.$settings['opening_page'];
                $info['banner_position'] = $info['logoposition_xs']." ".$info['logoposition_sm']." ".$info['logoposition_md']." ".$info['logoposition_lg'];
                $info['banner_class'] = (!empty($info['banner_class']) ? $info['banner_class'] : '');
            }
        }
        $default_options += $banner_info;

        $info += $default_options;
        ksort($info);

        return fusion_render(BOILERPLATES.'bootstrap4/html/', 'navbar.twig', $info, TRUE);
    }

}
