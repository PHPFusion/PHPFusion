<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2009 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: geshi_bbcode_include.php
| Author: Wooya
| Fixed: slawekneo
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

if (preg_match("/\/forum\//i", FUSION_REQUEST)) global $data;

unset($lines);
unset($ccount);
unset($matches);
include_once(INCLUDES."bbcodes/geshi/geshi.php");
preg_match_all("#\[geshi=(.*?)\](.*?)\[/geshi\]#si",$text,$matches,PREG_PATTERN_ORDER);
for($i=0; $i<count($matches[1]); $i++) {
	$lines = explode("\n", $matches[2][$i]);
	if (count($lines)<200) {
		$input = str_replace('<br>','',str_replace('<br  />','', str_replace('<br />', '', stripslashes($matches[2][$i]))));
		//replace problematic characters
		$search = array("\\", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;", "&amp;");
		$replace = array("\\\\", "\"", "'", "\\", "\"", "\'", "<", ">", "&");
		$input = str_replace($search,$replace, $input);
		$geshi = new GeSHi($input, $matches[1][$i]);
		$geshi -> set_header_type(GESHI_HEADER_PRE);
		$geshi -> set_overall_style('font-family:\'Courier New\', Courier; font-size:12px;');
		$geshi -> set_link_styles(GESHI_LINK, 'font-weight:bold;');
		$geshi -> set_link_styles(GESHI_HOVER, 'background-color: #f0f000;');
		$geshi -> enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 10);
		$geshi -> set_footer_content($locale['bb_geshi_info']);
		$geshi -> set_footer_content_style('font-family:Verdana,Arial,sans-serif;color:#808080;font-size:9px;font-weight:bold;background-color:#f0f0ff;border-top: 1px solid #d0d0d0;padding:2px;width:400px');
		if (preg_match("/\/forum\//i", FUSION_REQUEST) && isset($data['post_id'])) {
			$geshi_save = "<a href='".INCLUDES."bbcodes/geshi_bbcode_save.php?thread_id=".$_GET['thread_id']."&amp;post_id=".$data['post_id']."&amp;code_id=".$i."'><img src='".INCLUDES."bbcodes/images/geshi_save.png' alt='".$locale['bb_geshi_save']."' title='".$locale['bb_geshi_save']."' style='border:none' /></a>&nbsp;&nbsp;";
		} else {
			$geshi_save = "";
		}
		$text2 = "<div class='tbl-border tbl2' style='width:400px'>".$geshi_save."<strong>GeSHi: ".$geshi->get_language_name()."</strong></div><div class='tbl-border tbl1' style='width:400px;height:auto;white-space:nowrap;overflow:auto;background-color:#ffffff;'><code style='white-space:nowrap'>".$geshi->parse_code()."</code></div>";
		$text = str_replace($matches[0][$i], $text2, $text);
	} else {
		$ccount = substr_count($text, "[geshi=");
		for ($i=0;$i < $ccount;$i++) {
			if (preg_match("/\/forum\//i", FUSION_REQUEST) && isset($data['post_id'])) {
				$geshi_save = "<a href=\'".INCLUDES."bbcodes/geshi_bbcode_save.php?thread_id=".$_GET['thread_id']."&amp;post_id=".$data['post_id']."&amp;code_id=".$i."\'><img src=\'".INCLUDES."bbcodes/images/geshi_save.png\' alt=\'".$locale['bb_geshi_save']."\' title=\'".$locale['bb_geshi_save']."\' style=\'border:none\' /></a>&nbsp;&nbsp;";
			} else {
				$geshi_save = "";
			}
			$text = preg_replace("#\[geshi=(.*?)\](.*?)\[/geshi\]#sie", "'<div class=\'tbl-border tbl2\' style=\'width:400px\'>".$geshi_save."<strong><i><u>".$locale['bb_geshi_parser1'].":</u></i> ".$locale['bb_geshi_parser2'].":</strong></div><div class=\'tbl-border tbl1\' style=\'width:400px;white-space:nowrap;overflow:auto\'><code style=\'white-space:nowrap\'>'.formatcode('\\2').'<br /><br /><br /></code></div>'", $text);
		}
	}
	unset($lines);
}
?>
