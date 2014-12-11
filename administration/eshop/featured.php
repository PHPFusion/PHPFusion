<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: featured.php
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

$fthumbs_per_row = "3";

if (isset($_GET['action']) && $_GET['action'] == "refresh") {
	$i = 1;
	$result = dbquery("SELECT * FROM ".DB_ESHOP_FEATITEMS." WHERE featitem_cid = '".$_REQUEST['category']."' ORDER BY featitem_order");
	while ($data = dbarray($result)) {
	       $result2 = dbquery("UPDATE ".DB_ESHOP_FEATITEMS." SET featitem_order='$i' WHERE featitem_item='".$data['featitem_item']."'");
	       $i++;
	}
	redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;category=".$_REQUEST['category']."&amp;refreshed");
}

if (isset($_GET['action']) && $_GET['action'] == "refreshb") {
	$i = 1;
	$result = dbquery("SELECT * FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_cid = '".$_REQUEST['category']."' ORDER BY featbanner_order");
	while ($data = dbarray($result)) {
	       $result2 = dbquery("UPDATE ".DB_ESHOP_FEATBANNERS." SET featbanner_order='$i' WHERE featbanner_aid='".$data['featbanner_aid']."'");
	       $i++;
	}
	redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;category=".$_REQUEST['category']."&amp;refreshedb");
}

if (isset($_GET['action']) && $_GET['action'] == "mupb") {
	if (!isnum($_GET['order'])) { die("Denied"); }
	$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_cid = '".$_REQUEST['category']."' AND featbanner_order='".$_GET['order']."'"));
	$result = dbquery("UPDATE ".DB_ESHOP_FEATBANNERS." SET featbanner_order=featbanner_order+1 WHERE featbanner_aid='".$data['featbanner_aid']."'");
	$result = dbquery("UPDATE ".DB_ESHOP_FEATBANNERS." SET featbanner_order=featbanner_order-1 WHERE featbanner_aid='".$_GET['id']."'");
	redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;category=".$_REQUEST['category']."&amp;rowstart=".$_GET['rowstart']."");
} 

if (isset($_GET['action']) && $_GET['action'] == "mdownb") {
	if (!isnum($_GET['order'])) { die("Denied"); }
	$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_cid = '".$_REQUEST['category']."' AND featbanner_order='".$_GET['order']."'"));
	$result = dbquery("UPDATE ".DB_ESHOP_FEATBANNERS." SET featbanner_order=featbanner_order-1 WHERE featbanner_aid='".$data['featbanner_aid']."'");
	$result = dbquery("UPDATE ".DB_ESHOP_FEATBANNERS." SET featbanner_order=featbanner_order+1 WHERE featbanner_aid='".$_GET['id']."'");
	redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;category=".$_REQUEST['category']."&amp;rowstart=".$_GET['rowstart']."");
} 


if (isset($_GET['action']) && $_GET['action'] == "mup") {
	if (!isnum($_GET['order'])) { die("Denied"); }
	$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_FEATITEMS." WHERE featitem_cid = '".$_REQUEST['category']."' AND featitem_order='".$_GET['order']."'"));
	$result = dbquery("UPDATE ".DB_ESHOP_FEATITEMS." SET featitem_order=featitem_order+1 WHERE featitem_item='".$data['featitem_item']."'");
	$result = dbquery("UPDATE ".DB_ESHOP_FEATITEMS." SET featitem_order=featitem_order-1 WHERE featitem_item='".$_GET['id']."'");
	redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;category=".$_REQUEST['category']."");
} 

if (isset($_GET['action']) && $_GET['action'] == "mdown") {
	if (!isnum($_GET['order'])) { die("Denied"); }
	$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_FEATITEMS." WHERE featitem_cid = '".$_REQUEST['category']."' AND featitem_order='".$_GET['order']."'"));
	$result = dbquery("UPDATE ".DB_ESHOP_FEATITEMS." SET featitem_order=featitem_order-1 WHERE featitem_item='".$data['featitem_item']."'");
	$result = dbquery("UPDATE ".DB_ESHOP_FEATITEMS." SET featitem_order=featitem_order+1 WHERE featitem_item='".$_GET['id']."'");
	redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;category=".$_REQUEST['category']."");
} 


if (isset($_GET['action']) && $_GET['action'] == "addbanners") {

$cid = stripinput($_REQUEST['category']);
$id = stripinput($_POST['id']);
$cat = stripinput($_POST['cat']);
$url = stripinput($_POST['url']);
$featbanner_order = stripinput($_POST['order']);
	
if (!empty($_FILES['files'])) {
define("FPHOTOROOT", BASEDIR."eshop/pictures/banners/");
@mkdir(FPHOTOROOT."category_".$_REQUEST['category'], 0755);
@copy(IMAGES."index.php", FPHOTOROOT."category_".$_REQUEST['category']."/index.php");
define("FPHOTOS", BASEDIR."eshop/pictures/banners/category_".$_REQUEST['category']."/");
$target_path = FPHOTOS;

function attach_exists($file) {
	$dir = FPHOTOS;
	$i = 1;
	$file_name = substr($file, 0, strrpos($file, "."));
	$file_ext = strrchr($file, ".");
	while (file_exists($dir.$file)) {
		$file = $file_name."_".$i.$file_ext;
		$i++;
	}
	return $file;
}
$error = "";
foreach($_FILES as $attach){
	if ($attach['name'] != "" && !empty($attach['name']) && is_uploaded_file($attach['tmp_name'])) {
		$attachname = stripfilename(substr($attach['name'], 0, strrpos($attach['name'], ".")));
		$attachext = strtolower(strrchr($attach['name'],"."));
		if (preg_match("/^[-0-9A-Z_\[\]]+$/i", $attachname)) {
			$attachtypes = explode(",", $settings['attachtypes']);
		if (in_array($attachext, $attachtypes)) {
			$attachname .= $attachext;
			$attachname = attach_exists(strtolower($attachname));
			move_uploaded_file($attach['tmp_name'], FPHOTOS."".$attachname);
			chmod(FPHOTOS."".$attachname,0644);
		if (!@getimagesize(FPHOTOS."".$attachname) || !@verify_image(FPHOTOS."".$attachname)) {
			unlink(FPHOTOS."".$attachname);
			$error = 1;
			}
		if (!$error) { 
	$result = dbquery("UPDATE ".DB_ESHOP_FEATBANNERS." SET featbanner_order=featbanner_order+1 WHERE featbanner_cid = '".$_REQUEST['category']."' AND featbanner_order>='$featbanner_order'");		
	if (!$featbanner_order) { $featbanner_order = dbresult(dbquery("SELECT MAX(featbanner_order) FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_cid = '".$_REQUEST['category']."'"), 0) + 1; }
	$result = dbquery("INSERT INTO ".DB_ESHOP_FEATBANNERS." (featbanner_aid,featbanner_id,featbanner_url,featbanner_cat,featbanner_banner,featbanner_cid,featbanner_order) VALUES('','".$id."','".$url."','".$cat."','".$attachname."','".$cid."','".$featbanner_order."')");
  }
} else {
	@unlink($attach['tmp_name']);
	echo " Not allowed file type "; //Redirects anyway noone will see it as it is!
 }
} else {
	@unlink($attach['tmp_name']);
	echo " Invalid Filename "; //Redirects anyway noone will see it as it is!
   }
  }
 }
}
redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;category=".$_REQUEST['category']."&amp;banneradded");
}


if (isset($_GET['action']) && $_GET['action'] == "additem") {
	$id = stripinput($_POST['id']);
	$cid = stripinput($_REQUEST['category']);
	$featitem_order = stripinput($_POST['order']);
	if ($id >0) {
	$result = dbquery("UPDATE ".DB_ESHOP_FEATITEMS." SET featitem_order=featitem_order+1 WHERE featitem_cid = '".$_REQUEST['category']."' AND featitem_order>='$featitem_order'");	
	if (!$featitem_order) { $featitem_order = dbresult(dbquery("SELECT MAX(featitem_order) FROM ".DB_ESHOP_FEATITEMS." WHERE featitem_cid = '".$_REQUEST['category']."'"), 0) + 1; }
	$result = dbquery("INSERT INTO ".DB_ESHOP_FEATITEMS." (featitem_id,featitem_item,featitem_cid,featitem_order) VALUES('','$id','$cid','$featitem_order')");
	}
	redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;category=".$_REQUEST['category']."&amp;itemadded");
}

if (isset($_GET['action']) && $_GET['action'] == "delitem") {
	$remove = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_FEATITEMS." WHERE featitem_item='".$_GET['id']."'"));
	$result = dbquery("UPDATE ".DB_ESHOP_FEATITEMS." SET featitem_order=featitem_order-1 WHERE featitem_order>'".$remove['featitem_order']."' AND featitem_cid = '".$remove['featitem_cid']."'");
	$result = dbquery("DELETE FROM ".DB_ESHOP_FEATITEMS." WHERE featitem_item='".$_GET['id']."'");
	redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;category=".$_REQUEST['category']."&amp;iremoved");
}

if (isset($_GET['action']) && $_GET['action'] == "delbanner") {
	$remove = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_id='".$_GET['id']."'"));
	$result = dbquery("UPDATE ".DB_ESHOP_FEATBANNERS." SET featbanner_order=featbanner_order-1 WHERE featbanner_order>'".$remove['featbanner_order']."' AND featbanner_cid = '".$remove['featbanner_cid']."'");
	$result = dbquery("DELETE FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_id='".$_GET['id']."'");
	define("FPHOTOS", BASEDIR."eshop/pictures/banners/category_".$_REQUEST['category']."/");
	unlink(FPHOTOS."".$remove['featbanner_banner']);
	redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;category=".$_REQUEST['category']."&amp;bremoved");
}

if (!isset($_REQUEST['category']) && !isset($_GET['action'])) {
echo "<br /><form name='sectionform' method='post' action='".FUSION_SELF.$aidlink."&amp;a_page=featured'>";
echo "<table width='100%' cellspacing='4' cellpadding='0' align='center' class='tbl-border'>";
echo "<tr><td align='left' style='width:100px;'>".$locale['ESHFEAT123']." </td><td align='left'><select class='textbox' name='category' style='width:400px;' onchange=\"this.form.submit()\">";
echo "<option value=''>".$locale['ESHFEAT101']."</option>";
echo "<option value='0'>".$locale['ESHFEAT102']."</option>";
$result=dbquery("SELECT * FROM ".DB_ESHOP_CATS." ORDER BY parentid, title");
while($cat_data = dbarray($result)) {
if ($cat_data['parentid']!=0) $cat_data['title']=getparent($cat_data['parentid'],$cat_data['title']);
echo "<option value='".$cat_data['cid']."'>".$cat_data['title']."</option>";
}
echo "</select></td></tr></table></form><br />";
}

if (isset($_REQUEST['category']) && !isset($_GET['action'])) {
echo "<div style='float:left;width:49%;'>";
echo"<fieldset><legend align='center' style='margin-left:2px !important; width:85% !important;'>&nbsp;<b> ".$locale['ESHFEAT103']." </b>&nbsp;</legend>";

if (isset($_GET['itemadded'])) {
	echo "<div class='admin-message' style='width:350px;'> ".$locale['ESHFEAT104']." </div>";
}

if (isset($_GET['iremoved'])) {
	echo "<div class='admin-message' style='width:350px;'> ".$locale['ESHFEAT105']." </div>";
}

if (isset($_GET['refreshed'])) {
	echo "<div class='admin-message' style='width:350px;'> ".$locale['ESHFEAT106']." </div>";
}


//save items
echo "<form enctype='multipart/form-data' name='subsectionform' method='post' action='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;category=".$_REQUEST['category']."&amp;action=additem'>";
echo "<br /><br /><table width='100%' cellspacing='4' cellpadding='0' align='center'>";
echo "<tr><td align='left' style='width:100px;'>".$locale['ESHFEAT107']." </td><td align='left' style='width:180px;'><select class='textbox' name='id' style='width:220px !important;'>";
echo "<option value=''>".$locale['ESHFEAT108']."</option>";

if (isset($_REQUEST['category']) && $_REQUEST['category'] ==! 0) {
//let´s make a construct of all sub cats here.
$cats = "";
$result = dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE cid = '".$_REQUEST['category']."' OR parentid = '".$_REQUEST['category']."'");
while($data = dbarray($result)) {
$cats.="".$data['cid']." ";
}
$categorys=ltrim($cats);
$categorys=rtrim($categorys);
$q = "";
$kt = "";
$val = "";
$kt = explode(" ",$categorys);
while(list($key,$val)=each($kt)){
if($val<>" " and strlen($val) > 0){ $q.= " cid = '".$val."' OR"; }
}
$q=substr($q,0,(strlen($q)-3));
$result=dbquery("SELECT * FROM ".DB_ESHOP." WHERE ".$q." ORDER BY title ASC");
} else {
$result=dbquery("SELECT * FROM ".DB_ESHOP." ORDER BY title ASC");
}

while($data = dbarray($result)) {
$checkdupe = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_FEATITEMS." WHERE featitem_cid = '".$_REQUEST['category']."' AND featitem_item='".$data['id']."'"));
if (!$checkdupe['featitem_item']) {
echo "<option value='".$data['id']."'>".$data['title']."</option>";
 }
}
echo "</select></td></tr>";
echo "<tr><td align='left'>".$locale['ESHFEAT122']."</td><td align='left'><input type='text' name='order' value='' class='textbox' style='width:40px;' /></td></tr>";
echo"<tr><td align='center' colspan='2'><div style='margin-top:10px;'></div><input type='submit' name='save' value='".$locale['ESHPPRO166']." ".$locale['ESHFEAT124']."' class='button'></td></tr>";
echo "</table><div style='padding-top:24px;'></div></form><br /></fieldset></div>";

echo "<div style='float:right;width:49%;'>";
echo"<fieldset><legend align='center' style='margin-left:2px !important; width:85% !important;'>&nbsp;<b> ".$locale['ESHFEAT109']." </b>&nbsp;</legend>";
if (isset($_GET['banneradded'])) {
	echo "<div class='admin-message' style='width:350px;'> ".$locale['ESHFEAT110']." </div>";
}

if (isset($_GET['refreshedb'])) {
	echo "<div class='admin-message' style='width:350px;'> ".$locale['ESHFEAT111']." </div>";
}

if (isset($_GET['bremoved'])) {
	echo "<div class='admin-message' style='width:350px;'> ".$locale['ESHFEAT112']." </div>";
}

//banner form
echo "<form enctype='multipart/form-data' name='bannerform' method='post' action='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;category=".$_REQUEST['category']."&amp;action=addbanners'>";
echo "<table width='100%' cellspacing='4' cellpadding='0' align='center'>";
echo "<tr><td align='left' style='width:80px;'>".$locale['ESHFEAT113']." </td><td align='left' style='width:180px;'><select class='textbox' name='id' style='width:220px !important;'>";
echo "<option value=''>".$locale['ESHFEAT114']."</option>";
//$result=dbquery("SELECT * FROM ".DB_ESHOP." WHERE cid = '".$_REQUEST['category']."' ORDER BY title ASC");

if (isset($_REQUEST['category']) && $_REQUEST['category'] ==! 0) {
//let´s make a construct of all sub cats here.
$cats = "";
$result = dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE cid = '".$_REQUEST['category']."' OR parentid = '".$_REQUEST['category']."'");
while($data = dbarray($result)) {
$cats.="".$data['cid']." ";
}
$categorys=ltrim($cats);
$categorys=rtrim($categorys);
$q = "";
$kt = "";
$val = "";
$kt = explode(" ",$categorys);
while(list($key,$val)=each($kt)){
if($val<>" " and strlen($val) > 0){ $q.= " cid = '".$val."' OR"; }
}
$q=substr($q,0,(strlen($q)-3));
$result=dbquery("SELECT * FROM ".DB_ESHOP." WHERE ".$q." ORDER BY title ASC");
} else {
$result=dbquery("SELECT * FROM ".DB_ESHOP." ORDER BY title ASC");
}

while($data = dbarray($result)) {
$checkdupe = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_cid = '".$_REQUEST['category']."' AND featbanner_id='".$data['id']."'"));
if (!$checkdupe['featbanner_id']) {
echo "<option value='".$data['id']."'>".$data['title']."</option>";
 }
}
echo "</select></td></tr>";

echo "<tr><td align='left' style='width:80px;'>".$locale['ESHFEAT125']." </td><td align='left'><select class='textbox' name='cat' style='width:220px !important;'>";
echo "<option value=''>".$locale['ESHFEAT126']."</option>";
$result=dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE parentid = '".$_REQUEST['category']."' ORDER BY parentid, title");
while($cat_data = dbarray($result)) {
if ($cat_data['parentid']!=0) $cat_data['title']=getparent($cat_data['parentid'],$cat_data['title']);
echo "<option value='".$cat_data['cid']."'>".$cat_data['title']."</option>";
}
echo "</select></td></tr>";

echo "<tr><td align='left' style='width:80px;'>".$locale['ESHFEAT127']."</td><td align='left' style='width:80px;'><input type='text' name='url' value='' class='textbox' style='width:180px !important;' /></td></tr>\n";

echo "<tr><td align='left' style='width:80px;'>".$locale['ESHFEAT115']."</td><td align='left'><input type='file' name='files' class='textbox' style='width:160px;' />".$locale['ESHFEAT122']."<input type='text' name='order' value='' class='textbox' style='width:20px;' /><br /></td></tr>\n";
echo"<tr><td align='center' colspan='2'><input type='submit' name='save' value='".$locale['ESHPPRO166']." ".$locale['ESHFEAT116']."' class='button'></td></tr>";
echo "</table></form><br /></fieldset></div>";
echo "<div class='clear'></div>";
echo "<hr />";
//List Banners

//Front view start
$result = dbquery("select * FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_cid = '".$_REQUEST['category']."' ORDER BY featbanner_order ASC");
$rows = dbrows($result);
if ($rows) {
$result = dbquery("select * FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_cid = '".$_REQUEST['category']."' ORDER BY featbanner_order LIMIT ".$_GET['rowstart'].",1");
	$counter = 0; $k = ($_GET['rowstart'] == 0 ? 1 : $_GET['rowstart'] + 1);
	echo "<table cellpadding='0' cellspacing='0' width='100%' align='center'><tr>\n";
	while ($data = dbarray($result)) {
	$itemdatab = dbarray(dbquery("SELECT * FROM ".DB_ESHOP." WHERE id = '".$data['featbanner_id']."'"));
	$upb = ""; $downb = "";
		if ($rows != 1){
			$orderub = $data['featbanner_order'] - 1;
			$orderdb = $data['featbanner_order'] + 1;
			if ($k == 1) {
				$downb = " &middot;\n<a href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;id=".$data['featbanner_aid']."&amp;category=".$_REQUEST['category']."&amp;action=mdownb&amp;order=$orderdb&amp;rowstart=".$_GET['rowstart']."'><img src='".THEME."images/right.gif' alt='".$ESHPHOTOSL['467']."' title='".$ESHPHOTOSL['467']."' border='0' style='vertical-align:middle'></a>\n";
			} elseif ($k < $rows){
				$upb = "<a href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;id=".$data['featbanner_aid']."&amp;category=".$_REQUEST['category']."&amp;action=mupb&amp;order=$orderub&amp;rowstart=".$_GET['rowstart']."'><img src='".THEME."images/left.gif' alt='".$ESHPHOTOSL['468']."' title='".$ESHPHOTOSL['468']."' border='0' style='vertical-align:middle'></a> &middot;\n";
				$downb = " &middot;\n<a href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;id=".$data['featbanner_aid']."&amp;category=".$_REQUEST['category']."&amp;action=mdownb&amp;order=$orderdb&amp;rowstart=".$_GET['rowstart']."'><img src='".THEME."images/right.gif' alt='".$ESHPHOTOSL['467']."' title='".$ESHPHOTOSL['467']."' border='0' style='vertical-align:middle'></a>\n";
			} else {
				$upb = "<a href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;id=".$data['featbanner_aid']."&amp;category=".$_REQUEST['category']."&amp;action=mupb&amp;order=$orderub&amp;rowstart=".$_GET['rowstart']."'><img src='".THEME."images/left.gif' alt='".$ESHPHOTOSL['468']."' title='".$ESHPHOTOSL['468']."' border='0' style='vertical-align:middle'></a> &middot;\n";
			}
		}
		if ($counter != 0 && ($counter % 1	== 0)) echo "</tr>\n<tr>\n";
    echo "<td align='center' class='tbl'>\n";
		echo "<b>".$data['featbanner_order']." - ".$itemdatab['title']." - ".$data['featbanner_banner']."</b><br /><br />\n";
		echo "<a href='".BASEDIR."eshop/eshop.php?product=".$data['featbanner_id']."'><img style='width:728px;height:90px;' src='".($data['featbanner_banner'] ? "".checkeShpImageExists(BASEDIR."eshop/pictures/banners/category_".$_REQUEST['category']."/".$data['featbanner_banner'])."" : "".BASEDIR."eshop/img/nopic_thumb.gif")."' alt='' border='0' style='padding:4px;' /></a>";
		echo "<br /><br />\n ".$upb;
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;action=delbanner&amp;category=".$_REQUEST['category']."&amp;id=".$data['featbanner_id']."'>".$locale['ESHFEAT121']."</a> ".$downb;
	echo "</td>\n";
		$counter++; $k++;
}
	echo "</tr>\n</table>\n";
	if ($rows > 1) echo "<div align='center' style='margin-top:5px;'>\n".makeeshoppagenav($_GET['rowstart'],1,$rows,3,FUSION_SELF.$aidlink."&amp;a_page=featured&amp;category=".$_REQUEST['category']."&amp;")."\n</div>\n";
} else {
	echo "<div class='admin-message'> ".$locale['ESHFEAT117']." </div>";
}

//List items
echo "<hr />";
$result= dbquery("SELECT * FROM ".DB_ESHOP_FEATITEMS." 	WHERE featitem_cid = '".$_REQUEST['category']."' ORDER BY featitem_order ASC");
$rows = dbrows($result);
if ($rows != 0) {

$counter = 0; $k = 1;
	echo "<table cellpadding='0' cellspacing='1' width='100%'>\n<tr>\n";
	while ($data = dbarray($result)) {
	$itemdata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP." WHERE id = '".$data['featitem_item']."'"));
		$up = ""; $down = "";
		if ($rows != 1){
			$orderu = $data['featitem_order'] - 1;
			$orderd = $data['featitem_order'] + 1;
			if ($k == 1) {
				$down = " &middot;\n<a href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;id=".$itemdata['id']."&amp;category=".$_REQUEST['category']."&amp;action=mdown&amp;order=$orderd'><img src='".THEME."images/right.gif' alt='".$ESHPHOTOSL['467']."' title='".$ESHPHOTOSL['467']."' border='0' style='vertical-align:middle'></a>\n";
			} elseif ($k < $rows){
				$up = "<a href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;id=".$itemdata['id']."&amp;category=".$_REQUEST['category']."&amp;action=mup&amp;order=$orderu'><img src='".THEME."images/left.gif' alt='".$ESHPHOTOSL['468']."' title='".$ESHPHOTOSL['468']."' border='0' style='vertical-align:middle'></a> &middot;\n";
				$down = " &middot;\n<a href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;id=".$itemdata['id']."&amp;category=".$_REQUEST['category']."&amp;action=mdown&amp;order=$orderd'><img src='".THEME."images/right.gif' alt='".$ESHPHOTOSL['467']."' title='".$ESHPHOTOSL['467']."' border='0' style='vertical-align:middle'></a>\n";
			} else {
				$up = "<a href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;id=".$itemdata['id']."&amp;category=".$_REQUEST['category']."&amp;action=mup&amp;order=$orderu'><img src='".THEME."images/left.gif' alt='".$ESHPHOTOSL['468']."' title='".$ESHPHOTOSL['468']."' border='0' style='vertical-align:middle'></a> &middot;\n";
			}
		}
		if ($counter != 0 && ($counter % $fthumbs_per_row == 0)) echo "</tr>\n<tr>\n";
		echo "<td align='center' valign='top' class='tbl'>\n";
		echo "<b>".$data['featitem_order']." ".$itemdata['title']."</b><br /><br />\n";
		if ($settings['eshop_ratios'] == "1") {
		echo "<a href='".BASEDIR."eshop/eshop.php?product=".$itemdata['id']."'><img src='".($itemdata['thumb'] ? "".checkeShpImageExists(BASEDIR."eshop/pictures/".$itemdata['thumb']."")."" : "".BASEDIR."eshop/img/nopic_thumb.gif")."' alt='' height='100%' border='0' style='padding:4px;' /></a>";
} else {
	echo "<a href='".BASEDIR."eshop/eshop.php?product=".$itemdata['id']."'><img src='".($itemdata['thumb'] ? "".checkeShpImageExists(BASEDIR."eshop/pictures/".$itemdata['thumb']."")."" : "".BASEDIR."eshop/img/nopic_thumb.gif")."' alt='' width='".$settings['eshop_idisp_w']."' height='".$settings['eshop_idisp_h']."' border='0' style='padding:4px;' /></a>";
}
		echo "<br /><br />\n ".$up;
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=Main&amp;action=edit&amp;id=".$itemdata['id']."&amp;category=".$_REQUEST['category']."'>".$locale['ESHFEAT120']."</a> &middot;\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;action=delitem&amp;category=".$_REQUEST['category']."&amp;id=".$itemdata['id']."'>".$locale['ESHFEAT121']."</a> ".$down;
		echo "</td>\n";
		$counter++; $k++;
	}
	echo "</tr>\n<tr>\n<td align='center' colspan='".$fthumbs_per_row."' class='tbl2'><a class='button' href='".FUSION_SELF.$aidlink."&amp;a_page=featured'>Close Section</a>";
    echo "</td>\n</tr>\n</table>\n";
echo "<div style='text-align:center;margin-top:5px'>[ <a href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;action=refresh&amp;category=".$_REQUEST['category']."'> ".$locale['ESHFEAT118']." </a> ] || [ <a href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;action=refreshb&amp;category=".$_REQUEST['category']."'> ".$locale['ESHFEAT119']." </a> ]</div>\n"; 
 }
}

?>