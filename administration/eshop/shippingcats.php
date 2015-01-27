<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: shippingcats.php
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
if (isset($_GET['catid']) && !isnum($_GET['catid'])) die("Denied");

define("CAT_DIR", INFUSIONS."eshop/shippingimgs/");
$cat_files = makefilelist(CAT_DIR, ".|..|index.php", true);
$cat_list = makefileopts($cat_files);


if (isset($_GET['step']) && $_GET['step'] == "delete") {
	$result = dbquery("DELETE FROM ".DB_ESHOP_SHIPPINGCATS." WHERE cid='".$_GET['catid']."'");
} 

if (isset($_POST['save_cat'])) {
	$title = stripinput($_POST['title']);
	$cat_image = stripinput($_POST['cat_image']);

if (isset($_GET['step']) && $_GET['step'] == "edit") {
	$result = dbquery("UPDATE ".DB_ESHOP_SHIPPINGCATS." SET title='$title',image='$cat_image' WHERE cid ='".$_GET['catid']."'");
} else {
	$result = dbquery("INSERT INTO ".DB_ESHOP_SHIPPINGCATS." (cid,title,image) VALUES('', '$title','$cat_image')");
}
	redirect(FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;s_page=shippingcats");
}

if (isset($_GET['step']) && $_GET['step'] == "edit") {
	$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGCATS." WHERE cid='".$_GET['catid']."'"));
	$cid = $data['cid'];
	$title = $data['title'];
	$cat_image = $data['image'];
	$formaction = FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;s_page=shippingcats&amp;step=edit&amp;id=".$data['cid'];

} else {
	
	$cid = ""; 
	$title = ""; 
	$cat_image = "generic.png";
	$formaction = FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;s_page=shippingcats";
}

echo "<form name='addcat' method='post' action='$formaction'>
<table align='center' cellspacing='0' cellpadding='0' class='tbl'><tr>
<td width='25%'><img src='".CAT_DIR.($cat_image!=''?$cat_image:"")."' height='60' name='cat_image_preview' alt='' /></td>
<td width='35%'><select name='cat_image' class='textbox' style='width:200px;' onChange=\"document.cat_image_preview.src = '".CAT_DIR."' + document.addcat.cat_image.options[document.addcat.cat_image.selectedIndex].value;\">
<option value='".$cat_image."' ".($cat_image == "$cat_image" ? " selected" : "").">".$cat_image."</option>
$cat_list</select></td>
<td width='40%' align='center'>".$locale['ESHPSHPMTS102']."</td>
<td align='center'><input type='text' name='title' value='$title' class='textbox' style='width:120px;'> </td> 
<td align='right'><input type='submit'name='save_cat' value='".$locale['ESHPSHPMTS103']."' class='button'></td>
</tr></table></form>\n";

$result = dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGCATS."");
$rows = dbrows($result);
if ($rows != 0) {

echo "<br /><table align='center' cellspacing='4' cellpadding='0' class='tbl-border' width='99%'><tr>
<td class='tbl2' align='center' width='1%'><b>".$locale['ESHPSHPMTS104']."</b></td>
<td class='tbl2' align='center' width='1%'><b>".$locale['ESHPSHPMTS105']."</b></td>
</tr>";

$result = dbquery("SELECT * FROM ".DB_ESHOP_SHIPPINGCATS." ORDER BY title ASC, title LIMIT ".$_GET['rowstart'].",25");
while ($data = dbarray($result)) {
echo "<tr style='height:20px;' onMouseOver=\"this.className='tbl2'\" onMouseOut=\"this.className='tbl1'\">
<td align='left' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;s_page=shippingcats&amp;step=edit&amp;catid=".$data['cid']."'><b>".$data['title']."</b></a></td>
<td align='center' width='1%'><a href='".FUSION_SELF.$aidlink."&amp;a_page=shipping&amp;s_page=shippingcats&amp;step=delete&amp;catid=".$data['cid']."' onClick='return confirmdelete();'><img src='".BASEDIR."eshop/img/remove.png' border='0' height='20' style='vertical-align:middle;'  alt='' /></a></td></tr>";
} 
echo "</table>";
echo "<div align='center' style='margin-top:5px;'>".makePageNav($_GET['rowstart'],25,$rows,3,FUSION_SELF.$aidlink."&amp;shipping&amp;s_page=shippingcats&amp;")."\n</div>\n";
} else {
echo "<div class='admin-message'>".$locale['ESHPSHPMTS106']."</div>\n";
}
?>