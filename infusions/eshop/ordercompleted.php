<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: ordercompleted.php
| Author: Joakim Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once dirname(__FILE__)."../maincore.php";
require_once THEMES."templates/header.php";
include INCLUDES."eshop_functions_include.php";

add_to_title($locale['ESHPCHK159']);
opentable($locale['ESHPCHK159']);

if (iMEMBER) { $username = $userdata['user_id']; } else { $username = $_SERVER['REMOTE_ADDR']; }
buildeshopheader();

$odata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE ouid='".$username."' ORDER BY oid DESC LIMIT 0,1"));

echo "<center><div style = 'font-family : Verdana, Arial, Helvetica, sans-serif;
	margin-top:20px;
	text-align: center;
	width: 350px;
	font-size: 11px;
	font-weight: bold;
	padding: 5px 0 5px 20px;
	margin-bottom: 5px;
	color: #05B;
	border: 1px solid #9CC0EE;
	-webkit-border-bottom-right-radius: 5px;
	-webkit-border-bottom-left-radius: 5px;
	-moz-border-radius-bottomright: 5px;
	-moz-border-radius-bottomleft: 5px;
	border-bottom-right-radius: 5px;
	border-bottom-left-radius: 5px;
	-webkit-border-top-left-radius: 5px;
	-webkit-border-top-right-radius: 5px;
	-moz-border-radius-topleft: 5px;
	-moz-border-radius-topright: 5px;
	border-top-left-radius: 5px;
	border-top-right-radius: 5px;';> ".$locale['ESHPB104']." </div></center>";

if ($odata) {
echo $odata['oorder'];

//clear the cart.
dbquery("DELETE FROM ".DB_ESHOP_CART." WHERE puid ='".$username."'");
} else {
	echo "<div class='admin-message' align='center' style='margin-top:5px;'>".$locale['ESHPCHK150']."</div>\n";
}


closetable();
require_once THEMES."templates/footer.php";
