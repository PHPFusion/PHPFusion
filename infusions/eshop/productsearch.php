<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: productsearch.php
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

if (isset($_POST['FilterSelect']) && !isnum($_POST['FilterSelect'])) die("Denied");
if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }

include INCLUDES."eshop_functions_include.php";

if (iMEMBER) { $username=$userdata['user_id']; } else { $username=$_SERVER['REMOTE_ADDR']; }

opentable($locale['SRCH163']);

buildeshopheader();

$search = stripinput(trim($_REQUEST['esrchtext']));
if (!preg_match("/^[-0-9A-Z\xe6\xc6\xf8\xd8\xe5\xc5\xf6\xd6\xe4\xc4_@\s]+$/i", $search)) { 

	echo "<br /><div class='admin-message'>".$locale['SRCH159']."</div><br />";
} else {
echo "<br />";
if($search != "" && strlen($search) <= 2)  {
	echo "<br /><div class='admin-message'><center><b>".$locale['SRCH156']."</b><br />".$locale['SRCH151']."</center></div><br />";
closetable();
require_once THEMES."templates/footer.php";
exit;
} 

add_to_title(" - ".$locale['SRCH163']."");
buildfilters();

$result1st = dbquery("SELECT * FROM ".DB_ESHOP." WHERE title LIKE '%$search%' ORDER BY ".$filter." ");

if (dbrows($result1st) != 0) {
$numRecords = dbrows($result1st);
echo "<div class='clear'><div class='admin-message'><center>".$locale['SRCH152']." <b><font color='red'>".$numRecords."</b></font> ".$locale['SRCH153']."</center></div><br />";
$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE title LIKE '%$search%' ORDER BY ".$filter." LIMIT ".$_GET['rowstart'].",".$eshop_settings['eshop_nopp']."");
	$counter = 0; 
	echo "<table cellpadding='0' cellspacing='0' width='100%' style='margin: 0 auto;'><tr>\n";
	while ($data = dbarray($result)) {
		if ($counter != 0 && ($counter % $eshop_settings['eshop_ipr'] == 0)) echo "</tr>\n<tr>\n";
      		echo "<td align='center' valign='top' class='tbl'>\n";
		eshopitems();
		echo "</td>\n";
		$counter++;
		}
		echo "</tr>\n</table>\n";
if ($numRecords > $eshop_settings['eshop_nopp']) echo "<div align='center' style='margin-top:5px;'>\n".makeeshoppagenav($_GET['rowstart'],$eshop_settings['eshop_nopp'],$numRecords,3,FUSION_SELF."?".(isset($_COOKIE['Filter']) ? "FilterSelect=".$_COOKIE['Filter']."&amp;esrchtext=".$search."&amp;" : "esrchtext=".$search."&amp" )."")."\n</div>\n";
} else {
	echo "<br /><div class='clear'></div><div class='admin-message'><center>".$locale['SRCH155']."</center></div><br />";
 }
}
echo "<div style='float:left;margin-top 15px;padding:10px;'><a class='eshpbutton ".$eshop_settings['eshop_return_color']."' href='javascript:history.back(-1)'>&laquo; Return</a></div>";
closetable();
require_once THEMES."templates/footer.php";
