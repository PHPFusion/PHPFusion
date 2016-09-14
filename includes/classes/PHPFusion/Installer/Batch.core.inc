<?php

namespace PHPFusion\Installer\Lib;

use PHPFusion\Installer\Install_Core;

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

    const COLLATION = "ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
    private static $batch = NULL;

    public static function getInstance() {
        if (self::$batch === NULL) {
            self::$batch = new static();
        }

        return self::$batch;
    }

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
     * - null: true if is null (default not null)
     * - auto_increment - 1
     * - key - 1 for Unique Primary Key (Non Clustered Index), 2 for Key (Clustered Index)
     * - index - true if index (primary key do not need to be indexed)
     * - unsigned - true if column is unsigned (default no unsigned)

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
    public static function get_core_tables() {
        /*
         * Modeled for compositing table sql comparison for upgrade/reinstall/and install friendly
         * rather than maintaining files after files
         */
        $table_package['admin'] = [
            'admin_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'auto_increment' => TRUE,
                'key' => 1, //PRIMARY KEY (admin_id)
            ], //admin_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'admin_rights' => [
                'type' => 'CHAR',
                'length' => 4
            ], //admin_rights CHAR(4) NOT NULL DEFAULT '',
            'admin_image' => [
                'type' => 'VARCHAR',
                'length' => 50
            ], //admin_image VARCHAR(50) NOT NULL DEFAULT '',
            'admin_title' => [
                'type' => 'VARCHAR',
                'length' => 50
            ], //admin_title VARCHAR(50) NOT NULL DEFAULT '',
            'admin_link' => [
                'type' => 'VARCHAR',
                'length' => 100,
                'default' => 'reserved',
            ], //admin_link VARCHAR(100) NOT NULL DEFAULT 'reserved',
            'admin_page' => [
                'type' => 'TINYINT',
                'length' => 1,
                'default' => 1,
                'unsigned' => TRUE,
            ], //admin_page TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
        ];
        $table_package['mlt_tables'] = [
            'mlt_rights' => [
                'type' => 'CHAR',
                'length' => 4,
                'key' => 1 //PRIMARY KEY (mlt_rights)
            ], //mlt_rights CHAR(4) NOT NULL DEFAULT '',
            'mlt_title' => [
                'type' => 'VARCHAR',
                'length' => 50,
            ], //mlt_title VARCHAR(50) NOT NULL DEFAULT '',
            'mlt_status' => [
                'type' => 'VARCHAR',
                'length' => 50,
            ] //mlt_status VARCHAR(50) NOT NULL DEFAULT '',
        ];
        $table_package['language_sessions'] = [
            'user_ip' => [
                'type' => 'VARCHAR',
                'length' => 20,
                'default' => '0.0.0.0',
            ], //user_ip VARCHAR(20) NOT NULL DEFAULT '0.0.0.0',
            'user_language' => [
                'type' => 'VARCHAR',
                'length' => 50,
                'default' => filter_input(INPUT_POST, 'localeset')
            ], //user_language VARCHAR(50) NOT NULL DEFAULT '".filter_input(INPUT_POST, 'localeset')."',
            'user_datestamp' => [
                'type' => 'INT',
                'length' => 10,
                'default' => '0',
            ] //user_datestamp INT(10) NOT NULL default '0'
        ];
        $table_package['admin_resetlog'] = [
            'reset_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'unsigned' => TRUE,
                'auto_increment' => TRUE,
                'key' => 1, //PRIMARY KEY (reset_id)
            ], //reset_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
            'reset_admin_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'default' => 1,
                'unsigned' => TRUE
            ], //reset_admin_id mediumint(8) unsigned NOT NULL default '1',
            'reset_timestamp' => [
                'type' => 'INT',
                'length' => 10,
                'unsigned' => TRUE,
                'default' => '0',
            ], //reset_timestamp int(10) unsigned NOT NULL default '0',
            'reset_success' => [
                'type' => 'TEXT'
            ], //reset_sucess text NOT NULL,
            'reset_failed' => [
                'type' => 'TEXT'
            ], //reset_failed text NOT NULL,
            'reset_admins' => [
                'type' => 'VARCHAR',
                'length' => 8,
                'default' => '0',
            ], //reset_admins varchar(8) NOT NULL default '0',
            'reset_reason' => [
                'type' => 'VARCHAR',
                'length' => 255
            ] //reset_reason varchar(255) NOT NULL,
        ];
        $table_package['bbcodes'] = [
            'bbcode_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'unsigned' => TRUE,
                'auto_increment' => TRUE,
                'key' => 1, //PRIMARY KEY (bbcode_id),
            ], //bbcode_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'bbcode_name' => [
                'type' => 'VARCHAR',
                'length' => 20,
            ], //bbcode_name VARCHAR(20) NOT NULL DEFAULT '',
            'bbcode_order' => [
                'type' => 'SMALLINT',
                'length' => 5,
                'key' => 2, //KEY bbcode_order (bbcode_order)
                'unsigned' => TRUE,
            ], //bbcode_order SMALLINT(5) UNSIGNED NOT NULL,
        ];
        $table_package['blacklist'] = [
            'blacklist_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'unsigned' => TRUE,
                'auto_increment' => TRUE,
                'key' => 1, //PRIMARY KEY (blacklist_id),
            ], //blacklist_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'blacklist_user_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'unsigned' => TRUE,
                'default' => '0',
            ], //blacklist_user_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'blacklist_ip' => [
                'type' => 'VARCHAR',
                'length' => 45,
            ], //blacklist_ip VARCHAR(45) NOT NULL DEFAULT '',
            'blacklist_ip_type' => [
                'type' => 'TINYINT',
                'length' => 1,
                'default' => 4,
                'key' => 2 //KEY blacklist_ip_type (blacklist_ip_type)
            ], //blacklist_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
            'blacklist_email' => [
                'type' => 'VARCHAR',
                'length' => 100
            ],//blacklist_email VARCHAR(100) NOT NULL DEFAULT '',
            'blacklist_reason' => [
                'type' => 'TEXT'
            ],//blacklist_reason TEXT NOT NULL,
            'blacklist_datestamp' => [
                'type' => 'INT',
                'length' => 10,
            ] //blacklist_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
        ];
        $table_package['captcha'] = [
            'captcha_datestamp' => [
                'type' => 'INT',
                'length' => 10,
                'key' => 2, //KEY captcha_datestamp (captcha_datestamp)
            ], //captcha_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
            'captcha_ip' => [
                'type' => 'VARCHAR',
                'length' => 45
            ], //captcha_ip VARCHAR(45) NOT NULL DEFAULT '',
            'captcha_ip_type' => [
                'type' => 'TINYINT',
                'length' => 1,
                'default' => 4
            ], //captcha_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
            'captcha_encode' => [
                'type' => 'VARCHAR',
                'length' => 32
            ], //captcha_encode VARCHAR(32) NOT NULL DEFAULT '',
            'captcha_string' => [
                'type' => 'VARCHAR',
                'length' => 15
            ] //captcha_string VARCHAR(15) NOT NULL DEFAULT '',
        ];
        $table_package['custom_pages'] = [
            'page_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'auto_increment' => TRUE,
                'key' => 1 //PRIMARY KEY (page_id)
            ], //page_id MEDIUMINT(8) NOT NULL AUTO_INCREMENT,
            'page_cat' => [
                'type' => 'MEDIUMINT',
                'length' => 8
            ], //page_cat MEDIUMINT(8) NOT NULL DEFAULT '0',
            'page_link_cat' => [
                'type' => 'MEDIUMINT',
                'length' => 9,
                'unsigned' => TRUE,
            ], // page_link_cat MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0',
            'page_title' => [
                'type' => 'VARCHAR',
                'length' => 200
            ], // page_title VARCHAR(200) NOT NULL DEFAULT '',
            'page_access' => [
                'type' => 'TINYINT',
                'length' => 4,
            ], //page_access TINYINT(4) NOT NULL DEFAULT '0',
            'page_content' => [
                'type' => 'TEXT'
            ], //page_content TEXT NOT NULL,
            'page_keywords' => [
                'type' => 'VARCHAR',
                'length' => 250,
            ], // page_keywords VARCHAR(250) NOT NULL DEFAULT '',
            'page_status' => [
                'type' => 'SMALLINT',
                'length' => 1,
            ], //page_status SMALLINT(1) NOT NULL DEFAULT '0',
            'page_user' => [
                'type' => 'MEDIUMINT',
                'length' => 9,
            ], //page_user MEDIUMINT(9) NOT NULL DEFAULT '0',
            'page_datestamp' => [
                'type' => 'INT',
                'length' => 10,
            ], //page_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
            'page_language' => [
                'type' => 'VARCHAR',
                'length' => 255,
                'default' => filter_input(INPUT_POST, 'localeset'),
            ], //page_language VARCHAR(255) NOT NULL DEFAULT '".filter_input(INPUT_POST, 'localeset')."',
            'page_grid_id' => [
                'type' => 'MEDIUMINT',
                'length' => 9,
            ], //page_grid_id MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0',
            'page_content_id' => [
                'type' => 'MEDIUMINT',
                'length' => 9,
            ], //page_content_id MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0',
            'page_left_panel' => [
                'type' => 'TINYINT',
                'length' => 1,
            ], //page_left_panel TINYINT(1) NOT NULL DEFAULT '0',
            'page_right_panel' => [
                'type' => 'TINYINT',
                'length' => 1,
            ], //page_right_panel TINYINT(1) NOT NULL DEFAULT '0',
            'page_header_panel' => [
                'type' => 'TINYINT',
                'length' => 1,
            ], //page_header_panel TINYINT(1) NOT NULL DEFAULT '0',
            'page_footer_panel' => [
                'type' => 'TINYINT',
                'length' => 1,
            ], //page_footer_panel TINYINT(1) NOT NULL DEFAULT '0',
            'page_top_panel' => [
                'type' => 'TINYINT',
                'length' => 1,
            ], //page_top_panel TINYINT(1) NOT NULL DEFAULT '0',
            'page_bottom_panel' => [
                'type' => 'TINYINT',
                'length' => 1,
            ], //page_bottom_panel TINYINT(1) NOT NULL DEFAULT '0',
        ];
        $table_package['custom_pages_grid'] = [
            'page_id' => [
                'type' => 'MEDIUMINT',
                'length' => 9,
                'key' => 2,
            ], //page_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'page_grid_id' => [
                'type' => 'MEDIUMINT',
                'length' => 9,
                'auto_increment' => TRUE,
                'key' => 1
            ], //page_grid_id MEDIUMINT(9) UNSIGNED  NOT NULL AUTO_INCREMENT,
            'page_grid_container' => [
                'type' => 'TINYINT',
                'length' => 1,
            ], // page_grid_container TINYINT(1) NOT NULL DEFAULT '0',
            'page_grid_column_count' => [
                'type' => 'TINYINT',
                'length' => 1
            ], // page_grid_column_count TINYINT(1) NOT NULL DEFAULT '0',
            'page_grid_html_id' => [
                'type' => 'VARCHAR',
                'length' => 50,
            ], //page_grid_html_id VARCHAR(50) NOT NULL DEFAULT '',
            'page_grid_class' => [
                'type' => 'VARCHAR',
                'length' => 100
            ], //page_grid_class VARCHAR(100) NOT NULL DEFAULT '',
            'page_grid_order' => [
                'type' => 'TINYINT',
                'length' => 5,
            ], // page_grid_order TINYINT(5) NOT NULL DEFAULT '0',
        ];
        $table_package['custom_pages_content'] = [
            'page_id' => [
                'type' => 'MEDIUMINT',
                'length' => 9,
                'key' => 2 //KEY page_id (page_id),
            ], //page_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'page_grid_id' => [
                'type' => 'MEDIUMINT',
                'length' => 9,
                'key' => 2 //KEY page_grid_id (page_grid_id)
            ], //page_grid_id MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0',
            'page_content_id' => [
                'type' => 'MEDIUMINT',
                'length' => 9,
                'auto_increment' => TRUE,
                'key' => 1 //PRIMARY KEY (page_content_id),
            ], //page_content_id MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
            'page_content_type' => [
                'type' => 'VARCHAR',
                'length' => 50
            ], //page_content_type VARCHAR(50) NOT NULL DEFAULT '',
            'page_content' => [
                'type' => 'TEXT',
            ], //page_content TEXT NOT NULL,
            'page_options' => [
                'type' => 'TEXT',
            ], //page_options TEXT NOT NULL,
            'page_content_order' => [
                'type' => 'TINYINT',
                'length' => 5
            ], //page_content_order TINYINT(5) NOT NULL DEFAULT '0',
            'page_widget' => [
                'type' => 'VARCHAR',
                'length' => 100
            ], //page_widget VARCHAR(100) NOT NULL DEFAULT '',
        ];
        $table_package['comments'] = [
            'comment_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'auto_increment' => TRUE,
                'key' => 1
            ], //comment_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'comment_item_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8
            ], //comment_item_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'comment_type' => [
                'type' => 'CHAR',
                'length' => 4
            ], //comment_type CHAR(4) NOT NULL DEFAULT '',
            'comment_cat' => [
                'type' => 'MEDIUMINT',
                'length' => 8
            ], //comment_cat MEDIUMINT(8) NOT NULL DEFAULT '0',
            'comment_name' => [
                'type' => 'VARCHAR',
                'length' => 100
            ],//comment_name VARCHAR(50) NOT NULL DEFAULT '',
            'comment_message' => [
                'type' => 'TEXT',
            ], //comment_message TEXT NOT NULL,
            'comment_datestamp' => [
                'type' => 'INT',
                'length' => 10,
                'key' => 2 //KEY comment_datestamp (comment_datestamp)
            ], //comment_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
            'comment_ip' => [
                'type' => 'VARCHAR',
                'length' => 45
            ], //comment_ip VARCHAR(45) NOT NULL DEFAULT '',
            'comment_ip_type' => [
                'type' => 'TINYINT',
                'default' => 4,
                'length' => 1
            ], //comment_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
            'comment_hidden' => [
                'type' => 'TINYINT',
                'length' => 1
            ], //comment_hidden TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
        ];
        $table_package['errors'] = [
            'error_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'auto_increment' => TRUE,
                'key' => 1
            ], //error_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
            'error_level' => [
                'type' => 'SMALLINT',
                'length' => 5,
                'unsigned' => TRUE,
            ], //error_level smallint(5) unsigned NOT NULL,
            'error_message' => [
                'type' => 'TEXT',
            ], //error_message text NOT NULL,
            'error_file' => [
                'type' => 'VARCHAR',
                'length' => 255
            ], //error_file varchar(255) NOT NULL,
            'error_line' => [
                'type' => 'SMALLINT',
                'length' => 5
            ], // error_line smallint(5) NOT NULL,
            'error_page' => [
                'type' => 'VARCHAR',
                'length' => 200
            ], // error_page varchar(200) NOT NULL,
            'error_user_level' => [
                'type' => 'TINYINT',
                'length' => 4
            ], //error_user_level TINYINT(4) NOT NULL,
            'error_user_ip' => [
                'type' => 'VARCHAR',
                'length' => 45
            ], //error_user_ip varchar(45) NOT NULL default '',
            'error_user_ip_type' => [
                'type' => 'TINYINT',
                'length' => 1,
                'default' => 4
            ], //error_user_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
            'error_status' => [
                'type' => 'TINYINT',
                'length' => 1
            ], //error_status tinyint(1) NOT NULL default '0',
            'error_timestamp' => [
                'type' => 'INT',
                'length' => 10
            ] //error_timestamp int(10) NOT NULL,
        ];
        $table_package['flood_control'] = [
            'flood_ip' => [
                'type' => 'VARCHAR',
                'length' => 45
            ], //flood_ip VARCHAR(45) NOT NULL DEFAULT '',
            'flood_ip_type' => [
                'type' => 'TINYINT',
                'length' => 1,
                'default' => 4
            ], //flood_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
            'food_timestamp' => [
                'type' => 'INT',
                'length' => 10,
                'unsigned' => TRUE,
                'key' => 2 //KEY flood_timestamp (flood_timestamp)
            ], //flood_timestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
        ];
        $table_package['infusions'] = [
            'inf_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'auto_increment' => TRUE,
                'key' => 1 //PRIMARY KEY (inf_id)
            ], //inf_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'inf_title' => [
                'type' => 'VARCHAR',
                'length' => 100,
            ], //inf_title VARCHAR(100) NOT NULL DEFAULT '',
            'inf_folder' => [
                'type' => 'VARCHAR',
                'length' => 100,
            ], //inf_folder VARCHAR(100) NOT NULL DEFAULT '',
            'inf_version' => [
                'type' => 'VARCHAR',
                'length' => 10,
                'default' => '0'
            ], //inf_version VARCHAR(10) NOT NULL DEFAULT '0',
        ];
        $table_package['messages'] = [
            'message_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'auto_increment' => TRUE,
                'key' => 1 //PRIMARY KEY (message_id)
            ], // message_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'message_to' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'unsigned' => TRUE
            ], // message_to MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'message_from' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'unsigned' => TRUE
            ], //message_from MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'message_user' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'unsigned' => TRUE
            ], //message_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'message_subject' => [
                'type' => 'VARCHAR',
                'length' => 100,
            ], //message_subject VARCHAR(100) NOT NULL DEFAULT '',
            'message_message' => [
                'type' => 'TEXT'
            ], //message_message TEXT NOT NULL,
            'message_smileys' => [
                'type' => 'SMALLINT',
                'length' => 1,
            ], //message_smileys CHAR(1) NOT NULL DEFAULT '', @note: changed
            'message_read' => [
                'type' => 'SMALLINT',
                'length' => 1,
            ], //message_read TINYINT(1) UNSIGNED NOT NULL DEFAULT '0', @note: changed
            'message_datestamp' => [
                'type' => 'INT',
                'length' => 10,
                'unsigned' => TRUE,
                'key' => 2 //KEY message_datestamp (message_datestamp)
            ], //message_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
            'message_folder' => [
                'type' => 'TINYINT',
                'length' => 1,
            ] //message_folder TINYINT(1) UNSIGNED NOT NULL DEFAULT  '0',
        ];
        $table_package['messages_options'] = [
            'user_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'key' => 1 // PRIMARY KEY (user_id)
            ], //user_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'pm_email_notify' => [
                'type' => 'SMALLINT', //@note:changed
                'length' => 1,
            ], //pm_email_notify tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            'pm_save_sent' => [
                'type' => 'SMALLINT',
                'length' => 100,
            ], //pm_save_sent tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            'pm_inbox' => [
                'type' => 'SMALLINT',
                'length' => 5,
            ], //pm_inbox SMALLINT(5) UNSIGNED DEFAULT '0' NOT NULL, @note:changed
            'pm_savebox' => [
                'type' => 'SMALLINT',
                'length' => 5,
            ], // pm_savebox SMALLINT(5) UNSIGNED DEFAULT '0' NOT NULL, @note: changed
            'pm_sentbox' => [
                'type' => 'SMALLINT',
                'length' => 5,
            ] //pm_sentbox SMALLINT(5) UNSIGNED DEFAULT '0' NOT NULL,
        ];
        $table_package['new_users'] = [
            'user_code' => [
                'type' => 'VARCHAR',
                'length' => 40,
            ], //user_code VARCHAR(40) NOT NULL,
            'user_name' => [
                'type' => 'VARCHAR',
                'length' => 30,
            ], //user_name VARCHAR(30) NOT NULL,
            'user_email' => [
                'type' => 'VARCHAR',
                'length' => 100,
            ], //user_email VARCHAR(100) NOT NULL,
            'user_datestamp' => [
                'type' => 'INT',
                'length' => 10,
                'default' => '0',
                'key' => 2, //KEY user_datestamp (user_datestamp)
            ], //user_datestamp INT(10) UNSIGNED DEFAULT '0' NOT NULL,
            'user_info' => [
                'type' => 'TEXT'
            ], //user_info TEXT NOT NULL,
        ];
        $table_package['email_verify'] = [
            'user_id' => [
                'type' => 'VARCHAR',
                'length' => 32,
            ], //user_id MEDIUMINT(8) NOT NULL,
            'user_code' => [
                'type' => 'VARCHAR',
                'length' => 32,
            ], //user_code VARCHAR(32) NOT NULL,
            'user_email' => [
                'type' => 'VARCHAR',
                'length' => 100,
            ], //user_email VARCHAR(100) NOT NULL,
            'user_datestamp' => [
                'type' => 'INT',
                'length' => 10,
                'unsigned' => TRUE,
                'key' => 2 //KEY user_datestamp (user_datestamp)
            ], //user_datestamp INT(10) UNSIGNED DEFAULT '0' NOT NULL,
        ];
        $table_package['email_templates'] = [
            'template_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'auto_increment' => TRUE,
                'key' => 1 //PRIMARY KEY (template_id)
            ], //template_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'template_key' => [
                'type' => 'VARCHAR',
                'length' => 10,
            ], //template_key VARCHAR(10) NOT NULL,
            'template_format' => [
                'type' => 'VARCHAR',
                'length' => 10,
            ], //template_format VARCHAR(10) NOT NULL,
            'template_active' => [
                'type' => 'TINYINT',
                'length' => 1,
            ], //template_active TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            'template_name' => [
                'type' => 'VARCHAR',
                'length' => 300,
            ], //template_name VARCHAR(300) NOT NULL,
            'template_subject' => [
                'type' => 'TEXT',
            ], //template_subject TEXT NOT NULL,
            'template_content' => [
                'type' => 'TEXT'
            ], //template_content TEXT NOT NULL,
            'template_sender_name' => [
                'type' => 'VARCHAR',
                'length' => 30,
            ], //template_sender_name VARCHAR(30) NOT NULL,
            'template_sender_email' => [
                'type' => 'VARCHAR',
                'length' => 100,
            ], //template_sender_email VARCHAR(100) NOT NULL,
            'template_language' => [
                'type' => 'VARCHAR',
                'length' => 50,
            ] //template_language VARCHAR(50) NOT NULL,
        ];
        $table_package['ratings'] = [
            'rating_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'auto_increment' => TRUE,
                'key' => 1, //PRIMARY KEY (rating_id)
            ],//rating_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'rating_item_id' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'unsigned' => TRUE,
            ],//rating_item_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'rating_type' => [
                'type' => 'CHAR',
                'length' => 4,
                'default' => ''
            ],//rating_type CHAR(4) NOT NULL DEFAULT '',
            'rating_user' => [
                'type' => 'MEDIUMINT',
                'length' => 8,
                'unsigned' => TRUE,
            ],//rating_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'rating_vote' => [
                'type' => 'TINYINT',
                'length' => 1,
            ], //rating_vote TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            'rating_datestamp' => [
                'type' => 'INT',
                'length' => 10,
                'key' => 2 //@note:changed
            ], //rating_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
            'rating_ip' => [
                'type' => 'VARCHAR',
                'length' => 45,
                'default' => '',
            ], //rating_ip VARCHAR(45) NOT NULL DEFAULT '',
            'rating_ip_type' => [
                'type' => 'TINYINT',
                'length' => 1,
                'unsigned' => TRUE,
                'default' => 4
            ] //rating_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
        ];
        $table_package['online'] = [
            'online_user' => [
                'type' => 'VARCHAR',
                'length' => 50,
                'default' => ''
            ], //online_user VARCHAR(50) NOT NULL DEFAULT '',
            'online_ip' => [
                'type' => 'VARCHAR',
                'length' => 45,
                'default' => ''
            ], //online_ip VARCHAR(45) NOT NULL DEFAULT '',
            'online_ip_type' => [
                'type' => 'TINYINT',
                'length' => 1,
                'unsigned' => TRUE,
                'default' => 4
            ], //online_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
            'online_lastactive' => [
                'type' => 'INT',
                'length' => 10,
                'unsigned' => TRUE,
                'default' => '0'
            ] //online_lastactive INT(10) UNSIGNED NOT NULL DEFAULT '0'
        ];
        $table_package['panels'] = [
            '' => [
                'type' => '',
                'length' => 100,
                'key' => 1 //PRIMARY KEY (panel_id),
            ],//panel_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            '' => [
                'type' => '',
                'length' => 100,
            ],//panel_name VARCHAR(100) NOT NULL DEFAULT '',
            '' => [
                'type' => '',
                'length' => 100,
            ],//panel_filename VARCHAR(100) NOT NULL DEFAULT '',
            '' => [
                'type' => '',
                'length' => 100,
            ],//panel_content TEXT NOT NULL,
            '' => [
                'type' => '',
                'length' => 100,
            ],//panel_side TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
            '' => [
                'type' => '',
                'length' => 100,
                'key' => 2, //KEY panel_order (panel_order)
            ],//panel_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
            '' => [
                'type' => '',
                'length' => 100,
            ],//panel_type VARCHAR(20) NOT NULL DEFAULT '',
            '' => [
                'type' => '',
                'length' => 100,
            ],//panel_access TINYINT(4) NOT NULL DEFAULT '0',
            '' => [
                'type' => '',
                'length' => 100,
            ],//panel_display TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            '' => [
                'type' => '',
                'length' => 100,
            ],//panel_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            '' => [
                'type' => '',
                'length' => 100,
            ],//panel_url_list TEXT NOT NULL,
            '' => [
                'type' => '',
                'length' => 100,
            ],//panel_restriction TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            '' => [
                'type' => '',
                'length' => 100,
            ],
            //panel_languages VARCHAR(200) NOT NULL DEFAULT '".implode('.', filter_input(INPUT_POST, 'enabled_languages', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: array(LANGUAGE))."',
            '' => [
                'type' => '',
                'length' => 100,
            ],
        ];
        $table_package[''] = [
            '' => [
                'type' => '',
                'length' => 100,
            ],
            '' => [
                'type' => '',
                'length' => 100,
            ],
            '' => [
                'type' => '',
                'length' => 100,
            ],
            '' => [
                'type' => '',
                'length' => 100,
            ],
            '' => [
                'type' => '',
                'length' => 100,
            ],
            '' => [
                'type' => '',
                'length' => 100,
            ],
            '' => [
                'type' => '',
                'length' => 100,
            ],
            '' => [
                'type' => '',
                'length' => 100,
            ]
        ];



        /*
         * Parser ----- NOTES for code writing
         *      // primary key not necessary auto increment!!
                // if auto_increment no default.
                // IF is auto_increment and is INT or MEDIUMINT add `unsigned`
                // unsigned is off by default

                // text has no default
                // do not build default for all fields except number fields if not defined
                // everything is `not null` unless null is specified

                // smallInt do not need a default -- ????

                // tinyint has default '0'
                // varchar might have '0' default. if isset default , make sure look into it and get value. all to assign '0' if empty. if '', dont declare defaults
                //  if no isset is blank '' -- ref inf_version of DB_INFUSIONS


                    "permalinks_alias" => " (
                alias_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                alias_url VARCHAR(200) NOT NULL DEFAULT '',
                alias_php_url VARCHAR(200) NOT NULL DEFAULT '',
                alias_type VARCHAR(10) NOT NULL DEFAULT '',
                alias_item_id INT(10) UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (alias_id),
                KEY alias_id (alias_id)
                ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "permalinks_method" => " (
                pattern_id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                pattern_type INT(5) UNSIGNED NOT NULL,
                pattern_source VARCHAR(200) NOT NULL DEFAULT '',
                pattern_target VARCHAR(200) NOT NULL DEFAULT '',
                pattern_cat VARCHAR(10) NOT NULL DEFAULT '',
                PRIMARY KEY (pattern_id)
                ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "permalinks_rewrites" => " (
                rewrite_id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                rewrite_name VARCHAR(50) NOT NULL DEFAULT '',
                PRIMARY KEY (rewrite_id)
                ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "settings" => " (
                settings_name VARCHAR(200) NOT NULL DEFAULT '',
                settings_value TEXT NOT NULL,
                PRIMARY KEY (settings_name)
                ) ENGINE=MYISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "settings_inf" => " (
                settings_name VARCHAR(200) NOT NULL DEFAULT '',
                settings_value TEXT NOT NULL,
                settings_inf VARCHAR(200) NOT NULL DEFAULT '',
                PRIMARY KEY (settings_name)
                ) ENGINE=MYISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "settings_theme" => " (
                settings_name VARCHAR(200) NOT NULL DEFAULT '',
                settings_value TEXT NOT NULL,
                settings_theme VARCHAR(200) NOT NULL DEFAULT '',
                PRIMARY KEY (settings_name)
                ) ENGINE=MYISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "site_links" => " (
                link_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                link_cat MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0',
                link_name VARCHAR(100) NOT NULL DEFAULT '',
                link_url VARCHAR(200) NOT NULL DEFAULT '',
                link_icon VARCHAR(100) NOT NULL DEFAULT '',
                link_visibility TINYINT(4) NOT NULL DEFAULT '0',
                link_position TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                link_window TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                link_order SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0',
                link_language VARCHAR(50) NOT NULL DEFAULT '".filter_input(INPUT_POST, 'localeset')."',
                PRIMARY KEY (link_id)
                ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "smileys" => " (
                smiley_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                smiley_code VARCHAR(50) NOT NULL,
                smiley_image VARCHAR(100) NOT NULL,
                smiley_text VARCHAR(100) NOT NULL,
                PRIMARY KEY (smiley_id)
                ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "submissions" => " (
                submit_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                submit_type CHAR(1) NOT NULL,
                submit_user MEDIUMINT(8) UNSIGNED DEFAULT '0' NOT NULL,
                submit_datestamp INT(10) UNSIGNED DEFAULT '0' NOT NULL,
                submit_criteria TEXT NOT NULL,
                PRIMARY KEY (submit_id)
                ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "suspends" => " (
                suspend_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                suspended_user MEDIUMINT(8) UNSIGNED NOT NULL,
                suspending_admin MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
                suspend_ip VARCHAR(45) NOT NULL DEFAULT '',
                suspend_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
                suspend_date INT(10) NOT NULL DEFAULT '0',
                suspend_reason TEXT NOT NULL,
                suspend_type TINYINT(1) NOT NULL DEFAULT '0',
                reinstating_admin MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
                reinstate_reason TEXT NOT NULL,
                reinstate_date INT(10) NOT NULL DEFAULT '0',
                reinstate_ip VARCHAR(45) NOT NULL DEFAULT '',
                reinstate_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
                PRIMARY KEY (suspend_id)
                ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "user_field_cats" => " (
                field_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT ,
                field_cat_name TEXT NOT NULL,
                field_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
                field_cat_db VARCHAR(100) NOT NULL,
                field_cat_index VARCHAR(200) NOT NULL,
                field_cat_class VARCHAR(50) NOT NULL,
                field_cat_order SMALLINT(5) UNSIGNED NOT NULL ,
                PRIMARY KEY (field_cat_id)
                ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "user_fields" => " (
                field_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                field_title TEXT NOT NULL,
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
                ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "user_groups" => " (
                group_id TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
                group_name VARCHAR(100) NOT NULL,
                group_description VARCHAR(200) NOT NULL,
                PRIMARY KEY (group_id)
                ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "user_log" => " (
                userlog_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                userlog_user_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
                userlog_field VARCHAR(50) NOT NULL DEFAULT '',
                userlog_value_new TEXT NOT NULL,
                userlog_value_old TEXT NOT NULL,
                userlog_timestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (userlog_id),
                KEY userlog_user_id (userlog_user_id),
                KEY userlog_field (userlog_field)
                ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "users" => " (
                user_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_name VARCHAR(30) NOT NULL DEFAULT '',
                user_algo VARCHAR(10) NOT NULL DEFAULT 'sha256',
                user_salt VARCHAR(40) NOT NULL DEFAULT '',
                user_password VARCHAR(64) NOT NULL DEFAULT '',
                user_admin_algo VARCHAR(10) NOT NULL DEFAULT 'sha256',
                user_admin_salt VARCHAR(40) NOT NULL DEFAULT '',
                user_admin_password VARCHAR(64) NOT NULL DEFAULT '',
                user_email VARCHAR(100) NOT NULL DEFAULT '',
                user_hide_email TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                user_timezone VARCHAR(50) NOT NULL DEFAULT 'Europe/London',
                user_avatar VARCHAR(100) NOT NULL DEFAULT '',
                user_posts SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
                user_threads TEXT NOT NULL,
                user_joined INT(10) UNSIGNED NOT NULL DEFAULT '0',
                user_lastvisit INT(10) UNSIGNED NOT NULL DEFAULT '0',
                user_ip VARCHAR(45) NOT NULL DEFAULT '0.0.0.0',
                user_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
                user_rights TEXT NOT NULL,
                user_groups TEXT NOT NULL,
                user_level TINYINT(4) NOT NULL DEFAULT '-101',
                user_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                user_inbox SMALLINT(6) unsigned not null default '0',
                user_outbox SMALLINT(6) unsigned not null default '0',
                user_archive SMALLINT(6) unsigned not null default '0',
                user_pm_email_notify TINYINT(1) not null default '0',
                user_pm_save_sent TINYINT(1) not null default '0',
                user_actiontime INT(10) UNSIGNED NOT NULL DEFAULT '0',
                user_theme VARCHAR(100) NOT NULL DEFAULT 'Default',
                user_location VARCHAR(50) NOT NULL DEFAULT '',
                user_birthdate DATE NOT NULL DEFAULT '1900-01-01',
                user_skype VARCHAR(100) NOT NULL DEFAULT '',
                user_aim VARCHAR(16) NOT NULL DEFAULT '',
                user_icq VARCHAR(15) NOT NULL DEFAULT '',
                user_yahoo VARCHAR(100) NOT NULL DEFAULT '',
                user_web VARCHAR(200) NOT NULL DEFAULT '',
                user_sig TEXT NOT NULL,
                user_language VARCHAR(50) NOT NULL DEFAULT '".filter_input(INPUT_POST, 'localeset')."',
                PRIMARY KEY (user_id),
                KEY user_name (user_name),
                KEY user_joined (user_joined),
                KEY user_lastvisit (user_lastvisit)
                ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci",
                    "theme" => " (
                theme_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                theme_name VARCHAR(50) NOT NULL,
                theme_title VARCHAR(50) NOT NULL,
                theme_file VARCHAR(200) NOT NULL,
                theme_datestamp INT(10) UNSIGNED DEFAULT '0' NOT NULL,
                theme_user MEDIUMINT(8) UNSIGNED NOT NULL,
                theme_active TINYINT(1) UNSIGNED NOT NULL,
                theme_config TEXT NOT NULL,
                PRIMARY KEY (theme_id)
                ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci"
                );
                */

    }


    /*
     * Returns the full package
     */
    public static function package_runtime() {


    }


}