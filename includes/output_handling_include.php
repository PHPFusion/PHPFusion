<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: output_handling_include.php
| Author: Max Toball (Matonor)
| Co-Author: Takács Ákos (Rimelek)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\OutputHandler;

/**
 * Set the new title of the page
 *
 * @param string $title
 */
function set_title($title = "") {
    OutputHandler::setTitle($title);
}

/**
 * Append something to the title of the page
 *
 * @param string $addition
 */
function add_to_title($addition = "") {
    OutputHandler::addToTitle($addition);
}

/**
 * Set a meta tag by name
 *
 * @param string $name
 * @param string $content
 */
function set_meta($name, $content = "") {
    OutputHandler::setMeta($name, $content);
}

/**
 * Append something to a meta tag
 *
 * @param string $name
 * @param string $addition
 */
function add_to_meta($name, $addition = "") {
    OutputHandler::addToMeta($name, $addition);
}

/**
 * Add content to the html head
 *
 * @param string $tag
 */
function add_to_head($tag = "") {
    OutputHandler::addToHead($tag);
}

/**
 * Add content to the footer
 *
 * @param string $tag
 */
function add_to_footer($tag = "") {
    OutputHandler::addToFooter($tag);
}

/**
 * Replace something in the output using regexp
 *
 * @param string $target Regexp pattern without delimiters
 * @param string $replace The new content
 * @param string $modifiers Regexp modifiers
 */
function replace_in_output($target, $replace, $modifiers = "") {
    OutputHandler::replaceInOutput($target, $replace, $modifiers);
}

/**
 * Add a new output handler function
 *
 * @param callback $callback The name of a function or other callable object
 */
function add_handler($callback) {
    OutputHandler::addHandler($callback);
}

/**
 * Execute the output handlers
 *
 * @param string $output
 *
 * @return string
 */
function handle_output($output) {
    return OutputHandler::handleOutput($output);
}

/**
 * Add javascript source code to the output
 *
 * @param string $tag
 */
function add_to_jquery($tag = "") {
    OutputHandler::addToJQuery($tag);
}

/**
 * Add css code to the output
 *
 * @param string $tag
 */
function add_to_css($tag = "") {
    OutputHandler::addToCss($tag);
}
