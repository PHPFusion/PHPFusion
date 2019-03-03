<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_faqs_include_button.php
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

if (defined('FAQ_EXIST')) {
    $form_elements = &$form_elements;
    $radio_button = &$radio_button;
    $form_elements += [
        'faqs' => [
            'enabled'   => ['0' => 'fields1', '1' => 'fields2', '2' => 'fields3', '3' => 'order1', '4' => 'order2'],
            'disabled'  => ['0' => 'datelimit', '1' => 'sort', '2' => 'chars'],
            'display'   => [],
            'nodisplay' => [],
        ]
    ];
    $radio_button += [
        'faqs' => form_checkbox('stype', fusion_get_locale('fq400', INFUSIONS."faq/locale/".LOCALESET."search/faqs.php"), Search_Engine::get_param('stype'),
            [
                'type'          => 'radio',
                'value'         => 'faqs',
                'reverse_label' => TRUE,
                'onclick'       => 'display(this.value)',
                'input_id'      => 'faqs',
                'class'         => 'm-b-0'
            ]
        )
    ];
}
