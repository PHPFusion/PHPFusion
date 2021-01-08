<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: shoutbox_panel.php
| Author: Core Development Team (coredevs@phpfusion.com)
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

include_once INCLUDES."infusions_include.php";

// Check if a locale file is available that match the selected locale.
if (file_exists(INFUSIONS."shoutbox_panel/locale/".LANGUAGE.".php")) {
    // Load the locale file matching selection.
    include INFUSIONS."shoutbox_panel/locale/".LANGUAGE.".php";
} else {
    // Load the default locale file.
    include INFUSIONS."shoutbox_panel/locale/English.php";
}

$shout_settings = get_settings("shoutbox_panel");

$link = FORM_REQUEST;
$link = preg_replace("^(&amp;|\?)s_action=(edit|delete)&amp;shout_id=\d*^", "", $link);
$sep = stristr($link, "?") ? "&amp;" : "?";
$shout_link = "";
$shout_message = "";

if (iMEMBER && (isset($_GET['s_action']) && $_GET['s_action'] == "delete") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
    if ((iADMIN && checkrights("S")) || (iMEMBER && dbcount("(shout_id)", DB_SHOUTBOX, "shout_id='".$_GET['shout_id']."' AND shout_name='".$userdata['user_id']."'"))) {
        $result = dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_id='".$_GET['shout_id']."'".(iADMIN ? "" : " AND shout_name='".$userdata['user_id']."'"));
    }
    redirect($link);
}

if (!function_exists("sbwrap")) {
    function sbwrap($text) {
        global $locale;

        $tags = 0;
        $chars = 0;
        $res = "";

        $str_len = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);

        for ($i = 0; $i < $str_len; $i++) {
            $chr = function_exists('mb_substr') ? mb_substr($text, $i, 1, 'UTF-8') : substr($text, $i, 1);
            if ($chr == "<") {
                if (substr($text, ($i + 1), 6) == "a href" || substr($text, ($i + 1), 3) == "img") {
                    $chr = " ".$chr;
                    $chars = 0;
                }
                $tags++;
            } else if ($chr == "&") {
                if (substr($text, ($i + 1), 5) == "quot;") {
                    $chars = $chars - 5;
                } else if (substr($text, ($i + 1), 4) == "amp;" || substr($text, ($i + 1), 4) == "#39;" || substr($text, ($i + 1), 4) == "#92;") {
                    $chars = $chars - 4;
                } else if (substr($text, ($i + 1), 3) == "lt;" || substr($text, ($i + 1), 3) == "gt;") {
                    $chars = $chars - 3;
                }
            } else if ($chr == ">") {
                $tags--;
            } else if ($chr == " ") {
                $chars = 0;
            } else if (!$tags) {
                $chars++;
            }

            if (!$tags && $chars == 18) {
                $chr .= "<br />";
                $chars = 0;
            }
            $res .= $chr;
        }

        return $res;
    }
}

openside($locale['SB_title']);
if (iMEMBER || $shout_settings['guest_shouts'] == "1") {
    include_once INCLUDES."bbcode_include.php";
    if (isset($_POST['post_shout'])) {
        $flood = FALSE;
        if (iMEMBER) {
            $shout_name = $userdata['user_id'];
        } else if ($shout_settings['guest_shouts'] == "1") {
            $shout_name = trim(stripinput($_POST['shout_name']));
            $shout_name = preg_replace("(^[+0-9\s]*)", "", $shout_name);
            if (isnum($shout_name)) {
                $shout_name = "";
            }

            if (!iADMIN) {
                $_CAPTCHA_IS_VALID = FALSE;
                include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php";
                if ($_CAPTCHA_IS_VALID == FALSE) {
                    redirect($link);
                }
            }
        }
        $shout_message = str_replace("\n", " ", $_POST['shout_message']);
        $shout_message = preg_replace("/^(.{255}).*$/", "$1", $shout_message);
        $shout_message = trim(stripinput(censorwords($shout_message)));
        if (iMEMBER && (isset($_GET['s_action']) && $_GET['s_action'] == "edit") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
            $comment_updated = FALSE;
            if ((iADMIN && checkrights("S")) || (iMEMBER && dbcount("(shout_id)", DB_SHOUTBOX, "shout_id='".$_GET['shout_id']."' AND shout_name='".$userdata['user_id']."'"))) {
                if ($shout_message) {
                    $result = dbquery("UPDATE ".DB_SHOUTBOX." SET shout_message='$shout_message' WHERE shout_id='".$_GET['shout_id']."'".(iADMIN ? "" : " AND shout_name='".$userdata['user_id']."'"));
                }
            }
            redirect($link);
        } else if ($shout_name && $shout_message) {
            require_once INCLUDES."flood_include.php";
            if (!flood_control("shout_datestamp", DB_SHOUTBOX, "shout_ip='".USER_IP."'")) {
                $result = dbquery("INSERT INTO ".DB_SHOUTBOX." (shout_name, shout_message, shout_datestamp, shout_ip, shout_ip_type, shout_hidden".(multilang_table("SB") ? ", shout_language)" : ")")." VALUES ('$shout_name', '$shout_message', '".time()."', '".USER_IP."', '".USER_IP_TYPE."', '0'".(multilang_table("SB") ? ", '".LANGUAGE."')" : ")"));
            }
        }
        redirect($link);
    }
    if (iMEMBER && (isset($_GET['s_action']) && $_GET['s_action'] == "edit") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
        $esresult = dbquery(
            "SELECT ts.shout_id, ts.shout_name, ts.shout_message, tu.user_id, tu.user_name
			FROM ".DB_SHOUTBOX." ts
			LEFT JOIN ".DB_USERS." tu ON ts.shout_name=tu.user_id
			".(multilang_table("SB") ? "WHERE shout_language='".LANGUAGE."' AND" : "WHERE")." ts.shout_id='".$_GET['shout_id']."'"
        );
        if (dbrows($esresult)) {
            $esdata = dbarray($esresult);
            if ((iADMIN && checkrights("S")) || (iMEMBER && $esdata['shout_name'] == $userdata['user_id'] && isset($esdata['user_name']))) {
                if ((isset($_GET['s_action']) && $_GET['s_action'] == "edit") && (isset($_GET['shout_id']) && isnum($_GET['shout_id']))) {
                    $edit_url = $sep."s_action=edit&amp;shout_id=".$esdata['shout_id'];
                } else {
                    $edit_url = "";
                }
                $shout_link = $link.$edit_url;
                $shout_message = $esdata['shout_message'];
            }
        } else {
            $shout_link = $link;
            $shout_message = "";
        }
    } else {
        $shout_link = $link;
        $shout_message = "";
    }

    echo "<a id='edit_shout' name='edit_shout'></a>\n";
    echo "<form name='shout_form' method='post' action='".FORM_REQUEST."'>\n";
    if (iGUEST) {
        echo $locale['SB_name']."<br />\n";
        echo "<input type='text' name='shout_name' value='' class='textbox' maxlength='30' style='width:140px' /><br />\n";
        echo $locale['SB_message']."<br />\n";
    }
    echo "<textarea name='shout_message' rows='4' cols='20' class='textbox' style='width:140px'>".$shout_message."</textarea><br />\n";
    echo display_bbcodes("150px;", "shout_message", "shout_form", "smiley|b|u|url|color")."\n";
    if (iGUEST) {
        echo $locale['SB_validation_code']."<br />\n";
        include INCLUDES."captchas/".$settings['captcha']."/captcha_display.php";
        if (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT)) {
            echo "<input type='text' id='captcha_code' name='captcha_code' class='textbox' autocomplete='off' style='width:100px' />";
        }
    }

    echo "<br /><input type='submit' name='post_shout' value='".$locale['SB_shout']."' class='button' />\n";
    echo "</form>\n<br />\n";
} else {
    echo "<div style='text-align:center'>".$locale['SB_login_req']."</div><br />\n";
}
$numrows = dbcount("(shout_id)", DB_SHOUTBOX, "shout_hidden='0'");
$result = dbquery(
    "SELECT ts.shout_id, ts.shout_name, ts.shout_message, ts.shout_datestamp, tu.user_id, tu.user_name, tu.user_status
	FROM ".DB_SHOUTBOX." ts
	LEFT JOIN ".DB_USERS." tu ON ts.shout_name=tu.user_id
	".(multilang_table("SB") ? "WHERE shout_language='".LANGUAGE."' AND" : "WHERE")." shout_hidden='0'
	ORDER BY ts.shout_datestamp DESC LIMIT 0,".$shout_settings['visible_shouts']
);
if (dbrows($result)) {
    $i = 0;
    while ($data = dbarray($result)) {
        echo "<div class='shoutboxname'>";
        if ($data['user_name']) {
            echo "<span class='side'>".profile_link($data['shout_name'], $data['user_name'], $data['user_status'])."</span>\n";
        } else {
            echo $data['shout_name']."\n";
        }
        echo "</div>\n";
        echo "<div class='shoutboxdate'>".showdate("forumdate", $data['shout_datestamp'])."</div>";
        echo "<div class='shoutbox'>".sbwrap(parseubb(parsesmileys($data['shout_message']), "b|i|u|url|color"))."</div>\n";
        if ((iADMIN && checkrights("S")) || (iMEMBER && $data['shout_name'] == $userdata['user_id'] && isset($data['user_name']))) {
            echo "[<a href='".$link.$sep."s_action=edit&amp;shout_id=".$data['shout_id']."#edit_shout"."' class='side'>".$locale['SB_edit']."</a>]\n";
            echo "[<a href='".$link.$sep."s_action=delete&amp;shout_id=".$data['shout_id']."' onclick=\"return confirm('".$locale['SB_warning_shout']."');\" class='side'>".$locale['SB_delete']."</a>]<br />\n";
        }
        $i++;
        if ($i != $numrows) {
            echo "<br />\n";
        }
    }
    if ($numrows > $shout_settings['visible_shouts']) {
        echo "<div style='text-align:center'>\n<a href='".BASEDIR."infusions/shoutbox_panel/shoutbox_archive.php' class='side'>".$locale['SB_archive']."</a>\n</div>\n";
    }
} else {
    echo "<div>".$locale['SB_no_msgs']."</div>\n";
}
closeside();
