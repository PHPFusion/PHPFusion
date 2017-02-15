<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_forums_include_button.php
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

if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (db_exists(DB_FORUMS)) {

    $result = dbquery("
            SELECT f.forum_id, f.forum_name, f2.forum_name 'forum_cat_name'
            FROM ".DB_FORUMS." f
            INNER JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
            ".(multilang_table('FO') ? "WHERE f.forum_language=:language AND " : 'WHERE ').groupaccess('f.forum_access')."
            AND f.forum_cat!=:cat ORDER BY f2.forum_order ASC, f.forum_order ASC
            ",
                      [
                          ':language' => LANGUAGE,
                          ':cat' => 0,
                      ]
    );

    $flist = array('0' => fusion_get_locale('f401', LOCALE.LOCALESET."search/forums.php"));
    while ($data2 = dbarray($result)) {
        $flist[$data2['forum_id']] = trimlink($data2['forum_name'], 20);
    }

    $form_elements += array(
        'forums' => array(
            'enabled' => array(
                '0' => 'datelimit', '1' => 'fields1', '2' => 'fields2', '3' => 'fields3', '4' => 'sort', '5' => 'order1', '6' => 'order2',
                '7' => 'chars'
            ),
            'disabled' => array(),
            'display' => array(),
            'nodisplay' => array(),
        )
    );

    $radio_button += array(
        'forums' => form_checkbox('stype', fusion_get_locale('f400', LOCALE.LOCALESET."search/forums.php"), Search_Engine::get_param('stype'),
                                  array(
                                      'type' => 'radio',
                                      'value' => 'forums',
                                      'reverse_label' => TRUE,
                                      'onclick' => 'display(this.value)',
                                      'input_id' => 'forums'
                                  )
            ).form_select('forum_id', '', Search_Engine::get_param('forum_id'),
                          array(
                              'options' => $flist,
                              'inline' => TRUE,
                              'inner_width' => '150px',
                              'allowclear' => TRUE
                          ))
    );
}