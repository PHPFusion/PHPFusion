<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: blog_cats.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../maincore.php';
if (!checkRights("BLC") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {redirect("../index.php");}

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/blog-cats.php";

if (isset($_GET['status']) && !isset($message)) {
    if ($_GET['status'] == "sn") {
        $message = $locale['420'];
    } else if ($_GET['status'] == "su") {
        $message = $locale['421'];
    } else if ($_GET['status'] == "dn") {
        $message = $locale['422']."<br />\n<span class='small'>".$locale['423']."</span>";
    } else if ($_GET['status'] == "dy") {
        $message = $locale['424'];
    }
    if ($message) {
		echo "<div id='close-message'><div class='admin-message alert alert-info'>".$message."</div></div>\n";
    }
}

$openTable = '';

if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
    $result = dbcount("(blog_cat)", DB_BLOG, "blog_cat='".$_GET['cat_id']."'");
    if (!empty($result)) {
        redirect(FUSION_SELF.$aidlink."&status=dn");
    } else {
        $result = dbquery("DELETE FROM ".DB_BLOG_CATS." WHERE blog_cat_id='".$_GET['cat_id']."'");
        redirect(FUSION_SELF.$aidlink."&status=dy");
    }
} else if (isset($_POST['save_cat'])) {
    $cat_name = stripinput($_POST['cat_name']);
    $cat_image = stripinput($_POST['cat_image']);
    $cat_language = stripinput($_POST['cat_language']);
    if ($cat_name && $cat_image) {
        if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
            $result = dbquery("UPDATE ".DB_BLOG_CATS." SET blog_cat_name='$cat_name', blog_cat_image='$cat_image', blog_cat_language='$cat_language' WHERE blog_cat_id='".$_GET['cat_id']."'");
            redirect(FUSION_SELF.$aidlink."&status=su");
        } else {
            $checkCat = dbcount("(blog_cat_id)", DB_BLOG_CATS, "blog_cat_name='".$cat_name."'");
            if ($checkCat == 0) {
                $result = dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('$cat_name', '$cat_image', '$cat_language')");
                redirect(FUSION_SELF.$aidlink."&status=sn");
            } else {
                $error = 2;
                $formaction = FUSION_SELF.$aidlink;
                $openTable = $locale['401'];
            }
        }
    } else {
        $error = 1;
        $formaction = FUSION_SELF.$aidlink;
        $openTable = $locale['401'];
    }
} else if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
    $result = dbquery("SELECT blog_cat_id, blog_cat_name, blog_cat_image, blog_cat_language FROM ".DB_BLOG_CATS." ".(multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."' AND" : "WHERE")." blog_cat_id='".$_GET['cat_id']."'");
    if (dbrows($result)) {
        $data = dbarray($result);
        $cat_name = $data['blog_cat_name'];
        $cat_image = $data['blog_cat_image'];
        $cat_language = $data['blog_cat_language'];
        $formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['blog_cat_id'];
        $openTable = $locale['400'];
    } else {
        redirect(FUSION_SELF.$aidlink);
    }
} else {
    $cat_name = "";
    $cat_image = "";
    $cat_language = LANGUAGE;
    $formaction = FUSION_SELF.$aidlink;
    $openTable = $locale['401'];
}
$image_files = makefilelist(IMAGES_BC, ".|..|index.php", TRUE);
$image_list = makefileopts($image_files, $cat_image);

if (isset($error) && isnum($error)) {
    if ($error == 1) {
        $errorMessage = $locale['460'];
    } else if ($error == 2) {
        $errorMessage = $locale['461'];
    }
    if ($errorMessage) {
        echo "<div id='close-message'><div class='admin-message'>".$errorMessage."</div></div>\n";
    }
}

opentable($openTable);
echo "<form name='addcat' method='post' action='".$formaction."'>\n";
echo "<table cellpadding='0' cellspacing='0' width='400' class='center'>\n<tr>\n";
echo "<td width='130' class='tbl'>".$locale['430']."</td>\n";
echo "<td class='tbl'><input type='text' name='cat_name' value='".$cat_name."' class='textbox' style='width:200px;' /></td>\n";
echo "</tr>\n";
if (multilang_table("NS")) {
    echo "<tr><td class='tbl'>".$locale['global_ML100']."</td>\n";
    $opts = get_available_languages_list($selected_language = "$cat_language");
    echo "<td class='tbl'>
	<select name='cat_language' class='textbox' style='width:200px;'>".$opts."</select></td>\n";
    echo "</tr>\n";
} else {
    echo "<input type='hidden' name='cat_language' value='".$cat_language."' />\n";
}
echo "<tr><td width='130' class='tbl'>".$locale['431']."</td>\n";
echo "<td class='tbl'><select name='cat_image' class='textbox' style='width:200px;'>\n".$image_list."</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />\n";
echo "<input type='submit' name='save_cat' value='".$locale['432']."' class='button' /></td>\n";
echo "</tr>\n</table>\n</form>\n";
closetable();

opentable($locale['402']);
$result = dbquery("SELECT blog_cat_id, blog_cat_name FROM ".DB_BLOG_CATS." ".(multilang_table("NS") ? "WHERE blog_cat_language='".LANGUAGE."'" : "")." ORDER BY blog_cat_name");
$rows = dbrows($result);
if ($rows != 0) {
    $counter = 0;
    $columns = 4;
    echo "<table cellpadding='0' cellspacing='1' width='400' class='center'>\n<tr>\n";
    while ($data = dbarray($result)) {
        if ($counter != 0 && ($counter % $columns == 0))
            echo "</tr>\n<tr>\n";
        echo "<td align='center' width='25%' class='tbl'><strong>".$data['blog_cat_name']."</strong><br /><br />\n";
        echo "<img src='".get_image("bc_".$data['blog_cat_name'])."' alt='".$data['blog_cat_name']."' class='blog-category' /><br /><br />\n";
        echo "<span class='small'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['blog_cat_id']."'>".$locale['433']."</a> -\n";
        echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;cat_id=".$data['blog_cat_id']."' onclick=\"return confirm('".$locale['450']."');\">".$locale['434']."</a></span></td>\n";
        $counter++;
    }
    echo "</tr>\n</table>\n";
} else {
    echo "<div style='text-align:center'><br />\n".$locale['435']."<br /><br />\n</div>\n";
}
echo "<div style='text-align:center'><br />\n<a href='".ADMIN."images.php".$aidlink."&amp;ifolder=imagesbc'>".$locale['436']."</a><br /><br />\n</div>\n";
closetable();

require_once THEMES."templates/footer.php";
