<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_faqs_include_button.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

include LOCALE.LOCALESET."search/faqs.php";

$form_elements['faqs']['enabled'] = array("fields1", "fields2", "fields3", "order1", "order2");
$form_elements['faqs']['disabled'] = array("datelimit", "sort", "chars");
$form_elements['faqs']['display'] = array();
$form_elements['faqs']['nodisplay'] = array();

$radio_button['faqs'] = "<label><input type='radio' name='stype' value='faqs'".($_GET['stype'] == "faqs" ? " checked='checked'" : "")." onclick=\"display(this.value)\" /> ".$locale['fq400']."</label>";
?>