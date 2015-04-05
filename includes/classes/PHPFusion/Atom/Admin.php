<?php

namespace PHPFusion\Atom;
require_once LOCALE.LOCALESET."admin/theme.php";

/**
 * Administration Page for Theme Settings
 * Class Admin
 * @package PHPFusion\Atom
 */
class Admin {

	public function __construct() {
		global $aidlink, $locale;
		$_GET['action'] = isset($_GET['action']) && $_GET['action'] ? $_GET['action'] : '';
		$_GET['status'] = isset($_GET['status']) && $_GET['status'] ? $_GET['status'] : '';
		add_to_breadcrumbs(array('link'=>ADMIN."theme.php".$aidlink, 'title'=>$locale['theme_1000']));
		self::set_theme_active();
	}

	protected function set_theme_active() {
		global $aidlink;
		if (isset($_POST['activate'])) {
			$theme_name = form_sanitizer($_POST['activate'], '');
			if (self::verify_theme($theme_name)) {
				$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$theme_name."' WHERE settings_name='theme'");
				if ($result) redirect(FUSION_SELF.$aidlink);
			}
		}
	}

	static function get_edit_status() {
		$theme_name = isset($_POST['theme']) ? stripinput($_POST['theme']) : '';
		return (is_dir(THEMES.$theme_name) && file_exists(THEMES.$theme_name."/theme.php") && file_exists(THEMES.$theme_name."/styles.css") && fusion_get_settings('theme') == $theme_name) ? true : false;
	}
	// what else to verify on theme?
	static function verify_theme($theme_name) {
		return (is_dir(THEMES.$theme_name) && file_exists(THEMES.$theme_name."/theme.php") && file_exists(THEMES.$theme_name."/styles.css") && fusion_get_settings('theme') !== $theme_name) ? true : false;
	}

	static function theme_editor() {
		global $aidlink;
		if (!isset($_POST['theme'])) redirect(FUSION_SELF.$aidlink);
		add_to_breadcrumbs(array('link'=>'', 'title'=>'Theme Configuration'));
		// The working engine class to build css and set the db
		$atom = new \PHPFusion\Atom\Atom();
		$atom->target_folder = $_POST['theme'];
		$atom->theme_name = $_POST['theme'];
		$atom->load_theme_actions();
		$atom->set_theme();
		$atom->render_theme_presets();
		$atom->theme_editor();
	}

	static function list_theme() {
		global $locale, $aidlink, $settings;
		$data = array();
		$_dir = makefilelist(THEMES, ".|..|templates|admin_templates", TRUE, "folders");
		foreach($_dir as $folder) {
			$theme_dbfile = '/theme_db.php';
			$status = $settings['theme'] == $folder ? 1 : 0;
			if (file_exists(THEMES.$folder.$theme_dbfile)) {
				// 9.00 compatible theme.
				$theme_folder = '';
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
			echo openform('editfrm-active-'.$theme_name, 'post', FUSION_SELF.$aidlink."&amp;action=edit", array('notice'=>0, 'max_tokens' => 1));
			echo form_button('theme', $locale['theme_1005'], $theme_name, array('class'=>'btn-default'));
			echo closeform();
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo "<h4 class='strong text-dark m-b-20'>".$theme_data['title']."</h4>";
			echo "<div>\n";
			if (!empty($theme_data['description'])) echo "<span>".$theme_data['description']."</span><br/>";
			if (!empty($theme_data['license'])) echo "<span class='display-inline-block m-r-10'><span class='text-dark'>License</span> ".$theme_data['license']."</span>\n";
			if (!empty($theme_data['version'])) echo "<span class='display-inline-block m-r-10'><span class='text-dark'>Version</span> ".$theme_data['version']."</span>\n";
			if (!empty($theme_data['author'])) echo "<span class='display-inline-block m-r-10'>Created by ".$theme_data['author']."</span>";
			if (!empty($theme_data['web'])) echo  "<span><a href='".$theme_data['web']."'>".$theme_data['web']."</a></span>";
			echo "<h5><label class='label label-success'>".$locale['theme_1006']."</label></h5>\n";
			echo "</div>\n";
			echo "</div>\n";

			echo "</div>\n</div>\n";
		}
		// list down the rest of the themes
		foreach ($data[0] as $theme_name => $theme_data) {
			//print_p($theme_data);
			echo "<div class='panel panel-default'>\n";
			echo "<div class='panel-body'>\n";
			echo "<div class='pull-left m-r-10'>".thumbnail($theme_data['screenshot'], '150px')."</div>\n";
			echo openform('editfrm-inactive-'.$theme_name, 'post', FUSION_SELF.$aidlink."&amp;action=edit", array('class'=>'pull-right', 'notice'=>0, 'max_tokens' => 1));
			echo form_button('activate', $locale['theme_1012'], $theme_name, array('class'=>'btn-primary'));
			echo closeform();
			echo "<div class='overflow-hide'>\n";
			echo "<h4 class='strong text-dark m-b-20'>".$theme_data['title']."</h4>";
			echo "<div>\n";
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

	static function theme_uploader() {
		global $locale, $aidlink, $defender;

		if (isset($_POST['upload'])) {
			require_once INCLUDES."infusions_include.php";
			$src_file = 'theme_files';
			$target_folder = THEMES;
			$valid_ext = '.zip';
			$max_size = 5*1000*1000;
			$upload = upload_file($src_file, '', $target_folder, $valid_ext, $max_size);
			if ($upload['error'] !='0') {
				$defender->stop();
				switch($upload['error']) {
					case 1:
						addNotice('danger', sprintf($locale['theme_error_001'], parsebytesize($max_size, 2)));
						break;
					case 2:
						addNotice('danger', $locale['theme_error_002']);
						break;
					case 3:
						addNotice('danger', $locale['theme_error_003']);
						break;
					case 4:
						addNotice('danger', $locale['theme_error_004']);
						break;
					default :
						addNotice('danger', $locale['theme_error_003']);
				}
			} else {
				$target_file = $target_folder.$upload['target_file'];
				if (is_file($target_file)) {
					$path = pathinfo(realpath($target_file), PATHINFO_DIRNAME);
					if (class_exists('ZipArchive')) {
						$zip = new ZipArchive;
						$res = $zip->open($target_file);
						if ($res === TRUE) {
							// checks if first folder is theme.php
							if ($zip->locateName('theme.php') !== false) {
								// extract it to the path we determined above
								$zip->extractTo($path);
								addNotice('success', $locale['theme_success_001']);
							} else {
								$defender->stop();
								addNotice('danger', $locale['theme_error_005']);
							}
							$zip->close();
							@unlink($target_file);
							redirect(FUSION_SELF.$aidlink);
						} else {
							addNotice('danger', $locale['theme_error_005']);
							@unlink($target_file);
							redirect(FUSION_SELF.$aidlink);
						}
					} else {
						addNotice('warning', $locale['theme_error_006']);
						@unlink($target_file);
						redirect(FUSION_SELF.$aidlink);
					}
				}
			}
		}

		echo openform('inputform', 'post', FUSION_SELF.$aidlink, array('enctype'=>1, 'max_tokens' => 1));
		echo form_fileinput($locale['theme_1007'], 'theme_files', 'theme_files', '', '', array());
		echo form_button('upload', $locale['theme_1007'], 'upload theme', array('class'=>'btn btn-primary'));
		echo closeform();
	}
}
