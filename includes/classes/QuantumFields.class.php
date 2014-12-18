<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: QuantumFields.class.php
| Author: PHP-Fusion Inc
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
require_once LOCALE.LOCALESET.'admin/fields.php';
class quantumFields {
	// Setup XUI
	public $system_title = '';
	public $admin_rights = '';
	public $locale_file = '';
	public $category_db = '';
	public $field_db = '';
	public $plugin_folder = '';
	public $plugin_locale_folder = '';
	public $debug = FALSE;
	// System Internals
	private $max_rows = 0;
	private $locale = array();
	private $page_list = array();
	private $cat_list = array();
	private $page = array();
	private $fields = array(); // maybe can mix with enabled_fields.
	private $enabled_fields = array();
	private $available_fields = array();
	private $available_field_info = array();
	private $user_field_dbinfo = '';

	/* Constructor */
	public function boot() {
		global $locale;
		$this->locale = $locale;
		add_to_breadcrumbs(array('link' => '', 'title' => $this->system_title));
		add_to_title(': '.$this->system_title);
		$this->verify_field_tables();
		$this->load_data();
		$this->load_field_cats();
		$this->move_fields();
		$this->delete_category();
		$this->delete_fields();
		$this->available_fields();
		$this->render_fields();
	}

	/* System integrity check and repairs */
	private function verify_field_tables() {
		if (!db_exists($this->category_db)) {
			// build the table if not exist.
			$result = dbquery("CREATE TABLE ".$this->category_db." (
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
		// build the table if not exist.
		if (!db_exists($this->field_db)) {
			$result = dbquery("CREATE TABLE ".$this->field_db." (
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
	public function load_data() {
		// get the page first.
		$this->page = dbquery_tree_full($this->category_db, 'field_cat_id', 'field_parent', "ORDER BY field_cat_order ASC");
		$result = dbquery("SELECT field.*, cat.* FROM
		".$this->field_db." field
		LEFT JOIN ".$this->category_db." cat on (cat.field_cat_id = field.field_cat)
		ORDER BY cat.field_cat_order ASC, field.field_order ASC
		");
		$this->max_rows = dbrows($result);
		if ($this->max_rows > 0) {
			while ($data = dbarray($result)) {
				$this->fields[$data['field_cat']][] = $data;
			}
		}
	}

	public function render_fields() {
		global $aidlink;
		$locale = $this->locale;
		if ($this->debug) print_p($_POST);
		opentable($this->system_title);
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-8 col-md-8 col-lg-8'>\n";
		if (!empty($this->page[0])) {
			foreach ($this->page[0] as $page_id => $page_data) {
				$tab_title['title'][$page_id] = $page_data['field_cat_name'];
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
					echo "<div class='list-group-item display-inline-block'>\n";
					echo "<span class='strong'>".$page_details['field_cat_name']."</span> <a class='text-smaller' href='".FUSION_SELF.$aidlink."&amp;action=cat_edit&amp;cat_id=".$page_id."'>".$locale['edit']."</a> - ";
					echo "<a class='text-smaller' href='".FUSION_SELF.$aidlink."&amp;action=cat_delete&amp;cat_id=".$page_id."'>".$locale['delete']."</a>";
					echo "</div>\n";

					echo "<div class='clearfix m-t-20'>\n";
					$i = 0;
					$counter = count($this->page[$page_id])-1;
					foreach ($this->page[$page_id] as $cat_id => $field_cat) {
						// field category information
						if ($this->debug) print_p($field_cat);
						echo "<div class='clearfix'>\n";
						echo form_para($field_cat['field_cat_name'], $cat_id.'-'.$field_cat['field_cat_name'], 'profile_category_name display-inline-block pull-left');
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
								//print_p($field_data);
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
								echo $this->phpfusion_field_DOM($field_data);
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
		$this->phpfusion_field_buttons();
		echo "</div>\n";
		closetable();
	}

	private function move_fields() {
		global $aidlink;
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
	private function delete_category() {
		global $defender, $aidlink;
		$locale = $this->locale;
		$data = array();
		if (isset($_GET['action']) && $_GET['action'] == 'cat_delete' && isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
			// do action
			if (isset($_POST['delete_cat'])) {
				// get root node
				if (isset($_POST['delete_subcat']) or isset($_POST['delete_field'])) {
					if (in_array($_GET['cat_id'], $this->page_list)) { // this is root.
						$result = dbquery("SELECT field_cat_id, field_cat_db FROM ".$this->category_db." WHERE field_cat_id='".$_GET['cat_id']."'");
					} else { // is is not a root.
						$result = dbquery("SELECT uf.field_cat_id, root.field_cat_db FROM ".$this->category_db." uf LEFT JOIN ".$this->category_db." root ON (uf.field_parent = root.field_cat_id) WHERE uf.field_cat_id='".intval($_GET['cat_id'])."'");
					}
					$target_database = ''; $field_list = array();
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
					$result = dbquery("SELECT field_id, field_name FROM ".$this->field_db." WHERE field_cat='".intval($_GET['cat_id'])."'");
					if (dbrows($result)>0) {
						$new_parent = form_sanitizer($_POST['move_field'], 0, 'move_field');
						while ($data = dbarray($result)) {
							if (!$this->debug) $result = dbquery("UPDATE ".$this->field_db." SET field_cat='".$new_parent."' WHERE field_id='".$data['field_id']."'");
							if ($this->debug) print_p("MOVED ".$data['field_id']." TO category ".$new_parent);
						}
						if (!$this->debug) $result = dbquery("DELETE FROM ".$this->category_db." WHERE field_cat_id='".intval($_GET['cat_id'])."'");
						if ($this->debug) print_p("DELETE ".$_GET['cat_id']." FROM ".$this->category_db);
					}
				}
				if (!$this->debug) redirect(FUSION_SELF.$aidlink."&amp;status=cat_deleted");
			}
			// show form
			else {
				$form_action = FUSION_SELF.$aidlink."&amp;action=cat_delete&amp;cat_id=".$_GET['cat_id'];
				$result = dbquery("SELECT * FROM ".$this->category_db." WHERE field_cat_id='".$_GET['cat_id']."'");
				if (dbrows($result) > 0) {
					$data += dbarray($result);

					// get field list
					$field_list = array();
					$result = dbquery("SELECT field_id, field_name, field_cat FROM ".$this->field_db." WHERE field_cat='".intval($_GET['cat_id'])."'");
					if (dbrows($result)>0) {
						// get field list.
						while ($data = dbarray($result)) {
							$field_list[$data['field_cat']][$data['field_id']] = $data['field_name'];
						}
					}


					if (isset($this->page[$_GET['cat_id']]) or $field_list[$_GET['cat_id']] > 0) {
						echo openmodal("delete", $locale['fields_0313'], array('class'=>'modal-lg modal-center zindex-boost', 'static'=>1));
						echo openform('delete_cat_form', 'delete_cat_form', 'post', $form_action, array('downtime'=>0));
						if (isset($this->page[$_GET['cat_id']])) {
							echo "<div class='row'>\n";
							echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n<span class='strong'>".sprintf($locale['fields_0600'], count($this->page[$_GET['cat_id']]) )."</span><br/>\n";
							echo "<div class='alert alert-info m-t-10 text-smaller strong'>\n";
							foreach($this->page[$_GET['cat_id']] as $arr=>$field_category) {
								echo "- ".$field_category['field_cat_name']."<br/>\n";
							}
							echo "</div>\n";
							echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
							$page_list = $this->page_list;
							unset($page_list[$_GET['cat_id']]);
							if (count($page_list) >0) {
								echo form_select($locale['fields_0314'], 'move_subcat', 'move_subcat', $page_list, '');
							}
							echo form_checkbox($locale['fields_0315'], 'delete_subcat', 'delete_subcat', '');
							echo "</div></div>";
						}

						if (isset($field_list[$_GET['cat_id']])) {
							echo "<div class='row'>\n";
							echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n<span class='strong'>".sprintf($locale['fields_0601'], count($field_list[$_GET['cat_id']]))."</span><br/>\n";
							echo "<div class='alert alert-info m-t-10 text-smaller strong'>\n";
							foreach($field_list[$_GET['cat_id']] as $arr=>$field) {
								echo "- ".$field."<br/>\n";
							}
							echo "</div>\n";
							echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
							$exclude_list[] = $_GET['cat_id'];
							foreach($this->page_list as $page_id => $page_name) {
								$exclude_list[] = $page_id;
							}
							echo form_select_tree($locale['fields_0316'], 'move_field', 'move_field', '', array('no_root'=>1, 'disable_opts'=>$exclude_list), $this->category_db, 'field_cat_name', 'field_cat_id', 'field_parent');
							echo form_checkbox($locale['fields_0317'], 'delete_field', 'delete_field', '');
							echo "</div></div>";
						}
						echo form_button($locale['fields_0313'], 'delete_cat', 'delete_cat', $locale['fields_0313'], array('class'=>'btn-danger btn-sm'));
						echo form_button($locale['cancel'], 'cancel', 'cancel', $locale['cancel'], array('class'=>'btn-default m-l-10 btn-sm'));
						echo closeform();
						echo closemodal();
					}
				} else {
					redirect(FUSION_SELF.$aidlink);
				}
			}
		}
	}

	/* Execution of delete fields */
	private function delete_fields() {
		global $aidlink;
		if (isset($_GET['action']) && $_GET['action'] == 'field_delete' && isset($_GET['field_id']) && isnum($_GET['field_id'])) {
			$result = dbquery("SELECT field.field_id, field.field_cat, field.field_order, field.field_name, u.field_cat_id, u.field_parent, root.field_cat_db
			FROM ".$this->field_db." field
			LEFT JOIN ".$this->category_db." u ON (field.field_cat=u.field_cat_id)
			LEFT JOIN ".$this->category_db." root on (u.field_parent = root.field_cat_id)
			WHERE field_id='".intval($_GET['field_id'])."'
			");
			if (dbrows($result)>0) {
				$data = dbarray($result);
				$target_database = $data['field_cat_db'] ? DB_PREFIX.$data['field_cat_db'] : DB_USERS;
				$field_list = fieldgenerator($target_database);
				if (in_array($data['field_name'], $field_list)) {
					// drop database
					if (!$this->debug && ($target_database)) $result = dbquery("ALTER TABLE ".$target_database." DROP ".$data['field_name']);
					if ($this->debug) print_p("DROP ".$data['field_name']." FROM ".$target_database);
					// reorder the rest of the same cat minus 1
					if (!$this->debug && ($target_database)) $result = dbquery("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_order > '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
					if (!$this->debug && ($target_database)) $result = dbquery("DELETE FROM ".$this->field_db." WHERE field_id='".$data['field_id']."'");
					if ($this->debug) print_p("DELETE ".$data['field_id']." FROM ".$this->field_db);
				}
				if (!$this->debug) redirect(FUSION_SELF.$aidlink."status=field_deleted");
			} else {
				if (!$this->debug) redirect(FUSION_SELF.$aidlink);
			}
		}
	}

	/* Returns $cat_list */
	private function load_field_cats() {
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
		$info = array('textbox' => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
			'select' => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
			'textarea' => "TEXT NOT NULL",
			'checkbox' => "TINYINT(3) NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
			'toggle' => "TINYINT(3) NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
			'datepicker' => "TINYINT(10) NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
			'colorpicker' => "VARCHAR(10) NOT NULL DEFAULT '".$default_value."'",
			'upload' => "VARCHAR(100) NOT NULL DEFAULT '".$default_value."'",
			'hidden' => "VARCHAR(50) NOT NULL DEFAULT '".$default_value."'",
			'address' => "TEXT NOT NULL",);
		return $info[$type];
	}

	/* The Current Stable PHP-Fusion Dynamics Module */
	private function dynamics_type() {
		$locale = $this->locale;
		return array('file' => $locale['fields_0500'],
			'textbox'       => $locale['fields_0501'],
			'select'        => $locale['fields_0502'],
			'textarea'      => $locale['fields_0503'],
			'checkbox'      => $locale['fields_0504'],
			'toggle'        => $locale['fields_0505'],
			'datepicker'    => $locale['fields_0506'],
			'colorpicker'   => $locale['fields_0507'],
			'upload'        => $locale['fields_0508'],
			'hidden'        => $locale['fields_0509'],
			'address'       => $locale['fields_0510']);
	}

	private function synthesize_fields($data, $type = 'dynamics') {
		global $aidlink, $defender;
		$locale = $this->locale;
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

		$rows = dbcount("(field_id)", $this->field_db, "field_id='".$data['field_id']."'");
		if ($rows) {
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
							$defender->addNotice(sprintf($locale['fields_0104'], $cat_data['field_cat_name']));
						}
					}
					// make ordering of the same table.
					print_p($data['field_order']);
					print_p($cat_data['field_order']);
					if ($data['field_order'] > $cat_data['field_order']) {
						if (!$this->debug) $result = dbquery("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_order > ".$cat_data['field_order']." AND field_order <= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
						if ($this->debug) print_p("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_order > '".$cat_data['field_order']."' AND field_order <= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
					} elseif ($data['field_order'] < $cat_data['field_order']) {
						if (!$this->debug) $result = dbquery("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_order < ".$cat_data['field_order']." AND field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
						if ($this->debug) print_p("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_order < '".$cat_data['field_order']."' AND field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
					}
				}
				if ($this->debug) print_p($data);
				if (!$this->debug && !defined('FUSION_NULL')) dbquery_insert($this->field_db, $data, 'update');
				if (!$this->debug && !defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink.'&amp;status=field_updated');
			} else {
				$defender->stop();
				$defender->addNotice($locale['fields_0105']);
			}
		} else {
			if ($this->debug) print_p('Save Mode');
			// Alter $this->field_db table - add column.
			$cresult = dbquery("SELECT cat.field_cat_id, cat.field_parent, cat.field_cat_order, root.field_cat_db, root.field_cat_index
									FROM ".$this->category_db." cat
									LEFT JOIN ".$this->category_db." root ON (cat.field_parent = root.field_cat_id)
									WHERE cat.field_cat_id='".$data['field_cat']."'");
			if (dbrows($cresult) > 0) {
				$cat_data = dbarray($cresult);
				$target_database = $cat_data['field_cat_db'] ? DB_PREFIX.$cat_data['field_cat_db'] : DB_USERS;
				$field_arrays = fieldgenerator($target_database);
				if (!in_array($data['field_name'], $field_arrays)) { // safe to execute alter.
					if (!$this->debug) $result = dbquery("ALTER TABLE ".$target_database." ADD ".$data['field_name']." ".$field_attr);
					if ($this->debug) print_p("Alter DB_".$target_database." with ".$data['field_name']." on ".$field_attr);
				} else {
					$defender->stop();
					$defender->addNotice($locale['fields_0106']);
				}
				// ordering
				if ($this->debug) print_p($data);
				if (!$this->debug && !defined('FUSION_NULL')) $result = dbquery("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_order > '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
				if (!$this->debug && !defined('FUSION_NULL')) dbquery_insert($this->field_db, $data, 'save');
				if (!$this->debug && !defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink.'&amp;status=field_added');
			} else {
				$defender->stop();
				$defender->addNotice($locale['fields_0107']);
			}
		}
	}

	/* The master form for Adding or Editing Dynamic Fields */
	private function dynamics_form() {
		global $aidlink, $defender, $locale;
		$config = array();
		$config_1 = array();
		$config_2 = array();
		$data = array();
		$cat_list = $this->cat_list;
		$form_action = FUSION_SELF.$aidlink;
		if (isset($_GET['action']) && $_GET['action'] == 'field_edit' && isset($_GET['field_id']) && isnum($_GET['field_id'])) {
			$form_action .= "&amp;action=".$_GET['action']."&amp;field_id=".$_GET['field_id'];
			$result = dbquery("SELECT * FROM ".$this->field_db." WHERE field_id='".$_GET['field_id']."'");
			if (dbrows($result) > 0) {
				$data += dbarray($result);
				if ($data['field_type'] == 'upload') {
					$data += unserialize($data['config']); // uncompress serialized extended information.
				}
			} else {
				if (!$this->debug) redirect(FUSION_SELF.$aidlink);
			}
			if ($this->debug) print_p($data);
			// Initialize Constructor Fields
			$data['field_type'] = isset($_POST['add_field']) ? form_sanitizer($_POST['add_field'], '') : $data['field_type'];
			//if (!$data['field_type']) redirect(FUSION_SELF.$aidlink);
			$data['field_id'] = isset($_POST['field_id']) ? form_sanitizer($_POST['field_id'], '', 'field_id') : $data['field_id'];
			$data['field_title'] = isset($_POST['field_title']) ? form_sanitizer($_POST['field_title'], '', 'field_title') : $data['field_title'];
			$data['field_name'] = isset($_POST['field_name']) ? form_sanitizer($_POST['field_name'], '', 'field_name') : $data['field_name'];
			$data['field_name'] = str_replace(' ', '_', $data['field_name']); // make sure no space.
			$data['field_cat'] = isset($_POST['field_cat']) ? form_sanitizer($_POST['field_cat'], '', 'field_cat') : $data['field_cat'];
			$data['field_options'] = isset($_POST['field_options']) ? form_sanitizer($_POST['field_options'], '', 'field_options') : $data['field_options'];
			$data['field_default'] = isset($_POST['field_default']) ? form_sanitizer($_POST['field_default'], '', 'field_default') : $data['field_default'];
			$data['field_error'] = isset($_POST['field_error']) ? form_sanitizer($_POST['field_error'], '', 'field_error') : $data['field_error'];
			$data['field_required'] = isset($_POST['field_required']) ? 1 : isset($_POST['field_id']) ? 0 : $data['field_required'];
			$data['field_log'] = isset($_POST['field_log']) ? 1 : isset($_POST['field_id']) ? 0 : $data['field_log'];
			$data['field_registration'] = isset($_POST['field_registration']) ? 1 : isset($_POST['field_id']) ? 0 : $data['field_registration'];
			$data['field_order'] = isset($_POST['field_order']) ? form_sanitizer($_POST['field_order'], '0', 'field_order') : $data['field_order'];
			if ($data['field_type'] == 'upload') {
				// these are to be serialized. init all.
				$max_b = isset($_POST['field_max_b']) ? form_sanitizer($_POST['field_max_b'], '', 'field_max_b') : 150000;
				$calc = isset($_POST['field_calc']) ? form_sanitizer($_POST['field_calc'], '', 'field_calc') : 1;
				$config['field_max_b'] = isset($_POST['field_max_b']) && isset($_POST['field_calc']) ? $max_b*$calc : $data['field_max_b'];
				$config['field_upload_type'] = isset($_POST['field_upload_type']) ? form_sanitizer($_POST['field_upload_type'], '', 'field_upload_type') : $data['field_upload_type'];
				$config['field_upload_path'] = isset($_POST['field_upload_path']) ? form_sanitizer($_POST['field_upload_path'], '', 'field_upload_path') : $data['field_upload_path'];
				$config_1['field_valid_file_ext'] = isset($_POST['field_valid_file_ext']) && $config['field_upload_type'] == 'file' ? form_sanitizer($_POST['field_valid_file_ext'], '', 'field_valid_file_ext') : $data['field_valid_file_ext'];
				$config_2['field_valid_image_ext'] = isset($_POST['field_valid_image_ext']) && $config['field_upload_type'] == 'image' ? form_sanitizer($_POST['field_valid_image_ext'], '', 'field_valid_image_ext') : $data['field_valid_image_ext'];
				$config_2['field_image_max_w'] = isset($_POST['field_image_max_w']) && $config['field_upload_type'] == 'image' ? form_sanitizer($_POST['field_image_max_w'], '', 'field_image_max_w') : $data['field_image_max_w'];
				$config_2['field_image_max_h'] = isset($_POST['field_image_max_h']) && $config['field_upload_type'] == 'image' ? form_sanitizer($_POST['field_image_max_h'], '', 'field_image_max_h') : $data['field_image_max_h'];
				$config_2['field_thumbnail'] = isset($_POST['field_thumbnail']) ? form_sanitizer($_POST['field_thumbnail'], 0, 'field_thumbnail') : $data['field_thumbnail'];
				$config_2['field_thumb_upload_path'] = isset($_POST['field_thumb_upload_path']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail'] ? form_sanitizer($_POST['field_thumb_upload_path'], '', 'field_thumb_upload_path') : $data['field_thumb_upload_path'];
				$config_2['field_thumb_w'] = isset($_POST['field_thumb_w']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail'] ? form_sanitizer($_POST['field_thumb_w'], '', 'field_thumb_w') : $data['field_thumb_w'];
				$config_2['field_thumb_h'] = isset($_POST['field_thumb_h']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail'] ? form_sanitizer($_POST['field_thumb_h'], '', 'field_thumb_h') : $data['field_thumb_h'];
				$config_2['field_thumbnail_2'] = isset($_POST['field_thumbnail_2']) ? 1 : isset($_POST['field_id']) ? 0 : $data['field_thumbnail_2'];
				$config_2['field_thumb2_upload_path'] = isset($_POST['field_thumb2_upload_path']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail_2'] ? form_sanitizer($_POST['field_thumb2_upload_path'], '', 'field_thumb2_upload_path') : $data['field_thumb2_upload_path'];
				$config_2['field_thumb2_w'] = isset($_POST['field_thumb2_w']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail_2'] ? form_sanitizer($_POST['field_thumb2_w'], '', 'field_thumb2_w') : $data['field_thumb2_w'];
				$config_2['field_thumb2_h'] = isset($_POST['field_thumb2_h']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail_2'] ? form_sanitizer($_POST['field_thumb2_h'], '', 'field_thumb2_h') : $data['field_thumb2_h'];
				$config_2['field_delete_original'] = isset($_POST['field_delete_original']) && $config['field_upload_type'] == 'image' ? 1 : isset($_POST['field_id']) ? 0 : $data['field_delete_original'];
			}
		} else {
			// Initialize Constructor Fields
			$data['field_type'] = isset($_POST['add_field']) ? form_sanitizer($_POST['add_field'], '') : '';
			if (!$data['field_type']) redirect(FUSION_SELF.$aidlink);
			$data['field_id'] = isset($_POST['field_id']) ? form_sanitizer($_POST['field_id'], '', 'field_id') : isset($_GET['field_id']) && isnum($_GET['field_id']) ? $_GET['field_id'] : 0;
			$data['field_title'] = isset($_POST['field_title']) ? form_sanitizer($_POST['field_title'], '', 'field_title') : '';
			$data['field_name'] = isset($_POST['field_name']) ? form_sanitizer($_POST['field_name'], '', 'field_name') : '';
			$data['field_name'] = strtolower(str_replace(' ', '_', $data['field_name'])); // make sure no space.
			$data['field_cat'] = isset($_POST['field_cat']) ? form_sanitizer($_POST['field_cat'], '', 'field_cat') : 0;
			$data['field_options'] = isset($_POST['field_options']) ? form_sanitizer($_POST['field_options'], '', 'field_options') : '';
			$data['field_default'] = isset($_POST['field_default']) ? form_sanitizer($_POST['field_default'], '', 'field_default') : '';
			$data['field_error'] = isset($_POST['field_error']) ? form_sanitizer($_POST['field_error'], '', 'field_error') : '';
			$data['field_required'] = isset($_POST['field_required']) ? 1 : 0;
			$data['field_log'] = isset($_POST['field_log']) ? 1 : 0;
			$data['field_registration'] = isset($_POST['field_registration']) ? 1 : 0;
			$data['field_order'] = isset($_POST['field_order']) ? form_sanitizer($_POST['field_order'], '0', 'field_order') : 0;
			if ($data['field_type'] == 'upload') {
				// these are to be serialized. init all.
				$max_b = isset($_POST['field_max_b']) ? form_sanitizer($_POST['field_max_b'], '', 'field_max_b') : 150000;
				$calc = isset($_POST['field_calc']) ? form_sanitizer($_POST['field_calc'], '', 'field_calc') : 1;
				$config['field_max_b'] = $max_b*$calc;
				$config['field_upload_type'] = isset($_POST['field_upload_type']) ? form_sanitizer($_POST['field_upload_type'], '', 'field_upload_type') : 'file';
				$config['field_upload_path'] = isset($_POST['field_upload_path']) ? form_sanitizer($_POST['field_upload_path'], '', 'field_upload_path') : '';
				$config_1['field_valid_file_ext'] = isset($_POST['field_valid_file_ext']) && $config['field_upload_type'] == 'file' ? form_sanitizer($_POST['field_valid_file_ext'], '', 'field_valid_file_ext') : '.zip,.rar,.tar,.bz2,.7z';
				$config_2['field_valid_image_ext'] = isset($_POST['field_valid_image_ext']) && $config['field_upload_type'] == 'image' ? form_sanitizer($_POST['field_valid_image_ext'], '', 'field_valid_image_ext') : '.jpg,.jpeg,.gif,.png';
				$config_2['field_image_max_w'] = isset($_POST['field_image_max_w']) && $config['field_upload_type'] == 'image' ? form_sanitizer($_POST['field_image_max_w'], '', 'field_image_max_w') : 1800;
				$config_2['field_image_max_h'] = isset($_POST['field_image_max_h']) && $config['field_upload_type'] == 'image' ? form_sanitizer($_POST['field_image_max_h'], '', 'field_image_max_h') : 1600;
				$config_2['field_thumbnail'] = isset($_POST['field_thumbnail']) ? 1 : 0;
				$config_2['field_thumb_upload_path'] = isset($_POST['field_thumb_upload_path']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail'] ? form_sanitizer($_POST['field_thumb_upload_path'], '', 'field_thumb_upload_path') : '';
				$config_2['field_thumb_w'] = isset($_POST['field_thumb_w']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail'] ? form_sanitizer($_POST['field_thumb_w'], '', 'field_thumb_w') : 100;
				$config_2['field_thumb_h'] = isset($_POST['field_thumb_h']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail'] ? form_sanitizer($_POST['field_thumb_h'], '', 'field_thumb_h') : 100;
				$config_2['field_thumbnail_2'] = isset($_POST['field_thumbnail_2']) ? 1 : 0;
				$config_2['field_thumb2_upload_path'] = isset($_POST['field_thumb2_upload_path']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail_2'] ? form_sanitizer($_POST['field_thumb2_upload_path'], '', 'field_thumb2_upload_path') : '';
				$config_2['field_thumb2_w'] = isset($_POST['field_thumb2_w']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail_2'] ? form_sanitizer($_POST['field_thumb2_w'], '', 'field_thumb2_w') : 400;
				$config_2['field_thumb2_h'] = isset($_POST['field_thumb2_h']) && $config['field_upload_type'] == 'image' && $config_2['field_thumbnail_2'] ? form_sanitizer($_POST['field_thumb2_h'], '', 'field_thumb2_h') : 300;
				$config_2['field_delete_original'] = isset($_POST['field_delete_original']) && $config['field_upload_type'] == 'image' ? 1 : 0;
			}
		}
		if (isset($_POST['save_field'])) {
			// Serialize the extra fields.. no bloating table.
			if ($data['field_type'] == 'upload') {
				if ($config['field_upload_type'] == 'file') {
					$config = array_merge($config, $config_1);
				} elseif ($config['field_upload_type'] == 'image') {
					// upload path must be required.
					$config = array_merge($config, $config_2);
				} else {
					$defender->stop();
					$defender->addNotice($locale['fields_0108']);
				}
				if (!defined('FUSION_NULL')) {
					$data['config'] = serialize($config);
				}
			}
			// ok now save into UF.
			$this->synthesize_fields($data, 'dynamics');
		}
		echo "<div class='m-t-20'>\n";
		echo openform('fieldform', 'fieldform', 'post', $form_action, array('downtime' => 0));
		foreach ($this->page_list as $index => $v) {
			$disable_opts[] = $index;
		}
		echo form_select_tree($locale['fields_0450'], 'field_cat', 'field_cat', $data['field_cat'], array('no_root' => 1,
			'disable_opts' => $disable_opts), $this->category_db, 'field_cat_name', 'field_cat_id', 'field_parent');
		echo form_text($locale['fields_0451'], 'field_title', 'field_title', $data['field_title'], array('placeholder' => $locale['fields_0452'],
			'required' => 1)); //
		echo form_text($locale['fields_0453'], 'field_name', 'field_name', $data['field_name'], array('placeholder' => $locale['fields_0454'],
			'required' => 1)); //
		if ($data['field_type'] == 'select') echo form_select($locale['fields_0455'], 'field_options', 'field_options', array(), $data['field_options'], array('required' => 1,
			'tags' => 1,
			'multiple' => 1));
		if ($data['field_type'] == 'upload') {
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
			echo form_select($locale['fields_0457'], 'field_upload_type', 'field_upload_type', $file_upload_type, $config['field_upload_type']);
			echo form_text($locale['fields_0458'], 'field_upload_path', 'field_upload_path', $config['field_upload_path'], array('placeholder' => $locale['fields_0459'],
				'required' => 1));
			echo "<label for='field_max_b'>".$locale['fields_0460']."</label>\n<br/>";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
			echo form_text('', 'field_max_b', 'field_max_b', $calc_b, array('class' => 'm-b-0',
				'number' => 1,
				'required' => 1));
			echo "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";
			echo form_select('', 'field_calc', 'field_calc', $calc_opts, $calc_c, array('width' => '100%'));
			echo "</div>\n</div>\n";
			// File Type
			echo "<div id='file_type'>\n";
			echo form_select($locale['fields_0461'], 'field_valid_file_ext', 'field_valid_file_ext', $file_type_list, $config_1['field_valid_file_ext'], array('multiple' => 1,
				'tags' => 1,
				'required' => 1));
			echo "</div>\n";
			// Image Type
			echo "<div id='image_type'>\n";
			echo form_select($locale['fields_0462'], 'field_valid_image_ext', 'field_valid_image_ext', $file_image_list, $config_2['field_valid_image_ext'], array('multiple' => 1,
				'tags' => 1,
				'required' => 1));
			echo "<label>".$locale['fields_0463']."</label>\n<br/>";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
			echo form_text($locale['fields_0464'], 'field_image_max_w', 'field_image_max_w', $config_2['field_image_max_w'], array('number' => 1,
				'placeholder' => $locale['fields_0466'],
				'required' => 1));
			echo "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";
			echo form_text($locale['fields_0465'], 'field_image_max_h', 'field_image_max_h', $config_2['field_image_max_h'], array('number' => 1,
				'placeholder' => $locale['fields_0466'],
				'required' => 1));
			echo "</div>\n</div>\n";
			echo form_checkbox($locale['fields_0467'], 'field_thumbnail', 'field_thumbnail', $config_2['field_thumbnail']);
			echo "<div id='field_t1'>\n";
			echo form_text($locale['fields_0468'], 'field_thumb_upload_path', 'field_thumb_upload_path', $config_2['field_thumb_upload_path'], array('placeholder' => $locale['fields_0469'],
				'required' => 1));
			echo "<label>".$locale['fields_0470']."</label>\n<br/>";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
			echo form_text($locale['fields_0471'], 'field_thumb_w', 'field_thumb_w', $config_2['field_thumb_w'], array('number' => 1,
				'placeholder' => $locale['fields_0466'],
				'required' => 1));
			echo "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";
			echo form_text($locale['fields_0472'], 'field_thumb_h', 'field_thumb_h', $config_2['field_thumb_h'], array('number' => 1,
				'placeholder' => $locale['fields_0466'],
				'required' => 1));
			echo "</div>\n</div>\n";
			echo "</div>\n";
			echo form_checkbox($locale['fields_0473'], 'field_thumbnail_2', 'field_thumbnail_2', $config_2['field_thumbnail_2']);
			echo "<div id='field_t2'>\n";
			echo form_text($locale['fields_0474'], 'field_thumb2_upload_path', 'field_thumb2_upload_path', $config_2['field_thumb2_upload_path'], array('placeholder' => $locale['fields_0469'],
				'required' => 1));
			echo "<label>".$locale['fields_0475']."</label>\n<br/>";
			echo "<div class='row'>\n";
			echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
			echo form_text($locale['fields_0476'], 'field_thumb2_w', 'field_thumb2_w', $config_2['field_thumb2_h'], array('number' => 1,
				'placeholder' => $locale['fields_0466'],
				'required' => 1));
			echo "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";
			echo form_text($locale['fields_0477'], 'field_thumb2_h', 'field_thumb2_h', $config_2['field_thumb2_h'], array('number' => 1,
				'placeholder' => $locale['fields_0466'],
				'required' => 1));
			echo "</div>\n</div>\n";
			echo "</div>\n";
			echo form_checkbox($locale['fields_0478'], 'field_delete_original', 'field_delete_original', $config_2['field_delete_original']);
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
			if ($data['field_type'] !== 'textarea') echo form_text($locale['fields_0480'], 'field_default', 'field_default', $data['field_default']);
			echo form_text($locale['fields_0481'], 'field_error', 'field_error', $data['field_error']);
		}
		echo form_checkbox($locale['fields_0482'], 'field_required', 'field_required', $data['field_required']);
		echo form_checkbox($locale['fields_0483'], 'field_log', 'field_log', $data['field_log']);
		echo form_text($locale['fields_0484'], 'field_order', 'field_order', $data['field_order'], array('number' => 1));
		echo form_checkbox($locale['fields_0485'], 'field_registration', 'field_registration', $data['field_registration']);
		echo form_hidden('', 'add_field', 'add_field', $data['field_type']);
		echo form_hidden('', 'field_id', 'field_id', $data['field_id']);
		echo form_button($locale['fields_0488'], 'save_field', 'save_field', 'save', array('class' => 'btn-sm btn-primary'));
		echo closeform();
		echo "</div>\n";
	}

	/* Add Modules Plugin Form */
	private function modules_form() {
		global $aidlink, $defender;
		// @todo:ordering
		$data = array();
		$form_action = FUSION_SELF.$aidlink;
		if (isset($_GET['action']) && $_GET['action'] == 'module_edit' && isset($_GET['module_id']) && isnum($_GET['module_id'])) {
			$form_action .= "&amp;action=".$_GET['action']."&amp;module_id=".$_GET['module_id'];
			$result = dbquery("SELECT * FROM ".$this->field_db." WHERE field_id='".$_GET['module_id']."'");
			if (dbrows($result) > 0) {
				$data += dbarray($result);
			} else {
				if (!$this->debug) redirect(FUSION_SELF.$aidlink);
			}
			if ($this->debug) print_p($data);
			$data['add_module'] = isset($_POST['add_module']) ? form_sanitizer($_POST['add_module']) : $data['field_name'];
			$data['field_type'] = 'file'; //
			$data['field_id'] = isset($_POST['field_id']) ? form_sanitizer($_POST['field_id'], '', 'field_id') : isset($_GET['module_id']) && isnum($_GET['module_id']) ? $_GET['module_id'] : 0;
			$data['field_title'] = isset($_POST['field_title']) ? form_sanitizer($_POST['field_title'], '', 'field_title') : $data['field_title'];
			$data['field_name'] = isset($_POST['field_name']) ? form_sanitizer($_POST['field_name'], '', 'field_name') : $data['field_name'];
			$data['field_name'] = str_replace(' ', '_', $data['field_name']); // make sure no space.
			$data['field_cat'] = isset($_POST['field_cat']) ? form_sanitizer($_POST['field_cat'], '', 'field_cat') : $data['field_cat']; //
			$data['field_default'] = isset($_POST['field_default']) ? form_sanitizer($_POST['field_default'], '', 'field_default') : $data['field_default']; //
			$data['field_error'] = isset($_POST['field_error']) ? form_sanitizer($_POST['field_error'], '', 'field_error') : $data['field_error'];
			$data['field_required'] = isset($_POST['field_required']) ? 1 : isset($_POST['field_id']) ? 0 : $data['field_required'];
			$data['field_log'] = isset($_POST['field_log']) ? 1 : isset($_POST['field_id']) ? 0 : $data['field_log'];
			$data['field_registration'] = isset($_POST['field_registration']) ? 1 : isset($_POST['field_id']) ? 0 : $data['field_registration'];
			$data['field_order'] = isset($_POST['field_order']) ? form_sanitizer($_POST['field_order'], '0', 'field_order') : $data['field_order'];
		} else {
			// new
			$data['add_module'] = isset($_POST['add_module']) ? form_sanitizer($_POST['add_module']) : $_POST['add_module'];
			if (!$data['add_module']) redirect(FUSION_SELF.$aidlink);
			$data['field_type'] = 'file'; //
			$data['field_id'] = isset($_POST['field_id']) ? form_sanitizer($_POST['field_id'], '', 'field_id') : isset($_GET['field_id']) && isnum($_GET['field_id']) ? $_GET['field_id'] : 0;
			$data['field_title'] = isset($_POST['field_title']) ? form_sanitizer($_POST['field_title'], '', 'field_title') : ''; //
			$data['field_name'] = isset($_POST['field_name']) ? form_sanitizer($_POST['field_name'], '', 'field_name') : ''; //
			$data['field_name'] = str_replace(' ', '_', $data['field_name']); // make sure no space.
			$data['field_cat'] = isset($_POST['field_cat']) ? form_sanitizer($_POST['field_cat'], '', 'field_cat') : 0; //
			$data['field_option'] = isset($_POST['field_option']) ? form_sanitizer($_POST['field_option'], '', 'field_option') : ''; //
			$data['field_default'] = isset($_POST['field_default']) ? form_sanitizer($_POST['field_default'], '', 'field_default') : ''; //
			$data['field_error'] = isset($_POST['field_error']) ? form_sanitizer($_POST['field_error'], '', 'field_error') : ''; //
			$data['field_required'] = isset($_POST['field_required']) ? 1 : 0; //
			$data['field_log'] = isset($_POST['field_log']) ? 1 : 0; //
			$data['field_registration'] = isset($_POST['field_registration']) ? 1 : 0; //
			$data['field_order'] = isset($_POST['field_order']) ? form_sanitizer($_POST['field_order'], '0', 'field_order') : 0; //
		}
		$locale = $this->locale;
		$user_field_name = '';
		$user_field_api_version = '';
		$user_field_desc = '';
		$user_field_dbname = '';
		$user_field_dbinfo = '';
		if (file_exists($this->plugin_locale_folder.stripinput($data['add_module']).".php") && file_exists($this->plugin_folder.stripinput($data['add_module'])."_include_var.php")) {
			include $this->plugin_locale_folder.stripinput($data['add_module']).".php";
			include $this->plugin_folder.stripinput($data['add_module'])."_include_var.php";
			$this->user_field_dbinfo = $user_field_dbinfo;
		} else {
			$defender->stop();
			$defender->addNotice($locale['fields_0109']);
		}
		// Script Execution
		if (isset($_POST['enable'])) {
			$this->synthesize_fields($data, 'module');
		}

		echo "<div class='m-t-20'>\n";
		echo openform('fieldform', 'fieldform', 'post', $form_action, array('downtime' => 0));
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
		foreach ($this->page_list as $index => $v) {
			$disable_opts[] = $index;
		}
		echo form_select_tree($locale['fields_0410'], 'field_cat', 'field_cat', $data['field_cat'], array('no_root' => 1,
			'disable_opts' => $disable_opts), $this->category_db, 'field_cat_name', 'field_cat_id', 'field_parent');
		if ($user_field_dbinfo != "") {
			if (version_compare($user_field_api_version, "1.01.00", ">=")) {
				echo form_checkbox($locale['fields_0411'], 'field_required', 'field_required', $data['field_required']);
			} else {
				echo "<p>\n".$locale['428']."</p>\n";
			}
		}
		if ($user_field_dbinfo != "") {
			if (version_compare($user_field_api_version, "1.01.00", ">=")) {
				echo form_checkbox($locale['fields_0412'], 'field_log', 'field_log', $data['field_log']);
			} else {
				echo "<p>\n".$locale['429a']."</p>\n";
			}
		}
		if ($user_field_dbinfo != "") {
			echo form_checkbox($locale['fields_0413'], 'field_registration', 'field_registration', $data['field_registration']);
		}
		echo form_text($locale['fields_0414'], 'field_order', 'field_order', $data['field_order']);
		echo form_hidden('', 'add_module', 'add_module', $data['add_module']);
		echo form_hidden('', 'field_name', 'field_name', $user_field_dbname);
		echo form_hidden('', 'field_title', 'field_title', $user_field_name);
		// new api introduced
		echo form_hidden('', 'field_default', 'field_default', isset($user_field_default) ? $user_field_default : '');
		echo form_hidden('', 'field_options', 'field_options', isset($user_field_options) ? $user_field_options : '');
		echo form_hidden('', 'field_error', 'field_error', isset($user_field_error) ? $user_field_error : '');
		echo form_hidden('', 'field_config', 'field_config', isset($user_field_config) ? $user_field_config : '');
		echo form_hidden('', 'field_id', 'field_id', $data['field_id']);
		echo form_button(($data['field_id'] ? $locale['fields_0415'] : $locale['fields_0416']), 'enable', 'enable', ($data['field_id'] ? $locale['fields_0415'] : $locale['fields_0416']), array('class' => 'btn-primary btn-sm'));
		echo closeform();
		echo "</div>\n";
	}

	/* Category & Page Form */
	private function category_form() {
		global $aidlink, $defender;
		$locale = $this->locale;
		$data = array();
		if (isset($_GET['action']) && $_GET['action'] == 'cat_edit' && isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
			$result = dbquery("SELECT * FROM ".$this->category_db." WHERE field_cat_id='".$_GET['cat_id']."'");
			if (dbrows($result) > 0) {
				$data += dbarray($result);
			} else {
				if (!$this->debug) redirect(FUSION_SELF.$aidlink);
			}
			// override by post.
			$data['field_cat_id'] = isset($_POST['field_cat_id']) ? form_sanitizer($_POST['field_cat_id'], '', 'field_cat_id') : $data['field_cat_id'];
			$data['field_cat_name'] = isset($_POST['field_cat_name']) ? form_sanitizer($_POST['field_cat_name'], '', 'field_cat_name') : $data['field_cat_name'];
			$data['field_parent'] = isset($_POST['field_parent']) ? form_sanitizer($_POST['field_parent'], '', 'field_parent') : $data['field_parent'];
			$data['field_cat_order'] = isset($_POST['field_cat_order']) ? form_sanitizer($_POST['field_cat_order'], '', 'field_cat_order') : $data['field_cat_order'];
			$data['field_cat_db'] = isset($_POST['field_cat_db']) ? form_sanitizer($_POST['field_cat_db'], '', 'field_cat_db') : $data['field_cat_db'];
			$data['field_cat_index'] = isset($_POST['field_cat_index']) ? form_sanitizer($_POST['field_cat_index'], '', 'field_cat_index') : $data['field_cat_index'];
			$data['field_cat_class'] = isset($_POST['field_cat_class']) ? form_sanitizer($_POST['field_cat_class'], '', 'field_cat_class') : $data['field_cat_class'];
		} else {
			$data['field_cat_id'] = isset($_POST['field_cat_id']) ? form_sanitizer($_POST['field_cat_id'], '', 'field_cat_id') : 0;
			$data['field_cat_name'] = isset($_POST['field_cat_name']) ? form_sanitizer($_POST['field_cat_name'], '', 'field_cat_name') : '';
			$data['field_parent'] = isset($_POST['field_parent']) ? form_sanitizer($_POST['field_parent'], '', 'field_parent') : '';
			$data['field_cat_order'] = isset($_POST['field_cat_order']) ? form_sanitizer($_POST['field_cat_order'], '', 'field_cat_order') : 0;
			$data['field_cat_db'] = isset($_POST['field_cat_db']) ? form_sanitizer($_POST['field_cat_db'], '', 'field_cat_db') : '';
			$data['field_cat_index'] = isset($_POST['field_cat_index']) ? form_sanitizer($_POST['field_cat_index'], '', 'field_cat_index') : '';
			$data['field_cat_class'] = isset($_POST['field_cat_class']) ? form_sanitizer($_POST['field_cat_class'], '', 'field_cat_class') : '';
		}
		if (isset($_POST['save_cat'])) {
			// safety
			if ($data['field_cat_order'] == 0) {
				$data['field_cat_order'] = dbresult(dbquery("SELECT MAX(field_cat_order) FROM ".$this->category_db." WHERE field_parent='".$data['field_parent']."'"), 0)+1;
			}
			if ($data['field_parent'] > 0) {
				$data['field_cat_db'] = '';
				$data['field_cat_index'] = '';
				$data['field_cat_class'] = '';
			}
			// shuffle between save and update
			$rows = dbcount("('field_cat_id')", $this->category_db, "field_cat_id='".$data['field_cat_id']."'");
			if ($rows > 0) {
				if ($this->debug) print_p('Update Mode');
				if ($this->debug) print_p($data);
				// ordering.
				$cat_data = dbarray(dbquery("SELECT * FROM ".$this->category_db." WHERE field_cat_id='".$data['field_cat_id']."'"));
				if ($data['field_cat_order'] > $cat_data['field_cat_order']) {
					if (!$this->debug && !defined('FUSION_NULL')) $result = dbquery("UPDATE ".$this->category_db." SET field_cat_order=field_cat_order-1 WHERE field_cat_order > ".$cat_data['field_cat_order']." AND field_cat_order <= '".$data['field_cat_order']."' AND field_cat='".$data['field_parent']."'");
				} elseif ($data['field_cat_order'] < $cat_data['field_cat_order']) {
					if (!$this->debug && !defined('FUSION_NULL')) $result = dbquery("UPDATE ".$this->category_db." SET field_cat_order=field_cat_order+1 WHERE field_cat_order < ".$cat_data['field_cat_order']." AND field_cat_order >= '".$data['field_cat_order']."' AND field_cat='".$data['field_parent']."'");
				}
				// build the page table on update and save
				if (!$this->debug && !defined('FUSION_NULL') && $data['field_cat_db'] && $data['field_cat_index'] && $data['field_cat_db'] !== 'users') { // if entered a field cat db and index and is not DB_USERS
					if (!db_exists(DB_PREFIX.$data['field_cat_db'])) { // check duplicates.
						// create principal table
						$result = dbquery("CREATE TABLE ".DB_PREFIX.$data['field_cat_db']." (
								".$data['field_cat_index']."_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT ,
								".$data['field_cat_index']." MEDIUMINT(8) NOT NULL DEFAULT '0',
								PRIMARY KEY (".$data['field_cat_index']."_id)
								) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
					} else {
						$defender->stop();
						$defender->addNotice($locale['fields_0110']);
					}
				}
				if (!$this->debug && !defined('FUSION_NULL')) dbquery_insert($this->category_db, $data, 'update');
				if (!$this->debug && !defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&amp;status=update_cat");
			} else {
				if ($this->debug) print_p('Save Mode');
				if ($this->debug) print_p($data);
				if (!$this->debug && !defined('FUSION_NULL') && $data['field_cat_db'] && $data['field_cat_index'] && $data['field_cat_db'] !== 'users') { // if entered a field cat db and index and is not DB_USERS
					if (!db_exists(DB_PREFIX.$data['field_cat_db'])) { // check duplicates.
						// create principal table
						$result = dbquery("CREATE TABLE ".DB_PREFIX.$data['field_cat_db']." (
								".$data['field_cat_index']."_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT ,
								".$data['field_cat_index']." MEDIUMINT(8) NOT NULL DEFAULT '0',
								PRIMARY KEY (".$data['field_cat_index']."_id)
								) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
					} else {
						$defender->stop();
						$defender->addNotice($locale['fields_0110']);
					}
				}
				if (!$this->debug && !defined('FUSION_NULL')) $result = dbquery("UPDATE ".$this->category_db." SET field_cat_order=field_cat_order+1 WHERE field_cat_order >= '".$data['field_cat_order']."' AND field_parent='".$data['field_parent']."'");
				if (!$this->debug && !defined('FUSION_NULL')) dbquery_insert($this->category_db, $data, 'save');
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
		echo openform('cat_form', 'cat_form', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));
		echo form_text($locale['fields_0430'], 'field_cat_name', 'field_cat_name', $data['field_cat_name'], array('required' => 1));
		echo form_select_tree($locale['fields_0431'], 'field_parent', 'field_parent', $data['field_parent'], array('parent_value' => $locale['fields_0432'],
			'disable_opts' => $cat_list), $this->category_db, 'field_cat_name', 'field_cat_id', 'field_parent');
		echo form_text($locale['fields_0433'], 'field_cat_order', 'field_cat_order', $data['field_cat_order'], array('number' => 1));
		echo form_hidden('', 'field_cat_id', 'field_cat_id', $data['field_cat_id'], array('number' => 1));
		echo form_hidden('', 'add_cat', 'add_cat', 'add_cat');
		// root settings
		echo "<div id='page_settings'>\n";
		echo "<div class='text-smaller m-b-10'>".$locale['fields_0111']."</div>\n";
		echo form_text(sprintf($locale['fields_0434'], DB_PREFIX), 'field_cat_db', 'field_cat_db', $data['field_cat_db'], array('placeholder' => 'users'));
		echo "<div class='text-smaller m-b-10'>".$locale['fields_0112']."</div>\n";
		echo form_text($locale['fields_0435'], 'field_cat_index', 'field_cat_index', $data['field_cat_index'], array('placeholder' => 'user_id'));
		echo "<div class='text-smaller m-b-10'>".$locale['fields_0113']."</div>\n";
		echo form_text($locale['fields_0436'], 'field_cat_class', 'field_cat_class', $data['field_cat_class'], array('placeholder' => 'entypo xxxxx'));
		echo "</div>\n";
		add_to_jquery("
		$('#field_parent').val() == '0' ? $('#page_settings').show() : $('#page_settings').hide()
		$('#field_parent').bind('change', function() {
		$(this).val() == '0' ? $('#page_settings').show() : $('#page_settings').hide()
		});
		");
		echo form_button($locale['fields_0318'], 'save_cat', 'save_cat', 'save_cat', array('class' => 'btn-sm btn-primary'));
		echo closeform();
	}

	/* Populates enabled and available Plugin Fields Var */
	private function available_fields() {
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
						$field_title = $field_name[0].'_'.$field_name[1];
						if (!in_array($field_title, $this->enabled_fields)) {
							// ok need to get locale.
							if (file_exists($this->plugin_locale_folder.$field_title.".php")) {
								include $this->plugin_locale_folder.$field_title.".php";
								include $this->plugin_folder.$field_title."_include_var.php";
								$this->available_field_info[$field_title] = array('title' => $user_field_name,
									'description' => $user_field_desc);
								$this->available_fields[$field_title] = $user_field_name;
							}
						}
						unset($field_name);
					}
				}
			}
			closedir($temp);
		}
	}

	/* Buttons */
	private function phpfusion_field_buttons() {
		global $aidlink;
		$locale = $this->locale;
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
		elseif (isset($_POST['add_field']) && in_array($_POST['add_field'], array_flip($this->dynamics_type()))) {
			$tab_title['title'][] = $locale['fields_0306'];
			$tab_title['id'][] = 'add';
			$tab_title['icon'][] = '';
			$tab_active = tab_active($tab_title, 2);
		}
		elseif (isset($_POST['add_module']) && in_array($_POST['add_module'], array_flip($this->available_fields))) {
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
		echo openform('addfield', 'addfield', 'post', FUSION_SELF.$aidlink, array('notice' => 0, 'downtime' => 0));
		echo form_button($locale['fields_0311'], 'add_cat', 'add_cat', 'add_cat', array('class' => 'm-t-20 m-b-20 btn-sm btn-primary btn-block',
			'icon' => 'entypo plus-circled'));
		if (!empty($this->cat_list)) {
			echo "<div class='row m-t-20'>\n";
			$field_type = $this->dynamics_type();
			unset($field_type['file']);
			foreach ($field_type as $type => $name) {
				echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-b-20'>".form_button($name, 'add_field', 'add_field-'.$name, $type, array('class' => 'btn-block btn-sm btn-default'))."</div>\n";
			}
			echo "</div>\n";
		}
		echo closeform();
		echo closetabbody();
		if (!empty($this->cat_list)) {
			echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active);
			// list down modules.
			echo openform('addfield', 'addfield', 'post', FUSION_SELF.$aidlink, array('notice' => 0, 'downtime' => 0));
			echo "<div class='m-t-20'>\n";
			foreach ($this->available_field_info as $title => $module_data) {
				echo "<div class='list-group-item'>";
				echo form_button($locale['fields_0312'], 'add_module', 'add_module-'.$title, $title, array('class' => 'btn-sm btn-default pull-right m-l-10'));
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
			$this->category_form();
			echo "</div>\n";
			echo closetabbody();
		} elseif (isset($_POST['add_field']) && in_array($_POST['add_field'], array_flip($this->dynamics_type())) or (isset($_GET['action']) && $_GET['action'] == 'field_edit' && isset($_GET['field_id']) && isnum($_GET['field_id']))) {
			echo opentabbody($tab_title['title'][2], $tab_title['id'][2], $tab_active);
			$this->dynamics_form();
			echo closetabbody();
		} elseif (isset($_POST['add_module']) && in_array($_POST['add_module'], array_flip($this->available_fields)) or (isset($_GET['action']) && $_GET['action'] == 'module_edit' && isset($_GET['module_id']) && isnum($_GET['module_id']))) {
			echo opentabbody($tab_title['title'][2], $tab_title['id'][2], $tab_active);
			$this->modules_form();
			echo closetabbody();
		}
		echo closetab();
	}

	/* Stable components only */
	private function phpfusion_field_DOM($data) {
		// deactivate all.
		//print_p($data);
		global $settings, $locale;
		$profile_method = 'input';
		$user_data = array();
		$options['deactivate'] = 0;
		$options['inline'] = 1;
		if ($data['field_error']) $options['error_text'] = $data['field_error'];
		if ($data['field_required']) $options['required'] = $data['field_required'];
		if ($data['field_default']) $options['placeholder'] = $data['field_default'];
		if ($data['field_options']) $option_list = explode(',', $data['field_options']);
		if ($data['field_type'] == 'file') {
			if (file_exists($this->plugin_locale_folder.$data['field_name'].".php")) include $this->plugin_locale_folder.$data['field_name'].".php";
			if (file_exists($this->plugin_folder.$data['field_name']."_include.php")) include $this->plugin_folder.$data['field_name']."_include.php";
			if (isset($user_fields)) return $user_fields;
		} elseif ($data['field_type'] == 'textbox') {
			return form_text($data['field_title'], $data['field_name'], $data['field_name'], '', $options);
		} elseif ($data['field_type'] == 'select') {
			return form_select($data['field_title'], $data['field_name'], $data['field_name'], $option_list, '', $options);
		} elseif ($data['field_type'] == 'textarea') {
			return form_textarea($data['field_title'], $data['field_name'], $data['field_name'], '', $options);
		} elseif ($data['field_type'] == 'checkbox') {
			return form_checkbox($data['field_title'], $data['field_name'], $data['field_name'], '', $options);
		} elseif ($data['field_type'] == 'datepicker') {
			return form_datepicker($data['field_title'], $data['field_name'], $data['field_name'], '', $options);
		} elseif ($data['field_type'] == 'colorpicker') {
			return form_colorpicker($data['field_title'], $data['field_name'], $data['field_name'], '', $options);
		} elseif ($data['field_type'] == 'uploader') {
			return form_fileinput($data['field_title'], $data['field_name'], $data['field_name'], '', $options);
		} elseif ($data['field_type'] == 'hidden') {
			return form_hidden($data['field_title'], $data['field_name'], $data['field_name'], '', $options);
		} elseif ($data['field_type'] == 'address') {
			return form_address($data['field_title'], $data['field_name'], $data['field_name'], '', $options);
		} elseif ($data['field_type'] == 'toggle') {
			return form_toggle($data['field_title'], $data['field_name'], $data['field_name'], array($locale['off'],
				$locale['on']), $data['field_name'], $options);
		}
	}
}

?>