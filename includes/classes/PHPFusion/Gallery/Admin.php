<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Photo/Admin.php
| Author: Frederick MC Chan (Photo Gallery Admin UI)
| Implementing my idea of centralized Interface for Gallery of all sorts
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Gallery;
class Admin {
	private $image_upload_dir = '';
	private $photo_db = '';
	private $photo_cat_db = '';
	private $upload_settings = array(
		'thumbnail_folder' => 'thumbs',
		'thumbnail' => 1,
		'thumbnail_w' => 150,
		'thumbnail_h' => 150,
		'thumbnail_suffix' => '_t1',
		'thumbnail2' => 1,
		'thumbnail2_w' => 400,
		'thumbnail2_h' => 400,
		'thumbnail2_suffix' => '_t2',
		'delete_original' => 1,
		'max_width' => 1800,
		'max_height' => 1600,
		'max_byte' => 1500000, // 1.5 million bytes is 1.5mb
		'multiple' => 0,);
	private $enable_comments = FALSE;
	private $enable_ratings = FALSE;
	private $allow_comments = FALSE;
	private $allow_ratings = FALSE;
	private $gallery_rights = '';
	private $enable_album = TRUE;
	private $albums_per_page = 30;
	private $gallery_rows = 6;
	private $album_id = 0;
	private $photo_id = 0;
	private $rowstart = 0;
	private $action = '';
	private $album_max_order = 0;
	private $photo_max_order = 0;
	/**
	 * For best view: to recommend thumbnail_1 size set at 260px min.
	 * @var array
	 */
	private $album_data = array('album_id' => 0,
		'album_title' => '',
		'album_description' => '',
		'album_thumb' => '',
		'album_user' => 0,
		'album_access' => 0,
		'album_order' => 0,
		'album_datestamp' => 0,
		'album_language' => '',);
	private $photo_data = array('photo_id' => 0,
		'album_id' => 0,
		'photo_title' => '',
		'photo_description' => '',
		'photo_keywords' => '',
		'photo_filename' => '',
		'photo_thumb1' => '',
		'photo_thumb2' => '',
		'photo_datestamp' => '',
		'photo_user' => 0,
		'photo_views' => 0,
		'photo_order' => 0,
		'photo_allow_comments' => 0,
		'photo_allow_ratings' => 0,);
	private $gallery_data = array(); // list out data
	/**
	 * Install Gallery if Table does not exist
	 */
	private function Install_Gallery() {
		if (!db_exists($this->photo_cat_db) && $this->enable_album) {
			$result = dbquery("CREATE TABLE ".$this->photo_cat_db." (
				album_id mediumint(8) unsigned not null auto_increment,
				album_title varchar(100) not null default '',
				album_description text not null,
				album_thumb varchar(100) not null default '',
				album_user mediumint(11) unsigned not null default '0',
				album_access bigint(3) unsigned not null default '901',
				album_order smallint(5) unsigned not null default '0',
				album_datestamp int(10) unsigned not null default '0',
				album_language varchar(50) not null default '',
				PRIMARY KEY (album_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
			if ($result) {
				notify($this->photo_cat_db.' SQL', 'Table created successfully.');
			}
		}
		if (!db_exists($this->photo_db)) {
			$result = dbquery("CREATE TABLE ".$this->photo_db." (
				photo_id mediumint(11) unsigned not null auto_increment,
				".($this->enable_album ? "album_id mediumint(11) unsigned not null default '0'," : '')."
				photo_title text varchar(100) not null default '',
				photo_description text not null,
				photo_keywords varchar(250) not null default '',
				photo_filename varchar(100) not null default '',
				photo_thumb1 varchar(100) not null default '',
				photo_thumb2 varchar(100) not null default '',
				photo_datestamp int(10) unsigned not null default '0',
				photo_user mediumint(11) unsigned not null default '0',
				photo_views int(10) unsigned not null default '0',
				photo_order smallint(5) unsigned not null default '0',
				".($this->enable_comments ? "photo_allow_comments tinyint(1) unsigned not null default '0'," : '')."
				".($this->enable_ratings ? "photo_allow_ratings tinyint(1) unsigned not null default '0'," : '')."
				PRIMARY KEY (photo_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
			if ($result) {
				notify($this->photo_db.' SQL', 'Table created successfully.');
			}
		}
	}

	public function __construct() {
		// Using GET to set the vars so it can be accessed in the entire class
		$this->album_id = isset($_GET['album_id']) && isnum($_GET['album_id']) ? $_GET['album_id'] : 0;
		$this->photo_id = isset($_GET['photo_id']) && isnum($_GET['photo_id']) ? $_GET['photo_id'] : 0;
		$this->rowstart = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;
		$this->action = isset($_GET['action']) && $_GET['action'] ? $_GET['action'] : '';
		$this->album_data['album_language'] = LANGUAGE;
		$this->album_data['album_datestamp'] = time();
	}

	/**
	 * @return array
	 */
	private function check_api() {
		$error = array();
		if (!$this->image_upload_dir) {
			$error[] = 'Image Upload Directory is not defined';
		}
		if (!$this->photo_cat_db) {
			$error[] = 'Photo Album DB Table is not defined';
		}
		if (!$this->photo_db) {
			$error[] = 'Photo DB Table is not defined';
		}
		if (!$this->gallery_rights) {
			$error[] = 'Photo Rights is not defined';
		}
		if (!empty($error)) {
			foreach ($error as $errors) {
				notify('Gallery Boot Error', $errors, array('sticky' => 1));
			}
		}
		return $error;
	}

	/**
	 *
	 */
	public function boot() {
		global $locale;
		require_once LOCALE.LOCALESET."admin/photoalbums.php";
		$_GET['action'] = isset($_GET['action']) && $_GET['action'] ? $_GET['action'] : '';
		$_GET['order'] = isset($_GET['order']) && isnum($_GET['order']) ? $_GET['order'] : 1;
		$_GET['gallery'] = isset($_GET['gallery']) && self::validate_album($_GET['gallery']) ? $_GET['gallery'] : '';
		if (function_exists('gd_info')) {
			//self::Install_Gallery();
			// set album max order
			$this->album_max_order = dbresult(dbquery("SELECT MAX(album_order) FROM ".$this->photo_cat_db." WHERE album_language='".LANGUAGE."'"), 0)+1;
			if (isset($_GET['gallery']) && $_GET['gallery'] > 0) {
				$this->photo_max_order = dbresult(dbquery("SELECT MAX(photo_order) FROM ".$this->photo_db." WHERE album_id='".intval($_GET['gallery'])."'"), 0)+1;
			}

			/**
			 * Display the requirements of booting
			 */
			$error = self::check_api();
			if (empty($error)) {
				self::delete_gallery();
				self::set_albumDB();
				self::set_photoDB();
				self::set_batchPhotoDB();
				switch ($_GET['action']) {
					case 'refresh':
						self::refresh_album_order();
						break;
					case 'mu':
						self::refresh_order('mu');
						break;
					case 'md':
						self::refresh_order('md');
						break;
					case 'mup':
						self::refresh_order('mup');
						break;
					case 'mdp':
						self::refresh_order('mdp');
						break;
				}
				self::display_gallery_filters();
				self::display_gallery();
			}
		} else {
			notify("gd_info() ".$locale['na'], '');
		}

	}

	/**
	 * @param string $gallery_rights
	 */
	public function setGalleryRights($gallery_rights) {
		$this->gallery_rights = $gallery_rights;
	}

	/**
	 * @param boolean $allow_comments
	 */
	public function setAllowComments($allow_comments) {
		$this->allow_comments = $allow_comments;
	}

	/**
	 * @param boolean $allow_ratings
	 */
	public function setAllowRatings($allow_ratings) {
		$this->allow_ratings = $allow_ratings;
	}

	/**
	 * @param boolean $enable_comments
	 */
	public function setEnableComments($enable_comments) {
		$this->enable_comments = $enable_comments;
	}

	/**
	 * @param boolean $enable_ratings
	 */
	public function setEnableRatings($enable_ratings) {
		$this->enable_ratings = $enable_ratings;
	}

	/**
	 * @param boolean $enable_album
	 */
	public function setEnableAlbum($enable_album) {
		$this->enable_album = $enable_album;
	}

	/**
	 * @param array $upload_settings
	 */
	public function setUploadSettings(array $upload_settings) {
		$this->upload_settings = $upload_settings;
	}

	/**
	 * @param string $image_upload_dir
	 */
	public function setImageUploadDir($image_upload_dir) {
		$this->image_upload_dir = $image_upload_dir;
	}

	/**
	 * @param string $photo_cat_db
	 */
	public function setPhotoCatDb($photo_cat_db) {
		$this->photo_cat_db = $photo_cat_db;
	}

	/**
	 * @param string $photo_db
	 */
	public function setPhotoDb($photo_db) {
		$this->photo_db = $photo_db;
	}

	/**
	 * Get Album Data - how to get
	 * @return array
	 */
	public function get_album($album_id = 0) {
		if (isnum($album_id)) {
			return dbarray(dbquery("SELECT * FROM ".$this->photo_cat_db." WHERE album_id='".intval($album_id)."' "));
		}
		return array();
	}

	/**
	 * Get Photo Data
	 * @param int $photo_id
	 * @return array|bool
	 */
	public function get_photo($photo_id = 0) {
		if (isnum($photo_id)) {
			return dbarray(dbquery("SELECT photo.*, photo.photo_user as user_id, album.album_title, album.album_description, u.user_name, u.user_status, u.user_avatar,
				count(comment.comment_id) as comment_count, count(rating.rating_id) as rating_count, sum(rating_vote) as total_votes
				FROM ".$this->photo_db." photo
				INNER JOIN ".$this->photo_cat_db." album on photo.album_id = album.album_id
				INNER JOIN ".DB_USERS." u on photo.photo_user=u.user_id
				LEFT JOIN ".DB_COMMENTS." comment on comment.comment_item_id=photo.photo_id AND comment.comment_type='".$this->gallery_rights."'
				LEFT JOIN ".DB_RATINGS." rating on rating.rating_item_id=photo.photo_id  AND rating.rating_type='".$this->gallery_rights."'
				WHERE photo_id='".intval($photo_id)."'
				"));
		}
		return array();
	}

	/**
	 * Check whether a album id is valid
	 * @param $album_id
	 * @return bool|string
	 */
	public function validate_album($album_id) {
		if (isnum($album_id)) {
			return dbcount("('album_id')", $this->photo_cat_db, "album_id=' ".intval($album_id)." '");
		}
		return FALSE;
	}

	/**
	 * @return string
	 */
	public function getPhotoCatDb() {
		return $this->photo_cat_db;
	}

	/**
	 * @return string
	 */
	public function getPhotoDb() {
		return $this->photo_db;
	}

	/**
	 * Check whether a photo id is valid
	 * @param $photo_id
	 * @return bool|string
	 */
	public function validate_photo($photo_id) {
		if (isnum($photo_id)) {
			return dbcount("('photo_id')", $this->photo_db, "photo_id='".intval($photo_id)."'");
		}
		return FALSE;
	}

	/**
	 * Returns an array of all rows of album records
	 * @return array
	 */
	private function get_albumlist() {
		$list = array();
		$result = dbquery("SELECT * FROM ".$this->photo_cat_db." ORDER BY album_order ASC");
		if (dbrows($result) > 0) {
			while ($data = dbarray($result)) {
				$list[$data['album_id']] = $data['album_title'];
			}
		}
		return $list;
	}

	/**
	 * SQL delete album
	 */
	private function delete_gallery() {
		global $locale;
		if (isset($_POST['cancel_delete'])) {
			redirect(clean_request('', array('gallery_delete', 'gallery_type', 'status'), FALSE));
		}
		if (isset($_GET['gallery_delete']) && isnum($_GET['gallery_delete']) && isset($_GET['gallery_type']) && isnum($_GET['gallery_type'])) {
			switch ($_GET['gallery_type']) {
				case '1': // album
					$album_id = $_GET['gallery_delete'];
					if (self::validate_album($album_id)) {
						$album_data = self::get_album($album_id);
						$photo_exists = dbcount("('photo_id')", $this->photo_db, "album_id = '".intval($album_id)."' ");
						if ($photo_exists) {
							if (isset($_POST['confirm_delete'])) {
								$move_album = form_sanitizer($_POST['delete_action'], '0', 'delete_action');
								$result = dbquery("SELECT * FROM ".$this->photo_db." WHERE album_id = '".$album_id."'");
								if (dbrows($result) > 0) {
									if ($move_album) {
										// move picture to $move_album
										while ($photo_data = dbarray($result)) {
											dbquery("UPDATE ".$this->photo_db." SET album_id='".intval($move_album)."' WHERE photo_id='".$photo_data['photo_id']."'");
										}
									} else {
										// delete all
										while ($photo_data = dbarray($result)) {
											@unlink(rtrim($this->image_upload_dir, '/').'/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$photo_data['photo_thumb']);
											@unlink(rtrim($this->image_upload_dir, '/').'/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$photo_data['photo_thumb2']);
											@unlink(rtrim($this->image_upload_dir, '/').'/'.$photo_data['photo_filename']);
											dbquery_insert($this->photo_db, $photo_data, 'delete');
										}
									}
									dbquery_insert($this->photo_cat_db, $album_data, 'delete');
									if (!defined('FUSION_NULL')) redirect(clean_request('status=adel', array('gallery_delete',
										'status',
										'gallery_type'), FALSE));
								}
							} else {
								echo openmodal('confirm_steps', 'There are Pictures found in Gallery Album - '.$album_data['album_title']);
								echo openform('inputform', 'post', FUSION_REQUEST, array('max_tokens' => 0,
									'notice' => 0));
								$list = self::get_albumlist();
								$options[0] = 'Delete the Entire Gallery Album';
								foreach ($list as $album_id => $album_title) {
									$options[$album_id] = 'Move to .. Gallery Album '.$album_title;
								}
								echo form_select('delete_action', 'Please select one of the following:', $options, '', array('inline' => 1,
									'width' => '300px'));
								echo form_button('confirm_delete', $locale['confirm'], $album_id, array('class' => 'btn-sm btn-danger col-sm-offset-3',
									'icon' => 'fa fa-trash'));
								echo form_button('cancel', $locale['cancel'], $album_id, array('class' => 'btn-sm btn-default m-l-10'));
								echo closeform();
								echo closemodal();
							}
						} else {
							dbquery_insert($this->photo_cat_db, $album_data, 'delete');
							if (!defined('FUSION_NULL')) redirect(clean_request('status=adel', array('gallery_delete',
								'status',
								'gallery_type'), FALSE));
						}
					}
					break;
				case '2': // picture
					$photo_id = $_GET['gallery_delete'];
					if (self::validate_photo($_GET['gallery_delete'])) {
						$photo_data = self::get_photo($photo_id);
						@unlink(rtrim($this->image_upload_dir, '/').'/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$photo_data['photo_thumb']);
						@unlink(rtrim($this->image_upload_dir, '/').'/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$photo_data['photo_thumb2']);
						@unlink(rtrim($this->image_upload_dir, '/').'/'.$photo_data['photo_filename']);
						dbquery_insert($this->photo_db, $photo_data, 'delete');
					}
					break;
			}
		}
	}

	/**
	 * SQL save or update album
	 */
	private function set_albumDB() {
		global $userdata;
		if (isset($_POST['upload_album'])) {
			// keep the modal open to listen to error messages
			add_to_jquery("$('#add_album-Modal').modal('show');");
			$this->album_data = array(
				'album_id' => isset($_POST['album_id']) ? form_sanitizer($_POST['album_id'], '', 'album_id') : 0,
				'album_title' => isset($_POST['album_title']) ? form_sanitizer($_POST['album_title'], '', 'album_title') : $this->album_data['album_title'],
				'album_description' => isset($_POST['album_description']) ? form_sanitizer($_POST['album_description'], '', 'album_description') : $this->album_data['album_description'],
				'album_user' => $userdata['user_id'],
				'album_access' => isset($_POST['album_access']) ? form_sanitizer($_POST['album_access'], 0, 'album_access') : $this->album_data['album_access'],
				'album_order' => isset($_POST['album_order']) ? form_sanitizer($_POST['album_order'], 0, 'album_order') : $this->album_data['album_order'],
				'album_datestamp' => time(),
				'album_language' => isset($_POST['album_language']) ? form_sanitizer($_POST['album_language'], '', 'album_language') : $this->album_data['album_language']
			);
			if (!$this->album_data['album_order']) $this->album_data['album_order'] = dbresult(dbquery("SELECT MAX(album_order) FROM ".$this->photo_cat_db." WHERE album_language='".LANGUAGE."'"), 0)+1;
			// point of injection of altered if you know the album_id -- possible bug: high volume sites will not be able to book the id unless record is made?
			if (!$this->album_data['album_id']) {
				$next_album_id = dbnextid($this->photo_cat_db);
				self::set_modified_upload_path($next_album_id, 'imagefile', 1);
			} else {
				self::set_modified_upload_path($this->album_data['album_id'], 'imagefile', 1);
			}
			self::set_modified_upload_path($next_album_id, 'album_file', 1);
			$upload_result = form_sanitizer($_FILES['album_file'], '', 'album_file');
			/** Note: Ensure your hidden field return does not bear the same input name as the fileinput name else form sanitizer will not sanitize properely as both bears same identifier */
			$this->album_data['album_thumb'] = form_sanitizer($_POST['album_hfile'], '', 'album_hfile');
			if (isset($upload_result['error']) && $upload_result['error'] !== '0') {
				// upload success
				$this->album_data['album_thumb'] = $upload_result['thumb1_name'];
				// only exist in new upload
				$image_name = $upload_result['image_name'];
				$thumb1_name = $upload_result['thumb1_name'];
				$thumb2_name = $upload_result['thumb2_name'];
			}
			/**
			 * Photo_data sourced from 2 place. If Album history exist, photo_data will follow sql. if not, follow the OOP field construct we initialized.
			 * Either way, there is no need to !empty() or isset() check.
			 */
			$album_history = self::get_album($this->album_data['album_id']);
			if (!empty($album_history)) {
				$thumb_photo = dbquery("SELECT photo_id, album_id, photo_filename, photo_thumb1, photo_thumb2, photo_views, photo_order, photo_allow_comments, photo_allow_ratings FROM ".$this->photo_db." WHERE photo_thumb1='".$album_history['album_thumb']."'");
				if (dbrows($thumb_photo) > 0) {
					$this->photo_data = dbarray($thumb_photo); // use back old records
				}
				// ok. now we need to delete the old picture set if changed
				if ($this->album_data['album_thumb'] !== $album_history['album_thumb']) {
					@unlink(rtrim($this->image_upload_dir, '/').'/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$this->photo_data['photo_thumb1']);
					@unlink(rtrim($this->image_upload_dir, '/').'/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$this->photo_data['photo_thumb2']);
					@unlink(rtrim($this->image_upload_dir, '/').'/'.$this->photo_data['photo_filename']);
				}
			}
			/**
			 * Recompile New Photo Data Output.
			 * 3 Elements in play for key - filename, thumb1 and thumb2.
			 * a. $image_name exist when we upload a new file. (First precedence).
			 * b. $this->photo_data will be `overwritten` if we have an album thumb change.
			 * c. If both a & b does not exist, it will follow the blank defaults.
			 */
			$this->photo_data = array('photo_id' => $this->photo_data['photo_id'],
				'album_id' => $this->photo_data['album_id'],
				'photo_title' => $this->album_data['album_title'],
				'photo_description' => $this->album_data['album_description'],
				'photo_keywords' => $this->album_data['album_title'],
				'photo_filename' => isset($image_name) ? $image_name : $this->photo_data['photo_filename'],
				'photo_thumb1' => isset($thumb1_name) ? $thumb1_name : $this->photo_data['photo_thumb1'],
				'photo_thumb2' => isset($thumb2_name) ? $thumb2_name : $this->photo_data['photo_thumb2'],
				'photo_datestamp' => $this->album_data['album_datestamp'],
				'photo_user' => $userdata['user_id'],
				'photo_views' => $this->photo_data['photo_views'],
				'photo_order' => $this->photo_data['photo_order'],
				'photo_allow_comments' => $this->photo_data['photo_allow_comments'],
				'photo_allow_ratings' => $this->photo_data['photo_allow_ratings'],);
			if ($this->album_data['album_id'] && self::validate_album($this->album_data['album_id'])) {
				$result = dbquery_order($this->photo_cat_db, $this->album_data['album_order'], 'album_order', $this->album_data['album_id'], 'album_id', FALSE, FALSE, 1, 'album_language', 'update');
				if ($result) {
					dbquery_insert($this->photo_cat_db, $this->album_data, 'update');
					if (!empty($this->photo_data) && self::validate_photo($this->photo_data['photo_id'])) {
						dbquery_insert($this->photo_db, $this->photo_data, 'update');
					}
					if (!defined('FUSION_NULL')) redirect(clean_request('status=au', array('gallery_edit',
						'gallery_type'), FALSE));
				}
			} else {
				// new saves
				$result = dbquery_order($this->photo_cat_db, $this->album_data['album_order'], 'album_order', FALSE, FALSE, FALSE, FALSE, 1, 'album_language', 'save');
				if ($result) {
					dbquery_insert($this->photo_cat_db, $this->album_data, 'save');
					$this->album_data['album_id'] = dblastid();
					if (!empty($this->photo_data) && $this->album_data['album_id']) {
						if (!$this->photo_data['photo_order']) $this->photo_data['photo_order'] = $this->photo_data['photo_order'] = dbresult(dbquery("SELECT MAX(photo_order) FROM ".$this->photo_db." WHERE album_id='".intval($this->album_data['album_id'])."'"), 0)+1;
						$this->photo_data['album_id'] = $this->album_data['album_id'];
						$result = dbquery_order($this->photo_db, $this->photo_data['photo_order'], 'photo_order', FALSE, FALSE, $this->photo_data['album_id'], 'album_id', FALSE, FALSE, 'save');
						if ($result) {
							dbquery_insert($this->photo_db, $this->photo_data, 'save');
						}
					}
					if (!defined('FUSION_NULL')) redirect(clean_request('status=an', array('gallery_edit',
						'gallery_type'), FALSE));
				}
			}
		}
	}

	/**
	 * Returns a modified path based on php ini safe mode
	 * This is for making a album_$id for better file organization
	 * @param $album_id
	 * @return string
	 */
	public function set_modified_upload_path($album_id, $input_name, $type = '1') {
		// define safemode here.
		if (!defined('SAFEMODE')) define("SAFEMODE", @ini_get("safe_mode") ? TRUE : FALSE);
		if (isnum($album_id) && $album_id) {
			switch ($type) {
				case '1':
					$upload_dir = !SAFEMODE ? rtrim($this->image_upload_dir, '/')."/album_".$album_id."/" : $this->image_upload_dir;
					$_SESSION['form_fields'][\defender::set_sessionUserID()][$_SERVER['PHP_SELF']][$input_name]['path'] = $upload_dir;
					break;
				case '2':
					$upload_dir = !SAFEMODE ? rtrim($this->image_upload_dir, '/')."/album_".$album_id."/" : $this->image_upload_dir;
					$_SESSION['form_fields'][\defender::set_sessionUserID()][$_SERVER['PHP_SELF']][$input_name]['path'] = $upload_dir;
					break;
			}
		}
	}

	/**
	 * Get a Virtual Path
	 * @param $album_id
	 * @return string
	 */
	public function get_virtual_path($album_id) {
		if (!defined('SAFEMODE')) define("SAFEMODE", @ini_get("safe_mode") ? TRUE : FALSE);
		return !SAFEMODE ? rtrim($this->image_upload_dir, '/')."/album_".$album_id."/" : $this->image_upload_dir;
	}

	/**
	 * Returns Album Image
	 * @param      $album_id
	 * @param      $picture
	 * @param      $thumb1
	 * @param      $thumb2
	 * @param bool $hiRes
	 * @return bool|string
	 */
	public function get_album_image($album_id, $thumbnail_folder, $picture, $thumb1, $thumb2, $hiRes=false) {
		$path = self::get_virtual_path($album_id);
		if (!$hiRes) {
			if ($thumb1 && file_exists($path.rtrim($thumbnail_folder, '/')."/".$thumb1)) return $path.rtrim($thumbnail_folder, '/')."/".$thumb1;
			if ($thumb1 && file_exists($path.$thumb1)) return $path.$thumb1;
			if ($thumb2 && file_exists($path.rtrim($thumbnail_folder, '/')."/".$thumb2)) return $path.rtrim($thumbnail_folder, '/')."/".$thumb2;
			if ($thumb2 && file_exists($path.rtrim($thumbnail_folder, '/')."/".$thumb2)) return $path.$thumb2;
			if ($picture && file_exists($path.$picture)) return $path.$picture;
		} else {
			if ($picture && file_exists($path.$picture)) return $path.$picture;
			if ($thumb2 && file_exists($path.$thumb2)) return $path.$thumb2;
			if ($thumb2 && file_exists($path.rtrim($thumbnail_folder, '/')."/".$thumb2)) return $path.rtrim($thumbnail_folder, '/')."/".$thumb2;
			if ($thumb1 && file_exists($path.$thumb1)) return $path.$thumb1;
			if ($thumb1 && file_exists($path.rtrim($thumbnail_folder, '/')."/".$thumb1)) return $path.rtrim($thumbnail_folder, '/')."/".$thumb1;
		}
		return false;
	}


	/**
	 * SQL batch upload
	 */
	private function set_batchPhotoDB() {
		global $userdata;
		if (isset($_POST['batch_upload'])) {
			$album_id = form_sanitizer($_POST['album_id'], '0', 'album_id');
			if (self::validate_album($album_id)) {
				$data = $this->photo_data;
				$title = form_sanitizer($_POST['photo_title'], 'Untitled', 'photo_title');
				self::set_modified_upload_path($album_id, 'batch_file', 2);
				$upload = form_sanitizer($_FILES['batch_file'], '', 'batch_file'); // upload returns as array
				if (!empty($upload)) {
					$count = 1;
					foreach($upload as $arr => $files) {
						if ($files['error'] == 0) {
							$data['album_id'] = $album_id;
							$data['photo_title'] = $title.'_'.$count;
							$data['photo_filename'] = $files['image_name'];
							$data['photo_thumb1'] = $files['thumb1_name'];
							$data['photo_thumb2'] = $files['thumb2_name'];
							$data['photo_order'] = dbresult(dbquery("SELECT MAX(photo_order) FROM ".$this->photo_db." WHERE album_id='".$data['album_id']."'"), 0)+1;
							$data['photo_datestamp'] = time();
							$data['photo_user'] = $userdata['user_id'];
							dbquery_insert($this->photo_db, $data, 'save');
							$count++;
						}
					}
					if (!defined('FUSION_NULL')) redirect(FUSION_REQUEST);
				}
			}
		}
	}

	/**
	 * SQL save or update photo
	 */
	private function set_photoDB() {
		global $userdata;
		if (isset($_POST['upload_photo'])) {
			$this->photo_data = array(
				'photo_id' => isset($_POST['photo_id']) ? form_sanitizer($_POST['photo_id'], '', 'photo_id') : 0,
				'album_id' => isset($_POST['album_id']) ? form_sanitizer($_POST['album_id'], '', 'album_id') : 0,
				'photo_title' => isset($_POST['photo_title']) ? form_sanitizer($_POST['photo_title'], '', 'photo_title') : $this->photo_data['photo_title'],
				'photo_description' => isset($_POST['photo_description']) ? form_sanitizer($_POST['photo_description'], '', 'photo_description') : $this->photo_data['photo_description'],
				'photo_keywords' => isset($_POST['photo_keywords']) ? form_sanitizer($_POST['photo_keywords'], '', 'photo_keywords') : $this->photo_data['photo_keywords'],
				'photo_allow_comments' => isset($_POST['photo_allow_comments']) ? 1 : $this->photo_data['photo_comments'],
				'photo_allow_ratings' => isset($_POST['photo_allow_ratings']) ? 1 : $this->photo_data['photo_ratings'],
				'photo_user' => $userdata['user_id'],
				'photo_order' => isset($_POST['photo_order']) ? form_sanitizer($_POST['photo_order'], 0, 'photo_order') : $this->photo_data['photo_order'],
				'photo_datestamp' => time(),);
			// Push a new path value to defender for upload
			self::set_modified_upload_path($this->photo_data['album_id'], 'photo_file', 2);
			$upload_result = form_sanitizer($_FILES['photo_file'], '', 'photo_file');
			$this->photo_data['photo_filename'] = form_sanitizer($_POST['photo_hfile'], '', 'photo_hfile');
			$this->photo_data['photo_thumb1'] = form_sanitizer($_POST['photo_hthumb1'], '', 'photo_hthumb1');
			$this->photo_data['photo_thumb2'] = form_sanitizer($_POST['photo_hthumb2'], '', 'photo_hthumb2');
			if (isset($upload_result['error']) && $upload_result['error'] !== '0') {
				// upload success
				$this->photo_data['photo_filename'] = $upload_result['image_name'];
				$this->photo_data['photo_thumb1'] = $upload_result['thumb1_name'];
				$this->photo_data['photo_thumb2'] = $upload_result['thumb2_name'];
			}
			// photo ordering.
			if (!$this->photo_data['photo_order']) $this->photo_data['photo_order'] = dbresult(dbquery("SELECT MAX(photo_order) FROM ".$this->photo_db." WHERE album_id='".$this->photo_data['album_id']."'"), 0)+1;
			// fetch old data and compare if changed photo.
			$photo_history = self::get_photo($this->photo_data['photo_id']);
			if (!empty($photo_history)) {
				// ok. now we need to delete the old picture set if changed
				if ($this->photo_data['photo_filename'] !== $photo_history['photo_filename']) {
					@unlink(rtrim($this->image_upload_dir, '/').'/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$photo_history['photo_thumb1']);
					@unlink(rtrim($this->image_upload_dir, '/').'/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$photo_history['photo_thumb2']);
					@unlink(rtrim($this->image_upload_dir, '/').'/'.$photo_history['photo_filename']);
				}
			}
			if ($this->photo_data['photo_id'] && self::validate_photo($this->photo_data['photo_id'])) {
				$result = dbquery_order($this->photo_db, $this->photo_data['photo_order'], 'photo_order', $this->photo_data['photo_id'], 'photo_id', $this->photo_data['album_id'], 'album_id', FALSE, FALSE, 'update');
				if ($result) {
					dbquery_insert($this->photo_db, $this->photo_data, 'update');
					if (!defined('FUSION_NULL')) redirect(clean_request('status=pu', array('gallery_edit',
						'gallery_type'), FALSE));
				}
			} else {
				// new saves
				$result = dbquery_order($this->photo_db, $this->photo_data['photo_order'], 'photo_order', FALSE, FALSE, $this->photo_data['album_id'], 'album_id', FALSE, FALSE, 'save');
				if ($result) {
					dbquery_insert($this->photo_db, $this->photo_data, 'save');
					if (!defined('FUSION_NULL')) redirect(clean_request('status=pn', array('gallery_edit',
						'gallery_type'), FALSE));
				}
			}
		}
	}

	/**
	 * Automatic get last order photo in a album and update album cover
	 * @param $album_id
	 * @param $current_thumb
	 */
	private function refresh_album_thumb($album_id, $current_thumb) {
		$result = dbquery("SELECT photo_thumb1 FROM ".$this->photo_db." WHERE album_id='".intval($album_id)."' ORDER BY photo_order DESC");
		if (dbrows($result) > 0) {
			while ($photo_data = dbarray($result)) {
				if ($current_thumb !== $photo_data['photo_thumb1']) {
					$thumbnail = rtrim($this->image_upload_dir, '/').'/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$photo_data['photo_thumb1'];
					if (file_exists($thumbnail) && !is_dir($thumbnail)) {
						$result = dbquery("UPDATE ".$this->photo_cat_db." SET album_thumb='".$photo_data['photo_thumb1']."' WHERE album_id='".intval($album_id)."'");
						if ($result) break;
					}
				}
			}
		}
	}

	/**
	 * Deletion of thumb - not useful and very unfriendly
	 */
	private function delete_album_thumb() {
		if (isset($_POST['delete_album_thumb'])) {
			$album_id = form_sanitizer($_POST['album_id'], '', 'album_id');
			// delete the thumbnail and the existing picture record
			if (self::validate_album($album_id)) {
				$album_data = self::get_album($album_id);
				@unlink(rtrim($this->image_upload_dir, '/').'/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$album_data['album_thumb']);
				dbquery("UPDATE ".$this->photo_cat_db." SET album_thumb='' WHERE album_id='".intval($album_id)."'");
				$result = dbquery("SELECT photo_filename, photo_thumb1, photo_thumb2 FROM ".$this->photo_db." WHERE photo_thumb1 = '".$album_data['album_thumb']."'");
				if (dbrows($result) > 0) {
					$photo_data = dbarray($result);
					@unlink(rtrim($this->image_upload_dir, '/').'/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$photo_data['photo_thumb']);
					@unlink(rtrim($this->image_upload_dir, '/').'/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$photo_data['photo_thumb2']);
					@unlink(rtrim($this->image_upload_dir, '/').'/'.rtrim($this->upload_settings['thumbnail_folder'], '/').'/'.$photo_data['photo_filename']);
					dbquery_insert($this->photo_db, $photo_data, 'delete');
				}
			}
		}
	}

	/**
	 * Ui design regarding photo dropping. no dropping. can only be replaced.
	 */
	private function display_gallery_filters() {
		global $locale;
		$album_list = self::get_albumlist();
		$album_edit = 0;
		$photo_edit = 0;
		if (isset($_GET['gallery_edit']) && isnum($_GET['gallery_edit']) && isset($_GET['gallery_type']) && isnum($_GET['gallery_type'])) {
			// have type and edit
			switch ($_GET['gallery_type']) {
				case '1': // album type
					if (self::validate_album($_GET['gallery_edit'])) {
						$this->album_data = self::get_album($_GET['gallery_edit']);
						$album_edit = 1;
						add_to_jquery("$('#add_album-Modal').modal('show');");
					}
					break;
				case '2': // picture type
					if (self::validate_photo($_GET['gallery_edit'])) {
						$this->photo_data = self::get_photo($_GET['gallery_edit']);
						$photo_edit = 1;
						add_to_jquery("$('#add_photo-Modal').modal('show');");
					}
			}
		}
		$this->upload_settings += array('inline' => 1, 'type' => 'image', 'required' => !$album_edit ? 1 : 0);

		echo "<div class='m-t-10 m-b-20'>\n";
		echo form_button('add_album', $locale['600'], 'add_album', array('class' => 'btn-primary btn-sm m-r-10', 'icon' => 'fa fa-image'));
		echo form_button('add_photo', $locale['601'], 'add_photo', array('class' => 'btn-sm btn-default m-r-10', 'icon' => 'fa fa-camera'));
		if ($_GET['gallery']) echo form_button('batch_photo', $locale['photo_002'], 'batch_photo', array('class' => 'btn-sm btn-default m-r-10', 'icon' => 'fa fa-cloud-upload'));
		echo "<a title='".$locale['470c']."' class='btn button btn-sm btn-default' href='".clean_request('action=refresh', array('action'), FALSE)."'><i class='fa fa-file-o'></i> ".$locale['470c']."</a>";
		echo "</div>\n";
		if ($_GET['gallery']) {
			echo openmodal('batch_album', $locale['photo_002'], array('button_id'=>'batch_photo', 'static'=>1));
			echo openform('batchform', 'post', FUSION_REQUEST, array('max_tokens' => 1, 'enctype'=>1));
			echo "<div class='alert alert-info'>You can select more than 1 photos and submit them as a series into this album. Photo Title will be indexed in numbers.</div>";
			echo form_text('photo_title', $locale['622'], $this->photo_data['photo_title'], array('placeholder' => $locale['622'], 'inline' => 1));
			$up_settings = $this->upload_settings;
			unset($up_settings['multiple']);
			$up_settings += array('multiple'=>1);
			echo form_fileinput($locale['631'], 'batch_file[]', 'batch_file', $this->image_upload_dir, '', $up_settings);
			echo form_hidden('', 'album_id', 'album_id_batch', $_GET['gallery']);
			echo form_button('batch_upload', $locale['631'], $locale['631'], array('class'=>'btn btn-success'));
			echo closeform();
			echo closemodal();
		}

		echo openmodal('add_album', $album_edit ? $locale['606'] : $locale['605'], array('button_id' => 'add_album','static' => 1));
		echo openform('albumform', 'post', FUSION_REQUEST, array('max_tokens' => 1, 'enctype' => 1));
		echo "<div class='row'>\n<div class='col-xs-12 col-sm-9'>\n";
		echo form_text('album_title', $locale['607'], $this->album_data['album_title'], array('placeholder' => $locale['608'], 'inline' => 1, 'required' => 1));
		echo form_textarea('album_description', $locale['609'], $this->album_data['album_description'], array('placeholder' => $locale['610'],
			'inline' => 1));
		echo form_fileinput('Upload Picture', 'album_file', 'album_file', $this->image_upload_dir, '', $this->upload_settings);
		echo form_hidden('', 'album_hfile', 'album_hfile', $this->album_data['album_thumb']);
		echo form_select('album_access', $locale['611'],  getgroupOpts(), $this->album_data['album_access'], array('inline' => 1));
		echo form_hidden('', 'album_id', 'album_id', $this->album_data['album_id']);
		echo form_select('album_language', $locale['612'],  fusion_get_enabled_languages(), $this->album_data['album_language'], array('inline' => 1));
		echo form_select('album_order', $locale['613'], range(0, $this->album_max_order), $this->album_data['album_order'], array('inline' => 1,
			'width' => '150px')); // 0 picture, 1. ok.
		echo form_button('upload_album', $locale['save_changes'], 'upload_album', array('class' => 'btn-success btn-sm m-r-10'));
		echo "<button type='button' class='btn btn-sm btn-default' data-dismiss='modal'><i class='entypo cross'></i> ".$locale['close']."</button>\n";
		echo "</div>\n<div class='col-xs-12 col-sm-3 text-center'>\n";
		if ($album_edit) {
			echo "<div id='album_tmb' class='well'>\n";
			$img_path = self::get_virtual_path($this->album_data['album_id']).rtrim($this->upload_settings['thumbnail_folder'], '/')."/".$this->album_data['album_thumb'];
			echo "<img class='img-responsive' style='margin:0 auto;' src='$img_path' alt='".$this->album_data['album_title']."'/>\n";
			echo "</div>\n";
		}
		echo "</div>\n</div>\n";
		echo closeform();
		echo closemodal();

		echo openmodal('add_photo', $photo_edit ? $locale['621'] : $locale['620'], array('button_id' => 'add_photo'));
		echo openform('photoform', 'post', FUSION_REQUEST, array('max_tokens' => 1, 'enctype' => 1));
		echo "<div class='row'>\n<div class='col-xs-12 col-sm-9'>\n";
		echo form_text('photo_title', $locale['622'], $this->photo_data['photo_title'], array('placeholder' => $locale['623'], 'inline' => 1));
		$sel = (isset($_GET['gallery']) && isnum($_GET['gallery'])) ? $_GET['gallery'] : $this->photo_data['album_id'];
		echo form_select('album_id', $locale['624'], $album_list, $sel, array('inline' => 1));
		echo form_hidden('', 'photo_id', 'photo_id', $this->photo_data['photo_id']);
		echo form_hidden('', 'photo_order', 'photo_order', $this->photo_data['photo_order']);
		echo form_fileinput('Upload Picture', 'photo_file', 'photo_file', $this->image_upload_dir, '', $this->upload_settings);
		echo form_hidden('', 'photo_hfile', 'photo_hfile', $this->photo_data['photo_filename']);
		echo form_hidden('', 'photo_hthumb1', 'photo_hthumb1', $this->photo_data['photo_thumb1']);
		echo form_hidden('', 'photo_hthumb2', 'photo_hthumb2', $this->photo_data['photo_thumb2']);
		echo form_select('photo_keywords', $locale['625'],  array(), $this->photo_data['photo_keywords'], array('placeholder' => $locale['626'],
			'inline' => 1,
			'multiple' => 1,
			'width' => '100%',
			'tags' => 1));
		echo form_textarea('photo_description', $locale['627'], $this->photo_data['photo_description'], array('placeholder' => $locale['628'],
			'inline' => 1));
		echo form_select('photo_allow_comments', $locale['629'], array($locale['yes'], $locale['no']), $this->photo_data['photo_allow_comments'], array('inline' => 1));
		echo form_select('photo_allow_ratings', $locale['630'], array($locale['yes'], $locale['no']), $this->photo_data['photo_allow_ratings'], array('inline' => 1));
		echo form_button('upload_photo', $locale['631'], 'upload_photo', array('class' => 'btn-success btn-sm m-r-10'));
		echo "<button type='button' class='btn btn-sm btn-default' data-dismiss='modal'><i class='entypo cross'></i> ".$locale['close']."</button>\n";
		echo "</div>\n<div class='col-xs-12 col-sm-3 text-center'>\n";
		if ($photo_edit) {
			echo "<div id='photo_tmb' class='well'>\n";
			$img_path = self::get_virtual_path($this->photo_data['album_id']).rtrim($this->upload_settings['thumbnail_folder'], '/')."/".$this->photo_data['photo_thumb1'];
			echo "<img class='img-responsive' style='margin:0 auto;' src='$img_path' alt='".$this->photo_data['photo_title']."'/>\n";
			echo "</div>\n";
		}
		echo "</div>\n</div>\n";
		echo closeform();
		echo closemodal();

		// resets form when create album button
		add_to_jquery("
		$('#add_album').bind('click', function() {
			$.each($('#albumform').serializeArray(), function(i, field) {
    		$('#'+field.name).val('');
			});
			$('#album_tmb').hide();
		});
		$('#add_photo').bind('click', function() {
			$.each($('#photoform').serializeArray(), function(i, field) {
    		$('#'+field.name).val('');
			});
			$('#photo_tmb').hide();
		});
		");


	}

	/**
	 * HTML
	 * @param bool $modal
	 */
	private function display_photo($modal = FALSE) {
		error_reporting(0);
		if (isset($_GET['photo']) && isnum($_GET['photo'])) {
			global $userdata, $locale;
			require_once INCLUDES."photo_functions_include.php";
			$data = self::get_photo($_GET['photo']);
			/**
			 * Increment Views based on Session.
			 */
			$session_id = \defender::set_sessionUserID();
			if (!isset($_SESSION['gallery'][$data['photo_id']][$session_id])) {
				$_SESSION['gallery'][$data['photo_id']][$session_id] = time();
				dbquery("UPDATE ".$this->photo_db." SET photo_views=photo_views+1 WHERE photo_id='".$data['photo_id']."'");
			} else {
				$days_to_keep_session = 30;
				$time = $_SESSION['gallery'][$data['photo_id']][$session_id];
				if ($time <= time()-($days_to_keep_session*3600*24)) unset($_SESSION['gallery'][$data['photo_id']][$session_id]);
			}
			$img_path = self::get_album_image($data['album_id'], $this->upload_settings['thumbnail_folder'], $data['photo_filename'], $data['photo_thumb1'], $data['photo_thumb2'], true);
			$img_src = file_exists($img_path) && !is_dir($img_path) ? $img_path : 'holder.js/170x170/grey/text:'.$locale['na'];
			$file_exif = exif($img_src);
			echo openmodal('photo_show', '', array('class' => 'modal-lg'));
			?>
			<div class='row'>
				<div class='col-xs-12 col-sm-8 col-md-8 col-lg-9 display-inline-block'
					 style='border-right:1px solid #ddd'>
					<h2 class='m-t-0'><?php echo $data['photo_title'] ?></h2>
					<div class='text-smaller m-b-20'><span
							class='text-uppercase strong'><?php echo $locale['635']; ?></span> <?php echo $data['album_title'] ?>
					</div>
					<div class='display-inline' style='overflow: hidden;'>
						<img style='max-width:100%; display:block;' src='<?php echo $img_src ?>'>
					</div>
					<?php
					// comments
					if ($this->enable_comments && $this->allow_comments) {
						require_once INCLUDES."comments_include.php";
						showcomments($this->gallery_rights, $this->photo_db, 'photo_id', $data['photo_id'], FUSION_REQUEST);
					}
					?>
				</div>
				<div class='col-xs-12 col-sm-4 col-md-4 col-lg-3'>
					<div class='text-uppercase text-smaller strong'><?php echo $locale['636']; ?></div>
					<div class='pull-left m-r-10'>
						<?php echo display_avatar($data, '50px', '', '', 'img-rounded m-t-10'); ?>
					</div>
					<div class='overflow-hide'>
						<h4><?php echo profile_link($data['user_id'], $data['user_name'], $data['user_id'], 'text-dark') ?></h4>
						<div>
							<i class='fa fa-calendar m-r-10'></i> <?php echo $locale['637'].showdate('shortdate', $data['photo_datestamp']) ?>
						</div>
					</div>
					<hr>
					<?php
					echo form_button('rate', $locale['638'], 1, array('class' => 'btn-primary btn-sm btn-block',
						'icon' => 'fa fa-star'));
					echo form_button('comment', $locale['639'], 1, array('class' => 'btn-success btn-sm btn-block m-b-20',
						'icon' => 'fa fa-comments-o'));
					?>

					<?php
					add_to_jquery("
					".(isset($_GET['ratings']) && $_GET['ratings'] == $data['photo_id'] ? "$('#postrating').show();" : "$('#postrating').hide();")."
					".(isset($_GET['ratings']) && $_GET['ratings'] == $data['photo_id'] ? "$('#removerating').show();" : "$('#removerating').hide();")."
					$('#rate').bind('click', function() {
					$('#postrating').show();
					$('#removerating').show();
					});
					");
					require_once INCLUDES."ratings_include.php";
					showratings($this->gallery_rights, $data['photo_id'], FUSION_REQUEST);
					if ($data['photo_description']) {
						echo "
						<hr>
						<div class='text-uppercase text-smaller strong'>".$locale['640']."</div>
						".$data['photo_description'];
					}
					?>
					<hr>
					<div>
						<div class='display-block m-b-5'><i class='fa fa-eye m-r-10'></i><span
								class='text-smaller'><?php echo $locale['641']; ?></span><span
								class='pull-right text-bigger strong'><?php echo number_format($data['photo_views']) ?></span>
						</div>
						<div class='display-block m-b-5'><i class='fa fa-star-o m-r-10'></i><span
								class='text-smaller'><?php echo $locale['642']; ?></span><span
								class='pull-right  text-bigger strong'><?php echo $data['rating_count'] ? number_format(($data['rating_count']/$data['total_votes']*100)) : '0' ?>
								/100</span></div>
						<div class='display-block m-b-5'><i class='fa fa-comment-o m-r-10'></i><span
								class='text-smaller'><?php echo $locale['643']; ?></span><span
								class='pull-right text-bigger strong'><?php echo number_format($data['comment_count']) ?></span>
						</div>
						<div class='display-block m-b-5'><i class='fa fa-file-image-o m-r-10'></i><span
								class='text-smaller'><?php echo $locale['644']; ?></span><span
								class='pull-right text-bigger strong'><?php echo $file_exif['width'].'x'.$file_exif['height'] ?></span>
						</div>
						<div class='display-block m-b-5'><i class='fa fa-file-image-o m-r-10'></i><span
								class='text-smaller'><?php echo $locale['645']; ?></span><span
								class='pull-right text-bigger strong'><?php echo $file_exif['mime'] ?></span></div>
						<div class='display-block m-b-5'><i class='fa fa-file-image-o m-r-10'></i><span
								class='text-smaller'><?php echo $locale['646']; ?></span><span
								class='pull-right text-bigger strong'><?php echo $file_exif['channels'] ?></span></div>
						<div class='display-block m-b-5'><i class='fa fa-file-o m-r-10'></i><span
								class='text-smaller'><?php echo $locale['647']; ?></span><span
								class='pull-right text-bigger strong'><?php echo $file_exif['bits'] ?></span></div>
						<div class='display-block m-b-5'><i class='fa fa-instagram m-r-10'></i><span
								class='text-smaller'><?php echo $locale['648']; ?></span><span
								class='pull-right text-bigger strong'><?php echo $file_exif['iso'] ?></span></div>
						<div class='display-block m-b-5'><i class='fa fa-sun-o m-r-10'></i><span
								class='text-smaller'><?php echo $locale['649']; ?></span><span
								class='pull-right text-bigger strong'><?php echo $file_exif['exposure'] ?></span></div>
						<div class='display-block m-b-5'><i class='fa fa-eyedropper m-r-10'></i><span
								class='text-smaller'><?php echo $locale['650']; ?></span><span
								class='pull-right text-bigger strong'><?php echo $file_exif['aperture'] ?></span></div>
						<div class='display-block m-b-5'><i class='fa fa-camera m-r-10'></i><span
								class='text-smaller'><?php echo $locale['651']; ?></span><span
								class='pull-right text-bigger strong'><?php echo $file_exif['make'] ?></span></div>
						<div class='display-block m-b-5'><i class='fa fa-camera m-r-10'></i><span
								class='text-smaller'><?php echo $locale['652']; ?></span><span
								class='pull-right text-bigger strong'><?php echo $file_exif['model'] ?></span></div>
					</div>
					<hr>
					<?php
					if (!empty($data['keywords'])) {
						$keywords = explode(',', $data['keywords']);
						echo "<div class='text-uppercase text-smaller strong m-b-10'>".$locale['655']."</div>";
						foreach ($keywords as $key) {
							echo "<span class='strong btn btn-sm btn-default'>$key</span>\n";
						}
						echo "</div>\n";
					}
					?>
				</div>
			</div>
			<?php
			echo closemodal();
		}
	}

	/**
	 * Album Order Refresh
	 * Copied from original photoalbums admin
	 */
	public function refresh_album_order() {
		global $locale;
		$i = 1;
		$k = 1;
		$result2 = NULL;
		$result3 = NULL;
		$result = dbquery("SELECT album_id FROM ".$this->photo_cat_db." ".(multilang_table("PH") ? "WHERE album_language='".LANGUAGE."'" : "")." ORDER BY album_order");
		while ($data = dbarray($result)) {
			$result2 = dbquery("UPDATE ".$this->photo_cat_db." SET album_order='$i' ".(multilang_table("PH") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." album_id='".$data['album_id']."'");
			$result2 = dbquery("SELECT photo_id FROM ".$this->photo_db." WHERE album_id='".$data['album_id']."' ORDER BY photo_order");
			while ($data2 = dbarray($result2)) {
				$result3 = dbquery("UPDATE ".$this->photo_db." SET photo_order='$k' WHERE photo_id='".$data2['photo_id']."'");
				$k++;
			}
			$i++;
			$k = 1;
		}
		if ($result3 || $result2) notify($locale['470'], $locale['470a']);
		//redirect(FUSION_SELF.$aidlink);
	}

	private function refresh_order($action_type) {

		switch($action_type) {
			case 'md': // move down album + 1 order
				if (isnum($_GET['gallery_item']) && self::validate_album($_GET['gallery_item'])) {
					if ($_GET['order'] <= $this->album_max_order && $_GET['order'] > 1) {
						dbquery("UPDATE ".$this->photo_cat_db." SET album_order = album_order-1 WHERE album_order = '".$_GET['order']."'" );
						dbquery("UPDATE ".$this->photo_cat_db." SET album_order= '".$_GET['order']."' WHERE album_id =' ".$_GET['gallery_item']."'");
					}
				}
				break;
			case 'mu': // move up album -1
				if (isnum($_GET['gallery_item']) && self::validate_album($_GET['gallery_item'])) {
					if ($_GET['order'] < $this->album_max_order && $_GET['order'] >= 1) {
						dbquery("UPDATE ".$this->photo_cat_db." SET album_order = album_order+1 WHERE album_order = '".$_GET['order']."'" );
						dbquery("UPDATE ".$this->photo_cat_db." SET album_order= '".$_GET['order']."' WHERE album_id =' ".$_GET['gallery_item']."'");
					}
				}
				break;
			case 'mdp': // move down photo +1
				if (isnum($_GET['gallery_item']) && self::validate_photo($_GET['gallery_item'])) {
					if ($_GET['order'] <= $this->photo_max_order && $_GET['order'] > 1) {
						dbquery("UPDATE ".$this->photo_db." SET photo_order = photo_order-1 WHERE photo_order = '".$_GET['order']."'" );
						dbquery("UPDATE ".$this->photo_db." SET photo_order= '".$_GET['order']."' WHERE photo_id =' ".$_GET['gallery_item']."'");
					}
				}
				break;
			case 'mup': // move up photo -1
				if (isnum($_GET['gallery_item']) && self::validate_photo($_GET['gallery_item'])) {
					if ($_GET['order'] < $this->photo_max_order && $_GET['order'] >= 1) {
						dbquery("UPDATE ".$this->photo_db." SET photo_order = photo_order+1 WHERE photo_order = '".$_GET['order']."'" );
						dbquery("UPDATE ".$this->photo_db." SET photo_order= '".$_GET['order']."' WHERE photo_id =' ".$_GET['gallery_item']."'");
					}
				}
				break;
		}
	}


	/**
	 * Main Gallery HTML output
	 */
	private function display_gallery() {
		global $locale;
		self::gallery_css();
		self::display_photo(FALSE);
		/**
		 * Breadcrumb
		 */
		if ($_GET['gallery']>0) {

			$gallery_info = self::get_album($_GET['gallery']);
			add_to_breadcrumbs(array('link' => clean_request("gallery=".$_GET['gallery'], array('gallery',	'action'), FALSE, '&amp;'), 'title' => $gallery_info['album_title']));
		}
		$list = array();
		$rows = isset($_GET['gallery']) && isnum($_GET['gallery']) ? dbcount("('photo_id')", $this->photo_db, "album_id='".intval($_GET['gallery'])."'") : dbcount("('album_id')", $this->photo_cat_db);
		$multiplier = $rows > $this->albums_per_page ? $this->albums_per_page : $rows;
		$max_items_per_col = $multiplier/$this->gallery_rows;
		// get in current language.
		if ($rows) {
			if ($_GET['gallery'] >0 ) { // view photos
				$result = dbquery("SELECT photos.*, photos.photo_user as user_id, album.*, album.album_id as gallery_id, album.album_thumb, u.user_name, u.user_status, u.user_avatar,
				count(comment_id) as comment_count, sum(rating_vote) as total_votes, count(rating_id) as rating_count
				FROM ".$this->photo_db." photos
				INNER JOIN ".$this->photo_cat_db." album on photos.album_id = album.album_id
				INNER JOIN ".DB_USERS." u on u.user_id = photos.photo_user
				LEFT JOIN ".DB_COMMENTS." comment on comment.comment_item_id= photos.photo_id AND comment_type = '".$this->gallery_rights."'
				LEFT JOIN ".DB_RATINGS." rating on rating.rating_item_id = photos.photo_id AND rating_type = '".$this->gallery_rights."'
				WHERE ".groupaccess('album.album_access')." AND photos.album_id = '".intval($_GET['gallery'])."'
				GROUP BY photo_id
				ORDER BY photos.photo_order ASC, photos.photo_datestamp DESC LIMIT ".$this->rowstart.", $this->albums_per_page");
			} else { // view album
				$result = dbquery("SELECT album.*, album.album_user as user_id, u.user_name, u.user_status, u.user_avatar, count(photo.photo_id) as photo_count
				FROM ".$this->photo_cat_db." album
				LEFT JOIN ".$this->photo_db." photo on (photo.album_id = album.album_id)
				INNER JOIN ".DB_USERS." u on u.user_id=album.album_user
				WHERE ".groupaccess('album.album_access')."
				GROUP BY album_id ORDER BY album.album_order ASC, album.album_datestamp DESC LIMIT ".$this->rowstart.", $this->albums_per_page");
			}
			$current_rows = dbrows($result);
			if ($current_rows > 0) {
				$i = 1;
				$count = 1;
				$list = array();
				while ($data = dbarray($result)) {
					$this->gallery_data[] = $data;
					self::refresh_album_thumb($data['album_id'], $data['album_thumb']);
					$list[$i][$data['album_id']] = $data;
					if ($count >= $max_items_per_col) {
						$i++;
						$count = 1;
					}
					$count++;
				}
			}
		}
		$albums = $list;
		$container_span_sm = 24/$this->gallery_rows;
		$container_span_md = 12/$this->gallery_rows;
		echo "<h4>".(isset($_GET['gallery']) ? $locale['photo_001'] : $locale['photo_000'])."</h4>\n";
		if (!empty($albums)) {
			?>
			<div class='row'>
				<?php for ($i = 1; $i <= $this->gallery_rows; $i++) { // construct columns ?>
					<div
						class='col-xs-12 col-sm-<?php echo $container_span_sm ?> col-md-<?php echo $container_span_md ?>'>
						<?php
						if (!empty($albums[$i])) {
							foreach ($albums[$i] as $albumData) {
								$lang = explode('.', $albumData['album_language']);
								if (in_array(LANGUAGE, $lang)) {
									self::gallery_album($albumData, isset($_GET['gallery']) && isnum($_GET['gallery']) ? 2 : 1);
								}
							}
						}
						?>
					</div>
				<?php } ?>
			</div>
		<?php
			// pagination
			echo makepagenav($this->rowstart, $current_rows, $rows, 3, clean_request('', array(
				'gallery_edit',
				'gallery_item',
				'action',
				'order',
				'gallery_edit',
				'gallery_delete',
				'gallery_type'
			), false));
		} else {
			echo "<div class='well text-center'>".$locale['660']."</div>";
		}
	}


	/**
	 * @param array $data
	 * @param int   $type - 1 for album, 2 for photo
	 */
	private function gallery_album(array $data = array(), $type = 1) {
		global $userdata, $locale;
		$request = $type == 1 ? clean_request("gallery=".$data['album_id'], array('gallery',
			'gallery_edit',
			'gallery_type',
			'action',
			'gallery_item',
			'ratings'), FALSE) : clean_request('photo='.$data['photo_id'], array('photo',
			'gallery_edit',
			'gallery_type',
			'status',
			'action',
			'gallery_item',
			'ratings'), FALSE);
		$order_btns = '';
		if ($type == 1 && count($this->gallery_data)>1) {
			// move up and down
			$move_up = clean_request("gallery_item=".$data['album_id']."&action=mu&order=".($data['album_order']-1), array('photo', 'status', 'action', 'gallery', 'gallery_edit', 'gallery_type', 'ratings', 'gallery_item'), FALSE, '&amp;');
			$move_down = clean_request("gallery_item=".$data['album_id']."&action=md&order=".($data['album_order']+1), array('photo', 'status', 'action', 'gallery', 'gallery_edit', 'gallery_type', 'ratings', 'gallery_item'), FALSE, '&amp;');
			if ($data['album_order'] == 1) {
				$order_btns = "<a class='btn button btn-sm btn-default' href='".$move_down."'><i class='fa fa-arrow-down'></i></a>";
			} elseif ($data['album_order'] == count($this->gallery_data)) {
				$order_btns = "<a class='btn button btn-sm btn-default' href='".$move_up."'><i class='fa fa-arrow-up'></i></a>";
			} else {
				$order_btns = "<a class='btn button btn-sm btn-default' href='".$move_down."'><i class='fa fa-arrow-down'></i></a>";
				$order_btns .= "<a class='btn button btn-sm btn-default' href='".$move_up."'><i class='fa fa-arrow-up'></i></a>";
			}
		} elseif ($type == 2 && count($this->gallery_data)>1) {
			// move up and down
			$move_up = clean_request("gallery=".$_GET['gallery']."&gallery_item=".$data['photo_id']."&action=mup&order=".($data['photo_order']-1), array('photo', 'status', 'action', 'gallery', 'gallery_edit', 'gallery_type', 'ratings', 'gallery_item'), FALSE, '&amp;');
			$move_down = clean_request("gallery=".$_GET['gallery']."&gallery_item=".$data['photo_id']."&action=mdp&order=".($data['photo_order']+1), array('photo', 'status', 'action', 'gallery', 'gallery_edit', 'gallery_type', 'ratings', 'gallery_item'), FALSE, '&amp;');
			if ($data['photo_order'] == 1) {
				$order_btns = "<a class='btn button btn-sm btn-default' href='".$move_down."'><i class='fa fa-arrow-down'></i></a>";
			} elseif ($data['photo_order'] == count($this->gallery_data)) {
				$order_btns = "<a class='btn button btn-sm btn-default' href='".$move_up."'><i class='fa fa-arrow-up'></i></a>";
			} else {
				$order_btns = "<a class='btn button btn-sm btn-default' href='".$move_down."'><i class='fa fa-arrow-down'></i></a>";
				$order_btns .= "<a class='btn button btn-sm btn-default' href='".$move_up."'><i class='fa fa-arrow-up'></i></a>";
			}
		}

		?>

		<div class='gallery_album panel panel-default'>
		<div class='gallery_actions'>
			<a href='<?php echo $request ?>' class='gallery_overlay'></a>
			<div class='gallery_buttons btn-group'>
			<?php
			echo $order_btns;
			if (($this->enable_comments || $this->enable_ratings) && $type == 2) {
				if ($data['photo_allow_ratings']) {
					$rating_link = clean_request("gallery=".$data['album_id']."&photo=".$data['photo_id']."&ratings=".$data['photo_id'], array('photo',
						'status',
						'action',
						'gallery',
						'gallery_edit',
						'gallery_type',
						'ratings'), FALSE, '&amp;');
					echo "<a class='btn button btn-sm btn-default' href='".$rating_link."'>
						<i class='fa fa-star-o'></i>
						</a>";
				}
			}
			?>
			</div>
				<div class='gallery_writer pull-right'>
					<a class='btn button btn-sm btn-default'
					   href='<?php echo clean_request((isset($_GET['gallery']) ? "gallery=".$_GET['gallery']."&" : '')."gallery_edit=".($type == 1 ? $data['album_id'] : $data['photo_id'])."&gallery_type=$type", array('gallery_edit', 'photo', 'gallery',
						   'gallery_type'), FALSE, '&amp;') ?>'>
					<i class='fa fa-pencil fa-lg'></i>
					</a>
					<a class='btn button btn-sm btn-danger'
					   href='<?php echo clean_request((isset($_GET['gallery']) ? "gallery=".$_GET['gallery']."&" : '')."gallery_delete=".($type == 1 ? $data['album_id'] : $data['photo_id'])."&gallery_type=$type", array('gallery_delete', 'photo', 'gallery',
						   'gallery_type'), FALSE, '&amp;') ?>'>
					<i class='fa fa-trash fa-lg'></i>
					</a>
				</div>
				<div class='image_container'>
					<?php if ($type == 1) {
						$img_src = self::get_album_image($data['album_id'], $this->upload_settings['thumbnail_folder'], $data['album_thumb'], '', '');
						$img_src = file_exists($img_src) && !is_dir($img_src) ? $img_src : 'holder.js/170x170/grey/text:'.$locale['na'];
						echo "<img class='img-responsive' src='".$img_src."' alt='".$data['album_title']."'/>";
					} elseif ($type == 2) {
						$img_src = self::get_album_image($data['album_id'], $this->upload_settings['thumbnail_folder'], $data['photo_filename'], $data['photo_thumb1'], $data['photo_thumb2']);
						$img_src = file_exists($img_src) && !is_dir($img_src) ? $img_src : 'holder.js/170x170/grey/text:'.$locale['na'];
						echo "<img src='".$img_src."' alt='".$data['photo_title']."'/>";
					} ?>
				</div>
			</div>
			<div class='panel-body'>
				<?php
				if ($type == '1') {
					echo "
					<span class='gallery_title strong'>".$data['album_title']."</span>
					<br/>
					<i class='fa fa-camera retro mid-opacity'></i> ".format_word($data['photo_count'], $locale['fmt_photo'])."
					</div>
					";
				} elseif ($type == '2') {
					//  = 5
					$rating = $data['rating_count'] > 0 ? floor(($data['total_votes'] * 2)/$data['rating_count']) : 0;
					echo "
					<span class='gallery_title strong'>".$data['photo_title']."</span>
					<br/>
					<span class='text-lighter'>
						<span class='m-r-10'><i class='fa fa-comment'></i> ".format_word($data['comment_count'], $locale['fmt_comment'])."</span>
						<span class='m-r-10'><i class='fa fa-star'></i>".$rating."/10</span>
					</span>
					</div>
					";
					echo "
					<div class='panel-footer text-smaller clearfix'>
					<div class='pull-left m-r-5'>".display_avatar($data, '30px', '', '', 'img-rounded')."</div>
					<div class='gallery_profile_link overflow-hide text-lighter'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'], 'strong')."
					<span class='text-lighter display-block'>".timer($type == 1 ? $data['album_datestamp'] : $data['photo_datestamp'])."</span>
					</div>
					</div>";
				}
				?>
		</div>
	<?php
	}

	/**
	 * CSS injection - will port to css file.
	 */
	private function gallery_css() {
		add_to_head("
		<style>
		#postrating, #removerating {
			margin-bottom:25px;
		}
		.gallery_album {
			-webkit-border-radius: 6px;
			-moz-border-radius: 6px;
			border-radius: 6px;
		}
		.gallery_album > .gallery_actions > .image_container > img {
			width: 100% !important;
		}
		.gallery_album > .gallery_actions > .gallery_overlay {
			background-color: rgb(0, 0, 0);
			border-radius: 6px 6px 0px 0px;
			position: absolute;
			opacity: 0;
			width:100%;
			height:100%;
			transition: opacity 0.04s linear 0s;
			cursor: zoom-in;
		}
		.gallery_album > .gallery_actions {
			position: relative;
			bottom: 0px;
			left: 0px;
			right: 0px;
			top: 0px;
			overflow: hidden;
			-webkit-border-radius: 6px 6px 0 0;
			-moz-border-radius: 6px 6px 0 0;
			border-radius: 6px 6px 0 0;
		}
		.gallery_album > .gallery_actions:hover {
			opacity: 1;
		}
		 .gallery_album > .gallery_actions:hover > .gallery_overlay {
			opacity: 0.25;
		}
		.gallery_album > .gallery_actions > .gallery_buttons {
			position: absolute;
			top: 8px;
			left: 8px;
			opacity: 0;
		}
		.gallery_album > .gallery_actions > .gallery_writer {
			position: absolute;
			right: 8px;
			top: 8px;
			opacity: 0;
		}
		.gallery_album > .gallery_actions:hover > .gallery_buttons, .gallery_album > .gallery_actions:hover > .gallery_writer {
			opacity: 1;
		}
		.gallery_album .gallery_profile_link {
			line-height: 115%;
		}
		</style>
		");
	}

}
