<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_downloads_include_button.php
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

include LOCALE.LOCALESET."search/downloads.php";

$form_elements['downloads']['enabled'] = array("datelimit", "fields1", "fields2", "fields3", "sort", "order1", "order2", "chars");
$form_elements['downloads']['disabled'] = array();
$form_elements['downloads']['display'] = array();
$form_elements['downloads']['nodisplay'] = array();

$radio_button['downloads'] = "<label><input type='radio' name='stype' value='downloads'".($_GET['stype'] == "downloads" ? " checked='checked'" : "")." onclick=\"display(this.value)\" /> ".$locale['d400']."</label>";
?>