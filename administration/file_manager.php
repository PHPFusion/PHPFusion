<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: file_manager.php
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
require_once '../maincore.php';

if (!checkrights('FM') || !defined('iAUTH') || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect('../index.php'); }
require_once THEMES.'templates/admin_header.php';
include LOCALE.LOCALESET.'admin/image_uploads.php';

if (isset($_GET['status'])) {
	if ($_GET['status'] == 'del') {
		$title = $locale['400'];
		$message = '<strong>'.$locale['401'].'</strong>';
	} elseif ($_GET['status'] == 'upn') {
		$title = $locale['420'];
		$message = '<strong>'.$locale['425'].'</strong>';
	} elseif ($_GET['status'] == 'upy') {
		$title = $locale['420'];
		$message = "<img src='".$afolder.stripinput($_GET['img'])."' alt='".stripinput($_GET['img'])."' /><br /><br /><strong>".$locale['426']."</strong>";
	}
	opentable($title);
	echo "<div style='text-align:center'>'.$message.'</div>\n";
	closetable();
}

if (isset($_GET['del']) && in_array($_GET['del'], $image_list)) {
    unlink($afolder.stripinput($_GET['del']));
    if ($settings['tinymce_enabled'] == 1) { include INCLUDES.'buildlist.php'; }
    redirect(FUSION_SELF.$aidlink.'&status=del&ifolder='.$_GET['ifolder']);
} elseif (isset($_POST['uploadimage'])) {

	$error = '';

	$image_types = array(
		'.gif',
		'.GIF',
		'.jpeg',
		'.JPEG',
		'.jpg',
		'.JPG',
		'.png',
		'.PNG'
	);

	$imgext = strrchr(strtolower($_FILES['myfile']['name']), '.');
	$imgname = stripfilename(strtolower(substr($_FILES['myfile']['name'], 0, strrpos($_FILES['myfile']['name'], '.'))));
	$imgsize = $_FILES['myfile']['size'];
	$imgtemp = $_FILES['myfile']['tmp_name'];
	if (!in_array($imgext, $image_types)) {
		redirect(FUSION_SELF.$aidlink.'&status=upn&ifolder='.$_GET['ifolder']);
	} elseif (is_uploaded_file($imgtemp)){
		move_uploaded_file($imgtemp, $afolder.$imgname.$imgext);
		@chmod($afolder.$imgname.$imgext, 0644);
		if ($settings['tinymce_enabled'] == 1) { include INCLUDES.'buildlist.php'; }
		redirect(FUSION_SELF.$aidlink.'&status=upy&ifolder='.$_GET['ifolder'].'&img='.$imgname.$imgext);
	}
}

opentable($locale['100']);
echo "<div class='row' style='margin:0px;'><div class='col-sm-12 col-md-12 col-lg-12' style='padding:0px; margin-bottom:-20px;'>\n";
echo "<iframe style='width:100%; height:700px; border:0px;' src='".INCLUDES."filemanager/dialog.php?type=0&amp;fldr=".IMAGES."'></iframe>\n";
echo '</div></div>';
closetable();

require_once THEMES."templates/footer.php";
