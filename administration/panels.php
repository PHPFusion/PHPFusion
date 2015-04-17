<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: panels.php
| Author: Nick Jones (Digitanium)
| Author: Robert Gaudyn (Wooya)
| Author: Joakim Falk (Domi)
| Author: Frederick MC Chan (Hien)
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
pageAccess('P');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/panels.php";

/**
 * Class fusion_panels
 */
class fusion_panels {
	/**
	 * @var array|bool
	 */
	private $data = array(
		'panel_id' => 0,
		'panel_name' => '',
		'panel_filename' => '',
		'panel_content' => '',
		'panel_type' => 'php',
		'panel_side' => 1,
		'panel_order' => 0,
		'panel_access' => 0,
		'panel_display' => 0,
		'panel_status' => 0,
		'panel_url_list' => '',
		'panel_restriction' => 3,
		'panel_languages' => '',
	);
	/**
	 * @var string
	 */
	private $formaction = '';
	/**
	 * @var array
	 */
	private $panel_data = array();

	/**
	 * Sanitization Globals Vars
	 */
	public function __construct() {
		global $aidlink, $locale;
		$this->data['panel_languages'] = LANGUAGE;
		$this->data['panel_content'] = stripslashes($this->data['panel_content']);
		$_GET['panel_side'] = isset($_GET['panel_side']) && in_array($_GET['panel_side'] , array_flip(self::get_panel_grid())) ? $_GET['panel_side'] : 0;
		$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
		$_GET['status'] = isset($_GET['status']) ? $_GET['status'] : '';
		$_GET['panel_status'] = isset($_GET['panel_status']) ? $_GET['panel_status'] : 0;
		$this->panel_data = self::load_all_panels();

		switch ($_GET['action']) {
			case 'edit':
				$this->data = self::load_panel($_GET['panel_id']);
				$this->formaction = FUSION_SELF.$aidlink."&amp;section=panelform&amp;action=edit&amp;panel_id=".$_GET['panel_id'];
				break;
			case 'setstatus' :
				self::set_panel_status();
				break;
			case 'delete':
				self::delete_panel($_GET['panel_id']);
				break;
			default:
				$this->formaction = FUSION_SELF.$aidlink."&amp;section=panelform";
		}

		add_to_breadcrumbs(array('link'=>FUSION_SELF.$aidlink, 'title'=>$locale['600']));

		self::set_paneldb();
	}

	/**
	 * MYSQL save/update panels
	 */
	private function set_paneldb() {
		global $aidlink, $locale, $defender;

		if (isset($_POST['panel_save'])) {
			$this->data['panel_id'] = isset($_POST['panel_id']) ? form_sanitizer($_POST['panel_id'], '0', 'panel_id') : 0;
			$this->data['panel_name'] = isset($_POST['panel_name']) ? form_sanitizer($_POST['panel_name'], '', 'panel_name') : '';
			$this->data['panel_side'] = isset($_POST['panel_side']) ? form_sanitizer($_POST['panel_side'], 1, 'panel_side') : 1;
			$this->data['panel_access'] = isset($_POST['panel_access']) ? form_sanitizer($_POST['panel_access'], '0', 'panel_access') : 0;
			// panel name is unique
			$result = dbcount("(panel_id)", DB_PANELS, "panel_name='".$this->data['panel_name']."' AND panel_id !='".$this->data['panel_id']."'");
			if ($result) {
				$defender->stop();
				addNotice('danger', $locale['471']);
			}
			$this->data['panel_filename'] = isset($_POST['panel_filename']) ? form_sanitizer($_POST['panel_filename'], '', 'panel_filename') : '';
			// panel content formatting
			if (!$this->data['panel_filename']) {
				$this->data['panel_type'] = "php";
				$this->data['panel_content'] = isset($_POST['panel_content']) ? addslashes($_POST['panel_content']) : '';
				if (!$this->data['panel_content']) {
					$this->data['panel_content'] = "opentable(\"name\");\n"."echo \"Content\";\n"."closetable();";
					if ($this->data['panel_side'] == 1 || $this->data['panel_side'] == 4) {
						$this->data['panel_content'] =  "openside(\"name\");\n"."echo \"Content\";\n"."closeside();";
					}
				}
			} else {
				$this->data['panel_content'] = '';
				$this->data['panel_type'] = "file";
			}
            // need to add fourth option. only show in front page.
			$this->data['panel_restriction'] = isset($_POST['panel_restriction']) ? form_sanitizer($_POST['panel_restriction'], '', 'panel_restriction') : 0;
			// 3, show on all, 2 = show on home page. 1 = exclude , 0 = include
			//  post 0 to include all , 1 to exclude all, show all.
            if ($this->data['panel_restriction'] == '3') { // show on all
				$this->data['panel_display'] = ($this->data['panel_side'] !== 1 && $this->data['panel_side'] !== 4) ? 1 : 0;
				$this->data['panel_url_list'] = '';
            } elseif ($this->data['panel_restriction'] == '2') {
                // show on homepage only
                $this->data['panel_display'] = 0;
                $this->data['panel_url_list'] = '';
                if ($this->data['panel_side'] == 1 || $this->data['panel_side'] == 4) {
                    $this->data['panel_url_list'] = fusion_get_settings('opening_page'); // because 1 and 4 directly overide panel_display.
                }
			} else {
				// require panel_url_list in this case
				$this->data['panel_url_list'] = isset($_POST['panel_url_list']) ? form_sanitizer($_POST['panel_url_list'], '', 'panel_url_list') : '';
				if ($this->data['panel_url_list']) {
					$this->data['panel_url_list'] = str_replace(",", "\r\n", $this->data['panel_url_list']);
                    if ($this->data['panel_restriction'] == 1) { // exclude mode
                        $this->data['panel_display'] = ($this->data['panel_side'] !== 1 && $this->data['panel_side'] !== 4) ? 1 : 0;
                    } else { // include mode
                        $this->data['panel_display'] = ($this->data['panel_side'] !== 1 && $this->data['panel_side'] !== 4) ? 1 : 0;
                    }
                } else {
					$defender->stop();
					addNotice('danger', $locale['475']);
				}
			}

			$panel_languages = isset($_POST['panel_languages']) ? sanitize_array($_POST['panel_languages']) : array();
			if (!empty($panel_languages)) {
				$this->data['panel_languages'] = implode('.', $panel_languages);
			}
			// panel order .. add to last or sort - no need since we already have drag and drop... but if they dont have jquery this would be a good idea.
			/* $result = dbquery("SELECT panel_order FROM ".DB_PANELS." WHERE panel_side='".$panel_side."' ORDER BY panel_order DESC LIMIT 1");
			if (dbrows($result) != 0) {
				$data = dbarray($result);
				$neworder = $data['panel_order']+1;
			} else {
				$neworder = 1;
			} */
			if ($this->data['panel_id'] && self::verify_panel($this->data['panel_id'])) {
				dbquery_insert(DB_PANELS, $this->data, 'update');
				addNotice('info', $locale['482']);
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;section=listpanel&amp;status=su");
			} else {
				dbquery_insert(DB_PANELS, $this->data, 'save');
				addNotice('success', $locale['485']);
				if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;section=listpanel&amp;status=sn");
			}
		}
	}

	/**
	 * Return list of panels
	 * @return array
	 */
	private function get_panelOpts() {
		$panel_list = array();
		$current_panels = array();
        foreach($this->panel_data as $side => $panels) {
			foreach($panels as $data) {
				$current_panels[$data['panel_filename']] = $data['panel_filename'];
			}
		}
        // unset this panel if edit mode.
        if (isset($_GET['panel_id']) && isnum($_GET['panel_id']) && isset($_GET['action']) && $_GET['action'] == 'edit') {
            unset($current_panels[$this->data['panel_filename']]);
        }
		// find current installed panels.
		$temp = opendir(INFUSIONS);
		$panel_list['none'] = "None";
		while ($folder = readdir($temp)) {
			if (!in_array($folder, array(".", "..")) && !in_array($folder, $current_panels) && strstr($folder, "_panel")) {
				if (is_dir(INFUSIONS.$folder)) $panel_list[$folder] = $folder;
			}
		}
		closedir($temp);
		return $panel_list;
	}

	/**
	 * Return user groups array
	 * @return array
	 */
	static function get_accessOpts() {
		$ref = array();
		$user_groups = getusergroups();
		while (list($key, $user_group) = each($user_groups)) {
			$ref[$user_group[0]] = $user_group[1];
		}
		return $ref;
	}

	/**
	 * Return panel positions array
	 * @return array
	 */
	static function get_panel_grid() {
		global $locale;
		return array(
			'1' => $locale['420'],
			'2' => $locale['421'],
			'3' => $locale['425'],
			'4' => $locale['422'],
			'5' => $locale['426'],
			'6' => $locale['427'],
		);
	}

	/**
	 * Return page urls array
	 * @return array
	 */
	static function get_panel_url_list() {
		$list = array();
		$file_list = makefilelist(BASEDIR, ".|..|.htaccess|.DS_Store|config.php|config.temp.php|.gitignore|LICENSE|README.md|robots.txt|reactivate.php|rewrite.php|maintenance.php|maincore.php|lostpassword.php|index.php|error.php");
		foreach($file_list as $files) {
			$list[] = $files;
		}
		return $list;
	}

	/**
	 * Return restrictions type array
	 * @return array
	 */
	static function get_includeOpts() {
		global $locale;
		return array(
			'3' => $locale['459'],
			'2' => $locale['467'],
			'1' => $locale['464'],
			'0' => $locale['465'],
		);
	}

	/**
	 * Checks if a panel id is valid
	 * @param $id
	 * @return bool|string
	 */
	static function verify_panel($id) {
		if (isnum($id)) {
			return dbcount("(panel_id)", DB_PANELS, "panel_id='".intval($id)."'");
		}
		return false;
	}

	/**
	 * Fetch data for one panel
	 * @param $id
	 * @return array|bool
	 */
	static function load_panel($id) {
		if (isnum($id)) {
			$result = dbquery("SELECT * FROM ".DB_PANELS." WHERE panel_id='".intval($id)."'");
			if (dbrows($result)>0) {
				return dbarray($result);
			}
		}
		return array();
	}

	/**
	 * MYSQL actions delete panel
	 * @param $id
	 */
	static function delete_panel($id) {
		global $aidlink, $locale;
		if (self::verify_panel($id)) {
			$data = dbarray(dbquery("SELECT panel_side, panel_order FROM ".DB_PANELS." WHERE panel_id='".$_GET['panel_id']."'"));
			$result = dbquery("DELETE FROM ".DB_PANELS." WHERE panel_id='".$_GET['panel_id']."'");
			$result = dbquery("UPDATE ".DB_PANELS." SET panel_order=panel_order-1 WHERE panel_side='".$data['panel_side']."' AND panel_order>='".$data['panel_order']."'");
			addNotice('warning', $locale['489']);
			redirect(FUSION_SELF.$aidlink."&amp;status=del");
		}
	}

	/**
	 * MYSQL actions set active or inactive
	 */
	static function set_panel_status() {
		global $aidlink;
		$id = $_GET['panel_id'];
		if (self::verify_panel($id) && isnum($_GET['panel_status'])) {
			dbquery("UPDATE ".DB_PANELS." SET panel_status='".intval($_GET['panel_status'])."' WHERE panel_id='".intval($id)."'");
			redirect(FUSION_SELF.$aidlink);
		}
	}

	/**
	 * The Panel Editor Form
	 */
	public function add_panel_form() {
		global $locale;
        fusion_confirm_exit();
		echo "<div class='m-t-20'>\n";
		echo openform('panel_form', 'post', $this->formaction, array('max_tokens' => 1));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-8'>\n";
		openside('');
		echo form_hidden('', 'panel_id', 'panel_id', $this->data['panel_id']);
		echo form_text('panel_name', $locale['452'], $this->data['panel_name'], array('inline'=>1, 'required'=>1, )); //'error_text'=>$locale['470']
		echo form_select('panel_filename', $locale['453'], self::get_panelOpts(), $this->data['panel_filename'], array('inline'=>1));
        $grid_opts = self::get_panel_grid();
		echo form_select('panel_side', $locale['457'], $grid_opts, $this->data['panel_side'], array('inline'=>1));
		closeside();

		add_to_jquery("
		".($this->data['panel_filename'] > 0 ? "$('#pgrp').hide();" : "$('#pgrp').show();")."
		$('#panel_filename').bind('change', function(e) {
			if ($(this).val() > 0) { $('#pgrp').hide(); } else { $('#pgrp').show(); }
		});
		");

		echo "<div id='pgrp'>\n";
		echo form_textarea('panel_content', $locale['455'], $this->data['panel_content'], array('html'=>1, 'form_name'=>'panel_form', 'autosize'=>1, 'preview'=>1));
		echo "</div>\n";

		openside('');
		add_to_jquery("
		".(($this->data['panel_restriction'] == 3 || $this->data['panel_restriction'] == 2) ? "$('#panel_url_list-grp').hide();" : '')."
		$('#panel_restriction').bind('change', function(e) {
			if ($(this).val() == '3' || $(this).val() == '2') { $('#panel_url_list-grp').hide(); } else { $('#panel_url_list-grp').show(); }
		});
		");
		echo form_select('panel_restriction', 'Filter Type', self::get_includeOpts(), $this->data['panel_restriction'], array('inline'=>1));
		echo "<div id='panel_url_list-grp'>\n";
		echo "<div class='text-smaller'></div>\n";
		echo form_select('panel_url_list', $locale['462'], self::get_panel_url_list(), $this->data['panel_url_list'], array('inline'=>1, 'tags'=>1, 'multiple'=>1, 'width'=>'100%'));
		echo "</div>\n";
		echo form_hidden('', 'panel_display', 'panel_display', $this->data['panel_display']);
		closeside();

		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-4'>\n";
		openside('');
		echo form_select('panel_access',$locale['458'], self::get_accessOpts(), $this->data['panel_access']);
		echo form_button('panel_save', $locale['461'], $locale['461'], array('class'=>'btn-primary'));
		closeside();

		openside('');
		echo "<label class='label-control m-b-10'>".$locale['466']."</label>\n";
		$languages = !empty($this->data['panel_languages']) ? explode('.', $this->data['panel_languages']) : array();
		foreach(fusion_get_enabled_languages() as $language) {
			echo form_checkbox('panel_languages[]', $language, in_array($language, $languages) ? 1 : 0, array('class'=>'m-b-0', 'value'=>$language, 'input_id' => 'panel_lang-'.$language));
		}
		closeside();
		echo "</div>\n";
		echo "</div>\n";
		echo form_button('panel_save', $locale['461'], $locale['460'], array('class'=>'btn-primary'));
		echo closeform();
		echo "</div>\n";
	}

	/**
	 * Panel array
	 * @param int|null $panel_id
	 * @return array|string
	 */
	private function panels_list($panel_id = NULL) {
		$panel_list = array();
		$result = dbquery("SELECT panel_id, panel_filename FROM ".DB_PANELS." ORDER BY panel_id");
		while ($data = dbarray($result)) {
			$panels[] = $data['panel_filename'];
		}
		$temp = opendir(INFUSIONS);
		while ($folder = readdir($temp)) {
			if (!in_array($folder, array(".", "..")) && strstr($folder, "_panel")) {
				if (is_dir(INFUSIONS.$folder)) {
					if (!in_array($folder, $panels)) {
						$panel_list[] = ucwords(str_replace('_', ' ', $folder));
					}
				}
			}
		}
		closedir($temp);

		if ($panel_id != NULL) {
			return $panel_list[$panel_id];
		}
		sort($panel_list);

		return $panel_list;
	}

	/**
	 * Load entire DB_PANELS table
	 * @return array
	 */
	private function load_all_panels() {
		$list = array();
		$result = dbquery("SELECT * FROM ".DB_PANELS." ORDER BY panel_side ASC, panel_order ASC");
		if (dbrows($result)>0) {
			while ($data = dbarray($result)) {
				$list[$data['panel_side']][] = $data;
			}
		}
		return $list;
	}

	/**
	 * The container for each grid positions
	 * @param $side
	 * @return string
	 */
	private function panel_reactor($side) {
		global $locale, $aidlink;

		$grid_opts = self::get_panel_grid();
		$type = $grid_opts[$side];
		$k = 0;
		$count = dbcount("('panel_id')", DB_PANELS, "panel_side='".$side."'");
		$title = $type." <span id='side-".$side."' class='badge num pull-right'>".$count."</span>";
		$html = '';
		$html .= "<div class='panel panel-default' style='border-style: dashed'>\n<div class='panel-body clearfix'>\n";
		$html .= "<i class='fa fa-desktop m-r-10'></i> $title ";
		$html .= "</div>\n";
		$html .= "<ul id='panel-side".$side."' data-side='".$side."' style='list-style: none;' class='panels-list connected list-group p-10'>\n";
		if (isset($this->panel_data[$side])) {
			foreach($this->panel_data[$side] as $data) {
				$row_color = ($k%2 == 0 ? "tbl1" : "tbl2");
				$type = $data['panel_type'] == "file" ? $locale['423'] : $locale['424'];
				$html .= "<li id='listItem_".$data['panel_id']."' style='border:1px solid #ddd;' class='pointer list-group-item ".$row_color.($data['panel_status'] == 0 ? " pdisabled" : '')."'>\n";

				$html .= "<div class='handle'>\n";
				$html .= "<i class='pull-right display-inline-block m-t-5 m-r-10 fa fa-arrows-alt' title='move'></i>\n";

				$html .= "<div class='overflow-hide'>\n";
				$html .= "<a class='dropdown-toggle' data-toggle='dropdown'>\n";
				$html .= "<strong>".$data['panel_name']."</strong> <span class='caret'></span>\n\n";
				$html .= "</a>\n";

				$html .= "<ul class='dropdown-menu' role='panel-options'>\n";
				$html .= "<li style='padding:3px 20px;'>\n<i class='fa fa-bullseye m-r-10 m-t-5'></i> ".getgroupname($data['panel_access'])."</li>\n";
				$html .= "<li style='padding:3px 20px;'>\n<i class='fa fa-file-o m-r-10 m-t-5'></i> ".$type."</li>\n";
				$html .= "<li style='padding:3px 20px;'>\n<i class='fa fa-arrows-v m-r-10'></i> ".$data['panel_order']."</li>\n";
				$html .= "<li class='divider'></li>\n";
				$html .= "<li>\n<a href='".FUSION_SELF.$aidlink."&amp;section=panelform&amp;action=edit&amp;panel_id=".$data['panel_id']."'><i class='fa fa-pencil m-r-10 m-t-5'></i>".$locale['434']."</a>\n</li>\n";
				if ($data['panel_status'] == 0) {
					$html .= "<li>\n<a href='".FUSION_SELF.$aidlink."&amp;action=setstatus&amp;panel_status=1&amp;panel_id=".$data['panel_id']."'><i class='fa fa-check m-r-10 m-t-5'></i>".$locale['435']."</a>\n</li>\n";
				} else {
					$html .= "<li>\n<a href='".FUSION_SELF.$aidlink."&amp;action=setstatus&amp;panel_status=0&amp;panel_id=".$data['panel_id']."'><i class='fa fa-close m-r-10 m-t-5'></i>".$locale['436']."</a>\n</li>\n";
				}
				$html .= "<li>\n<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;panel_id=".$data['panel_id']."' onclick=\"return confirm('".$locale['440']."');\"><i class='fa fa-trash m-r-10 m-t-5'></i>".$locale['437']."</a>\n</li>\n";
				$html .= "</ul>\n";
				$html .= "</div>\n";
				$html .= "</div>\n";
				$html .= "</li>\n";
				$k++;

			}
		}
		$html .= "</ul>\n";
		$html .= "</div>\n";
		return $html;
	}

	/**
	 * Current Panel Template
	 */
	public function panel_listing() {
		global $locale, $aidlink;

		add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery-ui.js'></script>");
		add_to_jquery("
		$('.panels-list').sortable({
				handle : '.handle',
				placeholder: 'state-highlight',
				connectWith: '.connected',
				scroll: true,
				axis: 'auto',
				update: function () {
					var ul = $(this),
						order = ul.sortable('serialize'),
						i = 0;
					$('#info').load('panels_updater.php".$aidlink."&'+order);
					ul.find('.num').each(function(i) {
						$(this).text(i+1);
					});
					ul.find('li').removeClass('tbl2').removeClass('tbl1');
					ul.find('li:odd').addClass('tbl2');
					ul.find('li:even').addClass('tbl1');
					window.setTimeout('closeDiv();',2500);
				},
				receive: function () {
					var ul = $(this),
						order = ul.sortable('serialize'),
						pdata = ul.attr('data-side');
						if (pdata == 1) { var psidetext = '".$locale['420']."'; }
						if (pdata == 2) { var psidetext = '".$locale['421']."'; }
						if (pdata == 3) { var psidetext = '".$locale['425']."'; }
						if (pdata == 4) { var psidetext = '".$locale['422']."'; }
					ul.find('.pside').each(function() {
						$(this).text(psidetext);
					});
					$('#info').load('panels_updater.php".$aidlink."&panel_side='+pdata+'&'+order);
				}
			});
		");

		echo "<div class='m-t-20'>\n";
		echo "<div id='info'></div>\n";
		echo "<div class='well text-center'>".$locale['410']."</div>\n";
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
		echo self::panel_reactor(5);
		echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
		echo self::panel_reactor(1);
		echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-6 col-lg-6'>\n";
		echo self::panel_reactor(2);
		echo "<div class='well text-center strong text-dark'>".$locale['606']."</div>\n";
		echo self::panel_reactor(3);
		echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-3 col-lg-3'>\n";
		echo self::panel_reactor(4);
		echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
		echo self::panel_reactor(6);
		echo "</div>\n</div>\n";
		echo "</div>\n";

		//Unused Panels in the directory
		$panel_list = self::panels_list();
		$title = $locale['602'].": ".count($panel_list)." ".(count($panel_list) == 1 ? $locale['605'] : $locale['604']);
		//opentable($title, "off");
		echo "<div class='panel panel-default'>\n";
		echo "<div class='panel-heading'>".$title."</div>\n";
		echo "<div class='panel-body text-dark'>\n";
		$k = 0;
		foreach ($panel_list as $panel) {
			echo "<div style='float:left;'>".$panel."</div>\n";
			echo "<div style='float:right; width:250px;'>";
			echo "</div>\n";
			echo "<div style='float:right; width:10%;'>File</div>\n";
			echo "<div style='clear:both;'></div>\n";
		}
		echo "</div>\n</div>\n";
	}

}

// do the table
opentable($locale['600']);
$fusion_panel = new fusion_panels();
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? $fusion_panel->verify_panel($_GET['panel_id']) : 0;
// build a new interface
$tab_title['title'][] = $locale['407'];
$tab_title['id'][] = 'listpanel';
$tab_title['icon'][] = '';
$tab_title['title'][] = $edit ? $locale['409'] : $locale['408'];
$tab_title['id'][] = 'panelform';
$tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
$tab_active = tab_active($tab_title, $edit ? 1 : 0, true, 1);
echo opentab($tab_title, $tab_active, 'id', FUSION_SELF.$aidlink);
echo opentabbody($tab_title['title'][0], 'listpanel', $tab_active, 1);
$fusion_panel->panel_listing();
echo closetabbody();
if (isset($_GET['section']) && $_GET['section'] == 'panelform') {
	echo opentabbody($tab_title['title'][1], 'panelform', $tab_active, 1);
	$fusion_panel->add_panel_form();
	echo closetabbody();
}
closetable();
require_once THEMES."templates/footer.php";
