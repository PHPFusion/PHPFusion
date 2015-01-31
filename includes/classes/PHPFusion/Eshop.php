<?php

namespace PHPFusion;


class Eshop {
	private $max_rows = 0;
	public function __construct() {
		$_GET['category'] = isset($_GET['category']) && isnum($_GET['category']) ? : 0;
		$_GET['product'] = isset($_GET['product']) && isnum($_GET['product']) ? : 0;
		$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $this->max_rows ? : 0;
		$_GET['FilterSelect'] = isset($_POST['FilterSelect']) && isnum($_POST['FilterSelect']) ? : 0;

		// include files
	}

	// clear cart actions
	static function clear_cart() {
		global $userdata, $locale;
		$id = iMEMBER ? $userdata['user_id'] : $_SERVER['REMOTE_ADDR'];
		if (isset($_GET['clearcart']) && isnum($id)) {
			dbquery("DELETE FROM ".DB_ESHOP_CART." WHERE puid ='".$id."'");
			echo admin_message($locale['ESHPC100']);
		}
	}

	static function get_title() {
		global $locale;
		$info = array();
		if ($_GET['category']) {

		} elseif ($_GET['product']) {

		} else {
			$info['title'] = $locale['ESHP001'];
			add_to_title($locale['ESHP031']);
		}
		return $info;
	}

	// special components ??
	static function makeeshoppagenav($start, $count, $total, $range = 0, $link = "") {
		global $locale;
		if ($link == "") $link = FUSION_SELF."?";
		$res = "";
		$pg_cnt = ceil($total/$count);
		if ($pg_cnt > 1) {
			$idx_back = $start-$count;
			$idx_next = $start+$count;
			$cur_page = ceil(($start+1)/$count);
			$res .= "<table style='width:500px' class='text-center tbl-border'><tr>\n";
			if ($idx_back >= 0) {
				$res .= "<td width='20%' align='center' class='tbl2'><span class='small'><a href='$link"."rowstart=$idx_back'>".$locale['ESHP002']."</a></span></td>\n";
			}
			$idx_fst = max($cur_page-$range, 1);
			$idx_lst = min($cur_page+$range, $pg_cnt);
			if ($range == 0) {
				$idx_fst = 1;
				$idx_lst = $pg_cnt;
			} else {
				$res .= "<td width='20%' align='center' class='tbl1'><span class='small'>".$locale['ESHP003']." $cur_page/$pg_cnt</span></td>\n";
			}
			if ($idx_next < $total) {
				$res .= "<td width='20%' align='center' class='tbl2'><span class='small'><a href='$link"."rowstart=$idx_next'>".$locale['ESHP004']."</a></span></td>\n";
			}
			$res .= "</tr>\n</table>\n";
		}
		return $res;
	}

	static function buildfilters() {
		global $data, $locale, $settings, $rowstart, $filter, $category;
		$filter = "";
		echo '<script type="text/javascript">
		<!--
		var saveclass = null;
		function saveFilter(cookieValue) {
			var sel = document.getElementById("FilterSelect");
			saveclass = saveclass ? saveclass : document.body.className;
			document.body.className = saveclass + " " + sel.value;
			setCookie("Filter", cookieValue, 365);
		}
		function setCookie(cookieName, cookieValue, nDays) {
			var today = new Date();
			var expire = new Date();
			if (nDays==null || nDays==0)
				nDays=1;
			expire.setTime(today.getTime() + 3600000*24*nDays);
			document.cookie = cookieName+"="+escape(cookieValue) + ";expires="+expire.toGMTString();
			$("#filters").submit();
		}
		function readCookie(name) {
		  var nameEQ = name + "=";
		  var ca = document.cookie.split(";");
		  for(var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == " ") c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
		  }
		  return null;
		}
		function readCookie(name) {
		  var nameEQ = name + "=";
		  var ca = document.cookie.split(";");
		  for(var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == " ") c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
		  }
		  return null;
		}
		document.addEventListener("DOMContentLoaded", function() {
			var FilterSelect = document.getElementById("FilterSelect");
			var selectedFilter = readCookie("Filter");
			FilterSelect.value = selectedFilter;
			saveclass = saveclass ? saveclass : document.body.className;
			document.body.className = saveclass + " " + selectedFilter;
		});
		-->
		</script>';

		echo "<div style='float:right;margin-top:5px;margin-left:5px;'>";
		echo "<form name='filters' id='filters' action='".FUSION_SELF."".(isset($_GET['rowstart']) ? "?rowstart=".$_GET['rowstart']."" : "")."".(isset($_GET['category']) ? "&amp;category=".$_GET['category']."" : "")."".(isset($_REQUEST['esrchtext']) ? "&amp;esrchtext=".$_REQUEST['esrchtext']."" : "")."' method='post'>
		<div style='font-size:16px;display:inline;vertical-align:middle;'> ".$locale['ESHPF207']." </div> <select class='eshptextbox' style='height:23px !important;width:140px !important;' name='FilterSelect' id='FilterSelect' onchange='saveFilter(this.value);'>
		<option value='1'>".$locale['ESHPF200']."</option>
		<option value='2'>".$locale['ESHPF201']."</option>
		<option value='3'>".$locale['ESHPF202']."</option>
		<option value='4'>".$locale['ESHPF203']."</option>
		<option value='5'>".$locale['ESHPF204']."</option>
		<option value='6'>".$locale['ESHPF205']."</option>
		<option value='7'>".$locale['ESHPF206']."</option>
		</select></form></div>";
		if (!isset($_COOKIE['Filter'])) {
			$filter = "iorder ASC";
		}
		if (isset($_COOKIE['Filter']) && $_COOKIE['Filter'] == "1") {
			$filter = "iorder ASC";
		}
		if (isset($_COOKIE['Filter']) && $_COOKIE['Filter'] == "2") {
			$filter = "sellcount DESC";
		}
		if (isset($_COOKIE['Filter']) && $_COOKIE['Filter'] == "3") {
			$filter = "id DESC";
		}
		if (isset($_COOKIE['Filter']) && $_COOKIE['Filter'] == "4") {
			$filter = "price ASC";
		}
		if (isset($_COOKIE['Filter']) && $_COOKIE['Filter'] == "5") {
			$filter = "price DESC";
		}
		if (isset($_COOKIE['Filter']) && $_COOKIE['Filter'] == "6") {
			$filter = "title ASC";
		}
		if (isset($_COOKIE['Filter']) && $_COOKIE['Filter'] == "7") {
			$filter = "title DESC";
		}
	}


	static function get_category() {
		$info['category'] = array();
		$info['category'] = dbquery_tree_full(DB_ESHOP_CATS, 'cid', 'parentid');
		// inject link to all items
		if (!empty($info['category'])) {
			foreach($info['category'] as $branch_id => $branch) {
				foreach($branch as $id => $node) {
					$info['category'][$branch_id][$id]['link'] = BASEDIR."eshop.php?category=".$node['cid'];
				}
			}
		}
		return $info;
	}

	public function get_product() {
		$result = NULL;
		$info = array();
		// set max rows
		$max_result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE active = '1' AND ".groupaccess('access')."");
		$this->max_rows = dbrows($max_result);
		$info['max_rows'] = $this->max_rows;
		if ($_GET['product']) {
			$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE active = '1' AND id='".intval($_GET['product'])."' AND ".groupaccess('access')." LIMIT ".$_GET['rowstart'].", ".fusion_get_settings('eshop_noppf')."");
			if (!dbrows($result)) {
				redirect(BASEDIR."eshop.php");
			}
		} else {
			$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE active = '1' AND ".groupaccess('access')." LIMIT ".$_GET['rowstart'].", ".fusion_get_settings('eshop_noppf')."");
		}
		if (dbrows($result)>0) {
			if (multilang_table("ES")) {
				while ($data = dbarray($result)) {
					$es_langs = explode('.', $data['product_languages']);
					if (in_array(LANGUAGE, $es_langs)) {
						$info['item'][$data['id']] = $data;
					}
				}
			} else {
				while ($data = dbarray($result)) {
					$info['item'][$data['id']] = $data;
				}
			}
		} else {
			$info['error'] = 'No products added'; //$locale[''];
		}

		$info['pagenav'] = ($this->max_rows > fusion_get_settings('eshop_noppf')) ? self::makeeshoppagenav($_GET['rowstart'],fusion_get_settings('eshop_noppf'),$this->max_rows,3,FUSION_SELF."?".(isset($_COOKIE['Filter']) ? "FilterSelect=".$_COOKIE['Filter']."&amp;" : "" )."") : '';
		return $info;
	}

	static function get_featured() {

		$result= dbquery("SELECT ter.* FROM ".DB_ESHOP." ter
		LEFT JOIN ".DB_ESHOP_FEATITEMS." titm ON ter.id=titm.featitem_item
		WHERE featitem_cid = '".(isset($_REQUEST['category']) ? $_REQUEST['category'] : "0")."' ORDER BY featitem_order");
		$rows = dbrows($result);

	}

}

