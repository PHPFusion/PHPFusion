<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/classes/weblinks/weblinks_view.php
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
namespace PHPFusion\Weblinks;

/**
 * Controller package for if/else
 * Class WeblinksView
 *
 * @package PHPFusion\Weblinks
 */
class WeblinksView extends Weblinks {
    public function display_weblink() {
        // filter_input - does not working with SEO
        $weblink_id = isset($_GET['weblink_id']) ? $_GET['weblink_id'] : 0;
        $this->cat_id = isset($_GET['cat_id']) ? $_GET['cat_id'] : 0;
        if (!empty($weblink_id) && isnum($weblink_id)) {
            return self::set_WeblinkCount($weblink_id);
        } else if (!empty($this->cat_id) && isnum($this->cat_id)) {
            $info = $this->set_WeblinkCatInfo($this->cat_id);
            return display_weblinks_item($info);
        }
        $info = $this->set_WeblinksInfo();
        return display_main_weblinks($info);
    }
}
