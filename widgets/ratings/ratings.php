<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Ratings/ratings.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Class commentsWidget
 */
class ratingsWidget extends \PHPFusion\Page\PageModel implements \PHPFusion\Page\WidgetInterface {

    public function display_widget($colData) {
        ob_start();
        require_once INCLUDES."ratings_include.php";
        showratings("C", self::$data['page_id'], BASEDIR."viewpage.php?page_id=".self::$data['page_id']);
        $html = ob_get_contents();
        ob_end_clean();

        return (string)$html;
    }

}
