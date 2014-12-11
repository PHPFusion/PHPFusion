<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: coupons.php
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

if (isset($_GET['step']) && $_GET['step'] == "delete") {
	if (!preg_match("/^[-0-9A-ZÅÄÖ._@\s]+$/i", $_GET['cuid'])) { die("Denied"); exit; }
	$result = dbquery("DELETE FROM ".DB_ESHOP_COUPONS." WHERE cuid='".$_GET['cuid']."'");
} 

if (isset($_POST['save_item'])) {

if (!preg_match("/^[-0-9A-ZÅÄÖ._@\s]+$/i", $_POST['cuid'])) { die("Denied"); exit; }
$cuid = stripinput($_POST['cuid']);
$name = stripinput($_POST['cuname']);
$type = stripinput($_POST['cutype']);
$value = stripinput($_POST['cuvalue']);
$active = stripinput($_POST['active']);
$start = 0; 
$end = 0;

	if ($_POST['custart']['mday']!="--" && $_POST['custart']['mon']!="--" && $_POST['custart']['year']!="----") {
		$start = mktime($_POST['custart']['hours'],$_POST['custart']['minutes'],0,$_POST['custart']['mon'],$_POST['custart']['mday'],$_POST['custart']['year']);
	}
	if ($_POST['cuend']['mday']!="--" && $_POST['cuend']['mon']!="--" && $_POST['cuend']['year']!="----") {
		$end = mktime($_POST['cuend']['hours'],$_POST['cuend']['minutes'],0,$_POST['cuend']['mon'],$_POST['cuend']['mday'],$_POST['cuend']['year']);
	}

if (isset($_GET['step']) && $_GET['step'] == "edit") {
	$result = dbquery("UPDATE ".DB_ESHOP_COUPONS." SET cuid = '$cuid', cuname = '$name', cutype = '$type' , cuvalue = '$value' , custart = '$start', cuend = '$end', active = '$active' WHERE cuid ='".$_GET['cuid']."'");
} else {
	$result = dbquery("INSERT INTO ".DB_ESHOP_COUPONS." (cuid,cuname,cutype,cuvalue,custart,cuend,active) VALUES('$cuid','$name','$type','$value','$start','$end','$active')");
}
	redirect(FUSION_SELF.$aidlink."&amp;a_page=cupons&amp;cuid=$cuid");
}

if (isset($_GET['step']) && $_GET['step'] == "edit") {
	$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_COUPONS." WHERE cuid='".$_GET['cuid']."'"));
	$cuid = $data['cuid'];
	$name = $data['cuname'];
	$type = $data['cutype'];
	$value = $data['cuvalue'];
	if ($data['custart']>0) $custart = getdate($data['custart']);
	if ($data['cuend']>0) $cuend = getdate($data['cuend']);
	$active = $data['active'];
	$formaction = FUSION_SELF.$aidlink."&amp;a_page=cupons&amp;step=edit&amp;cuid=".$data['cuid'];
	
} else {

$cupon_rand = rand(1000000, 9999999);
$cupon_hash = substr(md5($cupon_rand), 0, 15); 

	$cuid =  strtoupper($cupon_hash);
	$name = "";
	$type = "";
	$value = "";
	$custart = "";
	$cuend = "";
	$active = "";
	$formaction = FUSION_SELF.$aidlink."&amp;a_page=cupons";
}

echo "<form name='addcupon' method='post' action='$formaction'>";
echo "<table cellpadding='0' cellspacing='0' width='100%'>";
echo "
<tr><td class='tbl' align='left'>".$locale['ESHPCUPNS101']."</td>
<td class='tbl' align='left'> <input type='text' name='cuid' value='$cuid' class='textbox'></td>
</tr><tr><td class='tbl' align='left'>".$locale['ESHPCUPNS102']."</td>
<td class='tbl' align='left'> <input type='text' name='cuname' value='$name' class='textbox'></td>
</tr>";

echo "<tr><td class='tbl' align='left'>".$locale['ESHPCUPNS103']."</td><td class='tbl' align='left'><select name='cutype' class='textbox'>
    <option value='0'".($type == "0" ? " selected" : "").">".$locale['ESHPCUPNS112']."</option>
    <option value='1'".($type == "1" ? " selected" : "").">".$locale['ESHPCUPNS113']."</option>
    </select></td></tr>";

echo "<tr><td class='tbl' align='left'>".$locale['ESHPCUPNS104']."</td>
<td class='tbl' align='left'> <input type='text' class='textbox' name='cuvalue' value='$value' style='width:50px !important;'></td></tr>";

echo "<tr><td class='tbl' align='left'>".$locale['ESHPCUPNS105']."</td>
<td class='tbl' align='left'><select name='custart[mday]' class='textbox' style='width:50px !important;'>\n<option>--</option>\n";
for ($i=1;$i<=31;$i++) echo "<option".(isset($custart['mday']) && $custart['mday'] == $i ? " selected='selected'" : "").">$i</option>\n";
echo "</select> <select name='custart[mon]' class='textbox' style='width:50px !important;'>\n<option>--</option>\n";
for ($i=1;$i<=12;$i++) echo "<option".(isset($custart['mon']) && $custart['mon'] == $i ? " selected='selected'" : "").">$i</option>\n";
echo "</select> <select name='custart[year]' class='textbox' style='width:100px !important;'>\n<option>----</option>\n";
for ($i=(isset($custart['year']) && $custart['year'] != "----" ? $custart['year'] : date('Y'));$i<=date("Y", strtotime('+10 years'));$i++) echo "<option".(isset($custart['year']) && $custart['year'] == $i ? " selected='selected'" : "").">$i</option>\n";
echo "</select> / <select name='custart[hours]' class='textbox' style='width:50px !important;'>\n";
for ($i=0;$i<=24;$i++) echo "<option".(isset($custart['hours']) && $custart['hours'] == $i ? " selected='selected'" : "").">$i</option>\n";
echo "</select> : <select name='custart[minutes]' class='textbox' style='width:50px !important;'>\n";
for ($i=0;$i<=60;$i++) echo "<option".(isset($custart['minutes']) && $custart['minutes'] == $i ? " selected='selected'" : "").">$i</option>\n";
echo "</select> : 00 </td></tr>\n";

echo "<tr><td class='tbl' align='left'>".$locale['ESHPCUPNS106']."</td>
<td class='tbl' align='left'> <select name='cuend[mday]' class='textbox' style='width:50px !important;'>\n<option>--</option>\n";
for ($i=1;$i<=31;$i++) echo "<option".(isset($cuend['mday']) && $cuend['mday'] == $i ? " selected='selected'" : "").">$i</option>\n";
echo "</select> <select name='cuend[mon]' class='textbox' style='width:50px !important;'>\n<option>--</option>\n";
for ($i=1;$i<=12;$i++) echo "<option".(isset($cuend['mon']) && $cuend['mon'] == $i ? " selected='selected'" : "").">$i</option>\n";
echo "</select> <select name='cuend[year]' class='textbox' style='width:100px !important;'>\n<option>----</option>\n";
for ($i=(isset($cuend['year']) && $cuend['year'] != "----" ? $cuend['year'] : date('Y'));$i<=date("Y", strtotime('+10 years'));$i++) echo "<option".(isset($cuend['year']) && $cuend['year'] == $i ? " selected='selected'" : "").">$i</option>\n";
echo "</select> / <select name='cuend[hours]' class='textbox' style='width:50px !important;'>\n";
for ($i=0;$i<=24;$i++) echo "<option".(isset($cuend['hours']) && $cuend['hours'] == $i ? " selected='selected'" : "").">$i</option>\n";
echo "</select> : <select name='cuend[minutes]' class='textbox' style='width:50px !important;'>\n";
for ($i=0;$i<=60;$i++) echo "<option".(isset($cuend['minutes']) && $cuend['minutes'] == $i ? " selected='selected'" : "").">$i</option>\n";
echo "</select> : 00 </td></tr>\n";
echo "<tr><td class='tbl' align='left'>".$locale['ESHPCUPNS107']."</td><td class='tbl' align='left'><select name='active' class='textbox' style='width:80px !important;'>
    <option value='1'".($active == "1" ? " selected" : "").">".$locale['ESHPCUPNS108']."</option>
    <option value='0'".($active == "0" ? " selected" : "").">".$locale['ESHPCUPNS109']."</option>
    </select></td></tr>";
echo "</table>";
echo "<center><div style='padding:5px;'><input type='submit'name='save_item' value='".$locale['ESHPCUPNS111']."' class='button'></div></center>";
echo "</form>\n";

echo "<div class='clear'></div>";
echo "<hr />";
$result = dbquery("SELECT * FROM ".DB_ESHOP_COUPONS."");
$rows = dbrows($result);
if ($rows != 0) {
echo "<br /><table align='center' cellspacing='4' cellpadding='0' class='tbl-border' width='99%'><tr>
<td class='tbl2' align='center' width='1%'><b>".$locale['ESHPCUPNS101']."</b></td>
<td class='tbl2' align='center' width='1%'><b>".$locale['ESHPCUPNS102']."</b></td>
<td class='tbl2' align='center' width='1%'><b>".$locale['ESHPCUPNS105']."</b></td>
<td class='tbl2' align='center' width='1%'><b>".$locale['ESHPCUPNS106']."</b></td>
<td class='tbl2' align='center' width='1%'><b>Options</b></td>
</tr>\n";

$result = dbquery("SELECT * FROM ".DB_ESHOP_COUPONS." LIMIT ".$_GET['rowstart'].",15");
while ($data = dbarray($result)) {
echo "<tr style='height:20px;' onMouseOver=\"this.className='tbl2'\" onMouseOut=\"this.className='tbl1'\">";
echo "<td align='center' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=cupons&amp;step=edit&amp;cuid=".$data['cuid']."'><b>".$data['cuid']."</b></a></td>\n";
echo "<td align='center' width='1%'><b>".$data['cuname']."</b></td>";
echo "<td align='center' width='1%'><b>".showdate("forumdate", $data['custart'])."</b></td>";
echo "<td align='center' width='1%'><b>".showdate("forumdate", $data['cuend'])."</b></td>";
echo "<td align='center' width='1%'> ".($data['active'] =="1" ? "<img src='".BASEDIR."eshop/img/bullet_green.png' border='0' width='20' style='vertical-align:middle;' />" : "<img src='".BASEDIR."eshop/img/bullet_red.png' border='0' width='20' style='vertical-align:middle;' />")." &middot;<a href='".FUSION_SELF.$aidlink."&amp;a_page=cupons&amp;step=edit&amp;cuid=".$data['cuid']."'><img src='".BASEDIR."eshop/img/edit.png' alt='' border='0' width='20' style='vertical-align:middle;' /></a>&middot;<a href='".FUSION_SELF.$aidlink."&amp;a_page=cupons&amp;step=delete&amp;cuid=".$data['cuid']."' onClick='return confirmdelete();'><img src='".BASEDIR."eshop/img/remove.png' border='0' width='20' style='vertical-align:middle;'  alt='Remove' /></a></td></tr>";

} 
echo "</table>\n";
echo "<div align='center' style='margin-top:5px;'>".makePageNav($_GET['rowstart'],15,$rows,3,FUSION_SELF.$aidlink."&amp;cupons&amp;")."\n</div>\n";
} else {
echo "<div class='admin-message'>".$locale['ESHPCUPNS110']."</div>\n";
}

?>