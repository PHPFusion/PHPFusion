<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_forum.php
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
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
pageAccess('S3');

add_to_breadcrumbs(array('link'=>ADMIN.'settings_forum.php'.$aidlink, 'title'=>$locale['forum_settings']));
if (isset($_GET['action']) && $_GET['action'] == "count_posts") {
	$result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." GROUP BY post_author");
	if (dbrows($result)) {
		while ($data = dbarray($result)) {
			$result2 = dbquery("UPDATE ".DB_USERS." SET user_posts='".$data['num_posts']."' WHERE user_id='".$data['post_author']."'");
		}
	}
}

if (isset($_POST['savesettings'])) {
	$admin_password = (isset($_POST['admin_password'])) ? form_sanitizer($_POST['admin_password'], '', 'admin_password') : '';
	if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "") && !defined('FUSION_NULL')) {
		$numofthreads = form_sanitizer($_POST['numofthreads'], 5, 'numofthreads');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$numofthreads' WHERE settings_name='numofthreads'") : '';
		$forum_ips = form_sanitizer($_POST['forum_ips'], 103, 'forum_ips');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['forum_ips']) ? $_POST['forum_ips'] : "103")."' WHERE settings_name='forum_ips'") : '';
		$attachmax = form_sanitizer($_POST['calc_b'], 2, 'calc_b')*form_sanitizer($_POST['calc_c'], 1000000, 'calc_c');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$attachmax' WHERE settings_name='attachmax'") : '';
		$attachmax_count = form_sanitizer($_POST['attachmax_count'], 5, 'attachmax_count');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$attachmax_count' WHERE settings_name='attachmax_count'") : '';
		$attachtypes = form_sanitizer($_POST['attachtypes'], '', 'attachtypes');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$attachtypes' WHERE settings_name='attachtypes'") : '';
		$thread_notify = form_sanitizer($_POST['thread_notify'], '', 'thread_notify');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$thread_notify' WHERE settings_name='thread_notify'") : '';
		$forum_ranks = form_sanitizer($_POST['forum_ranks'], '0', 'forum_ranks');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$forum_ranks' WHERE settings_name='forum_ranks'") : '';
		$forum_edit_lock = form_sanitizer($_POST['forum_edit_lock'], '0', 'forum_edit_lock');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$forum_edit_lock' WHERE settings_name='forum_edit_lock'") : '';
		$forum_edit_timelimit = form_sanitizer($_POST['forum_edit_timelimit'], '0', 'forum_edit_timelimit');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$forum_edit_timelimit' WHERE settings_name='forum_edit_timelimit'") : '';
		$popular_threads_timeframe = form_sanitizer($_POST['popular_threads_timeframe'], '604800', 'popular_threads_timeframe');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$popular_threads_timeframe' WHERE settings_name='popular_threads_timeframe'") : '';
		$forum_last_posts_reply = form_sanitizer($_POST['forum_last_posts_reply'], '0', 'forum_last_posts_reply');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$forum_last_posts_reply' WHERE settings_name='forum_last_posts_reply'") : '';
		$forum_last_post_avatar = form_sanitizer($_POST['forum_last_post_avatar'], '0', 'forum_last_post_avatar');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$forum_last_post_avatar' WHERE settings_name='forum_last_post_avatar'") : '';
		$forum_editpost_to_lastpost = form_sanitizer($_POST['forum_editpost_to_lastpost'], '0', 'forum_editpost_to_lastpost');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$forum_editpost_to_lastpost' WHERE settings_name='forum_editpost_to_lastpost'") : '';
		addNotice('success', $locale['900']);
		redirect(FUSION_SELF.$aidlink);
	}
}

$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}

/**
 * Options for dropdown field
 */
$yes_no_array = array('1' => $locale['yes'], '0' => $locale['no']);
$num_opts = range(1,30);

opentable($locale['forum_settings']);
echo "<div class='well'>".$locale['forum_description']."</div>";

echo openform('settingsform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-8'>\n";
openside('');
echo "<span class='small pull-right'>* ".$locale['506']."</span><br/>\n";
echo form_select('numofthreads', $locale['505'], $num_opts, $settings2['numofthreads'], array('error_text' => $locale['error_value'], 'inline' => 1));
closeside();
openside('');
echo form_select('thread_notify', $locale['512'], $yes_no_array, $settings2['thread_notify'], array('error_text' => $locale['error_value'], 'inline' => 1));
closeside();
openside('');
echo "<span class='pull-right position-absolute small' style='right:30px;'>".$locale['537']."</span>\n";
echo form_select('forum_edit_timelimit', $locale['536'], array('0','10','30','45','60'), $settings2['forum_edit_timelimit'], array('max_length' => 2, 'width' => '100px', 'required' => 1, 'error_text' => $locale['error_value'], 'inline' => 1));
echo form_select('forum_ips', $locale['507'], $yes_no_array, $settings2['forum_ips'], array('error_text' => $locale['error_value'], 'inline' => 1));
echo form_select('forum_ranks', $locale['520'], $yes_no_array, $settings2['forum_ranks'], array('error_text' => $locale['error_value'], 'inline' => 1));
echo form_select('forum_last_post_avatar', $locale['539'], $yes_no_array, $settings2['forum_last_post_avatar'], array('error_text' => $locale['error_value'], 'inline' => 1));
echo form_select('forum_edit_lock', $locale['521'], $yes_no_array, $settings2['forum_edit_lock'], array('error_text' => $locale['error_value'], 'inline' => 1));
echo form_select('forum_editpost_to_lastpost', $locale['538'], $yes_no_array, $settings2['forum_editpost_to_lastpost'], array('error_text' => $locale['error_value'], 'inline' => 1));
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-4'>\n";
openside('');
$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
$calc_c = calculate_byte($settings2['attachmax']);
$calc_b = $settings2['attachmax']/$calc_c;
require_once INCLUDES."mimetypes_include.php";
$mime = mimeTypes();
$mime_opts = array();
foreach ($mime as $m => $Mime) {
	$ext = ".$m";
	$mime_opts[$ext] = $ext;
}

echo "<div class='clearfix'>\n";
	echo "<span class='pull-right small'>".$locale['509']."</span>";
	echo "<label for='calc_c'>".$locale['508']."</label><br />\n";
	echo form_text('calc_b', '', $calc_b, array('required' => 1, 'number' => 1, 'error_text' => $locale['error_rate'], 'width' => '100px', 'max_length' => '3', 'class' => 'm-r-10 pull-left'));
	echo form_select('calc_c', '', $calc_opts, $calc_c, array('placeholder' => $locale['choose'], 'class' => 'pull-left', 'width' => '100%'));
echo "</div>\n";
echo "<div class='clearfix'>\n";
	echo "<span class='small pull-right'>".$locale['535']."</span>\n";
	echo "<label for='attachmax_count'>".$locale['534']."</label>\n";
	echo form_select('attachmax_count', '', range(1, 10), $settings2['attachmax_count'], array('error_text' => $locale['error_value'], 'width'=>'100%'));
echo "</div>\n";
echo "<div class='clearfix'>\n";
	echo "<span class='small pull-right'>".$locale['511']."</span>\n";
	echo "<label for='attachtypes'>".$locale['510']."</label>";
	echo form_select('attachtypes[]', '', $mime_opts, $settings2['attachtypes'], array('input_id'=>'attachtypes', 'error_text' => $locale['error_type'], 'placeholder' => $locale['choose'], 'multiple' => 1, 'width' => '100%' , 'delimiter' => '|'));
echo "</div>\n";
closeside();

openside('');
$timeframe_opts = array('604800' => $locale['527'], '2419200' => $locale['528'], '31557600' => $locale['529'], '0' => $locale['530']);
$array_opts = array('0' => $locale['519'], '1' => $locale['533']);
for ($i = 2; $i <= 20; $i++) {
	$array_opts[$i] = sprintf($locale['532'], $i);
}
if (isset($_GET['action']) && $_GET['action'] == "count_posts") {
	echo form_alert($locale['524'], '', array('class'=>'warning'));
}

echo "<div class='clearfix'>\n";
	echo form_select('popular_threads_timeframe', $locale['525'], $timeframe_opts, $settings2['popular_threads_timeframe'], array('error_text' => $locale['error_value'], 'width'=>'100%'));
echo "</div>\n";
echo "<div class='clearfix'>\n";
	echo form_select('forum_last_posts_reply', $locale['531'], $array_opts, $settings2['forum_last_posts_reply'], array('error_text' => $locale['error_value'], 'width'=>'100%'));
echo "</div>\n";
echo "<a class='btn btn-sm btn-primary btn-block' href='".FUSION_SELF.$aidlink."&amp;action=count_posts'>".$locale['523']."</a>";
closeside();
echo "</div>\n";
echo "</div>\n";


echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success'));

echo closeform();
closetable();

require_once THEMES."templates/footer.php";

function calculate_byte($download_max_b) {
	$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
	foreach ($calc_opts as $byte => $val) {
		if ($download_max_b/$byte <= 999) {
			return $byte;
		}
	}
	return 1000000;
}