<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
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
namespace PHPFusion\Forums\Postify;

/**
 * Class Postify_Newpoll
 *
 * @status  Stable
 * @package PHPFusion\Forums\Postify
 */
class Postify_Newpoll extends Forum_Postify {

    public function execute() {
        add_to_title(self::$locale['global_201'].self::$locale['forum_0607']);
        add_breadcrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0607']]);
        render_postify([
            'title'       => self::$locale['forum_0366'],
            'error'       => $this->getPostifyErrorMessage(),
            'description' => self::$locale['forum_0607'],
            'link'        => $this->getPostifyUri()
        ]);
    }
}
