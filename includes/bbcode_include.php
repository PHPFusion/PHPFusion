<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

function display_bbcodes($width, $textarea_name = "message", $inputform_name = "inputform", $selected = "") {
    $bbcode_cache = cache_bbcode();
    if ($selected) {
        $sel_bbcodes = explode("|", $selected);
    }
    $__BBCODE__ = array();
    $bbcodes = "";

    foreach ($bbcode_cache as $bbcode) {
        if (file_exists(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {
            $locale_file = LOCALE.LOCALESET."bbcodes/".$bbcode.".php";
            \PHPFusion\Locale::setLocale($locale_file);
        } elseif (file_exists(LOCALE."English/bbcodes/".$bbcode.".php")) {
            $locale_file = LOCALE."English/bbcodes/".$bbcode.".php";
            \PHPFusion\Locale::setLocale($locale_file);
        }
    }
    $locale = fusion_get_locale();
    foreach ($bbcode_cache as $bbcode) {
        if ($selected && in_array($bbcode, $sel_bbcodes)) {
            include(INCLUDES."bbcodes/".$bbcode."_bbcode_include_var.php");
        } elseif (!$selected) {
            include(INCLUDES."bbcodes/".$bbcode."_bbcode_include_var.php");
        }
    }

    $check_path = $_SERVER['DOCUMENT_ROOT'].fusion_get_settings('site_path').'includes/bbcodes/images/';
    $img_path = fusion_get_settings('siteurl').'includes/bbcodes/images/';

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
                $onclick = "insertText('".$textarea_name."','".$bbdata['bbcode_start']."','".$inputform_name."');return false;";
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
        $bbcodes .= substr($bbdata['value'], 0,
            1) != "!" ? "<input ".$type." class='bbcode' onclick=\"".$onclick."\" ".$onmouseover." ".$onmouseout." title='".$bbdata['description']."' />\n" : "";
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
    }
    unset ($__BBCODE__);

    return "<div style='width:".$width."'>\n".$bbcodes."</div>\n";
}

function strip_bbcodes($text) {
    return $text;

    global $p_data;
    if (iADMIN) {
        return $text;
    }
    $textarea_name = "";
    $inputform_name = "";
    $bbcode_cache = cache_bbcode();

    foreach ($bbcode_cache as $bbcode) {
        $locale = fusion_get_locale('', LOCALE.LOCALESET.'bbcodes/'.$bbcode.'.php');

        include(INCLUDES."bbcodes/".$bbcode."_bbcode_include_var.php");
    }
    if (!empty($__BBCODE_NOT_QUOTABLE__) and is_array($__BBCODE_NOT_QUOTABLE__)) {
        foreach ($__BBCODE_NOT_QUOTABLE__ as $bbname) {
            $text = preg_replace('#\['.$bbname.'(.*?)\](.*?)\[/'.$bbname.'\]#si', '', $text);
        }
        unset ($__BBCODE_NOT_QUOTABLE__);
    }

    return $text;
}


