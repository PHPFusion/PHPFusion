<?php

namespace PHPFusion\Page;
/**
 * This is the front end editing software
 * Class PageController
 * @package PHPFusion\Page
 */
class PageController extends PageComposer {

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