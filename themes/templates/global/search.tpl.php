<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: search.tpl.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

if (!function_exists('display_search')) {
    /**
     * Template for search form
     *
     * @return string
     */
    function render_search($info) {
        $locale = fusion_get_locale();

        $html = fusion_get_function('opentable', $info['title'])."
        <div class='spacer-sm'>
        <div class='clearfix m-b-15'>".$info['search_text']."</div>
        ".$info['search_method']."
        ".$info['search_button']."
        </div>
        <div class='row search'>
        
        <div class='col-xs-12 col-sm-6'>
            <div class='well p-20'>
            <p><strong>".$locale['405']."</strong></p>
            <table style='width:100%'>";

        foreach ($info['search_sources']['radio_buttons'] as $value) {
            $html .= "<tr><td>".$value."</td></tr>";
        }

        $html .= "
            </table>
            </div>
        </div>
        
        <div class='col-xs-12 col-sm-6'>
            <div class='well p-20'>
                <div class='row'>
                    <div class='col-xs-12 col-sm-3'>".$locale['420']."</div>
                    <div class='col-xs-12 col-sm-9'>
                    ".$info['search_areas']['datelimit']."
                    ".$info['search_areas']['title_message']."
                    ".$info['search_areas']['message']."
                    ".$info['search_areas']['title']."
                    </div>
                </div>
            </div>
            
            <div class='well p-20'>
            <div class='row'>
                <div class='col-xs-12 col-sm-3'>".$locale['440']."</div>
                <div class='col-xs-12 col-sm-9'>
                    ".$info['sort_areas']['sort']."
                    ".$info['sort_areas']['desc']."
                    ".$info['sort_areas']['asc']."
                </div>
                </div>
            </div>
            
            <div class='well p-20'>
            <div class='row'>
                <div class='col-xs-12 col-sm-3'>".$locale['460']."</div>
                <div class='col-xs-12 col-sm-9'>
                    ".$info['char_areas']."
                </div>
            </div>
            </div>
        </div></div>
        ".fusion_get_function('closetable');

        return $html;
    }
}

if (!function_exists('render_search_no_result')) {
    /**
     * Template for search no results when stext is less than 3
     *
     * @return string
     */
    function render_search_no_result($info) {
        return fusion_get_function('opentable', $info['title'])."
        <div class='alert alert-warning m-t-10'>".$info['content']."</div>
        ".fusion_get_function('closetable');
    }
}

if (!function_exists('render_search_count')) {
    /**
     * Template for search result item counting
     *
     * @return string
     */
    function render_search_count($info) {
        $html = '';
        if (!empty($info['disqualified_stexts'])) {
            $html .= "<div class='well m-t-10 text-center strong'>".$info['disqualified_stexts']."</div><br />";
        }

        if (!empty($info['navigation'])) {
            $html .= "<div class='center m-t-10 m-b-10'>".$info['navigation'].'</div>';
        }

        $html .= "<div class='clearfix spacer-xs well'>".$info['search_count'].$info['result_text']."</div>";
        $html .= "<div class='search_result'><div class='block'>".$info['results']."</div></div>";

        $html .= $info['navigation_result'];

        return $html;
    }
}

if (!function_exists('render_sesult')) {
    function render_search_no_reggsult($info) {
    }
}
