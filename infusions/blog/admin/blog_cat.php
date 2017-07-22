<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/blog_cat.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
pageAccess('BLOG');
/**
 * Delete category images
 */
if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
    $result = dbcount("(blog_cat)", DB_BLOG, "blog_cat='".$_GET['cat_id']."'") || dbcount("(blog_cat_id)", DB_BLOG_CATS,
                                                                                          "blog_cat_parent='".$_GET['cat_id']."'");
    if (!empty($result)) {
        addNotice("danger", $locale['blog_0522']."-<span class='small'>".$locale['blog_0523']."</span>");
        redirect(FUSION_SELF.$aidlink);
    } else {
        $result = dbquery("DELETE FROM ".DB_BLOG_CATS." WHERE blog_cat_id='".intval($_GET['cat_id'])."'");
        addNotice("success", $locale['blog_0524b']);
        redirect(FUSION_SELF.$aidlink);
    }
    redirect(clean_request("", array("action"), FALSE));
}
$data = array(
    "blog_cat_id" => 0,
    "blog_cat_name" => "",
    "blog_cat_hidden" => array(),
    "blog_cat_parent" => 0,
    "blog_cat_image" => "",
    "blog_cat_language" => LANGUAGE,
);
$formAction = FUSION_REQUEST;
$formTitle = $locale['blog_0409'];
// if edit, override $data
if (isset($_POST['save_cat'])) {
    $inputArray = array(
        "blog_cat_id" => form_sanitizer($_POST['blog_cat_id'], "", "blog_cat_id"),
        "blog_cat_name" => form_sanitizer($_POST['blog_cat_name'], "", "blog_cat_name"),
        "blog_cat_parent" => form_sanitizer($_POST['blog_cat_parent'], 0, "blog_cat_parent"),
        "blog_cat_image" => form_sanitizer($_POST['blog_cat_image'], "", "blog_cat_image"),
        "blog_cat_language" => form_sanitizer($_POST['blog_cat_language'], LANGUAGE, "blog_cat_language"),
    );
    $categoryNameCheck = array(
        "when_updating" => "blog_cat_name='".$inputArray['blog_cat_name']."' and blog_cat_id !='".$inputArray['blog_cat_id']."' ".(multilang_table("BL") ? "and blog_cat_language = '".LANGUAGE."'" : ""),
        "when_saving" => "blog_cat_name='".$inputArray['blog_cat_name']."' ".(multilang_table("BL") ? "and blog_cat_language = '".LANGUAGE."'" : ""),
    );
    if (defender::safe()) {
        // check category name is unique when updating
        if (dbcount("(blog_cat_id)", DB_BLOG_CATS, "blog_cat_id='".$inputArray['blog_cat_id']."'")) {
            if (!dbcount("(blog_cat_id)", DB_BLOG_CATS, $categoryNameCheck['when_updating'])) {
                dbquery_insert(DB_BLOG_CATS, $inputArray, "update");
                addNotice("success", $locale['blog_0521']);
                // FUSION_REQUEST without the "action" gets
                redirect(clean_request("", array("action"), FALSE));
            } else {
                addNotice('danger', $locale['blog_0561']);
            }
        } else {
            // check category name is unique when saving new
            if (!dbcount("(blog_cat_id)", DB_BLOG_CATS, $categoryNameCheck['when_saving'])) {
                dbquery_insert(DB_BLOG_CATS, $inputArray, "save");
                addNotice("success", $locale['blog_0520']);
                redirect(FUSION_REQUEST);
            } else {
                addNotice('danger', $locale['blog_0561']);
            }
        }
    }
} elseif ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
    $result = dbquery("SELECT blog_cat_id, blog_cat_name, blog_cat_parent, blog_cat_image, blog_cat_language FROM ".DB_BLOG_CATS." ".(multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."' AND" : "WHERE")." blog_cat_id='".intval($_GET['cat_id'])."'");
    if (dbrows($result)) {
        $data = dbarray($result);
        $data['blog_cat_hidden'] = array($data['blog_cat_id']);
        $formTitle = $locale['blog_0402'];
    } else {
        // FUSION_REQUEST without the "action" gets
        redirect(clean_request("", array("action"), FALSE));
    }
}
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $formTitle]);

echo '<div class="m-t-10">';
echo '<h2>'.$formTitle.'</h2>';

echo openform("addcat", "post", $formAction);
openside("");
echo form_hidden("blog_cat_id", "", $data['blog_cat_id']);
echo form_text("blog_cat_name", $locale['blog_0530'], $data['blog_cat_name'], array(
    "required" => TRUE,
    "inline" => TRUE,
    "error_text" => $locale['blog_0560']
));
echo form_select_tree("blog_cat_parent", $locale['blog_0533'], $data['blog_cat_parent'], array(
    "inline" => TRUE,
    "disable_opts" => $data['blog_cat_hidden'],
    "hide_disabled" => TRUE,
    "query" => (multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."'" : "")
), DB_BLOG_CATS, "blog_cat_name", "blog_cat_id", "blog_cat_parent");
if (multilang_table("BL")) {
    echo form_select("blog_cat_language", $locale['global_ML100'], $data['blog_cat_language'], array(
        "inline" => TRUE,
        "options" => fusion_get_enabled_languages(),
        "placeholder" => $locale['choose']
    ));
} else {
    echo form_hidden("blog_cat_language", "", $data['blog_cat_language']);
}
echo form_select("blog_cat_image", $locale['blog_0531'], $data['blog_cat_image'], array(
    "inline" => TRUE,
    "options" => blogCatImageOpts(),
));
echo form_button("save_cat", $locale['blog_0532'], $locale['blog_0532'], array("class" => "btn-success", "icon" => "fa fa-hdd-o"));
closeside();
echo "<hr/>\n";
echo "<div class='overflow-hide'>";
echo "<div class='pull-right'><a class='btn btn-primary' href='".ADMIN."images.php".$aidlink."&amp;ifolder=imagesbc'>".$locale['blog_0536']."</a><br /><br />\n</div>\n";
echo "<h4>".$locale['blog_0407']."</h4>\n";
echo "</div>";
$result = dbquery("SELECT blog_cat_id, blog_cat_name FROM ".DB_BLOG_CATS." ".(multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."'" : "")." ORDER BY blog_cat_name");
$rows = dbrows($result);
if ($rows != 0) {
    echo "<div class='row'>";
    while ($data = dbarray($result)) {
        echo "<div class='col-xs-12 col-sm-3'>";
        echo "<div class='well clearfix'>\n";
        echo "<div class='pull-left' style='width:70px;'>\n";
        echo thumbnail(get_image("bl_".$data['blog_cat_name']), '50px');
        echo "</div>\n";
        echo "<div class='overflow-hide'><h4 class='m-b-5 m-t-5'>".getblogCatPath($data['blog_cat_id'])."</h4>";
        echo "<span><a href='".clean_request("action=edit&cat_id=".$data['blog_cat_id'], ['aid', 'section'], TRUE)."'>".$locale['edit']."</a> &middot; ";
        echo "<a href='".clean_request("action=delete&cat_id=".$data['blog_cat_id'], ['aid', 'section'], TRUE)."' onclick=\"return confirm('".$locale['blog_0550']."');\">".$locale['delete']."</a></span>\n";
        echo "</div>\n</div>\n";
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<div class='well text-center'>".$locale['blog_0461']."</div>\n";
}

echo '</div>';

function getblogCatPath($item_id) {
    $full_path = "";
    while ($item_id > 0) {
        $result = dbquery("SELECT blog_cat_id, blog_cat_name, blog_cat_parent FROM ".DB_BLOG_CATS." WHERE blog_cat_id='$item_id'".(multilang_table("BL") ? " AND blog_cat_language='".LANGUAGE."'" : ""));
        if (dbrows($result)) {
            $data = dbarray($result);
            if ($full_path) {
                $full_path = " / ".$full_path;
            }
            $full_path = $data['blog_cat_name'].$full_path;
            $item_id = $data['blog_cat_parent'];
        }
    }

    return $full_path;
}

function blogCatImageOpts() {
    $image_files = makefilelist(IMAGES_BC, ".|..|index.php", TRUE);
    $image_list = array();
    foreach ($image_files as $image) {
        $image_list[$image] = $image;
    }

    return $image_list;
}