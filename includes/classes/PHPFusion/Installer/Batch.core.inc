<?php

namespace PHPFusion\Installer;

/**
 * Class Batch_Core
 *
 * Batching of the installation process
 ** PHP-Fusion will compare existing tables with the package contents and build
 * according to identified requirements of a non-destructive approach.
 *
 * - Should the table is missing, the batch process will auto create the table.
 * - Should the table is found, and the batch process will check against table columns and create new column..
 * - Should the table is of the wrong type, to alter the type.
 *
 * The batch will also generate differences in a log in the end of the batch run.
 *
 * @package PHPFusion\Installer\Lib
 */
class Batch_Core extends Install_Core {

    const FUSION_TABLE_COLLATION = "ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
    const INSERT_STATEMENT = "INSERT INTO {%table%} {%key%} VALUES {%values%}";
    const UPDATE_STATEMENT = "UPDATE {%table%} SET {%values%} WHERE {%where%}";

    /*
     * Defines the PHP-Fusion Package and to be developed with the PHP-Fusion sql-handler
     * http://dev.mysql.com/doc/refman/5.7/en/show-columns.html
     * http://dev.mysql.com/doc/refman/5.5/en/data-types.html
     * http://dev.mysql.com/doc/refman/5.5/en/create-table.html
     * - Latest build is 0902
     *
     * Array key as table name
     * Array values as field_name and field types
     *
     * Field Type Array Indexes as following:
     * - type : the type of the column
     * - length: the length/values of the column
     * - default: default values if defined
     * - null: TRUE if is null (default not null)
     * - auto_increment - 1
     * - key - 1 for Unique Primary Key (Non Clustered Index), 2 for Key (Clustered Index)
     * - index - TRUE if index (primary key do not need to be indexed)
     * - unsigned - TRUE if column is unsigned (default no unsigned)

     */

    /*
     * Note on types assignment:
     * tinyint: 1 byte, -128 to +127 / 0 to 255 (unsigned)
     * smallint: 2 bytes, -32,768 to +32,767 / 0 to 65,535 (unsigned)
     * mediumint: 3 bytes, -8,388,608 to 8,388,607 / 0 to 16,777,215 (unsigned)
     * int/integer: 4 bytes, -2,147,483,648 to +2,147,483,647 / 0 to 4,294,967,295 (unsigned)
     * bigint: 8 bytes, -9,223,372,036,854,775,808 to 9,223,372,036,854,775,807 / 0 to 18,446,744,073,709,551,615 (unsigned)
     * The "unsigned" types are only available in MySQL, and the rest just use the signed ranges, with one notable exception:
     * tinyint in SQL Server is unsigned and has a value range of 0 to 255
     */
    private static $batch = NULL;
    private static $table_name = '';
    private static $table_cols = '';
    private static $sql_createTable = "CREATE TABLE {%table%} ({%table_attr%}) {%collation%}";

    /*
     * Generate the statements required
     */
    private static $sql_alterTable = "ALTER TABLE {%table%}";
    private static $required_default = array();
    private static $sql_createStatement = "{%col_name%}{%type%}{%length%}{%unsigned%}{%null%}{%default%}{%auto_increment%}";

    /*
     * The batch runtime will generate two kinds of results.
     * It runs silently and does not return anything except generation $batched_results and $batch_updated_results;
     * Therefore, it shall return last state of this instance, so we can fetch its last state in the core installer
     */
    private static $runtime_results = NULL;

    public static function getInstance() {
        if (self::$batch === NULL) {
            self::$batch = new static();
            self::$required_default = array_flip(['INT', 'BIGINT', 'MEDIUMINT', 'SMALLINT', 'TINYINT']);
        }

        return self::$batch;
    }

    /**
     * @param null $key
     * $key indexes:
     * created, updated, deleted
     * @return null
     */
    public function batch_runtime($key = NULL) {

        if (self::$runtime_results === NULL) {

            require_once CLASSES."PHPFusion/Installer/Lib/Core.tables.inc";
            require_once CLASSES."PHPFusion/Installer/Lib/Core.settings.inc";

            if (dbconnect(self::$connection['db_host'], self::$connection['db_user'], self::$connection['db_pass'], self::$connection['db_name'],
                          TRUE)) {

                foreach (get_core_tables() as self::$table_name => self::$table_cols) {
                    // simplify ternary
                    if (!defined('DB_PREFIX')) {
                        define('DB_PREFIX', self::$db_prefix);
                    }
                    if (db_exists(self::$db_prefix.self::$table_name)) {
                        // This is where upgrade is
                        // executing deep scan - check tables and check columns
                        // calculate changes and generate line diff, mutate $table_attr
                        //$mode = 'alter';
                        //$statement_type = self::$sql_alterStatement;

                        // check the 'insert' and 'update'
                        // must have this table name.

                        // to batch inserts -- need dbquery check.
                        //if (!empty($this->batch_insert_rows())) {
                        //self::$runtime_results['insert'][self::$table_name] = $this->batch_insert_rows();
                        //}

                    } else {

                        self::$runtime_results['create'][self::$table_name] = $this->batch_create_table();
                        // what is the languages to set? later to set
                        if (!empty($this->batch_insert_rows())) {
                            self::$runtime_results['insert'][self::$table_name] = $this->batch_insert_rows();
                        }

                    }
                }
            } else {
                // failing to connect will result in an installer crash.
                exit('Illegal operations');
            }
        }

        return ($key != NULL && isset(self::$runtime_results[$key]) ? self::$runtime_results[$key] : NULL);
    }

    /*
     * Non mutator static interpretation of the table packages
     * pure straightforward get default inserts only
     */

    private function batch_create_table() {
        // No table found, just create the table as new
        $line = array();
        $keys = array();
        $statement_type = self::$sql_createStatement;

        if (!empty(self::$table_cols)) {

            foreach (self::$table_cols as $col_name => $col_attr) {

                // Register column primary_keys and keys
                if (isset($col_attr['key'])) {
                    $keys[$col_attr['key']] = $col_name;
                }

                // Default Attr
                $default_create = '';
                if (isset($col_attr['default']) || isset(self::$required_default[$col_attr['type']]) && empty($col_attr['auto_increment'])) {
                    $default_create = 'DEFAULT \'0\'';
                    if (!empty($col_attr['default'])) {
                        $default_create = 'DEFAULT \''.$col_attr['default'].'\'';
                    }
                }

                $unsigned = '';
                $auto_increment = '';
                if (!empty($col_attr['unsigned']) || !empty($col_attr['auto_increment'])) {
                    $unsigned = 'UNSIGNED ';
                    if (!empty($col_attr['auto_increment'])) {
                        $auto_increment = 'AUTO_INCREMENT';
                    }
                }

                // Generate lines
                $line[] = trim(strtr($statement_type, [
                    '{%col_name%}' => $col_name." ",
                    '{%type%}' => $col_attr['type'],
                    '{%length%}' => (isset($col_attr['length']) ? "(".$col_attr['length'].") " : ''), // TEXT dont have length
                    '{%default%}' => $default_create." ",
                    '{%null%}' => (isset($col_attr['null']) && $col_attr['null'] ? ' NULL ' : ' NOT NULL '),
                    '{%unsigned%}' => $unsigned,
                    '{%auto_increment%}' => $auto_increment,
                ]));
            }

            if (!empty($keys)) {
                foreach ($keys as $key_type => $key_col_name) {
                    $line[] = $key_type > 1 ? "KEY $key_col_name ($key_col_name)" : "PRIMARY KEY ($key_col_name)";
                }
            }
        }

        return strtr(self::$sql_createTable, [
            '{%table%}' => self::$db_prefix.self::$table_name,
            '{%table_attr%}' => implode(',', $line),
            '{%collation%}' => Batch_Core::FUSION_TABLE_COLLATION
        ]);
    }

    private function batch_insert_rows() {


        if ($table_rows = get_table_rows(self::$table_name, self::$locale)) {
            if (isset($table_rows['insert'])) {
                // get column pattern
                $key = "`".implode("`, `", array_keys($table_rows['insert'][0]))."`";
                foreach ($table_rows['insert'] as $count => $inserts) {
                    /*
                    $rows[$count] = strtr(self::INSERT_STATEMENT, [
                        '{%table%}' => self::$db_prefix.self::$table_name,
                        '{%key%}'   =>  "`".implode("`,`", array_keys($inserts))."`",
                        '{%values%}'   =>  "'".implode("','", array_values($inserts))."'"
                    ]);
                    */
                    $values[] = "('".implode("', '", array_values($inserts))."')";
                }

                // return this
                return strtr(self::INSERT_STATEMENT, [
                    '{%table%}' => self::$db_prefix.self::$table_name,
                    '{%key%}' => "($key)",
                    '{%values%}' => implode(",\n", array_values($values))
                ]);
            }
        }

        return NULL;
    }

    /*
     * Handle core codes insertions - is inside foreach.
     */

    private function batch_update_table() {

    }

}