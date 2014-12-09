<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: personalfiles.php
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

$puserid = "";
$pdomain = "";

opentable("Payment completed");

include INFUSIONS."personal_files/infusion_db.php";

if (isset($_POST['save_domain']))  {
$puserid = $userdata['user_id'];
$pdomain = stripinput($_POST['domain']);
$result = dbquery("INSERT INTO ".DB_PFILES_DB." VALUES('', '$puserid','$pdomain','','','','','')");

$pm_subject = "Program preparation";
$pm_message = "A costumer need to have his program delivered.";
$result1 = dbquery("INSERT INTO ".DB_MESSAGES." (message_id, message_to, message_from, message_subject, message_message, message_smileys, message_read, message_datestamp, message_folder) VALUES('', '1', '".$userdata['user_id']."', '$pm_subject', '$pm_message', 'n', '0', '".time()."', '0')");

require_once INCLUDES."sendmail_include.php";
$name = $settings['sitename'];
$email = $settings['siteemail'];
$subject= "Program preparation";
$message = "A costumer need to have his program delivered.";
sendemail($name,$email,$name,$settings['siteemail'],$subject,$message,$type="html");

echo "<div class='admin-message'><center><br /> Thank you, please allow a maximum of 5 workingdays before your program preparation is completed. <br /> Once the process is done you will be informed and you will always have your personal download available here on ".$settings['sitename']." <br /> 
</div>";
} else {
echo "<div class='admin-message'><center><br /> Thank you for your purchase, <br /> in order to prepare your download we still need to connect your software to the domain where it will reside. </center><br />
<form name='inputform' method='post' action='".FUSION_SELF."' enctype='multipart/form-data'>
Enter your domain name : <input type='text' name='domain' class='textbox' style='width:200px;'> <input type='submit' name='save_domain' value='- Submit -' class='button'>
</form></div>";
}
closetable();
require_once THEMES."templates/footer.php";
?>