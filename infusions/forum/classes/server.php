<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Forum.php
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
namespace PHPFusion\Forums;

abstract class ForumServer {

    protected static $forum_settings = array();

    /**
     * Forum Settings
     * @return array
     */
    public static function get_forum_settings() {
        if (empty(self::$forum_settings)) {
            self::$forum_settings = get_settings('forum');
        }
        return (array) self::$forum_settings;
    }


}