<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: deprecated.php
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

/**
 * Current microtime as float to calculate script start/end time
 *
 * @return float
 * @deprecated since version 9.00, use microtime(TRUE) instead
 */
function get_microtime() {
    return microtime(TRUE);
}

if (!function_exists("showsubdate")) {
    /**
     * Show the current time often this code is used in theme subheader.
     *
     * @return string
     * @deprecated
     */
    function showsubdate() {
        return ucwords(showdate(fusion_get_settings('shortdate'), time()));
    }
}

if (!function_exists("newsposter")) {
    /**
     * @param        $info
     * @param string $sep
     * @param string $class
     *
     * @return string
     * @deprecated
     */
    function newsposter($info, $sep = "", $class = "") {
        $locale = fusion_get_locale();
        $link_class = $class ? "class='$class'" : "";
        $res = "&middot; <span ".$link_class.">".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</span> ";
        $res .= $locale['global_071'].showdate("newsdate", $info['news_date']);
        $res .= $info['news_ext'] == "y" || $info['news_allow_comments'] ? $sep."\n" : "\n";

        return "<!--news_poster-->".$res;
    }
}

if (!function_exists("newsopts")) {
    /**
     * @param        $info
     * @param        $sep
     * @param string $class
     *
     * @return string
     * @deprecated
     */
    function newsopts($info, $sep, $class = "") {
        $locale = fusion_get_locale();
        $res = "";
        $link_class = $class ? "class='$class'" : "";
        if (!isset($_GET['readmore']) && $info['news_ext'] == "y") {
            $res = "<a href='".INFUSIONS."news/news.php?readmore=".$info['news_id']."' ".$link_class.">".$locale['global_072']."</a> ".$sep." ";
        }
        if ($info['news_allow_comments'] && fusion_get_settings('comments_enabled') == "1") {
            $res .= "<a href='".INFUSIONS."news/news.php?readmore=".$info['news_id']."#comments' ".$link_class.">".$info['news_comments'].($info['news_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a> ".$sep." ";
        }
        if ($info['news_ext'] == "y" || ($info['news_allow_comments'] && fusion_get_settings('comments_enabled') == "1")) {
            $res .= $info['news_reads'].$locale['global_074']."\n ".$sep;
        }
        $res .= "<a href='print.php?type=N&amp;item_id=".$info['news_id']."'><i class='fa fa-print' title='".$locale['global_075']."'></i></a>\n";

        return "<!--news_opts-->".$res;
    }
}

if (!function_exists("newscat")) {
    /**
     * @param        $info
     * @param string $sep
     * @param string $class
     *
     * @return string
     * @deprecated
     */
    function newscat($info, $sep = "", $class = "") {
        $locale = fusion_get_locale();
        $res = "";
        $link_class = $class ? "class='$class'" : "";
        $res .= $locale['global_079'];
        if ($info['cat_id']) {
            $res .= "<a href='news_cats.php?cat_id=".$info['cat_id']."' ".$link_class.">".$info['cat_name']."</a>";
        } else {
            $res .= "<a href='news_cats.php?cat_id=0' ".$link_class.">".$locale['global_080']."</a>";
        }

        return "<!--news_cat-->".$res." $sep ";
    }
}

if (!function_exists("articleposter")) {
    /**
     * @param        $info
     * @param string $sep
     * @param string $class
     *
     * @return string
     * @deprecated
     */
    function articleposter($info, $sep = "", $class = "") {
        $locale = fusion_get_locale();
        $link_class = $class ? "class='$class'" : "";
        $res = "&middot; ".$locale['global_070']."<span ".$link_class.">".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</span>\n";
        $res .= $locale['global_071'].showdate("newsdate", $info['article_date']);
        $res .= ($info['article_allow_comments'] && fusion_get_settings('comments_enabled') == "1" ? $sep."\n" : "\n");

        return "<!--article_poster-->".$res;
    }
}

if (!function_exists("articleopts")) {
    /**
     * @param $info
     * @param $sep
     *
     * @return string
     * @deprecated
     */
    function articleopts($info, $sep) {
        $locale = fusion_get_locale();
        $res = "";
        if ($info['article_allow_comments'] && fusion_get_settings('comments_enabled') == "1") {
            $res = "<a href='articles.php?article_id=".$info['article_id']."#comments'>".$info['article_comments'].($info['article_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a> ".$sep."\n";
        }
        $res .= $info['article_reads'].$locale['global_074']." ".$sep."\n";
        $res .= "<a href='print.php?type=A&amp;item_id=".$info['article_id']."'><i class='fa fa-print' title='".$locale['global_075']."'></i></a>\n";

        return "<!--article_opts-->".$res;
    }
}

if (!function_exists("articlecat")) {
    /**
     * @param        $info
     * @param string $sep
     * @param string $class
     *
     * @return string
     * @deprecated
     */
    function articlecat($info, $sep = "", $class = "") {
        $locale = fusion_get_locale();
        $res = "";
        $link_class = $class ? "class='$class'" : "";
        $res .= $locale['global_079'];
        if ($info['cat_id']) {
            $res .= "<a href='articles.php?cat_id=".$info['cat_id']."' ".$link_class.">".$info['cat_name']."</a>";
        } else {
            $res .= "<a href='articles.php?cat_id=0' ".$link_class.">".$locale['global_080']."</a>";
        }

        return "<!--article_cat-->".$res." $sep ";
    }
}

if (!function_exists("itemoptions")) {
    /**
     * @param $item_type
     * @param $item_id
     *
     * @return string
     * @deprecated
     */
    function itemoptions($item_type, $item_id) {
        $locale = fusion_get_locale();
        $res = "";
        if ($item_type == "N") {
            if (iADMIN && checkrights($item_type)) {
                $res .= "<!--article_news_opts--> &middot; <a href='".INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;action=edit&amp;news_id=".$item_id."'><i class='fa fa-pencil' title='".$locale['global_076']."'></i></a>\n";
            }
        } else if ($item_type == "A") {
            if (iADMIN && checkrights($item_type)) {
                $res .= "<!--article_admin_opts--> &middot; <a href='".INFUSIONS."articles/articles_admin.php".fusion_get_aidlink()."&amp;action=edit&amp;article_id=".$item_id."'><i class='fa fa-pencil' title='".$locale['global_076']."'></i></a>\n";
            }
        }

        return $res;
    }
}

/**
 * for sitelinks - not hierarchy
 *
 * @param string $cat
 *
 * @return array
 * @deprecated
 */
function getcategory($cat) {
    $presult = dbquery("SELECT link_id, link_name, link_order FROM ".DB_SITE_LINKS." WHERE link_id='$cat'");
    if (dbrows($presult) > 0) {
        $md[$cat] = "Menu Item Root";
        $result = dbquery("SELECT link_id, link_name FROM ".DB_SITE_LINKS." WHERE link_cat='$cat' ORDER BY link_order ASC");
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $link_id = $data['link_id'];
                $link_name = $data['link_name'];
                $md[$link_id] = "- ".$link_name."";
            }

            return $md;
        }
    }

    return [];
}


if (!function_exists('tablebreak')) {
    /**
     * @deprecated
     */
    function tablebreak() {
        echo "<div class='spacer-md'></div>";
    }
}

if (!function_exists('opensidex')) {
    /**
     * @param null $title
     *
     * @deprecated use openside()
     */
    function opensidex($title = NULL) {
        openside($title);
    }
}

if (!function_exists('closesidex')) {
    /**
     * @deprecated use closeside()
     */
    function closesidex() {
        closeside();
    }
}

/**
 * @param $value
 *
 * @return string
 *
 * @deprecated use format_code()
 */
function formatcode($value) {
    return format_code($value);
}

/**
 * Interpret output to match input of textarea having both bbcode, html and tinymce buttons
 *
 * @param string $value
 * @param bool   $parse_smileys
 * @param bool   $parse_bbcode
 * @param bool   $decode
 * @param string $default_image_folder
 * @param bool   $add_line_breaks
 * @param bool   $descript
 *
 * @return string
 *
 * @deprecated use parse_text()
 */
function parse_textarea($value, $parse_smileys = TRUE, $parse_bbcode = TRUE, $decode = TRUE, $default_image_folder = IMAGES, $add_line_breaks = FALSE, $descript = TRUE) {
    $options = [
        'parse_smileys'        => $parse_smileys,
        'parse_bbcode'         => $parse_bbcode,
        'decode'               => $decode,
        'default_image_folder' => $default_image_folder,
        'add_line_breaks'      => $add_line_breaks,
        'descript'             => $descript
    ];

    return parse_text($value, $options);
}

/**
 * This option has been removed from PHP ini.
 *
 * @deprecated
 */
define("QUOTES_GPC", (bool)ini_get('magic_quotes_gpc'));

/**
 * Strip Slash Function, only stripslashes if magic_quotes_gpc is on.
 *
 * @param string $text The input string.
 *
 * @return string String with backslashes stripped off (\' becomes ' and so on), double backslashes (\) are made into a single backslash (\).
 *
 * @deprecated use stripslashes()
 */
function stripslash($text) {
    if (QUOTES_GPC) {
        $text = stripslashes($text);
    }

    return $text;
}

/**
 * Add Slash Function, add correct number of slashes depending on quotes_gpc
 *
 * @param string $text
 *
 * @return string
 *
 * @deprecated use addslashes()
 */
function addslash($text) {
    if (!QUOTES_GPC) {
        $text = addslashes(addslashes($text));
    } else {
        $text = addslashes($text);
    }

    return $text;
}

/**
 * Create <option></option> from the entries in a given array.
 *
 * @param array  $options  Options.
 * @param string $selected The item in the options that you want to select by default.
 *
 * @return string Array as a list of options for a select.
 *
 * @deprecated use form_select()
 */
function makefileopts($options, $selected = "") {
    $res = "";
    foreach ($options as $item) {
        $sel = ($selected == $item ? " selected='selected'" : "");
        $res .= "<option value='".$item."' $sel>".$item."</option>\n";
    }

    return $res;
}

/**
 * Create a selection list of possible languages in list.
 *
 * @param string $selected_language
 *
 * @return string
 * @deprecated use form_select()
 */
function get_available_languages_list($selected_language = "") {
    $enabled_languages = fusion_get_enabled_languages();
    $res = "";
    foreach ($enabled_languages as $language) {
        $sel = ($selected_language == $language ? " selected='selected'" : "");
        $label = str_replace('_', ' ', $language);
        $res .= "<option value='".$language."' $sel>".$label."</option>\n";
    }

    return $res;
}

/**
 * Custom Error Handler
 *
 * @param int    $error_level   Severity
 * @param string $error_message $e->message
 * @param string $error_file    The file in question, run a debug_backtrace()[2] in the file
 * @param int    $error_line    The line in question, run a debug_backtrace()[2] in the file
 *
 * @deprecated use set_error()
 */
function setError($error_level, $error_message, $error_file, $error_line) {
    set_error($error_level, $error_message, $error_file, $error_line);
}


/**
 * Unnecessary constant.
 *
 * @deprecated use time()
 */
define("TIME", time());
