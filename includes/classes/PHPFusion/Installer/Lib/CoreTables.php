<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: CoreTables.php
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

namespace PHPFusion\Installer\Lib;

class CoreTables {

    /**
     * Core table configurations
     *
     * @param string $localeset
     *
     * @return array
     */
    public static function get_core_tables($localeset) {

        /*
         * Modeled for compositing table sql comparison for upgrade/reinstall/and install friendly
         * rather than maintaining files after files
         */
        $table_package['admin'] = [
            'admin_id'       => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (admin_id)
                'unsigned'       => TRUE,
            ], //admin_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'admin_rights'   => [
                'type'    => 'CHAR',
                'length'  => 4,
                'default' => ''
            ], //admin_rights CHAR(4) NOT NULL DEFAULT '',
            'admin_idisplay' => [
                'type'     => 'SMALLINT',
                'length'   => 5,
                'default'  => 0,
                'unsigned' => TRUE,
            ],
            'admin_image'    => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ], //admin_image VARCHAR(50) NOT NULL DEFAULT '',
            'admin_svg'      => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => '',
            ],
            // SVG is the canonical vector icon source. The legacy
            // admin_glyph column is intentionally not part of the core schema.
            'admin_title'    => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => ''
            ], //admin_title VARCHAR(50) NOT NULL DEFAULT '',
            'admin_link'     => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => 'reserved',
            ], //admin_link VARCHAR(100) NOT NULL DEFAULT 'reserved',
            'admin_page'     => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => 1,
                'unsigned' => TRUE,
            ], //admin_page TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
            'admin_language' => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => $localeset,
            ], //admin_language VARCHAR(50) NOT NULL DEFAULT '...';
            'admin_order'    => [
                'type'     => 'INT',
                'length'   => 5,
                'default'  => 0,
                'unsigned' => TRUE,
            ],
        ];
        $table_package['mlt_tables'] = [
            'mlt_rights' => [
                'type'    => 'CHAR',
                'length'  => 4,
                'default' => '',
                'key'     => 1 //PRIMARY KEY (mlt_rights)
            ], //mlt_rights CHAR(4) NOT NULL DEFAULT '',
            'mlt_title'  => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => ''
            ], //mlt_title VARCHAR(50) NOT NULL DEFAULT '',
            'mlt_status' => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => ''
            ] //mlt_status VARCHAR(50) NOT NULL DEFAULT '',
        ];
        $table_package['language_sessions'] = [
            'user_ip'        => [
                'type'    => 'VARCHAR',
                'length'  => 45,
                'default' => '0.0.0.0',
            ], //user_ip VARCHAR(20) NOT NULL DEFAULT '0.0.0.0',
            'user_language'  => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => $localeset
            ], //user_language VARCHAR(50) NOT NULL DEFAULT '...';
            'user_datestamp' => [
                'type'     => 'INT',
                'length'   => 10,
                'default'  => '0',
                'unsigned' => TRUE,
            ] //user_datestamp INT(10) NOT NULL default '0'
        ];
        $table_package['admin_resetlog'] = [
            'reset_id'        => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (reset_id)
            ], //reset_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
            'reset_admin_id'  => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'default'  => 1,
                'unsigned' => TRUE
            ], //reset_admin_id mediumint(8) unsigned NOT NULL default '1',
            'reset_timestamp' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0',
            ], //reset_timestamp int(10) unsigned NOT NULL default '0',
            'reset_sucess'    => [
                'type' => 'TEXT'
            ], //reset_sucess text NOT NULL,
            'reset_failed'    => [
                'type' => 'TEXT'
            ], //reset_failed text NOT NULL,
            'reset_admins'    => [
                'type'    => 'VARCHAR',
                'length'  => 8,
                'default' => '0',
            ], //reset_admins varchar(8) NOT NULL default '0',
            'reset_reason'    => [
                'type'    => 'VARCHAR',
                'length'  => 255,
                'default' => ''
            ] //reset_reason varchar(255) NOT NULL,
        ];
        $table_package['bbcodes'] = [
            'bbcode_id'    => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (bbcode_id),
            ], //bbcode_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'bbcode_name'  => [
                'type'    => 'VARCHAR',
                'length'  => 20,
                'default' => ''
            ], //bbcode_name VARCHAR(20) NOT NULL DEFAULT '',
            'bbcode_order' => [
                'type'     => 'SMALLINT',
                'length'   => 5,
                'key'      => 2, //KEY bbcode_order (bbcode_order)
                'unsigned' => TRUE,
            ], //bbcode_order SMALLINT(5) UNSIGNED NOT NULL,
        ];
        $table_package['blacklist'] = [
            'blacklist_id'        => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (blacklist_id),
            ], //blacklist_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'blacklist_user_id'   => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'default'  => '0',
                'key'      => 2
            ], //blacklist_user_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'blacklist_ip'        => [
                'type'    => 'VARCHAR',
                'length'  => 45,
                'default' => ''
            ], //blacklist_ip VARCHAR(45) NOT NULL DEFAULT '',
            'blacklist_ip_type'   => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => 4,
                'key'     => 2 //KEY blacklist_ip_type (blacklist_ip_type)
            ], //blacklist_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
            'blacklist_email'     => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ],//blacklist_email VARCHAR(100) NOT NULL DEFAULT '',
            'blacklist_reason'    => [
                'type' => 'TEXT'
            ],//blacklist_reason TEXT NOT NULL,
            'blacklist_datestamp' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0',
            ] //blacklist_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
        ];
        $table_package['custom_pages'] = [
            'page_id'           => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (page_id)
                'unsigned'       => TRUE,
            ], //page_id MEDIUMINT(8) NOT NULL AUTO_INCREMENT,
            'page_cat'          => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'key'      => 2,
                'default'  => '0'
            ], //page_cat MEDIUMINT(8) NOT NULL DEFAULT '0',
            'page_link_cat'     => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'default'  => '0'
            ], // page_link_cat MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0',
            'page_title'        => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'default' => ''
            ], // page_title VARCHAR(200) NOT NULL DEFAULT '',
            'page_access'       => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => '0'
            ], //page_access VARCHAR(50) NOT NULL DEFAULT '0',
            'page_content'      => [
                'type' => 'LONGTEXT'
            ], //page_content TEXT NOT NULL,
            'page_keywords'     => [
                'type'    => 'VARCHAR',
                'length'  => 250,
                'default' => ''
            ], // page_keywords VARCHAR(250) NOT NULL DEFAULT '',
            'page_status'       => [
                'type'    => 'SMALLINT',
                'length'  => 1,
                'default' => '0'
            ], //page_status SMALLINT(1) NOT NULL DEFAULT '0',
            'page_breaks'       => [
                'type'    => 'CHAR',
                'length'  => 1,
                'default' => ''
            ], //page_breaks CHAR(1) NOT NULL DEFAULT '',
            'page_user'         => [
                'type'     => 'MEDIUMINT',
                'length'   => 9,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //page_user MEDIUMINT(9) NOT NULL DEFAULT '0',
            'page_datestamp'    => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //page_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
            'page_language'     => [
                'type'    => 'VARCHAR',
                'length'  => 255,
                'default' => $localeset,
            ], //page_language VARCHAR(255) NOT NULL DEFAULT '...';
            'page_grid_id'      => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'key'      => 2,
                'default'  => '0'
            ], //page_grid_id MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0',
            'page_content_id'   => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'key'      => 2,
                'default'  => '0'
            ], //page_content_id MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0',
            'page_left_panel'   => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], //page_left_panel TINYINT(1) NOT NULL DEFAULT '0',
            'page_right_panel'  => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], //page_right_panel TINYINT(1) NOT NULL DEFAULT '0',
            'page_header_panel' => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], //page_header_panel TINYINT(1) NOT NULL DEFAULT '0',
            'page_footer_panel' => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], //page_footer_panel TINYINT(1) NOT NULL DEFAULT '0',
            'page_top_panel'    => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], //page_top_panel TINYINT(1) NOT NULL DEFAULT '0',
            'page_bottom_panel' => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], //page_bottom_panel TINYINT(1) NOT NULL DEFAULT '0',
        ];
        $table_package['custom_pages_grid'] = [
            'page_id'                => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'key'      => 2,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //page_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'page_grid_id'           => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1,
                'unsigned'       => TRUE,
            ], //page_grid_id MEDIUMINT(9) UNSIGNED  NOT NULL AUTO_INCREMENT,
            'page_grid_container'    => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], // page_grid_container TINYINT(1) NOT NULL DEFAULT '0',
            'page_grid_column_count' => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], // page_grid_column_count TINYINT(1) NOT NULL DEFAULT '0',
            'page_grid_html_id'      => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => ''
            ], //page_grid_html_id VARCHAR(50) NOT NULL DEFAULT '',
            'page_grid_class'        => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ], //page_grid_class VARCHAR(100) NOT NULL DEFAULT '',
            'page_grid_order'        => [
                'type'     => 'TINYINT',
                'length'   => 5,
                'unsigned' => TRUE,
                'default'  => '0'
            ], // page_grid_order TINYINT(5) NOT NULL DEFAULT '0',
        ];
        $table_package['custom_pages_content'] = [
            'page_id'            => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'key'      => 2, //KEY page_id (page_id),
                'unsigned' => TRUE,
                'default'  => '0'
            ], //page_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'page_grid_id'       => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'key'      => 2, //KEY page_grid_id (page_grid_id)
                'unsigned' => TRUE,
                'default'  => '0'
            ], //page_grid_id MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0',
            'page_content_id'    => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (page_content_id),
                'unsigned'       => TRUE,
            ], //page_content_id MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
            'page_content_type'  => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => ''
            ], //page_content_type VARCHAR(50) NOT NULL DEFAULT '',
            'page_content'       => [
                'type' => 'LONGTEXT',
            ], //page_content TEXT NOT NULL,
            'page_options'       => [
                'type' => 'TEXT',
            ], //page_options TEXT NOT NULL,
            'page_content_order' => [
                'type'     => 'TINYINT',
                'length'   => 5,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //page_content_order TINYINT(5) NOT NULL DEFAULT '0',
            'page_widget'        => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => '0'
            ], //page_widget VARCHAR(100) NOT NULL DEFAULT '',
        ];
        $table_package['comments'] = [
            'comment_id'        => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1,
                'unsigned'       => TRUE,
            ], //comment_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'comment_item_id'   => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'key'      => 2,
                'default'  => '0'
            ], //comment_item_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'comment_type'      => [
                'type'    => 'CHAR',
                'length'  => 4,
                'default' => ''
            ], //comment_type CHAR(4) NOT NULL DEFAULT '',
            'comment_cat'       => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'key'      => 2,
                'default'  => '0'
            ], //comment_cat MEDIUMINT(8) NOT NULL DEFAULT '0',
            'comment_name'      => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ],//comment_name VARCHAR(50) NOT NULL DEFAULT '',
            'comment_subject'   => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'default' => ''
            ],
            'comment_message'   => [
                'type' => 'TEXT',
            ], //comment_message TEXT NOT NULL,
            'comment_datestamp' => [
                'type'     => 'INT',
                'length'   => 10,
                'key'      => 2, //KEY comment_datestamp (comment_datestamp)
                'unsigned' => TRUE,
                'default'  => '0'
            ], //comment_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
            'comment_ip'        => [
                'type'    => 'VARCHAR',
                'length'  => 45,
                'default' => ''
            ], //comment_ip VARCHAR(45) NOT NULL DEFAULT '',
            'comment_ip_type'   => [
                'type'    => 'TINYINT',
                'default' => 4,
                'length'  => 1
            ], //comment_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
            'comment_hidden'    => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], //comment_hidden TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
        ];
        $table_package['errors'] = [
            'error_id'           => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1,
                'unsigned'       => TRUE,
            ], //error_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
            'error_level'        => [
                'type'     => 'SMALLINT',
                'length'   => 5,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //error_level smallint(5) unsigned NOT NULL,
            'error_message'      => [
                'type' => 'TEXT',
            ], //error_message text NOT NULL,
            'error_file'         => [
                'type'    => 'VARCHAR',
                'length'  => 255,
                'default' => ''
            ], //error_file varchar(255) NOT NULL,
            'error_line'         => [
                'type'     => 'SMALLINT',
                'length'   => 5,
                'unsigned' => TRUE,
            ], // error_line smallint(5) NOT NULL,
            'error_page'         => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'default' => ''
            ], // error_page varchar(200) NOT NULL,
            'error_user_level'   => [
                'type'   => 'TINYINT',
                'length' => 4,
            ], //error_user_level TINYINT(4) NOT NULL,
            'error_user_ip'      => [
                'type'    => 'VARCHAR',
                'length'  => 45,
                'default' => ''
            ], //error_user_ip varchar(45) NOT NULL default '',
            'error_user_ip_type' => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => 4
            ], //error_user_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
            'error_status'       => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], //error_status tinyint(1) NOT NULL default '0',
            'recurred_date'      => [
                'type'    => 'DATETIME',
                'null'    => TRUE,
                'default' => 'NULL',
            ], // Latest recurrence, formatted YYYY-MM-DD HH:MM:SS; NULL means not recurred.
            'error_timestamp'    => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
            ] //error_timestamp int(10) NOT NULL,
        ];
        $table_package['flood_control'] = [
            'flood_ip'        => [
                'type'    => 'VARCHAR',
                'length'  => 45,
                'default' => ''
            ], //flood_ip VARCHAR(45) NOT NULL DEFAULT '',
            'flood_ip_type'   => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => 4
            ], //flood_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
            'flood_timestamp' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'key'      => 2, //KEY flood_timestamp (flood_timestamp)
                'default'  => '0'
            ] //flood_timestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
        ];
        $table_package['infusions'] = [
            'inf_id'      => [
                'type'           => 'MEDIUMINT',
                'length'         => 8,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (inf_id)
                'unsigned'       => TRUE,
            ], //inf_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'inf_title'   => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ], //inf_title VARCHAR(100) NOT NULL DEFAULT '',
            'inf_folder'  => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ], //inf_folder VARCHAR(100) NOT NULL DEFAULT '',
            'inf_version' => [
                'type'    => 'VARCHAR',
                'length'  => 10,
                'default' => '0'
            ], //inf_version VARCHAR(10) NOT NULL DEFAULT '0',
        ];
        $table_package['messages'] = [
            'message_id'        => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (message_id)
                'unsigned'       => TRUE,
            ], // message_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'message_to'        => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'key'      => 2,
                'default'  => '0'
            ], // message_to MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'message_from'      => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'key'      => 2,
                'default'  => '0'
            ], //message_from MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'message_user'      => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'key'      => 2,
                'default'  => '0'
            ], //message_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'message_subject'   => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ], //message_subject VARCHAR(100) NOT NULL DEFAULT '',
            'message_message'   => [
                'type' => 'TEXT'
            ], //message_message TEXT NOT NULL,
            'message_smileys'   => [
                'type'    => 'CHAR',
                'length'  => 1,
                'default' => ''
            ], //message_smileys CHAR(1) NOT NULL DEFAULT '', @note: changed
            'message_read'      => [
                'type'    => 'SMALLINT',
                'length'  => 1,
                'default' => '0'
            ], //message_read TINYINT(1) UNSIGNED NOT NULL DEFAULT '0', @note: changed
            'message_datestamp' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'key'      => 2, //KEY message_datestamp (message_datestamp)
                'default'  => '0'
            ], //message_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
            'message_folder'    => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ] //message_folder TINYINT(1) UNSIGNED NOT NULL DEFAULT  '0',
        ];
        $table_package['new_users'] = [
            'user_code'      => [
                'type'    => 'VARCHAR',
                'length'  => 40,
                'default' => ''
            ], //user_code VARCHAR(40) NOT NULL,
            'user_name'      => [
                'type'    => 'VARCHAR',
                'length'  => 30,
                'default' => ''
            ], //user_name VARCHAR(30) NOT NULL,
            'user_email'     => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ], //user_email VARCHAR(100) NOT NULL,
            'user_datestamp' => [
                'type'     => 'INT',
                'length'   => 10,
                'default'  => '0',
                'key'      => 2, //KEY user_datestamp (user_datestamp)
                'unsigned' => TRUE,
            ], //user_datestamp INT(10) UNSIGNED DEFAULT '0' NOT NULL,
            'user_info'      => [
                'type' => 'TEXT'
            ], //user_info TEXT NOT NULL,
        ];
        $table_package['email_verify'] = [
            'user_id'        => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
            ], //user_id MEDIUMINT(8) NOT NULL,
            'user_code'      => [
                'type'    => 'VARCHAR',
                'length'  => 32,
                'default' => ''
            ], //user_code VARCHAR(32) NOT NULL,
            'user_email'     => [
                'type'   => 'VARCHAR',
                'length' => 100,
            ], //user_email VARCHAR(100) NOT NULL,
            'user_datestamp' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'key'      => 2, //KEY user_datestamp (user_datestamp)
                'default'  => '0'
            ], //user_datestamp INT(10) UNSIGNED DEFAULT '0' NOT NULL,
        ];
        $table_package['email_templates'] = [
            'template_id'           => [
                'type'           => 'MEDIUMINT',
                'length'         => 8,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (template_id)
                'unsigned'       => TRUE,
            ], //template_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'template_key'          => [
                'type'    => 'VARCHAR',
                'length'  => 20,
                'default' => ''
            ], //template_key VARCHAR(10) NOT NULL,
            'template_format'       => [
                'type'    => 'VARCHAR',
                'length'  => 10,
                'default' => ''
            ], //template_format VARCHAR(10) NOT NULL,
            'template_active'       => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], //template_active TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            'template_name'         => [
                'type'    => 'VARCHAR',
                'length'  => 300,
                'default' => ''
            ], //template_name VARCHAR(300) NOT NULL,
            'template_subject'      => [
                'type' => 'TEXT',
            ], //template_subject TEXT NOT NULL,
            'template_content'      => [
                'type' => 'TEXT'
            ], //template_content TEXT NOT NULL,
            'template_sender_name'  => [
                'type'    => 'VARCHAR',
                'length'  => 30,
                'default' => ''
            ], //template_sender_name VARCHAR(30) NOT NULL,
            'template_sender_email' => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ], //template_sender_email VARCHAR(100) NOT NULL,
            'template_language'     => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => $localeset
            ] //template_language VARCHAR(50) NOT NULL,
        ];
        $table_package['policies'] = [
            'policy_name'     => [
                'type'   => 'VARCHAR',
                'length' => 200,
                'key'    => 2,
            ],
            'policy_content'  => [
                'type' => 'TEXT',
            ],
            'policy_date'     => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'key'      => 2,
            ],
            'policy_language' => [
                'type'    => 'VARCHAR',
                'length'  => 30,
                'default' => $localeset
            ]
        ];
        $table_package['ratings'] = [
            'rating_id'        => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (rating_id)
                'unsigned'       => TRUE,
            ],//rating_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'rating_item_id'   => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'default'  => '0'
            ],//rating_item_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'rating_type'      => [
                'type'    => 'CHAR',
                'length'  => 4,
                'default' => ''
            ],//rating_type CHAR(4) NOT NULL DEFAULT '',
            'rating_user'      => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'key'      => 2,
                'default'  => '0'
            ],//rating_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'rating_vote'      => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //rating_vote TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            'rating_datestamp' => [
                'type'     => 'INT',
                'length'   => 10,
                'key'      => 2, //@note:changed
                'unsigned' => TRUE,
                'default'  => '0'
            ], //rating_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
            'rating_ip'        => [
                'type'    => 'VARCHAR',
                'length'  => 45,
                'default' => ''
            ],
            'rating_ip_type'   => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => 4
            ]
        ];

        $table_package['online'] = [
            'online_user'       => [
                'type'   => 'VARCHAR',
                'length' => 100
            ],
            'online_ip'         => [
                'type'   => 'VARCHAR',
                'length' => 45
            ],
            'online_ip_type'    => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => 4
            ],
            'online_lastactive' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0'
            ]
        ];
        $table_package['panels'] = [
            'panel_id'          => [
                'type'           => 'MEDIUMINT',
                'length'         => 11,
                'auto_increment' => TRUE,
                'key'            => 1,
                'unsigned'       => TRUE,
            ],
            'panel_name'        => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ],
            'panel_filename'    => [
                'type'    => 'VARCHAR',
                'length'  => 150,
                'default' => ''
            ],
            'panel_content'     => [
                'type' => 'TEXT'
            ],
            'panel_side'        => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => 1
            ],
            'panel_order'       => [
                'type'     => 'SMALLINT',
                'length'   => 5,
                'unsigned' => TRUE,
                'default'  => '0',
                'key'      => 2,
            ],
            'panel_type'        => [
                'type'    => 'VARCHAR',
                'length'  => 20,
                'default' => ''
            ],
            'panel_php_exe'     => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => 0
            ],
            'panel_access'      => [
                'type'    => 'TINYINT',
                'length'  => 4,
                'default' => '0'
            ],
            'panel_display'     => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '0'
            ],
            'panel_status'      => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => '0',
                'unsigned' => TRUE,
            ],
            'panel_url_list'    => [
                'type' => 'TEXT'
            ],
            'panel_restriction' => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '0'
            ],
            'panel_languages'   => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'default' => $localeset
            ]
        ];
        $table_package['permalinks_alias'] = [
            'alias_id'      => [
                'type'           => 'MEDIUMINT',
                'length'         => 8,
                'auto_increment' => TRUE,
                'key'            => 1,
                'unsigned'       => TRUE,
            ],
            'alias_url'     => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'default' => ''
            ],
            'alias_php_url' => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'default' => ''
            ],
            'alias_type'    => [
                'type'    => 'VARCHAR',
                'length'  => 10,
                'default' => ''
            ],
            'alias_item_id' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0'
            ]
        ];
        $table_package['permalinks_method'] = [
            'pattern_id'     => [
                'type'           => 'MEDIUMINT',
                'length'         => 8,
                'auto_increment' => TRUE,
                'key'            => 1,
                'unsigned'       => TRUE,
            ], //pattern_id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'pattern_type'   => [
                'type'     => 'INT',
                'length'   => 5,
                'unsigned' => TRUE,
                'default'  => '0'
            ],
            'pattern_source' => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'default' => ''
            ],
            'pattern_target' => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'default' => ''
            ],
            'pattern_cat'    => [
                'type'    => 'VARCHAR',
                'length'  => 10,
                'default' => ''
            ]
        ];
        $table_package['permalinks_rewrites'] = [
            'rewrite_id'   => [
                'type'           => 'MEDIUMINT',
                'length'         => 8,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (rewrite_id)
                'unsigned'       => TRUE,
            ],
            'rewrite_name' => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => ''
            ]
        ];
        $table_package['sessions'] = [
            'session_id'    => [
                'type'   => 'VARCHAR',
                'length' => 32,
                'key'    => 1, //PRIMARY KEY (session_id),
            ], //session_id VARCHAR(32) NOT NULL,
            'session_start' => [
                'type'     => 'INT',
                'length'   => 10,
                // 'key'      => 1,
                'unsigned' => TRUE,
                'default'  => 0,
            ], // session_start INT(10) UNSIGNED NOT NULL DEFAULT '0',
            /*'session_key'   => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => '',
                'key'     => 2, //KEY session_key (session_key)
            ], // session_key VARCHAR(100) NOT NULL DEFAULT '',*/
            'session_data'  => [
                'type' => 'TEXT',
            ], //  session_data TEXT NOT NULL,
        ];
        $table_package['settings'] = [
            'settings_name'  => [
                'type'   => 'VARCHAR',
                'length' => 200,
                'key'    => 1 //PRIMARY KEY (settings_name)
            ], //settings_name VARCHAR(200) NOT NULL DEFAULT '',
            'settings_value' => [
                'type' => 'TEXT'
            ] //settings_value TEXT NOT NULL,
        ];
        $table_package['settings_inf'] = [
            'settings_name'  => [
                'type'   => 'VARCHAR',
                'length' => 200,
                'key'    => 1 //PRIMARY KEY (settings_name)
            ], //settings_name VARCHAR(200) NOT NULL DEFAULT '',
            'settings_value' => [
                'type' => 'TEXT'
            ], //settings_value TEXT NOT NULL,
            'settings_inf'   => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'default' => ''
            ], //settings_inf VARCHAR(200) NOT NULL DEFAULT '',
        ];

        /*
         * CREATE TABLE elite_scheduled_tasks (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `task_key` VARCHAR(100) NOT NULL,
            `payload` JSON NULL,
            `run_at` DATETIME NOT NULL,
            `executed_at` DATETIME NULL,
            `status` ENUM('pending','running','success','failed')
                NOT NULL DEFAULT 'pending',
            `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `max_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 3,
            `last_error` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`run_at`),
            INDEX (`status`)
        ) ENGINE=InnoDB;
         */

        $table_package['scheduled_tasks'] = [
            'id'           => [
                'type'           => 'BIGINT',
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (rewrite_id)
                'unsigned'       => TRUE,
            ],
            'task_key'     => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ],
            'payload'      => [
                'type' => 'JSON',
                'null' => TRUE,
            ],
            'dedupe_key'   => [
                'type'    => 'VARCHAR',
                'length'  => 191,
                'default' => 'NULL',
                'null'    => TRUE,
            ],
            'run_at'       => [
                'type'    => 'DATETIME',
            ],
            'executed_at'  => [
                'type'    => 'DATETIME',
                'default' => 'NULL',
                'null'    => TRUE,
            ],
            'status'       => [
                'type'    => 'ENUM ("pending", "running", "success", "failed")',
                'default' => 'pending',
            ],
            'attempts'     => [
                'type'    => 'TINYINT',
                'default' => '0'
            ],
            'max_attempts' => [
                'type'    => 'TINYINT',
                'default' => '3'
            ],
            'last_error'   => [
                'type' => 'TEXT',
                'null' => TRUE,
            ],
            'created_at'   => [
                'type'    => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'updated_at'   => [
                'type'      => 'TIMESTAMP',
                'default'   => 'CURRENT_TIMESTAMP',
                'on_update' => 'CURRENT_TIMESTAMP'
            ],
            '__indexes'    => [
                'uq_scheduler_dedupe' => [
                    'columns' => ['dedupe_key'],
                    'unique'  => TRUE,
                ],
                'idx_scheduler_due' => [
                    'columns' => ['status', 'run_at'],
                ],
            ],
        ];

        $table_package['settings_theme'] = [
            'settings_name'  => [
                'type'   => 'VARCHAR',
                'length' => 200,
                'key'    => 1 //PRIMARY KEY (settings_name)
            ], //settings_name VARCHAR(200) NOT NULL DEFAULT '',
            'settings_value' => [
                'type' => 'TEXT'
            ], //settings_value TEXT NOT NULL,
            'settings_theme' => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'default' => ''
            ], //settings_theme VARCHAR(200) NOT NULL DEFAULT '',
        ];
        $table_package['site_links'] = [
            'link_id'         => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (link_id)
                'unsigned'       => TRUE,
            ], //link_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'link_cat'        => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'key'      => 2,
                'default'  => '0'
            ], //link_cat MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0',
            'link_name'       => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ], //link_name VARCHAR(100) NOT NULL DEFAULT '',
            'link_url'        => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'key'     => 2,
                'default' => ''
            ], //link_url VARCHAR(200) NOT NULL DEFAULT '',
            'link_icon'       => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ], //link_icon VARCHAR(100) NOT NULL DEFAULT '',
            'link_visibility' => [
                'type'    => 'TINYINT',
                'length'  => 4,
                'default' => 0,
                'key'     => 2,
            ], //link_visibility TINYINT(4) NOT NULL DEFAULT '0',
            'link_position'   => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => 1,
                'key'      => 2
            ], //link_position TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
            'link_status'     => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => 0,
                'key'      => 2
            ],
            'link_window'     => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //link_window TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            'link_order'      => [
                'type'     => 'SMALLINT',
                'length'   => 2,
                'unsigned' => TRUE,
                'key'      => 2,
                'default'  => '0'
            ], //link_order SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0',
            'link_language'   => [
                'type'    => 'VARCHAR',
                'length'  => 70,
                'default' => $localeset
            ] //link_language VARCHAR(50) NOT NULL DEFAULT '...';
        ];
        $table_package['smileys'] = [
            'smiley_id'    => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (smiley_id)
                'unsigned'       => TRUE,
            ], // smiley_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'smiley_code'  => [
                'type'   => 'VARCHAR',
                'length' => 50,
                'key'    => 2
            ], //smiley_code VARCHAR(50) NOT NULL,
            'smiley_image' => [
                'type'   => 'VARCHAR',
                'length' => 200,
                'key'    => 2
            ], //smiley_image VARCHAR(100) NOT NULL,
            'smiley_text'  => [
                'type'   => 'VARCHAR',
                'length' => 100,
            ], //smiley_text VARCHAR(100) NOT NULL,
        ];
        $table_package['social'] = [
            'social_id'         => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1,
                'unsigned'       => TRUE,
            ],
            'social_user_id'    => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'default'  => '0',
            ],
            'social_target_id'  => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'default'  => '0',
            ],
            'social_type'       => [
                'type'    => 'VARCHAR',
                'length'  => 10,
                'default' => '',
            ],
            'social_status'     => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '0',
            ],
            'social_datestamp'  => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0',
            ],
            '__indexes'          => [
                'uq_social_relationship' => [
                    'columns' => ['social_user_id', 'social_target_id', 'social_type'],
                    'unique'  => TRUE,
                ],
                'idx_social_user_type_status' => [
                    'columns' => ['social_user_id', 'social_type', 'social_status'],
                ],
                'idx_social_target_type_status' => [
                    'columns' => ['social_target_id', 'social_type', 'social_status'],
                ],
            ],
        ];
        $table_package['social_settings'] = [
            'social_settings_user_id' => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'key'      => 1,
            ],
            'social_friend_privacy' => [
                'type'    => 'VARCHAR',
                'length'  => 16,
                'default' => 'everyone',
            ],
            'social_follow_privacy' => [
                'type'    => 'VARCHAR',
                'length'  => 16,
                'default' => 'everyone',
            ],
            'social_profile_visibility' => [
                'type'    => 'VARCHAR',
                'length'  => 16,
                'default' => 'members',
            ],
            'social_discoverable' => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '1',
            ],
            'social_notify_friend_request' => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '1',
            ],
            'social_notify_friend_accept' => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '1',
            ],
            'social_notify_follow' => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '1',
            ],
            'social_settings_updated' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0',
            ],
        ];
        $table_package['social_rate_limits'] = [
            'social_rate_user_id' => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'key'      => 1,
            ],
            'social_rate_action' => [
                'type'    => 'VARCHAR',
                'length'  => 32,
                'key'     => 1,
                'default' => '',
            ],
            'social_rate_window' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0',
            ],
            'social_rate_attempts' => [
                'type'     => 'SMALLINT',
                'length'   => 5,
                'unsigned' => TRUE,
                'default'  => '0',
            ],
            '__indexes' => [
                'idx_social_rate_window' => [
                    'columns' => ['social_rate_window'],
                ],
            ],
        ];
        $table_package['submissions'] = [
            'submit_id'        => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (submit_id)
                'unsigned'       => TRUE,
            ], //submit_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'submit_type'      => [
                'type'   => 'CHAR',
                'length' => 1,
            ],// submit_type CHAR(1) NOT NULL,
            'submit_user'      => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'default'  => '0',
                'key'      => 2
            ],//submit_user MEDIUMINT(8) UNSIGNED DEFAULT '0' NOT NULL,
            'submit_datestamp' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0',
                'key'      => 2  // @noted: changed
            ], //submit_datestamp INT(10) UNSIGNED DEFAULT '0' NOT NULL,
            'submit_criteria'  => [
                'type' => 'TEXT',
            ], //submit_criteria TEXT NOT NULL,
        ];
        $table_package['suspends'] = [
            'suspend_id'        => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (suspend_id)
                'unsigned'       => TRUE,
            ], //suspend_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'suspended_user'    => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'key'      => 2
            ], //suspended_user MEDIUMINT(8) UNSIGNED NOT NULL,
            'suspending_admin'  => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'default'  => '1',
                'key'      => 2
            ], //suspending_admin MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
            'suspend_ip'        => [
                'type'    => 'VARCHAR',
                'length'  => 45,
                'default' => ''
            ], //suspend_ip VARCHAR(45) NOT NULL DEFAULT '',
            'suspend_ip_type'   => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '4'
            ], //suspend_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
            'suspend_date'      => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //suspend_date INT(10) NOT NULL DEFAULT '0',
            'suspend_reason'    => [
                'type' => 'TEXT'
            ], //suspend_reason TEXT NOT NULL,
            'suspend_type'      => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], //suspend_type TINYINT(1) NOT NULL DEFAULT '0',
            'reinstating_admin' => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'default'  => '1',
                'key'      => 2
            ], //reinstating_admin MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
            'reinstate_reason'  => [
                'type' => 'TEXT'
            ], //reinstate_reason TEXT NOT NULL,
            'reinstate_date'    => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //reinstate_date INT(10) NOT NULL DEFAULT '0',
            'reinstate_ip'      => [
                'type'    => 'VARCHAR',
                'length'  => 45,
                'default' => ''
            ], //reinstate_ip VARCHAR(45) NOT NULL DEFAULT '',
            'reinstate_ip_type' => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '4'
            ], //reinstate_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
        ];
        $table_package['user_field_cats'] = [
            'field_cat_id'    => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (field_cat_id)
                'unsigned'       => TRUE,
            ],//field_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT ,
            'field_cat_name'  => [
                'type' => 'TEXT',
            ],//field_cat_name TEXT NOT NULL,
            'field_parent'    => [
                'type'     => 'MEDIUMINT',
                'length'   => 8,
                'unsigned' => TRUE,
                'default'  => '0'
            ],//field_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'field_cat_db'    => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ],//field_cat_db VARCHAR(100) NOT NULL,
            'field_cat_index' => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'default' => ''
            ],//field_cat_index VARCHAR(200) NOT NULL,
            'field_cat_class' => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => ''
            ],//field_cat_class VARCHAR(50) NOT NULL,
            'field_cat_order' => [
                'type'     => 'SMALLINT',
                'length'   => 5,
                'key'      => 2, //@noted: changed
                'unsigned' => TRUE,
            ]//field_cat_order SMALLINT(5) UNSIGNED NOT NULL ,
        ];
        $table_package['user_fields'] = [
            'field_id'           => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (field_id)
                'unsigned'       => TRUE,
            ], //field_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'field_title'        => [
                'type' => 'TEXT'
            ], //field_title TEXT NOT NULL,
            'field_name'         => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => ''
            ], //field_name VARCHAR(50) NOT NULL,
            'field_cat'          => [
                'type'     => 'MEDIUMINT',
                'length'   => 8,
                'default'  => 1,
                'unsigned' => TRUE
            ], //field_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
            'field_type'         => [
                'type'   => 'VARCHAR',
                'length' => 25,
            ], //field_type VARCHAR(25) NOT NULL,
            'field_default'      => [
                'type' => 'TEXT'
            ], //field_default TEXT NOT NULL,
            'field_options'      => [
                'type' => 'TEXT'
            ], //field_options TEXT NOT NULL,
            'field_error'        => [
                'type'   => 'VARCHAR',
                'length' => 50,
            ], //field_error VARCHAR(50) NOT NULL,
            'field_required'     => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //field_required TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            'field_log'          => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //field_log TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            'field_registration' => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //field_registration TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            'field_order'        => [
                'type'     => 'SMALLINT',
                'length'   => 5,
                'unsigned' => TRUE,
                'key'      => 2, //KEY field_order (field_order)
                'default'  => '0'
            ], //field_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
            'field_config'       => [
                'type' => 'TEXT'
            ] //field_config TEXT NOT NULL,
        ];
        $table_package['user_groups'] = [
            'group_id'          => [
                'type'           => 'TINYINT',
                'length'         => 3,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (group_id)
                'unsigned'       => TRUE,
            ], //group_id TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
            'group_name'        => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ], //group_name VARCHAR(100) NOT NULL,
            'group_description' => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'default' => ''
            ], //group_description VARCHAR(200) NOT NULL,
            'group_icon'        => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ] //group_icon VARCHAR(100) NOT NULL,
        ];
        $table_package['user_log'] = [
            'userlog_id'        => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (userlog_id)
                'unsigned'       => TRUE,
            ], //userlog_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT
            'userlog_user_id'   => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'key'      => 2, //KEY userlog_user_id (userlog_user_id)
                'default'  => '0'
            ], //userlog_user_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            'userlog_field'     => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'key'     => 2, //KEY userlog_field (userlog_field)
                'default' => ''
            ], //userlog_field VARCHAR(50) NOT NULL DEFAULT '',
            'userlog_value_new' => [
                'type' => 'TEXT'
            ], //userlog_value_new TEXT NOT NULL,
            'userlog_value_old' => [
                'type' => 'TEXT'
            ], //userlog_value_old TEXT NOT NULL,
            'userlog_timestamp' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //userlog_timestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
        ];
        $table_package['users'] = [
            'user_id'              => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (user_id),
                'unsigned'       => TRUE,
            ], // user_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'user_name'            => [
                'type'      => 'VARCHAR',
                'length'    => 30,
                'key'       => 2, //KEY user_name (user_name),
                'full_text' => TRUE, // FULLTEXT (user_name ASC)
                'default'   => ''
            ], //user_name VARCHAR(30) NOT NULL DEFAULT '',
            'user_algo'            => [
                'type'    => 'VARCHAR',
                'length'  => 10,
                'default' => 'sha256'
            ], //user_algo VARCHAR(10) NOT NULL DEFAULT 'sha256',
            'user_salt'            => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => ''
            ], //user_salt VARCHAR(40) NOT NULL DEFAULT '',
            'user_password'        => [
                'type'    => 'VARCHAR',
                'length'  => 64,
                'default' => ''
            ], //user_password VARCHAR(64) NOT NULL DEFAULT '',
            'user_admin_algo'      => [
                'type'    => 'VARCHAR',
                'length'  => 10,
                'default' => 'sha256'
            ], //user_admin_algo VARCHAR(10) NOT NULL DEFAULT 'sha256',
            'user_admin_salt'      => [
                'type'    => 'VARCHAR',
                'length'  => 40,
                'default' => ''
            ], //user_admin_salt VARCHAR(40) NOT NULL DEFAULT '',
            'user_admin_password'  => [
                'type'    => 'VARCHAR',
                'length'  => 64,
                'default' => ''
            ], //user_admin_password VARCHAR(64) NOT NULL DEFAULT '',
            'user_email'           => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'key'     => 2,
                'default' => ''
            ], //user_email VARCHAR(100) NOT NULL DEFAULT '',
            'user_hide_email'      => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => 1,
                'unsigned' => TRUE,
            ], //user_hide_email TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
            'user_timezone'        => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => 'Europe/London'
            ], //user_timezone VARCHAR(50) NOT NULL DEFAULT 'Europe/London',
            'user_avatar'          => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => '',
            ], //user_avatar VARCHAR(100) NOT NULL DEFAULT '',
            'user_posts'           => [
                'type'     => 'SMALLINT',
                'length'   => 5,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //user_posts SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
            'user_threads'         => [
                'type' => 'TEXT'
            ], //user_threads TEXT NOT NULL,
            'user_joined'          => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'key'      => 2, //KEY user_joined (user_joined),
                'default'  => '0'
            ], //user_joined INT(10) UNSIGNED NOT NULL DEFAULT '0',
            'user_lastvisit'       => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'key'      => 2, //KEY user_lastvisit (user_lastvisit)
                'default'  => '0'
            ], //user_lastvisit INT(10) UNSIGNED NOT NULL DEFAULT '0',
            'user_ip'              => [
                'type'    => 'VARCHAR',
                'length'  => 45,
                'default' => '0.0.0.0'
            ], //user_ip VARCHAR(45) NOT NULL DEFAULT '0.0.0.0',
            'user_ip_type'         => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => 4,
                'unsigned' => TRUE,
            ], //user_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
            'user_rights'          => [
                'type' => 'TEXT'
            ], //user_rights TEXT NOT NULL,
            'user_groups'          => [
                'type' => 'TEXT'
            ], //user_groups TEXT NOT NULL,
            'user_level'           => [
                'type'    => 'TINYINT',
                'length'  => 4,
                'default' => -101
            ], //user_level TINYINT(4) NOT NULL DEFAULT '-101',
            'user_status'          => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'key'      => 2,
                'default'  => '0'
            ], //user_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            'user_inbox'           => [
                'type'     => 'SMALLINT',
                'length'   => 6,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //user_inbox SMALLINT(6) unsigned not null default '0',
            'user_outbox'          => [
                'type'     => 'SMALLINT',
                'length'   => 6,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //user_outbox SMALLINT(6) unsigned not null default '0',
            'user_archive'         => [
                'type'     => 'SMALLINT',
                'length'   => 6,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //user_archive SMALLINT(6) unsigned not null default '0',
            'user_pm_email_notify' => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], //user_pm_email_notify TINYINT(1) not null default '0',
            'user_pm_save_sent'    => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], //user_pm_save_sent TINYINT(1) not null default '0',
            'user_actiontime'      => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //user_actiontime INT(10) UNSIGNED NOT NULL DEFAULT '0',
            'user_session'         => [
                'type'    => 'VARCHAR',
                'length'  => 170,
                'default' => ''
            ],
            'user_theme'           => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => 'Default'
            ], //user_theme VARCHAR(100) NOT NULL DEFAULT 'Default',
            'user_admin_theme'     => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => 'Default'
            ], //user_admin_theme VARCHAR(100) NOT NULL DEFAULT 'Default',
            'user_realname'        => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ], //user_realname VARCHAR(100) NOT NULL DEFAULT '',
            'user_bio'             => [
                'type' => 'TEXT'
            ], //user_bio TEXT NOT NULL,
            'user_location'        => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => ''
            ], //user_location VARCHAR(50) NOT NULL DEFAULT '',
            'user_state'           => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ], //user_state VARCHAR(100) NOT NULL DEFAULT '',
            'user_city'            => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ], //user_city VARCHAR(100) NOT NULL DEFAULT '',
            'user_birthdate'       => [
                'type'    => 'DATE',
                'default' => '1900-01-01'
            ], //user_birthdate DATE NOT NULL DEFAULT '1900-01-01',
            'user_skype'           => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => '',
            ], //user_skype VARCHAR(100) NOT NULL DEFAULT '',
            'user_icq'             => [
                'type'    => 'VARCHAR',
                'length'  => 15,
                'default' => '',
            ], //user_icq VARCHAR(15) NOT NULL DEFAULT '',
            'user_web'             => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'default' => '',
            ], //user_web VARCHAR(200) NOT NULL DEFAULT '',
            'user_twitter'         => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => '',
            ], //user_twitter VARCHAR(100) NOT NULL DEFAULT '',
            'user_linkedin'        => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => '',
            ], //user_linkedin VARCHAR(100) NOT NULL DEFAULT '',
            'user_discord'         => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => '',
            ], //user_discord VARCHAR(100) NOT NULL DEFAULT '',
            'user_sig'             => [
                'type' => 'TEXT'
            ], //user_sig TEXT NOT NULL,
            'user_language'        => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => $localeset
            ], //user_language VARCHAR(50) NOT NULL DEFAULT '...';
        ];

        $table_package['user_logins'] = [
            'login_id' => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, // PRIMARY KEY
                'unsigned'       => TRUE,
            ],
            'user_id' => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'unsigned'       => TRUE,
                'key'            => 2, // INDEX for quick user lookups
                'default'        => '0'
            ],
            'login_provider' => [
                'type'           => 'VARCHAR',
                'length'         => 50,
                'default'        => 'google',
                'key'            => 3, // Part of a composite index
            ],
            'login_uid' => [
                'type'           => 'VARCHAR',
                'length'         => 255,
                'default'        => '',
                'key'            => 3, // Unique identifier from provider (e.g., Google sub)
            ],
            'login_email' => [
                'type'           => 'VARCHAR',
                'length'         => 255,
                'default'        => ''
            ],
            'login_token' => [
                'type'           => 'TEXT',
                'default'        => ''
            ],
            'login_refresh_token' => [
                'type'           => 'TEXT',
                'default'        => ''
            ],
            'login_datestamp' => [
                'type'           => 'INT',
                'length'         => 10,
                'unsigned'       => TRUE,
                'default'        => '0'
            ]
        ];

        $table_package['theme'] = [
            'theme_id'        => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (theme_id)
                'unsigned'       => TRUE,
            ], //theme_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'theme_name'      => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => ''
            ], //theme_name VARCHAR(50) NOT NULL,
            'theme_title'     => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => ''
            ], //theme_title VARCHAR(50) NOT NULL,
            'theme_file'      => [
                'type'    => 'VARCHAR',
                'length'  => 200,
                'default' => ''
            ], //theme_file VARCHAR(200) NOT NULL,
            'theme_datestamp' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //theme_datestamp INT(10) UNSIGNED DEFAULT '0' NOT NULL,
            'theme_user'      => [
                'type'     => 'BIGINT',
                'length'   => 20,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //theme_user MEDIUMINT(8) UNSIGNED NOT NULL,
            'theme_active'    => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //theme_active TINYINT(1) UNSIGNED NOT NULL, @noted: change
            'theme_config'    => [
                'type' => 'TEXT'
            ]
        ];

        /*$table_package['user_notify'] = [
            'notice_id'        => [
                'type'           => 'MEDIUMINT',
                'length'         => 11,
                'auto_increment' => TRUE,
                'key'            => 1, //PRIMARY KEY (theme_id)
                'unsigned'       => TRUE,
            ],
            'notice_from'      => [
                'type'     => 'MEDIUMINT',
                'length'   => 11,
                'unsigned' => TRUE,
                'default'  => '0'
            ],
            'notice_to'        => [
                'type'     => 'MEDIUMINT',
                'length'   => 11,
                'unsigned' => TRUE,
                'default'  => '0'
            ],
            'notice_message'   => [
                'type' => 'TEXT',
            ],
            'notice_event'     => [
                'type' => 'TEXT',
            ],
            'notice_datestamp' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0'
            ],
            'notice_timestamp' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => '0'
            ],
            'notice_read'      => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ]
        ];*/

        // Add the new notifications table schema here
        $table_package['notifications'] = [
            'notification_id' => [
                'type' => 'BIGINT',
                'length' => '20',
                'auto_increment' => TRUE,
                'key' => 1, // PRIMARY KEY
                'unsigned' => TRUE,
            ],
            'notification_user_id' => [
                'type' => 'INT',
                'length' => '10',
                'unsigned' => TRUE,
                'default' => '0',
                'key' => 2,
            ],
            'notification_sender_id' => [
                'type' => 'INT',
                'length' => '10',
                'unsigned' => TRUE,
                'default' => '0',
                'key' => 2,
            ],
            'notification_infusion' => [
                'type' => 'VARCHAR',
                'length' => '255',
                'default' => ''
            ],
            'notification_type' => [
                'type' => 'VARCHAR',
                'length' => '50',
                'default' => 'info'
            ],
            'notification_title' => [
                'type' => 'VARCHAR',
                'length' => '255',
                'default' => ''
            ],
            'notification_message' => [
                'type' => 'TEXT',
                'length' => NULL,
            ],
            'notification_link' => [
                'type' => 'VARCHAR',
                'length' => '255',
                'default' => ''
            ],
            'notification_icon' => [
                'type' => 'VARCHAR',
                'length' => '100',
                'default' => ''
            ],
            'notification_key' => [
                'type' => 'VARCHAR',
                'length' => '100',
                'default' => 'NULL',
                'null' => TRUE,
            ],
            'notification_status' => [
                'type' => 'TINYINT',
                'length' => '1',
                'default' => '0',
                'unsigned' => TRUE
            ],
            'notification_created_at' => [
                'type' => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'notification_updated_at' => [
                'type' => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
                'on_update' => 'CURRENT_TIMESTAMP',
            ],
            '__indexes' => [
                'uq_notification_user_key' => [
                    'columns' => ['notification_user_id', 'notification_key'],
                    'unique'  => TRUE,
                ],
                'idx_notification_unread' => [
                    'columns' => ['notification_user_id', 'notification_status', 'notification_created_at'],
                ],
            ],
        ];


        return array_merge($table_package, self::get_ai_tables());
    }

    /**
     * Canonical provider-neutral AI Intelligence tables.
     *
     * These definitions live with the rest of the PHPFusion core schema so the
     * regular installer and schema comparison process remain authoritative.
     *
     * @return array<string, array<string, array<string, mixed>>>
     */
    public static function get_ai_tables(): array {
        return [
            'ai_providers' => [
                'provider_id' => self::aiId(),
                'provider_key' => self::aiString(64),
                'provider_name' => self::aiString(100),
                'driver' => self::aiString(191),
                'base_url' => self::aiString(255),
                'endpoint_path' => self::aiString(255),
                'default_model' => self::aiString(120),
                'config_json' => self::aiJson(),
                'priority' => self::aiInteger('SMALLINT', 5, 100),
                'timeout_seconds' => self::aiInteger('SMALLINT', 5, 30),
                'monthly_budget' => ['type' => 'DECIMAL', 'length' => '12,4', 'default' => '0.0000', 'unsigned' => TRUE],
                'status' => self::aiString(20, 'disabled'),
                'last_test_status' => self::aiString(20, 'untested'),
                'last_test_message' => ['type' => 'TEXT', 'null' => TRUE, 'default' => 'NULL'],
                'last_tested_at' => self::aiDateTime(),
                'created_by' => self::aiUserId(),
                'updated_by' => self::aiUserId(),
                'created_at' => self::aiTimestamp(),
                'updated_at' => self::aiTimestamp(TRUE),
                '__indexes' => [
                    'uq_ai_provider_key' => ['columns' => ['provider_key'], 'unique' => TRUE],
                    'idx_ai_provider_status' => ['columns' => ['status', 'priority']],
                ],
            ],
            'ai_credentials' => [
                'credential_id' => self::aiId(),
                'provider_id' => self::aiForeignId(),
                'credential_name' => self::aiString(100, 'Primary'),
                'cipher' => self::aiString(32),
                'ciphertext' => ['type' => 'MEDIUMTEXT'],
                'nonce' => ['type' => 'TEXT'],
                'fingerprint' => self::aiString(16),
                'key_version' => self::aiInteger('SMALLINT', 5, 1),
                'status' => self::aiString(20, 'active'),
                'rotated_at' => self::aiDateTime(),
                'created_by' => self::aiUserId(),
                'updated_by' => self::aiUserId(),
                'created_at' => self::aiTimestamp(),
                'updated_at' => self::aiTimestamp(TRUE),
                '__indexes' => [
                    'uq_ai_provider_credential_name' => ['columns' => ['provider_id', 'credential_name'], 'unique' => TRUE],
                    'idx_ai_credential_status' => ['columns' => ['provider_id', 'status']],
                ],
            ],
            'ai_tasks' => [
                'task_id' => self::aiId(),
                'task_namespace' => self::aiString(80),
                'task_key' => self::aiString(120),
                'task_title' => self::aiString(160),
                'task_description' => ['type' => 'TEXT'],
                'owner' => self::aiString(120, 'core'),
                'input_schema_json' => self::aiJson(),
                'output_schema_json' => self::aiJson(),
                'default_provider_id' => self::aiNullableForeignId(),
                'default_persona_id' => self::aiNullableForeignId(),
                'published_revision_id' => self::aiNullableForeignId(),
                'execution_mode' => self::aiString(20, 'single'),
                'context_source_key' => self::aiString(191),
                'chain_max_attempts' => self::aiInteger('SMALLINT', 5, 2),
                'chain_timeout_seconds' => self::aiInteger('SMALLINT', 5, 120),
                'final_step_key' => self::aiString(80),
                'developer_version' => self::aiString(40, '1.0.0'),
                'lock_version' => self::aiInteger('INT', 10, 1),
                'status' => self::aiString(20, 'draft'),
                'created_by' => self::aiUserId(),
                'updated_by' => self::aiUserId(),
                'created_at' => self::aiTimestamp(),
                'updated_at' => self::aiTimestamp(TRUE),
                '__indexes' => [
                    'uq_ai_task_key' => ['columns' => ['task_namespace', 'task_key'], 'unique' => TRUE],
                    'idx_ai_task_owner_status' => ['columns' => ['owner', 'status']],
                    'idx_ai_task_execution' => ['columns' => ['execution_mode', 'status']],
                ],
            ],
            'ai_prompt_revisions' => [
                'revision_id' => self::aiId(),
                'task_id' => self::aiForeignId(),
                'revision_number' => self::aiInteger('INT', 10, 1),
                'system_template' => ['type' => 'MEDIUMTEXT'],
                'user_template' => ['type' => 'MEDIUMTEXT'],
                'model_options_json' => self::aiJson(),
                'change_summary' => self::aiString(255),
                'content_checksum' => self::aiString(64),
                'state' => self::aiString(20, 'draft'),
                'created_by' => self::aiUserId(),
                'reviewed_by' => self::aiUserId(),
                'published_by' => self::aiUserId(),
                'created_at' => self::aiTimestamp(),
                'reviewed_at' => self::aiDateTime(),
                'published_at' => self::aiDateTime(),
                '__indexes' => [
                    'uq_ai_prompt_revision' => ['columns' => ['task_id', 'revision_number'], 'unique' => TRUE],
                    'idx_ai_prompt_state' => ['columns' => ['task_id', 'state', 'created_at']],
                ],
            ],
            'ai_personas' => [
                'persona_id' => self::aiId(),
                'persona_key' => self::aiString(120),
                'persona_name' => self::aiString(160),
                'persona_role' => self::aiString(160),
                'description' => ['type' => 'TEXT'],
                'system_prompt' => ['type' => 'MEDIUMTEXT'],
                'traits_json' => self::aiJson(),
                'knowledge_boundaries_json' => self::aiJson(),
                'example_dialogue_json' => self::aiJson(),
                'owner' => self::aiString(120, 'core'),
                'version' => self::aiString(40, '1.0.0'),
                'status' => self::aiString(20, 'draft'),
                'created_by' => self::aiUserId(),
                'updated_by' => self::aiUserId(),
                'created_at' => self::aiTimestamp(),
                'updated_at' => self::aiTimestamp(TRUE),
                '__indexes' => [
                    'uq_ai_persona_key' => ['columns' => ['persona_key'], 'unique' => TRUE],
                    'idx_ai_persona_owner_status' => ['columns' => ['owner', 'status']],
                ],
            ],
            'ai_context_sources' => [
                'source_id' => self::aiId(),
                'source_key' => self::aiString(191),
                'source_title' => self::aiString(160),
                'description' => ['type' => 'TEXT'],
                'owner' => self::aiString(120, 'core'),
                'input_schema_json' => self::aiJson(),
                'output_schema_json' => self::aiJson(),
                'developer_version' => self::aiString(40, '1.0.0'),
                'status' => self::aiString(20, 'disabled'),
                'created_by' => self::aiUserId(),
                'updated_by' => self::aiUserId(),
                'created_at' => self::aiTimestamp(),
                'updated_at' => self::aiTimestamp(TRUE),
                '__indexes' => [
                    'uq_ai_context_source_key' => ['columns' => ['source_key'], 'unique' => TRUE],
                    'idx_ai_context_source_owner_status' => ['columns' => ['owner', 'status']],
                ],
            ],
            'ai_task_steps' => [
                'step_id' => self::aiId(),
                'task_id' => self::aiForeignId(),
                'step_key' => self::aiString(80),
                'step_title' => self::aiString(160),
                'step_type' => self::aiString(20, 'agent'),
                'persona_id' => self::aiNullableForeignId(),
                'provider_id' => self::aiNullableForeignId(),
                'step_order' => self::aiInteger('SMALLINT', 5, 10),
                'depends_on_step_key' => self::aiString(80),
                'system_template' => ['type' => 'MEDIUMTEXT'],
                'user_template' => ['type' => 'MEDIUMTEXT'],
                'input_binding_json' => self::aiJson(),
                'output_binding_json' => self::aiJson(),
                'output_schema_json' => self::aiJson(),
                'model_options_json' => self::aiJson(),
                'condition_json' => self::aiJson(),
                'failure_policy' => self::aiString(20, 'stop'),
                'repair_step_key' => self::aiString(80),
                'max_attempts' => self::aiInteger('SMALLINT', 5, 1),
                'timeout_seconds' => self::aiInteger('SMALLINT', 5, 30),
                'result_path' => self::aiString(191),
                'is_final' => self::aiInteger('TINYINT', 1, 0),
                'status' => self::aiString(20, 'active'),
                'created_at' => self::aiTimestamp(),
                'updated_at' => self::aiTimestamp(TRUE),
                '__indexes' => [
                    'uq_ai_task_step_key' => ['columns' => ['task_id', 'step_key'], 'unique' => TRUE],
                    'idx_ai_task_step_order' => ['columns' => ['task_id', 'step_order', 'status']],
                    'idx_ai_task_step_type' => ['columns' => ['task_id', 'step_type', 'status']],
                ],
            ],
            'ai_runs' => [
                'run_id' => self::aiId(),
                'task_id' => self::aiForeignId(),
                'revision_id' => self::aiNullableForeignId(),
                'provider_id' => self::aiNullableForeignId(),
                'persona_id' => self::aiNullableForeignId(),
                'actor_id' => self::aiUserId(),
                'provider_request_id' => self::aiString(191),
                'model' => self::aiString(120),
                'status' => self::aiString(20, 'pending'),
                'input_tokens' => self::aiInteger('INT', 10, 0),
                'output_tokens' => self::aiInteger('INT', 10, 0),
                'latency_ms' => self::aiInteger('INT', 10, 0),
                'estimated_cost' => ['type' => 'DECIMAL', 'length' => '12,6', 'default' => '0.000000', 'unsigned' => TRUE],
                'error_code' => self::aiString(80),
                'error_message' => ['type' => 'TEXT', 'null' => TRUE, 'default' => 'NULL'],
                'request_checksum' => self::aiString(64),
                'started_at' => self::aiTimestamp(),
                'completed_at' => self::aiDateTime(),
                '__indexes' => [
                    'idx_ai_runs_task_status' => ['columns' => ['task_id', 'status', 'started_at']],
                    'idx_ai_runs_provider' => ['columns' => ['provider_id', 'started_at']],
                    'idx_ai_runs_actor' => ['columns' => ['actor_id', 'started_at']],
                ],
            ],
            'ai_step_runs' => [
                'step_run_id' => self::aiId(),
                'run_id' => self::aiForeignId(),
                'step_id' => self::aiForeignId(),
                'attempt_number' => self::aiInteger('SMALLINT', 5, 1),
                'provider_id' => self::aiNullableForeignId(),
                'persona_id' => self::aiNullableForeignId(),
                'provider_request_id' => self::aiString(191),
                'model' => self::aiString(120),
                'status' => self::aiString(20, 'pending'),
                'input_tokens' => self::aiInteger('INT', 10, 0),
                'output_tokens' => self::aiInteger('INT', 10, 0),
                'latency_ms' => self::aiInteger('INT', 10, 0),
                'estimated_cost' => ['type' => 'DECIMAL', 'length' => '12,6', 'default' => '0.000000', 'unsigned' => TRUE],
                'error_code' => self::aiString(80),
                'error_message' => ['type' => 'TEXT', 'null' => TRUE, 'default' => 'NULL'],
                'request_checksum' => self::aiString(64),
                'started_at' => self::aiTimestamp(),
                'completed_at' => self::aiDateTime(),
                '__indexes' => [
                    'uq_ai_step_run_attempt' => ['columns' => ['run_id', 'step_id', 'attempt_number'], 'unique' => TRUE],
                    'idx_ai_step_runs_status' => ['columns' => ['status', 'started_at']],
                    'idx_ai_step_runs_provider' => ['columns' => ['provider_id', 'started_at']],
                ],
            ],
            'ai_audit_log' => [
                'audit_id' => self::aiId(),
                'entity_type' => self::aiString(40),
                'entity_id' => self::aiForeignId(),
                'action' => self::aiString(60),
                'actor_id' => self::aiUserId(),
                'before_checksum' => self::aiString(64),
                'after_checksum' => self::aiString(64),
                'metadata_json' => self::aiJson(),
                'created_at' => self::aiTimestamp(),
                '__indexes' => [
                    'idx_ai_audit_entity' => ['columns' => ['entity_type', 'entity_id', 'created_at']],
                    'idx_ai_audit_actor' => ['columns' => ['actor_id', 'created_at']],
                ],
            ],
            'ai_comments' => [
                'comment_id' => self::aiId(),
                'entity_type' => self::aiString(40),
                'entity_id' => self::aiForeignId(),
                'revision_id' => self::aiNullableForeignId(),
                'comment_text' => ['type' => 'TEXT'],
                'status' => self::aiString(20, 'open'),
                'created_by' => self::aiUserId(),
                'resolved_by' => self::aiUserId(),
                'created_at' => self::aiTimestamp(),
                'resolved_at' => self::aiDateTime(),
                '__indexes' => [
                    'idx_ai_comments_entity' => ['columns' => ['entity_type', 'entity_id', 'status', 'created_at']],
                ],
            ],
        ];
    }

    /**
     * Convert the embedded AI metadata into the legacy upgrade newtable format.
     *
     * @return array<string, string>
     */
    public static function get_ai_upgrade_table_definitions(string $prefix): array {
        $definitions = [];
        foreach (self::get_ai_tables() as $table => $columns) {
            $lines = [];
            $primaryKeys = [];
            foreach ($columns as $name => $attributes) {
                if (str_starts_with((string)$name, '__')) {
                    continue;
                }
                $lines[] = self::aiColumnSql((string)$name, $attributes);
                if ((int)($attributes['key'] ?? 0) === 1) {
                    $primaryKeys[] = (string)$name;
                }
            }
            if ($primaryKeys) {
                $lines[] = 'PRIMARY KEY (`'.implode('`, `', $primaryKeys).'`)';
            }
            foreach (($columns['__indexes'] ?? []) as $name => $index) {
                $indexColumns = '`'.implode('`, `', (array)($index['columns'] ?? [])).'`';
                $lines[] = (!empty($index['unique']) ? 'UNIQUE KEY' : 'KEY')
                    .' `'.$name.'` ('.$indexColumns.')';
            }
            $qualified = $prefix.$table;
            $definitions[$qualified] = $qualified.' ('.implode(', ', $lines).') '
                .'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        }
        return $definitions;
    }

    /** @param array<string, mixed> $attributes */
    private static function aiColumnSql(string $name, array $attributes): string {
        $sql = '`'.$name.'` '.strtoupper((string)$attributes['type']);
        if (isset($attributes['length'])) {
            $sql .= '('.$attributes['length'].')';
        }
        if (!empty($attributes['unsigned'])) {
            $sql .= ' UNSIGNED';
        }
        $sql .= !empty($attributes['null']) ? ' NULL' : ' NOT NULL';
        if (array_key_exists('default', $attributes)) {
            $default = $attributes['default'];
            if ($default === NULL || $default === 'NULL') {
                $sql .= ' DEFAULT NULL';
            } elseif ($default === 'CURRENT_TIMESTAMP') {
                $sql .= ' DEFAULT CURRENT_TIMESTAMP';
            } else {
                $sql .= " DEFAULT '".addslashes((string)$default)."'";
            }
        }
        if (!empty($attributes['auto_increment'])) {
            $sql .= ' AUTO_INCREMENT';
        }
        if (!empty($attributes['on_update'])) {
            $sql .= ' ON UPDATE '.$attributes['on_update'];
        }
        return $sql;
    }

    private static function aiId(): array {
        return ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'auto_increment' => TRUE, 'key' => 1];
    }

    private static function aiForeignId(): array {
        return ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'default' => 0];
    }

    private static function aiNullableForeignId(): array {
        return ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'null' => TRUE, 'default' => 'NULL'];
    }

    private static function aiUserId(): array {
        return ['type' => 'BIGINT', 'length' => 20, 'unsigned' => TRUE, 'default' => 0];
    }

    private static function aiInteger(string $type, int $length, int $default): array {
        return ['type' => $type, 'length' => $length, 'unsigned' => TRUE, 'default' => $default];
    }

    private static function aiString(int $length, string $default = ''): array {
        return ['type' => 'VARCHAR', 'length' => $length, 'default' => $default];
    }

    private static function aiJson(): array {
        return ['type' => 'JSON', 'null' => TRUE, 'default' => 'NULL'];
    }

    private static function aiDateTime(): array {
        return ['type' => 'DATETIME', 'null' => TRUE, 'default' => 'NULL'];
    }

    private static function aiTimestamp(bool $update = FALSE): array {
        $column = ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'];
        if ($update) {
            $column['on_update'] = 'CURRENT_TIMESTAMP';
        }
        return $column;
    }

}
