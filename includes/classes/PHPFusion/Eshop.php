<?php

namespace PHPFusion;


class Eshop {
	private $max_rows = 0;
	private $info = array();
	private $banner_path = '';
	public function __construct() {
		$this->banner_path = BASEDIR."eshop/pictures/banners/";
		$_GET['category'] = isset($_GET['category']) && isnum($_GET['category']) ?  $_GET['category'] : 0;
		$_GET['product'] = isset($_GET['product']) && isnum($_GET['product']) ? $_GET['product'] : 0;
		$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $this->max_rows ? : 0;
		$_GET['FilterSelect'] = isset($_POST['FilterSelect']) && isnum($_POST['FilterSelect']) ? $_POST['FilterSelect'] : 0;

		$this->info['category_index'] = dbquery_tree(DB_ESHOP_CATS, 'cid', 'parentid');
		$this->info['category'] = dbquery_tree_full(DB_ESHOP_CATS, 'cid', 'parentid');
		// include files
	}


	public function get_featured() {
		$info = array();
		$result = dbquery("select * FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_cid = '".$_GET['category']."' ORDER BY featbanner_order");
		if (dbrows($result)>0) {
			while ($data = dbarray($result)) {
				$data['featbanner_banner'] = file_exists($this->banner_path.$data['featbanner_banner']) ? $this->banner_path.$data['featbanner_banner'] : '';
				$info['featured'][$data['featbanner_aid']] = $data;
			}
		}
		return (array) $info;
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

	/**
	 * Get current category in relation to $_GET['category']
	 * @return array
	 */
	public function get_current_category() {
		$folder = get_parent($this->info['category_index'], $_GET['category']);
		if ($_GET['category']) {
			return (array) isset($this->info['category'][$folder][$_GET['category']]) ? $this->info['category'][$folder][$_GET['category']] : array();
		}
		return array();
	}

	/**
	 * Get Previous category in relation to current $_GET['category']
	 * @return array
	 */
	public function get_previous_category() {
		if ($_GET['category']) {
			$parent_id = get_parent($this->info['category_index'], $_GET['category']);
			$folder = get_parent($this->info['category_index'], $parent_id) ? get_parent($this->info['category_index'], $parent_id) : '0';
			if (isset($this->info['category'][$folder][$parent_id])) {
				return (array) $this->info['category'][$folder][$parent_id];
			} else {
				return array();
			}
		}
		return array();
	}

	public function get_title() {
		global $locale;
		$info = array();
		add_to_title($locale['ESHP031']);
		if ($_GET['category']) {
			$current_category = self::get_current_category();
			$info['title'] = $current_category['title'];
			add_to_title($locale['global_201'].$current_category['title']);
			add_to_breadcrumbs(array('link'=>BASEDIR."eshop.php?category=".$current_category['cid']."", 'title'=>$info['title']));
		} elseif ($_GET['product']) {
			add_to_head("<link rel='canonical' href='".fusion_get_settings('siteurl')."eshop.php?product=".$_GET['product']."'/>");
			add_to_title($locale['global_201'].$this->info['title']);
			add_to_title($locale['global_201'].$this->info['category_title']);
			if ($this->info['keywords']) { set_meta("keywords", $this->info['keywords']); }
			if (fusion_get_settings('eshop_folderlink') == 1 && fusion_get_settings('eshop_cats') == 1) {
				add_to_breadcrumbs(array('link'=>$this->info['category_link'], 'title'=>$this->info['category_title']));
				add_to_breadcrumbs(array('link'=>$this->info['product_link'], 'title'=>$this->info['product_title']));
			}
		} else {
			$info['title'] = $locale['ESHP001'];
		}
		return (array) $info;
	}

	/**
	 * Display Social Buttons
	 * Disable the shareing during SEO, it crash with SEO atm for some reason.
	 * wierd height behavior on g+1 button
	 * @param $product_id
	 * @param $product_picture
	 * @param $product_title
	 */
	static function display_social_buttons($product_id, $product_picture, $product_title) {
		if (!fusion_get_settings('site_seo') && fusion_get_settings('eshop_shareing') == 1) {
			//Load scripts to enable share buttons
			$meta = "<meta property='og:image' content='".fusion_get_settings('siteurl')."eshop/img/nopic.gif' />\n";
			if (file_exists(BASEDIR."eshop/pictures/".$product_picture)) {
				$meta = "<meta property='og:image' content='".fusion_get_settings('siteurl')."eshop/pictures/".$product_picture."' />\n";
			}
			add_to_head("".$meta."<meta property='og:title' content='".$product_title."' />");
			add_to_footer("
			<script type='text/javascript' src='https://connect.facebook.net/en_US/all.js#xfbml=1'></script>\n
			<script type='text/javascript' src='https://platform.twitter.com/widgets.js'></script>\n
			<script type='text/javascript' src='https://apis.google.com/js/plusone.js'>{ lang: 'en-GB' } </script>
			");

			$html = "<div class='clearfix m-b-20'>";
			//FB Like button
			$html .="<div class='pull-left m-r-10'>";
			$html .="<div id='FbCont".$product_id."'>
			<script type='text/javascript'>
				<!--//--><![CDATA[//><!--
				var fb = document.createElement('fb:like');
				fb.setAttribute('href','".fusion_get_settings('siteurl')."eshop.php?product=".$product_id."');
				fb.setAttribute('layout','button_count');
				fb.setAttribute('show_faces','true');
				fb.setAttribute('width','1');
				document.getElementById('FbCont".$product_id."').appendChild(fb);
				//--><!]]>
				</script>
			</div>";
			$html .="</div>";
			//Google+
			$html .="<div class='pull-left' style='width:70px; overflow:hidden; overflow: hidden;
					height: 40px;
					margin-top:-14px;
					display: inline-block;
					'>";
			$html .="<div class='g-plusone' id='gplusone".$product_id."'></div>
			<script type='text/javascript'>
			var Validplus=document.getElementById('gplusone".$product_id."');
			Validplus.setAttribute('data-size','medium');
			Validplus.setAttribute('data-count','true');
			Validplus.setAttribute('data-href','".fusion_get_settings('siteurl')."eshop.php?product=".$product_id."');
			</script>";
			$html .="</div>";
			//Twitter
			$html .="<div class='pull-left'>";
			$html .="<script type='text/javascript'>
			//<![CDATA[
			(function() {
    		document.write('<a href=\"http://twitter.com/share\" class=\"twitter-share-button\" data-count=\"horizontal\" data-url=\"".fusion_get_settings('siteurl')."eshop.php?product=".$product_id."\" data-text=\"".$product_title."\" data-via=\"eShop\">Tweet</a>');
    		var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
    		s.type = 'text/javascript';
    		s.async = true;
    		s1.parentNode.insertBefore(s, s1);
			})();
			//]]>
			</script>";
			$html .="</div>";
			//End share buttons
			$html .="</div>";
			return $html;
		}
	}

	static function picExist($image_file) {
		if (file_exists($image_file)) {
			return $image_file;
		} else {
			return SHOP."img/nopic_thumb.gif";
		}
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

	/**
	 * Get Category Information Array
	 * @return array
	 */
	public function get_category() {
		if (!empty($this->info['category'])) {
			foreach($this->info['category'] as $branch_id => $branch) {
				foreach($branch as $id => $node) {
					$this->info['category'][$branch_id][$id]['link'] = BASEDIR."eshop.php?category=".$node['cid'];
				}
			}
		}
		$info['category_index'] = $this->info['category_index'];
		$info['current_category'] = self::get_current_category();
		$info['previous_category'] = self::get_previous_category();
		$info['category'] = $this->info['category'];
		return (array) $info;
	}

	// Temporary Store this here for panels while I delete old codes
	protected function total_basket() {
		$username  ='';
		$settings = '';
		$items = "";
		$sum = "";
		$items = dbarray(dbquery("SELECT sum(cqty) as count FROM ".DB_ESHOP_CART." WHERE puid = '".$username."' ORDER BY tid ASC"));
		$sum = dbarray(dbquery("SELECT sum(cprice*cqty) as totals FROM ".DB_ESHOP_CART." WHERE puid = '".$username."'"));
		$vat = $settings['eshop_vat'];
		$price = $sum['totals'];
		$vat = ($price/100)*$vat;
		if ($settings['eshop_vat_default'] == "0") {
			$totalincvat = $price+$vat;
		} else {
			$totalincvat = $price;
		}
	}

	/**
	 * Fetches Product Photos when $_GET['product'] is available
	 * @return array
	 */
	static function get_product_photos() {
		$info = array();
		$result = dbquery("SELECT * FROM ".DB_ESHOP_PHOTOS." WHERE album_id='".intval($_GET['product'])."' ORDER BY photo_order");
		if (dbrows($result)>0) {
			while ($data = dbarray($result)) {
				$data['photo_filename'] = self::picExist(SHOP."pictures/album_".$data['album_id']."/".$data['photo_filename']);
				$data['photo_thumb1'] = self::picExist(SHOP."pictures/album_".$data['album_id']."/".$data['photo_thumb1']);
				$info['photos'][] = $data;
			}
		}
		return (array)$info;
	}


	/**
	 * Get Product Data from Database
	 * If ($_GET['category']) is available, will return info on the category and its child only
	 * If ($_GET['product']) is available, will return full product info
	 * @return array
	 */
	public function get_product() {
		global $locale;
		$result = null;
		$info = array();
		// set max rows
		$max_result = dbquery("SELECT id FROM ".DB_ESHOP." WHERE active = '1' AND ".groupaccess('access')."");
		$this->max_rows = dbrows($max_result);
		$info['max_rows'] = $this->max_rows;
		if ($_GET['product']) {
			$result = dbquery("SELECT i.*, if(i.cid >0, cat.title, 0) as category_title
			FROM ".DB_ESHOP." i
			LEFT JOIN ".DB_ESHOP_CATS." cat on (i.cid=cat.cid)
			WHERE active = '1' AND id='".intval($_GET['product'])."' AND ".groupaccess('access')." LIMIT ".$_GET['rowstart'].", ".fusion_get_settings('eshop_noppf')."");
			if (!dbrows($result)) {
				redirect(BASEDIR."eshop.php");
			} else {
				$data = dbarray($result);
				$es_langs = explode('.', $data['product_languages']);
				if (in_array(LANGUAGE, $es_langs)) {
					$data['net_price'] = $data['price'] * ((fusion_get_settings('eshop_vat')/100)+1); // 40% increase is 1.(40/100) = 1.4 * price = total
					$data['shipping'] = '';
					if (fusion_get_settings('eshop_freeshipsum')>0) {
						$data['shipping'] = ($data['net_price'] > fusion_get_settings('eshop_freeshipsum')) ? $locale['ESHP027']." ".$locale['ESHP028'] : $locale['ESHP025']."  ".$locale['ESHP026']." ".fusion_get_settings('eshop_freeshipsum')." ".fusion_get_settings('eshop_currency');
					}
					$data['version'] = $data['version'] ? $locale['ESHP007']." ".$data['version'] : '';
					$data['delivery'] = $data['delivery'] && $data['instock'] <=0 ?  $locale['ESHP012']." ".nl2br($data['delivery']) : '';
					$data['stock_status'] = '';
					if ($data['stock'] == 1) {
						$data['stock_status'] .= $locale['ESHP008'].": ";
						if ($data['instock'] >= 1) {
							$data['stock_status'] .= ($data['instock'] >= 10) ? $locale['ESHP009'] : $locale['ESHP010'];
							$data['stock_status'] .= " ".number_format($data['instock']);
						} else {
							$data['stock_status'] .= $locale['ESHP011'];
						}
					}

					$data['category_title'] = isnum($data['category_title']) ? "Front Page" : $data['category_title'];
					$data['category_link'] = isnum($data['category_title']) ? BASEDIR."eshop.php" : BASEDIR."category=".$data['cid'];
					$data['link'] = BASEDIR."eshop.php?product=".$data['id'];
					if ($data['thumb']) $data['thumb'] = self::picExist(BASEDIR."eshop/pictures/thumb/".$data['thumb']);
					if ($data['picture']) $data['picture'] = self::picExist(BASEDIR."eshop/pictures/".$data['picture']);

					$info['item'][$data['id']] = $data;
					$this->info['title'] = $data['title'];
					// push for title and meta
					$this->info['category_title'] = $data['category_title'];
					$this->info['category_link'] = BASEDIR."eshop.php?category=".$data['cid'];
					$this->info['product_title'] = $data['title'];
					$this->info['product_link'] = BASEDIR."eshop.php?product=".$data['id'];
					$this->info['keywords'] = $data['keywords'];

					return $info;
				}
			}
		} elseif ($_GET['category']) {
			// on category page
			$sql = "i.cid='".intval($_GET['category'])."'";
			if (isset($this->info['category'][$_GET['category']])) {
				// extract the keys of child from hierarchy tree
				$child_id = array_keys($this->info['category'][$_GET['category']]);
				$sql = "i.cid in (".intval($_GET['category']).implode(',',$child_id).")";
			}
			$result = dbquery("SELECT i.id, i.cid, i.title, i.thumb, i.price, i.picture, i.xprice, i.keywords, i.product_languages, cat.title as category_title
			FROM ".DB_ESHOP." i
			INNER JOIN ".DB_ESHOP_CATS." cat on i.cid = cat.cid
			WHERE ".$sql." AND active = '1' AND ".groupaccess('access')."
			ORDER BY dateadded DESC LIMIT ".$_GET['rowstart'].", ".fusion_get_settings('eshop_noppf')."
			");
		} else {
			// on main page
			$result = dbquery("SELECT id, cid, title, thumb, price, picture, xprice, keywords, product_languages, if(cid=0, 0, 1) as category_title FROM ".DB_ESHOP." WHERE active = '1' AND ".groupaccess('access')." ORDER BY dateadded DESC LIMIT ".$_GET['rowstart'].", ".fusion_get_settings('eshop_noppf')."");
		}
		if (dbrows($result)>0) {
			if (multilang_table("ES")) {
				while ($data = dbarray($result)) {
					$es_langs = explode('.', $data['product_languages']);
					if (in_array(LANGUAGE, $es_langs)) {
						$data['category_title'] = isnum($data['category_title']) ? "Front Page" : $data['category_title'];
						$data['category_link'] = isnum($data['category_title']) ? BASEDIR."eshop.php" : BASEDIR."category=".$data['cid'];
						$data['link'] = BASEDIR."eshop.php?product=".$data['id'];
						if ($data['thumb']) $data['thumb'] = BASEDIR."eshop/pictures/thumb/".$data['thumb'];
						if ($data['picture']) $data['picture'] = BASEDIR."eshop/pictures/".$data['picture'];
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

	static function get_featureds() {

		$result= dbquery("SELECT ter.* FROM
		".DB_ESHOP." ter
		LEFT JOIN ".DB_ESHOP_FEATITEMS." titm ON ter.id=titm.featitem_item
		WHERE featitem_cid = '".(isset($_REQUEST['category']) ? $_REQUEST['category'] : "0")."' ORDER BY featitem_order");
		$rows = dbrows($result);

	}

}

