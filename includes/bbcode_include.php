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
if (!defined("IN_FUSION")) { die("Access Denied"); }

function display_bbcodes($width, $textarea_name = "message", $inputform_name = "inputform", $selected = false) {
	global $bbcode_cache, $p_data;

	if (!$bbcode_cache) { cache_bbcode(); }
	if ($selected) { $sel_bbcodes = explode("|", $selected); }
	$__BBCODE__ = array(); $bbcodes = "";
	
	if (is_array($bbcode_cache) && count($bbcode_cache)) {
		foreach ($bbcode_cache as $bbcode) {
			if ($selected && in_array($bbcode, $sel_bbcodes)) {
				if (file_exists(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {
					include (LOCALE.LOCALESET."bbcodes/".$bbcode.".php");
				} elseif (file_exists(LOCALE."English/bbcodes/".$bbcode.".php")) {
					include (LOCALE."English/bbcodes/".$bbcode.".php");
        }
				include (INCLUDES."bbcodes/".$bbcode."_bbcode_include_var.php");
			} elseif (!$selected) {
				if (file_exists(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {
					include (LOCALE.LOCALESET."bbcodes/".$bbcode.".php");
				} elseif (file_exists(LOCALE."English/bbcodes/".$bbcode.".php")) {
					include (LOCALE."English/bbcodes/".$bbcode.".php");
        }
				include (INCLUDES."bbcodes/".$bbcode."_bbcode_include_var.php");
			}
		}	
	}

	if (sizeof($__BBCODE__) != 0) {
		foreach ($__BBCODE__ as $key => $bbdata) {
			if (file_exists(INCLUDES."bbcodes/images/".$bbdata['value'].".png")) {
				$type = "type='image' src='".INCLUDES."bbcodes/images/".$bbdata['value'].".png'";
			} else if (file_exists(INCLUDES."bbcodes/images/".$bbdata['value'].".gif")) {
				$type = "type='image' src='".INCLUDES."bbcodes/images/".$bbdata['value'].".gif'";
			} else if (file_exists(INCLUDES."bbcodes/images/".$bbdata['value'].".jpg")) {
				$type = "type='image' src='".INCLUDES."bbcodes/images/".$bbdata['value'].".jpg'";
			} else {
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
			
			$bbcodes .= substr($bbdata['value'], 0, 1) != "!" ? "<input ".$type." class='bbcode' onclick=\"".$onclick."\" ".$onmouseover." ".$onmouseout." title='".$bbdata['description']."' />\n":"";
			if (array_key_exists('html_start', $bbdata) && $bbdata['html_start'] != "") { $bbcodes .= $bbdata['html_start']."\n"; }
			if (array_key_exists('includejscript', $bbdata) && $bbdata['includejscript'] != "") { $bbcodes .= "<script type='text/javascript' src='".INCLUDES."bbcodes/".$bbdata['includejscript']."'></script>\n"; }
			if (array_key_exists('calljscript', $bbdata) && $bbdata['calljscript'] != "") { $bbcodes .= "<script type='text/javascript'>\n<!--\n".$bbdata['calljscript']."\n-->\n</script>\n"; }
			if (array_key_exists('phpfunction', $bbdata) && $bbdata['phpfunction'] != "") { $bbcodes .= $phpfunction; }
			if (array_key_exists('html_middle', $bbdata) && $bbdata['html_middle'] != "") { $bbcodes .= $bbdata['html_middle']."\n"; }
			if (array_key_exists('html_end', $bbdata) && $bbdata['html_end'] != "") { $bbcodes .= $bbdata['html_end']."\n"; }
		}
	}
	unset ($__BBCODE__);

	return "<div style='width:".$width."'>\n".$bbcodes."</div>\n";
}

function strip_bbcodes($text) {
	global $bbcode_cache, $p_data;
	$textarea_name = "";
	$inputform_name = "";
	if (!iADMIN) {
		if (!$bbcode_cache) { cache_bbcode(); }
		if (is_array($bbcode_cache) && count($bbcode_cache)) {
			foreach ($bbcode_cache as $bbcode) {
				if (file_exists(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {
					include (LOCALE.LOCALESET."bbcodes/".$bbcode.".php");
				}
				include (INCLUDES."bbcodes/".$bbcode."_bbcode_include_var.php");
			}
		}
		if (isset($__BBCODE_NOT_QUOTABLE__) && sizeof($__BBCODE_NOT_QUOTABLE__) != 0) {
			foreach ($__BBCODE_NOT_QUOTABLE__ as $key => $bbname) {
				$text = preg_replace('#\['.$bbname.'(.*?)\](.*?)\[/'.$bbname.'\]#si', '', $text);
			}
			unset ($__BBCODE_NOT_QUOTABLE__);
		}
	}
	return $text;
}
?>
