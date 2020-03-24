<?php

use PHPFusion\SiteLinks;

(defined('IN_FUSION') || exit);
if (iADMIN) {

    $menu_options = [
        'id'              => 'adminbar',
        'show_header'     => TRUE,
        'show_banner'     => FALSE,
        'container_fluid' => TRUE,
        'responsive'      => FALSE,
        'callback_data'   => [
            '0' => [
                'menu'   => [
                    'link_id'   => 'menu',
                    'link_name' => 'Menu',
                    'link_url'  => 'sample',
                    'link_cat'  => 0,
                ],
                'panels' => [
                    'link_id'   => 'panels',
                    'link_name' => 'Panels',
                    'link_url'  => 'panels',
                    'link_cat'  => 0,
                ],
                'theme'  => [
                    'link_id'   => 'theme',
                    'link_name' => 'Theme',
                    'link_url'  => 'theme',
                    'link_cat'  => 0,
                ]

            ]
        ]
    ];
    echo SiteLinks::setSubLinks($menu_options)->showSubLinks();
}

