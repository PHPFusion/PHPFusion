<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_photos_include_button.php
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

include LOCALE.LOCALESET."search/photos.php";

$form_elements['photos']['enabled'] = array("datelimit", "fields1", "fields2", "fields3", "sort", "order1", "order2", "chars");
$form_elements['photos']['disabled'] = array();
$form_elements['photos']['display'] = array();
$form_elements['photos']['nodisplay'] = array();

$radio_button['photos'] = "<label><input type='radio' name='stype' value='photos'".($_GET['stype'] == "photos" ? " checked='checked'" : "")." onclick=\"display(this.value)\" /> ".$locale['p400']."</label>";
?>