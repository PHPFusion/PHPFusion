<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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

echo "<div style='width:330px; margin:0 auto;'>";

// Display Capthca
echo "<img id='captcha' src='".INCLUDES."captchas/securimage2/securimage_show.php' alt='".$locale['global_600']."' align='left' />\n";

//echo "<a href='".INCLUDES."captchas/securimage2/securimage_play.php'>";
//echo "<img src='".INCLUDES."captchas/securimage2/images/audio_icon.gif' alt='' align='top' class='tbl-border' style='margin-bottom:1px' /></a><br />\n";

// Display Audio Button
echo "<object type='application/x-shockwave-flash' data='".INCLUDES."captchas/securimage2/securimage_play.swf?audio=".INCLUDES."captchas/securimage2/securimage_play.php&amp;bgColor1=#fff&amp;bgColor2=#fff&amp;iconColor=#777&amp;borderWidth=1&amp;borderColor=#000' height='23' width='23'>";
echo "<param name='movie' value='".INCLUDES."captchas/securimage2/securimage_play.swf?audio=".INCLUDES."captchas/securimage2/securimage_play.php&amp;bgColor1=#fff&amp;bgColor2=#fff&amp;iconColor=#777&amp;borderWidth=1&amp;borderColor=#000' />\n";
echo "</object><br />";

// Display New Capthca Button
echo "<a href='#' onclick=\"document.getElementById('captcha').src = '".INCLUDES."captchas/securimage2/securimage_show.php?sid=' + Math.random(); return false\">";
echo "<img src='".INCLUDES."captchas/securimage2/images/refresh.gif' alt='' align='bottom' class='tbl-border' /></a>\n";

if (isset($this)) { $this->setRequiredJavaScript("captcha_code", $locale['u195']); }

echo "</div>\n";
?>