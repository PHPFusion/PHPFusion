<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: shippingitems.php
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
if (isset($_GET['sid']) && !isnum($_GET['sid'])) die("Denied");
if (isset($_GET['id']) && !isnum($_GET['id'])) die("Denied");
if (isset($_GET['cid']) && !isnum($_GET['cid'])) die("Denied");

define("CAT_DIR", INFUSIONS."eshop/shippingimgs/");

$shippingthumbs_per_page = "16";
$shippingthumbs_per_row = "4";

if (isset($_GET['step']) && $_GET['step'] == "delete") {
	$result = dbquery("DELETE FROM ".DB_ESHOP_SHIPPINGITEMS." WHERE sid='".$_GET['id']."'");
} 


if (isset($_POST['save_item'])) {

$cid = stripinput($_POST['cid']);
$method = stripinput($_POST['method']);
$dtime = stripinput($_POST['dtime']);
$destination = stripinput($_POST['destination']);
$weightmin = stripinput($_POST['weightmin']);
$weightmax = stripinput($_POST['weightmax']);
$weightcost = stripinput($_POST['weightcost']);
$initialcost = stripinput($_POST['initialcost']);
$active = stripinput($_POST['active']);

if (isset($_GET['step']) && $_GET['step'] == "edit") {

	$result = dbquery("UPDATE ".DB_ESHOP_SHIPPINGITEMS." SET cid = '$cid', method = '$method', dtime = '$dtime' , destination = '$destination' , weightmin = '$weightmin', weightmax = '$weightmax',weightcost = '$weightcost',initialcost = '$initialcost' , active = '$active' WHERE sid ='".$_GET['id']."'");
} else {

	$result = dbquery("INSERT INTO ".DB_ESHOP_SHIPPINGITEMS." (sid,cid,method,dtime,destination,weightmin,weightmax,weightcost,initialcost,active) VALUES('','$cid','$method','$dtime','$destination','$weightmin','$weightmax','$weightcost','$initialcost','$active')");
}

	redirect(FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;s_page=shipping&amp;cid=$cid");
}

if (isset($_GET['cid'])) {
if (isset($_GET['step']) && $_GET['step'] == "edit") {
	$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGITEMS." WHERE sid='".$_GET['id']."'"));
	$cid = $data['cid'];
	$method = $data['method'];
	$dtime = $data['dtime'];
	$destination = $data['destination'];
	$weightmin = $data['weightmin'];
	$weightmax = $data['weightmax'];
	$weightcost = $data['weightcost'];
	$initialcost = $data['initialcost'];
	$active = $data['active'];
	$formaction = FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;s_page=shipping&amp;step=edit&amp;id=".$data['sid'];
	
} else {

	$cid = "";
	$method = "";
	$dtime = "";
	$destination = "";
	$weightmin = "0.00";
	$weightmax = "0.00";
	$weightcost = "";
	$initialcost = "";
	$active = "";
	$formaction = FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;s_page=shipping";
}

echo "<form name='addcat' method='post' action='$formaction'>";
echo "<table cellpadding='2' cellspacing='1' width='100%'><tr>
<td class='tbl2' width='1%' align='center'>".$locale['ESHPSHPMTS107']."</td>
<td class='tbl2' width='1%' align='center'>".$locale['ESHPSHPMTS108']."</td>
<td class='tbl2' width='1%' align='center'>".$locale['ESHPSHPMTS109']."</td>
<td class='tbl2' width='1%' align='center'>".$locale['ESHPSHPMTS110']." (".$settings['eshop_weightscale'].")</td>
<td class='tbl2' width='1%' align='center'>".$locale['ESHPSHPMTS111']." (".$settings['eshop_weightscale'].")</td>
<td class='tbl2' width='1%' align='center'>".$locale['ESHPSHPMTS112']."</td>
<td class='tbl2' width='1%' align='center'>".$locale['ESHPSHPMTS113']." (".$settings['eshop_weightscale'].")</td>
<td class='tbl2' width='1%' align='center'>".$locale['ESHPSHPMTS114']."</td>
</tr>\n";
echo "<input type='hidden' name='cid' value='".$_GET['cid']."'>";

$result = dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGITEMS." WHERE cid = '".$_GET['cid']."'");
$rows = dbrows($result);
if ($rows != 0) {
$result = dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGITEMS." WHERE cid = '".$_GET['cid']."' ORDER BY sid ASC LIMIT ".$_GET['rowstart'].",25");
while ($data = dbarray($result)) {
    if ($data['destination'] == "1") { $destlocale = $locale['D101']; }
	if ($data['destination'] == "2") { $destlocale = $locale['D102']; }
	if ($data['destination'] == "3") { $destlocale = $locale['D103']; }

echo "<tr>
<td class='tbl' width='1%' align='center'>".$data['method']."</td>
<td class='tbl' width='1%' align='center'>".$data['dtime']."</td>
<td class='tbl' width='1%' align='center'>".$destlocale."</td>
<td class='tbl' width='1%' align='center'>".$data['weightmin']."</td>
<td class='tbl' width='1%' align='center'>".$data['weightmax']."</td>
<td class='tbl' width='1%' align='center'>".$data['initialcost']."</td>
<td class='tbl' width='1%' align='center'>".$data['weightcost']."</td>
<td class='tbl' width='1%' align='center'>".($data['active'] =="1" ? "<img src='".BASEDIR."eshop/img/bullet_green.png' border='0' width='15' style='vertical-align:middle;' />" : "<img src='".BASEDIR."eshop/img/bullet_red.png' border='0' width='15' style='vertical-align:middle;' />")." &middot;<a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&s_page=shipping&amp;cid=".$_GET['cid']."&amp;step=edit&amp;id=".$data['sid']."'><img src='".BASEDIR."eshop/img/edit.png' alt='' border='0' width='15' style='vertical-align:middle;' /></a>&middot;<a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&s_page=shipping&amp;cid=".$_GET['cid']."&amp;step=delete&amp;id=".$data['sid']."'><img src='".BASEDIR."eshop/img/remove.png' border='0' width='15' style='vertical-align:middle;'  alt='Remove' /></a></td>
</select></td></tr>";
} 

echo "<div align='center' style='margin-top:5px;'>".makePageNav($_GET['rowstart'],25,$rows,3,FUSION_SELF.$aidlink."&amp;shipping&amp;s_page=shippingcats&amp;")."\n</div>\n";
} else {
echo "<br /><div class='admin-message'>".$locale['ESHPSHPMTS115']."</div>\n";
}
echo "<tr><td colspan='8'><hr /></td></tr>";
echo "<tr>
<td class='tbl' width='1%' align='center'><input type='text' name='method' value='$method' class='textbox' style='width:100px !important;'></td>
<td class='tbl' width='1%' align='center'><input type='text' name='dtime' value='$dtime' class='textbox' style='width:70px !important;'></td>
<td class='tbl' width='1%' align='center'><select class='textbox' name='destination' style='width:100px !important;'>
<option value='1'".($destination == "1" ? " selected" : "").">".$locale['D101']."</option>
<option value='2'".($destination == "2" ? " selected" : "").">".$locale['D102']."</option>
<option value='3'".($destination == "3" ? " selected" : "").">".$locale['D103']."</option>
</select></td>
<td class='tbl' width='1%' align='center'><input type='text' class='textbox' name='weightmin' value='$weightmin' style='width:40px !important;'></td>
<td class='tbl' width='1%' align='center'><input type='text' class='textbox' name='weightmax' value='$weightmax' style='width:40px !important;'></td>
<td class='tbl' width='1%' align='center'><input type='text' name='initialcost' class='textbox' value='$initialcost' style='width:30px !important;'></td>
<td class='tbl' width='1%' align='center'><input type='text' name='weightcost' class='textbox' value='$weightcost' style='width:30px !important;'></td>
<td class='tbl' width='1%' align='center'><select class='textbox' name='active' style='width:60px !important;'>
<option value='1' selected='selected'>Yes</option>
<option value='0'>No</option>
</select></td></tr>
</table>
<center><div style='padding:5px;'><input type='submit'name='save_item' value='".$locale['ESHPSHPMTS116']."' class='button'></div></center>";
echo "</form>\n";
} else {

$rows = dbcount("(cid)", "".DB_ESHOP_SHIPPINGCATS."");
if ($rows) {
echo "<div class='admin-message'> ".$locale['ESHPSHPMTS117']." </div>";

	$result = dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGCATS."  ORDER BY cid DESC LIMIT ".$_GET['rowstart'].",".$shippingthumbs_per_page);
	$counter = 0; $k = ($_GET['rowstart'] == 0 ? 1 : $_GET['rowstart'] + 1);
	echo "<table cellpadding='0' cellspacing='1' width='100%'>\n<tr>\n";
	while ($data = dbarray($result)) {

		if ($counter != 0 && ($counter % $shippingthumbs_per_row == 0)) echo "</tr>\n<tr>\n";
		echo "<td align='center' valign='top' class='tbl'>\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;cid=".$data['cid']."'><b>".$data['title']."</b><br />\n";
		if ($data['image'] && file_exists(CAT_DIR.$data['image'])){
			echo "<img src='".CAT_DIR.$data['image']."' alt='' border='0' width='100' height='100'>";
		} else {
			echo $ESHPALBUMS['462'];
		}
		echo "<br />&middot; <a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;cid=".$data['cid']."'>".$locale['ESHPHOTOS107']."</a> &middot;\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&s_page=shippingcats&step=edit&amp;catid=".$data['cid']."'>".$ESHPALBUMS['469']."</a> &middot;\n";
		echo "</td>\n";
		$counter++; $k++;
	}
echo "</tr>\n</table>\n";
if ($rows > $shippingthumbs_per_page) echo "<div align='center' style='margin-top:5px;'>\n".makeeshoppagenav($_GET['rowstart'],$shippingthumbs_per_page,$rows,3,FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;")."\n</div>\n";

} else {
echo "<div class='admin-message'>".$locale['ESHPSHPMTS106']."</div>\n";
}
}


?>