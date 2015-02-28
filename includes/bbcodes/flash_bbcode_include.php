<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: flash_bbcode_include.php
| Author: Wooya
| Improoved by: jantom
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
   
$text = preg_replace('#\[flash width=([0-9]*?) height=([0-9]*?)\]([^\s\'\";\+]*?)(\.swf)\[/flash\]#si', '<object type=\'application/x-shockwave-flash\' data=\''.INCLUDES.'bbcodes/flash/flash.swf?path=\3\4\' width=\'\1\' height=\'\2\'><param name=\'movie\' value=\''.INCLUDES.'bbcodes/flash/flash.swf?path=\3\4\'/><img src=\''.INCLUDES.'bbcodes/flash/noflash.gif\' width=\'80\' height=\'60\' alt=\'Flash not found \' /></object>', $text);  

?>

