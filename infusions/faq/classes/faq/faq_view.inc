<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles/classes/articles/articles_view.php
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

namespace PHPFusion\FAQ;

/**
 * Controller package for if/else
 * Class ArticlesView
 *
 * @package PHPFusion\Articles
 */
class FaqView extends Faq {
    public function display_faq() {
        // Display Article
        if (isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
            $info = $this->set_FaqInfo($_GET['cat_id']);
        } else {
            $info = $this->set_FaqInfo();
        }
        display_main_faq($info);
    }
}
