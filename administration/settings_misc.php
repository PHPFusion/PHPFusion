<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_misc.php
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
require_once "../maincore.php";
pageAccess('S6');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
add_breadcrumb(array('link' => ADMIN."settings_misc.php".$aidlink, 'title' => $locale['misc_settings']));

if (isset($_POST['savesettings'])) {
	$inputData = array(
		"tinymce_enabled" => form_sanitizer($_POST['tinymce_enabled'], 0, "tinymce_enabled"),
		"smtp_host" => form_sanitizer($_POST['smtp_host'], "", "smtp_host"),
		"smtp_port" => form_sanitizer($_POST['smtp_port'], "", "smtp_port"),
		"smtp_auth" => isset($_POST['smtp_auth']) && !empty($_POST['smtp_username']) && !empty($_POST['smtp_password']) ? TRUE : FALSE,
		"smtp_username" => form_sanitizer($_POST['smtp_username'], "", "smtp_username"),
		"login_method" => form_sanitizer($_POST['login_method'], 0, "login_method"),
		"thumb_compression" => form_sanitizer($_POST['thumb_compression'], 0, "thumb_compression"),
		"mime_check" => form_sanitizer($_POST['mime_check'], 0, "mime_check"),
		"guestposts" => form_sanitizer($_POST['guestposts'], 0, "guestposts"),
		"comments_enabled" => form_sanitizer($_POST['comments_enabled'], 0, "comments_enabled"),
		"comments_per_page" => form_sanitizer($_POST['comments_per_page'], 10, "comments_per_page"),
		"ratings_enabled" => form_sanitizer($_POST['ratings_enabled'], 0, "ratings_enabled"),
		"visitorcounter_enabled" => form_sanitizer($_POST['visitorcounter_enabled'], 0, "visitorcounter_enabled"),
		"rendertime_enabled" => form_sanitizer($_POST['rendertime_enabled'], 0, "rendertime_enabled"),
		"comments_sorting" => form_sanitizer($_POST['comments_sorting'], "DESC", "comments_sorting"),
		"index_url_bbcode" => form_sanitizer($_POST['index_url_bbcode'], 0, "index_url_bbcode"),
		"index_url_userweb" => form_sanitizer($_POST['index_url_userweb'], 0, "index_url_userweb"),
	);
	if (defender::safe()) {
		foreach($inputData as $settings_name => $settings_value) {
			$data = array(
				"settings_name" => $settings_name,
				"settings_value" => $settings_value
			);
			dbquery_insert(DB_SETTINGS, $data, "update", array("primary_key"=>"settings_name"));
		}
		addNotice('success', $locale['900']);
		redirect(FUSION_SELF.$aidlink);
	} else {
		addNotice('danger', $locale['901']);
	}
}
opentable($locale['misc_settings']);
echo "<div class='well'>".$locale['misc_description']."</div>";
echo openform('settingsform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-8'>\n";
openside('');
echo "<div class='pull-right m-b-10'><span class='small2'>".$locale['663']."</span></div>\n";
$choice_arr = array('1' => $locale['yes'], '0' => $locale['no']);
echo form_select('tinymce_enabled', $locale['662'], fusion_get_settings("tinymce_enabled"), array('options' => $choice_arr,
	'inline' => TRUE));
closeside();
openside('');
echo form_text('smtp_host', $locale['664']."<br/>", $settings['smtp_host'], array('max_length' => 200,
	'inline' => TRUE));
echo form_text('smtp_port', $locale['674'], $settings['smtp_port'], array('max_length' => 10, 'inline' => 1));
echo "<div class='pull-right m-b-10'><span class='small2'>".$locale['665']."</span></div>\n";
echo form_select('smtp_auth', $locale['698'], $settings['smtp_auth'], array('options' => $choice_arr,
	'inline' => TRUE));
echo form_text('smtp_username', $locale['666'], $settings['smtp_username'], array('max_length' => 100,
	'inline' => TRUE));
echo form_text('smtp_password', $locale['667'], $settings['smtp_password'], array('max_length' => 100,
	'inline' => TRUE));
closeside();
openside('');
$opts = array('0' => $locale['no'], '1' => $locale['689'], '2' => $locale['690']);
echo form_select('rendertime_enabled', $locale['688'], fusion_get_settings("rendertime_enabled"), array('options' => $opts,
	'inline' => TRUE));
closeside();
echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-4'>\n";
openside('');
$opts = array('0' => $locale['global_101'], '1' => $locale['699e'], '2' => $locale['699b']);
echo form_select('login_method', $locale['699'], fusion_get_settings("login_method"), array('options' => $opts,
	'width' => '100%'));
$gd_opts = array('gd1' => $locale['607'], 'gd2' => $locale['608']);
echo form_select('thumb_compression', $locale['606'], fusion_get_settings("thumb_compression"), array('options' => $gd_opts,
	'width' => '100%'));
echo form_select('mime_check', $locale['699f'], fusion_get_settings("mime_check"), array('options' => $choice_arr,
	'width' => '100%'));
echo form_select('guestposts', $locale['655'], fusion_get_settings("guestposts"), array('options' => $choice_arr,
	'width' => '100%'));
echo form_select('comments_enabled', $locale['671'], fusion_get_settings("comments_enabled"), array('options' => $choice_arr,
	'width' => '100%'));
echo form_text('comments_per_page', $locale['913'], fusion_get_settings("comments_per_page"), array('inline'=>0, 'required' => 0, 'error_text' => $locale['error_value'], 'number' => 1, 'width' => '250px'));
$sort_opts = array('ASC' => $locale['685'], 'DESC' => $locale['686']);
echo form_select('comments_sorting', $locale['684'], fusion_get_settings("comments_sorting"), array('options' => $sort_opts,
	'width' => '100%'));
echo form_select('comments_avatar', $locale['656'], fusion_get_settings("comments_avatar"), array('options' => $choice_arr,
	'width' => '100%'));
echo form_select('ratings_enabled', $locale['672'], fusion_get_settings("ratings_enabled"), array('options' => $choice_arr,
	'width' => '100%'));
echo form_select('visitorcounter_enabled', $locale['679'], fusion_get_settings("visitorcounter_enabled"), array('options' => $choice_arr,
	'width' => '100%'));
echo form_select('create_og_tags', $locale['1030'], fusion_get_settings("create_og_tags"), array('options' => $choice_arr,
	'width' => '100%'));
closeside();
openside('');
echo form_select('index_url_bbcode', $locale['1031'], fusion_get_settings("index_url_bbcode"), array('options' => $choice_arr,
	'width' => '100%'));
echo form_select('index_url_userweb', $locale['1032'], fusion_get_settings("index_url_userseb"), array('options' => $choice_arr,
	'width' => '100%'));
closeside();
echo "</div>\n</div>";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success'));
echo closeform();
closetable();
require_once THEMES."templates/footer.php";