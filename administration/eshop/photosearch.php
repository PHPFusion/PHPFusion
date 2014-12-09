<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: customersearch.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }
echo "<div class='clear'></div>";
$search = stripinput($_POST['psrchtext']);
cleanurl($search);
if (!preg_match("/^[-0-9A-Z_@\s]+$/i", $search)) 
{ 
echo "<br /><div class='admin-message'>".$locale['SRCH159']."</div><br />";
} else {
echo "<br />";
if($search != "" && strlen($search) < 1)
   {
echo "<br /><div class='admin-message'><center><b>".$locale['SRCH156']."</b><br />".$locale['SRCH151']."</center></div><br />";

} else {

$search_text=ltrim($search);
$search_text=rtrim($search_text);
$q = "";
$kt = "";
$val = "";
$kt = explode(" ",$search_text);
while(list($key,$val)=each($kt)){
if($val<>" " and strlen($val) > 0){ $q.= " title like '%$val%' or artno like '%$val%' or sartno like '%$val%' or id like '%$val%' or ";}
}
$q=substr($q,0,(strlen($q)-3));

$result = dbquery("SELECT id,cid,title,artno,sartno,thumb FROM ".DB_ESHOP." WHERE ".$q ." ORDER BY title ASC LIMIT 50");


}
add_to_title(" Customer Search");

if (dbrows($result) != 0) {
$numRecords = dbrows($result);
echo "<br /><div class='admin-message'><center>".$locale['SRCH152']." <b><font color='red'>".$numRecords."</b></font> ".$locale['SRCH153']."</center></div><br />";

	$counter = 0; $k = ($_GET['rowstart'] == 0 ? 1 : $_GET['rowstart'] + 1);
	echo "<table cellpadding='0' cellspacing='1' width='100%'>\n<tr>\n";
	while ($data = dbarray($result)) {

		if ($counter != 0 && ($counter % $albumthumbs_per_row == 0)) echo "</tr>\n<tr>\n";
		echo "<td align='center' valign='top' class='tbl'>\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=photos&ealbum_id=".$data['id']."'><b>".$data['title']."</b><br />\n";
		if ($data['thumb'] && file_exists(ESHPHOTOS.$data['thumb'])){
			echo "<img src='".ESHPHOTOS.$data['thumb']."' alt='' border='0' width='100' height='100'>";
		} else {
			echo $ESHPALBUMS['462'];
		}
		echo "<br /><a href='".FUSION_SELF.$aidlink."&amp;a_page=Main&amp;action=edit&amp;id=".$data['id']."'>".$ESHPALBUMS['469']."</a> &middot;\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=photos&amp;ealbum_id=".$data['id']."'>".$locale['ESHPHOTOS107']."</a> &middot;\n";
		echo "<br /><br />\n";
		echo $ESHPALBUMS['466'].dbcount("(photo_id)", "".DB_ESHOP_PHOTOS."", "album_id='".$data['id']."'")."<br />\n";
		echo "</td>\n";
		$counter++; $k++;
	}
echo "</tr>\n</table>\n";
} else {
echo "<br /><div class='admin-message'><center>".$locale['SRCH155']."</center></div><br />";
}
}
echo "<div style='float:left;margin-top 15px;padding:10px;'><a class='eshpbutton ".$settings['eshop_return_color']."' href='javascript:history.back(-1)'>&laquo; ".$locale['ESHP030']."</a></div>";
echo '<div style="clear:both"></div>';
?>