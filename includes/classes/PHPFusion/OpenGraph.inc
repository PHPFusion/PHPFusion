<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: OpenGraph.inc
| Author: Chubatyj Vitalij (Rizado)
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

class OpenGraph {
	private $data = array(
		'title' => '',
		'description' => '',
		'url' => '',
		'keywords' => '',
		'image' => '',
		'site_name' => '',
		'type' => 'website'
	);

	private static $ogAdded = false;

	public function ogCustomPage($pageid = 0) {
		$settings = fusion_get_settings();

		$info = array();
		$result = dbquery("SELECT `page_content`, `page_keywords`, `page_title` FROM `".DB_CUSTOM_PAGES."` WHERE `page_id` = '".intval($pageid)."' LIMIT 1");
		if (dbrows($result)) {
			$data = dbarray($result);
			$info['url'] = $settings['siteurl'].'viewpage.php?page_id='.$pageid;
			$info['keywords'] = $data['page_keywords'];
			$info['image'] = $settings['siteurl'].'images/favicons/mstile-144x144.png';
			$info['title'] = $data['page_title'];
			$info['description'] = fusion_first_words(strip_tags($data['page_content']), 50);
		}

		$this->setValues($info);
	}

	public function ogUserProfile($userid = 0) {
		$settings = fusion_get_settings();

		$info = array();
		$result = dbquery("SELECT * FROM `".DB_USERS."` WHERE `user_id` = '".intval($userid)."' LIMIT 1");
		// I know that is not good idea, but some user fields may be disabled... See next code
		if (dbrows($result)) {
			$data = dbarray($result);
			$info['url'] = $settings['siteurl'].'profile.php?lookup='.$userid;
			$info['keywords'] = $settings['keywords'];
			$realname = "";
			if (isset($data['user_name_first']) && trim($data['user_name_first'])) {
				$realname .= trim($data['user_name_first']);
			}

			if (isset($data['user_name_last']) && trim($data['user_name_last'])) {
				$realname .= " ".trim($data['user_name_last']);
			}

			if (trim($realname)) {
				$data['user_name'] .= " (".$realname.")";
			}

			$info['title'] = $data['user_name'];
			if (isset($data['user_avatar']) && trim($data['user_avatar'])) {
				$info['image'] = $settings['siteurl'].'images/avatars/'.$data['user_avatar'];
			}

			$info['description'] = $settings['description'];
		}

		$this->setValues($info);
	}

	public function ogDefault() {
		$this->setValues();
	}

	private function setValues($values = array()) {
		$settings = fusion_get_settings();

		if (!$this::$ogAdded) {

			foreach ($values as $key => $value) {
				$this->data[$key] = trim($value);
			}

			$this->data['site_name'] = $settings['sitename'];
			if ($values['title'] != '' && $values['description'] != '' && $values['url'] != '' && $values['keywords'] != '') {
			} else {
				$this->setDefaults();
			}

			$this->addToHead();
			$this::$ogAdded = true;
		}
	}

	private function setDefaults() {
		$settings = fusion_get_settings();

		$this->data = array(
			'title' => $settings['sitename'],
			'description' => $settings['description'],
			'url' => $settings['siteurl'],
			'keywords' => $settings['keywords'],
			'image' => $settings['siteurl'].'images/favicons/mstile-144x144.png',
			'site_name' => $settings['sitename'],
			'type' => 'website'
		);
	}

	private function addToHead() {
		foreach ($this->data as $key => $value) {
			if ($this->data != '') {
				add_to_head('<meta property="og:'.$key.'" content="'.$value.'" />');
			}
		}
	}
}
