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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

$search = Search_Engine::getInstance();

$form_elements += array(
    'sitelinks' => array(
        'enabled' => array('0' => 'fields1', '1' => 'fields2', '2' => 'fields3', '3' => 'order1', '4' => 'order2', '5' => 'chars'),
        'disabled' => array('0' => 'datelimit', '1' => 'sort'),
        'display' => array(),
        'nodisplay' => array(),
    )
);
$radio_button += array(
    'sitelinks' => form_checkbox('stype', fusion_get_locale('s400', LOCALE.LOCALESET."search/sitelinks.php"), $search::get_param('stype'),
        array(
            'type' => 'radio',
            'value' => 'sitelinks',
            'reverse_label' => TRUE,
            'onclick' => 'display(this.value)',
            'input_id' => 'sitelinks'
        )
    )
);
