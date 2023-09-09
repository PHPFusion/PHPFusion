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
    public static function get_core_tables( $localeset ) {
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
            'admin_image'    => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => ''
            ], //admin_image VARCHAR(50) NOT NULL DEFAULT '',
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
            ], //admin_language VARCHAR(50) NOT NULL DEFAULT '".$localeset."',
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
            ], //user_language VARCHAR(50) NOT NULL DEFAULT '".filter_input(INPUT_POST, 'localeset')."',
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
                'default'  => '0',
                'unsigned' => TRUE,
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
            ], //page_language VARCHAR(255) NOT NULL DEFAULT '".filter_input(INPUT_POST, 'localeset')."',
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
                'type'    => 'BIGINT',
                'length'  => 20,
                'key'     => 2,
                'default' => '0'
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
                'default' => '0'
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
            'inf_emails'  => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => '0',
                'unsigned' => TRUE,
            ]
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
            ], //template_key VARCHAR(20) NOT NULL,
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
                'length'  => 30,
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
            //panel_languages VARCHAR(200) NOT NULL DEFAULT '".implode('.', filter_input(INPUT_POST, 'enabled_languages', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: array(LANGUAGE))."',
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
            ] //link_language VARCHAR(50) NOT NULL DEFAULT '".filter_input(INPUT_POST, 'localeset')."',
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
                'length' => 4,
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
            ],
            'group_user_count'  => [
                'type'     => 'MEDIUMINT',
                'length'   => 11,
                'unsigned' => TRUE,
                'default'  => '0'
            ],
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

        $table_package['user_settings'] = [
            'user_id'                => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 2, //PRIMARY KEY (user_id),
                'unsigned'       => TRUE,
            ],
            'user_auth'              => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'unsigned' => TRUE,
                'default'  => 0,
            ],
            'user_hide_email'        => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => 1,
                'unsigned' => TRUE,
            ], //user_hide_email TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
            'user_hide_phone'        => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => 1,
                'unsigned' => TRUE,
            ],
            'user_inbox'             => [
                'type'     => 'SMALLINT',
                'length'   => 6,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //user_inbox SMALLINT(6) unsigned not null default '0',
            'user_outbox'            => [
                'type'     => 'SMALLINT',
                'length'   => 6,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //user_outbox SMALLINT(6) unsigned not null default '0',
            'user_archive'           => [
                'type'     => 'SMALLINT',
                'length'   => 6,
                'unsigned' => TRUE,
                'default'  => '0'
            ], //user_archive SMALLINT(6) unsigned not null default '0',
            'user_pm_save_sent'      => [
                'type'    => 'TINYINT',
                'length'  => 1,
                'default' => '0'
            ], //user_pm_save_sent TINYINT(1) not null default '0',
            'user_comments_notify'   => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => '0',
                'unsigned' => TRUE,
            ],
            'user_tag_notify'        => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => '0',
                'unsigned' => TRUE,
            ],
            'user_newsletter_notify' => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => '0',
                'unsigned' => TRUE,
            ],
            'user_follow_notify'     => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => '0',
                'unsigned' => TRUE,
            ],
            'user_pm_notify'         => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => '0',
                'unsigned' => TRUE,
            ],
            'user_pm_email'          => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => '0',
                'unsigned' => TRUE,
            ],
            'user_follow_email'      => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => '0',
                'unsigned' => TRUE,
            ],
            'user_feedback_email'    => [
                'type'     => 'TINYINT',
                'length'   => 1,
                'default'  => '0',
                'unsigned' => TRUE,
            ],
            'user_email_duration'    => [
                'type'     => 'TINYINT',
                'length'   => 2,
                'default'  => '4',
                'unsigned' => TRUE,
            ],
            'user_language'          => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => $localeset
            ], //user_language VARCHAR(50) NOT NULL DEFAULT '".filter_input(INPUT_POST, 'localeset')."',
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
            'user_firstname'       => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'key'     => 2,
                'default' => '',
            ],
            'user_lastname'        => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'key'     => 2,
                'default' => '',
            ],
            'user_addname'         => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'key'     => 2,
                'default' => '',
            ],
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
            ],
            'user_phone'           => [
                'type'    => 'VARCHAR',
                'length'  => 30,
                'key'     => 2,
                'default' => ''
            ],
            'user_email'           => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'key'     => 2,
                'default' => ''
            ], //user_email VARCHAR(100) NOT NULL DEFAULT '',
            'user_bio'             => [
                'type'    => 'VARCHAR',
                'length'  => 255,
                'default' => '',
            ],
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
            'user_auth_pin'        => [
                'type'    => 'VARCHAR',
                'length'  => 10,
                'default' => "",
            ],
            'user_auth_actiontime' => [
                'type'     => 'INT',
                'length'   => 10,
                'unsigned' => TRUE,
                'default'  => 0,
            ],
            'user_theme'           => [
                'type'    => 'VARCHAR',
                'length'  => 100,
                'default' => 'Default'
            ], //user_theme VARCHAR(100) NOT NULL DEFAULT 'Default',
            'user_location'        => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => ''
            ], //user_location VARCHAR(50) NOT NULL DEFAULT '',
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
            'user_sig'             => [
                'type' => 'TEXT'
            ], //user_sig TEXT NOT NULL,
            'user_timezone'        => [
                'type'    => 'VARCHAR',
                'length'  => 50,
                'default' => 'Europe/London'
            ], //user_timezone VARCHAR(50) NOT NULL DEFAULT 'Europe/London',
        ];

        $table_package['user_sessions'] = [
            'user_session_id' => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'auto_increment' => TRUE,
                'key'            => 1,
                'unsigned'       => TRUE,
            ],
            'user_id'      => [
                'type'           => 'BIGINT',
                'length'         => 20,
                'key'            => 2,
                'unsigned'       => TRUE,
            ], // user_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
            'user_session'         => [
                'type'    => 'VARCHAR',
                'length'  => 170,
                'default' => ''
            ],
            'user_ip'      => [
                'type'    => 'VARCHAR',
                'length'  => 45,
                'default' => '0.0.0.0'
            ], //user_name VARCHAR(30) NOT NULL DEFAULT '',
            'user_device' => [
                'type'    => 'VARCHAR',
                'length'  => 70,
                'default' => '',
            ],
            'user_os'      => [
                'type'    => 'VARCHAR',
                'length'  => 70,
                'default' => '',
            ],
            'user_browser' => [
                'type'    => 'VARCHAR',
                'length'  => 70,
                'default' => '',
            ],
            'user_logintime' => [
                'type'     => 'INT',
                'length'   => 10,
                'default'  => '0',
                'unsigned' => TRUE,
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

        return $table_package;
    }
}
