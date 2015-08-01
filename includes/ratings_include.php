<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: ratings_include.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
include LOCALE.LOCALESET."ratings.php";
function showratings($rating_type, $rating_item_id, $rating_link) {
	global $locale, $userdata;
	$settings = fusion_get_settings();
	//$rating_link = $rating_link;
	if ($settings['ratings_enabled'] == "1") {
		if (iMEMBER) {
			$d_rating = dbarray(dbquery("SELECT rating_vote,rating_datestamp FROM ".DB_RATINGS." WHERE rating_item_id='".$rating_item_id."' AND rating_type='".$rating_type."' AND rating_user='".$userdata['user_id']."'"));
			if (isset($_POST['post_rating'])) {
				if (isnum($_POST['rating']) && $_POST['rating'] > 0 && $_POST['rating'] < 6 && !isset($d_rating['rating_vote'])) {
					$result = dbquery("INSERT INTO ".DB_RATINGS." (rating_item_id, rating_type, rating_user, rating_vote, rating_datestamp, rating_ip, rating_ip_type) VALUES ('$rating_item_id', '$rating_type', '".$userdata['user_id']."', '".$_POST['rating']."', '".time()."', '".USER_IP."', '".USER_IP_TYPE."')");
					if ($result) defender::unset_field_session();
				}
				if (!$settings['site_seo']) redirect($rating_link);
			} elseif (isset($_POST['remove_rating'])) {
				$result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='$rating_item_id' AND rating_type='$rating_type' AND rating_user='".$userdata['user_id']."'");
				if ($result) defender::unset_field_session();
				if (!$settings['site_seo']) redirect($rating_link);
			}
		}
		$ratings = array(5 => $locale['r120'],
			4 => $locale['r121'],
			3 => $locale['r122'],
			2 => $locale['r123'],
			1 => $locale['r124']);
		if (!iMEMBER) {
			echo "<div class='text-center'>".$locale['r104']."</div>\n";
		} elseif (isset($d_rating['rating_vote'])) {
			echo openform('removerating', 'post', $settings['site_seo'] ? FUSION_ROOT : ''.$rating_link, array('max_tokens' => 1,
				'notice' => 0,
				'class' => 'text-center'));
			echo sprintf($locale['r105'], $ratings[$d_rating['rating_vote']], showdate("longdate", $d_rating['rating_datestamp']))."<br /><br />\n";
			echo form_button('remove_rating', $locale['r102'], $locale['r102'], array('class' => 'btn-default btn-sm',
				'icon' => 'fa fa-thumbs-down m-r-10'));
			echo closeform();
		} else {
			echo openform('postrating', 'post', $settings['site_seo'] ? FUSION_ROOT : ''.$rating_link, array('max_tokens' => 1,
				'notice' => 0,
				'class' => 'm-b-20 text-center'));
			echo form_select('rating', $locale['r106'], '', array('options' => $ratings, 'width' => '100%'));
			echo form_button('post_rating', $locale['r103'], $locale['r103'], array('class' => 'btn-primary btn-sm',
				'icon' => 'fa fa-thumbs-up m-r-10'));
			echo closeform();
		}
		$rating_votes = dbarray(dbquery("
		SELECT
		SUM(IF(rating_vote='5', 1, 0)) as r120,
		SUM(IF(rating_vote='4', 1, 0)) as r121,
		SUM(IF(rating_vote='3', 1, 0)) as r122,
		SUM(IF(rating_vote='2', 1, 0)) as r123,
		SUM(IF(rating_vote='1', 1, 0)) as r124
		FROM ".DB_RATINGS." WHERE rating_type='".$rating_type."' and rating_item_id='".intval($rating_item_id)."'
		"));
		if (!empty($rating_votes)) {
			foreach ($rating_votes as $key => $num) {
				$value = format_word($num, $locale['fmt_rating']);
				echo progress_bar($value, $locale[$key], FALSE, '10px', TRUE, FALSE);
			}
		} else {
			echo "<div class='text-center'>".$locale['r101']."</div>\n";
		}
	}
}