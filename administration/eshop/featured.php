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

class eShop_banners {

	private $data = array(
		'featbanner_aid' => 0,
		'featbanner_id' => 0,
		'featbanner_cat' => 0,
		'featbanner_title' => '',
		'featbanner_url' => '',
		'featbanner_banner' => '',
		'featbanner_order' => 0
	);

	private $banner_max_rows = 0;

	public function __construct() {
		$banner_max_rows = dbcount("('featbanner_aid')", DB_ESHOP_FEATBANNERS);
		$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $banner_max_rows ? $_GET['rowstart'] : 0;
		$_GET['status'] = isset($_GET['status']) ? $_GET['status'] : '';
		$_GET['b_id'] = isset($_GET['bid']) && isnum($_GET['bid']) ? $_GET['bid'] : 0;

	}

	static function getMessage() {
		global $locale;

		switch($_GET['status']) {
			case 'sn':
				return $locale['ESHFEAT110'];
				break;
			case 'refresh':
				return $locale['ESHFEAT111'];
				break;
			case 'del':
				return $locale['ESHFEAT112'];
				break;
		}
	}



	static function verify_banner() {
		return 0;
	}
	static function get_bannerType($type, $id) {
		if (isnum($type)) {
			switch($type) {
				case '1':
					return "<a href='".BASEDIR."eshop.php?category=".$id."'>Link</a>\n";
					break;
				case '2':
					return "<a href='".BASEDIR."eshop.php?product=".$id."'>Link</a>\n";
					break;
				case '3':
					return "<a href='".BASEDIR.$id."'>Link</a>\n";
					break;
			}
		}
		return false;
	}

	public function banner_listing() {
		global $locale;
		echo "<div class='m-t-10'>\n";
		echo "<table class='table table-responsive table-striped'>\n";
		echo "<tr>\n";
		echo "<th>Featured Banner Title</th>\n";
		echo "<th>Banner Image</th>\n";
		echo "<th>Banner Display Page</th>\n";
		echo "<th>Banner Showcase On</th>\n";
		echo "<th>Banner Link</th>\n";
		echo "<th>Order</th>\n";
		echo "</tr>\n";

		// create virtual add the column in by performing analysis
		// featbanner_aid = auto increment
		// featbanner_id = product id
		// featbanner_cat = product category
		// featbanner_cid = banner category
		// get banner max rows
		$result = dbquery("SELECT b.*,
					IF(b.featbanner_cid > 0, display.title, 0) as featbanner_display_title,
		 			IF(b.featbanner_cat > 0, category.title, IF(b.featbanner_id > 0, item.title, 'Custom Link')) as featbanner_showcase_title,
		 			IF(b.featbanner_cat > 0, 1, IF(b.featbanner_id > 0, 2, 3)) as featbanner_type,
		 			IF(b.featbanner_cat > 0, b.featbanner_cat, IF(b.featbanner_id > 0, b.featbanner_id, b.featbanner_url)) as featbanner_item_id
					FROM ".DB_ESHOP_FEATBANNERS." b
					LEFT JOIN ".DB_ESHOP_CATS." display on (b.featbanner_cid=display.cid)
					LEFT JOIN ".DB_ESHOP_CATS." category on (b.featbanner_cat=category.cid)
					LEFT JOIN ".DB_ESHOP." item on (b.featbanner_id=item.id)
					ORDER BY featbanner_order ASC
					LIMIT 0, 25
					");
		$rows = dbrows($result);
		if ($rows > 0) {
			while ($data = dbarray($result)) {
				echo "<tr>\n";
				echo "<td>".($data['featbanner_title'] > 0 ? $data['featbanner_title'] : $locale['ESHFEAT128'])."</td>\n";
				echo "<td><i class='fa fa-image fa-lg'></i></td>\n"; // load image via ajax
				echo "<td>".$data['featbanner_display_title']."</td>\n";
				echo "<td>".$data['featbanner_showcase_title']."</td>\n";
				echo "<td>".self::get_bannerType($data['featbanner_type'], $data['featbanner_item_id'])."</td>\n";
				echo "<td>".$data['featbanner_order']."</td>\n";
				echo "</tr>\n";
			}
		}
		echo "</table>\n";
		echo "</div>\n";
	}

	static function get_productOpts() {
		$list = array();
		$result = dbquery("SELECT id, title FROM ".DB_ESHOP."");
		if (dbrows($result)>0) {
			while ($data = dbarray($result)) {
				$list[$data['id']] = $data['title'];
			}
		}
		return $list;
	}

	public function add_banner_form() {
		global $locale;
		echo "<div class='m-t-10'>\n";
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-8'>\n";
		openside('');
		echo form_text('Banner Title', 'featbanner_title', 'featbanner_title', $this->data['featbanner_title'], array('required'=>1, 'inline'=>1));
		closeside();
		openside('');
		echo "<strong>Showcase Type <span class='required'>*</span></strong>\n";
		echo "<hr/>\n";
		echo form_select_tree($locale['ESHFEAT125'], 'featbanner_cat', 'featbanner_cat', $this->data['featbanner_cat'], array('inline'=>1, 'parent_value'=>'None'), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
		$product_opts[0] = "None";
		$product_opts += self::get_productOpts();
		echo form_select($locale['ESHFEAT113'], 'featbanner_id', 'featbanner_id', $product_opts, $this->data['featbanner_id'], array('inline'=>1));
		echo form_text($locale['ESHFEAT127'], 'featbanner_url', 'featbanner_url', $this->data['featbanner_url'], array('inline'=>1));
		closeside();
		openside('');
		echo form_fileinput($locale['ESHFEAT115'], 'featbanner_banner', 'featbanner_banner', IMAGES, $this->data['featbanner_banner'], array('type'=>'image', 'inline'=>1));
		closeside();
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-4'>\n";
		openside('');
		echo form_text($locale['ESHFEAT122'], 'featbanner_order', 'featbanner_order', $this->data['featbanner_order'], array('number'=>1));
		echo form_button($locale['save'], 'save_banner', 'save_banner1', $locale['save'], array('class'=>'btn-primary'));
		closeside();
		echo "</div>\n";
		echo "</div>\n";
		echo form_button($locale['save'], 'save_banner', 'save_banner', $locale['save'], array('class'=>'btn-primary'));
		echo "</div>\n";
	}
}


$banner = new eShop_banners();
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $banner->verify_banner($_GET['featbanner_aid']) : 0;
$tab_title['title'][] = 'Current Banners'; //$locale['ESHPCUPNS100'];
$tab_title['id'][] = 'banner';
$tab_title['icon'][] = '';
$tab_title['title'][] =  $edit ? 'Edit Banners' : $locale['ESHFEAT109']; // $locale['ESHPCUPNS115'] : $locale['ESHPCUPNS114'];
$tab_title['id'][] = 'bannerform';
$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
$tab_active = tab_active($tab_title, $edit ? 1 : 0, 1);
echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=customers");
echo opentabbody($tab_title['title'][0], 'banner', $tab_active, 1);
$banner->banner_listing();
echo closetabbody();
if (isset($_GET['section']) && $_GET['section'] == 'bannerform') {
	echo opentabbody($tab_title['title'][1], 'bannerform', $tab_active, 1);
	$banner->add_banner_form();
	echo closetabbody();
}










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
echo "<option value='".$cat_data['cid']."'>".$cat_data['cid']." -- ".$cat_data['title']."</option>";
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




}


?>