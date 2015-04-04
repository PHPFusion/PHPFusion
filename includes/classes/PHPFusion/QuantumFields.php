<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: QuantumFields.php
| Author: Frederick MC Chan (Hien)
| Co-Author: Chris Smith <code+php@chris.cs278.org>,
| Co-Author: Frank Bültge <frank@bueltge.de>
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion;

include LOCALE.LOCALESET.'admin/fields.php';

class QuantumFields {
	/**
	 * Set the Quantum System Fields Page Title
	 */
	protected $system_title = '';

	/**
	 * Set the admin rights to Quantum Fields Admin
	 * @var string
	 */
	protected $admin_rights = '';

	/**
	 * Set the Database to install field structure records
	 * Refer to v7.x User Fields Structrue
	 * @var string - category_db = DB_USER_FIELDS_CAT
	 * @var string - field_db = DB_USER_FIELDS
	 */
	protected $category_db = '';
	protected $field_db = '';
	/**
	 * Set system API folder paths
	 * Refer to v7.x User Fields API
	 * @var string - plugin_locale_folder (LOCALE.LOCALESET."user_fields/")
	 * @var string - plugin_folder (INCLUDES."user_fields/")
	 */
	protected $plugin_folder = '';
	protected $plugin_locale_folder = '';

	/**
	 * Set as `display` to show array values output
	 * Two methods - input or display
	 * @var string
	 */
	protected $method = 'input';

	/**
	 * feed $userData or $data here to append display_fields() values
	 * use the setter function setCallbackData()
	 * @var array
	 */
	protected $callback_data = array();

	// callback on the structure - use getters
	protected $fields = array(); // maybe can mix with enabled_fields.
	protected $cat_list = array();

	// debug mode
	protected $debug = false;
	protected $module_debug = false;
	protected $dom_debug = false;

	// System Internals
	private $input_page = 1;
	private $max_rows = 0;
	private $locale = array();
	private $page_list = array();
	private $page = array();
	private $enabled_fields = array();
	private $get_available_modules = array();
	private $available_field_info = array();
	private $user_field_dbinfo = '';

	/**
	 * @return array
	 */
	public function getCatList() {
		return $this->cat_list;
	}

	/**
	 * @return array
	 */
	public function getFields() {
		return $this->fields;
	}

	/* Shorthand Constructor for Quantum Admin UI */
	public function boot() {
		global $locale;
		pageAccess($this->admin_rights);
		define('IN_QUANTUM', true);

		if ($this->system_title) {
			add_to_breadcrumbs(array('link' => FUSION_REQUEST, 'title' => $this->system_title));
			add_to_title($this->system_title.' | ');
		}

		$this->install_quantum();
		if ($this->method == 'input') {
			$this->get_structureData();
			$this->load_field_cats();
			$this->sql_move_fields();
			$this->sql_delete_category();
			$this->sql_delete_fields();
			$this->get_available_modules();
		}
		$this->quantum_display_fields();
	}

	/**
	 * `input` renders field.
	 * `display` renders data
	 * @param string $method ('input' or 'display')
	 */
	public function setMethod($method) {
		$this->method = $method;
	}

	/**
	 * If modules are used, specify fields module path
	 * API follows Version 7.00's User Fields module.
	 * @param string $plugin_folder_path
	 */
	public function setPluginFolder($plugin_folder_path) {
		$this->plugin_folder = $plugin_folder_path;
	}

	/**
	 * If modules are used, specify fields module locale libs folder path
	 * API follows Version 7.00's User Fields Module.
	 * @param string $plugin_locale_folder
	 */
	public function setPluginLocaleFolder($locale_folder_path) {
		$this->plugin_locale_folder = $locale_folder_path;
	}

	/**
	 * Give your Quantum based system a name. Will add to breadcrumbs if available.
	 * @param string $system_title
	 */
	public function setSystemTitle($system_title) {
		$this->system_title = $system_title;
	}

	/**
	 * Quantum System Custom Locale File.
	 * Default path are LOCALE.LOCALESET.user_fields.php
	 * @param string $locale_file
	 */
	public function setLocaleFile($locale_file) {
		$this->locale_file = $locale_file;
	}

	/**
	 * Database Handler for Category Structuring
	 * If it does not exist, quantum will automatically build a template onload.
	 * @param string $category_db
	 */
	public function setCategoryDb($category_db) {
		$this->category_db = $category_db;
	}

	/**
	 * Database Handler for Field Structuring
	 * If it does not exist, quantum will automatically build a template onload.
	 * @param string $field_db
	 */
	public function setFieldDb($field_db) {
		$this->field_db = $field_db;
	}

	/**
	 * Additional data-id referencing.
	 * $userdata for instance.
	 * @param array $callback_data
	 */
	public function setCallbackData($callback_data) {
		$this->callback_data = $callback_data;
	}

	/**
	 * The internal admin rights by a user to use this system.
	 * if specified, to lock down to certain user rights.
	 * @param string $admin_rights
	 */
	public function setAdminRights($admin_rights) {
		$this->admin_rights = $admin_rights;
	}

	/**
	 * DB Installers for integrity/verification checks.
	 */
	public function install_quantum() {
		if (!db_exists($this->category_db)) {
			dbquery("CREATE TABLE ".$this->category_db." (
				field_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT ,
				field_cat_name VARCHAR(200) NOT NULL ,
				field_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
				field_cat_db VARCHAR(100) NOT NULL,
				field_cat_index VARCHAR(200) NOT NULL,
				field_cat_class VARCHAR(50) NOT NULL,
				field_cat_order SMALLINT(5) UNSIGNED NOT NULL ,
				PRIMARY KEY (field_cat_id)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		}
		if (!db_exists($this->field_db)) {
			dbquery("CREATE TABLE ".$this->field_db." (
				field_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
				field_title VARCHAR(50) NOT NULL,
				field_name VARCHAR(50) NOT NULL,
				field_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
				field_type VARCHAR(25) NOT NULL,
				field_default TEXT NOT NULL,
				field_options TEXT NOT NULL,
				field_error VARCHAR(50) NOT NULL,
				field_required TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
				field_log TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
				field_registration TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
				field_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
				field_config TEXT NOT NULL,
				PRIMARY KEY (field_id),
				KEY field_order (field_order)
			) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
		}
	}

	/* Returns array structure for render */
	public function get_structureData() {
		// get the page first.
		$this->page = dbquery_tree_full($this->category_db, 'field_cat_id', 'field_parent', "ORDER BY field_cat_order ASC");
		$result = dbquery("SELECT field.*, cat.field_cat_id, cat.field_cat_name,  cat.field_parent, cat.field_cat_class,
		root.field_cat_id as page_id, root.field_cat_name as page_name, root.field_cat_db, root.field_cat_index FROM
		".$this->field_db." field
		LEFT JOIN ".$this->category_db." cat on (cat.field_cat_id = field.field_cat)
		LEFT JOIN ".$this->category_db." root on (root.field_cat_id = cat.field_parent)
		ORDER BY cat.field_cat_order ASC, field.field_order ASC
		");
		$this->max_rows = dbrows($result);
		if ($this->max_rows > 0) {
			while ($data = dbarray($result)) {
				$this->fields[$data['field_cat']][] = $data;
			}
		}
	}

	/* Outputs Quantum Admin Dummy Fields */
	public function quantum_display_fields() {
		global $aidlink, $locale;
		if ($this->debug) print_p($_POST);
		opentable($this->system_title);
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-8 col-md-8 col-lg-8'>\n";
		if (!empty($this->page[0])) {
			$tab_title = array();
			foreach ($this->page[0] as $page_id => $page_data) {
				$tab_title['title'][$page_id] = self::parse_label($page_data['field_cat_name']);
				$tab_title['id'][$page_id] = $page_id;
				$tab_title['icon'][$page_id] = '';
			}
			$tab_active = tab_active($tab_title, 1);
			echo opentab($tab_title, $tab_active, 'uftab');
			foreach ($this->page[0] as $page_id => $page_details) {
				echo opentabbody($tab_title['title'][$page_id], $tab_title['id'][$page_id], $tab_active);
				// load all categories here.
				if ($this->debug) {
					echo "<div class='m-t-20 text-dark'>\n";
					if ($page_id == 1) {
						echo sprintf($locale['fields_0100'], DB_USERS);
					} else {
						echo sprintf($locale['fields_0101'], $page_details['field_cat_db'], $page_details['field_cat_index']);
					}
					echo "</div>\n";
				}
				if (isset($this->page[$page_id])) {
					echo "<div class='list-group-item display-inline-block m-t-20'>\n";
					echo "<span class='strong'>".self::parse_label($page_details['field_cat_name'])."</span> <a class='text-smaller' href='".FUSION_SELF.$aidlink."&amp;action=cat_edit&amp;cat_id=".$page_id."'>".$locale['edit']."</a> - ";
					echo "<a class='text-smaller' href='".FUSION_SELF.$aidlink."&amp;action=cat_delete&amp;cat_id=".$page_id."'>".$locale['delete']."</a>";
					echo "</div>\n";

					echo "<div class='clearfix m-t-20'>\n";
					$i = 0;
					$counter = count($this->page[$page_id])-1;
					foreach ($this->page[$page_id] as $cat_id => $field_cat) {
						// field category information
						if ($this->debug) print_p($field_cat);
						echo "<div class='clearfix'>\n";
						echo form_para(self::parse_label($field_cat['field_cat_name']), $cat_id.'-'.$field_cat['field_cat_name'], 'profile_category_name display-inline-block pull-left');
						echo "<div class='pull-left m-t-10 m-l-10'>\n";
						if ($i != 0) echo "<a class='text-smaller' href='".FUSION_SELF.$aidlink."&amp;action=cmu&amp;cat_id=".$cat_id."&amp;parent_id=".$field_cat['field_parent']."&amp;order=".($field_cat['field_cat_order']-1)."'>".$locale['move_up']."</a> - ";
						if ($i !== $counter) echo "<a class='text-smaller' href='".FUSION_SELF.$aidlink."&amp;action=cmd&amp;cat_id=".$cat_id."&amp;parent_id=".$field_cat['field_parent']."&amp;order=".($field_cat['field_cat_order']+1)."'>".$locale['move_down']."</a> - ";
						echo "<a class='text-smaller' href='".FUSION_SELF.$aidlink."&amp;action=cat_edit&amp;cat_id=".$cat_id."'>".$locale['edit']."</a> - ";
						echo "<a class='text-smaller' href='".FUSION_SELF.$aidlink."&amp;action=cat_delete&amp;cat_id=".$cat_id."'>".$locale['delete']."</a>";
						echo "</div>\n";
						echo "</div>\n";
						if (isset($this->fields[$cat_id])) {
							$k = 0;
							$item_counter = count($this->fields[$cat_id])-1;
							foreach ($this->fields[$cat_id] as $arr => $field_data) {
								if ($this->debug) print_p($field_data);
								echo "<div class='text-left'>\n";
								if ($k != 0) echo "<a class='text-smaller' href='".FUSION_SELF.$aidlink."&amp;action=fmu&amp;parent_id=".$field_data['field_cat']."&amp;field_id=".$field_data['field_id']."&amp;order=".($field_data['field_order']-1)."'>".$locale['move_up']."</a> - ";
								if ($k !== $item_counter) echo "<a class='text-smaller' href='".FUSION_SELF.$aidlink."&amp;action=fmd&amp;parent_id=".$field_data['field_cat']."&amp;field_id=".$field_data['field_id']."&amp;order=".($field_data['field_order']+1)."'>".$locale['move_down']."</a> - ";
								if ($field_data['field_type'] == 'file') {
									echo "<a class='text-smaller' href='".FUSION_SELF.$aidlink."&amp;action=module_edit&amp;module_id=".$field_data['field_id']."'>".$locale['edit']."</a> - ";
								} else {
									echo "<a class='text-smaller' href='".FUSION_SELF.$aidlink."&amp;action=field_edit&amp;field_id=".$field_data['field_id']."'>".$locale['edit']."</a> - ";
								}
								echo "<a class='text-smaller' href='".FUSION_SELF.$aidlink."&amp;action=field_delete&amp;field_id=".$field_data['field_id']."'>".$locale['delete']."</a>";
								echo "</div>\n";
								$options =	array('inline'=>1, 'show_title'=>1, 'hide_value'=>1);
								if ($field_data['field_type'] == 'file') {
									$options += array(
										'plugin_folder'=> $this->plugin_folder,
										'plugin_locale_folder' => $this->plugin_locale_folder,
									);
								}
								echo $this->display_fields($field_data, $this->callback_data, $this->method, $options);
								$k++;
							}
						}
						$i++;
					}
					echo "</div>\n";
				} else {
					// display no category
					echo "<div class='m-t-20 well text-center'>".$locale['fields_0102'].$page_details['field_cat_name']."</div>\n";
				}
				echo closetabbody();
			}
			echo closetab();
		} else {
			echo "<div class='well text-center'>".$locale['fields_0103']."</div>\n";
		}
		echo "</div>\n<div class='col-xs-12 col-sm-4 col-md-4 col-lg-4'>\n";
		$this->quantum_admin_buttons();
		echo "</div>\n";
		closetable();
	}

	/* Outputs Quantum Admin Button Sets */
	public function quantum_admin_buttons() {
		global $aidlink, $locale;
		$tab_title['title'][] = $locale['fields_0300'];
		$tab_title['id'][] = 'dyn';
		$tab_title['icon'][] = '';
		if (!empty($this->cat_list)) {
			$tab_title['title'][] = $locale['fields_0301'];
			$tab_title['id'][] = 'mod';
			$tab_title['icon'][] = '';
		}
		// Extended Tabs
		// add category
		if (isset($_POST['add_cat'])) {
			$tab_title['title'][] = $locale['fields_0305'];
			$tab_title['id'][] = 'add';
			$tab_title['icon'][] = '';
			$tab_active = (!empty($this->cat_list)) ? tab_active($tab_title, 2) : tab_active($tab_title, 1);
		} // add field
		elseif (isset($_POST['add_field']) && in_array($_POST['add_field'], $this->dynamics_type())) {
			$tab_title['title'][] = $locale['fields_0306'];
			$tab_title['id'][] = 'add';
			$tab_title['icon'][] = '';
			$tab_active = tab_active($tab_title, 2);
		}
		elseif (isset($_POST['add_module']) && in_array($_POST['add_module'], array_flip($this->get_available_modules))) {
			$tab_title['title'][] = $locale['fields_0307'];
			$tab_title['id'][] = 'add';
			$tab_title['icon'][] = '';
			$tab_active = tab_active($tab_title, 2);
		}
		elseif (isset($_GET['action']) && $_GET['action'] == 'cat_edit' && isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
			$tab_title['title'][] = $locale['fields_0308'];
			$tab_title['id'][] = 'edit';
			$tab_title['icon'][] = '';
			$tab_active = (!empty($this->cat_list)) ? tab_active($tab_title, 2) : tab_active($tab_title, 1);
		}
		elseif (isset($_GET['action']) && $_GET['action'] == 'field_edit' && isset($_GET['field_id']) && isnum($_GET['field_id'])) {
			$tab_title['title'][] = $locale['fields_0309'];
			$tab_title['id'][] = 'edit';
			$tab_title['icon'][] = '';
			$tab_active = tab_active($tab_title, 2);
		}
		elseif (isset($_GET['action']) && $_GET['action'] == 'module_edit' && isset($_GET['module_id']) && isnum($_GET['module_id'])) {
			$tab_title['title'][] = $locale['fields_0310'];
			$tab_title['id'][] = 'edit';
			$tab_title['icon'][] = '';
			$tab_active = tab_active($tab_title, 2);
		} else {
			$tab_active = tab_active($tab_title, 0);
		}
		echo opentab($tab_title, $tab_active, 'amd');
		echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active);
		echo openform('addfield', 'post', FUSION_SELF.$aidlink, array('notice' => 0, 'max_tokens' => 1));
		echo form_button('add_cat', $locale['fields_0311'], 'add_cat', array('class' => 'm-t-20 m-b-20 btn-sm btn-primary btn-block',
			'icon' => 'entypo plus-circled'));
		if (!empty($this->cat_list)) {
			echo "<div class='row m-t-20'>\n";
			$field_type = $this->dynamics_type();
			unset($field_type['file']);
			foreach ($field_type as $type => $name) {
				echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-b-20'>".form_button('add_field', $name, $name, array('type' => $type, 'class' => 'btn-block btn-sm btn-default'))."</div>\n";
			}
			echo "</div>\n";
		}
		echo closeform();
		echo closetabbody();
		if (!empty($this->cat_list)) {
			echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active);
			// list down modules.
			echo openform('addfield', 'post', FUSION_SELF.$aidlink, array('notice' => 0, 'max_tokens' => 1));
			echo "<div class='m-t-20'>\n";
			foreach ($this->available_field_info as $title => $module_data) {
				echo "<div class='list-group-item'>";
				echo form_button('add_module', $locale['fields_0312'], 'add_module-'.$title, array('class' => 'btn-sm btn-default pull-right m-l-10'));
				echo "<div class='overflow-hide'>\n";
				echo "<span class='text-dark strong'>".$module_data['title']."</span><br/>\n";
				echo "<span>".$module_data['description']."</span>\n<br/>";
				echo "</div>\n";
				echo "</div>\n";
			}
			echo "</div>\n";
			echo closeform();
			echo closetabbody();
		}
		if (isset($_POST['add_cat']) or (isset($_GET['action']) && $_GET['action'] == 'cat_edit' && isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
			if (!empty($this->cat_list)) {
				echo opentabbody($tab_title['title'][2], $tab_title['id'][2], $tab_active);
			} else {
				echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active);
			}
			echo "<div class='m-t-20'>\n";
			echo $this->quantum_category_form();
			echo "</div>\n";
			echo closetabbody();
		}

		elseif (isset($_POST['add_field']) && in_array($_POST['add_field'], $this->dynamics_type()) or (isset($_GET['action']) && $_GET['action'] == 'field_edit' && isset($_GET['field_id']) && isnum($_GET['field_id']))) {
			echo opentabbody($tab_title['title'][2], $tab_title['id'][2], $tab_active);
			$this->quantum_dynamics_form();
			echo closetabbody();
		} elseif (isset($_POST['add_module']) && in_array($_POST['add_module'], array_flip($this->get_available_modules)) or (isset($_GET['action']) && $_GET['action'] == 'module_edit' && isset($_GET['module_id']) && isnum($_GET['module_id']))) {
			echo opentabbody($tab_title['title'][2], $tab_title['id'][2], $tab_active);
			$this->quantum_module_form();
			echo closetabbody();
		}
		echo closetab();
	}

	/* Read into serialized field label and returns the value */
	public static function is_serialized( $value, &$result = null ) {
		// Bit of a give away this one
		if ( ! is_string( $value ) ) {
			return FALSE;
		}
		// Serialized FALSE, return TRUE. unserialize() returns FALSE on an
		// invalid string or it could return FALSE if the string is serialized
		// FALSE, eliminate that possibility.
		if ( 'b:0;' === $value ) {
			$result = FALSE;
			return TRUE;
		}
		$length	= strlen($value);
		$end	= '';
		if ( isset( $value[0] ) ) {
			switch ($value[0]) {
				case 's':
					if ( '"' !== $value[$length - 2] )
						return FALSE;
				case 'b':
				case 'i':
				case 'd':
					// This looks odd but it is quicker than isset()ing
					$end .= ';';
				case 'a':
				case 'O':
					$end .= '}';
					if ( ':' !== $value[1] )
						return FALSE;
					switch ( $value[2] ) {
						case 0:
						case 1:
						case 2:
						case 3:
						case 4:
						case 5:
						case 6:
						case 7:
						case 8:
						case 9:
							break;
						default:
							return FALSE;
					}
				case 'N':
					$end .= ';';
					if ( $value[$length - 1] !== $end[0] )
						return FALSE;
					break;
				default:
					return FALSE;
			}
		}
		if ( ( $result = @unserialize($value) ) === FALSE ) {
			$result = null;
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Parse the correct label language. Requires serialized $value.
	 * @param $value - Serialized
	 * @return string
	 * NOTE: If your field does not parse properly, check your column length. Set it to TEXT NOT NULL.
	 */
	public static function parse_label($value) {
		if (self::is_serialized($value)) {
			$value = @unserialize($value); // if anyone can give me a @unserialize($value) withotu E_NOTICE. I'll drop is_serialized function.
			return (string) (isset($value[LANGUAGE])) ? $value[LANGUAGE] : '';
		} else {
			return (string) $value;
		}
	}

	/** Short serialization function */
	public static function serialize_fields($input_name) {
		if (isset($_POST[$input_name])) {
			$field_var = array();
			foreach($_POST[$input_name] as $language => $value) {	$field_var[$language] = form_sanitizer($value, ''); }
			return serialize($field_var);
		}
		return false;
	}

	/* Parse serialized language data of $_POST fields,
	 * or if not exist, read serialized data from DB.
	 * If value is not serialized on read, to duplicate primary value across all language  */
	public static function fusion_getlocale($data, $input_name) {
		global $language_opts;
		if (isset($_POST[$input_name])) {
			return self::serialize_fields($input_name);
		} else {
			if (isset($data[$input_name])) {
				if (self::is_serialized($data[$input_name])) {
					return unserialize($data[$input_name]);
				} else {
					foreach($language_opts as $lang) {
						$value[$lang] = $data[$input_name];
					}
					return $value;
				}
			}
		}
	}

	/** Outputs a multilocale single field */
	public static function quantum_multilocale_fields($title, $input_name, $input_value, array $options = array()) {
		global $locale;
		$html = '';
		$language_opts = fusion_get_enabled_languages();
		$input_value = self::is_serialized($input_value) ? unserialize($input_value) : $input_value;
		$options += array(
			'function' => !empty($options['textarea']) && $options['textarea'] == 1 ? 'form_textarea' : 'form_text', // only 2 fields type need a multiple locale logically
			'required' => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
			'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : '',
			'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
			'width' => !empty($options['width']) ?  $options['width']  : '100%',
			'class' => !empty($options['class']) ?  $options['class']  : '',
			'inline' => !empty($options['inline']) ?  $options['inline']  : '',
			'max_length' => !empty($options['max_length']) ?  $options['max_length']  : '200',
			'error_text' => !empty($options['error_text']) ?  $options['error_text']  : '',
			'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? '1'  : '0',
			'icon' => !empty($options['icon']) ?  $options['icon']  : '',
			'input_id' => !empty($options['input_id']) ?  $options['input_id']  : $input_name,
		);
		$required = $options['required'];
		$html .= "<div id='".$options['input_id']."-field' class='form-group m-t-10 ".$options['class']." ".($options['icon'] ? 'has-feedback' : '')."'>\n";
		$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."'>$title ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
		$html .= ($options['inline']) ? "<div class='col-xs-12 ".($title ? "col-sm-9 col-md-9 col-lg-9 well p-l-15" : "col-sm-12 col-md-12 col-lg-12")."'>\n" : "<div class='p-t-10 p-b-10 well'>";
		$main_html = ''; $sub_html = '';
		foreach($language_opts as $lang) {
			$options['field_title'] = $title." (".$lang.")";
			$options['input_id'] = $input_name."-".$lang;
			if ($lang == LANGUAGE) {
				$options['required'] = $required;
				// Fix this
				$main_html .= $options['function']($input_name."[$lang]", $lang, isset($input_value[$lang]) ? $input_value[$lang] : $input_value, $options);
			} else {
				$options['required'] = 0;
				$sub_html .= $options['function']($input_name."[$lang]", $lang, isset($input_value[$lang]) ? $input_value[$lang] : '', $options);
			}
		}
		$html .= $main_html.$sub_html;
		if (count($language_opts)>1) {
			$html .= "<div class='dropdown ".($options['inline'] ? "col-sm-offset-3" : "")."'>\n";
			$html .= "<button id='lang_dropdown' data-toggle='dropdown' class='dropdown-toggle btn btn-sm btn-default' type='button'>".$locale['fields_0114']." <span class='caret'></span></button>\n";
			$html .= "<ul class='dropdown-menu' style='margin-top:10px; !important;'>\n";
			foreach($language_opts as $Lang) {
				if ($Lang !== LANGUAGE) {
					$html .= "<li><a data-add='".$Lang."' class='pointer data-add'>Add ".$Lang."</a></li>\n";
					if ($Lang !== LANGUAGE) {
						add_to_jquery("$('#".$input_name."-".$Lang."-field').hide();");

					}
				}
			}
			$html .= "</ul>\n";
			$html .= "</div>\n";

			add_to_jquery("

			$('.data-add').bind('click', function() {
				var lang = $(this).data('add');
				var dom = $('#".$input_name."-'+lang+'-field');
				if ($('#".$input_name."-'+lang+'-field').is(':visible')) {
					dom.hide();
					$(this).text('Add '+lang);
				} else {
					dom.show();
					$(this).text('Remove '+lang);
				}
			});
		");
		}
		$html .= "</div>\n";
		$html .= "</div>\n";
		return $html;
	}

	/* Move Fields Order - Up and Down */
	private function sql_move_fields() {
		global $aidlink, $locale;
		if (isset($_GET['action']) && isset($_GET['order']) && isnum($_GET['order']) && isset($_GET['parent_id']) && isnum($_GET['parent_id'])) {
			if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) && ($_GET['action'] == 'cmu' or $_GET['action'] == 'cmd')) {
				$data = dbarray(dbquery("SELECT field_cat_id FROM ".$this->category_db." WHERE field_parent='".intval($_GET['parent_id'])."' AND field_cat_order='".intval($_GET['order'])."'")); // more than 1.
				if ($_GET['action'] == 'cmu') { // category move up.
					if (!$this->debug) $result = dbquery("UPDATE ".$this->category_db." SET field_cat_order=field_cat_order+1 WHERE field_cat_id='".$data['field_cat_id']."'");
					if (!$this->debug) $result = dbquery("UPDATE ".$this->category_db." SET field_cat_order=field_cat_order-1 WHERE field_cat_id='".$_GET['cat_id']."'");
				} elseif ($_GET['action'] == 'cmd') {
					if (!$this->debug) $result = dbquery("UPDATE ".$this->category_db." SET field_cat_order=field_cat_order-1 WHERE field_cat_id='".$data['field_cat_id']."'");
					if (!$this->debug) $result = dbquery("UPDATE ".$this->category_db." SET field_cat_order=field_cat_order+1 WHERE field_cat_id='".$_GET['cat_id']."'");
				}
				if (!$this->debug) redirect(FUSION_SELF.$aidlink);
			} elseif (isset($_GET['field_id']) && isnum($_GET['field_id']) && ($_GET['action'] == 'fmu' or $_GET['action'] == 'fmd')) {
				$data = dbarray(dbquery("SELECT field_id FROM ".$this->field_db." WHERE field_cat='".intval($_GET['parent_id'])."' AND field_order='".intval($_GET['order'])."'"));
				if ($_GET['action'] == 'fmu') { // field move up.
					if (!$this->debug) $result = dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_id='".$data['field_id']."'");
					if (!$this->debug) $result = dbquery("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_id='".$_GET['field_id']."'");
					if ($this->debug) print_p("Move Field ID ".$_GET['field_id']." Up a slot and Field ID ".$data['field_id']." down a slot.");
				} elseif ($_GET['action'] == 'fmd') {
					if (!$this->debug) $result = dbquery("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_id='".$data['field_id']."'");
					if (!$this->debug) $result = dbquery("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_id='".$_GET['field_id']."'");
					if ($this->debug) print_p("Move Field ID ".$_GET['field_id']." down a slot and Field ID ".$data['field_id']." up a slot.");
				}
				if (!$this->debug) redirect(FUSION_SELF.$aidlink);
			}
		}
	}

	/* Execution of delete category */
	private function sql_delete_category() {
		global $aidlink, $locale;
		$data = array();
		if (isset($_POST['cancel'])) redirect(FUSION_SELF.$aidlink);
		//$this->debug = 1;
		if (isset($_GET['action']) && $_GET['action'] == 'cat_delete' && isset($_GET['cat_id']) && self::validate_fieldCat($_GET['cat_id'])) {
			// do action of the interior form
			if (isset($_POST['delete_cat'])) {
				// get root node
				if (isset($_POST['delete_subcat']) or isset($_POST['delete_field'])) {
					if (in_array($_GET['cat_id'], $this->page_list)) { // this is root.
						$result = dbquery("SELECT field_cat_id, field_cat_db FROM ".$this->category_db." WHERE field_cat_id='".$_GET['cat_id']."'");
					} else { // is is not a root.
						$result = dbquery("SELECT uf.field_cat_id, root.field_cat_db FROM ".$this->category_db." uf LEFT JOIN ".$this->category_db." root ON (uf.field_parent = root.field_cat_id) WHERE uf.field_cat_id='".intval($_GET['cat_id'])."'");
					}
					$target_database = '';
					$field_list = array();
					if (dbrows($result)>0) {
						$data += dbarray($result);
						$target_database = $data['field_cat_db'] ? DB_PREFIX.$data['field_cat_db'] : DB_USERS;
						$field_list = fieldgenerator($target_database);
					}
					if ($this->debug) print_p($field_list);
					if ($this->debug) print_p($target_database);
				}
				// root deletion path 1
				if (isset($_POST['delete_subcat'])) {
					if ($this->debug) print_p($this->page[$_GET['cat_id']]);
					// execute removal on child fields and cats
					foreach($this->page[$_GET['cat_id']] as $arr => $field_category) {
						$result = dbquery("SELECT field_id, field_name FROM ".$this->field_db." WHERE field_cat='".$field_category['field_cat_id']."'"); // find all child > 1
						if (dbrows($result)>0) {
							while ($data = dbarray($result)) {
								// remove column from db , and fields
								if (in_array($data['field_name'], $field_list)) { // verify table integrity
									if (!$this->debug && ($target_database)) $result = dbquery("ALTER TABLE ".$target_database." DROP ".$data['field_name']);
									if ($this->debug) print_p("DROP ".$data['field_name']." FROM ".$target_database);
									if (!$this->debug) $result = dbquery("DELETE FROM ".$this->field_db." WHERE field_id='".$data['field_id']."'");
									if ($this->debug) print_p("DELETE ".$data['field_id']." FROM ".$this->field_db);
								}
								// remove category.
								if (!$this->debug) $result = dbquery("DELETE FROM ".$this->category_db." WHERE field_cat_id='".$field_category['field_cat_id']."'");
								if ($this->debug) print_p("DELETE ".$field_category['field_cat_id']." FROM ".$this->category_db);
							}
						}
					}
					// remove category
					if (!$this->debug) $result = dbquery("DELETE FROM ".$this->category_db." WHERE field_cat_id='".intval($_GET['cat_id'])."'");
					if ($this->debug) print_p("DELETE ".$_GET['cat_id']." FROM ".$this->category_db);
				}
				// root deletion path 2
				elseif (isset($_POST['move_subcat']) && $_POST['move_subcat'] > 0) {
					foreach($this->page[$_GET['cat_id']] as $arr => $field_category) {
						$new_parent = form_sanitizer($_POST['move_subcat'], 0, 'move_subcat');
						if (!$this->debug) $result = dbquery("UPDATE ".$this->category_db." SET field_parent='".$new_parent."' WHERE field_cat_id='".$field_category['field_cat_id']."'");
						if ($this->debug) print_p("MOVED ".$field_category['field_cat_id']." TO category ".$new_parent);
					}
					// delete the category.
					if (!$this->debug) $result = dbquery("DELETE FROM ".$this->category_db." WHERE field_cat_id='".intval($_GET['cat_id'])."'");
					if ($this->debug) print_p("DELETE ".$_GET['cat_id']." FROM ".$this->category_db);
				}
				// category deletion path 1
				elseif (isset($_POST['delete_field'])) {
					if ($this->debug) print_p('Delete Fields');
					$result = dbquery("SELECT field_id, field_name FROM ".$this->field_db." WHERE field_cat='".intval($_GET['cat_id'])."'");
					if (dbrows($result)>0) {
						while ($data = dbarray($result)) {
							if (in_array($data['field_name'], $field_list)) { // verify table integrity
								if (!$this->debug && ($target_database)) $result = dbquery("ALTER TABLE ".$target_database." DROP ".$data['field_name']);
								if ($this->debug) print_p("DROP ".$data['field_name']." FROM ".$target_database);
								if (!$this->debug) $result = dbquery("DELETE FROM ".$this->field_db." WHERE field_id='".$data['field_id']."'");
								if ($this->debug) print_p("DELETE ".$data['field_id']." FROM ".$this->field_db);
							}
						}
						// remove category
						if (!$this->debug) $result = dbquery("DELETE FROM ".$this->category_db." WHERE field_cat_id='".intval($_GET['cat_id'])."'");
						if ($this->debug) print_p("DELETE ".$_GET['cat_id']." FROM ".$this->category_db);
					}
				}
				// category deletion path 2
				elseif (isset($_POST['move_field']) && $_POST['move_field'] >0) {
					$rows = dbcount("(field_id)", $this->field_db, "field_cat='".intval($_GET['cat_id'])."'");
					if ($rows) {
						$new_parent = form_sanitizer($_POST['move_field'], 0, 'move_field');
						$result = dbquery("UPDATE ".$this->field_db." SET field_cat='".intval($new_parent)."' WHERE field_cat='".intval($_GET['cat_id'])."'");
						$result = dbquery("DELETE FROM ".$this->category_db." WHERE field_cat_id='".intval($_GET['cat_id'])."'");
					}
					if (!$this->debug) redirect(FUSION_SELF.$aidlink."&amp;status=cat_deleted");
				}
				else {
					//delete just the category as it is without child.
					if (!$this->debug) $result = dbquery("DELETE FROM ".$this->category_db." WHERE field_cat_id='".intval($_GET['cat_id'])."'");
				}
				if (!$this->debug) redirect(FUSION_SELF.$aidlink."&amp;status=cat_deleted");
			}
			// show interior form
			else {

				$form_action = FUSION_SELF.$aidlink."&amp;action=cat_delete&amp;cat_id=".$_GET['cat_id'];
				$result = dbquery("SELECT * FROM ".$this->category_db." WHERE field_cat_id='".$_GET['cat_id']."'");
				if (dbrows($result) > 0) {
					$data += dbarray($result);
					// get field list - populate child fields of a category.
					$field_list = array();
					$result = dbquery("SELECT field_id, field_name, field_cat FROM ".$this->field_db." WHERE field_cat='".intval($_GET['cat_id'])."'");
					if (dbrows($result)>0) {
						// get field list.
						while ($data = dbarray($result)) {
							$field_list[$data['field_cat']][$data['field_id']] = $data['field_name'];
						}
					}
					if (isset($this->page[$data['field_parent']]) or $field_list[$_GET['cat_id']] > 0) {
						echo openmodal("delete", $locale['fields_0313'], array('class'=>'modal-lg modal-center zindex-boost', 'static'=>1));
						echo openform('delete_cat_form', 'post', $form_action, array('max_tokens' => 1));
						if (isset($this->page[$_GET['cat_id']])) {
							echo "<div class='row'>\n";
							echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n<span class='strong'>".sprintf($locale['fields_0600'], count($this->page[$_GET['cat_id']]) )."</span><br/>\n";
							echo "<div class='well strong m-t-10'>\n";
							foreach($this->page[$_GET['cat_id']] as $arr=>$field_category) {
								echo "- ".$field_category['field_cat_name']."<br/>\n";
							}
							echo "</div>\n";
							echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
							$page_list = $this->page_list;
							unset($page_list[$_GET['cat_id']]);
							if (count($page_list) >0) {
								echo form_select('move_subcat', $locale['fields_0314'], $page_list, '');
							}
							echo form_checkbox('delete_subcat', $locale['fields_0315'], '');
							echo "</div></div>";
						}
						if (isset($field_list[$_GET['cat_id']])) {
							echo "<div class='row'>\n";
							echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n<span class='strong'>".sprintf($locale['fields_0601'], count($field_list[$_GET['cat_id']]))."</span><br/>\n";
							echo "<div class='well strong m-t-10'>\n";
							foreach($field_list[$_GET['cat_id']] as $arr=>$field) {
								echo "- ".$field."<br/>\n";
							}
							echo "</div>\n";
							echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
							$exclude_list[] = $_GET['cat_id'];
							foreach($this->page_list as $page_id => $page_name) {
								$exclude_list[] = $page_id;
							}
							echo form_select_tree('move_field', $locale['fields_0316'], '', array('no_root'=>1, 'disable_opts'=>$exclude_list), $this->category_db, 'field_cat_name', 'field_cat_id', 'field_parent');
							echo form_checkbox('delete_field', $locale['fields_0317'], '');
							echo "</div></div>";
						}
						echo form_button('delete_cat', $locale['fields_0313'], $locale['fields_0313'], array('class'=>'btn-danger btn-sm'));
						echo form_button('cancel', $locale['cancel'], $locale['cancel'], array('class'=>'btn-default m-l-10 btn-sm'));
						echo closeform();
						echo closemodal();
					}
				} else {
					if ($this->debug) notify('Cat ID was not found. Please check again.', 'Category ID was not found. Please check again.');
					if (!$this->debug) redirect(FUSION_SELF.$aidlink);
				}
			}
		}
	}

	/* Execution of delete fields */
	private function sql_delete_fields() {
		global $aidlink, $locale;
		if (isset($_GET['action']) && $_GET['action'] == 'field_delete' && isset($_GET['field_id']) && self::validate_field($_GET['field_id'])) {
			$result = dbquery("SELECT field.field_id, field.field_cat, field.field_order, field.field_name, u.field_cat_id, u.field_parent, root.field_cat_db
			FROM ".$this->field_db." field
			LEFT JOIN ".$this->category_db." u ON (field.field_cat=u.field_cat_id)
			LEFT JOIN ".$this->category_db." root on (u.field_parent = root.field_cat_id)
			WHERE field_id='".intval($_GET['field_id'])."'
			");
			if (dbrows($result)>0) {
				if ($this->debug) print_p('Obtained Field Data');
				$data = dbarray($result);
				$target_database = $data['field_cat_db'] ? DB_PREFIX.$data['field_cat_db'] : DB_USERS;
				$field_list = fieldgenerator($target_database);
				if ($this->debug) print_p($field_list);
				if (in_array($data['field_name'], $field_list)) {
					// drop database
					if (!$this->debug && ($target_database)) $result = dbquery("ALTER TABLE ".$target_database." DROP ".$data['field_name']);
					if ($this->debug) print_p("DROP ".$data['field_name']." FROM ".$target_database);
					// reorder the rest of the same cat minus 1
					if (!$this->debug && ($target_database)) $result = dbquery("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_order > '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
					if (!$this->debug && ($target_database)) $result = dbquery("DELETE FROM ".$this->field_db." WHERE field_id='".$data['field_id']."'");
					if ($this->debug) print_p("DELETE ".$data['field_id']." FROM ".$this->field_db);
				} else {
					// just delete the field
					if ($this->debug) print_p("DELETE ".$data['field_id']." FROM ".$this->field_db);
					if (!$this->debug) $result = dbquery("DELETE FROM ".$this->field_db." WHERE field_id='".$data['field_id']."'");
				}
				if (!$this->debug) redirect(FUSION_SELF.$aidlink."&amp;status=field_deleted");
			} else {
				if ($this->debug) print_p('Did not get field data.');
				if (!$this->debug) redirect(FUSION_SELF.$aidlink);
			}
		}
	}

	/* Returns $cat_list */
	public function load_field_cats() {
		// Load Field Cats
		$result = dbquery("SELECT * FROM ".$this->category_db." WHERE field_parent='0' ORDER BY field_cat_order ASC");
		if (dbrows($result) > 0) {
			while ($list_data = dbarray($result)) {
				$this->page_list[$list_data['field_cat_id']] = $list_data['field_cat_name'];
			}
		}
		$result = dbquery("SELECT * FROM ".$this->category_db." WHERE field_parent !='0' ORDER BY field_cat_order ASC");
		if (dbrows($result) > 0) {
			while ($list_data = dbarray($result)) {
				$this->cat_list[$list_data['field_cat_id']] = $list_data['field_cat_name'];
			}
		}
	}

	/* Hardcoded Column Attributes - Can be added to forms but is it too technical for non coders? */
	private function dynamics_fieldinfo($type, $default_value) {
		$info = array(
			'textbox' => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
			'select' => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
			'textarea' => "TEXT NOT NULL",
			'tags' => "TEXT NOT NULL",
			'checkbox' => "TINYINT(3) NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
			'toggle' => "TINYINT(3) NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
			'datepicker' => "TINYINT(10) NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
			'location' => "VARCHAR(50) NOT NULL DEFAULT '".$default_value."'",
			'colorpicker' => "VARCHAR(10) NOT NULL DEFAULT '".$default_value."'",
			'upload' => "VARCHAR(100) NOT NULL DEFAULT '".$default_value."'",
			'hidden' => "VARCHAR(50) NOT NULL DEFAULT '".$default_value."'",
			'address' => "TEXT NOT NULL",
			'number' => "INT(10) UNSIGNED NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
			'email' => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
			'url' => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
		);
		return $info[$type];
	}

	/* The Current Stable PHP-Fusion Dynamics Module */
	public static function dynamics_type() {
		global $locale;
		return array(
			'file' 			=> $locale['fields_0500'],
			'textbox'       => $locale['fields_0501'],
			'select'        => $locale['fields_0502'],
			'textarea'      => $locale['fields_0503'],
			'checkbox'      => $locale['fields_0504'],
			'toggle'        => $locale['fields_0505'],
			'datepicker'    => $locale['fields_0506'],
			'colorpicker'   => $locale['fields_0507'],
			'upload'        => $locale['fields_0508'],
			'hidden'        => $locale['fields_0509'],
			'address'       => $locale['fields_0510'],
			'tags'       	=> $locale['fields_0511'],
			'location'      => $locale['fields_0512'],
			'number'      	=> $locale['fields_0513'],
			'email'      	=> $locale['fields_0514'],
			'url'      		=> $locale['fields_0515'],
		);
	}

	/** Field Creation */
	private function create_fields($data, $type = 'dynamics') {
		global $aidlink, $defender, $locale;

		$field_attr = '';
		if ($type == 'dynamics') {
			$field_attr = $this->dynamics_fieldinfo($data['field_type'], $data['field_default']);
		} elseif ($type == 'module') {
			$field_attr = $this->user_field_dbinfo;
		}

		$max_order = dbresult(dbquery("SELECT MAX(field_order) FROM ".$this->field_db." WHERE field_cat='".$data['field_cat']."'"), 0)+1;
		if ($data['field_order'] == 0 or $data['field_order'] > $max_order) {
			$data['field_order'] = $max_order;
		}

		if (self::validate_field($data['field_id'])) {
			if ($this->debug) print_p('Update mode');
			// update
			// Alter $this->field_db table - change and modify column.
			$old_record = dbquery("SELECT uf.*, cat.field_cat_id, cat.field_parent, cat.field_cat_order, root.field_cat_db, root.field_cat_index
									FROM ".$this->field_db." uf
									LEFT JOIN ".$this->category_db." cat ON (cat.field_cat_id = uf.field_cat)
									LEFT JOIN ".$this->category_db." root ON (cat.field_parent = root.field_cat_id)
									WHERE uf.field_id='".$data['field_id']."'"); // old database.
			if (dbrows($old_record) > 0) { // got old field cat
				$cat_data = dbarray($old_record);
				if ($this->debug) print_p($cat_data);
				$old_database = $cat_data['field_cat_db'] ? DB_PREFIX.$cat_data['field_cat_db'] : DB_USERS; // this was old database
				$field_arrays = fieldgenerator($old_database);
				// now check the new one fetch on new cat.
				$new_result = dbquery("SELECT cat.field_cat_id, cat.field_parent, cat.field_cat_order, root.field_cat_db, root.field_cat_index
						FROM ".$this->category_db." cat
						LEFT JOIN ".$this->category_db." root on (cat.field_parent = root.field_cat_id)
						WHERE cat.field_cat_id='".$data['field_cat']."'");
				if (dbrows($new_result) > 0) { // cat found.
					$new_cat_data = dbarray($new_result);
					$target_database = $new_cat_data['field_cat_db'] ? DB_PREFIX.$new_cat_data['field_cat_db'] : DB_USERS;
				} else {
					$target_database = DB_USERS;
				}
				if ($data['field_cat'] !== $cat_data['field_cat']) { // old and new mismatch - move to another category
					if ($this->debug) print_p("Path 1 Update Field");
					// drop the old one if target database aren't the same.
					if ($target_database !== $old_database) {
						if (!$this->debug) $result = dbquery("ALTER TABLE ".$old_database." DROP ".$cat_data['field_name']); // drop the old one.
						if ($this->debug) print_p("Dropping ".$old_database." with ".$cat_data['field_name']);
					}
					$field_arrays = fieldgenerator($target_database);
					if (!in_array($data['field_name'], $field_arrays)) { // this is new database check, if not exist, then add the column
						if (!$this->debug) $result = dbquery("ALTER TABLE ".$target_database." ADD ".$data['field_name']." ".$field_attr); // create the new one.
						if ($this->debug) print_p("ADD ".$target_database." with ".$data['field_name']." on ".$field_attr);
					}
					// sort the fields. if 2, greater than 2 all +1 on the new category
					if (!$this->debug) $result = dbquery("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
					if ($this->debug) print_p("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
					// since change table. fix all which is greater than link order.
					if (!$this->debug) $result = dbquery("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_order >= '".$cat_data['field_order']."' AND field_cat='".$cat_data['field_cat']."'");
					if ($this->debug) print_p("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_order >= '".$cat_data['field_order']."' AND field_cat='".$cat_data['field_cat']."'");

				} else { // same table.
					// check if same title.
					// if not same, change column name.
					if ($this->debug) print_p("Path 2 Update Field");
					if ($data['field_name'] !== $cat_data['field_name']) { // not same as old record on dbcolumn
						if (!in_array($data['field_name'], $field_arrays)) { // safe to execute alter.
							// change the current column name to the new one. we cannot and do not need to modify field properties. if they want to change that, they should drop this field.
							if (!$this->debug) $result = dbquery("ALTER TABLE ".$target_database." CHANGE ".$cat_data['field_name']." ".$data['field_name']);
							//if (!$this->debug) $result = dbquery("ALTER TABLE ".$target_database." CHANGE ".$cat_data['field_name']." ".$data['field_name']." ".$this->dynamics_fieldinfo($data['field_type'], $data['field_default']));
							// check whether need to modify.
							if ($this->debug) print_p("Renaming ".$target_database." column ".$cat_data['field_name']." to ".$data['field_name']);
						} else {
							$defender->stop();
							addNotice('danger', sprintf($locale['fields_0104'], $cat_data['field_cat_name']));
						}
					}
					// make ordering of the same table.
					if ($this->debug) print_p($data['field_order']);
					if ($this->debug) print_p($cat_data['field_order']);
					if ($data['field_order'] > $cat_data['field_order']) {
						if (!$this->debug) dbquery("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_order > ".$cat_data['field_order']." AND field_order <= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
						if ($this->debug) print_p("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_order > '".$cat_data['field_order']."' AND field_order <= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
					} elseif ($data['field_order'] < $cat_data['field_order']) {
						if (!$this->debug) dbquery("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_order < ".$cat_data['field_order']." AND field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
						if ($this->debug) print_p("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_order < '".$cat_data['field_order']."' AND field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
					}
				}
				if ($this->debug) print_p('Going to Synthesize this'); print_p($data);
				if (!$this->debug && !defined('FUSION_NULL')) dbquery_insert($this->field_db, $data, 'update');
				if (!$this->debug && !defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink.'&amp;status=field_updated');
			} else {
				$defender->stop();
				addNotice('danger', $locale['fields_0105']);
			}
		} else {
			if ($this->debug) print_p('Save Mode');
			// Alter $this->field_db table - add column.
			$cresult = dbquery("SELECT cat.field_cat_id, cat.field_parent, cat.field_cat_order, root.field_cat_db, root.field_cat_index
								FROM ".$this->category_db." cat
								LEFT JOIN ".$this->category_db." root ON (cat.field_parent = root.field_cat_id)
								WHERE cat.field_cat_id='".$data['field_cat']."'");
			if (dbrows($cresult) > 0 && !defined('FUSION_NULL')) {
				$cat_data = dbarray($cresult);
				$target_database = $cat_data['field_cat_db'] ? DB_PREFIX.$cat_data['field_cat_db'] : DB_USERS;
				$field_arrays = fieldgenerator($target_database);
				if (!in_array($data['field_name'], $field_arrays)) { // safe to execute alter.
					if (!$this->debug) $result = dbquery("ALTER TABLE ".$target_database." ADD ".$data['field_name']." ".$field_attr);
					if ($this->debug) print_p("Alter DB_".$target_database." with ".$data['field_name']." on ".$field_attr);
				} else {
					$defender->stop();
					addNotice('danger', $locale['fields_0106']);
				}
				// ordering
				if ($this->debug) print_p($data);
				if (!$this->debug && !defined('FUSION_NULL')) dbquery("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_order > '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
				if (!$this->debug && !defined('FUSION_NULL')) dbquery_insert($this->field_db, $data, 'save');
				if (!$this->debug && !defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink.'&amp;status=field_added');
			} else {
				$defender->stop();
				addNotice('danger', $locale['fields_0107']);
			}
		}
	}

	private function validate_field($field_id) {
		if (isnum($field_id)) {
			return dbcount("(field_id)", $this->field_db, "field_id='".intval($field_id)."'");
		}
		return false;
	}

	private $field_data = array(
		'add_module' => '',
		'field_type' => '',
		'field_id' => 0,
		'field_title' => '',
		'field_name' => '',
		'field_cat' => 0,
		'field_options' => '',
		'field_default' => '',
		'field_error' => '',
		'field_registration' => 0,
		'field_log' => 0,
		'field_required' => 0,
		'field_order' => 0,
	);

	/** The master form for Adding or Editing Dynamic Fields */
	private function quantum_dynamics_form() {
		global $aidlink, $defender, $locale;

		$config_2 = array(
			'field_thumbnail' => 0,
			'field_thumbnail_2' => 0,
		);

		$form_action = FUSION_SELF.$aidlink;

		if (isset($_GET['action']) && $_GET['action'] == 'field_edit' && isset($_GET['field_id']) && self::validate_field($_GET['field_id'])) {
			$form_action .= "&amp;action=".$_GET['action']."&amp;field_id=".$_GET['field_id'];
			$result = dbquery("SELECT * FROM ".$this->field_db." WHERE field_id='".$_GET['field_id']."'");
			if (dbrows($result) > 0) {
				$this->field_data = dbarray($result);
				if ($this->field_data['field_type'] == 'upload') {
					$this->field_data += unserialize($this->field_data['config']); // uncompress serialized extended information.
					if ($this->debug) print_p($this->field_data);
				}
			} else {
				if (!$this->debug) redirect(FUSION_SELF.$aidlink);
			}
		}

		$this->field_data['field_type'] =  isset($_POST['add_field']) ? form_sanitizer($_POST['add_field'], '') : $this->field_data['field_type'];

		if (isset($_POST['save_field'])) {
			$this->field_data = array(
				'field_type' =>  isset($_POST['add_field']) ? form_sanitizer($_POST['add_field'], '') : $this->field_data['field_type'],
				'field_id' =>  form_sanitizer($_POST['field_id'], '0', 'field_id'),
				'field_title' =>  form_sanitizer($_POST['field_title'], '', 'field_title' , 1),
				'field_name' => form_sanitizer($_POST['field_name'], '', 'field_name'),
				'field_cat' => form_sanitizer($_POST['field_cat'], '', 'field_cat'),
				'field_options' => isset($_POST['field_options']) ? form_sanitizer($_POST['field_options'], '', 'field_options') : $this->field_data['field_options'],
				'field_default' => isset($_POST['field_default']) ? form_sanitizer($_POST['field_default'], '', 'field_default') : $this->field_data['field_default'],
				'field_error' => form_sanitizer($_POST['field_error'], '', 'field_error'),
				'field_required' => isset($_POST['field_required']) ? 1 : 0,
				'field_log' =>  isset($_POST['field_log']) ? 1 : 0,
				'field_registration' => isset($_POST['field_registration']) ? 1 : 0,
				'field_order' => form_sanitizer($_POST['field_order'], '0', 'field_order'),
			);
			$this->field_data['field_name'] = str_replace(' ', '_', $this->field_data['field_name']); // make sure no space.
			if ($this->field_data['field_type'] == 'upload') {
				$max_b = isset($_POST['field_max_b']) ? form_sanitizer($_POST['field_max_b'], '', 'field_max_b') : 150000;
				$calc = isset($_POST['field_calc']) ? form_sanitizer($_POST['field_calc'], '', 'field_calc') : 1;
				$config = array(
					'field_max_b' => isset($_POST['field_max_b']) && isset($_POST['field_calc']) ? $max_b*$calc : $this->field_data['field_max_b'],
					'field_upload_type' => isset($_POST['field_upload_type']) ? form_sanitizer($_POST['field_upload_type'], '', 'field_upload_type') : $this->field_data['field_upload_type'],
					'field_upload_path' => isset($_POST['field_upload_path']) ? form_sanitizer($_POST['field_upload_path'], '', 'field_upload_path') : $this->field_data['field_upload_path'],
				);
				$config_1['field_valid_file_ext'] = isset($_POST['field_valid_file_ext']) && $config['field_upload_type'] == 'file' ? form_sanitizer($_POST['field_valid_file_ext'], '', 'field_valid_file_ext') : $this->field_data['field_valid_file_ext'];

				$config_2 = array(
					'field_valid_image_ext' => isset($_POST['field_valid_image_ext']) && $config['field_upload_type'] == 'image' ? form_sanitizer($_POST['field_valid_image_ext'], '', 'field_valid_image_ext') : $this->field_data['field_valid_image_ext'],
					'field_image_max_w' => isset($_POST['field_image_max_w']) && $config['field_upload_type'] == 'image' ? form_sanitizer($_POST['field_image_max_w'], '', 'field_image_max_w') : $this->field_data['field_image_max_w'],
					'field_image_max_h' => isset($_POST['field_image_max_h']) && $config['field_upload_type'] == 'image' ? form_sanitizer($_POST['field_image_max_h'], '', 'field_image_max_h') : $this->field_data['field_image_max_h'],
					'field_thumbnail' => isset($_POST['field_thumbnail']) ? form_sanitizer($_POST['field_thumbnail'], 0, 'field_thumbnail') : $this->field_data['field_thumbnail'],
					'field_thumb_upload_path' => isset($_POST['field_thumb_upload_path']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail'] ? form_sanitizer($_POST['field_thumb_upload_path'], '', 'field_thumb_upload_path') : $this->field_data['field_thumb_upload_path'],
					'field_thumb_w' => isset($_POST['field_thumb_w']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail'] ? form_sanitizer($_POST['field_thumb_w'], '', 'field_thumb_w') : $this->field_data['field_thumb_w'],
					'field_thumb_h' =>  isset($_POST['field_thumb_h']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail'] ? form_sanitizer($_POST['field_thumb_h'], '', 'field_thumb_h') : $this->field_data['field_thumb_h'],
					'field_thumbnail_2' => isset($_POST['field_thumbnail_2']) ? 1 : isset($_POST['field_id']) ? 0 : $this->field_data['field_thumbnail_2'],
					'field_thumb2_upload_path' => isset($_POST['field_thumb2_upload_path']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail_2'] ? form_sanitizer($_POST['field_thumb2_upload_path'], '', 'field_thumb2_upload_path') : $this->field_data['field_thumb2_upload_path'],
					'field_thumb2_w' => isset($_POST['field_thumb2_w']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail_2'] ? form_sanitizer($_POST['field_thumb2_w'], '', 'field_thumb2_w') : $this->field_data['field_thumb2_w'],
					'field_thumb2_h' => isset($_POST['field_thumb2_h']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail_2'] ? form_sanitizer($_POST['field_thumb2_h'], '', 'field_thumb2_h') : $this->field_data['field_thumb2_h'],
					'field_delete_original' => isset($_POST['field_delete_original']) && $config['field_upload_type'] == 'image' ? 1 : isset($_POST['field_id']) ? 0 : $this->field_data['field_delete_original'],
				);

				if ($config['field_upload_type'] == 'file') {
					$config = array_merge($config, $config_1);
				} elseif ($config['field_upload_type'] == 'image') {
					// upload path must be required.
					$config = array_merge($config, $config_2);
				} else {
					$defender->stop();
					addNotice('danger', $locale['fields_0108']);
				}
				if (!defined('FUSION_NULL')) {
					$this->field_data['config'] = serialize($config);
				}
			}
			$this->create_fields($this->field_data, 'dynamics');
		}

		echo "<div class='m-t-20'>\n";
		echo openform('fieldform', 'post', $form_action, array('max_tokens' => 1));
		$disable_opts = array();
		foreach ($this->page_list as $index => $v) {
			$disable_opts[] = $index;
		}
		// ok the value generated needs to be parsed by quantum
		echo form_select_tree('field_cat', $locale['fields_0450'],  $this->field_data['field_cat'], array('no_root' => 1, 'width'=>'100%', 'disable_opts' => $disable_opts), $this->category_db, 'field_cat_name', 'field_cat_id', 'field_parent');
		echo self::quantum_multilocale_fields($locale['fields_0451'], 'field_title', $this->field_data['field_title'], array('required'=>1));
		echo form_text('field_name', $locale['fields_0453'], $this->field_data['field_name'], array('placeholder' => $locale['fields_0454'], 'required' => 1));
		if ($this->field_data['field_type'] == 'select') echo form_select('field_options', $locale['fields_0455'], array(), $this->field_data['field_options'], array('required' => 1,
			'tags' => 1,
			'multiple' => 1));
		if ($this->field_data['field_type'] == 'upload') {
			require_once INCLUDES.'mimetypes_include.php';
			$file_type_list = array();
			$file_image_list = array();
			foreach (mimeTypes() as $file_ext => $occ) {
				if (!in_array($file_ext, array_flip(img_mimeTypes()))) {
					$file_type_list[] = '.'.$file_ext;
				}
			}
			foreach (img_mimeTypes() as $file_ext => $occ) {
				$file_image_list[] = '.'.$file_ext;
			}
			function calculate_byte($download_max_b) {
				global $locale;
				$calc_opts = array(1 => $locale['fields_0490'], 1000 => $locale['fields_0491'], 1000000 => $locale['fields_0492']);
				foreach ($calc_opts as $byte => $val) {
					if ($download_max_b/$byte <= 999) {
						return $byte;
					}
				}
				return 1000000;
			}

			$calc_opts = array(1 => $locale['fields_0490'], 1000 => $locale['fields_0491'], 1000000 => $locale['fields_0492']);
			$calc_c = calculate_byte($config['field_max_b']);
			$calc_b = $config['field_max_b']/$calc_c;
			$file_upload_type = array('file' => $locale['fields_0456'], 'image' => 'Image Only');
			echo form_select('field_upload_type', $locale['fields_0457'], $file_upload_type, $config['field_upload_type']);
			echo form_text('field_upload_path', $locale['fields_0458'], $config['field_upload_path'], array('placeholder' => $locale['fields_0459'],
				'required' => 1));
			echo "<label for='field_max_b'>".$locale['fields_0460']."</label>\n<br/>";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
			echo form_text('field_max_b', '', $calc_b, array('class' => 'm-b-0',
				'number' => 1,
				'required' => 1));
			echo "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";
			echo form_select('field_calc', '', $calc_opts, $calc_c, array('width' => '100%'));
			echo "</div>\n</div>\n";
			// File Type
			echo "<div id='file_type'>\n";
			echo form_select('field_valid_file_ext', $locale['fields_0461'], $file_type_list, $config_1['field_valid_file_ext'], array('multiple' => 1,
				'tags' => 1,
				'required' => 1));
			echo "</div>\n";
			// Image Type
			echo "<div id='image_type'>\n";
			echo form_select('field_valid_image_ext', $locale['fields_0462'], $file_image_list, $config_2['field_valid_image_ext'], array('multiple' => 1,
				'tags' => 1,
				'required' => 1));
			echo "<label>".$locale['fields_0463']."</label>\n<br/>";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
			echo form_text('field_image_max_w', $locale['fields_0464'], $config_2['field_image_max_w'], array('number' => 1,
				'placeholder' => $locale['fields_0466'],
				'required' => 1));
			echo "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";
			echo form_text('field_image_max_h', $locale['fields_0465'], $config_2['field_image_max_h'], array('number' => 1,
				'placeholder' => $locale['fields_0466'],
				'required' => 1));
			echo "</div>\n</div>\n";
			echo form_checkbox('field_thumbnail', $locale['fields_0467'], $config_2['field_thumbnail']);
			echo "<div id='field_t1'>\n";
			echo form_text('field_thumb_upload_path', $locale['fields_0468'], $config_2['field_thumb_upload_path'], array('placeholder' => $locale['fields_0469'],
				'required' => 1));
			echo "<label>".$locale['fields_0470']."</label>\n<br/>";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
			echo form_text('field_thumb_w', $locale['fields_0471'], $config_2['field_thumb_w'], array('number' => 1,
				'placeholder' => $locale['fields_0466'],
				'required' => 1));
			echo "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";
			echo form_text('field_thumb_h', $locale['fields_0472'], $config_2['field_thumb_h'], array('number' => 1,
				'placeholder' => $locale['fields_0466'],
				'required' => 1));
			echo "</div>\n</div>\n";
			echo "</div>\n";
			echo form_checkbox('field_thumbnail_2', $locale['fields_0473'], $config_2['field_thumbnail_2']);
			echo "<div id='field_t2'>\n";
			echo form_text('field_thumb2_upload_path', $locale['fields_0474'], $config_2['field_thumb2_upload_path'], array('placeholder' => $locale['fields_0469'],
				'required' => 1));
			echo "<label>".$locale['fields_0475']."</label>\n<br/>";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
			echo form_text('field_thumb2_w', $locale['fields_0476'], $config_2['field_thumb2_h'], array('number' => 1,
				'placeholder' => $locale['fields_0466'],
				'required' => 1));
			echo "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";
			echo form_text('field_thumb2_h', $locale['fields_0477'], $config_2['field_thumb2_h'], array('number' => 1,
				'placeholder' => $locale['fields_0466'],
				'required' => 1));
			echo "</div>\n</div>\n";
			echo "</div>\n";
			echo form_checkbox('field_delete_original', $locale['fields_0478'], $config_2['field_delete_original']);
			echo "</div>\n";
			add_to_jquery("
			if ($('#field_upload_type').select2().val() == 'image') {
				$('#image_type').show();
				$('#file_type').hide();
			} else {
				$('#image_type').hide();
				$('#file_type').show();
			}
			$('#field_upload_type').bind('change', function() {
				if ($(this).select2().val() == 'image') {
				$('#image_type').show();
				$('#file_type').hide();
				} else {
				$('#image_type').hide();
				$('#file_type').show();
				}
			});
			// thumbnail
			$('#field_thumbnail').is(':checked') ? $('#field_t1').show() : $('#field_t1').hide();
			$('#field_thumbnail').bind('click', function() {
				$(this).is(':checked') ? $('#field_t1').show() : $('#field_t1').hide();
			});
			// thumbnail 2
			$('#field_thumbnail_2').is(':checked') ? $('#field_t2').show() : $('#field_t2').hide();
			$('#field_thumbnail_2').bind('click', function() {
				$(this).is(':checked') ? $('#field_t2').show() : $('#field_t2').hide();
			});
			");
		} else {
			// @todo add config for textarea
			if ($this->field_data['field_type'] !== 'textarea') echo form_text('field_default', $locale['fields_0480'], $this->field_data['field_default']);
			echo form_text('field_error', $locale['fields_0481'], $this->field_data['field_error']);
		}
		echo form_checkbox('field_required', $locale['fields_0482'], $this->field_data['field_required']);
		echo form_checkbox('field_log', $locale['fields_0483'], $this->field_data['field_log']);
		echo form_text('field_order', $locale['fields_0484'], $this->field_data['field_order'], array('number' => 1));
		echo form_checkbox('field_registration', $locale['fields_0485'], $this->field_data['field_registration']);
		echo form_hidden('', 'add_field', 'add_field', $this->field_data['field_type']);
		echo form_hidden('', 'field_id', 'field_id', $this->field_data['field_id']);
		echo form_button('save_field', $locale['fields_0488'], 'save', array('class' => 'btn-sm btn-primary'));
		echo closeform();
		echo "</div>\n";
	}

	/** Add Modules Plugin Form */
	private function quantum_module_form() {
		global $aidlink, $defender, $locale;

		$form_action = FUSION_SELF.$aidlink;

		if (isset($_GET['action']) && $_GET['action'] == 'module_edit' && isset($_GET['module_id']) && isnum($_GET['module_id'])) {
			$form_action .= "&amp;action=".$_GET['action']."&amp;module_id=".$_GET['module_id'];
			$result = dbquery("SELECT * FROM ".$this->field_db." WHERE field_id='".$_GET['module_id']."'");
			if (dbrows($result) > 0) {
				$this->field_data = dbarray($result);
				if ($this->debug) {
					print_p('Old Data');
					print_p($this->field_data);
				}
			} else {
				if (!$this->debug) redirect(FUSION_SELF.$aidlink);
			}
		}
		$this->field_data['add_module'] = isset($_POST['add_module']) ? form_sanitizer($_POST['add_module']) : $this->field_data['field_name'];

		$user_field_name = '';
		$user_field_api_version = '';
		$user_field_desc = '';
		$user_field_dbname = '';
		$user_field_dbinfo = '';

		if (file_exists($this->plugin_locale_folder.stripinput($this->field_data['add_module']).".php") && file_exists($this->plugin_folder.stripinput($this->field_data['add_module'])."_include_var.php")) {
			include $this->plugin_locale_folder.stripinput($this->field_data['add_module']).".php";
			include $this->plugin_folder.stripinput($this->field_data['add_module'])."_include_var.php";
			$this->user_field_dbinfo = $user_field_dbinfo;
			if (!$user_field_dbinfo) {
				addNotice('warning', $locale['fields_0602']);
			}
		} else {
			$defender->stop();
			addNotice('danger', $locale['fields_0109']);
		}
		// Script Execution
		if (isset($_POST['enable'])) {

			$this->field_data = array(
				'add_module' => isset($_POST['add_module']) ? form_sanitizer($_POST['add_module']) : $this->field_data['field_name'],
				'field_type' => 'file',
				'field_id' => isset($_POST['field_id']) ? form_sanitizer($_POST['field_id'], '', 'field_id') : isset($_GET['module_id']) && isnum($_GET['module_id']) ? $_GET['module_id'] : 0,
				'field_title' => form_sanitizer($_POST['field_title'], '', 'field_title'),
				'field_name' => form_sanitizer($_POST['field_name'], '', 'field_name'),
				'field_cat' => form_sanitizer($_POST['field_cat'], '', 'field_cat'),
				'field_default' => form_sanitizer($_POST['field_default'], '', 'field_default'),
				'field_error' => form_sanitizer($_POST['field_error'], '', 'field_error'),
				'field_required' => isset($_POST['field_required']) ? 1 : 0,
				'field_registration' =>  isset($_POST['field_registration']) ? 1 : 0,
				'field_log' => isset($_POST['field_log']) ? 1 : 0,
				'field_order' => form_sanitizer($_POST['field_order'], '0', 'field_order')
			);
			$this->field_data['field_name'] = str_replace(' ', '_', $this->field_data['field_name']); // make sure no space.
			$this->create_fields($this->field_data, 'module');
		}

		echo "<div class='m-t-20'>\n";
		echo openform('fieldform', 'post', $form_action, array('max_tokens' => 1));
		echo "<p class='strong text-dark'>".$user_field_name."</p>\n";
		echo "<div class='well'>\n";
		echo "<p class='strong'>".$locale['fields_0400']."</p>\n";
		echo "<span class='text-dark strong'>".$locale['fields_0401']."</span> ".($user_field_api_version ? $user_field_api_version : $locale['fields_0402'])."<br/>\n";
		echo "<span class='text-dark strong'>".$locale['fields_0403']."</span>".($user_field_dbname ? "<br/>".$user_field_dbname : '<br/>'.$locale['fields_0404'])."<br/>\n";
		echo "<span class='text-dark strong'>".$locale['fields_0405']."</span>".($user_field_dbinfo ? "<br/>".$user_field_dbinfo : '<br/>'.$locale['fields_0406'])."<br/>\n";
		echo "<span class='text-dark strong'>".$locale['fields_0407']."</span>".($user_field_desc ? "<br/>".$user_field_desc : '')."<br/>\n";
		echo "</div>\n";
		echo "<hr/>\n";
		// start form.
		$disable_opts = array();
		foreach ($this->page_list as $index => $v) {
			$disable_opts[] = $index;
		}
		echo form_select_tree('field_cat', $locale['fields_0410'], $this->field_data['field_cat'], array('no_root' => 1, 'disable_opts' => $disable_opts), $this->category_db, 'field_cat_name', 'field_cat_id', 'field_parent');
		if ($user_field_dbinfo != "") {
			if (version_compare($user_field_api_version, "1.01.00", ">=")) {
				echo form_checkbox('field_required', $locale['fields_0411'], $this->field_data['field_required']);
			} else {
				echo "<p>\n".$locale['428']."</p>\n";
			}
		}

		if ($user_field_dbinfo != "") {
			if (version_compare($user_field_api_version, "1.01.00", ">=")) {
				echo form_checkbox('field_log', $locale['fields_0412'], $this->field_data['field_log']);
			} else {
				echo "<p>\n".$locale['429a']."</p>\n";
			}
		}

		if ($user_field_dbinfo != "") {
			echo form_checkbox('field_registration', $locale['fields_0413'], $this->field_data['field_registration']);
		}

		echo form_text('field_order', $locale['fields_0414'], $this->field_data['field_order']);
		echo form_hidden('', 'add_module', 'add_module', $this->field_data['add_module']);
		echo form_hidden('', 'field_name', 'field_name', $user_field_dbname);
		echo form_hidden('', 'field_title', 'field_title', $user_field_name);
		// new api introduced
		echo form_hidden('', 'field_default', 'field_default', isset($user_field_default) ? $user_field_default : '');
		echo form_hidden('', 'field_options', 'field_options', isset($user_field_options) ? $user_field_options : '');
		echo form_hidden('', 'field_error', 'field_error', isset($user_field_error) ? $user_field_error : '');
		echo form_hidden('', 'field_config', 'field_config', isset($user_field_config) ? $user_field_config : '');
		echo form_hidden('', 'field_id', 'field_id', $this->field_data['field_id']);
		echo form_button('enable', ($this->field_data['field_id'] ? $locale['fields_0415'] : $locale['fields_0416']), ($this->field_data['field_id'] ? $locale['fields_0415'] : $locale['fields_0416']), array('class' => 'btn-primary btn-sm'));
		echo closeform();
		echo "</div>\n";
	}

	private $field_cat_data = array(
		'field_cat_id' => 0,
		'field_cat_name' => '',
		'field_parent' => 0,
		'field_cat_order' => 0,
		'field_cat_db' => '',
		'field_cat_index' => '',
		'field_cat_class' => '',
	);

	private function validate_fieldCat($field_cat_id) {
		if (isnum($field_cat_id)) {
			return dbcount("(field_cat_id)", $this->category_db, "field_cat_id='".intval($field_cat_id)."'");
		}
		return false;
	}

	/* Category & Page Form */
	public function quantum_category_form() {
		global $aidlink, $defender, $locale;

		add_to_jquery("
		$('#field_parent').val() == '0' ? $('#page_settings').show() : $('#page_settings').hide()
		$('#field_parent').bind('change', function() {
		$(this).val() == '0' ? $('#page_settings').show() : $('#page_settings').hide()
		});
		");

		if (isset($_GET['action']) && $_GET['action'] == 'cat_edit' && isset($_GET['cat_id']) && self::validate_fieldCat($_GET['cat_id'])) {
			$result = dbquery("SELECT * FROM ".$this->category_db." WHERE field_cat_id='".$_GET['cat_id']."'");
			if (dbrows($result) > 0) {
				$this->field_cat_data = dbarray($result);
			} else {
				if (!$this->debug) redirect(FUSION_SELF.$aidlink);
			}
		}

		if (isset($_POST['save_cat'])) {

			$this->field_cat_data = array(
				'field_cat_id' => form_sanitizer($_POST['field_cat_id'], '', 'field_cat_id'),
				'field_cat_name' => self::fusion_getlocale($this->field_cat_data, 'field_cat_name'),
				'field_parent' => form_sanitizer($_POST['field_parent'], '', 'field_parent'),
				'field_cat_order' => form_sanitizer($_POST['field_cat_order'], '', 'field_cat_order'),
				'field_cat_db' => form_sanitizer($_POST['field_cat_db'], '', 'field_cat_db'),
				'field_cat_index' => form_sanitizer($_POST['field_cat_index'], '', 'field_cat_index'),
				'field_cat_class' => form_sanitizer($_POST['field_cat_class'], '', 'field_cat_class'),
			);

			if ($this->field_cat_data['field_cat_order'] == 0) {
				$this->field_cat_data['field_cat_order'] = dbresult(dbquery("SELECT MAX(field_cat_order) FROM ".$this->category_db." WHERE field_parent='".$this->field_cat_data['field_parent']."'"), 0)+1;
			}
			if ($this->field_cat_data['field_parent'] > 0) {
				$this->field_cat_data['field_cat_db'] = '';
				$this->field_cat_data['field_cat_index'] = '';
				$this->field_cat_data['field_cat_class'] = '';
			}

			function build_table($table_name, $primary_column) {
				global $locale, $defender;
				$result = null;
				if (!db_exists(DB_PREFIX.$table_name)) {
					$result = dbquery("CREATE TABLE ".DB_PREFIX.$table_name." (
								".$primary_column."_id MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
								".$primary_column." MEDIUMINT(8) NOT NULL DEFAULT '0',
								PRIMARY KEY (".$primary_column."_id)
								) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
				} else {
					$defender->stop();
					addNotice('danger', $locale['fields_0110']);
				}
				return $result;
			}

			// shuffle between save and update
			if (self::validate_fieldCat($this->field_cat_data['field_cat_id'])) {
				if ($this->debug) print_p('Update Mode');
				if ($this->debug) print_p($this->field_cat_data);
				dbquery_order($this->category_db, $this->field_cat_data['field_cat_order'], 'field_cat_order', $this->field_cat_data['field_cat_id'], 'field_cat_id', $this->field_cat_data['field_parent'], 'field_parent', false, false, 'update');
				if (!$this->debug && !defined('FUSION_NULL') && $this->field_cat_data['field_cat_db'] && $this->field_cat_data['field_cat_index'] && $this->field_cat_data['field_cat_db'] !== 'users') { // if entered a field cat db and index and is not DB_USERS
					if (!db_exists(DB_PREFIX.$this->field_cat_data['field_cat_db'])) { // check duplicates.
						// create principal table
						$result = build_table($this->field_cat_data['field_cat_db'], $this->field_cat_data['field_cat_index']);
					} else {
						$defender->stop();
						addNotice('danger', $locale['fields_0110']);
					}
				}
				if (!$this->debug && !defined('FUSION_NULL')) dbquery_insert($this->category_db, $this->field_cat_data, 'update');
				if (!$this->debug && !defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;status=update_cat");
			} else {
				if ($this->debug) print_p('Save Mode');
				if ($this->debug) print_p($this->field_cat_data);
				if ($this->field_cat_data['field_cat_index'] !=='' && $this->field_cat_data['field_cat_db'] !== 'users') {
					$result = build_table($this->field_cat_data['field_cat_db'], $this->field_cat_data['field_cat_index']);
				}
				dbquery_order($this->category_db, $this->field_cat_data['field_cat_order'], 'field_cat_order', $this->field_cat_data['field_cat_id'], 'field_cat_id', $this->field_cat_data['field_parent'], 'field_parent', true, 'field_cat_name', 'save');
				if (!$this->debug && !defined('FUSION_NULL')) dbquery_insert($this->category_db, $this->field_cat_data, 'save');
				if (!$this->debug && !defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&status=cat_save");
			}
		}

		// exclusion list - unselectable
		$cat_list = array();
		if (!empty($this->cat_list)) {
			foreach ($this->cat_list as $id => $value) {
				$cat_list[] = $id;
			}
		}

		$html = openform('cat_form', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
		$html .= self::quantum_multilocale_fields($locale['fields_0430'], 'field_cat_name', $this->field_cat_data['field_cat_name'], array('required'=>1));
		$html .= form_select_tree('field_parent', $locale['fields_0431'], $this->field_cat_data['field_parent'], array('parent_value' => $locale['fields_0432'], 'disable_opts' => $cat_list), $this->category_db, 'field_cat_name', 'field_cat_id', 'field_parent');
		$html .= form_text('field_cat_order', $locale['fields_0433'], $this->field_cat_data['field_cat_order'], array('number' => 1));
		$html .= form_hidden('', 'field_cat_id', 'field_cat_id', $this->field_cat_data['field_cat_id'], array('number' => 1));
		$html .= form_hidden('', 'add_cat', 'add_cat', 'add_cat');
		// root settings
		$html .= "<div id='page_settings'>\n";
		$html .= "<div class='text-smaller m-b-10'>".$locale['fields_0111']."</div>\n";
		$html .= form_text('field_cat_db', sprintf($locale['fields_0434'], DB_PREFIX), $this->field_cat_data['field_cat_db'], array('placeholder' => 'users'));
		$html .= "<div class='text-smaller m-b-10'>".$locale['fields_0112']."</div>\n";
		$html .= form_text('field_cat_index', $locale['fields_0435'], $this->field_cat_data['field_cat_index'], array('placeholder' => 'user_id'));
		$html .= "<div class='text-smaller m-b-10'>".$locale['fields_0113']."</div>\n";
		$html .= form_text('field_cat_class', $locale['fields_0436'], $this->field_cat_data['field_cat_class'], array('placeholder' => 'entypo xxxxx'));
		$html .= form_hidden('', 'add_cat', 'add_cat', 'add_cat');
		$html .= "</div>\n";
		$html .= form_button('save_cat', $locale['fields_0318'], 'save_cat', array('class' => 'btn-sm btn-primary'));
		$html .= closeform();
		return $html;
	}

	/** Populates enabled and available Plugin Fields Var */
	private function get_available_modules() {
		$result = dbquery("SELECT field_id, field_name, field_cat, field_required, field_log, field_registration, field_order, field_cat_name
					FROM ".$this->field_db." tuf
					INNER JOIN ".$this->category_db." tufc ON (tuf.field_cat = tufc.field_cat_id)
					WHERE field_type = 'file'
					ORDER BY field_cat_order, field_order");
		if (dbrows($result) > 0) {
			while ($data = dbarray($result)) {
				$this->enabled_fields[] = $data['field_name'];
			}
		}
		$user_field_name = '';
		$user_field_desc = '';
		if ($temp = opendir($this->plugin_folder)) {
			while (FALSE !== ($file = readdir($temp))) {
				if (!in_array($file, array("..", ".", "index.php")) && !is_dir($this->plugin_folder.$file)) {
					if (preg_match("/_var.php/i", $file)) {
						$field_name = explode("_", $file);
						$field_title = "";
						for ($i = 0; $i <= count($field_name) - 3; $i++) {
							$field_title .= ($field_title) ? "_" : "";
							$field_title .= $field_name[$i];
						}
						if (!in_array($field_title, $this->enabled_fields)) {
							if ($this->module_debug) print_p($field_title." set for load.");
							if (file_exists($this->plugin_locale_folder.$field_title.".php")) {
								include $this->plugin_locale_folder.$field_title.".php";
								include $this->plugin_folder.$field_title."_include_var.php";
								$this->available_field_info[$field_title] = array('title' => $user_field_name,
									'description' => $user_field_desc);
								$this->get_available_modules[$field_title] = $user_field_name;
								if ($this->module_debug) print_p($field_title." loaded.");
							} elseif ($this->module_debug) {
								print_p($field_title." locale missing!");
							}
						}
						unset($field_name);
					}
				}
			}
			closedir($temp);
		}
	}

	/** Display fields for each fieldDB record entry */
	public static function display_fields($data, $callback_data, $method = 'input', array $options = array()) {
		global $locale;
		/**
		 * Options
		 * =========
		 * hide_value = input value is not shown on fields render (off by default)
		 * encrypt = encrypt field names (off by default)
		 * show_title = display field label (off by default)
		 * deactivate = disable fields (off by default)
		 * inline = sets the field inline (off by default)
		 * error_text = sets the field error text (off by default)
		 * required = input must be filled when validate (off by default)
		 * placeholder = helper text in field name (off by default)
		 */
		$options['error_text'] = !empty($data['field_error']) ? $data['field_error'] : '';
		$options += array(
			'hide_value' => !empty($options['hide_value']) && $options['hide_value'] == 1 ? 1 : 0,
			'encrypt'	=> !empty($options['encrypt']) && $options['encrypt'] == 1 ? 1 : 0,
			'show_title' => !empty($options['show_title'])  && $options['show_title'] == 1 ? 1 : 0,
			'deactivate' => !empty($options['deactivate'])  && $options['deactivate'] == 1 ? 1 : 0,
			'inline'	=> !empty($options['inline']) && $options['inline'] == 1 ? 1 : 0,
			'error_text' => !empty($options['error_text']) ? $options['error_text'] : '',
			'required' => !empty($data['field_required']) || !empty($options['required']) ? $data['field_required'] : 0,
			'placeholder' => !empty($data['field_default']) || !empty($options['placeholder']) ? $data['field_default'] : '',
			'plugin_folder' => !empty($data['plugin_folder']) ? $data['plugin_folder'] : '',
			'plugin_locale_folder' => !empty($data['plugin_locale_folder']) ? $data['plugin_locale_folder'] : '',
			'debug' => !empty($data['debug']) && $data['debug'] == 1 ? 1 : 0
		);
		$option_list = $data['field_options'] ? explode(',', $data['field_options']) : array();

		$field_value = isset($callback_data[$data['field_name']]) ? $callback_data[$data['field_name']] : '';
		if (isset($_POST[$data['field_name']]) && !$options['hide_value']) {
			$field_value = $_POST[$data['field_name']];
		} elseif ($options['hide_value']) {
			$field_value = '';
		}

		switch($data['field_type']) {
			case 'file':
				$profile_method = $method;
				// can access options vars
				if (file_exists($options['plugin_locale_folder'].$data['field_name'].".php")) include $options['plugin_locale_folder'].$data['field_name'].".php";
				if (file_exists($options['plugin_folder'].$data['field_name']."_include.php")) include $options['plugin_folder'].$data['field_name']."_include.php";
				if ($options['debug']) print_p("Finding ".$options['plugin_locale_folder'].$data['field_name'].".php");
				if ($options['debug'] && file_exists($options['plugin_locale_folder'].$data['field_name'].".php")) print_p($data['field_name']." locale loaded");
				if ($options['debug']) print_p("Finding ".$options['plugin_folder'].$data['field_name']."_include.php");
				if ($options['debug'] && file_exists($options['plugin_folder'].$data['field_name']."_include.php")) print_p($data['field_name']." module loaded");
				if (isset($user_fields)) return $user_fields;
				break;
			case 'textbox':
				if ($method == 'input') {
					return form_text($data['field_name'], $options['show_title'] ? self::parse_label($data['field_title']) : '', $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>$callback_data[$data['field_name']]);
				}
				break;
			case 'number':
				if ($method == 'input') {
					$options += array('type' => 'number');
					return form_text($data['field_name'], $options['show_title'] ? self::parse_label($data['field_title']) : '', $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>$callback_data[$data['field_name']]);
				}
				break;
			case 'url':
				if ($method == 'input') {
					$options += array('type' => 'url');
					return form_text($data['field_name'], $options['show_title'] ? self::parse_label($data['field_title']) : '', $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>$callback_data[$data['field_name']]);
				}
				break;
			case 'email':
				if ($method == 'input') {
					$options += array('type' => 'email');
					return form_text($data['field_name'], $options['show_title'] ? self::parse_label($data['field_title']) : '', $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>$callback_data[$data['field_name']]);
				}
				break;
			case 'select':
				if ($method == 'input') {
					return form_select($data['field_name'], self::parse_label($data['field_title']), $option_list, $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>$callback_data[$data['field_name']]);
				}
				break;
			case 'tags':
				if ($method == 'input') {
					$options += array('tags'=>1, 'multiple'=>1, 'width'=>'100%');
					return form_select($data['field_name'], $options['show_title'] ? self::parse_label($data['field_title']) : '', $option_list, $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>$callback_data[$data['field_name']]);
				}
				break;
			case 'location':
				if ($method == 'input') {
					$options += array('width'=>'100%');
					return form_location(self::parse_label($data['field_title']), $data['field_name'], $data['field_name'], $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>$callback_data[$data['field_name']]);
				}
				break;
			case 'textarea':
				if ($method == 'input') {
					return form_textarea($data['field_name'], $options['show_title'] ? self::parse_label($data['field_title']) : '', $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>$callback_data[$data['field_name']]);
				}
				break;
			case 'checkbox':
				if ($method == 'input') {
					return form_checkbox($data['field_name'], $options['show_title'] ? self::parse_label($data['field_title']) : '', $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>$callback_data[$data['field_name']]);
				}
				break;
			case 'datepicker':
				if ($method == 'input') {
					return form_datepicker($data['field_name'], $options['show_title'] ? self::parse_label($data['field_title']) : '', $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>showdate('shortdate', $callback_data[$data['field_name']]));
				}
				break;
			case 'colorpicker':
				if ($method == 'input') {
					return form_colorpicker($data['field_name'], $options['show_title'] ? self::parse_label($data['field_title']) : '', $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>$callback_data[$data['field_name']]);
				}
				break;
			case 'uploader':
				if ($method == 'input') {
					return form_fileinput(self::parse_label($data['field_title']), $data['field_name'], $data['field_name'], $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>$callback_data[$data['field_name']]);
				}
				break;
			case 'hidden':
				if ($method == 'input') {
					return form_hidden(self::parse_label($data['field_title']), $data['field_name'], $data['field_name'], $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>$callback_data[$data['field_name']]);
				}
				break;
			case 'address':
				if ($method == 'input') {
					return form_address($options['show_title'] ? self::parse_label($data['field_title']) : '', $data['field_name'], $data['field_name'], $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>implode('|', $callback_data[$data['field_name']]));
				}
				break;
			case'toggle':
				$options['toggle'] = 1;
				$options['toggle_text'] = array($locale['off'], $locale['on']);

				if ($method == 'input') {
					return form_checkbox($data['field_name'], $options['show_title'] ? self::parse_label($data['field_title']) : '', $field_value, $options);
				} elseif ($method == 'display' && isset($field_data[$data['field_name']]) && $field_data[$data['field_name']]) {
					return array('title'=>self::parse_label($data['field_title']), 'value'=>$option_array[$callback_data[$data['field_name']]]);
				}
				break;
			default:
				return false;
		}
	}

	/* DEPRECATE */
	/* record fields each modules, and fields */
	/* will only update Modules. core fields is not going to be recorded, so you need to import $data in. */
	public function quantum_insert(array $data = array()) {
		$quantum_fields = array();
		$infinity_ref = array();
		// bug fix: to get only the relevant fields on specific page.
		$field_list = flatten_array($this->fields);
		// to generate $infinity_ref and $quantum_fields as reference and validate the $_POST input value.
		foreach($field_list as $field_id => $field_data) {
			if ($field_data['field_parent'] == $this->input_page) {
				$target_database = $field_data['field_cat_db'] ? DB_PREFIX.$field_data['field_cat_db'] : DB_USERS;
				$target_index = $field_data['field_cat_index'] ? $field_data['field_cat_index'] : 'user_id';
				$index_value = isset($_POST[$target_index]) ? form_sanitizer($_POST[$target_index], 0) : $data[$target_index];
				// create reference array
				$infinity_ref[$target_database] = array('index'=>$target_index, 'value'=>$index_value);
				if (isset($_POST[$field_data['field_name']])) {
					$quantum_fields[$target_database][$field_data['field_name']] = form_sanitizer($_POST[$field_data['field_name']], $field_data['field_default'], $field_data['field_name']);
				} else {
					$quantum_fields[$target_database][$field_data['field_name']] = isset($data['field_name']) ? $data[$field_data['field_name']] : '';
				}
			}
		}
		if (!empty($quantum_fields)) {
			$temp_table = '';
			foreach($quantum_fields as $_dbname => $_field_values) {
				$merged_data = array();
				$merged_data += $_field_values;
				$merged_data += $data; // appends all other necessary values to fill up the entire table values.
				if ($temp_table !== $_dbname) { // if $temp_table is different. check if table exist. run once if pass
					$merged_data += array($infinity_ref[$_dbname]['index'] => $infinity_ref[$_dbname]['value']); // Primary Key and Value.
					// ensure nothing is missing. this might be overkill. I would shut it down if not neccessary to lighten the load by 1-2 uncessary query.
					$result = dbquery("SELECT * FROM ".$_dbname." WHERE ".$infinity_ref[$_dbname]['index']." = '".$infinity_ref[$_dbname]['value']."'");
					if (dbrows($result)>0) {
						$merged_data += dbarray($result);
					}
				}
				dbquery_insert($_dbname, $merged_data, 'update');
			}
		}
	}

	/* Single array output match against $db - use get_structureData before to populate $fields */
	public function return_fields_input($db, $primary_key) {
		$output_fields = array();
		$field = flatten_array($this->fields);
		$output_fields[$db] = $this->callback_data;
		foreach($field as $arr => $field_data) {
			$target_database = $field_data['field_cat_db'] ? DB_PREFIX.$field_data['field_cat_db'] : $db;
			$col_name = $field_data['field_cat_index'] ? $field_data['field_cat_index'] : $primary_key;
			$index_value = isset($_POST[$col_name]) ? form_sanitizer($_POST[$col_name], 0) : '';
			// set once
			if (!isset($quantum_fields[$target_database][$col_name])) $quantum_fields[$target_database][$col_name] = $index_value;
			$output_fields[$target_database][$field_data['field_name']] = '';
			if (isset($_POST[$field_data['field_name']])) {
				$output_fields[$target_database][$field_data['field_name']] = form_sanitizer($_POST[$field_data['field_name']], $field_data['field_default'], $field_data['field_name']);
			}
		}
		return $output_fields;
	}
}