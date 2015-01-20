<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: ipnverify.php
| Author: J.Falk (Domi)
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
if (preg_match('~^(?:.+[.])?paypal[.]com$~', gethostbyaddr($_SERVER['REMOTE_ADDR'])) > 0) {
// came from paypal.com continue..

// read the data send by PayPal
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
}
  
// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
  
$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
if (!$fp) {
// HTTP ERROR
} else {
        fputs ($fp, $header . $req);
        while (!feof($fp)) {
        $res = fgets ($fp, 1024);
        if (strcmp ($res, "VERIFIED") == 0) {


	$customer = stripinput($_POST['custom']);

//check the order
$odata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE ouid='".$customer."' ORDER BY oid DESC LIMIT 0,1"));

//Mark it as paied
dbquery("UPDATE ".DB_ESHOP_ORDERS." SET opaid='1' WHERE oid='".$odata['oid']."'");

//clear the cart.
dbquery("DELETE FROM ".DB_ESHOP_CART." WHERE puid ='".$customer."'");

} else if (strcmp ($res, "INVALID") == 0) {

$customer = stripinput($_POST['custom']);

//check the order
$odata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE ouid='".$customer."' ORDER BY oid DESC LIMIT 0,1"));

//Send a message about the failed order
require_once INCLUDES."sendmail_include.php";

$subject = "OrderID ".$odata['oid']." Failed verification";
$toemail = $settings['siteemail'];
$toname = $settings['sitename'];
$message = $odata['oorder'];
sendemail($toname,$toemail,$settings['sitename'],$settings['siteemail'],$subject,$message,$type="html");

// Adjust stock and sell count.
$items = $odata['oitems'];
$items = explode(".", substr($items, 1));
    for ($i = 0;$i < count($items);$i++)  {
//update sellcount
dbquery("UPDATE ".DB_ESHOP." SET sellcount=sellcount-1 WHERE id = '".$items[$i]."'");
//update stock count. 
dbquery("UPDATE ".DB_ESHOP." SET instock=instock+1 WHERE id = '".$items[$i]."'");
} 
//Remove the order
$result = dbquery("DELETE FROM ".DB_ESHOP_ORDERS." WHERE oid='".$odata['oid']."'");

	}
}
fclose ($fp);
}
 
} else {

if (iMEMBER) {
	$pm_subject = "Invalid source";
	$pm_message = "The call to our verification file was not valid.\n Please write to ".$settings['siteemail']." for clarifications.\n If your payment went thru on your side, we will need transaction ID and your full name\n You can not reply to this automated message.\n\n Regards,\n Admin";
	dbquery("INSERT INTO ".DB_MESSAGES." (message_id, message_to, message_from, message_subject, message_message, message_smileys, message_read, message_datestamp, message_folder) VALUES('', '".$userdata['user_id']."', '1', '$pm_subject', '$pm_message', 'n', '0', '".time()."', '0')");
 }
}
?>