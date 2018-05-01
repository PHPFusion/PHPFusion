<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: attachment.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Forums\Threads;

class Attachment {

    /**
     * Permissions for Attachments
     *
     * @var array
     */
    private static $permissions = [];

    /**
     * Object
     *
     * @param array $thread_info
     */
    public function __construct(array $thread_info) {
        self::set_attachment_permissions($thread_info['permissions']);
    }

    /**
     * Set Permissions Settings
     *
     * @param array $thread_info
     */
    private static function set_attachment_permissions(array $thread_info) {
        self::$permissions = $thread_info;
    }

    /**
     * Fetches Permissions Settings
     *
     * @param $key
     *
     * @return bool
     */
    private static function get_attachment_permissions($key) {
        return (isset(self::$permissions[$key])) ? self::$permissions[$key] : FALSE;
    }

    public static function get_attachments(array $thread_data) {

        $attachments = [];

        if (self::get_attachment_permissions("can_download_attach") == TRUE) {
            $a_result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id='".intval($thread_data['thread_id'])."' ORDER BY post_id ASC");
            if (dbrows($a_result) > 0) {
                while ($a_data = dbarray($a_result)) {
                    if (file_exists(INFUSIONS."forum/attachments/".$a_data['attach_name'])) {
                        //$this->thread_info['attachments'][$a_data['post_id']][] = $a_data;
                        $attachments[$a_data['post_id']][] = $a_data;
                    }
                }
            }
        }
        return (array)$attachments;
    }
}
