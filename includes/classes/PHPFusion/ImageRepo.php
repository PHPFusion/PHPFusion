<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: ImageRepo.php
| Author: Takács Ákos (Rimelek)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;
if (!defined("IN_FUSION")) {
	die("Access Denied");
}

/**
 * A class to handle imagepaths
 */
class ImageRepo {
	/*
		The system images that are set by default for THEME and must be included are as follows.
		theme/images folder
		-	blank.gif
		-	down.gif
		-	left.gif
		-	panel_on.gif
		-	panel_off.gif
		-	right.gif
		-	pollbar.gif
		-	up.gif
		theme/forum/ folder
		- 	folder.gif
		-	folderlock.gif
		-	foldernew.gif
		-	edit.gif
		-	image_attach.png
		-	newthread.gif
		-	pm.gif
		-	pollbar.gif
		- 	profile.gif
		-	quote.gif
		-	reply.gif
		-	stickythread.gif
		-	web.gif
	*/
	// Flaws: Not having images in the theme will break the site. Even the files format are different. Developers have no options for CSS buttons.
	// If we change this now, it will break all the themes on main site repository. Only solution is to address this in a new version to force deprecate old themes.
	/**
	 *
	 * @var string[]
	 */
	/**
	 * All cached paths
	 *
	 * @var string[]
	 */
	private static $imagePaths = array();

	/**
	 * The state of the cache
	 *
	 * @var boolean
	 */
	private static $cached = FALSE;

	/**
	 * Fetch and cache all off the imagepaths
	 */
	private static function cache() {
		if (self::$cached) {
			return;
		}
		self::$cached = TRUE;
		//<editor-fold desc="imagePaths">
		self::$imagePaths = array(
			//A
			"arrow" => IMAGES."arrow.png",
			"attach" => FORUM."images/attach.png",
			//B
			"blank" => THEME."images/blank.gif",
			//C
			"calendar" => IMAGES."dl_calendar.png",
			//D
			"down" => THEME."images/down.gif",
			"download" => IMAGES."dl_download.png",
			"downloads" => IMAGES."dl_downloads1.png",
			//E
			"edit" => IMAGES."edit.png",
			//F
			"folder" => THEME."forum/folder.gif",
			"folderlock" => THEME."forum/folderlock.gif",
			"foldernew" => THEME."forum/foldernew.gif",
			"forum_edit" => THEME."forum/edit.gif",
			//G
			"go_first" => IMAGES."go_first.png",
			"go_last" => IMAGES."go_last.png",
			"go_next" => IMAGES."go_next.png",
			"go_previous" => IMAGES."go_previous.png",
			//H
			"homepage" => IMAGES."dl_homepage.png",
			"hot" => FORUM."images/hot.png",
			//I
			"info" => IMAGES."dl_info.png",
			"imagenotfound" => IMAGES."imagenotfound.jpg",
			"image_attach" => FORUM."images/image_attach.png",
			//J
			//K
			//L
			"left" => THEME."images/left.gif",
			"lastpost" => FORUM."images/lastpost.png",
			"lastpostnew" => FORUM."images/lastpostnew.png",
			//M
			//N
			"newthread" => THEME."forum/newthread.gif",
			"no" => IMAGES."no.png",
			"noavatar50" => "noavatar50.png", // will infusion get this??
			"noavatar100" => "noavatar100.png",
			"noavatar150" => "noavatar150.png",
			//O
			//P
			"panel_on" => THEME."images/panel_on.gif",
			"panel_off" => THEME."images/panel_off.gif",
			"pm" => THEME."forum/pm.gif",
			"poll_posticon" => FORUM."images/poll_posticon.gif",
			"pollbar" => THEME."images/pollbar.gif",
			"printer" => IMAGES."printer.png",
			//Q
			"quote" => THEME."forum/quote.gif",
			//R
			"reply" => THEME."forum/reply.gif",
			"right" => THEME."images/right.gif",
			//S
			"save" => IMAGES."php-save.png",
			"screenshot" => IMAGES."dl_screenshot.png",
			"star" => IMAGES."star.png",
			"statistics" => IMAGES."dl_stats.png",
			"stickythread" => THEME."forum/stickythread.gif",
			//T
			"tick" => IMAGES."tick.png",
			//U
			"up" => THEME."images/up.gif",
			//V
			//W
			"web" => THEME."forum/web.gif",
			//X
			//Y
			"yes" => IMAGES."yes.png"
			//Z
		);
		//</editor-fold>
		$installedTables = array(
			'blog' => db_exists('blog'),
			'news' => db_exists('news')
		);

		$selects = array(
			"SELECT admin_image as image, admin_rights as name, 'ac_' as prefix FROM ".DB_ADMIN
		);
		if ($installedTables['blog']) {
			$selects[] = "SELECT blog_cat_image as image, blog_cat_name as name, 'bl_' as prefix FROM ".DB_BLOG_CATS;
		}

		if ($installedTables['news']) {
			$selects[] = "SELECT news_cat_image as image, news_cat_name as name, 'nc_' as prefix FROM ".DB_NEWS_CATS;
		}

		//smiley
		foreach (cache_smileys() as $smiley) {
			self::$imagePaths["smiley_".$smiley['smiley_text']] = IMAGES."smiley/".$smiley['smiley_image'];
		}

		$union = implode(' union ', $selects);
		$result = dbquery($union);
		while ($data = dbarray($result)) {
			$image = "";
			switch ($data['prefix']) {
				case 'ac_':
					$image = file_exists(ADMIN."images/".$data['image']) ? ADMIN."images/".$data['image'] : (file_exists(INFUSIONS.$data['image']) ? INFUSIONS.$data['image'] : ADMIN."images/infusion_panel.png");
					break;
				case 'nl_':
				default :
					$image = file_exists(IMAGES_NC.$data['image']) ? IMAGES_NC.$data['image'] : IMAGES."imagenotfound.jpg";
					break;
				case 'bl_':
					$image = file_exists(IMAGES_BC.$data['image']) ? IMAGES_BC.$data['image'] : IMAGES."imagenotfound.jpg";
					break;
			}
			self::$imagePaths[$data['prefix'].$data['name']] = $image;
		}
	}

	/**
	 * Get all imagepaths
	 *
	 * @return string[]
	 */
	public static function getImagePaths() {
		self::cache();
		return self::$imagePaths;
	}

	/**
	 * Get the imagepath or the html "img" tag
	 *
	 * @param string $image The name of the image.
	 * @param string $alt "alt" attribute of the image
	 * @param string $style "style" attribute of the image
	 * @param string $title "title" attribute of the image
	 * @param string $atts Custom attributes of the image
	 * @return string The path of the image if the first argument is given,
	 * but others not. Otherwise the html "img" tag
	 */
	public static function getImage($image, $alt = "", $style = "", $title = "", $atts = "") {
		self::cache();

		$url = isset(self::$imagePaths[$image]) ? self::$imagePaths[$image] : IMAGES."not_found.gif";
		if ($style) {
			$style = " style='$style'";
		}
		if ($title) {
			$title = " title='".$title."'";
		}
		return ($alt or $style or $title or $atts)
			? "<img src='".$url."' alt='".$alt."'".$style.$title." ".$atts." />" :
			$url;
	}

	/**
	 * Set a path of an image
	 *
	 * @param string $name
	 * @param string $path
	 */
	public static function setImage($name, $path) {
		self::$imagePaths[$name] = $path;
	}

	/**
	 * Replace a part in each path
	 *
	 * @param string $source
	 * @param string $target
	 */
	public static function replaceInAllPath($source, $target) {
		self::cache();

		foreach (self::$imagePaths as $name => $path) {
			self::$imagePaths[$name] = str_replace($source, $target, $path);
		}
	}
}
