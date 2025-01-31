<?php

/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: bbcode_include.php
| Author: Wooya
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
 * Display BBCode buttons for a given textarea.
 *
 * @param string $width          Width of the DIV which holds all the BBCode buttons.
 * @param string $textarea_name  The name of the textarea the BBCodes will be inserted to.
 * @param string $inputform_name The name of the form the BBCodes will be inserted to.
 * @param string $selected       Show only certain BBCodes separated by |.
 *
 * @return string
 */
function display_bbcodes($width, $textarea_name = "message", $inputform_name = "inputform", $selected = "") {

    $bbcode_cache = cache_bbcode();
    $sel_bbcodes = '';
    if ($selected) {
        $sel_bbcodes = explode("|", $selected);
    }
    $__BBCODE__ = [];
    $bbcodes = "";

    foreach ($bbcode_cache as $bbcode) {
        if (file_exists(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {
            $locale_file = LOCALE.LOCALESET."bbcodes/".$bbcode.".php";
            \PHPFusion\Locale::setLocale($locale_file);
        } else if (file_exists(LOCALE."English/bbcodes/".$bbcode.".php")) {
            $locale_file = LOCALE."English/bbcodes/".$bbcode.".php";
            \PHPFusion\Locale::setLocale($locale_file);
        }
    }
    $locale = fusion_get_locale();
    foreach ($bbcode_cache as $bbcode) {
        if ($selected && in_array($bbcode, $sel_bbcodes)) {
            include(INCLUDES."bbcodes/".$bbcode."_bbcode_include_var.php");
        } else if (!$selected) {
            include(INCLUDES."bbcodes/".$bbcode."_bbcode_include_var.php");
        }
    }

    $check_path = __DIR__.'/bbcodes/images/';
    $img_path = FUSION_ROOT.fusion_get_settings('site_path').'includes/bbcodes/images/';
    $count = 1;
    foreach ($__BBCODE__ as $bbdata) {

        $input_type = 'input';
        if (isset($bbdata['svg'])) {
            $input_type = 'button';
            $input_content = '<span class="bbcode-icon-wrap">'.$bbdata['svg'].'</span>';
        } else {

            // old method, add a new one with embedded svg
            switch ($check_path.$bbdata['value']) {

                case is_file($check_path.$bbdata['value'].".svg"):
                    $type = "type='image' style='width: 24px; height: 24px;' src='".$img_path.$bbdata['value'].".svg'";
                    break;
                case is_file($check_path.$bbdata['value'].".png"):
                    $type = "type='image' src='".$img_path.$bbdata['value'].".png'";
                    break;
                case is_file($check_path.$bbdata['value'].".gif"):
                    $type = "type='image' src='".$img_path.$bbdata['value'].".gif'";
                    break;
                case is_file($check_path.$bbdata['value'].".jpg"):
                    $type = "type='image' src='".$img_path.$bbdata['value'].".jpg'";
                    break;
                default:
                    $type = "type='button' value='".$bbdata['value']."'";
            }
        }


        // these can become faster
        if (isset($bbdata['onclick']) && $bbdata['onclick'] != "") {
            $onclick = $bbdata['onclick'];
        } else {
            $onclick = "insertText('".$textarea_name."','".(!empty($bbdata['bbcode_start']) ? $bbdata['bbcode_start'] : '')."','".$inputform_name."');return false;";

            if (isset($bbdata['bbcode_end']) && $bbdata['bbcode_end'] != "") {
                $onclick = "addText('".$textarea_name."','".$bbdata['bbcode_start']."','".$bbdata['bbcode_end']."','".$inputform_name."');return false;";
            }

        }

        $onmouseover = "";
        if (isset($bbdata['onmouseover']) && $bbdata['onmouseover'] != "") {
            $onmouseover = "onMouseOver=\"".$bbdata['onmouseover']."\"";
        }

        $onmouseout = "";
        if (isset($bbdata['onmouseout']) && $bbdata['onmouseout'] != "") {
            $onmouseout = "onMouseOut=\"".$bbdata['onmouseout']."\"";
        }


        $dropdown_bbcodes = '';
        $dropdown = '';
        $dropdown_caret = '';
        $dropdown_bbcode_end = '';
        if (array_key_exists('dropdown', $bbdata) && $bbdata['dropdown'] == TRUE) {
            $dropdown = 'data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"';
            $dropdown_caret = '<i class="fas fa-caret-down"></i>';
            $bbcodes .= '<div class="dropdown">';
            $dropdown_bbcodes = '</div>';
        }

        $id = '';
        $bbdata['id'] = '';
        if (isset($bbdata['id']) && !empty($bbdata['id'])) {
            $id = 'id="'.$bbdata['id'].'"';
        }

        if ($input_type == 'button') {

            $bbcodes .= substr($bbdata['value'],
                0,
                1) != "!" && isset($input_content) ? "<button type='button' class='bbcode' ".$id." onclick=\"".$onclick."\" ".$onmouseover." ".$onmouseout." title='".$bbdata['description']."' aria-label='".$bbdata['description']."' aria-disabled='false' aria-pressed='false' ".$dropdown."/>\n".$input_content.$dropdown_caret."\n</button>\n" : "";
        } else {
            $bbcodes .= substr($bbdata['value'],
                0,
                1) != "!" ? "<input ".$type." class='bbcode' ".$id." onclick=\"".$onclick."\" ".$onmouseover." ".$onmouseout." title='".$bbdata['description']."' aria-label='".$bbdata['description']."' aria-disabled='false' aria-pressed='false' ".$dropdown."/>\n" : "";
        }


        if (isset($bbdata['dropdown']) && $bbdata['dropdown'] == TRUE) {
            $dropdown_tag = "div";
            if (!empty($data['dropdown_items'])) {
                $dropdown_tag = "ul";
            }
            $bbcodes .= "<$dropdown_tag class='bbcode-popup dropdown-menu ".($count > 2 ? "dropdown-menu-right " : "").(!empty($bbdata['dropdown_class']) ? $bbdata['dropdown_class'] : '')."' ".(!empty($bbdata['dropdown_style']) ? 'style="'.$bbdata['dropdown_style'].'"' : '')." aria-labelledby='".$bbdata['id']."'>\n";
            $dropdown_bbcode_end = "</$dropdown_tag>\n";
        }

        if (isset($bbdata['html_start']) && $bbdata['html_start'] != "") {
            $bbcodes .= $bbdata['html_start']."\n";
        }

        if (isset($bbdata['includejscript']) && $bbdata['includejscript'] != "") {
            fusion_load_script(INCLUDES.'bbcodes/'.$bbdata['includejscript']);
            // $bbcodes .= "<script type='text/javascript' src='" . INCLUDES . "bbcodes/" . $bbdata['includejscript'] . "'></script>\n";
        }

        if (isset($bbdata['calljscript']) && $bbdata['calljscript'] != "") {
            add_to_jquery($bbdata['calljscript']);
            // $bbcodes .= "<script type='text/javascript'>\n<!--\n" . $bbdata['calljscript'] . "\n-->\n</script>\n";
        }

        // Deprecate phpfunction
        if (isset($bbdata['phpfunction']) && $bbdata['phpfunction'] != "") {
            set_error(E_COMPILE_WARNING, 'BBcode method phpfunction is deprecated. Please replace with php_function and php_function_args', $bbdata['value'], 186);
        }

        if (!empty($bbdata['php_function']) && !empty($bbdata['php_function_args'])) {
            $bbcodes .= call_user_func_array($bbdata['php_function'], $bbdata['php_function_args']);
        }

        if (isset($bbdata['html_middle']) && $bbdata['html_middle'] != "") {
            $bbcodes .= $bbdata['html_middle']."\n";
        }

        if (!empty($bbdata['dropdown_items']) && is_array($bbdata['dropdown_items'])) {
            $bbcodes .= "<li>".implode('</li><li>', $bbdata['dropdown_items'])."</li>";
        }

        if (isset($bbdata['html_end']) && $bbdata['html_end'] != "") {
            $bbcodes .= $bbdata['html_end']."\n";
        }

        // dont have to query twice.
        $bbcodes .= $dropdown_bbcode_end.$dropdown_bbcodes;
        $count++;
    }

    unset ($__BBCODE__);

    return "<div style='width:".$width."'>\n".$bbcodes."</div>\n";
}

/**
 * Strip declared BBCodes and their content away from quoted messages.
 *
 * @param string $text A string containing text with bbcodes.
 *
 * @return string
 */
function strip_bbcodes($text) {

    $textarea_name = '';
    $inputform_name = '';

    if (!iADMIN) {
        $bbcode_cache = cache_bbcode();
        if (is_array($bbcode_cache) && count($bbcode_cache)) {
            foreach ($bbcode_cache as $bbcode) {
                if (file_exists(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {
                    include(LOCALE.LOCALESET."bbcodes/".$bbcode.".php");
                }
                include(INCLUDES."bbcodes/".$bbcode."_bbcode_include_var.php");
            }
        }
        if (isset($__BBCODE_NOT_QUOTABLE__) && sizeof($__BBCODE_NOT_QUOTABLE__) != 0) {
            foreach ($__BBCODE_NOT_QUOTABLE__ as $bbname) {
                $text = preg_replace('#\['.$bbname.'(.*?)\](.*?)\[/'.$bbname.'\]#si', '', $text);
            }
            unset ($__BBCODE_NOT_QUOTABLE__);
        }
    }

    return $text;
}


function clean_smileys($val) {

    if (ctype_alpha($val)) {
        return $val;
    }

    return '\\'.$val;
}

function smiley_replace() {

    $list = [];
    foreach (cache_smileys() as $smiley) {
        //$smiley_code = implode('', array_map('clean_smileys', str_split($smiley['smiley_code'], 1)));
        $list['\\<img style=\\"width:20px;height:20px;\\" src=\\"'.get_image("smiley_".$smiley['smiley_text']).'\\" alt=\\"\\"\\>'] = $smiley['smiley_code'];
    }

    return $list;
}

function smiley_regex() {

    $list = [];
    foreach (cache_smileys() as $smiley) {
        $smiley_code = implode('', array_map('clean_smileys', str_split($smiley['smiley_code'], 1)));
        $list[$smiley_code] = '<img style="width:20px;height:20px;" src="'.get_image("smiley_".$smiley['smiley_text']).'" alt="">';
    }

    return $list;
}

/**
 * Show smiley's button which will insert the smileys to the given textarea and form.
 *
 * @return string  Option for users to insert smileys in a post by displaying the smiley's button.
 */
function display_smiley_options(): string {

    $res = "<div style='display:flex;flex-direction:row;flex-wrap:wrap;width:100%;'>\n";
    foreach (cache_smileys() as $smiley) {

        $res .= "<span class='icons smaller' data-action='bbcode_smileys' data-smiley='".$smiley['smiley_code']."'>";
        $res .= "<img class='smiley' src='".get_image("smiley_".$smiley['smiley_text'])."' alt='".$smiley['smiley_code']."' title='".$smiley['smiley_code']."'>"; //onclick=\"insertText('".$textarea."', '".$smiley['smiley_code']."', '".$form."');
        $res .= "</span>";
    }
    $res .= "</div>\n";

    return $res;
}


function cache_color() {

    return [
        '#1ABC9C', '#17A085', '#2ECC71', '#27AE60', '#3498DB',
        '#2980B9', '#9B59B6', '#8E44AD', '#34495E', '#2C3E50',
        '#F1C40E', '#F39C12', '#E67E21', '#D35400', '#E74C3C',
        '#C0392B', '#ECF0F1', '#BDC3C7', '#94A5A6', '#7F8C8D'
    ];
}

function display_bbcode_colors() {

    $colors = "";
    $i = 0;
    foreach (cache_color() as $color) {
        if ($i != 0 && ($i % 10 == 0)) {
            $colors .= "<br />\n";
        }
        $colors .= "<span class='btns icons smaller' data-action='color' data-tagname='$color' style='background:$color;border-radius:5px;display:inline-block;'>";
        $colors .= "</span>";
    }

    return $colors;
}
