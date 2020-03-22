<?php

use PHPFusion\SiteLinks;

(defined('IN_FUSION') || exit);

$menu_options = [
    'id'            => 'adminbar',
    'show_header'   => FALSE,
    'container'     => TRUE,
    'callback_data' => [
        'admin' => [
            'menu' => [
                'link_id'   => 'menu',
                'link_name' => 'Menu',
                'link_url'  => 'sample',
                'link_cat'  => 0,
            ]
        ]
    ]
];
echo 'Admin bar goes here';
echo SiteLinks::setSubLinks($menu_options)->showSubLinks();
