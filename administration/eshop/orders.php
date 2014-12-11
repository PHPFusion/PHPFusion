<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: orders.php
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
if (isset($_GET['orderid']) && !isnum($_GET['orderid'])) die("Denied");

if (!isset($_GET['o_page'])){
$_GET['o_page'] = "orders";
}

if ($_GET['o_page'] == "orders"){
$tbl0 = "tbl1";
}else{
$tbl0 = "tbl2";
}
if ($_GET['o_page'] == "history"){
$tbl1 = "tbl1";
}else{
$tbl1 = "tbl2";
}
echo "<table align='center' cellspacing='1' cellpadding='0' class='tbl-border' width='100%'><tr>
<td align='center' class='".$tbl0."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders'>".$locale['ESHP301']."</a></td>
<td align='center' class='".$tbl1."' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=history'>".$locale['ESHP302']."</a></td>
</tr><tr><td align='left' class='tbl' colspan='2'>";


if (isset($_GET['osearch'])) {
include BASEDIR."eshop/ordersearch.php";
} else {

if ($_GET['o_page'] == "orders") {

if (isset($_GET['step']) && $_GET['step'] == "delete") {
$odata = dbarray(dbquery("SELECT oitems FROM ".DB_ESHOP_ORDERS." WHERE oid='".$_GET['orderid']."'"));

echo "<div class='admin-message' align='center' style='margin-top:5px;'>".$locale['ESHP303']."<br /></div>\n";
echo "<table align='center' cellspacing='2' cellpadding='2 width='99%'>";
$items = $odata['oitems'];
$items = explode(".", substr($items, 1));
    for ($i = 0;$i < count($items);$i++)  {
//update sellcount
dbquery("UPDATE ".DB_ESHOP." SET sellcount=sellcount-1 WHERE id = '".$items[$i]."'");
//update stock count. 
dbquery("UPDATE ".DB_ESHOP." SET instock=instock+1 WHERE id = '".$items[$i]."'");
echo "<tr><td class='tbl2' width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;a_page=Main&action=edit&id=".$items[$i]."'> ".$locale['ESHP304']." ".$items[$i]." </a> ".$locale['ESHP305']." </td></tr>";
} 
echo "</table>";
$result = dbquery("DELETE FROM ".DB_ESHOP_ORDERS." WHERE oid='".$_GET['orderid']."'");

//redirect(FUSION_SELF.$aidlink."&amp;a_page=orders");
} 

if (isset($_GET['updateorder'])) {
if (isset($_POST['ocompleted'])) { $ocompleted = stripinput($_POST['ocompleted']); }
if (isset($_POST['opaid'])) { $opaid = stripinput($_POST['opaid']); }
if (isset($_POST['oamessage'])) { $oamessage = stripinput($_POST['oamessage']); }
dbquery("UPDATE ".DB_ESHOP_ORDERS." SET opaid='$opaid',ocompleted='$ocompleted',oamessage='$oamessage' WHERE oid='".$_GET['orderid']."'");
redirect(FUSION_SELF.$aidlink."&amp;a_page=orders");
}


//view order
if (isset($_GET['vieworder'])) {
$odata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE oid='".$_GET['orderid']."' LIMIT 0,1"));
if ($odata) {
echo "<fieldset><legend align='left'>&nbsp; ".$locale['ESHP306']." : ".$odata['oid']." ".$locale['ESHP307']." ".$odata['oname']." - ".showdate("longdate", $odata['odate'])." &nbsp;</legend>";
echo "<br /><form name='inputform' method='post' action='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;updateorder&amp;orderid=".$_GET['orderid']."'>
<table align='center' cellspacing='2' cellpadding='2' width='99%'><tr>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP308']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP309']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHP310']."</b></td>
<td class='tbl2' width='1%' align='center'></td>
</tr><tr>\n";
echo "<td width='1%' align='center'><textarea name='oamessage' cols='2' rows='1' style='width:150px;' class='textbox'>".nl2br($odata['oamessage'])."</textarea></td>";
echo "<td width='1%' align='center'><select name='opaid' class='textbox'>
      <option value='1'".($odata['opaid'] == "1" ? " selected" : "").">".$locale['ESHP311']."</option>
      <option value=''".($odata['opaid'] == "" ? " selected" : "").">".$locale['ESHP312']."</option>
      </select> </td>\n";
echo "<td width='1%' align='center'><select name='ocompleted' class='textbox'>
      <option value='1'".($odata['ocompleted'] == "1" ? " selected" : "").">".$locale['ESHP311']."</option>
      <option value=''".($odata['ocompleted'] == "" ? " selected" : "").">".$locale['ESHP312']."</option>
      </select></td>";
echo "<td width='1%' align='center'><input name='items'  value='".$odata['oitems']."' type='hidden' /><input type='submit' value='".$locale['ESHP313']."' /></td>";
echo "</tr></table></form><br /></fieldset><br />";
echo $odata['oorder'];
echo "<br />";

echo "<div style='clear:both;'></div>";

echo "<div style='float:right;margin-top 15px;padding:10px;'><a class='printorder button' href='".ADMIN."eshop/printorder.php".$aidlink."&amp;orderid=".$_GET['orderid']."'>".$locale['ESHP314']."</a></div>";
echo "<div style='float:left;margin-top 15px;padding:10px;'><a class='".($settings['eshop_return_color'] =="default" ? "button" : "eshpbutton ".$settings['eshop_return_color']."")."' href='javascript:history.back(-1)'>&laquo; ".$locale['ESHP030']."</a></div>";

} else {
echo "<div class='admin-message' align='center' style='margin-top:5px;'>".$locale['ESHP315']."</div><br />\n";
}
} else {

if (!isset($_GET['sortby']) || !preg_match("/^[0-9A-Z]$/", $_GET['sortby'])) $_GET['sortby'] = "all";
$orderby = ($_GET['sortby'] == "all" ? "" : " AND oname LIKE '".$_GET['sortby']."%'");

echo "<fieldset><legend align='left'>&nbsp; <b> ".$locale['ESHP316']." </b> &nbsp;</legend>";
$result= dbquery("SELECT * FROM  ".DB_ESHOP_ORDERS." WHERE opaid = '1' AND ocompleted = '' ".$orderby." ORDER BY oid ASC");
$rows = dbrows($result);
if ($rows != 0) {
$result= dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE opaid = '1' AND ocompleted = '' ".$orderby." ORDER BY oid ASC  LIMIT ".$_GET['rowstart'].",15");

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
$usercheck= dbquery("SELECT user_id FROM  ".DB_USERS." WHERE user_id = '".$data['ouid']."'");
$urows = dbrows($usercheck);
if ($urows != 0) {
echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;a_page=customers&amp;step=edit&amp;cuid=".$data['ouid']."'>".$data['oname']."</a></td>";
} else {
echo "<td width='1%' align='center'>".$data['oname']."</td>";
}
echo "<td width='1%' align='center'>".$data['ototal']." ".$settings['eshop_currency']."</a></td>";
echo "<td width='1%' align='center'>".($data['opaid'] =="1" ? "<img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/bullet_green.png' alt'' />" : "<img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/bullet_red.png' alt'' />")."</td>";
echo "<td width='1%' align='center'>".($data['ocompleted'] =="1" ? "<img style='width:20px; height:20px;vertical-align:middle;'  src='".BASEDIR."eshop/img/bullet_green.png' alt'' />" : "<img style='width:20px; height:20px;vertical-align:middle;'  src='".BASEDIR."eshop/img/bullet_red.png' alt'' />")."</td>";
echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;vieworder&amp;orderid=".$data['oid']."'><img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/orderlist.png' alt='' border='0' /></a></td>";
echo "<td width='1%' align='center'><a class='printorder' href='".ADMIN."eshop/printorder.php".$aidlink."&amp;orderid=".$data['oid']."'><img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/print.png' alt='' border='0' /></a></td>";
echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;step=delete&amp;orderid=".$data['oid']."'  onClick='return confirmdelete();'><img style='width:20px; height:20px;vertical-align:middle;'  src='".BASEDIR."eshop/img/remove.png' border='0'  alt='Remove' /></a></td>";
}
echo "</tr></table>\n";
echo "<div align='center' style='margin-top:5px;'>".makePageNav($_GET['rowstart'],15,$rows,3,FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;sortby=".$_GET['sortby']."&amp;")."\n</div>\n";
} else {
echo "<center><br />\n ".$locale['ESHP325']." <br /><br />\n</center>\n";
}
echo "<br /></fieldset><br />";

echo "<fieldset><legend align='left'>&nbsp; <b> ".$locale['ESHP326']." </b> &nbsp;</legend>";
$result= dbquery("SELECT * FROM  ".DB_ESHOP_ORDERS." WHERE opaid = '' ".$orderby." ORDER BY oid ASC");
$rows = dbrows($result);
if ($rows != 0) {
$result= dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE opaid = '' ".$orderby." ORDER BY oid ASC  LIMIT ".$_GET['rowstart'].",15");
	
	
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
$usercheck= dbquery("SELECT user_id FROM  ".DB_USERS." WHERE user_id = '".$data['ouid']."'");
$urows = dbrows($usercheck);
if ($urows != 0) {
echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;a_page=customers&amp;step=edit&amp;cuid=".$data['ouid']."'>".$data['oname']."</a></td>";
} else {
echo "<td width='1%' align='center'>".$data['oname']."</td>";
}
echo "<td width='1%' align='center'>".$data['ototal']." ".$settings['eshop_currency']."</a></td>";
echo "<td width='1%' align='center'>".($data['opaid'] =="1" ? "<img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/bullet_green.png' alt'' />" : "<img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/bullet_red.png' alt'' />")."</td>";
echo "<td width='1%' align='center'>".($data['ocompleted'] =="1" ? "<img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/bullet_green.png' alt'' />" : "<img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/bullet_red.png' alt'' />")."</td>";
echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;vieworder&amp;orderid=".$data['oid']."'><img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/orderlist.png' alt='' border='0' /></a></td>";
echo "<td width='1%' align='center'><a class='printorder' href='".ADMIN."eshop/printorder.php".$aidlink."&amp;orderid=".$data['oid']."'><img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/print.png' alt='' border='0' /></a></td>";
echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;step=delete&amp;orderid=".$data['oid']."'  onClick='return confirmdelete();'><img style='width:20px; height:20px;vertical-align:middle;' src='".BASEDIR."eshop/img/remove.png' border='0' alt='Remove' /></a></td>";
}
echo "</tr></table>\n";
echo "<div align='center' style='margin-top:5px;'>".makePageNav($_GET['rowstart'],15,$rows,3,FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;sortby=".$_GET['sortby']."&amp;")."\n</div>\n";
} else {
echo "<center><br />\n  <b>".$locale['ESHP327']."</b><br /><br />\n</center>\n";
}

echo "<br /></fieldset><br />";

$search = array(
"A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R",
"S","T","U","V","W","X","Y","Z","0","1","2","3","4","5","6","7","8","9");
echo "<hr /><table align='center' cellpadding='0' cellspacing='1' class='tbl-border'>\n<tr>\n";
echo "<td rowspan='2' class='tbl2'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;sortby=all'>".$locale['ESHP425']."</a></td>";
for ($i=0;$i < 36!="";$i++) {
echo "<td align='center' class='tbl1'><div class='small'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;sortby=".$search[$i]."'>".$search[$i]."</a></div></td>";
echo ($i==17 ? "<td rowspan='2' class='tbl2'><a href='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;o_page=orders&amp;sortby=all'>".$locale['ESHP426']."</a></td>\n</tr>\n<tr>\n" : "\n");
}
echo "</table><hr />\n";

}
} elseif ($_GET['o_page'] == "history") {
include "orderhistory.php";
}
}
if (isset($_POST['osrchtext'])) {
$searchtext = stripinput($_POST['osrchtext']);
} else  { $searchtext = $locale['SRCH161']; }

echo "<div style='float:right;margin-top:5px;'><form id='search_form'  name='inputform' method='post' action='".FUSION_SELF.$aidlink."&amp;a_page=orders&amp;osearch'>
<span style='vertical-align:middle;font-size:14px;'>".$locale['ESHP328']."</span>";
echo "<input type='text' name='osrchtext' class='textbox' style='margin-left:1px; margin-right:1px; margin-bottom:5px; width:160px;'  value='".$searchtext."' onblur=\"if(this.value=='') this.value='".$searchtext."';\" onfocus=\"if(this.value=='".$searchtext."') this.value='';\" />";
echo "<input type='image' id='search_image' src='".BASEDIR."eshop/img/search_icon.png' alt='".$locale['SRCH161']."' />";
echo "</form></div>";
echo "</td></tr></table>";
?>