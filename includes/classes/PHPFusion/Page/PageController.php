<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: PageController.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\Page;
/**
 * This is the front end editing software
 * Class PageController
 * @package PHPFusion\Page
 */
class PageController extends PageModel {

    /**
     * Return page composer object
     * @param bool|FALSE $set_info
     * @return null|static
     */
    protected static $page_instance = null;

    public static function getInstance() {
        if (empty(self::$page_instance)) {
            self::$page_instance = new Static;
        }
        return self::$page_instance;
    }

    // the entire administration interface
    public static function display_Page() {
        self::set_PageInfo();
        render_customPage(self::$info);
    }

}