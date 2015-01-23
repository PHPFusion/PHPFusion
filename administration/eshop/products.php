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

if (!defined("IN_FUSION")) die("Access Denied");
/*

	echo '<script type="text/javascript">
	//<![CDATA[
	function showexttabs(){
		$("#exttabs").animate({"height": "toggle"}, { duration: 500 });
	}
	//]]>
	</script>';
*/

/*
if (isset($_POST['access'])) {
	$access = isnum($_POST['access']) ? $_POST['access'] : "0";
} else {
	$access = "0";
}

if (isset($_GET['psearch'])) {
	include ADMIN."eshop/productsearch.php";
} else {


	// get message

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
*/
class eShop_item {

	private $data = array(
		'title' => '',
		'description' => '',
		'anything1' => '',
		'anything1n' => '',
		'anything2' => '',
		'anything2n' => '',
		'anything3' => '',
		'anything3n' => '',
		'introtext' => '',
		'category' => 0,
		'image_url' => '',
		'thumb_url' => '',
		'thumb2_url' => '',
		'weight' => '',
		'cid' => '',
		'price' => '',
		'xprice' => '',
		'stock' => 0,
		'version' => '',
		'status' => 1,
		'active' => 1,
		'gallery_on' => 1,
		'cart_on' => 1,
		'buynow' => 1,
		'delivery' => '',
		'demo'=> '',
		'rpage'=> 0,
		'iorder'=> '',
		'artno' => '',
		'sartno' => '',
		'instock' => '',
		'dmulti' => 1,
		'cupons' => 1,
		'access'=>0,
		'dynf' => '',
		'clist' => '',
		'slist' => '',
		'list' => '',
		'qty' => '',
		'sellcount' => '',
		'dateadded' => '',
		'campaign' => '',
		'languages' => '',
		'ratings' => 1,
		'comments' => 1,
		'linebreaks' => 1,
		'keywords' => '',
	);
	private $formaction = '';

	public function __construct() {
		global $aidlink, $settings;
		$_GET['id'] = isset($_GET['id']) && isnum($_GET['id']) ? $_GET['id'] : 0;
		/*
		if (isset($_GET['action']) && $_GET['action'] == "edit") {
			$this->formaction = FUSION_SELF.$aidlink."&amp;action=edit&id=".$data['id']."".(fusion_get_settings('eshop_cats') == "1" ? "&amp;category=".$_REQUEST['category']."" : "");
		} else {
			$this->formaction = FUSION_SELF.$aidlink."".(fusion_get_settings('eshop_cats') == "1" && isset($_REQUEST['category']) ? "&amp;category=".$_REQUEST['category']."" : "");
		}*/
		self::set_productdb();
	}

	static function verify_product_edit($id) {
		return dbcount("(id)", DB_ESHOP, "id='".$id."'");
	}

	// action refresh
	static function product_refresh() {
		global $aidlink, $settings;
		$i = 1;
		$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE cid = '".$_REQUEST['category']."' ORDER BY iorder");
		while ($data = dbarray($result)) {
			$result2 = dbquery("UPDATE ".DB_ESHOP." SET iorder='$i' WHERE id='".$data['id']."'");
			$i++;
		}
		redirect(FUSION_SELF.$aidlink."&amp;iorderrefresh".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."");
	}

	// action moveup
	static function product_moveup() {
		global $aidlink, $settings;
		if (isset($_GET['id']) && isnum($_GET['id'])) {
			$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP." WHERE cid = '".$_REQUEST['category']."' AND iorder='".intval($_GET['order'])."'"));
			$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder+1 WHERE cid = '".$_REQUEST['category']."' AND id='".$data['id']."'");
			$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder-1 WHERE cid = '".$_REQUEST['category']."' AND id='".$_GET['id']."'");
			redirect(FUSION_SELF.$aidlink."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."");
		}
	}
	// action movedown
	static function product_movedown() {
		global $aidlink, $settings;
		if (isset($_GET['id']) && isnum($_GET['id'])) {
			$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP." WHERE cid = '".$_REQUEST['category']."' AND iorder='".intval($_GET['order'])."'"));
		$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder-1 WHERE cid = '".$_REQUEST['category']."' AND id='".$data['id']."'");
		$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder+1 WHERE cid = '".$_REQUEST['category']."' AND id='".$_GET['id']."'");
		redirect(FUSION_SELF.$aidlink."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."");
		}
	}
	// action delete
	static function product_delete() {
		global $aidlink;
		if (isset($_GET['id']) && isnum($_GET['id'])) {
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
	}

	private function set_productdb() {
		global $aidlink;

		if (isset($_POST['save_cat'])) {
			$this->data['title'] = isset($_POST['title']) ? form_sanitizer($_POST['title'], '', 'title') : '';
			$this->data['cid'] = isset($_POST['cid']) ? form_sanitizer($_POST['cid'], '', 'cid') : 0;
			$this->data['picture'] = isset($_POST['image']) ? form_sanitizer($_POST['image'], '', 'image') : '';
			$this->data['thumb'] = isset($_POST['thumb']) ? form_sanitizer($_POST['thumb'], '', 'thumb') : '';
			$this->data['thumb2'] = isset($_POST['thumb2']) ? form_sanitizer($_POST['thumb2'], '', 'thumb2') : '';

			$upload = form_sanitizer($_FILES['imagefile'], '', 'imagefile');
			if ($upload) {
				$this->data['picture'] = $upload['image_name'];
				$this->data['thumb'] = $upload['thumb1_name'];
				$this->data['thumb2'] = $upload['thumb2_name'];
			}
			$this->data['introtext'] = isset($_POST['introtext']) ? form_sanitizer($_POST['introtext'], '', 'introtext') : '';
			$this->data['description'] = isset($_POST['description']) ? addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['description'])) : '';
			$this->data['anything1n'] = isset($_POST['anything1n']) ? form_sanitizer($_POST['anything1n'], '', 'anything1n') : '';
			$this->data['anything1'] = isset($_POST['anything1']) ? addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['anything1'])) : '';
			$this->data['anything2n'] = isset($_POST['anything2n']) ? form_sanitizer($_POST['anything1n'], '', 'anything2n') : '';
			$this->data['anything2'] = isset($_POST['anything2']) ? addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['anything2'])) : '';
			$this->data['anything2n'] = isset($_POST['anything2n']) ? form_sanitizer($_POST['anything1n'], '', 'anything2n') : '';
			$this->data['anything2'] = isset($_POST['anything2']) ? addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['anything2'])) : '';
			$this->data['anything3n'] = isset($_POST['anything3n']) ? form_sanitizer($_POST['anything3n'], '', 'anything3n') : '';
			$this->data['anything3'] = isset($_POST['anything3']) ? addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['anything3'])) : '';
			$this->data['weight'] = isset($_POST['weight']) ? str_replace(',', '.', form_sanitizer($_POST['weight'], '', 'weight')) : '';
			$this->data['price'] = isset($_POST['price']) ? str_replace(',', '.', form_sanitizer($_POST['price'], '', 'price')) : '';
			$this->data['xprice'] = isset($_POST['xprice']) ? str_replace(',', '.', form_sanitizer($_POST['xprice'], '', 'xprice')) : '';
			$this->data['stock'] = isset($_POST['stock']) ? str_replace(',', '.', form_sanitizer($_POST['stock'], '', 'stock')) : '';
			$this->data['version'] = isset($_POST['version']) ? str_replace(',', '.', form_sanitizer($_POST['version'], '', 'version')) : '';
			$this->data['status'] = isset($_POST['status']) ? form_sanitizer($_POST['version'], '0', 'version') : 0;
			$this->data['active'] = isset($_POST['active']) ? form_sanitizer($_POST['active'], '0', 'active') : 0;
			$this->data['gallery_on'] = isset($_POST['gallery_on']) ? form_sanitizer($_POST['gallery_on'], '0', 'gallery_on') : 0;
			$this->data['delivery'] = isset($_POST['delivery']) ? form_sanitizer($_POST['delivery'], '0', 'delivery') : 0;
			$this->data['demo'] = isset($_POST['demo']) ? form_sanitizer($_POST['demo'], '', 'demo') : '';
			$this->data['cart_on'] = isset($_POST['cart_on']) ? form_sanitizer($_POST['cart_on'], '0', 'cart_on') : 0;
			$this->data['buynow'] = isset($_POST['buynow']) ? form_sanitizer($_POST['buynow'], '0', 'buynow') : 0;
			$this->data['rpage'] = isset($_POST['rpage']) ? form_sanitizer($_POST['rpage'], '', 'rpage') : '';
			$this->data['rpage'] = isset($_POST['rpage']) ? form_sanitizer($_POST['rpage'], '', 'rpage') : '';
			$this->data['dynf'] = isset($_POST['dynf']) ? form_sanitizer($_POST['dynf'], '', 'dynf') : '';
			$this->data['qty'] = isset($_POST['qty']) ? form_sanitizer($_POST['qty'], '0', 'qty') : 0;
			$this->data['sellcount'] = isset($_POST['sellcount']) ? form_sanitizer($_POST['sellcount'], 0, 'sellcount') : 0;
			$this->data['artno'] = isset($_POST['artno']) ? form_sanitizer($_POST['artno'], '', 'artno') : '';
			$this->data['sartno'] = isset($_POST['sartno']) ? form_sanitizer($_POST['sartno'], '', 'sartno') : '';
			$this->data['instock'] = isset($_POST['instock']) ? form_sanitizer($_POST['instock'], '', 'instock') : '';
			$this->data['iorder'] = isset($_POST['iorder']) ? form_sanitizer($_POST['iorder'], '0', 'iorder') : 0;
			$this->data['dmulti'] = isset($_POST['dmulti']) ? form_sanitizer($_POST['dmulti'], '1', 'dmulti') : 1;
			$this->data['cupons'] = isset($_POST['cupons']) ? 1 : 0;
			$this->data['access'] = isset($_POST['access']) ? form_sanitizer($_POST['access'], '0', 'access') : 0;
			$this->data['dateadded'] = isset($_POST['dateadded']) ? form_sanitizer($_POST['dateadded'], time(), 'dateadded') : time();
			$this->data['campaign'] = isset($_POST['campaign']) ? 1 : 0;
			$this->data['ratings'] = isset($_POST['ratings']) ? 1 : 0;
			$this->data['comments'] = isset($_POST['comments']) ? 1 : 0;
			$this->data['linebreaks'] = isset($_POST['linebreaks']) ? 1 : 0;
			$this->data['keywords'] = isset($_POST['keywords']) ? form_sanitizer($_POST['keywords'], '', 'keywords') : '';
			$this->data['cat_languages'] = isset($_POST['cat_languages']) ? form_sanitizer($_POST['cat_languages'], '') : array();

			if (isset($_POST['cList'])) {
				for ($i = 0, $l = count($_POST['cList']); $i < $l; $i++) {
					$this->data['clist'] .= ".\"".$_POST['cList'][$i]."\"";
				}
			}
			$this->data['icolor'] = form_sanitizer($this->data['clist'], '');

			if (isset($_POST['sList'])) {
				for ($i = 0, $l = count($_POST['sList']); $i < $l; $i++) {
					$this->data['slist'] .= ".\"".$_POST['sList'][$i]."\"";
				}
			}
			$this->data['dync'] = form_sanitizer($this->data['slist'], '');

			if (self::verify_product_edit($_GET['id'])) {
				/*
				$old_iorder = dbresult(dbquery("SELECT iorder FROM ".DB_ESHOP." WHERE cid = '".$this->data['cid']."' AND id='".$this->data['id']."'"), 0);
				if ($this->data['iorder'] > $old_iorder) {
					$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder-1 WHERE cid = '".$this->data['cid']."' AND iorder>'$old_iorder' AND iorder<='".$this->data['iorder']."'");
				} elseif ($this->data['iorder'] < $old_iorder) {
					$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder+1 WHERE cid = '".$this->data['cid']."' AND iorder<'$old_iorder' AND iorder>='".$this->data['iorder']."'");
				}
				dbquery_insert(DB_ESHOP, $this->data, 'update');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;status=su"); */
			} else {
				/*
				if (!$this->data['iorder']) $iorder = dbresult(dbquery("SELECT MAX(iorder) FROM ".DB_ESHOP." WHERE cid = '".$this->data['cid']."'"), 0)+1;
				$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder+1 WHERE cid = '".$this->data['cid']."' AND iorder>='".$this->data['iorder']."'");
				dbquery_insert(DB_ESHOP, $this->data, 'save');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;status=sn"); */
				print_p($this->data);
			}
			//redirect("".FUSION_SELF.$aidlink."&amp;complete&amp;error=".$error."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."");
		}
	}

	static function products_data() {
		$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE id='".$_GET['id']."'");
		$data = dbarray($result);
	}

	public function product_form() {
		global $aidlink, $locale, $settings;
		$data['cat_languages'] = array();

		$itemcolors = str_replace(".", ",", $this->data['clist']);
		$itemcolors = ltrim($itemcolors, ',');
		$itemdyncs = str_replace(".", ",", $this->data['slist']);
		$itemdyncs = ltrim($itemdyncs, ',');

		// check function
		add_to_jquery('
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
		');

		add_to_jquery('
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
		$(document).on("change", ".sList-chk", function () { if ($(this).attr("checked")) { return; } else {  $(this).parent(".sList").remove();  }	});
	');

		add_to_jquery('
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
		$(document).on("change", ".cList-chk", function () { if ($(this).attr("checked")) { return;  } else { $(this).parent(".cList").remove(); } });
		');

		echo "<div class='m-t-20'>\n";
		echo openform('productform', 'productform', 'post', $this->formaction, array('enc_type'=>1));

		$subtab_title['title'][] = "Product Info";
		$subtab_title['id'][] = "pinfo";
		$subtab_title['icon'][] = 'fa fa-gift fa-lg m-r-10';

		$subtab_title['title'][] = "Stock Options";
		$subtab_title['id'][] = "pricing";
		$subtab_title['icon'][] = 'fa fa-cube fa-lg m-r-10';

		$subtab_title['title'][] = "Product Feature";
		$subtab_title['id'][] = "pfeature";
		$subtab_title['icon'][] = 'fa fa-info-circle fa-lg m-r-10';

		$subtab_title['title'][] = "Catalog Feature";
		$subtab_title['id'][] = "cfeature";
		$subtab_title['icon'][] = 'fa fa-pencil-square fa-lg m-r-10';

		$tab_active = tab_active($subtab_title, 0);

		echo "<div class='row m-t-20'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";
		echo form_text($locale['ESHPPRO104'], 'title', 'title', '', array('required'=>1, 'inline'=>1));

		echo form_select($locale['ESHPPRO192'], 'keywords', 'keywords', array(), '', array('width'=>'100%', 'tags'=>1, 'multiple'=>1, 'inline'=>1));
		echo form_text($locale['ESHPPRO122'], 'iorder', 'iorder', '', array('inline'=>1, 'number'=>1, 'tip'=>$locale['ESHPPRO123']));

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
		echo "</div><div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";
		openside('');
		if (fusion_get_settings('eshop_cats')) {
			echo form_select_tree($locale['ESHPPRO105'], 'cid', 'cid', '', array('no_root'=>1, 'width'=>'100%', 'placeholder'=>$locale['ESHP016']), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
		} else {
			echo $locale['ESHPPRO105']." : ".$locale['ESHPPRO106'];
			echo form_hidden('', 'cid', 'cid', 0);
			//echo "<tr><td align='left'>".$locale['ESHPPRO105']."</td><td align='left'><input type='hidden' name='cid' value='$category'>".$locale['ESHPPRO106']."</td></tr>";
		}
		$visibility_opts = array();
		$user_groups = getusergroups();
		while (list($key, $user_group) = each($user_groups)) {
			$visibility_opts[$user_group[0]] = $user_group[1];
		}
		echo form_select($locale['ESHPCATS109'], 'access', 'access', $visibility_opts, '', array('tip'=>$locale['ESHPPRO159'],'width'=>'100%'));
		echo form_button($locale['save'], 'save_cat', 'save_cat2', $locale['save'], array('class'=>'btn-primary'));
		closeside();
		echo "</div></div>\n";

		echo opentab($subtab_title, $tab_active, 'pformtab');
		// general info
		echo opentabbody($subtab_title['title'][0], $subtab_title['id'][0], $tab_active);
		echo "<div class='row m-t-20'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";

		openside('');
		echo form_text($locale['ESHPPRO111'], 'price', 'price', '', array('number'=>1, 'required'=>1, 'width'=>'200px', 'inline'=>1, 'placeholder'=>fusion_get_settings('eshop_currency')));
		closeside();
		openside('');
		echo form_text($locale['ESHPPRO107'], 'artno', 'artno', '', array('inline'=>1, 'placeholder'=>'Serial/ Reference No'));
		echo form_text($locale['ESHPPRO108'], 'sartno', 'sartno', '', array('inline'=>1, 'placeholder'=>'Serial/ Reference No'));
		closeside();
		if (fusion_get_settings('eshop_pretext')) {
			echo form_textarea($locale['ESHPPRO160'], 'introtext', 'introtext', '', array('html'=>1, 'preview'=>1, 'autosize'=>1));
			echo "<div class='text-smaller'>".$locale['ESHPPRO161']."</div>\n";
		} else {
			echo form_hidden('', 'introtext', 'introtext', '');
		}
		echo form_textarea($locale['ESHPPRO162'], 'description', 'description', '', array('html'=>1, 'preview'=>1, 'autosize'=>1));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";

		echo form_text($locale['ESHPPRO124'], 'sellcount', 'sellcount', '', array('deactivate'=>1, 'tip'=>$locale['ESHPPRO125']));

		openside('');
		echo form_checkbox($locale['ESHPPRO149'], 'cart_on', 'cart_on', '', array('tip'=>$locale['ESHPPRO150'], 'width'=>'100%'));
		echo form_checkbox('Allow to buy multiple items', 'qty', 'qty', '', array('tip'=>$locale['ESHPPRO151'], 'width'=>'100%'));
		closeside();

		openside('');
		echo form_checkbox($locale['ESHPPRO188'], 'ratings', 'ratings', '', array('tip'=>$locale['ESHPPRO188']));
		echo form_checkbox($locale['ESHPPRO189'], 'comments', 'comments', '', array('tip'=>$locale['ESHPPRO189']));
		closeside();
		echo "</div>\n";
		echo "</div>\n";
		echo closetabbody();

		// pricing
		echo opentabbody($subtab_title['title'][1], $subtab_title['id'][1], $tab_active);
		echo "<div class='row m-t-20'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";
		echo form_text($locale['ESHPPRO143'], 'delivery', 'delivery', '', array('tip'=>$locale['ESHPPRO144'], 'inline'=>1, 'width'=>'200px', 'number'=>1, 'placeholder'=>'Days'));
		echo form_text($locale['ESHPPRO152'], 'dmulti', 'dmulti', '', array('inline'=>1, 'tip'=>$locale['ESHPPRO153'], 'width'=>'200px', 'placeholder'=>'Items Quantity'));
		openside('');
		echo form_text($locale['ESHPPRO112'], 'xprice', 'xprice', '', array('number'=>1, 'inline'=>1, 'width'=>'200px', 'tip'=>$locale['ESHPPRO113'], 'placeholder'=>fusion_get_settings('eshop_currency')));
		echo form_checkbox($locale['ESHPPRO184'], 'campaign', 'campaign', '', array('inline'=>1, 'tip'=>$locale['ESHPPRO185'], 'class'=>'col-sm-offset-3'));
		echo form_checkbox($locale['ESHPPRO182'], 'cupons', 'cupons', '', array('inline'=>1, 'tip'=>$locale['ESHPPRO183'], 'class'=>'col-sm-offset-3'));
		closeside();
		echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";
		openside('');
		echo form_select($locale['ESHPPRO147'], 'active', 'active', array('0'=>$locale['no'], '1'=>$locale['yes']), '', array('tip'=>$locale['ESHPPRO148'], 'width'=>'100%'));
		echo form_select($locale['ESHPPRO145'], 'status', 'status', array('0'=>$locale['no'], '1'=>$locale['yes']), '', array('tip'=>$locale['ESHPPRO146'], 'width'=>'100%'));
		closeside();
		openside('');
		echo form_select($locale['ESHPPRO137'], 'stock', 'stock', array('1'=>$locale['yes'],'2'=>$locale['no']), '', array('tip'=> $locale['ESHPPRO140'], 'width'=>'100%'));
		echo form_text($locale['ESHPPRO141'], 'instock', 'instock', '', array('tip'=>$locale['ESHPPRO142'], 'number'=>1));
		closeside();
		echo "</div>\n</div>\n";
		echo closetabbody();

		// product feature
		echo opentabbody($subtab_title['title'][2], $subtab_title['id'][2], $tab_active);
		echo "<div class='row m-t-20'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";
		openside('');
		echo form_text($locale['ESHPPRO135'], 'demo', 'demo', '', array('inline'=>1, 'tip'=>$locale['ESHPPRO136'], 'url'=>1, 'placeholder'=>'http://'));
		closeside();
		openside('');
		echo form_para('Custom Attributes', 'cst', 'cst', array('tip'=>$locale['ESHPPRO117']));
		echo form_text('Attributes', 'dynf', 'dynf', '', array('placeholder'=>'Label'));
		echo form_text('Values', 'dyncList', 'dyncList', '', array('placeholder'=>'Attributes'));
		echo "<div><a href='javascript:;' id='adddync' class='btn button btn-sm btn-primary m-b-20'>".$locale['ESHPPRO116']."</a>\n</div>\n";
		echo form_para($locale['ESHPPRO118'],'118', '118');
		echo "<div id='sList'>\n";
		echo "</div>\n";
		closeside();
		openside('');
		global $ESHPCLRS;
		for ($i=1; $i <= 135; $i++) { $colors_array[$i] = $ESHPCLRS[$i]; }
		echo form_select('Colors', 'colorList', 'colorList', $colors_array, '', array('inline'=>1, 'tip'=>$locale['ESHPPRO121'], 'width'=>'100%'));
		echo "<div id='cList'></div>\n";
		closeside();
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";
		openside('');
		echo form_text($locale['ESHPPRO133'], 'version', 'version', '', array('tip'=>$locale['ESHPPRO134']));
		closeside();
		openside('');
		echo form_text($locale['ESHPPRO114'], 'weight', 'weight', '', array('number'=>1, 'tip'=>$locale['ESHPPRO115'], 'placeholder'=> fusion_get_settings('eshop_weightscale')));
		closeside();
		echo "</div>\n";
		echo "</div>\n";
		echo closetabbody();

		// custom info
		echo opentabbody($subtab_title['title'][3], $subtab_title['id'][3], $tab_active);
		echo "<div class='row m-t-20'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";

		$tab_title['title'][] = 'Image File Upload';
		$tab_title['id'][] = 'a1';
		$tab_title['icon'][] = '';

		$tab_title['title'][] = 'Custom URL';
		$tab_title['id'][] = 'a2';
		$tab_title['icon'][] = '';

		$tab_active = tab_active($tab_title, 0);
		openside('');
		echo opentab($tab_title, $tab_active, 'custom');
		echo opentabbody($tab_title['title'][0], 'a1' , $tab_active);
		echo "<div class='m-t-20'>\n";
		$path = BASEDIR."eshop/pictures/";
		$imagebytes = $settings['eshop_image_b'];
		$imagewidth = $settings['eshop_image_w'];
		$imageheight = $settings['eshop_image_h'];
		$thumbwidth = $settings['eshop_image_tw'];
		$thumbheight = $settings['eshop_image_th'];
		$thumb2width = $settings['eshop_image_t2w'];
		$thumb2height = $settings['eshop_image_t2h'];
		echo "<span class='text-smaller display-inline-block m-b-10'>".$locale['ESHPPRO110']."</span>\n";
		echo form_fileinput($locale['ESHPPRO109'], 'imagefile', 'imagefile', $path, '', array('width'=>'190px', 'inline'=>1, 'type'=>'image',
			'max_width' => $imagewidth,
			'max_height' => $imageheight,
			'max_byte'=> $imagebytes,
			'thumbnail_folder' => 'thumb',
			'thumbnail'=>1,
			'thumbnail_w'=> $thumbwidth,
			'thumbnail_h'=> $thumbheight,
			'thumbnail2'=>1,
			'thumbnail2_w'=> $thumb2width,
			'thumbnail2_h'=> $thumb2height,
		));
		echo "</div>\n";
		echo closetabbody();
		echo opentabbody($tab_title['title'][1], 'a2', $tab_active);
		echo "<div class='m-t-20'>\n";
		echo form_text($locale['ESHPPRO130'], 'image', 'image', '', array('inline'=>1, 'url'=>1, 'placeholder'=>'http://'));
		echo form_text($locale['ESHPPRO131'], 'thumb', 'thumb', '', array('inline'=>1, 'url'=>1, 'placeholder'=>'http://'));
		echo form_text($locale['ESHPPRO132'], 'thumb2', 'thumb2', '', array('inline'=>1, 'url'=>1, 'placeholder'=>'http://'));
		echo "</div>\n";
		echo closetabbody();
		echo closetab();
		closeside();
		echo form_checkbox($locale['ESHPPRO190'], 'linebreaks', 'linebreaks', '');
		//echo "<span class='text-smaller'>".$locale['ESHPPRO163']."</span>\n";
		echo form_text('Additional Information 1', 'anything1n', 'anything1n', '', array('placeholder'=>'Section Title'));
		echo form_textarea('', 'anything1', 'anything1', '', array('autosize'=>1));
		echo form_text('Additional Information 2', 'anything2n', 'anything2n', '', array('placeholder'=>'Section Title'));
		echo form_textarea('', 'anything2', 'anything2', '', array('autosize'=>1));
		echo form_text('Additional Information 3', 'anything3n', 'anything3n', '', array('placeholder'=>'Section Title'));
		echo form_textarea('', 'anything3', 'anything3', '', array('autosize'=>1));
		echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";
		openside('');
		echo form_select($locale['ESHPPRO126'], 'gallery_on', 'gallery_on', array('0'=>$locale['off'], '1'=>$locale['on']), '', array('width'=>'100%', 'tip'=>$locale['ESHPPRO129']));
		closeside();
		openside('');
		echo form_select($locale['ESHPPRO154'], 'buynow', 'buynow', array('0'=>$locale['no'], '1'=> $locale['yes']), '', array('tip'=>$locale['ESHPPRO155'], 'width'=>'100%'));
		$callback_dir = makefilelist(BASEDIR."eshop/purchasescripts/", ".|..|index.php", TRUE, "files");
		foreach($callback_dir as $page) {
			$page_array[$page] = $page;
		}
		echo form_select($locale['ESHPPRO156'], 'rpage', 'rpage', $page_array, '', array('tip'=>$locale['ESHPPRO158'], 'width'=>'100%'));
		closeside();
		echo "</div>\n";
		echo "</div>\n";
		echo closetabbody();
		echo closetab();

		echo form_hidden('', 'dateadded', 'dateadded', '');
		/*
		 * [1/23/15, 3:12:27 PM] Domi -: Most basic ones
[1/23/15, 3:12:28 PM] Domi -: Product title *
Your Art.no
Supplier Art.no
Product Language(s)
Order
Keywords
Price
Xprice

Short description
Full description
Enable line breaks in description tabs, ( need to be off for HTML ).
		 */
		echo form_button($locale['save'], 'save_cat', 'save_cat', $locale['save'], array('class'=>'btn btn-primary'));
		echo closeform();
		echo "</div>\n";
	}

	static function product_listing() {
		global $locale, $aidlink;
		echo "<div class='m-t-20'>\n";
		echo openform('search_form', 'search_form', 'post', FUSION_SELF.$aidlink."&amp;a_page=main", array("downtime"=>10));

		echo closeform();
		echo "</div>\n";
	}

}

/*
echo "<div style='float:right;margin-top:5px;'>\n";
echo "<form id='search_form'  name='inputform' method='post' action='".FUSION_SELF.$aidlink."&amp;psearch'>
<span style='vertical-align:middle;font-size:14px;'>".$locale['ESHPPRO178']."</span>";
echo "<input type='text' name='psrchtext' class='textbox' style='margin-left:1px; margin-right:1px; margin-bottom:5px; width:160px;'  value='".$searchtext."' onblur=\"if(this.value=='') this.value='".$searchtext."';\" onfocus=\"if(this.value=='".$searchtext."') this.value='';\" />";
echo "<input type='image' id='search_image' src='".SHOP."img/search_icon.png' alt='".$locale['SRCH162']."' />";
echo "</form></div>";
*/

$item = new eShop_item();
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $item->verify_product_edit($_GET['id']) : 0;

$tab_title['title'][] = 'Products';
$tab_title['id'][] = 'product';
$tab_title['icon'][] = '';
$tab_title['title'][] = $edit ? 'Edit Product' : 'Add Product';
$tab_title['id'][] = 'itemform';
$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';

$tab_active = tab_active($tab_title, $edit ? 1 : 0, 1, 1);
// need to have a get message here

echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=main");
echo opentabbody($tab_title['title'][0], 'product', $tab_active, 1);
$item->product_listing();
echo closetabbody();
if (isset($_GET['section']) && $_GET['section'] == 'itemform') {
	echo opentabbody($tab_title['title'][1], 'itemform', $tab_active, 1);
	$item->product_form();
	echo closetabbody();
}
closetable();
?>