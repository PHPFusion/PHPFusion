<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 PHP-Fusion International
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: components.php
| Author: Hien (Frederick MC Chan)
| Author: Falk (Joakim Falk)
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
require_once INCLUDES."theme_functions_include.php";

function breadcrumbs() {
	$html = "";
	$html .= "<ul class='breadcrumb'>\n";
	$html .= "<li><a href='#'>Home</a> <span class='divider'>/</span></li>";
	$html .= "<li><a href='#'>Library</a> <span class='divider'>/</span></li>";
	$html .= "<li class='active'>Data</li>";
	$html .= "</ul>";
	return $html;
}

// accordion
function accordion($id, $array) {
	$html = "<div class='accordion' id='$id'>\n";
	foreach ($array as $field_id => $data) {
		$active = ($field_id == '0') ? 'in' : '';
		$child = ($field_id == '0') ? '' : 'accordion-n';
		$title =  str_replace('/[^A-Z]+$/i', " ",$data['title']);
		$title_id_cc = str_replace(" ", "-", $title);
		$html .= 	"<div class='accordion-group clearfix'>";
		$html .=	"<div class='accordion-heading'>";
		$html .=	"<a class='accordion-toggle $child' data-toggle='collapse' data-parent='#$id' href='#".$title_id_cc."-".$id."'> ".$title." <i class='entypo cw'></i></a>";
		$html .= 	"</div>\n";
		$html .=    "<div id='".$title_id_cc."-".$id."' class='accordion-body collapse $active'><div class='accordion-inner'>\n";
		$html .=    $data['content'];
		$html .=    "</div></div></div>\n";
	}
	$html .= "</div>\n";
	return $html;
}



function trim_text($word, $limit) {

	if (strlen($word) > $limit) {
		$word = substr($word, 0, $limit);
		$word = substr($word, 0, strrpos($word, ' ')).'...';
	}
	return $word;
}

function unformat_text($word) {
	// this will clean up all formatting in text.
	$format[] = "<strong>";
	$format[] = "<center>";
	$format[] = "<p>";
	$format[] = "<span>";
	$format[] = "<h1>";
	$format[] = "<h2>";
	$format[] = "<h3>";
	$format[] = "<h4>";
	$format[] = "<h5>";
	$format[] = "<h6>";
	foreach($format as $arr=>$value) {
		$word = str_replace($value, '', $word);
	}
	return $word;
}


/* Image function */
function get_avg_luminance($filename, $num_samples=10) {
	$img = imagecreatefromjpeg($filename);
	$width = imagesx($img);
	$height = imagesy($img);
	$x_step = intval($width/$num_samples);
	$y_step = intval($height/$num_samples);
	$total_lum = 0;
	$sample_no = 1;
	for ($x=0; $x<$width; $x+=$x_step) {
		for ($y=0; $y<$height; $y+=$y_step) {
			$rgb = imagecolorat($img, $x, $y);
			$r = ($rgb >> 16) & 0xFF;
			$g = ($rgb >> 8) & 0xFF;
			$b = $rgb & 0xFF;
			// choose a simple luminance formula from here
			// http://stackoverflow.com/questions/596216/formula-to-determine-brightness-of-rgb-color
			$lum = ($r+$r+$b+$g+$g+$g)/6;
			$total_lum += $lum;
			// debugging code
			//           echo "$sample_no - XY: $x,$y = $r, $g, $b = $lum<br />";
			$sample_no++;
		}
	}
	// work out the average
	$avg_lum  = $total_lum/$sample_no;
	return $avg_lum;
}

add_to_jquery("
$('.atooltip').tooltip();
");
?>