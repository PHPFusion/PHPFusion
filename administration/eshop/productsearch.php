<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
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

$result = dbquery("SELECT id,cid,title,artno,sartno FROM ".DB_ESHOP." WHERE ".$q ." ORDER BY title ASC LIMIT 50");


}
add_to_title(" Customer Search");

if (dbrows($result) != 0) {
$numRecords = dbrows($result);
echo "<br /><div class='admin-message'><center>".$locale['SRCH152']." <b><font color='red'>".$numRecords."</b></font> ".$locale['SRCH153']."</center></div><br />";


echo "<table align='center' cellspacing='4' cellpadding='0' class='tbl-border' width='99%'><tr>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPPRO172']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPPRO173']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPPRO174']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPPRO176']."</b></td>
</tr>\n";

while ($data = dbarray($result)) {
echo "<tr style='height:20px;' onMouseOver=\"this.className='tbl2'\" onMouseOut=\"this.className='tbl1'\">";
echo "<td width='1%' align='left'><a href='".FUSION_SELF.$aidlink."".($data['cid'] ? "&amp;category=".$data['cid']."" : "")."&amp;a_page=Main&action=edit&id=".$data['id']."'><b>".$data['title']."</b></a></td>\n";
echo "<td width='1%' align='center'> ".($data['artno'] !== '' ? "".$data['artno']."" : "".$data['id']."")." </td>";
echo "<td width='1%' align='center'> ".($data['sartno'] !== '' ? "".$data['sartno']."" : "N/A")." </td>";
echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."".($data['cid'] ? "&amp;category=".$data['cid']."" : "")."&amp;action=delete&id=".$data['id']."' onClick='return confirmdelete();'><img src='".BASEDIR."eshop/img/remove.png' border='0' height='20' style='vertical-align:middle;'  alt='' /></a></td></tr>";
echo "</td>";
}

	echo '</table><div style="clear:both"></div>';
} else {
echo "<br /><div class='admin-message'><center>".$locale['SRCH155']."</center></div><br />";
}
}
echo "<div style='float:left;margin-top 15px;padding:10px;'><a class='eshpbutton ".$settings['eshop_return_color']."' href='javascript:history.back(-1)'>&laquo; ".$locale['ESHP030']."</a></div>";
echo '<div style="clear:both"></div>';
?>