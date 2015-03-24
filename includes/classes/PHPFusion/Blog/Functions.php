<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Functions.php
| Author: Frederick MC CHan (Hien)
| Version: 9.00
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Blog;

/**
 * Functions for Blog System
 * Class Functions
 * @package PHPFusion\Blog
 */
class Functions {
	/**
	 * Blog Category Hierarchy Full Data
	 * @return array
	 */
	public static function get_blogCat() {
		return dbquery_tree_full(DB_BLOG_CATS, "blog_cat_id", "blog_cat_parent");
	}

	/**
	 * Get Single Blog Category Data
	 * @param $id
	 * @return array|bool
	 */
	public static function get_blogCatData($id) {
		if (self::validate_blogCat($id)) {
			return dbarray(dbquery("SELECT * FROM ".DB_BLOG_CATS." WHERE blog_cat_id='".intval($id)."'"));
		}
		return false;
	}

	/**
	 * Get Blog Category Hierarchy Index
	 * @return array
	 */
	public static function get_blogCatsIndex() {
		return dbquery_tree(DB_BLOG_CATS, 'blog_cat_id', 'blog_cat_parent');
	}

	/**
	 * Format Blog Category Listing
	 * @return array
	 */
	public static function get_blogCatsData() {
		global $locale;
		$data = dbquery_tree_full(DB_BLOG_CATS, 'blog_cat_id', 'blog_cat_parent', "".(multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."'" : '')."");
		$data[0][0] = array(
			'blog_cat_id' => 0,
			'blog_cat_parent' => 0,
			'blog_cat_name' => $locale['global_080'],
			'blog_cat_image' => '',
			'blog_cat_language' => LANGUAGE,
			'blog_cat_link' => "<a href='".BASEDIR."blog.php?cat_id=0'>".$locale['global_080']."</a>"
		);
		foreach($data as $index => $cat_data) {
			foreach($cat_data as $blog_cat_id => $cat) {
				$data[$index][$blog_cat_id]['blog_cat_link'] = "<a href='".BASEDIR."blog.php?cat_id=".$cat['blog_cat_id']."'>".$cat['blog_cat_name']."</a>";
			}
		}
		return $data;

	}

	/**
	 * Validate Blog Cat
	 * @param $id
	 * @return bool|string
	 */
	public static function validate_blogCat($id) {
		if (is_numeric($id)) {
			if ($id < 1) {
				return 1;
			} else {
				return dbcount("('blog_cat_id')", DB_BLOG_CATS, "blog_cat_id='".intval($id)."'");
			}
		}
		return false;
	}


	/**
	 * Validate blog
	 * @param $id
	 * @return bool|string
	 */
	public static function validate_blog($id) {
		if (isnum($id)) {
			return (int) dbcount("('blog_id')", DB_BLOG, "blog_id='".intval($id)."'");
		}
		return (int) false;
	}

	/**
	 * Session based blog reads updater
	 * @param $blog_id
	 */
	public static function update_blogReads($blog_id) {
		$session_id = \defender::set_sessionUserID();
		if (!isset($_SESSION['blog'][$blog_id][$session_id])) {
			$_SESSION['blog'][$blog_id][$session_id] = time();
			dbquery("UPDATE ".DB_BLOG." SET blog_reads=blog_reads+1 WHERE blog_id='".intval($blog_id)."'");
		} else {
			$days_to_keep_session = 30;
			$time = $_SESSION['blog'][$blog_id][$session_id];
			if ($time <= time()-($days_to_keep_session*3600*24)) {
				$_SESSION['blog'][$blog_id][$session_id] = time();
				dbquery("UPDATE ".DB_BLOG." SET blog_reads=blog_reads+1 WHERE blog_id='".intval($blog_id)."'");
			}
		}
	}


	/**
	 * Get the best available paths for image and thumbnail
	 * @param      $blog_image
	 * @param      $blog_image_t1
	 * @param      $blog_image_t2
	 * @param bool $hiRes -- true for image, false for thumb
	 * @return bool|string
	 */
	public static function get_blog_image_path($blog_image, $blog_image_t1, $blog_image_t2, $hiRes = false) {
		if (!$hiRes) {
			if ($blog_image_t1 && file_exists(IMAGES_B_T.$blog_image_t1)) return IMAGES_B_T.$blog_image_t1;
			if ($blog_image_t1 && file_exists(IMAGES_B.$blog_image_t1)) return IMAGES_B.$blog_image_t1;
			if ($blog_image_t2 && file_exists(IMAGES_B_T.$blog_image_t2)) return IMAGES_B_T.$blog_image_t2;
			if ($blog_image_t2 && file_exists(IMAGES_B.$blog_image_t2)) return IMAGES_B.$blog_image_t2;
			if ($blog_image && file_exists(IMAGES_B.$blog_image)) return IMAGES_B.$blog_image;
		} else {
			if ($blog_image && file_exists(IMAGES_B.$blog_image)) return IMAGES_B.$blog_image;
			if ($blog_image_t2 && file_exists(IMAGES_B.$blog_image_t2)) return IMAGES_B.$blog_image_t2;
			if ($blog_image_t2 && file_exists(IMAGES_B_T.$blog_image_t2)) return IMAGES_B_T.$blog_image_t2;
			if ($blog_image_t1 && file_exists(IMAGES_B.$blog_image_t1)) return IMAGES_B.$blog_image_t1;
			if ($blog_image_t1 && file_exists(IMAGES_B_T.$blog_image_t1)) return IMAGES_B_T.$blog_image_t1;
		}
		return false;
	}

}