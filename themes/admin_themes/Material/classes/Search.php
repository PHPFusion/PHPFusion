<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Material/classes/Search.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
/**
 * Based on Artemis Search
 */

if (!defined("IN_FUSION")) {
	die("Access Denied");
}

use PHPFusion\Admin;

class Search extends Admin {
	private $result = array(
		"data" => array(),
		"count" => 0,
		"status" => 105,
		"message" => ""
	);

	public function __construct() {
		if ($this->AuthorizeAid()) {
			if (\defender::safe()) {
				$this->SearchPages();
				$message = $this->result['message'] = self::SetLocale($this->result['status']);

				if (!empty($message)) {
					echo '<li><span>'.$message.'</span></li>';
				}

				if (!empty($this->result)) {
					$this->SetResult($this->result);
					$this->DisplayResult();
				}
			} else {
				$this->result['status'] = 101;
			}
		} else {
			$this->result['status'] = 100;
		}
	}

	private function AuthorizeAid() {
		if (isset($_GET['aid']) && iAUTH == $_GET['aid']) {
			return TRUE;
		}

		return FALSE;
	}

	private function SearchPages() {
		$admin           = new Admin();
		$available_pages = $admin->getAdminPages();
		$search_string   = $_GET['pagestring'];

		if (strlen($search_string) >= 2) {
			$pages = flatten_array($available_pages);
			$result_rows = 0;

			if (!empty($pages)) {
				foreach ($pages as $page) {
					if (stristr($page['admin_title'], $search_string) == TRUE || stristr($page['admin_link'], $search_string) == TRUE) {
						$this->result['data'][] = $page;
						$result_rows++;
					}
				}
			} else {
				$this->result['status'] = 102;
			}

			if ($result_rows > 0) {
				$this->result['count'] = $result_rows;
			} else {
				$this->result['status'] = 104;
			}
		} else {
			$this->result['status'] = 103;
		}
	}

	public function SetResult($result) {
		$this->result = $result;
	}

	public function DisplayResult() {
		$aidlink = fusion_get_aidlink();
		$uri     = pathinfo($_GET['url']);
		$count   = substr($_GET['url'], -1) == "/" ? substr_count($uri['dirname'], "/") : substr_count($uri['dirname'], "/") -1;
		$prefix  = str_repeat("../", $count);
		$infusions_count = substr($_GET['url'], 1) == "/" ? substr_count($uri['dirname'], "/") : substr_count($uri['dirname'], "/") -1;
		$infusions_prefix = str_repeat("../", $infusions_count);

		if (!empty($this->result['data'])) {
			foreach ($this->result['data'] as $data) {
				$title = $data['admin_title'];

				$link = $data['admin_link'];

				if (stristr($data['admin_link'], '/infusions/')) {
					$link = $infusions_prefix.$data['admin_link'];
				} else if (strpos($_SERVER['REQUEST_URI'], 'infusions')) {
					$link = '/administration/'.$data['admin_link'];
				} else if (empty(fusion_get_settings('site_path'))) {
					$link = $prefix.'/administration/'.$data['admin_link'];
				} else {
					$link = $prefix.$data['admin_link'];
				}

				$link = $link.$aidlink;

				if ($data['admin_page'] !== 5) {
					$title = isset($locale[$data['admin_rights']]) ? $locale[$data['admin_rights']] : $title;
				}

				if (checkrights($data['admin_rights'])) {
					echo '<li><a href="'.$link.'"><img src="'.get_image("ac_".$data['admin_rights']).'" alt="'.$title.'" class="admin-image"/>'.$title.'</a></li>';
				}
			}
		}
	}

	public function SetLocale($lc = NULL) {
		$locale = array();
		if (file_exists(MATERIAL."locale/".LANGUAGE.".php")) {
			include MATERIAL."locale/".LANGUAGE.".php";
		} else {
			include MATERIAL."locale/English.php";
		}

		return $locale['material_'.$lc];
	}
}
