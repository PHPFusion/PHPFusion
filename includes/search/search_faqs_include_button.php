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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
if (db_exists(DB_FAQS)) {
    $form_elements += array(
        'faqs' => array(
            'enabled' => array('0' => 'fields1', '1' => 'fields2', '2' => 'fields3', '3' => 'order1', '4' => 'order2'),
            'disabled' => array('0' => 'datelimit', '1' => 'sort', '2' => 'chars'),
            'display' => array(),
            'nodisplay' => array(),
        )
    );
    $radio_button += array(
        'faqs' => form_checkbox('stype', fusion_get_locale('fq400', LOCALE.LOCALESET."search/faqs.php"), Search_Engine::get_param('stype'),
                                array(
                                    'type' => 'radio',
                                    'value' => 'faqs',
                                    'reverse_label' => TRUE,
                                    'onclick' => 'display(this.value)',
                                    'input_id' => 'faqs'
                                )
        )
    );
}
