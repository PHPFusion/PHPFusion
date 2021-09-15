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

    foreach ($__BBCODE__ as $bbdata) {
        switch ($check_path.$bbdata['value']) {
            case file_exists($check_path.$bbdata['value'].".svg"):
                $type = "type='image' style='width: 24px; height: 24px;' src='".$img_path.$bbdata['value'].".svg'";
                break;
            case file_exists($check_path.$bbdata['value'].".png"):
                $type = "type='image' src='".$img_path.$bbdata['value'].".png'";
                break;
            case file_exists($check_path.$bbdata['value'].".gif"):
                $type = "type='image' src='".$img_path.$bbdata['value'].".gif'";
                break;
            case file_exists($check_path.$bbdata['value'].".jpg"):
                $type = "type='image' src='".$img_path.$bbdata['value'].".jpg'";
                break;
            default:
                $type = "type='button' value='".$bbdata['value']."'";
        }

        if (array_key_exists('onclick', $bbdata) && $bbdata['onclick'] != "") {
            $onclick = $bbdata['onclick'];
        } else {
            if (array_key_exists('bbcode_end', $bbdata) && $bbdata['bbcode_end'] != "") {
                $onclick = "addText('".$textarea_name."','".$bbdata['bbcode_start']."','".$bbdata['bbcode_end']."','".$inputform_name."');return false;";
            } else {
                $onclick = "insertText('".$textarea_name."','".(!empty($bbdata['bbcode_start']) ? $bbdata['bbcode_start'] : '')."','".$inputform_name."');return false;";
            }
        }
        if (array_key_exists('onmouseover', $bbdata) && $bbdata['onmouseover'] != "") {
            $onmouseover = "onMouseOver=\"".$bbdata['onmouseover']."\"";
        } else {
            $onmouseover = "";
        }
        if (array_key_exists('onmouseout', $bbdata) && $bbdata['onmouseout'] != "") {
            $onmouseout = "onMouseOut=\"".$bbdata['onmouseout']."\"";
        } else {
            $onmouseout = "";
        }
        if (array_key_exists('phpfunction', $bbdata) && $bbdata['phpfunction'] != "") {
            $php = $bbdata['phpfunction'].(substr($bbdata['phpfunction'], -1, 1) != ";" ? ";" : "");
            ob_start();
            eval($php);
            $phpfunction = ob_get_contents();
            ob_end_clean();
        } else {
            $phpfunction = "";
        }

        $dropdown = '';
        if (array_key_exists('dropdown', $bbdata) && $bbdata['dropdown'] == TRUE) {
            $dropdown = 'data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"';
            $bbcodes .= '<div class="dropdown display-inline-block">';
        }

        $id = '';
        $bbdata['id'] = '';
        if (array_key_exists('id', $bbdata) && $bbdata['id'] != "") {
            $id = 'id="'.$bbdata['id'].'"';
        }

        $bbcodes .= substr($bbdata['value'], 0, 1) != "!" ? "<input ".$type." class='bbcode' ".$id." onclick=\"".$onclick."\" ".$onmouseover." ".$onmouseout." title='".$bbdata['description']."' ".$dropdown."/>\n" : "";

        if (array_key_exists('dropdown', $bbdata) && $bbdata['dropdown'] == TRUE) {
            $bbcodes .= "<div class='bbcode-popup dropdown-menu ".(!empty($bbdata['dropdown_class']) ? $bbdata['dropdown_class'] : '')."' ".(!empty($bbdata['dropdown_style']) ? 'style="'.$bbdata['dropdown_style'].'"' : '')." aria-labelledby='".$bbdata['id']."'>";
        }
        if (array_key_exists('html_start', $bbdata) && $bbdata['html_start'] != "") {
            $bbcodes .= $bbdata['html_start']."\n";
        }
        if (array_key_exists('includejscript', $bbdata) && $bbdata['includejscript'] != "") {
            $bbcodes .= "<script type='text/javascript' src='".INCLUDES."bbcodes/".$bbdata['includejscript']."'></script>\n";
        }
        if (array_key_exists('calljscript', $bbdata) && $bbdata['calljscript'] != "") {
            $bbcodes .= "<script type='text/javascript'>\n<!--\n".$bbdata['calljscript']."\n-->\n</script>\n";
        }
        if (array_key_exists('phpfunction', $bbdata) && $bbdata['phpfunction'] != "") {
            $bbcodes .= $phpfunction;
        }
        if (array_key_exists('html_middle', $bbdata) && $bbdata['html_middle'] != "") {
            $bbcodes .= $bbdata['html_middle']."\n";
        }
        if (array_key_exists('html_end', $bbdata) && $bbdata['html_end'] != "") {
            $bbcodes .= $bbdata['html_end']."\n";
        }
        if (array_key_exists('dropdown', $bbdata) && $bbdata['dropdown'] == TRUE) {
            $bbcodes .= '</div>';
            $bbcodes .= '</div>';
        }
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
