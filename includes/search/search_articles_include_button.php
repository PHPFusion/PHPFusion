<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_articles_include_button.php
| Author: Robert Gaudyn (Wooya)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
if (db_exists(DB_ARTICLES)) {
    $form_elements['articles']['enabled'] = array("datelimit", "fields1", "fields2", "fields3", "sort", "order1", "order2", "chars");
    $form_elements['articles']['disabled'] = array();
    $form_elements['articles']['display'] = array();
    $form_elements['articles']['nodisplay'] = array();
    $radio_button['articles'] = form_checkbox('stype', fusion_get_locale('a400', LOCALE.LOCALESET."search/articles.php"), $_GET['stype'],
                                        array(
                                            'type'      => 'radio',
                                            'value'     => 'articles',
                                            'reverse_label' => TRUE,
                                            'onclick' => 'display(this.value)',
                                            'input_id' => 'articles'
                                          )
                              );
}
