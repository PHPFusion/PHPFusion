<?php
namespace PHPFusion\Forums\Postify;

use PHPFusion\BreadCrumbs;

/**
 * Class Postify_New
 *
 * @status  Stable
 *
 * @package PHPFusion\Forums\Postify
 */
class Postify_New extends Forum_Postify {
    public function execute() {
        add_to_title(self::$locale['global_201'].self::$locale['forum_0501']);
        BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0501']]);
        render_postify([
            'title'   => self::$locale['forum_0501'],
            'message' => $this->get_postify_error_message() ?: self::$locale['forum_0543'],
            'error'   => $this->get_postify_error_message(),
            'link'    => $this->get_postify_uri()
        ]);
    }
}