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

/**
 * Class eShop_item
 */
class eShop_item {
	/**
	 * @var array|bool
	 */
	private $data = array(
		'id' => 0,
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
		'iorder'=> 0,
		'artno' => '',
		'sartno' => '',
		'instock' => '',
		'dmulti' => 1,
		'cupons' => 1,
		'access'=>0,
		'dynf' => '',
		'clist' => '',
		'slist' => '',
		'qty' => 1,
		'sellcount' => 0,
		'dateadded' => '',
		'campaign' => '',
		'product_languages' => array(),
		'ratings' => 1,
		'comments' => 1,
		'linebreaks' => 1,
		'keywords' => '',
		'iColor'=>'',
		'dync' => '',
	);
	/**
	 * @var string
	 */
	private $formaction = '';
	/**
	 * @var string
	 */
	private $filter_Sql = '';
	/**
	 * @var bool|int|string
	 */
	private $max_rowstart = 0;
	/**
	 * Constructor and Sanitize Globals
	 */
	public function __construct() {
		global $aidlink, $settings;
		$_GET['id'] = isset($_GET['id']) && isnum($_GET['id']) ? $_GET['id'] : 0;
		$_GET['parent_id'] = isset($_GET['parent_id']) && isnum($_GET['parent_id']) ? $_GET['parent_id'] : 0;
		$this->max_rowstart = dbcount("(i.id)", DB_ESHOP." i LEFT JOIN ".DB_ESHOP_CATS ." cat on (cat.cid=i.cid)", "cat.parentid='".intval($_GET['parent_id'])."'");
		$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $this->max_rowstart ? $_GET['rowstart'] : 0;
		$this->data['product_languages'] = fusion_get_enabled_languages();
		$this->data['dateadded'] = time();

		$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
		switch ($_GET['action']) {
			case 'refresh' :
				self::refresh_order();
				break;
			case 'moveup' :
				self::product_moveup();
				break;
			case 'movedown':
				self::product_movedown();
				break;
			case 'delete':
				self::product_delete();
				break;
			case 'edit' :
				if (self::verify_product_edit($_GET['id'])) {
					$this->data = self::products_data();
					$this->formaction = FUSION_SELF.$aidlink."&amp;action=edit&id=".$_GET['id']."".($settings['eshop_cats'] == "1" ? "&amp;parent_id=".$_GET['parent_id']."" : "");
				}
				break;
			default :
				$this->formaction = FUSION_SELF.$aidlink."".($settings['eshop_cats'] == "1" && isset($_GET['parent_id']) ? "&amp;parent_id=".$_GET['parent_id']."" : "");
		}

		if (isset($_POST['save_cat'])) self::set_productdb();
		self::quick_save();
	}

	static function category_check() {
		global $settings;
		if ($settings['eshop_cats'] == 1) {
			return dbcount("(cid)", DB_ESHOP_CATS);
		}
		return false;
	}

	/**
	 * Quick saving MYSQL update
	 */
	static function quick_save() {
		global $aidlink;
		if (isset($_POST['cats_quicksave'])) {
			$quick['id'] = isset($_POST['id']) ? form_sanitizer($_POST['id'], '0', 'id') : 0;
			$quick['title'] = isset($_POST['title']) ? form_sanitizer($_POST['title'], '', 'title') : '';
			$quick['artno'] = isset($_POST['artno']) ? form_sanitizer($_POST['artno'], '', 'artno') : '';
			$quick['sartno'] = isset($_POST['sartno']) ? form_sanitizer($_POST['sartno'], '', 'sartno') : '';
			$quick['price'] = isset($_POST['price']) ? form_sanitizer($_POST['price'], '', 'price') : '';
			$quick['xprice'] = isset($_POST['xprice']) ? form_sanitizer($_POST['xprice'], '0', 'xprice') : 0;
			$quick['instock'] = isset($_POST['xprice']) ? form_sanitizer($_POST['instock'], '0', 'instock') : 0;
			$quick['active'] = isset($_POST['active']) ? form_sanitizer($_POST['active'], '0', 'active') : 0;
			$quick['status'] = isset($_POST['status']) ? form_sanitizer($_POST['status'], '0', 'status') : 0;
			if ($quick['id']) {
				$c_result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE id='".intval($quick['id'])."'");
				if (dbrows($c_result) > 0) {
					$quick += dbarray($c_result);
					dbquery_insert(DB_ESHOP, $quick, 'update');
				}
			}
		}
	}

	/**
	 * Validate row exist for edit
	 * @param $id
	 * @return bool|string
	 */
	static function verify_product_edit($id) {
		return dbcount("(id)", DB_ESHOP, "id='".$id."'");
	}

	/**
	 * Get Availability Array
	 * @return array
	 */
	static function getAvailability() {
		global $locale;
		return array(
			'0' => $locale['ESHPPRO145a'],
			'1' => $locale['ESHPPRO145b'],
		);
	}

	/**
	 * Return access levels
	 * @return array
	 */
	static function getVisibilityOpts() {
		$visibility_opts = array();
		$user_groups = getusergroups();
		while (list($key, $user_group) = each($user_groups)) {
			$visibility_opts[$user_group[0]] = $user_group[1];
		}
		return $visibility_opts;
	}

	/**
	 * Refresh Order
	 */
	static function refresh_order() {
		global $aidlink, $settings;

		//$i = 1;
		//$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE cid = '".$_REQUEST['category']."' ORDER BY iorder");
		//while ($data = dbarray($result)) {
		//	$result2 = dbquery("UPDATE ".DB_ESHOP." SET iorder='$i' WHERE id='".$data['id']."'");
		//	$i++;
		//}
		//redirect(FUSION_SELF.$aidlink."&amp;iorderrefresh".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."");
	}

	/**
	 * Move Up function
	 */
	static function product_moveup() {
		global $aidlink;
		if (isset($_GET['id']) && isnum($_GET['id']) && isset($_GET['cat']) && isnum($_GET['cat'])) {
			$data = dbarray(dbquery("SELECT id, cid, iorder FROM ".DB_ESHOP." WHERE cid = '".$_GET['cat']."' AND id='".intval($_GET['id'])."'"));
			dbquery("UPDATE ".DB_ESHOP." SET iorder = iorder+1 WHERE cid = '".$data['cid']."' AND iorder = '".($data['iorder']-1)."'");
			dbquery("UPDATE ".DB_ESHOP." SET iorder = iorder-1 WHERE id = '".$data['id']."'");
			redirect(FUSION_SELF.$aidlink."&amp;a_page=main");
		}
	}
	// action movedown
	/**
	 *
	 */
	static function product_movedown() {
		global $aidlink;
		if (isset($_GET['id']) && isnum($_GET['id']) && isset($_GET['cat']) && isnum($_GET['cat'])) {
			$data = dbarray(dbquery("SELECT id, cid, iorder FROM ".DB_ESHOP." WHERE cid = '".$_GET['cat']."' AND id='".intval($_GET['id'])."'"));
			dbquery("UPDATE ".DB_ESHOP." SET iorder = iorder-1 WHERE cid = '".$data['cid']."' AND iorder = '".($data['iorder']+1)."'");
			dbquery("UPDATE ".DB_ESHOP." SET iorder = iorder+1 WHERE id = '".$data['id']."'");
			redirect(FUSION_SELF.$aidlink."&amp;a_page=main");
		}
	}
	// action delete
	/**
	 *
	 */
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
			dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder-1 WHERE iorder>'".$remove['iorder']."' AND cid = '".$remove['cid']."'");
			dbquery("DELETE FROM ".DB_ESHOP." WHERE id='".$_GET['id']."'");
			redirect(FUSION_SELF.$aidlink."&amp;a_page=main");
		}
	}

	/**
	 * Shows Message based on $_GET['status']
	 */
	static function getMessage() {
		global $locale;
		$message = '';
		if (isset($_GET['status'])) {
			switch ($_GET['status']) {
				case 'sn' :
					$message = $locale['ESHP432'];
					break;
				case 'su' :
					$message = $locale['ESHP431'];
					break;
				case 'del' :
					$message = $locale['ESHPPRO101'];
					break;
				case 'refresh' :
					$message = $locale['ESHPPRO100'];
			}
			if ($message) {
				echo "<div class='admin-message'>$message</div>";
			}
		}
	}

	/**
	 * MYSQL insert or update
	 */
	private function set_productdb() {
		global $aidlink;
			$this->data['title'] = isset($_POST['title']) ? form_sanitizer($_POST['title'], '', 'title') : '';
			$this->data['cid'] = isset($_POST['cid']) ? form_sanitizer($_POST['cid'], '', 'cid') : 0;
			$this->data['picture'] = isset($_POST['image']) ? form_sanitizer($_POST['image'], '', 'image') : '';
			$this->data['thumb'] = isset($_POST['thumb']) ? form_sanitizer($_POST['thumb'], '', 'thumb') : '';
			$this->data['thumb2'] = isset($_POST['thumb2']) ? form_sanitizer($_POST['thumb2'], '', 'thumb2') : '';
			$upload = isset($_FILES['imagefile']) ? form_sanitizer($_FILES['imagefile'], '', 'imagefile') : '';
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
			$this->data['status'] = isset($_POST['status']) ? form_sanitizer($_POST['status'], '0', 'status') : 0;
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
			$this->data['product_languages'] = isset($_POST['product_languages']) ? form_sanitizer($_POST['product_languages'], '') : '';

			if (isset($_POST['cList'])) {
				$cList = '';
				for ($i = 0, $l = count($_POST['cList']); $i < $l; $i++) {
					$cList .= ".\"".$_POST['cList'][$i]."\"";
				}
			}
			$this->data['icolor'] = isset($cList) ? form_sanitizer($cList, '') : '';

			if (isset($_POST['sList'])) {
				$sList = '';
				for ($i = 0, $l = count($_POST['sList']); $i < $l; $i++) {
					$sList .= ".\"".$_POST['sList'][$i]."\"";
				}
			}
			$this->data['dync'] = isset($sList) ? form_sanitizer($sList, '') : '';
			if (self::verify_product_edit($_GET['id'])) {

				$old_data = dbarray(dbquery("SELECT cid, iorder, dateadded FROM ".DB_ESHOP." WHERE id='".$this->data['id']."'"));
				$this->data['dateadded'] = $old_data['dateadded']; // static time
				// at anytime, if order is 0, new order means max order
				if (!$this->data['iorder']) $this->data['iorder'] = dbresult(dbquery("SELECT MAX(iorder) FROM ".DB_ESHOP." WHERE cid='".$this->data['cid']."'"), 0)+1;
				// refresh ordering
				if ($old_data['cid'] !== $this->data['cid']) { // not the same category
					// refresh ex-category ordering
					dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder-1 WHERE cid='".$old_data['cid']."' AND iorder > '".$old_data['iorder']."'"); // -1 to all previous category.
				} else { // same category
					// refresh current category
					if ($this->data['iorder'] > $old_data['iorder']) {
						//echo 'new order is more than old order';
						dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder-1 WHERE cid = '".$this->data['cid']."' AND (iorder > '".$old_data['iorder']."' AND iorder <= '".$this->data['iorder']."')");

					} elseif ($this->data['iorder'] < $old_data['iorder']) {
						//echo 'new order is less than old order';
						dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder+1 WHERE cid = '".$this->data['cid']."' AND (iorder < '".$old_data['iorder']."' AND iorder >= '".$this->data['iorder']."')");
					}
				}
				dbquery_insert(DB_ESHOP, $this->data, 'update');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;status=su");
			} else {
				if (!$this->data['iorder']) $this->data['iorder'] = dbresult(dbquery("SELECT MAX(iorder) FROM ".DB_ESHOP." WHERE cid='".$this->data['cid']."'"), 0)+1;
				$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder+1 WHERE cid = '".$this->data['cid']."' AND iorder>='".$this->data['iorder']."'");
				dbquery_insert(DB_ESHOP, $this->data, 'save');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;status=sn");
			}
			//redirect("".FUSION_SELF.$aidlink."&amp;complete&amp;error=".$error."".($settings['eshop_cats'] == "1" ? "&amp;category=".$_REQUEST['category']."" : "")."");
	}

	/**
	 * Data callback
	 * @return array|bool
	 */
	static function products_data() {
		$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE id='".$_GET['id']."'");
		if (dbrows($result)>0) {
			return dbarray($result);
		}
	}

	/**
	 * The Form Template
	 */
	public function product_form() {
		global $aidlink, $locale, $settings;

		if (!is_array($this->data['product_languages']) && empty($this->data['product_languages'])) $this->data['product_languages'] = array();
		$itemcolors = '';
		if (isset($this->data['icolor'])) {
			$itemcolors = str_replace(".", ",", html_entity_decode($this->data['icolor']));
			$itemcolors = ltrim($itemcolors, ',');
		}
		$itemdyncs = '';
		if (isset($this->data['dync'])) {
			$itemdyncs = str_replace(".", ",", html_entity_decode($this->data['dync']));
			$itemdyncs = ltrim($itemdyncs, ',');
		}
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
			   error:function(e) {
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
		echo openform('productform', 'productform', 'post', $this->formaction, array('enctype'=>1));

		$subtab_title['title'][] = $locale['ptabs_000'];
		$subtab_title['id'][] = "pinfo";
		$subtab_title['icon'][] = 'fa fa-gift fa-lg m-r-10';

		$subtab_title['title'][] = $locale['ptabs_001'];
		$subtab_title['id'][] = "pricing";
		$subtab_title['icon'][] = 'fa fa-cube fa-lg m-r-10';

		$subtab_title['title'][] = $locale['ptabs_002'];
		$subtab_title['id'][] = "pfeature";
		$subtab_title['icon'][] = 'fa fa-info-circle fa-lg m-r-10';

		$subtab_title['title'][] = $locale['ptabs_003'];
		$subtab_title['id'][] = "cfeature";
		$subtab_title['icon'][] = 'fa fa-pencil-square fa-lg m-r-10';

		$tab_active = tab_active($subtab_title, 0);
		// primary information
		echo "<div class='row m-t-20'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";
		echo form_text($locale['ESHPPRO104'], 'title', 'title', $this->data['title'], array('required'=>1, 'inline'=>1));
		echo form_select($locale['ESHPPRO192'], 'keywords', 'keywords', array(), $this->data['keywords'], array('width'=>'100%', 'tags'=>1, 'multiple'=>1, 'inline'=>1));
		echo form_text($locale['ESHPPRO122'], 'iorder', 'iorder', $this->data['iorder'], array('inline'=>1, 'number'=>1, 'width'=>'200px', 'tip'=>$locale['ESHPPRO123']));
		// languages
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo "<label class='control-label'>".$locale['ESHPPRO191']."</label></div>\n";
		echo "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n";
		foreach (fusion_get_enabled_languages() as $lang) {
			$check = (in_array($lang, $this->data['product_languages'])) ? 1 : 0;
			echo "<div class='display-inline-block text-left m-r-10'>\n";
			echo form_checkbox($lang, 'product_languages[]', 'lang-'.$lang, $check, array('value' => $lang));
			echo "</div>\n";
		}
		echo "</div>\n";
		echo "</div>\n";
		echo "</div><div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";
		openside('');
		if (fusion_get_settings('eshop_cats')) {
			echo form_select_tree($locale['ESHPPRO105'], 'cid', 'cid', $this->data['cid'], array('no_root'=>1, 'width'=>'100%', 'placeholder'=>$locale['ESHP016']), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
		} else {
			echo $locale['ESHPPRO105']." : ".$locale['ESHPPRO106'];
			echo form_hidden('', 'cid', 'cid', 0);
		}
		echo form_select($locale['ESHPCATS109'], 'access', 'access', self::getVisibilityOpts(), $this->data['access'], array('tip'=>$locale['ESHPPRO159'],'width'=>'100%'));
		echo form_button($locale['save'], 'save_cat', 'save_cat2', $locale['save'], array('class'=>'btn-primary'));
		closeside();
		echo "</div></div>\n";
		echo opentab($subtab_title, $tab_active, 'pformtab');
		// general info
		echo opentabbody($subtab_title['title'][0], $subtab_title['id'][0], $tab_active);
		echo "<div class='row m-t-20'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";
		openside('');
		echo form_text($locale['ESHPPRO111'], 'price', 'price', $this->data['price'], array('number'=>1, 'required'=>1, 'width'=>'200px', 'inline'=>1, 'placeholder'=>$settings['eshop_currency']));
		closeside();
		openside('');
		echo form_text($locale['ESHPPRO107'], 'artno', 'artno', $this->data['artno'], array('inline'=>1, 'placeholder'=>$locale['ESHPPRO199']));
		echo form_text($locale['ESHPPRO108'], 'sartno', 'sartno', $this->data['sartno'], array('inline'=>1, 'placeholder'=>$locale['ESHPPRO199']));
		closeside();
		if (fusion_get_settings('eshop_pretext')) {
			echo form_textarea($locale['ESHPPRO160'], 'introtext', 'introtext', $this->data['introtext'], array('html'=>1, 'preview'=>1, 'autosize'=>1));
			echo "<div class='text-smaller'>".$locale['ESHPPRO161']."</div>\n";
		} else {
			echo form_hidden('', 'introtext', 'introtext', $this->data['introtext']);
		}
		echo form_textarea($locale['ESHPPRO162'], 'description', 'description', $this->data['description'], array('html'=>1, 'preview'=>1, 'autosize'=>1));
		echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";
		echo form_text($locale['ESHPPRO124'], 'sellcount', 'sellcount', $this->data['sellcount'], array('deactivate'=>1, 'tip'=>$locale['ESHPPRO125']));
		openside('');
		echo form_checkbox($locale['ESHPPRO149'], 'cart_on', 'cart_on', $this->data['cart_on'], array('tip'=>$locale['ESHPPRO150'], 'width'=>'100%'));
		echo form_checkbox($locale['ESHPPRO193'], 'qty', 'qty', $this->data['qty'], array('tip'=>$locale['ESHPPRO151'], 'width'=>'100%'));
		closeside();
		openside('');
		echo form_checkbox($locale['ESHPPRO188'], 'ratings', 'ratings', $this->data['ratings'], array('tip'=>$locale['ESHPPRO188']));
		echo form_checkbox($locale['ESHPPRO189'], 'comments', 'comments', $this->data['comments'], array('tip'=>$locale['ESHPPRO189']));
		closeside();
		echo "</div>\n</div>\n";
		echo closetabbody();
		// pricing
		echo opentabbody($subtab_title['title'][1], $subtab_title['id'][1], $tab_active);
		echo "<div class='row m-t-20'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";
		echo form_text($locale['ESHPPRO143'], 'delivery', 'delivery', $this->data['delivery'], array('tip'=>$locale['ESHPPRO144'], 'inline'=>1, 'width'=>'200px', 'number'=>1, 'placeholder'=>$locale['ESHPPRO200']));
		echo form_text($locale['ESHPPRO152'], 'dmulti', 'dmulti', $this->data['dmulti'], array('inline'=>1, 'tip'=>$locale['ESHPPRO153'], 'width'=>'200px', 'placeholder'=>$locale['ESHP019']));
		openside('');
		echo form_text($locale['ESHPPRO112'], 'xprice', 'xprice', $this->data['xprice'], array('number'=>1, 'inline'=>1, 'width'=>'200px', 'tip'=>$locale['ESHPPRO113'], 'placeholder'=>$settings['eshop_currency']));
		echo form_checkbox($locale['ESHPPRO184'], 'campaign', 'campaign', $this->data['campaign'], array('inline'=>1, 'tip'=>$locale['ESHPPRO185'], 'class'=>'col-sm-offset-3'));
		echo form_checkbox($locale['ESHPPRO182'], 'cupons', 'cupons', $this->data['cupons'], array('inline'=>1, 'tip'=>$locale['ESHPPRO183'], 'class'=>'col-sm-offset-3'));
		closeside();
		echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";
		openside('');
		echo form_select($locale['ESHPPRO147'], 'active', 'active', array('0'=>$locale['no'], '1'=>$locale['yes']), $this->data['active'], array('tip'=>$locale['ESHPPRO148'], 'width'=>'100%'));
		echo form_select($locale['ESHPPRO145'], 'status', 'status', array('0'=>$locale['no'], '1'=>$locale['yes']), $this->data['status'], array('tip'=>$locale['ESHPPRO146'], 'width'=>'100%'));
		closeside();
		openside('');
		echo form_select($locale['ESHPPRO137'], 'stock', 'stock', array('1'=>$locale['yes'],'2'=>$locale['no']), $this->data['stock'], array('tip'=> $locale['ESHPPRO140'], 'width'=>'100%'));
		echo form_text($locale['ESHPPRO141'], 'instock', 'instock', $this->data['instock'], array('tip'=>$locale['ESHPPRO142'], 'number'=>1));
		closeside();
		echo "</div>\n</div>\n";
		echo closetabbody();
		// product feature
		echo opentabbody($subtab_title['title'][2], $subtab_title['id'][2], $tab_active);
		echo "<div class='row m-t-20'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";
		openside('');
		echo form_text($locale['ESHPPRO135'], 'demo', 'demo', $this->data['demo'], array('inline'=>1, 'tip'=>$locale['ESHPPRO136'], 'placeholder'=>'http://'));
		closeside();
		openside('');
		echo form_para($locale['ESHPPRO194'], 'cst', 'cst', array('tip'=>$locale['ESHPPRO117']));
		echo form_text($locale['ESHPPRO195'], 'dynf', 'dynf', '', array('placeholder'=>$locale['ESHPPRO197'], 'inline'=>1));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3'>\n";
		echo "<label for='dyncList' class='label-control'>\n".$locale['ESHPPRO196']."</label>\n";
		echo "</div>\n<div class='col-xs-12 col-sm-9' style='padding-left:6px;'>\n";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-6 col-sm-6'>\n";
			echo form_text('', 'dyncList', 'dyncList', '');
			echo "</div>\n<div class='col-xs-6 col-sm-6'>\n";
			echo "<div><a href='javascript:;' id='adddync' class='btn button btn-default m-b-20'><i class='fa fa-plus fa-lg'></i> ".$locale['ESHPPRO116']."</a>\n</div>\n";
			echo "</div>\n</div>\n";

		echo "</div></div>\n";
		echo form_para($locale['ESHPPRO118'],'118', '118');
		echo "<div id='sList'>\n";
		echo "</div>\n";
		closeside();
		openside('');
		global $ESHPCLRS;
		for ($i=1; $i <= 135; $i++) { $colors_array[$i] = $ESHPCLRS[$i]; }
		echo form_select($locale['ESHP017'], 'colorList', 'colorList', $colors_array, '', array('inline'=>1, 'tip'=>$locale['ESHPPRO121'], 'width'=>'100%'));
		echo "<div id='cList'></div>\n";
		closeside();
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";
		openside('');
		echo form_text($locale['ESHPPRO133'], 'version', 'version', $this->data['version'], array('tip'=>$locale['ESHPPRO134']));
		closeside();
		openside('');
		echo form_text($locale['ESHPPRO114'], 'weight', 'weight', $this->data['weight'], array('number'=>1, 'tip'=>$locale['ESHPPRO115'], 'placeholder'=> fusion_get_settings('eshop_weightscale')));
		closeside();
		echo "</div>\n";
		echo "</div>\n";
		echo closetabbody();
		// custom info
		echo opentabbody($subtab_title['title'][3], $subtab_title['id'][3], $tab_active);
		echo "<div class='row m-t-20'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";
		$tab_title['title'][] = $locale['ptabs_004'];
		$tab_title['id'][] = 'a1';
		$tab_title['icon'][] = '';
		$tab_title['title'][] = $locale['ptabs_005'];
		$tab_title['id'][] = 'a2';
		$tab_title['icon'][] = '';
		$tab_active = tab_active($tab_title, 0);
		openside('');
		echo opentab($tab_title, $tab_active, 'custom');
		echo opentabbody($tab_title['title'][0], 'a1' , $tab_active);
		echo "<div class='m-t-20'>\n";
		echo "<span class='text-smaller display-inline-block m-b-10'>".$locale['ESHPPRO110']."</span>\n";
		echo form_fileinput($locale['ESHPPRO109'], 'imagefile', 'imagefile', BASEDIR."eshop/pictures/", '', array('width'=>'190px', 'inline'=>1, 'type'=>'image',
			'max_width' => $settings['eshop_image_w'],
			'max_height' => $settings['eshop_image_h'],
			'max_byte'=> $settings['eshop_image_b'],
			'thumbnail_folder' => 'thumb',
			'thumbnail'=>1,
			'thumbnail_w'=> $settings['eshop_image_tw'],
			'thumbnail_h'=> $settings['eshop_image_th'],
			'thumbnail2'=>1,
			'thumbnail2_w'=> $settings['eshop_image_t2w'],
			'thumbnail2_h'=> $settings['eshop_image_t2h'],
		));
		echo "</div>\n";
		echo closetabbody();
		echo opentabbody($tab_title['title'][1], 'a2', $tab_active);
		echo "<div class='m-t-20'>\n";
		echo form_text($locale['ESHPPRO130'], 'image', 'image', '', array('inline'=>1, 'placeholder'=>'http://'));
		echo form_text($locale['ESHPPRO131'], 'thumb', 'thumb', '', array('inline'=>1, 'placeholder'=>'http://'));
		echo form_text($locale['ESHPPRO132'], 'thumb2', 'thumb2', '', array('inline'=>1, 'placeholder'=>'http://'));
		echo "</div>\n";
		echo closetabbody();
		echo closetab();
		closeside();
		echo form_checkbox($locale['ESHPPRO190'], 'linebreaks', 'linebreaks', $this->data['linebreaks']);
		//echo "<span class='text-smaller'>".$locale['ESHPPRO163']."</span>\n";
		echo form_text($locale['ESHPPRO201']." 1", 'anything1n', 'anything1n', $this->data['anything1n'], array('placeholder'=>$locale['ESHPPRO198']));
		echo form_textarea('', 'anything1', 'anything1', '', array('autosize'=>1));
		echo form_text($locale['ESHPPRO201']." 2", 'anything2n', 'anything2n', $this->data['anything2n'], array('placeholder'=>$locale['ESHPPRO198']));
		echo form_textarea('', 'anything2', 'anything2', '', array('autosize'=>1));
		echo form_text($locale['ESHPPRO201']." 3", 'anything3n', 'anything3n', $this->data['anything3n'], array('placeholder'=>$locale['ESHPPRO198']));
		echo form_textarea('', 'anything3', 'anything3', '', array('autosize'=>1));
		echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";
		openside('');
		echo form_select($locale['ESHPPRO126'], 'gallery_on', 'gallery_on', array('0'=>$locale['off'], '1'=>$locale['on']), $this->data['gallery_on'], array('width'=>'100%', 'tip'=>$locale['ESHPPRO129']));
		closeside();
		openside('');
		echo form_select($locale['ESHPPRO154'], 'buynow', 'buynow', array('0'=>$locale['no'], '1'=> $locale['yes']), $this->data['buynow'], array('tip'=>$locale['ESHPPRO155'], 'width'=>'100%'));
		$page_array = array();
		$callback_dir = makefilelist(BASEDIR."eshop/purchasescripts/", ".|..|index.php", TRUE, "files");
		foreach($callback_dir as $page) {
			$page_array[$page] = $page;
		}
		echo form_select($locale['ESHPPRO156'], 'rpage', 'rpage', $page_array, $this->data['rpage'], array('tip'=>$locale['ESHPPRO158'], 'width'=>'100%'));
		closeside();
		echo "</div>\n";
		echo "</div>\n";
		echo closetabbody();
		echo closetab();
		echo form_hidden('', 'dateadded', 'dateadded', $this->data['dateadded']);
		echo form_hidden('', 'id', 'id', $this->data['id']);
		echo form_button($locale['save'], 'save_cat', 'save_cat', $locale['save'], array('class'=>'btn btn-primary'));
		echo closeform();
		echo "</div>\n";
	}

	/**
	 * The Filter Template
	 */
	private function product_view_filters() {
		global $locale, $aidlink;

		$category =  isset($_POST['category']) && isnum($_POST['category'])  ? form_sanitizer($_POST['category'], '', 'category') : 0;
		$access = isset($_POST['access']) && isnum($_POST['access']) ? form_sanitizer($_POST['access'], '', 'access') : 0;
		$item_status = isset($_GET['status']) && $_GET['status'] == 1 ? 1 : 0;
		$this->filter_Sql = !$item_status ? "AND (i.status='1' or i.status='0')" : "AND i.status='0'";
		if (isset($_POST['filter'])) {
			$this->filter_Sql .= $category ? "AND i.cid='".intval($category)."'" : '';
			$this->filter_Sql .= $access ? "AND i.access='".intval($access)."'" : '';
		}
		echo "<div class='m-t-20 display-block'>\n";
		echo "<div class='display-inline-block search-align m-r-10'>\n";
		echo form_text('', 'srch_text', 'srch_text', '', array('placeholder'=>$locale['SRCH158'], 'inline'=>1, 'class'=>'m-b-0 m-r-10', 'width'=>'250px'));
		echo form_button($locale['SRCH164'], 'search', 'search-btn', $locale['SRCH158'], array('class'=>'btn-primary m-b-20 m-t-0'));
		echo "</div>\n";
		echo "<div class='display-inline-block m-r-10'>\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;status=0' ".(!$item_status ? "class='text-dark'" : '').">All (".number_format(dbcount("(id)", DB_ESHOP)).")</a>\n - ";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;status=1' ".($item_status ? "class='text-dark'" : '').">Unlisted (".number_format(dbcount("(id)", DB_ESHOP, "status='0'")).")</a>\n - ";
		echo "</div>\n";
		echo "<div class='display-inline-block'>\n";
		echo openform('get_filter', 'get_filters', 'post', clean_request('', array('aid', 'status', 'section')), array('notice'=>0, 'downtime'=>10));
		echo "</div>\n";
		echo "<div class='display-inline-block m-r-10'>\n";
		echo form_select_tree('', 'category', 'category', $category, array('no_root'=>1, 'width'=>'200px', 'allowclear'=>1, 'placeholder'=>$locale['ESHFEAT125']), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
		echo "</div>\n";
		echo "<div class='display-inline-block m-r-10'>\n";
		echo form_select('', 'access', 'access-filter', self::getVisibilityOpts(), $access, array('width'=>'150px', 'allowclear'=>1, 'placeholder'=>$locale['ESHPCATS109']));
		echo "</div>\n";
		echo "<div class='display-inline-block' >\n";
		echo form_button('Filter', 'filter', 'filter', 'go_filter', array('class'=>'btn-default'));
		echo "</div>\n";
		echo closeform();
		echo "</div>\n";
		add_to_jquery("
		$('#search-btn').bind('click', function(e) {
			$.ajax({
				url: '".ADMIN."includes/eshop_search.php',
				dataType: 'html',
				type: 'post',
				beforeSend: function(e) { $('#eshopitem-links').html('<tr><td class=\"text-center\"colspan=\'12\'><img src=\"".IMAGES."loader.gif\"/></td></tr>'); },
				data: { q: $('#srch_text').val(), token: '".$aidlink."' },
				success: function(e) {
					// append html
					$('#eshopitem-links').html(e);
				},
				error : function(e) {
				console.log(e);
				}
			});
		});
		");
	}

	/**
	 * The Listing Template
	 */
	public function product_listing() {
		global $locale, $aidlink, $settings;

		add_to_jquery("
		$('.actionbar').hide();
		$('tr').hover(
			function(e) { $('#product-'+ $(this).data('id') +'-actions').show(); },
			function(e) { $('#product-'+ $(this).data('id') +'-actions').hide(); }
		);

		$('.qform').hide();
		$('.qedit').bind('click', function(e) {
			// ok now we need jquery, need some security at least.token for example. lets serialize.
			$.ajax({
				url: '".ADMIN."includes/eshop_products.php',
				dataType: 'json',
				type: 'post',
				data: { q: $(this).data('id'), token: '".$aidlink."' },
				success: function(e) {
					$('#ids').val(e.id);
					$('#titles').val(e.title);
					$('#artnos').val(e.artno);
					$('#sartnos').val(e.sartno);
					$('#prices').val(e.price);
					$('#xprices').val(e.xprice);
					$('#instocks').val(e.instock);
					$('#actives').select2('val', e.active);
					$('#statuss').select2('val', e.status);
				},
				error : function(e) {
				console.log(e);
				}
			});
			$('.qform').show();
			$('.list-result').hide();
		});
		$('#cancel').bind('click', function(e) {
			$('.qform').hide();
			$('.list-result').show();
		});
		");
		self::product_view_filters();
		echo "<div class='m-t-20'>\n";
		echo "<table class='table table-responsive'>\n";
		echo "<tr>\n";
		echo "<th></th>\n";
		echo "<th class='col-xs-3 col-sm-3'>".$locale['ESHPPRO172']."</th>\n";
		echo "<th>".$locale['ESHFEAT125']."</th>\n";
		echo "<th>".$locale['ESHP006']."</th>\n";
		echo "<th>".$locale['ESHPF139']."</th>\n";
		echo "<th>".$locale['ESHPPRO174']."</th>\n";
		echo "<th>".$locale['ESHPCATS135']."</th>\n";
		echo "<th>".$locale['ESHPPRO169']."</th>\n";
		echo "<th>".$locale['ESHPCATS109']."</th>\n";
		echo "<th>".$locale['ESHPPRO122']."</th>\n";
		echo "<th>".$locale['ESHPPRO191']."</th>\n";
		echo "</tr>\n";
		echo "<tr class='qform'>\n";
		echo "<td colspan='12'>\n";
		echo "<div class='list-group-item m-t-20 m-b-20'>\n";
		echo openform('quick_edit', 'quick_edit', 'post', FUSION_SELF.$aidlink."&amp;a_page=main", array('downtime' => 0, 'notice' => 0));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-4'>\n";
		echo form_text($locale['ESHPPRO172'], 'title', 'titles', '', array('required'=>1, 'inline'=>1));
		echo form_text($locale['ESHPPRO107'], 'artno', 'artnos', '', array('inline'=>1));
		echo form_text($locale['ESHPPRO174'], 'sartno', 'sartnos', '', array('inline'=>1));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-4'>\n";
		echo form_text($locale['ESHPPRO111'], 'price', 'prices', $this->data['price'], array('number'=>1, 'inline'=>1, 'required'=>1, 'width'=>'100%', 'placeholder'=>$settings['eshop_currency']));
		echo form_text($locale['ESHPPRO112'], 'xprice', 'xprices', $this->data['xprice'], array('number'=>1, 'width'=>'100%', 'inline'=>1, 'placeholder'=>$settings['eshop_currency']));
		echo form_text($locale['ESHPPRO141'], 'instock', 'instocks', $this->data['instock'], array('number'=>1, 'inline'=>1));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-4'>\n";
		echo form_select($locale['ESHPPRO147'], 'active', 'actives', array('0'=>$locale['no'], '1'=>$locale['yes']), $this->data['active'], array('width'=>'100%'));
		echo form_select($locale['ESHPPRO145'], 'status', 'statuss', array('0'=>$locale['no'], '1'=>$locale['yes']), $this->data['status'], array('width'=>'100%'));
		echo form_hidden('', 'id', 'ids', '', array('writable' => 1));
		echo "</div>\n";
		echo "</div>\n";
		echo "<div class='m-t-10 m-b-10'>\n";
		echo form_button($locale['cancel'], 'cancel', 'cancel', 'cancel', array('class' => 'btn btn-default m-r-10', 'type' => 'button'));
		echo form_button($locale['update'], 'cats_quicksave', 'cats_quicksave', 'save', array('class' => 'btn btn-primary'));
		echo "</div>\n";
		echo closeform();
		echo "</div>\n";
		echo "</td>\n";
		echo "</tr>\n";

		$result = dbquery("SELECT
			i.id, i.title, i.cid, i.price, i.artno, i.sartno, i.status, i.access, i.dateadded, i.iorder, i.product_languages, cat.title as cat_title
			FROM ".DB_ESHOP." i
			".($settings['eshop_cats'] ? "INNER JOIN ".DB_ESHOP_CATS." cat on (cat.cid=i.cid)" : '')."
			".$this->filter_Sql."
			ORDER BY cat.cat_order ASC, i.iorder ASC LIMIT 0, 25
		");
		$rows = dbrows($result);
		if ($rows>0) {
			$i = 0;
			echo "<tbody id='eshopitem-links' class='connected'>\n";
			while ($data = dbarray($result)) {
				$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
				echo "<tr id='listItem_".$data['id']."' data-id='".$data['id']."' class='list-result ".$row_color."'>\n";
				echo "<td></td>\n";
				echo "<td class='col-xs-3 col-sm-3 col-md-3 col-lg-3'>\n";
				echo "<a class='text-dark' title='".$locale['edit']."' href='".FUSION_SELF.$aidlink."&amp;a_page=main&amp;section=itemform&amp;action=edit&amp;id=".$data['id']."'>".$data['title']."</a>";
				echo "<div class='actionbar text-smaller' id='product-".$data['id']."-actions'>
				<a href='".FUSION_SELF.$aidlink."&amp;a_page=main&amp;section=itemform&amp;action=edit&amp;id=".$data['id']."'>".$locale['edit']."</a> |
				<a class='qedit pointer' data-id='".$data['id']."'>".$locale['qedit']."</a> |
				<a class='delete' href='".FUSION_SELF.$aidlink."&amp;a_page=main&amp;action=delete&amp;id=".$data['id']."' onclick=\"return confirm('".$locale['ESHPCATS134']."');\">".$locale['delete']."</a>
				";
				echo "</td>\n";
				echo "<td>".($settings['eshop_cats'] ? $data['cat_title'] : $locale['global_080'])."</td>\n";
				echo "<td>".$settings['eshop_currency']." ".number_format($data['price'], 2, '.', ',')."</td>\n";
				echo "<td>".$data['artno']."</td>\n";
				echo "<td>".$data['sartno']."</td>\n";
				echo "<td>\n";
				echo ($i == 0) ? "" : "<a title='".$locale['ESHPCATS137']."' href='".FUSION_SELF.$aidlink."&amp;a_page=main&amp;action=moveup&amp;cat=".$data['cid']."&amp;id=".$data['id']."'><i class='entypo up-bold m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
				echo ($i == $rows-1) ? "" : "<a title='".$locale['ESHPCATS138']."' href='".FUSION_SELF.$aidlink."&amp;a_page=main&amp;action=movedown&amp;cat=".$data['cid']."&amp;id=".$data['id']."'><i class='entypo down-bold m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
				echo "</td>\n"; // move up and down.
				$availability = self::getAvailability();
				echo "<td>".$availability[$data['status']]."</td>\n";
				$access = self::getVisibilityOpts();
				echo "<td>".$access[$data['access']]."</td>\n";
				echo "<td>".$data['iorder']."</td>\n";
				echo "<td>".str_replace('.', ', ', $data['product_languages'])."</td>\n";
				echo "</tr>\n";
				$i++;
			}
			echo "</tbody>\n";
		} else {
			if (!self::category_check()) {
				echo "<tr>\n<td class='text-center' colspan='12'><div class='alert alert-warning m-t-20'>".$locale['ESHPPRO102']."</div></td>\n</tr>\n";
			} else {
				echo "<tr>\n<td class='text-center' colspan='12'><div class='alert alert-warning m-t-20'>".$locale['ESHPPRO177']."</div></td>\n</tr>\n";
			}
		}
		echo "</table>\n";
		if ($this->max_rowstart > $rows) {
			echo "<div class='text-center'>".makePageNav($_GET['rowstart'], 15, $this->max_rowstart, 3, FUSION_SELF.$aidlink."&amp;status=".$_GET['status'].($settings['eshop_cats'] ? "&amp;parent_id=".$_GET['parent_id'] : ''))."</div>\n";
		}
		echo "</div>\n";
	}
}

$item = new eShop_item();
$category_count = $item->category_check();
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit' && $category_count) ? $item->verify_product_edit($_GET['id']) : 0;

$tab_title['title'][] = $locale['ESHPPRO097'];
$tab_title['id'][] = 'product';
$tab_title['icon'][] = '';
if ($category_count) {
	$tab_title['title'][] = $edit ? $locale['ESHPPRO098'] : $locale['ESHPPRO099'];
	$tab_title['id'][] = 'itemform';
	$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
}
$tab_active = tab_active($tab_title, ($edit ? 'itemform' : 'product'), 1);
$item->getMessage();
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