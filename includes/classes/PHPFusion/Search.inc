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
        if (function_exists('render_search')) {
            return (string)render_search();
        }
        return open_table('{%title%}')."
        <div class='spacer-sm'>
        <div class='clearfix m-b-15'>{%search_text%}</div>
        {%search_method%}
        {%search_button%}
        </div>
        <div class='row'>
        <div class='col-xs-12 col-sm-6'>
            <div class='well'>
            {%search_sources%}
            </div>
        </div><div class='col-xs-12 col-sm-6'>
            <div class='well'>
            {%search_areas%}
            </div>
            <div class='well'>
            {%sort_areas%}
            </div>
            <div class='well'>
            {%char_areas%}
            </div>
        </div></div>
        ".close_table();
    }
    /**
     * Template for search no results when stext is less than 3
     * @return string
     */
    public static function render_search_no_result() {
        if (function_exists('render_search_no_result')) {
            return (string)render_search_no_result();
        }
        return open_table('{%title%}')."<div class='alert alert-warning m-t-10'>{%content%}</div>\n".close_table();
    }

    /**
     * Template for search result item counting
     * @return string
     */
    public static function render_search_count() {
        if (function_exists('render_search_count')) {
            return (string)render_search_count();
        }
        return "<div class='clearfix spacer-xs well'>{%search_count%}{%result_text%}</div>";
    }

    /**
     * Template for each search module results
     * @return string
     */
    public static function render_search_item() {
        if (function_exists('render_search_item')) {
            return (string)render_search_item();
        }
        return "<div class='panel panel-default'>\n
        <div class='panel-body'>
        <h4><i class='{%icon_class%}' style='color:#14BDA6'></i>{%search_title%}</h4>
        </div>\n
        <div class='panel-body'><div class='spacer-xs'>".THEME_BULLET."{%search_result%}</div></div>
        <div class='panel-body'>
        {%search_content%}
        </div>\n
        </div>\n
        ";
    }
}