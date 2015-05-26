<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: crl.php
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
require_once "../../../maincore.php";
require_once THEMES."templates/header.php";


if(!iMEMBER){ die("Denied"); exit;}


include INFUSIONS."crl_list/infusion_db.php";

$puserid = "";
$pdomain = "";

opentable("Payment completed");

if (isset($_POST['save_domain']))  {
$puserid = $userdata['user_id'];
$pdomain = stripinput($_POST['domain']);
$odata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE ouid='".$userdata['user_id']."' ORDER BY oid DESC LIMIT 0,1"));

$result = dbquery("INSERT INTO ".DB_CRL." VALUES('', '$puserid','$pdomain','','','".time()."','','".$odata['oid']."')");
$pm_subject = "Copyright Removal License";
$pm_message = "A copyright removal license have been sold";
$result1 = dbquery("INSERT INTO ".DB_MESSAGES." (message_id, message_to, message_from, message_subject, message_message, message_smileys, message_read, message_datestamp, message_folder) VALUES('', '1', '".$userdata['user_id']."', '$pm_subject', '$pm_message', 'n', '0', '".time()."', '0')");

require_once INCLUDES."sendmail_include.php";
$name = $settings['sitename'];
$email = $settings['siteemail'];
$subject= "CRL preparation";
$message = "A copyright removal license have been sold";
sendemail($name,$email,$name,$settings['siteemail'],$subject,$message,$type="html");

echo "<div class='admin-message'><center><br /> Thank you for your support. <br /> You can now legally remove ".$settings['sitename']."´s Copyright on your purchased product.<br /><br /></div>";

} else {
echo "<div class='admin-message'><center><br /> Thank you for your purchase, <br /> We still need to connect your CRL license to the domain where it will reside.<br /> Each CRL licenses are domain bound. </center><br />
<form name='inputform' method='post' action='".FUSION_SELF."' enctype='multipart/form-data'>
Enter your domain name : <input type='text' name='domain' class='textbox' style='width:200px;'> <input type='submit' name='save_domain' value='- Submit -' class='button'>
</form></div>";
}

closetable();
require_once THEMES."templates/footer.php";
