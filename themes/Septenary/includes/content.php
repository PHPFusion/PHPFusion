<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2014 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Septenary Theme
| Filename: content.php
| Version: 1.00
| Author: PHP-Fusion Mods UK
| Developer & Designer: Craig, Chan
| Site: http://www.phpfusionmods.co.uk
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
open_grid('section-3', 1);
echo AU_CENTER ? "<div class='au-content'>".AU_CENTER."</div>\n" : '';
echo "<div class='row'>\n";
if (LEFT !=='' or RIGHT !=='') {
	echo "<div class='hidden-xs col-sm-3 col-md-3 col-lg-3 leftbar'>\n";
	echo RIGHT.LEFT;
	echo "</div>\n";
}
echo "<div class='".how_to_calculate_bootstrap_span()."main-content'>\n";
// Get all notices, we also include notices that are meant to be displayed on all pages
echo renderNotices(getNotices(array('all', FUSION_SELF)));
echo U_CENTER;
echo CONTENT;
echo L_CENTER;
echo "</div>\n";
echo BL_CENTER ? "<div class='bl-content'>".BL_CENTER."</div>\n" : '';
echo "</div>\n";
close_grid(1);

/**
 * You can have many formula to it..
 * @return string
 */
function how_to_calculate_bootstrap_span() {

	$default_side_span_sm = 3; // <---- change this to change the sidebar width on tablet
	$default_side_span_md = 3; //<--- change this to change the sidebar width on laptop
	$default_side_span_lg = 3; // <---- change this to change the sidebar width on desktop
	$how_many_sides_are_visible = 0;

	if (defined('LEFT') && LEFT !=='') $how_many_sides_are_visible++;

	if ($how_many_sides_are_visible > 0) {
		$span =  array(
			'col-xs-' => 12,
			'col-sm-' => 12-($how_many_sides_are_visible*$default_side_span_sm),
			'col-md-' => 12-($how_many_sides_are_visible*$default_side_span_md),
			'col-lg-' => 12-($how_many_sides_are_visible*$default_side_span_lg),
		);
	} else {
		$span = array(
			'col-xs-' => 12,
			'col-sm-' => 12,
			'col-md-' => 12,
			'col-lg-' => 12,
		);
	}
	$css = '';
	foreach($span as $css_class => $css_value) {
		$css .= "".$css_class.$css_value." ";
	}
	return $css;
}
