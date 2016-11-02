<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: images.php
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
require_once "../maincore.php";
pageAccess('IM');

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/image_uploads.php";

if (isset($_GET['action']) && $_GET['action'] = "update") {
    include INCLUDES."buildlist.php";
}

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'images.php'.fusion_get_aidlink(), 'title' => $locale['460']]);

$folders = array(
    "images" => IMAGES,
    "imagesa" => IMAGES_A,
    "imagesn" => IMAGES_N,
    "imagesnc" => IMAGES_NC,
    "imagesb" => IMAGES_B,
    "imagesbc" => IMAGES_BC
);

if (isset($_GET['ifolder']) && ctype_alnum($_GET['ifolder']) == 1 && isset($folders[$_GET['ifolder']])) {
    $_GET['ifolder'] = stripinput($_GET['ifolder']);
    $afolder = $folders[$_GET['ifolder']];
} else {
    $_GET['ifolder'] = "images";
    $afolder = IMAGES;
}

$image_list = makefilelist($afolder, ".|..", TRUE, "files", "php|js|ico|DS_Store|SVN");
if ($image_list) {
    $image_count = count($image_list);
} else {
    $image_count = 0;
}

if (isset($_GET['del']) && in_array($_GET['del'], $image_list)) {
    unlink($afolder.stripinput($_GET['del']));
    if ($settings['tinymce_enabled'] == 1) {
        include INCLUDES."buildlist.php";
    }
    addNotice('warning', $locale['400']);
    redirect(FUSION_SELF.$aidlink."&amp;ifolder=".$_GET['ifolder']);
} elseif (isset($_POST['uploadimage'])) {
    $data = array(
        'myfile' => ''
    );

    if (defender::safe()) {
        if (!empty($_FILES['myfile'])) { // when files is uploaded.
            $upload = form_sanitizer($_FILES['myfile'], '', 'myfile');

            if (!empty($upload) && $upload['error'] == 0) {
                $data['myfile'] = $upload['image_name'];
                if ($settings['tinymce_enabled'] == 1) {
                    include INCLUDES."buildlist.php";
                }
                if (defender::safe()) {
                    addNotice('success', $locale['420']);
                    redirect(FUSION_SELF.$aidlink."&amp;ifolder=".$_GET['ifolder']."&img=".$data['myfile']);
                }
            }
            redirect(FUSION_SELF.$aidlink."&amp;ifolder=".$_GET['ifolder']);
        }
    }
} else {
    opentable($locale['460']);
    $tab_title['title'] = array($locale['460'], $locale['420']);
    $tab_title['id'] = array("list", "upload");
    $active_set = isset($_POST['upload']) ? 1 : 0;
    $active_tab = tab_active($tab_title, $active_set);
    echo opentab($tab_title, $active_tab, 'images_tab');
    echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $active_tab);
    echo "<div class='m-t-20'>\n";
    if (isset($_GET['view']) && in_array($_GET['view'], $image_list)) {
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['440']]);
        opentable($locale['440']);
        echo "<div style='text-align:center'><br />\n";
        $image_ext = strrchr($afolder.stripinput($_GET['view']), ".");
        if (in_array($image_ext, array(".gif", ".GIF", ".ico", ".jpg", ".JPG", ".jpeg", ".JPEG", ".png", ".PNG"))) {
            echo "<img class='img-responsive img-thumbnail' src='".$afolder.stripinput($_GET['view'])."' alt='".stripinput($_GET['view'])."' /><br /><br />\n";
        } else {
            echo "<strong>".$locale['441']."</strong><br /><br />\n";
        }
        echo "<a href='".FUSION_SELF.$aidlink."&amp;ifolder=".$_GET['ifolder']."&amp;del=".stripinput($_GET['view'])."' onclick=\"return confirm('".$locale['470']."');\">".$locale['442']."</a>";
        echo "<br /><br />\n<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n</div>\n";
        closetable();
    } else {
        echo "<table cellpadding='0' cellspacing='1'  class='table table-responsive tbl-border center'>\n<tr>\n";
        echo "<td align='center' colspan='2' class='tbl2'>\n";
        echo "<div class='btn-group'>\n";
        echo "<a class='btn btn-default ".($_GET['ifolder'] == "images" ? "active" : "")."' href='".FUSION_SELF.$aidlink."&amp;ifolder=images'>".$locale['422']."</a>\n";
        echo "<a class='btn btn-default ".($_GET['ifolder'] == "imagesa" ? "active" : "")."' href='".FUSION_SELF.$aidlink."&amp;ifolder=imagesa'>".$locale['423']."</a>\n";
        echo "<a class='btn btn-default ".($_GET['ifolder'] == "imagesn" ? "active" : "")."' href='".FUSION_SELF.$aidlink."&amp;ifolder=imagesn'>".$locale['424']."</a>\n";
        echo "<a class='btn btn-default ".($_GET['ifolder'] == "imagesnc" ? "active" : "")."' href='".FUSION_SELF.$aidlink."&amp;ifolder=imagesnc'>".$locale['427']."</a>\n";
        echo "<a class='btn btn-default ".($_GET['ifolder'] == "imagesb" ? "active" : "")."' href='".FUSION_SELF.$aidlink."&amp;ifolder=imagesb'>".$locale['428']."</a>\n";
        echo "<a class='btn btn-default ".($_GET['ifolder'] == "imagesbc" ? "active" : "")."' href='".FUSION_SELF.$aidlink."&amp;ifolder=imagesbc'>".$locale['429']."</a>\n";
        echo "</div>\n";
        echo "</td>\n</tr>\n";
        if ($image_list) {
            for ($i = 0; $i < $image_count; $i++) {
                if ($i % 2 == 0) {
                    $row_color = "tbl1";
                } else {
                    $row_color = "tbl2";
                }
                echo "<tr>\n<td class='".$row_color."'>".$image_list[$i]."</td>\n";
                echo "<td align='right' width='1%' class='".$row_color."' style='white-space:nowrap'>\n";
                echo "<a href='".FUSION_SELF.$aidlink."&amp;ifolder=".$_GET['ifolder']."&amp;view=".$image_list[$i]."'>".$locale['461']."</a> -\n";
                echo "<a href='".FUSION_SELF.$aidlink."&amp;ifolder=".$_GET['ifolder']."&amp;del=".$image_list[$i]."' onclick=\"return confirm('".$locale['470']."');\">".$locale['462']."</a></td>\n";
                echo "</tr>\n";
            }
            if ($settings['tinymce_enabled'] == 1) {
                echo "<tr>\n<td align='center' colspan='2' class='tbl1'><a href='".FUSION_SELF.$aidlink."&amp;ifolder=".$_GET['ifolder']."&amp;action=update'>".$locale['464']."</a></td>\n</tr>\n";
            }
        } else {
            echo "<tr>\n<td align='center' class='tbl1'>".$locale['463']."</td>\n</tr>\n";
        }
        echo "</table>\n";
    }
    echo "</div>\n";
    echo closetabbody();

    echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $active_tab);
    echo "<div class='m-t-20'>\n";
        echo openform('uploadform', 'post', FUSION_REQUEST, array('enctype' => TRUE));
        echo form_fileinput("myfile", $locale['421'], "", array('upload_path' => $afolder, 'type' => 'image','required' => TRUE));
        echo form_button('uploadimage', $locale['420'], $locale['420'], array('class' => 'btn-primary'));
    echo closeform();
    echo "</div>\n";
    echo closetabbody();
    echo closetab();

    closetable();
}

require_once THEMES."templates/footer.php";
