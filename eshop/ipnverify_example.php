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

} else if (strcmp ($res, "INVALID") == 0) {

	$customer = stripinput($_POST['custom']);
	$pm_subject = "Failed verification";
    $pm_message = "The verification for your payment failed.\n Please write to management@php-fusion.co.uk for clarifications.\n If your payment went thru on your side, we will need transaction ID and your full name.\n You can not reply to this automated message.\n\n Regards,\n Autobot";
	dbquery("INSERT INTO ".DB_MESSAGES." (message_id, message_to, message_from, message_subject, message_message, message_smileys, message_read, message_datestamp, message_folder) VALUES('', '".$customer."', '15756', '$pm_subject', '$pm_message', 'n', '0', '".time()."', '0')");

//check the order
$odata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE ouid='".$customer."' ORDER BY oid DESC LIMIT 0,1"));

//send us a message
require_once INCLUDES."sendmail_include.php";

//Send us a message about it
$subject = "OrderID ".$odata['oid']." Failed verification";
$toemail = "management@php-fusion.co.uk";
$toname = "PHP-Fusion Management";
$message = $odata['oorder'];
sendemail($toname,$toemail,$settings['sitename'],$settings['siteemail'],$subject,$message,$type="html");
	}
}
fclose ($fp);
 }
} else {
echo "Not a valid PayPal source";
if (iMEMBER) {
	$pm_subject = "Invalid source";
	$pm_message = "The call to our verification file was not valid.\n Please write to management@php-fusion.co.uk for clarifications.\n If your payment went thru on your side, we will need transaction ID and your full name\n You can not reply to this automated message.\n\n Regards,\n Autobot";
	dbquery("INSERT INTO ".DB_MESSAGES." (message_id, message_to, message_from, message_subject, message_message, message_smileys, message_read, message_datestamp, message_folder) VALUES('', '".$userdata['user_id']."', '15756', '$pm_subject', '$pm_message', 'n', '0', '".time()."', '0')");
 }
}
?>