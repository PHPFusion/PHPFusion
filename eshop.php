<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop.php
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
require_once dirname(__FILE__)."/maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."eshop.php";
require_once THEMES."templates/global/eshop.php";
//include INCLUDES."eshop_functions_include.php";

//Close the tree when eShop home have been clicked.
/*
if ($settings['eshop_cats'] == "1") {
echo '<script type="text/javascript"> 
	d.closeAll();
</script>';
}
*/
$eShop = new PHPFusion\Eshop();
$info = $eShop->get_category();
$info += $eShop->get_product();
$info += $eShop->get_featured();
$info += $eShop->get_title();
$info += $eShop->get_product_photos();
render_eshop_nav($info);
if ($_GET['category']) {
	// view category page
	render_eshop_featured_product($info);
	render_eshop_page_content($info);
	render_eshop_featured_category($info);
} elseif ($_GET['product']) {
	// view product page
	render_eshop_product($info);
} else {
	render_eshop_featured_url($info);
	render_eshop_featured_product($info);
	render_eshop_page_content($info);
	render_eshop_featured_category($info);
}


//////////////--------- <3><  ------------------ ///////////////




// mvc functions
//buildeshopheader();

//item details start
/*
elseif (isset($_GET['category'])) {

//Expand selected category if we have folderlinks on.
if ($settings['eshop_folderlink'] == "1") {
	echo '<script type="text/javascript"> 
	d.openTo('.$_GET['category'].', true);
	</script>';
}

//Check if we have a maincat and if subcats are there.
$resultc = dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE parentid='".$_GET['category']."'");
if (dbrows($resultc)) {
echo breadseo($_GET['category']);
echo "<div class='clear'></div>";

//check featured banners
buildeshopbanners();

//Check featured section first
$result= dbquery("SELECT ter.* FROM ".DB_ESHOP." ter
		LEFT JOIN ".DB_ESHOP_FEATITEMS." titm ON ter.id=titm.featitem_item
		WHERE featitem_cid = '".($_REQUEST['category'] ? $_REQUEST['category'] : "0")."' ORDER BY featitem_order");
$rows = dbrows($result);

if ($rows) {
$counter = 0; 
	echo "<table cellpadding='0' cellspacing='0' width='100%' align='center'><tr>\n";
	while ($data = dbarray($result)) {
	if ($counter != 0 && ($counter % $settings['eshop_ipr'] == 0)) echo "</tr>\n<tr>\n";
    echo "<td align='center' class='tbl'>\n";
	eshopitems();
	echo "</td>\n";
	$counter++;
}
	echo "</tr>\n</table>\n";
echo "<hr />";
} 
	$counter = 0;
	echo "<table cellpadding='0' cellspacing='4' width='100%'>\n<tr>\n";
	while ($data = dbarray($resultc)) {
	if ($counter != 0 && ($counter % $settings['eshop_cipr'] == 0)) echo "</tr>\n<tr>\n";
	if ($settings['eshop_cat_disp'] == "1") {
		echo "<td align='center' valign='top' class='arealist' onclick=\"location='".BASEDIR."eshop/eshop.php?category=".$data['cid']."'\" style='cursor:pointer;'>\n";
	} else {
	echo "<td align='center' valign='top'><a href='".BASEDIR."eshop/eshop.php?category=".$data['cid']."'><img style='width:".$settings['eshop_catimg_w']."px; height:".$settings['eshop_catimg_h']."px;' src ='".BASEDIR."eshop/categoryimgs/".$data['image']."' alt='".$data['title']."' /></a><br />";
	}
	echo "<a href='".BASEDIR."eshop/eshop.php?category=".$data['cid']."'>".$data['title']."</a>";
	echo "</td>\n";
	$counter++; 
  }
	echo "</tr>\n</table>\n";
} else {

//add filters
buildfilters();

//Cat view start
$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE cid='".$_GET['category']."' AND active = '1' AND ".groupaccess('access')." ORDER BY ".$filter."");
if (dbrows($result)) {
$cdata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE cid='".$_GET['category']."' AND ".groupaccess('access').""));
echo '<div style="margin-top:10px;"></div><div class="tbl-border" style="width:60%;float:left;padding-left: 7px;padding-top: 5px;padding-bottom: 5px;background-color:#f8f8f8;line-height:15px !important;height:15px !important;display:inline;">'.breadcrumb($_GET['category']).'</div>';
echo breadseo($_GET['category']);
echo "<div class='clear'></div>";
//check featured banners


//Check featured section first
	$resultfeat= dbquery("SELECT ter.* FROM ".DB_ESHOP." ter
	LEFT JOIN ".DB_ESHOP_FEATITEMS." titm ON ter.id=titm.featitem_item
	WHERE featitem_cid = '".($_REQUEST['category'] ? $_REQUEST['category'] : "0")."' ORDER BY featitem_order");
	$rows = dbrows($resultfeat);

if ($rows) {
	$counter = 0; 
	echo "<table cellpadding='0' cellspacing='0' width='100%' align='center'><tr>\n";
	while ($data = dbarray($resultfeat)) {
	if ($counter != 0 && ($counter % $settings['eshop_ipr'] == 0)) echo "</tr>\n<tr>\n";
    echo "<td align='center' class='tbl'>\n";
	eshopitems();
	echo "</td>\n";
	$counter++;
}
	echo "</tr>\n</table>\n";

} 

$rows = dbrows($result);
$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE cid='".$_GET['category']."' AND active = '1' AND ".groupaccess('access')." ORDER BY ".$filter." LIMIT ".$_GET['rowstart'].",".$settings['eshop_nopp']."");
	$counter = 0; 
	echo "<table cellpadding='0' cellspacing='0' width='100%' align='center'><tr>\n";
	while ($data = dbarray($result)) {
	if ($counter != 0 && ($counter % $settings['eshop_ipr'] == 0)) echo "</tr>\n<tr>\n";
	echo "<td align='center' class='tbl'>\n";
	eshopitems();
	echo "</td>\n";
	$counter++;
}
echo "</tr>\n</table>\n";
if ($rows > $settings['eshop_nopp']) echo "<div align='center' style='margin-top:5px;'>\n".makeeshoppagenav($_GET['rowstart'],$settings['eshop_nopp'],$rows,3,FUSION_SELF."?category=".$_GET['category']."&amp;".(isset($_COOKIE['Filter']) ? "FilterSelect=".$_COOKIE['Filter']."&amp;" : "" )."")."\n</div>\n";
echo "<div class='clear'></div>";
} else {
echo "<div class='clear'></div>";
echo "<br /><div class='admin-message'> ".$locale['ESHPP102']." </div>";
  }
 }
}

closetable();

//convert guest shopping to member when they visit eshop, this check is also made in the checkout.
if (iMEMBER) {
$usercartchk = dbarray(dbquery("SELECT puid FROM ".DB_ESHOP_CART." WHERE puid = '".$_SERVER['REMOTE_ADDR']."' LIMIT 0,1"));
if ($usercartchk['puid']) {
dbquery("UPDATE ".DB_ESHOP_CART." SET puid = '".$userdata['user_id']."' WHERE puid = '".$_SERVER['REMOTE_ADDR']."'");
 }
}

//Sanitize the cart from 1 month old orders.
dbquery("DELETE FROM ".DB_ESHOP_CART." WHERE cadded < ".time()."-2592180");
*/
require_once THEMES."templates/footer.php";
?>