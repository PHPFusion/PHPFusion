<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Search.inc
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
 * @package PHPFusion
 */
class Search extends Search_Engine {

    /**
     * Global view function
     */
    public static function display_search() {
        self::display_search_form();
        if (strlen(self::get_param('stext')) >= 3) {
            self::display_results();
        } elseif (isset($_POST['stext'])) {
            self::display_noResults();
        }
    }
    /**
     * Template for search form
     * @return string
     */
    public static function render_search() {
        // Developer's option - mutate this string to output your template
        if (self::$render_search) {
            return self::$render_search;
        }
        // Designer's option - make a design a HTML in render_search() function in your theme
        if (function_exists('render_search')) {
            return (string)render_search();
        }
        // Default
        return fusion_get_function('opentable', "{%title%}")."
        <div class='spacer-sm'>
        <div class='clearfix m-b-15'>{%search_text%}</div>
        {%search_method%}
        {%search_button%}
        </div>
        <div class='row search'>
        <div class='col-xs-12 col-sm-6'>
            <div class='well p-20'>
            {%search_sources%}
            </div>
        </div><div class='col-xs-12 col-sm-6'>
            <div class='well p-20'>
            {%search_areas%}
            </div>
            <div class='well p-20'>
            {%sort_areas%}
            </div>
            <div class='well p-20'>
            {%char_areas%}
            </div>
        </div></div>
        ".fusion_get_function('closetable');
    }
    /**
     * Template for search no results when stext is less than 3
     * @return string
     */
    public static function render_search_no_result() {
        if (self::$search_no_result) {
            return self::$search_no_result;
        }
        if (function_exists('render_search_no_result')) {
            return (string)render_search_no_result();
        }

        return fusion_get_function('opentable', '{%title%}')."<div class='alert alert-warning m-t-10'>{%content%}</div>\n".fusion_get_function('closetable');
    }

    /**
     * Template for search result item counting
     * @return string
     */
    public static function render_search_count() {
        if (self::$search_count) {
            return self::$search_count;
        }
        if (function_exists('render_search_count')) {
            return (string)render_search_count();
        }
        return "<div class='clearfix spacer-xs well'>{%search_count%}{%result_text%}</div>";
    }

    /*
     * Template for search Simple Items
     */
    public static function render_search_item($new_window = FALSE) {
        if (function_exists('render_search_item')) {
            return (string)self::render_search_item($new_window);
        }
        return "
        <!---results-->
        <li>
            <div class='clearfix'>
                <div class='pull-left m-r-10'>{%item_image%}</div>
                <div class='overflow-hide'>
                    <span class='va' style='height:30px;'></span>
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
            return (string)self::render_search_item_list();
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
     * @return string
     */
    public static function render_search_item_image() {
        if (self::$search_item_image) {
            return self::$search_item_image;
        }
        if (function_exists('render_search_item_image')) {
            return (string)self::render_search_item_image();
        }
        return "
        <li class='display-inline-block m-2'><a href='{%item_url%}'>{%item_image%}</a></li>
        ";
    }

    /**
     * Template for each search module results
     * @return string
     */
    public static function render_search_item_wrapper() {
        if (self::$search_item_wrapper) {
            return self::$search_item_wrapper;
        }
        if (function_exists('render_search_item')) {
            return (string)render_search_item_wrapper();
        }

        return "
        <div class='panel panel-default'>
            <div class='panel-body'>
                <h4>{%image%} {%search_title%}</h4>
            </div>
            <div class='panel-body'><div class='spacer-xs'>".THEME_BULLET."{%search_result%}</div></div>
            <div class='panel-body'>
                <ul class='block spacer-xs'>{%search_content%}</ul>
            </div>
        </div>
        ";
    }
}