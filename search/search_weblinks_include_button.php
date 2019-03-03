<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_weblinks_include_button.php
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

if (defined('WEBLINKS_EXIST')) {
    $form_elements = &$form_elements;
    $radio_button = &$radio_button;
    $form_elements += [
        'weblinks' => [
            'enabled'   => [
                '0' => 'datelimit', '1' => 'fields1', '2' => 'fields2', '3' => 'fields3', '4' => 'sort', '5' => 'order1', '6' => 'order2',
                '7' => 'chars'
            ],
            'disabled'  => [],
            'display'   => [],
            'nodisplay' => [],
        ]
    ];
    $radio_button += [
        'weblinks' => form_checkbox('stype', fusion_get_locale('w400', INFUSIONS.'weblinks/locale/'.LOCALESET.'search/weblinks.php'), Search_Engine::get_param('stype'),
            [
                'type'          => 'radio',
                'value'         => 'weblinks',
                'reverse_label' => TRUE,
                'onclick'       => 'display(this.value)',
                'input_id'      => 'weblinks',
                'class'         => 'm-b-0'
            ]
        )
    ];
}
