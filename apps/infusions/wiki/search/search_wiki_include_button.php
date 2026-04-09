<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: search_wiki_include_button.php
| Author: RobiNN
+--------------------------------------------------------*/
namespace PHPFusion\Search;

defined('IN_FUSION') || exit;

if (db_exists(DB_WIKI)) {
    $form_elements = &$form_elements;
    $radio_button = &$radio_button;

    $form_elements += [
        'wiki' => [
            'enabled'   => [
                '0' => 'datelimit',
                '1' => 'fields1',
                '2' => 'fields2',
                '3' => 'fields3',
                '4' => 'sort',
                '5' => 'order1',
                '6' => 'order2',
                '7' => 'chars'
            ],
            'disabled'  => [],
            'display'   => [],
            'nodisplay' => [],
        ]
    ];

    if (file_exists(WIKI.'locale/'.LOCALESET.'search/wiki.php')) {
        $locale_file = WIKI.'locale/'.LOCALESET.'search/wiki.php';
    } else {
        $locale_file = WIKI.'locale/English/search/wiki.php';
    }

    $radio_button += [
        'wiki' => form_checkbox('stype', fusion_get_locale('wiki400', $locale_file), Search_Engine::get_param('stype'), [
            'type'          => 'radio',
            'value'         => 'wiki',
            'reverse_label' => TRUE,
            'onclick'       => 'display(this.value)',
            'input_id'      => 'wiki',
            'class'         => 'm-b-0'
        ])
    ];
}
