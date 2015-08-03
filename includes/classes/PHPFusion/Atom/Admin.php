<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: Atom/Admin.php
| Author: Frederick MC Chan (hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
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
		add_breadcrumb(array('link'=>ADMIN."theme.php".$aidlink, 'title'=>$locale['theme_1000']));
		self::set_theme_active();
		if (isset($_POST['install_widget']) && fusion_get_settings('theme') == $_POST['install_widget']) {
			$widget_name = form_sanitizer($_POST['install_widget'], '');
			self::install_widget($widget_name);
		}
	}

	/**
	 * Set them as active
	 */
	protected function set_theme_active() {
		global $aidlink;
		if (isset($_POST['activate'])) {
			$theme_name = form_sanitizer($_POST['activate'], '');
			if (self::theme_installable($theme_name)) {
				$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$theme_name."' WHERE settings_name='theme'");
				if ($result) redirect(FUSION_SELF.$aidlink);
			}
		}
	}

	/**
	 * Process the sql insertion
	 * @param $theme_folder
	 */
	private function install_widget($theme_folder) {
		global $locale, $aidlink;
		if (iADMIN && self::get_edit_status($theme_folder) && file_exists(THEMES.$theme_folder."/theme_db.php") && !dbcount("(settings_name)", DB_SETTINGS_THEME, "settings_theme='".$theme_folder."'"))
		{
			include THEMES.$theme_folder."/theme_db.php";

			if (isset($theme_newtable) && is_array($theme_newtable)) {
				foreach ($theme_newtable as $item) {
					dbquery("CREATE TABLE ".$item);
				}
			}

			if (isset($theme_insertdbrow) && is_array($theme_insertdbrow)) {
				foreach ($theme_insertdbrow as $item) {
					print_p($item);
					dbquery("INSERT INTO ".$item);
				}
			}
			addNotice('success', sprintf($locale['theme_1019'], ucwords($theme_folder)));
			redirect(FUSION_SELF.$aidlink);
		}

	}

	/**
	 * Verify theme exist
	 * @param $theme_name
	 * @return bool
	 */
	public static function verify_theme($theme_name) {
		return (is_dir(THEMES.$theme_name) && file_exists(THEMES.$theme_name."/theme.php") && file_exists(THEMES.$theme_name."/styles.css") && fusion_get_settings('theme') == $theme_name) ? true : false;
	}

	/**
	 * Verify that theme exist and not active
	 * @param $theme_name
	 * @return bool
	 */
	static function theme_installable($theme_name) {
		return (is_dir(THEMES.$theme_name) && file_exists(THEMES.$theme_name."/theme.php") && file_exists(THEMES.$theme_name."/styles.css") && fusion_get_settings('theme') !== $theme_name) ? true : false;
	}

	/** Check if a theme widget file exist */
	static function theme_widget_exists($theme_name) {
		return (is_dir(THEMES.$theme_name) && file_exists(THEMES.$theme_name."/widget.php")) ? true : false;
	}

	public static function display_theme_editor($theme_name) {
		global $aidlink, $locale;
		// sanitize theme exist
		$theme_name = self::verify_theme($theme_name) ? $theme_name : "";
		if (!$theme_name) { redirect(clean_request("", array("aid"), true)); }

		add_breadcrumb(array('link'=>'', 'title'=>$locale['theme_1018']));

		// go with tabs
		$tab['title'] = array($locale['theme_1022'], $locale['theme_1023'], $locale['theme_1024']);
		$tab['id'] = array("dashboard", "widgets", "css");
		$tab['icon'] = array("fa fa-edit fa-fw", "fa fa-cube fa-fw", "fa fa-css3 fa-fw");
		if (isset($_GET['action'])) {
			$tab['title'][] = $locale['theme_1029'];
			$tab['id'][] = "close";
			$tab['icon'][] = "fa fa-close fa-fw";
		}

		$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $tab['id']) ? $_GET['section'] : "dashboard";
		$tab_active = $_GET['section'];
		$atom = new \PHPFusion\Atom\Atom();
		$atom->target_folder = $theme_name;
		$atom->theme_name = $theme_name;
		$atom->set_theme();
		$atom->load_theme_actions();

		echo opentab($tab, $tab_active, "theme_admin", true);
		// now include the thing as necessary
		switch($_GET['section']) {
			case "dashboard":
				// when we click delete preset
				if (isset($_POST['delete_preset']) && isnum($_POST['delete_preset']))
				{
					$file = dbarray(dbquery("SELECT theme_file FROM ".DB_THEME." WHERE theme_id='".$_POST['delete_preset']."'"));
					@unlink(THEMES.$file['theme_file']);
					dbquery("DELETE FROM ".DB_THEME." WHERE theme_id='".$_POST['delete_preset']."'");
					addNotice('success', $locale['theme_success_002']);
					redirect(FUSION_REQUEST);
				}
				$atom->display_theme_overview();
				break;
			case "widgets":
				$atom->display_theme_widgets();
				break;
			case "css":
				$atom->theme_editor();
				break;
			case "close":
				redirect(FUSION_SELF.$aidlink);
				break;
			default:
				break;
		}
		echo closetab();

	}

	public static function display_theme_list() {
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

				// Find widgets
				if (isset($theme_newtable) || isset($theme_insertdbrow)) {
					$data[$status][$folder]['widget'] = true;
					// count how many widget components
					$data[$status][$folder]['widgets'] = isset($theme_newtable) ? count($theme_newtable) : 0;
					// check if widgets installed - @todo: how to handle theme that only have new table but no row.
					$data[$status][$folder]['widget_status'] = dbcount("(settings_name)", DB_SETTINGS_THEME, "settings_theme='".$theme_folder."'") > 0 ? true : false;
				}

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

		krsort($data);
		foreach ($data as $status => $themes) {
			foreach($themes as $theme_name => $theme_data) {
				echo "<div class='panel panel-default'>\n";
				echo "<div class='panel-body'>\n";
				// Links
				echo "<div class='pull-left m-r-10'>".thumbnail($theme_data['screenshot'], '150px')."</div>\n";
				echo "<div class='btn-group pull-right m-l-20 m-t-20'>\n";
				if ($status == true) {
					echo "<a class='btn btn-primary btn-sm' href='".FUSION_SELF.$aidlink."&action=manage&amp;theme=".$theme_name."'><i class='fa fa-cog fa-fw'></i> ".$locale['theme_1005']."</a>\n";
				} else {
					echo "<a class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&action=set_active&amp;theme=".$theme_name."'><i class='fa fa-diamond fa-fw'></i> ".$locale['theme_1012']."</a>";
				}
				echo "</div>\n";
				echo "<div class='overflow-hide'>\n";
				echo "<h4 class='strong text-dark'>".($status == true ? "<i class='fa fa-diamond fa-fw'></i>" : "").$theme_data['title']."</h4>";
				echo "<div>\n";
				if (!empty($theme_data['description'])) echo "<div class='display-block m-b-10'>".$theme_data['description']."</div>";
				if (!empty($theme_data['license'])) echo "<span class='display-inline-block m-r-10'><span class='text-dark'>".$locale['theme_1013']."</span> ".$theme_data['license']."</span>\n";
				if (!empty($theme_data['version'])) echo "<span class='display-inline-block m-r-10'><span class='text-dark'>".$locale['theme_1014']."</span> ".$theme_data['version']."</span>\n";
				if (!empty($theme_data['author'])) echo "<span class='display-inline-block m-r-10'>".$theme_data['author']."</span>";
				if (!empty($theme_data['web'])) echo  "<span><a title='".$locale['theme_1015']."' href='".$theme_data['web']."'>".$locale['theme_1015']."</a></span>";
				echo "<div class='m-t-10'>\n";
				if ($status == true) {
					echo "<label class='label label-success m-r-5'>".$locale['theme_1006']."</label>\n";
				}
				if (isset($theme_data['widgets'])) {
					echo "<label class='label label-default'>".format_word($theme_data['widgets'], $locale['theme_1021'])."</label>\n";
				}
				echo "</div>\n";
				echo "</div>\n";
				echo "</div>\n";
				echo "</div>\n</div>\n";
				unset($theme_data);
			}
		}
	}

	public static function theme_uploader() {
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
		echo form_fileinput('theme_files', $locale['theme_1007'], '', array());
		echo form_button('upload', $locale['theme_1007'], 'upload theme', array('class'=>'btn btn-primary'));
		echo closeform();
	}
}
