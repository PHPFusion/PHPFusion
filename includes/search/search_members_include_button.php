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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

$form_elements += array(
    'members' => array(
        'enabled' => array('0' => 'order1', '1' => 'order2'),
        'disabled' => array('0' => 'datelimit', '1' => 'fields1', '2' => 'fields2', '3' => 'fields3', '4' => 'sort', '5' => 'chars'),
        'display' => array(),
        'nodisplay' => array(),
    )
);
$radio_button += array(
    'members' => form_checkbox('stype', fusion_get_locale('m400', LOCALE.LOCALESET."search/members.php"), Search_Engine::get_param('stype'),
                               array(
                                   'type' => 'radio',
                                   'value' => 'members',
                                   'reverse_label' => TRUE,
                                   'onclick' => 'display(this.value)',
                                   'input_id' => 'members'
                               )
    )
);
