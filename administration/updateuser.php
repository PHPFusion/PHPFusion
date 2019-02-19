<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: updateuser.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

pageAccess('M');

if (!isset($_GET['user_id']) || !isnum($_GET['user_id'])) {
    redirect(FUSION_SELF.$aidlink);
}

$error = "";
$db_values = "";
$set_avatar = "";
$result = dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".$_GET['user_id']."'");
$user_data = dbarray($result);
$user_name = trim(preg_replace("/ +/i", " ", $_POST['user_name']));
$user_email = trim(stripinput($_POST['user_email']));
$user_new_password = trim(stripinput($_POST['user_new_password']));
$user_new_password2 = trim(stripinput($_POST['user_new_password2']));
if ($user_name == "" || $user_email == "") {
    $error .= $locale['451']."<br />\n";
} else {
    if (preg_check("/^[-0-9A-Z_@\s]+$/i", $user_name)) {
        if ($user_name != $user_data['user_name']) {
            $result = dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_name='".$user_name."'");
            if (dbrows($result)) {
                $error = $locale['453']."<br />\n";
            }
        }
    } else {
        $error .= $locale['452']."<br />\n";
    }
    if (preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $user_email)) {
        if ($user_email != $user_data['user_email']) {
            $result = dbquery("SELECT user_email FROM ".DB_USERS." WHERE user_email='".$user_email."'");
            if (dbrows($result) != 0) {
                $error = $locale['455']."<br />\n";
            }
        }
    } else {
        $error .= $locale['454']."<br />\n";
    }
}
if ($user_new_password != "") {
    if ($user_new_password2 != $user_new_password) {
        $error .= $locale['456']."<br />";
    } else {
        if ($_POST['user_hash'] == $user_data['user_password']) {
            if (!preg_match("/^[0-9A-Z@]{6,20}$/i", $user_new_password)) {
                $error .= $locale['457']."<br />\n";
            }
        } else {
            $error .= $locale['458']."<br />\n";
        }
    }
}
$user_hide_email = isnum($_POST['user_hide_email']) ? $_POST['user_hide_email'] : "1";
if ($error == "") {
    if (!$user_data['user_avatar'] && !empty($_FILES['user_avatar']['name']) && is_uploaded_file($_FILES['user_avatar']['tmp_name'])) {
        $newavatar = $_FILES['user_avatar'];
        $avatarext = strrchr($newavatar['name'], ".");
        $avatarname = substr($newavatar['name'], 0, strrpos($newavatar['name'], "."));
        if (preg_check("/^[-0-9A-Z_\[\]]+$/i", $avatarname) && preg_check("/(\.gif|\.GIF|\.jpg|\.JPG|\.jpeg|\.JPEG|\.png|\.PNG)$/",
                $avatarext) && $newavatar['size'] <= 30720
        ) {
            $avatarname = $avatarname."[".$userdata['user_id']."]".$avatarext;
            move_uploaded_file($newavatar['tmp_name'], IMAGES."avatars/".$avatarname);
            chmod(IMAGES."avatars/".$avatarname, 0644);
            $set_avatar = ", user_avatar='".$avatarname."'";
            if ($size = @getimagesize(IMAGES."avatars/".$avatarname)) {
                if ($size['0'] > 100 || $size['1'] > 100) {
                    @unlink(IMAGES."avatars/".$avatarname);
                    $set_avatar = "";
                } else if (!verify_image(IMAGES."avatars/".$avatarname)) {
                    @unlink(IMAGES."avatars/".$avatarname);
                    $set_avatar = "";
                    \defender::stop();
                    addNotice('danger', fusion_get_locale('error_secure_file'));
                }
            } else {
                @unlink(IMAGES."avatars/".$avatarname);
                $set_avatar = "";
            }
        } else {
            $set_avatar = "";
        }
    }
    if (isset($_POST['del_avatar'])) {
        @unlink(IMAGES."avatars/".$user_data['user_avatar']);
        $set_avatar = ", user_avatar=''";
    }
    $result = dbquery("SELECT * FROM ".DB_USER_FIELDS." tuf
        INNER JOIN ".DB_USER_FIELD_CATS." tufc ON tuf.field_cat = tufc.field_cat_id
        ORDER BY field_cat_order, field_order");
    if (dbrows($result)) {
        $profile_method = "validate_update";
        while ($data = dbarray($result)) {
            if (file_exists(LOCALE.LOCALESET."user_fields/".$data['field_name'].".php")) {
                include LOCALE.LOCALESET."user_fields/".$data['field_name'].".php";
            }
            if (file_exists(INCLUDES."user_fields/".$data['field_name']."_include.php")) {
                include INCLUDES."user_fields/".$data['field_name']."_include.php";
            }
        }
    }
    if ($user_new_password) {
        $new_pass = " user_password='".md5(md5($user_new_password))."', ";
    } else {
        $new_pass = " ";
    }
    $result = dbquery("UPDATE ".DB_USERS." SET user_name='$user_name',".$new_pass."user_email='$user_email', user_hide_email='$user_hide_email'".($set_avatar ? $set_avatar : "").$db_values." WHERE user_id='".$user_data['user_id']."'");
}
