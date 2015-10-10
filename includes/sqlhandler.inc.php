<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System Version 9.00
| Copyright (C) 2002 - 2013 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Advanced SQL Handling Methods Functions API
| Filename: sqlhandler.inc.php
| Author: PHP-Fusion 8 Development Team
| Coded by : Frederick MC Chan (Hien)
| Version : 9.1.1 (please update every commit)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

// Hierarchy Type 1 - key to index method

/**
 * Hierarchy Id to Category Output
 * Returns cat-id relationships
 * @param      $db
 * @param      $id_col
 * @param      $cat_col
 * @param bool $filter
 * @return array
 */
function dbquery_tree($db, $id_col, $cat_col, $filter = FALSE) {
	$index = array();
	$query = dbquery("SELECT $id_col, $cat_col FROM ".$db." $filter");
	while ($row = dbarray($query)) {
		$id = $row[$id_col];
		$parent_id = $row[$cat_col] === NULL ? "NULL" : $row[$cat_col];
		$index[$parent_id][] = $id;
	}
	return (array) $index;
}

/**
 * Hierarchy Full Data Output
 * Returns cat-id relationships with full data
 * @param      $db
 * @param      $id_col
 * @param      $cat_col
 * @param bool $sql_cond
 * @return array
 */
function dbquery_tree_full($db, $id_col, $cat_col, $filter = FALSE) {
	$data = array();
	$index = array();
	$query = dbquery("SELECT * FROM ".$db." ".$filter."");
	while ($row = dbarray($query)) {
		$id = $row[$id_col];
		$parent_id = $row[$cat_col] === NULL ? "0" : $row[$cat_col];
		$data[$id] = $row;
		$index[$parent_id][$id] = $row;
	}
	return (array) $index;
}

/**
 * Get index information from dbquery_tree_full.
 * @param $data  - array generated from dbquery_tree_full();
 * @return array
 */
function tree_index($data) {
	$list = array();
	if (!empty($data)) {
		foreach($data as $arr=>$value) {
			$list[$arr] = array_keys($value);
		}
	}
	return $list;
}

/**
 * Get Tree Root ID of a Child from dbquery_tree() result
 * @param array $index
 * @param       $child_id
 * @return int
 */
function get_root(array $index, $child_id) {
	foreach ($index as $key => $array) {
		if (in_array($child_id, $array)) {
			if ($key == 0) {
				return $child_id;
			} else {
				return (int) get_root($index, $key);
			}
		}
	}
}

/**
 * Get Tree Root ID of a child via SQL
 * Alternative function to get a root of a specific item when dbtree is not available
 * @param $db
 * @param $id_col
 * @param $cat_col
 * @param $parent_id
 * @return int
 */
function get_hkey($db, $id_col, $cat_col, $parent_id) {
	$hkey = & $hkey;
	$result = dbquery("SELECT $id_col, $cat_col FROM ".$db." WHERE $id_col = '$parent_id' LIMIT 1");
	if (dbrows($result) > 0) {
		$data = dbarray($result);
		if ($data[$cat_col] > 0) {
			$hkey = get_hkey($db, $id_col, $cat_col, $data[$cat_col]);
		} else {
			$hkey = $data[$id_col];
		}
	} else {
		// predict current row.
		$rows = dbarray(dbquery("SELECT MAX($id_col) as row FROM ".$db.""));
		$rows = $rows['row'];
		$hkey = $rows+1;
	}
	return (int) $hkey;
}

/**
 * Get immediate Parent ID from dbquery_tree() result
 * @param array $index
 * @param       $child_id
 * @return int
 */
function get_parent(array $index, $child_id) {
	foreach ($index as $key => $value) {
		if (in_array($child_id, $value)) {
			return (int) $key;
		}
	}
}

/**
 * Get all parent ID from dbquery_tree()
 * @param array $index
 * @param       $child_id
 * @param array $list
 * @return array
 */
function get_all_parent(array $index, $child_id, array &$list = array()) {
	foreach ($index as $key => $value) {
		if (in_array($child_id, $value)) {
			if ($key == 0) {
				return $list;
			} else {
				$list[] = $key;
				return (array) get_all_parent($index, $key, $list);
			}
		}
	}
}

/**
 * Get Child IDs from dbquery_tree() result
 * @param       $index
 * @param       $parent_id
 * @param array $children
 * @return array
 */
function get_child($index, $parent_id, array &$children = array()) {
	$parent_id = $parent_id === NULL ? null : $parent_id;
	if (isset($index[$parent_id])) {
		foreach ($index[$parent_id] as $id) {
			$children[] = $id;
			get_child($index, $id, $children);
		}
	}
	return $children;
}

/**
 * Get current depth from dbquery_tree() result
 * @param      $index
 * @param      $child_id
 * @param bool $depth
 * @return bool|int
 */
function get_depth($index, $child_id, $depth = FALSE) {
	if (!$depth) {
		$depth = 1;
	}
	foreach ($index as $key => $value) {
		if (in_array($child_id, $value)) {
			if ($key == 0) {
				return (int) $depth;
			} else {
				return (int) get_depth($index, $key, $depth+1);
			}
		}
	}
}

/**
 * Get maximum depth of a hierarchy tree
 * @param $array
 * @return int
 */
function array_depth($array) {
	$max_depth = 1;
	foreach ($array as $value) {
		if (is_array($value)) {
			$depth = array_depth($value)+1;
			if ($depth > $max_depth) {
				$max_depth = $depth;
			}
		}
	}
	return (int) $max_depth;
}

// Hierarchy Type 2 - child key method

/**
 * Get Hierarchy Array with injected child key
 * This is a slower model to fetch hierarchy data than dbquery_tree_full
 * @param      $db
 * @param      $id_col
 * @param      $cat_col
 * @param bool $cat_value
 * @param bool $filter
 * @return array
 */
function dbtree($db, $id_col, $cat_col, $cat_value=FALSE, $filter = FALSE) {
	$refs = array();
	$list = array();
	$col_names = fieldgenerator($db);
	$result = dbquery("SELECT * FROM ".$db." ".$filter." ");
	while ($data = dbarray($result)) {
		foreach ($col_names as $arr => $v) {
			if ($v == $id_col) {
				$thisref = &$refs[$data[$id_col]];
			}
			$thisref[$v] = $data[$v];
		}
		if ($data[$id_col] == $cat_value) { // cat_val = 0 = impossible
			$list[$data[$id_col]] = &$thisref; // pushing mechanism.
		} elseif ($data[$cat_col] == $cat_value) { // 0;
			// if current $data[article_cat_cat] == "3"
			// $list[1] <-- inject in array. // list the current children.
			$refs[$data[$cat_col]]['children'][$data[$id_col]] = &$thisref;
		} else {
			$refs[$data[$cat_col]]['children'][$data[$id_col]] = &$thisref;
		}
	}
	return (array)$list;
}

/**
 * Lighter version of dbtree() with only id and child key
 * @param bool $db
 * @param      $id_col
 * @param      $cat_col
 * @param bool $cat_value
 * @return array
 */
function dbtree_index($db = FALSE, $id_col, $cat_col, $cat_value = FALSE) {
	$refs = array();
	$list = array();
	$result = dbquery("SELECT * FROM ".$db."");
	$col_names = fieldgenerator($db);
	$i = 1;
	while ($data = dbarray($result)) {
		foreach ($col_names as $arr => $v) {
			if ($v == $id_col) {
				$thisref = & $refs[$data[$id_col]];
			}
			$thisref[$v] = $data[$v];
		}
		if ($data[$cat_col] == $cat_value) {
			$list[$data[$id_col]] = & $thisref;
		} else {
			$refs[$data[$cat_col]]['child'][$data[$id_col]] = & $thisref;
		}
		$i++;
	}
	return (array)$list;
}

/**
 * To sort key on tree_index results
 * @param $result
 * @param $key
 * @return array
 */
function sort_tree(&$result, $key) {
	$master_sort = sorter($result, $key);
	foreach ($master_sort as $data) {
		$id = $data[$key];
		// remove child
		$newdata = $data;
		unset($data['children']);
		$current_array[$id] = $data; // fielded parents
		if (array_key_exists("children", $newdata)) { // or isset($newdata['children'], whichever.
			$result = $newdata['children'];
			$current_array[$id]['children'] = sort_tree($result, $key);
		}
	}
	return (array) $current_array;
}

/**
 * Internal to sort_tree - Very useful
 * @param $array
 * @param $key
 * @return array
 */
function sorter(&$array, $key) {
	$sorter = array();
	$ret = array();
	reset($array);
	foreach ($array as $ii => $va) {
		$sorter[$ii] = $va[$key];
	}
	asort($sorter);
	foreach ($sorter as $ii => $va) {
		$ret[$ii] = $array[$ii];
	}
	$array = $ret;
	return (array)$array;
}

/**
 * Get the total max depths of dbtree()
 * @param        $data
 * @param        $field
 * @param        $match
 * @param string $depth
 * @return int
 */
function tree_depth($data, $field, $match, $depth = '1') {
	if (!$depth) {
		$depth = '1';
	} else {
		$depth = & $depth;
	}
	foreach ($data as $arr) {
		if ($arr[$field] == $match) {
			return (int)$depth;
		} else {
			if (array_key_exists('children', $arr)) {
				$deep = tree_depth($arr['children'], $field, $match, $depth+1);
				if ($deep) {
					return (int) $deep;
				}
			}
		}
	}
}

/**
 * Get the occurences of a column name matching value
 * $unpublish_count = tree_count($dbtree_result, "wiki_cat_status", "0")-1;
 * @param      $data - $data = dbquery_tree(...);
 * @param bool $field
 * @param bool $match
 * @return int
 */
function tree_count($data, $column_name = FALSE, $value_to_match = FALSE) {
	// Find Occurence of match in a tree.
	//
	if (!isset($counter)) {
		$counter = 0;
	} else {
		$counter = &$counter;
	}
	foreach ($data as $arr) {
		if (!empty($column_name)) {
			if ($arr[$column_name] == $value_to_match) {
				$counter++;
			}
		} else {
			$counter++;
		}
		if (array_key_exists("children", $arr)) {
			$counter = tree_count($arr['children'], $column_name, $value_to_match)+$counter;
		}
	}
	return (int)$counter;
}

/**
 * This model will create a up to "HUNDREDS" of self joins to get a relational hierarchy
 * @param      $db
 * @param      $id_col
 * @param      $cat_col
 * @param bool $filter
 * @param bool $filter_order
 * @param bool $filter_show
 * @param bool $depth
 * @return bool|mixed|PDOStatement|resource
 */
function tree_join_method_sql_deprecated($db, $id_col, $cat_col, $filter = FALSE, $filter_order = FALSE, $filter_show = FALSE, $depth = FALSE) {
	$selector = '';
	$column = '';
	$conditions = '';
	if (!$depth) {
		$depth = 10;
	}
	for ($i = 0; $depth >= $i; $i++) {
		$prev = $i-1;
		$selector .= ($i == $depth) ? "t$i.$id_col as level$i" : "t$i.$id_col as level$i, ";
		$column .= ($i == 0) ? "FROM $db AS t$i " : "LEFT JOIN $db AS t$i ON t$i.$cat_col = t$prev.$id_col ";
		if ($i == 0) {
			$conditions .= "WHERE t$i.$cat_col='0' OR";
		} else {
			$conditions .= ($i == $depth) ? " t$i.$cat_col = t$prev.$id_col " : " t$i.$cat_col= t$prev.$id_col OR ";
		}
	}
	$result = dbquery("SELECT $selector $column $conditions ORDER BY t1.$cat_col ASC, t1.$id_col ASC $filter_show", 1);
	return $result;
}


// Might move to Dynamics -- Used by form_select_tree(); only //
/* Hierarchy Data - returns full data array -- used by select2 tree dropdown */
function dbquery_tree_data($db, $id_col, $cat_col, $filter = FALSE, $filter_order = FALSE, $filter_show = FALSE) {
	$data = array();
	$index = array();
	$filter_order = ($filter_order) ? "ORDER BY $filter_order" : '';
	$query = dbquery("SELECT * FROM ".$db." $filter $filter_order $filter_show"); // mysql_query("SELECT id, parent_id, name FROM categories ORDER BY name");
	while ($row = dbarray($query)) {
		$id = $row[$id_col];
		$data[$id] = $row;
	}
	return $data;
}

// need dbquery_tree_data to function
function display_parent_nodes($data, $id_col, $cat_col, $id) {
	/*
	* Display parent nodes
	*/
	$current = $data[$id];
	$parent_id = $current[$cat_col] === NULL ? "NULL" : $current[$cat_col];
	$parents = array();
	while (isset($data[$parent_id])) {
		$current = $data[$parent_id];
		$parent_id = $current[$cat_col] === NULL ? "NULL" : $current[$cat_col];
		$parents[] = $current[$id_col];
	}
	//    echo implode(" > ", array_reverse($parents));
	return $parents;
}

/**
 * MYSQL Show Columns Shorthand
 * Returns available columns in a table
 * @param $db
 * @return array
 */
function fieldgenerator($db) {
	$cresult = dbquery("SHOW COLUMNS FROM $db");
	$col_names = array();
	while ($cdata = dbarray($cresult)) {
		$col_names[] = $cdata['Field'];
	}
	return (array) $col_names;
}

/**
 * MYSQL Row modifiers. Insert/Update/Delete
 *
 * @param string $table
 * @param array $inputdata
 * @param string $mode save|update|delete
 * @param array $options
 * 	<ul>
 * 		<li><strong>debug (boolean)</strong>:
 * 			If TRUE, do nothing, just show the SQL. FALSE by default</li>
 * 		<li><strong>primary_key (string|string[])</strong>:
 * 			Names of primary key columns. If it is empty,
 * 			columns will detected automatically.</li>
 * 		<li><strong>no_unique (boolean)</strong>:
 * 			If TRUE, primary key columns will be not removed
 * 			from $inputdata. FALSE by default.</li>
 * 		<li><strong>keep_session (boolean)</strong>:
 * 			If TRUE, defender will not unset field sessions.</li>
 * 	</ul>
 * @return int|FALSE
 * 	If an error happens, it returns FALSE.
 * 	Otherwise, if $mode is save and the primary key column is
 * 	incremented automatically, this function returns the last inserted id.
 * 	In other cases it always returns 0.
 */
function dbquery_insert($table, $inputdata, $mode, array $options = array()) {
	$options += array(
		'debug' => FALSE,
		'primary_key' => '',
		'no_unique' => FALSE,
		'keep_session' => FALSE
	);

	if (defined("FUSION_NULL")) {
		if ($options['debug']) {
			print_p('Fusion Null Declared. Developer, check form tokens.');
		}
		return FALSE;
	}

	global $defender;

	$cresult = dbquery("SHOW COLUMNS FROM $table");
	$columns = array();
	$pkcolumns = array();
	while ($cdata = dbarray($cresult)) {
		$columns[] = $cdata['Field'];
		if ($cdata['Key'] === 'PRI') {
			$pkcolumns[$cdata['Field']] = $cdata['Field'];
		}
	}
	if ($options['primary_key']) {
		$options['primary_key'] = (array) $options['primary_key'];
		$pkcolumns = array_combine($options['primary_key'], $options['primary_key']);
	}
	$sanitized_input = array();
	$data = array_intersect_key($inputdata, array_flip($columns));
	$pkvalues = array_intersect_key($data, $pkcolumns);
	if (!$options['no_unique'] and $mode !== 'save') {
		foreach ($pkcolumns as $c) {
			unset($data[$c]);
		}
	}

	if (!$data) {
		if ($options['debug']) {
			print_p('$inputdata does not contain any valid column.');
		}
		return FALSE;
	}

	$sqlPatterns = array(
		'save'   => 'INSERT INTO `{table}` SET {values}',
		'update' => 'UPDATE `{table}` SET {values} WHERE {where}',
		'delete' => 'DELETE FROM `{table}` WHERE {where}'
	);

	foreach ($data as $name => $value) {
		$sanitized_input[] = "`$name` = '$value'";
	}

	if (!isset($sqlPatterns[$mode])) {
		// TODO Replace die with something better. I kept the old way (Rimelek)
		die();
	}
	$where = '';

	if ($mode === 'update' or $mode === 'delete') {
		$pkwhere = array();
		foreach ($pkvalues as $name => $pkvalue) {
			$pkwhere[] = "`$name`='$pkvalue'";
		}
		$where = implode(' AND ', $pkwhere);
	}
	$sql = strtr($sqlPatterns[$mode], array(
		'{table}' => $table,
		'{values}' => implode(', ', $sanitized_input),
		'{where}' => $where
	));
	$result = NULL;
	if ($options['debug']) {
		print_p($where);
		print_p($sanitized_input);
		print_p($sql);
	} else {
		$result = dbquery($sql);
		if (!$options['keep_session']) {
			//print_p('field session unset during '.$sql);
			$defender->unset_field_session();
		}
	}
	if ($result === FALSE) {
		// Because dblastid() can return the id of the last record of the error log.
		return FALSE;
	}
	return ($mode === 'save') ? dblastid() : 0;
}


/**
 * SQL statement helper to find values in between dots
 * @param $column_name
 * @param $value
 * @return string
 * Example: language column contains '.BL.NS.NC.NG'
 * 			SELECT * FROM ".DB." WHERE ".in_group(language, 'BL')."
 */
function in_group($column_name, $value, $delim = '.') {
	return "CONCAT($column_name, '$delim') like '%$value.%' ";
}

// for sitelinks - not hierarchy
function getcategory($cat) {
	$presult = dbquery("SELECT link_id, link_name, link_order FROM ".DB_SITE_LINKS." WHERE link_id='$cat'");
	if (dbrows($presult) > 0) {
		$pdata = dbarray($presult);
		$link_id = $pdata['link_id'];
		$link_order = $pdata['link_order'];
		$link_name = $pdata['link_name'];
		$md[$cat] = "Menu Item Root";
		$result = dbquery("SELECT link_id, link_name FROM ".DB_SITE_LINKS." WHERE link_cat='$cat' ORDER BY link_order ASC");
		if (dbrows($result) > 0) {
			while ($data = dbarray($result)) {
				$link_id = $data['link_id'];
				$link_name = $data['link_name'];
				$md[$link_id] = "- ".$link_name."";
			}
		}
	}
	return $md;
}

/**
 * Check if a PHPFusion table exists
 *
 * However you can pass the table name with or without prefix,
 * this function only check the prefixed tables of the PHPFusion
 * 
 * @todo We should find a better name. fusion_table_exists or fusion_dbtable_exists...
 * 
 * @staticvar boolean[] $tables
 * @param string $table The name of the table with or without prefix
 * @param boolean $updateCache The state of a table is cached.
 * 	Pass TRUE if you want to update the cached state of the table.
 * @return boolean
 */
// This is the new one
function db_exists($table, $updateCache = FALSE) {
	static $tables = NULL;

	$length = strlen(DB_PREFIX);
	if (substr($table, 0, $length) === DB_PREFIX) {
		$table = substr($table, $length);
	}
	$sql = "SELECT substring(table_name, ".($length+1).") "
		   ." FROM information_schema.tables WHERE table_schema = database() "
		   . " AND table_name LIKE :table_pattern";
	if ($tables === NULL) {
		$result = dbquery($sql, array(':table_pattern' => str_replace('_', '\_', DB_PREFIX).'%'));
		while ($row = dbarraynum($result)) {
			$tables[$row[0]] = TRUE;
		}
	} elseif ($updateCache) {
		$tables[$table] = dbresult(dbquery('SELECT exists('.$sql.')', array(':table_pattern' => DB_PREFIX.$table)), 0);
	}
	return !empty($tables[$table]);
}

/**
 * ID is required only for update mode.
 * @param        $dbname
 * @param int    $current_order
 * @param        $order_col
 * @param int    $current_id
 * @param bool   $id_col
 * @param int    $current_category
 * @param bool   $cat_col
 * @param string $multilang_prefix
 * @param string $multilang_col
 * @param string $mode
 * @return bool|mixed|PDOStatement|resource
 */
function dbquery_order($dbname, $current_order, $order_col, $current_id = 0, $id_col = FALSE, $current_category = 0, $cat_col = FALSE, $multilang = false, $multilang_col = '', $mode = 'update') {

	$multilang_sql_1 = $multilang && $multilang_col ? "WHERE $multilang_col='".LANGUAGE."'" : '';
	$multilang_sql_2 = $multilang && $multilang_col ? "AND $multilang_col='".LANGUAGE."'" : '';

	if (!$current_order) $current_order = dbresult(dbquery("SELECT MAX($order_col) FROM ".$dbname." ".$multilang_sql_1), 0)+1;

	switch($mode) {
		case 'save':
			if ($order_col && $current_order && $dbname) {
				if (!empty($current_category) && (!empty($cat_col))) {
					$result = dbquery("UPDATE ".$dbname." SET $order_col=$order_col+1 WHERE $cat_col='".intval($current_category)."' AND $order_col>='".intval($current_order)."' $multilang_sql_2");
					return $result;
				} else {
					$result = dbquery("UPDATE ".$dbname." SET $order_col=$order_col+1 WHERE $order_col>='".intval($current_order)."' $multilang_sql_2");
					return $result;
				}
			} else {
				defender::stop();
			}
			break;
		case 'update':
			if ($id_col && $current_id && $order_col && $current_order && $dbname) {
				$old_order = dbresult(dbquery("SELECT $order_col FROM ".$dbname." WHERE $id_col='".intval($current_id)."' $multilang_sql_2"), 0);
				if (!empty($current_category) && (!empty($cat_col))) {
					if ($current_order > $old_order) {
						$result = dbquery("UPDATE ".$dbname." SET $order_col=$order_col-1 WHERE $cat_col='".intval($current_category)."' AND $order_col>'$old_order' AND $order_col<='".intval($current_order)."' $multilang_sql_2");
						return $result;
					} elseif ($current_order < $old_order) {
						$result = dbquery("UPDATE ".$dbname." SET $order_col=$order_col+1 WHERE $cat_col='".intval($current_category)."' AND $order_col<'$old_order' AND $order_col>='".intval($current_order)."' $multilang_sql_2");
						return $result;
					} else {
						return true;
					}
				} else {
					if ($current_order > $old_order) {
						$result = dbquery("UPDATE ".$dbname." SET $order_col=$order_col-1 WHERE $order_col>'$old_order' AND $order_col<='".intval($current_order)."' $multilang_sql_2");
						return $result;
					} elseif ($current_order < $old_order) {
						$result = dbquery("UPDATE ".$dbname." SET $order_col=$order_col+1 WHERE $order_col<'$old_order' AND $order_col>='".intval($current_order)."' $multilang_sql_2");
						return $result;
					} else {
						return true;
					}
				}
			} else {
				defender::stop();
			}
			break;
		case 'delete':
			if ($order_col && $current_order && $dbname) {
				if (!empty($current_category) && (!empty($cat_col))) {
					// in nested mode, $cat and $cat_col is REQUIRED.
					$result = dbquery("UPDATE ".$dbname." SET $order_col=$order_col-1 WHERE $cat_col='".intval($current_category)."' AND $order_col>'".intval($current_order)."' $multilang_sql_2");
					return $result;
				} else {
					$result = dbquery("UPDATE ".$dbname." SET $order_col=$order_col-1 WHERE $order_col>'".intval($current_order)."' $multilang_sql_2");
					return $result;
				}
			} else {
				defender::stop();
			}
			break;
		default:
			defender::stop();
	}
}

// Array Makers
/**
 * To flatten ANY multidimensional array
 * Best used to flatten any hierarchy array data
 * @param $result
 * @return mixed
 */
function flatten_array($result) { return call_user_func_array('array_merge', $result); }

/**
 * Short hand to explode strings to array by using commas
 * $array = construct_array("a,b,c,d,e,f,g");
 * $str_to_array = construct_array($str_with_commas); // See Geomap.inc.php
 *
 * @param      $string
 * @param bool $string2
 * @param bool $delimiter (symbol of delimiter)
 * @return array
 */
function construct_array($string, $string2 = FALSE, $delimiter = FALSE) {
	// in event string is array. skips this.
	if (!is_array($string)) {
		if ($delimiter && (!empty($delimiter))) {
			$delimiter = $delimiter;
		} else {
			$delimiter = ",";
		}
		$value = explode("$delimiter", $string);
		if ($string2 != "") {
			$value2 = explode("$delimiter", $string2);
		} else {
			$value2 = "";
		}
		if (is_array($value2)) {
			$value = array_combine($value2, $value);
		}
		return $value;
	} else {
		notify("Debug notice: There is a string injected in construct_array() function!", "Please recheck source codes in this page.");
	}
}

/**
 * To implode an array to string
 * Opposite of construct_array()
 * @param $string
 * @param $delimiter
 * @return string
 */
function deconstruct_array($string, $delimiter) {
	$value = implode("$delimiter", $string);
	return $value;
}

/* Single column search */
/* used to make searches on field */
// echo search_field(array('admin_title','admin_link'), 'ac c d ghi');
function search_field($columns, $text) {
	$condition = '';
	$text = explode(" ", $text);
	foreach ($text as $search_text) {
		if (strlen($search_text) >= 3) {
			$the_sql[] = stripinput($search_text);
		}
	}
	foreach ($the_sql as $counter => $search_text) {
		if (strlen($search_text) >= 3) {
			if (is_array($columns)) {
				$condition .= "(";
				foreach ($columns as $arr => $col_field) {
					$condition .= ($arr == count($columns)-1) ? "$col_field LIKE '%$search_text%'" : "$col_field LIKE '%$search_text%' OR ";
				}
				$condition .= ")";
			} else {
				$condition .= "($col_field LIKE '%$search_text%')";
			}
		}
		$condition .= ($counter == count($the_sql)-1) ? "  " : " OR ";
	}
	return $condition;
}

