<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: categories.php
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

class eShop_cats {


private $data = array(
					'cid' => 0,
					'title' => '',
					'parentid' => '',
					'image' => '',
					'status' => '',
					'cat_languages' => array(),
					'cat_order'	=> 0,
					'access' => 0,
				);

	private $eshop_cat_index = array();

	public function __construct() {
		define("CAT_DIR", BASEDIR."eshop/categoryimgs/");
		if (isset($_REQUEST['cid']) && !isnum($_REQUEST['cid'])) die("Denied");
		if (isset($_POST['status']) && !isnum($_POST['status'])) die("Denied");
		if (isset($_POST['access']) && !isnum($_POST['access'])) die("Denied");
		$access = isset($_POST['access']) ? $_POST['access'] : 0;
		$_GET['cid'] = isset($_GET['cid']) && isnum($_GET['cid']) ? $_GET['cid'] : 0;
		$_GET['parent_id'] = isset($_GET['parent_id']) && isnum($_GET['parent_id']) ? $_GET['parent_id'] : 0;

		if (isset($_POST['save_cat'])) { // the only post for saving category
			self::set_categorydb();
		}
		// do breadcrumbs
		$this->eshop_cat_index = dbquery_tree(DB_ESHOP_CATS, 'cid', 'parentid');
		self::doBreadcrumbs($this->eshop_cat_index);

	}

	// return breadcrumbs output
	private function doBreadcrumbs($eshop_cat_index) {
		global $aidlink;
		/* Make an infinity traverse */
		function breadcrumb_arrays($index, $id) {
			global $aidlink;
			$crumb = &$crumb;
			//$crumb += $crumb;
			if (isset($index[get_parent($index, $id)])) {
				$_name = dbarray(dbquery("SELECT cid, title FROM ".DB_ESHOP_CATS." WHERE cid='".$id."'"));
				$crumb = array('link'=>FUSION_SELF.$aidlink."&amp;parent_id=".$_name['cid'], 'title'=>$_name['title']);
				if (isset($index[get_parent($index, $id)])) {
					if (get_parent($index, $id) == 0) {
						return $crumb;
					}
					$crumb_1 = breadcrumb_arrays($index, get_parent($index, $id));
					$crumb = array_merge_recursive($crumb, $crumb_1); // convert so can comply to Fusion Tab API.
				}
			}
			return $crumb;
		}
		// then we make a infinity recursive function to loop/break it out.
		$crumb = breadcrumb_arrays($eshop_cat_index, $_GET['parent_id']);
		// then we sort in reverse.
		if (count($crumb['title']) > 1)  { krsort($crumb['title']); krsort($crumb['link']); }
		// then we loop it out using Dan's breadcrumb.
		if (count($crumb['title']) > 1) {
			foreach($crumb['title'] as $i => $value) {
				add_to_breadcrumbs(array('link'=>$crumb['link'][$i], 'title'=>$value));
			}
		} elseif (isset($crumb['title'])) {
			add_to_breadcrumbs(array('link'=>$crumb['link'], 'title'=>$crumb['title']));
		}
		// hola!
	}

	// returns an array of $cat_files;
	static function getImageOpts() {
		global $locale;

		$cat_files = array('default.png' => $locale['ESHPCATS122']);
		$cat_list = makefilelist(CAT_DIR, ".|..|index.php", TRUE);
		foreach($cat_list as $files) {
			$cat_files[$files] = $files;
		}
		return $cat_files;
	}
	// Return the sizes
	static function getSizeOpts() {
		global $locale;
		return array(
			'1' => $locale['ESHPCATS103'],
			'2' => $locale['ESHPCATS104'],
		);
	}
	// Return access levels
	static function getVisibilityOpts() {
		$visibility_opts = array();
		$user_groups = getusergroups();
		while(list($key, $user_group) = each($user_groups)){
			$visibility_opts[$user_group[0]] = $user_group[1];
		}
		return $visibility_opts;
	}
	// Return rows
	static function verify_cat_edit($cid) {
		return dbcount("(cid)", DB_ESHOP_CATS, "cid='".$cid."'");
	}

	// MYSQL set or update
	private function set_categorydb() {
		global $aidlink;
		$this->data['cid'] = isset($_POST['cid']) ? form_sanitizer($_POST['cid'], '0', 'cid') : 0;
		$this->data['title'] = isset($_POST['title']) ? form_sanitizer($_POST['title'], '', 'title') : '';
		$this->data['parentid'] = isset($_POST['parentid']) ? form_sanitizer($_POST['parentid'], '', 'parentid') : '';
		$this->data['image'] = isset($_POST['image']) ? form_sanitizer($_POST['image'], '', 'image') : '';
		$this->data['status'] = isset($_POST['status']) ? form_sanitizer($_POST['status'], '', 'status') : '';
		$this->data['cat_languages'] = isset($_POST['cat_languages']) ? form_sanitizer($_POST['cat_languages'], '') : array();
		$this->data['cat_order'] = isset($_POST['cat_order']) ? form_sanitizer($_POST['cat_order'], '0', 'cat_order') : 0;
		$this->data['access'] = isset($_POST['access']) ? form_sanitizer($_POST['access'], '0', 'access') : 0;
		if (self::verify_cat_edit($this->data['cid'])) { // this is update
			// find the category
			$old_order = dbarray(dbquery("SELECT cat_order FROM ".DB_ESHOP_CATS." WHERE cid='".$this->data['cid']."'"));
			if ($old_order > $this->data['cat_order']) { // current order is shifting up. 6 to 3., 1,2,(3),3->4,4->5,5->6. where orders which is less than 6 but is more or equals current.
				$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order+1 WHERE parentid='".$data['parentid']."' AND cat_order<'".$old_order['cat_order']."' AND cat_order>='".$this->data['cat_order']."'");
			} elseif ($old_order < $this->data['cat_order']) { // current order is shifting down. 3 to 6. 1,2,(3),3<-4,5,5<-(6),7. where orders which is more than old order, and less than current equals.
				$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order-1 WHERE parentid='".$this->data['parentid']."' AND cat_order>'".$old_order['cat_order']."' AND cat_order<='".$this->data['cat_order']."'");
			} // else no change.
			dbquery_insert(DB_ESHOP_CATS, $this->data, 'update');
			if (!defined("FUSION_NULL")) redirect("".FUSION_SELF.$aidlink."&amp;a_page=categories&status=su");
		} else { // this is save
			if (!$this->data['cat_order']) $this->data['cat_order'] = dbresult(dbquery("SELECT MAX(cat_order) FROM ".DB_ESHOP_CATS." WHERE parentid='".$this->data['parentid']."'"), 0)+1;
			$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order+1 WHERE parentid='".$this->data['parentid']."' AND cat_order>='".$this->data['cat_order']."'");
			dbquery_insert(DB_ESHOP_CATS, $this->data, 'save');
			if (!defined("FUSION_NULL")) redirect("".FUSION_SELF.$aidlink."&amp;a_page=categories&status=sn");
		}

	}

	// the form
	public function add_cat_form() {
		global $locale, $aidlink;
		$enabled_languages = fusion_get_enabled_languages();

		$this->data['cat_languages'] = (is_array($this->data['cat_languages'])) ? $this->data['cat_languages'] : array();

		$form_action = FUSION_SELF.$aidlink."&amp;a_page=categories";
		echo openform('addcat', 'add_cat', 'post', $form_action, array('class'=>'m-t-20'));
		echo form_text($locale['ESHPCATS100'], 'title', 'title', $this->data['title'], array('max_length'=>100, 'inline'=>1));
		echo form_select_tree('Category', 'parentid', 'parentid', $this->data['parentid'], array('inline'=>1), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
		// Languages in a row.
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo "<label class='control-label'>".$locale['ESHPPRO191']."</label>";
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n";

		foreach($enabled_languages as $lang) {
			$check = (in_array($lang, $this->data['cat_languages'])) ? 1 : 0;
			echo "<div class='display-inline-block text-left m-r-10'>\n";
			echo form_checkbox($lang, 'cat_languages[]', 'lang-'.$lang, $check, array('value'=>$lang));
			echo "</div>\n";
		}

		echo "</div>\n";
		echo "</div>\n";
		echo form_select($locale['ESHPCATS105'], 'image', 'image', self::getImageOpts(), $this->data['image'], array('inline'=>1));
		echo form_select($locale['ESHPCATS101'], 'status', 'status', self::getSizeOpts(), $this->data['status'], array('inline'=>1, 'placeholder'=>$locale['ESHPCATS102']));
		echo form_select($locale['ESHPCATS109'], 'access', 'access', self::getVisibilityOpts(), $this->data['access'], array('inline'=>1));
		echo form_text('Order', 'cat_order', 'cat_order', $this->data['cat_order'], array('inline'=>1, 'number'=>1, 'width'=>'100px'));
		echo form_hidden('', 'cid', 'cid', $this->data['cid']);
		echo form_button($locale['ESHPCATS112'], 'save_cat', 'save_cat', $locale['ESHPCATS112'], array('class'=>'btn-primary'));
		echo closeform();
	}

	// the listing -- WIP (COFFEE BREAK !)
	public function category_listing() {
		global $locale, $aidlink;

		$cat_index = $this->eshop_cat_index;
		echo "<div class='m-t-20'>\n";
		if (!isset($_GET['enter_cat'])) {
			$result = dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE parentid='0' ORDER BY cat_order ASC");
			$rows = dbrows($result);
			if ($rows > 0) {
				$type_icon = array(
					'1' => 'entypo folder',
					'2' => 'entypo chat',
					'3' => 'entypo link',
					'4' => 'entypo graduation-cap');
				$i = 1;
				while ($data = dbarray($result)) {
					$up = $data['cat_order']-1;
					$down = $data['cat_order']+1;
					echo "<div class='panel panel-default'>\n";
					echo "<div class='panel-body'>\n";
					echo "<div class='pull-left m-r-10'>\n";
					echo "<i class='entypo eye'></i>\n";
					echo "</div>\n";
					echo "<div class='overflow-hide'>\n";
					echo "<div class='row'>\n";
					echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
					$html2 = '';
					if ($data['image'] && file_exists(CAT_DIR.$data['image'])) {
						echo "<div class='pull-left m-r-10'>\n".thumbnail(CAT_DIR.$data['image'], '50px')."</div>\n";
						echo "<div class='overflow-hide'>\n";
						$html2 = "</div>\n";
					}
					echo "<span class='strong'><a href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;enter_cat&amp;cid=".$data['cid']."'>".$data['title']."</a></span>".$html2."";
					echo "</div>\n<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
					echo "<div class='pull-right'>\n";
					echo ($i == 1) ? "" : "<a title='mup' href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;action=moveup&amp;order=$up&amp;cid=".$data['cid']."'><i class='entypo up-bold m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
					echo ($i == $rows) ? "" : "<a title='mdown' href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;action=movedown&amp;order=$down&amp;cid=".$data['cid']."'><i class='entypo down-bold m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
					echo "<a title='".$locale['ESHPCATS133']."' href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;EditCurrentCategory&cid=".$data['cid']."'><i class='entypo cog m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
					echo "<a title='".$locale['ESHPCATS117']."' href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;deletecat&amp;cid=".$data['cid']."' onclick=\"return confirm('".$locale['ESHPCATS134']."');\"><i class='entypo icancel m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
					echo "</div>\n";
					$subcats = get_child($cat_index, $data['cid']);
					$subcats = !empty($subcats) ? count($subcats) : 0;
					echo "<span class='text-dark text-smaller strong'>".$locale['ESHPCATS132']." : ".number_format($subcats)."</span>\n<br/>";
					echo "</div></div>\n";
					echo "</div>\n";
					echo "</div>\n</div>\n";
					$i++;
				}
				echo "<div style='text-align:center;margin-top:5px'>[ <a href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;action=refresh'> ".$locale['ESHPCATS130']." </a> ]</div>\n";
			} else {
				echo "<div class='well text-center'>".$locale['ESHPCATS115']."</div>\n";
			}

			echo openform('inputform', 'inputform', 'post', FUSION_SELF.$aidlink."&amp;a_page=categories", array('downtime' => 0,
				'notice' => 0));
			echo form_button($locale['ESHPCATS123'], 'add_main_cat', 'add_main_cat', 'add_main_cat', array('class' => 'btn btn-sm btn-primary'));
			echo closeform();
		}

		if (isset($_GET['enter_cat'])) {
			$result = dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE parentid='".$_GET['cid']."' ORDER BY cat_order ASC");
			$rows = dbrows($result);
			if ($rows > 0) {
				$type_icon = array('1' => 'entypo folder',
					'2' => 'entypo chat',
					'3' => 'entypo link',
					'4' => 'entypo graduation-cap');
				$i = 1;
				while ($data = dbarray($result)) {
					$up = $data['cat_order']-1;
					$down = $data['cat_order']+1;
					echo "<div class='panel panel-default'>\n";
					echo "<div class='panel-body'>\n";
					echo "<div class='pull-left m-r-10'>\n";
					echo "<i class='entypo eye'></i>\n";
					echo "</div>\n";
					echo "<div class='overflow-hide'>\n";
					echo "<div class='row'>\n";
					echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
					$html2 = '';
					if ($data['image'] && file_exists(CAT_DIR.$data['image'])) {
						echo "<div class='pull-left m-r-10'>\n".thumbnail(CAT_DIR.$data['image'], '50px')."</div>\n";
						echo "<div class='overflow-hide'>\n";
						$html2 = "</div>\n";
					}
					echo "<span class='strong'><a href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;enter_cat&amp;cid=".$data['cid']."'>".$data['title']."</a></span>".$html2."";
					echo "</div>\n<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
					echo "<div class='pull-right'>\n";
					echo ($i == 1) ? "" : "<a title='mup' href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;action=moveupsub&amp;order=$up&amp;cid=".$data['cid']."&amp;mcid=".$_GET['cid']."'><i class='entypo up-bold m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
					echo ($i == $rows) ? "" : "<a title='mdown' href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;action=movedownsub&amp;order=$down&amp;cid=".$data['cid']."&amp;mcid=".$_GET['cid']."'><i class='entypo down-bold m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
					echo "<a title='".$locale['ESHPCATS133']."' href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;EditCurrentCategory&cid=".$data['cid']."'><i class='entypo cog m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
					echo "<a title='".$locale['ESHPCATS117']."' href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;deletecat&amp;cid=".$data['cid']."' onclick=\"return confirm('".$locale['ESHPCATS134']."');\"><i class='entypo icancel m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
					echo "</div>\n";
					$subcats = get_child($cat_index, $data['cid']);
					$subcats = !empty($subcats) ? count($subcats) : 0;
					echo "<span class='text-dark text-smaller strong'>".$locale['ESHPCATS132']." : ".number_format($subcats)."</span>\n<br/>";
					echo "</div></div>\n";
					echo "</div>\n";
					echo "</div>\n</div>\n";
					$i++;
				}
				echo "<div style='text-align:center;margin-top:5px'>[ <a href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;action=refresh_sub_cats&amp;cid=".$_GET['cid']."'> ".$locale['ESHPCATS131']." </a> ]</div>\n";
			} else {
				echo "<div class='well text-center'>".$locale['ESHPCATS115']."</div>\n";
			}
			echo openform('inputform', 'inputform', 'post', FUSION_SELF.$aidlink."&amp;a_page=categories&amp;cid=".$_GET['cid']."", array('downtime' => 0,
				'notice' => 0));
			echo form_button($locale['ESHPCATS124'], 'add_sub_cat', 'add_sub_cat', 'add_sub_cat', array('class' => 'btn btn-sm btn-primary'));
			echo closeform();


	}
		echo "</div>\n";
	}

}










if (isset($_GET['action']) && $_GET['action'] == "refresh") {
	$i = 1;
	$result = dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE parentid='0'");
	while ($data = dbarray($result)) {
		dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order='".$i."' WHERE cid='".$data['cid']."'");
		$i++;
	}
	redirect(FUSION_SELF.$aidlink."&amp;a_page=categories&amp;cat_orderrefresh");
}
if (isset($_GET['action']) && $_GET['action'] == "refresh_sub_cats") {
	$i = 1;
	$result = dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE parentid='".$_GET['cid']."' ORDER BY cat_order");
	while ($data = dbarray($result)) {
		dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order='".$i."' WHERE cid='".$data['cid']."'");
		$i++;
	}
	redirect(FUSION_SELF.$aidlink."&amp;a_page=categories&amp;enter_cat&amp;cid=".$_GET['cid']."&amp;cat_orderrefresh");
}
if ((isset($_GET['action']) && $_GET['action'] == "moveup") && (isset($_GET['cid']) && isnum($_GET['cid']))) {
	$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE cid = '".$_GET['cid']."' AND cat_order='".intval($_GET['order'])."'"));
	$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order+1 WHERE cid='".$data['cid']."'");
	$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order-1 WHERE cid='".$_GET['cid']."'");
	redirect(FUSION_SELF.$aidlink."&amp;a_page=categories");
}
if ((isset($_GET['action']) && $_GET['action'] == "movedown") && (isset($_GET['cid']) && isnum($_GET['cid']))) {
	$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE cid = '".$_GET['cid']."' AND cat_order='".intval($_GET['order'])."'"));
	$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order-1 WHERE cid='".$data['cid']."'");
	$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order+1 WHERE cid='".$_GET['cid']."'");
	redirect(FUSION_SELF.$aidlink."&amp;a_page=categories");
}
if ((isset($_GET['action']) && $_GET['action'] == "moveupsub") && (isset($_GET['cid']) && isnum($_GET['cid']))) {
	$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE cid = '".$_GET['cid']."' AND cat_order='".intval($_GET['order'])."'"));
	$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order+1 WHERE cid='".$data['cid']."'");
	$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order-1 WHERE cid='".$_GET['cid']."'");
	redirect(FUSION_SELF.$aidlink."&amp;a_page=categories&amp;enter_cat&amp;cid=".$_GET['mcid']."");
}
if ((isset($_GET['action']) && $_GET['action'] == "movedownsub") && (isset($_GET['cid']) && isnum($_GET['cid']))) {
	$data = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE cid = '".$_GET['cid']."' AND cat_order='".intval($_GET['order'])."'"));
	$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order-1 WHERE cid='".$data['cid']."'");
	$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order+1 WHERE cid='".$_GET['cid']."'");
	redirect(FUSION_SELF.$aidlink."&amp;a_page=categories&amp;enter_cat&amp;cid=".$_GET['mcid']."");
}

if (isset($_GET['deletecat'])) {
	$result = dbquery("DELETE FROM ".DB_ESHOP_CATS." WHERE cid='".$_REQUEST['cid']."'");
	$result2 = dbquery("UPDATE ".DB_ESHOP." SET cid='0' WHERE cid='".$_REQUEST['cid']."'");
	redirect("".FUSION_SELF.$aidlink."&amp;a_page=categories&catdeleted");
}

if (isset($_GET['catdeleted'])) {
	echo "<br /><div class='admin-message'>".$locale['ESHPCATS118']."</div><br />";
}

if (isset($_GET['catadded'])) {
	echo "<br /><div class='admin-message'>".$locale['ESHPCATS119']."</div><br />";
}
if (isset($_GET['catupdated'])) {
	echo "<br /><div class='admin-message'>".$locale['ESHPCATS120']."</div><br />";
}
if (isset($_POST['SaveCategoryChanges'])) {
	$cid = stripinput($_REQUEST['cid']);
	$title = stripinput($_POST['title']);
	$image = stripinput($_POST['image']);
	$parentid = stripinput($_POST['parentid']);
	$status = stripinput($_POST['status']);
	$order = "";
	$languages = "";
	for ($pl = 0; $pl < sizeof($_POST['languages']); $pl++) {
		$languages .= $_POST['languages'][$pl].($pl < (sizeof($_POST['languages'])-1) ? "." : "");
	}
	dbquery("UPDATE ".DB_ESHOP_CATS." SET title = '$title', access= '$access',  image = '$image', parentid = '$parentid', status = '$status', cat_order='$order', cat_languages='$languages' WHERE cid ='$cid' LIMIT 1");
	//redirect("".FUSION_SELF.$aidlink."&amp;a_page=categories&catupdated");
}

if (isset($_GET['EditCurrentCategory'])) {
	$result = dbquery("SELECT * FROM ".DB_ESHOP_CATS." where cid='".$_GET['cid']."'");
	$cat_data = dbarray($result);
	$stitle = getparent($cat_data['parentid'], $cat_data['title']);
	$title1 = "$stitle";
	$image = $cat_data['image'];
	$access = $cat_data['access'];
	$order = $cat_data['cat_order'];
	$languages = $cat_data['cat_languages'];
	echo "<fieldset style='align:left;width:97%;display:block;float:left;margin-left:10px;margin-right:10px;margin-top:2px;margin-bottom:2px;'>
<legend>&nbsp;<b> ".$locale['ESHPCATS121']." </b>&nbsp;</legend>";
	echo "<form name='addcat' action='".FUSION_SELF.$aidlink."&amp;a_page=categories&SaveCategoryChanges' method='post'>";
	echo '<table width="100%" cellspacing="1" cellpadding="1" border="0" align="center">
<tr><td>'.$locale['ESHPCATS106'].'</td><td><input class="textbox" type="text" name="title" size="30" value="'.$cat_data['title'].'"/></td></tr>';
	for ($x = 0; $x < sizeof($enabled_languages); $x++) {
		$languages .= $enabled_languages[$x].(($x < sizeof($enabled_languages)-1) ? "." : "");
	}
	$langs = explode('.', $languages);
	$locale_files = makefilelist(LOCALE, ".|..", TRUE, "folders");
	echo "<td>".$locale['ESHPPRO191']."</td>";
	echo "<td colspan='2'>";
	for ($i = 0; $i < sizeof($locale_files); $i++) {
		if (in_array($locale_files[$i], $enabled_languages)) {
			echo "<input type='checkbox' value='".$locale_files[$i]."' name='languages[]' class='textbox' ".(in_array($locale_files[$i], $langs) ? "checked='checked'" : "")."> ".str_replace('_', ' ', $locale_files[$i])." ";
		}
		if ($i%2 == 0 && $i != 0) echo "<br  />";
	}
	echo "</td></tr>";
	echo '<tr><td>'.$locale['ESHPCATS105'].'</td>';
	echo "<td width='35%'><select name='image' class='textbox' style='width:200px;'><option value='".$image."' ".($image == "$image" ? " selected" : "").">".$image."</option>$cat_list</select>
</td><td width='25%'><img style='height:50px;width:50px;' src='".CAT_DIR.($image != '' ? $image : "")."' name='image_preview' alt='' /></td>";
	echo '<tr><td>'.$locale['ESHPCATS107'].'</td><td><select class="textbox" name="parentid">';
	if ($cat_data['parentid']) {
		echo '<option value="'.$cat_data['parentid'].'">'.$title1.'</option>';
	}
	echo '<option value="0">'.$locale['ESHPCATS108'].'</option>';
	$result = dbquery("SELECT cid, title, parentid FROM ".DB_ESHOP_CATS." WHERE cid!='".$_REQUEST['cid']."' ORDER BY parentid,title");
	while (list($cidp, $title, $parentid) = dbarraynum($result)) {
		if ($parentid != 0) {
			$title = getparent($parentid, $title);
		}
		echo '<option value="'.$cidp.'">'.$title.'</option>';
	}
	echo '</select></td></tr><tr><td>'.$locale['ESHPCATS101'].'</td>
<td><select class="textbox" name="status" size="1">
<option value="'.$cat_data['status'].'" selected>'.$locale['ESHPCATS102'].'</option>
<option value="1">'.$locale['ESHPCATS103'].'</option>
<option value="2">'.$locale['ESHPCATS104'].'</option>
</select></td></tr>';
	echo "<tr><td>".$locale['ESHPCATS109']."</td>
<td><select name='access' class='textbox'>
$visibility_opts</select></td>
</tr>";
	echo '<tr><td colspan="2" align="center">
<input type="hidden" name="cid" value="'.$_REQUEST['cid'].'">
<input type="hidden" name="SaveCategoryChanges" value="SaveCategoryChanges">
<input class="button" type="submit" name="submit" value="'.$locale['ESHPCATS112'].'  &raquo; '.$cat_data['title'].'"><br /><br />';
	echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=categories&deletecat&cid=".$_REQUEST['cid']."'><b>".$locale['ESHPCATS117']."  &raquo;  ".$stitle."</b></a>";
	echo '</td></tr></table></form></fieldset>';
}

if (isset($_POST['AddSubCategory'])) {
	$cid = stripinput($_REQUEST['cid']);
	$title = stripinput($_POST['title']);
	$image = stripinput($_POST['image']);
	$status = stripinput($_POST['status']);
	$languages = "";
	for ($pl = 0; $pl < sizeof($_POST['languages']); $pl++) {
		$languages .= $_POST['languages'][$pl].($pl < (sizeof($_POST['languages'])-1) ? "." : "");
	}
	$order = "";
	dbquery("INSERT INTO ".DB_ESHOP_CATS." (cid,title,access,image,parentid,status,cat_order,cat_languages)VALUES (NULL, '$title','$access', '$image', '$cid', '$status','$order','$languages');");
	redirect("".FUSION_SELF.$aidlink."&amp;a_page=categories&catadded&amp;enter_cat&cid=".$_REQUEST['cid']."");
}

// Render Out.
$category = new eShop_cats(); // load constructs
$edit = $category->verify_cat_edit($_GET['cid']);
// build a new interface
$tab_title['title'][] = 'Current Categories';
$tab_title['id'][] = 'listcat';
$tab_title['icon'][] = '';
$tab_title['title'][] = $edit ? "Edit Category" : "Add Category";
$tab_title['id'][] = 'catform';
$tab_title['icon'][] =  $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
$tab_active = tab_active($tab_title, $edit ?  1 : 0, 1, 1);
global $aidlink;
echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=categories");
echo opentabbody($tab_title['title'][0], 'listcat', $tab_active, 1);
$category->category_listing();
echo closetab();
echo opentabbody($tab_title['title'][1], 'catform', $tab_active, 1);
$category->add_cat_form();
echo closetab();


?>