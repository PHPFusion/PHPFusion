<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sqlhandler.inc.php
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

class SqlHandler {

    /**
     * Add column to a specific table
     *
     * @param $table_name
     * @param $new_column_name
     * @param $field_attributes
     */
    public static function add_column($table_name, $new_column_name, $field_attributes) {
        if (!empty($field_attributes)) {
            $result = dbquery("ALTER TABLE ".$table_name." ADD ".$new_column_name." ".$field_attributes); // create the new one.
            if (!$result) {
                fusion_stop();
                add_notice("danger", "Unable to add column ".$new_column_name." with attributes - ".$field_attributes);
            }
        }
    }

    /**
     * Drop column of a table
     *
     * @param $table_name
     * @param $old_column_name
     */
    public static function drop_column($table_name, $old_column_name) {
        $result = dbquery("ALTER TABLE ".$table_name." DROP ".$old_column_name);
        if (!$result) {
            \Defender::stop();
            add_notice("danger", "Unable to drop column ".$old_column_name);
        }
    }

    /**
     * Function to build a new table
     *
     * @param $new_table
     * @param $primary_column
     *
     * @return bool|mixed|null|PDOStatement|resource
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
     * @param $old_table
     * @param $new_table
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
                add_notice("danger", "Unable to move all columns from ".$old_table." to " > $new_table);
            }
        }
    }

    /**
     * Drop table
     *
     * @param $old_table
     */
    public static function drop_table($old_table) {

        $old_table = !stristr($old_table, DB_PREFIX) ? DB_PREFIX.$old_table : $old_table;
        $result = dbquery("DROP TABLE IF EXISTS ".$old_table);
        if (!$result) {
            \Defender::stop();
        }
        if (!fusion_safe()) {
            add_notice("danger", "Unable to drop ".$old_table);
        }

    }


    /**
     * Function to rename column name
     *
     * @param $table_name
     * @param $old_column_name
     * @param $new_column_name
     * @param $field_attributes
     */
    public static function rename_column($table_name, $old_column_name, $new_column_name, $field_attributes) {
        $result = dbquery("ALTER TABLE ".$table_name." CHANGE ".$old_column_name." ".$new_column_name." ".$field_attributes);
        if (!$result) {
            \Defender::stop();
            add_notice("danger", "Unable to alter ".$old_column_name." to ".$new_column_name);
        }
    }

    /**
     * Move a single column from one table to another
     *
     * @param $old_table
     * @param $new_table
     * @param $column_name
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
                \Defender::stop();
            }
            if ($result && fusion_safe()) {
                dbquery("INSERT INTO ".$new_table." (".$data['Field'].") SELECT ".$data['Field']." FROM ".$old_table);
            }
            if (!$result && fusion_safe()) {
                \Defender::stop();
            }
            if (!fusion_safe()) {
                add_notice("danger", "Cannot move ".$column_name);
            }
        }
    }

}

/**
 * Updates the settings for settings table
 *
 * @param $settings
 *
 * @return mixed|null
 */
function dbquery_update_settings($settings) {
    if (!empty($settings) && fusion_safe()) {
        $sql_arr = [];
        foreach ($settings as $key => $value) {
            $sql_arr[] = "('$key', '$value')";
        }
        $sql = "INSERT INTO ".DB_SETTINGS." (settings_name, settings_value) VALUES ".implode(',', $sql_arr)." ON DUPLICATE KEY UPDATE settings_name=VALUES(settings_name), settings_value=VALUES(settings_value)";
    }
    if (isset($sql)) {
        return dbquery($sql);
    }
    return NULL;
}

/**
 * Updates the settings for infusions settings table
 *
 * @param $inf_folder
 * @param $settings
 *
 * @return mixed|null
 */
function dbquery_update_infusions($inf_folder, $settings) {
    if (!empty($inf_folder) && !empty($settings) && fusion_safe()) {
        $sql_arr = [];
        foreach ($settings as $key => $value) {
            $sql_arr[] = "('$key', '$value', '$inf_folder')";
        }
        $sql = "INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ".implode(',', $sql_arr)." ON DUPLICATE KEY UPDATE settings_name=VALUES(settings_name), settings_value=VALUES(settings_value), settings_inf=VALUES(settings_inf)";
    }
    if (isset($sql)) {
        return dbquery($sql);
    }
    return NULL;
}

// Hierarchy Type 1 - key to index method

/**
 * Hierarchy ID to Category Output
 * Returns cat-id relationships
 *
 * @param        $db            - Table Name
 * @param        $id_col        - ID column
 * @param        $cat_col       - Category Column
 * @param bool   $filter        - Conditions
 * @param string $query_replace - Replace the entire query
 *
 * @return array
 */
function dbquery_tree($db, $id_col, $cat_col, $filter = FALSE, $query_replace = "") {
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

    return (array)$index;
}

/**
 * Hierarchy Full Data Output
 * Returns cat-id relationships with full data
 *
 * @param      $db
 * @param      $id_col
 * @param      $cat_col
 * @param bool $filter        - replace conditional structure
 * @param      $query_replace - replace the entire query structure
 *
 * @return array
 */
function dbquery_tree_full($db, $id_col, $cat_col, $filter = FALSE, $query_replace = "") {
    $data = [];
    $index = [];
    $query = "SELECT * FROM ".$db." ".$filter;
    if (!empty($query_replace)) {
        $query = $query_replace;
    }
    $query = dbquery($query);
    while ($row = dbarray($query)) {
        $id = $row[$id_col];
        $parent_id = $row[$cat_col] === NULL ? "0" : $row[$cat_col];
        $data[$id] = $row;
        $index[$parent_id][$id] = $row;
    }

    return (array)$index;
}

/**
 * Get index information from dbquery_tree_full.
 *
 * @param $data - array generated from dbquery_tree_full();
 *
 * @return array
 */
function tree_index($data) {
    $list = [];
    if (!empty($data)) {
        foreach ($data as $arr => $value) {
            $list[$arr] = array_keys($value);
        }
    }

    return $list;
}

/**
 * Reduce the results of a hierarchy tree array to a non multidimensional single output value while preserving keys
 *
 * @param string $result results from dbquery_tree_full() or dbquery_tree()
 * @param int    $id_col the id_col
 *
 * @return array
 */
function reduce_tree($result, $id_col) {
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
 * Get Tree Root ID of a Child from dbquery_tree() result
 *
 * @param array $index
 * @param       $child_id
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
 * Get Tree Root ID of a child via SQL
 * Alternative function to get a root of a specific item when dbtree is not available
 *
 * @param string $db         The table name relative to the search
 * @param int    $id_col     The unique id column name of $db
 * @param int    $cat_col    The category id column name of $db
 * @param int    $current_id The current id of the item relative to the ancestor root
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
 * Get immediate Parent ID from dbquery_tree() result
 *
 * @param array $index
 * @param       $child_id
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
 * Get immediate Parent Array from dbquery_tree_full() result
 *
 * @param array $data
 * @param       $child_id
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
 * Get all parent ID from dbquery_tree()
 *
 * @param array $index
 * @param       $child_id
 * @param array $list
 *
 * @return array
 */
function get_all_parent(array $index, $child_id, array &$list = []) {
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
 * Get Child IDs from dbquery_tree() result
 *
 * @param       $index
 * @param       $parent_id
 * @param array $children
 *
 * @return array
 */
function get_child($index, $parent_id, array &$children = []) {
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
 * Get current depth from dbquery_tree() result
 *
 * @param      $index
 * @param      $child_id
 * @param bool $depth
 *
 * @return bool|int
 */
function get_depth($index, $child_id, $depth = FALSE) {
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
 * Get maximum depth of a hierarchy tree
 *
 * @param $array
 *
 * @return int
 */
function array_depth($array) {
    $max_depth = 1;
    foreach ($array as $value) {
        if (is_array($value)) {
            $depth = array_depth($value) + 1;
            if ($depth > $max_depth) {
                $max_depth = $depth;
            }
        }
    }

    return (int)$max_depth;
}

// Hierarchy Type 2 - child key method

/**
 * Get Hierarchy Array with injected child key
 * This is a slower model to fetch hierarchy data than dbquery_tree_full
 *
 * @param      $db
 * @param      $id_col
 * @param      $cat_col
 * @param bool $cat_value
 * @param bool $filter
 *
 * @return array
 */
function dbtree($db, $id_col, $cat_col, $cat_value = FALSE, $filter = FALSE) {
    $refs = [];
    $list = [];
    $col_names = fieldgenerator($db);
    $result = dbquery("SELECT * FROM ".$db." ".($filter ?: "ORDER BY $id_col ASC"));
    while ($data = dbarray($result)) {
        foreach ($col_names as $arr => $v) {
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

    return (array)$list;
}

/**
 * Lighter version of dbtree() with only id and child key
 *
 * @param bool $db
 * @param      $id_col
 * @param      $cat_col
 * @param bool $cat_value
 *
 * @return array
 */
function dbtree_index($db, $id_col, $cat_col, $cat_value = FALSE) {
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

    return (array)$list;
}

/**
 * To sort key on dbtree_index results
 *
 * @param $result
 * @param $key
 *
 * @return array
 */
function sort_tree(&$result, $key) {
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

    return (array)$current_array;
}

/**
 * Sort tree associative array
 *
 * @param        $array
 * @param        $key
 * @param string $sort
 *
 * @return array
 */
function sorter(&$array, $key, $sort = 'ASC') {
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

    return (array)$array;
}

/**
 * Get the total max depths of dbtree()
 *
 * @param $data
 * @param $field
 * @param $match
 * @param $depth
 *
 * @return int
 */
function tree_depth($data, $field, $match, $depth = 1) {
    if (!$depth) {
        $depth = 1;
    }

    foreach ($data as $arr) {
        if ($arr[$field] == $match) {
            return (int)$depth;
        } else {
            if (array_key_exists('children', $arr)) {
                $deep = tree_depth($arr['children'], $field, $match, $depth + 1);
                if ($deep) {
                    return (int)$deep;
                }
            }
        }
    }

    return NULL;
}

/**
 * @param      $data - $data = dbquery_tree(...);
 * @param bool $column_name
 * @param bool $value_to_match
 *
 * @return int
 * @todo: Change to count on index in favor of deprecated method
 *      Get the occurences of a column name matching value
 *      $unpublish_count = tree_count($dbtree_result, "column_name", "value")-1;
 *
 */
function tree_count($data, $column_name = FALSE, $value_to_match = FALSE) {
    // Find Occurence of match in a tree.
    //
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
 * This model will create a up to "HUNDREDS" of self joins to get a relational hierarchy
 *
 * @param      $db
 * @param      $id_col
 * @param      $cat_col
 * @param bool $filter
 * @param bool $filter_order
 * @param bool $filter_show
 * @param bool $depth
 *
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
        $prev = $i - 1;
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

/*
* Display parent nodes
*/
// need dbquery_tree_data to function
function display_parent_nodes($data, $id_col, $cat_col, $id) {
    $current = $data[$id];
    $parent_id = $current[$cat_col] === NULL ? "NULL" : $current[$cat_col];
    $parents = [];
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
 *
 * @param $db
 *
 * @return array
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
 * MYSQL Row modifiers. Insert/Update/Delete
 *
 * @param string $table
 * @param array  $inputdata
 * @param string $mode save|update|delete|multi_update
 * @param array  $options
 *                     Options:
 *                     bool debug If TRUE, do nothing, just show the SQL. FALSE by default
 *                     string primary_key Names of primary key columns. If it is empty, columns will detected automatically.
 *                     bool no_unique If TRUE, primary key columns will be not removed from $inputdata. FALSE by default.
 *                     bool keep_session If TRUE, defender will not unset field sessions.
 *
 * @return int|FALSE
 * @throws Exception
 *
 * If an error happens, it returns FALSE.
 * Otherwise, if $mode is save and the primary key column is
 * incremented automatically, this function returns the last inserted id.
 *
 * In other cases it always returns 0.
 */
function dbquery_insert($table, $inputdata, $mode, array $options = []) {
    $options += [
        'debug'        => FALSE,
        'primary_key'  => '',
        'no_unique'    => FALSE,
        'keep_session' => TRUE
    ];

    if (!fusion_safe()) {
        if ($options['debug']) {
            print_p('Fusion Null Declared. Developer, check form tokens.');
        }

        return FALSE;
    }

    $sqlPatterns = [
        'save'         => /** @lang text */
            'INSERT INTO `{table}` SET {values}',
        'update'       => /** @lang text */
            'UPDATE `{table}` SET {values} {where}',
        'delete'       => /** @lang text */
            'DELETE FROM `{table}` {where}',
        'multi_update' => /** @lang text */
            'INSERT INTO `{table}` {values} VALUES {multi_values} ON DUPLICATE KEY UPDATE {key_values}',
    ];

    static $table_data = [];
    if (empty($table_data[$table])) {

        $cresult = dbquery("SHOW COLUMNS FROM $table");
        $columns = [];
        $pkcolumns = [];
        while ($cdata = dbarray($cresult)) {
            $columns[] = $cdata['Field'];
            if ($cdata['Key'] === 'PRI') {
                $pkcolumns[$cdata['Field']] = $cdata['Field'];
            }
        }

        $table_data[$table] = [
            'columns'   => $columns,
            'pkcolumns' => $pkcolumns
        ];
    }

    if ($options['primary_key']) {
        $options['primary_key'] = (array)$options['primary_key'];
        $table_data[$table]['pkcolumns'] = array_combine($options['primary_key'], $options['primary_key']);
    }

    if ($mode == 'multi_update') {
        $data = reset($inputdata);
        if (!$data) {
            if ($options['debug']) {
                print_p('$inputdata does not contain any valid column.');
            }
            return FALSE;
        }
        $columns = [];
        $values = [];
        $column_keys = [];
        foreach ($inputdata as $input_data_arr) {
            $columns = array_keys($input_data_arr) + $columns;
            $values[] = "('".implode("','", $input_data_arr)."')";
        }
        $columns = array_intersect_key(array_flip($columns), array_flip($table_data[$table]['columns']));
        $columns = array_flip($columns);
        foreach ($columns as $column_key_names) {
            $column_keys[] = $column_key_names.'=VALUES('.$column_key_names.')';
        }
        $sql = strtr($sqlPatterns[$mode], [
            '{table}'        => $table,
            '{values}'       => '('.implode(',', $columns).')', // might update all the table values, we should take the intersected
            '{multi_values}' => implode(',', $values),
            '{key_values}'   => implode(',', $column_keys)
        ]);

        if (!$options['debug']) {
            dbquery($sql);
            return FALSE;
        }
        print_p($sql);
        return FALSE;
    }

    $sanitized_input = [];
    $data = array_intersect_key($inputdata, array_flip($table_data[$table]['columns']));
    $pkvalues = array_intersect_key($data, $table_data[$table]['pkcolumns']);
    if (!$options['no_unique'] and $mode !== 'save') {
        foreach ($table_data[$table]['pkcolumns'] as $c) {
            unset($data[$c]);
        }
    }
    //print_P( $table_data );
    if (!$data) {
        if ($options['debug']) {
            print_p('$inputdata does not contain any valid column.');
        }
        return FALSE;
    }

    // If values are missing, means that the $value below is an array. Set $strict to true to check for arrays.
    $strict = TRUE;
    foreach ($data as $name => $value) {
        if ($strict === TRUE) {
            if (is_array($name)) {
                throw new Exception('SQL column name cannot be an array');
            }
            if (is_array($value)) {
                throw new Exception('SQL value cannot be an array');
            }
        }
        // No value into SQL is in array
        if (is_array($value)) {
            $value = '';
        }
        $sanitized_input[] = "`$name` = '$value'";
    }
    if (!isset($sqlPatterns[$mode])) {
        die();
    }

    $where = '';

    if ($mode === 'update' or $mode === 'delete') {
        $pkwhere = [];
        foreach ($pkvalues as $name => $pkvalue) {
            $pkwhere[] = "`$name`='$pkvalue'";
        }
        $where = implode(' AND ', $pkwhere);
    }

    $sql = strtr($sqlPatterns[$mode], [
        '{table}'  => $table,
        '{values}' => implode(', ', $sanitized_input),
        '{where}'  => $where ? "WHERE ".$where : ''
    ]);

    $result = NULL;
    if (!$options['debug']) {
        $result = dbquery($sql);
        if (!$options['keep_session']) {
            //print_p('field session unset during '.$sql);
            Defender::unset_field_session();
        }
        if ($result === FALSE) {
            // Because dblastid() can return the id of the last record of the error log.
            return FALSE;
        }
        return ($mode === 'save') ? dblastid() : 0;
    }

}

/**
 * check multilang tables
 *
 * @staticvar boolean[] $tables
 *
 * @param string $table Table name
 *
 * @return boolean
 */
function multilang_table($table) {
    static $tables = NULL;
    if ($tables === NULL) {
        $tables = [];
        $result = dbquery("SELECT mlt_rights FROM ".DB_LANGUAGE_TABLES." WHERE mlt_status='1'");
        while ($row = dbarraynum($result)) {
            $tables[$row[0]] = TRUE;
        }
    }

    return isset($tables[$table]);
}

/**
 * SQL statement helper to find values in between dots
 *
 * @param        $column_name
 * @param        $value
 * @param string $delim
 *
 * @return string
 * Example: language column contains '.BL.NS.NC.NG'
 *            SELECT * FROM ".DB." WHERE ".in_group(language, 'BL')."
 */
function in_group($column_name, $value, $delim = ',') {
    return "CONCAT('$delim', $column_name, '$delim') LIKE '%$delim$value$delim%' ";
}

/**
 * SQL Language Value
 *
 * @param $table_col - target
 *
 * @return string - calculated conditions
 * Usage: $result = dbquery("SELECT * FROM ".DB_NEWS." WHERE ".multilocale_col_select('news_subject')." = '".$data['news_subject']."'");
 * Usage: $tree_data = dbquery_tree_full(DB_NEWS_CATS, "news_cat_id", "news_cat_parent", "order by ".language_column("news_cat_name"));
 */
function multilang_column($table_col) {
    $installed_lang = fusion_get_enabled_languages();
    $i = 1;
    $val_key = 2; // this is the first pair
    foreach ($installed_lang as $locale => $language) {
        if ($locale == LANGUAGE) {
            $val_key = $i * 2;
        }
        $i++;
    }

    return "replace(replace(replace(substring_index(substring_index($table_col, ';', ".$val_key."),':',-1), '\"', ''), '{%sc%}', ':') , '{%dq%}', '')";
}

// for sitelinks - not hierarchy
function getcategory($cat) {
    $presult = dbquery("SELECT link_id, link_name, link_order FROM ".DB_SITE_LINKS." WHERE link_id='$cat'");
    if (dbrows($presult) > 0) {
        $md[$cat] = "Menu Item Root";
        $result = dbquery("SELECT link_id, link_name FROM ".DB_SITE_LINKS." WHERE link_cat='$cat' ORDER BY link_order ASC");
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $link_id = $data['link_id'];
                $link_name = $data['link_name'];
                $md[$link_id] = "- ".$link_name."";
            }

            return $md;
        }
    }

    return [];
}

/**
 * Check if a PHPFusion table exists
 *
 * However you can pass the table name with or without prefix,
 * this function only check the prefixed tables of the PHPFusion
 *
 * @staticvar boolean[] $tables
 *
 * @param string $table The name of the table with or without prefix
 *                      Pass TRUE if you want to update the cached state of the table.
 *
 * @return boolean
 */
function db_exists($table) {
    if (strpos($table, DB_PREFIX) === FALSE) {
        $table = DB_PREFIX.$table;
    }
    $query = dbquery("SHOW TABLES LIKE '$table'");

    return boolval(dbrows($query));
}

/**
 * Determine whether column exists in a table
 *
 * @param           $table
 * @param           $column
 * @param bool|TRUE $add_prefix
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

    return (isset($table_config[$table][$column]) ? TRUE : FALSE);
}

/**
 * ID is required only for update mode.
 *
 * @param        $dbname
 * @param int    $current_order
 * @param        $order_col
 * @param int    $current_id
 * @param bool   $id_col
 * @param int    $current_category
 * @param bool   $cat_col
 * @param bool   $multilang
 * @param string $multilang_col
 * @param string $mode
 *
 * @return bool|mixed|PDOStatement|resource
 */
function dbquery_order($dbname, $current_order, $order_col, $current_id = 0, $id_col = FALSE, $current_category = 0, $cat_col = FALSE, $multilang = FALSE, $multilang_col = '', $mode = 'update') {

    $multilang_sql_1 = $multilang && $multilang_col ? "WHERE ".in_group($multilang_col, LANGUAGE) : '';
    $multilang_sql_2 = $multilang && $multilang_col ? "AND ".in_group($multilang_col, LANGUAGE) : '';

    if (!$current_order) {
        $current_order = dbresult(dbquery("SELECT MAX($order_col) FROM ".$dbname." ".$multilang_sql_1), 0) + 1;
    }

    switch ($mode) {
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
                \Defender::stop();
            }
            break;
        case 'update':
            if ($id_col && $current_id && $order_col && $current_order && $dbname) {
                $old_order = dbresult(dbquery("SELECT $order_col FROM ".$dbname." WHERE $id_col='".intval($current_id)."' $multilang_sql_2"), 0);
                if (!empty($current_category) && (!empty($cat_col))) {
                    if ($current_order > $old_order) {
                        $result = dbquery("UPDATE ".$dbname." SET $order_col=$order_col-1 WHERE $cat_col='".intval($current_category)."' AND $order_col>'$old_order' AND $order_col<='".intval($current_order)."' $multilang_sql_2");

                        return $result;
                    } else if ($current_order < $old_order) {
                        $result = dbquery("UPDATE ".$dbname." SET $order_col=$order_col+1 WHERE $cat_col='".intval($current_category)."' AND $order_col<'$old_order' AND $order_col>='".intval($current_order)."' $multilang_sql_2");

                        return $result;
                    } else {
                        return TRUE;
                    }
                } else {
                    if ($current_order > $old_order) {
                        $result = dbquery("UPDATE ".$dbname." SET $order_col=$order_col-1 WHERE $order_col>'$old_order' AND $order_col<='".intval($current_order)."' $multilang_sql_2");

                        return $result;
                    } else if ($current_order < $old_order) {
                        $result = dbquery("UPDATE ".$dbname." SET $order_col=$order_col+1 WHERE $order_col<'$old_order' AND $order_col>='".intval($current_order)."' $multilang_sql_2");

                        return $result;
                    } else {
                        return TRUE;
                    }
                }
            } else {
                \Defender::stop();
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
                \Defender::stop();
            }
            break;
        default:
            \Defender::stop();
    }

    return NULL;
}

// Array Makers
/**
 * To flatten ANY multidimensional array
 * Best used to flatten any hierarchy array data
 *
 * @param $result
 *
 * @return mixed
 */
function flatten_array($result) {
    return call_user_func_array('array_merge', $result);
}

/**
 * Short hand to explode strings to array by using commas
 * $array = construct_array("a,b,c,d,e,f,g");
 * $str_to_array = construct_array($str_with_commas); // See Geomap.inc.php
 *
 * @param      $string
 * @param bool $string2
 * @param bool $delimiter (symbol of delimiter)
 *
 * @return array
 */
function construct_array($string, $string2 = FALSE, $delimiter = FALSE) {
    // in event string is array. skips this.
    if (!is_array($string)) {
        if (empty($delimiter)) {
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
        //debug_print_backtrace();
        add_notice('info', "Debug notice: There is a string injected in construct_array() function!<br/>Please recheck source codes in this page.");
    }

    return NULL;
}

/**
 * To implode an array to string
 * Opposite of construct_array()
 *
 * @param $string
 * @param $delimiter
 *
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
            //else {
            //	$condition .= "($col_field LIKE '%$search_text%')";
            //}
        }
        $condition .= ($counter == count($the_sql) - 1) ? "  " : " OR ";
    }

    return $condition;
}
