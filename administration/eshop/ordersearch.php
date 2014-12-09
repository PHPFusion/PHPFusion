<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: ordersearch.php
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
$search = stripinput($_POST['osrchtext']);
cleanurl($search);
if (!preg_match("/^[-0-9A-Z_@\s]+$/i", $search)) 
{ 
echo "<br /><div class='admin-message'>".$locale['SRCH159']."</div><br />";
} else {
echo "<br />";
if($search != "" && strlen($search) < 1)
   {
echo "<br /><div class='admin-message'><center><b>".$locale['SRCH156']."</b><br />".$locale['SRCH151']."</center></div><br />";
exit;
} else {

$search_text=ltrim($search);
$search_text=rtrim($search_text);
$q = "";
$kt = "";
$val = "";
$kt = explode(" ",$search_text);
while(list($key,$val)=each($kt)){
if($val<>" " and strlen($val) > 0){ $q.= " oid like '%$val%' or oname like '%$val%' or ";}
}
$q=substr($q,0,(strlen($q)-3));

$result = dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE ".$q ." ORDER BY oid ASC LIMIT 50");


}
add_to_title(" Order Search");

if (dbrows($result) != 0) {
$numRecords = dbrows($result);
echo "<br /><div class='admin-message'><center>".$locale['SRCH152']." <b><font color='red'>".$numRecords."</b></font> ".$locale['SRCH153']."</center></div><br />";


echo "<br /><table align='center' cellspacing='4' cellpadding='0' class='tbl-border' width='99%'><tr>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP317']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP318']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP319']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP320']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP321']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP322']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP323']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP324']."</b></td>
</tr>\n";

while ($data = dbarray($result)) {
echo "<tr style='height:20px;' onMouseOver=\"this.className='tbl2'\" onMouseOut=\"this.className='tbl1'\">";
echo "<td width='1%' align='center'> <a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;vieworder&amp;orderid=".$data['oid']."'><b>".$data['oid']."</b></a></td>\n";
echo "<td width='1%' align='center'> <a href='".FUSION_SELF.$aidlink."&amp;a_page=customers&amp;step=edit&amp;cuid=".$data['ouid']."'>".$data['oname']."</a></td>";
echo "<td width='1%' align='center'>".$data['ototal']." ".$settings['eshop_currency']."</a></td>";
echo "<td width='1%' align='center'>".($data['opaid'] =="1" ? "<img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/bullet_green.png' alt'' />" : "<img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/bullet_red.png' alt'' />")."</td>";
echo "<td width='1%' align='center'>".($data['ocompleted'] =="1" ? "<img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/bullet_green.png' alt'' />" : "<img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/bullet_red.png' alt'' />")."</td>";
echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;vieworder&amp;orderid=".$data['oid']."'><img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/orderlist.png' alt='' border='0' /></a></td>";
echo "<td width='1%' align='center'><a class='printorder' href='".ADMIN."eshop/printorder.php".$aidlink."&amp;orderid=".$data['oid']."'><img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/print.png' alt='' border='0' /></a></td>";
echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;step=delete&amp;orderid=".$data['oid']."'  onClick='return confirmdelete();'><img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/remove.png' border='0' alt='Remove' /></a></td>";
}
echo "</tr></table>\n"; 
	
	echo '<div style="clear:both"></div>';
} else {
echo "<br /><div class='admin-message'><center>".$locale['SRCH155']."</center></div><br />";
}
}
echo "<div style='float:left;margin-top 15px;padding:10px;'><a class='eshpbutton ".$settings['eshop_return_color']."' href='javascript:history.back(-1)'>&laquo; ".$locale['ESHP030']."</a></div>";

?>