<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Page/PageView.inc
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
namespace PHPFusion\Page;

class PageView extends PageController {

    /**
     * Return page composer object
     * @param bool|FALSE $set_info
     * @return null|static
     */
    protected static $page_instance = NULL;

    public static function getInstance($set_info = FALSE, $page_id = 0) {
        if (self::$page_instance === NULL) {
            self::$page_instance = new static();
            if ($set_info) {
                self::set_PageInfo($page_id);
            }
        }

        return (object)self::$page_instance;
    }

    /**
     * Displays HTML output of Page
     */
    public static function display_Page() {
        require_once THEMES."templates/global/custompage.php";
        display_page(self::$info);
    }

    public static function display_Composer() {
        ob_start();
        foreach (self::$composerData as $row_id => $columns) :
            if (!empty($columns)) :
                $row_prop = flatten_array($columns);
                $row_htmlId = ($row_prop['page_grid_html_id'] ? $row_prop['page_grid_html_id'] : "row-".$row_id);
                $row_htmlClass = ($row_prop['page_grid_class'] ? " ".$row_prop['page_grid_class'] : "");
                // check if there are any content in this row, if no, don't render
                if ($row_prop['page_content'] or $row_prop['page_options']) :
                ?>
                <div id="<?php echo $row_htmlId ?>" class="row<?php echo $row_htmlClass ?>">
                    <?php if ($row_prop['page_grid_container']) : ?>
                    <div class="container">
                        <?php endif;
                    foreach ($columns as $column_id => $colData) :
                        if ($colData['page_content_id']) :
                            ?>
                            <div class="<?php echo self::calculateSpan($colData['page_grid_column_count'], count($columns)) ?>">
                                <?php echo self::display_Widget($colData) ?>
                            </div>
                        <?php endif;
                    endforeach;
                        if ($row_prop['page_grid_container']) : ?>
                    </div>
                <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif;
        endforeach;
        $html = ob_get_contents();
        ob_end_clean();

        return (string)$html;
    }
}
