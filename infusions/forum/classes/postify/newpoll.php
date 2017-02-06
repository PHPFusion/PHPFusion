<?php
namespace PHPFusion\Forums\Postify;

use PHPFusion\BreadCrumbs;

/**
 * Class Postify_Newpoll
 *
 * @status  Stable
 * @package PHPFusion\Forums\Postify
 */
class Postify_Newpoll extends Forum_Postify {

    public function execute() {
        add_to_title(self::$locale['global_201'].self::$locale['forum_0607']);
        BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0607']]);
        render_postify([
            'title'       => self::$locale['forum_0366'],
            'error'       => $this->get_postify_error_message(),
            'description' => self::$locale['forum_0607'],
            'link'        => $this->get_postify_uri()
        ]);
    }
}