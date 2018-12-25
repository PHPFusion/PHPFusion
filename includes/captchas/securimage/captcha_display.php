<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: captcha_display.php
| Author: Hans Kristian Flaatten
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

echo "<div style='width:170px; margin:0 auto;'>";

// Display Captcha
echo "<img id='captcha' src='".INCLUDES."captchas/securimage/securimage_show.php' alt='".$locale['global_600']."' align='left' />\n";

// Display Audio Button
echo "<a href='".INCLUDES."captchas/securimage/securimage_play.php'>";
echo "<img src='".INCLUDES."captchas/securimage/images/audio_icon.gif' alt='' align='top' class='tbl-border' style='margin-bottom:1px' /></a><br />\n";

// Display New Captcha Button
echo "<a href='#' onclick=\"document.getElementById('captcha').src = '".INCLUDES."captchas/securimage/securimage_show.php?sid=' + Math.random(); return false\">";
echo "<img src='".INCLUDES."captchas/securimage/images/refresh.gif' alt='' align='bottom' class='tbl-border' /></a>\n";

echo "</div>\n";
?>