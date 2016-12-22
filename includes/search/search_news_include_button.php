<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_news_include_button.php
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

if (db_exists(DB_NEWS)) {

    $form_elements += array(
        'news' => array(
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
        'news' => form_checkbox('stype', fusion_get_locale('n400', LOCALE.LOCALESET."search/news.php"), Search_Engine::get_param('stype'),
                                array(
                                    'type' => 'radio',
                                    'value' => 'news',
                                    'reverse_label' => TRUE,
                                    'onclick' => 'display(this.value)',
                                    'input_id' => 'news'
                                )
        )
    );
}
