<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: sqlhandler.inc.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\DBCache;

class SqlHandler {
    /**
     * Add column to a specific table.
     *
     * @param string $table_name
     * @param string $new_column_name
     * @param string $field_attributes
     */
    public static function add_column($table_name, $new_column_name, $field_attributes) {
        if (!empty($field_attributes)) {
            $result = dbquery("ALTER TABLE ".$table_name." ADD ".$new_column_name." ".$field_attributes); // create the new one.
            if (!$result) {
                fusion_stop("Unable to add column ".$new_column_name." with attributes - ".$field_attributes);
            }
        }
    }

    /**
     * Drop column of a table.
     *
     * @param string $table_name
     * @param string $old_column_name
     */
    public static function drop_column($table_name, $old_column_name) {
        $result = dbquery("ALTER TABLE ".$table_name." DROP ".$old_column_name);
        if (!$result) {
            fusion_stop("Unable to drop column ".$old_column_name);
        }
    }

    /**
     * Build a new table.
     *
     * @param string $new_table
     * @param string $primary_column
     *
     * @return mixed
     */
    public static function build_table($new_table, $primary_column) {
        $new_table = !stristr($new_table, DB_PREFIX) ? DB_PREFIX.$new_table : $new_table;
        $result = NULL;
        if (!db_exists($new_table)) {
            $result = dbquery("CREATE TABLE ".$new_table." (
                ".$primary_column."_key MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
                ".$primary_column." MEDIUMINT(8) NOT NULL DEFAULT '0',
                PRIMARY KEY (".$primary_column."_key),
                KEY ".$primary_column." (".$primary_column.")
            ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
        }

        return $result;
    }

    /**
     * Move old table to new table.
     *
     * @param string $old_table
     * @param string $new_table
     */
    public static function transfer_table($old_table, $new_table) {
        $old_table = !stristr($old_table, DB_PREFIX) ? DB_PREFIX.$old_table : $old_table;
        $new_table = !stristr($old_table, DB_PREFIX) ? DB_PREFIX.$new_table : $new_table;
        $result = dbquery("SHOW COLUMNS FROM ".$old_table);
        if (dbrows($result) > 0) {
            $i = 1;
            while ($data = dbarray($result)) {
                if ($data['Key'] !== "PRI" && $i > 2) {
                    $result = dbquery("ALTER TABLE ".$new_table." ADD COLUMN ".$data['Field']." ".$data['Type']." ".($data['Null'] == "NO" ? "NOT NULL" : "NULL")." DEFAULT '".$data['Default']."'");
                    if (!$result && fusion_safe()) {
                        dbquery("INSERT INTO ".$new_table." (".$data['Field'].") SELECT ".$data['Field']." FROM ".$old_table);
                    }
                }
                $i++;
            }
            if (!fusion_safe()) {
                addnotice("danger", "Unable to move all columns from ".$old_table." to " > $new_table);
            }
        }
    }

    /**
     * Drop table.
     *
     * @param string $old_table
     */
    public static function drop_table($old_table) {
        $old_table = !stristr($old_table, DB_PREFIX) ? DB_PREFIX.$old_table : $old_table;
        $result = dbquery("DROP TABLE IF EXISTS ".$old_table);
        if (!$result) {
            fusion_stop();
        }
        if (!fusion_safe()) {
            addnotice("danger", "Unable to drop ".$old_table);
        }

    }

    /**
     * Rename column name.
     *
     * @param string $table_name
     * @param string $old_column_name
     * @param string $new_column_name
     * @param string $field_attributes
     */
    public static function rename_column($table_name, $old_column_name, $new_column_name, $field_attributes) {
        $result = dbquery("ALTER TABLE ".$table_name." CHANGE ".$old_column_name." ".$new_column_name." ".$field_attributes."");
        if (!$result) {
            fusion_stop("Unable to alter ".$old_column_name." to ".$new_column_name);
        }
    }

    /**
     * Move a single column from one table to another.
     *
     * @param string $old_table
     * @param string $new_table
     * @param string $column_name
     */
    public static function move_column($old_table, $new_table, $column_name) {
        $result = dbquery("SHOW COLUMNS FROM ".$old_table);
        $data = [];
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                if ($data['Field'] == $column_name) {
                    break;
                }
            }
        }
        if (!empty($data)) {
            $result = dbquery("ALTER TABLE ".$new_table." ADD COLUMN ".$data['Field']." ".$data['Type']." ".($data['Null'] == "NO" ? "NOT NULL" : "NULL")." DEFAULT '".$data['Default']."'");
            if (!$result) {
                fusion_stop();
            }
            if ($result && fusion_safe()) {
                dbquery("INSERT INTO ".$new_table." (".$data['Field'].") SELECT ".$data['Field']." FROM ".$old_table);
            }
            if (!$result && fusion_safe()) {
                fusion_stop();
            }
            if (!fusion_safe()) {
                addnotice("danger", "Cannot move ".$column_name);
            }
        }
    }

}

/**
 * Hierarchy ID to category output.
 *
 * @param string $db            Table name.
 * @param string $id_col        ID column.
 * @param string $cat_col       Category column.
 * @param string $filter        Replace conditional structure.
 * @param string $query_replace Replace the entire query structure.
 *
 * @return array Returns cat-id relationships.
 */
function dbquery_tree($db, $id_col, $cat_col, $filter = NULL, $query_replace = NULL) {
    $index = [];
    $query = "SELECT $id_col, $cat_col FROM ".$db." ".$filter;
    if (!empty($query_replace)) {
        $query = $query_replace;
    }
    $result = dbquery($query);
    while ($row = dbarray($result)) {
        $id = $row[$id_col];
        $parent_id = $row[$cat_col] === NULL ? "NULL" : $row[$cat_col];
        $index[$parent_id][] = $id;
    }

    return $index;
}

/**
 * Hierarchy full data output.
 *
 * @param string $db            Table name.
 * @param string $id_col        ID column.
 * @param string $cat_col       Category column.
 * @param string $filter        Replace conditional structure.
 * @param string $query_replace Replace the entire query structure.
 *
 * @return array Returns cat-id relationships with full data.
 */
function dbquery_tree_full($db, $id_col, $cat_col, $filter = NULL, $query_replace = NULL) {
    //$data = [];
    $index = [];
    $query = "SELECT * FROM ".$db." ".$filter;
    if (!empty($query_replace)) {
        $query = $query_replace;
    }
    $query = dbquery($query);
    while ($row = dbarray($query)) {
        $id = $row[$id_col];
        $parent_id = $row[$cat_col] === NULL ? "0" : $row[$cat_col];
        //$data[$id] = $row;
        $index[$parent_id][$id] = $row;
    }

    return $index;
}

/**
 * Get index information from dbquery_tree_full().
 *
 * @param array $data Array generated from dbquery_tree_full().
 *
 * @return array
 */
function tree_index(array $data) {
    $list = [];
    if (!empty($data)) {
        foreach ($data as $arr => $value) {
            $list[$arr] = array_keys($value);
        }
    }

    return $list;
}

/**
 * Reduce the results of a hierarchy tree array to a non-multidimensional
 * single output value while preserving keys.
 *
 * @param array  $result Results from dbquery_tree_full() or dbquery_tree().
 * @param string $id_col ID column.
 *
 * @return array
 */
function reduce_tree(array $result, $id_col) {
    $arrays = flatten_array($result);
    $list = [];
    foreach ($arrays as $value) {
        if (isset($value[$id_col])) {
            $list[$value[$id_col]] = $value;
        } else {
            $list[$value] = $value;
        }
    }
    return $list;
}

/**
 * Get tree root ID of a child from dbquery_tree() result.
 *
 * @param array $index    Results from dbquery_tree()
 * @param int   $child_id Child ID.
 *
 * @return int
 */
function get_root(array $index, $child_id) {
    foreach ($index as $key => $array) {
        if (in_array($child_id, $array)) {
            if ($key == 0) {
                return $child_id;
            } else {
                return (int)get_root($index, $key);
            }
        }
    }

    return NULL;
}

/**
 * Get tree root ID of a child via SQL.
 * Alternative function to get a root of a specific item when dbtree is not available.
 *
 * @param string $db         The table name relative to the search.
 * @param string $id_col     The unique id column name of $db.
 * @param string $cat_col    The category id column name of $db.
 * @param int    $current_id The current id of the item relative to the ancestor root.
 *
 * @return int
 */
function get_hkey($db, $id_col, $cat_col, $current_id) {
    $result = dbquery("SELECT $id_col, $cat_col FROM ".$db." WHERE $id_col =:pid LIMIT 1", [':pid' => intval($current_id)]);
    if (dbrows($result) > 0) {
        $data = dbarray($result);
        if ($data[$cat_col] > 0) {
            $hkey = get_hkey($db, $id_col, $cat_col, $data[$cat_col]);
        } else {
            $hkey = $data[$id_col];
        }
    } else {
        // predict current row.
        $rows = dbarray(dbquery("SELECT MAX($id_col) as num FROM ".$db));
        $rows = !empty($rows['num']) ? $rows['num'] : 0;
        $hkey = $rows + 1;
    }

    return (int)$hkey;
}

/**
 * Get immediate parent ID from dbquery_tree() result.
 *
 * @param array $index    Results from dbquery_tree().
 * @param int   $child_id Child ID.
 *
 * @return int
 */
function get_parent(array $index, $child_id) {
    foreach ($index as $key => $value) {
        if (in_array($child_id, $value)) {
            return (int)$key;
        }
    }

    return NULL;
}

/**
 * Get immediate parent array from dbquery_tree_full() result.
 *
 * @param array $data     Results from dbquery_tree_full().
 * @param int   $child_id Child ID.
 *
 * @return array
 */
function get_parent_array(array $data, $child_id) {
    foreach ($data as $value) {
        if (isset($value[$child_id])) {
            return (array)$value[$child_id];
        }
    }

    return NULL;
}

/**
 * Get parent IDs from dbquery_tree() result.
 *
 * @param array $index    Results from dbquery_tree().
 * @param int   $child_id Child ID.
 * @param array $list
 *
 * @return array|int
 */
function get_all_parent(array $index, $child_id, $list = []) {
    foreach ($index as $key => $value) {
        if (in_array($child_id, $value)) {
            if ($key == 0) {

                if (!empty($list)) {
                    return $list;
                }

                return $key;
            } else {
                $list[] = $key;
                return (array)get_all_parent($index, $key, $list);
            }
        }
    }

    return NULL;
}

/**
 * Get child IDs from dbquery_tree() result.
 *
 * @param array $index     Results from dbquery_tree().
 * @param int   $parent_id Parent ID.
 * @param array $children
 *
 * @return array
 */
function get_child(array $index, $parent_id, $children = []) {
    $parent_id = $parent_id === NULL ? NULL : $parent_id;
    if (isset($index[$parent_id])) {
        foreach ($index[$parent_id] as $id) {
            $children[] = $id;
            get_child($index, $id, $children);
        }
    }

    return $children;
}

/**
 * Get current depth from dbquery_tree() result.
 *
 * @param array $index    Results from dbquery_tree().
 * @param int   $child_id Child ID.
 * @param int   $depth
 *
 * @return int
 */
function get_depth(array $index, $child_id, $depth = NULL) {
    if (!$depth) {
        $depth = 1;
    }
    foreach ($index as $key => $value) {
        if (in_array($child_id, $value)) {
            if ($key == 0) {
                return (int)$depth;
            } else {
                return (int)get_depth($index, $key, $depth + 1);
            }
        }
    }

    return NULL;
}

/**
 * Get maximum depth of a hierarchy tree.
 *
 * @param array $array Results from dbquery_tree().
 *
 * @return int
 */
function array_depth(array $array) {
    $max_depth = 1;
    foreach ($array as $value) {
        if (is_array($value)) {
            $depth = array_depth($value) + 1;
            if ($depth > $max_depth) {
                $max_depth = $depth;
            }
        }
    }

    return $max_depth;
}

/**
 * Get hierarchy array with injected child key.
 * This is a slower model to fetch hierarchy data than dbquery_tree_full();
 *
 * @param string $db        Table name.
 * @param string $id_col    ID column.
 * @param string $cat_col   Category column.
 * @param string $cat_value Category value.
 * @param string $filter    Replace conditional structure.
 *
 * @return array
 */
function dbtree($db, $id_col, $cat_col, $cat_value = NULL, $filter = NULL) {
    $refs = [];
    $list = [];
    $col_names = fieldgenerator($db);
    $result = dbquery("SELECT * FROM ".$db." ".($filter ?: "ORDER BY $id_col ASC"));
    while ($data = dbarray($result)) {
        foreach ($col_names as $v) {
            if ($v == $id_col) {
                $thisref = &$refs[$data[$id_col]];
            }
            $thisref[$v] = $data[$v];
        }
        if ($data[$cat_col] == $cat_value) {
            $list[$data[$id_col]] = &$thisref;
        } else {
            $refs[$data[$cat_col]]['children'][$data[$id_col]] = &$thisref;
        }
    }

    return $list;
}

/**
 * Lighter version of dbtree() with only id and child key.
 *
 * @param string $db        Table name.
 * @param string $id_col    ID column.
 * @param string $cat_col   Category column.
 * @param string $cat_value Category value.
 *
 * @return array
 */
function dbtree_index($db, $id_col, $cat_col, $cat_value = NULL) {
    $refs = [];
    $list = [];
    $result = dbquery("SELECT * FROM ".$db);
    $col_names = fieldgenerator($db);
    $i = 1;
    while ($data = dbarray($result)) {
        foreach ($col_names as $v) {
            if ($v == $id_col) {
                $thisref = &$refs[$data[$id_col]];
            }
            $thisref[$v] = $data[$v];
        }
        if ($data[$cat_col] == $cat_value) {
            $list[$data[$id_col]] = &$thisref;
        } else {
            $refs[$data[$cat_col]]['child'][$data[$id_col]] = &$thisref;
        }
        $i++;
    }

    return $list;
}

/**
 * To sort key on dbtree_index() results.
 *
 * @param array  $result dbtree_index() result.
 * @param string $key    Array key.
 *
 * @return array
 */
function sort_tree(array $result, $key) {
    $current_array = [];
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

    return $current_array;
}

/**
 * Sort tree an associative array.
 *
 * @param array  $array
 * @param string $key
 * @param string $sort
 *
 * @return array
 */
function sorter(array $array, $key, $sort = 'ASC') {
    $sorter = [];
    $ret = [];
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii] = $va[$key];
    }
    if ($sort == 'ASC') {
        asort($sorter);
    } else if ($sort == 'DESC') {
        arsort($sorter);
    }
    foreach ($sorter as $ii => $va) {
        $ret[$ii] = $array[$ii];
    }
    $array = $ret;

    return $array;
}

/**
 * Get the total max depths of dbtree().
 *
 * @param array  $data Results from dbtree().
 * @param string $field
 * @param string $match
 * @param int    $depth
 *
 * @return int
 */
function tree_depth(array $data, $field, $match, $depth = 1) {
    if (!$depth) {
        $depth = '1';
    }

    foreach ($data as $arr) {
        if ($arr[$field] == $match) {
            return (int)$depth;
        } else {
            if (array_key_exists('children', $arr)) {
                $deep = tree_depth($arr['children'], $field, $match, $depth + 1);
                if ($deep) {
                    return $deep;
                }
            }
        }
    }

    return NULL;
}

/**
 * Count result from dbquery_tree().
 *
 * @param array $data           Results from dbquery_tree().
 * @param bool  $column_name    Column name.
 * @param bool  $value_to_match Value to match.
 *
 * @return int
 * @todo: Change to count on index in favor of deprecated method
 *      Get the occurences of a column name matching value
 *      $unpublish_count = tree_count($dbtree_result, "column_name", "value")-1;
 *
 */
function tree_count(array $data, $column_name = NULL, $value_to_match = NULL) {
    // Find Occurence of match in a tree.

    if (!isset($counter)) {
        $counter = 0;
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
            $counter = tree_count($arr['children'], $column_name, $value_to_match) + $counter;
        }
    }

    return (int)$counter;
}

/**
 * Display parent nodes.
 *
 * @param array  $data    Result from dbquery_tree().
 * @param string $id_col  ID column.
 * @param string $cat_col Category column.
 * @param int    $id
 *
 * @return array
 */
function display_parent_nodes(array $data, $id_col, $cat_col, $id) {
    $current = $data[$id];
    $parent_id = $current[$cat_col] === NULL ? "NULL" : $current[$cat_col];
    $parents = [];
    while (isset($data[$parent_id])) {
        $current = $data[$parent_id];
        $parent_id = $current[$cat_col] === NULL ? "NULL" : $current[$cat_col];
        $parents[] = $current[$id_col];
    }

    // echo implode(" > ", array_reverse($parents));
    return $parents;
}

/**
 * MySQL show columns shorthand.
 *
 * @param string $db Table name.
 *
 * @return array Returns available columns in a table.
 */
function fieldgenerator($db) {
    static $col_names = [];

    if (empty($col_names[$db])) {
        $cresult = dbquery("SHOW COLUMNS FROM $db");
        $col_names = [];
        while ($cdata = dbarray($cresult)) {
            $col_names[$db][] = $cdata['Field'];
        }
    }

    return (array)$col_names[$db];
}

/**
 * MySQL row modifiers. Insert/Update/Delete.
 *
 * @param string $table     Table name.
 * @param array  $inputdata Input data.
 * @param string $mode      Possible value: save, update, delete
 * @param array  $options
 *
 * @return int|false If an error happens, it returns false.
 *                   Otherwise, if $mode is 'save' and the primary key column is
 *                   incremented automatically, this function returns the last inserted id.
 *                   In other cases it always returns 0.
 */
function dbquery_insert($table, array $inputdata, $mode, $options = []) {
    $options += [
        'debug'        => FALSE, // If true, do nothing, just show the SQL.
        'primary_key'  => '', // Name of primary key column. If it is empty, column will detect automatically.
        'no_unique'    => FALSE, // If true, primary key column will be not removed from $inputdata.
        'keep_session' => TRUE // If true, defender will not unset field sessions.
    ];

    if (!fusion_safe()) {
        if ($options['debug']) {
            print_p('Fusion Null Declared. Developer, check form tokens.');
        }

        return FALSE;
    }

    static $columns = [];
    static $pkcolumns = [];

    if (!isset($columns[$table])) {

        $cresult = dbquery("SHOW COLUMNS FROM $table");
        while ($cdata = dbarray($cresult)) {
            $columns[$table][] = $cdata['Field'];
            if ($cdata['Key'] === 'PRI') {
                $pkcolumns[$table][$cdata['Field']] = $cdata['Field'];
            }
        }
    }

    if ($options['primary_key']) {
        $options['primary_key'] = (array)$options['primary_key'];
        $pkcolumns[$table] = array_combine($options['primary_key'], $options['primary_key']);
    }

    $sanitized_input = [];

    $data = array_intersect_key($inputdata, array_flip($columns[$table]));

    $pkvalues = array_intersect_key($data, $pkcolumns[$table]);

    if (!$options['no_unique'] and $mode !== 'save') {
        foreach ($pkcolumns[$table] as $c) {
            unset($data[$c]);
        }
    }

    if (!$data) {
        if ($options['debug']) {
            print_p('$inputdata does not contain any valid column.');
        }

        return FALSE;
    }

    $sqlPatterns = [
        'save'   => 'INSERT INTO {table} SET {values}',
        'update' => 'UPDATE {table} SET {values} {where}',
        'delete' => 'DELETE FROM {table} {where}'
    ];

    $params = [];
    foreach ($data as $name => $value) {
        $sanitized_input[] = "$name = :$name";
        $params[":$name"] = $value;
    }

    if (!isset($sqlPatterns[$mode])) {
        die();
    }
    $where = '';

    if ($mode === 'update' or $mode === 'delete') {
        $pkwhere = [];
        foreach ($pkvalues as $name => $pkvalue) {
            $pkwhere[] = "$name='$pkvalue'";
        }
        $where = implode(' AND ', $pkwhere);
    }

    if ($mode === 'delete') {
        $params = []; // fix for "Invalid parameter number: number of bound variables does not match number of tokens"
    }

    $sql = strtr($sqlPatterns[$mode], [
        '{table}'  => $table,
        '{values}' => implode(', ', $sanitized_input),
        '{where}'  => $where ? "WHERE ".$where : ''
    ]);

    $result = NULL;
    if ($options['debug']) {
        print_p($where);
        print_p($sanitized_input);
        print_p($params);
        print_p($sql);
    } else {
        $result = dbquery($sql, $params);
        if (!$options['keep_session']) {
            Defender::getInstance()->unset_field_session();
        }
    }
    if ($result === FALSE) {
        return FALSE;
    }

    return (int)($mode === 'save') ? dblastid() : 0;
}

/**
 * SQL statement helper to find values in between dots.
 *
 * @param string $column_name
 * @param string $value
 * @param string $delim
 *
 * @return string
 * Example: language column contains '.BL.NS.NC.NG'
 *          SELECT * FROM ".DB." WHERE ".in_group(language, 'BL')."
 */
function in_group($column_name, $value, $delim = ',') {
    return "CONCAT('$delim', $column_name, '$delim') LIKE '%$delim$value$delim%' ";
}

/**
 * Check multilang tabl.
 *
 * @param string $rights Multilang rights.
 *
 * @return bool
 */
function multilang_table($rights) {
    static $tables = NULL;
    if ($tables === NULL) {
        $tables = [];
        $result = dbquery("SELECT mlt_rights FROM ".DB_LANGUAGE_TABLES." WHERE mlt_status='1'");
        while ($row = dbarraynum($result)) {
            $tables[$row[0]] = TRUE;
        }
    }

    return isset($tables[$rights]);
}

/**
 * SQL language value
 *
 * @param string $column Column name.
 *
 * @return string - calculated conditions
 * Usage: $result = dbquery("SELECT * FROM ".DB_NEWS." WHERE ".multilang_column('news_subject')." = '".$data['news_subject']."'");
 * Usage: $tree_data = dbquery_tree_full(DB_NEWS_CATS, "news_cat_id", "news_cat_parent", "order by ".multilang_column("news_cat_name"));
 */
function multilang_column($column) {
    $installed_lang = fusion_get_enabled_languages();
    $i = 1;
    $val_key = 2; // this is the first pair
    foreach ($installed_lang as $locale => $language) {
        if ($locale == LANGUAGE) {
            $val_key = $i * 2;
        }
        $i++;
    }

    return "replace(replace(replace(substring_index(substring_index($column, ';', ".$val_key."),':',-1), '\"', ''), '{%sc%}', ':') , '{%dq%}', '')";
}

/**
 * Check if a table exists.
 *
 * However, you can pass the table name with or without prefix
 *
 * @param string $table      The name of the table with or without prefix
 * @param bool   $add_prefix Add table prefix.
 *
 * @return bool
 */
function db_exists($table, $add_prefix = TRUE) {
    if ($add_prefix === TRUE) {
        if (strpos($table, DB_PREFIX) === FALSE) {
            $table = DB_PREFIX.$table;
        }
    }

    $query = dbquery("SHOW TABLES LIKE '$table'");

    return boolval(dbrows($query));
}

/**
 * Determine whether column exists in a table.
 *
 * @param string $table      Table name.
 * @param string $column     Column name.
 * @param bool   $add_prefix Add table prefix.
 *
 * @return bool
 */
function column_exists($table, $column, $add_prefix = TRUE) {
    static $table_config = [];

    if ($add_prefix === TRUE) {
        if (strpos($table, DB_PREFIX) === FALSE) {
            $table = DB_PREFIX.$table;
        }
    }

    if (empty($table_config[$table])) {
        $table_config[$table] = array_flip(fieldgenerator($table));
    }

    return isset($table_config[$table][$column]);
}

/**
 * Table rows ordering.
 *
 * @param string $dbname           Table name.
 * @param int    $current_order    Current order.
 * @param string $order_col        Column with order number.
 * @param int    $current_id       Current ID. ID is required only for update mode.
 * @param string $id_col           ID column. ID is required only for update mode.
 * @param int    $current_category Current category.
 * @param string $cat_col          Category column.
 * @param bool   $multilang        Enable multilang.
 * @param string $multilang_col    Multilang column.
 * @param string $mode             Possible value: save, update, delete.
 *
 * @return mixed
 */
function dbquery_order($dbname, $current_order, $order_col, $current_id = 0, $id_col = NULL, $current_category = 0, $cat_col = NULL, $multilang = FALSE, $multilang_col = '', $mode = 'update') {
    $multilang_sql_1 = $multilang && $multilang_col ? "WHERE ".in_group($multilang_col, LANGUAGE) : '';
    $multilang_sql_2 = $multilang && $multilang_col ? "AND ".in_group($multilang_col, LANGUAGE) : '';

    if (!$current_order) {
        $current_order = dbresult(dbquery("SELECT MAX($order_col) FROM ".$dbname." ".$multilang_sql_1), 0) + 1;
    }

    switch ($mode) {
        case 'save':
            if ($order_col && $current_order && $dbname) {
                if (!empty($current_category) && (!empty($cat_col))) {
                    return dbquery("UPDATE ".$dbname." SET $order_col=$order_col+1 WHERE $cat_col='".intval($current_category)."' AND $order_col>='".intval($current_order)."' $multilang_sql_2");
                } else {
                    return dbquery("UPDATE ".$dbname." SET $order_col=$order_col+1 WHERE $order_col>='".intval($current_order)."' $multilang_sql_2");
                }
            } else {
                fusion_stop();
            }
            break;
        case 'update':
            if ($id_col && $current_id && $order_col && $current_order && $dbname) {
                $old_order = dbresult(dbquery("SELECT $order_col FROM ".$dbname." WHERE $id_col='".intval($current_id)."' $multilang_sql_2"), 0);
                if (!empty($current_category) && (!empty($cat_col))) {
                    if ($current_order > $old_order) {
                        return dbquery("UPDATE ".$dbname." SET $order_col=$order_col-1 WHERE $cat_col='".intval($current_category)."' AND $order_col>'$old_order' AND $order_col<='".intval($current_order)."' $multilang_sql_2");
                    } else if ($current_order < $old_order) {
                        return dbquery("UPDATE ".$dbname." SET $order_col=$order_col+1 WHERE $cat_col='".intval($current_category)."' AND $order_col<'$old_order' AND $order_col>='".intval($current_order)."' $multilang_sql_2");
                    } else {
                        return TRUE;
                    }
                } else {
                    if ($current_order > $old_order) {
                        return dbquery("UPDATE ".$dbname." SET $order_col=$order_col-1 WHERE $order_col>'$old_order' AND $order_col<='".intval($current_order)."' $multilang_sql_2");
                    } else if ($current_order < $old_order) {
                        return dbquery("UPDATE ".$dbname." SET $order_col=$order_col+1 WHERE $order_col<'$old_order' AND $order_col>='".intval($current_order)."' $multilang_sql_2");
                    } else {
                        return TRUE;
                    }
                }
            } else {
                fusion_stop();
            }
            break;
        case 'delete':
            if ($order_col && $current_order && $dbname) {
                if (!empty($current_category) && (!empty($cat_col))) {
                    // in nested mode, $cat and $cat_col is REQUIRED.
                    return dbquery("UPDATE ".$dbname." SET $order_col=$order_col-1 WHERE $cat_col='".intval($current_category)."' AND $order_col>'".intval($current_order)."' $multilang_sql_2");
                } else {
                    return dbquery("UPDATE ".$dbname." SET $order_col=$order_col-1 WHERE $order_col>'".intval($current_order)."' $multilang_sql_2");
                }
            } else {
                fusion_stop();
            }
            break;
        default:
            fusion_stop();
    }

    return NULL;
}

/**
 * To flatten any multidimensional array.
 * Best used to flatten any hierarchy array data.
 *
 * @param array $array Multidimensional array.
 *
 * @return array
 */
function flatten_array(array $array) {
    return call_user_func_array('array_merge', $array);
}

/**
 * Shorthand to build/combines an array
 *
 * @param array $array  Initial array to create with keys named in a non-multidimensional single array.
 *                      This will create a multidimensional blank array.
 *                      Example: ['column', 'coumn2', 'column3']
 * @param array $array2 Override. An array default value that consist of keys and defined values. ['column' => 'default']
 *
 * @return array|false
 */
function new_array(array $array, array $array2 = []) {
    $new_arr = array_combine(array_keys(array_flip($array)), array_fill(0, count($array), ''));
    if (!empty($array2)) {
        $new_arr = array_merge($new_arr, $array2);
    }
    return $new_arr;
}

/**
 * Single column search.
 * used to make searches on field
 * echo search_field(['admin_title', 'admin_link'], 'ac c d ghi');
 *
 * @param array  $columns Table columns.
 * @param string $text    Any string.
 *
 * @return string
 */
function search_field(array $columns, $text) {
    $condition = '';
    $text = explode(" ", $text);
    $the_sql = [];
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
                    $condition .= ($arr == count($columns) - 1) ? "$col_field LIKE '%$search_text%'" : "$col_field LIKE '%$search_text%' OR ";
                }
                $condition .= ")";
            }
        }
        $condition .= ($counter == count($the_sql) - 1) ? "  " : " OR ";
    }

    return $condition;
}

/**
 * Cached query.
 *
 * @param string $key
 * @param string $query
 * @param array  $parameters
 *
 * @return false|int|mixed
 */
function cdquery($key, $query, $parameters = []) {
    return DBCache::getInstance()->dbquery($key, $query, $parameters);
}

/**
 * Cached number of rows.
 *
 * @param mixed $result
 *
 * @return int
 */
function cdrows($result) {
    return DBCache::getInstance()->dbrows($result);
}

/**
 * Cached associative object array.
 *
 * @param mixed $result
 *
 * @return array|null
 */
function cdarray($result) {
    return DBCache::getInstance()->dbarray($result);
}

/**
 * Fetch one row as a numeric array
 *
 * @param mixed $result
 *
 * @return array|mixed
 */
function cdarraynum($result) {
    return DBCache::getInstance()->dbarraynum($result);
}

/**
 * Fetch the first column of a specific row
 *
 * @param mixed $result
 * @param int   $row
 *
 * @return mixed|string
 */
function cdresult($result, $row) {
    return DBCache::getInstance()->dbresult($result, $row);
}

/**
 * Runs cache flush command.
 */
function cdflush() {
    DBCache::getInstance()->flush();
}

/**
 * Resets the cache and invalidates it.
 *
 * @param string $key
 */
function cdreset($key) {
    DBCache::getInstance()->delete($key);
}

/**
 * Hierarchy full data output.
 *
 * @param string $key
 * @param string $db
 * @param string $id_col
 * @param string $cat_col
 * @param null   $filter        replace conditional structure
 * @param null   $query_replace replace the entire query structure
 *
 * @return array Returns cat-id relationships with full data
 */
function cdquery_tree_full($key, $db, $id_col, $cat_col, $filter = NULL, $query_replace = NULL) {
    $index = [];
    $query = "SELECT * FROM ".$db." ".$filter;
    if (!empty($query_replace)) {
        $query = $query_replace;
    }
    $query = cdquery($key, $query);
    while ($row = cdarray($query)) {
        $id = $row[$id_col];
        $parent_id = $row[$cat_col] === NULL ? "0" : $row[$cat_col];
        $index[$parent_id][$id] = $row;
    }

    return $index;
}

/**
 * Hierarchy ID to category output.
 *
 * @param string $key
 * @param string $db            Table name
 * @param string $id_col        ID column
 * @param string $cat_col       Category column
 * @param null   $filter        Conditions
 * @param null   $query_replace Replace the entire query
 *
 * @return array Returns cat-id relationships
 */
function cdquery_tree($key, $db, $id_col, $cat_col, $filter = NULL, $query_replace = NULL) {
    $index = [];
    $query = "SELECT $id_col, $cat_col FROM ".$db." ".$filter;
    if (!empty($query_replace)) {
        $query = $query_replace;
    }
    $result = cdquery($key, $query);
    while ($row = cdarray($result)) {
        $id = $row[$id_col];
        $parent_id = $row[$cat_col] === NULL ? "NULL" : $row[$cat_col];
        $index[$parent_id][] = $id;
    }

    return $index;
}
