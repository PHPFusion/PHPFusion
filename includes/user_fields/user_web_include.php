<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_web_include.php
| Author: Digitanium
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
// Display user field input
if ($profile_method == "input") {
	$options = array('inline'=>1, 'max_width'=>'200px');
	//$field_value = str_replace('http://', '', $field_value);
	//$field_value = str_replace('https://', '', $field_value);
	$user_fields = form_text('user_web', $options['show_title'] ? $locale['uf_web'] : '', $field_value, $options);
	// Display in profile
} elseif ($profile_method == "display") {
	$user_fields = array('title'=>$locale['uf_web'], 'value'=>(fusion_get_settings('index_url_userweb') ? "" : "<!--noindex-->")."<a href='".$field_value."' title='".$field_value."' ".(fusion_get_settings('index_url_userweb') ? "" : "rel='nofollow' ")."target='_blank'>".$locale['uf_web_001']."</a>".(fusion_get_settings('index_url_userweb') ? "" : "<!--/noindex-->"));
}
