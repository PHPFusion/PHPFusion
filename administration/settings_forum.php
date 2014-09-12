<?php
    /*-------------------------------------------------------+
    | PHP-Fusion Content Management System
    | Copyright (C) PHP-Fusion Inc
    | http://www.php-fusion.co.uk/
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

    if (!checkrights("S3") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
        redirect("../index.php");
    }

    require_once THEMES."templates/admin_header.php";
    include LOCALE.LOCALESET."admin/settings.php";

    if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
        if ($_GET['error'] == 0) {
            $message = $locale['900'];
        }
        if (isset($message)) {
            echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
        }
    }

    if (isset($_GET['action']) && $_GET['action'] == "count_posts") {
        $result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_POSTS." GROUP BY post_author");
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $result2 = dbquery("UPDATE ".DB_USERS." SET user_posts='".$data['num_posts']."' WHERE user_id='".$data['post_author']."'");
            }
        }
    }

    if (isset($_POST['savesettings'])) {
        $admin_password = (isset($_POST['admin_password'])) ? form_sanitizer($_POST['admin_password'], '', 'admin_password') : '';
        if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "") && !defined('FUSION_NULL')) {
            $numofthreads               = form_sanitizer($_POST['numofthreads'], 5, 'numofthreads');
            $result                     = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$numofthreads' WHERE settings_name='numofthreads'") : '';
            $forum_ips                  = form_sanitizer($_POST['forum_ips'], 103, 'forum_ips');
            $result                     = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['forum_ips']) ? $_POST['forum_ips'] : "103")."' WHERE settings_name='forum_ips'") : '';
            $attachmax                  = form_sanitizer($_POST['calc_b'], 2, 'calc_b')*form_sanitizer($_POST['calc_c'], 1000000, 'calc_c');
            $result                     = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$attachmax' WHERE settings_name='attachmax'") : '';
            $attachmax_count            = form_sanitizer($_POST['attachmax_count'], 5, 'attachmax_count');
            $result                     = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$attachmax_count' WHERE settings_name='attachmax_count'") : '';
            $attachtypes                = form_sanitizer($_POST['attachtypes'], '', 'attachtypes');
            $result                     = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['attachtypes'])."' WHERE settings_name='attachtypes'") : '';
            $thread_notify              = form_sanitizer($_POST['thread_notify'], '', 'thread_notify');
            $result                     = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$thread_notify' WHERE settings_name='thread_notify'") : '';
            $forum_ranks                = form_sanitizer($_POST['forum_ranks'], '0', 'forum_ranks');
            $result                     = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$forum_ranks' WHERE settings_name='forum_ranks'") : '';
            $forum_edit_lock            = form_sanitizer($_POST['forum_edit_lock'], '0', 'forum_edit_lock');
            $result                     = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$forum_edit_lock' WHERE settings_name='forum_edit_lock'") : '';
            $forum_edit_timelimit       = form_sanitizer($_POST['forum_edit_timelimit'], '0', 'forum_edit_timelimit');
            $result                     = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$forum_edit_timelimit' WHERE settings_name='forum_edit_timelimit'") : '';
            $popular_threads_timeframe  = form_sanitizer($_POST['popular_threads_timeframe'], '604800', 'popular_threads_timeframe');
            $result                     = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$popular_threads_timeframe' WHERE settings_name='popular_threads_timeframe'") : '';
            $forum_last_posts_reply     = form_sanitizer($_POST['forum_last_posts_reply'], '0', 'forum_last_posts_reply');
            $result                     = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$forum_last_posts_reply' WHERE settings_name='forum_last_posts_reply'") : '';
            $forum_last_post_avatar     = form_sanitizer($_POST['forum_last_post_avatar'], '0', 'forum_last_post_avatar');
            $result                     = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$forum_last_post_avatar' WHERE settings_name='forum_last_post_avatar'") : '';
            $forum_editpost_to_lastpost = form_sanitizer($_POST['forum_editpost_to_lastpost'], '0', 'forum_editpost_to_lastpost');
            $result                     = (!defined('FUSION_NULL')) ? dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$forum_editpost_to_lastpost' WHERE settings_name='forum_editpost_to_lastpost'") : '';
            set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
            redirect(FUSION_SELF.$aidlink."&error=0");
        }
    }

    $settings2 = array();
    $result = dbquery("SELECT * FROM ".DB_SETTINGS);
    while ($data = dbarray($result)) {
        $settings2[$data['settings_name']] = $data['settings_value'];
    }
    function calculate_byte($download_max_b) {
        $calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
        foreach ($calc_opts as $byte => $val) {
            if ($download_max_b/$byte <= 999) {
                return $byte;
            }
        }
        return 1000000;
    }

    opentable($locale['400']);

    echo openform('settingsform', 'settingsfrom', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));
    echo "<table class='table table-responsive center'>\n<tbody>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='numofthreads'>".$locale['505']."</label> <span class='required'>*</span> <br /><span class='small2'>".$locale['506']."</span></td>\n";
    echo "<td width='50%' class='tbl'>\n";
    $num_opts = array('5' => '5', '10' => '10', '15' => '15', '20' => '20',);
    echo form_select('', 'numofthreads', 'numofthreads', $num_opts, $settings2['numofthreads'], array('required' => 1, 'error_text' => $locale['error_value']));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='forum_ips'>".$locale['507']."</label> <span class='required'>*</span></td>\n";
    echo "<td width='50%' class='tbl'>\n";
    $yes_no_array = array('0' => $locale['yes'], '1' => $locale['no']);
    echo form_select('', 'forum_ips', 'forum_ips', $yes_no_array, $settings2['forum_ips'], array('required' => 1, 'error_text' => $locale['error_value']));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='calc_c'>".$locale['508']."</label> <span class='required'>*</span><br /><span class='small2'>".$locale['509']."</span> </td>\n";
    echo "<td width='50%' class='tbl'>\n";
    $calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
    $calc_c = calculate_byte($settings2['attachmax']);
    $calc_b = $settings2['attachmax']/$calc_c;
    echo form_text('', 'calc_b', 'calc_b', $calc_b, array('required' => 1, 'number' => 1, 'error_text' => $locale['error_rate'], 'width' => '100px', 'max_length' => '3', 'class' => 'pull-left m-r-10'));
    echo form_select('', 'calc_c', 'calc_c', $calc_opts, $calc_c, array('placeholder' => $locale['choose'], 'class' => 'pull-left', 'width' => '180px'));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'>".$locale['534']." <span class='required'>*</span><br /><span class='small2'>".$locale['535']."</span> </td>\n";
    echo "<td width='50%' class='tbl'>\n";
    echo form_select('', 'attachmax_count', 'attachmax_count', range(1, 10), $settings2['attachmax_count'], array('required' => 1, 'error_text' => $locale['error_value']));
    echo "</td>\n</td>\n";
    echo "</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='attachtypes'>".$locale['510']."</label> <span class='required'>*</span><br /><span class='small2'>".$locale['511']."</span></td>\n";
    echo "<td width='50%' class='tbl'>\n";
    require_once INCLUDES."mimetypes_include.php";
    $mime = mimeTypes();
    $mime_opts = array();
    foreach ($mime as $m => $Mime) {
        $ext             = ".$m";
        $mime_opts[$ext] = $ext;
    }
    echo form_select('', 'attachtypes[]', 'attachtypes', $mime_opts, $settings2['attachtypes'], array('error_text' => $locale['error_type'], 'placeholder' => $locale['choose'], 'multiple' => 1));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='thread_notify'>".$locale['512']."</label> <span class='required'>*</span></td>\n";
    echo "<td width='50%' class='tbl'>\n";
    echo form_select('', 'thread_notify', 'thread_notify', $yes_no_array, $settings2['thread_notify'], array('required' => 1, 'error_text' => $locale['error_value']));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='forum_ranks'>".$locale['520']."</label></td>\n";
    echo "<td width='50%' class='tbl'>\n";
    echo form_select('', 'forum_ranks', 'forum_ranks', $yes_no_array, $settings2['forum_ranks'], array('required' => 1, 'error_text' => $locale['error_value']));
    echo "</td>\n</tr>\n<tr>\n";
    // Show avatar with last post in forum
    echo "<td width='50%' class='tbl'><label for='forum_last_post_avatar'>".$locale['539']."</label> <span class='required'>*</span></td>\n";
    echo "<td width='50%' class='tbl'>\n";
    echo form_select('', 'forum_last_post_avatar', 'forum_last_post_avatar', $yes_no_array, $settings2['forum_last_post_avatar'], array('required' => 1, 'error_text' => $locale['error_value']));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='forum_edit_lock'>".$locale['521']."</label><br /><span class='small2'>".$locale['522']."</span></td>\n";
    echo "<td width='50%' class='tbl'>\n";
    echo form_select('', 'forum_edit_lock', 'forum_edit_lock', $yes_no_array, $settings2['forum_edit_lock'], array('required' => 1, 'error_text' => $locale['error_value']));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='forum_edit_timelimit'>".$locale['536']."</label> <span class='required'>*</span><br /><span class='small2'>".$locale['537']."</span></td>\n";
    echo "<td width='50%' class='tbl'>\n";
    echo form_text('', 'forum_edit_timelimit', 'forum_edit_timelimit', $settings2['forum_edit_timelimit'], array('max_length' => 2, 'width' => '100px', 'required' => 1, 'error_text' => $locale['error_value'], 'number' => 1));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='forum_editpost_to_lastpost'>".$locale['538']."</label> <span class='required'>*</span></td>\n";
    echo "<td width='50%' class='tbl'>\n";
    echo form_select('', 'forum_editpost_to_lastpost', 'forum_editpost_to_lastpost', $yes_no_array, $settings2['forum_editpost_to_lastpost'], array('required' => 1, 'error_text' => $locale['error_value']));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='popular_threads_timeframe'>".$locale['525']."</label><br /><span class='small2'>".$locale['526']."</span></td>\n";
    echo "<td width='50%' class='tbl'>\n";
    $timeframe_opts = array('604800' => $locale['527'], '2419200' => $locale['528'], '31557600' => $locale['529'], '0' => $locale['530']);
    echo form_select('', 'popular_threads_timeframe', 'popular_threads_timeframe', $timeframe_opts, $settings2['popular_threads_timeframe'], array('required' => 1, 'error_text' => $locale['error_value']));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='50%' class='tbl'><label for='forum_last_posts_reply'>".$locale['531']."</label> <span class='required'>*</span> </td>";
    echo "<td width='50%' class='tbl'>\n";
    $array_opts = array('0' => $locale['519'], '1' => $locale['533']);
    for ($i = 2; $i <= 20; $i++) {
        $array_opts[$i] = sprintf($locale['532'], $i);
    }
    echo form_select('', 'forum_last_posts_reply', 'forum_last_posts_reply', $array_opts, $settings2['forum_last_posts_reply'], array('required' => 1, 'error_text' => $locale['error_value']));
    echo "</td>\n</tr>\n<tr>\n";
    if (!check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
        echo "<td class='tbl'><label for='admin_password'>".$locale['853']."</label> <span class='required'>*</span></td>\n";
        echo "<td class='tbl'>\n";
        echo form_text('', 'admin_password', 'admin_password', isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "", array('required' => 1, 'password' => 1, 'error_text' => $locale['global_182']));
        echo "</td>\n</tr>\n<tr>\n";
    }
    echo "<td class='tbl'>\n</td>\n<td align='center' class='tbl'>\n";
    echo "<a class='btn btn-default btn-block' href='".FUSION_SELF.$aidlink."&amp;action=count_posts'>".$locale['523']."</a>".(isset($_GET['action']) && $_GET['action'] == "count_posts" ? " ".$locale['524'] : "")."\n";
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td align='center' colspan='2' class='tbl'><br />\n";
    echo form_button($locale['750'], 'savesettings', 'savesettings', $locale['750'], array('class' => 'btn-primary'));
    echo "</td>\n</tr>\n</tbody>\n</table>\n</form>\n";
    closetable();

    require_once THEMES."templates/footer.php";
?>
