<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Search.php
| Author: Frederick MC Chan
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;

use PHPFusion\Search\Search_Engine;

/**
 * Class Search
 * Template
 *
 * @package PHPFusion
 */
class Search extends Search_Engine {

    /*
     * Template for search Simple Items
     */
    public static function render_search_item($new_window = FALSE) {
        if (function_exists('render_search_item')) {
            return (string)render_search_item($new_window);
        }
        return "
        <!---results-->
        <li>
            <div class='clearfix'>
                <div class='pull-left m-r-10'>{%item_image%}</div>
                <div class='overflow-hide'>
                    <a ".($new_window ? "target='_blank' " : ' ')." href='{%item_url%}'><strong>{%item_title%}</strong></a><br/>{%item_description%}
                </div>
            </div>
        </li>
        <!---//results-->
        ";
    }

    /*
     * Template for search Full Listing Item Type
     */
    public static function render_search_item_list() {
        if (self::$search_item_list) {
            return self::$search_item_list;
        }
        if (function_exists('render_search_item_list')) {
            return (string)render_search_item_list();
        }
        return "
        <li class='spacer-xs'>
            <div class='clearfix'>
                <div class='pull-left m-r-10'>{%item_image%}</div>
                <div class='overflow-hide'><a href='{%item_url%}'><strong>{%item_title%}</strong></a><br/>
                {%item_description%}
                {%item_search_context%}
                {%item_search_criteria%}
                </div>
            </div>
        </li>
        ";
    }

    /**
     * Template for search Image Listing Item Type
     *
     * @return string
     */
    public static function render_search_item_image() {
        if (self::$search_item_image) {
            return self::$search_item_image;
        }
        if (function_exists('render_search_item_image')) {
            return (string)self::render_search_item_image();
        }
        return "<li><a href='{%item_url%}' class='display-inline-block m-2'>{%item_image%}</a> <div class='display-inline-block'>{%item_title%}{%item_description%}</div></li>";
    }

    /**
     * Template for each search module results
     *
     * @return string
     */
    public static function render_search_item_wrapper() {
        if (self::$search_item_wrapper) {
            return self::$search_item_wrapper;
        }
        if (function_exists('render_search_item_wrapper')) {
            return (string)render_search_item_wrapper();
        }

        return "
        <div class='panel panel-default'>
            <div class='panel-body'>
                <h4>{%image%} {%search_title%}</h4>
            </div>
            <div class='panel-body'><div class='spacer-xs'>&middot; {%search_result%}</div></div>
            <div class='panel-body'>
                <ul class='block spacer-xs'>{%search_content%}</ul>
            </div>
        </div>
        ";
    }
}
