<?php
/**
 * @param $js_code
 */
function slickJS($js_code) {
    if (!defined('slickJS')) {
        define('slickJS', true);
        add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/slick/slick.min.css'>");
        add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/slick/slick-theme.min.css'>");
        add_to_footer("<script type='text/javascript' src='".INCLUDES."jquery/slick/slick.min.js'></script>");
    }

    if ($js_code) add_to_jquery($js_code);

};