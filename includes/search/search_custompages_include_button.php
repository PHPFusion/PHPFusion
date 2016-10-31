<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_custompages_include_button.php
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
$form_elements['custompages']['enabled'] = array("fields1", "fields2", "fields3", "order1", "order2", "chars");
$form_elements['custompages']['disabled'] = array("datelimit", "sort");
$form_elements['custompages']['display'] = array();
$form_elements['custompages']['nodisplay'] = array();
$radio_button['custompages'] = form_checkbox('stype', fusion_get_locale('c400', LOCALE.LOCALESET."search/custompages.php"), $_GET['stype'],
                               					array(
                                   					'type' 			=> 'radio',
                                   					'value' 		=> 'custompages',
                                   					'reverse_label' => TRUE,
                               						)
							            		);
