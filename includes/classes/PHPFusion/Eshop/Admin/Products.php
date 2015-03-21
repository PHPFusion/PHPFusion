<?php

namespace PHPFusion\Eshop\Admin;

use PHPFusion\Eshop\Eshop;
use PHPFusion\QuantumFields;

class Products {

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
		'gallery' => 1,
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
		'picture'=>'',
		'thumb'=>'',
		'thumb2' => '',
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

	private $upload_settings = array();
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
					$this->formaction = FUSION_SELF.$aidlink."&amp;action=edit&id=".$_GET['id']."".($settings['eshop_cats'] == "1" ? "&amp;section=itemform&amp;parent_id=".$_GET['parent_id']."" : "");
				}
				break;
			default :
				$this->formaction = FUSION_SELF.$aidlink."".($settings['eshop_cats'] == "1" && isset($_GET['parent_id']) ? "&amp;section=itemform&amp;parent_id=".$_GET['parent_id']."" : "");
		}

		$this->upload_settings = array(
			'thumbnail_folder'=>'thumbs',
			'thumbnail' => 1,
			'thumbnail_w' =>  fusion_get_settings('eshop_image_tw'),
			'thumbnail_h' =>  fusion_get_settings('eshop_image_th'),
			'thumbnail_suffix' =>'_t1',
			'thumbnail2'=>1,
			'thumbnail2_w' 	=>  fusion_get_settings('eshop_image_t2w'),
			'thumbnail2_h' 	=>  fusion_get_settings('eshop_image_t2h'),
			'thumbnail2_suffix' => '_t2',
			'delete_original' => 1,
			'max_width'		=>	fusion_get_settings('eshop_image_w'),
			'max_height'	=>	fusion_get_settings('eshop_image_h'),
			'max_byte'		=>	fusion_get_settings('eshop_image_b'),
			'multiple' => 0,
			'type'=>'image',
		);


		self::set_productdb();
		self::quick_save();
	}

	/**
	 * Determine whether eshop category has been turned on
	 * @return bool
	 */
	static function category_check() {
		return (boolean) fusion_get_settings('eshop_cats');
	}

	/**
	 * Checks whether category mode has been turned on
	 * @return bool|string
	 */
	static function category_count() {
		if (fusion_get_settings('eshop_cats') == 1) {
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
			$quick['title'] = isset($_POST['title']) ? form_sanitizer($_POST['title'], '', 'title', 1) : '';
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
		return dbcount("(id)", DB_ESHOP, "id='".intval($id)."'");
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
				echo admin_message($message);
			}
		}
	}

	/**
	 * MYSQL insert or update
	 */
	private function set_productdb() {
		global $aidlink, $userdata;

		// Delete Product Image
		define("SAFEMODE", @ini_get("safe_mode") ? TRUE : FALSE);
		if (isset($_POST['delete_image']) && isnum($_POST['delete_image'])) {
			if (self::verify_product_edit($_POST['delete_image'])) {
				@unlink(BASEDIR."eshop/pictures/".$this->data['picture']);
				@unlink(BASEDIR."eshop/pictures/thumbs/".$this->data['thumb']);
				@unlink(BASEDIR."eshop/pictures/thumbs/".$this->data['thumb2']);
				$this->data['picture'] = '';
				$this->data['thumb'] = '';
				$this->data['thumb2'] = '';
				dbquery_insert(DB_ESHOP, $this->data, 'update');
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;a_page=main&amp;section=itemform&amp;action=edit&amp;id=".$this->data['id']);
			}
		}

		// Update / Save Product
		if (isset($_POST['save_cat'])) {
			$this->data = array(
				'title' => 	form_sanitizer($_POST['title'], '', 'title'),
				'cid' 	=>	form_sanitizer($_POST['cid'], '0', 'cid'),
				'introtext' =>	form_sanitizer($_POST['introtext'], '', 'introtext'),
				'description' =>	addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['description'])),
				'anything1n' =>	addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['anything1n'])),
				'anything2n' =>	addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['anything2n'])),
				'anything3n' =>	addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['anything3n'])),
				'weight' =>	str_replace(',', '.', form_sanitizer($_POST['weight'], '', 'weight')),
				'price' =>	str_replace(',', '.', form_sanitizer($_POST['price'], '', 'price')),
				'xprice' =>	str_replace(',', '.', form_sanitizer($_POST['xprice'], '', 'xprice')),
				'stock' =>	str_replace(',', '.', form_sanitizer($_POST['stock'], '', 'stock')),
				'version' =>	str_replace(',', '.', form_sanitizer($_POST['version'], '', 'version')),
				'status' =>	form_sanitizer($_POST['status'], '', 'status'),
				'active' =>	form_sanitizer($_POST['active'], '', 'active'),
				'delivery' =>	form_sanitizer($_POST['delivery'], '0', 'delivery'),
				'demo' =>	form_sanitizer($_POST['demo'], '', 'demo'),
				'cart_on' =>	form_sanitizer($_POST['cart_on'], '0', 'cart_on'),
				'buynow' =>	form_sanitizer($_POST['buynow'], '', 'buynow'),
				'rpage' =>	form_sanitizer($_POST['rpage'], '', 'rpage'),
				'dynf' =>	form_sanitizer($_POST['dynf'], '', 'dynf'),
				'qty' =>	form_sanitizer($_POST['qty'], '0', 'dynf'),
				'sellcount' =>	form_sanitizer($_POST['sellcount'], '0', 'sellcount'),
				'artno' =>	form_sanitizer($_POST['artno'], '', 'artno'),
				'sartno' =>	form_sanitizer($_POST['sartno'], '', 'sartno'),
				'instock' =>	form_sanitizer($_POST['instock'], '', 'instock'),
				'iorder' =>	form_sanitizer($_POST['iorder'], '0', 'iorder'),
				'dmulti' =>	form_sanitizer($_POST['dmulti'], '1', 'dmulti'),
				'access' =>	form_sanitizer($_POST['access'], '0', 'access'),
				'keywords' =>	form_sanitizer($_POST['keywords'], '', 'keywords'),
				'product_languages' =>	form_sanitizer($_POST['product_languages'], '', 'product_languages'),
				'dateadded' =>	isset($_POST['dateadded']) ? form_sanitizer($_POST['dateadded'], time(), 'dateadded') : time(),
				'cupons' => isset($_POST['cupons']) ? 1 : 0,
				'campaign' => isset($_POST['campaign']) ? 1 : 0,
				'ratings' => isset($_POST['ratings']) ? 1 : 0,
				'comments' => isset($_POST['comments']) ? 1 : 0,
				'linebreaks' => isset($_POST['linebreaks']) ? 1 : 0,
			);

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

			// retain old update records
			$this->data['gallery'] = isset($_POST['gallery']) ? form_sanitizer($_POST['gallery'], '0', 'gallery') : 0;

			$this->data['picture'] = isset($_POST['image']) ? form_sanitizer($_POST['picture'], '', 'picture') : '';
			$this->data['thumb'] = isset($_POST['thumb']) ? form_sanitizer($_POST['thumb'], '', 'thumb') : '';
			$this->data['thumb2'] = isset($_POST['thumb2']) ? form_sanitizer($_POST['thumb2'], '', 'thumb2') : '';

			// conditions is that gallery is not off,
			// image file is uploaded -- override gallery off --- will always increment new gallery.
			if (is_uploaded_file($_FILES['imagefile']['tmp_name'])) {
				/**
				 * Galleries Uploading - used the class to reuse some of the functions only.
				 * Uploading is still defined custom by the codes in this page.
				 */
				$gallery = new \PHPFusion\Gallery\Admin();
				$gallery->setUploadSettings($this->upload_settings);
				$gallery->setImageUploadDir(BASEDIR."eshop/pictures/");
				$gallery->setPhotoCatDb(DB_ESHOP_ALBUMS);
				$gallery->setPhotoDb(DB_ESHOP_PHOTOS);
				$gallery->setGalleryRights('ESHP');

				$album_data = array(
					'album_id' => $this->data['gallery'],
					'album_title' => $this->data['title'],
					'album_description' => '',
					'album_user' => $userdata['user_id'],
					'album_access' => $this->data['access'],
					'album_order' => 0,
					'album_datestamp' => time(),
					'album_language' => $this->data['product_languages'],
				);

				if (!$album_data['album_order']) $album_data['album_order'] = dbresult(dbquery("SELECT MAX(album_order) FROM ".DB_ESHOP_ALBUMS." WHERE album_language='".LANGUAGE."'"), 0)+1;
				// point of injection of altered if you know the album_id -- possible bug: high volume sites will not be able to book the id unless record is made?
				if (!$album_data['album_id']) {
					$next_album_id = dbnextid(DB_ESHOP_ALBUMS);
					$gallery->set_modified_upload_path($next_album_id, 'imagefile', 1);
				} else {
					$gallery->set_modified_upload_path($album_data['album_id'], 'imagefile', 1);
				}

				$upload_result = form_sanitizer($_FILES['imagefile'], '', 'imagefile');
				/** Note: Ensure your hidden field return does not bear the same input name as the fileinput name else form sanitizer will not sanitize properely as both bears same identifier */
				$album_data['album_thumb'] = $this->data['thumb'];

				if (isset($upload_result['error']) && $upload_result['error'] !== '0') {
					// upload success
					$album_data['album_thumb'] = $upload_result['thumb1_name'];
					// only exist in new upload
					$image_name = $upload_result['image_name'];
					$thumb1_name = $upload_result['thumb1_name'];
					$thumb2_name = $upload_result['thumb2_name'];

					// override with new data
					$this->data['picture'] = $image_name;
					$this->data['thumb'] = $thumb1_name;
					$this->data['thumb2'] = $thumb2_name;
				}

				// replace photo if different
				$album_history = $gallery->get_album($album_data['album_id']);
				$photo_data = array(
					'photo_id' => 0,
					'album_id' => 0,
					'photo_title' => $this->data['title'],
					'photo_description'=> '',
					'photo_keywords' => $this->data['keywords'],
					'photo_datestamp' => time(),
					'photo_views' => 0,
					'photo_order' => 0,
					'photo_filename' => '',
					'photo_thumb1' => '',
					'photo_thumb2' => '',
					'photo_allow_comments' => $this->data['comments'],
					'photo_allow_ratings' => $this->data['ratings']
				);
				if (!empty($album_history)) {
					$thumb_photo = dbquery("SELECT photo_id, album_id, photo_filename, photo_thumb1, photo_thumb2, photo_views, photo_order, photo_allow_comments, photo_allow_ratings FROM ".DB_ESHOP_PHOTOS." WHERE photo_thumb1='".$album_history['album_thumb']."'");
					if (dbrows($thumb_photo) > 0) {
						$photo_data = dbarray($thumb_photo); // use back old records
					}
					// ok. now we need to delete the old picture set if changed
					if ($album_data['album_thumb'] !== $album_history['album_thumb']) {
						@unlink(BASEDIR.'eshop/pictures/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$photo_data['photo_thumb1']);
						@unlink(BASEDIR.'eshop/pictures/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$photo_data['photo_thumb2']);
						@unlink(BASEDIR.'eshop/pictures/'.$photo_data['photo_filename']);
					}
				}
				// override new.
				$photo_data = array(
					'photo_id' => $photo_data['photo_id'],
					'album_id' => $photo_data['album_id'],
					'photo_title' => $album_data['album_title'],
					'photo_description' => $album_data['album_description'],
					'photo_keywords' => $album_data['album_title'],
					'photo_filename' => isset($image_name) ? $image_name : $photo_data['photo_filename'],
					'photo_thumb1' => isset($thumb1_name) ? $thumb1_name : $photo_data['photo_thumb1'],
					'photo_thumb2' => isset($thumb2_name) ? $thumb2_name : $photo_data['photo_thumb2'],
					'photo_datestamp' => $album_data['album_datestamp'],
					'photo_user' => $userdata['user_id'],
					'photo_views' => $photo_data['photo_views'],
					'photo_order' => $photo_data['photo_order'],
					'photo_allow_comments' => $photo_data['photo_allow_comments'],
					'photo_allow_ratings' => $photo_data['photo_allow_ratings']
				);

				if ($album_data['album_id'] && $gallery->validate_album($album_data['album_id'])) {
					$result = dbquery_order(DB_ESHOP_ALBUMS, $album_data['album_order'], 'album_order', $album_data['album_id'], 'album_id', FALSE, FALSE, 1, 'album_language', 'update');
					if ($result) {
						dbquery_insert(DB_ESHOP_ALBUMS, $album_data, 'update');
						if (!empty($photo_data) && $gallery->validate_photo($photo_data['photo_id'])) {
							dbquery_insert(DB_ESHOP_PHOTOS, $photo_data, 'update');
						}
					}
				} else {
					// new saves
					$result = dbquery_order(DB_ESHOP_ALBUMS, $album_data['album_order'], 'album_order', FALSE, FALSE, FALSE, FALSE, 1, 'album_language', 'save');
					if ($result) {
						dbquery_insert(DB_ESHOP_ALBUMS, $album_data, 'save');
						$album_data['album_id'] = dblastid();
						if (!empty($photo_data) && $album_data['album_id']) {
							if (!$photo_data['photo_order']) $photo_data['photo_order'] = $photo_data['photo_order'] = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." WHERE album_id='".intval($album_data['album_id'])."'"), 0)+1;
							$photo_data['album_id'] = $album_data['album_id'];
							$result = dbquery_order(DB_ESHOP_PHOTOS, $photo_data['photo_order'], 'photo_order', FALSE, FALSE, $photo_data['album_id'], 'album_id', FALSE, FALSE, 'save');
							if ($result) {
								dbquery_insert(DB_ESHOP_PHOTOS, $photo_data, 'save');
							}
						}
					}
				}
			}

			if (self::verify_product_edit($this->data['id'])) {
				$old_data = dbarray(dbquery("SELECT cid, iorder, dateadded FROM ".DB_ESHOP." WHERE id='".$this->data['id']."'"));
				$this->data['dateadded'] = $old_data['dateadded']; // static time
				// at anytime, if order is 0, new order means max order
				if (!$this->data['iorder']) $this->data['iorder'] = dbresult(dbquery("SELECT MAX(iorder) FROM ".DB_ESHOP." WHERE cid='".$this->data['cid']."'"), 0)+1;
				//	$result = dbquery_order(DB_ESHOP, $this->data['iorder'], 'iorder', $this->data['id'], 'id', $this->data['cid'], 'cid', $this->data['product_languages'], 'product_languages', 'update');
				if ($old_data['cid'] !== $this->data['cid']) { // not the same category
					// refresh ex-category ordering
					dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder-1 WHERE cid='".$old_data['cid']."' AND iorder > '".$old_data['iorder']."' AND language='".$this->data['product_languages']."'"); // -1 to all previous category.
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
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;parent_id=".$_GET['parentid']."&amp;status=su");
			} else {
				if (!$this->data['iorder']) $this->data['iorder'] = dbresult(dbquery("SELECT MAX(iorder) FROM ".DB_ESHOP." WHERE cid='".$this->data['cid']."'"), 0)+1;
				$result = dbquery("UPDATE ".DB_ESHOP." SET iorder=iorder+1 WHERE cid = '".$this->data['cid']."' AND iorder>='".$this->data['iorder']."'");
				dbquery_insert(DB_ESHOP, $this->data, 'save', array('keep_session'=>1));
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;parent_id=".$_GET['parentid']."&amp;status=sn");
			}
		}
	}

	/**
	 * Data callback
	 * @return array|bool
	 */
	public static function products_data() {
		$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE id='".$_GET['id']."'");
		if (dbrows($result)>0) {
			return dbarray($result);
		}
	}

	/**
	 * The Form Template
	 */
	public function product_form() {
		global $locale, $settings;
		$enabled_languages = fusion_get_enabled_languages();
		$this->data['product_languages'] = is_array($this->data['product_languages']) ? $this->data['product_languages'] : $enabled_languages;
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
		// confirmation dialog
		fusion_confirm_exit();
		// check function
		add_to_jquery('
		function doCheck(cid) {
			var cid = +cid;
			var data = "cid="+ cid;
			 $.ajax({
			   type: "GET",
			   url: "'.ADMIN.'eshop/getcolorname.php",
			   data: data,
			   beforeSend: function(result) {
			   $("#colors"+cid).html("Loading color.."); },
			   success: function(result){
					$("#colors"+cid).empty();
					$("#colors"+cid).show();
					$("#cp-"+cid).remove();
					$("#colors"+cid).append(result);
				},timeout: 235000,
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
			document.getElementById("sList").innerHTML += "<div class=\"list-group-item display-inline-block m-2\"><label class=\"sList\"><input checked =\"checked\" class=\"sList-chk\" name=\"sList[]\" type=\"checkbox\" value=\""+sval+"\"> "+sval+"</div></label></div>";
		}

		//add item when selected in list
		$("#dyncList-append-btn").click(function () {
			var sitem = $("#dyncList").val();
			//If value is empty nothing should happend
			if(sitem !== "") {
				if ($.inArray(sitem,dyncArray) == -1) {
				$("#dyncList").val("");
				dyncArray.push(sitem);
				document.getElementById("sList").innerHTML += "<div class=\"list-group-item display-inline-block m-2\"><label class=\"sList\"><input checked =\"checked\" class=\"sList-chk\" name=\"sList[]\" type=\"checkbox\" value=\""+sitem+"\"> "+sitem+"</label></div>";
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
		document.getElementById("cList").innerHTML += "<div class=\"display-block m-2\"><label class=\"cList\"><input checked =\"checked\" class=\"cList-chk\" name=\"cList[]\" type=\"checkbox\" value=\""+ val +"\"> <span class=\"cListdiv\" id=\"colors"+val+"\"></span></label></div>";
		doCheck(val);
		}
		//remove color when clicked. hmm..
		$(document).on("change", ".cList-chk", function () { if ($(this).attr("checked")) { return;  } else { $(this).parent(".cList").remove(); } });
		');

		echo "<div class='m-t-20'>\n";
		echo openform('productform', 'productform', 'post', $this->formaction, array('enctype'=>1, 'downtime' => 1));

		// primary information
		echo "<div class='row m-t-20'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-9'>\n";
		openside('');
		// now defaulted - always use cart
		echo form_hidden($locale['ESHPPRO149'], 'cart_on', 'cart_on', 1, array('placeholder'=>$locale['ESHPPRO150'], 'width'=>'100%'));
		echo form_text($locale['ESHPPRO104'], 'title', 'title', $this->data['title'], array('required'=>1, 'inline'=>1));
		echo form_select($locale['ESHPPRO192'], 'keywords', 'keywords', array(), $this->data['keywords'], array('width'=>'100%', 'tags'=>1, 'multiple'=>1, 'inline'=>1));
		//'placeholder'=>$locale['ESHPPRO136']
		echo form_text($locale['ESHP013'], 'demo', 'demo', $this->data['demo'], array('inline'=>1, 'placeholder'=>'http://'));

		$this->upload_settings += array('inline'=>1);
		echo form_fileinput($locale['ESHPPRO109'], 'imagefile', 'imagefile', BASEDIR."eshop/pictures/", '', $this->upload_settings);

		//echo "<span class='text-smaller display-inline-block m-b-10'>".$locale['ESHPPRO110']."</span>\n";
		echo form_hidden('', 'picture', 'picture', $this->data['picture']);
		echo form_hidden('', 'thumb', 'thumb', $this->data['thumb']);
		echo form_hidden('', 'thumb2', 'thumb2', $this->data['thumb2']);

		echo form_text($locale['ESHPPRO107'], 'artno', 'artno', $this->data['artno'], array('inline'=>1, 'placeholder'=>$locale['ESHPPRO199']));
		echo form_text($locale['ESHPPRO108'], 'sartno', 'sartno', $this->data['sartno'], array('inline'=>1, 'placeholder'=>$locale['ESHPPRO199']));
		echo form_text($locale['ESHPPRO133'], 'version', 'version', $this->data['version'], array('inline'=>1, 'width'=>'250px', 'placeholder'=>$locale['ESHPPRO134']));
		echo form_text($locale['ESHPPRO114'], 'weight', 'weight', $this->data['weight'], array('inline'=>1, 'number'=>1, 'width'=>'250px', 'placeholder'=> fusion_get_settings('eshop_weightscale')));
		echo form_text($locale['ESHPPRO122'], 'iorder', 'iorder', $this->data['iorder'], array('inline'=>1, 'number'=>1, 'width'=>'100px', 'placeholder'=>$locale['ESHPPRO123']));
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
		closeside();
		/**
		 * Pricing
		 */
		echo form_para($locale['ESHPPRO194b'], 'cst', 'cst', array('placeholder'=>$locale['ESHPPRO117']));

		$pricing['title'][] = 'Product Pricing';
		$pricing['id'][] = 'ppp';
		$pricing['icon'][] = 'fa fa-gift fa-lg m-r-10';

		$pricing['title'][] = 'Coupons and Campaign';
		$pricing['id'][] = 'ppc';
		$pricing['icon'][] = 'fa fa-gift fa-lg m-r-10';

		$_active = tab_active($pricing, 0);

		echo opentab($pricing, $_active, 'price_tabs');
		echo opentabbody($pricing['title'][0], $pricing['id'][0], $_active);
		echo "<div class='m-t-10'>\n";
		openside('');
		echo form_text($locale['ESHPPRO111'], 'price', 'price', $this->data['price'], array('number'=>1, 'required'=>1, 'width'=>'250px', 'inline'=>1, 'placeholder'=>$settings['eshop_currency']));
		//'placeholder'=>$locale['ESHPPRO144'],
		echo form_text($locale['ESHPPRO143'], 'delivery', 'delivery', $this->data['delivery'], array( 'inline'=>1, 'width'=>'250px', 'number'=>1, 'placeholder'=>$locale['ESHPPRO200']));
		//echo "<span class='pull-right text-smaller m-b-10 mid-opacity'>".$locale['ESHPPRO153']."</span>\n";
		echo form_text($locale['ESHPPRO152'], 'dmulti', 'dmulti', $this->data['dmulti'], array('inline'=>1, 'placeholder'=>$locale['ESHP019'], 'width'=>'250px'));
		closeside();
		echo "</div>\n";
		echo closetabbody();

		echo opentabbody($pricing['title'][1], $pricing['id'][1], $_active);
		echo "<div class='m-t-10'>\n";
		openside('');
		echo form_select($locale['ESHPPRO182'], 'cupons', 'cupons', array($locale['no'], $locale['yes']), $this->data['cupons'], array('inline'=>1));
		echo form_select($locale['ESHPPRO184'], 'campaign', 'campaign',  array($locale['no'], $locale['yes']), $this->data['campaign'], array('inline'=>1, 'placeholder'=>$locale['ESHPPRO185']));
		echo form_text($locale['ESHPPRO112'], 'xprice', 'xprice', $this->data['xprice'], array('number'=>1, 'inline'=>1, 'width'=>'250px', 'placeholder'=>$settings['eshop_currency']));
		closeside();
		echo "</div>\n";
		echo closetabbody();
		echo closetab();

		/**
		 * Attributes
		 */
		echo form_para($locale['ESHPPRO194a'], 'cst2', 'cst2', array('placeholder'=>$locale['ESHPPRO117']));

		openside('');
		echo form_text($locale['ESHPPRO194'], 'dynf', 'dynf', $this->data['dynf'], array('placeholder'=>$locale['ESHPPRO197'], 'inline'=>1));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3'>\n";
		echo "<label for='dyncList' class='label-control'>\n".$locale['ESHPPRO196']."</label>\n";
		echo "</div>\n<div class='col-xs-12 col-sm-9' style='padding-left:6px;'>\n";
		echo "<div class='row'>\n";
		echo "<div class='col-xs-6 col-sm-6'>\n";
		echo form_text('', 'dyncList', 'dyncList', '', array('append_button'=>1, 'append_type'=>'button', 'append_value'=>$locale['ESHPPRO116']));
		echo "</div>\n</div>\n";
		echo form_para($locale['ESHPPRO118'],'118', '118');
		echo "<div id='sList'>\n";
		echo "</div>\n";
		echo "</div></div>\n";
		closeside();
		openside('');
		add_to_jquery("
		$('.colorpick').tooltip();
		$('.colorpick').bind('click', function(e) {
			var hex = $(this).data('hex');
			var val = $(this).data('value');
			var title = $(this).data('title');
			if ($.inArray(val,colorArray) == -1) {
				colorArray.push(val);
				$(this).remove();
				$('#cList').append('<div class=\'display-block m-2\'><label class=\'cList\'><input checked =\'checked\' class=\'cList-chk\' name=\'cList[]\' type=\'checkbox\' value=\''+val+'\'> '+ title +'</label></div>');
			}
		});

		");
		echo "<div class='form-group'>\n";
		echo "<label class='col-xs-12 col-sm-3'>Choose Product Colors</label>\n";
		echo "<div class='col-xs-12 col-sm-9'>\n";
		echo "<div class='btn-group'>\n";
		echo "<button title='Color' class='dropdown-toggle btn btn-default m-b-10 button' data-toggle='dropdown'><i class='fa fa-eyedropper m-r-10'></i> Add Color <span class='caret'></span></button>\n";
		echo "<ul class='dropdown-menu' role='text-color' style='width:300px;'>\n";
		echo "<li>\n";
		echo "<div class='display-block p-l-10 p-r-5 p-t-5 p-b-0' style='width:100%'>\n";
		$color_list = Eshop::get_iColor();
		foreach($color_list as $value => $attributes) {
			echo "<a id='cp-".$value."' class='pointer display-inline-block colorpick' title='".$attributes['title']."' data-hex='".$attributes['hex']."' data-value='".$value."' data-title='".$attributes['title']."' style='width:23px; height:23px; background-color:".$attributes['hex']."; margin:2px; text-decoration:none;'>&nbsp;</a>\n";
		}
		echo "</div>\n";
		echo "</li>\n";
		echo "</ul>\n";
		echo "</div>\n";
		echo "<div id='cList' style='display: block; margin: 10px -2px; padding: 0;'></div>\n";
		echo "</div>\n</div>\n";
		closeside();


		openside('');
		echo form_select($locale['ESHPPRO190'], 'linebreaks', 'linebreaks', array($locale['no'], $locale['yes']), $this->data['linebreaks'], array('inline'=>1));
		if (fusion_get_settings('eshop_pretext')) {
			echo form_textarea($locale['ESHPPRO160'], 'introtext', 'introtext', $this->data['introtext'], array('placeholder'=>$locale['ESHPPRO161'], 'autosize'=>1));
		} else {
			echo form_hidden('', 'introtext', 'introtext', $this->data['introtext']);
		}
		echo form_textarea($locale['ESHPPRO162'], 'description', 'description', $this->data['description'], array('inline'=>1,'autosize'=>1));
		//'placeholder'=>$locale['ESHPPRO163'],
		echo form_text($locale['ESHPPRO201']." 1", 'anything1n', 'anything1n', $this->data['anything1n'], array('inline'=>1, 'placeholder'=>$locale['ESHPPRO198']));
		echo form_textarea('', 'anything1', 'anything1', $this->data['anything1'], array('autosize'=>1));
		echo form_text($locale['ESHPPRO201']." 2", 'anything2n', 'anything2n', $this->data['anything2n'], array('inline'=>1, 'placeholder'=>$locale['ESHPPRO198']));
		echo form_textarea('', 'anything2', 'anything2', $this->data['anything2'], array('autosize'=>1));
		echo form_text($locale['ESHPPRO201']." 3", 'anything3n', 'anything3n', $this->data['anything3n'], array('inline'=>1, 'placeholder'=>$locale['ESHPPRO198']));
		echo form_textarea('', 'anything3', 'anything3', $this->data['anything3'], array('autosize'=>1));
		closeside();

		// end of column 1
		echo "</div><div class='col-xs-12 col-sm-12 col-md-3'>\n";
		// column 2
		if (!defined('SAFEMODE')) define("SAFEMODE", @ini_get("safe_mode") ? TRUE : FALSE);
		$img_path = !SAFEMODE ? BASEDIR."eshop/pictures/album_".$this->data['gallery']."/thumbs/".$this->data['thumb'] : BASEDIR."eshop/pictures/thumbs/".$this->data['thumb'];
		if ($this->data['thumb'] && file_exists($img_path)) {
			echo "<div class='display-block text-center list-group-item m-b-10'>\n";
			echo "<img class='img-responsive' src='".$img_path."' />";
			echo "</div>\n";
		}

		echo form_hidden($locale['ESHPPRO124'], 'sellcount', 'sellcount', $this->data['sellcount'], array('deactivate'=>1, 'placeholder'=>$locale['ESHPPRO125']));
		openside('');
		if (self::category_check()) {
			echo form_select_tree($locale['ESHPPRO105'], 'cid', 'cid', $this->data['cid'], array('no_root'=>1, 'width'=>'100%', 'placeholder'=>$locale['ESHP016']), DB_ESHOP_CATS, 'title', 'cid', 'parentid');
		} else {
			echo $locale['ESHPPRO105']." : ".$locale['ESHPPRO106'];
			echo form_hidden('', 'cid', 'cid', 0);
		}
		echo form_select($locale['ESHPPRO147'], 'active', 'active', array('0'=>$locale['no'], '1'=>$locale['yes']), $this->data['active'], array('placeholder'=>$locale['ESHPPRO148'], 'width'=>'100%'));
		echo form_select($locale['ESHPPRO145'], 'status', 'status', array('0'=>$locale['no'], '1'=>$locale['yes']), $this->data['status'], array('placeholder'=>$locale['ESHPPRO146'], 'width'=>'100%'));
		echo form_select($locale['ESHPCATS109'], 'access', 'access', self::getVisibilityOpts(), $this->data['access'], array('placeholder'=>$locale['ESHPPRO159'],'width'=>'100%'));
		echo form_select($locale['ESHPPRO137'], 'stock', 'stock', array('1'=>$locale['yes'],'2'=>$locale['no']), $this->data['stock'], array('placeholder'=> $locale['ESHPPRO140'], 'width'=>'100%'));
		echo form_text($locale['ESHPPRO141'], 'instock', 'instock', $this->data['instock'], array('placeholder'=>$locale['ESHP019'], 'number'=>1));
		echo form_button($locale['save_changes'], 'save_cat', 'save_cat2', $locale['save'], array('class'=>'btn-success', 'icon'=>'fa fa-check-square-o'));
		closeside();

		// get albumList
		$list[0] = $locale['off'];
		$result = dbquery("SELECT * FROM ".DB_ESHOP_ALBUMS." ORDER BY album_order ASC");
		if (dbrows($result) > 0) {
			while ($data = dbarray($result)) {
				$list[$data['album_id']] = $data['album_title'];
			}
		}
		echo form_select($locale['ESHPPRO126'], 'gallery', 'gallery', $list, $this->data['gallery'], array('width'=>'100%', 'placeholder'=>$locale['ESHPPRO129']));

		echo form_select($locale['ESHPPRO154'], 'buynow', 'buynow', array('0'=>$locale['no'], '1'=> $locale['yes']), $this->data['buynow'], array('placeholder'=>$locale['ESHPPRO155'], 'width'=>'100%'));
		$page_array = array();
		$callback_dir = makefilelist(BASEDIR."eshop/purchasescripts/", ".|..|index.php", TRUE, "files");
		foreach($callback_dir as $page) {
			$page_array[$page] = $page;
		}
		echo form_select($locale['ESHPPRO156'], 'rpage', 'rpage', $page_array, $this->data['rpage'], array('placeholder'=>$locale['ESHPPRO158'], 'width'=>'100%'));

		echo form_checkbox($locale['ESHPPRO193'], 'qty', 'qty', $this->data['qty'], array('placeholder'=>$locale['ESHPPRO151'], 'width'=>'100%'));
		echo form_checkbox($locale['ESHPPRO188'], 'ratings', 'ratings', $this->data['ratings'], array('placeholder'=>$locale['ESHPPRO188']));
		echo form_checkbox($locale['ESHPPRO189'], 'comments', 'comments', $this->data['comments'], array('placeholder'=>$locale['ESHPPRO189']));
		echo "</div></div>\n";

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
		echo openform('get_filter', 'get_filters', 'post', clean_request('', array('aid', 'status', 'section')), array('notice'=>0, 'downtime'=>1));
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
		echo "<table class='table table-responsive table-striped'>\n";
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
		echo openform('quick_edit', 'quick_edit', 'post', FUSION_SELF.$aidlink."&amp;a_page=main", array('downtime' => 1, 'notice' => 0));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-4'>\n";
		echo QuantumFields::quantum_multilocale_fields($locale['ESHPPRO172'], 'title', 'titles', '', array('required'=>1, 'inline'=>1));
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
			".(self::category_check() ? "LEFT JOIN ".DB_ESHOP_CATS." cat on (cat.cid=i.cid)" : '')."
			".$this->filter_Sql."
			ORDER BY cat.cat_order ASC, i.iorder ASC LIMIT 0, 25
		");
		$rows = dbrows($result);
		if ($rows>0) {
			echo "<tbody id='eshopitem-links' class='connected'>\n";
			$i = 0;
			while ($data = dbarray($result)) {
				echo "<tr id='listItem_".$data['id']."' data-id='".$data['id']."' class='list-result'>\n";
				echo "<td></td>\n";
				echo "<td class='col-xs-3 col-sm-3 col-md-3 col-lg-3'>\n";
				echo "<a class='text-dark' title='".$locale['edit']."' href='".FUSION_SELF.$aidlink."&amp;a_page=main&amp;section=itemform&amp;action=edit&amp;id=".$data['id']."'>
				".QuantumFields::parse_label($data['title'])."</a>";
				echo "<div class='actionbar text-smaller' id='product-".$data['id']."-actions'>
				<a href='".FUSION_SELF.$aidlink."&amp;a_page=main&amp;section=itemform&amp;action=edit&amp;id=".$data['id']."'>".$locale['edit']."</a> |
				<a class='qedit pointer' data-id='".$data['id']."'>".$locale['qedit']."</a> |
				<a class='delete' href='".FUSION_SELF.$aidlink."&amp;a_page=main&amp;action=delete&amp;id=".$data['id']."' onclick=\"return confirm('".$locale['ESHPCATS134']."');\">".$locale['delete']."</a>
				";
				echo "</td>\n";
				echo "<td>".($settings['eshop_cats'] ? QuantumFields::parse_label($data['cat_title']) : $locale['global_080'])."</td>\n";
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
			if (self::category_check() && !self::category_count()) {
				$cat_link = clean_request("&a_page=categories", array('a_page'), false);
				echo "<tr>\n<td class='text-center' colspan='11'><div class='well'>".sprintf($locale['ESHPPRO102'], $cat_link)."</div></td>\n</tr>\n";
			} else {
				echo "<tr>\n<td class='text-center' colspan='11'><div class='alert alert-warning m-t-20'>".$locale['ESHPPRO177']."</div></td>\n</tr>\n";
			}
		}
		echo "</table>\n";
		if ($this->max_rowstart > $rows) {
			echo "<div class='text-center'>".makePageNav($_GET['rowstart'], 15, $this->max_rowstart, 3, FUSION_SELF.$aidlink."&amp;status=".$_GET['status'].($settings['eshop_cats'] ? "&amp;parent_id=".$_GET['parent_id'] : ''))."</div>\n";
		}
		echo "</div>\n";
	}
}