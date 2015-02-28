<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: images.php
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

if (!checkrights("IM") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/image_uploads.php";

if (isset($_GET['action']) && $_GET['action'] = "update") include INCLUDES."buildlist.php";

$folders = array("images" => IMAGES, "imagesa" => IMAGES_A, "imagesn" => IMAGES_N, "imagesnc" => IMAGES_NC);
if (isset($_GET['ifolder']) && ctype_alnum($_GET['ifolder']) == 1 && isset($folders[$_GET['ifolder']])) {
	$_GET['ifolder'] = stripinput($_GET['ifolder']);
	$afolder = $folders[$_GET['ifolder']];
} else {
	$_GET['ifolder'] = "images"; $afolder = IMAGES;
}

// List images
$image_list = makefilelist($afolder, ".|..", true, "files", "php|js|ico|DS_Store|SVN");
if ($image_list) {
	$image_count = count($image_list);
} else {
	$image_count = 0;
}

if (isset($_GET['status'])) {
	if ($_GET['status'] == "del") {
		$title = $locale['400'];
		$message = "<strong>".$locale['401']."</strong>";
	} elseif ($_GET['status'] == "upn") {
		$title = $locale['420'];
		$message = "<strong>".$locale['425']."</strong>";
	} elseif ($_GET['status'] == "upy") {
		$title = $locale['420'];
		$message = "<img src='".$afolder.stripinput($_GET['img'])."' alt='".stripinput($_GET['img'])."' /><br /><br />\n<strong>".$locale['426']."</strong>";
	}
	opentable($title);
	echo "<div style='text-align:center'>".$message."</div>\n";
	closetable();
}

if (isset($_GET['del']) && in_array($_GET['del'], $image_list)) {
	unlink($afolder.stripinput($_GET['del']));
	if ($settings['tinymce_enabled'] == 1) { include INCLUDES."buildlist.php"; }
	redirect(FUSION_SELF.$aidlink."&status=del&ifolder=".$_GET['ifolder']);
} elseif (isset($_POST['uploadimage'])) {
	$error = "";
	$image_types = array(
		".gif",
		".GIF",
		".jpeg",
		".JPEG",
		".jpg",
		".JPG",
		".png",
		".PNG"
	);
	$imgext = strrchr(strtolower($_FILES['myfile']['name']), ".");
	$imgname = stripfilename(strtolower(substr($_FILES['myfile']['name'], 0, strrpos($_FILES['myfile']['name'], "."))));
	$imgsize = $_FILES['myfile']['size'];
	$imgtemp = $_FILES['myfile']['tmp_name'];
	if (!in_array($imgext, $image_types)) {
		redirect(FUSION_SELF.$aidlink."&status=upn&ifolder=".$_GET['ifolder']);
	} elseif (is_uploaded_file($imgtemp)){
		move_uploaded_file($imgtemp, $afolder.$imgname.$imgext);
		@chmod($afolder.$imgname.$imgext, 0644);
		if ($settings['tinymce_enabled'] == 1) { include INCLUDES."buildlist.php"; }
		redirect(FUSION_SELF.$aidlink."&status=upy&ifolder=".$_GET['ifolder']."&img=".$imgname.$imgext);
	}
} else {
	opentable($locale['420']);
	echo "<form name='uploadform' method='post' action='".FUSION_SELF.$aidlink."&amp;ifolder=".$_GET['ifolder']."' enctype='multipart/form-data'>\n";
	echo "<table cellpadding='0' cellspacing='0' width='350' class='center'>\n<tr>\n";
	echo "<td width='80' class='tbl'>".$locale['421']."</td>\n";
	echo "<td class='tbl'><input type='file' name='myfile' class='textbox' style='width:250px;' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'>\n";
	echo "<input type='submit' name='uploadimage' value='".$locale['420']."' class='button' /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();

	if (isset($_GET['view']) && in_array($_GET['view'], $image_list)) {
		opentable($locale['440']);
		echo "<div style='text-align:center'><br />\n";
		$image_ext = strrchr($afolder.stripinput($_GET['view']),".");
		if (in_array($image_ext, array(".gif",".GIF",".ico",".jpg",".JPG",".jpeg",".JPEG",".png",".PNG"))) {
			echo "<img src='".$afolder.stripinput($_GET['view'])."' alt='".stripinput($_GET['view'])."' /><br /><br />\n";
		} else {
			echo $locale['441']."<br /><br />\n";
		}
		echo "<a href='".FUSION_SELF.$aidlink."&amp;ifolder=".$_GET['ifolder']."&amp;del=".stripinput($_GET['view'])."' onclick=\"return confirm('".$locale['470']."');\">".$locale['442']."</a>";
		echo "<br /><br />\n<a href='".FUSION_SELF.$aidlink."'>".$locale['402']."</a><br /><br />\n</div>\n";
		closetable();
	} else {
		opentable($locale['460']);
		echo "<table cellpadding='0' cellspacing='1' width='450' class='tbl-border center'>\n<tr>\n";
		echo "<td align='center' colspan='2' class='tbl2'>\n";
		echo "<span style='font-weight:".($_GET['ifolder'] == "images" ? "bold" : "normal")."'><a href='".FUSION_SELF.$aidlink."&amp;ifolder=images'>".$locale['422']."</a></span> |\n";
		echo "<span style='font-weight:".($_GET['ifolder'] == "imagesa" ? "bold" : "normal")."'><a href='".FUSION_SELF.$aidlink."&amp;ifolder=imagesa'>".$locale['423']."</a></span> |\n";
		echo "<span style='font-weight:".($_GET['ifolder'] == "imagesn" ? "bold" : "normal")."'><a href='".FUSION_SELF.$aidlink."&amp;ifolder=imagesn'>".$locale['424']."</a></span> |\n";
		echo "<span style='font-weight:".($_GET['ifolder'] == "imagesnc" ? "bold" : "normal")."'><a href='".FUSION_SELF.$aidlink."&amp;ifolder=imagesnc'>".$locale['427']."</a></span>\n";
		echo "</td>\n</tr>\n";
		if ($image_list) {
			for ($i=0; $i < $image_count; $i++) {
				if ($i % 2 == 0) { $row_color = "tbl1"; } else { $row_color = "tbl2"; }
				echo "<tr>\n<td class='".$row_color."'>".$image_list[$i]."</td>\n";
				echo "<td align='right' width='1%' class='".$row_color."' style='white-space:nowrap'>\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;ifolder=".$_GET['ifolder']."&amp;view=".$image_list[$i]."'>".$locale['461']."</a> -\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;ifolder=".$_GET['ifolder']."&amp;del=".$image_list[$i]."' onclick=\"return confirm('".$locale['470']."');\">".$locale['462']."</a></td>\n";
				echo "</tr>\n";
			}
			if ($settings['tinymce_enabled'] == 1) echo "<tr>\n<td align='center' colspan='2' class='tbl1'><a href='".FUSION_SELF.$aidlink."&amp;ifolder=".$_GET['ifolder']."&amp;action=update'>".$locale['464']."</a></td>\n</tr>\n";
		} else {
			echo "<tr>\n<td align='center' class='tbl1'>".$locale['463']."</td>\n</tr>\n";
		}
		echo "</table>\n";
		closetable();
	}
}

require_once THEMES."templates/footer.php";
?>