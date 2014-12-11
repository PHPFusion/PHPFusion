<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop.php
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
require_once "../maincore.php";
require_once THEMES."templates/header.php";
if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
if (isset($_POST['FilterSelect']) && !isnum($_POST['FilterSelect'])) die("Denied");
include INCLUDES."eshop_functions_include.php";


if (iMEMBER) { $username=$userdata['user_id']; } else { $username=$_SERVER['REMOTE_ADDR']; }

opentable($locale['ESHP001']);
buildeshopheader();
add_to_title(" - ".$locale['ESHCMP100']."");
buildfilters();

//campaigns view start
$result = dbquery("select * FROM ".DB_ESHOP." WHERE active = '1' AND campaign='1' AND ".groupaccess('access')." ORDER BY ".$filter."");
$rows = dbrows($result);
if ($rows) {
$result = dbquery("select * FROM ".DB_ESHOP." WHERE active = '1' AND campaign='1' AND ".groupaccess('access')." ORDER BY ".$filter." LIMIT ".$_GET['rowstart'].",".$settings['eshop_noppf']."");
	$counter = 0; 
	echo "<table cellpadding='0' cellspacing='0' width='100%' align='center'><tr>\n";
	while ($data = dbarray($result)) {
	if ($counter != 0 && ($counter % $settings['eshop_ipr'] == 0)) echo "</tr>\n<tr>\n";
    echo "<td align='center' class='tbl'>\n";
	eshopitems();
	echo "</td>\n";
	$counter++;
}
	echo "</tr>\n</table>\n";
	if ($rows > $settings['eshop_noppf']) echo "<div align='center' style='margin-top:5px;'>\n".makeeshoppagenav($_GET['rowstart'],$settings['eshop_noppf'],$rows,3,FUSION_SELF."?".(isset($_COOKIE['Filter']) ? "FilterSelect=".$_COOKIE['Filter']."&amp;" : "" )."")."\n</div>\n";

} else {
	echo '<div style="clear:both"></div>';
	echo "<br /><div class='admin-message'>".$locale['ESHCMP101']."</div>\n";
}


echo '<div style="float:right; padding-left:5px;"><br />
eShop <!-- COPYRIGHT: UNAUTHORIZED REMOVAL WILL VIOLATE LICENSE, To purchase a legal Copyright removal please visit http://www.venue.nu/eshop/eshop.php and buy a CRL for the product -->  <a target="_blank" href="http://www.venue.nu" title="Venue"> &copy; 2013-'.date('Y').' </a> </div>';
echo '<div style="clear:both"></div>';
closetable();

//convert guest shopping to member when they visit eshop, this check is also made in the checkout.
if (iMEMBER) {
$usercartchk = dbarray(dbquery("SELECT puid FROM ".DB_ESHOP_CART." WHERE puid = '".$_SERVER['REMOTE_ADDR']."' LIMIT 0,1"));
if ($usercartchk['puid']) {
dbquery("UPDATE ".DB_ESHOP_CART." SET puid = '".$userdata['user_id']."' WHERE puid = '".$_SERVER['REMOTE_ADDR']."'");
 }
}
//Sanitize the cart from 1 month old orders.
dbquery("DELETE FROM ".DB_ESHOP_CART." WHERE cadded < ".time()."-2592180");
require_once THEMES."templates/footer.php";
?>
