<?php
namespace PHPFusion\Forums\Postify;

use PHPFusion\BreadCrumbs;

/**
 * Forum Edit Reply
 * Class Postify_Reply
 *
 * @Status  Stable
 *
 * @package PHPFusion\Forums\Postify
 */
class Postify_Deletepoll extends Forum_Postify {

    public function execute() {
        add_to_title(self::$locale['global_201'].(self::$locale['forum_0615']));
        BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0615']]);
        redirect(self::$default_redirect_link, 2);
        render_postify([
            'title'       => self::$locale['forum_0615'],
            'error'       => $this->get_postify_error_message(),
            'description' => self::$locale['forum_0547'],
            'link'        => $this->get_postify_uri(),
        ]);
    }
}