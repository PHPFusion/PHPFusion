<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: breadcrumbs.php
| Author: JoiNNN
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

class BreadCrumbs {

	private static $breadcrumbs = array();

	// Whether to add the 'Home' link
	public $show_home = TRUE;
	// Whether to make last breadcrumb a link
	public $last_no_link = TRUE;
	// The class wrapping the breacrumbs
	public $class = 'breadcrumb';


	public function __construct() {
		global $locale;
		// I can't override $show_home if I put it in here. PLEASE FIX IT
		if ($this->show_home) {
			self::addBreadCrumb(array('link' => BASEDIR.'index.php', 'title' => $locale['home'], 'class' => 'home-link crumb'));
		}
	}

	/**
	 * Add a link to the breadcrumb
	 *
	 * @param array $link Keys: link, title
	 */
	public static function addBreadCrumb(array $link) {
		$link += array(
			'title' => '',
			'link' => '',
			'class' => 'crumb'
		);
		$link['title'] = trim($link['title']);
		if (!empty($link['title'])) {
			self::$breadcrumbs[] = $link;
		}
	}

	/**
	 * Get breadcrumbs
	 *
	 * @return array Keys of elements: title, link
	 */
	public function getBreadCrumbs() {
		$count = count(self::$breadcrumbs);
		if ($this->last_no_link && $count) {
			self::$breadcrumbs[$count-1]['link'] = '';
		}

		return self::$breadcrumbs;
	}
}

?>