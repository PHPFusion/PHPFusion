<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: Downloads.php
| Author: Frederick MC Chan (hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Downloads;
if (!defined("IN_FUSION")) { die("Access Denied"); }

class Functions {
	/**
	 * Download Category Hierarchy Full Data
	 * @return array
	 */
	public static function get_downloadCats() {
		return dbquery_tree_full(DB_DOWNLOAD_CATS, 'download_cat_id', 'download_cat_parent', (multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")."");
	}
	/**
	 * Get Single Download Category Data
	 * @param $id
	 * @return array|bool
	 */
	public static function get_downloadCatData($id) {
		if (self::validate_downloadCat($id)) {
			return dbarray(dbquery("SELECT * FROM ".DB_DOWNLOAD_CATS." WHERE download_cat_id='".intval($id)."'"));
		}
		return false;
	}

	/**
	 * Get Download Category Hierarchy Index
	 * @return array
	 */
	public static function get_downloadCatsIndex() {
		return dbquery_tree(DB_DOWNLOAD_CATS, 'download_cat_id', 'download_cat_parent');
	}

	/**
	 * Validate Download Cat
	 * @param $id
	 * @return bool|string
	 */
	public static function validate_downloadCat($id) {
		if (is_numeric($id)) {
			if ($id < 1) {
				return 1;
			} else {
				return dbcount("('download_cat_id')", DB_DOWNLOAD_CATS, "download_cat_id='".intval($id)."'");
			}
		}
		return false;
	}

	/**
	 * Format Blog Category Listing
	 * @return array
	 */
	public static function get_downloadCatsData() {
		global $locale;
		$data = dbquery_tree_full(DB_DOWNLOAD_CATS, 'download_cat_id', 'download_cat_parent', "".(multilang_table("BL") ? "WHERE download_cat_language='".LANGUAGE."'" : '')."");
		foreach($data as $index => $cat_data) {
			foreach($cat_data as $download_cat_id => $cat) {
				$data[$index][$download_cat_id]['download_cat_link'] = "<a href='".BASEDIR."downloads.php?cat_id=".$cat['download_cat_id']."'>".$cat['download_cat_name']."</a>";
			}
		}
		return $data;
	}


	/**
	 * Validate Download
	 * @param $id
	 * @return bool|string
	 */
	public static function validate_download($id) {
		if (isnum($id)) {
			return (int) dbcount("('download_id')", DB_DOWNLOADS, "download_id='".intval($id)."'");
		}
		return (int) false;
	}


	/**
	 * Get the best available paths for image and thumbnail
	 * @param      $blog_image
	 * @param      $blog_image_t1
	 * @param      $blog_image_t2
	 * @param bool $hiRes -- true for image, false for thumb
	 * @return bool|string
	 */
	public static function get_download_image_path($download_image, $download_image_thumb, $hiRes = false) {
		if (!$hiRes) {
			if ($download_image_thumb && file_exists(DOWNLOADS.'images/thumbs/'.$download_image_thumb)) return DOWNLOADS.'images/thumbs/'.$download_image_thumb;
			if ($download_image_thumb && file_exists(DOWNLOADS.'images/thumbs/'.$download_image_thumb)) return DOWNLOADS.'images/thumbs/'.$download_image_thumb;
			if ($download_image && file_exists(DOWNLOADS.'images/'.$download_image)) return DOWNLOADS.'images/'.$download_image;
		} else {
			if ($download_image && file_exists(DOWNLOADS.'images/'.$download_image)) return DOWNLOADS.'images/'.$download_image;
			if ($download_image_thumb && file_exists(DOWNLOADS.'images/'.$download_image_thumb)) return DOWNLOADS.'images/'.$download_image_thumb;
			if ($download_image_thumb && file_exists(DOWNLOADS.'images/thumbs/'.$download_image_thumb)) return DOWNLOADS.'images/thumbs/'.$download_image_thumb;
		}
		return false;
	}
}
