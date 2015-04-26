<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2014 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Septenary Theme
| Filename: content.php
| Version: 1.00
| Author: PHP-Fusion Mods UK
| Developer & Designer: Craig, Hien
| Site: http://www.phpfusionmods.co.uk
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
open_grid('section-3', 1);
echo AU_CENTER ? "<div class='au-content'>".AU_CENTER."</div>\n" : '';
echo "<div class='row'>\n";
echo "<div class='hidden-xs col-sm-3 col-md-3 col-lg-3 leftbar'>\n";
echo RIGHT.LEFT;
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9 main-content'>\n";
// Get all notices, we also include notices that are meant to be displayed on all pages
$notices = getNotices(array('all', FUSION_SELF));
echo renderNotices($notices);
echo U_CENTER;
echo CONTENT;
echo L_CENTER;
echo "</div>\n";
echo BL_CENTER ? "<div class='bl-content'>".BL_CENTER."</div>\n" : '';
echo "</div>\n";
close_grid(1);
?>