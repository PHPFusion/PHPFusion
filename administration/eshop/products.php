<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: products.php
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
if (isset($_POST['access'])) { $access = isnum($_POST['access']) ? $_POST['access'] : "0"; } else { $access = "0"; }

if (isset($_GET['psearch'])) {
include ADMIN."eshop/productsearch.php";
} else {

echo '<script type="text/javascript">
//<![CDATA[
function showexttabs(){
$("#exttabs").animate({"height": "toggle"}, { duration: 500 });
}
//]]>
</script>';


if (isset($_GET['action']) && $_GET['action'] == "refresh") {
	$i = 1;
	$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE cid = '".$_REQUEST['category']."' ORDER BY iorder");
	while ($data = dbarray($result)) {
	       $result2 = dbquery("UPDATE ".DB_ESHOP." SET iorder='$i' WHERE id='".$data['id']."'");
	       $i++;
	}
	redirect(FUSION_SELF.$aidlink."&amp;iorderrefresh".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."");
}

if ((isset($_GET['action']) && $_GET['action'] == "moveup") && (isset($_GET['id']) && isnum($_GET['id']))) {
	$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP." WHERE cid = '".$_REQUEST['category']."' AND iorder='".intval($_GET['order'])."'"));
	$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder+1 WHERE cid = '".$_REQUEST['category']."' AND id='".$data['id']."'");
	$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder-1 WHERE cid = '".$_REQUEST['category']."' AND id='".$_GET['id']."'");
	redirect(FUSION_SELF.$aidlink."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."");
} 

if ((isset($_GET['action']) && $_GET['action'] == "movedown") && (isset($_GET['id']) && isnum($_GET['id']))) {
	$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP." WHERE cid = '".$_REQUEST['category']."' AND iorder='".intval($_GET['order'])."'"));
	$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder-1 WHERE cid = '".$_REQUEST['category']."' AND id='".$data['id']."'");
	$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder+1 WHERE cid = '".$_REQUEST['category']."' AND id='".$_GET['id']."'");
	redirect(FUSION_SELF.$aidlink."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."");
}

if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['id']) && isnum($_GET['id']))) {
	$result1 = dbquery("SELECT picture,thumb,thumb2,iorder,cid FROM ".DB_ESHOP." WHERE id='".$_GET['id']."'");
	$remove = dbarray($result1);
	$picture= BASEDIR."eshop/pictures/".$remove['picture'];
	$thumb = BASEDIR."eshop/pictures/".$remove['thumb'];
	$thumb2 = BASEDIR."eshop/pictures/".$remove['thumb2'];
if ($remove['picture']) {
	@unlink($picture);
}
if ($remove['thumb']) {
	@unlink($thumb);
}
if ($remove['thumb2']) {
	@unlink($thumb2);
}
	$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder-1 WHERE iorder>'".$remove['iorder']."' AND cid = '".$remove['cid']."'");
	$result = dbquery("DELETE FROM ".DB_ESHOP." WHERE id='".$_GET['id']."'");
	redirect(FUSION_SELF.$aidlink."&amp;ideleted".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."");
 }
 
$imagebytes = $settings['eshop_image_b'];
$imagewidth = $settings['eshop_image_w'];
$imageheight = $settings['eshop_image_h'];
$thumbwidth = $settings['eshop_image_tw'];
$thumbheight = $settings['eshop_image_th'];
$thumb2width = $settings['eshop_image_t2w']; 
$thumb2height = $settings['eshop_image_t2h'];

if (isset($_GET['iorderrefresh'])) {
	echo "<div class='admin-message'>".$locale['ESHPPRO100']."</div>\n";
}

if (isset($_GET['ideleted'])) {
	echo "<div class='admin-message'>".$locale['ESHPPRO101']."</div>\n";
}

if (isset($_GET['complete'])) {
	$message = "";
	if ($_GET['error'] == 1) { $message .= $locale['ESHP427']; }
	elseif ($_GET['error'] == 2) { $message .= "".$locale['ESHP428']." parsebytesize($imagebytes)"; }
	elseif ($_GET['error'] == 3) { $message .= $locale['ESHP429']; }
	elseif ($_GET['error'] == 4) { $message .= "".$locale['ESHP430']." ".$imagewidth."x".$imageheight.""; }
	elseif ($_GET['errors'] == "") { $message = $locale['ESHP431']; }
	echo "<div class='admin-message'>".$message."</div>\n";
}

if (isset($_POST['save_cat'])) {
	$error = "";
	$photo_file = $_POST['image'];
	$photo_thumb1 = $_POST['thumb'];
	$photo_thumb2 = $_POST['thumb2'];
	$newimgfile = $_FILES['imagefile'];

if (!empty($newimgfile['name']) && is_uploaded_file($newimgfile['tmp_name'])) {

	$error="";
	$photo_file = ""; $photo_thumb1 = ""; $photo_thumb2 = "";

	if (is_uploaded_file($_FILES['imagefile']['tmp_name'])) {
		$photo_types = array(".gif",".jpg",".jpeg",".png");
		$photo_pic = $_FILES['imagefile'];
		$photo_name = strtolower(substr($photo_pic['name'], 0, strrpos($photo_pic['name'], ".")));
		$photo_ext = strtolower(strrchr($photo_pic['name'],"."));
		$photo_dest = BASEDIR."eshop/pictures/";
		if (!preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $photo_pic['name'])) {
			$error = 1;
		} elseif ($photo_pic['size'] > $imagebytes){
			$error = 2;
		} elseif (!in_array($photo_ext, $photo_types)) {
			$error = 3;
		} else {
			$photo_file = image_exists($photo_dest, $photo_name.$photo_ext);
			move_uploaded_file($photo_pic['tmp_name'], $photo_dest.$photo_file);
			chmod($photo_dest.$photo_file, 0644);
			$imagefile = @getimagesize($photo_dest.$photo_file);
			if ($imagefile[0] > $imagewidth || $imagefile[1] > $imageheight) {
				$error = 4;
				unlink($photo_dest.$photo_file);
			} else {
				$photo_thumb1 = image_exists($photo_dest, $photo_name."_t1".$photo_ext);
				createthumbnail($imagefile[2], $photo_dest.$photo_file, $photo_dest.$photo_thumb1, $thumbwidth, $thumbheight);
				if ($imagefile[0] > $imagewidth || $imagefile[1] > $imageheight) {
					$photo_thumb2 = image_exists($photo_dest, $photo_name."_t2".$photo_ext);
					createthumbnail($imagefile[2], $photo_dest.$photo_file, $photo_dest.$photo_thumb2, $thumb2width, $thumb2height);
				}
			}
		}
	}
//end image upload function
}

$clist = "";
$slist = "";
$title = stripinput($_POST['title']);
$category = stripinput($_POST['cid']);
$introtext = stripinput($_POST['introtext']);
$description = addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['description']));
$anything1 = addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['anything1']));
$anything1n = stripinput($_POST['anything1n']);
$anything2 = addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['anything2']));
$anything2n = stripinput($_POST['anything2n']);
$anything3 = addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['anything3']));
$anything3n = stripinput($_POST['anything3n']);
$weight = stripinput($_POST['weight']);
$weight = str_replace(",", ".", $weight); 
$price = stripinput($_POST['price']);
$price = str_replace(",", ".", $price); 
$xprice = stripinput($_POST['xprice']);
$xprice = str_replace(",", ".", $xprice); 
$stock = stripinput($_POST['stock']);
$version = stripinput($_POST['version']);
$status = stripinput($_POST['status']);
$active = stripinput($_POST['active']);
$gallery_on = stripinput($_POST['gallery_on']);
$delivery = stripinput($_POST['delivery']);
$demo = stripinput($_POST['demo']);
$cart_on = stripinput($_POST['cart_on']);
$buynow = stripinput($_POST['buynow']);
$rpage = stripinput($_POST['rpage']);
$dynf = stripinput($_POST['dynf']);
$qty = stripinput($_POST['qty']);
$sellcount = stripinput($_POST['sellcount']);
$artno = stripinput($_POST['artno']);
$sartno = stripinput($_POST['sartno']);
$instock = stripinput($_POST['instock']);
$iorder = stripinput($_POST['iorder']);
$dmulti = stripinput($_POST['dmulti']);
$cupons = stripinput($_POST['cupons']);
$access = stripinput($_POST['access']);
$dateadded = stripinput($_POST['dateadded']);
$campaign = stripinput($_POST['campaign']);

if (isset($_POST['cList'])) {
for ($i = 0, $l = count($_POST['cList']); $i < $l; $i++) {
$clist .=".\"".$_POST['cList'][$i]."\"";
 }
}

if (isset($_POST['sList'])) {
for ($i = 0, $l = count($_POST['sList']); $i < $l; $i++) {
$slist .=".\"".$_POST['sList'][$i]."\"";
 }
}

if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['id']) && isnum($_GET['id']))) {
$old_iorder = dbresult(dbquery("SELECT iorder FROM ".DB_ESHOP." WHERE cid = '".$category."' AND id='".$_GET['id']."'"), 0);
if ($iorder > $old_iorder) {
$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder-1 WHERE cid = '".$category."' AND iorder>'$old_iorder' AND iorder<='$iorder'");
} elseif ($iorder < $old_iorder) {
$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder+1 WHERE cid = '".$category."' AND iorder<'$old_iorder' AND iorder>='$iorder'");
 }
$result = dbquery("UPDATE ".DB_ESHOP." SET title='$title', cid='$category', picture='$photo_file', thumb='$photo_thumb1',thumb2='$photo_thumb2',introtext='$introtext',description='$description',anything1='$anything1',anything1n='$anything1n',anything2='$anything2',anything2n='$anything2n',anything3='$anything3',anything3n='$anything3n',weight='$weight',price='$price',xprice='$xprice',stock='$stock',version='$version',status='$status',active='$active',gallery_on='$gallery_on',delivery='$delivery', demo='$demo',cart_on='$cart_on',buynow='$buynow',rpage='$rpage',icolor='$clist',dynf='$dynf',dync='$slist',qty='$qty',sellcount='$sellcount',iorder='$iorder',artno='$artno',sartno='$sartno',instock='$instock',dmulti='$dmulti',cupons='$cupons',access='$access',campaign='$campaign',dateadded='$dateadded' WHERE id='".$_GET['id']."'");
} else {
if (!$iorder) { $iorder = dbresult(dbquery("SELECT MAX(iorder) FROM ".DB_ESHOP." WHERE cid = '".$category."'"), 0) + 1; }
$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder+1 WHERE cid = '".$category."' AND iorder>='$iorder'");	
$result = dbquery("INSERT INTO ".DB_ESHOP." VALUES('', '$title',  '$category', '$photo_file', '$photo_thumb1','$photo_thumb2', '$introtext','$description','$anything1','$anything1n','$anything2','$anything2n','$anything3','$anything3n','$weight','$price','$xprice','$stock','$version','$status','$active','$gallery_on','$delivery','$demo','$cart_on','$buynow','$rpage','$clist','$dynf','$slist','$qty','$sellcount','$iorder','$artno','$sartno','$instock','$dmulti','$cupons','$access','$campaign','".time()."','','')");
 }
redirect("".FUSION_SELF.$aidlink."&amp;complete&amp;error=".$error."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."");
}

if (isset($_GET['action']) && $_GET['action'] == "edit") {
$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE id='".$_GET['id']."'");
$data = dbarray($result);
$title = $data['title'];
$description = stripslashes($data['description']);
$introtext = $data['introtext'];
$anything1 = stripslashes($data['anything1']);
$anything1n = $data['anything1n'];
$anything2 = stripslashes($data['anything2']);
$anything2n = $data['anything2n'];
$anything3 = stripslashes($data['anything3']);
$anything3n = $data['anything3n'];
$category= $data['cid'];
$image_url= $data['picture'];
$thumb_url= $data['thumb'];
$thumb2_url= $data['thumb2'];
$weight = $data['weight'];
$category = $data['cid'];
$price = $data['price'];
$xprice = $data['xprice'];
$stock = $data['stock'];
$version = $data['version'];
$status = $data['status'];
$active = $data['active'];
$gallery_on = $data['gallery_on'];
$delivery = $data['delivery'];
$demo = $data['demo'];
$cart_on = $data['cart_on'];
$buynow = $data['buynow'];
$rpage = $data['rpage'];
$iorder = $data['iorder'];
$artno = $data['artno'];
$sartno = $data['sartno'];
$instock = $data['instock'];
$dmulti = $data['dmulti'];
$cupons = $data['cupons'];
$access = $data['access'];
$clist = $data['icolor'];
$dynf = $data['dynf'];
$slist = $data['dync'];
$qty = $data['qty'];
$sellcount = $data['sellcount'];
$dateadded = $data['dateadded'];
$campaign = $data['campaign'];
$formaction = "".FUSION_SELF.$aidlink."&amp;action=edit&id=".$data['id']."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."";

} else {

$title = "";
$description = "";
$anything1 = "";
$anything1n  = "";
$anything2  = "";
$anything2n  = "";
$anything3  = "";
$anything3n  = "";
$introtext = "";
$category = "";
$image_url= "";
$thumb_url= "";
$thumb2_url= "";
$weight = "";
$category = "";
$price = "";
$xprice = "";
$stock = "";
$version = "";
$status = "";
$active = "";
$gallery_on = "";
$cart_on = "";
$buynow = "";
$delivery = "";
$demo = "";
$rpage = "";
$iorder = "";
$artno = "";
$sartno = "";
$instock = "";
$dmulti = "1";
$cupons = "1";
$access = "";
$dynf= "";
$clist = "";
$slist = "";
$list= "";
$qty = "";
$sellcount = "";
$dateadded = "";
$campaign = "";

$formaction = FUSION_SELF.$aidlink."".($settings['eshop_cats'] == "1" && isset($_REQUEST['category']) ? "&amp;category=".$_REQUEST['category']."" : "")."";
}

$itemcolors = str_replace(".",",",$clist); 
$itemcolors= ltrim ($itemcolors,',');

$itemdyncs = str_replace(".",",",$slist); 
$itemdyncs= ltrim ($itemdyncs,',');

echo '<script type="text/javascript">
function doCheck(cid) {
    var cid = +cid;
    var data = "cid="+ cid;
     $.ajax({
       type: "GET",
       url: "getcolorname.php",
       data: data,
       beforeSend: function(result) { 
       $("#colors"+cid).html("Loading color.."); },
       success: function(result){ 
       $("#colors"+cid).empty();
       $("#colors"+cid).show();
       $("#colors"+cid).append(result); },timeout: 235000,
       error:function() {
       $("#colors"+cid).html("Something went wrong!");
     }
  });
}

$(document).ready(function() {
var dyncArray = [];
//List items already saved
	var dyncs = ['.$itemdyncs.'];
	for (var i = 0, len = dyncs.length; i < len; i++) {
	var sval = dyncs[i];
	dyncArray.push(sval);
	document.getElementById("sList").innerHTML += "<label class=\"sList\"><input checked =\"checked\" class=\"sList-chk\" name=\"sList[]\" type=\"checkbox\" value=\""+sval+"\"> "+sval+"</div></label>";
}

//add item when selected in list
$("#adddync").click(function () {
	var sitem = $("#dyncList").val();
//If value is empty nothing should happend
	if(sitem !== "") {
		if ($.inArray(sitem,dyncArray) == -1) {
		$("#dyncList").val();
		dyncArray.push(sitem);
    document.getElementById("sList").innerHTML += "<label class=\"sList\"><input checked =\"checked\" class=\"sList-chk\" name=\"sList[]\" type=\"checkbox\" value=\""+sitem+"\"> "+sitem+"</label>";
  }
 }
});

//remove dync when clicked
$(document).on("change", ".sList-chk", function () {
  if ($(this).attr("checked")) {
   return;
   } else {
   $(this).parent(".sList").remove();
  }
});

var colorArray = [];
//populate items already saved for edit
var numbers = ['.$itemcolors.'];
for (i=0;i<numbers.length;i++){
var val = numbers[i];
colorArray.push(val);
document.getElementById("cList").innerHTML += "<label class=\"cList\"><input checked =\"checked\" class=\"cList-chk\" name=\"cList[]\" type=\"checkbox\" value=\""+val+"\"> <div class=\"cListdiv\" id=\"colors"+val+"\"></div></label>";
doCheck(val); 
}

//add item when selected in list
$("#colorList").change(function () {
//If value is empty nothing should happend
	if(this.value !== "") {
		if ($.inArray(this.value,colorArray) == -1) {
		var citem = $("#colorList").find("option:selected").text();
		colorArray.push($(this).val());
		document.getElementById("cList").innerHTML += "<label class=\"cList\"><input checked =\"checked\" class=\"cList-chk\" name=\"cList[]\" type=\"checkbox\" value=\""+this.value+"\"> "+citem+"</label>";
	}
}
});

//remove color when clicked
$(document).on("change", ".cList-chk", function () {
  if ($(this).attr("checked")) {
    return;
  } else {
    $(this).parent(".cList").remove();
   }
 });
});
</script>';

$visibility_opts = ""; $sel = "";
$user_groups = getusergroups();
while(list($key, $user_group) = each($user_groups)){
$sel = ($access == $user_group['0'] ? " selected" : "");
$visibility_opts .= "<option value='".$user_group['0']."'$sel>".$user_group['1']."</option>\n";
}

if ($settings['eshop_cats'] == "1" && !isset($_REQUEST['category']) && !isset($_GET['action'])) {
//check for main cats.

echo "<br /><form name='catform' method='post' action='".FUSION_SELF.$aidlink."'>";
echo "<table width='100%' cellspacing='4' cellpadding='0' align='center' class='tbl-border'>";
echo "<tr><td align='left' style='width:290px;'>".$locale['ESHPPRO186']." </td>
<td align='left'><select class='textbox' name='category' style='width:400px;' onchange=\"this.form.submit()\">";
echo "<option value='0'>".$locale['ESHP016']."</option>";
$result2=dbquery("select * from ".DB_ESHOP_CATS." order by parentid, title");
while($cat_data = dbarray($result2)) {
if ($cat_data['parentid']!=0) $cat_data['title']=getparent($cat_data['parentid'],$cat_data['title']);
echo "<option value='".$cat_data['cid']."'>".$cat_data['title']."</option>";
}
echo "</select></td></tr></table></form>";

} else {

//check for subcats in a selected category section.
if (isset($_REQUEST['category'])) {
$resultc = dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE parentid='".$_REQUEST['category']."'");
if (dbrows($resultc)) {

echo "<br /><form name='subcatform' method='post' action='".FUSION_SELF.$aidlink."'>";
echo "<table width='100%' cellspacing='4' cellpadding='0' align='center' class='tbl-border'>";
echo "<tr><td align='left'>".$locale['ESHPPRO187']." </td>
<td align='left'><select class='textbox' name='category' style='width:350px;' onchange=\"this.form.submit()\">";
echo "<option value=''>".$locale['ESHP016']."</option>";
$result2=dbquery("select * from ".DB_ESHOP_CATS." WHERE parentid='".$_REQUEST['category']."' order by parentid, title");
while($cat_data = dbarray($result2)) {
if ($cat_data['parentid']!=0) $cat_data['title']=getparent($cat_data['parentid'],$cat_data['title']);
echo "<option value='".$cat_data['cid']."'>".$cat_data['title']."</option>";
}
echo "</select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO103']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr></table></form><hr />";
  }
 }

echo "<form name='inputform' method='post' action='$formaction' enctype='multipart/form-data'>";
echo "<div style='float:left;width:50%;'>";
echo "<input type='hidden' name='dateadded' value='$dateadded' />";
echo "<table width='100%' cellspacing='4' cellpadding='0' align='center'>
<tr><td align='left'>".$locale['ESHPPRO104']."</td><td align='left'><input type='text' name='title' value='".$title."' class='textbox' style='width:190px;'></td></tr>";
if ($settings['eshop_cats'] == "1") {
echo "<tr><td align='left'>".$locale['ESHPPRO105']."</td>
<td align='left'><select class='textbox' name='cid' style='width:190px;'>";
$catdata=dbarray(dbquery("select title,cid from ".DB_ESHOP_CATS." WHERE cid='".$data['cid']."'"));
echo "<option value=''>".$locale['ESHPPRO181']."</option>";
echo "<option value='".$data['cid']."'>".$catdata['title']."</option>";
$result2=dbquery("select * from ".DB_ESHOP_CATS." order by parentid, title");
while($cat_data = dbarray($result2)) {
if ($cat_data['parentid']!=0) $cat_data['title']=getparent($cat_data['parentid'],$cat_data['title']);
echo "<option value='".$cat_data['cid']."' ".($_REQUEST['category'] == $cat_data['cid'] ? " selected" : "").">".$cat_data['title']."</option>";
}
echo "</select></td></tr>";
} else {
echo "<tr><td align='left'>".$locale['ESHPPRO105']."</td><td align='left'><input type='hidden' name='cid' value='$category'>".$locale['ESHPPRO106']."</td></tr>";
}
echo "<tr><td align='left'>".$locale['ESHPPRO107']."</td><td align='left'><input type='text' name='artno' value='".$artno."' class='textbox' style='width:190px;'></td></tr>";
echo "<tr><td align='left'>".$locale['ESHPPRO108']."</td><td align='left'><input type='text' name='sartno' value='".$sartno."' class='textbox' style='width:190px;'></td></tr>";
echo "<tr><td align='left'>".$locale['ESHPPRO109']."</td><td align='left'><input type='file' name='imagefile' enctype='image/jpeg' value='$image_url' class='textbox' style='width:180px;'>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO110']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";

echo "<tr><td align='left'>".$locale['ESHPPRO111']."</td><td align='left'><input type='text' name='price' value='$price' class='textbox'  style='width:60px;'>  ".$settings['eshop_currency']."</td></tr>";
echo "<tr><td align='left'>".$locale['ESHPPRO112']."</td><td align='left'><input type='text' name='xprice' value='$xprice' class='textbox' style='width:60px;'>  ".$settings['eshop_currency']."
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO113']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a></td></tr>";

echo "<tr><td align='left'>".$locale['ESHPPRO114']."</td><td align='left'><input type='text' name='weight' value='$weight' class='textbox' style='width:60px;'>  ".$settings['eshop_weightscale']."
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO115']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a></td></tr>";

echo "<tr><td align='left'><input type='text' name='dynf' value='$dynf' class='textbox' style='width:100px;'></td>";
echo "<td align='left'><input type='text' name='dyncList' id='dyncList' class='textbox' style='width:70px;'><a href='javascript:;' id='adddync' class='button'>".$locale['ESHPPRO116']."</a>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO117']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a></td></tr>

<tr><td align='left' valign='middle'>".$locale['ESHPPRO118']."</td><td align='left'>
<div id ='sList' style='width:250px;margin-top:5px;margin-bottom:5px;height:150px;overflow: auto;position:relative;'></div></td>
</tr>";
echo "<tr><td align='left' valign='middle'>".$locale['ESHPPRO119']."</td><td style='padding-top:10px;' align='left'>
      <select name='colorList' id='colorList' class='textbox'>
      <option value=''>".$locale['ESHPPRO120']."</option>";
echo "<option value='1' style='background-color:#F0F8FF;'>".$ESHPCLRS['1']."</option>";
echo "<option value='2' style='background-color:#FAEBD7;'>".$ESHPCLRS['2']."</option>";
echo "<option value='3' style='background-color:#00FFFF;'>".$ESHPCLRS['3']."</option>";
echo "<option value='4' style='background-color:#7FFFD4;'>".$ESHPCLRS['4']."</option>";
echo "<option value='5' style='background-color:#F0FFFF;'>".$ESHPCLRS['5']."</option>";
echo "<option value='6' style='background-color:#F5F5DC;'>".$ESHPCLRS['6']."</option>";
echo "<option value='7' style='background-color:#FFE4C4;'>".$ESHPCLRS['7']."</option>";
echo "<option value='8' style='background-color:#000000;'>".$ESHPCLRS['8']."</option>";
echo "<option value='9' style='background-color:#FFEBCD;'>".$ESHPCLRS['9']."</option>";
echo "<option value='10' style='background-color:#0000FF;'>".$ESHPCLRS['10']."</option>";
echo "<option value='11' style='background-color:#8A2BE2;'>".$ESHPCLRS['11']."</option>";
echo "<option value='12' style='background-color:#A52A2A;'>".$ESHPCLRS['12']."</option>";
echo "<option value='13' style='background-color:#DEB887;'>".$ESHPCLRS['13']."</option>";
echo "<option value='14' style='background-color:#5F9EA0;'>".$ESHPCLRS['14']."</option>";
echo "<option value='15' style='background-color:#7FFF00;'>".$ESHPCLRS['15']."</option>";
echo "<option value='16' style='background-color:#D2691E;'>".$ESHPCLRS['16']."</option>";
echo "<option value='17' style='background-color:#FF7F50;'>".$ESHPCLRS['17']."</option>";
echo "<option value='18' style='background-color:#6495ED;'>".$ESHPCLRS['18']."</option>";
echo "<option value='19' style='background-color:#FFF8DC;'>".$ESHPCLRS['19']."</option>";
echo "<option value='20' style='background-color:#DC143C;'>".$ESHPCLRS['20']."</option>";
echo "<option value='21' style='background-color:#00FFFF;'>".$ESHPCLRS['21']."</option>";
echo "<option value='22' style='background-color:#00008B;'>".$ESHPCLRS['22']."</option>";
echo "<option value='23' style='background-color:#008B8B;'>".$ESHPCLRS['23']."</option>";
echo "<option value='24' style='background-color:#B8860B;'>".$ESHPCLRS['24']."</option>";
echo "<option value='25' style='background-color:#A9A9A9;'>".$ESHPCLRS['25']."</option>";
echo "<option value='26' style='background-color:#BDB76B;'>".$ESHPCLRS['26']."</option>";
echo "<option value='27' style='background-color:#8B008B;'>".$ESHPCLRS['27']."</option>";
echo "<option value='28' style='background-color:#556B2F;'>".$ESHPCLRS['28']."</option>";
echo "<option value='29' style='background-color:#FF8C00;'>".$ESHPCLRS['29']."</option>";
echo "<option value='30' style='background-color:#9932CC;'>".$ESHPCLRS['30']."</option>";
echo "<option value='31' style='background-color:#8B0000;'>".$ESHPCLRS['31']."</option>";
echo "<option value='32' style='background-color:#E9967A;'>".$ESHPCLRS['32']."</option>";
echo "<option value='33' style='background-color:#8FBC8F;'>".$ESHPCLRS['33']."</option>";
echo "<option value='34' style='background-color:#483D8B;'>".$ESHPCLRS['34']."</option>";
echo "<option value='35' style='background-color:#2F4F4F;'>".$ESHPCLRS['35']."</option>";
echo "<option value='36' style='background-color:#00CED1;'>".$ESHPCLRS['36']."</option>";
echo "<option value='37' style='background-color:#9400D3;'>".$ESHPCLRS['37']."</option>";
echo "<option value='38' style='background-color:#FF1493;'>".$ESHPCLRS['38']."</option>";
echo "<option value='39' style='background-color:#00BFFF;'>".$ESHPCLRS['39']."</option>";
echo "<option value='40' style='background-color:#696969;'>".$ESHPCLRS['40']."</option>";
echo "<option value='41' style='background-color:#1E90FF;'>".$ESHPCLRS['41']."</option>";
echo "<option value='42' style='background-color:#B22222;'>".$ESHPCLRS['42']."</option>";
echo "<option value='43' style='background-color:#FFFAF0;'>".$ESHPCLRS['43']."</option>";
echo "<option value='44' style='background-color:#228B22;'>".$ESHPCLRS['44']."</option>";
echo "<option value='45' style='background-color:#FF00FF;'>".$ESHPCLRS['45']."</option>";
echo "<option value='46' style='background-color:#DCDCDC;'>".$ESHPCLRS['46']."</option>";
echo "<option value='47' style='background-color:#F8F8FF;'>".$ESHPCLRS['47']."</option>";
echo "<option value='48' style='background-color:#FFD700;'>".$ESHPCLRS['48']."</option>";
echo "<option value='49' style='background-color:#DAA520;'>".$ESHPCLRS['49']."</option>";
echo "<option value='50' style='background-color:#808080;'>".$ESHPCLRS['50']."</option>";
echo "<option value='51' style='background-color:#008000;'>".$ESHPCLRS['51']."</option>";
echo "<option value='52' style='background-color:#ADFF2F;'>".$ESHPCLRS['52']."</option>";
echo "<option value='53' style='background-color:#F0FFF0;'>".$ESHPCLRS['53']."</option>";
echo "<option value='54' style='background-color:#FF69B4;'>".$ESHPCLRS['54']."</option>";
echo "<option value='55' style='background-color:#CD5C5C;'>".$ESHPCLRS['55']."</option>";
echo "<option value='56' style='background-color:#4B0082;'>".$ESHPCLRS['56']."</option>";
echo "<option value='57' style='background-color:#F0E68C;'>".$ESHPCLRS['57']."</option>";
echo "<option value='58' style='background-color:#E6E6FA;'>".$ESHPCLRS['58']."</option>";
echo "<option value='59' style='background-color:#FFF0F5;'>".$ESHPCLRS['59']."</option>";
echo "<option value='60' style='background-color:#7CFC00;'>".$ESHPCLRS['60']."</option>";
echo "<option value='61' style='background-color:#FFFACD;'>".$ESHPCLRS['61']."</option>";
echo "<option value='62' style='background-color:#ADD8E6;'>".$ESHPCLRS['62']."</option>";
echo "<option value='63' style='background-color:#F08080;'>".$ESHPCLRS['63']."</option>";
echo "<option value='64' style='background-color:#E0FFFF;'>".$ESHPCLRS['64']."</option>";
echo "<option value='65' style='background-color:#FAFAD2;'>".$ESHPCLRS['65']."</option>";
echo "<option value='66' style='background-color:#D3D3D3;'>".$ESHPCLRS['66']."</option>";
echo "<option value='67' style='background-color:#90EE90;'>".$ESHPCLRS['67']."</option>";
echo "<option value='68' style='background-color:#FFB6C1;'>".$ESHPCLRS['68']."</option>";
echo "<option value='69' style='background-color:#FFA07A;'>".$ESHPCLRS['69']."</option>";
echo "<option value='70' style='background-color:#20B2AA;'>".$ESHPCLRS['70']."</option>";
echo "<option value='71' style='background-color:#87CEFA;'>".$ESHPCLRS['71']."</option>";
echo "<option value='72' style='background-color:#778899;'>".$ESHPCLRS['72']."</option>";
echo "<option value='73' style='background-color:#B0C4DE;'>".$ESHPCLRS['73']."</option>";
echo "<option value='74' style='background-color:#FFFFE0;'>".$ESHPCLRS['74']."</option>";
echo "<option value='75' style='background-color:#00FF00;'>".$ESHPCLRS['75']."</option>";
echo "<option value='76' style='background-color:#FF00FF;'>".$ESHPCLRS['76']."</option>";
echo "<option value='77' style='background-color:#800000;'>".$ESHPCLRS['77']."</option>";
echo "<option value='78' style='background-color:#66CDAA;'>".$ESHPCLRS['78']."</option>";
echo "<option value='79' style='background-color:#0000CD;'>".$ESHPCLRS['79']."</option>";
echo "<option value='80' style='background-color:#BA55D3;'>".$ESHPCLRS['80']."</option>";
echo "<option value='81' style='background-color:#9370DB;'>".$ESHPCLRS['81']."</option>";
echo "<option value='82' style='background-color:#3CB371;'>".$ESHPCLRS['82']."</option>";
echo "<option value='83' style='background-color:#7B68EE;'>".$ESHPCLRS['83']."</option>";
echo "<option value='84' style='background-color:#00FA9A;'>".$ESHPCLRS['84']."</option>";
echo "<option value='85' style='background-color:#48D1CC;'>".$ESHPCLRS['85']."</option>";
echo "<option value='86' style='background-color:#C71585;'>".$ESHPCLRS['86']."</option>";
echo "<option value='87' style='background-color:#191970;'>".$ESHPCLRS['87']."</option>";
echo "<option value='88' style='background-color:#F5FFFA;'>".$ESHPCLRS['88']."</option>";
echo "<option value='89' style='background-color:#FFE4E1;'>".$ESHPCLRS['89']."</option>";
echo "<option value='90' style='background-color:#FFE4B5;'>".$ESHPCLRS['90']."</option>";
echo "<option value='91' style='background-color:#FFDEAD;'>".$ESHPCLRS['91']."</option>";
echo "<option value='92' style='background-color:#000080;'>".$ESHPCLRS['92']."</option>";
echo "<option value='93' style='background-color:#FDF5E6;'>".$ESHPCLRS['93']."</option>";
echo "<option value='94' style='background-color:#808000;'>".$ESHPCLRS['94']."</option>";
echo "<option value='95' style='background-color:#6B8E23;'>".$ESHPCLRS['95']."</option>";
echo "<option value='96' style='background-color:#FFA500;'>".$ESHPCLRS['96']."</option>";
echo "<option value='97' style='background-color:#FF4500;'>".$ESHPCLRS['97']."</option>";
echo "<option value='98' style='background-color:#DA70D6;'>".$ESHPCLRS['98']."</option>";
echo "<option value='99' style='background-color:#EEE8AA;'>".$ESHPCLRS['99']."</option>";
echo "<option value='100' style='background-color:#98FB98;'>".$ESHPCLRS['100']."</option>";
echo "<option value='101' style='background-color:#AFEEEE;'>".$ESHPCLRS['101']."</option>";
echo "<option value='102' style='background-color:#DB7093;'>".$ESHPCLRS['102']."</option>";
echo "<option value='103' style='background-color:#FFEFD5;'>".$ESHPCLRS['103']."</option>";
echo "<option value='104' style='background-color:#FFDAB9;'>".$ESHPCLRS['104']."</option>";
echo "<option value='105' style='background-color:#CD853F;'>".$ESHPCLRS['105']."</option>";
echo "<option value='106' style='background-color:#FFC0CB;'>".$ESHPCLRS['106']."</option>";
echo "<option value='107' style='background-color:#DDA0DD;'>".$ESHPCLRS['107']."</option>";
echo "<option value='108' style='background-color:#B0E0E6;'>".$ESHPCLRS['108']."</option>";
echo "<option value='109' style='background-color:#800080;'>".$ESHPCLRS['109']."</option>";
echo "<option value='110' style='background-color:#FF0000;'>".$ESHPCLRS['110']."</option>";
echo "<option value='111' style='background-color:#BC8F8F;'>".$ESHPCLRS['111']."</option>";
echo "<option value='112' style='background-color:#8B4513;'>".$ESHPCLRS['112']."</option>";
echo "<option value='113' style='background-color:#FA8072;'>".$ESHPCLRS['113']."</option>";
echo "<option value='114' style='background-color:#F4A460;'>".$ESHPCLRS['114']."</option>";
echo "<option value='115' style='background-color:#2E8B57;'>".$ESHPCLRS['115']."</option>";
echo "<option value='116' style='background-color:#FFF5EE;'>".$ESHPCLRS['116']."</option>";
echo "<option value='117' style='background-color:#A0522D;'>".$ESHPCLRS['117']."</option>";
echo "<option value='118' style='background-color:#C0C0C0;'>".$ESHPCLRS['118']."</option>";
echo "<option value='119' style='background-color:#87CEEB;'>".$ESHPCLRS['119']."</option>";
echo "<option value='120' style='background-color:#6A5ACD;'>".$ESHPCLRS['120']."</option>";
echo "<option value='121' style='background-color:#708090;'>".$ESHPCLRS['121']."</option>";
echo "<option value='122' style='background-color:#FFFAFA;'>".$ESHPCLRS['122']."</option>";
echo "<option value='123' style='background-color:#00FF7F;'>".$ESHPCLRS['123']."</option>";
echo "<option value='124' style='background-color:#4682B4;'>".$ESHPCLRS['124']."</option>";
echo "<option value='125' style='background-color:#D2B48C;'>".$ESHPCLRS['125']."</option>";
echo "<option value='126' style='background-color:#008080;'>".$ESHPCLRS['126']."</option>";
echo "<option value='127' style='background-color:#D8BFD8;'>".$ESHPCLRS['127']."</option>";
echo "<option value='128' style='background-color:#FF6347;'>".$ESHPCLRS['128']."</option>";
echo "<option value='129' style='background-color:#40E0D0;'>".$ESHPCLRS['129']."</option>";
echo "<option value='130' style='background-color:#EE82EE;'>".$ESHPCLRS['130']."</option>";
echo "<option value='131' style='background-color:#F5DEB3;'>".$ESHPCLRS['131']."</option>";
echo "<option value='132' style='background-color:#FFFFFF;'>".$ESHPCLRS['132']."</option>";
echo "<option value='133' style='background-color:#F5F5F5;'>".$ESHPCLRS['133']."</option>";
echo "<option value='134' style='background-color:#FFFF00;'>".$ESHPCLRS['134']."</option>";
echo "<option value='135' style='background-color:#9ACD32;'>".$ESHPCLRS['135']."</option>";
echo "</select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO121']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
<br />
<div id ='cList' style='width:250px;margin-top:5px;height:150px;overflow: auto;position:relative;'></div></td>
</tr>";
echo "</table>";

echo "</div><div style='float:right;width:50%;'>";
echo "<table width='100%' cellspacing='4' cellpadding='0' align='center'>";
echo "<tr><td align='left'>".$locale['ESHPPRO122']." </td><td align='left'><input type='text' name='iorder' value='$iorder' class='textbox' style='width:40px;'>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO123']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";
echo "<tr><td align='left'>".$locale['ESHPPRO124']." </td><td align='left'><input type='text' name='sellcount' value='$sellcount' class='textbox' style='width:40px;'>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO125']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";
echo "<tr><td align='left'>".$locale['ESHPPRO184']."</td><td align='left'><select name='campaign' class='textbox'>
       <option value='0'".($campaign == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
	   <option value='1'".($campaign == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
	   </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO185']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";

echo "<tr><td align='left'>".$locale['ESHPPRO182']."</td><td align='left'><select name='cupons' class='textbox'>
       <option value='1'".($cupons == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
	   <option value='0'".($cupons == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
	   </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO183']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";
echo "<tr><td align='left'>".$locale['ESHPPRO126']."</td><td align='left'><select name='gallery_on' class='textbox'>
       <option value='0'".($gallery_on == "0" ? " selected" : "").">".$locale['ESHPPRO127']."</option>
	   <option value='1'".($gallery_on == "1" ? " selected" : "").">".$locale['ESHPPRO128']."</option>
      </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO129']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";
echo "<tr><td align='left'>".$locale['ESHPPRO130']."</td><td align='left'><input type='text' name='image' value='$image_url' class='textbox' readonly style='width:200px;'></td></tr>
<tr><td align='left'>".$locale['ESHPPRO131']."</td><td align='left'><input type='text' name='thumb' value='$thumb_url' class='textbox' readonly style='width:200px;'></td></tr>
<tr><td align='left'>".$locale['ESHPPRO132']."</td><td align='left'><input type='text' name='thumb2' value='$thumb2_url' class='textbox' readonly style='width:200px;'></td></tr>";
echo "<tr><td align='left'>".$locale['ESHPPRO133']."</td><td align='left'><input type='text' name='version' value='$version' class='textbox' style='width:60px;'>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO134']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";
echo "<tr><td align='left'>".$locale['ESHPPRO135']."</td><td align='left'><input type='text' name='demo' value='$demo' class='textbox' style='width:170px;'>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO136']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";
echo "<tr><td align='left'>".$locale['ESHPPRO137']."</td><td align='left'><select name='stock' class='textbox'>
      <option value='1'".($stock == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
	  <option value='0'".($stock == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
	  </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO140']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";
echo "<tr><td align='left'>".$locale['ESHPPRO141']."</td><td align='left'><input type='text' name='instock' value='$instock' class='textbox' style='width:40px;'>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO142']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";
echo "<tr><td align='left'>".$locale['ESHPPRO143']."</td><td align='left'><input type='text' name='delivery' value='$delivery' class='textbox' style='width:170px;' />
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO144']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";

echo "<tr><td align='left'>".$locale['ESHPPRO145']."</td><td align='left'><select name='status' class='textbox'>
      <option value='1'".($status == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
      <option value='0'".($status == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
      </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO146']." 
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";

echo "<tr><td align='left'>".$locale['ESHPPRO147']."</td><td align='left'><select name='active' class='textbox'>
      <option value='1'".($active == "1" ? " selected" : "").">".$locale['ESHPPRO128']."</option>
      <option value='0'".($active == "0" ? " selected" : "").">".$locale['ESHPPRO127']."</option>
      </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO148']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";
echo "<tr><td align='left'>".$locale['ESHPPRO149']."</td><td align='left'><select name='cart_on' class='textbox'>
      <option value='1'".($cart_on == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
      <option value='0'".($cart_on == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
      </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO150']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";
echo "<tr><td align='left'>Allow to buy multiple items</td><td align='left'><select name='qty' class='textbox'>
      <option value='1'".($qty == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
      <option value='0'".($qty == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
      </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO151']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";

echo "<tr><td align='left'>".$locale['ESHPPRO152']."</td><td align='left'><input type='text' name='dmulti' value='$dmulti' class='textbox' style='width:40px;'>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO153']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";

echo "<tr><td align='left'>".$locale['ESHPPRO154']."</td><td align='left'><select name='buynow' class='textbox'>
      <option value='0'".($buynow == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
      <option value='1'".($buynow == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
      </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO155']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";

echo "<tr><td align='left'>".$locale['ESHPPRO156']."</td><td>";
    $callback_dir = makefilelist(BASEDIR."eshop/purchasescripts/",  ".|..|index.php", true, "files");
  echo "<select name='rpage' class='textbox' style='width:180px;' >";
  echo "<option value=''>".$locale['ESHPPRO157']."</option>";
     foreach($callback_dir as $callback){
  echo "<option value='$callback' ".($callback == "$rpage" ? " selected" : "").">$callback</option>";
 }
echo "</select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO158']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";
echo "<tr><td align='left'>".$locale['ESHPCATS109']."</td>
<td align='left'><select name='access' class='textbox'>
$visibility_opts</select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO159']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></a>
</td></tr>";

echo "</table>";
echo "</div>";
echo "<div style='clear:both;'></div>";

echo "<table width='100%' cellspacing='4' cellpadding='0'>";
if ($settings['eshop_pretext'] == "1") {
echo "<tr><td align='left'>".$locale['ESHPPRO160']."</td><td align='left'><textarea name='introtext' cols='90' rows='3' class='textbox span6'>$introtext</textarea>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO161']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:top;' /></a>
</td></tr>";
} else {
echo "<input type='hidden' name='introtext' value='$introtext'>";
}

echo "<tr><td align='left'>".$locale['ESHPPRO162']."</td><td align='left'><textarea name='description' cols='90' rows='6' class='textbox span6'>$description</textarea>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO163']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:top;' /></a>
</td></tr>";
echo "</table>\n";

echo '<br /><center><a href="#" onClick="showexttabs(); return false;" class="button"><b>'.$locale['ESHPPRO164'].'</b></a></center><br />';
echo "<div id='exttabs'>";
echo "<table width='100%' cellspacing='4' cellpadding='0'>";
echo "<tr><td align='left'><input type='text' name='anything1n' value='$anything1n' class='textbox' style='width:90px;'></td><td align='left'><textarea name='anything1' cols='90' rows='6' class='textbox span6'>$anything1</textarea>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO165']."
</span><img src='".BASEDIR."eshop/img/helper.png' height='25' border='0' alt='' style='vertical-align:top;' /></a>

</td></tr>";
echo "<tr><td align='left'><input type='text' name='anything2n' value='$anything2n' class='textbox' style='width:90px;'></td><td align='left'><textarea name='anything2' cols='90' rows='6' class='textbox span6'>$anything2</textarea></td></tr>";
echo "<tr><td align='left'><input type='text' name='anything3n' value='$anything3n' class='textbox' style='width:90px;'></td><td align='left'><textarea name='anything3' cols='90' rows='6' class='textbox span6'>$anything3</textarea></td></tr>";
echo "</table></div>\n";

echo "<table width='100%' cellspacing='4' cellpadding='0'>";
echo"<tr><td align='center' colspan='3'><input type='submit' name='save_cat' value='".$locale['ESHPPRO166']."' class='button'></td></tr>";
echo "</table></div></form>\n";
}
echo "<hr />";
//Do this if cats are on
if ($settings['eshop_cats'] == "1" && !isset($_REQUEST['category'])) {
//Search for orphan items before we select an category.
echo "<div class='admin-message'>".$locale['ESHPPRO167']."</div>";
$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE cid=''");
$rows = dbrows($result);
if ($rows != 0) {

echo "<table align='center' cellspacing='4' cellpadding='0' width='99%'><tr>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPPRO168']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPPRO169']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPPRO170']."</b></td>
</tr>\n";
while ($data = dbarray($result)) {
echo "<tr style='height:20px;' onMouseOver=\"this.className='tbl2'\" onMouseOut=\"this.className='tbl1'\">";
echo "<td width='1%' align='left'><a href='".FUSION_SELF.$aidlink."&amp;a_page=Main&action=edit&amp;category=0&amp;id=".$data['id']."'><b>".$data['title']."</b></a></td>\n";
echo "<td width='1%' align='center'>".$locale['ESHPPRO180']."</td>\n";
echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;action=delete&id=".$data['id']."' onClick='return confirmdelete();'><img src='".BASEDIR."eshop/img/remove.png' border='0' height='20' style='vertical-align:middle;'  alt='' /></a></td></tr>";
echo "</td>";
}
echo "</table>\n";

} else {
echo "<center><br />\n ".$locale['ESHPPRO171']." <br /><br />\n</center>\n";
 }
} else {

if (!isset($_GET['sortby']) || !preg_match("/^[0-9A-Z]$/", $_GET['sortby'])) $_GET['sortby'] = "all";

if ($settings['eshop_cats'] == "1") {
$orderby = ($_GET['sortby'] == "all" ? "" : " WHERE title LIKE '".$_GET['sortby']."%' AND cid = '".$_REQUEST['category']."'");
if (isset($_GET['sortby']) && $_GET['sortby'] == "all") {
$srtmtd = "WHERE cid = '".$_REQUEST['category']."'";
} else {
$srtmtd = "";
 }
} else {
$orderby = ($_GET['sortby'] == "all" ? "" : " WHERE title LIKE '".$_GET['sortby']."%' AND cid = ''");
if (isset($_GET['sortby']) && $_GET['sortby'] == "all") {
$srtmtd = "WHERE cid = ''";
} else {
$srtmtd = "";
  }
}

$result = dbquery("SELECT * FROM ".DB_ESHOP." ".$srtmtd." ".$orderby." ORDER BY iorder, title");
$rows = dbrows($result);

if ($rows != 0) {
	$i = 0; $k = 1;

echo "<table align='center' cellspacing='4' cellpadding='0' width='99%' class='tbl-border'><tr>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPPRO172']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPPRO173']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPPRO174']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPPRO175']."</b></td>
<td class='tbl2' width='1%' align='center'><b>".$locale['ESHPPRO176']."</b></td>
</tr>\n";

$result = dbquery("SELECT * FROM ".DB_ESHOP." ".$srtmtd." ".$orderby." ORDER BY iorder, title LIMIT ".$_GET['rowstart'].",15");

while ($data = dbarray($result)) {
echo "<tr style='height:20px;' onMouseOver=\"this.className='tbl2'\" onMouseOut=\"this.className='tbl1'\">";
echo "<td width='1%' align='left'><a href='".FUSION_SELF.$aidlink."&amp;a_page=Main&action=edit&id=".$data['id']."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."'><b>".$data['title']."</b></a></td>\n";
echo "<td width='1%' align='center'> ".($data['artno'] !== '' ? "".$data['artno']."" : "".$data['id']."")." </td>";
echo "<td width='1%' align='center'> ".($data['sartno'] !== '' ? "".$data['sartno']."" : "N/A")." </td>";
echo "<td align='center' width='1%'>\n";
if (dbrows($result) != 1) {
$up = $data['iorder'] - 1;
$down = $data['iorder'] + 1;
if ($k == 1) {
echo "<a href='".FUSION_SELF.$aidlink."&amp;action=movedown&amp;order=$down&amp;id=".$data['id']."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."'><img src='".get_image("down")."' alt='Move down' title='Move down' style='border:0px;' /></a>\n";
} elseif ($k < dbrows($result)) {
echo "<a href='".FUSION_SELF.$aidlink."&amp;action=moveup&amp;order=$up&amp;id=".$data['id']."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."'><img src='".get_image("up")."' alt='Move up' title='Move up' style='border:0px;' /></a>\n";
echo "<a href='".FUSION_SELF.$aidlink."&amp;action=movedown&amp;order=$down&amp;id=".$data['id']."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."'><img src='".get_image("down")."' alt='Move down' title='Move down'  style='border:0px;' /></a>\n";
} else {
echo "<a href='".FUSION_SELF.$aidlink."&amp;action=moveup&amp;order=$up&amp;id=".$data['id']."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."'><img src='".get_image("up")."' alt='Move up' title='Move up' style='border:0px;' /></a>\n";
 }
}
$k++;
echo " #".$data['iorder']."</td>\n";
echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;action=delete&id=".$data['id']."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."' onClick='return confirmdelete();'><img src='".BASEDIR."eshop/img/remove.png' border='0' height='20' style='vertical-align:middle;'  alt='' /></a></td></tr>";
echo "</td>";
}
echo "</table>\n";

echo "<div align='center' style='margin-top:5px;'>".makePageNav($_GET['rowstart'],15,$rows,3,FUSION_SELF.$aidlink."&amp;sortby=".$_GET['sortby']."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."&")."\n</div>\n";
} else {
echo "<center><br />\n <b>".$locale['ESHPPRO177']."</b> <br /><br />\n</center>\n";
}
$search = array(
"A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R",
"S","T","U","V","W","X","Y","Z","0","1","2","3","4","5","6","7","8","9");
echo "<hr><table align='center' cellpadding='0' cellspacing='1' class='tbl-border'>\n<tr>\n";
echo "<td rowspan='2' class='tbl2'><a href='".FUSION_SELF.$aidlink."&amp;sortby=all".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."'>".$locale['ESHP425']."</a></td>";
for ($i=0;$i < 36!="";$i++) {
echo "<td align='center' class='tbl1'><div class='small'><a href='".FUSION_SELF.$aidlink."&amp;sortby=".$search[$i]."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."'>".$search[$i]."</a></div></td>";
echo ($i==17 ? "<td rowspan='2' class='tbl2'><a href='".FUSION_SELF.$aidlink."&amp;sortby=all".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."'>".$locale['ESHP426']."</a></td>\n</tr>\n<tr>\n" : "\n");
}
echo "</table>\n";
if (dbrows($result)) { echo "<div style='text-align:center;margin-top:5px'>[ <a href='".FUSION_SELF.$aidlink."&amp;action=refresh".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."'> ".$locale['ESHPPRO179']." </a> ]</div>\n"; }
 }
}

if (isset($_POST['psrchtext'])) {
$searchtext = stripinput($_POST['psrchtext']);
} else { $searchtext = $locale['SRCH162']; }

echo "<div style='float:right;margin-top:5px;'><form id='search_form'  name='inputform' method='post' action='".FUSION_SELF.$aidlink."&amp;psearch'>
<span style='vertical-align:middle;font-size:14px;'>".$locale['ESHPPRO178']."</span>";
echo "<input type='text' name='psrchtext' class='textbox' style='margin-left:1px; margin-right:1px; margin-bottom:5px; width:160px;'  value='".$searchtext."' onblur=\"if(this.value=='') this.value='".$searchtext."';\" onfocus=\"if(this.value=='".$searchtext."') this.value='';\" />";
echo "<input type='image' id='search_image' src='".BASEDIR."eshop/img/search_icon.png' alt='".$locale['SRCH162']."' />";
echo "</form></div>";
?>