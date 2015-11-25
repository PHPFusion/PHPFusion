<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: debonair/include/about_us.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

echo "<h3 class='icon3 margin'>".$locale['debonair_0410']."</h3>\n";
echo "<address>\n";
echo "<strong>".fusion_get_settings("sitename")."</strong>\n<br/>\n";
echo fusion_get_settings("description");
echo "<br/>".$locale['debonair_0411']." ".fusion_get_settings("siteemail");
echo "</address>\n";