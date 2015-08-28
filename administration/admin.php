<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: administration/admin_icons.php
| Author: Frederick MC Chan
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
* Class Admin
*/
class Admin {
/**
 * @var array
 */
public $admin_page_icons = array(
	'0'=>'fa fa-fw fa-dashboard', '1' => 'fa fa-fw fa-microphone', '2' => 'fa fa-fw fa-users', '3'=>'fa fa-fw fa-cog', '4'=> 'fa fa-fw fa-wrench', '5'=>'fa fa-fw fa-cubes'
);
// pair via admin rights - set the base here now.
/**
 * @var array
 */
public $admin_link_icons = array(
	'AC' 	=> 	'fa fa-fw fa-book', 			// articles categories
	'A' 	=> 	'fa fa-fw fa-book', 			// articles
	'BLOG' 	=> 	'fa fa-fw fa-graduation-cap', 	// blog
	'BLC' 	=> 	'fa fa-fw fa-graduation-cap', 	// blog categories
	'CP' 	=> 	'fa fa-fw fa-leaf', 			// custom page
	'DC' 	=> 	'fa fa-fw fa-cloud-download', 	// download categories
	'D' 	=> 	'fa fa-fw fa-cloud-download', 	// downloads
	'ESHP' 	=> 	'fa fa-fw fa-shopping-cart',	// eshop
	'FQ' 	=> 	'fa fa-fw fa-life-buoy',		// frequent asked questions
	'F' 	=> 	'fa fa-fw fa-comment-o',		// forum
	'IM' 	=> 	'fa fa-fw fa-picture-o',		// Images
	'N' 	=> 	'fa fa-fw fa-newspaper-o',		// news
	'NC' 	=> 	'fa fa-fw fa-newspaper-o',		// news categories
	'PM' 	=> 	'fa fa-fw fa-envelope-o',		// private message
	'PH' 	=> 	'fa fa-fw fa-camera-retro',		// photo album ?
	'PO' 	=> 	'fa fa-fw fa-bar-chart',		// Poll
	'WC'	=>	'fa fa-fw fa-sitemap',			// weblink cats
	'W'		=>	'fa fa-fw fa-sitemap',			// weblinks
	'APWR'	=>	'fa fa-fw fa-medkit',			// Admin Password Reset
	'AD'	=>	'fa fa-fw fa-user-md',			// Administrator
	'B'		=>	'fa fa-fw fa-ban',				// Blacklist
	'FR'	=>	'fa fa-fw fa-gavel',			// Forum Ranks
	'M'		=> 	'fa fa-fw fa-user',				// Members
	'MI'	=> 	'fa fa-fw fa-barcode',			// Migration tool
	'SU'	=>	'fa fa-fw fa-file-o',			// User Submissions
	'UF'	=>	'fa fa-fw fa-table',			// User Fields
	'UG'	=>	'fa fa-fw fa-users',			// user groups
	'UL'	=>	'fa fa-fw fa-coffee',			// user logs
	'SB'	=>	'fa fa-fw fa-language',			// Banners
	'BB'	=>	'fa fa-fw fa-bold',				// Bbcode
	'DB'	=>	'fa fa-fw fa-history',			// database backup
	'MAIL'	=>	'fa fa-fw fa-send',				// Email templates
	'ERRO'	=>	'fa fa-fw fa-bug',				// Error Logs
	'I'		=>	'fa fa-fw fa-cubes',			// Infusions
	'P'		=>	'fa fa-fw fa-desktop',			// Panels
	'PL'	=>	'fa fa-fw fa-puzzle-piece',		// Permalink
	'PI'	=> 	'fa fa-fw fa-info-circle',		// php Info
	'ROB'	=>	'fa fa-fw fa-android',			// robots.txt
	'SL'	=>	'fa fa-fw fa-link',				// Site Links
	'SM'	=>	'fa fa-fw fa-smile-o',			// Smileys
	'TS'	=> 	'fa fa-fw fa-magic',			// Theme
	'U'		=>	'fa fa-fw fa-database',			// Upgrade
	'LANG'	=>	'fa fa-fw fa-flag',				// Language Settings
	'S1'	=>	'fa fa-fw fa-hospital-o',		// Main Settings
	'S2'	=>	'fa fa-fw fa-clock-o',			// Time and Date
	'S3'	=> 	'fa fa-fw fa-comments-o',		// Forum Settings
	'S4'	=> 	'fa fa-fw fa-key',				// Registration Settings
	'S5'	=>	'fa fa-fw fa-camera-retro',		// Photo Gallery Settings
	'S6'	=>	'fa fa-fw fa-gears',			// Miscellaneous Settings
	'S7'	=>	'fa fa-fw fa-envelope-square',	// PM Settings
	'S8'	=>	'fa fa-fw fa-newspaper-o',		// News Settings
	'S9'	=>	'fa fa-fw fa-users',			// User Management
	'S10'	=>	'fa fa-fw fa-arrow-circle-up',	// Items Per Page
	'S11'	=>	'fa fa-fw fa-cloud-download',	// Download Settings
	'S12'	=>	'fa fa-fw fa-shield',			// Security Settings
	'S13'	=>	'fa fa-fw fa-graduation-cap',	// Blog Settings
);
/**
 * @var array
 */
private $admin_pages = array();
/**
 * @var array
 */
private $pages = array(1 => FALSE, 2 => FALSE, 3 => FALSE, 4 => FALSE, 5 => FALSE);
/**
 *	Constructor class. No Params
 */
	private $current_page = '';

public function __construct() {
	global $aidlink, $locale, $pages, $admin_pages, $settings;
	@list($title) = dbarraynum(dbquery("SELECT admin_title FROM ".DB_ADMIN." WHERE admin_link='".FUSION_SELF."'"));
	add_to_title($locale['global_200'].$locale['global_123'].($title ? $locale['global_201'].$title : ""));
	$this->admin_pages = $admin_pages;
	$this->pages = $pages;
	$this->current_page = self::_currentPage();
}

/**
 * @param $admin_rights
 * @return bool|string
 */
public function get_admin_icons($admin_rights) {
	// admin rights might not yield an icon & admin_icons override might not have the key.
	if (isset($this->admin_link_icons[$admin_rights]) && $this->admin_link_icons[$admin_rights]) {
		return "<i class='admin_ico ".$this->admin_link_icons[$admin_rights]."'></i>\n";
	}
	return false;
}

/**
 * @param $page_number
 * @return string
 */
public function get_admin_page_icons($page_number) {
	if (isset($this->admin_page_icons[$page_number]) && $this->admin_page_icons[$page_number]) {
		return "<i class='admin_ico ".$this->admin_page_icons[$page_number]."'></i>\n";
	}
}

/**
 * @return string
 */
public function vertical_admin_nav() {
	global $aidlink, $locale;
	$html = "<ul id='adl' class='admin-vertical-link'>\n";
	for ($i = 0; $i < 6; $i++) {
		$result = dbquery("SELECT * FROM ".DB_ADMIN." WHERE admin_page='".$i."' AND admin_link !='reserved' ORDER BY admin_title ASC");
		$active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && $this->_isActive() == $i) ? 1 : 0;
		$html .= "<li class='".($active ? 'active panel' : 'panel')."' >\n";
		if ($i == 0) {
			$html .= "<a class='adl-link' href='".ADMIN."index.php".$aidlink."&amp;pagenum=0'>".$this->get_admin_page_icons($i)." ".$locale['ac0'.$i]." ".($i > 0 ? "<span class='adl-drop pull-right'></span>" : '')."</a>\n";
		} else {
			$html .= "<a class='adl-link ".($active ? '' : 'collapsed')."' data-parent='#adl' data-toggle='collapse' href='#adl-$i'>".$this->get_admin_page_icons($i)." ".$locale['ac0'.$i].($i == 5 ? " (".dbrows($result).")" : "")." ".($i > 0 ? "<span class='adl-drop pull-right'></span>" : '')."</a>\n";
			$html .= "<div id='adl-$i' class='collapse ".($active ? 'in' : '')."'>\n";
			if (dbrows($result) > 0) {
				$html .= "<ul class='admin-submenu'>\n";
				while ($data = dbarray($result)) {
					$secondary_active = $data['admin_link'] == $this->current_page ? "class='active'" : '';
					$html .= checkrights($data['admin_rights']) ? "<li $secondary_active><a href='".ADMIN.$data['admin_link'].$aidlink."'> ".$this->get_admin_icons($data['admin_rights'])." ".($data['admin_page'] == 5 ? $data['admin_title'] : $locale[$data['admin_rights']])."</a></li>\n" : '';
				}
				$html .= "</ul>\n";
			}
			$html .= "</div>\n";
			$html .= "</li>\n";
		}
	}
	$html .= "</ul>\n";

	return $html;
}

/**
 * Build a return that always synchronize with the DB_ADMIN url.
 * by Hien
 */
private function _currentPage() {
	$path_info = pathinfo(START_PAGE);
	if (stristr(FUSION_REQUEST, '/administration/')) {
		$path_info = $path_info['filename'].'.php';
	} else {
		$path_info = '../'.$path_info['dirname'].'/'.$path_info['filename'].'.php';
	}
	return $path_info;
}


/**
 * @return int|string
 */
public function _isActive() {
	foreach ($this->admin_pages as $key => $data) {
		$data_link = array_flip($data);
		if (isset($data_link[$this->current_page])) {
			return $key;
		}
	}
	return '0';
}

public function horiziontal_admin_nav() {
	global $aidlink, $locale;
	$html = "<ul class='admin-horizontal-link'>\n";
	for ($i = 0; $i < 6; $i++) {
		if ($i<5 || $i==5 && dbcount("(inf_id)", DB_INFUSIONS, "")) {
			$active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && $this->_isActive() == $i) ? 1 : 0;
			$html .= "<li ".($active ? "class='active'" : '')."><a href='".ADMIN.$aidlink."&amp;pagenum=$i'>".$this->get_admin_page_icons($i)." ".$locale['ac0'.$i]."</a></li>\n";
		}
	}
	$html .= "</ul>\n";
	return $html;
}
}
