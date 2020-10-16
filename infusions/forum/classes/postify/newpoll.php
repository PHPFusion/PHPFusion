<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: newpoll.php
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
namespace PHPFusion\Infusions\Forum\Classes\Postify;

use PHPFusion\Infusions\Forum\Classes\ForumPostify;
/**
 * Class Postify_Newpoll
 *
 * @status  Stable
 * @package PHPFusion\Infusions\Forum\Postify
 */
class PostifyNewpoll extends ForumPostify {

    public function execute() {
        add_to_title(self::$locale['global_201'].self::$locale['forum_0607']);
        add_breadcrumb(array('link' => FUSION_REQUEST, 'title' => self::$locale['forum_0607']));
        render_postify([
            'title'       => self::$locale['forum_0366'],
            'error'       => $this->get_postify_error_message(),
            'description' => self::$locale['forum_0607'],
            'link'        => $this->get_postify_uri()
        ]);
    }
}
