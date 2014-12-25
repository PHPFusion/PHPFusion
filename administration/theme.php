<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme.php
| Author: Frederick MC Chan (Hien)
| Co-Author: PHP-Fusion Development Team
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
require_once THEMES."templates/admin_header.php";
require_once CLASSES."atom.class.php";
require_once LOCALE.LOCALESET."admin/theme.php";
if (isset($_GET['status'])) {
	$message = '';
	if ($_GET['status'] == 'uploaded') {
		$message = "Theme was uploaded to your theme folder successfully.";
	}
	elseif ($_GET['status'] == 'failed') {
		$message = "The file failed to be verified. Please extract the zip and do a manual FTP upload.";
	}
	elseif ($_GET['status'] == 'version_error') {
		$message = "Your server do not support ZipArchive. Please extract the zip and do a manual FTP upload.";
	}
	elseif ($_GET['status'] == 'success') {
		$message = "Theme presets is successfully created.";
	}
	if ($message) {
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
	}
}

opentable('Theme Management');

$edit_mode = ((isset($_GET['action']) && $_GET['action'] == 'edit') && (isset($_POST['theme']) && file_exists(THEMES.$_POST['theme']))) ? 1 : 0;

$tab_title['title'][] = 'Current Themes';
$tab_title['id'][] = 'its';
$tab_title['icon'][] = '';

if ($edit_mode) {
	$tab_title['title'][] = 'Edit Theme';
	$tab_title['id'][] = 'edt';
	$tab_title['icon'][] = '';
} else {
	$tab_title['title'][] = 'Install New Theme';
	$tab_title['id'][] = 'upt';
	$tab_title['icon'][] = '';
}

$active_tab = tab_active($tab_title, $edit_mode);

echo opentab($tab_title, $active_tab, 'theme_tab');
echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $active_tab);
echo "<div class='m-t-20'>\n";
list_theme();
echo "</div>\n";
echo closetabbody();
if ($edit_mode) {
	echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $active_tab);
	echo "<div class='m-t-20'>\n";
	theme_editor();
	echo "</div>\n";
	echo closetabbody();
} else {
	echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $active_tab);
	echo "<div class='m-t-20'>\n";
	theme_uploader();
	echo "</div>\n";
	echo closetabbody();
}
echo closetab();
closetable();

/* Theme Engine editor */
function theme_editor() {
	global $aidlink;
	if (!isset($_POST['theme'])) redirect(FUSION_SELF.$aidlink);
	$atom = new atom();
	$atom->target_folder = $_POST['theme'];
	$atom->theme_name = $_POST['theme'];
	$atom->load_theme();
	$atom->set_theme();
	$atom->render_theme_presets();
	$atom->theme_editor();
}


function list_theme() {
	global $aidlink, $settings;
	$data = array();
	$_dir = makefilelist(THEMES, ".|..|templates|admin_templates", TRUE, "folders");
	foreach($_dir as $folder) {
		$theme_dbfile = '/theme_db.php';
		$status = $settings['theme'] == $folder ? 1 : 0;
		if (file_exists(THEMES.$folder.$theme_dbfile)) {
			// 9.00 compatible theme.
			include_once THEMES.$folder.$theme_dbfile;
			$data[$status][$folder]['readme'] = isset($theme_readme) ? $theme_readme : '';
			$data[$status][$folder]['folder'] = isset($theme_folder) && file_exists(THEMES.$theme_folder.'/theme.php') ? THEMES.$theme_folder : '';
			$data[$status][$folder]['screenshot'] = isset($theme_screenshot) && file_exists(THEMES.$theme_folder."/".$theme_screenshot) ? THEMES.$theme_folder."/".$theme_screenshot : IMAGES.'imagenotfound.jpg';;
			$data[$status][$folder]['title'] = isset($theme_title) ? $theme_title : '';
			$data[$status][$folder]['web'] = isset($theme_web) ? $theme_web : '';
			$data[$status][$folder]['author'] = isset($theme_author) ? $theme_author : '';
			$data[$status][$folder]['license'] = isset($theme_license) ? $theme_license : '';
			$data[$status][$folder]['version'] = isset($theme_version) ? $theme_version : '';
			$data[$status][$folder]['description'] = isset($theme_description) ? $theme_description : '';
		} else {
			// older legacy theme.
			if (file_exists(THEMES.$folder.'/theme.php')) {
				$theme_screenshot = '/screenshot.jpg';
				$data[$status][$folder] = array('readme'=>'','folder'=>'','title'=>'','screenshot'=>'','author'=>'','license'=>'','version'=>'','description'=>'No description available for this theme.');
				$data[$status][$folder]['folder'] = THEMES.$folder;
				$data[$status][$folder]['title'] = $folder;
				$data[$status][$folder]['screenshot'] = file_exists(THEMES.$folder.$theme_screenshot) ? THEMES.$folder.$theme_screenshot : IMAGES.'imagenotfound.jpg';
			}
		}
	}

	// list down the active one.
	foreach ($data[1] as $theme_name => $theme_data) {
		//print_p($theme_data);
		echo "<div class='panel panel-default'>\n";
		echo "<div class='panel-body'>\n";
		echo "<div class='pull-left m-r-10'>".thumbnail($theme_data['screenshot'], '150px')."</div>\n";
		echo "<div class='btn-group pull-right m-t-20'>\n";
		echo openform('editfrm', 'editfrm', 'post', FUSION_SELF.$aidlink."&amp;action=edit", array('notice'=>0, 'downtime'=>0));
		echo form_button('Configure Theme', 'theme', 'theme', $theme_name, array('class'=>'btn-default'));
		echo closeform();
		echo "</div>\n";
		echo "<div class='overflow-hide'>\n";
		echo "<div class='strong text-dark m-b-20'>".$theme_data['title']."</div>";
		echo "<div class='text-smaller'>\n";
		if (!empty($theme_data['description'])) echo "<span>".$theme_data['description']."</span><br/>";
		if (!empty($theme_data['license'])) echo "<span class='display-inline-block m-r-10'><span class='text-dark'>License</span> ".$theme_data['license']."</span>\n";
		if (!empty($theme_data['version'])) echo "<span class='display-inline-block m-r-10'><span class='text-dark'>Version</span> ".$theme_data['version']."</span>\n";
		if (!empty($theme_data['author'])) echo "<span class='display-inline-block m-r-10'>Created by ".$theme_data['author']."</span>";
		if (!empty($theme_data['web'])) echo  "<span><a href='".$theme_data['web']."'>".$theme_data['web']."</a></span>";
		echo "<h5><label class='label label-success'>Primary Theme</label></h5>\n";
		echo "</div>\n";

		echo "</div>\n";

		echo "</div>\n</div>\n";
	}


	foreach ($data[0] as $theme_name => $theme_data) {
		//print_p($theme_data);
		echo "<div class='panel panel-default'>\n";
		echo "<div class='panel-body'>\n";
		echo "<div class='pull-left m-r-10'>".thumbnail($theme_data['screenshot'], '150px')."</div>\n";
		echo "<div class='btn-group pull-right m-t-20'>\n";
		echo "<a href='' class='btn btn-default btn-sm'>Set as Active</a>";
		echo "<a href='' class='btn btn-default btn-sm'>Delete</a>";
		echo "</div>\n";
		echo "<div class='overflow-hide'>\n";
		echo "<div class='strong text-dark m-b-20'>".$theme_data['title']."</div>";
		echo "<div class='text-smaller'>\n";
		if (!empty($theme_data['description'])) echo "<span>".$theme_data['description']."</span><br/>";
		if (!empty($theme_data['license'])) echo "<span class='display-inline-block m-r-10'><span class='text-dark'>License</span> ".$theme_data['license']."</span>\n";
		if (!empty($theme_data['version'])) echo "<span class='display-inline-block m-r-10'><span class='text-dark'>Version</span> ".$theme_data['version']."</span>\n";
		if (!empty($theme_data['author'])) echo "<span class='display-inline-block m-r-10'>Created by ".$theme_data['author']."</span>";
		if (!empty($theme_data['web'])) echo  "<span><a href='".$theme_data['web']."'>".$theme_data['web']."</a></span>";
		echo "</div>\n";
		echo "</div>\n";
		echo "</div>\n</div>\n";
	}
}

function theme_uploader() {
	global $aidlink, $defender;
	if (isset($_POST['upload'])) {
		require_once INCLUDES."infusions_include.php";
		$src_file = 'theme_files';
		$target_folder = THEMES;
		$valid_ext = '.zip';
		$max_size = 5*1000*1000;
		$upload = upload_file($src_file, '', $target_folder, $valid_ext, $max_size);
		if ($upload['error'] !='0') {
			$defender->stop();
			$defender->setNoticeTitle('Theme was not uploaded due to the following errors:');
			switch($upload['error']) {
				case 1:
					$defender->addNotice('Theme package is too big. Please use only '.parsebytesize($max_size, 2));
					break;
				case 2:
					$defender->addNotice('Theme package is not a valid. Please only compress themes using .zip file extension.');
					break;
				case 3:
					$defender->addNotice('Unkown Error');
					break;
				case 4:
					$defender->addNotice('Theme file was not being uploaded by the server.');
					break;
				default :
					$defender->addNotice('Unknown Error');
			}
		} else {
			$target_file = $target_folder.$upload['target_file'];
			if (is_file($target_file)) {
				$path = pathinfo(realpath($target_file), PATHINFO_DIRNAME);
				if (class_exists('ZipArchive')) {
					$zip = new ZipArchive;
					$res = $zip->open($target_file);
					if ($res === TRUE) {
						// extract it to the path we determined above
						$zip->extractTo($path);
						$zip->close();
						@unlink($target_file);
						redirect(FUSION_SELF.$aidlink."&amp;status=uploaded");
					} else {
						@unlink($target_file);
						redirect(FUSION_SELF.$aidlink."&amp;status=failed");
					}
				} else {
					@unlink($target_file);
					redirect(FUSION_SELF.$aidlink."&amp;status=version_error");
				}
			}
		}
	}
	echo openform('inputform', 'inputform', 'post', FUSION_SELF.$aidlink, array('enctype'=>1));
	echo form_fileinput('Upload Theme Package', 'theme_files', 'theme_files', '', '', array());
	echo form_button('Upload Theme', 'upload', 'upload', 'upload theme', array('class'=>'btn btn-primary'));
	echo closeform();
}


require_once THEMES."templates/footer.php";

?>