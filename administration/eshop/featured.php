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

/**
 * Class eShop_banners
 */
class eShop_banners {
	/**
	 * @var array|bool
	 */
	private $data = array(
		'featbanner_aid' => 0,
		'featbanner_id' => 0,
		'featbanner_cid' => 0,
		'featbanner_cat' => 0,
		'featbanner_title' => '',
		'featbanner_url' => '',
		'featbanner_banner' => '',
		'featbanner_order' => 0
	);
	/**
	 * @var bool|int|string
	 */
	private $banner_max_rows = 0;
	/**
	 * @var bool|int|string
	 */
	private $item_max_rows = 0;
	/**
	 * @var string
	 */
	private $formaction = '';
	/**
	 * @var string
	 */
	private $iformaction = '';
	/**
	 * @var array|bool
	 */
	private $idata = array(
		'featitem_id' => 0,
		'featitem_title' => '',
		'featitem_description' => '',
		'featitem_item' => 0,
		'featitem_cid' => 0,
		'featitem_order' => 0,
	);

	/**
	 *
	 */
	public function __construct() {
		global $aidlink;
		// decided it will be too complex to save banners to many hundreds of folders.. it's okay to get banners just in 1 location for automated upload.
		define("FPHOTOROOT", BASEDIR."eshop/pictures/banners/");
		$this->banner_max_rows = dbcount("('featbanner_aid')", DB_ESHOP_FEATBANNERS);
		$this->item_max_rows = dbcount("('featitem_id')", DB_ESHOP_FEATITEMS);
		$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $this->banner_max_rows ? $_GET['rowstart'] : 0;
		$_GET['i_rowstart'] = isset($_GET['i_rowstart']) && isnum($_GET['i_rowstart']) && $_GET['i_rowstart'] <= $this->item_max_rows ? $_GET['i_rowstart'] : 0;
		$_GET['status'] = isset($_GET['status']) ? $_GET['status'] : '';
		$_GET['b_id'] = isset($_GET['b_id']) && isnum($_GET['b_id']) ? $_GET['b_id'] : 0;
		$_GET['i_id'] = isset($_GET['i_id']) && isnum($_GET['i_id']) ? $_GET['i_id'] : 0;
		$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
		$_GET['section'] = isset($_GET['section']) ? $_GET['section'] : 'items';
		switch($_GET['action']) {
			case 'edit':
				$this->data = self::get_bannerData($_GET['b_id']);
				$this->formaction = FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=bannerform&amp;action=edit&amp;b_id=".$_GET['b_id'];
				break;
			case 'delete':
				self::delete_banner($_GET['b_id']);
				break;
			case 'iedit':
				$this->idata = self::get_itemData($_GET['i_id']);
				$this->iformaction = FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=itemform&amp;action=iedit&amp;b_id=".$_GET['i_id'];
				break;
			case 'idelete':
				self::delete_item($_GET['i_id']);
				break;
			default:
				$this->formaction = FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=bannerform";
				$this->iformaction = FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=itemform";
		}

		self::set_bannerdb();
		self::set_itemdb();
		self::banner_quick_save();
		self::item_quick_save();
	}

	/**
	 * Shows message
	 */
	static function getMessage() {
		global $locale;
		if (isset($_GET['status'])) {
			$message = '';
			switch($_GET['status']) {
				case 'su':
					$message = $locale['ESHFEAT110b'];
					break;
				case 'sn':
					$message = $locale['ESHFEAT110'];
					break;
				case 'refresh':
					$message = $locale['ESHFEAT111'];
					break;
				case 'del':
					$message = $locale['ESHFEAT112'];
					break;
				case 'isn':
					$message = $locale['ESHFEAT110a'];
					break;
				case 'isu':
					$message = $locale['ESHFEAT110c'];
					break;
				case 'idel':
					$message = $locale['ESHFEAT110d'];
					break;
			}
			if ($message) {
				echo admin_message($message);
			}
		}
	}

	// verify mysql
	/**
	 * @param $id
	 * @return bool|string
	 */
	static function verify_banner($id) {
		if (isnum($id)) {
			return dbcount("(featbanner_aid)", DB_ESHOP_FEATBANNERS, "featbanner_aid='".intval($id)."'");
		}
		return false;
	}

	/**
	 * @param $id
	 * @return bool|string
	 */
	static function verify_item($id) {
		if (isnum($id)) {
			return dbcount("(featitem_id)", DB_ESHOP_FEATITEMS, "featitem_id='".intval($id)."'");
		}
		return false;
	}

	// get data mysql
	/**
	 * @param $id
	 * @return array|bool
	 */
	static function get_bannerData($id) {
		if (isnum($id)) {
			return dbarray(dbquery("SELECT * FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_aid='".intval($id)."'"));
		}
		return array();
	}

	/**
	 * @param $id
	 * @return array|bool
	 */
	static function get_itemData($id) {
		if (isnum($id)) {
			return dbarray(dbquery("SELECT * FROM ".DB_ESHOP_FEATITEMS." WHERE featitem_id='".intval($id)."'"));
		}
		return array();
	}

	// delete mysql
	/**
	 * @param $id
	 */
	static function delete_banner($id) {
		global $aidlink;
		if (isnum($id)) {
			$data = self::get_bannerData($_GET['b_id']);
			if (!empty($data)) {
				dbquery("UPDATE ".DB_ESHOP_FEATBANNERS." SET featbanner_order=featbanner_order-1 WHERE featbanner_order>'".$data['featbanner_order']."' AND featbanner_cid = '".$data['featbanner_cid']."'");
				if ($data['featbanner_banner']) @unlink(FPHOTOROOT.$data['featbanner_banner']);
				dbquery("DELETE FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_aid = '".intval($id)."'");
				redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=banner&amp;status=del");
			}
		}
	}

	/**
	 * @param $id
	 */
	static function delete_item($id) {
		global $aidlink;
		if (isnum($id)) {
			$data = self::get_itemData($_GET['i_id']);
			if (!empty($data)) {
				dbquery("UPDATE ".DB_ESHOP_FEATITEMS." SET featitem_order=featitem_order-1 WHERE featitem_order>'".$data['featitem_order']."' AND featitem_cid = '".$data['featbanner_cid']."'");
				dbquery("DELETE FROM ".DB_ESHOP_FEATITEMS." WHERE featitem_id = '".intval($id)."'");
				redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=items&amp;status=idel");
			}
		}
	}

	/**
	 *
	 */
	private function banner_quick_save() {
		global $defender, $locale, $aidlink;
		if (isset($_POST['banner_quicksave'])) {
			$quick['featbanner_aid'] = isset($_POST['featbanner_aid']) ? form_sanitizer($_POST['featbanner_aid'], '0', 'featbanner_aid') : 0;
			$quick['featbanner_title'] = isset($_POST['featbanner_title']) ? form_sanitizer($_POST['featbanner_title'], '', 'featbanner_title') : '';
			$quick['featbanner_cid'] = isset($_POST['featbanner_cid']) ? form_sanitizer($_POST['featbanner_cid'], '0', 'featbanner_cid') : 0;
			$quick['featbanner_id'] = isset($_POST['featbanner_id']) ? form_sanitizer($_POST['featbanner_id'], '0', 'featbanner_id') : 0;
			$quick['featbanner_url'] = isset($_POST['featbanner_url']) ? form_sanitizer($_POST['featbanner_url'], '', 'featbanner_url') : '';
			$quick['featbanner_cat'] = isset($_POST['featbanner_cat']) ? form_sanitizer($_POST['featbanner_cat'], '0', 'featbanner_cat') : 0;
			if (self::verify_banner($quick['featbanner_aid'])) {
				$c_result = dbquery("SELECT * FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_aid='".intval($quick['featbanner_aid'])."'");
				if (dbrows($c_result) > 0) {
					$quick += dbarray($c_result);
					dbquery_insert(DB_ESHOP_FEATBANNERS, $quick, 'update');
					redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=banner&amp;status=su");
				}
			}
		}
	}

	/**
	 *
	 */
	private function item_quick_save() {
		global $defender, $locale, $aidlink;
		if (isset($_POST['item_quicksave'])) {
			$quick['featitem_id'] = isset($_POST['featitem_id']) ? form_sanitizer($_POST['featitem_id'], '0', 'featitem_id') : 0;
			$quick['featitem_title'] = isset($_POST['featitem_title']) ? form_sanitizer($_POST['featitem_title'], '', 'featitem_title') : '';
			$quick['featitem_description'] = isset($_POST['featitem_description']) ? form_sanitizer($_POST['featitem_description'], '', 'featitem_description') : '';
			$quick['featitem_cid'] = isset($_POST['featitem_cid']) ? form_sanitizer($_POST['featitem_cid'], '0', 'featitem_cid') : 0;
			if (self::verify_item($quick['featitem_id'])) {
				$c_result = dbquery("SELECT * FROM ".DB_ESHOP_FEATITEMS." WHERE featitem_id='".intval($quick['featitem_id'])."'");
				if (dbrows($c_result) > 0) {
					$quick += dbarray($c_result);
					dbquery_insert(DB_ESHOP_FEATITEMS, $quick, 'update');
					redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=items&amp;status=isu");
				}
			}
		}
	}

	/**
	 *
	 */
	private function set_bannerdb() {
		global $defender, $locale, $aidlink;
		if (isset($_POST['save_banner'])) {
			$this->data['featbanner_aid'] = isset($_POST['featbanner_aid']) ? form_sanitizer($_POST['featbanner_aid'], '0', 'featbanner_aid') : 0;
			$i = 0;
			$this->data['featbanner_id'] = isset($_POST['featbanner_id']) ? form_sanitizer($_POST['featbanner_id'], '0', 'featbanner_id') : 0;
			if ($this->data['featbanner_id']) $i++;
			$this->data['featbanner_url'] = isset($_POST['featbanner_url']) ? form_sanitizer($_POST['featbanner_url'], '', 'featbanner_url') : '';
			if ($this->data['featbanner_url']) $i++;
			$this->data['featbanner_cat'] = isset($_POST['featbanner_cat']) ? form_sanitizer($_POST['featbanner_cat'], '', 'featbanner_cat') : 0;
			if ($this->data['featbanner_cat']) $i++;
			if ($i > 1) {
				$defender->stop();
				$defender->addNotice($locale['ESHFEAT131']);
			}
			$this->data['featbanner_title'] = isset($_POST['featbanner_title']) ? form_sanitizer($_POST['featbanner_title'], '', 'featbanner_title') : '';
			$this->data['featbanner_cid'] = isset($_POST['featbanner_cid']) ? form_sanitizer($_POST['featbanner_cid'], '', 'featbanner_cid') : 0;
			$this->data['featbanner_order'] = isset($_POST['featbanner_order']) ? form_sanitizer($_POST['featbanner_order'], '', 'featbanner_order') : 0;
			if (isset($_FILES['featbanner_banner']) && is_uploaded_file($_FILES['featbanner_banner']['tmp_name']) && !defined('FUSION_NULL')) {
				if ($this->data['featbanner_banner']) @unlink(FPHOTOROOT.$this->data['featbanner_banner']);
				$upload = form_sanitizer($_FILES['featbanner_banner'], '', 'featbanner_banner');
				if (empty($upload['error'])) {
					$this->data['featbanner_banner'] = $upload['image_name'];
				}

			} elseif (isset($_POST['featbanner_hidden'])) {
				$this->data['featbanner_banner'] = form_sanitizer($_POST['featbanner_hidden'], '', 'featbanner_hidden');
				if (!$this->data['featbanner_banner']) {
					$defender->stop();
					$defender->addNotice($locale['ESHFEAT130']);
				}
			}

			if (self::verify_banner($this->data['featbanner_aid'])) {
				// find the category
				$old_data = dbarray(dbquery("SELECT featbanner_cid, featbanner_order FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_cid='".$this->data['featbanner_cid']."'"));
				if (!$this->data['featbanner_order']) $this->data['featbanner_order'] = dbresult(dbquery("SELECT MAX(featbanner_order) FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_cid='".$this->data['featbanner_cid']."'"), 0)+1;
				// refresh ordering
				if ($old_data['featbanner_cid'] !== $this->data['featbanner_cid']) { // not the same category
					// refresh ex-category ordering
					dbquery("UPDATE ".DB_ESHOP_FEATBANNERS." SET featbanner_order=featbanner_order-1 WHERE featbanner_cid='".$old_data['featbanner_cid']."' AND featbanner_order > '".$old_data['featbanner_order']."'"); // -1 to all previous category.
				} else { // same category
					// refresh current category
					if ($this->data['featbanner_order'] > $old_data['featbanner_order']) {
						//echo 'new order is more than old order';
						dbquery("UPDATE ".DB_ESHOP_FEATBANNERS." SET featbanner_order=featbanner_order-1 WHERE featbanner_cid = '".$this->data['featbanner_cid']."' AND (featbanner_order > '".$old_data['featbanner_order']."' AND featbanner_order <= '".$this->data['featbanner_order']."')");
					} elseif ($this->data['featbanner_order'] < $old_data['featbanner_order']) {
						//echo 'new order is less than old order';
						dbquery("UPDATE ".DB_ESHOP_FEATBANNERS." SET featbanner_order=featbanner_order+1 WHERE featbanner_cid = '".$this->data['featbanner_cid']."' AND (featbanner_order < '".$old_data['featbanner_order']."' AND featbanner_order >= '".$this->data['featbanner_order']."')");
					}
				}
				dbquery_insert(DB_ESHOP_FEATBANNERS, $this->data, 'update');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=banner&amp;status=su");
			} else {
				// sort order
				if (!$this->data['featbanner_order']) $this->data['featbanner_order'] = dbresult(dbquery("SELECT MAX(featbanner_order) FROM ".DB_ESHOP_FEATBANNERS." WHERE featbanner_cid='".$this->data['featbanner_cid']."'"), 0)+1;
				dbquery("UPDATE ".DB_ESHOP_FEATBANNERS." SET featbanner_order=featbanner_order+1 WHERE featbanner_cid='".$this->data['featbanner_cid']."' AND featbanner_order>='".$this->data['featbanner_order']."'");
				dbquery_insert(DB_ESHOP_FEATBANNERS, $this->data, 'save');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=banner&amp;status=sn");
			}
		}
	}

	/**
	 *
	 */
	private function set_itemdb() {
		global $defender, $locale, $aidlink;
		if (isset($_POST['save_item'])) {
			$this->idata['featitem_id'] = isset($_POST['featitem_id']) ? form_sanitizer($_POST['featitem_id'], '0', 'featitem_id') : 0;
			$this->idata['featitem_title'] = isset($_POST['featitem_title']) ? form_sanitizer($_POST['featitem_title'], '', 'featitem_title') : '';
			$this->idata['featitem_description'] = isset($_POST['featitem_description']) ? form_sanitizer($_POST['featitem_description'], '', 'featitem_description') : '';
			$this->idata['featitem_item'] = isset($_POST['featitem_item']) ? form_sanitizer($_POST['featitem_item'], '0', 'featitem_item') : 0;
			$this->idata['featitem_cid'] = isset($_POST['featitem_cid']) ? form_sanitizer($_POST['featitem_cid'], '0', 'featitem_cid') : 0;
			$this->idata['featitem_order'] = isset($_POST['featitem_order']) ? form_sanitizer($_POST['featitem_order'], '', 'featitem_order') : 0;
			if (self::verify_item($this->data['featitem_id'])) {
				// find the category
				$old_data = dbarray(dbquery("SELECT featitem_cid, featitem_order FROM ".DB_ESHOP_FEATITEMS." WHERE featitem_cid='".$this->idata['featitem_cid']."'"));
				if (!$this->idata['featitem_order']) $this->idata['featitem_order'] = dbresult(dbquery("SELECT MAX(featitem_order) FROM ".DB_ESHOP_FEATITEMS." WHERE featitem_cid='".$this->idata['featitem_cid']."'"), 0)+1;
				// refresh ordering
				if ($old_data['featitem_cid'] !== $this->idata['featitem_cid']) { // not the same category
					// refresh ex-category ordering
					dbquery("UPDATE ".DB_ESHOP_FEATITEMS." SET featitem_order=featitem_order-1 WHERE featitem_cid='".$old_data['featitem_cid']."' AND featitem_order > '".$old_data['featitem_order']."'"); // -1 to all previous category.
				} else { // same category
					// refresh current category
					if ($this->idata['featitem_order'] > $old_data['featitem_order']) {
						//echo 'new order is more than old order';
						dbquery("UPDATE ".DB_ESHOP_FEATITEMS." SET featitem_order=featitem_order-1 WHERE featitem_cid = '".$this->idata['featitem_cid']."' AND (featitem_order > '".$old_data['featitem_order']."' AND featitem_order <= '".$this->idata['featitem_order']."')");
					} elseif ($this->idata['featitem_order'] < $old_data['featitem_order']) {
						//echo 'new order is less than old order';
						dbquery("UPDATE ".DB_ESHOP_FEATITEMS." SET featitem_order=featitem_order+1 WHERE featitem_cid = '".$this->idata['featitem_cid']."' AND (featitem_order < '".$old_data['featitem_order']."' AND featitem_order >= '".$this->idata['featitem_order']."')");
					}
				}
				dbquery_insert(DB_ESHOP_FEATITEMS, $this->idata, 'update');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=items&amp;status=isu");
			} else {
				// sort order
				if (!$this->idata['featitem_order']) $this->idata['featitem_order'] = dbresult(dbquery("SELECT MAX(featitem_order) FROM ".DB_ESHOP_FEATITEMS." WHERE featitem_cid='".$this->idata['featitem_cid']."'"), 0)+1;
				dbquery("UPDATE ".DB_ESHOP_FEATITEMS." SET featitem_order=featitem_order+1 WHERE featitem_cid='".$this->idata['featitem_cid']."' AND featitem_order>='".$this->idata['featitem_order']."'");
				dbquery_insert(DB_ESHOP_FEATITEMS, $this->idata, 'save');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=items&amp;status=isn");
			}
		}
	}

	/**
	 * @param $type
	 * @param $id
	 * @return bool|string
	 */
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

	/**
	 *
	 */
	public function banner_listing() {
		global $locale, $aidlink;
		add_to_jquery("
		$('.actionbar').hide();
		$('tr').hover(
			function(e) { $('#banner-'+ $(this).data('id') +'-actions').show(); },
			function(e) { $('#banner-'+ $(this).data('id') +'-actions').hide(); }
		);
		$('.qform').hide();
		$('.qedit').bind('click', function(e) {
			$.ajax({
				url: '".ADMIN."includes/eshop_banner.php',
				dataType: 'json',
				type: 'post',
				data: { q: $(this).data('id'), token: '".$aidlink."' },
				success: function(e) {
					console.log(e);
					$('#featbanner_aids').val(e.featbanner_aid);
					$('#featbanner_titles').val(e.featbanner_title);
					$('#featbanner_cids').select2('val', e.featbanner_cid);
					$('#featbanner_ids').select2('val', e.featbanner_id);
					$('#featbanner_url').val(e.featbanner_url);
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

		echo "<div class='m-t-10'>\n";
		echo "<table class='table table-responsive table-striped'>\n";
		echo "<tr>\n";
		echo "<th></th>\n";
		echo "<th class='col-xs-3 col-sm-3'>".$locale['ESHFEAT90']."</th>\n";
		echo "<th>".$locale['ESHFEAT91']."</th>\n";
		echo "<th>".$locale['ESHFEAT92']."</th>\n";
		echo "<th>".$locale['ESHFEAT93']."</th>\n";
		echo "<th>".$locale['ESHFEAT94']."</th>\n";
		echo "<th>".$locale['ESHFEAT95']."</th>\n";
		echo "</tr>\n";

		echo "<tr class='qform'>\n";
		echo "<td colspan='7'>\n";
		echo "<div class='list-group-item m-t-20 m-b-20'>\n";
		echo openform('quick_edit', 'quick_edit', 'post', FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=banner", array('downtime' => 1, 'notice' => 0));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-6'>\n";
		echo form_text($locale['ESHFEAT90'], 'featbanner_title', 'featbanner_titles', '', array('required'=>1, 'inline'=>1));
		echo form_select_tree($locale['ESHFEAT125'], 'featbanner_cid', 'featbanner_cids', '', array('inline'=>1, 'parent_value'=>$locale['ESHFEAT128']), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-6'>\n";
		openside('');
		echo "<strong>".$locale['ESHFEAT96']." <i class='fa fa-question-circle pointer' title='".$locale['ESHFEAT96a']."'></i></strong>\n";
		echo "<hr/>\n";
		echo form_select_tree($locale['ESHFEAT113a'], 'featbanner_cat', 'featbanner_cats', '', array('inline'=>1, 'parent_value'=>$locale['ESHFEAT128']), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
		$product_opts[0] = '--';
		$product_opts += self::get_productOpts();
		echo form_select($locale['ESHFEAT113'], 'featbanner_id', 'featbanner_ids', $product_opts, $this->data['featbanner_id'], array('inline'=>1));
		echo form_text($locale['ESHFEAT127'], 'featbanner_url', 'featbanner_urls', $this->data['featbanner_url'], array('inline'=>1, 'placeholder'=>fusion_get_settings('siteurl')));
		closeside();
		echo form_hidden('', 'featbanner_aid', 'featbanner_aids', '', array('writable' => 1));
		echo "</div>\n";
		echo "</div>\n";
		echo "<div class='m-t-10 m-b-10'>\n";
		echo form_button($locale['cancel'], 'cancel', 'cancel', 'cancel', array('class' => 'btn btn-default m-r-10',
			'type' => 'button'));
		echo form_button($locale['update'], 'banner_quicksave', 'banner_quicksave', 'save', array('class' => 'btn btn-primary'));
		echo "</div>\n";
		echo closeform();
		echo "</div>\n";
		echo "</td>\n";
		echo "</tr>\n";

		$result = dbquery("SELECT b.*,
					IF(b.featbanner_cid > 0, display.title, 0) as featbanner_display_title,
		 			IF(b.featbanner_cat > 0, category.title, IF(b.featbanner_id > 0, item.title, 0)) as featbanner_showcase_title,
		 			IF(b.featbanner_cat > 0, 1, IF(b.featbanner_id > 0, 2, 3)) as featbanner_type,
		 			IF(b.featbanner_cat > 0, b.featbanner_cat, IF(b.featbanner_id > 0, b.featbanner_id, b.featbanner_url)) as featbanner_item_id
					FROM ".DB_ESHOP_FEATBANNERS." b
					LEFT JOIN ".DB_ESHOP_CATS." display on (b.featbanner_cid=display.cid)
					LEFT JOIN ".DB_ESHOP_CATS." category on (b.featbanner_cat=category.cid)
					LEFT JOIN ".DB_ESHOP." item on (b.featbanner_id=item.id)
					ORDER BY featbanner_cid ASC, featbanner_order ASC
					LIMIT ".$_GET['rowstart'].", 25
					");
		$rows = dbrows($result);
		if ($rows > 0) {
			if (!defined('colorbox')) {
				define('colorbox', true);
				add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
				add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
				add_to_jquery("$('.colorbox').colorbox();");
			}
			echo "<tbody id='eshopitem-links' class='connected'>\n";
			while ($data = dbarray($result)) {
				echo "<tr id='listItem_".$data['featbanner_aid']."' data-id='".$data['featbanner_aid']."' class='list-result'>\n";
				echo "<td></td>\n";
				echo "<td>".$data['featbanner_title']."\n";
				echo "<div class='actionbar text-smaller' id='banner-".$data['featbanner_aid']."-actions'>
					<a href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=bannerform&amp;action=edit&amp;b_id=".$data['featbanner_aid']."'>".$locale['edit']."</a> |
					<a class='qedit pointer' data-id='".$data['featbanner_aid']."'>".$locale['qedit']."</a> |
					<a class='delete' href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=banner&amp;action=delete&amp;b_id=".$data['featbanner_aid']."' onclick=\"return confirm('".$locale['ESHFEAT129']."');\">".$locale['delete']."</a>
					</div>\n";
				echo "</td>\n";
				echo "<td><a title='".$data['featbanner_title']."' href='".FPHOTOROOT.$data['featbanner_banner']."' class='colorbox'><i class='text-dark fa fa-image fa-lg'></i></a></td>\n"; // load image via ajax
				echo "<td>".($data['featbanner_display_title'] == '0' ? $locale['ESHFEAT128'] : $data['featbanner_display_title'])."</td>\n"; //
				echo "<td>".($data['featbanner_showcase_title'] == '0' ? $locale['ESHFEAT108a'] : $data['featbanner_showcase_title'])."</td>\n";
				echo "<td>".self::get_bannerType($data['featbanner_type'], $data['featbanner_item_id'])."</td>\n";
				echo "<td>".$data['featbanner_order']."</td>\n";
				echo "</tr>\n";
			}
			echo "</tbody>\n";
		} else {
			echo "<tr><td colspan='7' class='text-center'><div class='alert alert-warning m-t-10'>".$locale['ESHFEAT117']."</div></td></tr>\n";
		}
		echo "</table>\n";
		if ($this->banner_max_rows > $rows) {
			echo "<div class='text-center'>".makePageNav($_GET['rowstart'], 15, $this->banner_max_rows, 3, FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=banner")."</div>\n";
		}
		echo "</div>\n";
	}

	/**
	 *
	 */
	public function item_listing() {
		global $locale, $aidlink;
		add_to_jquery("
		$('tr').hover(
			function(e) { $('#item-'+ $(this).data('id') +'-actions').show(); },
			function(e) { $('#item-'+ $(this).data('id') +'-actions').hide(); }
		);
		$('.qform2').hide();
		$('.qedit2').bind('click', function(e) {
			$.ajax({
				url: '".ADMIN."includes/eshop_featitem.php',
				dataType: 'json',
				type: 'post',
				data: { q: $(this).data('id'), token: '".$aidlink."' },
				success: function(e) {
					console.log(e);
					$('#featitem_ids').val(e.featitem_id);
					$('#featitem_titles').val(e.featitem_title);
					$('#featitem_descriptions').val(e.featitem_description);
					$('#featitem_cids').select2('val', e.featitem_cid);
				},
				error : function(e) {
					console.log(e);
				}
			});
			$('.qform2').show();
			$('.list-result2').hide();
		});
		$('#cancel2').bind('click', function(e) {
			$('.qform2').hide();
			$('.list-result2').show();
		});
		");

		echo "<div class='m-t-10'>\n";
		echo "<table class='table table-responsive table-striped'>\n";
		echo "<tr>\n";
		echo "<th></th>\n";
		echo "<th class='col-xs-3 col-sm-3'>".$locale['ESHFEAT90']."</th>\n";
		echo "<th>".$locale['ESHFEAT92']."</th>\n";
		echo "<th>".$locale['ESHFEAT93']."</th>\n";
		echo "<th>".$locale['ESHFEAT94']."</th>\n";
		echo "<th>".$locale['ESHFEAT95']."</th>\n";
		echo "</tr>\n";

		echo "<tr class='qform2'>\n";
		echo "<td colspan='7'>\n";
		echo "<div class='list-group-item m-t-20 m-b-20'>\n";
		echo openform('quick_edit2', 'quick_edit2', 'post', FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=items", array('downtime' => 1, 'notice' => 0));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-6'>\n";
		echo form_text($locale['ESHFEAT90a'], 'featitem_title', 'featitem_titles', '', array('required'=>1, 'inline'=>1));
		echo form_textarea($locale['ESHFEAT90b'], 'featitem_description', 'featitem_descriptions', '', array('required'=>1, 'autosize'=>1, 'inline'=>1));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-6'>\n";
		openside('');
		echo form_select_tree($locale['ESHFEAT125'], 'featitem_cid', 'featitem_cids', '', array('inline'=>1, 'parent_value'=>$locale['ESHFEAT128']), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
		closeside();
		echo form_hidden('', 'featitem_id', 'featitem_ids', '', array('writable' => 1));
		echo "</div>\n";
		echo "</div>\n";
		echo "<div class='m-t-10 m-b-10'>\n";
		echo form_button($locale['cancel'], 'cancel', 'cancel2', 'cancel', array('class' => 'btn btn-default m-r-10',
			'type' => 'button'));
		echo form_button($locale['update'], 'item_quicksave', 'item_quicksave', 'save', array('class' => 'btn btn-primary'));
		echo "</div>\n";
		echo closeform();
		echo "</div>\n";
		echo "</td>\n";
		echo "</tr>\n";
		$result = dbquery("SELECT b.*,
					IF(b.featitem_cid > 0, display.title, 0) as featitem_display_title,
					item.title as featitem_product_title
					FROM ".DB_ESHOP_FEATITEMS." b
					LEFT JOIN ".DB_ESHOP_CATS." display on (b.featitem_cid=display.cid)
					LEFT JOIN ".DB_ESHOP." item on (b.featitem_item=item.id)
					ORDER BY featitem_cid ASC, featitem_order ASC
					LIMIT ".$_GET['i_rowstart'].", 25
					");
		$rows = dbrows($result);
		if ($rows > 0) {
			echo "<tbody id='eshopitem-links' class='connected'>\n";
			while ($data = dbarray($result)) {
				echo "<tr id='listItem_".$data['featitem_id']."' data-id='".$data['featitem_id']."' class='list-result2'>\n";
				echo "<td></td>\n";
				echo "<td>".$data['featitem_title']."\n";
				echo "<div class='actionbar text-smaller' id='item-".$data['featitem_id']."-actions'>
					<a href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=itemform&amp;action=iedit&amp;i_id=".$data['featitem_id']."'>".$locale['edit']."</a> |
					<a class='qedit2 pointer' data-id='".$data['featitem_id']."'>".$locale['qedit']."</a> |
					<a class='delete' href='".FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=itemform&amp;action=idelete&amp;i_id=".$data['featitem_id']."' onclick=\"return confirm('".$locale['ESHFEAT129']."');\">".$locale['delete']."</a>
					</div>\n";
				echo "</td>\n";
				echo "<td>".($data['featitem_display_title'] == '0' ? $locale['ESHFEAT128'] : $data['featitem_display_title'])."</td>\n";
				echo "<td>".$data['featitem_product_title']."</td>\n";
				echo "<td><a href='".BASEDIR."eshop.php?product=".$data['featitem_item']."'>Link</a></td>\n";
				echo "<td>".$data['featitem_order']."</td>\n";
				echo "</tr>\n";
			}
			echo "</tbody>\n";
		} else {
			echo "<tr><td colspan='7' class='text-center'><div class='alert alert-warning m-t-10'>".$locale['ESHFEAT117']."</div></td></tr>\n";
		}
		echo "</table>\n";
		if ($this->item_max_rows > $rows) {
			echo "<div class='text-center'>".makePageNav($_GET['rowstart'], 15, $this->item_max_rows, 3, FUSION_SELF.$aidlink."&amp;a_page=featured&amp;section=items")."</div>\n";
		}
		echo "</div>\n";
	}

	/**
	 * @return array
	 */
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

	/**
	 *
	 */
	public function add_banner_form() {
		global $locale;
		echo "<div class='m-t-10'>\n";
		echo openform('banner_form', 'banner_form', 'post', $this->formaction, array('enctype'=>1, 'downtime' => 1));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-8'>\n";
		openside('');
		echo form_hidden('', 'featbanner_aid', 'featbanner_aid', $this->data['featbanner_aid']);
		echo form_text($locale['ESHFEAT90'], 'featbanner_title', 'featbanner_title', $this->data['featbanner_title'], array('required'=>1, 'inline'=>1));
		echo form_select_tree($locale['ESHFEAT125'], 'featbanner_cid', 'featbanner_cid', $this->data['featbanner_cid'], array('inline'=>1, 'parent_value'=>$locale['ESHFEAT128']), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
		closeside();
		openside('');
		echo form_hidden('', 'featbanner_hidden', 'featbanner_hidden', $this->data['featbanner_banner']);
		echo form_fileinput($locale['ESHFEAT115'], 'featbanner_banner', 'featbanner_banner', FPHOTOROOT, '', array(
			'type'=>'image',
			'required'=>1,
			'inline'=>1,
			'thumbnail' => 0,
			'thumbnail2' => 0,
			'max_byte' => 5*1000*1000,
			'max_width' => 3000,
			'max_height' => 3000,
		));
		echo "<span class='col-xs-12 col-sm-offset-3 text-smaller'>".$locale['ESHFEAT115a']."</span>\n";
		closeside();
		openside('');
		echo "<strong>".$locale['ESHFEAT96']." <i class='fa fa-question-circle pointer' title='".$locale['ESHFEAT96a']."'></i> <span class='required'>*</span></strong>\n";
		echo "<hr/>\n";
		echo form_select_tree($locale['ESHFEAT113a'], 'featbanner_cat', 'featbanner_cat', $this->data['featbanner_cat'], array('inline'=>1, 'parent_value'=>'--'), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
		$product_opts[0] = '--';
		$product_opts += self::get_productOpts();
		echo form_select($locale['ESHFEAT113'], 'featbanner_id', 'featbanner_id', $product_opts, $this->data['featbanner_id'], array('inline'=>1));
		echo form_text($locale['ESHFEAT127'], 'featbanner_url', 'featbanner_url', $this->data['featbanner_url'], array('inline'=>1, 'placeholder'=>fusion_get_settings('siteurl')));
		closeside();

		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-4'>\n";
		openside('');
		echo form_text($locale['ESHFEAT122'], 'featbanner_order', 'featbanner_order', $this->data['featbanner_order'], array('number'=>1));
		echo form_button($locale['save'], 'save_banner', 'save_banner1', $locale['save'], array('class'=>'btn-primary'));
		closeside();

		if ($this->data['featbanner_banner']) {
			echo "<div class='display-block' style='width:100%'>\n";
			echo thumbnail(FPHOTOROOT.$this->data['featbanner_banner'], '300px');
			echo "</div>\n";
		}


		echo "</div>\n";
		echo "</div>\n";
		echo form_button($locale['save'], 'save_banner', 'save_banner', $locale['save'], array('class'=>'btn-primary'));
		echo closeform();
		echo "</div>\n";
	}

	/**
	 *
	 */
	public function add_item_form() {
		global $locale;
		echo "<div class='m-t-10'>\n";
		echo openform('item_form', 'item_form', 'post', $this->formaction, array('downtime' => 1));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-8'>\n";
		openside('');
		echo form_hidden('', 'featitem_id', 'featitem_id', $this->idata['featitem_id']);
		echo form_select($locale['ESHFEAT113'], 'featitem_item', 'featitem_item',  self::get_productOpts(), $this->idata['featitem_item'], array('inline'=>1));
		echo form_text($locale['ESHFEAT90a'], 'featitem_title', 'featitem_title', $this->idata['featitem_title'], array('required'=>1, 'inline'=>1));
		echo form_textarea($locale['ESHFEAT90b'], 'featitem_description', 'featitem_description', $this->idata['featitem_description'], array('required'=>1, 'autosize'=>1, 'inline'=>1));
		echo form_select_tree($locale['ESHFEAT125'], 'featitem_cid', 'featitem_cid', $this->idata['featitem_cid'], array('inline'=>1, 'parent_value'=>$locale['ESHFEAT128']), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
		closeside();
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-4'>\n";
		openside('');
		echo form_text($locale['ESHFEAT122'], 'featitem_order', 'featitem_order', $this->idata['featitem_order'], array('number'=>1));
		echo form_button($locale['save'], 'save_item', 'save_item1', $locale['save'], array('class'=>'btn-primary'));
		closeside();
		echo "</div>\n";
		echo "</div>\n";
		echo form_button($locale['save'], 'save_item', 'save_item', $locale['save'], array('class'=>'btn-primary'));
		echo closeform();
		echo "</div>\n";
	}
}

$banner = new eShop_banners();
$banner->getMessage();
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $banner->verify_banner($_GET['b_id']) : 0;
$cedit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $banner->verify_item($_GET['i_id']) : 0;

$tab_title['title'][] = $locale['ESHFEAT108b'];
$tab_title['id'][] = 'items';
$tab_title['icon'][] = '';
$tab_title['title'][] = $locale['ESHFEAT108'];
$tab_title['id'][] = 'banner';
$tab_title['icon'][] = '';

$tab_title['title'][] =  $edit ? $locale['ESHFEAT109a'] : $locale['ESHFEAT109'];
$tab_title['id'][] = 'bannerform';
$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';

$tab_title['title'][] =  $edit ? $locale['ESHFEAT108d'] : $locale['ESHFEAT108c'];
$tab_title['id'][] = 'itemform';
$tab_title['icon'][] = $cedit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';


$tab_active = tab_active($tab_title, $edit ? 1 : 0, 1);

echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink."&amp;a_page=featured");
echo opentabbody($tab_title['title'][0], 'items', $tab_active, 1);
$banner->item_listing();
echo closetabbody();

echo opentabbody($tab_title['title'][1], 'banner', $tab_active, 1);
$banner->banner_listing();
echo closetabbody();

switch($_GET['section']) {
	case 'bannerform':
		echo opentabbody($tab_title['title'][2], 'bannerform', $tab_active, 1);
		$banner->add_banner_form();
		echo closetabbody();
		break;
	case 'itemform':
		echo opentabbody($tab_title['title'][2], 'itemform', $tab_active, 1);
		$banner->add_item_form();
		echo closetabbody();
		break;
}
closetable();