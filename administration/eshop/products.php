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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
if (isset($_POST['access'])) {
	$access = isnum($_POST['access']) ? $_POST['access'] : "0";
} else {
	$access = "0";
}
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
		$remove = dbarray(dbquery("SELECT picture,thumb,thumb2,iorder,cid FROM ".DB_ESHOP." WHERE id='".$_GET['id']."'"));
		$picture = BASEDIR."eshop/pictures/".$remove['picture'];
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
		if ($_GET['error'] == 1) {
			$message .= $locale['ESHP427'];
		} elseif ($_GET['error'] == 2) {
			$message .= "".$locale['ESHP428']." parsebytesize($imagebytes)";
		} elseif ($_GET['error'] == 3) {
			$message .= $locale['ESHP429'];
		} elseif ($_GET['error'] == 4) {
			$message .= "".$locale['ESHP430']." ".$imagewidth."x".$imageheight."";
		} elseif ($_GET['errors'] == "") {
			$message = $locale['ESHP431'];
		}
		echo "<div class='admin-message'>".$message."</div>\n";
	}
	if (isset($_POST['save_cat'])) {
		$error = "";
		$photo_file = $_POST['image'];
		$photo_thumb1 = $_POST['thumb'];
		$photo_thumb2 = $_POST['thumb2'];
		$newimgfile = $_FILES['imagefile'];
		if (!empty($newimgfile['name']) && is_uploaded_file($newimgfile['tmp_name'])) {
			$error = "";
			$photo_file = "";
			$photo_thumb1 = "";
			$photo_thumb2 = "";
			if (is_uploaded_file($_FILES['imagefile']['tmp_name'])) {
				$photo_types = array(".gif", ".jpg", ".jpeg", ".png");
				$photo_pic = $_FILES['imagefile'];
				$photo_name = strtolower(substr($photo_pic['name'], 0, strrpos($photo_pic['name'], ".")));
				$photo_ext = strtolower(strrchr($photo_pic['name'], "."));
				$photo_dest = BASEDIR."eshop/pictures/";
				if (!preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $photo_pic['name'])) {
					$error = 1;
				} elseif ($photo_pic['size'] > $imagebytes) {
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
		$ratings = stripinput($_POST['ratings']);
		$comments = stripinput($_POST['comments']);
		$linebreaks = stripinput($_POST['linebreaks']);
		$keywords = stripinput($_POST['keywords']);
		$languages = "";
		for ($pl = 0; $pl < sizeof($_POST['languages']); $pl++) {
			$languages .= $_POST['languages'][$pl].($pl < (sizeof($_POST['languages'])-1) ? "." : "");
		}
		if (isset($_POST['cList'])) {
			for ($i = 0, $l = count($_POST['cList']); $i < $l; $i++) {
				$clist .= ".\"".$_POST['cList'][$i]."\"";
			}
		}
		if (isset($_POST['sList'])) {
			for ($i = 0, $l = count($_POST['sList']); $i < $l; $i++) {
				$slist .= ".\"".$_POST['sList'][$i]."\"";
			}
		}
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['id']) && isnum($_GET['id']))) {
			$old_iorder = dbresult(dbquery("SELECT iorder FROM ".DB_ESHOP." WHERE cid = '".$category."' AND id='".$_GET['id']."'"), 0);
			if ($iorder > $old_iorder) {
				$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder-1 WHERE cid = '".$category."' AND iorder>'$old_iorder' AND iorder<='$iorder'");
			} elseif ($iorder < $old_iorder) {
				$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder+1 WHERE cid = '".$category."' AND iorder<'$old_iorder' AND iorder>='$iorder'");
			}
			$result = dbquery("UPDATE ".DB_ESHOP." SET title='$title', cid='$category', picture='$photo_file', thumb='$photo_thumb1',thumb2='$photo_thumb2',introtext='$introtext',description='$description',anything1='$anything1',anything1n='$anything1n',anything2='$anything2',anything2n='$anything2n',anything3='$anything3',anything3n='$anything3n',weight='$weight',price='$price',xprice='$xprice',stock='$stock',version='$version',status='$status',active='$active',gallery_on='$gallery_on',delivery='$delivery', demo='$demo',cart_on='$cart_on',buynow='$buynow',rpage='$rpage',icolor='$clist',dynf='$dynf',dync='$slist',qty='$qty',sellcount='$sellcount',iorder='$iorder',artno='$artno',sartno='$sartno',instock='$instock',dmulti='$dmulti',cupons='$cupons',access='$access',campaign='$campaign',comments='$comments',ratings='$ratings',linebreaks='$linebreaks',keywords='$keywords',product_languages='$languages',dateadded='$dateadded' WHERE id='".$_GET['id']."'");
		} else {
			if (!$iorder) {
				$iorder = dbresult(dbquery("SELECT MAX(iorder) FROM ".DB_ESHOP." WHERE cid = '".$category."'"), 0)+1;
			}
			$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder+1 WHERE cid = '".$category."' AND iorder>='$iorder'");
			$result = dbquery("INSERT INTO ".DB_ESHOP." VALUES('', '$title',  '$category', '$photo_file', '$photo_thumb1','$photo_thumb2', '$introtext','$description','$anything1','$anything1n','$anything2','$anything2n','$anything3','$anything3n','$weight','$price','$xprice','$stock','$version','$status','$active','$gallery_on','$delivery','$demo','$cart_on','$buynow','$rpage','$clist','$dynf','$slist','$qty','$sellcount','$iorder','$artno','$sartno','$instock','$dmulti','$cupons','$access','$campaign','$comments','$ratings','$linebreaks','$keywords','$languages','".time()."')");
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
		$category = $data['cid'];
		$image_url = $data['picture'];
		$thumb_url = $data['thumb'];
		$thumb2_url = $data['thumb2'];
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
		$languages = $data['product_languages'];
		$ratings = $data['ratings'];
		$comments = $data['comments'];
		$linebreaks = $data['linebreaks'];
		$keywords = $data['keywords'];
		$formaction = "".FUSION_SELF.$aidlink."&amp;action=edit&id=".$data['id']."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."";
	} else {
		$title = "";
		$description = "";
		$anything1 = "";
		$anything1n = "";
		$anything2 = "";
		$anything2n = "";
		$anything3 = "";
		$anything3n = "";
		$introtext = "";
		$category = "";
		$image_url = "";
		$thumb_url = "";
		$thumb2_url = "";
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
		$dynf = "";
		$clist = "";
		$slist = "";
		$list = "";
		$qty = "";
		$sellcount = "";
		$dateadded = "";
		$campaign = "";
		$languages = "";
		$ratings = "";
		$comments = "";
		$linebreaks = "";
		$keywords = "";
		$formaction = FUSION_SELF.$aidlink."".($settings['eshop_cats'] == "1" && isset($_REQUEST['category']) ? "&amp;category=".$_REQUEST['category']."" : "")."";
	}
	$itemcolors = str_replace(".", ",", $clist);
	$itemcolors = ltrim($itemcolors, ',');
	$itemdyncs = str_replace(".", ",", $slist);
	$itemdyncs = ltrim($itemdyncs, ',');
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
	$visibility_opts = "";
	$sel = "";
	$user_groups = getusergroups();
	while (list($key, $user_group) = each($user_groups)) {
		$sel = ($access == $user_group['0'] ? " selected" : "");
		$visibility_opts .= "<option value='".$user_group['0']."'$sel>".$user_group['1']."</option>\n";
	}

	if ($settings['eshop_cats'] == "1" && !isset($_REQUEST['category']) && !isset($_GET['action'])) {
		//check for main cats.
		function cat_form() {
			global $aidlink, $locale;
			echo openform('catform', 'catform', 'post', FUSION_SELF.$aidlink."&amp;a_page=main");
			echo form_select_tree($locale['ESHPPRO186'], 'category', 'category', '', array('parent_value'=>$locale['ESHP016'], 'placeholder'=>$locale['ESHP016'], 'allowclear'=>1), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
			echo closeform();
			add_to_jquery("
				$('#category').bind('change', function(e) { this.form.submit();	});
			");
		}

		cat_form();

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
				$result2 = dbquery("select * from ".DB_ESHOP_CATS." WHERE parentid='".$_REQUEST['category']."' order by parentid, title");
				while ($cat_data = dbarray($result2)) {
					if ($cat_data['parentid'] != 0) $cat_data['title'] = getparent($cat_data['parentid'], $cat_data['title']);
					echo "<option value='".$cat_data['cid']."'>".$cat_data['title']."</option>";
				}
				echo "</select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO103']."
</span><img src='".SHOP."img/helper.png' height='25' border='0' alt='' style='vertical-align:middle;' /></div>
</td></tr></table></form><hr />";
			}
		}

		// yay i found the product form
		function product_form() {
			global $aidlink, $locale;
			$data['cat_languages'] = array();

			echo openform('productform', 'productform', 'post', FUSION_SELF.$aidlink."&amp;a_page=main", array('enc_type'=>1));
			echo "<div class='row'>\n";
			echo "<div class='col-xs-12 col-sm-8 col-md-8 col-lg-8'>\n";
			echo form_hidden('', 'dateadded', 'dateadded', '');
			echo form_text($locale['ESHPPRO104'], 'title', 'title', '', array('inline'=>1));
			echo "<div class='row'>\n";
			echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
			echo "<label class='control-label'>".$locale['ESHPPRO191']."</label>";
			echo "</div>\n";
			echo "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n";
			foreach (fusion_get_enabled_languages() as $lang) {
				$check = (in_array($lang, $data['cat_languages'])) ? 1 : 0;
				echo "<div class='display-inline-block text-left m-r-10'>\n";
				echo form_checkbox($lang, 'languages[]', 'lang-'.$lang, $check, array('value' => $lang));
				echo "</div>\n";
			}
			echo "</div>\n";
			echo "</div>\n";


			$tab_title['title'][] = 'Image File Upload';
			$tab_title['id'][] = 'a1';
			$tab_title['icon'][] = '';

			$tab_title['title'][] = 'Custom URL';
			$tab_title['id'][] = 'a2';
			$tab_title['icon'][] = '';

			$tab_active = tab_active($tab_title, 0);
			echo form_select($locale['ESHPPRO126'], 'gallery_on', 'gallery_on', array('0'=>$locale['off'], '1'=>$locale['on']), '', array('inline'=>1, 'tip'=>$locale['ESHPPRO129']));
			echo opentab($tab_title, $tab_active, 'custom');
			echo opentabbody($tab_title['title'][0], 'a1' , $tab_active);
			echo form_fileinput($locale['ESHPPRO109'], 'imagefile', 'imagefile', CAT_DIR, '', array('width'=>'190px', 'inline'=>1, 'type'=>'image'));
			echo "<span class='text-smaller'>".$locale['ESHPPRO110']."</span>\n";
			echo closetabbody();
			echo opentabbody($tab_title['title'][1], 'a2', $tab_active);
			echo "aaaa";
			echo closetabbody();
			echo closetab();


			echo "<div class='row m-b-20'>\n";
			echo "<div class='col-xs-12 col-sm-6'>\n";
			echo form_text($locale['ESHPPRO107'], 'artno', 'artno', '', array('inline'=>1));
			echo form_text($locale['ESHPPRO108'], 'sartno', 'sartno', '', array('inline'=>1));
			echo form_text($locale['ESHPPRO111'], 'price', 'price', '', array('number'=>1, 'inline'=>1,  'placeholder'=>fusion_get_settings('eshop_currency')));
			echo form_text($locale['ESHPPRO112'], 'xprice', 'xprice', '', array('number'=>1, 'inline'=>1, 'tip'=>$locale['ESHPPRO113'], 'placeholder'=>fusion_get_settings('eshop_currency')));
			echo form_checkbox($locale['ESHPPRO184'], 'campaign', 'campaign', '', array('inline'=>1, 'tip'=>$locale['ESHPPRO185']));
			echo form_checkbox($locale['ESHPPRO182'], 'cupons', 'cupons', '', array('inline'=>1, 'tip'=>$locale['ESHPPRO183']));
			echo "</div>\n";

			echo "<div class='col-xs-12 col-sm-6'>\n";
			echo form_text($locale['ESHPPRO122'], 'iorder', 'iorder', '', array('inline'=>1, 'number'=>1, 'tip'=>$locale['ESHPPRO123']));
			echo form_text($locale['ESHPPRO124'], 'sellcount', 'sellcount', '', array('deactivate'=>1, 'inline'=>1, 'tip'=>$locale['ESHPPRO125']));
			echo "</div>\n";


			echo "</div>\n";

			echo "<div class='row'>\n";
			echo "<div class='col-xs-12 col-sm-4'>\n";

			echo "</div>\n";
			echo "<div class='col-xs-12 col-sm-4'>\n";

			echo "</div>\n";
			echo "<div class='col-xs-12 col-sm-4'>\n";

			echo "</div>\n";

			echo "</div>\n";




			echo "</div>\n";
			echo "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-4'>\n";
			openside('');
			if (fusion_get_settings('eshop_cats')) {
				echo form_select_tree($locale['ESHPPRO105'], 'cid', 'cid', '', array('no_root'=>1, 'placeholder'=>$locale['ESHP016'], 'inline'=>1), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
			} else {
				echo $locale['ESHPPRO105']." : ".$locale['ESHPPRO106'];
				//echo "<tr><td align='left'>".$locale['ESHPPRO105']."</td><td align='left'><input type='hidden' name='cid' value='$category'>".$locale['ESHPPRO106']."</td></tr>";
			}
			closeside();

			openside('');
			echo form_select($locale['ESHPPRO192'], 'keywords', 'keywords', array(), '', array('inline'=>1, 'width'=>'100%', 'tags'=>1, 'multiple'=>1));
			closeside();

			openside('');
			echo form_text($locale['ESHPPRO114'], 'weight', 'weight', '', array('number'=>1, 'width'=>'100px', 'placeholder'=> fusion_get_settings('eshop_weightscale'), 'inline'=>1));
			echo "<span class='text-smaller'>".$locale['ESHPPRO115']."</span>\n";
			closeside();

			openside('');
			echo form_text('Custom Attributes', 'dynf', 'dynf', '', array('inline'=>1, 'placeholder'=>'Label'));
			echo form_text('Values', 'dyncList', 'dyncList', '', array('inline'=>1, 'placeholder'=>'Attributes'));
			echo "<div><a href='javascript:;' id='adddync' class='btn button btn-sm btn-primary m-b-20'>".$locale['ESHPPRO116']."</a>\n</div>\n";
			echo form_para($locale['ESHPPRO118'],'118', '118');
			echo "<div id='sList'>\n";
			echo "</div>\n";
			echo "<div class='text-smaller'>".$locale['ESHPPRO117']."</div>\n";
			closeside();

			openside('');
			global $ESHPCLRS;
			for ($i=1; $i <= 135; $i++) { $colors_array[$i] = $ESHPCLRS[$i]; }
			echo form_select('', 'colorList', 'colorList', $colors_array, '', array('inline'=>1, 'width'=>'100%'));
			echo "<span class='text-smaller'>".$locale['ESHPPRO121']."</span>\n";
			echo "<div id='cList'></div>\n";
			closeside();


			echo "</div>\n";
			echo "</div>\n";
			echo closeform();
		}

		product_form();



		//echo "<form name='inputform' method='post' action='$formaction' enctype='multipart/form-data'>";
		echo "<div style='float:left;width:50%;'>";
		echo "</div><div style='float:right;width:50%;'>";
		echo "<table width='100%' cellspacing='4' cellpadding='0' align='center'>";

		echo "<tr><td align='left'>".$locale['ESHPPRO130']."</td><td align='left'><input type='text' name='image' value='$image_url' class='textbox' readonly style='width:200px;'></td></tr>
<tr><td align='left'>".$locale['ESHPPRO131']."</td><td align='left'><input type='text' name='thumb' value='$thumb_url' class='textbox' readonly style='width:200px;'></td></tr>
<tr><td align='left'>".$locale['ESHPPRO132']."</td><td align='left'><input type='text' name='thumb2' value='$thumb2_url' class='textbox' readonly style='width:200px;'></td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPPRO133']."</td><td align='left'><input type='text' name='version' value='$version' class='textbox' style='width:60px;'>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO134']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPPRO135']."</td><td align='left'><input type='text' name='demo' value='$demo' class='textbox' style='width:170px;'>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO136']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPPRO137']."</td><td align='left'><select name='stock' class='textbox'>
      <option value='1'".($stock == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
	  <option value='0'".($stock == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
	  </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO140']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPPRO141']."</td><td align='left'><input type='text' name='instock' value='$instock' class='textbox' style='width:40px;'>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO142']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPPRO143']."</td><td align='left'><input type='text' name='delivery' value='$delivery' class='textbox' style='width:170px;' />
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO144']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPPRO145']."</td><td align='left'><select name='status' class='textbox'>
      <option value='1'".($status == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
      <option value='0'".($status == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
      </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO146']." 
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPPRO147']."</td><td align='left'><select name='active' class='textbox'>
      <option value='1'".($active == "1" ? " selected" : "").">".$locale['ESHPPRO128']."</option>
      <option value='0'".($active == "0" ? " selected" : "").">".$locale['ESHPPRO127']."</option>
      </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO148']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPPRO149']."</td><td align='left'><select name='cart_on' class='textbox'>
      <option value='1'".($cart_on == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
      <option value='0'".($cart_on == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
      </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO150']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>Allow to buy multiple items</td><td align='left'><select name='qty' class='textbox'>
      <option value='1'".($qty == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
      <option value='0'".($qty == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
      </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO151']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPPRO152']."</td><td align='left'><input type='text' name='dmulti' value='$dmulti' class='textbox' style='width:40px;'>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO153']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPPRO154']."</td><td align='left'><select name='buynow' class='textbox'>
      <option value='0'".($buynow == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
      <option value='1'".($buynow == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
      </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO155']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPPRO188']."</td><td align='left'><select name='ratings' class='textbox'>
      <option value='0'".($ratings == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
      <option value='1'".($ratings == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
      </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO188']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPPRO189']."</td><td align='left'><select name='comments' class='textbox'>
      <option value='0'".($comments == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
      <option value='1'".($comments == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
      </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO189']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPPRO156']."</td><td>";
		$callback_dir = makefilelist(BASEDIR."eshop/purchasescripts/", ".|..|index.php", TRUE, "files");
		echo "<select name='rpage' class='textbox' style='width:180px;' >";
		echo "<option value=''>".$locale['ESHPPRO157']."</option>";
		foreach ($callback_dir as $callback) {
			echo "<option value='$callback' ".($callback == "$rpage" ? " selected" : "").">$callback</option>";
		}
		echo "</select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO158']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPPRO190']."</td><td align='left'><select name='linebreaks' class='textbox'>
      <option value='0'".($linebreaks == "0" ? " selected" : "").">".$locale['ESHPPRO139']."</option>
      <option value='1'".($linebreaks == "1" ? " selected" : "").">".$locale['ESHPPRO138']."</option>
      </select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO190']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "<tr><td align='left'>".$locale['ESHPCATS109']."</td>
<td align='left'><select name='access' class='textbox'>
$visibility_opts</select>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO159']."
</span><img src='".SHOP."img/helper.png' alt='' style='height:25px;vertical-align:middle;' /></a>
</td></tr>";
		echo "</table>";
		echo "</div>";
		echo "<div style='clear:both;'></div>";
		echo "<table width='100%' cellspacing='4' cellpadding='0'>";
		if ($settings['eshop_pretext'] == "1") {
			echo "<tr><td align='left'>".$locale['ESHPPRO160']."</td><td align='left'><textarea name='introtext' cols='90' rows='3' class='textbox span6'>$introtext</textarea>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO161']."
</span><img src='".SHOP."img/helper.png' height='25' border='0' alt='' style='vertical-align:top;' /></a>
</td></tr>";
		} else {
			echo "<input type='hidden' name='introtext' value='$introtext'>";
		}
		echo "<tr><td align='left'>".$locale['ESHPPRO162']."</td><td align='left'><textarea name='description' cols='90' rows='6' class='textbox span6'>$description</textarea>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO163']."
</span><img src='".SHOP."img/helper.png' height='25' border='0' alt='' style='vertical-align:top;' /></a>
</td></tr>";
		echo "</table>\n";
		echo '<br /><center><a href="#" onClick="showexttabs(); return false;" class="button"><b>'.$locale['ESHPPRO164'].'</b></a></center><br />';
		echo "<div id='exttabs'>";
		echo "<table width='100%' cellspacing='4' cellpadding='0'>";
		echo "<tr><td align='left'><input type='text' name='anything1n' value='$anything1n' class='textbox' style='width:90px;'></td><td align='left'><textarea name='anything1' cols='90' rows='6' class='textbox span6'>$anything1</textarea>
<a href='javascript:;' class='info'><span>
".$locale['ESHPPRO165']."
</span><img src='".SHOP."img/helper.png' height='25' border='0' alt='' style='vertical-align:top;' /></a>

</td></tr>";
		echo "<tr><td align='left'><input type='text' name='anything2n' value='$anything2n' class='textbox' style='width:90px;'></td><td align='left'><textarea name='anything2' cols='90' rows='6' class='textbox span6'>$anything2</textarea></td></tr>";
		echo "<tr><td align='left'><input type='text' name='anything3n' value='$anything3n' class='textbox' style='width:90px;'></td><td align='left'><textarea name='anything3' cols='90' rows='6' class='textbox span6'>$anything3</textarea></td></tr>";
		echo "</table></div>\n";
		echo "<table width='100%' cellspacing='4' cellpadding='0'>";
		echo "<tr><td align='center' colspan='3'><input type='submit' name='save_cat' value='".$locale['ESHPPRO166']."' class='button'></td></tr>";
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
				echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;action=delete&id=".$data['id']."' onClick='return confirmdelete();'><img src='".SHOP."img/remove.png' border='0' height='20' style='vertical-align:middle;'  alt='' /></a></td></tr>";
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
			$i = 0;
			$k = 1;
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
					$up = $data['iorder']-1;
					$down = $data['iorder']+1;
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
				echo "<td width='1%' align='center'><a href='".FUSION_SELF.$aidlink."&amp;action=delete&id=".$data['id']."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."' onClick='return confirmdelete();'><img src='".SHOP."img/remove.png' border='0' height='20' style='vertical-align:middle;'  alt='' /></a></td></tr>";
				echo "</td>";
			}
			echo "</table>\n";
			echo "<div align='center' style='margin-top:5px;'>".makePageNav($_GET['rowstart'], 15, $rows, 3, FUSION_SELF.$aidlink."&amp;sortby=".$_GET['sortby']."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."&")."\n</div>\n";
		} else {
			echo "<center><br />\n <b>".$locale['ESHPPRO177']."</b> <br /><br />\n</center>\n";
		}
		$search = array("A",
			"B",
			"C",
			"D",
			"E",
			"F",
			"G",
			"H",
			"I",
			"J",
			"K",
			"L",
			"M",
			"N",
			"O",
			"P",
			"Q",
			"R",
			"S",
			"T",
			"U",
			"V",
			"W",
			"X",
			"Y",
			"Z",
			"0",
			"1",
			"2",
			"3",
			"4",
			"5",
			"6",
			"7",
			"8",
			"9");
		echo "<hr><table align='center' cellpadding='0' cellspacing='1' class='tbl-border'>\n<tr>\n";
		echo "<td rowspan='2' class='tbl2'><a href='".FUSION_SELF.$aidlink."&amp;sortby=all".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."'>".$locale['ESHP425']."</a></td>";
		for ($i = 0; $i < 36 != ""; $i++) {
			echo "<td align='center' class='tbl1'><div class='small'><a href='".FUSION_SELF.$aidlink."&amp;sortby=".$search[$i]."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."'>".$search[$i]."</a></div></td>";
			echo($i == 17 ? "<td rowspan='2' class='tbl2'><a href='".FUSION_SELF.$aidlink."&amp;sortby=all".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."'>".$locale['ESHP426']."</a></td>\n</tr>\n<tr>\n" : "\n");
		}
		echo "</table>\n";
		if (dbrows($result)) {
			echo "<div style='text-align:center;margin-top:5px'>[ <a href='".FUSION_SELF.$aidlink."&amp;action=refresh".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."'> ".$locale['ESHPPRO179']." </a> ]</div>\n";
		}
	}
}
if (isset($_POST['psrchtext'])) {
	$searchtext = stripinput($_POST['psrchtext']);
} else {
	$searchtext = $locale['SRCH162'];
}

class eshop_products {

	static function product_listing() {
		global $locale, $aidlink;
		echo "<div class='m-t-20'>\n";
		echo openform('', 'search_form', 'search_form', 'post', FUSION_SELF.$aidlink."&amp;a_page=main");

		echo closeform();
		echo "</div>\n";
	}

}



echo "<div style='float:right;margin-top:5px;'>\n";
echo "<form id='search_form'  name='inputform' method='post' action='".FUSION_SELF.$aidlink."&amp;psearch'>
<span style='vertical-align:middle;font-size:14px;'>".$locale['ESHPPRO178']."</span>";
echo "<input type='text' name='psrchtext' class='textbox' style='margin-left:1px; margin-right:1px; margin-bottom:5px; width:160px;'  value='".$searchtext."' onblur=\"if(this.value=='') this.value='".$searchtext."';\" onfocus=\"if(this.value=='".$searchtext."') this.value='';\" />";
echo "<input type='image' id='search_image' src='".SHOP."img/search_icon.png' alt='".$locale['SRCH162']."' />";
echo "</form></div>";




?>