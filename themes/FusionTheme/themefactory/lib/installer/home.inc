<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Home.inc
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
namespace ThemeFactory\Lib\Installer;

/**
 * Showcase Page Composer
 */
class HomeInstall {

    private static $locale = [];

    public function __construct() {
        // Page
        self::$locale = fusion_get_locale('', THEME."themefactory/lib/installer/locale/".LANGUAGE.".php");
        $row_id = [];

        $cpData = [
            'page_id'           => 0,
            'page_cat'          => 0,
            'page_link_cat'     => 0,
            'page_title'        => self::$locale['homeSetup_0100'],
            'page_access'       => 0,
            'page_content'      => '',
            'page_keywords'     => self::$locale['homeSetup_0100'],
            'page_status'       => 1,
            'page_user'         => 1,
            'page_datestamp'    => time(),
            'page_language'     => LANGUAGE,
            'page_grid_id'      => 0,
            'page_content_id'   => 0,
            'page_left_panel'   => 1,
            'page_right_panel'  => 0,
            'page_top_panel'    => 0,
            'page_header_panel' => 0,
            'page_bottom_panel' => 0,
            'page_footer_panel' => 0,
        ];
        $page_id = dbquery_insert(DB_CUSTOM_PAGES, $cpData, 'save');
        // Build the grid for content
        $cgData = [
            'page_grid_id'           => 0,
            'page_id'                => $page_id,
            'page_grid_container'    => 1,
            'page_grid_column_count' => 1,
            'page_grid_html_id'      => 'content',
            'page_grid_class'        => '',
            'page_grid_order'        => 7,
        ];
        $grid_id = dbquery_insert(DB_CUSTOM_PAGES_GRID, $cgData, 'save');
        // Build the column for content
        $clData = [
            'page_id'            => $page_id,
            'page_grid_id'       => $grid_id,
            'page_content_id'    => 0,
            'page_content_type'  => 'Content',
            'page_content'       => '',
            'page_options'       => '',
            'page_content_order' => 1,
            'page_widget'        => ''
        ];
        $col_id = dbquery_insert(DB_CUSTOM_PAGES_CONTENT, $clData, 'save');
        // update
        dbquery("UPDATE ".DB_CUSTOM_PAGES." SET page_grid_id='$grid_id', page_content_id='$col_id' WHERE page_id='$page_id'");

        // Carousel Data
        $rowData[1] = [
            'page_grid_id'           => 0,
            'page_id'                => $page_id,
            'page_grid_container'    => 0,
            'page_grid_column_count' => 1,
            'page_grid_html_id'      => 'carousel_wrapper',
            'page_grid_class'        => '',
            'page_grid_order'        => 1,
        ];

        // theme
        $rowData[3] = [
            'page_grid_id'           => 0,
            'page_id'                => $page_id,
            'page_grid_container'    => 0,
            'page_grid_column_count' => 1,
            'page_grid_html_id'      => 'showcase',
            'page_grid_class'        => 'container',
            'page_grid_order'        => 3,
        ];

        $rowData[4] = [
            'page_grid_id'           => 0,
            'page_id'                => $page_id,
            'page_grid_container'    => 1,
            'page_grid_column_count' => 1,
            'page_grid_html_id'      => 'home_feature',
            'page_grid_class'        => '',
            'page_grid_order'        => 4,
        ];

        $rowData[5] = [
            'page_grid_id'           => 0,
            'page_id'                => $page_id,
            'page_grid_container'    => 1,
            'page_grid_column_count' => 1,
            'page_grid_html_id'      => 'support',
            'page_grid_class'        => '',
            'page_grid_order'        => 5,
        ];

        $rowData[6] = [
            'page_grid_id'           => 0,
            'page_id'                => $page_id,
            'page_grid_container'    => 0,
            'page_grid_column_count' => 1,
            'page_grid_html_id'      => 'home_parallax',
            'page_grid_class'        => '',
            'page_grid_order'        => 6,
        ];

        foreach ($rowData as $rowKeys => $rowArray) {
            $row_id[$rowKeys] = dbquery_insert(DB_CUSTOM_PAGES_GRID, $rowArray, 'save');
        }

        /**
         * End of row insertion
         */

        // Carousel - OK
        $sliderDesc = str_replace(["[b]", "[/b]", "[i]", "[/i]"], ["<strong>", "</strong>", "<i>", "</i>"],
            self::$locale['homeSetup_0102']);
        $sliderDesc .= "\n";
        $sliderDesc .= str_replace(["[b]", "[/b]", "[i]", "[/i]"], ["<strong>", "</strong>", "<i>", "</i>"],
            self::$locale['homeSetup_0103']);
        $sliderDesc .= "\n";
        $sliderDesc .= "<div class='logo'><img src='images/php-fusion-icon.png' alt='Logo'></div>";
        $slider_array[0] = [
            'slider_title'          => self::$locale['homeSetup_0101'],
            'slider_description'    => form_sanitizer($sliderDesc),
            'slider_link'           => '',
            'slider_order'          => 1,
            'slider_caption_offset' => '120',
            'slider_caption_align'  => 'text-left',
            'slider_title_size'     => 50,
            'slider_desc_size'      => 35,
            'slider_btn_size'       => '',
            'slider_image_src'      => 'default-carousel.jpg'
        ];
        $slider_options = [
            'slider_id'         => 'home_carousel',
            'slider_path'       => 'carousel',
            'slider_height'     => 700,
            'slider_navigation' => 0,
            'slider_indicator'  => 0,
            'slider_interval'   => 0,
        ];
        $colData[1] = [
            'page_id'            => $page_id,
            'page_grid_id'       => $row_id[1],
            'page_content_id'    => 0,
            'page_content_type'  => self::$locale['homeSetup_0104'],
            'page_content'       => \defender::serialize($slider_array),
            'page_options'       => \defender::serialize($slider_options),
            'page_content_order' => 1,
            'page_widget'        => 'slider'
        ];

        // Theme
        $colData[4] = [
            'page_id'            => $page_id,
            'page_grid_id'       => $row_id[3],
            'page_content_id'    => 0,
            'page_content_type'  => self::$locale['homeSetup_0107'],
            'page_content'       => \defender::serialize(
                [
                    'block_title'       => self::$locale['homeSetup_0112'],
                    'block_description' => self::$locale['homeSetup_0113'],
                    'block_align'       => 'text-left',
                    'block_class'       => '',
                    'block_margin'      => '150px 0',
                    'block_padding'     => ''
                ]
            ),
            'page_options'       => '',
            'page_content_order' => 1,
            'page_widget'        => 'block'
        ];

        // Why you'll love PHP-Fusion
        $colData[5] = [
            'page_id'            => $page_id,
            'page_grid_id'       => $row_id[4],
            'page_content_id'    => 0,
            'page_content_type'  => self::$locale['homeSetup_0107'],
            'page_content'       => \defender::serialize(
                [
                    'block_title'       => self::$locale['homeSetup_0114'],
                    'block_description' => self::$locale['homeSetup_0115'],
                    'block_align'       => 'text-center',
                    'block_class'       => '',
                    'block_margin'      => '70px 0',
                    'block_padding'     => ''
                ]
            ),
            'page_options'       => '',
            'page_content_order' => 1,
            'page_widget'        => 'block'
        ];

        // PFDN
        $content = str_replace(["[h4]", "[/h4]", "[p]", "[/p]"], ["<h4>", "</h4>", "<p>", "</p>"], self::$locale['homeSetup_0117']);
        $content .= str_replace(["[h4]", "[/h4]", "[p]", "[/p]"], ["<h4>", "</h4>", "<p>", "</p>"], self::$locale['homeSetup_0118']);
        $colData[6] = [
            'page_id'            => $page_id,
            'page_grid_id'       => $row_id[5],
            'page_content_id'    => 0,
            'page_content_type'  => self::$locale['homeSetup_0107'],
            'page_content'       => \defender::serialize(
                [
                    'block_title'       => self::$locale['homeSetup_0116'],
                    'block_description' => form_sanitizer($content),
                    'block_align'       => '',
                    'block_class'       => 'support',
                    'block_margin'      => '95px 0 0',
                    'block_padding'     => '30px'
                ]
            ),
            'page_options'       => '',
            'page_content_order' => 1,
            'page_widget'        => 'block'
        ];
        unset($content);

        $colData[7] = [
            'page_id'            => $page_id,
            'page_grid_id'       => $row_id[6],
            'page_content_id'    => 0,
            'page_content_type'  => self::$locale['homeSetup_0107'],
            'page_content'       => \defender::serialize(
                [
                    'block_title'       => self::$locale['homeSetup_0119'],
                    'block_description' => self::$locale['homeSetup_0120'],
                    'block_align'       => 'text-center',
                    'block_class'       => '',
                    'block_margin'      => '30px 0',
                    'block_padding'     => '60px'
                ]
            ),
            'page_options'       => '',
            'page_content_order' => 1,
            'page_widget'        => 'block'
        ];

        foreach ($colData as $row_Keys => $colArray) {
            dbquery_insert(DB_CUSTOM_PAGES_CONTENT, $colArray, 'save');
        }

        unset($colData);

        $last_inserted_home = dbresult(dbquery("SELECT MAX(page_id) 'max_page' FROM ".DB_CUSTOM_PAGES." ORDER BY page_id DESC LIMIT 1"), 0);
        dbquery("UPDATE ".DB_SETTINGS." SET settings_value='viewpage.php?page_id=$last_inserted_home' WHERE settings_name='opening_page'");

    }
}
