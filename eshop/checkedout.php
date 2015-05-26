<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: checkedout.php
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
require_once dirname(__FILE__)."../../maincore.php";
require_once THEMES."templates/header.php";
//if (!preg_match('/checkout.php/i',$_SERVER['HTTP_REFERER'])) { redirect(BASEDIR."eshop.php"); exit; }
include INCLUDES."eshop_functions_include.php";
add_to_title($locale['ESHPCHK159']);
opentable($locale['ESHPCHK159']);

if (iMEMBER) { $username = $userdata['user_id']; } else { $username = $_SERVER['REMOTE_ADDR']; }
//buildeshopheader();

$odata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE ouid='".$username."' ORDER BY oid DESC LIMIT 0,1"));

if ($odata) {
//handle payments
$pdata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_PAYMENTS." WHERE pid='".$odata['opaymethod']."'"));
if ($pdata['code']) {
	ob_start();
	eval("?>".stripslashes($pdata['code'])."<?php ");
	$custompage = ob_get_contents();
	ob_end_clean();
	echo $custompage;
} else if ($pdata['cfile']) {
	include SHOP."paymentscripts/".$pdata['cfile'];
} else {
	echo "<div class='admin-message' align='center' style='margin-top:5px;'>".$locale['ESHPCHK149']."</div>\n";
}
	echo $odata['oorder'];
} else {
	echo "<div class='admin-message' align='center' style='margin-top:5px;'>".$locale['ESHPCHK150']."</div>\n";
}

//Ok the order is stored and handled let´s send a confirmation to the customer, PM and a mail to the webmaster about this order.

//send a PM confirmation to site superadmin
$omessage = "[url=".$settings['siteurl']."administration/eshop/msghandler.php?id=".$odata['oid']."]".$locale['ESHP306']." : ".$odata['oid']." [/url] \n\n [url=".$settings['siteurl']."eshop/administration/msghandler.php]".$locale['ESHP209']."[/url] \n\n ".$locale['ESHP307']." ".$odata['oname']."\n\n";
dbquery("INSERT INTO ".DB_MESSAGES." ( message_id , message_to , message_user, message_from , message_subject , message_message , message_smileys , message_read , message_datestamp , message_folder )VALUES ('', '1', '".(iMEMBER ? $userdata['user_id'] : 1)."', '".(iMEMBER ? $userdata['user_id'] : 1)."', '".$locale['ESHP306']." : ".$odata['oid']."', '".$omessage."', 'y', '0', '".time()."' , '0');");

require_once INCLUDES."sendmail_include.php";

//send a mail confirmation to site email
$subject = "".$locale['ESHP306']." : ".$odata['oid']."";
$message = "\n<a href='".$settings['siteurl']."administration/eshop/msghandler.php?id=".$odata['oid']."'>".$locale['ESHP306']." : ".$odata['oid']." </a> <br /> <a href='".$settings['siteurl']."administration/eshop/msghandler.php'>".$locale['ESHP209']."</a> <br /> ".$locale['ESHP307']." ".$odata['oname']."\n\n";
sendemail($settings['sitename'],$settings['siteemail'],$settings['sitename'],$odata['oemail'],$subject,$message,$type="html");

//send a mail confirmation of the whole order to the customer
sendemail($odata['oname'],$odata['oemail'],$settings['sitename'],$settings['siteemail'],$locale['ESHPI103'],$odata['oorder'],$type="html");

closetable();
require_once THEMES."templates/footer.php";
