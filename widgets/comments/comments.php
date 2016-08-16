<?php

/**
 * Class commentsWidget
 */
class commentsWidget extends \PHPFusion\Page\PageModel implements \PHPFusion\Page\WidgetInterface {

    public function display_widget($colData) {
        ob_start();
        require_once INCLUDES."comments_include.php";
        showcomments("C", DB_CUSTOM_PAGES, "page_id", self::$data['page_id'], BASEDIR."viewpage.php?page_id=".self::$data['page_id']);
        $html = ob_get_contents();
        ob_end_clean();

        return (string)$html;
    }

}