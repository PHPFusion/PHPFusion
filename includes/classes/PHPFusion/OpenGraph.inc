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
	private static $data = array(
		'title' => '',
		'description' => '',
		'url' => '',
		'keywords' => '',
		'image' => '',
		'site_name' => '',
		'type' => 'website'
	);

	private static $ogAdded = false;

	public static function ogCustomPage($pageid = 0) {
		$settings = fusion_get_settings();

		$info = array();
		$result = dbquery("SELECT `page_content`, `page_keywords`, `page_title` FROM `".DB_CUSTOM_PAGES."` WHERE `page_id` = '".intval($pageid)."' LIMIT 1");
		if (dbrows($result)) {
			$data = dbarray($result);
			$info['url'] = $settings['siteurl'].'viewpage.php?page_id='.$pageid;
			$info['keywords'] = $data['page_keywords'] ? $data['page_keywords'] : fusion_get_settings('keywords');
			$info['image'] = $settings['siteurl'].'images/favicons/mstile-150x150.png';
			$info['title'] = $data['page_title'].' - '.fusion_get_settings('sitename');
			$info['description'] = $data['page_content'] ? fusion_first_words(strip_tags($data['page_content']), 50) : $settings['description'];
		}

		OpenGraph::setValues($info);
	}

	public static function ogUserProfile($userid = 0) {
		$settings = fusion_get_settings();
		$locale = fusion_get_locale('', LOCALE.LOCALESET."user_fields.php");

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

			$info['title'] = $locale['u103'].$locale['global_201'].$data['user_name'];
			$info['image'] = $data['user_avatar'] ? $settings['siteurl'].'images/avatars/'.$data['user_avatar'] : $settings['siteurl'].'images/avatars/no-avatar.jpg';
			$info['description'] = $settings['description'];
		}

		OpenGraph::setValues($info);
	}

	public static function ogDefault() {
		OpenGraph::setValues();
	}

	protected static function setValues($values = array()) {
		$settings = fusion_get_settings();

		if (!OpenGraph::$ogAdded) {

			foreach ($values as $key => $value) {
				OpenGraph::$data[$key] = trim($value);
			}

			OpenGraph::$data['site_name'] = $settings['sitename'];
			if (!empty($values['title']) && !empty($values['description']) && !empty($values['url']) && !empty($values['keywords'])) {
				OpenGraph::$data['title'] = $values['title'];
				OpenGraph::$data['description'] = $values['description'];
				OpenGraph::$data['url'] = $values['url'];
				OpenGraph::$data['keywords'] = $values['keywords'];
				if (!empty($values['image'])) OpenGraph::$data['image'] = $values['image'];
				if (!empty($values['type'])) OpenGraph::$data['type'] = $values['type'];
			} else {
				OpenGraph::setDefaults();
			}

			OpenGraph::addToHead();
			OpenGraph::$ogAdded = true;
		}
	}

	private static function setDefaults() {
		$settings = fusion_get_settings();

		OpenGraph::$data = array(
			'title' => $settings['sitename'],
			'description' => $settings['description'],
			'url' => $settings['siteurl'],
			'keywords' => $settings['keywords'],
			'image' => $settings['siteurl'].'images/favicons/mstile-150x150.png',
			'site_name' => $settings['sitename'],
			'type' => 'website'
		);
	}

	private static function addToHead() {
		foreach (OpenGraph::$data as $key => $value) {
			if (OpenGraph::$data != '') {
				add_to_head('<meta property="og:'.$key.'" content="'.$value.'" />');
			}
		}
	}
}
