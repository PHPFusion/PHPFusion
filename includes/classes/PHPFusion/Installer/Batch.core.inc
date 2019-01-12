<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Batch.core.inc
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

    const PROGRESS = "setTimeout(
                    function(){
                        $('.progress-bar').animate({
                            width: '{%pcg%}%'
                            }, {%progress_time%});
                            {%next%}
                            $('#content').html('{%content%}');
                            $('#pcg').prop('Counter', {%last_count%}).animate({Counter: {%this_count%} }, {
                            duration: 0,
                                easing: 'swing',
                                step: function (now) {
                                    $(this).text(Math.ceil(now));
                            }
                            });
                    }, {%microtime%}
                    );
                    ";
    const TERMINATE_PROGRESS = "$('.progress-bar').addClass('progress-bar-warning').";

    const FUSION_TABLE_COLLATION = "ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    const CREATE_TABLE_STATEMENT = "CREATE TABLE {%table%} ({%table_attr%}) {%collation%}";

    // Column name and datatypes
    const TABLE_ATTR_STATEMENT = "{%col_name%}{%type%}{%length%}{%unsigned%}{%null%}{%default%}{%auto_increment%}";

    // Adding missing column - we do not need to check column order
    const ADD_COLUMN_STATEMENT = "ALTER TABLE {%table%} ADD COLUMN {%table_attr%} AFTER {%column_before%}"; // we do not need drop column.

    // Modification of column data-types
    const ALTER_COLUMN_STATEMENT = "ALTER TABLE {%table%} MODIFY COLUMN {%table_attr%}"; // we do not need drop column.

    const INSERT_STATEMENT = "INSERT INTO {%table%} {%key%} VALUES {%values%}";

    const UPDATE_STATEMENT = "UPDATE {%table%} SET {%values%} WHERE {%where%}";

    const ADD_INDEX_STATEMENT = "ALTER TABLE {%table%} ADD INDEX {%column_name%} ({%column_name%})";

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
    private static $table_cols = [];


    /*
     * Generate the statements required
     */

    private static $required_default = [];

    /*
     * The batch runtime will generate two kinds of results.
     * It runs silently and does not return anything except generation $batched_results and $batch_updated_results;
     * Therefore, it shall return last state of this instance, so we can fetch its last state in the core installer
     */
    private static $runtime_results = NULL;

    private static $schema_storage = [];

    /*
     * Non mutator static interpretation of the table packages
     * pure straightforward get default inserts only
     */
    /**
     * Use Infusions Core installer to perform upgrades
     */
    private static $upgrade_runtime = [];
    private $js = [];

    /**
     * Return the instance for the Batcher
     *
     * @return static
     */
    public static function getInstance() {

        if (self::$batch === NULL) {
            self::$batch = new static();
            self::$required_default = array_flip(['INT', 'BIGINT', 'MEDIUMINT', 'SMALLINT', 'TINYINT']);
            require_once CLASSES."PHPFusion/Installer/Lib/Core.tables.inc";
            require_once CLASSES."PHPFusion/Installer/Lib/Core.settings.inc";
        }

        return self::$batch;
    }

    /**
     * Get the runtime results
     *
     * @param null $key
     *
     * @return null
     */
    public function batch_runtime($key = NULL) {

        if (self::$runtime_results === NULL) {

            if (dbconnect(self::$connection['db_host'], self::$connection['db_user'], self::$connection['db_pass'], self::$connection['db_name'], TRUE)) {

                foreach (get_core_tables(self::$localeset) as self::$table_name => self::$table_cols) {

                    if (db_exists(self::$connection['db_prefix'].self::$table_name)) {
                        /*
                         * Existing Installation
                         */
                        $this->check_existing_table(); //where is the one that runs upgrade?

                    } else {
                        /*
                         * New Installation
                         */
                        $this->create_new_table();
                    }
                }

            } else {
                // failing to connect will result in an installer crash.
                exit('Illegal operations');
            }

        } // end runtime null

        return ($key != NULL && isset(self::$runtime_results[$key]) ? self::$runtime_results[$key] : NULL);
    }

    /**
     * When table exists, need to be checked for data-types consistencies and column name consistencies
     */
    private function check_existing_table() {

        if ($schema_check = $this->get_table_schema(self::$table_name)) {

            // Iterate checks on every column of the table for consistency
            foreach (self::$table_cols as $col_name => $col_attr) {

                if (!isset($last_column_name)) {
                    $last_column_name = key(self::$table_cols);
                }
                /*
                 * If column exist in table, compare with column
                 * If column does not exist, add the column
                 */
                if (isset($schema_check[$col_name])) {

                    // has column and proceed to compare
                    $schema_compare[self::$table_name][$col_name] = array_diff($schema_check[$col_name], $col_attr);

                    // There is a difference in column data-types structures
                    if (!empty($schema_compare[self::$table_name][$col_name])) {

                        // Register column primary_keys and keys - @todo: have a check on this as well
                        if (isset($col_attr['key'])) {
                            $keys[$col_attr['key']] = $col_name;
                        }

                        self::$runtime_results['alter_column'][self::$table_name][$col_name] = strtr(self::ALTER_COLUMN_STATEMENT, [
                            '{%table%}'      => self::$connection['db_prefix'].self::$table_name,
                            '{%table_attr%}' => $this->get_table_attr($col_name, $col_attr)
                        ]);
                    }

                } else {

                    self::$runtime_results['add_column'][self::$table_name][$col_name] = strtr(self::ADD_COLUMN_STATEMENT, [
                        '{%table%}'         => self::$connection['db_prefix'].self::$table_name,
                        '{%table_attr%}'    => $this->get_table_attr($col_name, $col_attr),
                        '{%column_before%}' => $last_column_name
                    ]);

                }
                $last_column_name = $col_name;
            }
        }

    }

    /**
     * Fetches Existing Database Table Schema for comparisons
     *
     * @param $table_name
     *
     * @return null
     */
    private function get_table_schema($table_name) {

        if (empty(self::$schema_storage[$table_name])) {

            $schema_result = dbquery("DESC ".self::$connection['db_prefix'].$table_name);

            if (dbrows($schema_result)) {

                while ($schemaData = dbarray($schema_result)) {
                    $schema = []; //resets
                    // need to format the type and
                    if (isset($schemaData['Type'])) {
                        $schema_type = preg_split('/\s+/', $schemaData['Type']); // for unsigned

                        // Get Auto Increments
                        if ($schemaData['Extra'] == "auto_increment") {
                            $schema['auto_increment'] = TRUE;
                        }

                        if (!empty($schema_type[1]) && $schema_type[1] == 'unsigned' && !isset($schema['auto_increment'])) {
                            $schema['unsigned'] = TRUE;
                        }

                        $regex = "/([a-zA-Z\\s]*)\\((.*)\\)$/iu";
                        preg_match($regex, $schema_type[0], $matches);

                        if (!empty($matches)) {

                            if (isset($matches[1])) {
                                $schema['type'] = strtoupper($matches[1]);
                            }
                            if (isset($matches[2])) {
                                $schema['length'] = $matches[2];
                            }

                        } else {
                            // This field has no Length to extract
                            $schema['type'] = strtoupper($schema_type[0]);
                        }
                    }
                    // Get default
                    if (!empty($schemaData['Default'])) {
                        $schema['default'] = $schemaData['Default'];
                    }
                    // Get key
                    if (!empty($schemaData['Key'])) {
                        $schema['key'] = $schemaData['Key'] == "PRI" ? 1 : 2;
                    }

                    self::$schema_storage[$table_name][$schemaData['Field']] = $schema;
                }

                return self::$schema_storage[$table_name];
            }

        }

        return NULL;
    }

    /**
     * Get table column data-type attributes
     *
     * @param $col_name
     * @param $col_attr
     *
     * @return string
     */
    private function get_table_attr($col_name, $col_attr) {

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
        return trim(strtr(self::TABLE_ATTR_STATEMENT, [
            '{%col_name%}'       => $col_name." ",
            '{%type%}'           => $col_attr['type'],
            '{%length%}'         => (isset($col_attr['length']) ? "(".$col_attr['length'].") " : ''), // TEXT dont have length
            '{%default%}'        => $default_create." ",
            '{%null%}'           => (isset($col_attr['null']) && $col_attr['null'] ? ' NULL ' : ' NOT NULL '),
            '{%unsigned%}'       => $unsigned,
            '{%auto_increment%}' => $auto_increment,
        ]));
    }

    /**
     * Auto function - Table does not exist, and create new table and rows
     */
    private function create_new_table() {
        self::$runtime_results['create'][self::$table_name] = $this->batch_create_table();
        // Will only set and create on current locale only
        $batch_inserts = self::batch_insert_rows(self::$table_name, self::$localeset);
        if (!empty($batch_inserts)) {
            self::$runtime_results['insert'][self::$table_name] = $batch_inserts;
        }
    }

    /**
     * Create codes generation
     *
     * @return string
     */
    private function batch_create_table() {
        // No table found, just create the table as new
        $line = [];
        $keys = [];
        $statement_type = self::TABLE_ATTR_STATEMENT;

        if (!empty(self::$table_cols)) {

            foreach (self::$table_cols as $col_name => $col_attr) {

                // Register column primary_keys and keys
                if (isset($col_attr['key'])) {
                    $keys[$col_attr['key']] = $col_name;
                }
                // Register column full text
                if (!empty($col_attr['full_text'])) {
                    $full_texts[] = $col_name;
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
                    '{%col_name%}'       => $col_name." ",
                    '{%type%}'           => $col_attr['type'],
                    '{%length%}'         => (isset($col_attr['length']) ? "(".$col_attr['length'].") " : ''), // TEXT dont have length
                    '{%default%}'        => $default_create." ",
                    '{%null%}'           => (isset($col_attr['null']) && $col_attr['null'] ? ' NULL ' : ' NOT NULL '),
                    '{%unsigned%}'       => $unsigned,
                    '{%auto_increment%}' => $auto_increment,
                ]));
            }

            if (!empty($keys)) {
                foreach ($keys as $key_type => $key_col_name) {
                    $line[] = $key_type > 1 ? "KEY $key_col_name ($key_col_name)" : "PRIMARY KEY ($key_col_name)";
                }
            }

            if (!empty($full_texts)) {
                $line[] = "FULLTEXT(".implode(',', $full_texts).")";
            }

        }

        return strtr(self::CREATE_TABLE_STATEMENT, [
            '{%table%}'      => self::$connection['db_prefix'].self::$table_name,
            '{%table_attr%}' => implode(', ', $line),
            '{%collation%}'  => Batch_Core::FUSION_TABLE_COLLATION
        ]);

    }

    /**
     * Add default row records
     *
     * @param $table_name
     * @param $localeset
     *
     * @return null|string
     */
    public static function batch_insert_rows($table_name, $localeset) {

        if ($table_rows = get_table_rows($table_name, $localeset)) {
            if (isset($table_rows['insert'])) {
                $values = [];
                // get column pattern
                $key = "`".implode("`, `", array_keys($table_rows['insert'][0]))."`";
                foreach ($table_rows['insert'] as $count => $inserts) {
                    $values[] = "('".implode("', '", array_values($inserts))."')";
                }

                // return this
                return strtr(self::INSERT_STATEMENT, [
                    '{%table%}'  => DB_PREFIX.$table_name,
                    '{%key%}'    => "($key)",
                    '{%values%}' => implode(",\n", array_values($values))
                ]);
            }
        }

        return NULL;
    }

    /**
     * Checks for Upgrade Files
     * @return array
     */
    public function check_upgrades() {

        if (empty(self::$upgrade_runtime)) {

            if (version_compare(self::BUILD_VERSION, fusion_get_settings('version'), ">")) {

                // find the correct version to do
                $upgrade_folder_path = BASEDIR."upgrade/";

                if (file_exists($upgrade_folder_path)) {

                    $upgrade_files = makefilelist($upgrade_folder_path, ".|..|index.php", TRUE);

                    if (!empty($upgrade_files) && is_array($upgrade_files)) {

                        foreach ($upgrade_files as $upgrade_file) {

                            $filename = rtrim($upgrade_file, 'upgrade.inc');

                            if (version_compare($filename, fusion_get_settings('version'), ">")) {
                                /*
                                 * Use Infusions Core to load upgrade statements
                                 */
                                $upgrades = self::load_upgrade($upgrade_folder_path, $upgrade_folder_path.$upgrade_file);

                                // Remove unnecessary APIs
                                unset($upgrades['title']);
                                unset($upgrades['name']);
                                unset($upgrades['url']);
                                unset($upgrades['description']);
                                unset($upgrades['version']);
                                unset($upgrades['developer']);
                                unset($upgrades['email']);
                                unset($upgrades['weburl']);
                                unset($upgrades['folder']);
                                unset($upgrades['image']);
                                unset($upgrades['status']);

                                self::$upgrade_runtime[$filename] = $upgrades;
                            }

                        }
                    }
                }

            }
        }

        return self::$upgrade_runtime;
    }

    public function getProgress() {
        return end($this->js['jquery']);
    }

    public function ProgressHasError() {
        return (!empty($this->js['error'])) ? TRUE : FALSE;
    }

}
