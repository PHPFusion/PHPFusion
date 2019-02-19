<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_sitelinks_include_button.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Search;

defined('IN_FUSION') || exit;

$form_elements = &$form_elements;
$radio_button = &$radio_button;
$form_elements += [
    'sitelinks' => [
        'enabled'   => ['0' => 'fields1', '1' => 'fields2', '2' => 'fields3', '3' => 'order1', '4' => 'order2', '5' => 'chars'],
        'disabled'  => ['0' => 'datelimit', '1' => 'sort'],
        'display'   => [],
        'nodisplay' => [],
    ]
];
$radio_button += [
    'sitelinks' => form_checkbox('stype', fusion_get_locale('s400', LOCALE.LOCALESET."search/sitelinks.php"), Search_Engine::get_param('stype'),
        [
            'type'          => 'radio',
            'value'         => 'sitelinks',
            'reverse_label' => TRUE,
            'onclick'       => 'display(this.value)',
            'input_id'      => 'sitelinks',
            'class'         => 'm-b-0'
        ]
    )
];
