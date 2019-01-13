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
        if (isset($_GET['weblink_id']) && isnum($_GET['weblink_id'])) {
            return self::set_WeblinkCount($_GET['weblink_id']);
        } else if (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
            $info = $this->set_WeblinkCatInfo($_GET['cat_id']);
            return display_weblinks_item($info);
        }
        $info = $this->set_WeblinksInfo();
        return display_main_weblinks($info);
    }
}
