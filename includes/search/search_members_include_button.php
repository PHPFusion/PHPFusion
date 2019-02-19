<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_members_include_button.php
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
    'members' => [
        'enabled'   => ['0' => 'order1', '1' => 'order2'],
        'disabled'  => ['0' => 'datelimit', '1' => 'fields1', '2' => 'fields2', '3' => 'fields3', '4' => 'sort', '5' => 'chars'],
        'display'   => [],
        'nodisplay' => [],
    ]
];
$radio_button += [
    'members' => form_checkbox('stype', fusion_get_locale('m400', LOCALE.LOCALESET."search/members.php"), Search_Engine::get_param('stype'),
        [
            'type'          => 'radio',
            'value'         => 'members',
            'reverse_label' => TRUE,
            'onclick'       => 'display(this.value)',
            'input_id'      => 'members',
            'class'         => 'm-b-0'
        ]
    )
];
