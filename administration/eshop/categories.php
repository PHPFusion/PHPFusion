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

/**
 * Class eShop_cats
 */
class eShop_cats {
	/**
	 * @var array|bool
	 */
	private $data = array(
		'cid' => 0,
		'title' => '',
		'parentid' => '',
		'image' => '',
		'status' => '',
		'cat_languages' => array(),
		'cat_order' => 0,
		'access' => 0,);
	/**
	 * @var array
	 */
	private $eshop_cat_index = array();
	/**
	 * @var array
	 */
	private $eshop_data_tree = array();

	/**
	 *
	 */
	public function __construct() {
		global $aidlink;
		define("CAT_DIR", BASEDIR."eshop/categoryimgs/");
		if (isset($_REQUEST['cid']) && !isnum($_REQUEST['cid'])) die("Denied");
		if (isset($_POST['status']) && !isnum($_POST['status'])) die("Denied");
		if (isset($_POST['access']) && !isnum($_POST['access'])) die("Denied");
		// sanitize all vars
		$_GET['cid'] = isset($_GET['cid']) && isnum($_GET['cid']) ? $_GET['cid'] : 0;
		$_GET['parent_id'] = isset($_GET['parent_id']) && isnum($_GET['parent_id']) ? $_GET['parent_id'] : 0;
		$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
		switch ($_GET['action']) {
			case 'refresh' :
				self::refresh_category();
				break;
			case 'moveup' :
				self::cats_moveup();
				break;
			case 'movedown':
				self::cats_movedown();
				break;
			case 'delete':
				self::cats_delete();
				break;
		}
		if (isset($_POST['save_cat'])) { // the only post for saving category
			self::set_categorydb();
		}
		// do breadcrumbs
		$this->eshop_cat_index = dbquery_tree(DB_ESHOP_CATS, 'cid', 'parentid');
		$this->eshop_data_tree = dbquery_tree_full(DB_ESHOP_CATS, 'cid', 'parentid');
		self::make_breads($this->eshop_cat_index);
		if (self::verify_cat_edit($_GET['cid']) && isset($_GET['action']) && $_GET['action'] == 'edit') {
			$this->data = self::get_data();
		}
		self::quick_save();
	}

	// quick saving function
	/**
	 *
	 */
	static function quick_save() {
		global $aidlink;
		if (isset($_POST['cats_quicksave'])) {
			//self::quick_save();
			$quick['cid'] = isset($_POST['cid']) ? form_sanitizer($_POST['cid'], '0', 'cid') : 0;
			$quick['title'] = isset($_POST['title']) ? form_sanitizer($_POST['title'], '', 'title') : '';
			$quick['image'] = isset($_POST['image']) ? form_sanitizer($_POST['image'], '', 'image') : '';
			$quick['status'] = isset($_POST['status']) ? form_sanitizer($_POST['status'], '', 'status') : '';
			$quick['access'] = isset($_POST['access']) ? form_sanitizer($_POST['access'], '0', 'access') : 0;
			if ($quick['cid']) {
				$c_result = dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE cid='".intval($quick['cid'])."'");
				if (dbrows($c_result) > 0) {
					$quick += dbarray($c_result);
					dbquery_insert(DB_ESHOP_CATS, $quick, 'update');
					redirect(FUSION_SELF.$aidlink."&amp;a_page=categories");
				}
			}
		}
	}

	// return breadcrumbs output
	/**
	 * @param $eshop_cat_index
	 */
	private function make_breads($eshop_cat_index) {
		global $aidlink;
		/* Make an infinity traverse */
		function breadcrumb_arrays($index, $id) {
			global $aidlink;
			$crumb = & $crumb;
			//$crumb += $crumb;
			if (isset($index[get_parent($index, $id)])) {
				$_name = dbarray(dbquery("SELECT cid, title FROM ".DB_ESHOP_CATS." WHERE cid='".$id."'"));
				$crumb = array('link' => FUSION_SELF.$aidlink."&amp;a_page=categories&amp;parent_id=".$_name['cid'],
					'title' => $_name['title']);
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
		if (count($crumb['title']) > 1) {
			krsort($crumb['title']);
			krsort($crumb['link']);
		}
		// then we loop it out using Dan's breadcrumb.
		if (count($crumb['title']) > 1) {
			foreach ($crumb['title'] as $i => $value) {
				add_to_breadcrumbs(array('link' => $crumb['link'][$i], 'title' => $value));
			}
		} elseif (isset($crumb['title'])) {
			add_to_breadcrumbs(array('link' => $crumb['link'], 'title' => $crumb['title']));
		}
		// hola!
	}


	/**
	 * SQL Action Refresh Category
	 */
	static function refresh_category() {
		global $aidlink;
		$i = 1;
		$result = dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE parentid='".$_GET['pid']."'");
		while ($data = dbarray($result)) {
			dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order='".$i."' WHERE cid='".$data['cid']."'");
			$i++;
		}
		redirect(FUSION_SELF.$aidlink."&amp;a_page=categories&amp;status=refresh");
	}

	/**
	 * SQL Action Move Up
	 */
	static function cats_moveup() {
		global $aidlink;
		if (isset($_GET['cid']) && isnum($_GET['cid'])) {
			$data = dbarray(dbquery("SELECT cat_order, parentid FROM ".DB_ESHOP_CATS." WHERE cid = '".$_GET['cid']."'")); // subtract 1. so i'm 5, i need to find 4
			$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order+1 WHERE parentid='".$data['parentid']."' AND cat_order='".($data['cat_order']-1)."'");
			$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order-1 WHERE cid='".$_GET['cid']."'");
			redirect(FUSION_SELF.$aidlink."&amp;a_page=categories");
		}
	}

	/**
	 * SQL Action Move Down
	 */
	static function cats_movedown() {
		global $aidlink;
		if (isset($_GET['cid']) && isnum($_GET['cid'])) {
			$data = dbarray(dbquery("SELECT cat_order, parentid FROM ".DB_ESHOP_CATS." WHERE cid = '".$_GET['cid']."'"));
			$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order-1 WHERE parentid='".$data['parentid']."' AND cat_order='".($data['cat_order']+1)."'");
			$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order+1 WHERE cid='".$_GET['cid']."'");
			redirect(FUSION_SELF.$aidlink."&amp;a_page=categories");
		}
	}

	/**
	 * SQL Action Delete Category
	 */
	static function cats_delete() {
		global $aidlink;
		if (isset($_GET['cid']) && isnum($_GET['cid'])) {
			$data = dbarray(dbquery("SELECT cat_order, parentid FROM ".DB_ESHOP_CATS." WHERE cid = '".$_GET['cid']."'"));
			$result = dbquery("UPDATE ".DB_ESHOP_CATS." SET cat_order=cat_order-1 WHERE parentid='".$data['parentid']."' AND cat_order > '".($data['cat_order'])."'");
			$result = dbquery("DELETE FROM ".DB_ESHOP_CATS." WHERE cid='".intval($_GET['cid'])."'");
			$result2 = dbquery("UPDATE ".DB_ESHOP." SET cid='0' WHERE cid='".intval($_GET['cid'])."'"); // what holds this?
			redirect(FUSION_SELF.$aidlink."&amp;a_page=categories&amp;status=del");
		}
	}

	/**
	 * Outputs Image Filename arrays
	 * @return array
	 */
	static function getImageOpts() {
		global $locale;
		$cat_files = array('default.png' => $locale['ESHPCATS122']);
		$cat_list = makefilelist(CAT_DIR, ".|..|index.php", TRUE);
		foreach ($cat_list as $files) {
			$cat_files[$files] = $files;
		}
		return $cat_files;
	}

	/**
	 * Return the status
	 * @return array
	 */
	static function getSizeOpts() {
		global $locale;
		return array('1' => $locale['ESHPCATS103'],
			'2' => $locale['ESHPCATS104'],);
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
	 * Shows Message based on $_GET['status']
	 */
	static function getMessage() {
		global $locale;
		$message = '';
		if (isset($_GET['status'])) {
			switch ($_GET['status']) {
				case 'sn' :
					$message = $locale['ESHPCATS119'];
					break;
				case 'su' :
					$message = $locale['ESHPCATS120'];
					break;
				case 'del' :
					$message = $locale['ESHPCATS118'];
					break;
			}
			if ($message) {
				echo "<div class='admin-message'>$message</div>";
			}
		}
	}

	/**
	 * Validate whether an ID exist
	 * @param $cid
	 * @return bool|string
	 */
	static function verify_cat_edit($cid) {
		return dbcount("(cid)", DB_ESHOP_CATS, "cid='".$cid."'");
	}

	/**
	 * MYSQL Actions - Save or Update
	 */
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

	/**
	 * Fetch edit data
	 * @return array|bool
	 */
	private function get_data() {
		$result = dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE cid='".intval($_GET['cid'])."'");
		if (dbrows($result) > 0) {
			return dbarray($result);
		}
		return FALSE;
	}


	/**
	 * render form template
	 */
	public function add_cat_form() {
		global $locale, $aidlink;
		$enabled_languages = fusion_get_enabled_languages();
		$this->data['cat_languages'] = (is_array($this->data['cat_languages'])) ? $this->data['cat_languages'] : array();
		$form_action = FUSION_SELF.$aidlink."&amp;a_page=categories";
		echo openform('addcat', 'add_cat', 'post', $form_action, array('class' => 'm-t-20', 'downtime' => 10));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-8 col-md-8 col-lg-8'>\n";
		echo form_text($locale['ESHPCATS100'], 'title', 'titles', $this->data['title'], array('max_length' => 100,
			'inline' => 1));
		echo form_select_tree($locale['ESHPCATS106'], 'parentid', 'parentids', $this->data['parentid'], array('inline' => 1), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
		echo form_select($locale['ESHPCATS105'], 'image', 'images', self::getImageOpts(), $this->data['image'], array('inline' => 1));
		// Languages in a row.
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo "<label class='control-label'>".$locale['ESHPPRO191']."</label>";
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n";
		foreach ($enabled_languages as $lang) {
			$check = (in_array($lang, $this->data['cat_languages'])) ? 1 : 0;
			echo "<div class='display-inline-block text-left m-r-10'>\n";
			echo form_checkbox($lang, 'cat_languages[]', 'lang-'.$lang, $check, array('value' => $lang));
			echo "</div>\n";
		}
		echo "</div>\n";
		echo "</div>\n";
		echo form_text($locale['ESHPCATS136'], 'cat_order', 'cat_orders', $this->data['cat_order'], array('inline' => 1,
			'number' => 1,
			'width' => '100px'));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-4'>\n";
		openside('');
		echo form_select($locale['ESHPCATS101'], 'status', 'statuses', self::getSizeOpts(), $this->data['status'], array('width' => '100%',
			'placeholder' => $locale['ESHPCATS102']));
		echo form_select($locale['ESHPCATS109'], 'access', 'accesses', self::getVisibilityOpts(), $this->data['access'], array('width' => '100%'));
		closeside();
		echo form_hidden('', 'cid', 'cids', $this->data['cid']);
		echo "</div>\n</div>\n";
		echo form_button($locale['save'], 'save_cat', 'save_cats', $locale['save'], array('class' => 'btn-primary'));
		echo closeform();
	}

	/**
	 * render data table with quick access
	 */
	public function category_listing() {
		global $locale, $aidlink;
		add_to_jquery("
		$('.actionbar').hide();
		$('tr').hover(
			function(e) { $('#shop-'+ $(this).data('id') +'-actions').show(); },
			function(e) { $('#shop-'+ $(this).data('id') +'-actions').hide(); }
		);
		$('.qform').hide();
		$('.qedit').bind('click', function(e) {
			// ok now we need jquery, need some security at least.token for example. lets serialize.
			$.ajax({
				url: '".ADMIN."includes/eshop_cats.php',
				dataType: 'json',
				type: 'post',
				data: { q: $(this).data('id'), token: '".$aidlink."' },
				success: function(e) {
					$('#cid').val(e.cid);
					$('#title').val(e.title);
					$('#image').select2('val', e.image);
					$('#status').select2('val', e.status);
					$('#access').select2('val', e.access);
					var length = e.link_window;
					if (e.link_window > 0) { $('#link_window').attr('checked', true);	} else { $('#link_window').attr('checked', false); }
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
		$cat_data = $this->eshop_data_tree;
		$enabled_languages = fusion_get_enabled_languages();
		echo "<div class='m-t-20'>\n";
		echo "<table class='table table-responsive'>\n";
		echo "<tr>\n";
		echo "<th></th>\n";
		echo "<th>".$locale['ESHPCATS100']."</th>\n";
		echo "<th>".$locale['ESHPCATS132']."</th>\n";
		//echo "<th>Image</th>\n";
		echo "<th>".$locale['ESHPCATS109']."</th>\n";
		echo "<th>".$locale['ESHPCATS101']."</th>\n";
		echo "<th>".$locale['ESHPCATS135']."</th>\n";
		echo "<th>".$locale['ESHPCATS136']."</th>\n";
		echo "<th>".$locale['ESHPPRO191']."</th>\n";
		echo "</tr>\n";
		echo "<tr class='qform'>\n";
		echo "<td colspan='8'>\n";
		echo "<div class='list-group-item m-t-20 m-b-20'>\n";
		echo openform('quick_edit', 'quick_edit', 'post', FUSION_SELF.$aidlink."&amp;a_page=categories", array('downtime' => 0,
			'notice' => 0));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-5 col-md-12 col-lg-6'>\n";
		echo form_text($locale['ESHPCATS100'], 'title', 'title', '');
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_select($locale['ESHPCATS105'], 'image', 'image', self::getImageOpts(), '', array('inline' => 1));
		echo form_select($locale['ESHPCATS101'], 'status', 'status', self::getSizeOpts(), '', array('inline' => 1,
			'placeholder' => $locale['ESHPCATS102']));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-3'>\n";
		echo form_select($locale['ESHPCATS109'], 'access', 'access', self::getVisibilityOpts(), '', array('inline' => 1));
		echo form_hidden('', 'cid', 'cid', '', array('writable' => 1));
		echo "</div>\n";
		echo "</div>\n";
		echo "<div class='m-t-10 m-b-10'>\n";
		echo form_button($locale['cancel'], 'cancel', 'cancel', 'cancel', array('class' => 'btn btn-default m-r-10',
			'type' => 'button'));
		echo form_button($locale['update'], 'cats_quicksave', 'cats_quicksave', 'save', array('class' => 'btn btn-primary'));
		echo "</div>\n";
		echo closeform();
		echo "</div>\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "<tbody id='eshopcat-links' class='connected'>\n";
		if (!empty($cat_data[$_GET['parent_id']])) {
			$i = 0;
			$rows = count($cat_data[$_GET['parent_id']]);
			$cat_data = sort_tree($cat_data[$_GET['parent_id']], 'cat_order');
			foreach ($cat_data as $cid => $data) {
				$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
				$subcats = get_child($this->eshop_cat_index, $data['cid']);
				$subcats = !empty($subcats) ? count($subcats) : 0;
				echo "<tr id='listItem_".$data['cid']."' data-id='".$data['cid']."' class='list-result ".$row_color."'>\n";
				echo "<td></td>\n";
				echo "<td class='col-xs-3 col-sm-3 col-md-3 col-lg-3'>\n";
				echo "<a class='text-dark' href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;parent_id=".$data['cid']."'>".$data['title']."</a>";
				echo "<div class='actionbar text-smaller' id='shop-".$data['cid']."-actions'>
				<a href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;section=catform&amp;action=edit&amp;cid=".$data['cid']."'>".$locale['edit']."</a> |
				<a class='qedit pointer' data-id='".$data['cid']."'>".$locale['qedit']."</a> |
				<a class='delete' href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;action=delete&amp;cid=".$data['cid']."' onclick=\"return confirm('".$locale['ESHPCATS134']."');\">".$locale['delete']."</a>
				";
				echo "</td>\n";
				//echo "<td>\n".thumbnail(CAT_DIR.$data['image'], '30px')."</td>\n"; // will show no image thumbnail if no image
				echo "<td>".number_format($subcats)."</td>\n";
				echo "<td>".self::getVisibilityOpts()[$data['access']]."</td>\n";
				echo "<td>".self::getSizeOpts()[$data['status']]."</td>\n";
				echo "<td>\n";
				echo ($i == 0) ? "" : "<a title='".$locale['ESHPCATS137']."' href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;action=moveup&amp;cid=".$data['cid']."'><i class='entypo up-bold m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
				echo ($i == $rows) ? "" : "<a title='".$locale['ESHPCATS138']."' href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;action=movedown&amp;cid=".$data['cid']."'><i class='entypo down-bold m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
				echo "</td>\n"; // move up and down.
				echo "<td>".$data['cat_order']."</td>\n";
				echo "<td>".str_replace('.', ', ', $data['cat_languages'])."</td>\n";
				echo "</tr>\n";
				$i++;
			}
			$html2 = "<div class='text-center m-t-10'>[ <a href='".FUSION_SELF.$aidlink."&amp;a_page=categories&amp;action=refresh&amp;pid=".$_GET['parent_id']."'> ".$locale['ESHPCATS130']." </a> ]</div>\n";
		} else {
			echo "<tr><td colspan='5' class='text-center'>".$locale['ESHPCATS115']."</td></tr>\n";
		}
		echo "</tbody>\n";
		echo "</table>\n";
		echo "</div>\n";
		if (isset($html2)) echo $html2;
	}
}

$category = new eShop_cats(); // load constructs
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $category->verify_cat_edit($_GET['cid']) : 0;
// build a new interface
$tab_title['title'][] = $locale['ESHPCATS099'];
$tab_title['id'][] = 'listcat';
$tab_title['icon'][] = '';
$tab_title['title'][] = $edit ? $locale['ESHPCATS139'] : $locale['ESHPCATS140'];
$tab_title['id'][] = 'catform';
$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
$tab_active = tab_active($tab_title, $edit ? 1 : 0, 1, 1);
$category->getMessage();
echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=categories");
echo opentabbody($tab_title['title'][0], 'listcat', $tab_active, 1);
$category->category_listing();
echo closetabbody();
if (isset($_GET['section']) && $_GET['section'] == 'catform') {
	echo opentabbody($tab_title['title'][1], 'catform', $tab_active, 1);
	$category->add_cat_form();
	echo closetabbody();
}
closetable();
?>