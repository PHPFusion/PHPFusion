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

class AdminUI {

	private $image_upload_dir = '';

	private $photo_db = '';
	private $photo_cat_db = '';


	private $upload_settings = array(
		'thumbnail_folder'=>'',
		'thumbnail' => 1,
		'thumbnail_w' => 150,
		'thumbnail_h' => 150,
		'thumbnail_suffix' =>'_t1',
		'thumbnail2'=>1,
		'thumbnail2_w' 	=> 400,
		'thumbnail2_h' 	=> 400,
		'thumbnail2_suffix' => '_t2',
		'delete_original' => 1,
		'max_width'		=>	1800,
		'max_height'	=>	1600,
		'max_byte'		=>	1500000, // 1.5 million bytes is 1.5mb
		'multiple' => 0,
		);
	private $enable_comments = false;
	private $enable_ratings = false;
	private $allow_comments = false;
	private $allow_ratings = false;

	private $enable_album = true;

	private $albums_per_page = 30;
	private $gallery_rows = 6;
	private $photos_per_page = 30;

	private $album_id = 0;
	private $photo_id = 0;
	private $rowstart = 0;
	private $action = '';


	private $album_data = array(
		'album_id' => 0,
		'album_title' => '',
		'album_description' => '',
		'album_thumb' => '',
		'album_user' => 0,
		'album_access' => 0,
		'album_order' => 0,
		'album_datestamp'=> 0,
		'album_language' => '',
	);

	private $photo_data = array(
		'photo_id' => 0,
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
		'photo_allow_ratings' => 0,
	);

	/**
	 * Install Gallery if Table does not exist
	 */
	private function Install_Gallery() {

		if (!db_exists($this->photo_cat_db) && $this->enable_album) {
			$result = dbquery("CREATE TABLE ".$this->photo_cat_db." (
				album_id mediumint(11) unsigned not null auto_increment,
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
	 * Get Album Data
	 * Can create a filter injection
	 * @return array
	 */
	private function get_album() {
		$rows = dbcount("('album_id')", $this->photo_cat_db);
		$multiplier = $rows > $this->albums_per_page ? $this->albums_per_page : $rows;
		$max_items_per_col = $multiplier/$this->gallery_rows;
		if ($rows) {
			$result = dbquery("SELECT * FROM ".$this->photo_cat_db." ORDER BY album_datestamp DESC LIMIT ".$this->rowstart.", $this->albums_per_page");
			if (dbrows($result)>0) {
				$i = 1; $count = 1;
				$list = array();
				while($data = dbarray($result)) {
					$list[$i][$data['album_id']] = $data;
					if ($count >= $max_items_per_col) {
						$i++;
						$count = 0;
					}
					$count++;
				}
				return $list;
			}
		}
		return array();
	}

	public function boot() {
		self::Install_Gallery();
		define("SAFEMODE", @ini_get("safe_mode") ? TRUE : FALSE);
		define("GALLERY_PHOTO_DIR", $this->image_upload_dir.(!SAFEMODE ? "album_".$this->album_id."/" : ""));
		self::set_albumDB();
		self::display_album_filters();
		self::display_albums();
	}

	private function validate_album($album_id) {
		if (isnum($album_id)) {
			return dbcount("('album_id')", $this->photo_cat_db, "album_id='".intval($album_id)."'");
		}
		return false;
	}

	private function set_albumDB() {
		global $userdata;
		if (isset($_POST['upload_album'])) {
			$this->album_data =	array(
				'album_id' => isset($_POST['album_id']) ? form_sanitizer($_POST['album_id'], '', 'album_id') : 0,
				'album_title' => isset($_POST['album_title']) ? form_sanitizer($_POST['album_title'], '', 'album_title') : $this->album_data['album_title'],
				'album_description' => isset($_POST['album_description']) ? form_sanitizer($_POST['album_description'], '', 'album_description') : $this->album_data['album_description'],
				'album_user' => $userdata['user_id'],
				'album_access' => isset($_POST['album_title']) ? form_sanitizer($_POST['album_access'], 0, 'album_title') : $this->album_data['album_access'],
				'album_order' => isset($_POST['album_order']) ? form_sanitizer($_POST['album_order'], 0, 'album_order') : $this->album_data['album_order'],
				'album_datestamp'=> time(),
				'album_language' => isset($_POST['album_language']) ? form_sanitizer($_POST['album_language'], '', 'album_language') : $this->album_data['album_language'],
			);
			if (!$this->album_data['album_order']) $this->album_data['album_order'] = dbresult(dbquery("SELECT MAX(album_order) FROM ".$this->photo_cat_db." WHERE album_language='".LANGUAGE."'"), 0)+1;
			$upload_result = form_sanitizer($_FILES['album_file'], '', 'album_file');
			$photo_data = array();
			if (isset($upload_result['error']) && $upload_result['error'] !=='0') {
				// upload success
				$this->album_data['album_thumb'] = $upload_result['thumb1_name'];
				$image_name = $upload_result['image_name'];
				$thumb1_name = $upload_result['thumb1_name'];
				$thumb2_name = $upload_result['thumb2_name'];
				$photo_data = array(
					'photo_id' => 0,
					'photo_title' => $this->album_data['album_title'],
					'photo_description' => $this->album_data['album_description'],
					'photo_keywords' => $this->album_data['album_title'],
					'photo_filename' => $image_name,
					'photo_thumb1' => $thumb1_name,
					'photo_thumb2' => $thumb2_name,
					'photo_datestamp' => $this->album_data['album_datestamp'],
					'photo_user' => $userdata['user_id'],
					'photo_views' => 0,
					'photo_order' => 0,
					'photo_allow_comments' => 0,
					'photo_allow_ratings' => 0,
				);
				if (!$photo_data['photo_order']) $photo_data['photo_order'] = dbresult(dbquery("SELECT MAX(photo_order) FROM ".$this->photo_db), 0)+1;
			}

			if ($this->album_data['album_id'] && self::validate_album($this->album_data['album_id'])) {
				$result = dbquery_order($this->photo_cat_db, $this->album_data['album_order'], 'album_order', $this->album_data['album_id'], 'album_id',  false, false, 1, 'album_language', 'update');
				if ($result) {
					dbquery_insert($this->photo_cat_db, $this->album_data, 'update');
				}
			} else {
				$id = false;
				$result = dbquery_order($this->photo_cat_db, $this->album_data['album_order'], 'album_order', false, false, false, false, 1, 'album_language', 'save');
				if ($result) {
					dbquery_insert($this->photo_cat_db, $this->album_data, 'save');
					$id = dblastid();
				}
				if (!empty($photo_data) && $id) {
					$result = dbquery_order($this->photo_db, $photo_data['photo_order'], 'photo_order', false, false, false, false, false, false, 'save');
					if ($result) {
						dbquery_insert($this->photo_db, $photo_data, 'save');
					}
				}
			}
		}
	}

	private function set_photoDB() {

	}

	private function display_album_filters() {
		$list = array();
		foreach(getusergroups() as $groups) {
			$list[$groups[0]] = $groups[1];
		}
		$this->upload_settings += array('inline'=>1, 'type'=>'image');

		echo "<div class='m-t-10'>\n";
		echo form_button('Create Albums', 'add_album', 'add_album', 'add_album', array('class'=>'btn-primary btn-sm m-r-10', 'icon'=>'fa fa-image'));
		echo form_button('Add Photos', 'add_photo', 'add_photo', 'add_photo', array('class'=>'btn-sm btn-default', 'icon'=>'fa fa-camera'));
		echo "</div>\n";

		echo openmodal('add_album', 'Create Gallery Album', array('button_id'=>'add_album'));
		echo openform('albumform', 'albumform', 'post', FUSION_REQUEST, array('downtime'=>1, 'enctype'=>1));
		echo form_text('Title', 'album_title', 'album_title', '', array('placeholder'=>'Name of Gallery', 'inline'=>1));
		echo form_textarea('Description', 'album_description', 'album_description', '', array('placeholder'=>'What is your Gallery about?', 'inline'=>1));
		echo form_select('Access', 'album_access', 'album_access', $list, '', array('inline'=>1));

		echo form_fileinput('Upload Picture', 'album_file', 'album_file', $this->image_upload_dir, '', $this->upload_settings);
		echo form_select('Language', 'album_language', 'album_language', fusion_get_enabled_languages(), '', array('inline'=>1));
		echo form_button('Create Album', 'upload_album', 'upload_album', 'upload_album', array('class'=>'btn-primary'));
		echo closeform();
		echo closemodal();

		echo openmodal('add_photo', 'Upload A Photo', array('button_id'=>'add_photo'));
		echo openform('photoform', 'photoform', 'post', FUSION_REQUEST, array('downtime'=>1, 'enctype'=>1));
		echo form_text('Photo Title', 'photo_title', 'photo_title', '', array('placeholder'=>'Name of Gallery', 'inline'=>1));
		echo form_fileinput('Upload Picture', 'photo_file', 'photo_file', $this->image_upload_dir, '', $this->upload_settings);
		echo form_textarea('Photo Keywords', 'photo_keywords', 'photo_keywords', '', array('placeholder'=>'Keywords', 'inline'=>1));
		echo form_textarea('Photo Description', 'photo_description', 'photo_description', '', array('placeholder'=>'What is your Gallery about?', 'inline'=>1));
		echo form_select('Photo Access', 'photo_access', 'photo_access', $list, '', array('inline'=>1));
		echo form_button('Upload Photo', 'upload_photo', 'upload_photo', 'upload_photo', array('class'=>'btn-primary'));
		echo closeform();
		echo closemodal();

	}

	private function display_albums() {
		global $locale;
		self::album_css();
		$albums = self::get_album();
		//print_p($albums);
		$container_span = 12/$this->gallery_rows;
		?>
		<div class='row'>
			<?php for ($i=1; $i<=$this->gallery_rows; $i++) { // construct columns ?>
				<div class='col-xs-12 col-sm-<?php echo $container_span ?>'>
					<?php foreach($albums[$i] as $albumData) {
						self::album($albumData);
					}
					?>
				</div>
			<?php } ?>

		</div>
		<?php
	}

	private function album_css() {
		add_to_head("
		<style>
		.gallery_album {
			-webkit-border-radius: 6px;
			-moz-border-radius: 6px;
			border-radius: 6px;
		}
		.gallery_album > .image_container {
			display: inline-block;
			position: relative;
			cursor: zoom-in;
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

	private function album() {
		?>
		<div class='gallery_album panel panel-default'>
			<div class='gallery_actions'>
				<div class='gallery_overlay'></div>
				<div class='gallery_buttons'>
					<a class='btn button btn-sm btn-primary' href=''><i class='fa fa-star-o'></i></a>
					<a class='btn button btn-sm btn-success' href=''><i class='fa fa-comment'></i></a>
				</div>
				<div class='gallery_writer pull-right'>
					<a class='btn button btn-sm btn-default' href=''><i class='fa fa-pencil'></i></a>
				</div>
				<div class='image_container'>
					<img src='<?php echo IMAGES."a.jpg" ?>' alt=''/>
				</div>
			</div>
			<div class='panel-body'>
				<span class='gallery_title'>Eshop Catalog Page</span><br/>
						<span class='text-smaller text-lighter'>
							<span class='mid-opacity m-r-10'><i class='fa fa-comment'></i> 6</span>
							<span class='mid-opacity m-r-10'><i class='fa fa-star'></i> 6/10</span>
						</span>
			</div>
			<div class='panel-footer text-smaller clearfix'>
				<div class='pull-left m-r-5'>
					<?php global $userdata;
					echo display_avatar($userdata, '30px', '', '', 'img-rounded') ?>
				</div>
				<div class='gallery_profile_link overflow-hide text-lighter'>
					<?php echo profile_link($userdata['user_id'], $userdata['user_name'], $userdata['user_status']) ?>
					<span class='text-lighter display-block'><i class='fa fa-clock-o m-r-10'></i><?php echo showdate('shortdate', time()) ?></span>
				</div>
			</div>
		</div>
		<?php
	}


}

