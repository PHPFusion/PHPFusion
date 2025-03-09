<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: PageView.php
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
     * Displays HTML output of Page
     *
     * @param int $page_id
     */
    public function view($page_id = 0) {
        require_once THEMES."templates/global/custompage.tpl.php";
        self::setPageInfo($page_id);
        display_page(self::$info);
    }

    /**
     * @return string
     */
    public static function displayComposer() {
        $html = "";
        foreach (self::$composerData as $row_id => $columns) {
            if (!empty($columns)) {
                $row_prop = flatten_array($columns);
                $row_htmlId = (!empty($row_prop['page_grid_html_id']) ? $row_prop['page_grid_html_id'] : "row-".$row_id);
                $row_htmlClass = ($row_prop['page_grid_class'] ? " ".$row_prop['page_grid_class'] : "");
                if ($row_prop['page_content'] or $row_prop['page_options']) {
                    $html .= ($row_prop['page_grid_container'] ? "<div class='container'>\n" : "");
                    $html .= "<div id='$row_htmlId' class='row$row_htmlClass'>\n";
                    foreach ($columns as $colData) {
                        if ($colData['page_content_id']) {
                            $span = self::calculateSpan($colData['page_grid_column_count']);
                            $html .= "<div class='$span'>\n";
                            $html .= self::displayWidget($colData);
                            $html .= "</div>\n";
                        }
                    }
                    $html .= ($row_prop['page_grid_container'] ? "</div>\n" : "");
                    $html .= "</div>\n";
                }
            }
        }

        return $html;
    }
}
