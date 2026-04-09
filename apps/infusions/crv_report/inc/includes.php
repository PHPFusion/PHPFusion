<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright © 2002 - 2011 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: includes.php
| Author: PHP-Fusion Addons Team
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

       if (!defined("CRVR")) { define("CRVR", INFUSIONS."crv_report/"); }
       if (!defined("CRVR_INC")) { define("CRVR_INC", CRVR."inc/"); }
    
if (file_exists(CRVR."locale/".$settings['locale'].".php")) { include CRVR."locale/".$settings['locale'].".php"; } else { include CRVR."locale/English.php"; }

$forum_id = "113";
$autobot = "15756";
$req = "<span style='color:#FF0000;'><strong>*</strong></span>\n";


?>