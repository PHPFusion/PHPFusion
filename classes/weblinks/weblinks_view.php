<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: weblinks/classes/weblinks/weblinks_view.php
| Author: Core Development Team (coredevs@phpfusion.com)
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
        $weblink_id = get( 'weblink_id' );
        $this->cat_id = get( 'cat_id' );
        if ($weblink_id && isnum( $weblink_id ) ) {
            return self::set_WeblinkCount( $weblink_id );
        } else if ($this->cat_id && isnum( $this->cat_id ) ) {
            $info = $this->set_WeblinkCatInfo( $this->cat_id );
            return display_weblinks_item( $info );
        }
        $info = $this->set_WeblinksInfo();
        return display_main_weblinks( $info );
    }
}
