<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_forum_include_button.php
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
namespace PHPFusion;

use PHPFusion\Search\Search_Engine;

defined('IN_FUSION') || exit;

if (defined('FORUM_EXIST')) {
    $form_elements = &$form_elements;
    $radio_button = &$radio_button;
    $bind = [
        ':cat'      => '0',
    ];
    $fresult = "
            SELECT f.forum_id, f.forum_name, f2.forum_name 'forum_cat_name'
            FROM ".DB_FORUMS." f
            INNER JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
            ".(multilang_table('FO') ? "WHERE ".in_group('f.forum_language', LANGUAGE)." AND " : 'WHERE ').groupaccess('f.forum_access')."
            AND f.forum_cat!=:cat ORDER BY f2.forum_order ASC, f.forum_order ASC
            ";
    $result = dbquery($fresult, $bind);

    $flist = ['0' => fusion_get_locale('f401', INFUSIONS."forum/locale/".LOCALESET."search/forum.php")];
    while ($data2 = dbarray($result)) {
        $flist[$data2['forum_id']] = trimlink($data2['forum_name'], 20);
    }

    $form_elements += [
        'forum' => [
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
        'forum' => form_checkbox('stype', fusion_get_locale('f400', INFUSIONS."forum/locale/".LOCALESET."search/forum.php"), Search_Engine::get_param('stype'),
                [
                    'type'          => 'radio',
                    'value'         => 'forum',
                    'reverse_label' => TRUE,
                    'onclick'       => 'display(this.value)',
                    'input_id'      => 'forum',
                    'class'         => 'm-b-0'
                ]
            ).form_select('forum_id', '', Search_Engine::get_param('forum_id'),
                [
                    'options'     => $flist,
                    'inline'      => TRUE,
                    'inner_width' => '150px',
                    'allowclear'  => TRUE
                ])
    ];

    add_to_jquery('
      $("#advanced_search_form #forum-field").addClass("display-inline-block");
      $("#advanced_search_form #forum_id-field").removeClass("display-block").addClass("display-inline-block").addClass("m-b-0");
    ');
}
