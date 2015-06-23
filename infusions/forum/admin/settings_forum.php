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
require_once __DIR__."/../../../maincore.php";
if (!db_exists(DB_FORUMS)) {
	$_GET['code'] = 404;
	require_once BASEDIR.'error.php';
	exit;
}
pageAccess('F');

//require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";

add_breadcrumb(array('link'=>ADMIN.'settings_forum.php'.$aidlink, 'title'=>$locale['forum_settings']));

if (isset($_GET['action']) && $_GET['action'] == "count_posts") {
	$result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." GROUP BY post_author");
	if (dbrows($result)) {
		while ($data = dbarray($result)) {
			$result2 = dbquery("UPDATE ".DB_USERS." SET user_posts='".$data['num_posts']."' WHERE user_id='".$data['post_author']."'");
		}
	}
}
/**
 * List of Settings Column for Forum Infusion and default values
* threads_per_page = 20
 * posts_per_page = 20
 * forum_ips - -103
 * forum_attachmax = 1mb ---- 1,000,000 bytes (one million)
 * forum_attachmax_count = 5
 * forum_attachtypes .pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z
 * thread_notify = 1
 * forum_ranks = 1
 * forum_edit_lock = 0
 * forum_edit_timelimit = 0
 * popular_threads_timeframe = 604800
 * forum_last_posts_reply = 1
 * forum_last_post_avatar = 1
 * forum_editpost_to_lastpost = 0
 */
if (isset($_POST['savesettings'])) {
	$admin_password = (isset($_POST['admin_password'])) ? form_sanitizer($_POST['admin_password'], '', 'admin_password') : '';
	if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "") && !defined('FUSION_NULL')) {
		$numofthreads = form_sanitizer($_POST['numofthreads'], 20, 'numofthreads');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$numofthreads' WHERE settings_name='threads_per_page' AND settings_inf='forum'") : '';
		$threads_num = form_sanitizer($_POST['threads_per_page'], 20, 'threads_per_page');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$threads_num' WHERE settings_name='threads_per_page' AND settings_inf='forum'") : '';
		$posts_num = form_sanitizer($_POST['posts_per_page'], 20, 'posts_per_page');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$posts_num' WHERE settings_name='posts_per_page'  AND settings_inf='forum'") : '';
		$forum_ips = form_sanitizer($_POST['forum_ips'], '-103', 'forum_ips');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='".(isnum($_POST['forum_ips']) ? $_POST['forum_ips'] : "103")."' WHERE settings_name='forum_ips' AND settings_inf='forum'") : '';
		$attachmax = form_sanitizer($_POST['calc_b'], 1, 'calc_b')*form_sanitizer($_POST['calc_c'], 1000000, 'calc_c');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$attachmax' WHERE settings_name='forum_attachmax' AND settings_inf='forum'") : '';
		$attachmax_count = form_sanitizer($_POST['forum_attachmax_count'], 5, 'forum_attachmax_count');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$attachmax_count' WHERE settings_name='forum_attachmax_count' AND settings_inf='forum'") : '';
		$attachtypes = form_sanitizer($_POST['forum_attachtypes'], '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z', 'forum_attachtypes');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$attachtypes' WHERE settings_name='forum_attachtypes' AND settings_inf='forum'") : '';
		$thread_notify = form_sanitizer($_POST['thread_notify'], '', 'thread_notify');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$thread_notify' WHERE settings_name='thread_notify' AND settings_inf='forum'") : '';
		$forum_ranks = form_sanitizer($_POST['forum_ranks'], '1', 'forum_ranks');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$forum_ranks' WHERE settings_name='forum_ranks' AND settings_inf='forum'") : '';
		$forum_edit_lock = form_sanitizer($_POST['forum_edit_lock'], '0', 'forum_edit_lock');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$forum_edit_lock' WHERE settings_name='forum_edit_lock' AND settings_inf='forum'") : '';
		$forum_edit_timelimit = form_sanitizer($_POST['forum_edit_timelimit'], '0', 'forum_edit_timelimit');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$forum_edit_timelimit' WHERE settings_name='forum_edit_timelimit' AND settings_inf='forum'") : '';
		$popular_threads_timeframe = form_sanitizer($_POST['popular_threads_timeframe'], '604800', 'popular_threads_timeframe');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$popular_threads_timeframe' WHERE settings_name='popular_threads_timeframe' AND settings_inf='forum'") : '';
		$forum_last_posts_reply = form_sanitizer($_POST['forum_last_posts_reply'], '1', 'forum_last_posts_reply');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$forum_last_posts_reply' WHERE settings_name='forum_last_posts_reply' AND settings_inf='forum'") : '';
		$forum_last_post_avatar = form_sanitizer($_POST['forum_last_post_avatar'], '1', 'forum_last_post_avatar');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$forum_last_post_avatar' WHERE settings_name='forum_last_post_avatar' AND settings_inf='forum'") : '';
		$forum_editpost_to_lastpost = form_sanitizer($_POST['forum_editpost_to_lastpost'], '0', 'forum_editpost_to_lastpost');
		$result = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$forum_editpost_to_lastpost' WHERE settings_name='forum_editpost_to_lastpost' AND settings_inf='forum'") : '';
		addNotice('success', $locale['900']);
		redirect(FUSION_SELF.$aidlink.'&section=fs');
	}
}
/**
 * Options for dropdown field
 */
$yes_no_array = array('1' => $locale['yes'], '0' => $locale['no']);


opentable($locale['forum_settings']);
	echo "<div class='well'>".$locale['forum_description']."</div>";

/**
 * List of Settings Column for Forum Infusion and default values
 * numofthreads = 16 -- ok
 * threads_per_page = 20 -- ok
 * posts_per_page = 20 -- ok
 * forum_ips - -103 -- ok
 * forum_attachmax = 1mb ---- 1,000,000 bytes (one million) -- ok
 * forum_attachmax_count = 5 -- ok
 * forum_attachtypes .pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z -- ok
 * thread_notify = 1 == ok
 * forum_ranks = 1 -- ok
 * forum_edit_lock = 0 -- ok
 * forum_edit_timelimit = 0 -- ok
 * popular_threads_timeframe = 604800 -- ok
 * forum_last_posts_reply = 1 -- ok
 * forum_last_post_avatar = 1 -- ok
 * forum_editpost_to_lastpost = 0 -- ok
 */
echo openform('settingsform', 'post', FUSION_SELF.$aidlink.'&amp;section=fs', array('max_tokens' => 1));
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-8'>\n";
openside('');
echo "<span class='small pull-right'>* ".$locale['506']."</span><br/>\n";
echo form_text('numofthreads', $locale['505'], $forum_settings['numofthreads'], array('error_text' => $locale['error_value'], 'inline' => 1, 'width'=>'150px', 'type'=>'number'));
closeside();
openside('');
echo form_text('threads_per_page', $locale['forum_080'], $forum_settings['threads_per_page'], array('error_text' => $locale['error_value'], 'inline' => 1, 'width'=>'150px', 'type'=>'number'));
echo form_text('posts_per_page', $locale['forum_081'], $forum_settings['posts_per_page'], array('error_text' => $locale['error_value'], 'inline' => 1, 'width'=>'150px', 'type'=>'number'));
closeside();

openside('');
echo form_select('thread_notify', $locale['512'], $yes_no_array, $forum_settings['thread_notify'], array('error_text' => $locale['error_value'], 'inline' => 1));
closeside();
openside('');
echo "<span class='pull-right position-absolute small' style='right:30px;'>".$locale['537']."</span>\n";
echo form_select('forum_edit_timelimit', $locale['536'], array('0','10','30','45','60'), $forum_settings['forum_edit_timelimit'], array('max_length' => 2, 'width' => '100px', 'required' => 1, 'error_text' => $locale['error_value'], 'inline' => 1));

echo form_select('forum_ips', $locale['507'], $yes_no_array, $forum_settings['forum_ips'], array('error_text' => $locale['error_value'], 'inline' => 1));

echo form_select('forum_ranks', $locale['520'], $yes_no_array, $forum_settings['forum_ranks'], array('error_text' => $locale['error_value'], 'inline' => 1));
echo form_select('forum_last_post_avatar', $locale['539'], $yes_no_array, $forum_settings['forum_last_post_avatar'], array('error_text' => $locale['error_value'], 'inline' => 1));
echo form_select('forum_edit_lock', $locale['521'], $yes_no_array, $forum_settings['forum_edit_lock'], array('error_text' => $locale['error_value'], 'inline' => 1));
echo form_select('forum_editpost_to_lastpost', $locale['538'], $yes_no_array, $forum_settings['forum_editpost_to_lastpost'], array('error_text' => $locale['error_value'], 'inline' => 1));
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-4'>\n";
openside('');
$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
$calc_c = calculate_byte($forum_settings['forum_attachmax']);
$calc_b = $forum_settings['forum_attachmax']/$calc_c;
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
echo form_select('forum_attachmax_count', '', range(1, 10), $forum_settings['forum_attachmax_count'], array('error_text' => $locale['error_value'], 'width'=>'100%'));
echo "</div>\n";
echo "<div class='clearfix'>\n";
echo "<span class='small pull-right'>".$locale['511']."</span>\n";
// redo mime
sort($mime_opts);
// as ","
echo form_select('forum_attachtypes', $locale['510'], $mime_opts, $forum_settings['forum_attachtypes'], array('width'=>'100%', 'error_text' => $locale['error_type'], 'tags'=>1, 'multiple' => 1));
// as "|"
//echo form_select('attachtypes[]', '', $mime_opts, $forum_settings['attachtypes'], array('input_id'=>'attachtypes', 'error_text' => $locale['error_type'], 'placeholder' => $locale['choose'], 'multiple' => 1, 'width' => '100%' , 'delimiter' => '|'));
echo "</div>\n";
closeside();

openside('');
$timeframe_opts = array('604800' => $locale['527'], '2419200' => $locale['528'], '31557600' => $locale['529'], '0' => $locale['530']);
$array_opts = array('0' => $locale['519'], '1' => $locale['533']);
for ($i = 2; $i <= 20; $i++) { $array_opts[$i] = sprintf($locale['532'], $i); }
if (isset($_GET['action']) && $_GET['action'] == "count_posts") echo form_alert($locale['524'], '', array('class'=>'warning'));
echo "<div class='clearfix'>\n";
echo form_select('popular_threads_timeframe', $locale['525'], $timeframe_opts, $forum_settings['popular_threads_timeframe'], array('error_text' => $locale['error_value'], 'width'=>'100%'));
echo "</div>\n";
echo "<div class='clearfix'>\n";
echo form_select('forum_last_posts_reply', $locale['531'], $array_opts, $forum_settings['forum_last_posts_reply'], array('error_text' => $locale['error_value'], 'width'=>'100%'));
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
