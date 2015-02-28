<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_members_include_button.php
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

include LOCALE.LOCALESET."search/members.php";

$form_elements['members']['enabled'] = array("order1", "order2");
$form_elements['members']['disabled'] = array("datelimit", "fields1", "fields2", "fields3", "sort", "chars");
$form_elements['members']['display'] = array();
$form_elements['members']['nodisplay'] = array();

$radio_button['members'] = "<label><input type='radio' name='stype' value='members'".($_GET['stype'] == "members" ? " checked='checked'" : "")." onclick=\"display(this.value)\" /> ".$locale['m400']."</label>";
?>