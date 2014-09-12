<?php
    /*-------------------------------------------------------+
    | PHP-Fusion Content Management System
    | Copyright (C) PHP-Fusion Inc
    | http://www.php-fusion.co.uk/
    +--------------------------------------------------------+
    | Filename: settings_news.php
    | Author: Starefossen
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

    if (!checkRights("S8") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
        redirect("../index.php");
    }

    require_once THEMES."templates/admin_header.php";
    include LOCALE.LOCALESET."admin/settings.php";

    if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
        if ($_GET['error'] == 0) {
            $message = $locale['900'];
        } elseif ($_GET['error'] == 1) {
            $message = $locale['901'];
        }
        if (isset($message)) {
            echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
        }
    }

    if (isset($_POST['savesettings'])) {
        $error = 0;
        if (!defined('FUSION_NULL')) {
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['news_image_link']) ? $_POST['news_image_link'] : "0")."' WHERE settings_name='news_image_link'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['news_image_frontpage']) ? $_POST['news_image_frontpage'] : "0")."' WHERE settings_name='news_image_frontpage'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['news_image_readmore']) ? $_POST['news_image_readmore'] : "0")."' WHERE settings_name='news_image_readmore'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['news_thumb_ratio']) ? $_POST['news_thumb_ratio'] : "0")."' WHERE settings_name='news_thumb_ratio'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['news_thumb_w']) ? $_POST['news_thumb_w'] : "100")."' WHERE settings_name='news_thumb_w'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['news_thumb_h']) ? $_POST['news_thumb_h'] : "100")."' WHERE settings_name='news_thumb_h'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['news_photo_w']) ? $_POST['news_photo_w'] : "400")."' WHERE settings_name='news_photo_w'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['news_photo_h']) ? $_POST['news_photo_h'] : "300")."' WHERE settings_name='news_photo_h'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['news_photo_max_w']) ? $_POST['news_photo_max_w'] : "1800")."' WHERE settings_name='news_photo_max_w'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['news_photo_max_h']) ? $_POST['news_photo_max_h'] : "1600")."' WHERE settings_name='news_photo_max_h'");
            if (!$result) {
                $error = 1;
            }
            $news_photo_max_b = form_sanitizer($_POST['calc_b'], '150', 'calc_b')*form_sanitizer($_POST['calc_c'], '100000', 'calc_c');
            $result           = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$news_photo_max_b' WHERE settings_name='news_photo_max_b'");
            if (!$result) {
                $error = 1;
            }
            redirect(FUSION_SELF.$aidlink."&error=".$error);
        }
    }

    $settings2 = array();
    $result = dbquery("SELECT * FROM ".DB_SETTINGS);
    while ($data = dbarray($result)) {
        $settings2[$data['settings_name']] = $data['settings_value'];
    }

    opentable($locale['400']);
    echo openform('settingsform', 'settingsform', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));
    echo "<table class='table table-responsive center'>\n<tbody>\n<tr>\n";
    echo "<td width='30%' class='tbl'><label for='news_image_link'>".$locale['951']."</label></td>\n";
    echo "<td width='60%' class='tbl'>\n";
    $opts = array('0' => $locale['952'], '1' => $locale['953']);
    echo form_select('', 'news_image_link', 'news_image_link', $opts, $settings2['news_image_link']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='30%' class='tbl'><label for='news_image_frontpage'>".$locale['957']."</label></td>\n";
    echo "<td width='60%' class='tbl'>\n";
    $cat_opts = array('0' => $locale['959'], '1' => $locale['960']);
    echo form_select('', 'news_image_frontpage', 'news_image_frontpage', $cat_opts, $settings2['news_image_frontpage']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='30%' class='tbl'><label for='news_image_readmore'>".$locale['958']."</label></td>\n";
    echo "<td width='60%' class='tbl'>\n";
    echo form_select('', 'news_image_readmore', 'news_image_readmore', $cat_opts, $settings2['news_image_readmore']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td class='tbl2' align='center' colspan='2'><strong>".$locale['950']."</strong></td>\n";
    echo "</tr>\n<tr>\n";
    echo "<td width='30%' class='tbl'><label for='news_thumb_ratio'>".$locale['954']."</label></td>\n";
    echo "<td width='60%' class='tbl'>\n";
    $opts = array('0' => $locale['955'], '1' => $locale['956']);
    echo form_select('', 'news_thumb_ratio', 'news_thumb_ratio', $opts, $settings2['news_thumb_ratio']);
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='30%' class='tbl'><label for='news_thumb_w'>".$locale['601']."</label><br /><span class='small2'>".$locale['604']."</span></td>\n";
    echo "<td width='60%' class='tbl'>\n";
    echo form_text('', 'news_thumb_w', 'news_thumb_w', $settings2['news_thumb_w'], array('class' => 'pull-left', 'max_length' => 3));
    echo "<i class='entypo icancel pull-left m-r-10 m-l-10 m-t-10'></i>\n";
    echo form_text('', 'news_thumb_h', 'news_thumb_h', $settings2['news_thumb_h'], array('class' => 'pull-left', 'max_length' => 3));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='30%' class='tbl'><label for='news_photo_w'>".$locale['602']."</label><br /><span class='small2'>".$locale['604']."</span></td>\n";
    echo "<td width='60%' class='tbl'>\n";
    echo form_text('', 'news_photo_w', 'news_photo_w', $settings2['news_photo_w'], array('class' => 'pull-left', 'max_length' => 3));
    echo "<i class='entypo icancel pull-left m-r-10 m-l-10 m-t-10'></i>\n";
    echo form_text('', 'news_photo_h', 'news_photo_h', $settings2['news_photo_h'], array('class' => 'pull-left', 'max_length' => 3));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='30%' class='tbl'><label for='news_photo_max_w'>".$locale['603']."</label><br /><span class='small2'>".$locale['604']."</span></td>\n";
    echo "<td width='60%' class='tbl'>\n";
    echo form_text('', 'news_photo_max_w', 'news_photo_max_w', $settings2['news_photo_max_w'], array('class' => 'pull-left', 'max_length' => 4));
    echo "<i class='entypo icancel pull-left m-r-10 m-l-10 m-t-10'></i>\n";
    echo form_text('', 'news_photo_max_h', 'news_photo_max_h', $settings2['news_photo_max_h'], array('class' => 'pull-left', 'max_length' => 4));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td width='30%' class='tbl'><label for='calc_c'>".$locale['605']."</label></td>\n";
    echo "<td width='60%' class='tbl'>\n";
    function calculate_byte($download_max_b) {
        $calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
        foreach ($calc_opts as $byte => $val) {
            if ($download_max_b/$byte <= 999) {
                return $byte;
            }
        }
        return 1000000;
    }
    $calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
    $calc_c = calculate_byte($settings2['news_photo_max_b']);
    $calc_b = $settings2['news_photo_max_b']/$calc_c;
    echo form_text('', 'calc_b', 'calc_b', $calc_b, array('required' => 1, 'number' => 1, 'error_text' => $locale['error_rate'], 'width' => '100px', 'max_length' => '3', 'class' => 'pull-left m-r-10'));
    echo form_select('', 'calc_c', 'calc_c', $calc_opts, $calc_c, array('placeholder' => $locale['choose'], 'class' => 'pull-left', 'width' => '180px'));
    echo "</td>\n</tr>\n<tr>\n";
    echo "<td align='center' colspan='2' class='tbl'><br />\n";
    echo form_button($locale['750'], 'savesettings', 'savesettings', $locale['750'], array('class' => 'btn-primary'));
    echo "</td>\n</tr>\n</tbody>\n</table>\n";
    echo closeform();
    //</form>\n";
    closetable();

    require_once THEMES."templates/footer.php";
?>
